<?php
namespace WpCustomPostLib;
/*
 * このクラス自体は特に何も処理していません。
 * このクラスが必要かどうかちょっと迷ったが、「本文を保存する前後で何か
 * 操作をしたい時など、拡張するクラスがあった方が分かりやすいよなぁ」と
 * 思ったので作っておきます。
 */

require_once(__DIR__.'/AbstractData.php');

class PostContentData extends \WpCustomPostLib\AbstractData {
  public function __construct() {
    parent::__construct('content');
  }

  protected function do_save(int $post_id, \WP_Post $post, bool $update) {
    // $_POST['content']に設定した値をWordPressが勝手に処理してくれるので
    // 特に何かする必要はない。
  }

  protected function do_extract(\WP_Post $post) {
    // WordPresss組込のget_the_content関数はthe_contentフィルターを
    // 通してくれない。従って返ってくる値はショートコードが展開されていなかったりする。
    // このあたりの設計意図は謎だが、the_content関数はthe_contentフィルターを
    // 通した値を出力するので、驚き最小の原則に反する実装だと思う。
    //
    // see: https://developer.wordpress.org/reference/functions/get_the_content/
    // see: https://developer.wordpress.org/reference/functions/the_content/
    //
    // このクラスの処理では、the_contentで出力される値と同様のものを返却します。

    return str_replace(']]>', ']]&gt;', apply_filters('the_content', get_the_content(null, false, $post)));
  }
}
