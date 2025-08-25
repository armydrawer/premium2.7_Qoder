<?php
if (!defined('ABSPATH')) { exit(); }

function _create_bid_auto($sum, $sum_action, $create_new = 1, $hook_place = 'exchange_button') {
	global $wpdb;	
	
	$response = array(
		'status_text' => __('Create default error', 'pn'),
		'error' => 1,
		'error_fields' => array(),
		'data' => array(
			'url' => '',
			'hash' => '',
			'id' => 0,
			'direction' => '',
			'vd1' => '',
			'vd2' => '',
		),
	);
			
	$create_new = intval($create_new);		
			
	$direction_id = intval(is_param_post('direction_id'));
	
	if (!$direction_id) {
		
		$response['error'] = 1;
		$response['status_text'] = __('Error! The direction do not exist', 'pn');
		
		return $response;		
	}	
	
	$direction_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE direction_status = '1' AND auto_status = '1' AND id = '$direction_id'");
	if (!isset($direction_data->id)) {
		
		$response['error'] = 1;
		$response['status_text'] = __('Error! Exchange direction is disabled', 'pn');
		
		return $response;		
	}

	$direction = array();
	foreach ($direction_data as $direction_key => $direction_val) {
		$direction[$direction_key] = $direction_val;
	}
	
	$directions_meta = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions_meta WHERE item_id = '$direction_id'");
	foreach ($directions_meta as $direction_item) {
		$direction[$direction_item->meta_key] = $direction_item->meta_value;
	}	
	$direction = (object)$direction; 
	
	$currency_id_give = intval($direction->currency_id_give);
	$currency_id_get = intval($direction->currency_id_get);
	
	$vd1 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_give' AND auto_status = '1' AND currency_status = '1'");
	$vd2 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_get' AND auto_status = '1' AND currency_status = '1'");
	
	if (!isset($vd1->id) or !isset($vd2->id)) {
		
		$response['error'] = 1;
		$response['status_text'] = __('Error! The direction do not exist', 'pn');
		
		return $response;				
	}	
	
	$error_fields = array();
	
	/* accounts */
	$account1 = $account2 = '';
	$show_account = apply_filters('form_bids_account_give', $vd1->show_give, $direction, $vd1);
	if (1 == $show_account) {
		$account1 = is_param_post('account1');
		$account1 = get_purse($account1, $vd1);
		if (!$account1) {
			$error_fields['account1'] = __('invalid account number', 'pn');
		}
	}
	
	$show_account = apply_filters('form_bids_account_get', $vd2->show_get, $direction, $vd2);
	if (1 == $show_account) {
		$account2 = is_param_post('account2');
		$account2 = get_purse($account2, $vd2);
		if (!$account2) {
			$error_fields['account2'] = __('invalid account number', 'pn');
		}
	}
	/* end accounts */

	$sum = is_sum($sum);
	$sum_action = intval($sum_action);
	if ($sum_action < 1 or $sum_action > 6) { $sum_action = 1; }
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);		
	
	$calc_data = array(
		'vd1' => $vd1,
		'vd2' => $vd2,
		'direction' => $direction,
		'user_id' => $user_id,
		'ui' => $ui,
		'post_sum' => $sum,
		'dej' => $sum_action,
		'account1' => $account1,
		'account2' => $account2,
	);
	$calc_data = apply_filters('get_calc_data_params', $calc_data, 'action');
	$cdata = get_calc_data($calc_data, 1);
	
	$decimal_give = $cdata['decimal_give'];
	$decimal_get = $cdata['decimal_get'];
	$currency_code_give = $cdata['currency_code_give'];
	$currency_code_get = $cdata['currency_code_get'];
	$psys_give = $cdata['psys_give'];
	$psys_get = $cdata['psys_get'];	
	$course_give = $cdata['course_give'];
	$course_get = $cdata['course_get'];
	$currency_id_give = $vd1->id;
	$currency_id_get = $vd2->id;
	$currency_code_id_give = $vd1->currency_code_id;
	$currency_code_id_get = $vd2->currency_code_id;	
	$psys_id_give = $vd1->psys_id;
	$psys_id_get = $vd2->psys_id;	
	
	$sum1 = $cdata['sum1'];
	$sum1c = $cdata['sum1c'];
	$sum2 = $cdata['sum2'];
	$sum2c = $cdata['sum2c'];	
	
	$minmax_data = array(
		'min_give' => $cdata['min_give'],
		'max_give' => $cdata['max_give'],
		'min_get' => $cdata['min_get'],
		'max_get' => $cdata['max_get'],				
	);
	$dir_minmax = get_direction_minmax($direction, $vd1, $vd2, $course_give, $course_get, $cdata['reserve'], 'calculator', $minmax_data);  
	$min1 = is_isset($dir_minmax, 'min_give');
	$max1 = is_isset($dir_minmax, 'max_give');
	$min2 = is_isset($dir_minmax, 'min_get');
	$max2 = is_isset($dir_minmax, 'max_get');		
		
	if ($sum1 < $min1) {
		$error_fields['sum1'] = '<span class="js_amount" data-id="sum1" data-val="' . $min1 . '">' . __('min', 'pn') . '.: ' . is_out_sum($min1, $cdata['decimal_give'], 'tbl') . ' ' . $currency_code_give . '</span>';													
	}	
	
	if ($sum1 > $max1 and is_numeric($max1)) {
		$error_fields['sum1'] = '<span class="js_amount" data-id="sum1" data-val="' . $max1 . '">' . __('max', 'pn') . '.: ' . is_out_sum($max1, $cdata['decimal_give'], 'tbl') . ' ' . $currency_code_give . '</span>';													
	}
	
	if ($sum2 < $min2) {
		$error_fields['sum2'] = '<span class="js_amount" data-id="sum2" data-val="' . $min2 . '">' . __('min', 'pn') . '.: ' . is_out_sum($min2, $cdata['decimal_get'], 'tbl') . ' ' . $currency_code_get . '</span>';													
	}
	
	if ($sum2 > $max2 and is_numeric($max2)) {
		$error_fields['sum2'] = '<span class="js_amount" data-id="sum2" data-val="' . $max2 . '">' . __('max', 'pn') . '.: ' . is_out_sum($max2, $cdata['decimal_get'], 'tbl') . '' . $currency_code_get . '</span>';													
	}	
	
	if ($sum1 <= 0) {
		$error_fields['sum1'] = __('amount must be greater than 0', 'pn');
	}
	
	if ($sum2 <= 0) {
		$error_fields['sum2'] = __('amount must be greater than 0', 'pn');
	}
	
	if ($sum1c <= 0) {
		$error_fields['sum1c'] = __('amount must be greater than 0', 'pn');
	}
	
	if ($sum2c <= 0) {
		$error_fields['sum2c'] = __('amount must be greater than 0', 'pn');
	}		

	$unmetas = array();
	$auto_data = array();
	$metas = array();

	$dir_fields = $wpdb->get_results("
	SELECT * FROM " . $wpdb->prefix . "direction_custom_fields LEFT OUTER JOIN " . $wpdb->prefix . "cf_directions ON(" . $wpdb->prefix . "direction_custom_fields.id = " . $wpdb->prefix . "cf_directions.cf_id) 
	WHERE " . $wpdb->prefix . "direction_custom_fields.auto_status = '1' AND " . $wpdb->prefix . "direction_custom_fields.status='1' AND " . $wpdb->prefix . "cf_directions.direction_id = '$direction_id' ORDER BY cf_order ASC");
	foreach ($dir_fields as $op_item) {
		$op_id = $op_item->cf_id;
		$op_ctype = $op_item->vid;
		$op_name = pn_strip_input($op_item->cf_name);
		$op_req = $op_item->cf_req;
		$op_value = is_param_post('cf' . $op_id);
		$op_uniq = '';
		
		if (0 == $op_ctype) {
			$op_value = get_purse($op_value, $op_item);
		} elseif (1 == $op_ctype) {
			$op_value = intval($op_value);
		} else {
			$op_value = pn_strip_input($op_value);
		}
		
		if (1 == $op_req) {
			if (1 == $op_ctype and 0 == $op_value) {
				$error_fields['cf' . $op_id] = __('value is not selected', 'pn');
			} elseif (strlen($op_value) < 1) {
				$error_fields['cf' . $op_id] = __('field is filled with errors', 'pn');
			}
		}		
			
		$op_auto = $op_item->cf_auto;
		if (!$op_auto) { 
			if (1 == $op_ctype) {
					
				$op_datas = explode("\n", ctv_ml($op_item->datas));
				foreach ($op_datas as $key => $da) {
					$key = $key + 1;
					$da = pn_strip_input($da);
					if (strlen($da) > 0) {
						if ($key == $op_value) {
							$metas[] = array(
								'title' => $op_name,
								'data' => $da,
								'id' => $op_id,
							);
							$op_uniq = $da;	
						}		
					}
				}
					
			} else { 
				
				$metas[] = array(
					'title' => $op_name,
					'data' => $op_value,
					'id' => $op_id,
				);	
				$op_uniq = $op_value;
					
			}
		} else {
				
			$op_value = $op_uniq = strip_uf($op_value, $op_auto);
			if (!$op_value and 1 == $op_req) {
				$error_fields['cf' . $op_id] = __('field is filled with errors', 'pn');	
			} 
				
			$cauv = array(
				'error' => 0,
				'error_text' => ''
			);
			$cauv = apply_filters('cf_auto_form_value', $cauv, $op_value, $op_item, $direction, $cdata);
				
			if (1 == $cauv['error']) {
				$error_fields['cf' . $op_id] = $cauv['error_text'];				
			}
								
			$auto_data[$op_auto] = $op_value;
				
		}
			
		$uniqueid = pn_strip_input($op_item->uniqueid);
		if ($uniqueid) {
			$unmetas[$uniqueid] = $op_uniq;
		}		
	}
	
	$dmetas = array();
	$dmetas[1] = $dmetas[2] = array();	
	
	$sql = "
	SELECT * FROM " . $wpdb->prefix . "currency_custom_fields
	LEFT OUTER JOIN " . $wpdb->prefix . "cf_currency
	ON(" . $wpdb->prefix . "currency_custom_fields.id = " . $wpdb->prefix . "cf_currency.cf_id)
	WHERE " . $wpdb->prefix . "currency_custom_fields.auto_status = '1' AND " . $wpdb->prefix . "currency_custom_fields.status='1' AND " . $wpdb->prefix . "cf_currency.currency_id = '$currency_id_give' AND " . $wpdb->prefix . "cf_currency.place_id = '1'
	OR " . $wpdb->prefix . "currency_custom_fields.auto_status = '1' AND " . $wpdb->prefix . "currency_custom_fields.status='1' AND " . $wpdb->prefix . "cf_currency.currency_id = '$currency_id_get' AND " . $wpdb->prefix . "cf_currency.place_id = '2'
	";
	$fields = $wpdb->get_results($sql);
	foreach ($fields as $dp_item) {
		$place_id = $dp_item->place_id;
		$dp_id = $dp_item->cf_id;
		$cf_now = 'cfgive' . $dp_id;
		if (2 == $place_id) {
			$cf_now = 'cfget' . $dp_id;
		}
		$dp_name = pn_strip_input($dp_item->cf_name);
		$dp_req = $dp_item->cf_req;
		$dp_value = is_param_post($cf_now);
		$dp_ctype = $dp_item->vid;
		$dp_uniq = '';
		
		if (0 == $dp_ctype) {
			$dp_value = $dp_uniq = get_purse($dp_value,$dp_item);
		} elseif (1 == $dp_ctype) {
			$dp_value = intval($dp_value);
		} else {
			$dp_value = pn_strip_input($dp_value);
		}		
		
		if ($dp_req) {
			if (1 == $dp_ctype and !$dp_value) {
				$error_fields[$cf_now] = __('value is not selected', 'pn');
			} elseif (strlen($dp_value) < 1) {
				$error_fields[$cf_now] = __('field is filled with errors', 'pn');
			}	
		}
		
		if (1 == $dp_ctype) {
				
			$dp_datas = explode("\n", ctv_ml($dp_item->datas));
			foreach ($dp_datas as $key => $da) {
				$key = $key + 1;
				$da = pn_strip_input($da);
				if (strlen($da) > 0) {
					if ($key == $dp_value) {
						$dp_uniq = $da;
						$dmetas[$place_id][] = array(
							'title' => $dp_name,
							'data' => $da,
							'id' => $dp_id,
						);
					}		
				}
			}				
				
		} else {
		
			$dmetas[$place_id][] = array(
				'title' => $dp_name,
				'data' => $dp_value,
				'id' => $dp_id,
			);
				
		}
		
		$uniqueid = pn_strip_input($dp_item->uniqueid);
		if ($uniqueid) {
			$unmetas[$uniqueid] = $dp_uniq;
		}		
	}
		
	$array = array();
	$array['bid_locale'] = get_locale();
	$array['direction_id'] = $direction_id;
	$array['course_give'] = $course_give;
	$array['course_get'] = $course_get;
	$array['currency_code_give'] = $currency_code_give;
	$array['currency_code_get'] = $currency_code_get;
	$array['currency_id_give'] = $currency_id_give;
	$array['currency_id_get'] = $currency_id_get;
	$array['psys_give'] = $psys_give;
	$array['psys_get'] = $psys_get;
	$array['currency_code_id_give'] = $currency_code_id_give;
	$array['currency_code_id_get'] = $currency_code_id_get;
	$array['psys_id_give'] = $psys_id_give;
	$array['psys_id_get'] = $psys_id_get;
	$array['user_id'] = $user_id;
	$array['user_login'] = is_isset($ui, 'user_login');
	$array['user_ip'] = pn_real_ip();
	$array['user_agent'] = get_user_agent();
	$array['first_name'] = is_isset($auto_data, 'first_name');
	$array['last_name'] = is_isset($auto_data, 'last_name');
	$array['second_name'] = is_isset($auto_data, 'second_name');
	$array['user_phone'] = str_replace('+', '', is_isset($auto_data, 'user_phone'));
	$array['user_skype'] = is_isset($auto_data, 'user_skype');
	$array['user_email'] = is_isset($auto_data, 'user_email');
	$array['user_passport'] = is_isset($auto_data, 'user_passport');
	$array['user_telegram'] = is_isset($auto_data, 'user_telegram');
	$array['account_give'] = $account1;
	$array['account_get'] = $account2;
	$array['metas'] = serialize($metas);	
	$array['dmetas'] = serialize($dmetas);
	$array['unmetas'] = serialize($unmetas);
	$array['user_discount'] = $cdata['user_discount'];
	$array['user_discount_sum'] = $cdata['user_discount_sum'];		
	$array['exsum'] = $cdata['exsum'];
	$array['sum1'] = $sum1;
	$array['dop_com1'] = $cdata['dop_com1'];
	$array['sum1dc'] = $cdata['sum1dc'];
	$array['com_ps1'] = $cdata['com_ps1'];
	$array['sum1c'] = $sum1c;
	$array['sum1r'] = $cdata['sum1r'];
	$array['sum2t'] = $cdata['sum2t'];
	$array['sum2'] = $sum2;
	$array['dop_com2'] = $cdata['dop_com2'];
	$array['com_ps2'] = $cdata['com_ps2'];
	$array['sum2dc'] = $cdata['sum2dc'];
	$array['sum2c'] = $sum2c;
	$array['sum2r'] = $cdata['sum2r'];
	$array['profit'] = $cdata['profit'];
	$array['user_hash'] = get_user_hash();
		
	$array = apply_filters('array_data_create_bids', $array, $direction, $vd1, $vd2, $cdata);	
	
	$error_bids = array(
		'error_text' => array(),
		'error_fields' => $error_fields,
		'bid' => $array,
	);
	$error_bids = apply_filters('error_bids', $error_bids, $direction, $vd1, $vd2, $cdata, $unmetas);

	$error_text = (array)$error_bids['error_text'];
	$error_fields = $error_bids['error_fields'];
	$array = $error_bids['bid'];			
	
	if (is_array($error_text) and count($error_text) > 0 or count($error_fields) > 0) {

	} else {
		
		$datetime = current_time('mysql');
		$array['create_date'] = $datetime;
		$array['edit_date'] = $datetime;
		$array['status'] = 'auto';
		$array['hashed'] = unique_bid_hashed();	

		$wpdb->insert($wpdb->prefix . 'exchange_bids', $array);
		$exchange_id = $wpdb->insert_id;
		if ($exchange_id > 0) {
			$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$exchange_id' AND status = 'auto'");
			if (isset($item->id)) { 
			
				$ch_data = array(
					'bid' => $item,
					'set_status' => 'auto',
					'place' => $hook_place,
					'who' => 'user',
					'old_status' => 'none',
					'direction' => $direction
				);
				_change_bid_status($ch_data);	 		 
			
				$response['error'] = 0;
				$response['data']['hash'] = $array['hashed'];
				$response['data']['url'] = get_bids_url($array['hashed']);
				$response['data']['id'] = $exchange_id;
				$response['data']['direction'] = $direction;
				$response['data']['vd1'] = $vd1;
				$response['data']['vd2'] = $vd2;
				
				$response['status_text'] = __('Your order successfully created', 'pn');
				
				if ($create_new) {
					sleep(2);
					$res = _create_bid_new($item, $direction, $vd1, $vd2, 0, $hook_place);
					if ($res['error']) {
						$error_text[] = $res['error_text'];
					}	
				} 
				
			} else {	
				$error_text[] = __('Error! No bid in db', 'pn');			
			}
		} else {
			$res_errors = _debug_table_from_db($exchange_id, 'exchange_bids', $array);
			$error_text = $res_errors;
		}
		
	}	
	
	if (is_array($error_text) and count($error_text) > 0 or count($error_fields) > 0) {
		
		$response['error'] = 1;
		$status_text = __('Error!', 'pn'); 
		if (is_array($error_text) and count($error_text) > 0) { 
			$status_text = implode('<br />', $error_text);
		} 	
		$response['status_text'] = $status_text;
		$response['error_fields'] = $error_fields;
		
	}		
		
	return $response;
}

