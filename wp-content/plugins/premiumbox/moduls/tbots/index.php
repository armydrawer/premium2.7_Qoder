<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]T-bots[:en_US][ru_RU:]T-bots[:ru_RU]
description: [en_US:]Telegram-API-bots[:en_US][ru_RU:]Telegram-API-bots[:ru_RU]
version: 2.7.2
category: [en_US:]Other[:en_US][ru_RU:]Остальное[:ru_RU]
cat: other
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('pn_plugin_activate', 'db_all_moduls_active_tapibot');
add_action('all_moduls_active_' . $name, 'db_all_moduls_active_tapibot');
function db_all_moduls_active_tapibot() {
	global $wpdb;
								
	$table_name = $wpdb->prefix . "tapibots";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`bot_token` varchar(500) NOT NULL,
		`bot_title` longtext NOT NULL,
		`bot_settings` longtext NOT NULL,
		`api_server` varchar(500) NOT NULL,
		`api_version` varchar(50) NOT NULL,
		`api_login` varchar(250) NOT NULL,
		`api_key` varchar(250) NOT NULL,
		`api_lang` varchar(250) NOT NULL,
		`api_partner_id` varchar(50) NOT NULL,
		`bot_parsmode` int(1) NOT NULL default '0',
		`bot_logs` int(1) NOT NULL default '0',
		`bot_status` int(1) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`bot_status`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "tapibots LIKE 'bot_parsmode'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "tapibots ADD `bot_parsmode` int(1) NOT NULL default '0'");
	}	

	$table_name = $wpdb->prefix . "tapibot_logs";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`tapibot_id` bigint(20) NOT NULL default '0',
		`create_date` datetime NOT NULL,
		`log_ip` varchar(150) NOT NULL,
		`log_url` longtext NOT NULL,
		`log_post` longtext NOT NULL,
		`log_json` longtext NOT NULL,
		`log_headers` longtext NOT NULL,
		`log_answer` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`create_date`),
		INDEX (`tapibot_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);

	$table_name = $wpdb->prefix . "tapibot_words";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`enter_word` varchar(250) NOT NULL,
		`get_word` varchar(250) NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`enter_word`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);

	$table_name = $wpdb->prefix . "tapibot_chats";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`tapibot_id` bigint(20) NOT NULL default '0',
		`uniq_id` bigint(20) NOT NULL default '0',
		`now_step` varchar(5) NOT NULL default '0',
		`now_info` longtext NOT NULL,
		`save_info` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`tapibot_id`),
		INDEX (`uniq_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);

	$table_name = $wpdb->prefix . "tapibot_bids";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`tapibot_id` bigint(20) NOT NULL default '0',
		`uniq_id` bigint(20) NOT NULL default '0',
		`bid_id` bigint(20) NOT NULL default '0',
		`bid_info` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`tapibot_id`),
		INDEX (`uniq_id`),
		INDEX (`bid_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);	

}

add_filter('pn_caps', 'tapibot_pn_caps');
function tapibot_pn_caps($pn_caps) {
	
	$pn_caps['pn_tapibot'] = __('T-API-bot', 'pn');
	
	return $pn_caps;
}

add_action('admin_menu', 'admin_menu_tapibot', 20001);
function admin_menu_tapibot() {
	
	$plugin = get_plugin_class();
	if (current_user_can('administrator') or current_user_can('pn_tapibot')) {
		add_menu_page(__('T-API-bots', 'pn'), __('T-API-bots', 'pn'), 'read', 'all_tapibot', array($plugin, 'admin_temp'), $plugin->get_icon_link('geoip'));  
		add_submenu_page("all_tapibot", __('Add', 'pn'), __('Add', 'pn'), 'read', "all_add_tapibot", array($plugin, 'admin_temp'));
		add_submenu_page("all_tapibot", __('Words', 'pn'), __('Words', 'pn'), 'read', "all_tapibot_words", array($plugin, 'admin_temp'));
		add_submenu_page("all_tapibot", __('Add', 'pn'), __('Add', 'pn'), 'read', "all_add_tapibot_words", array($plugin, 'admin_temp'));
		add_submenu_page("all_tapibot", __('Logs', 'pn'), __('Logs', 'pn'), 'read', "all_tapibotlogs", array($plugin, 'admin_temp'));
	}	
	
}

function del_tapibotlogs() {
	global $wpdb, $premiumbox;
	
	if (!$premiumbox->is_up_mode()) {
		
		$count_day = intval(get_logs_sett('delete_tapibotlogs_day'));
		if (!$count_day) { $count_day = 3; }

		if ($count_day > 0) {
			$time = current_time('timestamp') - ($count_day * DAY_IN_SECONDS); 
			$ldate = date('Y-m-d H:i:s', $time);
			$wpdb->query("DELETE FROM " . $wpdb->prefix . "tapibot_logs WHERE create_date < '$ldate'");
		}
	}
} 

add_filter('list_cron_func', 'del_tapibotlogs_list_cron_func');
function del_tapibotlogs_list_cron_func($filters) {	

	$filters['del_tapibotlogs'] = array(
		'title' => __('Delete T-API-bot logs', 'pn'),
		'site' => '1day',
	);
	
	return $filters;
}

add_filter('list_logs_settings', 'tapibotlogs_list_logs_settings');
function tapibotlogs_list_logs_settings($filters) {
	
	$filters['delete_tapibotlogs_day'] = array(
		'title' => __('Delete T-API-bot logs', 'pn') . ' (' . __('days', 'pn') . ')',
		'count' => 3,
		'minimum' => 1,
	);
	
	return $filters;
} 

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'add_words');
$plugin->include_path(__FILE__, 'list_words');
$plugin->include_path(__FILE__, 'list_logs');
$plugin->include_path(__FILE__, 'add');
$plugin->include_path(__FILE__, 'list');
$plugin->include_path(__FILE__, 'class');
$plugin->include_path(__FILE__, 'test_server');
$plugin->include_path(__FILE__, 'functions');
$plugin->include_path(__FILE__, 'bot');