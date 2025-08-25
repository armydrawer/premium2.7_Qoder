<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]API[:en_US][ru_RU:]API[:ru_RU]
description: [en_US:]API[:en_US][ru_RU:]API[:ru_RU]
version: 2.7.0
category: [en_US:]Other[:en_US][ru_RU:]Остальное[:ru_RU]
cat: other
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('pn_plugin_activate', 'db_all_moduls_active_api');
add_action('all_moduls_active_' . $name, 'db_all_moduls_active_api');
function db_all_moduls_active_api() {
	global $wpdb;
			
	$table_name = $wpdb->prefix . "api";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`create_date` datetime NOT NULL,
		`user_id` bigint(20) NOT NULL default '0',
		`user_login` varchar(250) NOT NULL,
		`enable_ip` longtext NOT NULL,
		`api_login` varchar(250) NOT NULL,
		`api_key` varchar(250) NOT NULL,
		`api_actions` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`create_date`),
		INDEX (`api_login`),
		INDEX (`api_key`),
		INDEX (`user_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);

	$table_name = $wpdb->prefix . "api_logs"; 
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`api_login` varchar(250) NOT NULL,
		`api_key` varchar(250) NOT NULL,
		`api_action` varchar(250) NOT NULL,
		`ip` varchar(250) NOT NULL,
		`post_data` longtext NOT NULL,
		`headers_data` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`create_date`),
		INDEX (`api_login`),
		INDEX (`api_key`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);

	$table_name = $wpdb->prefix . "api_callbacks"; 
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`callback_url` varchar(250) NOT NULL,
		`bid_id` bigint(20) NOT NULL default '0',
		`ip` varchar(250) NOT NULL,
		`post_data` longtext NOT NULL,
		`result_data` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`create_date`),
		INDEX (`bid_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "api_callbacks LIKE 'post_data'");
	if (0 == $query) { /* 2.6 */
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "api_callbacks ADD `post_data` longtext NOT NULL");
	}	

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'work_api'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `work_api` int(1) NOT NULL default '0'");
	}

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'api_login'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `api_login` varchar(50) NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'api_id'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `api_id` varchar(150) NOT NULL default '0'");
	}

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'api'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `api` int(1) NOT NULL default '0'");
    }	

}

add_action('delete_user', 'api_delete_user');
function api_delete_user($user_id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "api WHERE user_id = '$user_id'");		
}

add_filter('pn_caps', 'api_pn_caps');
function api_pn_caps($pn_caps) {
	
	$pn_caps['pn_api'] = __('API', 'pn');
	
	return $pn_caps;
}

add_action('admin_menu', 'admin_menu_api', 20000);
function admin_menu_api() {
	
	$plugin = get_plugin_class();
	if (current_user_can('administrator') or current_user_can('pn_api')) {
		add_menu_page(__('API','pn'), __('API', 'pn'), 'read', 'all_api', array($plugin, 'admin_temp'), $plugin->get_icon_link('geoip'));  
		add_submenu_page("all_api", __('Add', 'pn'), __('Add', 'pn'), 'read', "all_add_api", array($plugin, 'admin_temp'));
		add_submenu_page("all_api", __('Logs', 'pn'), __('Logs', 'pn'), 'read', "all_logs_api", array($plugin, 'admin_temp'));
		add_submenu_page("all_api", __('Callbacks', 'pn'), __('Callbacks', 'pn'), 'read', "all_callbacks_api", array($plugin, 'admin_temp'));
		add_submenu_page("all_api", __('Settings', 'pn'), __('Settings', 'pn'), 'read', "all_settings_api", array($plugin, 'admin_temp'));
	}	
	
}

function unique_api_key() {
	global $wpdb;
	
	$value = get_random_password(32, true, true);
	if ($value) {
		$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "api WHERE api_key = '$value'");
		if ($cc > 0) {
			return unique_api_key();
		} else {
			return $value;
		}
	} 
	
	return '';	
}

function is_api_key($item) {
	
	$item = pn_string($item);
	$new_item = '';
	if (preg_match("/^[a-zA-z0-9]{32}$/", $item, $matches)) {
		$new_item = $item;
	} 
	
	return $new_item;
}

add_filter('set_exchange_cat_filters', 'set_exchange_cat_filters_api');
function set_exchange_cat_filters_api($cats) {
	
	$cats['api'] = __('API', 'pn');
	
	return $cats;
}

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'methods');
$plugin->include_path(__FILE__, 'settings');
$plugin->include_path(__FILE__, 'user_settings');
$plugin->include_path(__FILE__, 'add');
$plugin->include_path(__FILE__, 'list');
$plugin->include_path(__FILE__, 'list_logs');
$plugin->include_path(__FILE__, 'list_callbacks');
$plugin->include_path(__FILE__, 'user_api');
$plugin->include_path(__FILE__, 'api');
$plugin->include_path(__FILE__, 'bids');
$plugin->include_path(__FILE__, 'shortcodes');