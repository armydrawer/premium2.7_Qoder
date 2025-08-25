<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('set_pn_direction')) {
	add_action('plugins_loaded', 'set_pn_direction', 10);
	function set_pn_direction() {
		global $pn_rtl, $pn_lang;
		
		$pn_rtl = 'ltr';
		
		if (!is_admin()) {
			$rtl_options = is_isset($pn_lang, 'rtl');
			if (!is_array($rtl_options)) { $rtl_options = array(); }
			
			$now_lang = get_locale();
			$now_rtl = trim(is_isset($rtl_options, $now_lang));
			$get_rtl = trim(is_param_get('get_rtl'));
			if ($now_rtl and 'rtl' == $now_rtl or $get_rtl and is_debug_mode()) {
				$pn_rtl = 'rtl';
			}
		}
	}
}

if (!function_exists('get_pn_rtl')) {
	function get_pn_rtl() {
		global $pn_rtl;
		
		return $pn_rtl;
	}
}	

if (!function_exists('is_pn_rtl')) {	
	function is_pn_rtl() {
		
		$true = 0;
		if ('rtl' == get_pn_rtl()) {
			$true = 1;
		}
		
		return $true;
	}
}	

if (!function_exists('pn_rtl_language_attributes')) {
	add_filter('language_attributes', 'pn_rtl_language_attributes');
	function pn_rtl_language_attributes($output) {
		
		$output .= ' dir="' . get_pn_rtl() . '"';
		
		return $output;
	}	
}	

if (!function_exists('pn_rtl_body_class')) {
	add_filter('body_class', 'pn_rtl_body_class');
	function pn_rtl_body_class($classes) {
		
		if (is_pn_rtl()) {
			$classes[] = 'rtl_body';
		} 
		
		return $classes;
	}
}