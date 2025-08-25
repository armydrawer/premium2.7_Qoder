<?php

if (!defined('ABSPATH')) exit();

if (!function_exists('premium_verify_csrf')) {
    add_action('premium_post', 'premium_verify_csrf', 0);
    function premium_verify_csrf($m) {

        if ('post' == $m or 'action' == $m) {
            $method = trim(is_param_get('meth'));
            if (!pn_verify_nonce(is_param_get('yid'), is_param_get('ynd'))) {
                if ('get' == $method) {

                    pn_display_mess(__('System error (code: anticsfr)', 'premium'));

                } else {

                    header('Content-Type: application/json; charset=' . get_charset());
                    status_header(200);

                    $log = array();
                    $log['status'] = 'error';
                    $log['status_code'] = '1';
                    $log['status_text'] = __('System error (code: anticsfr)', 'premium');
                    echo pn_json_encode($log);
                    exit;

                }
            }
        }

    }
}

if (!function_exists('pn_remove_pingback_method')) {
    add_filter('xmlrpc_enabled', '__return_false');
    add_filter('wp_xmlrpc_server_class', 'disable_wp_xmlrpc_server_class');
    function disable_wp_xmlrpc_server_class() {

        return 'disable_wp_xmlrpc_server_class';
    }

    class disable_wp_xmlrpc_server_class {
        function serve_request() {

            echo 'XMLRPC disabled';
            exit;
        }
    }

    add_filter('xmlrpc_methods', 'pn_remove_pingback_method');
    function pn_remove_pingback_method($methods) {

        if (isset($methods['pingback.ping'])) {
            unset($methods['pingback.ping']);
        }
        if (isset($methods['pingback.extensions.getPingbacks'])) {
            unset($methods['pingback.extensions.getPingbacks']);
        }

        return $methods;
    }

    add_filter('wp_headers', 'pn_remove_x_pingback_header');
    function pn_remove_x_pingback_header($headers) {

        if (isset($headers['X-Pingback'])) {
            unset($headers['X-Pingback']);
        }

        return $headers;
    }
}

if (!function_exists('security_comment_text')) {

    add_filter('comment_text', 'security_comment_text', 0);
    add_filter('the_content', 'security_comment_text', 0);
    add_filter('the_excerpt', 'security_comment_text', 0);
    function security_comment_text($content) {

        return pn_strip_text($content);
    }

    add_filter('the_title', 'security_the_title', 0);
    function security_the_title($content) {

        return pn_strip_input($content);
    }

    add_filter('is_email', 'security_is_email', 0);
    function security_is_email($content) {

        return pn_strip_input(trim($content));
    }
}

if (!function_exists('security_preprocess_comment')) {
    add_filter('preprocess_comment', 'security_preprocess_comment', 10);
    function security_preprocess_comment($commentdata) {

        if (is_array($commentdata)) {
            $new_comment = array();
            foreach ($commentdata as $k => $v) {
                $new_comment[$k] = pn_maxf_mb(pn_strip_text($v), 2000);
            }

            return $new_comment;
        }

        return $commentdata;
    }
}

