<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]AML[:en_US][ru_RU:]AML[:ru_RU]
description: [en_US:]AML services[:en_US][ru_RU:]AML сервисы[:ru_RU]
version: 2.7.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'all_moduls_active_amlcheck');
add_action('pn_plugin_activate', 'all_moduls_active_amlcheck');
function all_moduls_active_amlcheck() {
	global $wpdb;			

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'amlcheck'");
    if (0 == $query) { 
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `amlcheck` varchar(150) NOT NULL");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'amlcheck_opts'");
    if (0 == $query) { 
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `amlcheck_opts` longtext NOT NULL");
    }

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'aml_give'");
    if (0 == $query) { 
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `aml_give` longtext NOT NULL");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'aml_get'");
    if (0 == $query) { 
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `aml_get` longtext NOT NULL");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'aml_merch'");
    if (0 == $query) { 
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `aml_merch` longtext NOT NULL");
    }	

	$table_name = $wpdb->prefix . "amlcheck_logs"; 
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
		PRIMARY KEY (`id`),
		INDEX (`create_date`),
		INDEX (`m_name`),
		INDEX (`m_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);

}

/*
'nd' - 1
'hash'
'score' -
'signals' - 
'status'
	- 0 - не проверяли
	- 1 - выполнено полностью
	- 2 - допроверка
	- 3 - ошибка (раньше 0, 2)
*/

add_filter('pn_caps', 'amlcheck_pn_caps');
function amlcheck_pn_caps($pn_caps) {
	
	$pn_caps['pn_amlcheck'] = __('Work with AML services', 'pn');
	
	return $pn_caps;
}

add_action('admin_menu', 'admin_menu_amlcheck', 300);
function admin_menu_amlcheck() {
	global $premiumbox;	
	
	if (current_user_can('administrator') or current_user_can('pn_amlcheck')) {
		add_menu_page(__('AML', 'pn'), __('AML', 'pn'), 'read', 'pn_amlcheck', array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('merchants'), 602);
		add_submenu_page("pn_amlcheck", __('AML', 'pn'), __('AML', 'pn'), 'read', "pn_amlcheck", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_amlcheck", __('Add', 'pn'), __('Add', 'pn'), 'read', "pn_amlcheck_add", array($premiumbox, 'admin_temp'));	
		add_submenu_page("pn_amlcheck", __('Logs', 'pn'), __('Logs', 'pn'), 'read', "pn_amlcheck_logs", array($premiumbox, 'admin_temp'));		
	}
	
}

function del_amlcheck_logs() {
	global $wpdb, $premiumbox;
	
	if (!$premiumbox->is_up_mode()) {
		
		$count_day = intval(get_logs_sett('delete_amlchecklogs_day'));
		if (!$count_day) { $count_day = 3; }

		if ($count_day > 0) {
			$time = current_time('timestamp') - ($count_day * DAY_IN_SECONDS); 
			$ldate = date('Y-m-d H:i:s', $time);
			$wpdb->query("DELETE FROM " . $wpdb->prefix . "amlcheck_logs WHERE create_date < '$ldate'");
		}
		
	}
} 

add_filter('list_cron_func', 'del_amlcheck_logs_list_cron_func');
function del_amlcheck_logs_list_cron_func($filters) {
	
	$filters['del_amlcheck_logs'] = array(
		'title' => __('Delete AML logs', 'pn'),
		'site' => '1day',
		'file' => 'none',
	);
	
	return $filters;
}

add_filter('list_logs_settings', 'amlcheck_logs_list_logs_settings');
function amlcheck_logs_list_logs_settings($filters) {
	
	$filters['delete_amlchecklogs_day'] = array(
		'title' => __('Delete trade scripts log', 'pn') . ' (' . __('days', 'pn') . ')',
		'count' => 3,
		'minimum' => 1,
	);
	
	return $filters;
} 

add_action('save_amlcheck_error', 'log_save_amlcheck_error', 10, 8); 
function log_save_amlcheck_error($m_name, $m_id, $url, $headers, $json_data, $result = '', $error = '', $bid_id = '') {
	global $wpdb;

	$m_data = get_amlcheck_data($m_id);
	$disable_logs = intval(is_isset($m_data, 'disable_logs'));

	if (!$disable_logs) {

		$arr = array();
		$arr['create_date'] = current_time('mysql');
		$arr['m_name'] = is_extension_name($m_name);
		$arr['m_id'] = is_extension_name($m_id);
		$arr['bid_id'] = intval($bid_id);
		$arr['ip'] = pn_strip_input(pn_real_ip());
		$arr['url'] = pn_strip_input(print_r($url, true));
		$arr['headers'] = pn_strip_input(print_r($headers, true));
		$arr['json_data'] = pn_strip_input(print_r($json_data, true));
		$arr['result'] = addslashes(pn_maxf(pn_strip_input(print_r($result, true)), 60000));
		$arr['error'] = pn_strip_input(print_r($error, true));	
		$wpdb->insert($wpdb->prefix . 'amlcheck_logs', $arr);
	
	}
	
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'functions');
set_extandeds($premiumbox, 'amlcheck');
$premiumbox->include_path(__FILE__, 'list');
$premiumbox->include_path(__FILE__, 'add'); 
$premiumbox->include_path(__FILE__, 'filters');
$premiumbox->include_path(__FILE__, 'logs');
$premiumbox->include_path(__FILE__, 'cron');