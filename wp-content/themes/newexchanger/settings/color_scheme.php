<?php
if (!defined('ABSPATH')) { exit(); }

add_action('admin_menu', 'admin_menu_theme_color_scheme');
function admin_menu_theme_color_scheme() {
	
	$plugin = get_plugin_class();
	add_submenu_page("themes.php", __('Color scheme', 'pntheme'), __('Color scheme', 'pntheme'), 'administrator', "pn_theme_color_scheme", array($plugin, 'admin_temp'));
	
}

add_filter('pn_adminpage_title_pn_theme_color_scheme', 'def_adminpage_title_pn_theme_color_scheme');
function def_adminpage_title_pn_theme_color_scheme($page) {
	
	return __('Color scheme', 'pntheme');
} 

add_action('pn_adminpage_content_pn_theme_color_scheme', 'def_pn_adminpage_content_pn_theme_color_scheme');
function def_pn_adminpage_content_pn_theme_color_scheme() {
	
	$plugin = get_plugin_class();
	
	$options = array();
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Color scheme', 'pntheme'),
		'submit' => __('Save', 'pntheme'),
	);
	
	$options['color_scheme'] = array(
		'view' => 'select',
		'title' => __('Color scheme', 'pntheme'),
		'options' => array('color_l' => __('light', 'pntheme'), 'color_d' => __('dark', 'pntheme')),
		'default' => $plugin->get_option('color_scheme'),
		'name' => 'color_scheme',
		'work' => 'input',
	);
	$options['color_scheme_help'] = array(
		'view' => 'help',
		'title' => __('More info', 'pntheme'),
		'default' => sprintf(__('Find out more information about website appearance settings by <a href="%1$s" target="_blank">link</a>.', 'pntheme'), 'https://premiumexchanger.com/wiki/appearance-settings/'),
	);	

	$form = new PremiumForm();
	$params_form = array(
		'filter' => 'pn_theme_color_scheme_option',
	);
	$form->init_form($params_form, $options);
	
} 

add_action('premium_action_pn_theme_color_scheme', 'def_premium_action_pn_theme_color_scheme');
function def_premium_action_pn_theme_color_scheme() {	

	$plugin = get_plugin_class();

	_method('post');
	
	pn_only_caps(array('administrator'));

	$form = new PremiumForm();
	$form->send_header();
	
	$color_scheme = pn_strip_input(is_param_post('color_scheme'));
	$plugin->update_option('color_scheme', '', $color_scheme);			
		
	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
				
	$form->answer_form($back_url);	
	
}

add_filter('body_class', 'color_scheme_body_class');
function color_scheme_body_class($classes) {
	
	$plugin = get_plugin_class();
	$classes[] = $plugin->get_option('color_scheme');
	
	return $classes;
} 