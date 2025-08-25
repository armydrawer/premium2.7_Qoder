<?php
if (!defined('ABSPATH')) { exit(); }
 
add_action('premium_action_bids_live_change', 'pn_premium_action_bids_live_change');
function pn_premium_action_bids_live_change() {
	
	_method('post');
	_json_head();
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error', 'pn');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	if (current_user_can('administrator') or current_user_can('pn_bids')) {
	
		$ulc = array();
		$ulc['autoupdate'] = intval(is_param_post('autoupdate'));
		$ulc['timeupdate'] = intval(is_param_post('timeupdate'));
		$ulc['rington'] = intval(is_param_post('rington'));
		$ulc['hidetransit'] = intval(is_param_post('hidetransit'));
		
		$you_status = array();
		$status = (array)is_param_post('status');
		if (count($status) > 0) {
			foreach ($status as $val) {
				$you_status[] = is_status_name($val);
			}
		} 
		
		$ulc['status'] = implode(',', $you_status);
		update_user_meta($user_id, 'user_live_change', $ulc) or add_user_meta($user_id, 'user_live_change', $ulc, true);
	
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Authorisation Error', 'pn');
	}
	
	echo pn_json_encode($log);
	exit;
}

add_action('premium_action_bids_live_html', 'pn_premium_action_bids_live_html');
function pn_premium_action_bids_live_html() {
	global $wpdb;

	_method('post');
	_json_head();
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error','pn');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	if (current_user_can('administrator') or current_user_can('pn_bids')) {
	
		$enable = array();
		$bid_status_list = list_bid_status();
		if (is_array($bid_status_list)) {	
			foreach ($bid_status_list as $key => $status) {
				$enable[] = trim($key);
			}
		}
		
		$bid_status = explode(',', is_param_post('bid_status'));
		$en_join = array();
		foreach ($bid_status as $bs) {
			$bs = trim($bs);
			if (in_array($bs,$enable)) {
				$en_join[] = "'" . $bs . "'";
			}
		}
		
		$hide_id = array();
		$old_join = array();
		$old_id = explode(',', is_param_post('old_id'));
		foreach ($old_id as $id) {
			$id = intval($id);
			if ($id) {
				$old_join[] = "'" . $id . "'";
				$hide_id[] = $id;
			}
		}

		$last_id = intval(is_param_post('last_id'));
		$bids = array();
		if (count($en_join) > 0) {
			
			$join = implode(',', $en_join);
			$where = apply_filters('module_live_where', "status IN($join) AND id > $last_id");
			
			$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE $where ORDER BY id ASC");
			foreach ($items as $item) {
				
				$bid_id = $item->id;
				$last_id = $bid_id;
				$status = is_status_name($item->status);
				$link = admin_url('admin.php?page=pn_bids&bidid=' . $item->id);
				$status_name = get_bid_status($item->status);
				
				$bids[] = array(
					'id' => $bid_id,
					'status' => $status,
					'status_name' => $status_name,
					'link' => $link,
					'sum_give' => is_sum($item->sum1dc),
					'sum_get' => is_sum($item->sum2c),
					'cur_give' => pn_strip_input(ctv_ml($item->psys_give)) . ' ' . is_site_value($item->currency_code_give),
					'cur_get' => pn_strip_input(ctv_ml($item->psys_get)) . ' ' . is_site_value($item->currency_code_get),
				);
				
			}
			
			if (count($old_join) > 0) {
				$hide_id = array();
				$id_join = implode(',', $old_join);
				
				$del_items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status NOT IN($join) AND id IN($id_join) $where ORDER BY id ASC");
				foreach ($del_items as $item) {
					$hide_id[] = $item->id;
				}	
			}
		
		} 
		
		$log['hide_id'] = $hide_id;
		$log['last_id'] = $last_id;	
		$log['bids'] = $bids;
		$log['status'] = 'success';
	
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Authorisation Error', 'pn');		
	}
	
    echo pn_json_encode($log);	
	exit;
}