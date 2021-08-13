function WpCustomPostLib() {}

WpCustomPostLib.open_frame = function(callback, multiple=false) {
  const frame = wp.media({
    title: '画像を選択してください',
    multiple: multiple,
    button: { text: '選択' },
  });

  frame.on('select', function() {
    callback(frame.state().get('selection'));
  });

  frame.open();
}

WpCustomPostLib.select_image = function(img_element, value_element) {
  WpCustomPostLib.open_frame(function(selection) {
    var file = selection.models[0];
    img_element.src = file.attributes.url;
    value_element.value = file.attributes.id;
  }, false);
}

WpCustomPostLib.remove_item = function(item) {
  item.parentElement.removeChild(item);
}
