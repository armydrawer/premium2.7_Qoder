<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_settings_userwallets', 'def_adminpage_title_pn_settings_userwallets');
	function def_adminpage_title_pn_settings_userwallets() {
		
		return __('Settings', 'pn');
	} 

	add_action('pn_adminpage_content_pn_settings_userwallets', 'def_adminpage_content_pn_settings_userwallets');
	function def_adminpage_content_pn_settings_userwallets() {
		global $premiumbox;	
		
		$form = new PremiumForm();
		
		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);
		$options['acc_created'] = array(
			'view' => 'select',
			'title' => __('Create currency accounts when creating an order', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('usve', 'acc_created'),
			'name' => 'acc_created',
		);	
		$options['uniq'] = array(
			'view' => 'select',
			'title' => __('Prohibit adding account number if it has already been added other user', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('usve', 'uniq'),
			'name' => 'uniq',
		);
		$options['line1'] = array(
			'view' => 'line',
		);	
		$options['addacctext'] = array(
			'view' => 'editor',
			'title' => __('Text on the page for adding an account number', 'pn'),
			'default' => $premiumbox->get_option('usve', 'addacctext'),
			'formatting_tags' => 1,
			'rows' => '13',
			'name' => 'addacctext',
			'work' => 'text',
			'ml' => 1,
		);		
		
		$params_form = array(
			'filter' => 'pn_userwallets_settings_options',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
		
	} 

}

add_action('premium_action_pn_settings_userwallets', 'def_premium_action_pn_settings_userwallets');
function def_premium_action_pn_settings_userwallets() {
	global $premiumbox;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_userwallets'));

	$options = array('acc_created', 'uniq');
	foreach ($options as $key) {
		$val = intval(is_param_post($key));
		$premiumbox->update_option('usve', $key, $val);
	}
	
	$options = array('addacctext');
	foreach ($options as $key) {
		$val = pn_strip_text(is_param_post_ml($key));
		$premiumbox->update_option('usve', $key, $val);
	}	
				
	do_action('pn_userwallets_settings_options_post');
				
	$url = admin_url('admin.php?page=pn_settings_userwallets&reply=true');
	$form->answer_form($url);
	
}