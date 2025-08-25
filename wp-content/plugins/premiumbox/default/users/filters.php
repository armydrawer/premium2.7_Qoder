<?php

if (!defined('ABSPATH')) exit();

if (!function_exists('pn_edit_user_link')) {
    function pn_edit_user_link($user_id) {

        return admin_url("admin.php?page=all_edit_user&item_id=" . $user_id);
    }
}

if (!function_exists('pn_sanitize_user')) {
    add_filter('sanitize_user', 'pn_sanitize_user');
    function pn_sanitize_user($login) {

        return is_user($login);
    }
}

if (!function_exists('pn_delete_user')) {
    add_action('delete_user', 'pn_delete_user');
    function pn_delete_user($user_id) {
        global $wpdb;

        $wpdb->query("DELETE FROM " . $wpdb->prefix . "auth_logs WHERE user_id = '$user_id'");
        $wpdb->query("DELETE FROM " . $wpdb->prefix . "comment_system WHERE item_id = '$user_id' AND itemtype = 'user'");
    }
}

if (!function_exists('del_authlogs')) {
    function del_authlogs() {
        global $wpdb;

        $plugin = get_plugin_class();
        if (!$plugin->is_up_mode()) {
            $count_day = get_logs_sett('delete_autologs_day');
            if ($count_day > 0) {
                $time = current_time('timestamp') - ($count_day * DAY_IN_SECONDS);
                $ldate = date('Y-m-d H:i:s', $time);
                $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "auth_logs WHERE auth_date < '$ldate'");
                foreach ($items as $item) {
                    $item_id = $item->id;
                    $res = apply_filters('item_authlogs_delete_before', pn_ind(), $item_id, $item);
                    if ($res['ind']) {
                        $result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "auth_logs WHERE id = '$item_id'");
                        do_action('item_authlogs_delete', $item_id, $item, $result);
                    }
                }
            }
        }
    }
}

if (!function_exists('del_authlogs_cronjob')) {
    add_filter('list_cron_func', 'del_authlogs_cronjob');
    function del_authlogs_cronjob($filters) {

        $filters['del_authlogs'] = array(
            'title' => __('Deleting authorization logs', 'pn'),
            'site' => '1day',
            'file' => 'none',
        );

        return $filters;
    }
}

if (!function_exists('authlogs_list_logs_settings')) {
    add_filter('list_logs_settings', 'authlogs_list_logs_settings');
    function authlogs_list_logs_settings($filters) {

        $filters['delete_autologs_day'] = array(
            'title' => __('Deleting authorization logs', 'pn') . ' (' . __('days', 'pn') . ')',
            'count' => 60,
            'minimum' => 3,
        );

        return $filters;
    }
}

if (!function_exists('auth_fail_logs')) {
    add_filter('authenticate', 'auth_fail_logs', 1000);
    function auth_fail_logs($user) {
        global $wpdb;

        $logmail = '';
        if (isset($_POST['log'])) {
            $logmail = is_param_post('log');
        }
        if (isset($_POST['logmail'])) {
            $logmail = is_param_post('logmail');
        }

        if ($logmail and is_wp_error($user)) {

            if (strstr($logmail, '@')) {
                $logmail = is_email($logmail);
                $ui = get_user_by('email', $logmail);
            } else {
                $logmail = is_user($logmail);
                $ui = get_user_by('login', $logmail);
            }

            if (isset($ui->ID)) {

                $error_text = pn_strip_input($user->get_error_message());

                $user_id = $ui->ID;

                $old_user_browser = is_isset($ui, 'user_browser');
                $old_user_ip = is_isset($ui, 'user_ip');

                $now_user_browser = pn_maxf(pn_strip_input(is_isset($_SERVER, 'HTTP_USER_AGENT')), 500);
                $now_user_ip = pn_real_ip();

                $array = array();
                $array['auth_date'] = current_time('mysql');
                $array['user_id'] = $user_id;
                $array['user_login'] = is_user($ui->user_login);
                $array['now_user_ip'] = $now_user_ip;
                $array['now_user_browser'] = $now_user_browser;
                $array['old_user_ip'] = $old_user_ip;
                $array['old_user_browser'] = $old_user_browser;
                $array['auth_status'] = 0;
                $array['auth_status_text'] = $error_text;
                $wpdb->insert($wpdb->prefix . 'auth_logs', $array);

            }
        }

        return $user;
    }
}

