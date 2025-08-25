<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Currency accounts[:en_US][ru_RU:]Счета валют[:ru_RU]
description: [en_US:]Currency accounts (older version)[:en_US][ru_RU:]Счета валют (устаревшая версия)[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_vaccounts');
add_action('pn_plugin_activate', 'bd_all_moduls_active_vaccounts');
function bd_all_moduls_active_vaccounts() {
	global $wpdb;	
	
	$table_name = $wpdb->prefix . "currency_accounts";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`currency_id` bigint(20) NOT NULL default '0',
		`accountnum` longtext NOT NULL,
		`text_comment` longtext NOT NULL,
		`inday` varchar(50) NOT NULL default '0',
		`inmonth` varchar(50) NOT NULL default '0',
		`status` int(1) NOT NULL default '0',
		`accunique` int(1) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`currency_id`),
		INDEX (`status`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency_accounts LIKE 'accunique'");  /* 2.5 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency_accounts ADD `accunique` int(1) NOT NULL default '0'");
	}	
	
}

add_action('admin_menu', 'admin_menu_vaccounts');
function admin_menu_vaccounts() {
	global $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_vaccounts')) {
		add_menu_page(__('Currency accounts', 'pn'), __('Currency accounts', 'pn'), 'read', 'pn_vaccounts', array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('accounts'), 900);  
		add_submenu_page("pn_vaccounts", __('Add', 'pn'), __('Add', 'pn'), 'read', "pn_add_vaccounts", array($premiumbox, 'admin_temp'));	
		add_submenu_page("pn_vaccounts", __('Add list', 'pn'), __('Add list', 'pn'), 'read', "pn_add_vaccounts_many", array($premiumbox, 'admin_temp'));
	}
	
}

add_filter('pn_caps', 'vaccounts_pn_caps');
function vaccounts_pn_caps($pn_caps) {
	
	$pn_caps['pn_vaccounts'] = __('Work with currency accounts', 'pn');
	
	return $pn_caps;
}

add_action('item_currency_delete', 'vaccounts_item_currency_delete');
function vaccounts_item_currency_delete($id) {
	global $wpdb;
	
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency_accounts WHERE currency_id = '$id'");
	foreach ($items as $item) {
		$item_id = $item->id;
		$res = apply_filters('item_vaccounts_delete_before', pn_ind(), $item_id, $item);
		if ($res['ind']) {
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "currency_accounts WHERE id = '$item_id'");
			do_action('item_vaccounts_delete', $item_id, $item, $result);
			delete_vaccs_txtmeta($item_id);
		}
	}
	
}

global $premiumbox;	
$premiumbox->include_path(__FILE__, 'add');
$premiumbox->include_path(__FILE__, 'add_many');
$premiumbox->include_path(__FILE__, 'list');

$premiumbox->auto_include($path . '/shortcode');