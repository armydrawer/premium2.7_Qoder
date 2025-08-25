<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]SMS[:en_US][ru_RU:]SMS[:ru_RU]
description: [en_US:]SMS[:en_US][ru_RU:]SMS[:ru_RU]
version: 2.7.0
category: [en_US:]Other[:en_US][ru_RU:]Остальное[:ru_RU]
cat: other
dependent: -
*/

add_action('admin_menu', 'admin_menu_sms', 50);
function admin_menu_sms() {
	global $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_change_notify')) {
		add_submenu_page("all_mail_temps", __('SMS templates', 'pn'), __('SMS templates', 'pn'), 'read', "all_sms_temps", array($premiumbox, 'admin_temp'));
		add_submenu_page("all_mail_temps", __('SMS gates', 'pn'), __('SMS gates', 'pn'), 'read', "all_sms_list", array($premiumbox, 'admin_temp'));
		add_submenu_page("all_mail_temps", __('Add SMS gates', 'pn'), __('Add SMS gates', 'pn'), 'read', "all_sms_add", array($premiumbox, 'admin_temp'));
	}
	
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'functions');
set_extandeds($premiumbox, 'sms');
$premiumbox->include_path(__FILE__, 'list');
$premiumbox->include_path(__FILE__, 'add');
$premiumbox->include_path(__FILE__, 'settings');