<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Confirmation of e-mail before create bid[:en_US][ru_RU:]Подтверждение e-mail перед созданием заявки[:ru_RU]
description: [en_US:]Confirmation of e-mail before create bid[:en_US][ru_RU:]Подтверждение e-mail перед созданием заявки[:ru_RU]
version: 2.7.0
category: [en_US:]Security[:en_US][ru_RU:]Безопасность[:ru_RU]
cat: secur
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('pn_plugin_activate', 'all_moduls_active_confirmexchmail');
add_action('all_moduls_active_' . $name, 'all_moduls_active_confirmexchmail');
function all_moduls_active_confirmexchmail() {
	global $wpdb;

	$table_name = $wpdb->prefix . "confirmexchmail";
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

add_filter('list_user_notify', 'list_user_notify_napsemail', 10, 2);
function list_user_notify_napsemail($places, $place) {
	
	if ('email' == $place) {
		$places['napsemail'] = __('Confirmation of e-mail before create bid', 'pn');
	}
	
	return $places;
}

add_filter('list_notify_tags_napsemail', 'def_mailtemp_tags_napsemail');
function def_mailtemp_tags_napsemail($tags) {
	
	$tags['code'] = array(
		'title' => __('Confirmation code', 'pn'),
		'start' => '[code]',
	);
	
	return $tags;
}

add_action('admin_menu', 'admin_menu_napsemail');
function admin_menu_napsemail() {
	global $premiumbox;		

	add_submenu_page("pn_moduls", __('Confirmation of e-mail before create bid', 'pn'), __('Confirmation of e-mail before create bid', 'pn'), 'administrator', "pn_napsemail", array($premiumbox, 'admin_temp'));
}

add_filter('pn_adminpage_title_pn_napsemail', 'def_adminpage_title_pn_napsemail');
function def_adminpage_title_pn_napsemail($page) {
	
	return __('Confirmation of e-mail before create bid', 'pn');
}

add_action('pn_adminpage_content_pn_napsemail', 'pn_admin_content_pn_napsemail');
function pn_admin_content_pn_napsemail() {
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
		'default' => $premiumbox->get_option('napsemail', 'vid'),
		'name' => 'vid',
		'work' => 'int',
	);	
	$options['sendto'] = array(
		'view' => 'select',
		'title' => __('Send e-mail to', 'pn'),
		'options' => array('0' => __('All users', 'pn'), '1' => __('Guests', 'pn')),
		'default' => $premiumbox->get_option('napsemail', 'sendto'),
		'name' => 'sendto',
		'work' => 'int',
	);
	$options['time_check'] = array(
		'view' => 'input',
		'title' => __('Timeout (seconds)', 'pn'),
		'default' => $premiumbox->get_option('napsemail', 'time_check'),
		'name' => 'time_check',
		'work' => 'int',
	);	
	$options['field'] = array(
		'view' => 'select',
		'title' => __('Verification option', 'pn'),
		'options' => array('0' => __('Account Send', 'pn'), '1' => __('Account Receive', 'pn'), '2' => __('E-mail', 'pn')),
		'default' => $premiumbox->get_option('napsemail', 'field'),
		'name' => 'field',
		'work' => 'int',
	);	
	$params_form = array(
		'filter' => 'pn_napsemail_options',
		'button_title' => __('Save', 'pn'),
	);
	$form->init_form($params_form, $options);
	
}  

add_action('premium_action_pn_napsemail', 'def_premium_action_pn_napsemail');
function def_premium_action_pn_napsemail() {
	global $wpdb, $premiumbox;	

	_method('post');
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));
	
	$vid = intval(is_param_post('vid'));
	$premiumbox->update_option('napsemail', 'vid', $vid);
	
	$field = intval(is_param_post('field'));
	$premiumbox->update_option('napsemail', 'field', $field);	
	
	$sendto = intval(is_param_post('sendto'));
	$premiumbox->update_option('napsemail', 'sendto', $sendto);

	$time_check = intval(is_param_post('time_check'));
	$premiumbox->update_option('napsemail', 'time_check', $time_check);	

	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
			
	$form->answer_form($back_url);
	
}  

if (!function_exists('list_tabs_direction_verify')) {
	add_filter('list_tabs_direction', 'list_tabs_direction_verify');
	function list_tabs_direction_verify($list_tabs) {
		
		$list_tabs['verify'] = __('Verification', 'pn');
		
		return $list_tabs;
	}
}

