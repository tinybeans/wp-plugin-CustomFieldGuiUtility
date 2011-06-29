<?php
/*
-- This Plugin's Information --------------------------------
  Plugin Name: Custom Field Gui Utility
  Plugin URI: http://www.tinybeans.net/blog/download/wp-plugin/cfg-utility-3.html
  Description: WordPress 3.1 のカスタムフィールドを使いやすくするプラグイン「Custom Field GUI」のカスタマイズ版。Original plugin's author is <a href="http://rhymedcode.net">Joshua Sigar</a>.
  Author: Customized by Tomohiro Okuwaki
  Author URI: http://www.tinybeans.net/blog/
  Version: 3.1.2
  Customize: Tomohiro Okuwaki (http://www.tinybeans.net/blog/)
  Thanks: @hadakadenkyu <http://twitter.com/hadakadenkyu>
-- This Plugin's Information --------------------------------

-- Original Plugin's Information --------------------------------
  Original Plugin's Name: rc:custom_field_gui
  Original Plugin's URI: http://rhymedcode.net/projects/custom-field-gui
  Original Plugin's Description: Automatically adds form element(s) in Write Post panel, which act as a Post's custom field(s). Configuration is thru conf.ini. Instruction is on readme.txt.
  Original Plugin's Author: Joshua Sigar
  Original Plugin's Version: 1.5
  Original Plugin's Author URI: http://rhymedcode.net
-- /Original Plugin's Information --------------------------------
*/ 

/*
rc:custom_field_gui
Licensed under the MIT License
Copyright (c)  2005 Joshua Sigar

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the
Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software,
and to permit persons to whom the Software is furnished to
do so, subject to the following conditions:

The above copyright notice and this permission notice shall
be included in all copies or substantial portions of the
Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY
KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

// カスタムフィールドを入れるボックス名の指定 [start]
$box_name = 'カスタムフィールド';
// カスタムフィールドを入れるボックス名の指定 [ end ]

function insert_head () {
    $plugin_url = get_bloginfo('wpurl') . '/wp-content/plugins/custom-field-gui-utility/';
    $head = <<< EOF
        <link rel="stylesheet" href="{$plugin_url}facebox/facebox.css" type="text/css" media="all" />
        <link rel="stylesheet" href="{$plugin_url}cfg-utility.css" type="text/css" media="all" />
        <script type="text/javascript" src="{$plugin_url}facebox/facebox.js"></script>
        <script type="text/javascript" src="{$plugin_url}cookie.js"></script>
        <script type="text/javascript" src="{$plugin_url}cfg-utility.js"></script>
EOF;
    echo $head;
}
add_action ('admin_head','insert_head');

include_once ('cfg-utility.class.php');
require_once (ABSPATH . 'wp-admin/includes/template.php');

/* post and page */
add_action ('simple_edit_form', array ('cfg_utility_class', 'insert_gui'));
  /* simple_edit_form:詳細設定のセクションが含まれない「シンプルモード」記事投稿フォームの終盤で実行する。デフォルトではシンプルモードフォームが使われるのはブックマークレットからの投稿のみ。*/

/* post */
add_meta_box('cfg_utility', $box_name, array('cfg_utility_class', 'insert_gui'), 'post', 'normal', 'high');

/* page and custom post type */
$cur_post_type=$_REQUEST['post_type'];
if($_REQUEST['post']){
    $cur_post_type=get_post_type($_REQUEST['post']);
}
if ($cur_post_type){
    add_meta_box('cfg_utility', $box_name, array('cfg_utility_class', 'insert_gui'), $cur_post_type, 'normal', 'high');
}

/* post and page */
add_action( 'edit_post', array( 'cfg_utility_class', 'edit_meta_value' ) );
  /* edit_post:投稿記事またはページが更新・編集された際に実行する。これには、コメントが追加・更新された場合（投稿またはページのコメント数が更新される）も含む。*/

/* post and page */
add_action( 'save_post', array( 'cfg_utility_class', 'edit_meta_value' ) );
  /* save_post:インポート機能の利用、記事・ページ編集フォームの利用、XMLRPCでの投稿、メールでの投稿のうちいずれかの方法で記事・ページが作成・更新された際に実行する。*/

/* post */
add_action( 'publish_post', array( 'cfg_utility_class', 'edit_meta_value' ) );
  /* publish_post:投稿記事が公開された際、または公開済みの記事の情報が編集された際に実行する。*/

/* page */
add_action( 'transition_post_status', array( 'cfg_utility_class', 'edit_meta_value' ) );
  /* transition_post_status:バージョン2.3以上。記事・ページが公開された際、またはステータスが「公開」に変更された場合に実行する。*/

/* Functions */
function get_imagefield($key) {
    $imagefield = post_custom($key);
    $out['id'] = preg_replace('/(\[)([0-9]+)(\])(http.+)/', '$2', $imagefield);
    $out['url'] = preg_replace('/(\[)([0-9]+)(\])(http.+)/', '$4', $imagefield);
    return $out;
}

function get_attachment_object($post_id) {
    global $wpdb;
    $attachment = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID = $post_id");
    $post = $attachment;
    setup_postdata($post);
    $out['title'] = $post->post_title;
    $out['url'] = $post->guid;
    $out['content'] = $post->post_content;
    $out['excerpt'] = $post->post_excerpt;
    $out['parent'] = $post->post_parent;
    $out['mime_type'] = $post->post_mime_type;
    return $out;
}

?>