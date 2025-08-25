<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Discount coupons[:en_US][ru_RU:]Скидочные купоны[:ru_RU]
description: [en_US:]Discount coupons[:en_US][ru_RU:]Скидочные купоны[:ru_RU]
version: 2.7.0
category: [en_US:]Users[:en_US][ru_RU:]Пользователи[:ru_RU]
cat: user
new: 1
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_coupons');
function bd_all_moduls_active_coupons() {
	global $wpdb;
	
	$query = $wpdb->query("SHOW COLUMNS FROM ". $wpdb->prefix ."exchange_bids LIKE 'coupon'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE ". $wpdb->prefix ."exchange_bids ADD `coupon` varchar(50) NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM ". $wpdb->prefix ."directions LIKE 'disabled_coupons'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."directions ADD `disabled_coupons` int(1) NOT NULL default '0'");
	}	
	
	$table_name = $wpdb->prefix . "coupons";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`coupon_code` varchar(50) NOT NULL,
		`discount` varchar(50) NOT NULL default '0',
		`coupon_type` int(1) NOT NULL default '0',
		`coupon_used` int(1) NOT NULL default '0',
		`status` int(1) NOT NULL default '0',        
		PRIMARY KEY (`id`),
		INDEX (`coupon_code`),
		INDEX (`coupon_type`),
		INDEX (`status`)			
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);
	
}

add_action('admin_menu', 'admin_menu_coupons');
function admin_menu_coupons() {
	global $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_coupons')) {
		add_menu_page(__('Discount coupons', 'pn'), __('Discount coupons', 'pn'), 'read', "pn_coupons", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('discount'));	
		add_submenu_page("pn_coupons", __('Add discount coupon', 'pn'), __('Add discount coupon', 'pn'), 'read', "pn_add_coupons", array($premiumbox, 'admin_temp'));
	}
}

add_filter('pn_caps', 'coupons_pn_caps');
function coupons_pn_caps($pn_caps) {
	
	$pn_caps['pn_coupons'] = __('Access to the coupons section', 'pn');
	
	return $pn_caps;
}

function unique_coupon($value = '', $data_id = 0) {
	global $wpdb;
	
	$data_id = intval($data_id);
	if (strlen($value) < 1) {
		$value = strtolower(wp_generate_password(12, false, false));
	}
	if ($value) {
		$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "coupons WHERE coupon_code = '$value' AND id != '$data_id'");
		if ($cc > 0) {
			return unique_coupon('', $data_id);
		} else {
			return $value;
		}
	} 
	
	return '';
}

function is_coupon($item) {
	
	$item = pn_string($item);
	$new_item = '';
	if (preg_match("/^[a-zA-z0-9_]{3,50}$/", $item, $matches)) {
		$new_item = $item;
	}
	
	return apply_filters('is_coupon', $new_item, $item);
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'add');
$premiumbox->include_path(__FILE__, 'list');
$premiumbox->include_path(__FILE__, 'filters'); 