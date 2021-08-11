<?php
namespace WpCustomPostLib;

require_once(__DIR__.'/PostMetaData.php');

// postmetaに値を複数の保存するデータクラス
class PostMetaDataMultiple extends \WpCustomPostLib\PostMetaData {
  public function __construct($name) {
    parent::__construct($name);
    array_push($this->start_save, function($post) {
      delete_post_meta($post->ID, $this->name);
    });
  }

  public function do_save(int $post_id, \WP_Post $post, bool $update) {
    if (!isset($_POST[$this->name])) return;
    foreach ($_POST[$this->name] as $x) {
      add_post_meta($post_id, $this->name, $this->sanitize($x));
    }
  }

  public function do_extract(\WP_Post $post) {
    return $this->contaminate(get_post_meta($post_id, $this->name, false));
  }
}
