<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Currency accounts[:en_US][ru_RU:]Счета валют для направлений[:ru_RU]
description: [en_US:]Currency accounts[:en_US][ru_RU:]Счета валют для направлений[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_caccounts');
add_action('pn_plugin_activate', 'bd_all_moduls_active_caccounts');
function bd_all_moduls_active_caccounts() {
	global $wpdb;	
	
	$table_name = $wpdb->prefix . "curr_accounts";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`title` longtext NOT NULL,
		`tech_title` longtext NOT NULL,
		`accountnum` longtext NOT NULL,
		`accountnum_hash` longtext NOT NULL,
		`text_comment` longtext NOT NULL,
		`inday` varchar(50) NOT NULL default '0',
		`inmonth` varchar(50) NOT NULL default '0',
		`status` int(1) NOT NULL default '0',
		`accunique` int(1) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`status`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "curr_accounts LIKE 'accunique'");  /* 2.5 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "curr_accounts ADD `accunique` int(1) NOT NULL default '0'");
	}

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "curr_accounts LIKE 'tech_title'");  /* 2.5 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "curr_accounts ADD `tech_title` longtext NOT NULL");
	}	
	
}

add_action('admin_menu', 'admin_menu_caccounts');
function admin_menu_caccounts() {
	global $premiumbox;	
	
	if (current_user_can('administrator') or current_user_can('pn_caccounts')) {
		add_menu_page(__('Currency accounts', 'pn'), __('Currency accounts', 'pn'), 'read', 'pn_caccounts', array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('accounts'), 901);  
		add_submenu_page("pn_caccounts", __('Add', 'pn'), __('Add', 'pn'), 'read', "pn_add_caccounts", array($premiumbox, 'admin_temp'));	
		add_submenu_page("pn_caccounts", __('Add list', 'pn'), __('Add list', 'pn'), 'read', "pn_add_caccounts_many", array($premiumbox, 'admin_temp'));
	}
	
}

add_filter('pn_caps', 'caccounts_pn_caps');
function caccounts_pn_caps($pn_caps) {
	
	$pn_caps['pn_caccounts'] = __('Work with currency accounts', 'pn');
	
	return $pn_caps;
}

global $premiumbox;	
$premiumbox->include_path(__FILE__, 'add');
$premiumbox->include_path(__FILE__, 'add_many');
$premiumbox->include_path(__FILE__, 'list');
$premiumbox->include_path(__FILE__, 'filters');