<?php
if (!defined('ABSPATH')) { exit(); }

function archive_plinks() {
	global $wpdb, $premiumbox;

	if (!$premiumbox->is_up_mode()) {

		$count_day = get_logs_sett('archive_plinks_day');
		if ($count_day > 0) {
			$time = current_time('timestamp') - ($count_day * DAY_IN_SECONDS); 
			$ldate = date('Y-m-d H:i:s', $time);
			$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "plinks WHERE pdate < '$ldate'");
			foreach ($items as $item) {
				$item_id = $item->id;	
				$user_id = $item->user_id;
				
				set_archive_data($user_id, 'plinks', '', '', 1);
				
				$wpdb->query("DELETE FROM " . $wpdb->prefix . "plinks WHERE id = '$item_id'");
			}		
		}

	}
} 

add_filter('list_cron_func', 'archive_plinks_list_cron_func');
function archive_plinks_list_cron_func($filters) {
	
	$filters['archive_plinks'] = array(
		'title' => __('Archiving partnership transitions', 'pn'),
		'site' => '1day',
		'file' => 'none',
	);
	
	return $filters;
}

add_filter('list_logs_settings', 'archive_plinks_list_logs_settings');
function archive_plinks_list_logs_settings($filters) {	

	$filters['archive_plinks_day'] = array(
		'title' => __('Archiving partnership transitions', 'pn') .' ('. __('days', 'pn') .')',
		'count' => 60,
		'minimum' => 0,
	);	
	
	return $filters;
} 