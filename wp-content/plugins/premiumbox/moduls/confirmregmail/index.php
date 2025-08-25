<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Confirmation of e-mail before registration[:en_US][ru_RU:]Подтверждение e-mail перед регистрацией[:ru_RU]
description: [en_US:]Confirmation of e-mail before registration[:en_US][ru_RU:]Подтверждение e-mail перед регистрацией[:ru_RU]
version: 2.7.0
category: [en_US:]Security[:en_US][ru_RU:]Безопасность[:ru_RU]
cat: secur
new: 1
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('pn_plugin_activate', 'all_moduls_active_confirmregmail');
add_action('all_moduls_active_' . $name, 'all_moduls_active_confirmregmail');
function all_moduls_active_confirmregmail() {
	global $wpdb;

	$table_name = $wpdb->prefix . "confirmregmail";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`create_date` datetime NOT NULL,
		`send_date` datetime NOT NULL,
		`confirm_code` varchar(250) NOT NULL,
		`user_value` varchar(250) NOT NULL,
		`user_session` varchar(250) NOT NULL,
		`status` bigint(20) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`user_value`),
		INDEX (`user_session`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql); 
			
}

add_filter('list_user_notify', 'list_user_notify_confirmregmail', 10, 2);
function list_user_notify_confirmregmail($places, $place) {
	
	if ('email' == $place) {
		$places['confirmregmail'] = __('Confirmation of e-mail before registration', 'pn');
	}
	
	return $places;
}

add_filter('list_notify_tags_confirmregmail','confirmregmail_mailtemp_tags', 100);
function confirmregmail_mailtemp_tags($tags) {
	
	$tags['code'] = array(
		'title' => __('Confirmation code', 'pn'),
		'start' => '[code]',
	);
	
	return $tags;
}

add_action('admin_menu', 'admin_menu_confirmregmail');
function admin_menu_confirmregmail() {
	global $premiumbox;	
	
	add_submenu_page("pn_moduls", __('Confirmation of e-mail before registration', 'pn'), __('Confirmation of e-mail before registration', 'pn'), 'administrator', "pn_confirmregmail", array($premiumbox, 'admin_temp'));
}

add_filter('pn_adminpage_title_pn_confirmregmail', 'def_adminpage_title_pn_confirmregmail');
function def_adminpage_title_pn_confirmregmail($page) {
	
	return __('Confirmation of e-mail before registration', 'pn');
}

add_action('pn_adminpage_content_pn_confirmregmail', 'pn_admin_content_pn_confirmregmail');
function pn_admin_content_pn_confirmregmail() {
	global $wpdb, $premiumbox;

	$form = new PremiumForm();

	$options = array();
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Settings', 'pn'),
		'submit' => __('Save', 'pn'),
	);
	$options['vid'] = array(
		'view' => 'select',
		'title' => __('Code type', 'pn'),
		'options' => array('0' => __('Digits', 'pn'), '1' => __('Letters', 'pn')),
		'default' => $premiumbox->get_option('confirmregmail', 'vid'),
		'name' => 'vid',
		'work' => 'int',
	);	
	$options['time_check'] = array(
		'view' => 'input',
		'title' => __('Timeout (seconds)', 'pn'),
		'default' => $premiumbox->get_option('confirmregmail', 'time_check'),
		'name' => 'time_check',
		'work' => 'int',
	);	
	$params_form = array(
		'filter' => 'pn_confirmregmail_options',
		'button_title' => __('Save', 'pn'),
	);
	$form->init_form($params_form, $options);	  
}  

add_action('premium_action_pn_confirmregmail', 'def_premium_action_pn_confirmregmail');
function def_premium_action_pn_confirmregmail() {
	global $wpdb, $premiumbox;	

	_method('post');
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));
	
	$vid = intval(is_param_post('vid'));
	$premiumbox->update_option('confirmregmail', 'vid', $vid);
	
	$time_check = intval(is_param_post('time_check'));
	$premiumbox->update_option('confirmregmail', 'time_check', $time_check);	

	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
			
	$form->answer_form($back_url);
	
}  

add_filter('get_form_filelds', 'confirmregmail_get_form_filelds', 0, 2);
function confirmregmail_get_form_filelds($items, $place) {
	
	$n_items = array();	
	$n_items['confirm_code'] = array(
		'name' => 'confirm_code',
		'title' => sprintf(__('Confirmation code %s', 'pn'), 'e-mail'),
		'value' => '',
		'type' => 'input',
		'hidden' => 1,
		'atts' => array('class' => 'error'),
	);	
	if ('registerform' == $place) {
		$items = pn_array_insert($items, 'email', $n_items, 'after');
	}
	if ('accountform' == $place) {
		$items = pn_array_insert($items, 'user_email', $n_items, 'after');
	}	
	
	return $items;
}

