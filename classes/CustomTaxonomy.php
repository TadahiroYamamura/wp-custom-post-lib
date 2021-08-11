<?php
namespace WpCustomPostLib;

require_once(__DIR__.'/CustomPost.php');

class CustomTaxonomy {
  private string $name;
  private string $label;
  private \WpCustomPostLib\CustomPost $post;

  public function __construct(string $name, string $label, \WpCustomPostLib\CustomPost $custom_post, array $options=[]) {
    $this->name = $name;
    $this->label = $label;
    $this->post = $custom_post;
    $options = $this->init_options($label, $options);
    add_action('init', function() use($name, $custom_post, $options) {
      register_taxonomy($name, $custom_post->get_name(), $options);
    });

    // 「メタボックスに表示させない」という独自オプションを定義
    if ($options['show_meta_box'] === false) {
      add_action("add_meta_boxes_".$custom_post->get_name(), function() use($name, $custom_post, $options) {
        $metabox_id = $options['hierarchical'] ? $name."div" : "tagsdiv-".$name;
        remove_meta_box($metabox_id, $custom_post->get_name(), 'side');
      });
    }
  }

  protected function init_options(string $label, array $options): array {
    // この値は強制的に上書き
    $options['label'] = $label;
    $options['labels'] = ['name' => $label];

    // デフォルトの設定はHeadless CMSで使用する感じに
    return array_merge([
      'public'            => false,
      'show_ui'           => true,
      'show_admin_column' => true,
      'show_meta_box'     => true,
    ], $options);
  }

  public function get_name(): string {
    return $this->name;
  }

  public function list_all(): array {
    return get_terms([
      'taxonomy'   => $this->name,
      'hide_empty' => false,
      'orderby'    => 'menu_order',
      'order'      => 'ASC'
    ]);
  }

  public function enable_filter(\WpCustomPostLib\CustomPost $custom_post=null, array $options=[]): self {
    if (is_null($custom_post)) $custom_post = $this->post;
    add_action('restrict_manage_posts', function($post_type) use($custom_post, $options) {
      if ($post_type !== $custom_post->get_name()) return;
      wp_dropdown_categories(array_merge([
        'name'            => $this->name,
        'taxonomy'        => $this->name,
        'value_field'     => 'slug',
        'orderby'         => 'ID',
        'order'           => 'ASC',
        'show_option_all' => 'すべての'.$this->label,
        'show_count'      => true,
        'hide_empty'      => true,
        'hierarchical'    => true,
        'hide_if_empty'   => true,
      ], $options));
    });
    return $this;
  }
}
