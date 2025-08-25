<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Custom coefficients[:en_US][ru_RU:]Пользовательские коэффициенты[:ru_RU]
description: [en_US:]Custom coefficients[:en_US][ru_RU:]Пользовательские коэффициенты[:ru_RU]
version: 2.7.1
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
new: 1
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_filter('pn_caps','indxs_pn_caps');
function indxs_pn_caps($pn_caps) {
		
	$pn_caps['pn_indxs'] = __('Custom coefficients', 'pn');
		
	return $pn_caps;
}

add_action('all_moduls_active_' . $name, 'indxs_all_moduls_active');
function indxs_all_moduls_active() {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "indxs";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
        `cat_id` int(5) NOT NULL default '0',
		`indx_name` varchar(150) NOT NULL,
		`indx_value` longtext NOT NULL,
		`indx_type` int(5) NOT NULL default '0',
		`indx_comment` longtext NOT NULL,
		`site_order` bigint(20) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`cat_id`),
		INDEX (`site_order`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);
	
}

add_action('admin_menu', 'admin_menu_indxs');
function admin_menu_indxs() {
	global $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_indxs')) {
		
		add_menu_page(__('Custom coefficients', 'pn'), __('Custom coefficients', 'pn'), 'read', "pn_indxs", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('parser'));	
		add_submenu_page("pn_indxs", __('Add coefficient', 'pn'), __('Add coefficient', 'pn'), 'read', "pn_add_indxs", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_indxs", __('Sort coefficient', 'pn'), __('Sort coefficient', 'pn'), 'read', "pn_sort_indxs", array($premiumbox, 'admin_temp'));
		
	}
	
}

function get_inxs_cats() {
	
	$array = array(
		'0' => __('Manual', 'pn'),
	);
	
	return apply_filters('inxs_cats', $array);
}

function unique_indx($value = '', $data_id = 0) {
	global $wpdb;
	
	$data_id = intval($data_id);
	
	$value = is_inxs($value);
	
	if (strlen($value) < 1) {
		$value = strtolower(wp_generate_password(12, false, false));
	}	
	
	if ('index_' != mb_substr($value, 0, 6)) {
		$value = 'index_' . $value;
	}	
	
	if ($value) {
		$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "indxs WHERE indx_name = '$value' AND id != '$data_id'");
		if ($cc > 0) {
			return unique_indx('', $data_id);
		} else {
			return $value;
		}
	} 
	
	return '';
}

function is_inxs($item) {
	
	$item = pn_string($item);
	$new_item = '';
	if (preg_match("/^[a-zA-z0-9_]{3,140}$/", $item, $matches)) {
		$new_item = $item;
	}
	
	return apply_filters('is_inxs', $new_item, $item);
}

add_filter('get_formula_code', 'indxs_formula_code', 500, 4); 
function indxs_formula_code($n, $code, $id, $update) { 
	
	if ('[index_' == mb_substr($code, 0, 7)) {
		
		if ($update) {
			do_action('_update_indx', $code);
		}
		
		$indxs = get_indxs();
		$code = str_replace(array('[', ']'), '', $code);
		if (isset($indxs[$code])) {
			if (0 == $indxs[$code]->indx_type) {
				return '(' . $indxs[$code]->indx_value . ')';
			} else {
				return $indxs[$code]->indx_value;
			}
		} 
		
	}
	
	return $n;
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'add');
$premiumbox->include_path(__FILE__, 'list'); 
$premiumbox->include_path(__FILE__, 'sort');