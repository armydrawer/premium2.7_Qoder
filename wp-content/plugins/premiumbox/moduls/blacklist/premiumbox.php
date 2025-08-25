<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('pn_pp_adminform', 'blacklist_pn_pp_options');
function blacklist_pn_pp_options($options) {
	global $premiumbox;	
	
	$options['payoutblcheck'] = array(
		'view' => 'select',
		'title' => __('Check user details in blacklists when requesting affiliate rewards', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('partners', 'payoutblcheck'),
		'name' => 'payoutblcheck',
		'work' => 'int',
	);	
	
	return $options;
}

add_action('pn_pp_adminform_post', 'blacklist_pn_pp_options_post');
function blacklist_pn_pp_options_post() {
	global $premiumbox;
	
	$premiumbox->update_option('partners', 'payoutblcheck', intval(is_param_post('payoutblcheck')));
	
}

add_filter('item_user_payouts_add_before', 'blacklist_item_user_payouts_add_before', 10, 2);
function blacklist_item_user_payouts_add_before($res, $arr) {
	global $wpdb, $premiumbox;	
	
	if (1 == $res['ind'] and !_is('is_adminaction')) {
		$check = intval($premiumbox->get_option('partners', 'payoutblcheck'));
		if ($check) {
			$account = pn_strip_input($arr['pay_account']);
			$blacklist = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "blacklist WHERE meta_value='$account' AND meta_key='0'");
			if ($blacklist > 0) {
				$res['ind'] = 0;
				$res['error'] = __('Error! Your account in blacklist. Contact us', 'pn');
			}	
		}
	}
	
	return $res;
}

add_filter('get_statusbids_for_admin', 'get_statusbids_for_admin_blacklist');
function get_statusbids_for_admin_blacklist($st) {
	
	if (current_user_can('administrator') or current_user_can('pn_blacklist')) {
		
		$st['blacklist'] = array(
			'name' => 'blacklist',
			'title' => __('add to blacklist', 'pn'),
			'color' => '#ffffff',
			'background' => '#000000',
		);
		$st['delblacklist'] = array(
			'name' => 'delblacklist',
			'title' => __('remove from blacklist', 'pn'),
			'color' => '#ffffff',
			'background' => '#028e19',
		);		
		
	}
	
	return $st;
}

