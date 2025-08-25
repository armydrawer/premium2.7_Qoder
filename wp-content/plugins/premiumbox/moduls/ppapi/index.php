<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Affiliate program API[:en_US][ru_RU:]Партнерская программа API[:ru_RU]
description: [en_US:]Affiliate program API[:en_US][ru_RU:]Партнерская программа API[:ru_RU]
version: 2.7.0
category: [en_US:]Affiliate program[:en_US][ru_RU:]Партнерская программа[:ru_RU]
cat: affiliate_program
dependent: pp
deprecated: 1
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_ppapi');
add_action('pn_plugin_activate', 'bd_all_moduls_active_ppapi');
function bd_all_moduls_active_ppapi() {
	global $wpdb;	

    $query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'ppapikey'");
    if (0 == $query) {
        $wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `ppapikey` varchar(250) NOT NULL");
    }
	
    $query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'workppapikey'");
    if (0 == $query) {
        $wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `workppapikey` int(1) NOT NULL default '0'");
    }	

}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'filters'); 
$premiumbox->include_path(__FILE__, 'api'); 