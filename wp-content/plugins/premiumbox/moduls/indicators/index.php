<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Icon indicators[:en_US][ru_RU:]Иконки индикаторы[:ru_RU]
description: [en_US:]Icon indicators[:en_US][ru_RU:]Иконки индикаторы[:ru_RU]
version: 2.7.0
category: [en_US:]Users[:en_US][ru_RU:]Пользователи[:ru_RU]
cat: user
*/

if (!function_exists('wp_before_admin_bar_render_icind')) {
	add_action('wp_before_admin_bar_render', 'wp_before_admin_bar_render_icind', 10);
	function wp_before_admin_bar_render_icind() {
		global $wp_admin_bar;
		
		$plugin = get_plugin_class();
		
		$icons = apply_filters('_icon_indicators', array());
		foreach ($icons as $icon_key => $icon_data) {
			$dis = intval($plugin->get_option('iconbar_dis', $icon_key));
			if (1 != $dis) {
				$count = apply_filters('_icon_indicator_' . $icon_key, 0);
				if ($count > 0) {
					$wp_admin_bar->add_menu( array(
						'id'     => 'newii_' . $icon_key,
						'href' => is_isset($icon_data, 'url'),
						'title'  => '<div style="height: 32px; width: 32px; background: url(' . is_isset($icon_data, 'img') . ') no-repeat center center"></div>',
						'meta' => array( 
							'title' => is_isset($icon_data, 'title') . '(' . $count . ')',
							'class' => 'premium_ab_icon',
						)		
					));					
				}
			}
		}	
	}
}

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'settings');