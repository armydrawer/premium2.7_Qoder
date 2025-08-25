<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Blacklist[:en_US][ru_RU:]Черный список[:ru_RU]
description: [en_US:]Blacklist[:en_US][ru_RU:]Черный список[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_blacklist');
add_action('pn_plugin_activate', 'bd_all_moduls_active_blacklist');
function bd_all_moduls_active_blacklist() {
	global $wpdb;	

	$table_name = $wpdb->prefix . "blacklist";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`meta_key` varchar(12) NOT NULL default '0',
		`meta_value` longtext NOT NULL,
		`black_type` int(1) NOT NULL default '0',
		`comment_text` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`meta_key`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "blacklist LIKE 'comment_text'"); /* 1.6 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "blacklist ADD `comment_text` longtext NOT NULL");
	}

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "blacklist LIKE 'black_type'"); /* 2.7 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "blacklist ADD `black_type` int(1) NOT NULL default '0'");
	}		
	
}

add_action('admin_menu', 'admin_menu_blacklist');
function admin_menu_blacklist() {
	
	$plugin = get_plugin_class();
	if (current_user_can('administrator') or current_user_can('pn_blacklist')) {
		add_menu_page(__('Blacklist', 'pn'), __('Blacklist', 'pn'), 'read', 'all_blacklist', array($plugin, 'admin_temp'), $plugin->get_icon_link('blacklist'));  
		add_submenu_page("all_blacklist", __('Add', 'pn'), __('Add', 'pn'), 'read', "all_add_blacklist", array($plugin, 'admin_temp'));	
		add_submenu_page("all_blacklist", __('Add list', 'pn'), __('Add list', 'pn'), 'read', "all_add_blacklist_many", array($plugin, 'admin_temp'));
		add_submenu_page("all_blacklist", __('Settings', 'pn'), __('Settings', 'pn'), 'read', "all_settings_blacklist", array($plugin, 'admin_temp'));
	}
		
}

add_filter('pn_caps', 'blacklist_pn_caps');
function blacklist_pn_caps($pn_caps) {
	
	$pn_caps['pn_blacklist'] = __('Work with a blacklist', 'pn');
		
	return $pn_caps;
}

add_filter('list_user_notify', 'list_user_notify_blacklist');
function list_user_notify_blacklist($places_admin) {
	
	$places_admin['inblacklist'] = __('In blacklist', 'pn');
	
	return $places_admin;
}

add_filter('list_notify_tags_inblacklist', 'def_mailtemp_tags_inblacklist');
function def_mailtemp_tags_inblacklist($tags) {
	
	$tags['bid_id'] = array(
		'title' => __('Bid id', 'pn'),
		'start' => '[bid_id]',
	);	
		
	return $tags;
} 

function get_block_type($type) {
	
	$type = intval($type);
	if (1 == $type) {
		return 0;
	} elseif (2 == $type) {
		return 1;
	}
	$plugin = get_plugin_class();
	$type = intval($plugin->get_option('blacklist', 'method'));
	
	return $type;
}

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'add');
$plugin->include_path(__FILE__, 'add_many');
$plugin->include_path(__FILE__, 'comments');
$plugin->include_path(__FILE__, 'list');
$plugin->include_path(__FILE__, 'settings');
$plugin->include_path(__FILE__, 'premiumbox');