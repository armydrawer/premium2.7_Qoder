<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('lostpass_placed_form')) {
	add_filter('placed_form', 'lostpass_placed_form');
	function lostpass_placed_form($placed) {
		
		$placed['lostpass1form'] = __('Lost password form', 'pn');
		
		return $placed;
	}
}

if (!function_exists('list_user_notify_lostpass')) {
	add_filter('list_user_notify', 'list_user_notify_lostpass');
	function list_user_notify_lostpass($places_admin) {
		
		$places_admin['lostpassform'] = __('Lost password form', 'pn');
		
		return $places_admin;
	}
}

if (!function_exists('def_list_notify_tags_lostpassform')) {
	add_filter('list_notify_tags_lostpassform', 'def_list_notify_tags_lostpassform');
	function def_list_notify_tags_lostpassform($tags) {	
	
		$tags['link'] = array(
			'title' => __('Link', 'pn'),
			'start' => '[link]',
		);
		
		return $tags;
	}
}

if (!function_exists('def_lostpass1form_filelds')) {
	add_filter('lostpass1form_filelds', 'def_lostpass1form_filelds');
	function def_lostpass1form_filelds($items) {
		
		$ui = wp_get_current_user();
		$items['email'] = array(
			'name' => 'email',
			'title' => __('E-mail', 'pn'),
			'req' => 1,
			'value' => '',
			'type' => 'input',
		);	
		
		return $items;
	}
}

if (!function_exists('def_lostpass2form_filelds')) {
	add_filter('lostpass2form_filelds', 'def_lostpass2form_filelds');
	function def_lostpass2form_filelds($items) {
		
		$ui = wp_get_current_user();
		$items['pass'] = array(
			'name' => 'pass',
			'title' => __('New password', 'pn'),
			'req' => 1,
			'value' => '',
			'type' => 'password',
		);
		$items['pass2'] = array(
			'name' => 'pass2',
			'title' => __('New password again', 'pn'),
			'req' => 1,
			'value' => '',
			'type' => 'password',
		);	
		
		return $items;
	}
}

if (!function_exists('def_replace_array_lostpass1form')) {
	add_filter('replace_array_lostpass1form', 'def_replace_array_lostpass1form', 10, 3);
	function def_replace_array_lostpass1form($array, $prefix, $place = '') {
		
		$fields = get_form_fields('lostpass1form', $place); 
		
		$filter_name = '';
		if ('widget' == $place) {
			$prefix = 'widget_' . $prefix;
			$filter_name = 'widget_';
		}
		$html = prepare_form_fileds($fields, $filter_name . 'lostpass1_form_line', $prefix);	
		
		$array = array(
			'[form]' => '<form method="post" class="ajax_post_form" action="' . get_pn_action('lostpass1') . '">',
			'[/form]' => '</form>',
			'[result]' => '<div class="resultgo"></div>',
			'[html]' => $html,
			'[submit]' => '<input type="submit" formtarget="_top" name="submit" class="' . $prefix . '_submit" value="' . __('Reset password', 'pn') . '" />',
		);		
		
		return $array;
	}
}

if (!function_exists('def_replace_array_lostpass2form')) {
	add_filter('replace_array_lostpass2form', 'def_replace_array_lostpass2form', 10, 3);
	function def_replace_array_lostpass2form($array, $prefix, $place = '') {
		
		$fields = get_form_fields('lostpass2form', $place); 
		
		$filter_name = '';
		if ('widget' == $place) {
			$prefix = 'widget_' . $prefix;
			$filter_name = 'widget_';
		}
		$html = prepare_form_fileds($fields, $filter_name . 'lostpass2_form_line', $prefix);	
		
		$array = array(
			'[form]' => '
			<form method="post" class="ajax_post_form" action="' . get_pn_action('lostpass2') . '">
				<input type="hidden" name="recovery_hash" value="' . pn_strip_input(is_param_get('recovery_hash')) . '" />
				<input type="hidden" name="user_id" value="' . intval(is_param_get('user_id')) . '" />
			',
			'[/form]' => '</form>',
			'[result]' => '<div class="resultgo"></div>',
			'[html]' => $html,
			'[submit]' => '<input type="submit" formtarget="_top" name="submit" class="' . $prefix . '_submit" value="' . __('Save', 'pn') . '" />',
		);		
		
		return $array;
	}
}