add_action('tab_direction_verify', 'napsemail_tab_direction_verify', 50, 2);
function napsemail_tab_direction_verify($data, $data_id) {
?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Verification through e-mail', 'pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<select name="email_button" autocomplete="off">
					<?php 
					$email_button = intval(get_direction_meta($data_id, 'email_button')); 
					?>						
					<option value="0" <?php selected($email_button, 0); ?>><?php _e('No', 'pn');?></option>
					<option value="1" <?php selected($email_button, 1); ?>><?php _e('Yes', 'pn');?></option>						
				</select>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<select name="email_button_verify" autocomplete="off">
					<?php 
					$email_button_verify = intval(get_direction_meta($data_id, 'email_button_verify')); 
					?>						
					<option value="0" <?php selected($email_button_verify, 0); ?>><?php _e('Default', 'pn');?></option>
					<option value="1" <?php selected($email_button_verify, 1); ?>><?php _e('Account Send', 'pn');?></option>
					<option value="2" <?php selected($email_button_verify, 2); ?>><?php _e('Account Receive', 'pn');?></option>
					<option value="3" <?php selected($email_button_verify, 3); ?>><?php _e('E-mail', 'pn');?></option>					
				</select>
			</div>
		</div>		
	</div>	
<?php	
}  

add_action('item_direction_edit', 'item_direction_edit_napsemail'); 
add_action('item_direction_add', 'item_direction_edit_napsemail');
function item_direction_edit_napsemail($data_id) {
	
	$button = intval(is_param_post('email_button'));
	update_direction_meta($data_id, 'email_button', $button);
	
	$button_verify = intval(is_param_post('email_button_verify'));
	update_direction_meta($data_id, 'email_button_verify', $button_verify);
	
} 

function _confirmexchmail_field($direction) { 
	global $premiumbox;	
	
	$name = '';
	if (!_is('is_api')) {
		$sendto = intval($premiumbox->get_option('napsemail', 'sendto'));
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		if (0 == $sendto or 1 == $sendto and $user_id < 1) {		
			$button = intval(get_direction_meta($direction->id, 'email_button')); 
			if ($button) {
				$field_now = intval(get_direction_meta($direction->id, 'email_button_verify'));
				if (0 == $field_now) {
					$field = intval($premiumbox->get_option('napsemail', 'field'));
					if (0 == $field) {
						$name = 'account_give';
					} elseif (1 == $field) {
						$name = 'account_get';
					} elseif (2 == $field) {	
						$name = 'user_email';
					}
				} elseif (1 == $field_now) {
					$name = 'account_give';
				} elseif (2 == $field_now) {
					$name = 'account_get';
				} elseif (3 == $field_now) {
					$name = 'user_email';
				}
			}	
		}	
	}
	
	return $name;	
}

add_filter('list_direction_fields', 'napsemail_list_direction_fields', 10000, 2);  
function napsemail_list_direction_fields($fields, $direction) { 
	
	$n_fields = array();
	
	$field_name = _confirmexchmail_field($direction);
	foreach ($fields as $field_k => $field_v) {
		$format = trim(is_isset($field_v, 'format'));
		$n_fields[$field_k] = $field_v;
		if ('user_email' == $format and 'user_email' == $field_name) {
			
			$n_fields['user_email_verify_code'] = array(
				'type' => 'text',
				'name' => 'user_email_verify_code',
				'id' => 'user_email_verify_code',
				'autocomplete' => 'off',
				'value' => '',
				'label' => sprintf(__('Confirmation code %s', 'pn'), 'e-mail'),
				'req' => 0,
				'class' => 'js_user_email_verify_code',
				'wrap_class' => 'hidden_line',
			);	
			
		}
	}
	
	return $n_fields;
}

