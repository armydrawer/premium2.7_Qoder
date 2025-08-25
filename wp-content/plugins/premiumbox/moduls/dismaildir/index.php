<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Banned mail domains[:en_US][ru_RU:]Запрещенные почтовые домены[:ru_RU]
description: [en_US:]Banned mail domains[:en_US][ru_RU:]Запрещенные почтовые домены[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

add_filter('pn_config_option', 'dismaildir_pn_config_option');
function dismaildir_pn_config_option($options) {
	global $premiumbox;	
	
	$options['dismaildir_linetop'] = array(
		'view' => 'line',
	);	
	$options['dismaildir'] = array(
		'view' => 'textarea',
		'title' => __('Banned mail domains (in new line)', 'pn'),
		'default' => $premiumbox->get_option('dismaildir'),
		'rows' => '10',
		'name' => 'dismaildir',
		'work' => 'text',
	);		
	$options['dismaildir_linebot'] = array(
		'view' => 'line',
	);	
	
	return $options;	
}

add_action('pn_config_option_post', 'dismaildir_config_option_post');
function dismaildir_config_option_post() {
	global $premiumbox;	
	
	$dismaildir = pn_strip_text(is_param_post('dismaildir'));
	$premiumbox->update_option('dismaildir', '', $dismaildir);		
	
}

add_filter('error_bids', 'dismaildir_error_bids', 110, 4);  
function dismaildir_error_bids($error_bids, $direction, $vd1, $vd2) {
	global $wpdb, $premiumbox;	
		
	$disdomains = pn_strip_text($premiumbox->get_option('dismaildir'));
	$disdomains_arr = explode("\n", $disdomains);
	$disdomains_arr = array_map('trim', $disdomains_arr);
	$email_arr = explode('@', $error_bids['bid']['user_email']);
	$email_domain = trim(is_isset($email_arr, 1));
	if ($email_domain and in_array($email_domain, $disdomains_arr)) {	
	
		$error_bids['error_text'][] = __('Your e-mail is denied', 'pn');
		
	}

	return $error_bids;
}