<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Sort directions[:en_US][ru_RU:]Сортировка направлений[:ru_RU]
description: [en_US:]Sort directions[:en_US][ru_RU:]Сортировка направлений[:ru_RU]
version: 2.7.1
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('item_currency_delete', 'directionsort_item_currency_delete');
function directionsort_item_currency_delete($id) {

	delete_option('directions_order_' . $id);
 
}

add_action('init', 'del_stand_directions_table1', 9);
function del_stand_directions_table1() {
	
	remove_action('sort_directions_tbl1', 'def_sort_directions_tbl1');
	remove_filter('get_directions_table1', 'def_get_directions_table1');
	
}

add_filter('get_directions_table1', 'directionsort_get_directions_table1', 15, 5);
function directionsort_get_directions_table1($directions, $place, $where, $v, $currency_id_give = '') {
	global $wpdb;

	$currency_id_give = intval($currency_id_give);
	if ($currency_id_give > 0) {
		$where .= " AND currency_id_give = '$currency_id_give'";
	}
	
	$sorting = get_option('directions_order_' . $currency_id_give);
	$sorting = pn_json_decode($sorting);
	if (!is_array($sorting)) { $sorting = array(); }
	
	$directions = array();
	$dirs = array();
	
	$directions_arr = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where");
	foreach ($directions_arr as $dir) {
		if (isset($v[$dir->currency_id_give], $v[$dir->currency_id_get])) {
			$output = apply_filters('get_direction_output', 1, $dir, $place);
			if (1 == $output) {
				$dirs[] = array(
					'order' => intval(is_isset($sorting, $dir->id)),
					'd' => $dir,
				);
			}
		}
	}		
	
	$dirs = pn_array_sort($dirs, 'order', 'asc', 'num');
	
	foreach ($dirs as $dir_data) {
		$directions[$dir_data['d']->currency_id_give][] = $dir_data['d'];
	}	
	
	return $directions;
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'sort');