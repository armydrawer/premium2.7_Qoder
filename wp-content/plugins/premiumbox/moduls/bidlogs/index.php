<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Orders status log[:en_US][ru_RU:]Лог статусов заявок[:ru_RU]
description: [en_US:]Orders status log[:en_US][ru_RU:]Лог статусов заявок[:ru_RU]
version: 2.7.1
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

/* BD */
add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_bidlogs');
add_action('pn_plugin_activate', 'bd_all_moduls_active_bidlogs');
function bd_all_moduls_active_bidlogs() {
	global $wpdb;	
	
	$table_name = $wpdb->prefix . "bid_logs";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`createdate` datetime NOT NULL,
		`bid_id` bigint(20) NOT NULL default '0',
		`user_id` bigint(20) NOT NULL default '0',
		`user_login` varchar(150) NOT NULL,
		`old_status` varchar(150) NOT NULL,
		`new_status` varchar(150) NOT NULL,
		`place` varchar(50) NOT NULL,
		`who` varchar(50) NOT NULL,
		`course_give` varchar(50) NOT NULL default '0',
		`course_get` varchar(50) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`createdate`),
		INDEX (`bid_id`),
		INDEX (`user_id`),
		INDEX (`place`),
		INDEX (`who`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql); 
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "bid_logs LIKE 'who'"); /* 1.5 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "bid_logs ADD `who` varchar(50) NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "bid_logs LIKE 'course_give'"); /* 1.5 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "bid_logs ADD `course_give` varchar(50) NOT NULL default '0'");
	}	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "bid_logs LIKE 'course_get'"); /* 1.5 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "bid_logs ADD `course_get` varchar(50) NOT NULL default '0'");
	}	
}
/* end BD */

add_action('admin_menu', 'admin_menu_bidlogs', 100);
function admin_menu_bidlogs() {
	global $premiumbox;	
	
	if (current_user_can('administrator') or current_user_can('pn_bids')) {
		add_submenu_page("pn_bids", __('Orders status log', 'pn'), __('Orders status log', 'pn'), 'read', "pn_bidlogs", array($premiumbox, 'admin_temp'));
	}
}

add_action('ap_cron_set_status', 'bidlogs_ap_cron_set_status', 10, 3);
function bidlogs_ap_cron_set_status($item, $set_status, $m_id) {
	global $wpdb;
	
	$item_id = $item->id;
	$old_status = $item->status;
	if ($old_status != $set_status) {		
		$arr = array();
		$arr['createdate'] = current_time('mysql');
		$arr['bid_id'] = $item_id;
		$arr['user_id'] = 0;
		$arr['user_login'] = '';
		$arr['old_status'] = is_status_name($old_status);
		$arr['new_status'] = is_status_name($set_status);
		$arr['place'] = pn_strip_input($m_id);
		$arr['who'] = 'system';
		$arr['course_give'] = is_sum($item->course_give);
		$arr['course_get'] = is_sum($item->course_get);
		$wpdb->insert($wpdb->prefix.'bid_logs', $arr);
	}	
}

add_filter('change_bid_status', 'bidlogs_change_bidstatus', 70); //30   
function bidlogs_change_bidstatus($data) { 
	global $wpdb;

	$place = $data['place'];
	$set_status = $data['set_status'];
	$bid = $data['bid'];
	$who = $data['who'];
	$old_status = $data['old_status'];
	$direction = $data['direction'];
	
	$stop_action = intval(is_isset($data, 'stop'));

	if ($old_status != $set_status and !$stop_action) {
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);		
		$arr = array();
		$arr['createdate'] = current_time('mysql');
		$arr['bid_id'] = $bid->id;
		$arr['user_id'] = $user_id;
		$arr['user_login'] = is_isset($ui, 'user_login');
		$arr['old_status'] = is_status_name($old_status);
		$arr['new_status'] = is_status_name($set_status);
		$arr['place'] = pn_strip_input($place);
		$arr['who'] = pn_strip_input($who);
		$arr['course_give'] = is_sum($bid->course_give);
		$arr['course_get'] = is_sum($bid->course_get);
		$wpdb->insert($wpdb->prefix . 'bid_logs', $arr);
	}
	
	return $data;
}	

function delete_bidlogs() {
	global $wpdb, $premiumbox;
	
	if (!$premiumbox->is_up_mode()) {
		$count_day = get_logs_sett('delete_bidlogs_day');
		if ($count_day > 0) {
			$time = current_time('timestamp') - ($count_day * DAY_IN_SECONDS); 
			$ldate = date('Y-m-d H:i:s', $time);
			$wpdb->query("DELETE FROM " . $wpdb->prefix . "bid_logs WHERE createdate < '$ldate'");
		}
	}
} 

add_filter('list_cron_func', 'delete_bidlogs_list_cron_func');
function delete_bidlogs_list_cron_func($filters) {
	
	$filters['delete_bidlogs'] = array(
		'title' => __('Deleting old orders status log', 'pn'),
		'site' => '1day',
		'file' => 'none',
	);
	
	return $filters;
}

add_filter('list_logs_settings', 'bidlogs_list_logs_settings');
function bidlogs_list_logs_settings($filters) {	
	
	$filters['delete_bidlogs_day'] = array(
		'title' => __('Deleting old orders status log', 'pn') .' ('. __('days', 'pn') .')',
		'count' => 40,
		'minimum' => 2,
	);
	
	return $filters;
} 

function bidlogs_status($status) {
	
	$status_name = '';
	if ('realdelete' == $status) {
		$status_name = __('permanently deleted', 'pn'); /* т.е. не удаленная, а уничтоженная */
	} elseif ('archived' == $status) { 	
		$status_name = __('archived order', 'pn'); /* т.е. ушла в архив */
	} elseif ('auto' == $status) { 	
		$status_name = __('uncreated order', 'pn'); /* т.е. не подтвержденные данные */		
	} else {
		$status_name = get_bid_status($status);
	}
	
	return '<span class="stname st_' . is_status_name($status) . '">' . $status_name . '</span>';
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'list');