if (!function_exists('lostpass_page_shortcode')) {
	function lostpass_page_shortcode($atts, $content = "") {
		
		$temp = '';
		
		$temp .= apply_filters('before_lostpass_page', '');
				
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);			
				
		if ($user_id < 1) {

			$user_id = intval(is_param_get('user_id'));
			$recovery_hash = pn_strip_input(is_param_get('recovery_hash'));	

			if ($user_id and $recovery_hash) {	
				
				$array = get_form_replace_array('lostpass2form', 'lp');

				$temp_form = '
				<div class="lp_div_wrap">
				[form]
					
					<div class="lp_div_title">
						<div class="lp_div_title_ins">
							'. __('Password recovery', 'pn') .'
						</div>
					</div>
				
					<div class="lp_div">
						<div class="lp_div_ins">
							
							[html]
							
							<div class="lp_line has_submit">
								[submit]
							</div>
							
							[result]
							
						</div>
					</div>

				[/form]
				</div>
				';
		
				$temp_form = apply_filters('lostpass2_form_temp', $temp_form);
				$temp .= replace_tags($array, $temp_form);			
				
			} else {	

				$array = get_form_replace_array('lostpass1form', 'lp');
				
				$temp_form = '
				<div class="lp_div_wrap">
				[form]
					
					<div class="lp_div_title">
						<div class="lp_div_title_ins">
							'. __('Password recovery', 'pn') .'
						</div>
					</div>
				
					<div class="lp_div">
						<div class="lp_div_ins">
							
							[html]
							
							<div class="lp_line has_submit">
								[submit]
							</div>
							
							[result]
							
						</div>
					</div>

				[/form]
				</div>
				';
		
				$temp_form = apply_filters('lostpass1_form_temp', $temp_form);
				$temp .= replace_tags($array, $temp_form);			
				
			}		

		} else {
			$temp .= '<div class="resultfalse">' . __('Error! This form is available for unauthorized users only', 'pn') . '</div>';
		}	
		
		$temp .= apply_filters('after_lostpass_page', '');	
		
		return $temp;
	}
	add_shortcode('lostpass_page', 'lostpass_page_shortcode');
}

if (!function_exists('def_premium_siteaction_lostpass1')) {
	add_action('premium_siteaction_lostpass1', 'def_premium_siteaction_lostpass1');
	function def_premium_siteaction_lostpass1() {
		global $wpdb;	
		
		$plugin = get_plugin_class();
		
		_method('post');
		_json_head();

		$log = array();	
		$log['status'] = '';
		$log['status_code'] = 0;
		$log['status_text'] = '';
		
		$plugin->up_mode('post');
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		$log = _log_filter($log, 'lostpass1form');
		
		if ($user_id) {
			$log['status'] = 'error'; 
			$log['status_code'] = 1;
			$log['status_text']= __('Error! This form is available for unauthorized users only', 'pn');
			echo pn_json_encode($log);
			exit;		
		}
		
		if (!$log['status_code']) {
			$email = is_email(is_param_post('email'));
			if (!$email) {
				$log['status'] = 'error';
				$log['status_code'] = 2;
				$log['status_text'] = __('Error! You have entered an incorrect e-mail', 'pn');
				$log = pn_array_unset($log, 'url');
			}
		}

		if (!$log['status_code']) {
			$user_id = email_exists($email);
			if ($user_id < 1) {
				$log['status'] = 'error';
				$log['status_code'] = 3;
				$log['status_text'] = __('Error! This e-mail is not registered', 'pn');
				$log = pn_array_unset($log, 'url');			
			}		
		}

		if (!$log['status_code']) {
			$ui = get_userdata($user_id);
			$user_recovery_enable = intval(is_isset($ui, 'sec_lostpass'));
			if (!$user_recovery_enable) {
				$log['status'] = 'error';
				$log['status_code'] = 4;
				$log['status_text'] = __('Error! Password recovery is disabled', 'pn');
				$log = pn_array_unset($log, 'url');				
			}
		}		
		
		if (!$log['status_code']) {
			
			$user_password = wp_generate_password(20 , false, false);
			$ad_hash = md5($user_password);
					
			$wpdb->query("UPDATE ".$wpdb->prefix."users SET user_activation_key = '$ad_hash' WHERE user_email = '$email'");
					
			$notify_tags = array();
			$link = $plugin->get_page('lostpass');
			$link = add_query_args(array('user_id' => $user_id, 'recovery_hash' => $user_password), $link);
			$link = apply_filters('lostpass_remind_link', $link, $user_id, $user_password);
			$notify_tags['[link]'] = $link;
			$notify_tags = apply_filters('notify_tags_lostpassform', $notify_tags, $ui);		

			$user_send_data = array(
				'user_email' => $email,
			);	
			$user_send_data = apply_filters('user_send_data', $user_send_data, 'lostpassform', $ui);
			$result_mail = apply_filters('premium_send_message', 0, 'lostpassform', $notify_tags, $user_send_data); 				

			$log['status'] = 'success';
			$log['status_code'] = 0;
			$log['clear'] = 1;
			$log['status_text'] = apply_filters('lostpass1_success_message', __('Confirmation e-mail is sent you', 'pn'));	
			
		}  	   	
			
		echo pn_json_encode($log);	
		exit;
	}
}

