<?php
namespace WpCustomPostLib;

abstract class AbstractData {
  public $name;
  protected $start_save = [];
  protected $on_save = [];

  public function __construct($name) {
    $this->name = $name;
  }

  public function start_save($data) {
    foreach ($this->start_save as $callback) {
      $data = call_user_func($callback, $data);
    }
    return $data;
  }

  public function save(int $post_id, \WP_Post $post, bool $update) {
    foreach ($this->on_save as $callback) {
      call_user_func($callback, $post_id, $post, $update);
    }
    $this->do_save($post_id, $post, $update);
  }

  public function extract(\WP_Post $post) {
    return $this->do_extract($post);
  }

  // データを保存用にサニタイジングする
  protected function sanitize($data) {
    return esc_textarea($data);
  }

  // データを管理画面用に加工する。
  // サニタイジング(=消毒、無害化)に対する反対語が分からないので
  // とりあえず"汚染"という名前を付ける。
  protected function contaminate($data) {
    return $data;  // 基本はそのまま表示でよい。保存時にエスケープされているはずなので。
  }

  protected abstract function do_save(int $post_id, \WP_Post $post, bool $update);
  protected abstract function do_extract(\WP_Post $post);
}
