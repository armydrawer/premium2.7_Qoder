<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Currency reserve from file[:en_US][ru_RU:]Парсер резерва из файла[:ru_RU]
description: [en_US:]Currency reserve from file[:en_US][ru_RU:]Парсер резерва из файла[:ru_RU] 
version: 2.7.0
category: [en_US:]Currency[:en_US][ru_RU:]Валюты[:ru_RU]
cat: currency
dependent: naps_reserv
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_filter('reserve_place_list', 'fres_reserve_place_list', 10, 2);
function fres_reserve_place_list($list, $place) {
	
	$reservs = get_reserv_fres($place);
	foreach ($reservs as $key => $val) {
		$list[$key] = sprintf(__('File reserve, line %1s, name %2s', 'pn'), is_isset($val, 'line'), is_isset($val, 'title')) . '(' . is_isset($val, 'sum') . ')';
	}
	
	return $list;
}

function get_reserv_fres($place = 'currency') {
	global $premiumbox, $fres_c, $fres_d;
	
	$arr = array();
	if ('currency' == $place) {
		$url = trim($premiumbox->get_option('fres', 'url'));
	} else {
		$url = trim($premiumbox->get_option('fres', 'url2'));
	}
	$name = 'cfilereserve';
	if ('direction' == $place) {
		$name = 'dfilereserve';
	}
	if ($url) {
		$curl = get_curl_parser($url, '', 'moduls', 'fres');
		$string = $curl['output'];
		if (!$curl['err']) {
			$lines = explode("\n",$string);
			$r = 0;
			foreach ($lines as $line) { $r++;
				$pars_line = explode('=', $line);
				if (isset($pars_line[1])) {
					$sum = is_sum($pars_line[1]);
					$title = pn_strip_input($pars_line[0]);
					if ($title) {
						$arr[$name . '_' . $r] = array(
							'line' => $r,
							'title' => $pars_line[0],
							'sum' => $sum,
						);
					}
				}					
			}
			if ('currency' == $place) {
				$fres_c = $arr;
			} else {
				$fres_d = $arr;
			}			
		}			
	}	
	
	return $arr;
}

add_filter('get_formula_code', 'fres_get_formula_code', 15, 4);
function fres_get_formula_code($n, $code, $id, $update) {  
	global $fres_c, $fres_d;
	
	if (strstr($code, '[cfilereserve_')) {
		$ncode = str_replace(array('[', ']'), '', $code);
		if (!is_array($fres_c)) {
			$fres_c = get_reserv_fres('currency');
		}
		if (isset($fres_c[$ncode], $fres_c[$ncode]['sum'])) {
			return is_sum($fres_c[$ncode]['sum'], 20);
		}
	}	
	
	if (strstr($code, '[dfilereserve_')) {
		$ncode = str_replace(array('[', ']'), '', $code);
		if (!is_array($fres_d)) {
			$fres_d = get_reserv_fres('direction');
		}	
		if (isset($fres_d[$ncode], $fres_d[$ncode]['sum'])) {
			return is_sum($fres_d[$ncode]['sum'], 20);
		}		
	}
	
	return $n;
}

function fres_request_cron() {
	global $wpdb, $premiumbox;	

	if (check_hash_cron() and !$premiumbox->is_up_mode()) {

		$where = apply_filters('fres_where_filter_currency', '');

		$reserv_in_file = get_reserv_fres('currency');
		$name = 'cfilereserve_';
		$currencies = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' AND reserv_place LIKE '{$name}%' $where");
		foreach ($currencies as $currency) {
			update_currency_reserve($currency->id, $currency);
		}
		
		$where = apply_filters('fres_where_filter_direction', '');
		
		$reserv_in_file = get_reserv_fres('direction');
		$name = 'dfilereserve_';
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'reserv_place'");
		if (1 == $query and function_exists('update_direction_reserve')) {
			$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' AND reserv_place LIKE '{$name}%' $where");
			foreach ($directions as $direction) {
				update_direction_reserve($direction->id, $direction);	
			}	
		}
		
		do_action('fres_change_reserve');
	}
	
}

add_filter('list_cron_func', 'fres_list_cron_func');
function fres_list_cron_func($filters) {
	
	$filters['fres_request_cron'] = array(
		'title' => __('Parsing reserves from file', 'pn'),
		'file' => '10min',
		'site' => 'none',
	);
	
	return $filters; 
}

add_action('admin_menu', 'admin_menu_fres');
function admin_menu_fres() {
	global $premiumbox;	
	
	add_submenu_page("pn_moduls", __('File reserve', 'pn'), __('File reserve', 'pn'), 'administrator', "pn_fres", array($premiumbox, 'admin_temp'));
}

add_filter('pn_adminpage_title_pn_fres', 'def_adminpage_title_pn_fres');
function def_adminpage_title_pn_fres($page) {
	
	return __('File reserve', 'pn');
} 

add_action('pn_adminpage_content_pn_fres','def_pn_admin_content_pn_fres');
function def_pn_admin_content_pn_fres() {
	global $premiumbox;

	$form = new PremiumForm();

	$site_url = get_site_url_or();
	$text = '
	'. __('Cron URL for updating reserves', 'pn') .'<br /><a href="' . get_cron_link('fres_request_cron') . '" target="_blank">' . get_cron_link('fres_request_cron') . '</a>
	';
	$form->substrate($text);
	
	$options = array();
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('File reserve settings', 'pn'),
		'submit' => __('Save', 'pn'),
	);	
	$options['url'] = array(
		'view' => 'inputbig',
		'title' => __('URL of file with reserves for Currency section', 'pn'),
		'default' => $premiumbox->get_option('fres', 'url'),
		'name' => 'url',
	);
	$options['url2'] = array(
		'view' => 'inputbig',
		'title' => __('URL of file with reserves for Exchange directions section', 'pn'),
		'default' => $premiumbox->get_option('fres', 'url2'),
		'name' => 'url2',
	);
	$params_form = array(
		'filter' => 'pn_fres_options',
		'button_title' => __('Save', 'pn'),
	);
	$form->init_form($params_form, $options);

}  

add_action('premium_action_pn_fres', 'def_premium_action_pn_fres');
function def_premium_action_pn_fres() {
	global $premiumbox;	

	_method('post');
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));

	$options = array('url', 'url2');	
	foreach ($options as $key) {
		$premiumbox->update_option('fres', $key, pn_strip_input(is_param_post($key)));
	}				
	
	$url = admin_url('admin.php?page=pn_fres&reply=true');
	$form->answer_form($url);
	
} 