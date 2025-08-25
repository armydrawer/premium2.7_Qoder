<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Don't make currency reserve negative[:en_US][ru_RU:]Не делать резерв валюты отрицательным[:ru_RU]
description: [en_US:]Don't make currency reserve negative[:en_US][ru_RU:]Не делать резерв валюты отрицательным[:ru_RU]
version: 2.7.0
category: [en_US:]Currency[:en_US][ru_RU:]Валюты[:ru_RU]
cat: currency
*/

add_filter('get_currency_reserve', 'get_currency_reserve_currminreserv', 10000, 3);
function get_currency_reserve_currminreserv($reserve, $data, $decimal) {
	
	if ($reserve < 0) {
		$reserve = 0;
	}		
	
	return $reserve;
}

add_filter('get_direction_reserve', 'get_direction_reserve_currminreserv', 10000, 4);
function get_direction_reserve_currminreserv($reserve, $vd1, $vd2, $direction) {
	
	if ($reserve < 0) {
		$reserve = 0;
	}			
	
	return $reserve;
}										