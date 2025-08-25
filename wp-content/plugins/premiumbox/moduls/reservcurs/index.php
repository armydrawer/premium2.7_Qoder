<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Exchange rate dependent on currency reserve[:en_US][ru_RU:]Курс зависящий от резерва[:ru_RU]
description: [en_US:]Exchange rate dependent on currency reserve[:en_US][ru_RU:]Курс зависящий от резерва[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('item_direction_delete', 'item_direction_delete_reservcurs');
function item_direction_delete_reservcurs($item_id) { 
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "constructs WHERE itemtype = 'coursebyreserve' AND item_id = '$item_id'");
	
}
 
add_action('item_direction_edit', 'item_direction_reservcurs');
add_action('item_direction_add', 'item_direction_reservcurs');
function item_direction_reservcurs($item_id) { 
	global $wpdb;	
	
	$wpdb->query("UPDATE " . $wpdb->prefix . "constructs SET item_id = '$item_id' WHERE itemtype = 'coursebyreserve' AND item_id = '0'");
	
}
 
add_filter('db_constructs_itemtype', 'db_constructs_itemtype_coursebyreserve', 10, 2);
function db_constructs_itemtype_coursebyreserve($name, $ind) {
	
	if ('coursebyreserve' == $ind) {
		$name = 'coursebyreserve';
	}
	
	return $name;
}

add_filter('db_constructs_access', 'db_constructs_access_coursebyreserve', 10, 2);
function db_constructs_access_coursebyreserve($name, $ind) {
	
	if ('coursebyreserve' == $ind) {
		$name = 'pn_directions';
	}
	
	return $name;
}

add_filter('db_constructs_scheme', 'coursebyreserve_constructs_scheme', 10, 2);
function coursebyreserve_constructs_scheme($array, $ind) {
	global $premiumbox;	
	
	if ('coursebyreserve' == $ind) {
		
		$pers = '';
		$what = intval($premiumbox->get_option('reservcurs', 'what'));
		if (1 == $what) {
			$pers = ' (%)';
		}			
		
		$array = array(
			'rate' => array(
				'name' => 'amount',
				'title' => __('Reserve', 'pn'),
				'type' => 'input',
			),
			'course1' => array(
				'name' => 'course1',
				'title' => __('Send', 'pn') . $pers,
				'type' => 'input',
			),
			'course2' => array(
				'name' => 'course2',
				'title' => __('Receive', 'pn') . $pers,
				'type' => 'input',
			),			
			'actions' => array(
				'type' => 'actions',
			),			
		);
		
	}
	return $array;
}

add_action('tab_direction_tab2', 'tab_direction_tab2_reservcurs', 12);
function tab_direction_tab2_reservcurs($data) {	
 
	$data_id = intval(is_isset($data, 'id'));
	$form = new PremiumForm();
	if ($data_id > 0) {
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange rate dependent on currency reserve', 'pn'); ?></span></div>
			<?php echo the_constructs_html($data_id, 'coursebyreserve'); ?>
		</div>
	</div>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<?php $form->help(__('More info', 'pn'), __('Specify the value of the exchange rate depending on the currency reserve', 'pn')); ?>
		</div>
	</div>			
	<?php 
	}
}

