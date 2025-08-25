<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Telegram notifications[:en_US][ru_RU:]Telegram уведомления[:ru_RU]
description: [en_US:]Notifications via messenger Telegram[:en_US][ru_RU:]Уведомления через Telegram[:ru_RU]
version: 2.7.1
category: [en_US:]Other[:en_US][ru_RU:]Остальное[:ru_RU]
cat: other
dependent: -
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);
 
add_action('pn_plugin_activate', 'bd_all_moduls_active_telegram');
add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_telegram');
function bd_all_moduls_active_telegram() {
	global $wpdb;

	$table_name = $wpdb->prefix . "telegram"; 
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`create_date` datetime NOT NULL,
		`telegram_login` varchar(150) NOT NULL,
		`site_user_id` bigint(20) NOT NULL default '0',
		`telegram_chat_id` bigint(20) NOT NULL default '0',
		`data` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`create_date`),
		INDEX (`site_user_id`),
		INDEX (`telegram_chat_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);	
	
	$table_name = $wpdb->prefix . "telegram_logs";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`create_date` datetime NOT NULL,
		`log_ip` varchar(150) NOT NULL,
		`type` int(1) NOT NULL default '0',
		`place` int(1) NOT NULL default '0',
		`error_text` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`create_date`),
		INDEX (`type`),
		INDEX (`place`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "telegram_logs LIKE 'log_ip'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "telegram_logs ADD `log_ip` varchar(150) NOT NULL");
    }	
	
}
 
add_action('admin_menu', 'admin_menu_telegram');
function admin_menu_telegram() {
	global $premiumbox;
	
	add_submenu_page("all_mail_temps", __('Telegram templates', 'pn'), __('Telegram templates', 'pn'), 'read', "all_telegram_temps", array($premiumbox, 'admin_temp'));
	
	add_menu_page(__('Telegram', 'pn'), __('Telegram', 'pn'), 'administrator', "all_telegram", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('telegram'));
	add_submenu_page("all_telegram",__('Settings', 'pn'), __('Settings', 'pn'), 'administrator', "all_telegram", array($premiumbox, 'admin_temp'));
	add_submenu_page("all_telegram",__('Message logs', 'pn'), __('Message logs', 'pn'), 'administrator', "all_telegram_logs", array($premiumbox, 'admin_temp'));
}

add_filter('accountform_filelds', 'telegram_accountform_filelds', 20);
function telegram_accountform_filelds($items) {
	
	if (isset($items['user_telegram'])) {	
		$data = get_option('telegram_settings');
		if (!is_array($data)) { $data = array(); }
		$tooltip = pn_strip_input(ctv_ml(is_isset($data, 'tooltip')));
		if (strlen($tooltip) > 0) {
			$items['user_telegram']['tooltip'] = $tooltip;
		}
	}		
	
	return $items;
}
 
global $premiumbox;
$premiumbox->include_path(__FILE__, 'class');
$premiumbox->include_path(__FILE__, 'settings');
$premiumbox->include_path(__FILE__, 'bot_settings');
$premiumbox->include_path(__FILE__, 'logs');
$premiumbox->include_path(__FILE__, 'bot');