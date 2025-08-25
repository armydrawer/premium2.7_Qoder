<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]BestChange API parser[:en_US][ru_RU:]BestChange API парсер[:ru_RU]
description: [en_US:]BestChange API parser[:en_US][ru_RU:]BestChange API парсер[:ru_RU]
version: 2.7.2
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_bestchangeapi');
add_action('pn_plugin_activate', 'bd_all_moduls_active_bestchangeapi');
function bd_all_moduls_active_bestchangeapi() {
	global $wpdb;	
	 	
	$table_name = $wpdb->prefix . "bestchangeapi_currency_codes";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`currency_code_id` bigint(20) NOT NULL default '0',
		`currency_code_title` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`currency_code_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);

	$table_name = $wpdb->prefix . "bestchangeapi_cities";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`city_id` bigint(20) NOT NULL default '0',
		`city_title` longtext NOT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);	
	
	$table_name = $wpdb->prefix . "bestchangeapi_directions";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`direction_id` bigint(20) NOT NULL default '0',
		`currency_id_give` bigint(20) NOT NULL default '0',
		`currency_id_get` bigint(20) NOT NULL default '0',
		`v1` bigint(20) NOT NULL default '0',
		`v2` bigint(20) NOT NULL default '0',
		`city_id` bigint(20) NOT NULL default '0',
		`pars_position` varchar(250) NOT NULL default '0',
		`min_res` varchar(250) NOT NULL default '0',
		`step` varchar(250) NOT NULL default '0',
		`float_course` int(1) NOT NULL default '0',
		`convert_course` int(1) NOT NULL default '0',
		`reset_course` int(1) NOT NULL default '0',
		`standart_course_give` varchar(250) NOT NULL default '0',
		`standart_course_get` varchar(250) NOT NULL default '0',
		`min_sum` varchar(250) NOT NULL default '0',
		`max_sum` varchar(250) NOT NULL default '0',		
		`standart_new_parser` bigint(20) NOT NULL default '0',
		`standart_new_parser_actions_give` varchar(150) NOT NULL default '0',
		`standart_new_parser_actions_get` varchar(150) NOT NULL default '0',
		`minsum_new_parser` bigint(20) NOT NULL default '0',
		`minsum_new_parser_actions` varchar(150) NOT NULL default '0',
		`maxsum_new_parser` bigint(20) NOT NULL default '0',
		`maxsum_new_parser_actions` varchar(150) NOT NULL default '0',
		`black_ids` longtext NOT NULL,
		`white_ids` longtext NOT NULL,
		`status` int(1) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`direction_id`),
		INDEX (`currency_id_give`),
		INDEX (`currency_id_get`),
		INDEX (`status`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);	

	$query = $wpdb->query("SHOW COLUMNS FROM ". $wpdb->prefix ."directions LIKE 'bestchangeapi_id'"); 
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `bestchangeapi_id` bigint(20) NOT NULL default '0'");
	}		
	
	$table_name = $wpdb->prefix . "bestchangeapi_logs"; 
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`url` longtext NOT NULL,
		`headers` longtext NOT NULL,
		`json_data` longtext NOT NULL,
		`result` longtext NOT NULL,
		`error` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`create_date`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);	
	
}	

add_filter('pn_caps', 'bestchangeapi_pn_caps');
function bestchangeapi_pn_caps($caps) {
	
	$caps['pn_bestchangeapi'] = __('Bestchange API parser', 'pn');
	
	return $caps;
}

add_action('admin_menu', 'admin_menu_bestchangeapi');
function admin_menu_bestchangeapi() {
	global $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_bestchangeapi')) {
		add_menu_page(__('BestChange API parser', 'pn'), __('BestChange API parser', 'pn'), 'read', "pn_bestchangeapi", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('parser'));
		add_submenu_page("pn_bestchangeapi", __('Settings', 'pn'), __('Settings', 'pn'), 'read', "pn_bestchangeapi", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_bestchangeapi", __('Logs', 'pn'), __('Logs', 'pn'), 'read', "pn_bestchangeapi_logs", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_bestchangeapi", __('Adjustments', 'pn'), __('Adjustments', 'pn'), 'read', "pn_bcorrs", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_bestchangeapi", __('Add adjustment', 'pn'), __('Add adjustment', 'pn'), 'read', "pn_add_bcorrs", array($premiumbox, 'admin_temp'));
	}
}

add_action('item_bcorrs_deactive', 'item_bcorrs_deactive_bestchangeapi', 10, 2);
add_action('item_bcorrs_delete', 'item_bcorrs_deactive_bestchangeapi', 10, 2);
function item_bcorrs_deactive_bestchangeapi($item_id, $item) {
	global $wpdb;	
	
	$wpdb->update($wpdb->prefix . "directions", array('bestchangeapi_id' => 0), array('id' => $item->direction_id));
	
}

add_filter('list_admin_notify', 'list_admin_notify_bestchangeapi', 100, 2);
function list_admin_notify_bestchangeapi($places, $place = '') {
	
	$places['bestchangesecury'] = __('Bestchange security step', 'pn');
	
	return $places;
}

add_filter('list_notify_tags_bestchangesecury', 'def_mailtemp_tags_bestchangeapi');
function def_mailtemp_tags_bestchangeapi($tags) {
		
	$tags['direction'] = array(
		'title' => __('Exchange direction', 'pn'),
		'start' => '[direction]',
	);	
	$tags['errors'] = array(
		'title' => __('Errors', 'pn'),
		'start' => '[errors]',
	);
		
	return $tags;
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'class');
$premiumbox->include_path(__FILE__, 'settings');
$premiumbox->include_path(__FILE__, 'api');
$premiumbox->include_path(__FILE__, 'display');
$premiumbox->include_path(__FILE__, 'filters');
$premiumbox->include_path(__FILE__, 'list');
$premiumbox->include_path(__FILE__, 'add');
$premiumbox->include_path(__FILE__, 'logs');