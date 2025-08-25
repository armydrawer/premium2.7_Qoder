<?php
if (!defined('ABSPATH')) { exit(); }
 
function paymerchants_setting_list($data, $db_data, $place) {
		
	$options = array();	
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Settings', 'pn'),
		'submit' => __('Save', 'pn'),
	);
	$options['hidden_block'] = array(
		'view' => 'hidden_input',
		'name' => 'item_id',
		'default' => $db_data->id,
	);
	$options['instruction'] = array(
		'view' => 'editor',
		'title' => __('Payout instruction for user', 'pn'),
		'default' => is_isset($data, 'text'),
		'name' => 'text',
		'tags' => apply_filters('direction_instruction_tags', array(), 'paymerchant_instruction'),
		'rows' => '12',
		'formatting_tags' => 1,
		'other_tags' => 1,
		'ml' => 1,
		'work' => 'text',
	);
	$tags = array(
		'paysum' => array(
			'title' => __('Payment amount', 'pn'),
			'start' => '[paysum]',
		),
		'currency_give' => array(
			'title' => __('Currency name Giving', 'pn'),
			'start' => '[currency_give]',
		),
		'currency_get' => array(
			'title' => __('Currency name Receiving', 'pn'),
			'start' => '[currency_get]',
		),
		'fio' => array(
			'title' => __('User name', 'pn'),
			'start' => '[fio]',
		),
		'ip' => array(
			'title' => __('User IP', 'pn'),
			'start' => '[ip]',
		),
	);
	$tags = apply_filters('direction_instruction_tags', $tags, 'paymerchant_note');
	$tags = apply_filters('paymerchant_admin_tags', $tags, $db_data->ext_plugin);
	$options['note'] = array(
		'view' => 'editor',
		'title' => __('Note for payment', 'pn'),
		'default' => is_isset($data, 'note'),
		'tags' => $tags,
		'rows' => '12',
		'name' => 'note',
		'work' => 'text',
		'ml' => 1,
	);
	$options['realpay'] = array(
		'view' => 'select',
		'title' => __('Automatic payout when order has status "Paid order"', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => is_isset($data, 'realpay'),
		'name' => 'realpay',
		'work' => 'int',
	);
	$options['verify'] = array(
		'view' => 'select',
		'title' => __('Automatic payout when order has status "Order is on checking"', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => is_isset($data, 'verify'),
		'name' => 'verify',
		'work' => 'int',
	);
	$options['button'] = array(
		'view' => 'select',
		'title' => __('Button used to make payouts according to order manually', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => is_isset($data, 'button'),
		'name' => 'button',
		'work' => 'int',
	);
	$options['line0'] = array(
		'view' => 'line',
	);		
	$options['max'] = array(
		'view' => 'input',
		'title' => __('Daily automatic payout limit', 'pn'),
		'default' => is_isset($data, 'max'),
		'name' => 'max',
		'work' => 'sum',
	);
	$options['max_month'] = array(
		'view' => 'input',
		'title' => __('Monthly automatic payout limit', 'pn'),
		'default' => is_isset($data, 'max_month'),
		'name' => 'max_month',
		'work' => 'sum',
	);
	$options['min_sum'] = array(
		'view' => 'input',
		'title' => __('Min. amount of automatic payouts due to order', 'pn'),
		'default' => is_isset($data, 'min_sum'),
		'name' => 'min_sum',
		'work' => 'sum',
	);		
	$options['max_sum'] = array(
		'view' => 'input',
		'title' => __('Max. amount of automatic payouts due to order', 'pn'),
		'default' => is_isset($data, 'max_sum'),
		'name' => 'max_sum',
		'work' => 'sum',
	);
	$where_sum = array(
		'0' => __('Amount To receive (add.fees and PS fees)', 'pn'), 
		'1' => __('Amount To receive (add. fees)', 'pn'), 
		'2' => __('Amount for reserve', 'pn'), 
		'3' => __('Amount (discount included)', 'pn'), 
	);
	$options['where_sum'] = array(
		'view' => 'select',
		'title' => __('Amount transfer for payout', 'pn'),
		'options' => $where_sum,
		'default' => is_isset($data, 'where_sum'),
		'name' => 'where_sum',
		'work' => 'int',
	);	
	$options['checkpay'] = array(
		'view' => 'select',
		'title' => __('Check payment history by API', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => is_isset($data, 'checkpay'),
		'name' => 'checkpay',
		'work' => 'int',
	);
	$options['enableip'] = array(
		'view' => 'textarea',
		'title' => __('Authorized IP (at the beginning of a new line)', 'pn'),
		'default' => is_isset($data, 'enableip'),
		'name' => 'enableip',
		'rows' => '8',
		'work' => 'text',
	);
	$options['cronhash'] = array(
		'view' => 'inputbig',
		'title' => __('Cron URL hash', 'pn'),
		'default' => is_isset($data, 'cronhash'),
		'name' => 'cronhash',
		'work' => 'symbols',
	);	
	$options['resulturl'] = array(
		'view' => 'inputbig',
		'title' => __('Status/Result URL hash', 'pn'),
		'default' => is_isset($data, 'resulturl'),
		'name' => 'resulturl',
		'work' => 'symbols',
	);
	$options['show_error'] = array(
		'view' => 'select',
		'title' => __('Debug mode', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => is_isset($data, 'show_error'),
		'name' => 'show_error',
		'work' => 'int',
	);		
	$options['line1'] = array(
		'view' => 'line',
	);
	$options['timeout'] = array(
		'view' => 'input',
		'title' => __('Automatic payout delay (in hours)', 'pn'),
		'default' => is_isset($data, 'timeout'),
		'name' => 'timeout',
		'work' => 'sum',
	);
	$options['timeout_user'] = array(
		'view' => 'select',
		'title' => __('Whom the delay is for', 'pn'),
		'options' => array('0' => __('everyone', 'pn'), '1' => __('newcomers', 'pn'), '2' => __('not registered users', 'pn'), '3' => __('not verified users', 'pn')),
		'default' => is_isset($data,'timeout_user'),
		'name' => 'timeout_user',
		'work' => 'int',
	);	
	$options['line_timeout'] = array(
		'view' => 'line',
	);
		
	$statused = list_bid_status();
	if (!is_array($statused)) { $statused = array(); }

	$error_status = trim(is_isset($data, 'error_status'));
	if (!$error_status) { $error_status = 'payouterror'; }
		
	$options['error_status'] = array(
		'view' => 'select',
		'title' => __('API status error', 'pn'),
		'options' => $statused,
		'default' => $error_status,
		'name' => 'error_status',
		'work' => 'symbols',
	);

	$options['priority'] = array(
		'view' => 'input',
		'title' => __('Priority', 'pn'),
		'default' => intval(is_isset($data, 'priority')),
		'name' => 'priority',
		'work' => 'int',
	);	
	$options['dirs'] = array(
		'view' => 'user_func',
		'func_data' => $db_data->ext_key,
		'func' => '_paymerchants_dirs_option',
		'name' => 'dirs',
	);
	$options['curl_timeout'] = array(
		'view' => 'input',
		'title' => __('Timeout (sec.) work script', 'pn'),
		'default' => is_isset($data, 'curl_timeout'),
		'name' => 'curl_timeout',
		'work' => 'int',
	);
	$options['curl_timeout_help'] = array(
		'view' => 'help',
		'title' => __('More info', 'pn'),
		'default' => __('Timeout is the period when the website awaits a response from a third-party service. If no response is received in the preset period, the website will continue running without response. If the duration is not specified or is 0, the standard 20-second timeout is applied. There is no universal value for the timeout, since it depends on the operation speed of a specific service.', 'pn'),
	);	
		
	$options = apply_filters('_paymerchants_options', $options, $db_data->ext_plugin, $data, $db_data->ext_key, $place);
	
	return $options;
}

function _paymerchants_dirs_option($ext_key) {
	global $wpdb;
	
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' ORDER BY site_order1 ASC");
?>
	<div class="premium_standart_line">
		<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Exchange directions', 'pn'); ?></div></div>
		<div class="premium_stline_right"><div class="premium_stline_right_ins">
			<div class="premium_wrap_standart">
				<?php 
				$scroll_lists = array();
				if (is_array($items)) {
					foreach ($items as $item) {
						$m_arr = @unserialize(is_isset($item, 'm_out')); 
						$m_arr = (array)$m_arr;
						
						$checked = 0;
						if (in_array($ext_key, $m_arr)) {
							$checked = 1;
						}
						$scroll_lists[] = array(
							'title' => '[' . $item->id . '] ' . pn_strip_input($item->tech_name) . pn_item_status($item, 'direction_status', array('0' => __('inactive direction', 'pn'), '1' => __('active direction', 'pn'), '2' => __('hold direction', 'pn'))),
							'checked' => $checked,
							'value' => $item->id,
						);
					}	
				}	
				echo get_check_list($scroll_lists, 'dirs[]', '', '300', 1);				
				?>
				<div class="premium_clear"></div>
			</div>
		</div></div>
			<div class="premium_clear"></div>
	</div>
<?php
} 

add_filter('_paymerchants_ext_options_array', 'def_paymerchants_ext_options_array', 10, 2);
function def_paymerchants_ext_options_array($data, $db_data) {
	global $wpdb;	
	
	$dirs = is_param_post('dirs'); 
	if (!is_array($dirs)) { $dirs = array(); }
	$ext_key = $db_data->ext_key;
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' ORDER BY site_order1 ASC");
	foreach ($items as $item) {
		$item_id = $item->id;
		$up = 0;
		$m_arr = @unserialize(is_isset($item, 'm_out')); 
		$m_arr = (array)$m_arr;
		
		$n_arr = array();
		
		if (in_array($ext_key, $m_arr)) {
			if (!in_array($item_id, $dirs)) {
				foreach ($m_arr as $m_ar) {
					if ($m_ar != $ext_key) {
						$n_arr[] = $m_ar;
					}
				}
				$up = 1;
			}
		} else {
			if (in_array($item_id, $dirs)) {
				$n_arr = $m_arr;
				$n_arr[] = $ext_key;
				$up = 1;
			}	
		}
		
		if ($up) {
			$array = array();
			$array['m_out'] = @serialize($n_arr);
			$wpdb->update($wpdb->prefix . 'directions', $array, array('id' => $item_id));
		}
	}
	
	return $data;
}

add_filter('curl_ap', 'timeout_paymerch_curl', 10, 3);
function timeout_paymerch_curl($ch, $m_name, $m_id) {
	
	$m_data = get_paymerch_data($m_id);
	$timeout = intval(is_isset($m_data, 'curl_timeout'));
	if ($timeout > 0) {
		
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		
	}
	
	return $ch;
}
 
function get_paymerch_data($m_id) {
	
	$data = array();
	$extandeds = get_extandeds();
	foreach ($extandeds as $ext) {
		if ('paymerchants' == $ext->ext_type and $ext->ext_key == $m_id) {
			$data = pn_json_decode($ext->ext_options);
			if (!is_array($data)) { $data = array(); }
			$data['ext_status'] = $ext->ext_status;
		}
	}
	
	return $data;
}
 
add_action('instruction_paymerchant', 'def_instruction_paymerchant', 1, 5);
function def_instruction_paymerchant($instruction, $bids_data, $direction, $vd1, $vd2) {
	global $premiumbox;
	
	if (isset($bids_data->m_out)) {
		$m_id = $bids_data->m_out;
		if ($m_id) {
			$data = get_paymerch_data($m_id); 
			$text = trim(ctv_ml(is_isset($data, 'text')));
			if (strlen($text) > 0) {
				return $text;
			} else {
				$show = intval($premiumbox->get_option('exchange', 'mp_ins'));
				if (0 == $show) {
					return $text;
				} else {
					return $instruction;
				}
			}
		}
	}
	
	return $instruction;
}

function get_text_paymerch($m_id, $item, $pay_sum = 0) {
	
	$text = '';
	if ($m_id and isset($item->id)) {
		
		$data = get_paymerch_data($m_id);
		$text = trim(ctv_ml(is_isset($data, 'note')));
		
		$fio_arr = array();
		if ($item->last_name) {
			$fio_arr[] = $item->last_name;
		}
		if ($item->first_name) {
			$fio_arr[] = $item->first_name;
		}
		if ($item->second_name) {
			$fio_arr[] = $item->second_name;
		}		
		$fio = pn_strip_input(implode(' ', $fio_arr));
		
		$text = apply_filters('get_text_paymerch', $text, $m_id, $item);
		$text = str_replace(array('[id]', '[exchange_id]'), $item->id, $text);
		$text = str_replace('[create_date]', $item->create_date, $text);
		$text = str_replace('[edit_date]', $item->edit_date, $text);
		$text = str_replace('[course_give]', pn_strip_input($item->course_give), $text);
		$text = str_replace('[course_get]', pn_strip_input($item->course_get), $text);
		$text = str_replace('[psys_give]', pn_strip_input(ctv_ml($item->psys_give)), $text);
		$text = str_replace('[psys_get]', pn_strip_input(ctv_ml($item->psys_get)), $text);
		$text = str_replace('[currency_code_give]', pn_strip_input($item->currency_code_give), $text);
		$text = str_replace('[currency_code_get]', pn_strip_input($item->currency_code_get), $text);	
		$text = str_replace('[first_name]', pn_strip_input($item->first_name), $text);
		$text = str_replace('[last_name]', pn_strip_input($item->last_name), $text);
		$text = str_replace('[second_name]', pn_strip_input($item->second_name), $text);
		$text = str_replace(array('[user_phone]', '[phone]'), pn_strip_input($item->user_phone), $text);
		$text = str_replace(array('[user_skype]', '[skype]'), pn_strip_input($item->user_skype), $text);
		$text = str_replace(array('[user_email]', '[email]'), pn_strip_input($item->user_email), $text);
		$text = str_replace('[user_telegram]', pn_strip_input($item->user_telegram), $text);
		$text = str_replace(array('[user_passport]', '[passport]'), pn_strip_input($item->user_passport), $text);
		$text = str_replace('[to_account]', get_shtd_to_account($item), $text); 
		$text = str_replace('[dest_tag]', pn_strip_input($item->dest_tag), $text);
		$text = str_replace('[bidurl]', get_bids_url($item->hashed), $text);		
		$text = str_replace('[paysum]', $pay_sum, $text);
		$text = str_replace(array('[sum1dc]', '[sum_dc]'), is_sum($item->sum1dc), $text); 
		$text = str_replace('[sum1]', is_sum($item->sum1), $text);
		$text = str_replace('[sum1c]', is_sum($item->sum1c), $text);
		$text = str_replace(array('[valut1]', '[currency_give]'), pn_strip_input(ctv_ml($item->psys_give)) .' '. pn_strip_input($item->currency_code_give), $text);	
		$text = str_replace('[sum2]', is_sum($item->sum2), $text);
		$text = str_replace('[sum2c]', is_sum($item->sum2c), $text);
		$text = str_replace('[sum2dc]', is_sum($item->sum2dc), $text);
		$text = str_replace(array('[valut2]', '[currency_get]'), pn_strip_input(ctv_ml($item->psys_get)) .' '. pn_strip_input($item->currency_code_get), $text);
		$text = str_replace('[account_give]', pn_strip_input($item->account_give), $text);
		$text = str_replace('[account_get]', pn_strip_input($item->account_get), $text);
		$text = str_replace('[fio]', $fio, $text);		
		$text = str_replace('[ip]', pn_strip_input($item->user_ip), $text);	
		
		$bid_trans_in = pn_strip_input($item->txid_in);
		if (!$bid_trans_in) { $bid_trans_in = pn_strip_input($item->trans_in); }
			
		$bid_trans_out = pn_strip_input($item->txid_out);
		if (!$bid_trans_out) { $bid_trans_out = pn_strip_input($item->trans_out); }	
		
		$text = str_replace('[bid_trans_in]', $bid_trans_in, $text);
		$text = str_replace('[bid_trans_out]', $bid_trans_out, $text);
		
		$text = str_replace('[trans_in]', pn_strip_input($item->trans_in), $text); 
		$text = str_replace('[trans_out]', pn_strip_input($item->trans_out), $text);
		$text = str_replace('[txid_in]', pn_strip_input($item->txid_in), $text);
		$text = str_replace('[txid_out]', pn_strip_input($item->txid_out), $text);
		$text = str_replace('[frozen_date]', get_pn_time($item->touap_date) ,$text);	
		if (strstr($text,'[bid_delete_time]')) {
			$bid_delete_time = apply_filters('bid_delete_time', '---', $item);
			$text = str_replace('[bid_delete_time]', $bid_delete_time, $text);
		}
		$unmetas = @unserialize($item->unmetas);
		if (is_array($unmetas)) {
			foreach ($unmetas as $un_key => $un_value) {
				$text = str_replace('[uniq id="' . $un_key . '"]', pn_strip_input(ctv_ml($un_value)), $text);
			}
		}
		
	}
	
	return esc_attr(trim($text));
}

add_filter('paymerchant_admin_tags', 'remove_paymerchant_admin_tags');
function remove_paymerchant_admin_tags($tags) {
	
	$tags = pn_array_unset($tags, array(
		'coupon_code',
		'coupon_info',
		'recalc_course',
		'recalc_amount',
		'spbbonus',
		'spbbonus_sum',
		'verification_status',
		'verify_amount',
		'verification_link',
		'create_acc_give',
		'create_acc_get',
		'bid_recalc',
		'frozen_date',
		'num_schet',
		'confirm_count',
		'confirm_count_time'
	));
	
	return $tags;
}
 
add_filter('list_admin_notify', 'list_admin_notify_paymerchant');
function list_admin_notify_paymerchant($places_admin) {
	
	$places_admin['paymerchant_error'] = __('Automatic payout error', 'pn');
	
	return $places_admin;
}

add_filter('list_notify_tags_paymerchant_error', 'def_mailtemp_tags_paymerchant_error');
function def_mailtemp_tags_paymerchant_error($tags) {

	$tags['bid_id'] = array(
		'title' => __('Order ID', 'pn'),
		'start' => '[bid_id]',
	);
	$tags['error_txt'] = array(
		'title' => __('Error', 'pn'),
		'start' => '[error_txt]',
	);

	return $tags;
} 

function send_paymerchant_error($bid_id, $error_txt) {

	$notify_tags = array();
	$notify_tags['[bid_id]'] = $bid_id;
	$notify_tags['[error_txt]'] = $error_txt;
	$notify_tags = apply_filters('notify_tags_paymerchant_error', $notify_tags);		

	$user_send_data = array(
		'admin_email' => 1,
	);
	$result_mail = apply_filters('premium_send_message', 0, 'paymerchant_error', $notify_tags, $user_send_data); 	

}		

add_action('paymerchant_secure','def_paymerchant_secure',1, 5);
function def_paymerchant_secure($m_name, $req, $m_id, $m_defin, $m_data) {
	
	if ($m_id) {	
		$yes_ip = trim(is_isset($m_data, 'enableip'));
		$user_ip = pn_real_ip();
		if ($yes_ip and !pn_has_ip($yes_ip)) { 
			do_action('paymerchant_logs', $m_name, $m_id, sprintf(__('IP adress (%s) is blocked', 'pn'), $user_ip));
			die(sprintf(__('IP adress (%s) is blocked', 'pn'), $user_ip));
			exit;
		}
	}
	
} 		

function get_pscript($m_id) {
	
	return get_ext_plugin($m_id, 'paymerchants');
}

add_filter('change_bid_status','paymerch_change_bidstatus', 2500);     
function paymerch_change_bidstatus($data) { 
	global $wpdb;	

	$place = $data['place'];
	$set_status = $data['set_status'];
	$bid = $data['bid'];
	$who = $data['who'];
	$old_status = $data['old_status'];
	$direction = $data['direction'];

	$stop_action = intval(is_isset($data, 'stop'));
	if (!$stop_action) {
		if ('admin_panel' != $place) {
			if ('realpay' == $set_status or 'verify' == $set_status) {
				$bid = set_paymerchant($bid, $direction);
				$stop = 0;
				if (isset($bid->stop)) {
					$stop = $bid->stop;
					unset($bid->stop);
				}
				if (!$stop) {
					$m_id = $bid->m_out;
					if ($m_id) {
						$direction_data = get_direction_meta(is_isset($direction, 'id'), 'paymerch_data');
						$go_autopay = intval(is_isset($direction_data, 'm_out_' . $set_status));
						$paymerch_data = get_paymerch_data($m_id);
						$paymerch_autopay = intval(is_isset($paymerch_data, $set_status));
						if ($paymerch_autopay and 0 == $go_autopay or 2 == $go_autopay) { 
							do_action('paymerchant_action_bid', $m_id, $bid, 'site', $direction_data, $place, $direction, $paymerch_data);
						}
					}
				}
			}
		}
	}
	
	return $data;
}

add_filter('onebid_actions', 'onebid_actions_paymerch', 99, 3);
function onebid_actions_paymerch($actions, $item, $v) {
	
	$status = $item->status;
	
	$st = get_status_sett('apbutton', 1);
	if (in_array($status, $st)) {
		if (current_user_can('administrator') or current_user_can('pn_bids_payouts')) {
			$item = set_paymerchant($item, '');
			$m_id = $item->m_out;
			if ($m_id) {
				if (is_paymerch_button($m_id)) {
					$actions['pay_merch'] = array(
						'type' => 'link',
						'title' => __('Transfer', 'pn'),
						'label' => __('Transfer', 'pn'),
						'link' => pn_link('paymerchant_bid_action') . '&id=' . $item->id,
						'link_target' => '_blank',
						'link_class' => 'pay_merch',
					);	
					$test_mode = apply_filters('autopay_test', 0);
					if (1 == $test_mode) {
						$actions['pay_merch_test'] = array(
							'type' => 'link',
							'title' => __('Transfer', 'pn') . '(test)',
							'label' => __('Transfer', 'pn') . '(test)',
							'link' => pn_link('paymerchant_bid_action') . '&id=' . $item->id . '&test=1',
							'link_target' => '_blank',
							'link_class' => 'pay_merch',
						);							
					}
				}
			}
		}
	}
	
	return $actions;
}

add_action('premium_action_paymerchant_bid_action', 'def_paymerchant_bid_action');
function def_paymerchant_bid_action() {
	global $wpdb;

	if (current_user_can('administrator') or current_user_can('pn_bids_payouts')) {
		admin_pass_protected(__('Enter security code', 'pn'), __('Enter', 'pn'), 'pay');	
		
		$bid_id = intval(is_param_get('id'));
		$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$bid_id'");
		if (isset($item->id)) {
			$status = $item->status;
			
			$st = get_status_sett('apbutton', 1);
			if (in_array($status, $st)) {
				$direction_id = intval(is_isset($item, 'direction_id'));
				$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' AND id = '$direction_id'");
				$m_id = $item->m_out;
				if ($m_id) {
					if (is_paymerch_button($m_id)) {

						$direction_data = get_direction_meta($direction_id, 'paymerch_data');
						$paymerch_data = get_paymerch_data($m_id);
						
						do_action('paymerchant_action_bid', $m_id, $item, 'admin', $direction_data, 'admin_panel', $direction, $paymerch_data);

					} else {
						pn_display_mess(__('Error! Automatic payout is disabled', 'pn'));
					}
				} else {
					pn_display_mess(__('Error! Automatic payout is disabled', 'pn'));
				}
			} else {
				pn_display_mess(__('Error! Incorrect status of the order', 'pn'));
			}
		} else {
			pn_display_mess(__('Error! Order does not exist', 'pn'));
		}
	} else {
		pn_display_mess(__('Error! Insufficient privileges', 'pn'));
	}
}

add_filter('autopayment_filter', 'def_autopayment_filter', 0, 6); 
function def_autopayment_filter($au_filter, $m_id, $item, $place, $direction_data, $paymerch_data) {	
	
	$c_data = is_substitution($item);
	if (count($c_data) > 0) {
		$au_filter['error'][] = __('Data from the order were compromised', 'pn') . ' ' . join(',', $c_data);
	}
	
	$ap_status = get_bids_meta($item->id, 'ap_status');
	$autopay_status = intval(is_isset($ap_status, 'status'));
	if (1 == $autopay_status) {
		$au_filter['error'][] = __('Automatic payout has already been made', 'pn');		
	}	
	
	$out_sum = is_sum(is_paymerch_sum($item, $paymerch_data), 12);
	if ($out_sum <= 0) {
		$au_filter['error'][] = __('Payout amount 0 or less', 'pn');
	}
	
	return $au_filter;
}

add_filter('autopayment_filter', 'avsumbig_autopayment_filter', 30, 8);
function avsumbig_autopayment_filter($au_filter, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $direction) {
	global $premiumbox;	

	$avsumbig = intval($premiumbox->get_option('exchange', 'avsumbig'));
	$exceed_pay = intval(is_isset($item, 'exceed_pay'));
	if (1 == $exceed_pay and 0 == $avsumbig) { 
		$au_filter['error'][] = __('According to the setting, we slow down applications with overpayment', 'pn');  
	}	

	return $au_filter;
}

function is_paymerch_sum($bids_data, $data) {
	
	$where_sum = intval(is_isset($data, 'where_sum'));
	$sum = '0';
	if (0 == $where_sum) {
		$sum = $bids_data->sum2c;
	} elseif (1 == $where_sum) {
		$sum = $bids_data->sum2dc;
	} elseif (2 == $where_sum) {
		$sum = $bids_data->sum2r;
	} elseif (3 == $where_sum) {
		$sum = $bids_data->sum2;
	}
	
	return $sum;
}

function is_paymerch_checkpay($m_id) {
	
	$data = get_paymerch_data($m_id);  
	
	return intval(is_isset($data, 'checkpay'));
}

function is_paymerch_button($m_id) {
	
	$data = get_paymerch_data($m_id);  
	
	return intval(is_isset($data, 'button'));
}

function set_paymerchant($bids_data, $direction) {
	global $wpdb;

	$direction_id = intval(is_isset($bids_data, 'direction_id'));
	if (!isset($direction->id)) {
		$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' AND id = '$direction_id'");
	}
		
	$dir_data = get_direction_meta(is_isset($direction, 'id'), 'paymerch_data');
	$m_outs = @unserialize(is_isset($direction, 'm_out')); if (!is_array($m_outs)) { $m_outs = array(); }
	$m_outs = apply_filters('issue_paymerch_list', $m_outs, $direction, $bids_data); 
		
	$merch = $bids_data->m_out;
	$now_merch = '';
		
	$m_arrs = array();
	foreach ($m_outs as $mer) {
		$data = get_paymerch_data($mer);
		$ext_status = intval(is_isset($data, 'ext_status'));
		if (1 == $ext_status) {
			$priority = intval(is_isset($data, 'priority'));
			if ($priority > 100000) { $priority = 100000; }
			if ($mer == $merch) { $priority = 100001; }
			$m_arrs[$mer] = array(
				'priority' => $priority,
				'data' => $data,
			);
		}	
	}	
		
	$m_arrs = pn_array_sort($m_arrs, 'priority', 'desc', 'num');
	
	$st = get_status_sett('paymerchlim', 1);
	
	$stop = 0;
		
	foreach ($m_arrs as $mer => $m_arr) {
		$data = is_isset($m_arr, 'data');
		$go = 1;
			
		$sum = is_sum(is_paymerch_sum($bids_data, $data));
					
		$min_sum = is_sum(is_isset($dir_data, 'm_out_min_sum')); 
		if ($min_sum <= 0) {
			$min_sum = is_sum(is_isset($data, 'min_sum'));
		} 	
		if ($min_sum > 0 and $sum < $min_sum) {
			$go = 0;
		}
					
		$max_sum = is_sum(is_isset($dir_data, 'm_out_max_sum')); 
		if ($max_sum <= 0) {
			$max_sum = is_sum(is_isset($data, 'max_sum'));
		}
		if ($max_sum > 0 and $go) {
			$go = 0;
			if ($max_sum >= $sum) {
				$go = 1;
			} 
		}
					
		$max_day = is_sum(is_isset($dir_data, 'm_out_max'));
		if ($max_day <= 0) {
			$max_day = is_sum(is_isset($data, 'max'));
		}	

		if ($max_day > 0 and $go) {
			$go = 0;
			$date = current_time('Y-m-d 00:00:00');
			$sum_in = get_sum_for_autopay($mer, $date, $st, $bids_data->id);
			$sum_day = $sum_in + $sum;
			if ($max_day >= $sum_day) {
				$go = 1;
			} 
		}

		$max_month = is_sum(is_isset($dir_data, 'm_out_max_month'));
		if ($max_month <= 0) {
			$max_month = is_sum(is_isset($data, 'max_month'));
		}	
		if ($max_month > 0 and $go) {
			$go = 0;
			$date = current_time('Y-m-01 00:00:00');
			$sum_in = get_sum_for_autopay($mer, $date, $st, $bids_data->id);
			$sum_month = $sum_in + $sum;
			if ($max_month >= $sum_month) {
				$go = 1;
			} 
		}	
		
		$ind = array(
			'stop' => 0,
			'go' => $go,
			'bids_data' => $bids_data,
		);
		$ind = apply_filters('init_paymerchant', $ind, $mer, $direction, $bids_data, $data, $dir_data);
		if ($ind['go']) {
			
			$now_merch = $mer;
			$bids_data = $ind['bids_data'];
			$stop = $ind['stop'];
			
			break;
		}	
		
	}		
	
	if ($merch != $now_merch) {
		$wpdb->update($wpdb->prefix . 'exchange_bids', array('m_out' => $now_merch), array('id' => $bids_data->id));
		$bids_data = pn_object_replace($bids_data, array('m_out' => $now_merch)); 
	}		
	
	$bids_data = pn_object_replace($bids_data, array('stop' => $stop)); 
	
	return $bids_data;
}

add_filter('_icon_indicators', 'scrpayerror_icon_indicators');
function scrpayerror_icon_indicators($lists) {
	global $premiumbox;
	
	$lists['scrpayerror'] = array(
		'title' => __('Orders with payout error (payment system API)', 'pn'),
		'img' => $premiumbox->plugin_url . 'images/payouterror.png',
		'url' => admin_url('admin.php?page=pn_bids&idspage=1&bidstatus[]=scrpayerror')
	);
	
	return $lists;
}

add_filter('_icon_indicator_scrpayerror', 'def_icon_indicator_scrpayerror');
function def_icon_indicator_scrpayerror($count) {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_bids')) {
		$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'scrpayerror'");
	}	
	
	return $count;
}

if (!class_exists('Ext_AutoPayut_Premiumbox')) {
	class Ext_AutoPayut_Premiumbox extends Ext_Premium { 

		function __construct($file, $title, $cron = 0)
		{
			
			global $premiumbox;
			parent::__construct($file, $title, 'paymerchants', $premiumbox); 

			if ($cron) {
				$ids = $this->get_ids('paymerchants', $this->name);
				foreach ($ids as $id) {
					add_action('premium_merchant_ap_' . $id . '_cron' . chash_url($id, 'ap'), array($this, 'paymerchant_cron'));
				}
			}
			
			add_action('_paymerchants_options', array($this, 'get_options'), 10, 5);
			add_filter('paymerchants_security_' . $this->name, array($this, 'security_errors'), 10, 3);
			add_action('ext_paymerchants_delete', array($this, 'del_directions'), 10, 2);
			
			add_filter('reserve_place_list',array($this, 'reserve_place_list'));
			add_filter('get_formula_code', array($this, 'formula_code'), 700, 4);
			add_filter('calc_currency_reserve', array($this, 'calc_currency_reserve'), 10, 4); 
			add_action('paymerchant_action_bid', array($this, 'paymerchant_action_bid'), 99, 7);	
			add_filter('autopayment_filter', array($this, 'check_history'), 200, 10); 
			
		}	

		function replace_constant($m_defin, $name) {
			global $premiumbox;
			
			$file_some = trim(is_isset($m_defin, $name));
			$file_arr = explode('/', $file_some);
			$file = end($file_arr);
			if ($file) {
				return $premiumbox->plugin_dir . '/paymerchants/' . $this->name . '/dostup/' . $file;
			}
			
			return '';
		}

		function paymerchant_cron() {
			
			$m_id = key_for_url('_cron', 'ap_');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_paymerch_data($m_id);
			
			$this->cron($m_id, $m_defin, $m_data);
			
			_e('Done', 'pn');
			
		}
		
		function cron($m_id, $m_defin, $m_data) {
			
		}		

		function calc_currency_reserve($reserv_calc, $reserv_place, $id, $item) {
			
			$ids = $this->get_ids('paymerchants', $this->name);
			foreach ($ids as $m_id) {
				if (strstr($reserv_calc, '[' . $m_id) and !strstr($reserv_calc, '[excursum_auto')) {
					$reserv_calc .= ' - [excursum_auto]';
				}
			}
			
			return $reserv_calc; 
		}

		function formula_code($n, $code, $id, $update) {
			global $pn_formula_codes;
		
			if (!is_array($pn_formula_codes)) {
				$pn_formula_codes = array();
			}

            $code = str_replace(array('[', ']'), '', $code);

            if (!str_starts_with($code, $this->name)) {
                return $n;
            }

            $ids = $this->get_ids('paymerchants', $this->name);
            foreach ($ids as $m_id) {
                if (!preg_match("/^" . $m_id . '_' . "[a-zA-Z0-9_]{0,300}$/", $code)) continue; // note: maybe replace to str_starts_with(..., ...)
                if ('success' != $this->settingtext('success', $m_id)) continue;
                if (!isset($this->get_res($m_id)[$code])) continue;

                if (isset($pn_formula_codes[$code])) {
                    return $pn_formula_codes[$code];
                } else {
                    $m_defin = $this->get_file_data($m_id);
                    $n = $this->update_reserve($code, $m_id, $m_defin);
                    $n = is_sum($n);
                    $pn_formula_codes[$code] = $n;
                }
            }
			
			return $n;
		}		
		
		function update_reserve($code, $m_id, $m_defin) {
			
			return 0;
		}
		
		function get_options($options, $name, $data, $id, $place) {
			
			if ($name == $this->name) {
				$options = $this->options($options, $data, $id, $place);

				$m_defin = $this->get_file_data($id);
				$purses = $this->get_res($id);
				$text = '';
				foreach ($purses as $k => $v) {
					$text .= '<div><input type="text" name="" class="clpb_item" data-clipboard-text="[' . $k . ']" value="[' . $k . ']" /> ' . $v . '</div>';
				}
				if ($text) {
					$options['codes_for_reserve'] = array(
						'view' => 'help',
						'title' => __('Currency reserve shortcode', 'pn'),
						'default' => $text,
					);
				}
			}
			
			return $options;
		}
		
		function options($options, $data, $id, $place) {
			
			return $options;
		}

		function reserve_place_list($list) {
			
			$ids = $this->get_ids('paymerchants', $this->name);
			foreach ($ids as $id) {
				$purses = $this->get_res($id);
				foreach ($purses as $k => $v) {
					$list[$k] = $v;
				}
			}
			
			return $list;
		}

		public function get_res($m_id) {
			
			$m_defin = $this->get_file_data($m_id);
			$res = $this->get_reserve_lists($m_id, $m_defin);
			$list = array();
			foreach ($res as $res_key => $res_value) {
				if (strlen($res_value) > 0) {
					$list[$res_key] = $this->title . '[' . $m_id . '] - ' . $res_value;
				}
			}
			
			return $list;
		}

		public function get_reserve_lists($m_id, $m_defin) {
			
			return array();
		}		
		
		public function paymerchant_action_bid($m_id, $item, $place, $direction_data, $modul_place, $direction, $paymerch_data) {
			
			$script = get_pscript($m_id);
			if ($script and $script == $this->name) {
				$test = 0;
				$test_mode = apply_filters('autopay_test', 0);
				$test_mode = intval($test_mode);
				if (1 == $test_mode and 'admin_panel' == $modul_place) {
					$test = intval(is_param_get('test'));
				}	
				$item_id = intval(is_isset($item, 'id'));
				if ($item_id) {
					$unmetas = @unserialize($item->unmetas);
					$au_filter = array(
						'error' => array(),
						'pay_error' => 0,
						'enable' => 1, //deprecated
					);
					$m_defin = $this->get_file_data($m_id);
					$au_filter = apply_filters('autopayment_filter', $au_filter, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $direction, $test, $m_defin);	
					$error = (array)$au_filter['error'];
					$pay_error = intval($au_filter['pay_error']);
					
					if (0 == count($error) and 0 == $pay_error) { 
					
						$this->do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin);
						
					} else {
						
						$this->reset_ap_status($error, $pay_error, $item, $place, $m_id, $test);
							
					}
				}	
			}
		}
		
		function check_history($au_filter, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $direction, $test, $m_defin) {
			
			if (isset($item->id) and 0 == count($au_filter['error'])) {
				$check_history = intval(is_isset($paymerch_data, 'checkpay'));
				if (1 == $check_history and $m_id and $m_id == $this->name) {
					$search_text = $this->search_in_history($item->id, $m_defin, $m_id);
					if ($search_text) {		
						$au_filter['error'][] = $search_text;
					}
				}
			}
			
			return $au_filter;
		}		
		
		function search_in_history($item_id, $m_defin, $m_id) {
			
			return '';
		}

		public function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {
			
			$this->logs(__('No action with payouts', 'pn'), $m_id, $item->id);
			
		}		
		
		public function check_ap_status($error, $item, $test) {
			
			$test = intval($test);
			if (1 != $test) {
				$ap_status = get_bids_meta($item->id, 'ap_status');
				$autopay_status = intval(is_isset($ap_status, 'status'));
				if (1 == $autopay_status) {
					$error[] = __('Automatic payout has already been made', 'pn');
				}	
			}
			
			return $error;
		}	
		
		public function set_ap_status($item, $test) {
			
			if (1 == $test) {
				return 1;
			} else {
				$ap_status = get_bids_meta($item->id, 'ap_status');
				$autopay_status = intval(is_isset($ap_status, 'status'));
				if (1 == $autopay_status) {
					return 0;
				} else {	
					$ap_status = array(
						'status' => 1,
						'time' => current_time('timestamp'),
					);
					return update_bids_meta($item->id, 'ap_status', $ap_status);
				}
			}
			
		}
		
		public function reset_ap_status($error, $pay_error, $item, $place, $m_id, $test) {
			
			if (1 == $pay_error) {
				
				$params = array(
					'm_place' => 'system scrpayerror',
					'system' => 'system',
				);
				set_bid_status('scrpayerror', $item->id, $params);
				
			} 
							
			$error_text = join('<br />', $error);
						
			do_action('paymerchant_error', $this->name, $m_id, $error, $item->id, $place, $pay_error);
						
			if ('admin' == $place) {
				pn_display_mess(__('Error!', 'pn') . $error_text);
			} else {
				send_paymerchant_error($item->id, $error_text);
			}	 

		}
		
		function reset_cron_status($item, $error_status, $m_id) {
			global $wpdb;
		
			$error_status = is_status_name($error_status);
			if (!$error_status) { $error_status = 'payouterror'; }
			
			$ap_status = array(
				'status' => 0,
				'time' => current_time('timestamp'),
			);
			update_bids_meta($item->id, 'ap_status', $ap_status);
			
			$arr = array(
				'status'=> $error_status,
				'edit_date'=> current_time('mysql'),
			);									
			$wpdb->update($wpdb->prefix . 'exchange_bids', $arr, array('id' => $item->id));
										
			do_action('ap_cron_set_status', $item, $error_status, $m_id);
			send_paymerchant_error($item->id, __('Your payment is declined', 'pn'));
			
		}	
		
		function logs($error_text, $m_id = '', $bid_id = '') {
			
			do_action('paymerchant_logs', $this->name, $m_id, $error_text, $bid_id);
			
		}		
		
		public function security_errors($text, $id, $item) {
			
			$security_list = paymerchants_setting_list('', $item, 0);
			$data = get_paymerch_data($id);
			
			$errors = array();
			foreach ($security_list as $sec_k => $sec_val) {
				$sec_k = (string)$sec_k;
				if ('resulturl' == $sec_k) {
					if (!is_isset($data, $sec_k)) {
						$errors[] = '<span class="bred">-' . __('Hash for Status/Result URL not set', 'pn') . '</span>';
					}
				}
				if ('cronhash' == $sec_k) {
					if (!is_isset($data, $sec_k)) {
						$errors[] = '<span class="bred">-' . __('Hash for Cron URL not set', 'pn') . '</span>';
					}
				}
				if ('checkpay' == $sec_k) {
					if (1 != intval(is_isset($data, $sec_k))) {
						$errors[] = '<span class="bred">-' . __('Payment history verification through API interface disabled', 'pn') . '</span>';
					}
				}								
				if ('show_error' == $sec_k) {
					$sh = intval(is_isset($data, $sec_k));
					if (1 == $sh) {
						$errors[] = '<span class="bred">-' . __('Debug mode enabled', 'pn') . '</span>';
					}
				}				
			}
			
			if (count($errors) > 0) {
				return implode('<br />', $errors);
			}
			
			return $text;
		}

		function del_directions($script, $id) {
			global $wpdb;
			
			if ($script and $script == $this->name) {
				$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE m_out LIKE '%\"{$id}\"%'");
				foreach ($items as $item) {
					$m_out = @unserialize($item->m_out);
					if (!is_array($m_out)) { $m_out = array(); }
					foreach ($m_out as $m_out_k => $m_out_v) {
						if ($m_out_v == $id) {
							unset($m_out[$m_out_k]);
						}
					}
					$arr = array();
					$arr['m_out'] = @serialize($m_out);
					$wpdb->update($wpdb->prefix . "directions", $arr, array('id' => $item->id));
				}
				$wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET m_out = '' WHERE m_out = '$id'");
			}
			
		}
		
	}
}