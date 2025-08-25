<?php
if (!defined('ABSPATH')) { exit(); }

register_nav_menu('mobile_top_menu', __('Mobile top menu for guest', 'pntheme'));
register_nav_menu('mobile_top_menu_user', __('Mobile top menu for users', 'pntheme'));

if (function_exists('is_mobile') and is_mobile()) {
	remove_action('wp_enqueue_scripts', 'my_themeinit', 0);
	add_action('wp_enqueue_scripts', 'my_mobthemeinit', 100);
	function my_mobthemeinit() {
		
		$plugin = get_plugin_class();
		$plugin_url = get_premium_url();

		wp_deregister_style('open-sans');
		wp_enqueue_style('open-sans', is_ssl_url("https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600;1,700&display=swap"), false, $plugin->vers());		
		wp_enqueue_script("jquery table", $plugin_url . "js/jquery-table/script.min.js", false, $plugin->vers('0.5'));
		wp_enqueue_script("jquery-checkbox", $plugin_url . 'js/jquery-checkbox/script.min.js', false, $plugin->vers('0.2'));
		wp_enqueue_script('jquery mobile site js', PN_TEMPLATEURL . '/mobile/js/site.js', false, $plugin->vers());
		wp_enqueue_style('theme-style', PN_TEMPLATEURL . "/mobile/style.css", false, $plugin->vers());
		
	}
}