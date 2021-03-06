<?php
namespace WpCustomPostLib;

require_once(__DIR__.'/AbstractData.php');
require_once(__DIR__.'/CustomTaxonomy.php');

class PostTermData extends \WpCustomPostLib\AbstractData {
  private $multi = true;

  public function multiple(bool $flg=true): self {
    $this->multi = $flg;
    return $this;
  }

  public function do_save(int $post_id, \WP_Post $post, bool $update) {
    if ($this->multi) {
      if (!isset($_POST[$this->name]) || count($_POST[$this->name]) === 0) {
        $term_id = '';
      } else {
        $term_id = array_map('intval', $_POST[$this->name]);
      }
      wp_set_object_terms($post_id, $term_id, $this->name, false);
    } else {
      if (!isset($_POST[$this->name])) return;
      $term_id = intval($_POST[$this->name]);
      wp_set_object_terms($post_id, $term_id, $this->name, false);
    }
  }

  public function do_extract(\WP_Post $post) {
    $terms = get_the_terms($post, $this->name);
    if (is_wp_error($terms)) {
      var_dump($terms);
      throw $terms;
    } elseif ($terms === false) {
      // ドキュメントによると: false if there are no terms or the post does not exist
      return $this->multi ? [] : null;
    } else {
      return $this->multi ? $terms : $terms[0];
    }
  }
}
