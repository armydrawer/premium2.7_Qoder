<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('admin_menu_rtl')) {

	add_action('admin_menu', 'admin_menu_rtl');
	function admin_menu_rtl() {
		
		$plugin = get_plugin_class();
		add_submenu_page("options-general.php", __('Writing settings', 'pn'), __('Writing settings', 'pn'), 'administrator', "all_rtl", array($plugin, 'admin_temp'));
		
	}

	add_filter('pn_adminpage_title_all_rtl', 'def_pn_adminpage_title_all_rtl');
	function def_pn_adminpage_title_all_rtl($page) {
		
		return __('Writing settings', 'pn');
	}

	add_filter('all_rtl_option', 'def_all_rtl_option', 1);
	function def_all_rtl_option($options) {

		$langs = get_site_langs();
		
		$lang = get_option('pn_lang');
		if (!is_array($lang)) { $lang = array(); }
		
		$rtl = is_isset($lang, 'rtl');
		if (!is_array($rtl)) { $rtl = array(); }
		
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Writing settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);	
		foreach ($langs as $la_key => $la_title) {
			$options[$la_key] = array(
				'view' => 'select',
				'title' => sprintf(__('Writing setting for language "%s"', 'pn'), $la_title),
				'options' => array('ltr' => 'LTR', 'rtl' => 'RTL'),
				'default' => is_isset($rtl, $la_key),
				'name' => $la_key,
				'work' => 'input',
			);
		}		
		
		return $options;
	}	
	
	add_action('pn_adminpage_content_all_rtl', 'def_pn_adminpage_content_all_rtl');
	function def_pn_adminpage_content_all_rtl() {
		
		$form = new PremiumForm();
		$params_form = array(
			'filter' => 'all_rtl_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form);		
		
	}   

	add_action('premium_action_all_rtl','def_premium_action_all_rtl');
	function def_premium_action_all_rtl() {

		_method('post');
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));

		$langs = get_site_langs();
		
		$data = $form->strip_options('all_rtl_option', 'post');
				
		$lang = get_option('pn_lang');
		if (!is_array($lang)) { $lang = array(); }
		foreach ($langs as $la_key => $la_title) {
			$lang['rtl'][$la_key] = $data[$la_key];
		}
		update_option('pn_lang',$lang);
				
		do_action('all_rtl_option_post', $data);			
				
		$back_url = is_param_post('_wp_http_referer');
		$back_url = add_query_args(array('reply' => 'true'), $back_url);

		$form->answer_form($back_url);
		
	} 	
}