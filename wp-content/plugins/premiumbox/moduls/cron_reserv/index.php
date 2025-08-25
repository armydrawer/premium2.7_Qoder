<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Link to update the currency reserve (Cron)[:en_US][ru_RU:]Ссылка для обновления резерва валюты (по Cron)[:ru_RU]
description: [en_US:]Link to update the currency reserve (Cron)[:en_US][ru_RU:]Ссылка для обновления резерва валюты (по Cron)[:ru_RU]
version: 2.7.0
category: [en_US:]Currency[:en_US][ru_RU:]Валюты[:ru_RU]
cat: currency
*/

add_filter('pntable_columns_pn_currency', 'cres_pntable_columns_pn_currency', 1000);
function cres_pntable_columns_pn_currency($columns) {
	
	$columns['cres'] = __('Cron Link', 'pn');
	
	return $columns;
}

add_filter('pntable_column_pn_currency', 'currency_pntable_column_pn_currency', 10, 3);
function currency_pntable_column_pn_currency($html, $column_name, $item) {
	
	if ('cres' == $column_name) {
		return '<a href="' . get_request_link('cres', 'html', get_locale(), array('id' => $item->id)) .  get_hash_cron('&') . '" class="button" target="_blank">' . __('Link', 'pn') . '</a>'; 
	}
	
	return $html;
}

add_action('premium_request_cres', 'cres_request_cron');
function cres_request_cron() {
	
	$data_id = intval(is_param_get('id'));
	if ($data_id and check_hash_cron() and function_exists('update_currency_reserve')) {	
		update_currency_reserve($data_id);	
	}	
	
	_e('Done', 'pn');
	
}