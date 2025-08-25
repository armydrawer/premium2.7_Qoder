<?php
if (!defined('ABSPATH')) { exit(); }

add_action('admin_menu', 'admin_menu_bids');
function admin_menu_bids() {
	global $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_bids')) {	
		add_menu_page(__('Orders', 'pn'), __('Orders', 'pn'), 'read', "pn_bids", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('icon'), 3);
	}
}

add_filter('pn_caps', 'bids_pn_caps');
function bids_pn_caps($pn_caps) {
	
	$pn_caps['pn_bids'] = __('To process exchange orders', 'pn');
	$pn_caps['pn_bids_change'] = __('Changing order status', 'pn');
	$pn_caps['pn_bids_delete'] = __('Complete removal of orders', 'pn');
	$pn_caps['pn_bids_payouts'] = __('Making payouts by button', 'pn');
	
	return $pn_caps;
}

add_filter('get_statusbids_for_admin', 'get_statusbids_for_admin_remove', 1000);
function get_statusbids_for_admin_remove($st) {
	
	if (current_user_can('administrator') or current_user_can('pn_bids_delete')) {
		
		$st['realdelete'] = array(
			'name' => 'realdelete',
			'title' => __('complete removal', 'pn'),
			'color' => '#ffffff',
			'background' => '#ff0000',
		);		
		
	}
	
	return $st;
}

add_filter('_icon_indicators', 'bids_icon_indicators', 0);
function bids_icon_indicators($lists) {

	$plugin = get_plugin_class();
	$bidsind = $plugin->get_option('bidsind');
	if (!is_array($bidsind)) { $bidsind = array(); }
	
	$data = '';
	foreach ($bidsind as $st) {
		$st = is_status_name($st);
		if ($st) {
			$data .= '&bidstatus[]=' . $st;
		}
	}
	
	$lists['bidsind'] = array(
		'title' => __('Custom order statuses', 'pn'),
		'img' => $plugin->plugin_url . 'images/money.gif',
		'url' => admin_url('admin.php?page=pn_bids' . $data)
	);	
	
	return $lists;
}

add_filter('_icon_indicator_bidsind', 'def_icon_indicator_bids');
function def_icon_indicator_bids($count) {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_bids')) {
		
		$plugin = get_plugin_class();
		$bidsind = $plugin->get_option('bidsind');
		if (!is_array($bidsind)) { $bidsind = array(); }
			
		$status = create_data_for_db($bidsind, 'status');
		if ($status) {
			$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($status)");
		}

	}	
	
	return $count;
}

add_filter('change_bid_status', 'sethashdata_change_bidstatus', 95); //2400   
function sethashdata_change_bidstatus($data) {   
	
	$place = $data['place'];
	$set_status = $data['set_status'];
	$bid = $data['bid'];
	$who = $data['who'];
	$old_status = $data['old_status'];
	$direction = $data['direction'];	
	
	$stop_action = intval(is_isset($data, 'stop'));
	if (!$stop_action) {
		if ('new' == $set_status) {

			$hashdata = bid_hashdata($bid->id, $bid); 
			$hashdata = @serialize($hashdata);
			$data['bid'] = pn_object_replace($bid, array('hashdata' => $hashdata));

		}
	}
	
	return $data;
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'bids');
$premiumbox->include_path(__FILE__, 'ajax');