if (!function_exists('save_user_ip_browser')) {
    add_action('set_logged_in_cookie', 'save_user_ip_browser', 99, 4);
    function save_user_ip_browser($logged_in_cookie, $expire, $expiration, $user_id) {
        global $change_ld_account, $wpdb;

        if ($change_ld_account != 1 and $user_id > 0) {

            $ui = get_userdata($user_id);

            $old_user_browser = is_isset($ui, 'user_browser');
            $old_user_ip = is_isset($ui, 'user_ip');

            $now_user_browser = get_user_agent();
            $now_user_ip = pn_real_ip();

            $array = array();
            $array['user_browser'] = $now_user_browser;
            $array['user_ip'] = $now_user_ip;
            $wpdb->update($wpdb->prefix . "users", $array, array('ID' => $user_id));

            $array = array();
            $array['auth_date'] = current_time('mysql');
            $array['user_id'] = $user_id;
            $array['user_login'] = is_user($ui->user_login);
            $array['now_user_ip'] = $now_user_ip;
            $array['now_user_browser'] = $now_user_browser;
            $array['old_user_ip'] = $old_user_ip;
            $array['old_user_browser'] = $old_user_browser;
            $array['auth_status'] = 1;
            $wpdb->insert($wpdb->prefix . 'auth_logs', $array);

            $notify_tags = array();
            $notify_tags['[date]'] = $array['auth_date'];
            $notify_tags['[ip]'] = $array['now_user_ip'];
            $notify_tags['[browser]'] = get_browser_name($array['now_user_browser'], __('Unknown', 'pn'));
            $notify_tags['[old_ip]'] = $array['old_user_ip'];
            $notify_tags['[old_browser]'] = get_browser_name($array['old_user_browser'], __('Unknown', 'pn'));
            $notify_tags = apply_filters('notify_tags_alogs', $notify_tags, $array, $ui);

            $user_send_data = array();
            if (isset($ui->alogs_email) and 1 == $ui->alogs_email) {
                $user_send_data['user_email'] = is_isset($ui, 'user_email');
            }
            $user_send_data = apply_filters('user_send_data', $user_send_data, 'alogs', $ui);
            $result_mail = apply_filters('premium_send_message', 0, 'alogs', $notify_tags, $user_send_data);

        }
    }
}

if (!function_exists('list_user_notify_alogs')) {
    add_filter('list_user_notify', 'list_user_notify_alogs');
    function list_user_notify_alogs($places) {

        $places['alogs'] = __('Notify of user logging into personal account', 'pn');
        $places['letterauth'] = __('Two-factor authorization', 'pn');

        return $places;
    }
}

if (!function_exists('def_list_notify_tags_alogs')) {
    add_filter('list_notify_tags_alogs', 'def_list_notify_tags_alogs');
    function def_list_notify_tags_alogs($tags) {

        $tags['date'] = array(
            'title' => __('Date', 'pn'),
            'start' => '[date]',
        );
        $tags['ip'] = array(
            'title' => __('Current IP address', 'pn'),
            'start' => '[ip]',
        );
        $tags['browser'] = array(
            'title' => __('Current browser', 'pn'),
            'start' => '[browser]',
        );
        $tags['old_ip'] = array(
            'title' => __('Previous IP address', 'pn'),
            'start' => '[old_ip]',
        );
        $tags['old_browser'] = array(
            'title' => __('Previous browser', 'pn'),
            'start' => '[old_browser]',
        );

        return $tags;
    }
}

