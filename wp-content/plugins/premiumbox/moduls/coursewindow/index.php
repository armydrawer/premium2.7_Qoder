<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Notification window[:en_US][ru_RU:]Окно уведомлений[:ru_RU]
description: [en_US:]Popup notification about executed exchange and exchange rate change[:en_US][ru_RU:]Всплывающее окно уведомлений о совершнном обмене и изменения курса обмена[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

add_action('admin_menu', 'pn_adminpage_coursewindow', 1000);
function pn_adminpage_coursewindow() {
	
	global $premiumbox;	
	if (current_user_can('administrator') or current_user_can('pn_directions')) {
		add_submenu_page("pn_moduls", __('Notification window', 'pn'), __('Notification window', 'pn'), 'administrator', "pn_coursewindow", array($premiumbox, 'admin_temp'));
	}
	
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'settings');
$premiumbox->include_path(__FILE__, 'window');