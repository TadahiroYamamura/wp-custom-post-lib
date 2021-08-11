<?php
namespace WpCustomPostLib;

require_once(__DIR__.'/CustomPost.php');
require_once(__DIR__.'/AbstractData.php');

class PostImageData extends \WpCustomPostLib\AbstractData {
  protected $multi = false;

  public function __construct(string $name) {
    parent::__construct($name);
  }

  public function multiple(bool $flg=true): self {
    $this->multi = $flg;
    return $this;
  }

  protected function do_save(int $post_id, \WP_Post $post, bool $update) {
    if ($this->multi) {
      delete_post_meta($post_id, $this->name);
      if (isset($_POST[$this->name])) {
        foreach ($_POST[$this->name] as $x) {
          add_post_meta($post_id, $this->name, $this->sanitize($x));
        }
      }
    } else {
      if (isset($_POST[$this->name])) {
        update_post_meta($post_id, $this->name, $this->sanitize($_POST[$this->name]));
      }
    }
  }

  protected function do_extract(\WP_Post $post) {
    if ($this->multi) {
      return array_map(
        [$this, 'extract_image_data'],
        get_post_meta($post->ID, $this->name, false));
    } else {
      return $this->extract_image_data(
        get_post_meta($post->ID, $this->name, true));
    }
  }

  private function extract_image_data($id) {
    return [
      'id' => $id,
      'url' => wp_get_attachment_image_url($id, 'full', false),
      'name' => $this->name,
      'size' => getimagesize(get_attached_file($id))
    ];
  }
}
