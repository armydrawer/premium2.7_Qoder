<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('admin_menu_captcha')) {
	
	add_action('admin_menu', 'admin_menu_captcha');
	function admin_menu_captcha() {
		
		$plugin = get_plugin_class();
		add_submenu_page("options-general.php", __('Captcha', 'pn'), __('Captcha', 'pn'), 'administrator', "all_captcha", array($plugin, 'admin_temp'));
		
	}	
		
	add_filter('pn_adminpage_title_all_captcha', 'def_adminpage_title_all_captcha');
	function def_adminpage_title_all_captcha() {
		
		return __('Captcha', 'pn');
	}	
		
	add_action('pn_adminpage_content_all_captcha', 'def_pn_adminpage_content_all_captcha');
	function def_pn_adminpage_content_all_captcha() {
		
		$plugin = get_plugin_class();
			
		$form = new PremiumForm();	
			
		$options = array();	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Captcha', 'pn'),
			'submit' => __('Save', 'pn'),
		);	
			
		$placed = apply_filters('placed_form', array());	
		if (is_array($placed)) {
			foreach ($placed as $key => $title) {
				$now = intval($plugin->get_option('captcha', $key));
				$options[] = array(
					'view' => 'checkbox',
					'label' => $title,
					'value' => '1',
					'default' => $now,
					'name' => $key,
				);			
			}
		}
			
		$params_form = array(
			'filter' => 'all_captcha_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);		
	}

	add_action('premium_action_all_captcha', 'def_premium_action_all_captcha');
	function def_premium_action_all_captcha() {
		
		$plugin = get_plugin_class();	

		_method('post');
			
		$form = new PremiumForm();
		$form->send_header();			
			
		pn_only_caps(array('administrator'));

		$placed = apply_filters('placed_form', array());	
		if (is_array($placed)) {
			foreach ($placed as $key => $title) {	
				$plugin->update_option('captcha', $key, intval(is_param_post($key)));	
			}
		}		

		$back_url = is_param_post('_wp_http_referer');
		$back_url = add_query_args(array('reply' => 'true'), $back_url);
				
		$form->answer_form($back_url);
		
	}	
}