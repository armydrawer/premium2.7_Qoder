<?php

if (!defined('ABSPATH')) exit();

if (!function_exists('enableip_login_check')) {
    add_filter('authenticate', 'enableip_login_check', 60, 1);
    function enableip_login_check($user) {

        if (is_object($user) and isset($user->data->ID)) {
            if ('true' != PN_ADMIN_GOWP) {
                $enable_ips = trim($user->data->enable_ips);
                if ($enable_ips and !pn_has_ip($enable_ips)) {

                    $error = new WP_Error();
                    $error->add('pn_error', __('Error! Invalid IP address', 'pn'));
                    wp_clear_auth_cookie();

                    return $error;
                }
            }
        }

        return $user;
    }
}


if (!function_exists('init_enableip')) {
    add_action('init', 'init_enableip');
    function init_enableip() {
        if ('true' == PN_ADMIN_GOWP || !is_user_logged_in()) {
            return;
        }

        $ui = wp_get_current_user();
        $enable_ips = trim(is_isset($ui, 'enable_ips'));
        if ($enable_ips && !pn_has_ip($enable_ips)) {
            wp_logout();
            wp_safe_redirect(PN_SITE_URL);
            die();
        }

        if (!current_user_can('read')) {
            return;
        }

        $manager = WP_Session_Tokens::get_instance(get_current_user_id());
        $data = $manager->get(wp_get_session_token());

        if (!empty($data['session_hash']) && !hash_equals($data['session_hash'], asi_get_session_hash())) {
            $manager->destroy_all();
            wp_safe_redirect(PN_SITE_URL);
            die();
        }
    }
}


function asi_get_session_hash() {
    $user_agent = pn_strip_input(is_isset($_SERVER, 'HTTP_USER_AGENT'));
    $language = pn_strip_input(is_isset($_SERVER, 'HTTP_ACCEPT_LANGUAGE'));

    return wp_hash("{$user_agent}{$language}");
}


add_filter('attach_session_information', 'asi__session_hash', 10, 2);
function asi__session_hash($session, $user_id) {
    $session['session_hash'] = asi_get_session_hash();
    return $session;
}
