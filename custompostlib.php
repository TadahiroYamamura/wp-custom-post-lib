<?php
/*
 * Plugin Name: WP Custom Post Library
 * Plugin URI:  https://github.com/TadahiroYamamura/wp-custom-post-lib
 * Description: Class library for creating your custom post.
 * Version:     0.1.0
 * Author:      Tadahiro Yamamura
 * Author URI:  https://github.com/TadahiroYamamura
 * License:     MIT
 */

require_once(__DIR__.'/classes/AbstractData.php');
require_once(__DIR__.'/classes/AdminTable.php');
require_once(__DIR__.'/classes/CustomPost.php');
require_once(__DIR__.'/classes/CustomTaxonomy.php');
require_once(__DIR__.'/classes/PostContentData.php');
require_once(__DIR__.'/classes/PostImageData.php');
require_once(__DIR__.'/classes/PostMetaData.php');
require_once(__DIR__.'/classes/PostTermData.php');
require_once(__DIR__.'/classes/PostThumbnailData.php');
require_once(__DIR__.'/classes/Renderer.php');
