<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Export/Import[:en_US][ru_RU:]Экспорт/импорт[:ru_RU]
description: [en_US:]Export/Import of requests, currency, exchange directions[:en_US][ru_RU:]Экспорт/импорт заявок, валют, направлений обмена[:ru_RU]
version: 2.7.1
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('admin_menu', 'admin_menu_export');
function admin_menu_export() {
	global $premiumbox;	
	
	if (current_user_can('administrator') or current_user_can('pn_export_exchange')) {
		add_submenu_page("pn_moduls", __('Exchanges export', 'pn'), __('Exchanges export', 'pn'), 'read', "pn_export_exchange", array($premiumbox, 'admin_temp'));
	}
	
	if (current_user_can('administrator') or current_user_can('pn_export_exchange_direcions') or current_user_can('pn_import_exchange_direcions')) {
		add_submenu_page("pn_moduls", __('Exchange directions Export/Import', 'pn'), __('Exchange directions Export/Import', 'pn'), 'read', "pn_export_direction", array($premiumbox, 'admin_temp'));
	}
	
	if (current_user_can('administrator') or current_user_can('pn_export_currency') or current_user_can('pn_import_currency')) {
		add_submenu_page("pn_moduls", __('Currency Export/Import', 'pn'), __('Currency Export/Import', 'pn'), 'read', "pn_export_currency", array($premiumbox, 'admin_temp'));
	}
	
	if (current_user_can('administrator') or current_user_can('pn_impexp_parser')) {
		add_submenu_page("pn_moduls", __('Parser pairs Export/Import', 'pn'), __('Parser pairs Export/Import', 'pn'), 'read', "pn_export_parsers", array($premiumbox, 'admin_temp'));
	}
	
	if (current_user_can('administrator') or current_user_can('pn_impexp_bestchange')) {	
		add_submenu_page("pn_moduls", __('Bestchange Export/Import', 'pn'), __('Bestchange Export/Import', 'pn'), 'administrator', "pn_export_bestchange", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_moduls", __('Bestchange Export/Import', 'pn') . ' (api)', __('Bestchange Export/Import', 'pn') . ' (api)', 'administrator', "pn_export_bestchangeapi", array($premiumbox, 'admin_temp'));
	}
	
}

add_filter('pn_caps','export_pn_caps');
function export_pn_caps($caps) {
	
	$caps['pn_export_exchange'] = __('Exchanges export', 'pn');
	$caps['pn_export_exchange_direcions'] = __('Exchanges directions Export', 'pn');
	$caps['pn_import_exchange_direcions'] = __('Exchanges directions Import', 'pn');
	$caps['pn_export_currency'] = __('Currency Export', 'pn');
	$caps['pn_import_currency'] = __('Currency Import', 'pn');
	$caps['pn_impexp_parser'] = __('Parser pairs Import/Export', 'pn');
	$caps['pn_impexp_bestchange'] = __('BestChange Import/Export', 'pn');
	
	return $caps;
}

global $premiumbox;	
$premiumbox->include_path(__FILE__, 'exchange');
$premiumbox->include_path(__FILE__, 'direction');
$premiumbox->include_path(__FILE__, 'currency');
$premiumbox->include_path(__FILE__, 'parsers');
$premiumbox->include_path(__FILE__, 'bestchange');
$premiumbox->include_path(__FILE__, 'bestchangeapi');