<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('get_logs_sett')) {
	function get_logs_sett($key) {
		
		$plugin = get_plugin_class();
		$lists = apply_filters('list_logs_settings', array());
		if (isset($lists[$key])) {
			$list_data = $lists[$key];
			$minimum = intval(is_isset($list_data, 'minimum'));
			$count = intval(is_isset($list_data, 'count'));
			$set = intval($plugin->get_option('logssettings', $key));
			if ($set < $minimum) { $set = $count; }
			
			return $set;
		}
		
		return 0;
	} 
}

if (!function_exists('admin_menu_logssettings')) {
	
	add_action('admin_menu', 'admin_menu_logssettings');
	function admin_menu_logssettings() {
		
		$plugin = get_plugin_class();
		add_submenu_page("options-general.php", __('Logging settings', 'pn'), __('Logging settings', 'pn'), 'administrator', "all_logs_settings", array($plugin, 'admin_temp'));
		
	}	
		
	add_filter('pn_adminpage_title_all_logs_settings', 'def_adminpage_title_all_logs_settings');
	function def_adminpage_title_all_logs_settings() {
		
		return __('Logging settings', 'pn');
	}	
		
	add_action('pn_adminpage_content_all_logs_settings', 'def_pn_adminpage_content_all_logs_settings');
	function def_pn_adminpage_content_all_logs_settings() {
		
		$plugin = get_plugin_class();

		$options = array();	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Logging settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);	
		
		$lists = apply_filters('list_logs_settings', array());	
		if (is_array($lists)) {
			foreach ($lists as $list_key => $list_data) {
				$minimum = intval(is_isset($list_data, 'minimum'));
				$default = get_logs_sett($list_key);
				
				$options[$list_key] = array(
					'view' => 'input',
					'title' => is_isset($list_data, 'title') . ' (' . __('min.', 'pn') . ':' . $minimum . ')',
					'default' => $default,
					'name' => $list_key,				
				);			
			}
		}
		
		$form = new PremiumForm();
		$params_form = array(
			'filter' => 'all_logs_settings_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
		
	}

	add_action('premium_action_all_logs_settings', 'def_premium_action_all_logs_settings');
	function def_premium_action_all_logs_settings() {

		_method('post');
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
		
		$plugin = get_plugin_class();
		
		$lists = apply_filters('list_logs_settings', array());	
		if (is_array($lists)) {
			foreach ($lists as $list_key => $list_data) {
				$minimum = intval(is_isset($list_data, 'minimum'));
				$now = intval(is_param_post($list_key));
				if ($now < $minimum) { $now = $minimum; }
				$plugin->update_option('logssettings', $list_key, $now);
			}
		}	

		$back_url = is_param_post('_wp_http_referer');
		$back_url = add_query_args(array('reply' => 'true'), $back_url);
				
		$form->answer_form($back_url);
		
	}	
	
}