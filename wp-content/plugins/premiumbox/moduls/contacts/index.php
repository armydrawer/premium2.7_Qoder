<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Contacts[:en_US][ru_RU:]Контакты[:ru_RU]
description: [en_US:]Contacts[:en_US][ru_RU:]Форма контактов[:ru_RU]
version: 2.7.0
category: [en_US:]Settings[:en_US][ru_RU:]Настройки[:ru_RU]
cat: sett
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if (!function_exists('list_tech_pages_contacts')) {
	add_filter('pn_tech_pages', 'list_tech_pages_contacts');
	function list_tech_pages_contacts($pages) {
		
		$pages[] = array(
			'post_name'      => 'feedback',
			'post_title'     => '[en_US:]Contacts[:en_US][ru_RU:]Контакты[:ru_RU]',
			'post_content'   => '[contact_form]',
			'post_template'   => 'pn-pluginpage.php',
		);	
		
		return $pages;
	}
}

if (!function_exists('placed_form_contact')) {
	add_filter('placed_form', 'placed_form_contact');
	function placed_form_contact($placed) {
		
		$placed['contactform'] = __('Contact form', 'pn');
		
		return $placed;
	}
}

if (!function_exists('list_admin_notify_contactform')) {
	add_filter('list_admin_notify', 'list_admin_notify_contactform');
	function list_admin_notify_contactform($places_admin) {
		
		$places_admin['contactform'] = __('Contact form', 'pn');
		
		return $places_admin;
	}
}

if (!function_exists('list_user_notify_contactform')) {
	add_filter('list_user_notify', 'list_user_notify_contactform');
	function list_user_notify_contactform($places_admin) {
		
		$places_admin['contactform_auto'] = __('Auto-responder (contact form)', 'pn');
		
		return $places_admin;
	}
}

if (!function_exists('def_mailtemp_tags_contactform')) {
	add_filter('list_notify_tags_contactform', 'def_mailtemp_tags_contactform');
	add_filter('list_notify_tags_contactform_auto', 'def_mailtemp_tags_contactform');
	function def_mailtemp_tags_contactform($tags) {
		
		$tags['name'] = array(
			'title' => __('Your name', 'pn'),
			'start' => '[name]',
		);
		$tags['text'] = array(
			'title' => __('Text', 'pn'),
			'start' => '[text]',
		);	
		$tags['email'] = array(
			'title' => __('Your e-mail', 'pn'),
			'start' => '[email]',
		);	
		$tags['link'] = array(
			'title' => __('Reply link', 'pn'),
			'start' => '[link]',
		);
		$tags['ip'] = array(
			'title' => __('IP address', 'pn'),
			'start' => '[ip]',
		);	

		return $tags;
	}
}

if (!function_exists('def_contactform_filelds')) {
	add_filter('contactform_filelds', 'def_contactform_filelds');
	function def_contactform_filelds($items) {
		
		$ui = wp_get_current_user();

		$items['name'] = array(
			'name' => 'name',
			'title' => __('Your name', 'pn'),
			'req' => 1,
			'value' => strip_uf(is_isset($ui, 'first_name'), 'first_name'),
			'type' => 'input',
			'atts' => array('class' => 'notclear'),
		);
		$items['email'] = array(
			'name' => 'email',
			'title' => __('Your e-mail', 'pn'),
			'req' => 1,
			'value' => strip_uf(is_isset($ui, 'user_email'), 'user_email'),
			'type' => 'input',
			'atts' => array('class' => 'notclear'),
		);		
		$items['text'] = array(
			'name' => 'text',
			'title' => __('Message', 'pn'),
			'req' => 1,
			'value' => '', 
			'type' => 'text',
		);		
		
		return $items;
	}
}

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'settings');
$plugin->include_path(__FILE__, 'display');