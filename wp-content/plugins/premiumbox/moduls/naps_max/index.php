<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Exchange direction currency limit[:en_US][ru_RU:]Лимит резерва валюты по направлению обмена[:ru_RU]
description: [en_US:]Exchange direction currency limit[:en_US][ru_RU:]Лимит резерва валюты по направлению обмена[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);
 
add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_maxnaps');
add_action('pn_plugin_activate', 'bd_all_moduls_active_maxnaps');
function bd_all_moduls_active_maxnaps() {
	global $wpdb;	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'maxnaps'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `maxnaps` varchar(50) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'inday'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `inday` varchar(50) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'inmon'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `inmon` varchar(50) NOT NULL default '0'");
    }	
	
}

add_filter('list_export_directions', 'maxnaps_list_export_directions');
function maxnaps_list_export_directions($array) {
	
	$array['maxnaps'] = __('Max. amount for sending', 'pn');
	$array['inday'] = __('Reserve limit per day', 'pn');
	$array['inmon'] = __('Reserve limit per month', 'pn');
	
	return $array;
}

add_filter('export_directions_filter', 'maxnaps_export_directions_filter');
function maxnaps_export_directions_filter($export_filter) {
	
	$export_filter['sum_arr'][] = 'maxnaps';
	$export_filter['sum_arr'][] = 'inday';
	$export_filter['sum_arr'][] = 'inmon';
	
	return $export_filter;
}

add_action('tab_direction_reserve', 'maxnaps_tab_direction_tab', 100, 2);
function maxnaps_tab_direction_tab($data, $data_id) {
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Reserve limit for exhange direction', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="maxnaps" style="width: 200px;" value="<?php echo is_sum(is_isset($data, 'maxnaps')); ?>" />
			</div>			
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Reserve limit per day', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="inday" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'inday')); ?>" />
			</div>			
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Reserve limit per month', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="inmon" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'inmon')); ?>" />	
			</div>		
		</div>
	</div>		
	<?php 		
}

add_filter('pn_direction_addform_post', 'maxnaps_pn_direction_addform_post');
function maxnaps_pn_direction_addform_post($array) {
	
	$array['maxnaps'] = is_sum(is_param_post('maxnaps'));
	$array['inday'] = is_sum(is_param_post('inday'));
	$array['inmon'] = is_sum(is_param_post('inmon'));
	
	return $array;
}

add_filter('get_max_sum_get', 'maxnaps_get_max_sum_get', 11, 4);
function maxnaps_get_max_sum_get($max, $direction, $vd1, $vd2) {
	
	if ($direction->maxnaps > 0) {
		$sum_direction_all = get_sum_direction($direction->id, 'out');
		$maxnaps = $direction->maxnaps - $sum_direction_all;
		if ($maxnaps < 0) { $maxnaps = 0; }
		
		if (is_numeric($max)) {
			if ($max > $maxnaps) {
				$max = $maxnaps;
			}
		} else {
			$max = $maxnaps;
		}
	}		
	
	$time = current_time('timestamp');
	
	$inday = is_sum($direction->inday);
	if ($inday > 0) {
		$date = date('Y-m-d 00:00:00', $time);
		$sum_day = get_sum_direction($direction->id, 'out', $date);
		$inday = $inday - $sum_day;
		if (is_numeric($max)) {
			if ($max > $inday) {
				$max = $inday;
			}	
		} else {
			$max = $inday;
		}
	}	
	
	$inmon = is_sum($direction->inmon);
	if ($inmon > 0) {
		$date = date('Y-m-01 00:00:00', $time);
		$sum_mon = get_sum_direction($direction->id, 'out', $date);
		$inmon = $inmon - $sum_mon;
		if (is_numeric($max)) {
			if ($max > $inmon) {
				$max = $inmon;
			}	
		} else {
			$max = $inmon;
		}
	}	
	
	return $max;
}