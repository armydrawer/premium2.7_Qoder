<?php
if (!defined('ABSPATH')) { exit(); }

function pn_archives_bids() {
	global $wpdb, $premiumbox;

	if (!$premiumbox->is_up_mode()) {
		
		$del_file = intval($premiumbox->get_option('archivebids', 'txt'));
		$limit = intval($premiumbox->get_option('archivebids', 'limit_archive'));
		if ($limit < 1) { $limit = 5; }
		if ($limit > 20) { $limit = 20; }
		
		$count_day = get_logs_sett('archive_bids_day'); 
		$count_day = apply_filters('archive_bids_day', $count_day);
		if ($count_day > 0) {
			$second = $count_day * DAY_IN_SECONDS;
			$date = current_time('mysql');
			$time = current_time('timestamp') - $second;
			$ldate = date('Y-m-d H:i:s', $time);
			
			$my_dir = wp_upload_dir();
			$dir_old = $my_dir['basedir'] . '/bids/';
			$dir = $premiumbox->upload_dir . '/bids/';
			
			$del_files = array();
			
			$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE edit_date < '$ldate' AND status != 'auto' LIMIT $limit");
			foreach ($items as $item) {
				$id = $item->id;
				
				$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$id'");
				if ($result) {
							
					$status = $item->status;			
					$user_id = $item->user_id;
					$ref_id = is_isset($item, 'ref_id');
					$pcalc = intval(is_isset($item, 'pcalc'));
					$currency_code_id_give = $item->currency_code_id_give;
					$currency_code_id_get = $item->currency_code_id_get;
					$sum1c = is_sum($item->sum1c);
					$sum2c = is_sum($item->sum2c);		
					$partner_sum = is_sum(is_isset($item, 'partner_sum'));
					$currency_id_give = $item->currency_id_give;
					$currency_id_get = $item->currency_id_get;
					$domacc1 = intval(is_isset($item, 'domacc1'));
					$domacc2 = intval(is_isset($item, 'domacc2'));					
						
					if ('success' == $status) {
						if ($user_id > 0) {
							set_archive_data($user_id, 'user_exsum', '', '', $item->exsum);	
						}
						if (1 == $pcalc) {
							set_archive_data($ref_id, 'pbids', '', '', 1);
							set_archive_data($ref_id, 'pbids_sum', '', '', $partner_sum);
							set_archive_data($ref_id, 'pbids_exsum', '', '', $item->exsum);
						}
					}
						
					set_archive_data($currency_code_id_give, 'currency_code_give', $status, '', $item->sum1r);
					set_archive_data($currency_code_id_get, 'currency_code_get', $status, '', $item->sum2r);
					set_archive_data($currency_id_give, 'currency_give', $status, '', $item->sum1r);
					set_archive_data($currency_id_get, 'currency_get', $status, '', $item->sum2r);
					set_archive_data($item->direction_id, 'direction_give', $status, '', $item->sum1r);
					set_archive_data($item->direction_id, 'direction_get', $status, '', $item->sum2r);
						
					if ($user_id > 0) {
						
						set_archive_data($user_id, 'user_bids', $status, '', 1);
						
						if (1 == $domacc1) {
							set_archive_data($user_id, 'domacc1_currency_code', $status, $currency_code_id_give, $sum1c);
						}
						if (1 == $domacc2) {
							set_archive_data($user_id, 'domacc2_currency_code', $status, $currency_code_id_get, $sum2c);
						}				
					}
					
					do_action('archive_bids', $item->id, $item);
						
					$in_list = archive_data_list();	
						
					$archive_content = array();
					foreach ($item as $k => $v) {
						if (!in_array($k, $in_list)) {
							$archive_content[$k] = $v;
						}
					}
					$archive_content = apply_filters('archive_content', $archive_content, $item);
					
					$arr = array();
					
					foreach ($in_list as $inl) {
						$arr[$inl] = is_isset($item, $inl);
					}
					
					$arr['archive_date'] = $date;
					$arr['archive_content'] = serialize($archive_content);
					$arr['bid_id'] = $id;
					$wpdb->insert($wpdb->prefix . "archive_exchange_bids", $arr);
					
					$ch_data = array(
						'bid' => $item,
						'set_status' => 'archived',
						'place' => 'archive',
						'who' => 'system',
						'old_status' => $item->status
					);
					_change_bid_status($ch_data);						 	 
						
					$wpdb->query("DELETE FROM ".$wpdb->prefix."bids_meta WHERE item_id = '$id'");		
					
					$del_files[] = $dir_old . $id .'.txt';
					$del_files[] = $dir . $id .'.php';					
					
				}
			}
			
			if (1 == $del_file) {
				foreach ($del_files as $del_file_name) {
					if (is_file($del_file_name)) {
						@unlink($del_file_name);
					}					
				}
			}
			
		}
		
	}
} 

add_filter('list_cron_func', 'pn_archives_bids_list_cron_func');
function pn_archives_bids_list_cron_func($filters) {
	
	$filters['pn_archives_bids'] = array(
		'title' => __('Archiving orders older', 'pn'),
		'site' => '10min',
		'file' => 'none',
	);
	
	return $filters;
}

add_filter('list_logs_settings', 'pn_archives_bids_list_logs_settings');
function pn_archives_bids_list_logs_settings($filters) {
	
	$filters['archive_bids_day'] = array(
		'title' => __('Archiving orders older', 'pn') . ' (' . __('days', 'pn') . ')',
		'count' => 60,
		'minimum' => 5,
	);
	
	return $filters;
} 