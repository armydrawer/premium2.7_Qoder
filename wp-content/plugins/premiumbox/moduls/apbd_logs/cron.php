<?php
if (!defined('ABSPATH')) { exit(); }

function del_apbd() {
	global $wpdb, $premiumbox;
	
	if (!$premiumbox->is_up_mode()) {
		$count_day = get_logs_sett('del_apbd_day');
		$second = $count_day * 24 * 60 * 60;
		$second = apply_filters('del_apbd_second', $second);
		$time = current_time('timestamp') - $second;
		$ldate = date('Y-m-d H:i:s', $time);
		$wpdb->query("DELETE FROM " . $wpdb->prefix . "db_admin_logs WHERE trans_date < '$ldate'");
	}
	
} 

add_filter('list_cron_func', 'del_apbd_list_cron_func');
function del_apbd_list_cron_func($filters) {
	
	$filters['del_apbd'] = array(
		'title' => __('Deleting logs of administrator actions', 'pn'),
		'site' => 'now',
		'file' => 'none',
	);
	
	return $filters;
}

add_filter('list_logs_settings', 'apbd_list_logs_settings');
function apbd_list_logs_settings($filters) {
	
	$filters['del_apbd_day'] = array(
		'title' => __('Deleting logs of administrator actions', 'pn') . ' (' . __('days', 'pn') . ')',
		'count' => 30,
		'minimum' => 10,
	);
	
	return $filters;
} 