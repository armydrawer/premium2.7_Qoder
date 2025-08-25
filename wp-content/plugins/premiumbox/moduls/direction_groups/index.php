<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Direction groups[:en_US][ru_RU:]Группы направлений[:ru_RU]
description: [en_US:]Direction groups[:en_US][ru_RU:]Группы направлений[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
new: 1
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'all_moduls_active_dgroups');
add_action('pn_plugin_activate', 'all_moduls_active_dgroups');
function all_moduls_active_dgroups() {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "dgroups";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`title` longtext NOT NULL,
		`site_order` bigint(20) NOT NULL default '0',
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);		
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'group_id'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `group_id` varchar(250) NOT NULL default '0'");
    }	
	
}

add_action('admin_menu', 'admin_menu_dgroups');
function admin_menu_dgroups() {
	global $premiumbox;	
	
	if (current_user_can('administrator') or current_user_can('pn_directions')) {
		add_menu_page( __('Direction groups', 'pn'), __('Direction groups', 'pn'), 'read', "pn_dgroups", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('mystatus'));	
		add_submenu_page("pn_dgroups", __('Add', 'pn'), __('Add', 'pn'), 'read', "pn_add_dgroups", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_dgroups", __('Sort', 'pn'), __('Sort', 'pn'), 'read', "pn_sort_dgroups", array($premiumbox, 'admin_temp'));
	}
	
}

function get_dir_groups($title) {
	global $wpdb, $pn_dgroups;
	
	if (!is_array($pn_dgroups)) {
		$pn_dgroups = array();
		$lists = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "dgroups ORDER BY site_order ASC");
		foreach ($lists as $list) {
			$pn_dgroups[$list->id] = pn_strip_input($list->title);
		}
	}		
	
	$list = array();
	$list['0'] = $title;
	$list = $list + $pn_dgroups;
	
	return $list;
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'add');
$premiumbox->include_path(__FILE__, 'list');
$premiumbox->include_path(__FILE__, 'sort');
$premiumbox->include_path(__FILE__, 'filters');