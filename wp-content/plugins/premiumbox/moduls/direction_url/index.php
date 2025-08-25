<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Dash in URL of exchange direction[:en_US][ru_RU:]Тире в URL направления обмена[:ru_RU]
description: [en_US:]Rеplacing underscore with dash in URL of exchange direction[:en_US][ru_RU:]Замена нижнего подчеркивания на тире в URL направления обмена[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

add_filter('general_tech_pages', 'dirurl_general_tech_pages');
function dirurl_general_tech_pages($g_pages) {
	
	$g_pages['exchange'] = 'exchange-';
	
	return $g_pages;
}

add_filter('direction_permalink_temp', 'dirurl_direction_permalink_temp');
function dirurl_direction_permalink_temp($temp) {
	
	$temp = '[xmlv1]-to-[xmlv2]';
	
	return $temp;
}