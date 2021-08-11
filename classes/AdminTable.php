<?php
namespace WpCustomPostLib;

class AdminTable {
  private $columns = [];

  public function add_column(string $name, callable $callback, string $label='') {
    if (in_array($name, ['cb', 'title', 'date'], true)) {
      throw new Exception('WordPressデフォルトの列名を指定することはできません');
    }
    if (empty($label)) $label = $name;
    $this->columns[$name] = [
      'name' => $name,
      'label' => $label,
      'callback' => $callback
    ];
  }

  public function manage_posts_columns(array $post_columns) {
    foreach ($this->columns as $col) {
      $post_columns[$col['name']] = $col['label'];
    }
    return $post_columns;
  }

  public function manage_posts_custom_column(string $column_name, int $post_id) {
    if (!isset($this->columns[$column_name])) return;
    echo(call_user_func($this->columns[$column_name]['callback'], $post_id));
  }
}

/**
 * Usage Example:
 *
 * function get_value_from_meta($post_id) {
 *   return get_post_meta($post_id, 'sample', true);
 * }
 * $custompost = new CustomPost($name, $label, $options);
 * $custompost->get_table()->add_column('sample', 'get_value_from_meta', 'サンプル');
 */
