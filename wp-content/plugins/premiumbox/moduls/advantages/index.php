<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Block with advantages[:en_US][ru_RU:]Блок с преимуществами[:ru_RU]
description: [en_US:]Block with advantages[:en_US][ru_RU:]Блок с преимуществами[:ru_RU]
version: 2.7.1
category: [en_US:]Settings[:en_US][ru_RU:]Настройки[:ru_RU]
cat: sett
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if (!function_exists('bd_all_moduls_active_advantages')) {
	add_action('pn_plugin_activate', 'bd_all_moduls_active_advantages');
	add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_advantages');
	function bd_all_moduls_active_advantages() {
		global $wpdb;
			
		$table_name = $wpdb->prefix . "advantages";
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
			`create_date` datetime NOT NULL,
			`edit_date` datetime NOT NULL,
			`auto_status` int(1) NOT NULL default '1',
			`edit_user_id` bigint(20) NOT NULL default '0',		
			`title` longtext NOT NULL,
			`content` longtext NOT NULL,
			`link` longtext NOT NULL,
			`img` longtext NOT NULL,
			`site_order` bigint(20) NOT NULL default '0',
			`status` bigint(20) NOT NULL default '1',
			PRIMARY KEY (`id`),
			INDEX (`auto_status`),
			INDEX (`site_order`),
			INDEX (`status`),
			INDEX (`create_date`),
			INDEX (`edit_date`),
			INDEX (`edit_user_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
		$wpdb->query($sql); 
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "advantages LIKE 'link'"); /* 2.2 */
		if (1 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "advantages CHANGE `link` `link` longtext NOT NULL");
		}			
	}
}

if (!function_exists('advantages_pn_caps')) {
	add_filter('pn_caps', 'advantages_pn_caps');
	function advantages_pn_caps($pn_caps) {
		
		$pn_caps['pn_advantages'] = __('Advantages', 'pn');
		
		return $pn_caps;
	}
}

if (!function_exists('admin_menu_advantages') and is_admin()) {
	add_action('admin_menu', 'admin_menu_advantages');
	function admin_menu_advantages() {
		
		$plugin = get_plugin_class();
		if (current_user_cans('administrator, pn_advantages')) {
			add_menu_page(__('Advantages', 'pn'), __('Advantages', 'pn'), 'read', "all_advantages", array($plugin, 'admin_temp'), $plugin->get_icon_link('advantages'), 800);
			add_submenu_page("all_advantages", __('Add', 'pn'), __('Add', 'pn'), 'read', "all_add_advantages", array($plugin, 'admin_temp'));
			add_submenu_page("all_advantages", __('Sort', 'pn'), __('Sort', 'pn'), 'read', "all_sort_advantages", array($plugin, 'admin_temp'));
		} 	
		
	}
}

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'functions');
$plugin->include_path(__FILE__, 'add');
$plugin->include_path(__FILE__, 'list');
$plugin->include_path(__FILE__, 'sort');