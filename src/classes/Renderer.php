<?php
namespace WpCustomPostLib;

require_once(__DIR__.'/CustomPost.php');

abstract class Renderer {
  public abstract function render($data, \WP_Post $post, \WpCustomPostLib\CustomPost $custom_post);
}

// デフォルト実装
class DefaultRenderer extends \WpCustomPostLib\Renderer {
  private array $readable_props = ['template'];

  private string $template;

  public function __construct(string $template) {
    $this->change_template($template);
  }

  public function __get(string $name) {
    if (in_array($name, $this->readable_props, true)) {
      return $this->$name;
    }
    $trace = debug_backtrace();
    $f = $trace[0]['file'];
    $l = $trace[0]['line'];
    $msg = "Trying to read an undefined property in ${f} on line ${l}.";
    trigger_error($msg, E_USER_ERROR);
    return null;
  }

  public function __isset(string $name) {
    return in_array($name, $this->readable_props, true)
      && !empty($this->$name);
  }

  public function change_template(string $template) {
    if (!file_exists($template)) {
      $trace = debug_backtrace();
      $f = $trace[0]['file'];
      $l = $trace[0]['line'];
      $msg = "Not existing template specified in ${f} on line ${l}: ${template}";
      trigger_error($msg, E_USER_WARNING);
    }
    $this->template = $template;
    return $this;
  }

  public function render($data, \WP_Post $post, \WpCustomPostLib\CustomPost $custom_post) {
    extract($data);
    ob_start();
    include($this->template);
    ob_end_flush();
  }
}
