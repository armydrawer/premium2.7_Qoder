<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('change_bid_status', 'setuserebids_change_bidstatus', 45);     
function setuserebids_change_bidstatus($data) {  
	global $wpdb, $premiumbox;

	$place = $data['place'];
	$set_status = $data['set_status'];
	$bid = $data['bid'];
	$who = $data['who'];
	$old_status = $data['old_status'];
	$direction = $data['direction'];

	$stop_action = intval(is_isset($data, 'stop'));
	if (!$stop_action) {
		if ('new' == $set_status) {
			$user_id = intval($bid->user_id);
			$user_login = is_user($bid->user_login);
			$user_hash = pn_strip_input($bid->user_hash);
			if ($user_id > 0) {
				$wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET user_id = '$user_id', user_login = '$user_login' WHERE user_hash = '$user_hash' AND user_id < 1");
			}
		}
	}
	
	return $data;
}

add_action('pn_user_register', 'setuserebids_pn_user_register', 45);
function setuserebids_pn_user_register($user_id) {
	global $wpdb;
	
	$ui = get_userdata($user_id);
	$user_login = is_user(is_isset($ui, 'user_login'));
	$user_hash = get_user_hash();
	if ($user_id > 0 and isset($ui->ID)) {
		$wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET user_id = '$user_id', user_login = '$user_login' WHERE user_hash = '$user_hash' AND user_id < 1");
	}	
}

global $premiumbox; 
$premiumbox->include_path(__FILE__, 'calculator');
$premiumbox->include_path(__FILE__, 'funcs');
$premiumbox->include_path(__FILE__, 'action'); 
$premiumbox->include_path(__FILE__, 'cron'); 
$premiumbox->include_path(__FILE__, 'mails');