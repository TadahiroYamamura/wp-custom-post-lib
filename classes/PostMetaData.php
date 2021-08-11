<?php
namespace WpCustomPostLib;

require_once(__DIR__.'/AbstractData.php');

// postmetaに値をひとつ保存するデータクラス
class PostMetaData extends \WpCustomPostLib\AbstractData {
  private $accept_type = 'text';
  protected $acceptable_type = ['text', 'html', 'object'];
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

  private function accept(string $typename): self {
    if (in_array($typename, $this->acceptable_type, true)) {
      $this->accept = $typename;
      return $this;
    }
    $trace = debug_backtrace();
    $f = $trace[0]['file'];
    $l = $trace[0]['line'];
    $msg = implode(' ', [
      "Invalid data type was requested in ${f} on line ${l}.",
      "This request will be ignored.(you specified: ${typename})",
    ]);
    trigger_error($msg , E_USER_WARNING);
  }

  public function accept_text(): self {
    $this->accept('text');
    return $this;
  }

  public function accept_html(): self {
    $this->accept('html');
    return $this;
  }

  public function accept_object(): self {
    $this->accept('object');
    return $this;
  }

  public function default($value): self {
    $this->default_value = $value;
    return $this;
  }

  protected function sanitize($data) {
    switch ($this->accept_type) {
      case 'text': return esc_textarea($data);
      case 'html': return wp_kses_post($data);
      case 'object': return $data;
    }
    trigger_error(
      $this->accept_type." is not an implemented data type.",
      E_USER_ERROR);
  }

  protected function contaminate($data) {
    switch ($this->accept_type) {
      case 'text': return $data;
      case 'html': return esc_textarea($data);
      case 'object': return $data;
    }
    trigger_error(
      $this->accept_type." is not an implemented data type.",
      E_USER_ERROR);
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
