<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('admin_menu_contactform')) {
	add_action('admin_menu', 'admin_menu_contactform', 100);
	function admin_menu_contactform() {
		
		$plugin = get_plugin_class();
		if (current_user_can('administrator')) {
			add_submenu_page("options-general.php", __('Contact form', 'pn'), __('Contact form', 'pn'), 'administrator', "all_contactform", array($plugin, 'admin_temp'));
		}
		
	}
}

if (!function_exists('def_adminpage_title_all_contactform')) {
	add_filter('pn_adminpage_title_all_contactform', 'def_adminpage_title_all_contactform');
	function def_adminpage_title_all_contactform() {
		
		return __('Contact form settings', 'pn');
	}
}

if (!function_exists('def_adminpage_content_all_contactform')) {
	add_action('pn_adminpage_content_all_contactform', 'def_adminpage_content_all_contactform');
	function def_adminpage_content_all_contactform() {
		
		$plugin = get_plugin_class();
			
		$form = new PremiumForm();
			
		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);
		
		$options['stopwords'] = array(
			'view' => 'editor',
			'title' => __('Stop words', 'pn'),
			'default' => $plugin->get_option('contactform', 'stopwords'),
			'name' => 'stopwords',
			'rows' => '5',
		);
		$options['blacklist'] = array(
			'view' => 'editor',
			'title' => __('Black list (in new line)', 'pn'),
			'default' => $plugin->get_option('contactform', 'blacklist'),
			'name' => 'blacklist',
			'rows' => '20',
		);		
		$options['disdomains'] = array(
			'view' => 'editor',
			'title' => __('Disable e-mail domains (in new line)', 'pn'),
			'default' => $plugin->get_option('contactform', 'disdomains'),
			'name' => 'disdomains',
			'rows' => '10',
		);		
		$options['disabledip'] = array(
			'view' => 'editor',
			'title' => __('Disabled ip (in new line)', 'pn'),
			'default' => $plugin->get_option('contactform', 'disabledip'),
			'name' => 'disabledip',
			'rows' => '10',
		);			

		$params_form = array(
			'filter' => 'all_contactform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
	}
}

if (!function_exists('def_premium_action_all_contactform')) {
	add_action('premium_action_all_contactform', 'def_premium_action_all_contactform');
	function def_premium_action_all_contactform() {
		global $wpdb;	

		$plugin = get_plugin_class();
			
		_method('post');
			
		$form = new PremiumForm();
		$form->send_header();
			
		pn_only_caps(array('administrator'));
				
		$options = array('stopwords', 'blacklist', 'disdomains', 'disabledip');	
		foreach ($options as $key) {
			$val = pn_strip_input(is_param_post($key));
			$plugin->update_option('contactform', $key, $val);
		}
						
		do_action('all_contactform_post');
					
		$url = admin_url('admin.php?page=all_contactform&reply=true');
		$form->answer_form($url);
		
	}
}	