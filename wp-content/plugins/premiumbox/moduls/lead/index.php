<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Exchange rate conversion[:en_US][ru_RU:]Преобразование курса обмена[:ru_RU]
description: [en_US:]Exchange rate conversion to format 1 to XXX[:en_US][ru_RU:]Преобразование курса обмена к формату 1 к ХХХ[:ru_RU]
version: 2.7.0
category: [en_US:]Currency[:en_US][ru_RU:]Валюты[:ru_RU]
cat: currency
dependent: -
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_leadnum');
add_action('pn_plugin_activate', 'bd_all_moduls_active_leadnum');
function bd_all_moduls_active_leadnum() {
	global $wpdb;	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'lead_num'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `lead_num` varchar(20) NOT NULL default '0'");
	}
	
}

add_filter('pntable_columns_pn_currency', 'leadnum_pntable_columns_pn_currency');
function leadnum_pntable_columns_pn_currency($columns) {
	
	$n_columns = array();
	$n_columns['lead_num'] = __('Convert to', 'pn');
	$columns = pn_array_insert($columns, 'decimal', $n_columns);
	
	return $columns;
}

add_filter('pntable_column_pn_currency', 'leadnum_pntable_column_pn_currency', 10, 3);
function leadnum_pntable_column_pn_currency($html, $column_name, $item) {
	
	if ('lead_num' == $column_name) {
		return '<input type="text" style="width: 50px;" name="lead_num[' . $item->id . ']" value="' . intval($item->lead_num) . '" />'; 
	}
	
	return $html;
}

add_action('pntable_currency_save', 'leadnum_pntable_currency_save');
function leadnum_pntable_currency_save() {
	global $wpdb;
	
	if (isset($_POST['lead_num']) and is_array($_POST['lead_num'])) {
		foreach ($_POST['lead_num'] as $id => $lead_num) {
			$id = intval($id);
			$lead_num = intval($lead_num);
			if ($lead_num <= 0) { $lead_num = 0; }			
			$wpdb->query("UPDATE " . $wpdb->prefix . "currency SET lead_num = '$lead_num' WHERE id = '$id'");
		}
	}
	
}

add_action('tab_currency_tab1', 'leadnum_tab_currency_tab1', 50, 2);
function leadnum_tab_currency_tab1($data, $data_id) {
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Convert to', 'pn'); ?></span></div>
			
			<div class="premium_wrap_standart">
				<input type="text" name="lead_num" value="<?php echo is_sum(is_isset($data, 'lead_num')); ?>" />
			</div>
			
		</div>
	</div>
<?php		
}

add_filter('pn_currency_addform_post', 'leadnum_currency_addform_post');
function leadnum_currency_addform_post($array) {
	
	$lead_num = intval(is_param_post('lead_num')); if ($lead_num < 1) { $lead_num = 0; }
	$array['lead_num'] = $lead_num;	
	
	return $array;
}

add_filter('list_export_currency', 'leadnum_list_export_currency');
function leadnum_list_export_currency($array) {
	
	$array['lead_num'] = __('Convert to', 'pn');
	
	return $array;
}

add_filter('export_currency_filter', 'leadnum_export_currency_filter');
function leadnum_export_currency_filter($export_currency_filter) {
	
	$export_currency_filter['sum_arr'][] = 'lead_num';
	
	return $export_currency_filter;
}	

function get_lead_num($lead_num) {
	
	if ($lead_num <= 0) {
		$lead_num = apply_filters('default_lead_num', 100);
	}
	
	return $lead_num;
}

function get_leads_field($lead_num, $curs1, $curs2, $decimal) {
	
	$s = $curs2;
	if ($lead_num > 0 and $curs1 > 0) {
		$s = $curs2 * $lead_num / $curs1;	
    } 
	
	return is_sum($s, $decimal);
}

function get_course1($direction, $vd1, $vd2) {
	
	$lead_num = get_lead_num(intval(is_isset($vd1, 'lead_num')));
	
	return $lead_num;
}

function get_course2($direction, $vd1, $vd2, $course_give, $course_get) { 

	$lead_num = get_lead_num(intval(is_isset($vd1, 'lead_num')));
	$curs = get_leads_field($lead_num, $course_give, $course_get, intval(is_isset($vd2, 'currency_decimal')));
	
	return $curs;
}

add_filter('get_calc_data', 'get_calc_data_leadnum', 500, 2);
function get_calc_data_leadnum($cdata, $calc_data) {
	global $wpdb, $premiumbox;
	
	$vd1 = $calc_data['vd1'];
	$vd2 = $calc_data['vd2'];
	$direction = $calc_data['direction'];
	
	$course_give = $cdata['course_give'];
	$course_get = $cdata['course_get'];
	if ($course_give > 0 and $course_get > 0) {
		$cdata['course_give'] = get_course1($direction, $vd1, $vd2);
		$cdata['course_get'] = get_course2($direction, $vd1, $vd2, $course_give, $course_get);
	}
	
	return $cdata;
} 

add_filter('is_course_direction', 'leadnum_is_course_direction', 50, 5); 
function leadnum_is_course_direction($arr, $direction, $vd1, $vd2, $place) {
	
	// if ('table1' == $place or 'coursewindow' == $place) {
		$course_give = $arr['give'];
		$course_get = $arr['get'];
		if ($course_give > 0 and $course_get > 0) {
			$arr['give'] = get_course1($direction, $vd1, $vd2);
			$arr['get'] = get_course2($direction, $vd1, $vd2, $course_give, $course_get);
		}
	// }
	
	return $arr;
} 