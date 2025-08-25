<?php
if (!defined('ABSPATH')) { exit(); }

add_action('admin_menu', 'admin_menu_theme_header');
function admin_menu_theme_header() {
	
	$plugin = get_plugin_class();
	
	add_submenu_page("themes.php", __('Header', 'pntheme'), __('Header', 'pntheme'), 'administrator', "pn_theme_header", array($plugin, 'admin_temp'));
	
}

add_filter('pn_adminpage_title_pn_theme_header', 'def_adminpage_title_pn_theme_header');
function def_adminpage_title_pn_theme_header($page) {
	
	return __('Header', 'pntheme');
} 
 
add_filter('pn_theme_header_option', 'def_pn_theme_header_option', 1);
function def_pn_theme_header_option($options) {	
		
	$change = get_option('h_change');
	
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Header', 'pntheme'),
		'submit' => __('Save', 'pntheme'),
	);
	$options['fixheader'] = array(
		'view' => 'select',
		'title' => __('To fix', 'pntheme'),
		'options' => array('0' => __('nothing', 'pntheme'), '1' => __('bar', 'pntheme')),
		'default' => is_isset($change, 'fixheader'),
		'name' => 'fixheader',
		'work' => 'int',
	);	
	$options['linkhead'] = array(
		'view' => 'select',
		'title' => __('Logo link', 'pntheme'),
		'options' => array('0' => __('always', 'pntheme'), '1' => __('with the exception of homepage', 'pntheme')),
		'default' => is_isset($change, 'linkhead'),
		'name' => 'linkhead',
		'work' => 'int',
	);
	$options['hideloginbutton'] = array(
		'view' => 'select',
		'title' => __('Hide login/register link', 'pntheme'),
		'options' => array('0' => __('No', 'pntheme'), '1' => __('Yes', 'pntheme')),
		'default' => is_isset($change, 'hideloginbutton'),
		'name' => 'hideloginbutton',
		'work' => 'int',
	);	
	$options['line1'] = array(
		'view' => 'line',
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
	$options['line2'] = array(
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
	$options['icq'] = array(
		'view' => 'inputbig',
		'title' => __('ICQ', 'pntheme'),
		'default' => is_isset($change, 'icq'),
		'name' => 'icq',
		'work' => 'input',
		'ml' => 1,
	);
	$options['skype'] = array(
		'view' => 'inputbig',
		'title' => __('Skype', 'pntheme'),
		'default' => is_isset($change, 'skype'),
		'name' => 'skype',
		'work' => 'input',
		'ml' => 1,
	);
	$options['email'] = array(
		'view' => 'inputbig',
		'title' => __('E-mail', 'pntheme'),
		'default' => is_isset($change, 'email'),
		'name' => 'email',
		'work' => 'input',
		'ml' => 1,
	);
	$options['telegram'] = array(
		'view' => 'inputbig',
		'title' => __('Telegram', 'pntheme'),
		'default' => is_isset($change, 'telegram'),
		'name' => 'telegram',
		'work' => 'input',
		'ml' => 1,
	);
	$options['viber'] = array(
		'view' => 'inputbig',
		'title' => __('Viber', 'pntheme'),
		'default' => is_isset($change, 'viber'),
		'name' => 'viber',
		'work' => 'input',
		'ml' => 1,
	);
	$options['whatsapp'] = array(
		'view' => 'inputbig',
		'title' => __('WhatsApp', 'pntheme'),
		'default' => is_isset($change, 'whatsapp'),
		'name' => 'whatsapp',
		'work' => 'input',
		'ml' => 1,
	);
	$options['jabber'] = array(
		'view' => 'inputbig',
		'title' => __('Jabber', 'pntheme'),
		'default' => is_isset($change, 'jabber'),
		'name' => 'jabber',
		'work' => 'input',
		'ml' => 1,
	);			
	
	return $options;
}

add_action('pn_adminpage_content_pn_theme_header', 'def_pn_adminpage_content_pn_theme_header');
function def_pn_adminpage_content_pn_theme_header() {
	
	$form = new PremiumForm();
	$params_form = array(
		'filter' => 'pn_theme_header_option',
	);
	$form->init_form($params_form);
	
} 

add_action('premium_action_pn_theme_header', 'def_premium_action_pn_theme_header');
function def_premium_action_pn_theme_header() {

	_method('post');
	
	pn_only_caps(array('administrator'));

	$form = new PremiumForm();
	$form->send_header();
	
	$data = $form->strip_options('pn_theme_header_option', 'post');
		
	$change = get_option('h_change');
	if (!is_array($change)) { $change = array(); }
	
	$change['fixheader'] = $data['fixheader']; 	
	$change['linkhead'] = $data['linkhead'];
	$change['hideloginbutton'] = $data['hideloginbutton'];
	$change['timetable'] = $data['timetable'];			
	$change['phone'] = $data['phone'];
	$change['icq'] = $data['icq'];
	$change['skype'] = $data['skype'];
	$change['email'] = $data['email'];
	$change['telegram'] = str_replace('@', '', $data['telegram']);
	$change['viber'] = $data['viber'];
	$change['whatsapp'] = $data['whatsapp'];
	$change['jabber'] = $data['jabber'];
	
	update_option('h_change', $change);					
		
	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
				
	$form->answer_form($back_url);	
		
}