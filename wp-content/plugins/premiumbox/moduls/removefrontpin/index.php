<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Removing pin-code field in authorization form[:en_US][ru_RU:]Удаление поля пин-код в форме авторизации[:ru_RU]
description: [en_US:]Removing pin-code field in authorization form щт user part of web site[:en_US][ru_RU:]Удаление поля пин-код в форме авторизации в пользовательской части сайта[:ru_RU]
version: 2.7.0
category: [en_US:]Other[:en_US][ru_RU:]Остальное[:ru_RU]
cat: other
*/

add_action('init', 'remove_pincode_front', 0);
function remove_pincode_front() {
	
	remove_filter('authenticate', 'pincode_sitelogin_check', 100, 1);
	remove_filter('get_form_filelds', 'pincode_get_form_filelds', 0, 2);
	
}