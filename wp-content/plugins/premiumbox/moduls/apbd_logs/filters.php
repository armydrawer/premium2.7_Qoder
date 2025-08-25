<?php
if (!defined('ABSPATH')) { exit(); }

add_action('item_currency_code_delete', 'apbd_item_currency_code_delete'); 
function apbd_item_currency_code_delete($id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "db_admin_logs WHERE item_id = '$id' AND tbl_name = 'currency_code'");
}

add_action('item_currency_code_save', 'apbd_item_currency_code_save', 1000, 4);
function apbd_item_currency_code_save($id, $item, $result, $arr) {
	
	insert_apbd('currency_code', $id, $arr, $item);
	
}

add_action('item_currency_code_basket', 'apbd_item_currency_code_basket', 1000, 3);
function apbd_item_currency_code_basket($id, $item, $result) {
	
	$arr = array(
		'auto_status' => '0',
	);
	insert_apbd('currency_code', $id, $arr, $item);
	
}

add_action('item_currency_code_unbasket', 'apbd_item_currency_code_unbasket', 1000, 3);
function apbd_item_currency_code_unbasket($id, $item, $result) {
	
	$arr = array(
		'auto_status' => '1',
	);
	insert_apbd('currency_code', $id, $arr, $item);
	
}

add_action('item_currency_code_add', 'apbd_item_currency_code_edit', 1000, 2);
add_action('item_currency_code_edit', 'apbd_item_currency_code_edit', 1000, 3);
function apbd_item_currency_code_edit($id, $array, $ldata = '') {	

	insert_apbd('currency_code', $id, $array, $ldata);
	
}

add_action('pn_adminpage_content_pn_add_currency_codes', 'apbd_adminpage_content_pn_add_currency_codes', 11);
function apbd_adminpage_content_pn_add_currency_codes() {
		
	view_apbd('currency_code');
	
}

/***********************/

add_action('item_psys_delete', 'apbd_item_psys_delete'); 
function apbd_item_psys_delete($id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "db_admin_logs WHERE item_id = '$id' AND tbl_name = 'psys'");
}

add_action('item_psys_save', 'apbd_item_psys_save', 1000, 4);
function apbd_item_psys_save($id, $item, $result, $arr) {
	
	insert_apbd('psys', $id, $arr, $item);
	
}

add_action('item_psys_basket', 'apbd_item_psys_basket', 1000, 3);
function apbd_item_psys_basket($id, $item, $result) {
	
	$arr = array(
		'auto_status' => '0',
	);
	insert_apbd('psys', $id, $arr, $item);
	
}

add_action('item_psys_unbasket', 'apbd_item_psys_unbasket', 1000, 3);
function apbd_item_psys_unbasket($id, $item, $result) {
	
	$arr = array(
		'auto_status' => '1',
	);
	insert_apbd('psys', $id, $arr, $item);
	
}

add_action('item_psys_add', 'apbd_item_psys_edit', 1000, 2);
add_action('item_psys_edit', 'apbd_item_psys_edit', 1000, 3);
function apbd_item_psys_edit($id, $array, $ldata = '') {	

	insert_apbd('psys', $id, $array, $ldata);
	
}

add_action('pn_adminpage_content_pn_add_psys', 'apbd_adminpage_content_pn_add_psys', 11);
function apbd_adminpage_content_pn_add_psys() {
		
	view_apbd('psys');
	
}

/***********************/

add_action('item_currency_delete', 'apbd_item_currency_delete'); 
function apbd_item_currency_delete($id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "db_admin_logs WHERE item_id = '$id' AND tbl_name = 'currency'");
}

add_action('item_currency_save', 'apbd_item_currency_save', 1000, 4);
function apbd_item_currency_save($id, $item, $result, $arr) {
	
	insert_apbd('currency', $id, $arr, $item);
	
}

add_action('item_currency_basket', 'apbd_item_currency_basket', 1000, 3);
function apbd_item_currency_basket ($id, $item, $result) {
	
	$arr = array(
		'auto_status' => '0',
	);
	insert_apbd('currency', $id, $arr, $item);
	
}

add_action('item_currency_unbasket', 'apbd_item_currency_unbasket', 1000, 3);
function apbd_item_currency_unbasket($id, $item, $result) {
	
	$arr = array(
		'auto_status' => '1',
	);
	insert_apbd('currency', $id, $arr, $item);
	
}