function _create_bid_new($item, $direction, $vd1, $vd2, $check_minmax = 0, $hook_place = 'exchange_button') {
	global $wpdb, $premiumbox;

	$response = array(
		'error_text' => '',
		'error' => 0,
	);

	$check_minmax = intval($check_minmax);

	if ($check_minmax) {

		$dir_minmax = get_direction_minmax($direction, $vd1, $vd2, $item->course_give, $item->course_get);  
		$min1 = is_isset($dir_minmax, 'min_give');
		$max1 = is_isset($dir_minmax, 'max_give');
		$min2 = is_isset($dir_minmax, 'min_get');
		$max2 = is_isset($dir_minmax, 'max_get');
						
		$sum1 = is_sum($item->sum1);
		$sum2 = is_sum($item->sum2);
										
		if ($sum1 > $max1 and is_numeric($max1) or $sum2 > $max2 and is_numeric($max2)) {
					
			$response = array(
				'error_text' => __('Error! Not enough reserve for the exchange', 'pn'),
				'error' => 1,
			);				
					
		}
	
	}
			
	if (!$response['error']) {
			
		add_pn_cookie('cache_sum', 0);			
		
		$datetime = current_time('mysql');
			
		$array = array();
		$array['create_date'] = $datetime;
		$array['edit_date'] = $datetime;
		$array['status'] = 'new';
		$array = apply_filters('array_data_bids_new', $array, $item);
		
		$wpdb->update($wpdb->prefix . 'exchange_bids', $array, array('id' => $item->id));
		
		$old_status = $item->status;
		$item = pn_object_replace($item, $array);
		
		$ch_data = array(
			'bid' => $item,
			'set_status' => 'new',
			'place' => $hook_place,
			'who' => 'user',
			'old_status' => $old_status,
			'direction' => $direction
		);
		_change_bid_status($ch_data);	 	
		
	}	
	
	return $response;
}