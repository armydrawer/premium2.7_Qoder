<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Widget for checking order status[:en_US][ru_RU:]Виджет для проверки статуса заявки[:ru_RU]
description: [en_US:]Widget for checking order status[:en_US][ru_RU:]Виджет для проверки статуса заявки[:ru_RU]
version: 2.7.0
category: [en_US:]Settings[:en_US][ru_RU:]Настройки[:ru_RU]
cat: sett
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_filter('pn_tech_pages', 'list_tech_pages_checkstatus');
function list_tech_pages_checkstatus($pages) {
 
	$pages[] = array(
		'post_name'      => 'checkstatus',
		'post_title'     => '[en_US:]Check order status[:en_US][ru_RU:]Проверка статуса заявки[:ru_RU]',
		'post_content'   => '[checkstatus_form]',
		'post_template'   => 'pn-pluginpage.php',
	);	
	
	return $pages;
}

add_filter('placed_form', 'placed_form_checkstatus');
function placed_form_checkstatus($placed) {
	
	$placed['checkstatusform'] = __('Check order status', 'pn');
	
	return $placed;
}

add_filter('checkstatusform_filelds', 'def_checkstatusform_filelds');
function def_checkstatusform_filelds($items) {
	
	$ui = wp_get_current_user();
	
	$items['exchange_id'] = array(
		'name' => 'exchange_id',
		'title' => __('Exchange ID', 'pn'),
		'placeholder' => '',
		'req' => 0,
		'value' => '', 
		'type' => 'input',
	);	
	$items['email'] = array(
		'name' => 'email',
		'title' => __('Your e-mail', 'pn'),
		'placeholder' => '',
		'req' => 1,
		'value' => is_email(is_isset($ui, 'user_email')),
		'type' => 'input',
	);	
	
	return $items;
}

global $premiumbox;
$premiumbox->auto_include($path . '/shortcode');
$premiumbox->file_include($path . '/widget/check');