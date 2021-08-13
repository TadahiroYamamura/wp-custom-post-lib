<?php
namespace WpCustomPostLib;

function admin_input($name, $label, $value, $options=[]) {
  $options = array_merge([
    'type' => 'text',
    'class' => '',
  ], $options);
?>
  <label class="admin-input-component <?= $options['type'] ?> <?= $options['class'] ?>">
    <input type="<?= $type ?>" class="component" name="<?= $name ?>" placeholder=" " value="<?= $value ?>">
    <span class="label"><?= $label ?></span>
  </label>
<?php
}



function admin_textarea($name, $label, $value, $options=[]) {
  $options = array_merge([
    'class' => '',
  ], $options);
?>
  <label class="admin-input-component textarea <?= $options['class'] ?>">
    <textarea class="component" name="<?= $name ?>" placeholder=" "><?= $value ?></textarea>
    <span class="label"><?= $label ?></span>
  </label>
<?php
}

function admin_checkbox($name, $label, $value, $checked, $options=[]) {
  $options = array_merge([
    'class' => '',
  ], $options);
?>
  <label class="admin-input-component checkbox <?= $options['class'] ?>">
    <input type="checkbox" class="component" name="<?= $name ?>" value="<?= $value ?>" <?= checked($checked) ?>>
    <span class="label"><?= $label ?></span>
  </label>
<?php
}

function admin_select($name, $label, $items, $current_value, $options=[]) {
  $options = array_merge([
    'class' => '',
  ], $options);
?>
  <label class="admin-input-component select <?= $options['class'] ?>">
    <select class="component" name="<?= $name ?>">
      <option value="" hidden></option>
      <?php foreach ($items as $value => $text): ?>
        <option value="<?= $value?>" <?= selected($value === $current_value) ?>><?= $text ?></option>
      <?php endforeach; ?>
    </select>
    <span class="label"><?= $label ?></span>
  </label>
<?php
}