add_action('item_currency_active', 'apbd_item_currency_active', 1000, 3);
function apbd_item_currency_active($id, $item, $result) {
	
	$arr = array(
		'currency_status' => '1',
	);
	insert_apbd('currency', $id, $arr, $item);
	
}

add_action('item_currency_deactive', 'apbd_item_currency_deactive', 1000, 3);
function apbd_item_currency_deactive($id, $item, $result) {
	
	$arr = array(
		'currency_status' => '0',
	);
	insert_apbd('currency', $id, $arr, $item);
	
}

add_action('item_currency_add', 'apbd_item_currency_edit', 1000, 2);
add_action('item_currency_edit', 'apbd_item_currency_edit', 1000, 3);
function apbd_item_currency_edit($id, $array, $ldata = '') {	

	insert_apbd('currency', $id, $array, $ldata);
	
}

add_action('pn_adminpage_content_pn_add_currency', 'apbd_adminpage_content_pn_add_currency', 11);
function apbd_adminpage_content_pn_add_currency() {
		
	view_apbd('currency');
	
}

/***********************/

add_action('item_currency_reserve_delete', 'apbd_item_currency_reserve_delete'); 
function apbd_item_currency_reserve_delete($id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "db_admin_logs WHERE item_id = '$id' AND tbl_name = 'reserve'");
}

add_action('item_currency_reserve_basket', 'apbd_item_currency_reserve_basket', 1000, 3);
function apbd_item_currency_reserve_basket($id, $item, $result) {
	
	$arr = array(
		'auto_status' => '0',
	);
	insert_apbd('reserve', $id, $arr, $item);
	
}

add_action('item_currency_reserve_unbasket', 'apbd_item_currency_reserve_unbasket', 1000, 3);
function apbd_item_currency_reserve_unbasket($id, $item, $result) {
	
	$arr = array(
		'auto_status' => '1',
	);
	insert_apbd('reserve', $id, $arr, $item);
	
}

add_action('item_currency_reserve_add', 'apbd_item_currency_reserve_edit', 1000, 2);
add_action('item_currency_reserve_edit', 'apbd_item_currency_reserve_edit', 1000, 3);
function apbd_item_currency_reserve_edit($id, $array, $ldata = '') {	

	insert_apbd('reserve', $id, $array, $ldata);
	
}

add_action('pn_adminpage_content_pn_add_currency_reserve', 'apbd_adminpage_content_pn_add_currency_reserve', 11);
function apbd_adminpage_content_pn_add_currency_reserve() {
	
	view_apbd('reserve');
	
}

/********************/

add_action('item_iac_delete', 'apbd_item_iac_delete'); 
function apbd_item_iac_delete($id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "db_admin_logs WHERE item_id = '$id' AND tbl_name = 'iac'");
}

add_action('item_iac_approve', 'apbd_item_iac_approve', 1000, 3);
function apbd_item_iac_approve($id, $item, $result) {
	
	$arr = array(
		'status' => '1',
	);
	insert_apbd('iac', $id, $arr, $item);
	
}

add_action('item_iac_unapprove', 'apbd_item_iac_unapprove', 1000, 3);
function apbd_item_iac_unapprove($id, $item, $result) {
	
	$arr = array(
		'status' => '0',
	);
	insert_apbd('iac', $id, $arr, $item);
	
}

add_action('item_iac_add', 'apbd_item_iac_edit', 1000, 2);
add_action('item_iac_edit', 'apbd_item_iac_edit', 1000, 3);
function apbd_item_iac_edit($id, $array, $ldata = '') {	

	insert_apbd('iac', $id, $array, $ldata);
	
}

add_action('pn_adminpage_content_pn_iac_add', 'apbd_adminpage_content_pn_iac_add', 11);
function apbd_adminpage_content_pn_iac_add() {
	
	view_apbd('iac');
	
}

/*************************/

add_action('item_discount_delete', 'apbd_item_discount_delete'); 
function apbd_item_discount_delete($id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "db_admin_logs WHERE item_id = '$id' AND tbl_name = 'discount'");
}

add_action('item_discount_add', 'apbd_item_discount_edit', 1000, 2);
add_action('item_discount_edit', 'apbd_item_discount_edit', 1000, 3);
function apbd_item_discount_edit($id, $array, $ldata = '') {	

	insert_apbd('discount', $id, $array, $ldata);
	
}

add_action('pn_adminpage_content_pn_add_discount', 'apbd_adminpage_content_pn_add_discount', 11);
function apbd_adminpage_content_pn_add_discount() {
	
	view_apbd('discount');
	
}

