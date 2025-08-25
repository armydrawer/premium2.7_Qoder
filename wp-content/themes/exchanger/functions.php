<?php
if (!defined('ABSPATH')) { exit(); }

add_action('init', 'langtheme_init');	
function langtheme_init() {
	
	load_theme_textdomain('pntheme', get_template_directory() . '/lang');
	
}

if (!function_exists('theme_include')) {
	function theme_include($page) {
		
		$pager = TEMPLATEPATH . "/" . $page . ".php";
		if (file_exists($pager)) {
			include($pager);
		}
		
	}
}

add_action('template_redirect', 'init_premium_theme', 0);
function init_premium_theme() {	
	if (!function_exists('get_plugin_class')) { 
	
		header('Content-Type: text/html; charset=utf-8');
		$text = trim(get_option('pn_update_plugin_text'));
		if (mb_strlen($text) < 1) { $text = __('Dear users, right now our website is updating. Please come back later.', 'pntheme'); }
		$text = apply_filters('comment_text', $text);
		$output_html = '<div style="border: 1px solid #ff0000; padding: 10px 15px; font: 13px Arial; width: 500px; border-radius: 3px; margin: 0 auto; text-align: center;">' . $text . '</div>';
		echo apply_filters('update_mode_plugin', $output_html, $text);
		exit;
		
	}	
}

if (!function_exists('get_plugin_class')) {
	return;
}

theme_include('includes/sites_func');
theme_include('includes/breadcrumb');
theme_include('includes/api');
theme_include('includes/comment_func');

theme_include('settings/color_scheme'); 
theme_include('settings/header');
theme_include('settings/home');
theme_include('settings/footer');