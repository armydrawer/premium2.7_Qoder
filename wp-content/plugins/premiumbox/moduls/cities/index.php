<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]City selection in the direction of exchange[:en_US][ru_RU:]Выбор города в направлении обмена[:ru_RU]
description: [en_US:]City selection in the direction of exchange[:en_US][ru_RU:]Выбор города в направлении обмена[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_cities');
add_action('pn_plugin_activate', 'bd_all_moduls_active_cities');
function bd_all_moduls_active_cities() {
	global $wpdb;
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'city'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `city` varchar(20) NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'cities'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `cities` longtext NOT NULL");
	}
	
	$table_name = $wpdb->prefix . "cities";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`create_date` datetime NOT NULL,
		`edit_date` datetime NOT NULL,
		`auto_status` int(1) NOT NULL default '1',
		`edit_user_id` bigint(20) NOT NULL default '0',		
		`title` longtext NOT NULL,
		`xml_value` varchar(150) NOT NULL,
		`status` int(1) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`create_date`),
		INDEX (`edit_date`),
		INDEX (`edit_user_id`),
		INDEX (`auto_status`),
		INDEX (`status`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);	
	
}

add_action('admin_menu', 'admin_menu_cities');
function admin_menu_cities() {
	
	$plugin = get_plugin_class();
	if (current_user_can('administrator')) {
		add_menu_page(__('Cities', 'pn'), __('Cities', 'pn'), 'read', 'all_cities', array($plugin, 'admin_temp'));  
		add_submenu_page("all_cities", __('Add', 'pn'), __('Add', 'pn'), 'read', "all_add_cities", array($plugin, 'admin_temp'));
	}
	
}

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'add');
$plugin->include_path(__FILE__, 'list');
$plugin->include_path(__FILE__, 'filters');