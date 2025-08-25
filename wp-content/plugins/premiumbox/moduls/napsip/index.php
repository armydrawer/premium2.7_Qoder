<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Restriction for users[:en_US][ru_RU:]Ограничения для пользователей[:ru_RU]
description: [en_US:]Restriction for users by IP address, account number, login, etc. when orders are created[:en_US][ru_RU:]Ограничение для пользователей по IP адресу, номеру счета, логину и т.п. при создании заявок[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_napsip');
add_action('pn_plugin_activate', 'bd_all_moduls_active_napsip');
function bd_all_moduls_active_napsip() {
	global $wpdb;
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'not_ip'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `not_ip` longtext NOT NULL");
    }
	
}

add_action('tab_direction_tab7', 'napsip_tab_direction_tab', 30, 2);
function napsip_tab_direction_tab($data, $data_id) {

	$not_ip = pn_strip_input_array(pn_json_decode(is_isset($data, 'not_ip')));
	
	$naps_constraints = get_direction_meta($data_id, 'naps_constraints');
	if (!is_array($naps_constraints)) { $naps_constraints = array(); }
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Prohibited IP (at the beginning of a new line)', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<textarea name="not_ip" style="width: 100%; height: 100px;"><?php echo join("\n", $not_ip); ?></textarea>
			</div>
		</div>		
	</div>		
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. number of exchange orders from same IP per day', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="napsip_max_ip" style="width: 200px;" value="<?php echo intval(is_isset($naps_constraints, 'max_ip')); ?>" />
			</div>
		</div>	
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. number of exchange orders from same account Send per day', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="napsip_max_account1" style="width: 200px;" value="<?php echo intval(is_isset($naps_constraints, 'max_account1')); ?>" />
			</div>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. number of exchange orders from same account Receive per day', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="napsip_max_account2" style="width: 200px;" value="<?php echo intval(is_isset($naps_constraints, 'max_account2')); ?>" />
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. number of exchange orders from same user login per day', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="napsip_max_user" style="width: 200px;" value="<?php echo intval(is_isset($naps_constraints, 'max_user')); ?>" />
			</div>
		</div>		
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. number of exchange orders from same e-mail per day', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="napsip_max_email" style="width: 200px;" value="<?php echo intval(is_isset($naps_constraints, 'max_email')); ?>" />
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. number of active exchange orders from user', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="napsip_max_user_active" style="width: 200px;" value="<?php echo intval(is_isset($naps_constraints, 'max_user_active')); ?>" />
			</div>
		</div>		
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="premium_wrap_standart">
				<label><input type="checkbox" name="napsip_identsum" <?php checked(1, is_isset($naps_constraints, 'identsum')); ?> value="1" /> <?php _e('Prohibit creating requests with the same amount I give', 'pn'); ?></label>
			</div>
		</div>	
	</div>	
	<?php 		
} 
 
add_filter('pn_direction_addform_post', 'napsip_pn_direction_addform_post');
function napsip_pn_direction_addform_post($array) {
	
	$not_ip = explode("\n", is_param_post('not_ip'));
	$array['not_ip'] = pn_json_encode(pn_strip_input_array($not_ip));
	
	return $array;
}
 
add_action('item_direction_edit', 'item_direction_edit_napsip', 10, 2);
add_action('item_direction_add', 'item_direction_edit_napsip', 10, 2);
function item_direction_edit_napsip($data_id, $array) {
	
	$naps_constraints = array(
		'max_ip' => intval(is_param_post('napsip_max_ip')),
		'max_account1' => intval(is_param_post('napsip_max_account1')),
		'max_account2' => intval(is_param_post('napsip_max_account2')),
		'max_user' => intval(is_param_post('napsip_max_user')),
		'max_email' => intval(is_param_post('napsip_max_email')),
		'max_user_active' => intval(is_param_post('napsip_max_user_active')),
		'identsum' => intval(is_param_post('napsip_identsum')),
	);
	update_direction_meta($data_id, 'naps_constraints', $naps_constraints);
	
}	

