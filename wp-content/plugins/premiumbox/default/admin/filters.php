<?php

if (!defined('ABSPATH')) exit();

$plugin = get_plugin_class();

add_filter('admin_footer_text', '__return_false', 0);

if (1 == $plugin->get_option('admin', 'w0')) {
    remove_action('welcome_panel', 'wp_welcome_panel');
}

add_action('wp_dashboard_setup', 'pn_remove_dashboard_widgets');
function pn_remove_dashboard_widgets() {

    $plugin = get_plugin_class();

    remove_meta_box('dashboard_site_health', 'dashboard', 'normal');

    if (1 == $plugin->get_option('admin', 'w1')) {
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
    }

    if (1 == $plugin->get_option('admin', 'w2')) {
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');
    }

    if (1 == $plugin->get_option('admin', 'w3')) {
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    }

    if (1 == $plugin->get_option('admin', 'w4') or function_exists('is_ml') and is_ml()) {
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
    }

    if (1 == $plugin->get_option('admin', 'w6')) {
        remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
    }

    if (1 == $plugin->get_option('admin', 'w7')) {
        remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
    }

    if (1 == $plugin->get_option('admin', 'w8')) {
        remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
    }

    remove_meta_box('dashboard_secondary', 'dashboard', 'side');

}

add_action('widgets_init', 'pn_remove_default_widget');
function pn_remove_default_widget() {

    unregister_widget('WP_Widget_RSS');
    unregister_widget('WP_Widget_Calendar');
    unregister_widget('WP_Widget_Tag_Cloud');
    unregister_widget('WP_Nav_Menu_Widget');
    unregister_widget('WP_Widget_Recent_Posts');
    unregister_widget('WP_Widget_Pages');
    unregister_widget('WP_Widget_Archives');
    unregister_widget('WP_Widget_Meta');
    unregister_widget('WP_Widget_Search');
    unregister_widget('WP_Widget_Categories');

    $plugin = get_plugin_class();
    if (1 == $plugin->get_option('admin', 'ws1')) {
        unregister_widget('WP_Widget_Recent_Comments');
    }

}

add_action('admin_init', 'pn_close_admin_mail');
function pn_close_admin_mail() {
    add_filter('wp_new_user_notification_email_admin', 'pn_wp_new_user_notification_email_admin');
}

function pn_wp_new_user_notification_email_admin($wp_new_user_notification_email_admin) {

    if (isset($wp_new_user_notification_email_admin['to'])) {
        unset($wp_new_user_notification_email_admin['to']);
    }

    return $wp_new_user_notification_email_admin;
}

add_action('admin_init', 'admin_init__deleting_pages');
function admin_init__deleting_pages() {
    global $pagenow;

    if (in_array($pagenow, ['site-health.php', 'site-health-info.php'])) {
        wp_die(__('Sorry, you are not allowed to access this page.'));
    } elseif ('options.php' == $pagenow) {
        if (
            'GET' == $_SERVER['REQUEST_METHOD'] ||
            'options' == ($_POST['option_page'] ?? '')
        ) {
            wp_die(__('Sorry, you are not allowed to access this page.'));
        }
    }

    /*$request = ltrim(get_request_query(), '/');
    if (in_array($request, ['wp-admin/site-health.php', 'wp-admin/site-health-info.php', 'wp-admin/options.php'])) {
        pn_display_mess(__('Page does not exist', 'pn'));
    }*/
}

