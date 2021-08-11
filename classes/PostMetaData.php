<?php
namespace WpCustomPostLib;

require_once(__DIR__.'/AbstractData.php');

// postmetaに値をひとつ保存するデータクラス
class PostMetaData extends \WpCustomPostLib\AbstractData {
  protected $accept_html = false;
  protected $multi = false;
  protected $default_value = null;

  public function do_save(int $post_id, \WP_Post $post, bool $update) {
    if (!isset($_POST[$this->name])) return;

    if ($this->multi) {
      $this->set_default_value($post_id, $post, $update);
      delete_post_meta($post_id, $this->name);
      foreach ($_POST[$this->name] as $v) {
        add_post_meta($post_id, $this->name, $this->sanitize($v));
      }
    } else {
      $this->set_default_value($post_id, $post, $update);
      update_post_meta($post_id, $this->name, $this->sanitize($_POST[$this->name]));
    }
  }

  public function do_extract(\WP_Post $post) {
    if ($this->multi) {
      return array_map([$this, 'contaminate'], get_post_meta($post->ID, $this->name, false));
    } else {
      return $this->contaminate(get_post_meta($post->ID, $this->name, true));
    }
  }

  public function multiple(bool $flg=true): self {
    $this->multi = $flg;
    return $this;
  }

  public function accept_html(bool $flag=true): self {
    $this->accept_html = $flag;
    return $this;
  }

  public function default($value): self {
    $this->default_value = $value;
    return $this;
  }

  protected function sanitize($data) {
    return $this->accept_html ? wp_kses_post($data) : esc_textarea($data);
  }

  protected function contaminate($data) {
    return $this->accept_html ? esc_textarea($data) : $data;
  }

  private function set_default_value(int $post_id, \WP_Post $post, bool $update) {
    if (is_null($this->default_value)) return;
    if (!empty($_POST[$this->name])) return;
    if (is_callable($this->default_value)) {
      $_POST[$this->name] = call_user_func($this->default_value, $post_id, $post, $update);
    } else {
      $_POST[$this->name] = $this->multi ? [$this->default_value] : $this->default_value;
    }
  }
}
