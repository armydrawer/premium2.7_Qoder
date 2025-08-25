<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Users verification[:en_US][ru_RU:]Верификация пользователей[:ru_RU]
description: [en_US:]Users verification[:en_US][ru_RU:]Верификация пользователей[:ru_RU]
version: 2.7.0
category: [en_US:]Users[:en_US][ru_RU:]Пользователи[:ru_RU]
cat: user
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_userverify');
add_action('pn_plugin_activate', 'bd_all_moduls_active_userverify');
function bd_all_moduls_active_userverify() {
	global $wpdb;	
		
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'user_verify'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `user_verify` int(1) NOT NULL default '0'");
	}	
		
	/* 
	поля верификации

	title - название поля
	fieldvid - вид поля
	locale - версия локализации
	helps - подсказка
	status - 0-не работает, 1-работает
	uv_order - порядок
	*/	
	$table_name = $wpdb->prefix . "uv_field";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`title` longtext NOT NULL,
		`fieldvid` int(1) NOT NULL default '0',
		`datas` longtext NOT NULL,
		`locale` varchar(20) NOT NULL,
		`uv_auto` varchar(250) NOT NULL,
		`uv_req` int(1) NOT NULL default '0',
		`helps` longtext NOT NULL,
		`country` longtext NOT NULL,
		`status` int(1) NOT NULL default '0',
		`uv_order` bigint(20) NOT NULL default '0',
		`eximg` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`fieldvid`),
		INDEX (`locale`),
		INDEX (`status`),
		INDEX (`uv_order`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "uv_field LIKE 'helps'"); /* 2.0 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "uv_field ADD `helps` longtext NOT NULL");
	}
		
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "uv_field LIKE 'country'"); /* 2.0 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "uv_field ADD `country` longtext NOT NULL");
	}
		
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "uv_field LIKE 'eximg'"); /* 2.0 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "uv_field ADD `eximg` longtext NOT NULL");
	}
		
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "uv_field LIKE 'datas'"); /* 2.1 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "uv_field ADD `datas` longtext NOT NULL");
	}		
		
	/*
	Данные юзера по заявке
	uv_data - данные
	uv_id - id заявки
	uv_field - id поля заявки
	*/	
	$table_name = $wpdb->prefix . "uv_field_user";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) NOT NULL default '0',
		`uv_data` longtext NOT NULL,
		`uv_id` bigint(20) NOT NULL default '0',
		`uv_field` bigint(20) NOT NULL default '0',
		`fieldvid` int(1) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`user_id`),
		INDEX (`uv_id`),
		INDEX (`uv_field`),
		INDEX (`fieldvid`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);
		
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "uv_field_user LIKE 'fieldvid'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "uv_field_user ADD `fieldvid` int(1) NOT NULL default '0'");
	}	
		
	/* 
	верификаци
	 
	create_date - дата создания
	user_id - id юзера
	user_login - логин юзера
	user_email - e-mail юзера
	theip - ip
	comment - причина отказа
	status - 0-авто, 1-отправлено, 2-подтвержден, 3-отказано
	*/
	$table_name = $wpdb->prefix . "verify_bids";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`edit_date` datetime NOT NULL,
		`auto_status` int(1) NOT NULL default '1',
		`edit_user_id` bigint(20) NOT NULL default '0',
		`user_id` bigint(20) NOT NULL default '0',
		`user_login` varchar(250) NOT NULL,
		`user_email` varchar(250) NOT NULL,
		`user_ip` varchar(250) NOT NULL, 
		`user_country` varchar(20) NOT NULL,
		`comment` longtext NOT NULL,
		`locale` varchar(20) NOT NULL,
		`status` int(1) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`create_date`),
		INDEX (`edit_date`),
		INDEX (`auto_status`),
		INDEX (`edit_user_id`),
		INDEX (`user_id`),
		INDEX (`locale`),
		INDEX (`status`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);	
		
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "verify_bids LIKE 'user_country'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "verify_bids ADD `user_country` varchar(20) NOT NULL");
	}	

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'user_verify'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `user_verify` int(1) NOT NULL default '0'");
	}

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'verify'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `verify` int(1) NOT NULL default '0'");
	}	

	$wpdb->query("DELETE FROM " . $wpdb->prefix . "directions_meta WHERE meta_key = 'verify'");

}

add_filter('pn_tech_pages', 'list_tech_pages_userverify');
function list_tech_pages_userverify($pages) {
		
	$pages[] = array(
		'post_name'      => 'userverify',
		'post_title'     => '[ru_RU:]Верификация аккаунта[:ru_RU][en_US:]User verification[:en_US]',
		'post_content'   => '[userverify]',
		'post_template'   => 'pn-pluginpage.php',
	);		
		
	return $pages;
}

add_filter('pn_caps', 'userverify_pn_caps');
function userverify_pn_caps($pn_caps) {
		
	$pn_caps['pn_userverify'] = __('Work with user verification', 'pn');
		
	return $pn_caps;
}

