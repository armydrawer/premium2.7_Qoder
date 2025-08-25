<?php
if (!defined('ABSPATH')) { exit(); }

add_action('admin_menu', 'pn_adminpage_theme_mobile');
function pn_adminpage_theme_mobile() {
	
	$plugin = get_plugin_class();
	
	add_submenu_page("themes.php", __('Mobile version', 'pntheme'), __('Mobile version', 'pntheme'), 'administrator', "pn_mobile_theme", array($plugin, 'admin_temp'));
	
}

add_filter('pn_adminpage_title_pn_mobile_theme', 'pn_adminpage_title_pn_mobile_theme');
function pn_adminpage_title_pn_mobile_theme($page) {
	
	return __('Mobile version', 'pntheme');
} 

add_filter('pn_mobile_theme_option', 'def_pn_mobile_theme_option', 1);
function def_pn_mobile_theme_option($options) {
	global $wpdb;

	$change = get_option('mobile_change');
	
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Mobile version', 'pntheme'),
		'submit' => __('Save', 'pntheme'),
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
	$options[] = array(
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
	$options[] = array(
		'view' => 'line',
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

	return $options;
}

add_action('pn_adminpage_content_pn_mobile_theme', 'def_pn_adminpage_content_pn_mobile_theme');
function def_pn_adminpage_content_pn_mobile_theme() {

	$form = new PremiumForm();
	$params_form = array(
		'filter' => 'pn_mobile_theme_option',
	);
	$form->init_form($params_form);	
		
} 

add_action('premium_action_pn_mobile_theme', 'def_premium_action_pn_mobile_theme');
function def_premium_action_pn_mobile_theme() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator'));

	$form = new PremiumForm();
	$form->send_header();
	
	$data = $form->strip_options('pn_mobile_theme_option', 'post');
	
	$change = get_option('mobile_change');
	if (!is_array($change)) { $change = array(); }
		
	$change['linkhead'] = $data['linkhead'];
	$change['hideloginbutton'] = $data['hideloginbutton'];
	$change['phone'] = $data['phone'];
	$change['icq'] = $data['icq'];
	$change['skype'] = $data['skype'];
	$change['email'] = $data['email'];
	$change['telegram'] = str_replace('@', '',  $data['telegram']);
	$change['viber'] = $data['viber'];
	$change['whatsapp'] = $data['whatsapp'];
	$change['jabber'] = $data['jabber'];
	$change['ctext'] = $data['ctext'];
	
	update_option('mobile_change', $change);
	
	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
			
	$form->answer_form($back_url);	
}

add_filter('all_themelogo_option', 'mobile_all_themelogo_option', 1);
function mobile_all_themelogo_option($options) {

	$plugin = get_plugin_class();
	
	$noptions = array(
		'mobilelogo' => array(
			'view' => 'uploader',
			'title' => __('Logo', 'pntheme') . ' (' . __('Mobile version', 'pntheme') . ')',
			'default' => $plugin->get_option('mobilelogo'),
			'name' => 'mobilelogo',
			'work' => 'input',
			'ml' => 1,
		)
	);	
	$options = pn_array_insert($options, 'logo', $noptions);
	
	$noptions = array(
		'mobiletextlogo' => array(
			'view' => 'inputbig',
			'title' => __('Text logo', 'pntheme') . ' (' . __('Mobile version', 'pntheme') . ')',
			'default' => $plugin->get_option('mobiletextlogo'),
			'name' => 'mobiletextlogo',
			'work' => 'input',
			'ml' => 1,
		)
	);	
	$options = pn_array_insert($options, 'textlogo', $noptions);
		
	return $options;
}

add_action('all_themelogo_option_post', 'mobile_all_themelogo_option_post');
function mobile_all_themelogo_option_post($data) {
	
	$plugin = get_plugin_class();
	$plugin->update_option('mobilelogo', '', $data['mobilelogo']);
	$plugin->update_option('mobiletextlogo', '', $data['mobiletextlogo']);
	
}

function get_mobile_logotype() {
	
	$plugin = get_plugin_class();
	
	return is_ssl_url(pn_strip_input(ctv_ml($plugin->get_option('mobilelogo'))));
}

function get_mobile_textlogo() {
	
	$plugin = get_plugin_class();
	
	return pn_strip_input(ctv_ml($plugin->get_option('mobiletextlogo')));
} 