/*************************/

add_action('item_direction_delete', 'apbd_item_direction_delete'); 
function apbd_item_direction_delete($id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "db_admin_logs WHERE item_id = '$id' AND tbl_name = 'direction'");
}

add_action('item_direction_save', 'apbd_item_direction_save', 1000, 4);
function apbd_item_direction_save($id, $item, $result, $arr) {
	
	insert_apbd('direction', $id, $arr, $item);
	
}

add_action('item_direction_basket', 'apbd_item_direction_basket', 1000, 3);
function apbd_item_direction_basket($id, $item, $result) {
	
	$arr = array(
		'auto_status' => '0',
	);
	insert_apbd('direction', $id, $arr, $item);
	
}

add_action('item_direction_unbasket', 'apbd_item_direction_unbasket', 1000, 3);
function apbd_item_direction_unbasket($id, $item, $result) {
	
	$arr = array(
		'auto_status' => '1',
	);
	insert_apbd('direction', $id, $arr, $item);
	
}

add_action('item_direction_active', 'apbd_item_direction_active', 1000, 3);
function apbd_item_direction_active($id, $item, $result) {
	
	$arr = array(
		'direction_status' => '1',
	);
	insert_apbd('direction', $id, $arr, $item);
	
}

add_action('item_direction_hold', 'apbd_item_direction_hold', 1000, 3);
function apbd_item_direction_hold($id, $item, $result) {
	
	$arr = array(
		'direction_status' => '2',
	);
	insert_apbd('direction', $id, $arr, $item);
	
}

add_action('item_direction_deactive', 'apbd_item_direction_deactive', 1000, 3);
function apbd_item_direction_deactive($id, $item, $result) {
	
	$arr = array(
		'direction_status' => '0',
	);
	insert_apbd('direction', $id, $arr, $item);
	
}

add_action('item_direction_add', 'apbd_item_direction_edit', 1000, 2);
add_action('item_direction_edit', 'apbd_item_direction_edit', 1000, 3);
function apbd_item_direction_edit($id, $array, $ldata = '') {	

	insert_apbd('direction', $id, $array, $ldata);
	
}

add_action('pn_adminpage_content_pn_add_directions', 'apbd_adminpage_content_pn_add_directions', 11);
function apbd_adminpage_content_pn_add_directions() {
	
	view_apbd('direction');
	
}

/*************************/

add_action('item_partnpers_delete', 'apbd_item_partnpers_delete'); 
function apbd_item_partnpers_delete($id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "db_admin_logs WHERE item_id = '$id' AND tbl_name = 'partnpers'");
}

add_action('item_partnpers_add', 'apbd_item_partnpers_edit', 1000, 2);
add_action('item_partnpers_edit', 'apbd_item_partnpers_edit', 1000, 3);
function apbd_item_partnpers_edit($id, $array, $ldata = '') {	

	insert_apbd('partnpers', $id, $array, $ldata);
	
}

add_action('pn_adminpage_content_pn_add_partnpers', 'apbd_adminpage_content_pn_add_partnpers', 11);
function apbd_adminpage_content_pn_add_partnpers() {
	
	view_apbd('partnpers');
	
}

/*************************/

add_action('item_user_payouts_delete', 'apbd_item_user_payouts_delete'); 
function apbd_item_user_payouts_delete($id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "db_admin_logs WHERE item_id = '$id' AND tbl_name = 'payouts'");
}

add_action('item_user_payouts_basket', 'apbd_item_user_payouts_basket', 1000, 3);
function apbd_item_user_payouts_basket($id, $item, $result) {
	
	$arr = array(
		'auto_status' => '0',
	);
	insert_apbd('payouts', $id, $arr, $item);
	
}

add_action('item_user_payouts_unbasket', 'apbd_item_user_payouts_unbasket', 1000, 3);
function apbd_item_user_payouts_unbasket($id, $item, $result) {
	
	$arr = array(
		'auto_status' => '1',
	);
	insert_apbd('payouts', $id, $arr, $item);
	
}

add_action('item_user_payouts_wait', 'apbd_item_user_payouts_wait', 1000, 3);
function apbd_item_user_payouts_wait($id, $item, $result) {
	
	$arr = array(
		'status' => '0',
	);
	insert_apbd('payouts', $id, $arr, $item);
	
}

