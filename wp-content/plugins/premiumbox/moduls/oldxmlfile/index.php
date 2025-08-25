<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Old  URL of XML file [:en_US][ru_RU:]Старый URL XML файла[:ru_RU]
description: [en_US:]Old URL /exportxml.xml for XML file with exchange rates[:en_US][ru_RU:]Старый URL /exportxml.xml для XML файла с курсами[:ru_RU]
version: 2.7.0
category: [en_US:]Other[:en_US][ru_RU:]Остальное[:ru_RU]
cat: other
dependent: direction_xml
*/

if (!function_exists('init_oldxml')) {
	add_action('init', 'init_oldxml', 2);
	function init_oldxml() {	
		$request = ltrim(get_request_query(), '/');
		if ('exportxml.xml' == $request) {

			if (function_exists('def_premium_request_exportxml')) {
				def_premium_request_exportxml();
			}

			exit;
		}
	}
}