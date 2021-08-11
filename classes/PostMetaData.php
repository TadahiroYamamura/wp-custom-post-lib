<?php
namespace WpCustomPostLib;

require_once(__DIR__.'/AbstractData.php');

// postmetaに値をひとつ保存するデータクラス
class PostMetaData extends \WpCustomPostLib\AbstractData {
  protected $accept_html = false;
  protected $default_value = null;

  public function do_save(int $post_id, \WP_Post $post, bool $update) {
    if (!isset($_POST[$this->name])) return;
    if (!is_null($this->default_value) && empty($_POST[$this->name])) {
      $_POST[$this->name] = is_callable($this->default_value) ? call_user_func($this->default_value, $post_id, $post, $update) : $this->default_value;
    }
    update_post_meta($post_id, $this->name, $this->sanitize($_POST[$this->name]));
  }

  public function do_extract(\WP_Post $post) {
    return $this->contaminate(get_post_meta($post->ID, $this->name, true));
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
}
