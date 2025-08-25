<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('admin_menu_ga_settings')) {
	
	$plugin = get_plugin_class();
	$plugin->include_path(__FILE__, 'settings');
	$plugin->include_path(__FILE__, 'filters');

}