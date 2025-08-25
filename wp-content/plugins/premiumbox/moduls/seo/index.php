<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]SEO[:en_US][ru_RU:]SEO[:ru_RU]
description: [en_US:]SEO[:en_US][ru_RU:]SEO[:ru_RU]
version: 2.7.0
category: [en_US:]Settings[:en_US][ru_RU:]Настройки[:ru_RU]
cat: sett
*/

if (!function_exists('seo_pn_caps')) {
	add_filter('pn_caps', 'seo_pn_caps');
	function seo_pn_caps($pn_caps) {
		
		$pn_caps['pn_seo'] = __('Work with SEO', 'pn');
		
		return $pn_caps;
	}
}

if (!function_exists('admin_menu_seo')) {
	add_action('admin_menu', 'admin_menu_seo');
	function admin_menu_seo() {
		
		$plugin = get_plugin_class();
		if (current_user_can('administrator') or current_user_can('pn_seo')) {
			add_menu_page(__('SEO', 'pn'), __('SEO', 'pn'), 'read', 'all_seo', array($plugin, 'admin_temp'), $plugin->get_icon_link('seo'));  
			add_submenu_page("all_seo", __('Settings', 'pn'), __('Settings', 'pn'), 'read', "all_seo", array($plugin, 'admin_temp'));
			add_submenu_page("all_seo", __('Meta tags and metrics', 'pn'), __('Meta tags and metrics', 'pn'), 'read', "all_metasettings", array($plugin, 'admin_temp'));
			add_submenu_page("all_seo", __('XML sitemap settings', 'pn'), __('XML sitemap settings', 'pn'), 'read', "all_xmlmap", array($plugin, 'admin_temp'));
			add_submenu_page("all_seo", __('Robots.txt settings', 'pn'), __('Robots.txt settings', 'pn'), 'read', "all_robotstxt", array($plugin, 'admin_temp'));
		}
		
	}
}

add_theme_support('post-thumbnails');

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'meta'); 
$plugin->include_path(__FILE__, 'settings');
$plugin->include_path(__FILE__, 'metasettings');
$plugin->include_path(__FILE__, 'xmlmap');
$plugin->include_path(__FILE__, 'robotstxt');
$plugin->include_path(__FILE__, 'extlinks');