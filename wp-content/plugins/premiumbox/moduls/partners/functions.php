<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('get_partners')) {
	function get_partners() {
		global $wpdb;
		
		$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "partners WHERE auto_status = '1' AND status='1' ORDER BY site_order ASC");
		
		return $datas;
	}
}

if (!function_exists('get_partner_url')) {
	function get_partner_url($item) {
		
		return esc_url(ctv_ml($item->link));
	}
}

if (!function_exists('get_partner_title')) {
	function get_partner_title($item) {
		
		return pn_strip_input(ctv_ml($item->title));
	}
}

if (!function_exists('get_partner_logo')) {
	function get_partner_logo($item) {
		
		return is_ssl_url(esc_url($item->img));
	}
}