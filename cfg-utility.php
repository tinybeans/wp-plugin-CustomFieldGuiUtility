<?php
/*
-- This Plugin's Information --------------------------------
  Plugin Name: Custom Field Gui Utility
  Plugin URI: http://www.tinybeans.net/blog/download/wp-plugin/cfg-utility-3.html
  Description: WordPress 3.5用。カスタムフィールドを使いやすくするプラグイン「Custom Field GUI」の<a href="http://www.tinybeans.net/blog/">Tomohiro Okuwaki</a>、<a href="http://webcake.no003.info/">Tsuyoshi Kaneko</a>によるカスタマイズ版。オリジナルプラグインの作者は、 <a href="http://rhymedcode.net">Joshua Sigar氏</a>。
  Author: Tomohiro Okuwaki, Tsuyoshi Kaneko
  Version: 3.3.0
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

require_once (ABSPATH . 'wp-admin/includes/template.php');

// 管理画面にエディタ機能を追加
require_once('cfg-extender.php');

add_action ('admin_head','insert_head');
add_action ('add_meta_boxes', 'isert_custom_field_gui');

/* edit_post : 投稿記事またはページが更新・編集された際に実行する。これには、コメントが追加・更新された場合（投稿またはページのコメント数が更新される）も含む。 */
add_action('edit_post', 'edit_meta_value');

/* save_post : インポート機能の利用、記事・ページ編集フォームの利用、XMLRPCでの投稿、メールでの投稿のうちいずれかの方法で記事・ページが作成・更新された際に実行する。 */
add_action('save_post', 'edit_meta_value');

/* publish_post : 投稿記事が公開された際、または公開済みの記事の情報が編集された際に実行する。 */
add_action('publish_post', 'edit_meta_value');

/* transition_post_status : 記事・ページが公開された際、またはステータスが「公開」に変更された場合に実行する。 */
add_action('transition_post_status', 'edit_meta_value');

/******************
   Functions(main)
 ******************/

/* 管理画面のhead要素でCSSとJavaScriptファイルの読み込み */
function insert_head () {
    $current_dir = basename(dirname(__FILE__));
    $plugin_url = get_bloginfo('wpurl') . '/wp-content/plugins/' . $current_dir . '/';
    $head = <<< EOD
    <link rel="stylesheet" href="{$plugin_url}facebox/facebox.css" type="text/css" media="all" />
    <link rel="stylesheet" href="{$plugin_url}cfg-utility.css" type="text/css" media="all" />
    <link rel="stylesheet" href="{$plugin_url}exValidation/css/exvalidation.css" type="text/css" />
    <script type="text/javascript">
    var current_dir = "{$current_dir}";
    </script>
    <script type="text/javascript" src="{$plugin_url}facebox/facebox.js"></script>
    <script type="text/javascript" src="{$plugin_url}exValidation/js/exvalidation.js"></script>
    <script type="text/javascript" src="{$plugin_url}exValidation/js/exchecker-ja.js"></script>
    <script type="text/javascript" src="{$plugin_url}cfg-utility.js"></script>
    <script type="text/javascript">
    jQuery(function($){
        $("form#post").exValidation();
    });
    </script>
EOD;
    echo $head;
}

/* add_meta_boxesで実行する関数 */
function isert_custom_field_gui ($post_type = 'post', $post = NULL) {
    add_meta_box('cfg_utility', get_field_title($post_type), 'insert_gui', $post_type, 'normal', 'high');
}