add_action('after_update_currency_reserve', 'reservcurs_after_update_currency_reserve', 101, 3);
function reservcurs_after_update_currency_reserve($currency_reserve, $currency_id, $item) {
	global $wpdb, $premiumbox; 
		
	$what = intval($premiumbox->get_option('reservcurs', 'what'));
	$method = intval($premiumbox->get_option('reservcurs', 'method'));
	if (0 == $method) {
		$bd_ids = array();
		$bd_string = $currency_id . ',' . is_isset($item, 'tieds');
		$bd_string_arr = explode(',', $bd_string);
		$not_bd = array('rc', 'rd', 'd');
		foreach ($bd_string_arr as $bd_st) {
			$bd_st = trim($bd_st);
			if ($bd_st and !strstr_array($bd_st, $not_bd)) {
				$bd_ids[] = preg_replace( '/[^0-9]/', '', $bd_st);
			}
		}
		
		$bd_id = create_data_for_db($bd_ids, 'int');
		if ($bd_id) {
			$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' AND direction_status = '1' AND currency_id_get IN($bd_id)");
			foreach ($directions as $direction) {
				$direction_id = $direction->id;
				$dir_c = is_course_direction($direction, '', '', 'admin');
				$course_give = is_isset($dir_c, 'give');
				$course_get = is_isset($dir_c, 'get');
				$reserve = get_direction_reserve('', $item, $direction); 
				
				$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "constructs WHERE itemtype = 'coursebyreserve' AND item_id = '$direction_id' AND ('$reserve' -0.0) >= amount ORDER BY (amount -0.0) DESC");
				if (isset($data->id)) {
					
					$options = pn_json_decode($data->itemsettings);
					
					$arr = array();
					if (0 == $what) {
						$n_course_give = is_sum(is_isset($options, 'course1'));
						$n_course_get = is_sum(is_isset($options, 'course2'));
					} else {
						$c1 = is_sum(is_isset($options, 'course1'));
						$c2 = is_sum(is_isset($options, 'course2'));
						$one_pers1 = $course_give / 100;
						$n_course_give = $course_give + ($one_pers1 * $c1);
						$one_pers2 = $course_get / 100;
						$n_course_get = $course_get + ($one_pers2 * $c2);
					}
					$arr['course_give'] = $n_course_give;
					$arr['course_get'] = $n_course_get;
					if ($n_course_give > 0 and $n_course_get > 0) {
						$wpdb->update($wpdb->prefix . "directions", $arr, array('id' => $direction_id));
					}
				}		
			}
		}
		
		do_action('reservcurs_end');
	}
}

add_action('after_update_direction_reserve', 'reservcurs_after_update_direction_reserve', 101, 3);
function reservcurs_after_update_direction_reserve($reserve, $direction_id, $item) {
	global $wpdb, $premiumbox; 
		
	$what = intval($premiumbox->get_option('reservcurs', 'what'));
	$method = intval($premiumbox->get_option('reservcurs', 'method'));
	if (0 == $method) {
		
		$bd_ids = array();
		$bd_string = $direction_id . ',' . is_isset($item, 'tieds');
		$bd_string_arr = explode(',', $bd_string);
		$not_bd = array('rc', 'rd', 'c');
		foreach ($bd_string_arr as $bd_st) {
			$bd_st = trim($bd_st);
			if ($bd_st and !strstr_array($bd_st, $not_bd)) {
				$bd_ids[] = preg_replace( '/[^0-9]/', '', $bd_st);
			}
		}		
		
		$bd_id = create_data_for_db($bd_ids, 'int');
		if ($bd_id) {
			$dir_c = is_course_direction($item, '', '', 'admin');
			$course_give = is_isset($dir_c, 'give');
			$course_get = is_isset($dir_c, 'get'); 
				
			$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "constructs WHERE itemtype = 'coursebyreserve' AND item_id IN ($bd_id) AND ('$reserve' -0.0) >= amount ORDER BY (amount -0.0) DESC");
			foreach ($datas as $data) {
				
				$options = pn_json_decode($data->itemsettings);
				
				$arr = array();
				if (0 == $what) {
					$n_course_give = is_sum(is_isset($options, 'course1'));
					$n_course_get = is_sum(is_isset($options, 'course2'));
				} else {
					$c1 = is_sum(is_isset($options, 'course1'));
					$c2 = is_sum(is_isset($options, 'course2'));
					$one_pers1 = $course_give / 100;
					$n_course_give = $course_give + ($one_pers1 * $c1);
					$one_pers2 = $course_get / 100;
					$n_course_get = $course_get + ($one_pers2 * $c2);
				}
				$arr['course_give'] = $n_course_give;
				$arr['course_get'] = $n_course_get;
				if ($n_course_give > 0 and $n_course_get > 0) {
					$wpdb->update($wpdb->prefix . "directions", $arr, array('id' => $direction_id));
				}
				
			}
		}
		
		do_action('reservcurs_end');
	}
}