add_filter('registerform_ajax_form', 'confirmregmail_register_ajax_form', 10000);
function confirmregmail_register_ajax_form($log) {
	
	if (!$log['status_code']) {
		$email = is_email(is_param_post('email')); 
		if ($email) {
			$log = _confirmregmail($log, $email);
		}
	}	
	
	return $log;
}

add_filter('accountform_ajax_form', 'confirmregmail_account_ajax_form', 10000);
function confirmregmail_account_ajax_form($log) {
	
	if (!$log['status_code']) {
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		if ($user_id > 0) {
			$old_user_email = trim($ui->user_email);
			$email = is_email(is_param_post('user_email'));
			if ($email and $email != $old_user_email) {
				$log = _confirmregmail($log, $email);
			}
		}
	}	
	
	return $log;
}

function _confirmregmail($log, $email) {
	global $wpdb, $premiumbox;

	$delete_days = apply_filters('confirmregmail_delete_days', 5);
	$time = current_time('timestamp') - ($delete_days * DAY_IN_SECONDS);
	$ldate = date('Y-m-d H:i:s', $time);
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "confirmregmail WHERE create_date < '$ldate'");
			
	$user_session = get_session_id();
	$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "confirmregmail WHERE user_session = '$user_session' AND user_value = '$email'");
	if (!isset($item->id)) {
				
		$array = array();
		$array['create_date'] = current_time('mysql');
		$vid = intval($premiumbox->get_option('confirmregmail', 'vid'));
		if (1 == $vid) {
			$array['confirm_code'] = get_random_password(8, 0, 1);
		} else {
			$array['confirm_code'] = get_random_password(8, 1, 0);
		}
		$array['user_value'] = $email;
		$array['user_session'] = $user_session;
		$array['status'] = 0;
		$wpdb->insert($wpdb->prefix . "confirmregmail", $array);
		$array['id'] = $wpdb->insert_id;
		$item = (object)$array;
				
	}
	if (isset($item->id)) {
		$status = intval($item->status);
		if (1 != $status) {
			$get_confirm_code = pn_strip_input(is_param_post('confirm_code'));
			$confirm_code = pn_strip_input($item->confirm_code);
			if ($get_confirm_code and $get_confirm_code == $confirm_code) {
				
				$array = array();
				$array['status'] = 1;
				$wpdb->update($wpdb->prefix . "confirmregmail", $array, array('id' => $item->id));
				
			} else {
				if ($confirm_code and $get_confirm_code != $confirm_code) {
					
					$log['status']	= 'error';
					$log['status_code'] = '115';
					$log['status_text'] = sprintf(__('You entered an incorrect verification code %s', 'pn'), 'e-mail');
					
				}	
				
				$send_time = strtotime(is_isset($item, 'send_date'));
				$now_time = current_time('timestamp');
				$wait_seconds = intval($premiumbox->get_option('confirmregmail', 'time_check'));
				if ($wait_seconds < 0) { $wait_seconds = 180; }
				$send_time_last = $send_time + $wait_seconds;
				if (!isset($item->send_date) or $send_time_last < $now_time) {
							
					$array = array();
					$array['send_date'] = current_time('mysql');
					$wpdb->update($wpdb->prefix . "confirmregmail", $array, array('id' => $item->id));	
							
					$notify_tags = array();			
					$notify_tags['[code]'] = $confirm_code;
					$notify_tags = apply_filters('notify_tags_confirmregmail', $notify_tags);		

					$user_send_data = array(
						'user_email' => $email,
					);	
					$user_send_data = apply_filters('user_send_data', $user_send_data, 'confirmregmail');
					$result_mail = apply_filters('premium_send_message', 0, 'confirmregmail', $notify_tags, $user_send_data);
							
					$log['status']	= 'success';
					$log['status_code'] = '1'; 
					$log['show_hidden'] = 1;
					$log['status_text'] = __('An email with a confirmation code has been sent to your email. Enter it in the field.', 'pn');						
							
				}				
			}
				
		}
	} else {
				
		$log['status']	= 'error';
		$log['status_code'] = '115';
		$log['status_text'] = 'Error module "confirmregmail"';
				
	}
	
	return $log;		
}