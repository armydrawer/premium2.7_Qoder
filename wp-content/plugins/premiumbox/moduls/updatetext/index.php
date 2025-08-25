<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Maintenance notification[:en_US][ru_RU:]Текст уведомления технического обслуживания[:ru_RU]
description: [en_US:]Maintenance notification[:en_US][ru_RU:]Текст уведомления технического обслуживания[:ru_RU]
version: 2.7.0
category: [en_US:]Settings[:en_US][ru_RU:]Настройки[:ru_RU]
cat: sett
dependent: -
*/

add_filter('all_settings_option', 'mywarning_settings_option');
function mywarning_settings_option($options) {

	$options['line_mywarning'] = array(
		'view' => 'line',
	);
	$options['mywarning_text'] = array(
		'view' => 'editor',
		'title' => __('Maintenance message', 'pn'),
		'default' => get_option('pn_update_plugin_text'),
		'name' => 'mywarning_text',
		'rows' => '10',
	);		
		
	return $options;	
}

add_action('all_settings_option_post', 'mywarning_settings_option_post');
function mywarning_settings_option_post() {
		
	$text = pn_strip_text(is_param_post('mywarning_text'));
	update_option('pn_update_plugin_text', $text);
		
} 