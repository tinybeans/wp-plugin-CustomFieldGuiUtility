<?php
/*
Copyright (c)   2005 Joshua Sigar
Licensed under the MIT License:
Customize:   Tomohiro Okuwaki (http://www.tinybeans.net/blog/)
Thanks: @hadakadenkyu <http://twitter.com/hadakadenkyu>
Last update: 2011-04-20
*/

class cfg_utility_class {

    function sanitize_name($name) {
        $name = sanitize_title($name); // taken from WP's wp-includes/functions-formatting.php
        $name = str_replace('-', '_', $name);
        return $name;
    }

    function get_custom_fields($suffix = '') {
        $suffix = $suffix ? '-' . $suffix : '';
        $file = dirname(__FILE__) . '/conf' . $suffix . '.ini';
        if (!file_exists($file)) {
            return null;
        }
        $custom_fields = parse_ini_file($file, true);
        return $custom_fields;
    }

    function make_element ($name, $type, $class, $inside, $sample, $fieldname, $must) {
        $type    = ($type == 'filefield') ? 'imagefield filefield' : $type;
        $class   = isset($class) ? ' ' . $class : ' post';
        $must    = isset($must) ? ' must' : '';
        $caption = (($sample != '') and ($type != 'checkbox')) ? '<p class="cfg_sample">' . $sample . '</p>' : '';
        $elm = <<< EOF
            <div class="postbox {$type}{$class}{$must}" id="{$name}">
                <h4 class="cf_title">{$fieldname}</h4>
                <div class="inside">{$inside}{$caption}</div>
            </div>
EOF;
        return $elm;
    }
    
    function make_textfield ($name, $type, $class, $default, $size = 25, $sample, $fieldname, $must) {
        $title = $name;
        $name = 'cfg_' . cfg_utility_class::sanitize_name($name);
        if (isset($_REQUEST['post'])) {
            $value = get_post_meta($_REQUEST['post'], $title);
            $value = $value[0];
        }
        $value = isset($value) ? attribute_escape($value) : attribute_escape($default);
        $inside = <<< EOF
            <p class="cfg_input">
                <input class="data" type="text" id="{$name}" name="{$name}" value="{$value}" size="{$size}" title="{$default}" />
            </p>
EOF;
        $out = cfg_utility_class::make_element ($name, $type, $class, $inside, $sample, $fieldname, $must);
        return $out;
    }

    function make_imagefield ($name, $type, $class, $size = 25, $sample, $fieldname, $must, $idname) {
        $title = $name;
        $name = 'cfg_' . cfg_utility_class::sanitize_name($name);
        if (isset($_REQUEST['post'])) {
            $value = get_post_meta($_REQUEST['post'], $title);
            $value = attribute_escape($value[0]);
        }
        $inside = <<< EOF
            <p class="cfg_input">
                <input class="data" name="{$name}" value="{$value}" type="text" size="{$size}" />
                <img class="cancel" src="" width="16" height="16" style="display:none;" />
                <span class="thumb" id="{$name}_thumb">
                    <a href="#" class="image" rel="facebox"></a>
                </span>
            </p>
            <p>画像を追加：<img alt="画像を追加" src="images/media-button-other.gif" class="cfg_add_media" style="cursor:pointer;" /></P>
EOF;
        $out = cfg_utility_class::make_element ($name, $type, $class, $inside, $sample, $fieldname, $must);
        return $out;
    }

    function make_filefield ($name, $type, $class, $size = 25, $sample, $fieldname, $must, $idname) {
        $title = $name;
        $name = 'cfg_' . cfg_utility_class::sanitize_name($name);
        if (isset($_REQUEST['post'])) {
            $value = get_post_meta($_REQUEST['post'], $title);
            $value = attribute_escape($value[0]);
        }
        $inside = <<< EOF
            <p class="cfg_input">
                <input class="data" name="{$name}" value="{$value}" type="text" size="{$size}" />
                <img class="cancel" src="" width="16" height="16" style="display:none;" />
            </p>
            <p>ファイルを追加：<img alt="ファイルを追加" src="images/media-button-other.gif" class="cfg_add_media" style="cursor:pointer;" /></P>
EOF;
        $out = cfg_utility_class::make_element ($name, $type, $class, $inside, $sample, $fieldname, $must);
        return $out;
    }

