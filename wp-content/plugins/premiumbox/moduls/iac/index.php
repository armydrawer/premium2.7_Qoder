<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Internal account module[:en_US][ru_RU:]Модуль внутреннего счета[:ru_RU]
description: [en_US:]Internal user accounts[:en_US][ru_RU:]Внутренние счета пользователей[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'db_all_moduls_active_iac');
add_action('pn_plugin_activate', 'db_all_moduls_active_iac');
function db_all_moduls_active_iac() {
	global $wpdb;	

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency_codes LIKE 'iac_enable'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency_codes ADD `iac_enable` int(1) NOT NULL default '1'");
    }

	$table_name = $wpdb->prefix . "iac";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`title` longtext NOT NULL,
		`amount` varchar(50) NOT NULL default '0',
		`currency_code_id` bigint(20) NOT NULL default '0',
		`user_id` bigint(20) NOT NULL default '0',
		`bid_id` bigint(20) NOT NULL default '0',
		`status` int(1) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`user_id`),
		INDEX (`amount`),
		INDEX (`currency_code_id`),
		INDEX (`status`),
		INDEX (`bid_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);	
	
}

add_filter('pn_tech_pages', 'list_tech_pages_iac');
function list_tech_pages_iac($pages) {
	
	$pages[] = array(
	    'post_name' => 'iac',
	    'post_title' => '[en_US:]Internal account[:en_US][ru_RU:]Внутренний счет[:ru_RU]',
	    'post_content' => '[iac_page]',
		'post_template'   => 'pn-pluginpage.php',
	);	
	
	return $pages;
}
/* end BD */

add_filter('pn_currency_code_addform', 'iac_currency_code_addform', 10, 2);
function iac_currency_code_addform($options, $data) {
	
	$options['iac_line'] = array(
		'view' => 'line',
	);	
	$options['iac_enable'] = array(
		'view' => 'select',
		'title' => __('Internal account', 'pn'),
		'options' => array('1' => __('Yes', 'pn'), '0' => __('No', 'pn')),
		'default' => is_isset($data, 'iac_enable'),
		'name' => 'iac_enable',
	);		
	
	return $options;
}

add_filter('pn_currency_code_addform_post', 'iac_currency_code_addform_post');
function iac_currency_code_addform_post($array) {
	
	$array['iac_enable'] = intval(is_param_post('iac_enable'));
	
	return $array;
}
 
function get_user_iac($user_id, $currency_code_id) {
	global $wpdb;

	$user_id = intval($user_id);
	$currency_code_id = intval($currency_code_id);
	$sum = $wpdb->get_var("SELECT SUM(amount) FROM " . $wpdb->prefix . "iac WHERE user_id = '$user_id' AND currency_code_id = '$currency_code_id' AND status = '1'"); 
	$sum = is_sum($sum);
	
	return $sum;
}

add_action('admin_menu', 'admin_menu_iac');
function admin_menu_iac() {
	global $premiumbox;	
	
	if (current_user_can('administrator')) {
		add_menu_page(__('Internal account', 'pn'), __('Internal account', 'pn'), 'read', 'pn_iac', array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('pp'));  
		add_submenu_page("pn_iac", __('Adjustment', 'pn'), __('Adjustment', 'pn'), 'read', "pn_iac", array($premiumbox, 'admin_temp'));	
		add_submenu_page("pn_iac", __('Add adjustment', 'pn'), __('Add adjustment', 'pn'), 'read', "pn_iac_add", array($premiumbox, 'admin_temp'));	
	}
	
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'shortcode');
$premiumbox->include_path(__FILE__, 'users');
$premiumbox->include_path(__FILE__, 'list');
$premiumbox->include_path(__FILE__, 'add');
$premiumbox->include_path(__FILE__, 'filters');