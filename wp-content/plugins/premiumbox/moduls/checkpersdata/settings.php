<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('admin_menu_checkpersdata')) {
	add_action('admin_menu', 'admin_menu_checkpersdata', 500);
	function admin_menu_checkpersdata() {
		
		$plugin = get_plugin_class();
		add_submenu_page("options-general.php", __('Personal data processing', 'pn'), __('Personal data processing', 'pn'), 'administrator', "all_checkpersdata", array($plugin, 'admin_temp'));
		
	}
}	
		
if (!function_exists('def_adminpage_title_all_checkpersdata')) {	
	add_filter('pn_adminpage_title_all_checkpersdata', 'def_adminpage_title_all_checkpersdata');
	function def_adminpage_title_all_checkpersdata() {
		
		return __('Personal data processing', 'pn');
	}
}	
		
if (!function_exists('def_pn_adminpage_content_all_checkpersdata')) {	
	add_action('pn_adminpage_content_all_checkpersdata', 'def_pn_adminpage_content_all_checkpersdata');
	function def_pn_adminpage_content_all_checkpersdata() {
		
		$plugin = get_plugin_class();
				
		$form = new PremiumForm();	
				
		$options = array();	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Show checkbox "Consent to processing of personal data" in forms', 'pn'),
			'submit' => __('Save','pn'),
		);	
			
		$placed = apply_filters('placed_form', array());	
		if (is_array($placed)) {
			foreach ($placed as $key => $title) {
				$options[] = array(
					'view' => 'select',
					'title' => $title,
					'options' => array('0' =>__('No', 'pn'), '1' => __('Yes', 'pn')),
					'default' => $plugin->get_option('checkpersdata', $key),
					'name' => $key,
				);			
			}
		}
			
		$params_form = array(
			'filter' => 'all_checkpersdata_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
		
	}
}

if (!function_exists('def_premium_action_all_checkpersdata')) {	
	add_action('premium_action_all_checkpersdata', 'def_premium_action_all_checkpersdata');
	function def_premium_action_all_checkpersdata() {
		
		$plugin = get_plugin_class();
			
		_method('post');
			
		$form = new PremiumForm();
		$form->send_header();
			
		pn_only_caps(array('administrator'));

		$placed = apply_filters('placed_form', array());	
		if (is_array($placed)) {
			foreach ($placed as $key => $title) {	
				$plugin->update_option('checkpersdata', $key, intval(is_param_post($key)));	
			}
		}		

		$url = admin_url('options-general.php?page=all_checkpersdata&reply=true');
		$form->answer_form($url);
		
	}		
}	