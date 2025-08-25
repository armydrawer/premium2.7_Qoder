<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Additional fee that depends on exchanged amount[:en_US][ru_RU:]Дополнительная комиссия зависящая от суммы обмена[:ru_RU]
description: [en_US:]Additional fee that depends on exchanged amount[:en_US][ru_RU:]Дополнительная комиссия зависящая от суммы обмена[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('item_direction_delete', 'item_direction_delete_dopsumcomis');
function item_direction_delete_dopsumcomis($item_id) {
	global $wpdb;	
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "constructs WHERE itemtype = 'dcombysum' AND item_id = '$item_id'");
	
}

add_action('item_direction_edit', 'item_direction_dopsumcomis');
add_action('item_direction_add', 'item_direction_dopsumcomis');
function item_direction_dopsumcomis($item_id) { 
	global $wpdb;
	
	$wpdb->query("UPDATE " . $wpdb->prefix . "constructs SET item_id = '$item_id' WHERE itemtype = 'dcombysum' AND item_id = '0'");
	
}
 
add_filter('db_constructs_itemtype', 'db_constructs_itemtype_dcombysum', 10, 2);
function db_constructs_itemtype_dcombysum($name, $ind) {
	
	if ('dcombysum' == $ind) {
		$name = 'dcombysum';
	}
	
	return $name;
}
 
add_filter('db_constructs_access', 'db_constructs_access_dcombysum', 10, 2);
function db_constructs_access_dcombysum($name, $ind) {
	
	if ('dcombysum' == $ind) {
		$name = 'pn_directions';
	}
	
	return $name;
}

add_filter('db_constructs_scheme', 'dcombysum_constructs_scheme', 10, 2);
function dcombysum_constructs_scheme($array, $ind) {
	
	if ('dcombysum' == $ind) {
		$array = array(
			'rate' => array(
				'name' => 'amount',
				'title' => __('Amount', 'pn'),
				'type' => 'input',
			),
			'actions' => array(
				'type' => 'actions',
			),
			'title1' => array(
				'type' => 'title',
				'title' => __('Additional sender fee', 'pn'),
			),	
			'com_box_sum1' => array(
				'name' => 'com_box_sum1',
				'after' => 'S',
				'type' => 'input',
			),
			'com_box_pers1' => array(
				'name' => 'com_box_pers1',
				'after' => '%',
				'type' => 'input',
			),
			'clear1' => array(
				'type' => 'clear',
			),
			'title2' => array(
				'type' => 'title',
				'title' => __('Additional recipient fee', 'pn'),
			),
			'com_box_sum2' => array(
				'name' => 'com_box_sum2',
				'after' => 'S',
				'type' => 'input',
			),
			'com_box_pers2' => array(
				'name' => 'com_box_pers2',
				'after' => '%',
				'type' => 'input',
			),			
		);
	}
	
	return $array;
}

add_action('tab_direction_tab4', 'tab_direction_tab_dopsumcomis', 20);
function tab_direction_tab_dopsumcomis($data) {	

	$data_id = intval(is_isset($data, 'id'));
	$form = new PremiumForm();
	if ($data_id > 0) {
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Additional fee that depends on exchanged amount', 'pn'); ?></span></div>
			<?php echo the_constructs_html($data_id, 'dcombysum'); ?>
		</div>
	</div>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<?php $form->help(__('More info', 'pn'), __('Specify the value of the additional fee that depends on the "Send" amount.', 'pn')); ?>
		</div>
	</div>		
	<?php 
	}
}

add_filter('get_calc_data', 'get_calc_data_dopsumcomis', 100, 2);
function get_calc_data_dopsumcomis($cdata, $calc_data) { 
	global $wpdb;
	
	$direction = $calc_data['direction'];
	$post_sum = is_sum(is_isset($calc_data, 'post_sum'), 20);
	$dej = intval(is_isset($calc_data, 'dej'));
	$direction_id = $direction->id;
	
	$cc = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."constructs WHERE itemtype = 'dcombysum' AND item_id = '$direction_id'");
	if ($cc > 0) {
		$cdata['dis1c'] = 1;
		$cdata['dis2'] = 1;	
		$cdata['dis2c'] = 1;
	
		$data = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."constructs WHERE itemtype = 'dcombysum' AND item_id = '$direction_id' AND ('$post_sum' -0.0) >= amount ORDER BY (amount -0.0) DESC");
		if (isset($data->id)) {
			$options = pn_json_decode($data->itemsettings);
			$cdata['com_box_sum1'] = is_sum(is_isset($options, 'com_box_sum1'));
			$cdata['com_box_pers1'] = is_sum(is_isset($options, 'com_box_pers1'));	
			$cdata['com_box_sum2'] = is_sum(is_isset($options, 'com_box_sum2'));
			$cdata['com_box_pers2'] = is_sum(is_isset($options, 'com_box_pers2'));				
		}
	}
	
	return $cdata;
}