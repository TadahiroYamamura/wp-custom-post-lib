<?php
namespace WpCustomPostLib;

require_once(__DIR__.'/PostImageData.php');

class PostThumbnailData extends \WpCustomPostLib\PostImageData {
  protected function do_save(int $post_id, \WP_Post $post, bool $update) {
    if (!isset($_POST[$this->name])) return;
    $dat = $this->sanitize($_POST[$this->name]);
    set_post_thumbnail($post, $dat);
  }
}
