<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]!Do not activate without any reason! Automatic payouts log[:en_US][ru_RU:]!Не активируйте без необходимости! Лог автовыплат[:ru_RU]
description: [en_US:]!Do not activate without any reason! Automatic payouts log[:en_US][ru_RU:]!Не активируйте без необходимости! Лог автовыплат[:ru_RU]
version: 2.7.0
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_paymerchantlogs');
add_action('pn_plugin_activate', 'bd_all_moduls_active_paymerchantlogs');
function bd_all_moduls_active_paymerchantlogs() {
	global $wpdb;	
	
	$table_name = $wpdb->prefix . "ap_logs";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`m_name` varchar(150) NOT NULL,
		`m_id` varchar(150) NOT NULL,
		`bid_id` bigint(20) NOT NULL default '0',
		`ip` varchar(250) NOT NULL,
		`url` longtext NOT NULL,
		`headers` longtext NOT NULL,
		`json_data` longtext NOT NULL,
		`result` longtext NOT NULL,
		`error` longtext NOT NULL,		
		PRIMARY KEY ( `id` ),
		INDEX (`create_date`),
		INDEX (`bid_id`),
		INDEX (`m_name`),
		INDEX (`m_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);	
	
}
 
add_action('admin_menu', 'pn_adminpage_paymerchantlogs', 1000);
function pn_adminpage_paymerchantlogs() {
	global $premiumbox;	
	
	if (current_user_can('administrator') or current_user_can('pn_merchants')) {
		add_submenu_page("pn_merchants", __('Automatic payouts log', 'pn'), __('Automatic payouts log', 'pn'), 'read', "pn_paymerchantlogs", array($premiumbox, 'admin_temp'));
	}
	
}

add_action('_paymerchants_options', 'paymerchantlogs_merchants_options', 100, 5);
function paymerchantlogs_merchants_options($options, $name, $data, $id, $place) {
	
	$options['disable_logs'] = array(
		'view' => 'select',
		'title' => __('Disable logs', 'pn'),
		'options' => array('0' =>__('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => is_isset($data, 'disable_logs'),
		'name' => 'disable_logs',
		'work' => 'int',
	);	
	
	return $options;
}

add_action('paymerchant_secure', 'paymerchantlogs_merchant_secure', 10, 3); 
function paymerchantlogs_merchant_secure($m_name, $data, $m_id) {
	global $wpdb;

	if (is_array($data)) {
		$db_data = $data;
	} else {
		$db_data = $_REQUEST;
	}

	$m_data = get_paymerch_data($m_id);
	$disable_logs = intval(is_isset($m_data, 'disable_logs'));

	if (!$disable_logs) {	
	
		$arr = array();
		$arr['create_date'] = current_time('mysql');
		$arr['m_name'] = is_extension_name($m_name);
		$arr['m_id'] = is_extension_name($m_id);
		$arr['ip'] = pn_strip_input(pn_real_ip());
		$arr['result'] = addslashes(pn_maxf(pn_strip_input(print_r($db_data, true)), 60000));
		$wpdb->insert($wpdb->prefix . 'ap_logs', $arr);
	
	}
	
}

add_action('paymerchant_error','paymerchantlogs_paymerchant_error', 10, 4);
add_action('paymerchant_logs','paymerchantlogs_paymerchant_error', 10, 4);
function paymerchantlogs_paymerchant_error($m_name, $m_id, $data, $bid_id = '') {
	global $wpdb;
	
	$m_data = get_paymerch_data($m_id);
	$disable_logs = intval(is_isset($m_data, 'disable_logs'));

	if (!$disable_logs) {	
	
		$arr = array();
		$arr['create_date'] = current_time('mysql');
		$arr['m_name'] = is_extension_name($m_name);
		$arr['m_id'] = is_extension_name($m_id);
		$arr['ip'] = pn_strip_input(pn_real_ip());
		$arr['bid_id'] = intval($bid_id);
		$arr['result'] = addslashes(pn_maxf(pn_strip_input(print_r($data, true)), 60000));
		$wpdb->insert($wpdb->prefix . 'ap_logs', $arr);
	
	}
	
}

add_action('save_paymerchant_error','merchantlogs_save_paymerchant_error', 10, 7); 
function merchantlogs_save_paymerchant_error($m_name, $m_id, $url, $headers, $json_data, $result = '', $error = '') {
	global $wpdb;

	$m_data = get_paymerch_data($m_id);
	$disable_logs = intval(is_isset($m_data, 'disable_logs'));

	if (!$disable_logs) {

		$arr = array();
		$arr['create_date'] = current_time('mysql');
		$arr['m_name'] = is_extension_name($m_name);
		$arr['m_id'] = is_extension_name($m_id);
		$arr['ip'] = pn_strip_input(pn_real_ip());
		$arr['url'] = pn_strip_input(print_r($url, true));
		$arr['headers'] = pn_strip_input(print_r($headers, true));
		$arr['json_data'] = pn_strip_input(print_r($json_data, true));
		$arr['result'] = addslashes(pn_maxf(pn_strip_input(print_r($result, true)), 60000));
		$arr['error'] = pn_strip_input(print_r($error, true));	
		$wpdb->insert($wpdb->prefix . 'ap_logs', $arr);
	
	}
	
}

function del_paymerchantlogs() {
	global $wpdb, $premiumbox;
	
	if (!$premiumbox->is_up_mode()) {
		
		$count_day = intval(get_logs_sett('delete_paymerchantlogs_day'));
		if (!$count_day) { $count_day = 3; }

		if ($count_day > 0) {
			$time = current_time('timestamp') - ($count_day * DAY_IN_SECONDS); 
			$ldate = date('Y-m-d H:i:s', $time);
			$wpdb->query("DELETE FROM " . $wpdb->prefix . "ap_logs WHERE create_date < '$ldate'");
		}
	}
} 

add_filter('list_cron_func', 'del_paymerchantlogs_list_cron_func');
function del_paymerchantlogs_list_cron_func($filters) {
	
	$filters['del_paymerchantlogs'] = array(
		'title' => __('Deleting automatic payout logs', 'pn'),
		'site' => '1day',
		'file' => 'none',
	);
	
	return $filters;
}

add_filter('list_logs_settings', 'paymerchantlogs_list_logs_settings');
function paymerchantlogs_list_logs_settings($filters) {	
	
	$filters['delete_paymerchantlogs_day'] = array(
		'title' => __('Deleting automatic payout logs', 'pn') . ' (' . __('days', 'pn') . ')',
		'count' => 3,
		'minimum' => 1,
	);
	
	return $filters;
} 

global $premiumbox;
$premiumbox->include_path(__FILE__, 'list');