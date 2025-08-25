<?php 
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Request for reserve[:en_US][ru_RU:]Запрос резерва[:ru_RU]
description: [en_US:]Request for reserve[:en_US][ru_RU:]Запрос резерва[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('pn_plugin_activate', 'bd_pn_moduls_active_zresrve');
add_action('all_moduls_active_' . $name, 'bd_pn_moduls_active_zresrve');
function bd_pn_moduls_active_zresrve() {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "direction_reserve_requests"; 
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`request_date` datetime NOT NULL,
		`user_email` varchar(250) NOT NULL,
		`direction_id` bigint(20) NOT NULL default '0',
		`direction_title` longtext NOT NULL,
		`request_amount` varchar(250) NOT NULL,
		`request_comment` longtext NOT NULL,
		`request_locale` varchar(250) NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`request_date`),
		INDEX (`direction_id`),
		INDEX (`request_locale`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);
	
}

add_action('admin_menu', 'admin_menu_zreserve');
function admin_menu_zreserve() {
	global $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_zreserv')) {
		add_menu_page( __('Reserve requests', 'pn'), __('Reserve requests', 'pn'), 'read', "pn_zreserv", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('zreserve'));	
	}
	
}

add_filter('pn_caps', 'zreserve_pn_caps');
function zreserve_pn_caps($pn_caps) {
	
	$pn_caps['pn_zreserv'] = __('Work with reserve requests', 'pn');
	
	return $pn_caps;
}

add_filter('list_admin_notify', 'list_admin_notify_zreserv');
function list_admin_notify_zreserv($places_admin) {
	
	$places_admin['zreserv_admin'] = __('Reserve request', 'pn');
	
	return $places_admin;
}

add_filter('list_user_notify', 'list_user_notify_zreserv');
function list_user_notify_zreserv($places_admin) {
	
	$places_admin['zreserv'] = __('Reserve request', 'pn');
	
	return $places_admin;
}

add_filter('list_notify_tags_zreserv_admin', 'def_list_notify_tags_zreserv_admin');
add_filter('list_notify_tags_zreserv', 'def_list_notify_tags_zreserv_admin');
function def_list_notify_tags_zreserv_admin($tags) {
	
	$tags['email'] = array(
		'title' => __('E-mail', 'pn'),
		'start' => '[email]',
	);
	$tags['sum'] = array(
		'title' => __('Requested amount', 'pn'),
		'start' => '[sum]',
	);
	$tags['sumres'] = array(
		'title' => __('Amount reserved', 'pn'),
		'start' => '[sumres]',
	);		
	$tags['direction'] = array(
		'title' => __('Exchange direction', 'pn'),
		'start' => '[direction]',
	);
	$tags['direction_url'] = array(
		'title' => __('Exchange direction url', 'pn'),
		'start' => '[direction_url]',
	);
	$tags['comment'] = array(
		'title' => __('Comment', 'pn'),
		'start' => '[comment]',
	);	
	$tags['ip'] = array(
		'title' => __('IP address', 'pn'),
		'start' => '[ip]',
	);	

	return $tags;
}

add_action('item_direction_delete', 'zreserve_item_direction_delete', 0, 2);
function zreserve_item_direction_delete($id, $item) {
	global $wpdb;
	
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "direction_reserve_requests WHERE direction_id = '$id'");
	foreach ($items as $item) {	
		$item_id = $item->id;
		$res = apply_filters('item_zreserv_delete_before', pn_ind(), $item_id, $item);
		if ($res['ind']) {
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "direction_reserve_requests WHERE id = '$item_id'");
			do_action('item_zreserv_delete', $item_id, $item, $result);
		}
	}	
	
}