add_action('item_user_payouts_success', 'apbd_item_user_payouts_success', 1000, 3);
function apbd_item_user_payouts_success($id, $item, $result) {
	
	$arr = array(
		'status' => '1',
	);
	insert_apbd('payouts', $id, $arr, $item);
	
}

add_action('item_user_payouts_not', 'apbd_item_user_payouts_not', 1000, 3);
function apbd_item_user_payouts_not($id, $item, $result) {
	
	$arr = array(
		'status' => '2',
	);
	insert_apbd('payouts', $id, $arr, $item);
	
}

add_action('item_user_payouts_edit', 'apbd_item_user_payouts_edit', 1000, 3);
add_action('item_user_payouts_add', 'apbd_item_user_payouts_edit', 1000, 2);
function apbd_item_user_payouts_edit($id, $array, $ldata = '') {	

	insert_apbd('payouts', $id, $array, $ldata);
	
}

add_action('pn_adminpage_content_pn_add_payouts', 'apbd_adminpage_content_pn_add_payouts', 11);
function apbd_adminpage_content_pn_add_payouts() {
	
	view_apbd('payouts');
	
}

/*************************/

add_action('item_bcorrs_delete', 'apbd_item_bcorrs_delete'); 
function apbd_item_bcorrs_delete($id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "db_admin_logs WHERE item_id = '$id' AND tbl_name = 'bcorrs'");
}

add_action('item_bcorrs_save', 'apbd_item_bcorrs_save', 1000, 4);
function apbd_item_bcorrs_save($id, $item, $result, $arr) {
	
	insert_apbd('bcorrs', $id, $arr, $item);
	
}

add_action('item_bcorrs_active', 'apbd_item_bcorrs_active', 1000, 3);
function apbd_item_bcorrs_active($id, $item, $result) {
	
	$arr = array(
		'status' => '1',
	);
	insert_apbd('bcorrs', $id, $arr, $item);
	
}

add_action('item_bcorrs_deactive', 'apbd_item_bcorrs_deactive', 1000, 3);
function apbd_item_bcorrs_deactive($id, $item, $result) {
	
	$arr = array(
		'status' => '0',
	);
	insert_apbd('bcorrs', $id, $arr, $item);
	
}

add_action('item_bcorrs_tab_add', 'apbd_item_bcorrs_edit', 1000, 2);
add_action('item_bcorrs_tab_edit', 'apbd_item_bcorrs_edit', 1000, 3);
add_action('item_bcorrs_add', 'apbd_item_bcorrs_edit', 1000, 2);
add_action('item_bcorrs_edit', 'apbd_item_bcorrs_edit', 1000, 3);
function apbd_item_bcorrs_edit($id, $array, $ldata = '') {	

	insert_apbd('bcorrs', $id, $array, $ldata);
	
}

add_action('pn_adminpage_content_pn_add_bcorrs', 'apbd_adminpage_content_pn_add_bcorrs', 11);
function apbd_adminpage_content_pn_add_bcorrs() {
	
	view_apbd('bcorrs');
	
}

/*******************/

add_action('item_bccorrs_delete', 'apbd_item_bccorrs_delete'); 
function apbd_item_bccorrs_delete($id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "db_admin_logs WHERE item_id = '$id' AND tbl_name = 'bccorrs'");
}

add_action('item_bccorrs_save', 'apbd_item_bccorrs_save', 1000, 4);
function apbd_item_bccorrs_save($id, $item, $result, $arr) {
	
	insert_apbd('bccorrs', $id, $arr, $item);
	
}

add_action('item_bccorrs_active', 'apbd_item_bccorrs_active', 1000, 3);
function apbd_item_bccorrs_active($id, $item, $result) {
	
	$arr = array(
		'status' => '1',
	);
	insert_apbd('bccorrs', $id, $arr, $item);
	
}

add_action('item_bccorrs_deactive', 'apbd_item_bccorrs_deactive', 1000, 3);
function apbd_item_bccorrs_deactive($id, $item, $result) {
	
	$arr = array(
		'status' => '0',
	);
	insert_apbd('bccorrs', $id, $arr, $item);
	
}

add_action('item_bccorrs_tab_add', 'apbd_item_bccorrs_edit', 1000, 2);
add_action('item_bccorrs_tab_edit', 'apbd_item_bccorrs_edit', 1000, 3);
add_action('item_bccorrs_add', 'apbd_item_bccorrs_edit', 1000, 2);
add_action('item_bccorrs_edit', 'apbd_item_bccorrs_edit', 1000, 3);
function apbd_item_bccorrs_edit($id, $array, $ldata = '') {	

	insert_apbd('bccorrs', $id, $array, $ldata);
	
}

