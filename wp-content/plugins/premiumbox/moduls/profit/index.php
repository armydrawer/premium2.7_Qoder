<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Profit with orders[:en_US][ru_RU:]Автоматическое начисление прибыли[:ru_RU]
description: [en_US:]Profit with orders[:en_US][ru_RU:]Автоматическое начисление прибыли[:ru_RU]
version: 2.7.0
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

add_action('init', 'delete_standart_profit', 0);
function delete_standart_profit() {
	
	remove_action('tab_direction_tab2', 'profit_tab_direction_tab2', 20, 2);
	remove_filter('get_calc_data', 'calculate_profit_ditection', 10000, 2);
	
}

add_filter('pn_direction_addform_post', 'profit_direction_addform_post');
function profit_direction_addform_post($array) {
	
	$array['profit_sum1'] = '0';	
	$array['profit_pers1'] = '0';
	$array['profit_sum2'] = '0';	
	$array['profit_pers2'] = '0';
	
	return $array;
}

add_filter('get_calc_data', 'newprofit_profit_ditection', 10000, 2);
function newprofit_profit_ditection($cdata, $calc_data) {
	
	$profit1 = convert_sum($cdata['sum1r'], $cdata['currency_code_give'], cur_type());
	$profit2 = convert_sum($cdata['sum2r'], $cdata['currency_code_get'], cur_type());
	$cdata['profit'] = is_sum($profit1 - $profit2);
	
	return $cdata;
}