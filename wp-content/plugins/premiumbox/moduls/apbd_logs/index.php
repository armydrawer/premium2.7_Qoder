<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Logging the administrator activity[:en_US][ru_RU:]Логирование действий администратора[:ru_RU]
description: [en_US:]Logging the administrator activity[:en_US][ru_RU:]Логирование действий администратора[:ru_RU]
version: 2.7.0
category: [en_US:]Security[:en_US][ru_RU:]Безопасность[:ru_RU]
cat: secur
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_apbd');
add_action('pn_plugin_activate', 'bd_all_moduls_active_apbd');
function bd_all_moduls_active_apbd() {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "db_admin_logs";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`item_id` bigint(20) NOT NULL default '0',
		`tbl_name` varchar(250) NOT NULL default '0',
		`trans_type` int(1) NOT NULL default '0',
		`trans_date` datetime NOT NULL,
		`old_data` longtext NOT NULL,
		`new_data` longtext NOT NULL,
		`user_id` bigint(20) NOT NULL default '0',
		`user_login` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`item_id`),
		INDEX (`tbl_name`),
		INDEX (`trans_type`),
		INDEX (`trans_date`),
		INDEX (`user_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);	 
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'function');
$premiumbox->include_path(__FILE__, 'filters');
$premiumbox->include_path(__FILE__, 'cron');