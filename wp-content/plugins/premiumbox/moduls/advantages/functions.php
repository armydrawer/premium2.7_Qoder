<?php
if (!defined('ABSPATH')) exit();

if (!function_exists('get_advantages')) {
    function get_advantages() {
        global $wpdb;

        $datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "advantages WHERE auto_status = '1' AND status = '1' ORDER BY site_order ASC");

        return $datas;
    }
}

if (!function_exists('get_advantage_url')) {
    function get_advantage_url($item) {

        return esc_url(ctv_ml($item->link));
    }
}

if (!function_exists('get_advantage_image')) {
    function get_advantage_image($item) {

        return is_ssl_url(pn_strip_input($item->img));
    }
}

if (!function_exists('get_advantage_title')) {
    function get_advantage_title($item) {

        return pn_strip_input(ctv_ml($item->title));
    }
}

if (!function_exists('get_advantage_content')) {
    function get_advantage_content($item) {

        return do_shortcode(pn_strip_text(ctv_ml($item->content)));
    }
}
