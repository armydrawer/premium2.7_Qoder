<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	if (!function_exists('admin_menu_iconbar')) {
		add_action('admin_menu', 'admin_menu_iconbar', 500);
		function admin_menu_iconbar() {
			
			$plugin = get_plugin_class();
			add_submenu_page("options-general.php", __('Notification icons', 'pn'), __('Notification icons', 'pn'), 'administrator', "all_iconbar", array($plugin, 'admin_temp'));
			
		}	
	}
		
	if (!function_exists('def_adminpage_title_all_iconbar')) {	
		add_filter('pn_adminpage_title_all_iconbar', 'def_adminpage_title_all_iconbar');
		function def_adminpage_title_all_iconbar() {
			
			return __('Notification icons', 'pn');
		}	
	}
			
	if (!function_exists('def_pn_adminpage_content_all_iconbar')) {		
		add_action('pn_adminpage_content_all_iconbar', 'def_pn_adminpage_content_all_iconbar');
		function def_pn_adminpage_content_all_iconbar() {
			
			$plugin = get_plugin_class();
					
			$form = new PremiumForm();	
					
			$options = array();	
			$options['top_title'] = array(
				'view' => 'h3',
				'title' => __('Notification icons', 'pn'),
				'submit' => __('Save', 'pn'),
			);	
				
			$lists = apply_filters('_icon_indicators', array());	
			if (is_array($lists)) {
				foreach ($lists as $list_key => $list_data) {
					$options[] = array(
						'view' => 'select',
						'title' => __('Disable icon', 'pn') . ' "' . is_isset($list_data, 'title') . '"',
						'options' => array('0' => __('No' ,'pn'), '1' => __('Yes', 'pn')),
						'default' => $plugin->get_option('iconbar_dis', $list_key),
						'name' => $list_key,
					);			
				}
			}
				
			$params_form = array(
				'filter' => 'all_iconbar_option',
				'button_title' => __('Save', 'pn'),
			);
			$form->init_form($params_form, $options);	
				
		}
	}
	
}	

if (!function_exists('def_premium_action_all_iconbar')) {
	add_action('premium_action_all_iconbar', 'def_premium_action_all_iconbar');
	function def_premium_action_all_iconbar() {
		
		$plugin = get_plugin_class();	

		_method('post');
			
		$form = new PremiumForm();
		$form->send_header();
			
		pn_only_caps(array('administrator'));
				
		$lists = apply_filters('_icon_indicators', array());	
		if (is_array($lists)) {
			foreach ($lists as $list_key => $list_title) {	
				$plugin->update_option('iconbar_dis', $list_key, intval(is_param_post($list_key)));	
			}
		}		

		$url = admin_url('options-general.php?page=all_iconbar&reply=true');
		$form->answer_form($url);
		
	}	
}	