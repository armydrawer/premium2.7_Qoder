<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Additional rules[:en_US][ru_RU:]Дополнительные правила[:ru_RU]
description: [en_US:]Additional rules[:en_US][ru_RU:]Дополнительные правила[:ru_RU]
version: 2.7.0
category: [en_US:]Users[:en_US][ru_RU:]Пользователи[:ru_RU]
cat: user
*/

add_filter('pn_config_option', 'addrules_pn_config_option');
function addrules_pn_config_option($options) {
	global $premiumbox;	
	
	$n_options = array();
	$n_options['addrules'] = array(
		'view' => 'textarea',
		'title' => __('Additional rules', 'pn'),
		'default' => $premiumbox->get_option('addrules'),
		'rows' => '10',
		'name' => 'addrules',
		'work' => 'text',
		'ml' => 1,
	);
	$n_options['addrules_error'] = array(
		'view' => 'textarea',
		'title' => __('Additional rules (error)', 'pn'),
		'default' => $premiumbox->get_option('addrules_error'),
		'rows' => '10',
		'name' => 'addrules_error',
		'work' => 'text',
		'ml' => 1,
	);		
	$n_options['addrules_linebot'] = array(
		'view' => 'line',
	);	
	$options = pn_array_insert($options, 'tostext', $n_options, 'after'); 
	
	return $options;	
}

add_action('pn_config_option_post', 'addrules_config_option_post');
function addrules_config_option_post() {
	global $premiumbox;	
	
	$addrules = pn_strip_text(is_param_post_ml('addrules'));
	$premiumbox->update_option('addrules', '', $addrules);

	$addrules_error = pn_strip_text(is_param_post_ml('addrules_error'));
	$premiumbox->update_option('addrules_error', '', $addrules_error);	
	
}

add_filter('exchange_check_filter', 'addrules_exchange_check_filter', 150);
function addrules_exchange_check_filter($check) {
	
	$plugin = get_plugin_class();
	$add_rules = pn_strip_text(ctv_ml($plugin->get_option('addrules')));

	if (strlen($add_rules) > 0) {
		$check .= '
		<div class="exchange_checkpersdata">
			<label><input type="checkbox" name="add_rules" autocomplete="off" value="1" /> ' . $add_rules . '</label>
		</div>
		';	
	}
		
	return $check;
}

add_filter('before_ajax_form','before_ajax_form_addrules', 990, 2);
function before_ajax_form_addrules($logs, $name) {
	
	$plugin = get_plugin_class();	
	if ('exchangeform' == $name) {
		$add_rules = pn_strip_text(ctv_ml($plugin->get_option('addrules')));
		if (strlen($add_rules) > 0) {
			$add_rules_post = intval(is_param_post('add_rules'));
			if (!$add_rules_post) { 
				$error_text = pn_strip_text(ctv_ml($plugin->get_option('addrules_error')));
				if (strlen($error_text) < 1) {
					$error_text = sprintf(__('Error! You have not accepted "%s"', 'pn'), $add_rules);
				}				
				$logs['status']	= 'error';
				$logs['status_code'] = '1'; 
				$logs['status_text'] = $error_text;
			}
		}	
	}
		
	return $logs;
}