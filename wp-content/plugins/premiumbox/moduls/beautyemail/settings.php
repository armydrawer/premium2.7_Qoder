<?php
if (!defined('ABSPATH')) { exit(); }

add_action('admin_menu', 'admin_menu_beautyemail');
function admin_menu_beautyemail() {
	
	$plugin = get_plugin_class();
	add_submenu_page("all_mail_temps", __('E-mail settings', 'pn'), __('E-mail settings', 'pn'), 'administrator', "pn_beautyemail", array($plugin, 'admin_temp'));
	
}

add_filter('pn_adminpage_title_pn_beautyemail', 'def_adminpage_title_pn_beautyemail');
function def_adminpage_title_pn_beautyemail($page) {
	
	return __('E-mail settings', 'pn');
} 

add_filter('beautyemail_option', 'def_beautyemail_option', 1);
function def_beautyemail_option($options) {

	$change = get_option('e_change');
	
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('E-mail settings', 'pn'),
		'submit' => __('Save', 'pn'),
	);
	$options['logo'] = array(
		'view' => 'uploader',
		'title' => __('Logo', 'pn'),
		'default' => is_isset($change, 'logo'),
		'name' => 'logo',
		'work' => 'input',
		'ml' => 1,
	);	
	$options['textlogo'] = array(
		'view' => 'inputbig',
		'title' => __('Text logo', 'pn'),
		'default' => is_isset($change, 'textlogo'),
		'name' => 'textlogo',
		'work' => 'input',
		'ml' => 1,
	);	
	$options['phone'] = array(
		'view' => 'inputbig',
		'title' => __('Phone', 'pn'),
		'default' => is_isset($change, 'phone'),
		'name' => 'phone',
		'work' => 'input',
		'ml' => 1,
	);
	$options['icq'] = array(
		'view' => 'inputbig',
		'title' => __('ICQ', 'pn'),
		'default' => is_isset($change, 'icq'),
		'name' => 'icq',
		'work' => 'input',
		'ml' => 1,
	);
	$options['skype'] = array(
		'view' => 'inputbig',
		'title' => __('Skype', 'pn'),
		'default' => is_isset($change, 'skype'),
		'name' => 'skype',
		'work' => 'input',
		'ml' => 1,
	);
	$options['email'] = array(
		'view' => 'inputbig',
		'title' => __('E-mail', 'pn'),
		'default' => is_isset($change, 'email'),
		'name' => 'email',
		'work' => 'input',
		'ml' => 1,
	);
	$options['telegram'] = array(
		'view' => 'inputbig',
		'title' => __('Telegram', 'pn'),
		'default' => is_isset($change, 'telegram'),
		'name' => 'telegram',
		'work' => 'input',
		'ml' => 1,
	);
	$options['viber'] = array(
		'view' => 'inputbig',
		'title' => __('Viber', 'pn'),
		'default' => is_isset($change, 'viber'),
		'name' => 'viber',
		'work' => 'input',
		'ml' => 1,
	);
	$options['whatsapp'] = array(
		'view' => 'inputbig',
		'title' => __('WhatsApp', 'pn'),
		'default' => is_isset($change, 'whatsapp'),
		'name' => 'whatsapp',
		'work' => 'input',
		'ml' => 1,
	);
	$options['jabber'] = array(
		'view' => 'inputbig',
		'title' => __('Jabber', 'pn'),
		'default' => is_isset($change, 'jabber'),
		'name' => 'jabber',
		'work' => 'input',
		'ml' => 1,
	);
	$options['ctext'] = array(
		'view' => 'textarea',
		'title' => __('Copywriting', 'pn'),
		'default' => is_isset($change, 'ctext'),
		'name' => 'ctext',
		'rows' => '8',
		'work' => 'text',
		'ml' => 1,
	);			
	
	return $options;
}

add_action('pn_adminpage_content_pn_beautyemail', 'def_pn_adminpage_content_pn_beautyemail');
function def_pn_adminpage_content_pn_beautyemail() {
	
	$form = new PremiumForm();
	$params_form = array(
		'filter' => 'beautyemail_option',
	);
	$form->init_form($params_form);
	
} 

add_action('premium_action_pn_beautyemail', 'def_premium_action_pn_beautyemail');
function def_premium_action_pn_beautyemail() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator'));

	$form = new PremiumForm();
	$form->send_header();
	
	$data = $form->strip_options('beautyemail_option', 'post');
		
	$change = get_option('e_change');
	if (!is_array($change)) { $change = array(); }
	
	$change['textlogo'] = $data['textlogo'];
	$change['logo'] = $data['logo'];
	$change['ctext'] = $data['ctext'];		
	$change['phone'] = $data['phone'];
	$change['icq'] = $data['icq'];
	$change['skype'] = $data['skype'];
	$change['email'] = $data['email'];
	$change['telegram'] = str_replace('@', '', $data['telegram']);
	$change['viber'] = $data['viber'];
	$change['whatsapp'] = $data['whatsapp'];
	$change['jabber'] = $data['jabber'];
	
	update_option('e_change',$change);					
		
	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
				
	$form->answer_form($back_url);	
		
}