<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('change_bid_status', 'setmerchant_change_bidstatus', 150);  
function setmerchant_change_bidstatus($data) {  
	global $wpdb, $premiumbox;

	$place = $data['place'];
	$set_status = $data['set_status'];
	$bid = $data['bid'];
	$who = $data['who'];
	$old_status = $data['old_status'];
	$direction = $data['direction'];

	$stop_action = intval(is_isset($data, 'stop'));
	if (!$stop_action) {
	
		if ('new' == $set_status) {
			$bid = set_merchant($bid, $direction);
			$stop = 0;
			if (isset($bid->stop)) {
				$stop = $bid->stop;
				unset($bid->stop);
			}
			$data['bid'] = $bid;
			if ($stop) {
				$data['stop'] = 1;
			} else {
				$data['bid'] = apply_filters('after_set_merchant', $data['bid'], $direction);
			}
		}
		
		if ('cancel' == $set_status) {
			$m_in = trim(is_isset($bid, 'm_in'));
			if ($m_in) {
				$data['bid'] = apply_filters('cancel_bid_merchant', $bid, $m_in, $direction);
			}	
		}
		
		if ('payed' == $set_status) {
			$m_in = trim(is_isset($bid, 'm_in'));
			if ($m_in) {
				$data['bid'] = apply_filters('payed_bid_merchant', $bid, $m_in, $direction);
			}
		}

	}
	
	return $data;
}

add_action('premium_request_infomerchant', 'def_premium_request_infomerchant'); 
function def_premium_request_infomerchant() {
	global $wpdb, $premiumbox, $bids_data;	
	
	$error_text = __('No bid exists', 'pn');
	
	$show_data = pn_exchanges_output('exchange');
	if (1 == $show_data['work']) {
		$hashed = is_bid_hash(is_param_get('hash'));
		if ($hashed) {
			$st = get_status_sett('merch', 1);
			$bids_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE hashed = '$hashed'");	
			if (isset($bids_data->id)) {
				$bid_status = $bids_data->status;
				if (in_array($bid_status, $st)) {
				
					$api_login = trim(is_param_get('api_login'));
					$bid_api_login = trim(is_isset($bids_data, 'api_login'));
					if (is_true_userhash($bids_data) or $api_login and $bid_api_login and $api_login == $bid_api_login) {
						$direction_id = intval($bids_data->direction_id);
						$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE direction_status = '1' AND auto_status = '1' AND id = '$direction_id'");
						if (isset($direction->id)) {					
							$m_in = trim($bids_data->m_in);
							if ($m_in) {
									
								$sum_to_pay = apply_filters('sum_to_pay', is_sum($bids_data->sum1dc), $m_in, $direction, $bids_data);
								do_action('merchant_init_info', $m_in, $sum_to_pay, $direction);
								exit;
									
							}
						}
					} else {
						$error_text = __('Error! You cannot control this order in another browser', 'pn');
					}
				
				} else {
					
					$url = get_bids_url($hashed);
					wp_redirect($url);
					exit;
					
				}
			}	
		}
	} else {
		$error_text = __('Maintenance', 'pn');
		if (strlen($show_data['text']) > 0) {
			$error_text = $show_data['text'];
		}		
	}
	
	pn_display_mess($error_text, $error_text);	
}

add_action('premium_request_payedmerchant', 'def_premium_request_payedmerchant'); 
function def_premium_request_payedmerchant() {
	global $wpdb, $premiumbox, $bids_data;	
	
	$error_text = __('No bid exists', 'pn');
	
	$show_data = pn_exchanges_output('exchange');
	if (1 == $show_data['work']) {
		$hashed = is_bid_hash(is_param_get('hash'));
		if ($hashed) {
			$st = get_status_sett('merch', 1);
			$bids_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE hashed = '$hashed'");	
			if (isset($bids_data->id)) {
				$bid_status = $bids_data->status;
				if (in_array($bid_status, $st)) {
				
					$api_login = trim(is_param_get('api_login'));
					$bid_api_login = trim(is_isset($bids_data, 'api_login'));
					if (is_true_userhash($bids_data) or $api_login and $bid_api_login and $api_login == $bid_api_login) {
						$direction_id = intval($bids_data->direction_id);
						$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE direction_status = '1' AND auto_status = '1' AND id = '$direction_id'");
						if (isset($direction->id)) {					
							$m_in = trim($bids_data->m_in);
							if ($m_in) {
									
								$sum_to_pay = apply_filters('sum_to_pay', is_sum($bids_data->sum1dc), $m_in, $direction, $bids_data);
								do_action('merchant_init_form', $m_in, $sum_to_pay, $direction);
								exit;
									
							}
						}
					} else {
						$error_text = __('Error! You cannot control this order in another browser', 'pn');
					}
					
				} else {

					$url = get_bids_url($hashed);
					wp_redirect($url);
					exit;

				}
			}	
		}
	} else {
		$error_text = __('Maintenance', 'pn');
		if (strlen($show_data['text']) > 0) {
			$error_text = $show_data['text'];
		}		
	}
	
	pn_display_mess($error_text, $error_text);	
}	

add_action('premium_request_pagemerchant', 'def_premium_request_pagemerchant'); 
function def_premium_request_pagemerchant() {
	global $wpdb, $premiumbox, $bids_data;	
	
	$error_text = __('No bid exists', 'pn');
	
	$show_data = pn_exchanges_output('exchange');
	if (1 == $show_data['work']) {
		$hashed = is_bid_hash(is_param_get('hash'));
		if ($hashed) {
			$st = get_status_sett('merch', 1);
			$bids_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE hashed = '$hashed'");	
			if (isset($bids_data->id)) {
				$bid_status = $bids_data->status;
				if (in_array($bid_status, $st)) {
				
					$api_login = trim(is_param_get('api_login'));
					$bid_api_login = trim(is_isset($bids_data, 'api_login'));
					if (is_true_userhash($bids_data) or $api_login and $bid_api_login and $api_login == $bid_api_login) {
						$direction_id = intval($bids_data->direction_id);
						$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE direction_status = '1' AND auto_status = '1' AND id = '$direction_id'");
						if (isset($direction->id)) {					
							$m_in = trim($bids_data->m_in);
							if ($m_in) {
									
								$sum_to_pay = apply_filters('sum_to_pay', is_sum($bids_data->sum1dc), $m_in ,$direction, $bids_data);
								do_action('merchant_myaction', $m_in, $sum_to_pay, $direction);
								
								$url = get_bids_url($hashed);
								wp_redirect($url);
								exit;
									
							}
						}
					} else {
						$error_text = __('Error! You cannot control this order in another browser', 'pn');
					}
					
				} else {

					$url = get_bids_url($hashed);
					wp_redirect($url);
					exit;

				}
			}	
		}
	} else {
		$error_text = __('Maintenance', 'pn');
		if (strlen($show_data['text']) > 0) {
			$error_text = $show_data['text'];
		}		
	}
	
	pn_display_mess($error_text, $error_text);	
}	

