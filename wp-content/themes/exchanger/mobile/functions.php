<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('mobile_theme_include')) {
	return;
}

mobile_theme_include('includes/sites_func');
mobile_theme_include('includes/api');

mobile_theme_include('settings/color_scheme'); 
mobile_theme_include('settings/all');
mobile_theme_include('settings/home');