if (!function_exists('def_premium_siteaction_lostpass2')) {
	add_action('premium_siteaction_lostpass2', 'def_premium_siteaction_lostpass2');
	function def_premium_siteaction_lostpass2() {
		global $wpdb;	
		
		$plugin = get_plugin_class();
		
		_method('post');
		_json_head(); 

		$log = array();	
		$log['status'] = '';
		$log['status_code'] = 0;
		$log['status_text'] = '';
		
		$plugin->up_mode('post');
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		if ($user_id) {
			$log['status'] = 'error'; 
			$log['status_code'] = 1;
			$log['status_text']= __('Error! This form is available for unauthorized users only', 'pn');
			echo pn_json_encode($log);
			exit;		
		}
		
		$log = _log_filter($log, 'lostpass2form');	
		
		$recovery_hash = pn_strip_input(is_param_post('recovery_hash'));
		$user_id = intval(is_param_post('user_id'));
		$pass = is_password(is_param_post('pass'));
		$pass2 = is_password(is_param_post('pass2'));		
		
		if (!$log['status_code']) {
			if (!$pass or $pass != $pass2) {
				$log['status'] = 'error';
				$log['status_code'] = 2;
				$log['status_text'] = __('Error! Password is incorrect or does not match with the previously entered password', 'pn');
				$log = pn_array_unset($log, 'url');
			}
		}

		if (!$log['status_code']) {
			$user = $wpdb->get_row("SELECT *  FROM " . $wpdb->prefix . "users WHERE ID = '$user_id'");
			if (!isset($user->ID)) {
				$log['status'] = 'error';
				$log['status_code'] = 3;
				$log['status_text'] = __('Error! Password recovery is disabled', 'pn');
				$log = pn_array_unset($log, 'url');			
			}		
		}

		if (!$log['status_code']) {
			$user_recovery_enable = intval($user->sec_lostpass);
			if (!$user_recovery_enable) {
				$log['status'] = 'error';
				$log['status_code'] = 4;
				$log['status_text'] = __('Error! Password recovery is disabled', 'pn');
				$log = pn_array_unset($log, 'url');				
			}
		}

		if (!$log['status_code']) {
			$user_recovery_key = $user->user_activation_key;
			$recovery_hash = md5($recovery_hash);
			if (!$user_recovery_key or $user_recovery_key != $recovery_hash) {
				$log['status'] = 'error';
				$log['status_code'] = 5;
				$log['status_text'] = __('Error! Password recovery is disabled', 'pn');
				$log = pn_array_unset($log, 'url');				
			}
		}		
		
		if (!$log['status_code']) {	
		
			$user_pass = wp_hash_password($pass);
			$wpdb->query("UPDATE " . $wpdb->prefix . "users SET user_pass = '$user_pass', user_activation_key = '' WHERE ID = '$user_id'");
					
			$log['url'] = $link = get_safe_url(apply_filters('lostpass_login_redirect', $plugin->get_page('login')));
			$log['status'] = 'success';
			$log['clear'] = 1;
			$log['status_text'] = apply_filters('lostpass2_success_message', __('Password successfully changed', 'pn'));	
			
		}
			
		echo pn_json_encode($log);	
		exit;
	}
}