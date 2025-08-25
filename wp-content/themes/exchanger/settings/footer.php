<?php
if (!defined('ABSPATH')) { exit(); }

add_action('admin_menu', 'admin_menu_theme_footer');
function admin_menu_theme_footer() {
	
	$plugin = get_plugin_class();
	
	add_submenu_page("themes.php", __('Footer', 'pntheme'), __('Footer', 'pntheme'), 'administrator', "pn_theme_footer", array($plugin, 'admin_temp'));
	
}

add_filter('pn_adminpage_title_pn_theme_footer', 'def_adminpage_title_pn_theme_footer');
function def_adminpage_title_pn_theme_footer($page) {
	
	return __('Footer', 'pntheme');
} 

add_filter('pn_theme_footer_option', 'def_pn_theme_footer_option', 1);
function def_pn_theme_footer_option($options) {
	global $wpdb;

	$change = get_option('f_change');
	
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Footer', 'pntheme'),
		'submit' => __('Save', 'pntheme'),
	);
	$options['ctext'] = array(
		'view' => 'textarea',
		'title' => __('Copywriting', 'pntheme'),
		'default' => is_isset($change, 'ctext'),
		'name' => 'ctext',
		'rows' => '8',
		'work' => 'text',
		'ml' => 1,
	);
	$options['timetable'] = array(
		'view' => 'textarea',
		'title' => __('Timetable', 'pntheme'),
		'default' => is_isset($change, 'timetable'),
		'name' => 'timetable',
		'rows' => '8',
		'work' => 'text',
		'ml' => 1,
	);	
	$options['line1'] = array(
		'view' => 'line',
	);	
	$options['phone'] = array(
		'view' => 'inputbig',
		'title' => __('Phone', 'pntheme'),
		'default' => is_isset($change, 'phone'),
		'name' => 'phone',
		'work' => 'input',
		'ml' => 1,
	);
	$options['line2'] = array(
		'view' => 'line',
	);	
	$options['vk'] = array(
		'view' => 'inputbig',
		'title' => __('Link to Vk.com', 'pntheme'),
		'default' => is_isset($change, 'vk'),
		'name' => 'vk',
		'work' => 'input',
	);
	$options['fb'] = array(
		'view' => 'inputbig',
		'title' => __('Link to Facebook', 'pntheme'),
		'default' => is_isset($change, 'fb'),
		'name' => 'fb',
		'work' => 'input',
	);
	$options['tw'] = array(
		'view' => 'inputbig',
		'title' => __('Link to Twitter', 'pntheme'),
		'default' => is_isset($change, 'tw'),
		'name' => 'tw',
		'work' => 'input',
	);	
	$help = '
	<p>' . __('If you plan to use links as social buttons, use the following shortcode', 'pntheme') . '</p>
	<p><input type="text" name="" value="[soc_link]" onclick="this.select()" /></p>';
	$options['newpanel_help'] = array(
		'view' => 'help',
		'title' => __('Info', 'pntheme'),
		'default' => $help,
	);		
	
	return $options;
}

add_action('pn_adminpage_content_pn_theme_footer', 'def_pn_adminpage_content_pn_theme_footer');
function def_pn_adminpage_content_pn_theme_footer() {

	$form = new PremiumForm();
	$params_form = array(
		'filter' => 'pn_theme_footer_option',
	);
	$form->init_form($params_form);	
		
} 

add_action('premium_action_pn_theme_footer', 'def_premium_action_pn_theme_footer');
function def_premium_action_pn_theme_footer() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator'));
	
	$form = new PremiumForm();
	$form->send_header();
	
	$data = $form->strip_options('pn_theme_footer_option', 'post');

	$change = get_option('f_change');
	if (!is_array($change)) { $change = array(); }
	
	$change['ctext'] = $data['ctext']; 
	$change['timetable'] = $data['timetable']; 
	$change['phone'] = $data['phone']; 
	$change['vk'] = $data['vk'];
	$change['fb'] = $data['fb'];
	$change['tw'] = $data['tw'];	
	
	update_option('f_change', $change);
	
	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
			
	$form->answer_form($back_url);	
	
}