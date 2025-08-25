<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Formula for reserve[:en_US][ru_RU:]Формула для резерва[:ru_RU]
description: [en_US:]Formula for reserve[:en_US][ru_RU:]Формула для резерва[:ru_RU]
version: 2.7.0
category: [en_US:]Currency[:en_US][ru_RU:]Валюты[:ru_RU]
cat: currency
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('pn_plugin_activate', 'bd_all_moduls_active_freserve');
add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_freserve');
function bd_all_moduls_active_freserve() {
	global $wpdb;	

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'reserv_calc'"); 
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `reserv_calc` longtext NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'reserv_calc'"); 
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `reserv_calc` longtext NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'tieds'"); 
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `tieds` longtext NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'tieds'"); 
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `tieds` longtext NOT NULL");
	}	
	
}

add_filter('reserve_place_list', 'freserve_reserve_place_list');
function freserve_reserve_place_list($rplaced) {
	
	$placed = array();
	$placed[2] = '--' . __('According to the formula', 'pn') . '--';
	$rplaced = pn_array_insert($rplaced, '1', $placed);
	
	return $rplaced;
}
 
add_action('tab_currency_tab2','tab_currency_tab_freserve', 11, 2);
function tab_currency_tab_freserve($data, $data_id) { 

	$reserv_place = is_isset($data, 'reserv_place');
	$clr = ' pn_hide';
	if ('2' == $reserv_place) {
		$clr = '';
	}
?>	
	<div class="add_tabs_line line_currency_reserve line_currency_reserve2 <?php echo $clr; ?>">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Formula for reserve', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<textarea name="reserv_calc" style="width: 100%; height: 200px;"><?php echo pn_strip_input(is_isset($data, 'reserv_calc')); ?></textarea>
			</div>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Link reserve with currency reserve ID', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="tieds" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($data, 'tieds')); ?>" />
			</div>
		</div>
	</div>	
<?php
}	

add_filter('pn_currency_addform_post', 'freserve_currency_addform_post');
function freserve_currency_addform_post($array) {
	
	$array['reserv_calc'] = pn_strip_input(is_param_post('reserv_calc'));
	$array['tieds'] = pn_strip_input(is_param_post('tieds'));
	
	return $array;
}

add_action('tab_direction_reserve', 'tab_direction_tab_freserve', 11, 2);
function tab_direction_tab_freserve($data, $data_id) { 

	$reserve_place = is_isset($data, 'reserv_place');
	$clr = ' pn_hide';
	if ('2' == $reserve_place) {
		$clr = '';
	}
?>	
	<div class="add_tabs_line line_dir_reserve line_dir_reserve2<?php echo $clr; ?>">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Formula for reserve', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<textarea name="reserv_calc" style="width: 100%; height: 200px;"><?php echo pn_strip_input(is_isset($data, 'reserv_calc')); ?></textarea>
			</div>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Link reserve with exchange direction reserve ID', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="tieds" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($data, 'tieds')); ?>" />
			</div>
		</div>
	</div>	
<?php
}
 
add_filter('pn_direction_addform_post', 'freserve_pn_direction_addform_post');
function freserve_pn_direction_addform_post($array) {
	
	$array['reserv_calc'] = pn_strip_input(is_param_post('reserv_calc'));
	$array['tieds'] = pn_strip_input(is_param_post('tieds'));
	
	return $array;
}				

add_filter('fres_where_filter_currency', 'freserve_fres_where_filter_currency');
function freserve_fres_where_filter_currency($where) {
	
	$where .= " OR auto_status = '1' AND reserv_calc LIKE '%cfilereserve_%'";
	
	return $where;
}	

add_filter('fres_where_filter_direction', 'freserve_fres_where_filter_direction');
function freserve_fres_where_filter_direction($where) {
	
	$where .= " OR auto_status = '1' AND reserv_calc LIKE '%dfilereserve_%'";
	
	return $where;
} 
 
add_action('after_update_currency_reserve', 'freserve_after_update_currency_reserve', 0, 4);
function freserve_after_update_currency_reserve($reserve, $id, $item, $place) {
	global $pn_curr_up_list;

	if (!is_array($pn_curr_up_list)) { $pn_curr_up_list = array(); }
	$pn_curr_up_list[$id] = 1;

	$new_ids = formula_array_of_tieds($item->tieds);
	foreach ($new_ids as $n_id) {
		
		if (strstr($n_id, 'rc')) {
			$n_id = str_replace('rc', '', $n_id);
			$n_id = intval($n_id);
			if (function_exists('update_currency_reserve') and !isset($pn_curr_up_list[$n_id])) {
				update_currency_reserve($n_id, '', $place); 
			}
		} elseif (strstr($n_id, 'rd')) {
			$n_id = str_replace('rd', '', $n_id);
			$n_id = intval($n_id);
			if (function_exists('update_direction_reserve')) {
				update_direction_reserve($n_id, '', $place);
			}
		} elseif (strstr($n_id, 'd')) {
			$n_id = str_replace('d', '', $n_id);
			$n_id = intval($n_id);
			pm_update_nr($n_id, $reserve);
		} elseif (strstr($n_id, 'c')) {	
			$n_id = str_replace('c', '', $n_id);
			$n_id = intval($n_id);
			pm_update_vr($n_id, $reserve);
		} else {
			$n_id = intval($n_id);
			pm_update_vr($n_id, $reserve);
		}
		
	}
	
}

add_action('after_update_direction_reserve', 'freserve_after_update_direction_reserve', 0, 4);
function freserve_after_update_direction_reserve($reserve, $id, $item, $place) {
	global $pn_dirr_up_list;	
	
	if (!is_array($pn_dirr_up_list)) { $pn_dirr_up_list = array(); }
	$pn_dirr_up_list[$id] = 1;	
	
	$new_ids = formula_array_of_tieds($item->tieds);
	foreach ($new_ids as $n_id) {
		
		if (strstr($n_id, 'rc')) {
			$n_id = str_replace('rc', '', $n_id);
			$n_id = intval($n_id);
			if (function_exists('update_currency_reserve')) {
				update_currency_reserve($n_id, '', $place);
			}
		} elseif (strstr($n_id, 'rd')) {
			$n_id = str_replace('rd', '', $n_id);
			$n_id = intval($n_id);
			if (function_exists('update_direction_reserve') and !isset($pn_dirr_up_list[$n_id])) {
				update_direction_reserve($n_id, '', $place);
			}
		} elseif (strstr($n_id, 'd')) {
			$n_id = str_replace('d', '', $n_id);
			$n_id = intval($n_id);
			pm_update_nr($n_id, $reserve);
		} elseif (strstr($n_id, 'c')) {	
			$n_id = str_replace('c', '', $n_id);
			$n_id = intval($n_id);
			pm_update_vr($n_id, $reserve);
		} else {
			$n_id = intval($n_id);
			pm_update_nr($n_id, $reserve);
		}
		
	}
	
}

/* 
cfilereserve_, 
dfilereserve_, 
corres, 
excursum_give,
excursum_get,
excursum_auto
currency 
payouts, 
parser_
parser_index_ => indx_
*/ 