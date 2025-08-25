<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]User automatic registration[:en_US][ru_RU:]Автоматическая регистрация пользователя[:ru_RU]
description: [en_US:]User automatic registration during exchange[:en_US][ru_RU:]Автоматическая регистрация пользователя при обмене[:ru_RU]
version: 2.7.0
category: [en_US:]Users[:en_US][ru_RU:]Пользователи[:ru_RU]
cat: user
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('pn_plugin_activate', 'bd_all_moduls_active_autoreg');
add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_autoreg');
function bd_all_moduls_active_autoreg() {
	global $wpdb;
			
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'disable_autoreg'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `disable_autoreg` int(1) NOT NULL default '0'");
	}		
		
}
 
add_action('tab_direction_tab7', 'autoreg_tab_direction_tab7', 10, 2);
function autoreg_tab_direction_tab7($data, $data_id) {		
?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Disable automatic registration', 'pn'); ?></span></div>
				
			<div class="premium_wrap_standart">
				<?php 
				$disable_autoreg = intval(is_isset($data, 'disable_autoreg')); 
				?>														
				<select name="disable_autoreg" autocomplete="off">
					<option value="0" <?php selected($disable_autoreg, 0); ?>><?php _e('No', 'pn'); ?></option>
					<option value="1" <?php selected($disable_autoreg, 1); ?>><?php _e('Yes', 'pn'); ?></option>
				</select>
			</div>			

		</div>
	</div>
<?php
}	

add_filter('pn_direction_addform_post', 'autoreg_direction_addform_post');
function autoreg_direction_addform_post($array) {
	
	$array['disable_autoreg'] = intval(is_param_post('disable_autoreg'));
	
	return $array;
}

add_filter('error_bids', 'autoreg_error_bids', 200, 4); 
function autoreg_error_bids($error_bids, $direction, $vd1, $vd2) {
	global $wpdb, $premiumbox, $pn_regiter_site;	
	
	$user_id = intval(is_isset($error_bids['bid'], 'user_id'));
	$user_email = is_email(is_isset($error_bids['bid'], 'user_email'));
	$disable_autoreg = intval(is_isset($direction, 'disable_autoreg'));
	if (!_is('is_api')) { 
		if (1 != $disable_autoreg) { 
			if (!$user_id and $user_email) {
				if (!email_exists($user_email)) {
					$user_login = is_user(selection_email_login($user_email));
					if ($user_login) {
						$pn_regiter_site = 1;
						$pass = wp_generate_password(20, false, false);
						$user_id = wp_insert_user(array('user_login' => $user_login, 'user_email' => $user_email, 'user_pass' => $pass)) ;
						if ($user_id) {
										
							do_action('pn_user_register', $user_id);

							$error_bids['bid']['user_id'] = $user_id;
							$error_bids['bid']['user_login'] = $user_login;
										
							$fields = get_user_fields();	
							foreach ($fields as $field_key => $field_value) {
								$in = intval(is_isset($field_value, 'in'));
								if (1 != $in) {
									$value = strip_uf(is_isset($error_bids['bid'], $field_key), $field_key);
									if (strlen($value) > 0) {
										update_user_meta($user_id, $field_key, $value) or add_user_meta($user_id, $field_key, $value, true);
									}
								}	
							}
										
							$notify_tags = array();
							$notify_tags['[login]'] = $user_login;
							$notify_tags['[pass]'] = $pass;
							$notify_tags['[email]'] = $user_email;
							$notify_tags = apply_filters('notify_tags_registerform', $notify_tags, $user_id);		

							$user_send_data = array(
								'user_email' => $user_email,
							);	
							$user_send_data = apply_filters('user_send_data', $user_send_data, 'registerform', $error_bids['bid']);
							$result_mail = apply_filters('premium_send_message', 0, 'registerform', $notify_tags, $user_send_data);
									
							$secure_cookie = is_ssl();
							$creds = array();
							$creds['user_login'] = $user_login;
							$creds['user_password'] = $pass;
							$creds['remember'] = true;
							$sign_user = wp_signon($creds, $secure_cookie);
							
							if (isset($sign_user->errors['pn_error'], $sign_user->errors['pn_error'][0])) {
								$error_bids['error_text'][] = $sign_user->errors['pn_error'][0];
							}
			
						}
					}
				}
			}
		}	
	}
	
	return $error_bids;
}	