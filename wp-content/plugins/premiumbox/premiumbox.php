<?php 
/*
Plugin Name: Premium Exchanger
Plugin URI: https://premiumexchanger.com
Description: Professional e-currency exchanger
Version: 2.7
Author: Premium
Author URI: https://premiumexchanger.com
*/

if (!defined('ABSPATH')) { exit(); }

require(dirname(__FILE__) . "/includes/class-plugin.php");
if (!class_exists('Exchanger')) {
	return;
}

$plugin = new Exchanger(__FILE__);

$plugin->file_include('default/newadminpanel/index');
$plugin->file_include('default/up_mode/index');
$plugin->file_include('default/mail_temps');
$plugin->file_include('default/themesettings');
$plugin->file_include('default/settings');
$plugin->file_include('default/lang/index');
$plugin->file_include('default/rtl/index');
$plugin->file_include('default/admin/index');
$plugin->file_include('default/globalajax/index');
$plugin->file_include('default/cron'); 
$plugin->file_include('default/roles/index');
$plugin->file_include('default/users/index');
$plugin->file_include('default/captcha/index');
$plugin->file_include('default/logs_settings/index');
$plugin->file_include('default/moduls');

$plugin->file_include('plugin/migrate/index');
$plugin->file_include('plugin/admin/index');
$plugin->file_include('plugin/config'); 
$plugin->file_include('plugin/contacts/index');
$plugin->file_include('plugin/update/index');
$plugin->file_include('plugin/users/index');
$plugin->file_include('plugin/directions/index'); 
$plugin->file_include('plugin/currency/index');
$plugin->file_include('plugin/reserv/index');
$plugin->file_include('plugin/exchange/index');
$plugin->file_include('plugin/bids/index');
$plugin->file_include('plugin/merchants/index');
$plugin->file_include('plugin/stats/index');
$plugin->file_include('plugin/exchange_filters');

unset($plugin);