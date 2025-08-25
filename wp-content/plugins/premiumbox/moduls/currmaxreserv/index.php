<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Displayed value of the currency reserve[:en_US][ru_RU:]Максимальное отображаемое значение резерва валюты[:ru_RU]
description: [en_US:]Displayed value of the currency reserve[:en_US][ru_RU:]Максимальное отображаемое значение резерва валюты[:ru_RU]
version: 2.7.0
category: [en_US:]Currency[:en_US][ru_RU:]Валюты[:ru_RU]
cat: currency
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('pn_plugin_activate', 'bd_all_moduls_active_currmaxreserv');
add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_currmaxreserv');
function bd_all_moduls_active_currmaxreserv() {
	global $wpdb;	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'max_reserv'");
    if (0 == $query){ 
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `max_reserv` varchar(50) NOT NULL default '0'");
    }	
	
}

add_filter('list_export_currency', 'currmaxreserv_list_export_currency');
function currmaxreserv_list_export_currency($array) {
	
	$array['max_reserv'] = __('Max. displayed value of the currency reserve', 'pn');
	
	return $array;
}

add_filter('export_currency_filter', 'currmaxreserv_export_currency_filter');
function currmaxreserv_export_currency_filter($export_filter) {
	
	$export_filter['sum_arr']['max_reserv'] = 'max_reserv';
	
	return $export_filter;
}

add_action('tab_currency_tab2', 'maxreserve_tab_currency_tab2', 20, 2);
function maxreserve_tab_currency_tab2($data, $data_id) {
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Max. displayed value of the currency reserve', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="max_reserv" value="<?php echo is_sum(is_isset($data, 'max_reserv')); ?>" />
			</div>
		</div>
	</div>	
<?php		
}

add_filter('pn_currency_addform_post', 'currmaxreserv_currency_addform_post');
function currmaxreserv_currency_addform_post($array) {
	
	$array['max_reserv'] = is_sum(is_param_post('max_reserv'));
	
	return $array;
}

add_filter('get_currency_reserve', 'get_currency_reserve_currmaxreserv', 10001, 3);
function get_currency_reserve_currmaxreserv($reserve, $data, $decimal) {
	
	$max = is_sum($data->max_reserv);
	if ($max > 0) {
		if ($reserve > $max) {
			$reserve = $max;
		}
	}			
	
	return is_sum($reserve, $decimal);
}										