/* 設定ファイルの取得と変換 */
function get_conf_ini ($post_type) {
    $sufix_arry = array('');
    global $post;
    if (isset($post_type)) {
        array_unshift($sufix_arry, '-' . $post_type);
    }
    if ($post->ID) {
        array_unshift($sufix_arry, '-' . $post->ID);
    }
    $success_conf_ini = false;
    foreach ($sufix_arry as $suffix) {
        $file_path = dirname(__FILE__) . '/conf' . $suffix . '.ini';
        if (file_exists($file_path)) {
            $success_conf_ini = true;
            break;
        }
    }
    if (!$success_conf_ini) {
        user_error('Custom Field GUI Utility の設定ファイルが見つかりません。設定ファイルを設置するか、同プラグインを無効化してください。', E_USER_ERROR);
        return false;
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

    $post_type = 'post';
    $post_id = 0;
    if (is_object($obj)) {
        $post_type = $obj->post_type;
        $post_id = $obj->ID;
    }

    /* カテゴリを取得する */
    $cat_slugs = array();
    if ($post_type == 'post') {
        $post_cats = get_the_category($post_id);
        foreach ($post_cats as $post_cat) {
            if (isset($post_cat->slug)) {
                array_push($cat_slugs, urldecode($post_cat->slug));
            }
        }
    }

    /* 設定ファイルの取得と変換 */
    $fields = get_conf_ini($post_type);
    if (!$fields) {
        return;
    }

    /* nonceを設定する */
    $out = '<input type="hidden" name="custom-field-gui-verify-key" id="custom-field-gui-verify-key" value="' . wp_create_nonce('custom-field-gui') . '" />';

    foreach ($fields as $meta_key => $data) {

        /* パラメーター */
        $param = array(
            'post_id' => $post_id,
            'meta_key' => $meta_key,
            'type' => isset($data['type']) ? $data['type']: 'post',
            'class' => isset($data['class']) ? $data['class']: '',
            'default' => isset($data['default']) ? $data['default']: '',
            'size' => isset($data['size']) ? $data['size']: 25,
            'sample' => isset($data['sample']) ? $data['sample']: '',
            'fieldname' => isset($data['fieldname']) ? $data['fieldname']: '',
            'must' => isset($data['must']) ? $data['must']: '',
            'rows' => isset($data['rows']) ? $data['rows']: '',
            'cols' => isset($data['cols']) ? $data['cols']: '',
            'values' => isset($data['value']) ? explode('#', $data['value']): '',
            'category' => isset($data['category']) ? explode(' ', $data['category']): '',
            'placeholder' => isset($data['placeholder']) ? $data['placeholder']: '',
            'validation' => isset($data['validation']) ? $data['validation']: ''
        );

        /* 投稿タイプをチェックする */
        if (!empty($param['class'])) {

            $conf_class = preg_replace('/ +/', ' ', trim($param['class']));
            $conf_classes = explode(' ', $conf_class);
            if (!in_array($post_type, $conf_classes)) {
                continue;
            }
        }

        /* カテゴリをチェックする */
        if (!empty($param['category']) and !empty($cat_slugs)) {
            $skip_insert_gui = true;
            $conf_cats = $param['category'];
            foreach ($conf_cats as $conf_cat) {
                if (in_array(trim($conf_cat), $cat_slugs)) {
                    $skip_insert_gui = false;
                    break;
                }
            }
            if ($skip_insert_gui) {
                continue;
            }
        }

        $data_type = $param['type'];

        if ($data_type == 'textfield' or $data_type == 'imagefield' or $data_type == 'filefield') {
            $out .= make_textform($param);
        } elseif ($data_type == 'checkbox') {
            $out .= make_checkbox($param);
        } elseif ($data_type == 'multi_checkbox') {
            $out .= make_multi_checkbox($param);
        } elseif ($data_type == 'radio') {
            $out .= make_radio($param);
        } elseif ($data_type == 'select') {
            $out .= make_select($param);
        } elseif ($data_type == 'textarea') {
            $out .= make_textarea($param);
        } elseif ($data_type == 'hr') {
            $out .= make_hr($param);
        }
    }
    echo $out;
}

/* カスタムフィールドのキーのサニタイズ */
function sanitize_name($meta_key) {
    $name = sanitize_title($meta_key); // taken from WP's wp-includes/functions-formatting.php
    $name = str_replace('-', '_', $name);
    return $name;
}

/* input[type=text]要素を生成する */
function make_input ($name, $value, $size, $default, $input_type, $placeholder, $validation) {
    $attr_id_name = ($name) ? " id='$name' name='$name'": '';
    $attr_value = ($value || $value == 0) ? " value='$value'": '';
    $attr_size = ($size) ? " size='$size'": '';
    $attr_title = ($default || $default == 0) ? " title='$default'": '';
    $attr_type = ($input_type) ? " type='$input_type'": '';
    $attr_plce = ($placeholder || $placeholder == 0) ? " placeholder='$placeholder'": '';
    $validation_class = ($validation) ? ' ' . $validation: '';
    return '<input class="data'.$validation_class.'" '.$attr_id_name.$attr_value.$attr_title.$attr_type.$attr_size.$attr_plce.' />';
}

/* カスタムフィールドの入力フォームを生成する */
function make_element ($name, $type, $class, $inside, $sample, $fieldname, $must) {
    $id = $name ? $name . '_box': '';
    $type = ($type == 'filefield') ? ' imagefield filefield' : ' ' . $type;
    $class = $class ? ' ' . $class : ' post';
    $must = $must ? ' must' : '';
    $caption = $sample ? '<p class="cfg_sample">' . $sample . '</p>' : '';
    $elm = <<< EOF
        <div class="postbox{$type}{$class}{$must}" id="{$id}">
            <h4 class="cf_title">{$fieldname}</h4>
            <div class="inside">{$inside}{$caption}</div>
        </div>
EOF;
    return $elm;
}

/* input[type=text]系のカスタムフィールドのボックスの中身を生成する */
function make_textform ($param) {

    $post_id     = $param['post_id'];
    $meta_key    = $param['meta_key'];
    $type        = $param['type'];
    $class       = $param['class'];
    $default     = $param['default'];
    $size        = $param['size'];
    $sample      = $param['sample'];
    $fieldname   = $param['fieldname'];
    $must        = $param['must'];
    $placeholder = $param['placeholder'];
    $validation  = $param['validation'];

    $name = 'cfg_' . sanitize_name($meta_key);
    $meta_value = get_post_meta($post_id, $meta_key, true);
    if (!empty($meta_value) or strval($meta_value) === '0') {
        $value = esc_attr($meta_value);
    } elseif (!empty($default) || $default == 0) {
        $value = esc_attr($default);
    } else {
        $value = '';
    }
    $input = make_input ($name, $value, $size, $default, 'text', $placeholder, $validation);

    $media_buttons = '<input type="submit" class="button cfg-add-image" value="メディアを追加" />';

    if ($type == 'textfield') {
        $inside = <<< EOF
        <p class='cfg_input'>$input</p>
EOF;
        $out = make_element ($name, $type, $class, $inside, $sample, $fieldname, $must);
    } elseif ($type == 'imagefield') {
        $inside = <<< EOF
        <p class="cfg_input">
            $input
            <img class="cancel" src="" width="16" height="16" style="display:none;" />
            <span class="thumb" id="{$name}_thumb">
                <a href="#" class="image" rel="facebox"></a>
            </span>
        </p>
        <p class="cfg_add_media_pointer">{$media_buttons}</P>
EOF;
        $out = make_element ($name, $type, $class, $inside, $sample, $fieldname, $must);
    } elseif ($type == 'filefield') {
        $inside = <<< EOF
        <p class="cfg_input">
            $input
            <img class="cancel" src="" width="16" height="16" style="display:none;" />
        </p>
        <p class="cfg_add_media_pointer">{$media_buttons}</P>
EOF;
        $out = make_element ($name, $type, $class, $inside, $sample, $fieldname, $must);
    }
    return $out;
}

/* チェックボックスのカスタムフィールドのボックスの中身を生成する */
function make_checkbox ($param) {

    $post_id    = $param['post_id'];
    $meta_key   = $param['meta_key'];
    $type       = $param['type'];
    $class      = $param['class'];
    $default    = $param['default'];
    $sample     = $param['sample'];
    $fieldname  = $param['fieldname'];
    $must       = $param['must'];
    $validation = $param['validation'];

    $name = 'cfg_' . sanitize_name($meta_key);
    $meta_value = get_post_meta($post_id, $meta_key, true);
    if (!empty($meta_value)) {
        $checked = ' checked="checked"';
    } elseif (!empty($default) and trim($default) == 'checked') {
        $checked = ' checked="checked"';
    } else {
        $checked = '';
    }
    $inside = <<< EOF
        <p class="cfg_input">
            <label id="{$name}_label" class="select {$validation}" for="{$name}">
                <input class="checkbox data" name="{$name}" value="true" id="{$name}"{$checked} type="checkbox" />
                {$sample}
            </label>
        </p>
EOF;
    $out = make_element ($name, 'checkboxs', $class, $inside, '', $fieldname, $must);
    return $out;
}

/* マルチチェックボックスのカスタムフィールドのボックスの中身を生成する */
function make_multi_checkbox ($param) {

    $post_id    = $param['post_id'];
    $meta_key   = $param['meta_key'];
    $type       = $param['type'];
    $class      = $param['class'];
    $default    = $param['default'];
    $sample     = $param['sample'];
    $fieldname  = $param['fieldname'];
    $must       = $param['must'];
    $value      = '';
    $values     = $param['values'];
    $validation = $param['validation'];

    $name = 'cfg_' . sanitize_name($meta_key);
    $meta_value = get_post_meta($post_id, $meta_key, true);
    if (!empty($default)) {
        $value = preg_replace('/( |　)*#( |　)*/', ',', $default);
    }
    if (!empty($meta_value)) {
        $value = esc_attr($meta_value);
    }
    $item_array = array();
    foreach ($values as $val) {
        $id = $name . '_' . sanitize_name($val);
        $item = <<< EOF
            <label for="{$id}" class="items" title="{$val}">
                <input id="{$id}" name="{$id}" value="{$val}" type="checkbox" />
                {$val}
            </label>
EOF;
        array_push($item_array, $item);
    }
    $item_str = implode($item_array);
    $inside = <<< EOF
        <p class="cfg_input">
            <span id="{$name}_wrap" class="multi_checkbox_wrapper {$validation}">{$item_str}</span>
            <input class="data" id="{$name}_data" name="{$name}" value="{$value}" type="text" />
        </p>
EOF;
    $out = make_element ($name, $type, $class, $inside, $sample, $fieldname, $must);
    return $out;
}

/* ラジオボタンのカスタムフィールドのボックスの中身を生成する */
function make_radio ($param) {

    $post_id    = $param['post_id'];
    $meta_key   = $param['meta_key'];
    $type       = $param['type'];
    $class      = $param['class'];
    $default    = $param['default'];
    $sample     = $param['sample'];
    $fieldname  = $param['fieldname'];
    $must       = $param['must'];
    $values     = $param['values'];
    $validation = $param['validation'];

    $name = 'cfg_' . sanitize_name($meta_key);
    $meta_value = get_post_meta($post_id, $meta_key, true);
    if (!empty($meta_value)) {
        $selected = trim($meta_value);
    } elseif (!empty($default)) {
        $selected = trim($default);
    } else {
        $selected = '';
    }
    $item_array = array();
    foreach($values as $val) {
        $id = $name . '_' . uniqid();
        $checked = (trim($val) == $selected) ? ' checked="checked"' : ' ';
        $item = <<< EOF
            <p class="cfg_input">
                <label id="{$id}_label" for="{$id}">
                    <input class="data" id="{$id}" name="{$name}" value="{$val}"{$checked} type="radio" />
                    {$val}
                </label>
            </p>
EOF;
        array_push($item_array, $item);
    }
    $inside = implode($item_array);
    $inside = '<div id="' . $id . '_radio_wrapper" class="radio_wrapper ' . $validation . '" style="display: inline;">' . $inside . '</div>';
    $out = make_element ($name, $type, $class, $inside, $sample, $fieldname, $must);
    return $out;
}

/* セレクトボックスのカスタムフィールドのボックスの中身を生成する */
function make_select($param) {

    $post_id    = $param['post_id'];
    $meta_key   = $param['meta_key'];
    $type       = $param['type'];
    $class      = $param['class'];
    $default    = $param['default'];
    $sample     = $param['sample'];
    $fieldname  = $param['fieldname'];
    $must       = $param['must'];
    $values     = $param['values'];
    $validation = $param['validation'];

    $name = 'cfg_' . sanitize_name($meta_key);
    $meta_value = get_post_meta($post_id, $meta_key, true);

    if (!empty($meta_value)) {
        $selected = trim($meta_value);
    } else {
        $selected = trim($default);
    }
    $item = <<< EOF
        <select id="{$name}_select" name="{$name}" class="{$validation}">
            <option value="">Select</option>
EOF;
    $item_array = array($item);
    foreach ($values as $val) {
        $checked = (trim($val) == $selected) ? ' selected="selected"' : '';
        $item = <<< EOF
            <option class="data"{$checked} value="{$val}">{$val}</option>
EOF;
        array_push($item_array, $item);
    }
    array_push($item_array, '</select>');
    $inside = implode($item_array);
    $out = make_element ($name, $type, $class, $inside, $sample, $fieldname, $must);
    return $out;
}

/* テキストエリアのカスタムフィールドのボックスの中身を生成する */
function make_textarea($param) {

    $post_id    = $param['post_id'];
    $meta_key   = $param['meta_key'];
    $type       = $param['type'];
    $class      = $param['class'];
    $default    = $param['default'];
    $sample     = $param['sample'];
    $fieldname  = $param['fieldname'];
    $must       = $param['must'];
    $rows       = $param['rows'];
    $cols       = $param['cols'];
    $validation = $param['validation'];

    $name = 'cfg_' . sanitize_name($meta_key);
    $meta_value = get_post_meta($post_id, $meta_key, true);

    if (!empty($meta_value) or strval($meta_value) === '0') {
        $value = esc_attr($meta_value);
    } else {
        $value = esc_attr($default);
    }
    $inside = <<< EOF
        <textarea class="data {$validation}" id="{$name}" name="{$name}" type="textfield" rows="{$rows}" cols="{$cols}">{$value}</textarea>
EOF;
    $out = make_element ($name, $type, $class, $inside, $sample, $fieldname, $must);
    return $out;
}

/* 区切り線のボックスの中身を生成する */
function make_hr($param) {

    $class     = $param['class'];
    $fieldname = $param['fieldname'];

    return '<h5 class="postbox_hr ' . $class . '">' . $fieldname . '</h5>';
}

function edit_meta_value($post_id) {
    if ($post_id == 0) {
        return $post_id;
    }
    global $wpdb;
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }
    $nonce = isset($_REQUEST['custom-field-gui-verify-key']) ? $_REQUEST['custom-field-gui-verify-key']: '';
    if (!wp_verify_nonce($nonce, 'custom-field-gui')) {
        return $post_id;
    }
    global $post;
    $fields = get_conf_ini($post->post_type);
    if (!$fields) {
        return $post_id;
    }
    foreach($fields as $meta_key => $data) {
        $name = 'cfg_' . sanitize_name($meta_key);
        $data_type = isset($data['type']) ? $data['type']: '';
        if ($data_type == 'hr' or $meta_key == 'cfgu_setting') {
            continue;
        }
        $meta_value = isset($_REQUEST["$name"]) ? stripslashes(trim($_REQUEST["$name"])): '';
        if (isset($meta_value) && !empty($meta_value)) {
            delete_post_meta($post_id, $meta_key);
            if ($data_type == 'textfield' ||
                $data_type == 'imagefield' ||
                $data_type == 'filefield' ||
                $data_type == 'multi_checkbox' ||
                $data_type == 'radio'  ||
                $data_type == 'select' ||
                $data_type == 'textarea') {
                add_post_meta($post_id, $meta_key, $meta_value);
            } elseif ($data['type'] == 'checkbox') {
                add_post_meta($post_id, $meta_key, 'true');
            }
        } elseif (isset($meta_value) && strval($meta_value) === '0') {
            add_post_meta($post_id, $meta_key, '0');
        } else {
            delete_post_meta($post_id, $meta_key);
        }
    }
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
    $out['title'] = $attachment->post_title;
    $out['url'] = $attachment->guid;
    $out['content'] = $attachment->post_content;
    $out['excerpt'] = $attachment->post_excerpt;
    $out['parent'] = $attachment->post_parent;
    $out['mime_type'] = $attachment->post_mime_type;
    return $out;
}

?>