<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]GEO IP[:en_US][ru_RU:]GEO IP[:ru_RU]
description: [en_US:]Working with countries[:en_US][ru_RU:]Работа со странами[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_geoip');
add_action('pn_plugin_activate', 'bd_all_moduls_active_geoip');
function bd_all_moduls_active_geoip() {
	global $wpdb;	

	$table_name = $wpdb->prefix . "geoip_ips";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`theip` varchar(250) NOT NULL,
		`thetype` int(0) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`theip`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'user_country'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `user_country` varchar(10) NOT NULL");
	}

	$table_name = $wpdb->prefix . "geoip_memory";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`ip` varchar(250) NOT NULL,
		`country_attr` varchar(20) NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`ip`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'not_country'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `not_country` longtext NOT NULL");
	}	

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'only_country'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `only_country` longtext NOT NULL");
	}	

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'user_country'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `user_country` varchar(10) NOT NULL");
	}	

}

add_filter('pn_caps', 'geoip_pn_caps');
function geoip_pn_caps($pn_caps) {
		
	$pn_caps['pn_geoip'] = __('Use GEO IP', 'pn');
		
	return $pn_caps;
}

add_action('admin_menu', 'admin_menu_geoip');
function admin_menu_geoip() {
	
	$plugin = get_plugin_class();
	if (current_user_can('administrator') or current_user_can('pn_geoip')) {
		add_menu_page(__('GEO IP', 'pn'), __('GEO IP', 'pn'), 'read', 'all_geoip', array($plugin, 'admin_temp'), $plugin->get_icon_link('geoip'));  
		add_submenu_page("all_geoip", __('Settings', 'pn'), __('Settings', 'pn'), 'read', "all_geoip", array($plugin, 'admin_temp'));
		add_submenu_page("all_geoip", __('Blacklist', 'pn'), __('Blacklist', 'pn'), 'read', "all_geoip_blacklist", array($plugin, 'admin_temp'));
		add_submenu_page("all_geoip", __('Block IP', 'pn'), __('Block IP', 'pn'), 'read', "all_geoip_addblacklist", array($plugin, 'admin_temp'));	
		add_submenu_page("all_geoip", __('White list', 'pn'), __('White list', 'pn'), 'read', "all_geoip_whitelist", array($plugin, 'admin_temp'));
		add_submenu_page("all_geoip", __('Allow IP', 'pn'), __('Allow IP', 'pn'), 'read', "all_geoip_addwhitelist", array($plugin, 'admin_temp'));
		add_submenu_page("all_geoip", __('IP determination settings', 'pn'), __('IP determination settings', 'pn'), 'read', "all_geoip_settings_detected", array($plugin, 'admin_temp'));
	}
	
}

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'settings');
$plugin->include_path(__FILE__, 'detected_settings');
$plugin->include_path(__FILE__, 'blacklist');
$plugin->include_path(__FILE__, 'add_blacklist');
$plugin->include_path(__FILE__, 'whitelist');
$plugin->include_path(__FILE__, 'add_whitelist');
$plugin->include_path(__FILE__, 'filters'); 