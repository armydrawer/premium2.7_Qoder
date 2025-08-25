<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Registration confirmation by e-mail[:en_US][ru_RU:]Подтверждение регистрации по e-mail[:ru_RU]
description: [en_US:]Registration confirmation by e-mail[:en_US][ru_RU:]Подтверждение регистрации по e-mail[:ru_RU]
version: 2.7.0
category: [en_US:]Security[:en_US][ru_RU:]Безопасность[:ru_RU]
cat: secur
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if (!function_exists('all_moduls_active_rconfirm')) {
	add_action('pn_plugin_activate', 'all_moduls_active_rconfirm');
	add_action('all_moduls_active_' . $name, 'all_moduls_active_rconfirm');
	function all_moduls_active_rconfirm() {
		global $wpdb;
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'rconfirm'");
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `rconfirm` int(1) NOT NULL default '1'");
		}
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'rconfirm_time'");
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `rconfirm_time` varchar(20) NOT NULL");
		}		
	}
}

if (!function_exists('pn_user_register_data_rconfirm')) {
	add_filter('pn_user_register_data', 'pn_user_register_data_rconfirm');
	function pn_user_register_data_rconfirm($array) {
		
		$array['rconfirm'] = 0;
		
		return $array;
	}
}

if (!function_exists('rconfirm_all_user_editform')) {
	add_filter('all_user_editform', 'rconfirm_all_user_editform', 10, 2);
	function rconfirm_all_user_editform($options, $db_data) {
		$user_id = $db_data->ID;

		if (current_user_can('administrator')) {
			$rconfirm = intval($db_data->rconfirm);
			$n_options = array();
			$n_options['rconfirm'] = array(
				'view' => 'select',
				'title' => __('Confirmed e-mail', 'pn'),
				'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
				'default' => $rconfirm,
				'name' => 'rconfirm',
			);
			if (0 == $rconfirm) {
				$n_options['resend_rconfirm'] = array(
					'view' => 'textfield',
					'title' => __('E-mail confirmation', 'pn'),
					'default' => '<a href="' . pn_link('resend_userverify_email') . '&user_id=' . $user_id . '" class="button" target="_blank">' . __('Resend', 'pn') . '</a>',
				);		
			}
					
			$options = pn_array_insert($options, 'user_email', $n_options);		
		}
		
		return $options;
	}
}

if (!function_exists('rconfirm_all_user_editform_post')) { 
	add_action('all_user_editform_post', 'rconfirm_all_user_editform_post'); 
	function rconfirm_all_user_editform_post($new_user_data) {
		
		if (current_user_can('administrator')) {
			$new_user_data['rconfirm'] = intval(is_param_post('rconfirm'));
		}
		
		return $new_user_data;
	}
}

if (!function_exists('rconfirm_pntable_columns_all_users')) {
	add_filter('pntable_columns_all_users', 'rconfirm_pntable_columns_all_users');
	function rconfirm_pntable_columns_all_users($columns) {
		
		if (current_user_can('administrator')) {
			$n_columns = array();
			$n_columns['rconfirm'] = __('Confirmed e-mail', 'pn');
			$columns = pn_array_insert($columns, 'user_email', $n_columns);
		}
		
		return $columns;
	}
}

if (!function_exists('rconfirm_admintable_sortable_columns_users')) {
	add_filter('pntable_sortable_columns_users', 'rconfirm_admintable_sortable_columns_users');
	function rconfirm_admintable_sortable_columns_users($columns) {
		
		$columns['rconfirm'] = array('rconfirm', false);
		
		return $columns;
	}
}

if (!function_exists('def_pntable_column_all_users')) {
	add_filter('pntable_column_all_users', 'def_pntable_column_all_users', 10, 3); 
	function def_pntable_column_all_users($empty, $column_name, $item) {
		
		if ('rconfirm' == $column_name) {
			$rconfirm = intval($item->rconfirm);
			if (1 == $rconfirm) {
				return '<span class="bgreen">' . __('Yes', 'pn') . '</span>';
			} else {
				return '<a href="' . pn_link('resend_userverify_email') . '&user_id=' . $item->ID . '" class="button" target="_blank">' . __('Resend', 'pn') . '</a>';
			}
		}		
		
		return $empty;	
	}
}

if (!function_exists('resend_userverify_email')) {
	function resend_userverify_email($ui) {
		
		$notify_tags = array();
		$notify_tags = apply_filters('notify_tags_rconfirm', $notify_tags, $ui->ID);		

		$user_send_data = array(
			'user_email' => is_email($ui->user_email),
		);	
		$user_send_data = apply_filters('user_send_data', $user_send_data, 'rconfirm', $ui);
		$result_mail = apply_filters('premium_send_message', 0, 'rconfirm', $notify_tags, $user_send_data);
		
	}
}

if (!function_exists('def_premium_action_resend_userverify_email')) {
	add_action('premium_action_resend_userverify_email', 'def_premium_action_resend_userverify_email');
	function def_premium_action_resend_userverify_email() {	

		pn_only_caps(array('administrator'));
			
		$user_id = intval(is_param_get('user_id'));
		if ($user_id) {
			$ui = get_userdata($user_id);
			if (isset($ui->ID)) {
				$rconfirm = intval(is_isset($ui, 'rconfirm'));
				if (0 == $rconfirm) {
					resend_userverify_email($ui);
					pn_display_mess(__('Message sent', 'pn'), __('Message sent', 'pn'), 'true');
				} else {
					pn_display_mess(__('Error! User e-mail verified', 'pn'), __('Error! User e-mail verified', 'pn'), 'error');
				}
			}
		}	
		
		pn_display_mess(__('Error! Failed to send message', 'pn'), __('Error! Failed to send message', 'pn'), 'error');
		
	}
}