if (!function_exists('def_notify_tags_letterauth')) {
    add_filter('list_notify_tags_letterauth', 'def_notify_tags_letterauth');
    function def_notify_tags_letterauth($tags) {

        $tags['link'] = array(
            'title' => __('Login link', 'pn'),
            'start' => '[link]',
        );
        $tags['pincode'] = array(
            'title' => __('Pin-code', 'pn'),
            'start' => '[pincode]',
        );

        return $tags;
    }
}

if (!function_exists('admin_init_operator')) {
    add_action('admin_init', 'admin_init_operator');
    function admin_init_operator() {
        if (current_user_can('administrator') or current_user_can('read')) {
            global $wpdb;

            $ui = wp_get_current_user();
            $user_id = intval($ui->ID);

            $array = array();
            $array['last_adminpanel'] = current_time('timestamp');
            $wpdb->update($wpdb->prefix . "users", $array, array('ID' => $user_id));

        }
    }
}

if (!function_exists('standart_user_wp_dashboard_setup')) {
    add_action('wp_dashboard_setup', 'standart_user_wp_dashboard_setup');
    function standart_user_wp_dashboard_setup() {

        wp_add_dashboard_widget('standart_user_dashboard_widget', __('Users in Admin Panel', 'pn'), 'dashboard_users_in_admin_panel');

    }
}

if (!function_exists('dashboard_users_in_admin_panel')) {
    function dashboard_users_in_admin_panel() {
        global $wpdb;

        $time = current_time('timestamp') - 60;
        $users = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "users WHERE last_adminpanel >= '$time'");
        foreach ($users as $us) {
            echo '<strong>' . is_user($us->user_login) . '</strong> (' . date("d.m.Y, H:i:s", pn_strip_input($us->last_adminpanel)) . ')';
            echo '<hr />';
        }

    }
}

if (!function_exists('ban_site_check')) {
    add_action('init', 'ban_site_check', 9);
    function ban_site_check() {
        if (!current_user_can('administrator')) {
            $ui = wp_get_current_user();
            $user_bann = intval(is_isset($ui, 'user_bann'));
            if (1 == $user_bann) {

                wp_logout();
                wp_redirect(PN_SITE_URL);
                exit();

            }
        }
    }
}

if (!function_exists('ban_login_check')) {
    add_filter('authenticate', 'ban_login_check', 90, 1);
    function ban_login_check($user) {
        if (is_object($user) and isset($user->data->ID)) {
            if (!user_can($user, 'administrator')) {
                $user_bann = intval($user->data->user_bann);
                if ($user_bann) {

                    $error = new WP_Error();
                    $error->add('pn_error', __('Error! Your account blocked', 'pn'));
                    wp_clear_auth_cookie();

                    return $error;
                }
            }
        }

        return $user;
    }
}

if (!function_exists('user_pn_user_register')) {
    add_action('pn_user_register', 'user_pn_user_register');
    function user_pn_user_register($user_id) {
        global $wpdb;

        $array = array();
        $array['user_registered'] = current_time('mysql');
        $array['user_browser'] = get_user_agent();
        $array['user_ip'] = pn_real_ip();
        $array['user_bann'] = 0;
        $ui = wp_get_current_user();
        $created_data = array(
            'creator_id' => intval(is_isset($ui, 'ID')),
        );
        $created_data = pn_json_encode($created_data);
        $array['created_data'] = $created_data;
        $array = apply_filters('pn_user_register_data', $array, $user_id);
        $wpdb->update($wpdb->prefix . "users", $array, array('ID' => $user_id));
    }
}

if (!function_exists('pn_unset_profile_details')) {
    add_filter('user_contactmethods', 'pn_unset_profile_details', 10, 1);
    function pn_unset_profile_details($conts) {

        if (isset($conts['yim'])) {
            unset($conts['yim']);
        }
        if (isset($conts['aim'])) {
            unset($conts['aim']);
        }
        if (isset($conts['jabber'])) {
            unset($conts['jabber']);
        }

        $fields = get_user_fields();
        foreach ($fields as $field_key => $field_data) {
            if ('user_email' != $field_key) {
                $conts[$field_key] = is_isset($field_data, 'title');
            }
        }

        return $conts;
    }
}
