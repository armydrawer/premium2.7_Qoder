<?php
if (!defined('ABSPATH')) { exit(); }

function delete_auto_bids() {
	global $wpdb, $premiumbox;
	
	if (!$premiumbox->is_up_mode()) {
		$count_minute = get_logs_sett('delete_auto_bids');
		if (!$count_minute) { $count_minute = 15; }
		$second = $count_minute * 60;
		$time = current_time('timestamp') - $second;
		$ldate = date('Y-m-d H:i:s', $time);
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE create_date < '$ldate' AND status = 'auto'");
		foreach ($items as $item) {
			$id = $item->id;	
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$id'");
			if ($result) {
				$wpdb->query("DELETE FROM " . $wpdb->prefix . "bids_meta WHERE item_id = '$id'"); 

				$ch_data = array(
					'bid' => $item,
					'set_status' => 'realdelete',
					'place' => 'cron_auto_delete',
					'who' => 'system',
					'old_status' => 'auto',
					'direction' => ''
				);
				_change_bid_status($ch_data); 
				  
			}
		}
	}
	
} 

add_filter('list_cron_func', 'delete_auto_bids_list_cron_func');
function delete_auto_bids_list_cron_func($filters) {
	
	$filters['delete_auto_bids'] = array(
		'title' => __('Removing orders with inappropriate rules', 'pn'),
		'site' => 'now',
		'file' => 'none',
	);
	
	return $filters;
}

add_filter('list_logs_settings', 'delete_auto_bids_list_logs_settings');
function delete_auto_bids_list_logs_settings($filters) {
	
	$filters['delete_auto_bids'] = array(
		'title' => __('Removing orders with inappropriate rules', 'pn') . ' (' . __('minuts', 'pn') . ')',
		'count' => 15,
		'minimum' => 1,
	);	
	
	return $filters;
} 