/* filters table */
add_filter('tbl1_rightcol_data','tbl1_rightcol_data_zreserv', 10, 4);
add_filter('tbl4_rightcol_data','tbl1_rightcol_data_zreserv', 10, 4);
function tbl1_rightcol_data_zreserv($data, $direction_data, $vd1, $vd2) {

	$v_title1 = get_currency_title($vd1);		
	$v_title2 = get_currency_title($vd2);				
						
	$data['zreserve'] = '
	<div class="xtt_one_line_rez js_reserve" data-id="' . $direction_data->id . '" data-title="' . $v_title1 . '-' . $v_title2 . '">
		<div class="xtt_one_line_rez_ins">
			<span>' . __('Not enough?', 'pn') . '</span>
		</div>
	</div>														
	';
	
	return $data;													
}

add_filter('tbl2_rightcol_data','tbl2_rightcol_data_zreserv', 10, 5); 
function tbl2_rightcol_data_zreserv($data, $cdata, $vd1, $vd2, $direction) {
			
	$reserve = is_out_sum(get_direction_reserve($vd1, $vd2, $direction), $vd2->currency_decimal, 'reserv');
				
	$v_title1 = get_currency_title($vd1);		
	$v_title2 = get_currency_title($vd2);				
				
	$data['reserve'] = '
	<div class="xtp_line xtp_exchange_reserve">
		' . __('Reserve', 'pn') . ': <span class="js_reserv_html">' . $reserve . ' ' . $cdata['currency_code_get'] . '</span> <a href="#" class="xtp_link js_reserve" data-id="' . $direction->id . '" data-title="' . $v_title1 . '-' . $v_title2 . '">' . __('Not enough?', 'pn') . '</a> 
	</div>														
	';
	
	return $data;													
}

add_filter('tbl3_rightcol_data','tbl3_rightcol_data_zreserv', 10, 5); 
function tbl3_rightcol_data_zreserv($data, $cdata, $vd1, $vd2, $direction) {
				
	$reserve = is_out_sum(get_direction_reserve($vd1, $vd2, $direction), $vd2->currency_decimal, 'reserv');
				
	$v_title1 = get_currency_title($vd1);		
	$v_title2 = get_currency_title($vd2);				
						
	$data['reserve'] = '
	<div class="xtl_line xtl_exchange_reserve">
		' . __('Reserve','pn') . ': <span class="js_reserv_html">' . $reserve . ' ' . $cdata['currency_code_get'] . '</span> <a href="#" class="xtp_link js_reserve" data-id="' . $direction->id . '" data-title="' . $v_title1 . '-' . $v_title2 . '">' . __('Not enough?', 'pn') . '</a> 
	</div>														
	';
		
	return $data;													
}

add_filter('exchange_html_list', 'zreserv_exchange_html_list', 10, 5);
add_filter('exchange_html_list_ajax', 'zreserv_exchange_html_list', 10, 5);
function zreserv_exchange_html_list($array, $direction, $vd1, $vd2, $cdata) {

	$v_title1 = get_currency_title($vd1);		
	$v_title2 = get_currency_title($vd2);
	
	$array['[reserve]'] = '<span class="js_reserve_html">' . strip_tags($array['[reserve]']) . '</span> <a href="#" class="xtp_link js_reserve" data-id="' . $direction->id . '" data-title="' . $v_title1 . '-' . $v_title2 . '">' . __('Not enough?', 'pn') . '</a>';

	return $array;
}
/* end filters table */

add_filter('_icon_indicators', 'zreserve_icon_indicators');
function zreserve_icon_indicators($lists) {
	
	$plugin = get_plugin_class();
	$lists['zreserve'] = array(
		'title' => __('Reserve requests', 'pn'),
		'img' => $plugin->plugin_url . 'images/zreserv.png',
		'url' => admin_url('admin.php?page=pn_zreserv')
	);
	
	return $lists;
}

add_filter('_icon_indicator_zreserve', 'def_icon_indicator_zreserve');
function def_icon_indicator_zreserve($count) {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_zreserv')) {
		$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "direction_reserve_requests");
	}	
	
	return $count;
}

