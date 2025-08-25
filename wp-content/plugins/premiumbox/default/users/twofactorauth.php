<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('pincode_login_form')) {
	add_action('login_form', 'pincode_login_form');
	add_action('newadminpanel_form', 'pincode_login_form');
	function pincode_login_form() { 
	
		$pincode = pn_strip_input(is_param_get('set_pincode'));
		$cl = 'hidden_line';
		if ($pincode) {
			$cl = '';
		}
		$temp = '
		<div class="' . $cl . '">
			<div style="font-weight: 500; padding: 0 0 3px 0;">
				'. __('Personal PIN code','pn') .':
			</div>
			<div style="padding: 0 0 10px 0;">
				<input type="password" class="input" name="user_pin" autocomplete="off" value="' . $pincode . '" />
			</div>
		</div>
		';
		
		echo $temp;
	}
}

if (!function_exists('pincode_get_form_filelds')) { 
	add_filter('get_form_filelds', 'pincode_get_form_filelds', 0, 2);
	function pincode_get_form_filelds($items, $place = '') {
		
		$ui = wp_get_current_user();
		if ('loginform' == $place) {
			$logmail = pn_strip_input(is_param_get('set_logmail'));
			$pass = pn_strip_input(is_param_get('set_pass'));
			
			if (isset($items['logmail']) and $logmail) {
				$items['logmail']['value'] = $logmail;
			}
			if (isset($items['pass']) and $pass) {
				$items['pass']['value'] = $pass;
			}			
			
			$pincode = pn_strip_input(is_param_get('set_pincode'));
			$cl = 1;
			if ($pincode) {
				$cl = 0;
			}
			$n_items = array();	
			$n_items['user_pin'] = array(
				'name' => 'user_pin',
				'title' => __('Personal PIN code', 'pn'),
				'value' => $pincode,
				'type' => 'input',
				'hidden' => $cl,
			);
			$items = pn_array_insert($items, '', $n_items);
		}
		
		if ('securityform' == $place) {
			$n_items = array();	
			$n_items['email_login'] = array(
				'name' => 'email_login',
				'title' => __('Two-factor authentication by pin-code', 'pn') . ' (' . __('E-mail', 'pn') . ')',
				'req' => 0,
				'value' => is_isset($ui, 'email_login'),
				'type' => 'select',
				'options' => array(__('No', 'pn'), __('Yes', 'pn')),
			);
			$items = pn_array_insert($items, 'alogs_email', $n_items);
			if (isset($items['alogs_telegram'])) {
				$n_items = array();	
				$n_items['telegram_login'] = array(
					'name' => 'telegram_login',
					'title' => __('Two-factor authentication by pin-code', 'pn') . ' (' . __('Telegram', 'pn') . ')',
					'req' => 0,
					'value' => is_isset($ui, 'telegram_login'),
					'type' => 'select',
					'options' => array(__('No', 'pn'), __('Yes', 'pn')),
				);
				$items = pn_array_insert($items, 'alogs_telegram', $n_items);
			}
			if (isset($items['alogs_sms'])) {
				$n_items = array();	
					$n_items['sms_login'] = array(
						'name' => 'sms_login',
						'title' => __('Two-factor authentication by pin-code', 'pn') . ' (' . __('SMS', 'pn') . ')',
						'req' => 0,
						'value' => is_isset($ui, 'sms_login'),
						'type' => 'select',
						'options' => array(__('No', 'pn'), __('Yes', 'pn')),
				);
				$items = pn_array_insert($items, 'alogs_sms', $n_items);
			}			
		}	
		
		return $items;
	}
}	

if (!function_exists('pincode_global_check')) {
	function pincode_global_check($user) {
		global $wpdb;
		
		$plugin = get_plugin_class();
		$user_id = $user->data->ID;
		
		$db_user_pin = $user->data->user_pin;
		$user_pin = pn_strip_input(is_param_post('user_pin'));
		if (!$user_pin) {
			
			$user_send_data = array();
			if (isset($user->data->email_login) and 1 == $user->data->email_login) {
				$user_send_data['user_email'] = is_isset($user->data, 'user_email');
			}	
			$ui = get_userdata($user_id);
			$user_send_data = apply_filters('user_send_data', $user_send_data, 'letterauth', $ui);

			if (count($user_send_data) > 0) {
				
				$pin = get_random_password(6, true, true);
				$pinh = md5($pin);
				$wpdb->update($wpdb->prefix . "users", array('user_pin' => $pinh), array('ID' => $user_id));
						
				$notify_tags = array();
				$notify_tags['[pincode]'] = $pin;
						
				if (user_can($user_id, 'read')) {
					$link = get_admin_panel_url(); 
				} else {
					$link = rtrim($plugin->get_page('login'), '/') . '/';
				}
				
				$zn = '?';
				if (strstr($link, '?')) {
					$zn = '&';
				}						
						
				$link = $link . $zn . 'set_pincode=' . $pin . '&set_logmail='. is_param_post('logmail');
				$link = apply_filters('link_twofactorauth', $link, $pin); 
				$notify_tags['[link]'] = $link;
				$notify_tags = apply_filters('notify_tags_letterauth', $notify_tags, $user->data);
				
				$result_mail = apply_filters('premium_send_message', 0, 'letterauth', $notify_tags, $user_send_data); 		
					
				$error = new WP_Error();
				$error->add('pn_pin', __('You have been sent a pin code for authorization', 'pn'));
					
				wp_clear_auth_cookie();
				
				return $error;																							
			}
			
		} else {
			
			$user_pin_h = md5($user_pin);
			if ($user_pin_h != $db_user_pin) {
				
				$error = new WP_Error();
				$error->add('pn_error', __('Error! PIN entered incorrectly', 'pn'));
				wp_clear_auth_cookie();	
				
				return $error;
			}
			
		}	
		
		return $user;
	}
}

if (!function_exists('pincode_adminlogin_check')) {
	add_filter('authenticate', 'pincode_adminlogin_check', 100, 1);
	function pincode_adminlogin_check($user) {
		global $pn_log_in_site;
		
		$pn_log_in_site = intval($pn_log_in_site);
		if (is_object($user) and isset($user->data->ID)) {
			if (1 != $pn_log_in_site) {
				if ('true' != constant('PN_ADMIN_GOWP')) {
					return pincode_global_check($user);
				}
			}
		}
		
		return $user;
	}	
}

if (!function_exists('pincode_sitelogin_check')) {
	add_filter('authenticate', 'pincode_sitelogin_check', 100, 1);
	function pincode_sitelogin_check($user) {
		global $pn_log_in_site;
		
		$pn_log_in_site = intval($pn_log_in_site);
		if (is_object($user) and isset($user->data->ID)) {
			if (1 == $pn_log_in_site) {
				return pincode_global_check($user);	
			}
		}
		
		return $user;
	}	
}