add_action('admin_menu', 'admin_menu_userverify');
function admin_menu_userverify() {
		
	$plugin = get_plugin_class();
	if (current_user_can('administrator') or current_user_can('pn_userverify')) {
		add_menu_page(__('Verification', 'pn'), __('Verification', 'pn'), 'read', 'all_usve', array($plugin, 'admin_temp'), $plugin->get_icon_link('verify'));  
		add_submenu_page("all_usve", __('Add verification', 'pn'), __('Add verification','pn'), 'read', 'all_add_usve', array($plugin, 'admin_temp'));
		add_submenu_page("all_usve", __('Verification fields', 'pn'), __('Verification fields', 'pn'), 'read', 'all_usfield', array($plugin, 'admin_temp'));  	
		add_submenu_page("all_usve", __('Add verification field', 'pn'), __('Add verification field', 'pn'), 'read', 'all_add_usfield', array($plugin, 'admin_temp'));
		add_submenu_page("all_usve", __('Sort verification fields', 'pn'), __('Sort verification fields', 'pn'), 'read', 'all_sort_usfield', array($plugin, 'admin_temp'));		
		add_submenu_page("all_usve", __('Settings', 'pn'), __('Settings', 'pn'), 'read', "all_usve_settings", array($plugin, 'admin_temp'));		
	}
		
}

add_filter('list_admin_notify', 'list_admin_notify_userverify');
function list_admin_notify_userverify($places_admin) {
		
	$places_admin['userverify1'] = __('Request for identity verification', 'pn');
		
	return $places_admin;
}

add_filter('list_user_notify','list_user_notify_userverify');
function list_user_notify_userverify($places_admin) {
		
	$places_admin['userverify1_u'] = __('Successful identity verification', 'pn');
	$places_admin['userverify2_u'] = __('Identity verification declined', 'pn');
		
	return $places_admin;
}

add_filter('list_notify_tags_userverify2_u', 'def_mailtemp_tags_userverify2_u');
function def_mailtemp_tags_userverify2_u($tags) {
		
	$tags['text'] = array(
		'title' => __('Failure reason', 'pn'),
		'start' => '[text]',
	);
		
	return $tags;
}	

add_action('item_usfielduser_delete', 'def_item_usfielduser_delete', 10, 2);
function def_item_usfielduser_delete($id, $item) {
		
	$plugin = get_plugin_class();
	$file = $plugin->upload_dir . '/userverify/' . $item->uv_id . '/' . $id . '.php';
	if (is_file($file)) {
		@unlink($file);
	}
		
}

add_action('item_usfield_delete', 'def_usfield_delete', 10, 2);
function def_usfield_delete($id, $item) {
	global $wpdb;
		
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_field_user WHERE uv_field = '$id'");
	foreach ($items as $item) {
		$item_id = $item->id;
		$res = apply_filters('item_usfielduser_delete_before', pn_ind(), $item_id, $item);
		if ($res['ind']) {
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "uv_field_user WHERE id = '$item_id'");
			do_action('item_usfielduser_delete', $item_id, $item, $result);
		}
	}		
}

add_action('item_usfield_edit', 'def_usfield_edit', 10, 2);
function def_usfield_edit($data_id, $array) {	
	global $wpdb;
	
	$wpdb->query("UPDATE " . $wpdb->prefix . "uv_field_user SET fieldvid = '{$array['fieldvid']}' WHERE uv_field = '$data_id'");

}

add_action('item_usve_delete', 'def_usve_delete', 10, 2);
function def_usve_delete($id, $item) {
	global $wpdb;
		
	$plugin = get_plugin_class();

	$path = $plugin->upload_dir . '/userverify/' . $id . '/';
	full_del_dir($path);
		
	$user_id = $item->user_id;
	$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "verify_bids WHERE user_id = '$user_id' AND status = '2' AND auto_status = '1' AND id != '$id'");
	if (0 == $cc) {
		$user_item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "users WHERE ID = '$user_id' AND user_verify = '1'");
		if (isset($user_item->ID)) {
			$arr = array();
			$arr['user_verify'] = 0;
			$wpdb->update($wpdb->prefix . 'users', $arr, array('ID' => $user_id));
			do_action('item_users_unverify', $user_id);
		}	
	}	
		
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_field_user WHERE uv_id = '$id'");
	foreach ($items as $item) {
		$item_id = $item->id;
		$res = apply_filters('item_usfielduser_delete_before', pn_ind(), $item_id, $item);
		if ($res['ind']) {
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "uv_field_user WHERE id = '$item_id'");
			do_action('item_usfielduser_delete', $item_id, $item, $result); 
		}
	}	
}

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'settings');
$plugin->include_path(__FILE__, 'list_usfield');
$plugin->include_path(__FILE__, 'add_usfield');
$plugin->include_path(__FILE__, 'sort_usfield');
$plugin->include_path(__FILE__, 'usvedoc');
$plugin->include_path(__FILE__, 'usve');
$plugin->include_path(__FILE__, 'add_usve');
$plugin->include_path(__FILE__, 'comments_usve');
$plugin->include_path(__FILE__, 'filters');
$plugin->include_path(__FILE__, 'premiumbox');

$plugin->auto_include($path . '/shortcode');

$plugin->file_include($path . '/widget/userverify');