if (!function_exists('rconfirm_login_check')) {
	add_filter('authenticate', 'rconfirm_login_check', 70, 1);
	function rconfirm_login_check($user) {
		global $wpdb, $pn_regiter_site;
		
		if (is_object($user) and isset($user->data->ID)) {
			if (!user_can($user, 'administrator')) {
				$rconfirm = intval($user->data->rconfirm);
				$time_wait_rconfirm = apply_filters('time_wait_rconfirm', 15);
				$rconfirm_time = intval($user->data->rconfirm_time) + ($time_wait_rconfirm * 60);
				if (1 != $rconfirm) {	
			
					$ui = $user->data;
					$now_time = current_time('timestamp');
					if ($now_time > $rconfirm_time and 1 != $pn_regiter_site) {
						$arr = array();
						$arr['rconfirm_time'] = $now_time;
						$wpdb->update($wpdb->prefix . "users", $arr, array('ID' => $user->data->ID));
						
						resend_userverify_email($ui);
					}			
			
					$error = new WP_Error();
					$error->add( 'pn_error',__('You did not confirm your e-mail', 'pn'));
					wp_clear_auth_cookie();
								
					return $error;							
				}
			}
		}	
		
		return $user;
	}
}

if (!function_exists('init_rconfirm')) {
	add_action('init', 'init_rconfirm', 11);
	function init_rconfirm() {
		
		$ui = wp_get_current_user();
		$user_id = intval(is_isset($ui, 'ID'));
		if ($user_id) {
			if (!user_can($ui, 'administrator')) {
				$rconfirm = intval($ui->rconfirm);
				if (1 != $rconfirm) {
					
					wp_logout();
					wp_redirect(PN_SITE_URL);
					exit();
					
				}	
			}
		}
		
	}
}

if (!function_exists('list_user_notify_rconfirm')) { 
	add_filter('list_user_notify', 'list_user_notify_rconfirm', 10, 2);
	function list_user_notify_rconfirm($places, $place) {
		
		if ($place == 'email') {
			$places['rconfirm'] = __('Resent confirm user e-mail', 'pn');
		}
		
		return $places;
	}
}

if (!function_exists('rconfirm_mailtemp_tags')) {
	add_filter('list_notify_tags_registerform', 'rconfirm_mailtemp_tags', 100);
	add_filter('list_notify_tags_rconfirm', 'rconfirm_mailtemp_tags', 100);
	function rconfirm_mailtemp_tags($tags) {
		
		$tags['confirm_link'] = array(
			'title' => __('Link for e-mail confirmation', 'pn'),
			'start' => '[confirm_link]',
		);
		
		return $tags;
	}
}

if (!function_exists('rconfirm_mail_text')) {
	add_filter('notify_tags_rconfirm', 'rconfirm_mail_text', 10, 2);
	add_filter('notify_tags_registerform', 'rconfirm_mail_text', 10, 2);
	function rconfirm_mail_text($notify_tags, $user_id) {
		
		$hash = trim(get_user_meta($user_id, 'rconfirm_hash', true));
		if (!$hash) {
			$hash = wp_generate_password(30 ,false, false);
			update_user_meta($user_id, 'rconfirm_hash', $hash) or add_user_meta($user_id, 'rconfirm_hash', $hash, true);
		}
		
		$confirm_link = get_request_link('confirmemail', '', get_locale(), array('user' => $user_id, 'hash' => $hash));			
		$notify_tags['[confirm_link]'] = $confirm_link;
		
		return $notify_tags;
	}
}

if (!function_exists('def_premium_request_confirmemail')) {
	add_action('premium_request_confirmemail', 'def_premium_request_confirmemail');
	function def_premium_request_confirmemail() {
		global $wpdb;

		$plugin = get_plugin_class();

		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);
		if (!$user_id) {
			$user_id = intval(is_param_get('user'));
			$hash = trim(is_param_get('hash'));
			if ($user_id and $hash) {
				$user_hash = trim(get_user_meta($user_id, 'rconfirm_hash', true));
				if ($hash == $user_hash and $user_hash) {
					$wpdb->update($wpdb->prefix . "users", array('rconfirm' => 1), array('ID' => $user_id));
					
					$url = $plugin->get_page('login') . '?rconfirm=1';
					wp_redirect($url);
					exit;
				}
			}
		}
		
		pn_display_mess(__('Error! Error of e-mail confirmation', 'pn'), __('Error! Error of e-mail confirmation', 'pn'), 'error');
		
	}
}

if (!function_exists('register2_success_message_rconfirm')) {
	add_filter('register2_success_message', 'register2_success_message_rconfirm');
	function register2_success_message_rconfirm($text) {
		
		$text = __('You have successfully registered. A confirmation of registration has been sent to your E-mail. Follow the link from the letter and log in to your personal account', 'pn');
		
		return $text;
	}
}

if (!function_exists('before_login_page_rconfirm')) {
	add_filter('before_login_page', 'before_login_page_rconfirm', 100);
	function before_login_page_rconfirm($html) {
		
		$rconfirm = intval(is_param_get('rconfirm'));
		if (1 == $rconfirm) {
			$html .= '<div class="resulttrue">' . __('Your e-mail has been successfully confirmed', 'pn') . '</div>';
		}
		
		return $html;
	}
}