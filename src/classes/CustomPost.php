<?php
namespace WpCustomPostLib;

require_once(__DIR__.'/Renderer.php');
require_once(__DIR__.'/AdminTable.php');
require_once(__DIR__.'/AbstractData.php');
require_once(__DIR__.'/CustomTaxonomy.php');

class CustomPost {
  // このカスタム投稿の名前(PG内でpost_typeとして参照される方)
  private $_name;

  // このカスタム投稿の名前(管理画面GUIに表示される方)
  private $_label;

  // 管理画面の編集画面の描画を担当するオブジェクト
  protected $_renderer;

  // 管理画面の一覧表の描画を担当するオブジェクト
  protected $_admintable;

  // このカスタム投稿で扱うデータクラス群
  private $data = [];

  // このクラスに紐付いているタクソノミー群
  protected $taxonomies = [];

  // このクラス内でCallbackとして使用される関数群
  private $_callbacks = [
    'edit_form_after_title' => [],
    'wp_enqueue_scripts'    => [],
    'admin_enqueue_scripts' => [],
    'save_post'             => [],
    'wp_insert_post_data'   => [],
    'rest_prepare'          => [],
    'pre_get_posts'         => [],
  ];

  public function __construct(string $name, string $label, array $options=[]) {
    $self = $this;
    $this->_name = $name;
    $this->_label = $label;
    $this->_admintable = new AdminTable();

    // オプションにデフォルト値を適用
    $options = $this->init_options($label, $options);

    // オプションに基づいてオブジェクトを初期化
    $this->_renderer = new DefaultRenderer($options['template']);

    // 各種イベントを登録していく
    add_action('init', function() use($self, $options) { register_post_type($self->get_name(), $options); });
    add_action('wp_enqueue_scripts',                 [$this, 'wp_enqueue_scripts']);
    add_action('admin_enqueue_scripts',              [$this, 'admin_enqueue_scripts']);
    add_action('edit_form_after_title',              [$this, 'edit_form_after_title']);
    add_filter('wp_insert_post_data',                [$this, 'wp_insert_post_data'], 10, 1);
    add_filter('pre_get_posts',                      [$this, 'pre_get_posts']);
    add_action("save_post_{$name}",                  [$this, 'save_post'], 10, 3);
    add_filter("rest_prepare_{$name}",               [$this, 'rest_prepare'], 10, 3);
    add_filter("manage_{$name}_posts_columns",       [$this->_admintable, 'manage_posts_columns'], 10, 1);
    add_action("manage_{$name}_posts_custom_column", [$this->_admintable, 'manage_posts_custom_column'], 10, 2);
  }

  protected function init_options(string $label, array $options): array {
    $name = $this->get_name();

    // この値は強制的に上書き
    $options['label'] = $label;
    $options['labels'] = ['name' => $label];

    // デフォルトの設定はHeadless CMSで使用する感じに
    return array_merge([
      'public' => false,
      'show_ui' => true,
      'show_in_rest' => true,
      'menu_position' => 5,
      'supports' => ['title'],
      'template' => get_template_directory()."/views/${name}.php",
    ], $options);
  }

  public function get_name(): string {
    return $this->_name;
  }

  public function get_table(): \WpCustomPostLib\AdminTable {
    return $this->_admintable;
  }

  public function on(string $event, callable $fn): self {
    if (!in_array($event, array_keys($this->_callbacks)))
      throw new Exception('無効なイベントを登録しようとしています');
    $this->_callbacks[$event][] = $fn;
    return $this;
  }

  public function append(\WpCustomPostLib\AbstractData $data): self {
    $this->data[$data->name] = $data;
    return $this;
  }

  public function make_category(string $name, string $label, array $options=[]): \WpCustomPostLib\CustomTaxonomy {
    $options['hierarchical'] = true;
    $tax = new CustomTaxonomy($name, $label, $this, $options);
    $this->taxonomies[] = $tax;
    return $tax;
  }

