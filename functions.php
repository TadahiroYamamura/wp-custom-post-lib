<?php
namespace WpCustomPostLib;

function admin_input($name, $label, $value, $type="text") {
?>
  <label class="admin-input-component <?= $type ?>">
    <input type="<?= $type ?>" class="component" name="<?= $name ?>" placeholder=" " value="<?= $value ?>">
    <span class="label"><?= $label ?></span>
  </label>
<?php
}



function admin_textarea($name, $label, $value) {
?>
  <label class="admin-input-component textarea">
    <textarea class="component" name="<?= $name ?>" placeholder=" "><?= $value ?></textarea>
    <span class="label"><?= $label ?></span>
  </label>
<?php
}
