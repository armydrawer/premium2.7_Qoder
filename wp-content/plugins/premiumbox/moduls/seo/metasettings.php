<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('def_adminpage_title_all_metasettings')) {
	add_filter('pn_adminpage_title_all_metasettings', 'def_adminpage_title_all_metasettings');
	function def_adminpage_title_all_metasettings($page) {
		
		return __('Meta tags and metrics', 'pn');
	} 
}

if (!function_exists('def_all_metasettings_option')) {
	add_filter('all_metasettings_option', 'def_all_metasettings_option', 9);
	function def_all_metasettings_option($options) {
		
		$plugin = get_plugin_class();
			
		$options = array();
		$options['all_title'] = array(
			'view' => 'h3',
			'title' => __('Meta tags', 'pn'),
			'submit' => __('Save', 'pn'),
		);			
		$options['ya_meta'] = array(
			'view' => 'inputbig',
			'title' => __('Yandex meta tag', 'pn'),
			'default' => $plugin->get_option('seo', 'ya_meta'),
			'name' => 'ya_meta',
			'work' => 'input',
		);
		$options['gl_meta'] = array(
			'view' => 'inputbig',
			'title' => __('Google meta tag', 'pn'),
			'default' => $plugin->get_option('seo', 'gl_meta'),
			'name' => 'gl_meta',
			'work' => 'input',
		);	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Yandex.Metrika and goals', 'pn'),
			'submit' => __('Save', 'pn'),
		);			
		$options['ya_metrika'] = array(
			'view' => 'inputbig',
			'title' => __('Yandex.Metrika counter ID', 'pn'),
			'default' => $plugin->get_option('seo', 'ya_metrika'),
			'name' => 'ya_metrika',
			'work' => 'input',
		);
		$options['top_title2'] = array(
			'view' => 'h3',
			'title' => __('Google Analytics', 'pn'),
			'submit' => __('Save', 'pn'),
		);			
		$options['gglanalytic'] = array(
			'view' => 'inputbig',
			'title' => __('Google Analytics counter ID', 'pn'),
			'default' => $plugin->get_option('seo', 'gglanalytic'),
			'name' => 'gglanalytic',
			'work' => 'input',
		);			
			
		return $options;
	}
}	

if (!function_exists('def_adminpage_content_all_metasettings')) {
	add_action('pn_adminpage_content_all_metasettings', 'def_adminpage_content_all_metasettings');
	function def_adminpage_content_all_metasettings() {
		
		$form = new PremiumForm();
		$params_form = array(
			'filter' => 'all_metasettings_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form);
		
	} 
}

if (!function_exists('def_premium_action_all_metasettings')) {
	add_action('premium_action_all_metasettings', 'def_premium_action_all_metasettings');
	function def_premium_action_all_metasettings() {
		
		$plugin = get_plugin_class();

		_method('post');
			
		$form = new PremiumForm();
		$form->send_header();
			
		pn_only_caps(array('administrator', 'pn_seo'));
			
		$data = $form->strip_options('all_metasettings_option', 'post');
							
		foreach ($data as $data_key => $data_value) {
			$plugin->update_option('seo', $data_key, $data_value);
		}				
			
		$url = admin_url('admin.php?page=all_metasettings&reply=true');
		$form->answer_form($url);
		
	} 
}