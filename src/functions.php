<?php
namespace WpCustomPostLib;

function admin_input($name, $label, $value, $options=[]) {
  $options = array_merge([
    'type' => 'text',
    'class' => '',
    'onchange' => null,
    'oninput' => null,
  ], $options);
?>
  <label class="admin-input-component <?= $options['type'] ?> <?= $options['class'] ?>">
    <input
      type="<?= $options['type'] ?>"
      class="component"
      name="<?= $name ?>"
      placeholder=" "
      value="<?= $value ?>"
      <?= $options['onchange'] ? "onchange=\"${options['onchange']}\"" : '' ?>
      <?= $options['oninput'] ? "oninput=\"${options['oninput']}\"" : '' ?>
    >
    <span class="label"><?= $label ?></span>
  </label>
<?php
}



function admin_textarea($name, $label, $value, $options=[]) {
  $options = array_merge([
    'class' => '',
    'onchange' => null,
    'oninput' => null,
  ], $options);
?>
  <label class="admin-input-component textarea <?= $options['class'] ?>">
    <textarea
      class="component"
      name="<?= $name ?>"
      placeholder=" "
      <?= $options['onchange'] ? "onchange=\"${options['onchange']}\"" : '' ?>
      <?= $options['oninput'] ? "oninput=\"${options['oninput']}\"" : '' ?>
    ><?= $value ?></textarea>
    <span class="label"><?= $label ?></span>
  </label>
<?php
}

function admin_checkbox($name, $label, $value, $checked, $options=[]) {
  $options = array_merge([
    'class' => '',
    'onchange' => null,
  ], $options);
?>
  <label class="admin-input-component checkbox <?= $options['class'] ?>">
    <input
      type="checkbox"
      class="component"
      name="<?= $name ?>"
      value="<?= $value ?>"
      <?= checked($checked) ?>
      <?= $options['onchange'] ? "onchange=\"${options['onchange']}\"" : '' ?>
    >
    <span class="label"><?= $label ?></span>
  </label>
<?php
}

function admin_select($name, $label, $items, $current_value, $options=[]) {
  $options = array_merge([
    'class' => '',
    'onchange' => null,
  ], $options);

  if (count($items) > 0) {
    if (isset($items[0]) && $items[0] instanceof \WP_Term) {
      $arr = [];
      foreach ($items as $item) $arr[$item->term_id] = $item->name;
      $items = $arr;
    }

    if ($current_value instanceof \WP_Term) {
      $current_value = $current_value->term_id;
    }
  }
?>
  <label class="admin-input-component select <?= $options['class'] ?>">
    <select
      class="component"
      name="<?= $name ?>"
      <?= $options['onchange'] ? "onchange=\"${options['onchange']}\"" : '' ?>
    >
      <option value="" hidden></option>
      <?php foreach ($items as $value => $text): ?>
        <option value="<?= $value?>" <?= selected($value === $current_value) ?>><?= $text ?></option>
      <?php endforeach; ?>
    </select>
    <span class="label"><?= $label ?></span>
  </label>
<?php
}

function admin_image($name, $label, $id, $options=[]) {
  $options = array_merge([
    'class' => '',
    'default' => '',
    'size' => [600, 600],
    'onclick' => 'WpCustomPostLib.select_image(this, this.nextElementSibling); return false;',
  ], $options);
  if (empty($options['default'])) {
    $size = $options['size'];
    $options['default'] = "https://placehold.jp/${size[0]}x${size[1]}.png";
  }
?>
  <div class="admin-input-component image <?= $options['class'] ?>">
    <img class="component" src="<?= empty($id) ? $options['default'] : wp_get_attachment_image_url($id, 'full', false) ?>" onclick="<?= $options['onclick'] ?>">
    <input type="hidden" name="<?= $name ?>" value="<?= $id ?>">
    <?php if (!empty($label)): ?>
      <span class="label"><?= $label ?></span>
    <?php endif; ?>
  </div>
<?php
};