add_action('bidstatus_admin_action', 'bidstatus_admin_action_blacklist', 10, 2);
function bidstatus_admin_action_blacklist($ids, $action) {
	global $wpdb;

	if (current_user_can('administrator') or current_user_can('pn_blacklist')) {

		if ('blacklist' == $action) {
			
			foreach ($ids as $id) {
				$id = intval($id);
				$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$id'");
				if (isset($item->id)) {
							
					$account1 = pn_strip_input($item->account_give);
					if ($account1) {
						$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$account1' AND meta_key = '0'");
						if (0 == $cc) {
							$wpdb->insert($wpdb->prefix . 'blacklist', array('meta_value' => $account1, 'meta_key' => 0, 'comment_text' => sprintf(__('Bid id %s', 'pn'), $id)));
						}
					}

					$account2 = pn_strip_input($item->account_get);
					if ($account2) {
						$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$account2' AND meta_key = '0'");
						if (0 == $cc) {
							$wpdb->insert($wpdb->prefix . 'blacklist', array('meta_value' => $account2, 'meta_key' => 0, 'comment_text' => sprintf(__('Bid id %s', 'pn'), $id)));
						}	
					}						
							
					$user_email = is_email($item->user_email);
					if ($user_email) {
						$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$user_email' AND meta_key = '1'");
						if (0 == $cc) {
							$wpdb->insert($wpdb->prefix . 'blacklist', array('meta_value' => $user_email, 'meta_key' => 1, 'comment_text' => sprintf(__('Bid id %s', 'pn'), $id)));
						}
					}						
							
					$user_phone = str_replace('+', '', pn_strip_input($item->user_phone));
					if ($user_phone) {
						$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$user_phone' AND meta_key = '2'");
						if (0 == $cc) {
							$wpdb->insert($wpdb->prefix . 'blacklist', array('meta_value' => $user_phone, 'meta_key' => 2, 'comment_text' => sprintf(__('Bid id %s', 'pn'), $id)));
						}	
					}
							
					$user_skype = pn_strip_input($item->user_skype);
					if ($user_skype) {
						$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$user_skype' AND meta_key = '3'");
						if (0 == $cc) {
							$wpdb->insert($wpdb->prefix . 'blacklist', array('meta_value' => $user_skype, 'meta_key' => 3, 'comment_text' => sprintf(__('Bid id %s', 'pn'), $id)));
						}
					}

					$user_ip = pn_strip_input($item->user_ip);
					if ($user_ip) {
						$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$user_ip' AND meta_key = '4'");
						if (0 == $cc) {
							$wpdb->insert($wpdb->prefix . 'blacklist', array('meta_value' => $user_ip, 'meta_key' => 4, 'comment_text' => sprintf(__('Bid id %s', 'pn'), $id)));
						}
					}

					$pay_ac = pn_strip_input($item->pay_ac);
					if ($pay_ac) {
						$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$pay_ac' AND meta_key = '5'");
						if (0 == $cc) {
							$wpdb->insert($wpdb->prefix . 'blacklist', array('meta_value' => $pay_ac, 'meta_key' => 5, 'comment_text' => sprintf(__('Bid id %s', 'pn'), $id)));
						}
					}					
					
					if ($user_email) {

						$now_locale = get_locale();
						$bid_locale = $item->bid_locale;

						set_locale($bid_locale);
					
						$notify_tags = array();
						$notify_tags['[bid_id]'] = $item->id;
						$notify_tags = apply_filters('notify_tags_inblacklist', $notify_tags, $item);		

						$user_send_data = array(
							'user_email' => $user_email,
						);
						$user_send_data = apply_filters('user_send_data', $user_send_data, 'inblacklist', $item);
						$result_mail = apply_filters('premium_send_message', 0, 'inblacklist', $notify_tags, $user_send_data);
						
						set_locale($now_locale);

					}	
				}
			}
		}
		
		if ('delblacklist' == $action) {
			foreach ($ids as $id) {
				$id = intval($id);
				$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$id'");
				if (isset($item->id)) {
							
					$account1 = pn_strip_input($item->account_give);
					if ($account1) {
						$wpdb->query("DELETE FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$account1' AND meta_key = '0'");
					}

					$account2 = pn_strip_input($item->account_get);
					if ($account2) {
						$wpdb->query("DELETE FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$account2' AND meta_key = '0'");	
					}						
							
					$user_email = is_email($item->user_email);
					if ($user_email) {
						$wpdb->query("DELETE FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$user_email' AND meta_key = '1'");
					}						
							
					$user_phone = str_replace('+', '', pn_strip_input($item->user_phone));
					if ($user_phone) {
						$wpdb->query("DELETE FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$user_phone' AND meta_key = '2'");	
					}
							
					$user_skype = pn_strip_input($item->user_skype);
					if ($user_skype) {
						$wpdb->query("DELETE FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$user_skype' AND meta_key = '3'");
					}

					$user_ip = pn_strip_input($item->user_ip);
					if ($user_ip) {
						$wpdb->query("DELETE FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$user_ip' AND meta_key = '4'");
					}

					$pay_ac = pn_strip_input($item->pay_ac);
					if ($pay_ac) {
						$wpdb->query("DELETE FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$pay_ac' AND meta_key = '5'");
					}					
							
				}
			}
		}	
		
	}	
}

add_filter('error_bids','blacklist_error_bids', 700, 2); 
function blacklist_error_bids($error_bids, $direction) { 
	global $wpdb, $premiumbox;

	$error = 0; 

	$checks = $premiumbox->get_option('blacklist','check');
	if (!is_array($checks)) { $checks = array(); }

	$item = is_isset($error_bids['bid'], 'account_give');
	if (in_array(0, $checks) and $item and !isset($error_bids['error_fields']['account1']) and 0 == $error) {
		$blacklist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$item' AND meta_key = '0'");
		if (isset($blacklist->id)) {
			$m = get_block_type($blacklist->black_type);
			if (0 == $m) {
				
				$error_bids['error_fields']['account1'] = __('In blacklist', 'pn');
				$error = 1;
				
			}	
		}	
	}
		
	$item = is_isset($error_bids['bid'], 'account_get');
	if (in_array(1, $checks) and $item and !isset($error_bids['error_fields']['account2']) and 0 == $error) {
		$blacklist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$item' AND meta_key = '0'");
		if (isset($blacklist->id)) {
			$m = get_block_type($blacklist->black_type);
			if (0 == $m) {
				
				$error_bids['error_fields']['account2'] = __('In blacklist', 'pn');
				$error = 1;
				
			}	
		}
	}	
		
	$item = is_isset($error_bids['bid'], 'user_phone');
	if (in_array(2, $checks) and $item and 0 == $error) {
		$blacklist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$item' AND meta_key = '2'");
		if (isset($blacklist->id)) {
			$m = get_block_type($blacklist->black_type);
			if (0 == $m) {
				
				$error_bids['error_text'][] = __('Error! Your phone in black list', 'pn');
				$error = 1;
				
			}
		}
	}

	$item = is_isset($error_bids['bid'], 'user_skype');
	if (in_array(3, $checks) and $item and 0 == $error) {
		$blacklist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$item' AND meta_key = '3'");
		if (isset($blacklist->id)) {
			$m = get_block_type($blacklist->black_type);
			if (0 == $m) {
				
				$error_bids['error_text'][] = __('Error! Your skype in black list', 'pn');
				$error = 1;
				
			}
		}
	}

	$item = is_isset($error_bids['bid'], 'user_email');
	if (in_array(4, $checks) and $item and 0 == $error) {
		$value_arr = explode('@', $item);
		$domain = '@' . trim(is_isset($value_arr, 1));
		$blacklist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$item' AND meta_key = '1' OR meta_value = '$domain' AND meta_key = '1'");
		if (isset($blacklist->id)) {
			$m = get_block_type($blacklist->black_type);
			if (0 == $m) {
				
				$error_bids['error_text'][] = __('Error! Your e-mail in black list', 'pn');
				$error = 1;
				
			}
		}
	}

	$item = is_isset($error_bids['bid'], 'user_ip');
	if (in_array(5, $checks) and $item and 0 == $error) {
		$blacklist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$item' AND meta_key = '4'");
		if (isset($blacklist->id)) {
			$m = get_block_type($blacklist->black_type);
			if (0 == $m) {
				
				$error_bids['error_text'][] = __('Error! Your IP address in black list', 'pn');
				$error = 1;
				
			}
		}
	}
	
	return $error_bids;
}