    function make_checkbox ($name, $type, $class, $default, $sample, $fieldname, $must) {
        $title = $name;
        $name = 'cfg_' . cfg_utility_class::sanitize_name($name);
        if (isset($_REQUEST['post'])) {
            $checked = get_post_meta($_REQUEST['post'], $title);
            $checked = $checked ? ' checked="checked" ' : ' ';
        } else {
            if (isset($default) && trim($default) == 'checked') {
                $checked = ' checked="checked" ';
            }       
        }
        $inside = <<< EOF
            <p class="cfg_input">
                <label class="select" for="{$name}">
                    <input class="checkbox data" name="{$name}" value="true" id="{$name}"{$checked}type="checkbox" />
                    {$sample}
                </label>
            </p>
EOF;
        $out = cfg_utility_class::make_element ($name, 'checkboxs', $class, $inside, $sample, $fieldname, $must);
        return $out;
    }

    function make_multi_checkbox ($name, $type, $class, $values, $default, $sample, $fieldname, $must) {
        $title = $name;
        $name = 'cfg_' . cfg_utility_class::sanitize_name($name);
        if (isset($_REQUEST['post'])) {
            $value = get_post_meta($_REQUEST['post'], $title);
            $value = attribute_escape($value[0]);
        }
        if (!isset($value) and isset($default)) {
            $pattern = '/( |　)*#( |　)*/';
            $replacement = ',';
            $value = preg_replace($pattern, $replacement, $default);
        }
        echo($miyu);
        $item_array = array();
        foreach ($values as $val) {
            $id = $name . '_' . cfg_utility_class::sanitize_name($val);
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
                {$item_str}
                <input class="data" id="{$name}_data" name="{$name}" value="{$value}" type="text" />
            </p>
EOF;
        $out = cfg_utility_class::make_element ($name, $type, $class, $inside, $sample, $fieldname, $must);
        return $out;
    }

    function make_radio ($name, $type, $class, $values, $default, $sample, $fieldname, $must) {
        $title = $name;
        $name = 'cfg_' . cfg_utility_class::sanitize_name($name);
        if (isset($_REQUEST['post'])) {
            $selected = get_post_meta($_REQUEST['post'], $title);
            $selected = $selected[0];
        } else {
            $selected = $default;
        }
        $item_array = array();
        foreach($values as $val) {
            $id = $name . '_' . cfg_utility_class::sanitize_name($val);
            $checked = (trim($val) == trim($selected)) ? ' checked="checked" ' : ' ';
            $item = <<< EOF
                <p class="cfg_input">
                    <label for="{$id}">
                        <input class="data" id="{$id}" name="{$name}" value="{$val}"{$checked}type="radio" />
                        {$val}
                    </label>
                </p>
EOF;
            array_push($item_array, $item);
        }
        $inside = implode($item_array);
        $out = cfg_utility_class::make_element ($name, $type, $class, $inside, $sample, $fieldname, $must);
        return $out;
    }
    
    function make_select($name, $type, $class, $values, $default, $sample, $fieldname, $must) {
        $title = $name;
        $name = 'cfg_' . cfg_utility_class::sanitize_name($name);
        if (isset($_REQUEST['post'])) {
            $selected = get_post_meta($_REQUEST['post'], $title);
            $selected = $selected[0];
        } else {
            $selected = $default;
        }
        $item = <<< EOF
            <select name="{$name}">
                <option value="">Select</option>
EOF;
        $item_array = array($item);
        foreach ($values as $val) {
            $checked = (trim($val) == trim($selected)) ? ' selected="selected" ' : ' ';
            $item = <<< EOF
                <option class="data" value="{$val}"{$checked}>{$val}</option>
EOF;
            array_push($item_array, $item);
        }
        array_push($item_array, '</select>');
        $inside = implode($item_array);
        $out = cfg_utility_class::make_element ($name, $type, $class, $inside, $sample, $fieldname, $must);
        return $out;
    }

    function make_textarea($name, $type, $class, $rows, $cols, $sample, $fieldname, $must) {
        $title = $name;
        $name = 'cfg_' . cfg_utility_class::sanitize_name($name);
        if (isset($_REQUEST['post'])) {
            $value = get_post_meta($_REQUEST['post'], $title);
            $value = attribute_escape($value[0]);
        }
        $inside = <<< EOF
            <textarea class="data" id="{$name}" name="{$name}" type="textfield" rows="{$rows}" cols="{$cols}">{$value}</textarea>
EOF;
        $out = cfg_utility_class::make_element ($name, $type, $class, $inside, $sample, $fieldname, $must);
        return $out;
    }

    function make_hr($class, $fieldname) {
        return '<h5 class="postbox_hr ' . $class . '">' . $fieldname . '</h5>';
    }

    function insert_gui() {
        $fields = cfg_utility_class::get_custom_fields();
        if ($fields == null) {
            return;
        }
        $out = '<input type="hidden" name="custom-field-gui-verify-key" id="custom-field-gui-verify-key"
            value="' . wp_create_nonce('custom-field-gui') . '" />';

        foreach ($fields as $title => $data) {
            $cat_check = TRUE;
            $post_type = 'post';
            $post_id = $_REQUEST['post'];
            if (isset($post_id)) {
                $post_type = get_post_type($post_id);
                if ($post_type == 'post' and isset($data['category']) and $cat_check) {
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
            } elseif ($_REQUEST['post_type']){
                $post_type = $_REQUEST['post_type'];
            }
            $class_array = explode(' ',$data['class']);
            if (!in_array($post_type, $class_array)) {
                continue;
            }
            if ($data['type'] == 'textfield') {
                $out .= cfg_utility_class::make_textfield($title, $data['type'], $data['class'], $data['default'], $data['size'], $data['sample'], $data['fieldname'], $data['must']);
            } elseif ($data['type'] == 'imagefield') {
                $out .= cfg_utility_class::make_imagefield($title, $data['type'], $data['class'], $data['size'], $data['sample'], $data['fieldname'], $data['must'], $data['idname']);
            } elseif ($data['type'] == 'filefield') {
                $out .= cfg_utility_class::make_filefield($title, $data['type'], $data['class'], $data['size'], $data['sample'], $data['fieldname'], $data['must'], $data['idname']);
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
        echo $out;
    }

    function edit_meta_value($id) {
        if ($id != 0) {
            global $wpdb;
            if (!isset($id)) {
                $id = $_REQUEST['post_ID'];
            }
            if (!current_user_can('edit_post', $id)) {
                return $id;
            }
            if (!wp_verify_nonce($_REQUEST['custom-field-gui-verify-key'], 'custom-field-gui')) {
                return $id;
            }
            $fields = cfg_utility_class::get_custom_fields();
            if ($fields == null) {
                return;
            }

            foreach($fields as $title => $data) {
                $name = 'cfg_' . cfg_utility_class::sanitize_name($title);
                $title = $wpdb->escape(stripslashes(trim($title)));
                $meta_value = stripslashes(trim($_REQUEST[ "$name" ]));
                if (isset($meta_value) && !empty($meta_value)) {
                    delete_post_meta($id, $title);
                    if ($data['type'] == 'textfield' || 
                            $data['type'] == 'imagefield' || 
                            $data['type'] == 'filefield' || 
                            $data['type'] == 'multi_checkbox' ||
                            $data['type'] == 'radio'  ||
                            $data['type'] == 'select' || 
                            $data['type'] == 'textarea') {
                        add_post_meta($id, $title, $meta_value);
                    } elseif ($data['type'] == 'checkbox') {
                        add_post_meta($id, $title, 'true');
                    }
                } elseif (isset($meta_value) && strval($meta_value) === '0') {
                    add_post_meta($id, $title, '0');
                } else {
                    delete_post_meta($id, $title);
                }
            }
        }
    }
}
?>