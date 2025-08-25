<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Parsing of XML files with exchange rates of exchange offices[:en_US][ru_RU:]Парсинг XML файлов с курсами обменников[:ru_RU]
description: [en_US:]Parsing of XML files with exchange rates of other exchange offices using the Parser 2.0 module[:en_US][ru_RU:]Парсинг XML файлов с курсами других обменников с помощью модуля Парсер 2.0[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU] 
cat: directions
dependent: parser_settings
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('pn_plugin_activate', 'bd_all_moduls_active_blackparser');
add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_blackparser');
function bd_all_moduls_active_blackparser(){ 
	global $wpdb;	
	
	$table_name = $wpdb->prefix . "blackparsers";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`title` longtext NOT NULL,
		`url` longtext NOT NULL,
		PRIMARY KEY (`id`)	
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);	
	
}

add_action('admin_menu', 'admin_menu_blackparser', 20);
function admin_menu_blackparser() {
	global $premiumbox;	
	
	if (current_user_can('administrator') or current_user_can('pn_directions') or current_user_can('pn_parser')) {
		add_submenu_page("pn_new_parser", __('Sites', 'pn'), __('Sites', 'pn'), 'read', "pn_blackparser", array($premiumbox, 'admin_temp'));	
		add_submenu_page("pn_new_parser", __('Add website', 'pn'), __('Add website', 'pn'), 'read', "pn_add_blackparser", array($premiumbox, 'admin_temp'));
	}
	
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'list');
$premiumbox->include_path(__FILE__, 'add');
$premiumbox->include_path(__FILE__, 'filters'); 