add_action('pn_adminpage_content_pn_bc_add_corrs', 'apbd_adminpage_content_pn_bc_add_corrs', 11);
function apbd_adminpage_content_pn_bc_add_corrs() {
	
	view_apbd('bccorrs');
	
}

/*************************/

add_filter('change_bid_status', 'apbd_change_bidstatus', 200);    
function apbd_change_bidstatus($data) { 
	global $wpdb;

	$stop_action = intval(is_isset($data, 'stop'));
	if (!$stop_action) {
		$set_status = $data['set_status'];
		if ('realdelete' == $set_status or 'archived' == $set_status) {
			$id = $data['bid']->id;
			$wpdb->query("DELETE FROM " . $wpdb->prefix . "db_admin_logs WHERE item_id = '$id' AND tbl_name = 'bids'");
		}	
	}

	return $data;
}

add_action('item_pexch_save', 'apbd_item_pexch_save', 1000, 4);
function apbd_item_pexch_save($id, $item, $result, $arr) {
	
	insert_apbd('bids', $id, $arr, $item);
	
}

add_action('item_pexch_approve', 'apbd_item_pexch_approve', 1000, 3);
function apbd_item_pexch_approve($id, $item, $result) {
	
	$arr = array(
		'pcalc' => '1',
	);
	insert_apbd('bids', $id, $arr, $item);
	
}

add_action('item_pexch_unapprove', 'apbd_item_pexch_unapprove', 1000, 3);
function apbd_item_pexch_unapprove($id, $item, $result) {
	
	$arr = array(
		'pcalc' => '0',
	);
	insert_apbd('bids', $id, $arr, $item);
	
}	

add_action('onebid_edit','apbd_pn_onebid_edit', 11, 4);
function apbd_pn_onebid_edit($id, $array, $ldata = '', $lists = '') {	

	insert_apbd('bids', $id, $array, $ldata);

}	
	

add_action('onebid_edit_view','apbd_onebid_edit', 11, 3);
function apbd_onebid_edit($id, $item, $lists) {
	
	view_apbd('bids');

}

/*************************/

add_action('item_parser_pairs_delete', 'apbd_item_parser_pairs_delete'); 
function apbd_item_parser_pairs_delete($id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "db_admin_logs WHERE item_id = '$id' AND tbl_name = 'parser_pairs'");
}

add_action('item_parser_pairs_save', 'apbd_item_parser_pairs_save', 1000, 4);
function apbd_item_parser_pairs_save($id, $item, $result, $arr) {
	
	insert_apbd('parser_pairs', $id, $arr, $item);
	
}

add_action('item_parser_pairs_edit', 'apbd_item_parser_pairs_edit', 1000, 3);
add_action('item_parser_pairs_add', 'apbd_item_parser_pairs_edit', 1000, 2);
function apbd_item_parser_pairs_edit($id, $array, $ldata = '') {	

	insert_apbd('parser_pairs', $id, $array, $ldata);
	
}

add_action('pn_adminpage_content_pn_add_parser_pairs', 'apbd_adminpage_content_pn_add_parser_pairs', 11);
function apbd_adminpage_content_pn_add_parser_pairs() {
	
	view_apbd('parser_pairs');
	
}

/*************************/

add_action('item_indxs_delete', 'apbd_item_indxs_delete'); 
function apbd_item_indxs_delete($id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "db_admin_logs WHERE item_id = '$id' AND tbl_name = 'indxs'");
}

add_action('item_indxs_save', 'apbd_item_indxs_save', 1000, 4);
function apbd_item_indxs_save($id, $item, $result, $arr) {
	
	insert_apbd('indxs', $id, $arr, $item);
	
}

add_action('item_indxs_edit', 'apbd_item_indxs_edit', 1000, 3);
add_action('item_indxs_add', 'apbd_item_indxs_edit', 1000, 2);
function apbd_item_indxs_edit($id, $array, $ldata = '') {	

	insert_apbd('indxs', $id, $array, $ldata);
	
}

add_action('pn_adminpage_content_pn_add_indxs', 'apbd_adminpage_content_pn_add_indxs', 11);
function apbd_adminpage_content_pn_add_indxs() {
	
	view_apbd('indxs');
	
}