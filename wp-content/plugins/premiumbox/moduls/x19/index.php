<?php 
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Webmoney x19[:en_US][ru_RU:]Webmoney x19[:ru_RU]
description: [en_US:]Webmoney x19[:en_US][ru_RU:]Webmoney x19[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('pn_plugin_activate', 'bd_all_moduls_active_x19');
add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_x19');
function bd_all_moduls_active_x19() {
	global $wpdb;

	$table_name = $wpdb->prefix . "x19_logs";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`create_date` datetime NOT NULL,
		`dir_id` bigint(20) NOT NULL default '0',
		`error_text` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`create_date`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);	
	
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'webmoney/index');
$premiumbox->include_path(__FILE__, 'classed/wmxicore.class');
$premiumbox->include_path(__FILE__, 'classed/wmxi.class');
$premiumbox->include_path(__FILE__, 'classed/wmxiresult.class');
$premiumbox->include_path(__FILE__, 'classed/wmsigner.class');

add_action('admin_menu', 'admin_menu_x19');
function admin_menu_x19() {
	global $premiumbox;

	add_menu_page(__('X19', 'pn'), __('X19', 'pn'), 'administrator', "pn_x19_test", array($premiumbox, 'admin_temp'));
	add_submenu_page("pn_x19_test", __('Logs', 'pn'), __('Logs', 'pn'), 'administrator', "pn_x19_logs", array($premiumbox, 'admin_temp'));
	add_submenu_page("pn_x19_test", __('Settings', 'pn'), __('Settings', 'pn'), 'administrator', "pn_x19_settings", array($premiumbox, 'admin_temp'));

}

$premiumbox->include_path(__FILE__, 'settings');
$premiumbox->include_path(__FILE__, 'function');
$premiumbox->include_path(__FILE__, 'x19');
$premiumbox->include_path(__FILE__, 'logs');
$premiumbox->include_path(__FILE__, 'filters');