add_action('admin_menu', 'pn_remove_meta_boxes', 1000);
function pn_remove_meta_boxes() {
    $plugin = get_plugin_class();

    remove_submenu_page('tools.php', 'site-health.php');

    remove_meta_box('trackbacksdiv', 'post', 'normal');
    remove_meta_box('postcustom', 'post', 'normal');

    remove_meta_box('trackbacksdiv', 'page', 'normal');
    remove_meta_box('postcustom', 'page', 'normal');

    if (1 == $plugin->get_option('admin', 'ws0')) {
        remove_menu_page('edit.php');
    }

    if (1 == $plugin->get_option('admin', 'ws1')) {
        remove_menu_page('edit-comments.php');
        remove_submenu_page('options-general.php', 'options-discussion.php');
    }

    if (1 == $plugin->get_option('admin', 'ws2')) {
        remove_menu_page('upload.php');
    }

    if (1 == $plugin->get_option('admin', 'ws3')) {
        remove_menu_page('tools.php');
    }

    if (1 == $plugin->get_option('admin', 'ws4')) {
        remove_submenu_page('options-general.php', 'options-media.php');
    }

    if (1 == $plugin->get_option('admin', 'ws5')) {
        remove_submenu_page('options-general.php', 'options-privacy.php');
    }

    if (1 == $plugin->get_option('admin', 'ws6')) {
        remove_submenu_page('options-general.php', 'options-writing.php');
    }
}

remove_action('wp_head', 'wp_generator');

foreach (array('rss2_head', 'commentsrss2_head', 'rss_head', 'rdf_header', 'atom_head', 'comments_atom_head', 'opml_head', 'app_head') as $action) {
    remove_action($action, 'the_generator');
}

add_filter('rest_enabled', '__return_false');
remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');
remove_action('wp_head', 'rest_output_link_wp_head', 10, 0);
remove_action('template_redirect', 'rest_output_link_header', 11, 0);
remove_action('auth_cookie_malformed', 'rest_cookie_collect_status');
remove_action('auth_cookie_expired', 'rest_cookie_collect_status');
remove_action('auth_cookie_bad_username', 'rest_cookie_collect_status');
remove_action('auth_cookie_bad_hash', 'rest_cookie_collect_status');
remove_action('auth_cookie_valid', 'rest_cookie_collect_status');
remove_filter('rest_authentication_errors', 'rest_cookie_check_errors', 100);
remove_action('init', 'rest_api_init');
remove_action('rest_api_init', 'rest_api_default_filters', 10, 1);
remove_action('parse_request', 'rest_api_loaded');
remove_action('rest_api_init', 'wp_oembed_register_route');
remove_filter('rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10, 4);
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');

add_action('wp_before_admin_bar_render', 'pn_admin_bar_links');
function pn_admin_bar_links() {
    global $wp_admin_bar;

    $plugin = get_plugin_class();

    $wp_admin_bar->remove_menu('wp-logo');
    $wp_admin_bar->remove_menu('new-media');
    $wp_admin_bar->remove_menu('new-link');
    $wp_admin_bar->remove_menu('themes');
    $wp_admin_bar->remove_menu('search');
    $wp_admin_bar->remove_menu('customize');

    if (1 == $plugin->get_option('admin', 'ws0')) {
        $wp_admin_bar->remove_menu('new-post');
    }

    if (1 == $plugin->get_option('admin', 'ws1')) {
        $wp_admin_bar->remove_menu('comments');
    }
}

add_filter('the_content', 'do_shortcode', 10);
add_filter('comment_text', 'do_shortcode', 10);

add_action('parse_query', 'pn_search_turn_off');
function pn_search_turn_off($q, $e = true) {
    if (is_search()) {
        $q->is_search = false;
        $q->query_vars['s'] = false;
        $q->query['s'] = false;
        if ($e == true) {
            $q->is_404 = true;
        }
    }
}

add_filter('get_search_form', 'def_get_search_form');
function def_get_search_form() {
    return null;
}

function disable_all_feeds() {
    pn_display_mess(__('RSS feed is off', 'pn'));
}

if (1 == $plugin->get_option('admin', 'wm0')) {
    add_action('do_feed', 'disable_all_feeds', 1);
    add_action('do_feed_rdf', 'disable_all_feeds', 1);
    add_action('do_feed_rss', 'disable_all_feeds', 1);
    add_action('do_feed_rss2', 'disable_all_feeds', 1);
    add_action('do_feed_atom', 'disable_all_feeds', 1);
}
