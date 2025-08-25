<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('get_user_fields')) {

	function get_user_fields() {
		
		$list = array(	
			'last_name' => array(
				'title' => __('Last name', 'pn'),
			),
			'first_name' => array(
				'title' => __('First name', 'pn'),
			),			
			'second_name' => array(
				'title' => __('Second name', 'pn'),
			),
			'user_email' => array(
				'title' => __('E-mail', 'pn'),
				'in' => 1,
				'unique' => 1,
			),	
			'user_phone' => array(
				'title' => __('Mobile phone number', 'pn'),
			),
			'user_skype' => array(
				'title' => __('Skype', 'pn'),
			),
			'user_telegram' => array(
				'title' => __('Telegram', 'pn'),
			),
			'user_website' => array(
				'title' => __('Website', 'pn'),
			),
			'user_passport' => array(
				'title' => __('Passport number', 'pn'),
			),			
		);
		
		return apply_filters('user_fields', $list);	
	}

	add_filter('pn_adminpage_title_all_uf_settings', 'def_adminpage_title_all_uf_settings');
	function def_adminpage_title_all_uf_settings($page) {
		
		return __('User profile settings', 'pn');
	} 

	add_action('pn_adminpage_content_all_uf_settings', 'def_pn_adminpage_content_all_uf_settings');
	function def_pn_adminpage_content_all_uf_settings() {
		
		$plugin = get_plugin_class();

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Displaying fields on website', 'pn'),
			'submit' => __('Save', 'pn'),
		);
		
		$fields = get_user_fields();
		
		$uf = $plugin->get_option('user_fields');
		
		foreach ($fields as $field_key => $field_val) {
			$options[$field_key] = array(
				'view' => 'checkbox',
				'label' => sprintf(__('Display "%s" field', 'pn'), is_isset($field_val, 'title')),
				'value' => '1',
				'default' => is_isset($uf, $field_key),
				'name' => $field_key,
			);
		}
		
		$options['center_title'] = array(
			'view' => 'h3',
			'title' => __('Editing fields on website', 'pn'),
			'submit' => __('Save', 'pn'),
		);		
		
		$ufc = $plugin->get_option('user_fields_change');	
		
		foreach ($fields as $field_key => $field_val) {
			$options['ch_' . $field_key] = array(
				'view' => 'checkbox',
				'label' => sprintf(__('Allow user to change "%s" field contents', 'pn'), is_isset($field_val, 'title')),
				'value' => '1',
				'default' => is_isset($ufc, $field_key),
				'name' => 'ch_' . $field_key,
			);
		}	
		
		$form = new PremiumForm();
		$params_form = array(
			'filter' => 'all_usersettings_config_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
		
	} 

	add_action('premium_action_all_uf_settings', 'def_premium_action_all_uf_settings');
	function def_premium_action_all_uf_settings() {
		
		$plugin = get_plugin_class();

		_method('post');
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
		
		$fields = get_user_fields();
		
		$fields1 = $fields2 = array();
		
		foreach ($fields as $k => $v) {
			$val = intval(is_param_post($k));
			if ($val) {
				$fields1[$k] = $val;
			}
		}

		foreach ($fields as $k => $v) {
			$val = intval(is_param_post('ch_' . $k));
			if ($val) {
				$fields2[$k] = $val;
			}
		}	
		
		$plugin->update_option('user_fields', '', $fields1);
		$plugin->update_option('user_fields_change', '', $fields2);
		
		$back_url = is_param_post('_wp_http_referer');
		$back_url = add_query_args(array('reply' => 'true'), $back_url);
			
		$form->answer_form($back_url);
		
	}
}