add_action('after_update_currency_reserve','zreserve_after_update_currency_reserve', 10, 3);
function zreserve_after_update_currency_reserve($currency_reserve, $currency_id, $item) { 
	global $wpdb;
	
	$currency_id = intval($currency_id);
	
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
		if (isset($item->id)) {
			$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' AND direction_status IN('1','2') AND currency_id_get IN($bd_id)");
			foreach ($directions as $direction) {
				$direction_id = $direction->id;
				$reserve = get_direction_reserve('', $item, $direction);	
				$zapros = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "direction_reserve_requests WHERE direction_id = '$direction_id' AND request_amount <= $reserve");
				foreach ($zapros as $za) { 
					$zaid = $za->id;
					
					$wpdb->query("DELETE FROM " . $wpdb->prefix . "direction_reserve_requests WHERE id = '$zaid'");
						
					$now_locale = get_locale();
					$set_locale = pn_strip_input($za->request_locale);
					
					$user_email = is_email($za->user_email);						
					if ($user_email) { 
						
						set_locale($set_locale);

						$notify_tags = array();
						$notify_tags['[sumres]'] = $reserve;
						$notify_tags['[sum]'] = $za->request_amount;
						$notify_tags['[email]'] = $user_email;
						$notify_tags['[comment]'] = pn_strip_input($za->request_comment);
						$notify_tags['[ip]'] = pn_real_ip();
						$notify_tags['[direction]'] = pn_strip_input($za->direction_title);
						$notify_tags['[direction_url]'] = get_exchange_link($direction->direction_name);
						$notify_tags = apply_filters('notify_tags_zreserv', $notify_tags, $direction, $za, $reserve);					
							
						$user_send_data = array(
							'user_email' => $user_email,
						);	
						$result_mail = apply_filters('premium_send_message', 0, 'zreserv', $notify_tags, $user_send_data); 

						set_locale($now_locale);
							
					}					
				}
			}
		}
	}
}

add_action('after_update_direction_reserve','zreserve_after_update_direction_reserve', 10, 3);
function zreserve_after_update_direction_reserve($reserve, $direction_id, $item) { 
	global $wpdb;
	
	$direction_id = intval($direction_id);
	
	$bd_ids = array();
	$bd_string = $direction_id . ',' . is_isset($item,'tieds');
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
		if (isset($item->id) and 0 != $item->direction_status and 0 != $item->auto_status) {
			$zapros = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."direction_reserve_requests WHERE direction_id IN($bd_id) AND request_amount <= $reserve");
			foreach ($zapros as $za) {
				$zaid = $za->id;
				
				$wpdb->query("DELETE FROM " . $wpdb->prefix . "direction_reserve_requests WHERE id = '$zaid'");
						
				$now_locale = get_locale();
				$set_locale = pn_strip_input($za->request_locale);
						
				$user_email = is_email($za->user_email);							
				if ($user_email) {	
					
					set_locale($set_locale);

					$notify_tags = array();
					$notify_tags['[sumres]'] = $reserve;
					$notify_tags['[sum]'] = $za->request_amount;
					$notify_tags['[email]'] = $user_email;
					$notify_tags['[comment]'] = pn_strip_input($za->request_comment);
					$notify_tags['[ip]'] = pn_real_ip();
					$notify_tags['[direction]'] = pn_strip_input($za->direction_title);
					$notify_tags['[direction_url]'] = get_exchange_link($item->direction_name);	
					$notify_tags = apply_filters('notify_tags_zreserv', $notify_tags, $item, $za, $reserv);					
							
					$user_send_data = array(
						'user_email' => $user_email,
					);	
					$result_mail = apply_filters('premium_send_message', 0, 'zreserv', $notify_tags, $user_send_data); 
					
					set_locale($now_locale);
	
				}					
			}
		}
	}
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'list');
$premiumbox->include_path(__FILE__, 'window');