function merchants_setting_list($data, $db_data, $place) {
		
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
		'title' => __('Payment instruction for user', 'pn'),
		'default' => is_isset($data, 'text'),
		'name' => 'text',
		'tags' => apply_filters('direction_instruction_tags', array(), 'merchant_instruction'),
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
	$tags = apply_filters('direction_instruction_tags', $tags, 'merchant_note');
	$tags = apply_filters('merchant_admin_tags', $tags, $db_data->ext_plugin);
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
	$options['pagenote'] = array(
		'view' => 'editor',
		'title' => __('Message on payment page', 'pn'),
		'default' => is_isset($data, 'pagenote'),
		'tags' => $tags,
		'rows' => '12',
		'name' => 'pagenote',
		'work' => 'text',
		'formatting_tags' => 1,
		'other_tags' => 1,
		'ml' => 1,
	);		
	$options['corr'] = array(
		'view' => 'input',
		'title' => __('Payment amount error', 'pn'),
		'default' => is_isset($data, 'corr'),
		'name' => 'corr',
		'work' => 'percent',
	);
	$options['max'] = array(
		'view' => 'input',
		'title' => __('Daily limit for merchant', 'pn'),
		'default' => is_isset($data, 'max'),
		'name' => 'max',
		'work' => 'sum',
	);
	$options['max_month'] = array(
		'view' => 'input',
		'title' => __('Monthly limit for merchant', 'pn'),
		'default' => is_isset($data, 'max_month'),
		'name' => 'max_month',
		'work' => 'sum',
	);	
	$options['min_sum'] = array(
		'view' => 'input',
		'title' => __('Min. payment amount for single order', 'pn'),
		'default' => is_isset($data, 'min_sum'),
		'name' => 'min_sum',
		'work' => 'sum',
	);	
	$options['max_sum'] = array(
		'view' => 'input',
		'title' => __('Max. payment amount for single order', 'pn'),
		'default' => is_isset($data, 'max_sum'),
		'name' => 'max_sum',
		'work' => 'sum',
	);
	$options['maxc_day'] = array(
		'view' => 'input',
		'title' => __('Daily limit of orders (quantities) for merchant', 'pn'),
		'default' => is_isset($data, 'maxc_day'),
		'name' => 'maxc_day',
		'work' => 'int',
	);
	$options['maxc_month'] = array(
		'view' => 'input',
		'title' => __('Monthly limit of orders (quantities) for merchant', 'pn'),
		'default' => is_isset($data, 'maxc_month'),
		'name' => 'maxc_month',
		'work' => 'int',
	);		
	$options['discancel'] = array(
		'view' => 'select',
		'title' => __('Hide  button "Cancel order"', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => is_isset($data, 'discancel'),
		'name' => 'discancel',
		'work' => 'int',
	);	
	$options['dispay'] = array(
		'view' => 'select',
		'title' => __('Hide  button "Paid order"', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => is_isset($data, 'dispay'),
		'name' => 'dispay',
		'work' => 'int',
	);	
	$options['check_api'] = array(
		'view' => 'select',
		'title' => __('Check payment history by API', 'pn'),
		'options' => array('0' => __('No' ,'pn'), '1' => __('Yes', 'pn')),
		'default' => is_isset($data, 'check_api'),
		'name' => 'check_api',
		'work' => 'int',
	);
	$s_opts = array(
		'0' => __('Amount To send (add. fees)', 'pn'), 
		'1' => __('Amount To send (add.fees and PS fees)', 'pn'), 
		'2' => __('Amount for reserve', 'pn'),
		'3' => __('Amount To send', 'pn'),
	);	
	$options['stp'] = array(
		'view' => 'select',
		'title' => __('Amount of payment for user', 'pn'),
		'options' => $s_opts,
		'default' => is_isset($data, 'stp'),
		'name' => 'stp',
		'work' => 'int',
	);		
	$options['sfp'] = array(
		'view' => 'select',
		'title' => __('Amount expected to be creditede', 'pn'),
		'options' => $s_opts,
		'default' => is_isset($data, 'sfp'),
		'name' => 'sfp',
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
	$options['help_resulturl'] = array(
		'view' => 'help',
		'title' => __('More info', 'pn'),
		'default' => __('We recommend to use unique hashes at least 50 characters long, and containing Latin characters and numbers in random order. Create or generate a hash. For example ImYkwGjhuWyNasq2fdQJzVvCpis8umbx. When setting up the merchant on the side of the payment system as the status address (typically, this is the Status URL or Return URL), specify the URL with already specified hash. You can find the Status/Result URL with the specified hash below.', 'pn'),
	);	
	$options['show_error'] = array(
		'view' => 'select',
		'title' => __('Debug mode', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => is_isset($data, 'show_error'),
		'name' => 'show_error',
		'work' => 'int',
	);
	$options['center_title'] = array(
		'view' => 'h3',
		'title' => __('Actions with orders status in cases', 'pn'),
		'submit' => __('Save','pn'),
	);		
	$options['check'] = array(
		'view' => 'select',
		'title' => __('Number of account, from which payment was made does not match one specified in order', 'pn'),
		'options' => array('0' => __('Save order status as New', 'pn'), '1' => __('Change order status to On checking', 'pn'), '2' => __('Change order status to Paid', 'pn')),
		'default' => is_isset($data, 'check'),
		'name' => 'check',
		'work' => 'int',
	);	
	$options['invalid_ctype'] = array(
		'view' => 'select',
		'title' => __('Incorrect currency code', 'pn'),
		'options' => array('0' => __('Save order status as New', 'pn'), '1' => __('Change order status to On checking', 'pn'), '2' => __('Change order status to Paid', 'pn')),
		'default' => is_isset($data, 'invalid_ctype'),
		'name' => 'invalid_ctype',
		'work' => 'int',
	);
	$options['invalid_minsum'] = array(
		'view' => 'select',
		'title' => __('Payment amount is less than required', 'pn'),
		'options' => array('0' => __('Save order status as New', 'pn'), '1' => __('Change order status to On checking', 'pn'), '2' => __('Change order status to Paid', 'pn')),
		'default' => is_isset($data, 'invalid_minsum'),
		'name' => 'invalid_minsum',
		'work' => 'int',
	);
	$options['invalid_maxsum'] = array(
		'view' => 'select',
		'title' => __('Payment amount is more required', 'pn'),
		'options' => array('0' => __('Save order status as New', 'pn'), '1' => __('Change order status to On checking', 'pn'), '2' => __('Change order status to Paid', 'pn')),
		'default' => is_isset($data, 'invalid_maxsum'),
		'name' => 'invalid_maxsum',
		'work' => 'int',
	);
	$options['priority'] = array(
		'view' => 'input',
		'title' => __('Priority', 'pn'),
		'default' => intval(is_isset($data,'priority')),
		'name' => 'priority',
		'work' => 'int',
	);	
	$options['workstatus'] = array(
		'view' => 'user_func',
		'func_data' => $data,
		'func' => '_merch_workstatus_option',
		'name' => 'workstatus',
	);
	$options['dirs'] = array(
		'view' => 'user_func',
		'func_data' => $db_data->ext_key,
		'func' => '_merchants_dirs_option',
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
	$options['errorstatus'] = array(
		'view' => 'select',
		'title' => __('Merchant error status', 'pn'),
		'options' => array('0' => __('Status error', 'pn'), '1' => __('Waiting for details status', 'pn')),
		'default' => is_isset($data, 'errorstatus'),
		'name' => 'errorstatus',
		'work' => 'int',
	);	
		
	$options = apply_filters('_merchants_options', $options, $db_data->ext_plugin, $data, $db_data->ext_key, $place);	
	
	return $options;
}		

function _merch_workstatus_option($data) {
	
	$statused = list_bid_status();
	$workstatus = is_isset($data, 'workstatus');
	if (!is_array($workstatus)) { $workstatus = array(); }
	?>
	<div class="premium_standart_line">
		<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Personalization of statuses that can be received', 'pn'); ?></div></div>
		<div class="premium_stline_right"><div class="premium_stline_right_ins">
			<div class="premium_wrap_standart">
				<?php 
				$scroll_lists = array();
				if (is_array($statused)) {
					foreach ($statused as $key => $val) {
						$checked = 0;
						if (in_array($key, $workstatus)) {
							$checked = 1;
						}
						$scroll_lists[] = array(
							'title' => $val,
							'checked' => $checked,
							'value' => $key,
						);
					}	
				}	
				echo get_check_list($scroll_lists, 'workstatus[]', '', '300', 1);				
				?>
					<div class="premium_clear"></div>
			</div>
		</div></div>
			<div class="premium_clear"></div>
	</div>	
	<?php	
}  

function _merchants_dirs_option($ext_key) {
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
						$m_arr = @unserialize(is_isset($item, 'm_in')); 
						$m_arr = (array)$m_arr;
						
						$checked = 0;
						if (in_array($ext_key, $m_arr)) {
							$checked = 1;
						}
						$scroll_lists[] = array(
							'title' => '['. $item->id .'] ' . pn_strip_input($item->tech_name) . pn_item_status($item, 'direction_status', array('0' => __('inactive direction', 'pn'), '1' => __('active direction', 'pn'), '2' => __('hold direction', 'pn'))),
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

add_filter('_merchants_ext_options_array', 'def_merchants_ext_options_array', 10, 2);
function def_merchants_ext_options_array($data, $db_data) {
	global $wpdb;
	
	$workstatus = array();
	$d = is_param_post('workstatus');
	if (is_array($d)) {
		foreach ($d as $v) {
			$v = is_status_name($v);
			if ($v) {
				$workstatus[] = $v;
			}
		}
	}
	$data['workstatus'] = $workstatus;
	
	$dirs = is_param_post('dirs'); 
	if (!is_array($dirs)) { $dirs = array(); }
	$ext_key = $db_data->ext_key;
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' ORDER BY site_order1 ASC");
	foreach ($items as $item) {
		$item_id = $item->id;
		$up = 0;
		$m_arr = @unserialize(is_isset($item, 'm_in')); 
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
			$array['m_in'] = @serialize($n_arr);
			$wpdb->update($wpdb->prefix . 'directions', $array, array('id' => $item_id));
		}
	}
	
	return $data;
}

add_filter('curl_merch', 'timeout_merch_curl', 10, 3);
function timeout_merch_curl($ch, $m_name, $m_id) {
	
	$m_data = get_merch_data($m_id);
	$timeout = intval(is_isset($m_data, 'curl_timeout'));
	if ($timeout > 0) {
		
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		
	}
	
	return $ch;
}

function get_merch_data($m_id) {
	
	$data = array();
	$extandeds = get_extandeds();
	foreach ($extandeds as $ext) {
		if ('merchants' == $ext->ext_type and $ext->ext_key == $m_id) {
			$data = pn_json_decode($ext->ext_options);
			if (!is_array($data)) { $data = array(); }
			$data['ext_status'] = $ext->ext_status;
		}
	}
	
	return $data;
}

add_action('instruction_merchant', 'def_instruction_merchant', 1, 5);
function def_instruction_merchant($instruction, $bids_data, $direction, $vd1, $vd2) {
	global $premiumbox;
	
	if (isset($bids_data->m_in)) {
		$m_id = $bids_data->m_in;
		if ($m_id) {	
			$data = get_merch_data($m_id); 
			$text = trim(ctv_ml(is_isset($data, 'text')));
			if (strlen($text) > 0) {
				return $text;
			} else {
				$show = intval($premiumbox->get_option('exchange', 'm_ins'));
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

add_filter('allow_canceledbids', 'merch_allow_canceledbids', 110, 3);
function merch_allow_canceledbids($ind, $bids_data, $direction) {
	
	$m_id = $bids_data->m_in;
	if ($m_id) {
		$data = get_merch_data($m_id);
		$discancel = intval(is_isset($data, 'discancel'));
		if ($discancel) {
			return 0;
		}
	}
	
	return $ind;
}

add_filter('merchant_cancel_button','def_merchant_cancel_button', 110);
function def_merchant_cancel_button($button) {
	global $bids_data;
	
	$m_id = $bids_data->m_in;
	if ($m_id) {	
		$data = get_merch_data($m_id); 
		$discancel = intval(is_isset($data, 'discancel'));
		if ($discancel) {
			return '';
		}
	}
	
	return $button;
}

add_filter('allow_payedbids', 'merch_allow_payedbids', 110, 3);
function merch_allow_payedbids($ind, $bids_data, $direction) {
	
	$m_id = $bids_data->m_in;
	if ($m_id) {
		$data = get_merch_data($m_id);
		$dispay = intval(is_isset($data, 'dispay'));
		if ($dispay) {
			return 0;
		}
	}
	
	return $ind;
}

add_filter('merchant_payed_button', 'def_merchant_payed_button', 110);
function def_merchant_payed_button($button) {
	global $bids_data;
	
	$m_id = $bids_data->m_in;
	if ($m_id) {	
		$data = get_merch_data($m_id); 
		$dispay = intval(is_isset($data, 'dispay'));
		if ($dispay) {
			return '';
		}
	}
	
	return $button;
}

function _merch_workstatus($m_id, $def, $in_db = 0) {
	
	$status = $def;
	$data = get_merch_data($m_id);
	$workstatus = is_isset($data, 'workstatus');
	if (is_array($workstatus) and count($workstatus) > 0) { 
		$status = $workstatus;
	}
	$in_db = intval($in_db);
	if ($in_db) {
		return create_data_for_db($status, 'status');
	}
	
	return $status;
}

function get_merch_text($m_id, $item, $pay_sum = 0, $tb = '') {
	
	$text = '';
	if ($m_id and isset($item->id)) {
		
		$data = get_merch_data($m_id);
		$text = trim(ctv_ml(is_isset($data, $tb)));
		
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
		$fio = pn_strip_input(join(' ', $fio_arr));
		
		$text = apply_filters('get_text_pay', $text, $m_id, $item);
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
		$text = str_replace(array('[sum1dc]','[sum_dc]'), is_sum($item->sum1dc), $text); 
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
		if (strstr($text, '[bid_delete_time]')) {
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
	
	if ('note' == $tb) {
		return esc_attr(trim($text));
	} else {
		return pn_strip_text(trim($text));
	}		
}

add_filter('merchant_admin_tags', 'remove_merchant_admin_tags');
function remove_merchant_admin_tags($tags) {
	
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

function get_text_pay($m_id, $item, $pay_sum = 0) {
	
	return get_merch_text($m_id, $item, $pay_sum, 'note');
}

function get_pagenote($m_id, $item, $pay_sum = 0) {
	
	return get_merch_text($m_id, $item, $pay_sum, 'pagenote');
} 

add_action('merchant_secure','def_merchant_secure', 1, 5);
function def_merchant_secure($m_name, $req, $m_id, $m_defin, $m_data) {
	
	if ($m_id) {	
		$yes_ip = trim(is_isset($m_data, 'enableip'));
		$user_ip = pn_real_ip();
		if ($yes_ip and !pn_has_ip($yes_ip)) { 
			do_action('merchant_logs', $m_name, $m_id, sprintf(__('IP adress (%s) is blocked', 'pn'), $user_ip));
			die(sprintf(__('IP adress (%s) is blocked', 'pn'), $user_ip));
			exit;
		}
	}
	
} 

function get_corr_sum($m_id) {
	
	$data = get_merch_data($m_id);
	
	return is_isset($data, 'corr');
}

add_filter('merchant_bid_sum', 'def_merchant_bid_sum', 10, 2);
function def_merchant_bid_sum($sum, $m_id) {
	
	$corr = get_corr_sum($m_id);
	if (strstr($corr, '%')) {
		$corr = str_replace('%', '', $corr);
		$corr = is_sum($corr);
		$one_pers = 0;
		if ($sum > 0) {
			$one_pers = $sum / 100;
		}
		$new_sum = $sum - ($corr * $one_pers);
	} else {
		$corr = is_sum($corr);
		$new_sum = $sum - $corr;
	}
	
	return $new_sum;
}

function is_stp_merchant($m_id) {
	
	$data = get_merch_data($m_id); 
	
	return intval(is_isset($data, 'stp'));
} 

function is_sfp_merchant($m_id) {
	
	$data = get_merch_data($m_id); 
	
	return intval(is_isset($data, 'sfp'));
} 

function is_pay_purse($payer, $m_data, $m_id) {
	
	return apply_filters('pay_purse_merchant', $payer, $m_data, $m_id);
}

add_filter('pay_purse_merchant', 'def_pay_purse_merchant', 10);
function def_pay_purse_merchant($purse) {
	
	$purse = str_replace('+', '', $purse);
	$purse = preg_replace("/\s/", '', $purse);
	
	return $purse;
}

function get_payment_id($arg) {
	
	$id = intval(is_param_post($arg));
	if (!$id) { $id = intval(is_param_get($arg)); }
	
	return $id;
}

function redirect_merchant_action($id, $script = '', $place = '') {
	global $wpdb;
	
	$id = intval($id);	
	$script = trim($script);
	$place = intval($place);
	
	$text = __('You have successfully paid', 'pn');
	$res = 'true';
	if (0 == $place) {
		$text = __('You refused a payment', 'pn');
		$res = 'error';
	}

	if ($id > 0) {
		$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$id' AND status != 'auto' AND m_in != ''");
		if (isset($item->id)) {
			$m_in = $item->m_in;
			$m_script = get_mscript($m_in);
			if (strlen($script) > 0 and $script == $m_script or strlen($script) < 1) {
			
				do_action('redirect_merchant_action', $script, $m_in, $id);
				
				$hashed = is_bid_hash($item->hashed);
				$url = get_bids_url($hashed);
				wp_redirect($url);
				exit;		

			}
		} 
	} 
	
	pn_display_mess($text, $text, $res);	
}

function check_trans_in($m_in, $trans_id, $order_id) {
	global $wpdb;
	
	$trans_id = pn_maxf_mb(pn_strip_input($trans_id), 500);	
	$where = '';
	$order_id = intval($order_id);
	if ($order_id) {
		$where .= " AND id != '$order_id'";
	}
	
	return $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE trans_in = '$trans_id' AND m_in = '$m_in' $where");
}

function check_txid_in($m_in, $txid, $order_id) {
	global $wpdb;
	
	$txid = pn_maxf_mb(pn_strip_input($txid), 500);	
	$where = '';
	$order_id = intval($order_id);
	if ($order_id) {
		$where .= " AND id != '$order_id'";
	}
	
	return $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE txid_in = '$txid' AND m_in = '$m_in' $where");
}

function set_bid_status($status, $id, $params = array(), $direction = '') { 
	global $wpdb;	

	$sum = is_sum(is_isset($params, 'sum'), 12);
	$out_sum = is_sum(is_isset($params, 'out_sum'), 12);
	$bid_sum = is_sum(is_isset($params, 'bid_sum'), 12);
	$bid_corr_sum = is_sum(is_isset($params, 'bid_corr_sum'), 12);
	$pay_purse = pn_maxf_mb(pn_strip_input(is_isset($params, 'pay_purse')), 500);
	$to_account = pn_maxf_mb(pn_strip_input(is_isset($params, 'to_account')), 500); 
	$from_account = pn_maxf_mb(pn_strip_input(is_isset($params, 'from_account')), 500); 
	$trans_in = pn_maxf_mb(pn_strip_input(is_isset($params, 'trans_in')), 500); 
	$trans_out = pn_maxf_mb(pn_strip_input(is_isset($params, 'trans_out')), 500);
	$txid_in = pn_maxf_mb(pn_strip_input(is_isset($params, 'txid_in')), 500); 
	$txid_out = pn_maxf_mb(pn_strip_input(is_isset($params, 'txid_out')), 500);	
	$currency = strtoupper(trim(is_isset($params, 'currency')));
	$bid_currency = strtoupper(trim(is_isset($params, 'bid_currency')));
	$invalid_ctype = intval(is_isset($params, 'invalid_ctype'));
	$invalid_minsum = intval(is_isset($params, 'invalid_minsum'));
	$invalid_maxsum = intval(is_isset($params, 'invalid_maxsum'));
	$invalid_check = intval(is_isset($params, 'invalid_check'));
	$m_status = is_isset($params, 'bid_status');

	$id = intval($id);
	
	$m_place = trim(is_isset($params, 'm_place'));
	if (!$m_place) { 	
		$m_place = 'undefined';	
	}

	$system = trim(is_isset($params, 'system'));
	if ('user' != $system) { $system = 'system'; }
	
	$status = is_status_name($status);
	if ($id and $status) {
		$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$id' AND status != 'auto'");
		if (isset($item->id)) {
			$bid_status = $item->status;
			if (!is_array($m_status) or in_array($bid_status, $m_status)) {
				if ($bid_status != $status) {
					
					if ($bid_sum <= 0) {
						$bid_sum = is_sum($item->sum1r, 12);
					}
					
					if ($bid_corr_sum <= 0) {
						$bid_corr_sum = $bid_sum;
					}
					
					$account = apply_filters('pay_purse_merchant', $item->account_give);
					
					$arr = array(
						'edit_date'=> current_time('mysql') 
					);	
						
					if ($to_account) {
						$arr['to_account'] = $to_account;
					}
					
					if ($from_account) {
						$arr['from_account'] = $from_account;
					}
					
					if ($trans_in) {
						$arr['trans_in'] = $trans_in;			
					}
					
					if ($trans_out) {
						$arr['trans_out'] = $trans_out;				
					}
					
					if ($txid_in) {
						$arr['txid_in'] = $txid_in;			
					}
					
					if ($txid_out) {
						$arr['txid_out'] = $txid_out;				
					}					
						
					if ($sum > $bid_sum) {
						$arr['exceed_pay'] = 1;
					}
					
					if ($out_sum > 0) {
						$arr['out_sum'] = $out_sum;
					}
					
					if ($sum > 0) {
						$arr['pay_sum'] = $sum;					
					}
					
					if ($pay_purse) {
						$arr['pay_ac'] = $pay_purse;					
					}	

					$arr['status'] = $status;
						
					$st = array('realpay');	
					$st = apply_filters('set_bid_status_for_verify', $st);
					
					if (in_array($arr['status'], $st)) {
						if ($invalid_check > 0) {
							if ($pay_purse and $account) {
								if (strtoupper($pay_purse) != strtoupper($account)) {
									if (1 == $invalid_check) {
										$arr['status'] = 'verify';	
									}
								}
							}
						}
					}	
					
					if (in_array($arr['status'], $st)) {
						if ($invalid_ctype > 0) {
							if ($currency and $bid_currency) {
								if (strtoupper($currency) != strtoupper($bid_currency)) {
									if (1 == $invalid_ctype) {
										$arr['status'] = 'verify';
									}
								}	
							}
						}
					}
					
					if (in_array($arr['status'], $st)) {
						if ($invalid_minsum > 0) {
							if ($sum < $bid_corr_sum) {
								if (1 == $invalid_minsum) {
									$arr['status'] = 'verify';
								}
							}
						}
					}

					if (in_array($arr['status'], $st)) {
						if ($invalid_maxsum > 0) {
							if ($sum > $bid_sum) {
								if (1 == $invalid_maxsum) {
									$arr['status'] = 'verify';
								}
							}						
						}
					}

					$no_null_amount = array('realpay', 'coldpay', 'verify');
					if (isset($params['sum']) and in_array($arr['status'], $no_null_amount) and $sum <= 0) {
						$arr['status'] = 'error';
					}					
					
					$arr = apply_filters('set_bid_status_array', $arr, $params, $item, $direction);
						
					$result = $wpdb->update($wpdb->prefix . 'exchange_bids', $arr, array('id' => $item->id));
					if (1 == $result) {
						
						$old_status = $item->status;
						$item = pn_object_replace($item, $arr);
						
						$hashdata = bid_hashdata($item->id, $item);
						$hashdata = @serialize($hashdata);
						$item = pn_object_replace($item, array('hashdata' => $hashdata));
						
						$ch_data = array(
							'bid' => $item,
							'set_status' => $arr['status'],
							'place' => $m_place,
							'who' => $system,
							'old_status' => $old_status,
							'direction' => $direction
						);
						_change_bid_status($ch_data);
						 
					}				
				}
			}
		}	
	}
}	

add_filter('sum_to_pay', 'def_sum_to_pay', 1, 4); 
function def_sum_to_pay($sum, $m_id, $direction, $bids_data) {
	
	if ($m_id) {
		if (isset($bids_data->id)) {
			$vid = is_stp_merchant($m_id);
			if (1 == $vid) {
				return is_sum($bids_data->sum1c);
			} elseif (2 == $vid) {
				return is_sum($bids_data->sum1r);
			} elseif (3 == $vid) {
				return is_sum($bids_data->sum1);	
			}
		} 
	}	
	
	return $sum;
}

add_filter('sum_from_pay', 'def_sum_from_pay', 1, 4);
function def_sum_from_pay($sum, $m_id, $direction, $bids_data) {
	
	if ($m_id) {
		if (isset($bids_data->id)) {
			$vid = is_sfp_merchant($m_id);
			if (1 == $vid) {
				return is_sum($bids_data->sum1c);
			} elseif (2 == $vid) {
				return is_sum($bids_data->sum1r);
			} elseif (3 == $vid) {
				return is_sum($bids_data->sum1);	
			}
		} 
	}	
	
	return $sum;
}

add_filter('recalc_dej', 'def_recalc_dej', 1, 4);
function def_recalc_dej($dej, $m_id, $direction, $bids_data) {
	
	if ($m_id) {
		if (isset($bids_data->id)) {
			$vid = is_sfp_merchant($m_id);
			if (1 == $vid) {
				return 3;
			} elseif (2 == $vid) {
				return 6;
			} elseif (3 == $vid) {
				return 1;	
			}
		} 
	}	
	
	return $dej;
}

function get_stp($item, $m_id) {
	
	$vid = is_stp_merchant($m_id);
	$sum = is_sum($item->sum1dc);
	if (1 == $vid) {
		$sum = is_sum($item->sum1c);
	} elseif (2 == $vid) {
		$sum = is_sum($item->sum1r);
	} elseif (3 == $vid) {
		$sum = is_sum($item->sum1);		
	}	
	
	return $sum;	
}

function get_sfp($item, $m_id) {
	
	$vid = is_sfp_merchant($m_id);
	$sum = is_sum($item->sum1dc);
	if (1 == $vid) {
		$sum = is_sum($item->sum1c);
	} elseif (2 == $vid) {
		$sum = is_sum($item->sum1r);
	} elseif (3 == $vid) {
		$sum = is_sum($item->sum1);	
	}	
	
	return $sum;
}

function get_data_merchant_for_id($id, $item = '') { 
	global $wpdb;	

    $id = intval($id);
	$array = array();
	$array['err'] = 0;
	$array['status'] = $array['currency'] = $array['hashed'] = $array['m_id'] = $array['m_script'] = '';
	$array['sum'] = $array['pay_sum'] = 0;
	$array['bids_data'] = $array['direction_data'] = array();
	
	if ($id > 0) {
		if (!is_object($item)) {
			$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$id'");
		}
		if (isset($item->id)) {
			
			$array['err'] = 0;
			$array['status'] = is_status_name($item->status);
			$array['sum'] = is_sum($item->sum1dc);
			$array['currency'] = strtoupper(is_site_value($item->currency_code_give));
			$array['hashed'] = is_bid_hash($item->hashed);
			$array['bids_data'] = $item;
			$m_id = $item->m_in;
			$array['m_script'] = get_mscript($m_id);
			$array['m_id'] = $m_id;

			$direction_id = intval($item->direction_id);
			$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' AND id = '$direction_id'");
			if (isset($direction->id)) {
				$array['direction_data'] = $direction; 				
			}

			$array['pay_sum'] = apply_filters('sum_from_pay', is_sum($item->sum1dc), $item->m_in, $direction, $item); 
			
		} else {
			$array['err'] = 2;	
		}
	} else {
		$array['err'] = 1;
	}
	
	return $array;
}

function set_merchant($bids_data, $direction) {
	global $wpdb;

	$direction_id = intval(is_isset($bids_data, 'direction_id'));
	if (!isset($direction->id)) {
		$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' AND id = '$direction_id'");
	}
		
	$dir_data = get_direction_meta(is_isset($direction, 'id'), 'paymerch_data');
	$m_ins = @unserialize(is_isset($direction, 'm_in')); if (!is_array($m_ins)) { $m_ins = array(); }
	$m_ins = apply_filters('issue_merch_list', $m_ins, $direction, $bids_data);
		
	$merch = $bids_data->m_in;
	$now_merch = '';
		
	$m_arrs = array();
	foreach ($m_ins as $mer) {
		$data = get_merch_data($mer);
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
		
	$st = get_status_sett('merchlim', 1);
	
	$stop = 0;
	
	foreach ($m_arrs as $mer => $m_arr) {
		$data = is_isset($m_arr, 'data');
		$go = 1;
			
		$sum = get_sfp($bids_data, $mer);
				
		$min_sum = is_sum(is_isset($dir_data, 'm_in_min_sum')); 
		if ($min_sum <= 0) {
			$min_sum = is_sum(is_isset($data, 'min_sum'));
		} 	
		if ($min_sum > 0 and $sum < $min_sum) {
			$go = 0;
		}
				
		$max_sum = is_sum(is_isset($dir_data, 'm_in_max_sum')); 
		if ($max_sum <= 0) {
			$max_sum = is_sum(is_isset($data, 'max_sum'));
		}
		if ($max_sum > 0 and 1 == $go) {
			$go = 0;
			if ($max_sum >= $sum) {
				$go = 1;
			}
		}
					
		$max_day = is_sum(is_isset($dir_data, 'm_in_max'));
		if ($max_day <= 0) {
			$max_day = is_sum(is_isset($data, 'max'));
		}	
		if ($max_day > 0 and 1 == $go) {
			$go = 0;
			$date = current_time('Y-m-d 00:00:00');
			$sum_in = get_sum_for_merchpay($mer, $date, $st, $bids_data->id);
			$sum_day = $sum_in + $sum;
			if ($max_day >= $sum_day) {
				$go = 1;
			}
		}

		$max_month = is_sum(is_isset($dir_data, 'm_in_max_month'));
		if ($max_month <= 0) {
			$max_month = is_sum(is_isset($data, 'max_month'));
		}	
		if ($max_month > 0 and 1 == $go) {
			$go = 0;
			$date = current_time('Y-m-01 00:00:00');
			$sum_in = get_sum_for_merchpay($mer, $date, $st, $bids_data->id);
			$sum_month = $sum_in + $sum;
			if ($max_month >= $sum_month) {
				$go = 1;
			}
		}

		$maxc_day = intval(is_isset($dir_data, 'm_in_maxc_day'));
		if ($maxc_day <= 0) {
			$maxc_day = intval(is_isset($data, 'maxc_day'));
		}	
		if ($maxc_day > 0 and 1 == $go) {
			$go = 0;
			$date = current_time('Y-m-d 00:00:00');
			$count_in = get_count_for_merchpay($mer, $date, $st, $bids_data->id);
			$count_day = $count_in + 1;
			if ($maxc_day >= $count_day) {
				$go = 1;
			}
		}

		$maxc_month = intval(is_isset($dir_data, 'm_in_maxc_month'));
		if ($maxc_month <= 0) {
			$maxc_month = intval(is_isset($data, 'maxc_month'));
		}	
		if ($maxc_month > 0 and 1 == $go) {
			$go = 0;
			$date = current_time('Y-m-01 00:00:00');
			$count_in = get_count_for_merchpay($mer, $date, $st, $bids_data->id);
			$count_month = $count_in + 1;
			if ($maxc_month >= $count_month) {
				$go = 1;
			}
		}
		
		$ind = array(
			'stop' => 0,
			'go' => $go,
			'bids_data' => $bids_data,
		);
		$ind = apply_filters('init_merchant', $ind, $mer, $direction, $data, $dir_data);
		if ($ind['go']) {
			$now_merch = $mer;
			$bids_data = $ind['bids_data'];
			$stop = $ind['stop'];
			break;
		}
	}
	
	if ($merch != $now_merch) {
		$wpdb->update($wpdb->prefix . 'exchange_bids', array('m_in' => $now_merch), array('id' => $bids_data->id));
		$bids_data = pn_object_replace($bids_data, array('m_in' => $now_merch));
	}		
	
	$bids_data = pn_object_replace($bids_data, array('stop' => $stop));
	
	return $bids_data;
}

function get_mscript($m_id) {
	
	return get_ext_plugin($m_id, 'merchants');
}

add_filter('list_user_notify', 'merch_user_mailtemp');
function merch_user_mailtemp($places) {
	
	$places['generate_merchaddress'] = __('Address generation', 'pn');
	
	return $places;
}

add_filter('list_admin_notify', 'merch_admin_mailtemp');
function merch_admin_mailtemp($places) {
	
	$places['generate_merchaddress2'] = __('Address generation', 'pn');
	
	return $places;
}

add_filter('list_notify_tags_generate_merchaddress', 'merch_mailtemp_tags');
add_filter('list_notify_tags_generate_merchaddress2', 'merch_mailtemp_tags');
function merch_mailtemp_tags($tags) {
			
	$tags['bid_id'] = array(
		'title' => __('Order ID', 'pn'),
		'start' => '[bid_id]',
	);
	$tags['address'] = array(
		'title' => __('Address', 'pn'),
		'start' => '[address]',
	);
	$tags['sum'] = array(
		'title' => __('Amount', 'pn'),
		'start' => '[sum]',
	);
	$tags['currency_code_give'] = array(
		'title' => __('Currency code', 'pn'),
		'start' => '[currency_code_give]',
	);		 	
	$tags['count'] = array(
		'title' => __('Confirmations', 'pn'),
		'start' => '[count]',
	);
			
	return $tags;
}

if (!class_exists('Ext_Merchant_Premiumbox')) {
	class Ext_Merchant_Premiumbox extends Ext_Premium { 

		function __construct($file, $title, $cron = 0)
		{
			
			global $premiumbox;
			parent::__construct($file, $title, 'merchants', $premiumbox);
			
			if ($cron) {
				$ids = $this->get_ids('merchants', $this->name);
				foreach ($ids as $id) {
					add_action('premium_merchant_' . $id . '_cron' . chash_url($id), array($this, 'merchant_cron'));
				}
			}
			
			add_action('_merchants_options', array($this, 'get_options'), 10, 5);
			add_filter('merchants_security_' . $this->name, array($this, 'security_errors'), 10, 3);
			add_action('ext_merchants_delete', array($this, 'del_directions'), 10, 2);
			
			add_filter('init_merchant', array($this, 'set_merchant'), 100, 5);
			add_filter('cancel_bid_merchant', array($this, '_set_cancel'), 100, 3);
			add_filter('payed_bid_merchant', array($this, '_set_payed'), 100, 3);
			add_filter('merchant_bidform', array($this, 'bidform'), 99, 4); 
			add_filter('merchant_myaction', array($this, 'myaction'), 99, 3);
			add_filter('merchant_bidaction', array($this, 'bidaction'), 99, 4);
			add_filter('api_create_bid_data', array($this, 'api_create_bid_data'), 100, 6); 
			add_filter('merchant_payed_button', array($this, 'merchant_payed_button'), 100, 5); 
			add_filter('allow_payedbids', array($this, 'allow_payedbids'), 100, 4);
		}

		function set_merchant($ind, $m_in, $direction, $m_data, $dir_data) {
			global $premiumbox;
			
			if (1 == $ind['go']) {
				$bid = $ind['bids_data'];
				$script = get_mscript($m_in);
				if ($script and $script == $this->name) {
					
					$sum_to_pay = apply_filters('sum_to_pay', is_sum($bid->sum1dc), $m_in, $direction, $bid);
					
					$m_defin = $this->get_file_data($m_in);	
					
					global $bids_data;

					$bids_data = $bid;
					$result = $this->init($m_in, $sum_to_pay, $direction, $m_defin, $m_data);
					$result = intval($result);
					$ind['bids_data'] = $bids_data;
					 
					if ($result) {
						
						$ind['go'] = 1;
						$ind['stop'] = 0;
						
					} else {
						$mercherroraction = intval($premiumbox->get_option('exchange', 'mercherroraction'));
						$errorstatus = intval(is_isset($m_data, 'errorstatus'));
						if (1 == $mercherroraction) { 
						
							$ind['go'] = 1;
							$ind['stop'] = 1;
							
							$now_status = 'mercherror';
							if (1 == $errorstatus) {
								$now_status = 'merchwait';
							}
							
							$params = array(
								'm_place' => $m_in,
								'system' => 'user',
								'm_id' => $m_in,
								'm_data' => $m_data,
								'm_defin' => $m_defin,
							);
							set_bid_status($now_status, $bid->id, $params, $direction);

						} elseif (2 == $mercherroraction) {
							
							$ind['go'] = 0;
							$ind['stop'] = 0;
							
						} else {
							
							$ind['go'] = 1;
							$ind['stop'] = 0;
							
						}	
					}										
				
				}
			}
			
			return $ind;
		}
		
		function merch_type($m_id) {
			
			return 'form';
		}

		function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
			
			return 1;
		}
		
		function info_vd($bids_data) {
			global $wpdb;
			
			$currency_id_give = intval($bids_data->currency_id_give);
			$currency_id_get = intval($bids_data->currency_id_get);
			$vd1 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_give' AND auto_status = '1'");
			$vd2 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_get' AND auto_status = '1'");
			$vd = array(
				'vd1' => $vd1,
				'vd2' => $vd2,
			);
			
			return $vd;
		}

		function allow_payedbids($ind, $bids_data, $direction) {
			
			if ($ind) {
				$m_id = $bids_data->m_in;
				if ($m_id) {
					$script = get_ext_plugin($m_id, 'merchants');
					if ($script and $script == $this->name) {
						$type = $this->merch_type($m_id);	
						if ('form' == $type) {
							return 0;
						} elseif ('link' == $type) {
							return 0;
						} elseif ('mypaid' == $type) {
							return 0;
						} elseif ('myaction' == $type) {
							return 0;
						} elseif ('coupon' == $type) {
							return 0;
						} elseif ('address' == $type) {
							return 0;							
						}	
					}
				}
			}
			
			return $ind;
		}

		function get_pay_link($bid_id) {
			
			$pay_link = trim(get_bids_meta($bid_id, 'pay_link'));
			$pay_link = str_replace('&amp;', '&', $pay_link);
			
			return $pay_link;
		}
		
		function update_pay_link($bid_id, $pay_link) {
			
			update_bids_meta($bid_id, 'pay_link', $pay_link);
			
		}

		function merchant_payed_button($link, $sum_to_pay, $direction, $vd1, $vd2) {
			global $bids_data;
			
			$m_id = $bids_data->m_in;
			if ($m_id) {
				$script = get_ext_plugin($m_id, 'merchants');
				if ($script and $script == $this->name) {
					$type = $this->merch_type($m_id);
					if ('form' == $type) {
						return '<a href="' . get_request_link('payedmerchant', 'html', get_locale(), array('hash' => is_bid_hash($bids_data->hashed))) . '" target="_blank" class="success_paybutton">' . __('Make a payment', 'pn') . '</a>';
					} elseif ('mypaid' == $type) {
						return '<a href="' . get_request_link('pagemerchant', 'html', get_locale(), array('hash' => is_bid_hash($bids_data->hashed))) . '" class="success_paybutton iam_pay_bids">' . __('Paid', 'pn') . '</a>';
					} elseif ('myaction' == $type) {
						return '<a href="' . get_request_link('pagemerchant', 'html', get_locale(), array('hash' => is_bid_hash($bids_data->hashed))) . '" target="_blank" class="success_paybutton">' . __('Make a payment', 'pn') . '</a>';	
					} elseif ('coupon' == $type or 'address' == $type) {
						return '<a href="' . get_request_link('infomerchant', 'html', get_locale(), array('hash' => is_bid_hash($bids_data->hashed))) . '" target="_blank" class="success_paybutton">' . __('Make a payment', 'pn') . '</a>';	
					} elseif ('link' == $type) {
						$data = get_merch_data($m_id);
						$url = $this->get_pay_link($bids_data->id);
						if ($url) {
							$linkreferer = intval(is_isset($data, 'linkreferer'));
							$lref = '';
							if ($linkreferer) {
								$lref = ' rel="noreferrer noopener"';
							}
							return '<a href="' . $url . '" target="_blank" ' . $lref . ' class="success_paybutton">' . __('Make a payment', 'pn') . '</a>';
						} else {
							$error_text = pn_strip_input(ctv_ml(is_isset($data, 'linkerrortext')));
							if (strlen($error_text) < 1) { $error_text = __('Error! Please contact website technical support', 'pn'); }
							
							return '<div class="resultfalse paybutton_error">'. $error_text .'</div>';
						}
					}
				}
			}
			
			return $link;
		}

		function api_create_bid_data($data, $bid_data, $direction, $vd1, $vd2, $api_login) { 
		
			$m_id = $bid_data->m_in;
			if ($m_id) {
				$script = get_ext_plugin($m_id, 'merchants');
				if ($script and $script == $this->name) {
					$m_data = get_merch_data($m_id);
					$type = $this->merch_type($m_id);
					$data['api_actions']['type'] = $type;
					$sum_to_pay = apply_filters('sum_to_pay', is_sum(is_isset($bid_data, 'sum1dc')), $m_id, $direction, $bid_data);
					$data['api_actions']['pay_amount'] = $sum_to_pay;
					if ('form' == $type) {
						$data['api_actions']['pay'] = get_request_link('payedmerchant', 'html', get_locale(), array('hash' => is_bid_hash($bid_data->hashed), 'api_login' => $api_login));
					} elseif ('mypaid' == $type or 'myaction' == $type) { 
						$data['api_actions']['pay'] = get_request_link('pagemerchant', 'html', get_locale(), array('hash' => is_bid_hash($bid_data->hashed), 'api_login' => $api_login));
						$data['api_actions']['address'] = get_shtd_to_account($bid_data);  
						$data['api_actions']['dest_tag'] = pn_strip_input($bid_data->dest_tag);
					} elseif ('coupon' == $type or 'address' == $type) {
						$data['api_actions']['pay'] = get_request_link('infomerchant', 'html', get_locale(), array('hash' => is_bid_hash($bid_data->hashed), 'api_login' => $api_login));	
						$data['api_actions']['address'] = get_shtd_to_account($bid_data);  
						$data['api_actions']['dest_tag'] = pn_strip_input($bid_data->dest_tag);
					} elseif ('link' == $type) {
						$url = $this->get_pay_link($bid_data->id);
						if (!$url) {
							$url = 'disabled';
						}
						$data['api_actions']['pay'] = $url;					
					}	
					$discancel = intval(is_isset($m_data, 'discancel'));
					if ($discancel) {
						$data['api_actions']['cancel'] = 'disabled';
					}
					
					$dispay = intval(is_isset($m_data, 'dispay'));
					if ($dispay) {
						$data['api_actions']['pay'] = 'disabled';
					}					
				}
			}
			
			return $data;
		}

		function replace_constant($m_defin, $name) {
			global $premiumbox;
			
			$file_some = trim(is_isset($m_defin, $name));
			$file_arr = explode('/', $file_some);
			$file = end($file_arr);
			if ($file) {
				return $premiumbox->plugin_dir . '/merchants/' . $this->name . '/dostup/' . $file;
			}
			
			return '';
		}
		
		function merchant_cron() {
			
			$m_id = key_for_url('_cron');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			$this->cron($m_id, $m_defin, $m_data);
			
			_e('Done', 'pn');
			
		}
		
		function cron($m_id, $m_defin, $m_data) {
			
		}

		function bidform($temp, $m_id, $pay_sum, $direction) {
			
			return $temp;
		}	
		
		function myaction($m_id, $pay_sum, $direction) {
			
		}
		
		function confirm_count($m_id, $m_defin, $m_data) {
			
			return 0;
		}
		
		function _set_cancel($bid, $m_id, $direction) {
			
			if ($m_id) {
				$script = get_ext_plugin($m_id, 'merchants');
				if ($script and $script == $this->name) {
					return $this->set_cancel($bid, $m_id, $direction);
				}
			}
			
			return $bid;
		}
		
		function set_cancel($bid, $m_id, $direction) {
			
			return $bid;
		}
		
		function _set_payed($bid, $m_id, $direction) {
			
			if ($m_id) {
				$script = get_ext_plugin($m_id, 'merchants');
				if ($script and $script == $this->name) {
					return $this->set_payed($bid, $m_id, $direction);
				}
			}		
			
			return $bid;
		}
		
		function set_payed($bid, $m_id, $direction) {
			
			return $bid;
		}

		function bidaction($temp, $m_id, $pay_sum, $direction) {
			global $bids_data;
			
			$m_type = $this->merch_type($m_id);
			if ('address' == $m_type) {
				$script = get_mscript($m_id);
				if ($script and $script == $this->name) {
					$m_defin = $this->get_file_data($m_id);
					$m_data = get_merch_data($m_id);
		
					$currency = $bids_data->currency_code_give;
					$to_account = pn_strip_input($bids_data->to_account);
					$dest_tag = pn_strip_input($bids_data->dest_tag);
					if ($to_account) {	
					
						$pagenote = get_pagenote($m_id, $bids_data, $pay_sum);
						
						$list_data = array(
							'amount' => array(
								'title' => __('Amount', 'pn'),
								'copy' => $pay_sum,
								'text' => $pay_sum . ' ' . $currency,
							),
							'account' => array(
								'title' => __('Address', 'pn'),
								'copy' => $to_account,
								'text' => $to_account,
							),								
						);
						if ($dest_tag) {
							$list_data['dest_tag'] = array(
								'title' => __('Destination tag', 'pn'),
								'copy' => $dest_tag,
								'text' => $dest_tag,
							);
						}
						$descr = '';
						$confirm_count = $this->confirm_count($m_id, $m_defin, $m_data);
						if ($confirm_count > 0) {
							$descr = apply_filters('merchant_confirmations_text', sprintf(__('The order status changes to "Paid" when we get <b>%1$s</b> confirmations', 'pn'), $confirm_count), $bids_data);
						}
						$temp .= $this->zone_table($pagenote, $list_data, $descr);				

					} else { 
						$temp .= $this->zone_error(__('Error', 'pn'));
					} 
					
				}					
			}
			
			return $temp;
		}

		function get_options($options, $name, $data, $id, $place) {
			
			if ($name == $this->name) {
				$options = $this->options($options, $data, $id, $place);
				$type = $this->merch_type($id);
				if ($type and 'link' == $type) {
					
					$options['linkerrortext'] = array(
						'view' => 'editor',
						'title' => __('Link error text', 'pn'),
						'default' => is_isset($data, 'linkerrortext'),
						'tags' => '',
						'rows' => '8',
						'name' => 'linkerrortext',
						'work' => 'text',
						'formatting_tags' => 0,
						'other_tags' => 0,
						'ml' => 1,
					);
					
				}
			}
			
			return $options;
		}
		
		function options($options, $data, $id, $place) {
			
			return $options;
		}
 
		public function security_errors($text, $id, $item) {
			
			$security_list = merchants_setting_list('', $item, 0);
			$data = get_merch_data($id);
			
			$errors = array();
			foreach ($security_list as $sec_k => $sec_val) {
				$sec_k = (string)$sec_k;
				
				if ('cronhash' == $sec_k) {
					if (!is_isset($data, $sec_k)) {
						$errors[] = '<span class="bred">-' . __('Hash for Cron URL not set', 'pn') . '</span>';
					}
				}
				if ('resulturl' == $sec_k) {
					if (!is_isset($data, $sec_k)) {
						$errors[] = '<span class="bred">-' . __('Hash for Status/Result URL not set', 'pn') . '</span>';
					}
				}
				if ('check_api' == $sec_k) {
					if (1 != intval(is_isset($data, $sec_k))) {
						$errors[] = '<span class="bred">-' . __('Payment history verification through API interface disabled', 'pn') . '</span>';
					}
				}				
				if ('enableip' == $sec_k) {
					if (!trim(is_isset($data, $sec_k))) {
						$errors[] = '<span class="bred">-' . __('No restriction by IP address set', 'pn') . '</span>';
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
				return join('<br />', $errors);
			}
			
			return $text;
		}	

		function set_keys($keys) {
			
			$keys[] = $this->name;
			
			return $keys;
		}

		function logs($error_text, $m_id = '') {
			
			do_action('merchant_logs', $this->name, $m_id, $error_text);
			
		}
		
		function zone_form($pagenote, $list_data, $descr = '', $link = '', $hash = '') {
			global $bids_data;

			$m_id = $bids_data->m_in;
			$list_data = apply_filters('zone_form_list_data', $list_data, $m_id);
		
			$temp = '	
			<div class="zone_center">  
				<div class="zone_center_ins">';

					if (strlen($pagenote) > 0) {
						$temp .= '<div class="zone_description">' . apply_filters('comment_text', $pagenote) . '</div>';
					}	
						
					$temp .= '		
					<div class="zone_form">
						<form action="' . $link . '" method="post">
							<input type="hidden" name="hash" value="' . $hash . '" />';
							
							if (is_array($list_data)) {
								foreach ($list_data as $key => $item) {
									$new_temp = '
									<div class="zone_form_line">
										<div class="zone_form_label">' . is_isset($item, 'title') . '</div>
										<input type="text" required name="' . is_isset($item, 'name') . '" autocomplete="off" value="" />
										'. apply_filters('zone_form_line', '', $m_id, $key, $item) .'
									</div>								
									';
									$temp .= apply_filters('zone_form_line_after', $new_temp, $m_id, $key, $item);
								} 
							}	
						
						$temp .= '	
							<div class="zone_form_line">
								<input type="submit" class="submit_form" formtarget="_top" value="'. __('Submit code', 'pn').'" />
							</div>
						</form>
					</div>				
					';
			$temp .= '
				</div>
			</div>
			';	

			if (strlen($descr) > 0) {
				$temp .= '<div class="zone_descr">' . $descr . '</div>';
			}
			
			return apply_filters('zone_form', $temp, $m_id, $pagenote, $list_data, $descr, $link, $hash);
		}		
		
		function zone_table($pagenote, $list_data, $descr = '') {
			global $bids_data;	
			
			$m_id = $bids_data->m_in;
			$list_data = apply_filters('zone_table_list_data', $list_data, $m_id);
			
			$temp = '	
			<div class="zone_center">  
				<div class="zone_center_ins">';
					if (strlen($pagenote) > 0) {
						$temp .= '<div class="zone_description">' . apply_filters('comment_text', $pagenote) . '</div>';
					}							
					$temp .= '		
					<div class="zone_table">';
						if (is_array($list_data)) {
							foreach ($list_data as $key => $item) {
								$text = trim(is_isset($item, 'text'));
								$copy = trim(is_isset($item, 'copy'));
								if ($text) {	
									$new_temp = '
									<div class="zone_div">
										<div class="zone_title"><div class="zone_copy" data-clipboard-text="' . $copy . '"><div class="zone_copy_abs">' . __('copied to clipboard', 'pn') . '</div>' . is_isset($item, 'title') . '</div></div>
										<div class="zone_text" data-clipboard-text="' . $copy . '">' . $text . '</div>					
										'. apply_filters('zone_table_line', '', $m_id, $key, $item) .'
									</div>								
									';
									$temp .= apply_filters('zone_table_line_after', $new_temp, $m_id, $key, $item);
								}
							}
						}	
						$temp .= '						
					</div>				
					';
			$temp .= '
				</div>
			</div>
			';	
			if ($descr) {
				$temp .= '<div class="zone_descr">' . $descr . '</div>';
			}
			
			return apply_filters('zone_table', $temp, $m_id, $pagenote, $list_data, $descr);
		}
		
		function zone_error($error_text) {
			
			$temp = '<div class="error_div"><div class="error_div_ins">' . $error_text . '</div></div>';
			
			return $temp;
		}
		
		function error_back($hash, $code) {
			
			$back = get_request_link('infomerchant', 'html', get_locale(), array('hash' => is_bid_hash($hash), 'err' => $code));
			wp_redirect($back);
			exit;
		}		
		
		function del_directions($script, $id) {
			global $wpdb;
			
			if ($script and $script == $this->name) {
				
				$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE m_in LIKE '%\"{$id}\"%'");
				foreach ($items as $item) {
					$m_in = @unserialize($item->m_in);
					if (!is_array($m_in)) { $m_in = array(); }
					foreach ($m_in as $m_in_k => $m_in_v) {
						if ($m_in_v == $id) {
							unset($m_in[$m_in_k]);
						}
					}
					$arr = array();
					$arr['m_in'] = @serialize($m_in);
					$wpdb->update($wpdb->prefix . "directions", $arr, array('id' => $item->id));
				}
				$wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET m_in = '' WHERE m_in = '$id'");
				
			}
			
		}
	}
}