add_filter('get_calc_data', 'get_calc_data_reservcurs', 81, 2);
function get_calc_data_reservcurs($cdata, $calc_data) {
	global $wpdb, $premiumbox;
	
	$what = intval($premiumbox->get_option('reservcurs', 'what'));
	$method = intval($premiumbox->get_option('reservcurs', 'method'));
	if (1 == $method) {	
		$set_course = intval(is_isset($calc_data, 'set_course'));
		if (1 != $set_course) {
			$direction = $calc_data['direction'];
			$direction_id = $direction->id;
			
			$vd1 = $calc_data['vd1'];
			$vd2 = $calc_data['vd2'];
			
			$reserve = get_direction_reserve($vd1, $vd2, $direction);
			
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "constructs WHERE itemtype = 'coursebyreserve' AND item_id = '$direction_id' AND ('$reserve' -0.0) >= amount ORDER BY (amount -0.0) DESC");
			if (isset($data->id)) {
				
				$options = pn_json_decode($data->itemsettings);
				
				$course_give = $cdata['course_give'];
				$course_get = $cdata['course_get'];
					
				$decimal_give = $cdata['decimal_give'];
				$decimal_get = $cdata['decimal_get'];
			
				if (0 == $what) {
					$n_course_give = is_sum(is_isset($options, 'course1'));
					$n_course_get = is_sum(is_isset($options, 'course2'));
				} else {
					$c1 = is_sum(is_isset($options, 'course1'), 20);
					$c2 = is_sum(is_isset($options, 'course2'), 20);
					$one_pers1 = $course_give / 100;
					$n_course_give = $course_give + ($one_pers1 * $c1);
					$one_pers2 = $course_get / 100;
					$n_course_get = $course_get + ($one_pers2 * $c2);
				}		
			
				$cdata['course_give'] = is_sum($n_course_give, $decimal_give);
				$cdata['course_get'] = is_sum($n_course_get, $decimal_get);
			}
		}
	}
	
	return $cdata;
}	

add_action('admin_menu', 'admin_menu_reservcurs');
function admin_menu_reservcurs() {
	global $premiumbox;	
	
	add_submenu_page("pn_moduls", __('Exchange rate dependent on currency reserve', 'pn'), __('Exchange rate dependent on currency reserve', 'pn'), 'administrator', "pn_reservcurs", array($premiumbox, 'admin_temp'));
	
}

add_filter('pn_adminpage_title_pn_reservcurs', 'def_adminpage_title_pn_reservcurs');
function def_adminpage_title_pn_reservcurs($page) {
	
	return __('Exchange rate dependent on currency reserve', 'pn');
} 

add_action('pn_adminpage_content_pn_reservcurs', 'def_adminpage_content_pn_reservcurs');
function def_adminpage_content_pn_reservcurs() {
	global $premiumbox;

	$form = new PremiumForm();

	$options = array();
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => '',
		'submit' => __('Save', 'pn'),
	);	
	$options['what'] = array(
		'view' => 'select',
		'title' => __('Method of exchange rate formation', 'pn'),
		'options' => array('0' => __('specify the exchange rate directly', 'pn'), '1' => __('add interest to the exchange rate', 'pn')),
		'default' => $premiumbox->get_option('reservcurs', 'what'),
		'name' => 'what',
	);	
	$options['method'] = array(
		'view' => 'select',
		'title' => __('Update rates', 'pn'),
		'options' => array('0' => __('during currency operations', 'pn'), '1' => __('in an instant', 'pn')),
		'default' => $premiumbox->get_option('reservcurs', 'method'),
		'name' => 'method',
	);
	$params_form = array(
		'filter' => 'pn_reservcurs_options',
		'button_title' => __('Save', 'pn'),
	);
	$form->init_form($params_form, $options);
	
}  

add_action('premium_action_pn_reservcurs', 'def_premium_action_pn_reservcurs');
function def_premium_action_pn_reservcurs() {
	global $premiumbox;	

	_method('post');
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));

	$premiumbox->update_option('reservcurs', 'what', intval(is_param_post('what')));
	$premiumbox->update_option('reservcurs', 'method', intval(is_param_post('method')));

	$url = admin_url('admin.php?page=pn_reservcurs&reply=true');
	$form->answer_form($url);
	
}   