add_filter('error_bids', 'error_bids_napsip', 600, 5);  
function error_bids_napsip($error_bids, $direction, $vd1, $vd2, $cdata) { 
	global $wpdb;

	$user_ip = pn_strip_input(is_isset($error_bids['bid'], 'user_ip'));

	$not_ip = pn_strip_input_array(pn_json_decode(is_isset($direction, 'not_ip')));
	
	if (pn_has_ip($not_ip, $user_ip)) { 
	
		$error_bids['error_text'][] = __('Error! For your exchange denied', 'pn');
		
	} else {
		
		$naps_constraints = @unserialize(is_isset($direction, 'naps_constraints'));
		if (!is_array($naps_constraints)) { $naps_constraints = array(); }		
		
		$st = get_status_sett('bid_has');
		if ($st) { 
			$where = " AND status IN($st)";
		} else {
			$where = " AND status = 'success'";
		} 
			
		$date = current_time('Y-m-d 00:00:00');
		$direction_id = $direction->id;		
		
		$error = 0;
		
		$max_ip = intval(is_isset($naps_constraints, 'max_ip'));
		if ($max_ip > 0 and 0 == $error) {
			$now_cou = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE user_ip = '$user_ip' AND edit_date >= '$date' AND direction_id = '$direction_id' $where");
			if ($now_cou >= $max_ip) {
				
				$error_bids['error_text'][] = __('Error! For your exchange denied', 'pn');
				$error = 1;
				
			}
		}
		
		$max_account1 = intval(is_isset($naps_constraints, 'max_account1'));
		if ($max_account1 > 0 and 0 == $error) {
			$n_item = is_isset($error_bids['bid'],'account_give');
			if ($n_item) {
				$now_cou = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE account_give = '$n_item' AND edit_date >= '$date' AND direction_id = '$direction_id' $where");
				if ($now_cou >= $max_account1) {
					
					$error_bids['error_text'][] = __('Error! For your exchange denied', 'pn');
					$error = 1;
					
				}
			}
		}
		
		$max_account2 = intval(is_isset($naps_constraints, 'max_account2'));
		if ($max_account2 > 0 and 0 == $error) {		
			$n_item = is_isset($error_bids['bid'], 'account_get');
			if ($n_item) {
				$now_cou = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE account_get = '$n_item' AND edit_date >= '$date' AND direction_id = '$direction_id' $where");
				if ($now_cou >= $max_account2) {
					
					$error_bids['error_text'][] = __('Error! For your exchange denied', 'pn');
					$error = 1;
					
				}	
			}
		}
		
		$max_user = intval(is_isset($naps_constraints, 'max_user'));
		if ($max_user > 0 and 0 == $error) {	
			$n_item = intval(is_isset($error_bids['bid'], 'user_id'));		
			if ($n_item) {
				$now_cou = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE user_id = '$n_item' AND edit_date >= '$date' AND direction_id = '$direction_id' $where");
				if ($now_cou >= $max_user) {
					
					$error_bids['error_text'][] = __('Error! For your exchange denied', 'pn');
					$error = 1;
					
				}		
			}
		}
		
		$max_email = intval(is_isset($naps_constraints, 'max_email'));
		if ($max_email > 0 and 0 == $error) {	
			$n_item = is_isset($error_bids['bid'], 'user_email');
			if ($n_item) {
				$now_cou = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE user_email = '$n_item' AND edit_date >= '$date' AND direction_id = '$direction_id' $where");
				if ($now_cou >= $max_email) {
					
					$error_bids['error_text'][] = __('Error! For your exchange denied', 'pn');
					$error = 1;
					
				}		
			}
		}

		$st = get_status_sett('bid_active');
		if ($st) { 
			$where = " AND status IN($st)";
		} else {
			$where = " AND status = 'success'";
		}		
		
		$max_user_active = intval(is_isset($naps_constraints, 'max_user_active'));
		if ($max_user_active > 0 and 0 == $error) {	
			$n_item = intval(is_isset($error_bids['bid'], 'user_id'));		
			if ($n_item) {
				$now_cou = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE user_id = '$n_item' AND direction_id = '$direction_id' $where");
				if ($now_cou >= $max_user_active) {
					
					$error_bids['error_text'][] = __('Error! For your exchange denied', 'pn');
					$error = 1;	
					
				}		
			}
		}
		
		$amount_give = is_sum($cdata['sum1']);
		
		$identsum = intval(is_isset($naps_constraints, 'identsum'));
		if ($identsum and 0 == $error) {	
			$now_cou = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE sum1 = '$amount_give' AND direction_id = '$direction_id' $where");
			if ($now_cou > 0) {
						
				$error_bids['error_text'][] = __('Error! It is forbidden to create a request with this amount. Please change the amount.', 'pn');
				$error = 1;	
					
			}		
		}		
		
	}
	
	return $error_bids;
} 