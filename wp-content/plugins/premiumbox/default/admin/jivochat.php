<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('jivosite_config_option')) {
	add_filter('all_settings_option', 'jivosite_config_option', 11);
	function jivosite_config_option($options) {
		
		$plugin = get_plugin_class();
		$options['jsite'] = array(
			'view' => 'inputbig',
			'title' => __('Jivochat.com ID', 'pn'),
			'default' => $plugin->get_option('seo', 'jsite'),
			'name' => 'jsite',
			'work' => 'input',
			'ml' => 1,
		);
		$options['jsiteya'] = array(
			'view' => 'inputbig',
			'title' => __('Jivochat.com ID','pn') . '(yandex)',
			'default' => $plugin->get_option('seo', 'jsiteya'),
			'name' => 'jsiteya',
			'work' => 'input',
			'ml' => 1,
		);	
		
		return $options;
	}
}		

if (!function_exists('jivosite_pn_config_option_post')) {
	add_action('all_settings_option_post', 'jivosite_pn_config_option_post');
	function jivosite_pn_config_option_post($data) {
		
		$plugin = get_plugin_class();
		$plugin->update_option('seo', 'jsite', $data['jsite']);
		$plugin->update_option('seo', 'jsiteya', $data['jsiteya']);
		
	}
}

if (!function_exists('wp_footer_jivosite')) {
	
	function get_jsite() {
		
		$html = '';
		$plugin = get_plugin_class();
		$jsite = pn_strip_input(ctv_ml($plugin->get_option('seo', 'jsite')));
		if ($jsite) {
			$html .= '<script src="//code.jivosite.com/widget/' . $jsite . '" async></script>';
		}
		
		$jsiteya = pn_strip_input(ctv_ml($plugin->get_option('seo', 'jsiteya')));
		if ($jsiteya) {
			$html .= '<script src="//code-ya.jivosite.com/widget/' . $jsiteya . '" async></script>';
		}		
		
		return $html;
	}	
	
	add_action('wp_footer', 'jivosite_wp_footer');
	function jivosite_wp_footer() {
		echo get_jsite();
	}
	
	add_filter('merchant_footer', 'jivosite_merchant_footer', 995);
	function jivosite_merchant_footer($html) {
		
		$html .= get_jsite();
		
		return $html;
	}	
	
}