<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('pn_caps', 'currency_pn_caps');
function currency_pn_caps($pn_caps) {
	
	$pn_caps['pn_currency'] = __('Use currencies', 'pn');
	$pn_caps['pn_change_ir'] = __('To change internal exchange rate for code currencies', 'pn');
	
	return $pn_caps;
}

add_action('admin_menu', 'admin_menu_currency');
function admin_menu_currency() {
	global $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_currency')) {
		add_menu_page(__('Currency', 'pn'), __('Currency', 'pn'), 'read', "pn_currency", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('currency'), 625);	
		add_submenu_page("pn_currency", __('Add currency', 'pn'), __('Add currency', 'pn'), 'read', "pn_add_currency", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_currency", __('Sort currency', 'pn'), __('Sort currency', 'pn'), 'read', "pn_sort_currency", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_currency", __('Currency codes', 'pn'), __('Currency codes', 'pn'), 'read', "pn_currency_codes", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_currency", __('Add currency code', 'pn'), __('Add currency code', 'pn'), 'read', "pn_add_currency_codes", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_currency", __('Payment systems', 'pn'), __('Payment systems', 'pn'), 'read', "pn_psys", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_currency", __('Add payment system', 'pn'), __('Add payment system', 'pn'), 'read', "pn_add_psys", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_currency", __('Custom currency fields', 'pn'), __('Custom currency fields', 'pn'), 'read', "pn_cfc", array($premiumbox, 'admin_temp'));	
		add_submenu_page("pn_currency", __('Add custom field', 'pn'), __('Add custom field', 'pn'), 'read', "pn_add_cfc", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_currency", __('Sort custom fields', 'pn'), __('Sort custom fields', 'pn'), 'read', "pn_sort_cfc", array($premiumbox, 'admin_temp'));
	}
	
}

add_action('item_currency_delete', 'def_item_currency_delete',0,2);
function def_item_currency_delete($data_id, $item) {
	global $wpdb;

	$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."currency_meta WHERE item_id = '$data_id'");
	foreach ($items as $item) {
		$item_id = $item->id;
		$res = apply_filters('item_currencymeta_delete_before', pn_ind(), $item_id, $item);
		if ($res['ind']) {
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "currency_meta WHERE id = '$item_id'");
			do_action('item_currencymeta_delete', $item_id, $item, $result);
		}
	}
	
}

add_action('item_currency_code_edit', 'def_item_currency_code_edit',0,2);
function def_item_currency_code_edit($data_id, $array) {
	global $wpdb;
	
	if (isset($array['currency_code_title'])) {
		$wpdb->update($wpdb->prefix . 'currency', array('currency_code_title' => $array['currency_code_title']), array('currency_code_id' => $data_id));
	}
	
}

add_action('item_currency_code_delete', 'def_item_currency_code_delete');
function def_item_currency_code_delete($id) {
	global $wpdb;
	
	$wpdb->update($wpdb->prefix . 'currency', array('currency_code_title' => '', 'currency_code_id' => 0, 'currency_status' => 0), array('currency_code_id' => $id));
}

add_action('item_psys_edit','def_item_psys_edit', 0, 2);
function def_item_psys_edit($data_id, $array) {
	global $wpdb;	
	
	if (isset($array['psys_title'])) { 
		$wpdb->update($wpdb->prefix . 'currency', array('psys_title' => $array['psys_title'], 'psys_logo' => is_isset($array, 'psys_logo')), array('psys_id' => $data_id));
	}
	
}

add_action('item_psys_delete', 'def_item_psys_delete');
function def_item_psys_delete($id) {
	global $wpdb;
	
	$wpdb->update($wpdb->prefix . 'currency', array('psys_title' => '', 'psys_id' => 0, 'currency_status' => 0), array('psys_id' => $id));
}

function list_currency_codes($default = '') {
	global $wpdb;

	$lists = array();
	$default = trim($default);
	if ($default) {
		$lists[0] = '--' . $default . '--';
	}
	$lists_datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency_codes ORDER BY currency_code_title ASC");
	foreach ($lists_datas as $item) {
		$lists[$item->id] = is_site_value($item->currency_code_title) . pn_item_basket($item);
	}
	
	return $lists;
}

function list_psys($default = '') {
	global $wpdb;
	
	$psys = $psys_v = array();
	$psys_datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "psys");
	foreach ($psys_datas as $item) {
		$psys_v[$item->id] = pn_strip_input(ctv_ml($item->psys_title)) . pn_item_basket($item);
	}
	asort($psys_v);
	
	$default = trim($default);
	if ($default) {
		$psys[0] = '--' . $default . '--';
	}
	
	foreach ($psys_v as $k => $v) {
		$psys[$k] = $v;
	}
	
	return $psys;
}

add_action('item_currency_delete', 'cfc_item_currency_delete');
function cfc_item_currency_delete($item_id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "cf_currency WHERE currency_id = '$item_id'");
}

add_action('item_cfc_delete', 'def_cfc_delete');
function def_cfc_delete($item_id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "cf_currency WHERE cf_id = '$item_id'");
}

function list_currency($default, $show_decimal = 0) {
	global $wpdb;

	$currency = $currency_info = array();
	$n_currency = $n_currency_info = array();
	$currency_datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency");
	foreach ($currency_datas as $curr) {
		$title = pn_strip_input(ctv_ml($curr->psys_title)) . ' ' . is_site_value($curr->currency_code_title);
		$n_currency[$curr->id] = $title . pn_item_status($curr, 'currency_status') . pn_item_basket($curr);
		$n_currency_info[$curr->id] = array(
			'title' => $title . ' [id:' . $curr->id . ']' . pn_item_status($curr, 'currency_status') . pn_item_basket($curr),
			'decimal' => $curr->currency_decimal,
		);
	}
	
	asort($n_currency);
	$n_currency_info = pn_array_sort($n_currency_info, 'title', 'asc', 'text');
	
	$default = trim($default);
	if ($default) {
		$currency[0] = '--' . $default . '--';
		$currency_info[0] = array(
			'title' => '--' . $default . '--',
			'decimal' => 0,
		);
	}	
	
	foreach ($n_currency as $n_c_k => $n_c_v) {
		$currency[$n_c_k] = $n_c_v;
	}
	
	foreach ($n_currency_info as $n_c_k => $n_c_v) {
		$currency_info[$n_c_k] = $n_c_v;
	}
	
	if (1 == $show_decimal) {
		return $currency_info;
	} else {
		return $currency;
	}
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'add_currency');
$premiumbox->include_path(__FILE__, 'list_currency');
$premiumbox->include_path(__FILE__, 'sort_currency');
$premiumbox->include_path(__FILE__, 'add_currency_codes');
$premiumbox->include_path(__FILE__, 'list_currency_codes');
$premiumbox->include_path(__FILE__, 'add_psys');
$premiumbox->include_path(__FILE__, 'list_psys');
$premiumbox->include_path(__FILE__, 'add_cfc');
$premiumbox->include_path(__FILE__, 'list_cfc');
$premiumbox->include_path(__FILE__, 'sort_cfc');