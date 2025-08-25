<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Partners column[:en_US][ru_RU:]Блок партнеры[:ru_RU]
description: [en_US:]Show partners logo[:en_US][ru_RU:]Вывод логотипов партнеров[:ru_RU]
version: 2.7.0
category: [en_US:]Settings[:en_US][ru_RU:]Настройки[:ru_RU]
cat: sett
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if (!function_exists('bd_pn_moduls_active_partners')) {
	add_action('all_moduls_active_' . $name, 'bd_pn_moduls_active_partners');
	add_action('pn_plugin_activate', 'bd_pn_moduls_active_partners');
	function bd_pn_moduls_active_partners() {
		global $wpdb;
			
		$table_name = $wpdb->prefix . "partners";
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
			`create_date` datetime NOT NULL,
			`edit_date` datetime NOT NULL,
			`auto_status` int(1) NOT NULL default '1',
			`edit_user_id` bigint(20) NOT NULL default '0',		
			`title` longtext NOT NULL,
			`link` longtext NOT NULL,
			`img` longtext NOT NULL,
			`site_order` bigint(20) NOT NULL default '0',
			`status` bigint(20) NOT NULL default '1',
			PRIMARY KEY (`id`),
			INDEX (`create_date`),
			INDEX (`edit_date`),
			INDEX (`edit_user_id`),
			INDEX (`auto_status`),
			INDEX (`site_order`),
			INDEX (`status`)
		) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
		$wpdb->query($sql);

		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "partners LIKE 'link'"); /* 2.2 */
		if (1 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "partners CHANGE `link` `link` longtext NOT NULL");
		}		

	}
}

if (!function_exists('partners_pn_caps')) {
	add_filter('pn_caps', 'partners_pn_caps');
	function partners_pn_caps($pn_caps) {
		
		$pn_caps['pn_partners'] = __('Partners', 'pn');
		
		return $pn_caps;
	}
}

if (!function_exists('admin_menu_partners')) {
	add_action('admin_menu', 'admin_menu_partners');
	function admin_menu_partners() {
		
		$plugin = get_plugin_class();
		if (current_user_cans('administrator, pn_partners')) {
			add_menu_page(__('Partners', 'pn'), __('Partners', 'pn'), 'read', "all_partners", array($plugin, 'admin_temp'), $plugin->get_icon_link('partners'), 801);	
			add_submenu_page("all_partners", __('Add', 'pn'), __('Add', 'pn'), 'read', "all_add_partners", array($plugin, 'admin_temp'));
			add_submenu_page("all_partners", __('Sort', 'pn'), __('Sort', 'pn'), 'read', "all_sort_partners", array($plugin, 'admin_temp'));
		}  
		
	}
}

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'functions');
$plugin->include_path(__FILE__, 'add');
$plugin->include_path(__FILE__, 'list');
$plugin->include_path(__FILE__, 'sort');