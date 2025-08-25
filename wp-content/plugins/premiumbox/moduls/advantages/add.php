<?php
if (!defined('ABSPATH')) exit();


if (!function_exists('def_adminpage_title_all_add_advantages') and is_admin()) {
    add_filter('pn_adminpage_title_all_add_advantages', 'def_adminpage_title_all_add_advantages');
    function def_adminpage_title_all_add_advantages() {
        global $db_data, $wpdb;

        $data_id = 0;
        $item_id = intval(is_param_get('item_id'));
        $db_data = '';

        if ($item_id) {
            $db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "advantages WHERE id = '$item_id'");
            if (isset($db_data->id)) {
                $data_id = $db_data->id;
            }
        }

        if ($data_id) {
            return __('Edit advantages', 'pn');
        } else {
            return __('Add advantages', 'pn');
        }
    }
}

if (!function_exists('def_pn_adminpage_content_all_add_advantages') and is_admin()) {
    add_action('pn_adminpage_content_all_add_advantages', 'def_pn_adminpage_content_all_add_advantages');
    function def_pn_adminpage_content_all_add_advantages() {
        global $db_data, $wpdb;

        $form = new PremiumForm();

        $data_id = intval(is_isset($db_data, 'id'));
        if ($data_id) {
            $title = __('Edit advantages', 'pn');
        } else {
            $title = __('Add advantages', 'pn');
        }

        $back_menu = array();
        $back_menu['back'] = array(
            'link' => admin_url('admin.php?page=all_advantages'),
            'title' => __('Back to list', 'pn')
        );
        $back_menu['save'] = array(
            'link' => '#',
            'title' => __('Save', 'pn'),
            'atts' => array('class' => "savelink save_admin_ajax_form"),
        );
        if ($data_id) {
            $back_menu['add'] = array(
                'link' => admin_url('admin.php?page=all_add_advantages'),
                'title' => __('Add new', 'pn')
            );
        }
        $form->back_menu($back_menu, $db_data);

        $options = array();
        $options['hidden_block'] = array(
            'view' => 'hidden_input',
            'name' => 'data_id',
            'default' => $data_id,
        );
        $options['top_title'] = array(
            'view' => 'h3',
            'title' => $title,
            'submit' => __('Save', 'pn'),
        );
        $options['title'] = array(
            'view' => 'inputbig',
            'title' => __('Title', 'pn'),
            'default' => is_isset($db_data, 'title'),
            'name' => 'title',
            'work' => 'input',
            'ml' => 1,
        );
        $options['content'] = array(
            'view' => 'textarea',
            'title' => __('Text', 'pn'),
            'default' => is_isset($db_data, 'content'),
            'name' => 'content',
            'rows' => '10',
            'formatting_tags' => true,
            'other_tags' => true,
            'ml' => true,
        );
        $options['link'] = array(
            'view' => 'inputbig',
            'title' => __('Link', 'pn'),
            'default' => is_isset($db_data, 'link'),
            'name' => 'link',
            'work' => 'input',
            'ml' => 1,
        );
        $options['img'] = array(
            'view' => 'uploader',
            'title' => __('Image', 'pn'),
            'default' => is_isset($db_data, 'img'),
            'name' => 'img',
            'work' => 'input',
        );
        $options['status'] = array(
            'view' => 'select',
            'title' => __('Status', 'pn'),
            'options' => array('1' => __('published', 'pn'), '0' => __('moderating', 'pn')),
            'default' => is_isset($db_data, 'status'),
            'name' => 'status',
            'work' => 'int',
        );

        $params_form = array(
            'filter' => 'all_advantages_addform',
            'data' => $db_data,
            'button_title' => __('Save', 'pn'),
        );
        $form->init_form($params_form, $options);

    }
}

if (!function_exists('def_premium_action_all_add_advantages')) {
    add_action('premium_action_all_add_advantages', 'def_premium_action_all_add_advantages');
    function def_premium_action_all_add_advantages() {
        global $wpdb;

        _method('post');

        $form = new PremiumForm();
        $form->send_header();

        pn_only_caps(array('administrator', 'pn_advantages'));

        $data_id = intval(is_param_post('data_id'));

        $last_data = '';
        if ($data_id > 0) {
            $last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "advantages WHERE id = '$data_id'");
            if (!isset($last_data->id)) {
                $data_id = 0;
            }
        }

        $array = array();
        $array['title'] = pn_strip_input(is_param_post_ml('title'));
        $array['content'] = pn_strip_text(is_param_post_ml('content'));
        $array['link'] = pn_strip_input(is_param_post_ml('link'));
        $array['img'] = pn_strip_input(is_param_post('img'));
        $array['status'] = intval(is_param_post('status'));

        $ui = wp_get_current_user();
        $user_id = intval(is_isset($ui, 'ID'));

        $array['edit_date'] = current_time('mysql');
        $array['edit_user_id'] = $user_id;
        $array['auto_status'] = 1;
        $array = apply_filters('all_advantages_addform_post', $array, $last_data);

        if ($data_id) {
            $res = apply_filters('item_advantages_edit_before', pn_ind(), $data_id, $array, $last_data);
            if ($res['ind']) {
                $result = $wpdb->update($wpdb->prefix . 'advantages', $array, array('id' => $data_id));
                do_action('item_advantages_edit', $data_id, $array, $last_data, $result);
                $res_errors = _debug_table_from_db($result, 'advantages', $array);
                _display_db_table_error($form, $res_errors);
            } else {
                $form->error_form(is_isset($res, 'error'));
            }
        } else {
            $res = apply_filters('item_advantages_add_before', pn_ind(), $array);
            if ($res['ind']) {
                $array['create_date'] = current_time('mysql');
                $result = $wpdb->insert($wpdb->prefix . 'advantages', $array);
                $data_id = $wpdb->insert_id;
                if ($result) {
                    do_action('item_advantages_add', $data_id, $array);
                } else {
                    $res_errors = _debug_table_from_db($result, 'advantages', $array);
                    _display_db_table_error($form, $res_errors);
                }
            } else {
                $form->error_form(is_isset($res, 'error'));
            }
        }

        $url = admin_url('admin.php?page=all_add_advantages&item_id=' . $data_id . '&reply=true');
        $form->answer_form($url);
    }
}	