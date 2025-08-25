<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Initial request ID set up[:en_US][ru_RU:]Установка начального ID заявки[:ru_RU]
description: [en_US:]Initial request ID set up[:en_US][ru_RU:]Установка начального ID заявки[:ru_RU]
version: 2.7.0
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if (is_admin()) {

	add_action('admin_menu', 'admin_menu_setbidid', 100);
	function admin_menu_setbidid() {
		global $premiumbox;	
		
		add_submenu_page("pn_bids", __('Current order ID', 'pn'), __('Current order ID', 'pn'), 'administrator', "pn_setbidid", array($premiumbox, 'admin_temp'));
	}

	add_filter('pn_adminpage_title_pn_setbidid', 'def_adminpage_title_pn_setbidid');
	function def_adminpage_title_pn_setbidid($page) {
		
		return __('Current order ID', 'pn');
	} 

	add_action('pn_adminpage_content_pn_setbidid', 'def_adminpage_content_pn_setbidid');
	function def_adminpage_content_pn_setbidid() {
		global $wpdb;

		$form = new PremiumForm();
		
		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Current order ID', 'pn'),
			'submit' => __('Save', 'pn'),
		);		
		$options['new_id'] = array(
			'view' => 'input',
			'title' => __('Set new current order ID', 'pn'),
			'default' => '',
			'name' => 'new_id',
		);	
		$params_form = array(
			'form_link' => pn_link('pn_setbidid', 'post'),
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
	}  

}

add_action('premium_action_pn_setbidid', 'def_premium_action_pn_setbidid');
function def_premium_action_pn_setbidid() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator'));
		
	$new_id = intval(is_param_post('new_id'));
	if ($new_id > 0) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids AUTO_INCREMENT={$new_id};");
	}
		
	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
	$form->answer_form($back_url);
	
}	