add_filter('autopayment_filter', 'blacklist_ap_filter', 10, 8); 
function blacklist_ap_filter($au_filter, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $direction) {
	global $premiumbox, $wpdb;	
	
	$error = 0;
	
	$checks = $premiumbox->get_option('blacklist','check');
	if (!is_array($checks)) { $checks = array(); }
	
	if (0 == count($au_filter['error'])) {
	
		$check_elem = is_isset($item, 'account_give');
		if (in_array(0, $checks) and $check_elem and 0 == $error) {
			$blacklist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$check_elem' AND meta_key = '0'");
			if (isset($blacklist->id)) {
				$m = get_block_type($blacklist->black_type);
				if (1 == $m) {
					$error = 1;
				}
			}	
		}
		
		$check_elem = is_isset($item, 'account_get');
		if (in_array(1, $checks) and $check_elem and 0 == $error) {
			$blacklist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$check_elem' AND meta_key = '0'");
			if (isset($blacklist->id)) {
				$m = get_block_type($blacklist->black_type);
				if (1 == $m) {
					$error = 1;
				}
			}	
		}	
		
		$check_elem = is_isset($item, 'user_phone');
		if (in_array(2, $checks) and $check_elem and 0 == $error) {
			$blacklist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$check_elem' AND meta_key = '2'");
			if (isset($blacklist->id)) {
				$m = get_block_type($blacklist->black_type);
				if (1 == $m) {
					$error = 1;
				}
			}
		}

		$check_elem = is_isset($item, 'user_skype');
		if (in_array(3, $checks) and $check_elem and 0 == $error) {
			$blacklist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$check_elem' AND meta_key = '3'");
			if (isset($blacklist->id)) {
				$m = get_block_type($blacklist->black_type);
				if (1 == $m) {
					$error = 1;
				}
			}
		}
	 
		$check_elem = is_isset($item, 'user_email');
		if (in_array(4, $checks) and $check_elem and 0 == $error) {
			$value_arr = explode('@', $check_elem);
			$domain = '@' . trim(is_isset($value_arr, 1));
			$blacklist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$check_elem' AND meta_key = '1' OR meta_value = '$domain' AND meta_key = '1'");
			if (isset($blacklist->id)) {
				$m = get_block_type($blacklist->black_type);
				if (1 == $m) {
					$error = 1;
				}
			}
		}

		$check_elem = is_isset($item, 'user_ip');
		if (in_array(5, $checks) and $check_elem and 0 == $error) {
			$blacklist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$check_elem' AND meta_key = '4'");
			if (isset($blacklist->id)) {
				$m = get_block_type($blacklist->black_type);
				if (1 == $m) {
					$error = 1;
				}
			}
		}

		$check_elem = is_isset($item, 'pay_ac');
		if (in_array(6, $checks) and $check_elem and 0 == $error) {
			$blacklist = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blacklist WHERE meta_value = '$check_elem' AND meta_key = '5'");
			if (isset($blacklist->id)) {
				$m = get_block_type($blacklist->black_type);
				if (1 == $m) {
					$error = 1;
				}
			}
		}		

	}

	if ($error) {
		$au_filter['error'][] = 'Stopped by module blacklist';	
	}
	
	return $au_filter;
}