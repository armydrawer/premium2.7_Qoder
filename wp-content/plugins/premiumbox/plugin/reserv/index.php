<?php
if (!defined('ABSPATH')) { exit(); }

add_action('admin_menu', 'admin_menu_reserve');
function admin_menu_reserve() {
	global $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_currency_reserve')) {
		add_menu_page(__('Reserve adjustment', 'pn'), __('Reserve adjustment', 'pn'), 'read', "pn_currency_reserve", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('reserv'), 626);	
		add_submenu_page("pn_currency_reserve", __('Add reserve transaction', 'pn'), __('Add reserve transaction', 'pn'), 'read', "pn_add_currency_reserve", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_currency_reserve", __('Reserve adjustment (group)', 'pn'), __('Reserve adjustment (group)', 'pn'), 'read', "pn_mass_reserve", array($premiumbox, 'admin_temp'));
	}
}

add_filter('pn_caps', 'currency_reserve_pn_caps'); 
function currency_reserve_pn_caps($pn_caps) {
	
	$pn_caps['pn_currency_reserve'] = __('Use adjustment reserve', 'pn');
	
	return $pn_caps;
}

add_filter('change_bid_status', 'reserve_change_bidstatus', 1000);      
function reserve_change_bidstatus($data) { 
	
	$place = $data['place'];
	$set_status = $data['set_status'];
	$bid = $data['bid'];
	$who = $data['who'];
	$old_status = $data['old_status'];
	$direction = $data['direction'];	
	
	$stop_action = intval(is_isset($data, 'stop')); 
	if (!$stop_action) {
	
		update_currency_reserve($bid->currency_id_give, '', 'give_' . $set_status);
		update_currency_reserve($bid->currency_id_get, '', 'get_' . $set_status);
	
	}

	return $data;
}	

add_action('item_currency_edit', 'reserve_item_currency_edit', 1, 2);
function reserve_item_currency_edit($data_id, $array) {
	
	$object = (object)$array;
	update_currency_reserve($data_id, $object);
	
} 

add_action('item_currency_delete','reserve_item_currency_delete', 10, 2);
function reserve_item_currency_delete($id, $item) {
	global $wpdb;
	
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency_reserv WHERE currency_id = '$id'");
	foreach ($items as $item) {
		$item_id = $item->id;
		$res = apply_filters('item_currency_reserve_delete_before', pn_ind(), $item_id, $item);
		if ($res['ind']) {
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "currency_reserv WHERE id = '$item_id'");
			do_action('item_currency_reserve_delete', $item_id, $item, $result);
		}
	}
}

add_action('item_currency_code_edit', 'reserve_item_currency_code_edit', 1, 2);
function reserve_item_currency_code_edit($data_id, $array) {
	global $wpdb;
	
	$currency_code_title = is_isset($array, 'currency_code_title');
	$wpdb->update($wpdb->prefix . 'currency_reserv', array('currency_code_title' => $currency_code_title), array('currency_code_id' => $data_id));
}

add_action('item_currency_reserve_delete', 'reserve_item_currency_reserve_delete', 10, 2);
add_action('item_currency_reserve_basket', 'reserve_item_currency_reserve_delete', 10, 2);
add_action('item_currency_reserve_unbasket', 'reserve_item_currency_reserve_delete', 10, 2);
function reserve_item_currency_reserve_delete($id, $item) {
	
	update_currency_reserve($item->currency_id);
	
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'list');
$premiumbox->include_path(__FILE__, 'add');
$premiumbox->include_path(__FILE__, 'mass_add');
$premiumbox->include_path(__FILE__, 'settings');