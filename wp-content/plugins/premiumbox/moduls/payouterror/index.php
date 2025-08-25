<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Status "Automatic payout error"[:en_US][ru_RU:]Статус "Ошибка автовыплаты"[:ru_RU]
description: [en_US:]Status "Automatic payout error"[:en_US][ru_RU:]Статус "Ошибка автовыплаты"[:ru_RU]
version: 2.7.0
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

add_action('paymerchant_error', 'payouterror_paymerchant_error', 10, 6);
function payouterror_paymerchant_error($m_name, $m_id, $error, $item_id, $place, $pay_error) {
	
	if ($pay_error < 1) {

		$params = array(
			'm_place' => 'modul payouterror',
			'system' => 'system',
			'bid_status' => array('realpay', 'verify'),
		);
		set_bid_status('payouterror', $item_id, $params); 

	}
	
}

add_filter('_icon_indicators', 'payouterror_icon_indicators');
function payouterror_icon_indicators($lists) {
	global $premiumbox;
	
	$lists['payouterror'] = array(
		'title' => __('Orders with payout error', 'pn'),
		'img' => $premiumbox->plugin_url . 'images/payouterror.png',
		'url' => admin_url('admin.php?page=pn_bids&idspage=1&bidstatus[]=payouterror')
	);
	
	return $lists;
}

add_filter('_icon_indicator_payouterror', 'def_icon_indicator_payouterror');
function def_icon_indicator_payouterror($count) {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_bids')) {
		$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'payouterror'");
	}	
	
	return $count;
}