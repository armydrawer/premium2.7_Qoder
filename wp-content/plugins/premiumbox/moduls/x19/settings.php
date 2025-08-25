<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_x19_settings', 'def_adminpage_title_pn_x19_settings');
	function def_adminpage_title_pn_x19_settings($page) {
		
		return __('X19 settings', 'pn');
	} 

	add_action('pn_adminpage_content_pn_x19_settings', 'def_adminpage_content_pn_x19_settings');
	function def_adminpage_content_pn_x19_settings() {
		global $wpdb, $premiumbox;
		
		$form = new PremiumForm();
		
		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('X19 settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);
		$options['wmid'] = array(
			'view' => 'inputbig',
			'title' => __('WMID', 'pn'),
			'default' => $premiumbox->get_option('x19', 'wmid'),
			'name' => 'wmid',
		);
		$options['logs'] = array(
			'view' => 'select',
			'title' => __('Write logs', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('x19', 'logs'),
			'name' => 'logs',
		);
		$options['type'] = array(
			'view' => 'select',
			'title' => __('Type', 'pn'),
			'options' => array('0' => __('From file', 'pn'), '1' => __('CLASSIC', 'pn'), '2' => __('LIGHT', 'pn')),
			'default' => $premiumbox->get_option('x19', 'type'),
			'name' => 'type',
		);
		$options['classic_title'] = array(
			'view' => 'h3',
			'title' => __('CLASSIC settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);
		$options['clkey'] = array(
			'view' => 'inputbig',
			'title' => __('Absolute path to the key .kwm file', 'pn'),
			'default' => $premiumbox->get_option('x19', 'clkey'),
			'name' => 'clkey',
		);
		$text = '
		<div>{DIR_PATH}moduls/x19/webmoney/write here/Write here.kwm</div>
		';
		$options['help1'] = array(
			'view' => 'help',
			'title' => __('Example', 'pn'),
			'default' => $text,
		);
		$options['clpass'] = array(
			'view' => 'inputbig',
			'title' => __('Password file from .kwm keys', 'pn'),
			'default' => premium_decrypt($premiumbox->get_option('x19', 'clpass'), EXT_SALT),
			'name' => 'clpass',
			'atts' => array('type' => 'password'),
		);
		$options['ligth_title'] = array(
			'view' => 'h3',
			'title' => __('LIGHT settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);	
		$options['ltcert'] = array(
			'view' => 'inputbig',
			'title' => __('Absolute path to the certificate .cer. ONLY for WM Keeper Light WebPro (Light)', 'pn'),
			'default' => $premiumbox->get_option('x19', 'ltcert'),
			'name' => 'ltcert',
		);
		$text = '
		<div>{DIR_PATH}moduls/x19/webmoney/write here/Write here.cer</div>
		';
		$options['help2'] = array(
			'view' => 'help',
			'title' => __('Example', 'pn'),
			'default' => $text,
		);
		$options['ltkey'] = array(
			'view' => 'inputbig',
			'title' => __('Absolute path to the private key .key. ONLY for WM Keeper Light WebPro (Light)', 'pn'),
			'default' => $premiumbox->get_option('x19', 'ltkey'),
			'name' => 'ltkey',
		);
		$text = '
		<div>{DIR_PATH}moduls/x19/webmoney/write here/Write here.key</div>
		';
		$options['help3'] = array(
			'view' => 'help',
			'title' => __('Example', 'pn'),
			'default' => $text,
		);
		$options['ltpass'] = array(
			'view' => 'inputbig',
			'title' => __('Password for the private key .key. ONLY for WM Keeper Light WebPro (Light)', 'pn'),
			'default' => premium_decrypt($premiumbox->get_option('x19','ltpass'), EXT_SALT),
			'name' => 'ltpass',
			'atts' => array('type' => 'password'),
		);
		$params_form = array(
			'filter' => 'x19_settings_options',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
		
	}  

}

add_action('premium_action_pn_x19_settings', 'def_premium_action_pn_x19_settings');
function def_premium_action_pn_x19_settings() {
	global $wpdb, $premiumbox;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator'));
		
	$premiumbox->update_option('x19', 'wmid', pn_strip_input(is_param_post('wmid')));	
	$premiumbox->update_option('x19', 'type', intval(is_param_post('type')));
	$premiumbox->update_option('x19', 'logs', intval(is_param_post('logs')));

	$premiumbox->update_option('x19', 'clkey', pn_strip_input(is_param_post('clkey')));
	$premiumbox->update_option('x19', 'clpass', premium_encrypt(pn_strip_input(is_param_post('clpass')), EXT_SALT));
	$premiumbox->update_option('x19', 'ltcert', pn_strip_input(is_param_post('ltcert')));
	$premiumbox->update_option('x19', 'ltkey', pn_strip_input(is_param_post('ltkey')));
	$premiumbox->update_option('x19', 'ltpass', premium_encrypt(pn_strip_input(is_param_post('ltpass')), EXT_SALT));

	$url = admin_url('admin.php?page=pn_x19_settings&reply=true');
	$form->answer_form($url);
	
}