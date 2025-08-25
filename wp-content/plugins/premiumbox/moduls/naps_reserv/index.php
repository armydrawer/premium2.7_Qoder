<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Reserve settings for exchange directions[:en_US][ru_RU:]Настройки резерва для направлений обмена[:ru_RU]
description: [en_US:]Reserve settings for exchange directions[:en_US][ru_RU:]Настройки резерва для направлений обмена[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_napsreserv');
add_action('pn_plugin_activate', 'bd_all_moduls_active_napsreserv');
function bd_all_moduls_active_napsreserv() {
	global $wpdb;
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'direction_reserv'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `direction_reserv` varchar(250) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'reserv_place'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `reserv_place` varchar(250) NOT NULL default '0'");
    }	
}

add_filter('list_tabs_direction', 'list_tabs_direction_napsreserv', 1000, 2);
function list_tabs_direction_napsreserv($list_tabs, $db_data) {
	
	$reserve = 0;
	if (isset($db_data->id)) {
		$v = get_currency_data();
		$curr_id_get = $db_data->currency_id_get;
		$cur = is_isset($v, $curr_id_get);
		$reserve = get_direction_reserve('', $cur, $db_data);
	}
	$n_list_tabs = array();
	$n_list_tabs['reserve'] = __('Reserve', 'pn') . ' <span class="one_tabs_submenu">[' . get_sum_color($reserve) . ']</span>';
	
	return pn_array_insert($list_tabs, 'tab3', $n_list_tabs, 'before');
}

add_action('tab_direction_reserve', 'def_direction_tab_reserve', 10, 2);
function def_direction_tab_reserve($data, $data_id) {
	
	$form = new PremiumForm();
	
	$rplaced = array();
	$rplaced[0] = '--' . __('Default', 'pn') . '--';
	$rplaced[1] = '--' . __('From field for reserve', 'pn') . '--';
	$rplaced = apply_filters('reserve_place_list', $rplaced, 'direction');
	$rplaced = (array)$rplaced;

	$reserve_place = is_isset($data, 'reserv_place');
	$clr = ' pn_hide';
	if ('1' == $reserve_place) {
		$clr = '';
	}	
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Reserve', 'pn'); ?></span></div>
			<?php $form->select_search('reserv_place', $rplaced, $reserve_place, array('class' => 'js_hide_input', 'to_class' => 'line_dir_reserve'));  ?>
		</div>
	</div>
	<div class="add_tabs_line line_dir_reserve line_dir_reserve1<?php echo $clr; ?>">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Field for reserve', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="direction_reserv" style="width: 100px;" value="<?php echo is_sum(is_isset($data, 'direction_reserv')); ?>" />
			</div>
		</div>
	</div>
<?php
}

add_filter('pn_direction_addform_post', 'napsreserv_pn_direction_addform_post');
function napsreserv_pn_direction_addform_post($array) {
	
	$array['reserv_place'] = is_extension_name(is_param_post('reserv_place'));
	$array['direction_reserv'] = is_sum(is_param_post('direction_reserv'));
	
	return $array;
}
 
function update_direction_reserve($direction_id, $item = '', $place = 'all') {
	global $wpdb;
	
	$direction_id = intval($direction_id); 
	if ($direction_id) { 
		if (!isset($item->id)) {
			$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$direction_id'");
		}
		if (isset($item->id)) {
			$direction_reserve = is_sum($item->direction_reserv);
			$direction_reserve = apply_filters('update_direction_reserve', $direction_reserve, is_extension_name($item->reserv_place), $direction_id, $item, $place);
			do_action('after_update_direction_reserve', $direction_reserve, $direction_id, $item, $place);
		}
	}	
}

add_filter('change_bid_status', 'napsreserv_change_bidstatus', 1000);    
function napsreserv_change_bidstatus($data) {   

	$place = $data['place'];
	$set_status = $data['set_status'];
	$bid = $data['bid'];
	$who = $data['who'];
	$old_status = $data['old_status'];
	$direction = $data['direction'];
	
	$stop_action = intval(is_isset($data, 'stop')); 
	if (!$stop_action) {
		
		update_direction_reserve($bid->direction_id, $direction, $set_status);
		
	}

	return $data;
}

add_action('item_direction_edit', 'napsreserv_item_direction_edit', 1000, 2); 
add_action('item_direction_add', 'napsreserv_item_direction_edit', 1000, 2);
function napsreserv_item_direction_edit($data_id, $array) {
	
	update_direction_reserve($data_id);
	
}

add_filter('get_direction_reserve', 'napsreserv_get_direction_reserv', 9000, 4);
function napsreserv_get_direction_reserv($reserv, $vd1, $vd2, $direction) {
	
	if ('0' != $direction->reserv_place) {
		return $direction->direction_reserv;
	}
	
	return $reserv;
} 