if (!function_exists('security_query_vars')) {
    add_filter('query_vars', 'security_query_vars');
    function security_query_vars($data) {

        if (!is_admin()) {
            $key = array_search('author', $data);
            if ($key) {
                if (isset($data[$key])) {
                    unset($data[$key]);
                }
            }
            $key = array_search('author_name', $data);
            if ($key) {
                if (isset($data[$key])) {
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }
}

if (!function_exists('security_wp_dashboard_setup')) {
    add_action('wp_dashboard_setup', 'security_wp_dashboard_setup');
    function security_wp_dashboard_setup() {
        if (current_user_can('administrator')) {
            wp_add_dashboard_widget('standart_security_dashboard_widget', __('Security check', 'premium'), 'dashboard_security_in_admin_panel');
        }
    }

    function dashboard_security_in_admin_panel() {

        $errors = (array)apply_filters('premium_security_errors', []);

        if ($errors) {
            foreach ($errors as $error) {
                echo sprintf('<div class="dashboard_security_line">&mdash; %s</div>', $error);
            }
        } else {
            echo sprintf('<div class="bgreen">%s</div>', __('Security status - OK', 'premium'));
        }

    }
}

if (!function_exists('premium_admin_bar_security')) {
    add_action('wp_before_admin_bar_render', 'premium_admin_bar_security', 2);
    function premium_admin_bar_security() {
        global $wp_admin_bar;

        if (current_user_can('administrator')) {
            $errors = (array)apply_filters('premium_security_errors', []);

            if ($errors) {

                $wp_admin_bar->add_node(array(
                    'id' => 'security_alert',
                    'href' => admin_url('admin.php?page=all_security_alert'),
                    'title' => '<div style="height: 32px; width: 32px; background: url(' . get_premium_url() . '/images/alert.gif) no-repeat center center; background-size: contain;"></div>',
                    'meta' => array(
                        'title' => sprintf(__('Security errors (%s)', 'premium'), count($errors)),
                        'class' => 'premium_ab_icon',
                    )
                ));

            }
        }
    }
}

if (!function_exists('premium_security_admin_menu')) {
    add_action('admin_menu', 'premium_security_admin_menu');
    function premium_security_admin_menu() {
        if (function_exists('get_plugin_class')) {
            $plugin = get_plugin_class();
            add_submenu_page("pn_none_menu", __('Security errors', 'premium'), __('Security errors', 'premium'), 'administrator', "all_security_alert", array($plugin, 'admin_temp'));
        }
    }
}

if (!function_exists('def_adminpage_title_all_security_alert')) {
    add_filter('pn_adminpage_title_all_security_alert', 'def_adminpage_title_all_security_alert');
    function def_adminpage_title_all_security_alert() {

        return __('Security errors', 'premium');
    }
}

if (!function_exists('def_adminpage_content_all_security_alert')) {
    add_action('pn_adminpage_content_all_security_alert', 'def_adminpage_content_all_security_alert');
    function def_adminpage_content_all_security_alert() {
        ?>
        <div style="margin: 0 0 10px 0;">
            <?php
            if (class_exists('PremiumForm')) {
                $form = new PremiumForm();

                $url = sprintf('https://premiumexchanger.com/%s/wiki/biblioteka-hukov/', get_lang_key(get_admin_lang()));
                $text = sprintf(__('Specify security settings or follow <a href="%s" target="_blank">the link</a> to see instructions for disabling security settings notifications.', 'premium'), $url);
                $form->help(__('Instructions for disabling notifications', 'premium'), $text);
            }
            ?>
        </div>
        <?php
        $errors = (array)apply_filters('premium_security_errors', []);

        foreach ($errors as $error) {
            echo sprintf('<div class="security_line">%s</div>', $error);
        }
    }
}

if (!function_exists('def_premium_security_errors')) {
    add_filter('premium_security_errors', 'def_premium_security_errors');
    function def_premium_security_errors($errors) {

        if (is_file(sprintf('%s%s', ABSPATH, 'updater.php'))) {
            $errors[] = __('There is a dangerous script updater.php in root directory. Delete it', 'premium');
        }

        if (is_file(sprintf('%s%s', ABSPATH, 'damp_db.sql'))) {
            $errors[] = __('There is a dangerous file damp_db.sql in root directory. Delete it', 'premium');
        }

        if (is_dir(sprintf('%s%s', ABSPATH, 'installer/'))) {
            $errors[] = __('There is a dangerous folder installer in root directory. Delete it', 'premium');
        }

        if (!defined('DISALLOW_FILE_MODS') or defined('DISALLOW_FILE_MODS') and !constant('DISALLOW_FILE_MODS')) {
            $errors[] = __('Edit mode enabled. Disable it', 'premium');
        }

        if (defined('PN_ADMIN_GOWP') and 'true' == constant('PN_ADMIN_GOWP')) {
            $errors[] = __('Disable editing mode', 'premium');
        }

        if (function_exists('get_adminpanel_address') && !get_adminpanel_address()) {
            $errors[] = __('Set new address of website control panel', 'premium');
        }

        $enabled_functions = array_filter(['exec', 'system', 'passthru', 'shell_exec', 'proc_open', 'show_source'], 'function_exists');
        if ($enabled_functions) {
            $url = sprintf('https://premiumexchanger.com/%s/wiki/dangerous-functions/', get_lang_key(get_admin_lang()));
            $text = sprintf(__('Dangerous functions enabled (<a href="%s" target="_blank">more details</a>)', 'premium'), $url);
            $errors[] = sprintf('%s: %s', $text, implode(', ', $enabled_functions));
        }

        $get_permissions = fn($path) => !file_exists($path) ? 'not_found' : substr(sprintf('%o', fileperms($path)), -4);
        $paths_to_check = [
            '/' => ['recommended' => ['0755']],
            'wp-admin' => ['recommended' => ['0755']],
            'wp-includes' => ['recommended' => ['0755']],
            'wp-content' => ['recommended' => ['0755']],
            'wp-content/themes' => ['recommended' => ['0755']],
            'wp-content/plugins' => ['recommended' => ['0755']],
            'wp-content/uploads' => ['recommended' => ['0755']],
            'wp-content/pn_uploads' => ['recommended' => ['0755']],
            'index.php' => ['recommended' => ['0444', '0644']],
            '.htaccess' => ['recommended' => ['0644'], 'optional' => true],
            'wp-config.php' => ['recommended' => ['0400', '0440']],
        ];

        $problems = [];
        foreach ($paths_to_check as $path => $details) {
            $full_path = ($path === '/') ? rtrim(ABSPATH, '/') : ABSPATH . $path;
            $current_perms = $get_permissions($full_path);

            if (
                ($current_perms === 'not_found' && empty($details['optional'])) ||
                ($current_perms !== 'not_found' && !in_array($current_perms, $details['recommended']))
            ) {
                $problems[] = [
                    'path' => $path,
                    'current' => $current_perms,
                    'recommended' => implode('/', $details['recommended']),
                ];
            }
        }

        if ($problems) {
            $pd = [];
            foreach ($problems as $val) {
                $pd[] = "{$val['path']} (<del>{$val['current']}</del> &rarr; {$val['recommended']})";
            }

            $url = sprintf('https://premiumexchanger.com/%s/wiki/filedir-permissions/', get_lang_key(get_admin_lang()));
            $text = sprintf(__('Incorrect file or directory permissions (<a href="%s" target="_blank">more details</a>)', 'premium'), $url);
            $errors[] = sprintf('%s: %s', $text, implode(', ', $pd));
        }


        $ui = wp_get_current_user();

        if (isset($ui->user_login) and 'admin' == $ui->user_login or isset($ui->user_login) and 'administrator' == $ui->user_login) {
            $errors[] = __('Admin login is standard. Change it', 'premium');
        }

        if (
            isset($ui->email_login) and 1 != $ui->email_login and
            isset($ui->sms_login) and 1 != $ui->sms_login and
            isset($ui->telegram_login) and 1 != $ui->telegram_login
        ) {
            $errors[] = sprintf(__('Two-factor authentication is disabled. <a href="%s">Instructions</a> how to enable it', 'premium'), 'https://premium.gitbook.io/rukovodstvo-polzovatelya/osnovnye-nastroiki/nastroiki/dvukhfaktornaya-avtorizaciya-2fa-v-paneli-upravleniya-saitom');
        }

        if (isset($ui->user_pass) and '$P$BASwWSemU6D3fp2iRd2M7pX0SH.g2a/' == $ui->user_pass) {
            $errors[] = __('Admin password is standard. Change it', 'premium');
        }

        if (defined('MERCH_ACTION_PASSWORD') and strlen(MERCH_ACTION_PASSWORD) < 1 or defined('PAY_ACTION_PASSWORD') and strlen(PAY_ACTION_PASSWORD) < 1 or defined('EDIT_ACTION_PASSWORD') and strlen(EDIT_ACTION_PASSWORD) < 1) {
            $errors[] = sprintf(__('Security password is disabled. <a href="%s">Instructions</a> how to enable it', 'premium'), 'https://premium.gitbook.io/rukovodstvo-polzovatelya/osnovnye-nastroiki/nastroiki/kody-bezopasnosti');
        }

        return $errors;
    }
}