  public function make_tag(string $name, string $label, array $options=[]): \WpCustomPostLib\CustomTaxonomy {
    $options['hierarchical'] = false;
    $tax = new CustomTaxonomy($name, $label, $this, $options);
    $this->taxonomies[] = $tax;
    return $tax;
  }

  public function make_choices(string $name, string $label, array $options=[]): \WpCustomPostLib\CustomTaxonomy {
    $options['hierarchical'] = false;
    $options['show_admin_column'] = false;
    $options['show_meta_box'] = false;
    $tax = new CustomTaxonomy($name, $label, $this, $options);
    $this->taxonomies[] = $tax;
    return $tax;
  }






  /* ===============================================
   * 以下、WordPressのHookに対応するための関数群
   * =============================================== */
  private function is_associated(): bool {
    return $this->is_associated_archive() || $this->is_associated_single();
  }

  private function is_associated_single(): bool {
    return is_singular($this->get_name());
  }

  private function is_associated_archive(): bool {
    if (is_post_type_archive($this->get_name())) return true;
    return is_tax(array_map(function($t){return $t->get_name();}, $this->taxonomies));
  }

  public function wp_enqueue_scripts() {
    if ($this->is_associated()) {
      foreach ($this->_callbacks['wp_enqueue_scripts'] as $fn) {
        call_user_func($fn);
      }
    }
  }

  public function admin_enqueue_scripts() {
    if (is_null(get_current_screen())) return;
    if (get_current_screen()->post_type !== $this->get_name()) return;
    foreach ($this->_callbacks['admin_enqueue_scripts'] as $fn) {
      call_user_func($fn);
    }
  }

  public function edit_form_after_title(\WP_Post $post) {
    if ($post->post_type !== $this->get_name()) return;

    // まずはユーザーがやりたいことを実行
    foreach ($this->_callbacks['edit_form_after_title'] as $fn) {
      call_user_func($fn, $post);
    }

    // 準備が整ったはずなので、データを準備していく
    $render_data = [
      'data_class' => $this->data,
      'terms' => [],
    ];

    // このカスタム投稿に関連付けられているtermをすべて取得する
    foreach ($this->taxonomies as $tax) {
      $render_data['terms'][$tax->get_name()] = $tax->list_all();
    }

    // データクラスにデータの取得をやってもらう
    foreach ($this->data as $x) {
      $render_data[$x->name] = $x->extract($post);
    }

    // データ取得し終わったので画面に描画する
    $this->_renderer->render($render_data, $post, $this);
  }

  public function wp_insert_post_data($data) {
    if ($data['post_type'] !== $this->get_name()) return $data;
    foreach ($this->data as $x) {
      $data = $x->start_save($data);
    }
    foreach ($this->_callbacks['wp_insert_post_data'] as $fn) {
      $data = call_user_func($fn, $data);
    }
    return $data;
  }

  public function pre_get_posts(\WP_Query $query) {
    if ($this->is_associated_archive()) {
      foreach ($this->_callbacks['pre_get_posts'] as $fn) {
        call_user_func($fn, $query);
      }
    }
  }

  public function save_post(int $post_id, \WP_Post $post, bool $update) {
    if (!current_user_can('edit_post', $post_id)) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    foreach ($this->_callbacks['save_post'] as $fn) {
      call_user_func($fn, $post_id, $post, $update);
    }
    foreach ($this->data as $x) {
      $x->save($post_id, $post, $update);
    }
  }

  public function rest_prepare(\WP_REST_Response $response, \WP_Post $post, \WP_REST_Request $request): \WP_REST_Response {
    foreach ($this->data as $x) {
      $response->data[$x->name] = $x->extract($post);
    }
    foreach ($this->_callbacks['rest_prepare'] as $fn) {
      call_user_func($fn, $response, $post, $request);
    }
    return $response;
  }
}