add_filter('list_currency_fields', 'napsemail_list_currency_fields', 10000, 4);  
function napsemail_list_currency_fields($fields, $vd, $direction, $side_id) { 
	
	$n_fields = array();
	$field_name = _confirmexchmail_field($direction);
	foreach ($fields as $field_k => $field_v) {
		$name = trim(is_isset($field_v, 'name'));
		$n_fields[$field_k] = $field_v;
		if ('account1' == $name and 'account_give' == $field_name or 'account2' == $name and 'account_get' == $field_name) {
			
			$n_fields['user_email_verify_code'] = array(
				'type' => 'text',
				'name' => 'user_email_verify_code',
				'id' => 'user_email_verify_code',
				'autocomplete' => 'off',
				'value' => '',
				'label' => sprintf(__('Confirmation code %s', 'pn'), 'e-mail'),
				'req' => 0,
				'class' => 'js_user_email_verify_code',
				'wrap_class' => 'hidden_line',
			);	
			
		}
	}
	
	return $n_fields;
}

add_filter('error_bids', 'napsemail_error_bids', 150, 4); 
function napsemail_error_bids($error_bids, $direction, $vd1, $vd2) {
	global $wpdb, $premiumbox;	
	
	$error_text = $error_bids['error_text'];
	$error_fields = $error_bids['error_fields'];
	if (is_array($error_text) and count($error_text) > 0 or count($error_fields) > 0) {
		
	} else {	
	
		$field_name = _confirmexchmail_field($direction);
	
		$delete_days = apply_filters('confirmregmail_delete_days', 5);
		$time = current_time('timestamp') - ($delete_days * DAY_IN_SECONDS);
		$ldate = date('Y-m-d H:i:s', $time);
		$wpdb->query("DELETE FROM " . $wpdb->prefix . "confirmexchmail WHERE create_date < '$ldate'");

		if ($field_name) {
			$user_value = pn_strip_input(is_isset($error_bids['bid'], $field_name));
			if ($user_value) {
				$user_session = get_session_id();
				$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "confirmexchmail WHERE user_session = '$user_session' AND user_value = '$user_value'");
				if (!isset($item->id)) {
				
					$array = array();
					$array['create_date'] = current_time('mysql');
					$vid = intval($premiumbox->get_option('napsemail', 'vid'));
					if (1 == $vid) {
						$array['confirm_code'] = get_random_password(8, 0, 1);
					} else {
						$array['confirm_code'] = get_random_password(8, 1, 0);
					}
					$array['user_value'] = $user_value;
					$array['user_session'] = $user_session;
					$array['status'] = 0;
					$wpdb->insert($wpdb->prefix ."confirmexchmail", $array);
					$array['id'] = $wpdb->insert_id;
					$item = (object)$array;
				
				}	
				if (isset($item->id)) {
					$status = intval($item->status);
					if (1 != $status) {
						$get_confirm_code = pn_strip_input(is_param_post('user_email_verify_code'));
						$confirm_code = pn_strip_input($item->confirm_code);
						if ($get_confirm_code and $get_confirm_code == $confirm_code) {
							
							$array = array();
							$array['status'] = 1;
							$wpdb->update($wpdb->prefix ."confirmexchmail", $array, array('id' => $item->id));
							
						} else {
							if ($confirm_code and $get_confirm_code != $confirm_code) {
								
								$error_bids['error_fields']['user_email_verify_code'] = sprintf(__('You entered an incorrect verification code %s', 'pn'), 'e-mail');
								
							}	
							
							$send_time = strtotime(is_isset($item, 'send_date'));
							$now_time = current_time('timestamp');
							$wait_seconds = intval($premiumbox->get_option('napsemail', 'time_check'));
							if ($wait_seconds < 0) { $wait_seconds = 180; }
							$send_time_last = $send_time + $wait_seconds;
							if (!isset($item->send_date) or $send_time_last < $now_time) {
										
								$array = array();
								$array['send_date'] = current_time('mysql');
								$wpdb->update($wpdb->prefix . "confirmexchmail", $array, array('id' => $item->id));	
										
								$notify_tags = array();			
								$notify_tags['[code]'] = $confirm_code;
								$notify_tags = apply_filters('notify_tags_napsemail', $notify_tags);		

								$user_send_data = array(
									'user_email' => $user_value,
								);	
								$user_send_data = apply_filters('user_send_data', $user_send_data, 'napsemail');
								$result_mail = apply_filters('premium_send_message', 0, 'napsemail', $notify_tags, $user_send_data);
										
								$error_bids['error_fields']['user_email_verify_code'] = __('Please enter the verification code', 'pn');						
										
							}				
						}												
	
					}
				} else {
					
					$error_bids['error_text'][] = 'Error module "confirmexchmail"';
					
				}
			}
		}
	
	}
	
	return $error_bids;
}	