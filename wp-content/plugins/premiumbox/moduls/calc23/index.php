<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Calculator 2.3[:en_US][ru_RU:]Калькулятор 2.3[:ru_RU]
description: [en_US:]Calculator 2.3[:en_US][ru_RU:]Калькулятор 2.3[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

add_filter('get_calc_data', 'disabledcalc_get_calc_data', 15, 2);
function disabledcalc_get_calc_data($cdata, $calc_data) {
	
	$cdata['correct_up'] = '0';
	$cdata['correct_down'] = '0';
	$cdata['changed'] = '0';
	
	return $cdata;
}