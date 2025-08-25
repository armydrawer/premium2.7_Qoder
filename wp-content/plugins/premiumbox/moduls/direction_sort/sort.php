<?php
if (!defined('ABSPATH')) { exit(); }

add_action('sort_directions_tbl1', 'dirsort_sort_directions_tbl1');
function dirsort_sort_directions_tbl1() {
	global $wpdb;

	$form = new PremiumForm();

	$has_give = $has_get = array();
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions");
	foreach ($items as $item) {
		$has_give[$item->currency_id_give] = 1;
		$has_get[$item->currency_id_get] = 1;
	}	

	$places = array();
	$place = is_param_get('place');
	$currencies = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency ORDER BY t1_1 ASC");
	$curr_list = array();
	foreach ($currencies as $currency) {
		if (isset($has_give[$currency->id])) {
			$places[$currency->id] = get_currency_title($currency);
			$curr_list[$currency->id] = get_currency_title($currency);
		}
	}
	
	$selects = array();
	$selects[] = array(
		'link' => admin_url("admin.php?page=pn_sort_directions&sort_place=tbl1"),
		'title' => '--' . __('Left column', 'pn') . '--',
		'default' => '',
	);		
	if (is_array($places)) { 
		foreach ($places as $key => $val) { 
			$selects[] = array(
				'link' => admin_url("admin.php?page=pn_sort_directions&sort_place=tbl1&place=" . $key),
				'title' => $val,
				'default' => $key,
			);		
		}
	}		
	$form->select_box($place, $selects, __('Setting up', 'pn'));

	if (isset($places[$place])) {
		
		$place = intval($place);
		
		$sort_link = pn_link('pn_hidesort_directions_sort', 'post') . '&currency_id=' . $place;
		
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE currency_id_give = '$place'");
		
		$sorting = get_option('directions_order_' . $place);
		$sorting = pn_json_decode($sorting);
		if (!is_array($sorting)) { $sorting = array(); }

		$sort_list = array();
		
		$s_list = array();
		foreach ($items as $item) {
			$s_list[] = array(
				'order' => intval(is_isset($sorting, $item->id)),
				'data' => $item,
			);
		}	
		
		$s_list = pn_array_sort($s_list, 'order', 'asc', 'num');
		
		foreach ($s_list as $item_arr) {
			
			$item = $item_arr['data'];
			$sort_list[0][] = array(
				'title' => get_currency_title_by_id($item->currency_id_get),
				'id' => $item->id,
				'number' => $item->id,
			);
			
		}
			
		$form->sort_one_screen($sort_list, $sort_link);
		
	} else {
		
		$sort_link = pn_link('sort_table1_left', 'post');
			
		$sort_list = array();
		
		foreach ($curr_list as $curr_id => $curr_title) {
			$sort_list[0][] = array(
				'title' => $curr_title,
				'id' => $curr_id,
				'number' => $curr_id,
			);	
		}
			
		$form->sort_one_screen($sort_list, $sort_link);
		
	} 

}
	
add_action('premium_action_pn_hidesort_directions_sort', 'def_premium_action_pn_hidesort_directions_sort');
function def_premium_action_pn_hidesort_directions_sort() {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_directions')) {
		
		$number = is_param_post('number');
		
		$y = 0;
		
		$sorting = array();
		
		if (is_array($number)) {	
			foreach ($number as $id) { $y++;
				$id = intval($id);
				$sorting[$id] = $y;	
			}	
		}
		
		$sorting = pn_json_encode($sorting);
		update_option('directions_order_' . intval(is_param_get('currency_id')), $sorting);
		
	}
}