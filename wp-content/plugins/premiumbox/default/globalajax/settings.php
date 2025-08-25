<?php
if (!defined('ABSPATH')) { exit(); }

add_action('admin_menu', 'admin_menu_ga_settings');
function admin_menu_ga_settings() {
	
	$plugin = get_plugin_class();
	add_submenu_page("options-general.php", __('AJAX settings', 'pn'), __('AJAX settings', 'pn'), 'administrator', "all_ga_settings", array($plugin, 'admin_temp'));
	
}

add_filter('pn_adminpage_title_all_ga_settings', 'def_adminpage_title_all_ga_settings');
function def_adminpage_title_all_ga_settings($page) {
	
	return __('AJAX settings', 'pn');
} 

add_filter('all_ga_settings_option', 'def_all_ga_settings_option', 1);
function def_all_ga_settings_option($options) {	
			
	$plugin = get_plugin_class();	
			
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('AJAX settings', 'pn'),
		'submit' => __('Save', 'pn'),
	);
	$options['ga_admin'] = array(
		'view' => 'select',
		'title' => __('AJAX checker for admin panel', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $plugin->get_option('ga', 'ga_admin'),
		'name' => 'ga_admin',
		'work' => 'int',
	);
	$options['admin_time'] = array(
		'view' => 'input',
		'title' => __('Frequency of requests from admin panel', 'pn') . ' (' . __('seconds', 'pn') . ')',
		'default' => $plugin->get_option('ga', 'admin_time'),
		'name' => 'admin_time',
		'work' => 'int',
	);	
	$options['line1'] = array(
		'view' => 'line',
	);	
	$options['ga_site'] = array(
		'view' => 'select',
		'title' => __('AJAX checker for website', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $plugin->get_option('ga', 'ga_site'),
		'name' => 'ga_site',
		'work' => 'int',
	);			
	$options['site_time'] = array(
		'view' => 'input',
		'title' => __('Frequency of requests from website', 'pn') . ' (' . __('seconds', 'pn') . ')',
		'default' => $plugin->get_option('ga', 'site_time'),
		'name' => 'site_time',
		'work' => 'int',
	);
	$options['globalajax_help'] = array(
		'view' => 'help',
		'title' => __('More info', 'pn'),
		'default' => __('This option is able to create an additional load on server', 'pn'),
	);		
			
	return $options;
}

add_action('pn_adminpage_content_all_ga_settings', 'def_pn_adminpage_content_all_ga_settings');
function def_pn_adminpage_content_all_ga_settings() {

	$form = new PremiumForm();
	$params_form = array(
		'filter' => 'all_ga_settings_option',
		'button_title' => __('Save', 'pn'),
	);
	$form->init_form($params_form);

} 

add_action('premium_action_all_ga_settings', 'def_premium_action_all_ga_settings');
function def_premium_action_all_ga_settings() {

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator'));
			
	$plugin = get_plugin_class();	
			
	$data = $form->strip_options('all_ga_settings_option', 'post');		
			
	foreach ($data as $key => $val) {
		$param = intval($data[$key]);
		$plugin->update_option('ga', $key, $param);
	}		
		
	do_action('all_ga_settings_option_post', $data);			
		
	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
				
	$form->answer_form($back_url);	
}