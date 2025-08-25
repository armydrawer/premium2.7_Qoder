<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Email notification logs [:en_US][ru_RU:]Лог e-mail уведомлений[:ru_RU]
description: [en_US:]Email notification logs [:en_US][ru_RU:]Лог e-mail уведомлений[:ru_RU]
version: 2.7.1
category: [en_US:]E-mail[:en_US][ru_RU:]E-mail[:ru_RU]
cat: email
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_emlogs');
add_action('pn_plugin_activate', 'bd_all_moduls_active_emlogs');
function bd_all_moduls_active_emlogs() {
    global $wpdb;

    $table_name = $wpdb->prefix . "email_logs";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`to_mail` longtext NOT NULL,
		`subject` longtext NOT NULL,
		`html` longtext NOT NULL,
		`ot_name` longtext NOT NULL,
		`ot_mail` longtext NOT NULL,
		PRIMARY KEY ( `id` ),
		INDEX (`create_date`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
    $wpdb->query($sql);

}


add_action('admin_menu', 'admin_menu_emlogs', 49);
function admin_menu_emlogs() {

    $plugin = get_plugin_class();
    add_submenu_page("all_mail_temps", __('E-mail logs', 'pn'), __('E-mail logs', 'pn'), 'administrator', "all_emlogs", array($plugin, 'admin_temp'));

}


add_filter('wp_mail', 'emlogs_email_send', 1000);
function emlogs_email_send($atts) {
    global $wpdb;

    $arr = array();
    $arr['create_date'] = current_time('mysql');
    $to_mail = explode(',', is_isset($atts, 'to'));
    $arr['to_mail'] = pn_strip_input(implode(',', $to_mail));
    $arr['subject'] = pn_strip_input(is_isset($atts, 'subject'));
    $arr['html'] = pn_strip_input(is_isset($atts, 'message'));
    $arr['ot_name'] = pn_strip_input(str_replace(array('<', '>'), array('(', ')'), is_isset($atts, 'headers')));
    $arr['ot_mail'] = pn_strip_input(str_replace(array('<', '>'), array('(', ')'), is_isset($atts, 'headers')));
    $wpdb->insert($wpdb->prefix . 'email_logs', $arr);

    return $atts;
}


add_action('wp_mail_failed', 'emlogs_email_errors');
function emlogs_email_errors($wp_error) {
    global $wpdb;

    $data = [
        'create_date' => current_time('mysql'),
        'html' => pn_strip_input($wp_error->get_error_message()),
    ];
    $wpdb->insert("{$wpdb->prefix}email_logs", $data);
}


$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'list');