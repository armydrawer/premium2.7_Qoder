<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Number conversion[:en_US][ru_RU:]Преобразование цифр[:ru_RU]
description: [en_US:]Number conversion to a single format[:en_US][ru_RU:]Преобразование цифр в единый формат[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

add_filter('pn_config_option', 'beautynum_pn_config_option');
function beautynum_pn_config_option($options) {
	global $premiumbox;	
	
	$options['beautynum_l1'] = array(
		'view' => 'line',
	);	
	$options['adjust'] = array(
		'view' => 'select',
		'title' => __('Show zero digits in rate', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'adjust'),
		'name' => 'adjust',
	);
	$options['beautynum'] = array(
		'view' => 'select',
		'title' => __('Space digits in rate (1000 or 1 000)', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'beautynum'),
		'name' => 'beautynum',
	);	
	$options['maxsymb_all'] = array(
		'view' => 'input',
		'title' => __('Max number of decimal places in calculations on website', 'pn'), 
		'default' => $premiumbox->get_option('exchange', 'maxsymb_all'),
		'name' => 'maxsymb_all',
	);
	$options['maxsymb_reserv'] = array(
		'view' => 'input',
		'title' => __('Max number of decimal places in calculations for currency reserve', 'pn'),
		'default' => $premiumbox->get_option('exchange', 'maxsymb_reserv'),
		'name' => 'maxsymb_reserv',
	);
	$options['maxsymb_course'] = array(
		'view' => 'input',
		'title' => __('Max number of decimal places in calculations for currency exchange rates', 'pn'),
		'default' => $premiumbox->get_option('exchange', 'maxsymb_course'),
		'name' => 'maxsymb_course',
	);	
	$options['beautynum_l2'] = array(
		'view' => 'line',
	);
	
	return $options;
}

add_action('pn_config_option_post', 'beautynum_pn_config_option_post');
function beautynum_pn_config_option_post() {
	global $premiumbox;
	
	$options = array('adjust', 'beautynum', 'maxsymb_all', 'maxsymb_reserv', 'maxsymb_course');
	foreach ($options as $key) {
		$val = intval(is_param_post($key));
		$premiumbox->update_option('exchange', $key, $val);
	}
	 
}
  
add_filter('is_out_sum', 'beautynum_is_out_sum', 100, 3);
function beautynum_is_out_sum($sum, $decimal = 12, $place = '') {
	global $premiumbox;
	
	$place = pn_strip_input($place);
	$decimal = intval($decimal);
	$symb = intval($premiumbox->get_option('exchange', 'maxsymb_' . $place));
	if ($symb > 0) {
		$sum = get_cut_sum($sum, $symb);
	}
	
	if ('reserv' == $place or 'course' == $place or 'tbl' == $place) {
		
		if (1 == $premiumbox->get_option('exchange', 'adjust')) {
			$s_arr = explode('.', $sum);
			$s_ceil = trim(is_isset($s_arr, 0));
			$s_double = trim(is_isset($s_arr, 1));
			$decimal_now = mb_strlen($s_double);
			$sum = rtrim($sum, '.');
			if (!strstr($sum, '.')) {
				$sum .= '.';
			}
			if ($decimal > $decimal_now) {
				$dop_nul = $decimal - $decimal_now;
				$r = 0;
				while ($r++ < $dop_nul) {
					$sum .= '0';
				}
			} 
		}		
		
		if (1 == $premiumbox->get_option('exchange', 'beautynum')) {
			$s_arr = explode('.', $sum);
			$s_ceil = trim(is_isset($s_arr, 0));
			$s_double = trim(is_isset($s_arr, 1));
			$zn = '';
			if (strstr($s_ceil, '-')) {
				$zn = '-';
			}
			$new_s_ceil = ltrim($s_ceil, '-');
			$ceil_len = mb_strlen($new_s_ceil);
			$new_sum = '';
			$ceil_arr = array();
			$r = 0;
			while ($r++ < $ceil_len) {
				$ceil_arr[] = mb_substr($new_s_ceil, ($r - 1), 1);
			}
			$ceil_arr_reversed = array_reverse($ceil_arr);
			$s = 0;
			$n_arr = array();
			foreach ($ceil_arr_reversed as $cearre) { $s++;
				$n_arr[] = $cearre;
				if (3 == $s) { $s = 0; $n_arr[] = ' '; }
			}
			$n_arr_reversed = array_reverse($n_arr);
			$new_sum .= implode('', $n_arr_reversed);
			$new_sum = trim($new_sum) . '.' . $s_double;
			$new_sum = $zn . rtrim($new_sum, '.');
			
			return $new_sum;
		}		
		
	}	
	
	return $sum;
}