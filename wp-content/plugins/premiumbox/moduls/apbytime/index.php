<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Access to control panel by time[:en_US][ru_RU:]Доступ к панели управления по времени[:ru_RU]
description: [en_US:]Access to control panel by time[:en_US][ru_RU:]Доступ к панели управления по времени[:ru_RU]
version: 2.7.0
category: [en_US:]Security[:en_US][ru_RU:]Безопасность[:ru_RU]
cat: secur
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('init', 'init_apbytime', 10);
function init_apbytime() {
	
	$plugin = get_plugin_class();
	if (is_admin() and !current_user_can('administrator')) {	
		$ui = wp_get_current_user();
		$user_id = intval(is_isset($ui, 'ID'));
		if ($user_id) {
			$role = $ui->roles[0];
			$data = $plugin->get_option('apbytime', $role);
			if (!get_apbytime_status($data)) {
				pn_display_mess(__('Access to the control panel is temporarily disabled', 'Access to the control panel is temporarily disabled'));
			}	
		}	
	}
	
}

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'settings');