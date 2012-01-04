<?php
/*
-- This Plugin's Information --------------------------------
  Plugin Name: Custom Field Gui Utility
  Plugin URI: http://www.tinybeans.net/blog/download/wp-plugin/cfg-utility-3.html
  Description: WordPress 3.1 のカスタムフィールドを使いやすくするプラグイン「Custom Field GUI」のカスタマイズ版。Original plugin's author is <a href="http://rhymedcode.net">Joshua Sigar</a>.
  Author: Tomohiro Okuwaki
  Author URI: http://www.tinybeans.net/blog/
  Version: 3.1.4
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

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

require_once ('cfg-utility.class.php');
require_once (ABSPATH . 'wp-admin/includes/template.php');

add_action ('admin_head','insert_head');
add_action ('add_meta_boxes', 'isert_custom_field_gui');

/* page and custom post type */
$cur_post_type = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : '';
if (isset($_REQUEST['post'])){
    $cur_post_type=get_post_type($_REQUEST['post']);
}
if ($cur_post_type){
    add_action('add_meta_boxes', 'isert_custom_field_gui');
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

/******************
   Functions(main)
 ******************/

/* 管理画面のhead要素でCSSとJavaScriptファイルの読み込み */
function insert_head () {
    $plugin_url = get_bloginfo('wpurl') . '/wp-content/plugins/custom-field-gui-utility/';
    $head = <<< EOD
    <link rel="stylesheet" href="{$plugin_url}facebox/facebox.css" type="text/css" media="all" />
    <link rel="stylesheet" href="{$plugin_url}cfg-utility.css" type="text/css" media="all" />
    <script type="text/javascript" src="{$plugin_url}facebox/facebox.js"></script>
    <script type="text/javascript" src="{$plugin_url}cfg-utility.js"></script>
EOD;
    echo $head;
}

/* add_meta_boxesで実行する関数 */
function isert_custom_field_gui ($post_type = 'post', $post = NULL) {
    add_meta_box('cfg_utility', get_field_title($post_type), 'insert_gui', $post_type, 'normal', 'high');
}

/* 設定ファイルの取得と変換 */
function get_conf_ini ($post_type) {
    $file_path = dirname(__FILE__) . '/conf-' . $post_type . '.ini';
    if (! file_exists($file_path)) {
        $file_path = dirname(__FILE__) . '/conf.ini';
        if (! file_exists($file_path)) {
            user_error('Custom Field GUI Utility の設定ファイルが見つかりません。設定ファイルを設置するか、同プラグインを無効化してください。', E_USER_ERROR);
            return false;
        }
    }
    $conf = parse_ini_file($file_path, true);
    return $conf;
}

/* カスタムフィールドのボックス名を取得 */
function get_field_title ($post_type) {
    $conf = get_conf_ini($post_type);
    if ($conf and isset($conf['cfgu_setting']['boxname'])) {
        return $conf['cfgu_setting']['boxname'];
    } else {
        return 'カスタムフィールド';
    }
}

/* カスタムフィールドを挿入するメインの関数 */
function insert_gui ($obj) {
    print('<pre> $obj =====<br>');
    var_dump($obj);
    print('</pre>');

    $post_type = 'post';
    $post_id = NULL;
    if (is_object($obj)) {
        $post_type = $obj->post_type;
        $post_id = $obj->ID;
    }

    /* 設定ファイルの取得と変換 */
    $fields = get_conf_ini($post_type);
    if (!$fields) {
        return;
    }

    print('<pre> $fields =====<br>');
    var_dump($fields);
    print('</pre>');

    /* nonceを設定する */
    $out = '<input type="hidden" name="custom-field-gui-verify-key" id="custom-field-gui-verify-key" value="' . wp_create_nonce('custom-field-gui') . '" /><strong style="font-weight:bold;color:red;">Good Job!!</strong>';

$okuwaki = NULL;
if ($okuwaki) {

    foreach ($fields as $title => $data) {
        $cat_check = TRUE;
/*
        if (($post_id != '') and ($post_type == 'post') and isset($data['category']) and $cat_check) {
            $cat_array = explode(' ', $data['category']);
            $cats = get_the_category($post_id);
            foreach ($cats as $cat) {
                $cat_slug = $cat->slug;
                if (in_array($cat_slug, $cat_array)) {
                    $cat_check = FALSE;
                }
            }
            if ($cat_check) {
                continue;
            }
        }
        $class_array = explode(' ',$data['class']);
        if (!in_array($post_type, $class_array)) {
            continue;
        }
*/
/*
            $params = array(
                $post_id,
                $title,
                $data['type'],
                $data['class'],
                $data['default'],
                $data['size'],
                $data['sample'],
                $data['fieldname'],
                $data['must'],
                $data['idname']
            );
*/
        /* パラメーター */
        $data_type      = isset($data['type'])      ? $data['type']:      NULL;
        $data_class     = isset($data['class'])     ? $data['class']:     NULL;
        $data_default   = isset($data['default'])   ? $data['default']:   NULL;
        $data_size      = isset($data['size'])      ? $data['size']:      NULL;
        $data_sample    = isset($data['sample'])    ? $data['sample']:    NULL;
        $data_fieldname = isset($data['fieldname']) ? $data['fieldname']: NULL;
        $data_must      = isset($data['must'])      ? $data['must']:      NULL;
        $data_idname    = isset($data['idname'])    ? $data['idname']:    NULL;

        if ($data['type'] == 'textfield') {
            $out .= cfg_utility_class::make_textform(
                $post_id,
                $title,
                $data_type,
                $data_class,
                $data_default,
                $data_size,
                $data_sample,
                $data_fieldname,
                $data_must,
                ''/* $data_idname */
        );
        } elseif ($data['type'] == 'imagefield' or $data['type'] == 'filefield') {
            $out .= cfg_utility_class::make_textform(
                $post_id,
                $title,
                $data_type,
                $data_class,
                $data_default,
                $data_size,
                $data_sample,
                $data_fieldname,
                $data_must,
                $data_idname
            );
        } elseif ($data['type'] == 'checkbox') {
            $out .= 
                cfg_utility_class::make_checkbox($title, $data['type'], $data['class'], $data['default'], $data['sample'], $data['fieldname'], $data['must']);
        } elseif ($data['type'] == 'multi_checkbox') {
            $out .= 
                cfg_utility_class::make_multi_checkbox($title, $data['type'], $data['class'], explode('#', $data['value']), $data['default'], $data['sample'], $data['fieldname'], $data['must']);
        } elseif ($data['type'] == 'radio') {
            $out .= 
                cfg_utility_class::make_radio(
                    $title, $data['type'], $data['class'], explode('#', $data['value']), $data['default'], $data['sample'], $data['fieldname'], $data['must']);
        } elseif ($data['type'] == 'select') {
            $out .= 
                cfg_utility_class::make_select(
                    $title, $data['type'], $data['class'], explode('#', $data['value']), $data['default'], $data['sample'], $data['fieldname'], $data['must']);
        } elseif ($data['type'] == 'textarea') {
            $out .= 
                cfg_utility_class::make_textarea($title, $data['type'], $data['class'], $data['rows'], $data['cols'], $data['sample'], $data['fieldname'], $data['must']);
        } elseif ($data['type'] == 'hr') {
            $out .= 
                cfg_utility_class::make_hr($data['class'], $data['fieldname']);
        }
    }

}/* if $okuwaki */


    echo $out;
}

/* カスタムフィールドのキーのサニタイズ */
function sanitize_name($meta_key) {
    $meta_key = sanitize_title($meta_key); // taken from WP's wp-includes/functions-formatting.php
    $meta_key = str_replace('-', '_', $meta_key);
    return $meta_key;
}

/* input[type=text]要素を生成する */
function make_input ($meta_key, $value, $size, $default, $input_type) {
    $attr_id = ($meta_key) ? " id='$meta_key'": '';
    $attr_name = ($meta_key) ? " name='$meta_key'": '';
    $attr_value = ($value) ? " value='$value'": '';
    $attr_size = ($size) ? " size='$size'": '';
    $attr_title = ($default) ? " title='$default'": '';
    $attr_type = ($input_type) ? " type='$input_type'": '';
    return '<input '.$attr_id.$attr_name.$attr_value.$attr_title.$attr_type.' placeholder="UNKO!!!"/>';
}

/* カスタムフィールドの入力フォームを生成する */
function make_element ($meta_key, $type, $class, $inside, $sample, $fieldname, $must) {
    $type    = ($type == 'filefield') ? ' imagefield filefield' : ' ' . $type;
    $class   = $class ? ' ' . $class : ' post';
    $must    = $must ? ' must' : '';
    $caption = ($sample and ($type != 'checkbox')) ? '<p class="cfg_sample">' . $sample . '</p>' : '';
    $elm = <<< EOF
        <div class="postbox{$type}{$class}{$must}" id="{$meta_key}">
            <h4 class="cf_title">{$fieldname}</h4>
            <div class="inside">{$inside}{$caption}</div>
        </div>
EOF;
    return $elm;
}

/*************
   Functions(Template)
 *************/
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