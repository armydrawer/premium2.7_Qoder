<?php
if (!defined('ABSPATH')) { exit(); } 

/*
title: [en_US:]!Do not activate without any reason! Coupon code given during automatic payout[:en_US][ru_RU:]!Не активируйте без необходимости! Код купона при автовыплате[:ru_RU]
description: [en_US:]!Do not activate it without any reason! Show automatic payout coupon code in request form.[:en_US][ru_RU:]!Не активируйте без необходимости! Отображение кода купона автовыплаты в карточке заявки[:ru_RU]
version: 2.7.0
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

/* BD */
add_action('all_moduls_active_' . $name, 'all_moduls_active_fixbtccode');
function all_moduls_active_fixbtccode() {
	global $wpdb;	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'btc_code'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `btc_code` varchar(250) NOT NULL");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'btc_code_info'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `btc_code_info` varchar(250) NOT NULL");
    }	
	
}
/* end BD */

add_filter('pn_caps', 'fixbtccode_pn_caps');
function fixbtccode_pn_caps($pn_caps) {
	
	$pn_caps['pn_fixbtccode'] = __('Show coupon code for order in control panel', 'pn');
	
	return $pn_caps;
}

add_action('merchant_create_coupon', 'merchant_create_coupon_fixbtccode', 10, 4);
function merchant_create_coupon_fixbtccode($coupon_data, $item, $merchant, $place) {
	global $wpdb;
	
	if (isset($item->id) and is_array($coupon_data)) {
		$bid_id = $item->id;
		$array = array();
		$array['btc_code'] = is_isset($coupon_data, 'coupon');
		$array['btc_code_info'] = is_isset($coupon_data, 'coupon_code');
		$wpdb->update($wpdb->prefix . "exchange_bids", $array, array('id' => $bid_id));
	}
	
}

add_filter('onebid_col1', 'onebid_col1_fixbtccode', 99, 3);
function onebid_col1_fixbtccode($actions, $item, $v) {
	
	if (isset($item->btc_code) and $item->btc_code) {
		$btc_code = '---';
		if (current_user_can('administrator') or current_user_can('pn_fixbtccode')) {
			$btc_code = $item->btc_code;
		}	
		$actions['fixbtccode'] = array(
			'type' => 'text',
			'title' => __('Coupon code', 'pn'),
			'label' => $btc_code,
		);		
	}
	if (isset($item->btc_code_info) and $item->btc_code_info) {
		$btc_code_info = '---';
		if (current_user_can('administrator') or current_user_can('pn_fixbtccode')) {
			$btc_code_info = $item->btc_code_info;
		}		
		$actions['fixbtccodeinfo'] = array(
			'type' => 'text',
			'title' => __('Coupon info', 'pn'),
			'label' => $btc_code_info,
		);		
	}	
	
	return $actions;
}

add_filter('pn_config_option', 'fixbtccode_pn_config_option');
function fixbtccode_pn_config_option($options) {
	global $premiumbox;	
	
	$options['fixbtccode'] = array(
		'view' => 'select',
		'title' => __('Display coupon code in exchange history of user', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'fixbtccode'),
		'name' => 'fixbtccode',
	);
	
	return $options;
}

add_action('pn_config_option_post', 'fixbtccode_pn_config_option_post');
function fixbtccode_pn_config_option_post() {
	global $premiumbox;
	
	$options = array('fixbtccode');
	foreach ($options as $key) {
		$val = intval(is_param_post($key));
		$premiumbox->update_option('exchange', $key, $val);
	}
	 
}

add_filter('direction_instruction_tags', 'fixbtccode_directions_tags', 10, 2); 
function fixbtccode_directions_tags($tags, $key) {
	
	$in_page = array('description_txt', 'timeline_txt', 'window_txt', 'frozen_txt');
	if (!in_array($key, $in_page)) {
		$tags['coupon_code'] = array(
			'title' => __('Coupon code', 'pn'),
			'start' => '[coupon_code]',
		);
		$tags['coupon_info'] = array(
			'title' => __('Coupon info', 'pn'),
			'start' => '[coupon_info]',
		);		
	}
	
	return $tags;
}

add_filter('direction_instruction', 'fixbtccode_direction_instruction', 10, 5);
function fixbtccode_direction_instruction($instruction, $txt_name, $direction, $vd1, $vd2) { 
	global $bids_data, $premiumbox;	
	
	if (isset($bids_data->id)) {

		if (is_true_userhash($bids_data)) {
			$btc_code = pn_strip_input($bids_data->btc_code);
			$btc_code_info = pn_strip_input($bids_data->btc_code_info);
		} else {
			$btc_code = '---';
			$btc_code_info = '---';
		}
		if (!$btc_code) { $btc_code = '---'; }
		if (!$btc_code_info) { $btc_code_info = '---'; }
		
		$instruction = str_replace('[coupon_code]', $btc_code, $instruction);
		$instruction = str_replace('[coupon_info]', $btc_code_info, $instruction);
		
	}
	
	return $instruction;
}

add_filter('lists_table_userxch', 'fixbtccode_lists_userxch');
function fixbtccode_lists_userxch($lists) {
	global $premiumbox;	

	$fixbtccode = intval($premiumbox->get_option('exchange', 'fixbtccode'));
	if ($fixbtccode) {
		$lists['fixbtccode'] = __('Coupon code', 'pn');
		$lists['fixbtccodeinfo'] = __('Coupon info', 'pn');
	}	
	
	return $lists;
} 

add_filter('body_list_userxch', 'fixbtccode_body_list_userxch', 10, 6);
function fixbtccode_body_list_userxch($data_item, $item, $key, $title, $date_format, $time_format) {		
				
	if ('fixbtccode' == $key) {
		$code = '---';
		$btc_code = pn_strip_input($item->btc_code);
		if ($btc_code) {
			$code = $btc_code;
		}
		$data_item = '<span class="fixbtccode">' . $code . '</span>';
	}
	
	if ('fixbtccodeinfo' == $key) {
		$code = '---';
		$btc_code = pn_strip_input($item->btc_code_info);
		if ($btc_code) {
			$code = $btc_code;
		}
		$data_item = '<span class="fixbtccode">' . $code . '</span>';
	}	
	
	return $data_item;
}