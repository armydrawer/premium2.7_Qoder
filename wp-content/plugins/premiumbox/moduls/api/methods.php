<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('api_all_methods', 'test_api_all_methods');
function test_api_all_methods($lists) {
	
	$lists['test'] = 'test';
	$lists['get_direction_currencies'] = 'get_direction_currencies';
	$lists['get_directions'] = 'get_directions';
	$lists['get_direction'] = 'get_direction';
	$lists['get_exchanges'] = 'get_exchanges';
	$lists['get_calc'] = 'get_calc';
	$lists['create_bid'] = 'create_bid';
	$lists['cancel_bid'] = 'cancel_bid';
	$lists['pay_bid'] = 'pay_bid';
	$lists['success_bid'] = 'success_bid';
	$lists['bid_info'] = 'bid_info';
	$lists['get_partner_info'] = 'get_partner_info';
	$lists['get_partner_links'] = 'get_partner_links';
	$lists['get_partner_exchanges'] = 'get_partner_exchanges';
	$lists['get_partner_payouts'] = 'get_partner_payouts';
	$lists['add_partner_payout'] = 'add_partner_payout';
	
	return $lists;
}

add_action('userapi_v1_test', 'the_userapi_v1_test');
function the_userapi_v1_test($ui) {
	
	$json = array(
		'error' => 0,
		'error_text' => '',
		'data' => array(
			'ip' => pn_real_ip(),
			'user_id' => intval(is_isset($ui,'ID')),
			'locale' => get_locale(),
			'partner_id' => intval(is_param_post('partner_id')),
		),
	);	
	
	echo pn_json_encode($json);
	exit;
	
}

add_action('userapi_v1_get_direction_currencies', 'the_userapi_v1_get_direction_currencies');
function the_userapi_v1_get_direction_currencies($ui) {
	global $wpdb;	
	
	$give = array();
	$get = array();
	$error = 1;
	$error_text = 'maintenance';
	
	$show_data = pn_exchanges_output('api');
	if (1 == $show_data['show']) {
		$error = 0;
		$error_text = '';		
		
		$v = get_currency_data();
		$where = get_directions_where('api');
		
		$currency_id_give = intval(is_param_post('currency_id_give'));
		if ($currency_id_give > 0) {
			$where .= "AND currency_id_give = '$currency_id_give'";
		}
		$currency_id_get = intval(is_param_post('currency_id_get'));
		if ($currency_id_get > 0) {
			$where .= "AND currency_id_get = '$currency_id_get'";
		}
		
		$gives = $gets = array();
		
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where");
		foreach ($items as $item) { 
			$output = apply_filters('get_direction_output', 1, $item, 'api');	
			if ($output) {
				$curr_id_give = $item->currency_id_give;
				$curr_id_get = $item->currency_id_get;
				if (isset($v[$curr_id_give], $v[$curr_id_get])) { 
					$vd1 = $v[$curr_id_give];
					$vd2 = $v[$curr_id_get];
					
					if (!isset($gives[$curr_id_give])) {
						$gives[$curr_id_give] = 1;
						$give[] = array(
							'id' => $curr_id_give,
							'title' => get_currency_title($vd1),
							'logo' => _add_site_host(get_currency_logo($vd1, 1)),
						);
					}
					
					if (!isset($gets[$curr_id_get])) {
						$gets[$curr_id_get] = 1;					
						$get[] = array(
							'id' => $curr_id_get,
							'title' => get_currency_title($vd2),
							'logo' => _add_site_host(get_currency_logo($vd2, 1)),
						);	
					}	
				}	
			}		
		}
	} 
	
	$json = array(
		'error' => $error,
		'error_text' => $error_text,
		'data' => array(
			'give' => $give,
			'get' => $get,
		),
	);	
	echo pn_json_encode($json);
	exit;
	
}

add_action('userapi_v1_get_directions', 'the_userapi_v1_get_directions');
function the_userapi_v1_get_directions($ui) {
	global $wpdb;	
	
	$dirs = array();
	$error = 1;
	$error_text = 'maintenance';
	
	$show_data = pn_exchanges_output('api');
	if (1 == $show_data['show']) {
		$error = 0;
		$error_text = '';
		
		$v = get_currency_data();
		$where = get_directions_where('api');
		
		$currency_id_give = intval(is_param_post('currency_id_give'));
		if ($currency_id_give > 0) {
			$where .= "AND currency_id_give = '$currency_id_give' ";
		}
		$currency_id_get = intval(is_param_post('currency_id_get'));
		if ($currency_id_get > 0) {
			$where .= "AND currency_id_get = '$currency_id_get' ";
		}
		
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where");
		foreach ($items as $item) { 
			$output = apply_filters('get_direction_output', 1, $item, 'api');	
			if ($output) {
				$curr_id_give = $item->currency_id_give;
				$curr_id_get = $item->currency_id_get;
				if (isset($v[$curr_id_give], $v[$curr_id_get])) { 
					$vd1 = $v[$curr_id_give];
					$vd2 = $v[$curr_id_get];
					
					$now = array(
						'direction_id' => $item->id,
						'currency_give_id' => $curr_id_give,
						'currency_give_title' => get_currency_title($vd1),
						'currency_give_logo' => _add_site_host(get_currency_logo($vd1, 1)),
						'currency_get_id' => $curr_id_get,
						'currency_get_title' => get_currency_title($vd2),
						'currency_get_logo' => _add_site_host(get_currency_logo($vd2, 1)),						
					);
					$dirs[] = apply_filters('api_get_directions', $now, $item, $vd1, $vd2);
					
				}	
			}		
		}
	} 
	
	$json = array(
		'error' => $error,
		'error_text' => $error_text,
		'data' => $dirs,
	);	 
	echo pn_json_encode($json);
	exit;
	
}

add_action('userapi_v1_get_direction', 'the_userapi_v1_get_direction');
function the_userapi_v1_get_direction($ui) {
	global $wpdb;	
	
	$dir = array();
	$error = '1';
	$error_text = 'direction not found';
	
	$show_data = pn_exchanges_output('api');
	if (strlen($show_data['text']) > 0) {
		$error_text = $show_data['text'];
	}
	if (1 == $show_data['show']) {
		
		$v = get_currency_data();
		$where = get_directions_where('api');
		
		$error = '0';
		$error_text = '';
		
		$direction_id = intval(is_param_post('direction_id'));
		if ($direction_id) {
			$where .= "AND id = '$direction_id' ";
		}		
		
		$currency_id_give = intval(is_param_post('currency_id_give'));
		if ($currency_id_give) {
			$where .= "AND currency_id_give = '$currency_id_give' ";
		}

		$currency_id_get = intval(is_param_post('currency_id_get'));
		if ($currency_id_get) {
			$where .= "AND currency_id_get = '$currency_id_get' ";
		}
		
		$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where");
		foreach ($directions as $direction) { 
			$output = apply_filters('get_direction_output', 1, $direction, 'api');	
			if ($output) {
				$currency_id_give = $direction->currency_id_give;
				$currency_id_get = $direction->currency_id_get;
				if (isset($v[$currency_id_give], $v[$currency_id_get])) { 
					$vd1 = $v[$currency_id_give];
					$vd2 = $v[$currency_id_get];					
					
					$before_button_text = get_direction_descr('window_txt', $direction, $vd1, $vd2);	
					$before_button_text = apply_filters('direction_instruction', $before_button_text, 'window_txt', $direction, $vd1, $vd2);
					$before_button_text = apply_filters('comment_text', $before_button_text);
					
					$timeline_text = get_direction_descr('timeline_txt', $direction, $vd1, $vd2);
					$timeline_text = apply_filters('direction_instruction', $timeline_text, 'timeline_txt', $direction, $vd1, $vd2);
					$timeline_text = apply_filters('comment_text', $timeline_text);
					
					$frozen_text = '';
					if (2 == $direction->direction_status) {
						$frozen_text = get_direction_descr('frozen_txt', $direction, $vd1, $vd2);
						$frozen_text = apply_filters('direction_instruction', $frozen_text, 'frozen_txt', $direction, $vd1, $vd2);
						$frozen_text = apply_filters('comment_text', $frozen_text);
					}
					
					$calc_data = array(
						'vd1' => $vd1,
						'vd2' => $vd2,
						'direction' => $direction,
						'user_id' => 0,
						'ui' => '',
						'post_sum' => 0,
					);
					$calc_data = apply_filters('get_calc_data_params', $calc_data, 'exchangeform');
					$cdata = get_calc_data($calc_data, 1);
					
					$minmax_data = array(
						'min_give' => $cdata['min_give'],
						'max_give' => $cdata['max_give'],
						'min_get' => $cdata['min_get'],
						'max_get' => $cdata['max_get'],				
					);					
					
					$dir_minmax = get_direction_minmax($direction, $vd1, $vd2, $cdata['course_give'], $cdata['course_get'], $cdata['reserve'], 'calculator', $minmax_data);    
					$min1 = is_isset($dir_minmax, 'min_give');
					$max1 = is_isset($dir_minmax, 'max_give');
					$min2 = is_isset($dir_minmax, 'min_get');
					$max2 = is_isset($dir_minmax, 'max_get');
					
					$dir = array(
						'id' => $direction->id,
						'url' => get_exchange_link($direction->direction_name),
						'currency_code_give' => $cdata['currency_code_give'],
						'currency_code_get' => $cdata['currency_code_get'],
						'reserve' => $cdata['reserve'],
						'course_give' => $cdata['course_give'],
						'course_get' => $cdata['course_get'],
						'sum_give' => $cdata['sum1'],
						'sum_give_com' => $cdata['sum1c'],
						'sum_get' => $cdata['sum2'],
						'sum_get_com' => $cdata['sum2c'],
						'com_give' => $cdata['comis_text1'],
						'com_get' => $cdata['comis_text2'],
						'min_give' => $min1,
						'max_give' => $max1,
						'min_get' => $min2,
						'max_get' => $max2,
						'give_fields' => list_currency_fields($vd1, $direction, 1),
						'get_fields' => list_currency_fields($vd2, $direction, 2),
						'dir_fields' => list_direction_fields($direction),
						'info' => array(
							'timeline_text' => $timeline_text,
							'frozen_text' => $frozen_text,
							'before_button_text' => $before_button_text,
						),						
					);
					$dir = apply_filters('api_get_direction_data', $dir, $direction, $vd1, $vd2, $cdata);
					
					break;
				}	
			}		
		}
	}
	
	$json = array(
		'error' => $error,
		'error_text' => $error_text,
		'data' => $dir,
	);	
	echo pn_json_encode($json);
	exit;
	
}

add_action('userapi_v1_get_calc', 'the_userapi_v1_get_calc');
function the_userapi_v1_get_calc($ui) {
	global $wpdb;	
	
	$error = '1';
	$error_text = 'direction not found';
	$data = array();
	
	$show_data = pn_exchanges_output('api');
	if (strlen($show_data['text']) > 0) {
		$error_text = $show_data['text'];
	}
	if (1 == $show_data['show']) {
		
		$direction_id = intval(is_param_post('direction_id'));
		$sum = is_sum(is_param_post('calc_amount'));
		$dej = intval(is_param_post('calc_action'));
		$cd = urldecode(trim(is_param_post('cd')));
		parse_str($cd, $cd_arr);
		
		$where = get_directions_where('api');
		$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where AND id = '$direction_id'");
		if (isset($direction->id)) {
			$output = apply_filters('get_direction_output', 1, $direction, 'api');	
			if ($output) {
				$v = get_currency_data();
				$currency_id_give = $direction->currency_id_give;
				$currency_id_get = $direction->currency_id_get;
				if (isset($v[$currency_id_give], $v[$currency_id_get])) { 
					$vd1 = $v[$currency_id_give];
					$vd2 = $v[$currency_id_get];
					
					$error = '0';
					$error_text = '';					
					
					$calc_data = array( 
						'vd1' => $vd1,
						'vd2' => $vd2,
						'direction' => $direction,
						'user_id' => '',
						'ui' => '',
						'post_sum' => $sum,
						'dej' => $dej,
						'cd' => $cd_arr,
					);
					$calc_data = apply_filters('get_calc_data_params', $calc_data, 'calculator');							
					$cdata = get_calc_data($calc_data, 1);					
					
					$minmax_data = array(
						'min_give' => $cdata['min_give'],
						'max_give' => $cdata['max_give'],
						'min_get' => $cdata['min_get'],
						'max_get' => $cdata['max_get'],				
					);					
					
					$dir_minmax = get_direction_minmax($direction, $vd1, $vd2, $cdata['course_give'], $cdata['course_get'], $cdata['reserve'], 'calculator', $minmax_data); 
					$min1 = is_isset($dir_minmax, 'min_give');
					$max1 = is_isset($dir_minmax, 'max_give');
					$min2 = is_isset($dir_minmax, 'min_get');
					$max2 = is_isset($dir_minmax, 'max_get');					
					
					$data = array(
						'currency_code_give' => $cdata['currency_code_give'],
						'currency_code_get' => $cdata['currency_code_get'],
						'reserve' => $cdata['reserve'],
						'course_give' => $cdata['course_give'],
						'course_get' => $cdata['course_get'],
						'sum_give' => $cdata['sum1'],
						'sum_give_com' => $cdata['sum1c'],
						'sum_get' => $cdata['sum2'],
						'sum_get_com' => $cdata['sum2c'],
						'com_give' => $cdata['comis_text1'],
						'com_get' => $cdata['comis_text2'],
						'min_give' => $min1,
						'max_give' => $max1,
						'min_get' => $min2,
						'max_get' => $max2,
						'changed' => $cdata['changed'],
					);
					$data = apply_filters('api_get_calc_data', $data, $direction, $vd1, $vd2, $cdata);						
						
				}	
			}		
		}
	}

	$json = array(
		'error' => $error,
		'error_text' => $error_text,
		'data' => $data,
	);	
	echo pn_json_encode($json);
	exit;
}

add_action('userapi_v1_create_bid', 'the_userapi_v1_create_bid', 10, 2);
function the_userapi_v1_create_bid($ui, $api_login) {	
	global $wpdb;	
	
	$data = array();
	$error_fields = array();
	$error = '1';
	$error_text = 'direction not found';	
	
	$show_data = pn_exchanges_output('api');
	if (1 == $show_data['work']) {
		$info = _create_bid_auto(is_param_post('calc_amount'), is_param_post('calc_action'), 1, 'api');
		
		if ($info['error']) {
			
			$error = '3';
			$error_text = $info['status_text'];
			$error_fields = $info['error_fields'];
			
		} else {
			
			$callback_url = pn_strip_input(is_param_post('callback_url'));
			if ($callback_url) {
				update_bids_meta($info['data']['id'], 'api_callback_url', $callback_url);
			}
			
			$error = '0';
			$error_text = '';
			
			$data = array(
				'url' => $info['data']['url'],
				'id' => $info['data']['id'],
				'hash' => $info['data']['hash'],
			);
			
			$bid_id = intval(is_isset($data, 'id'));
			if ($bid_id > 0) {
				$bid_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$bid_id'");
				if (isset($bid_data->id)) {
				
					$data['status'] = $bid_data->status;
					$data['status_title'] = get_direction_tempdata($bid_data->status, 'naps_title');
				
					$data['psys_give'] = pn_strip_input(ctv_ml($bid_data->psys_give));
					$data['psys_get'] = pn_strip_input(ctv_ml($bid_data->psys_get));
					$data['currency_code_give'] = is_site_value($bid_data->currency_code_give);
					$data['currency_code_get'] = is_site_value($bid_data->currency_code_get);
					$data['amount_give'] = is_sum($bid_data->sum1dc);
					$data['amount_get'] = is_sum($bid_data->sum2c);
					$data['course_give'] = is_sum($bid_data->course_give);
					$data['course_get'] = is_sum($bid_data->course_get);
				
					$data['api_actions'] = array(
						'type' => 'default',
						'cancel' => 'api',
						'pay' => 'api',
						'pay_amount' => is_sum(is_isset($bid_data, 'sum1dc')),
						'instruction' => apply_filters('comment_text', _bid_pay_instruction($bid_data->status, $bid_data, $info['data']['direction'], $info['data']['vd1'], $info['data']['vd2'])),
					);	
					
					$data = apply_filters('api_create_bid_data', $data, $bid_data, $info['data']['direction'], $info['data']['vd1'], $info['data']['vd2'], $api_login); 				
				
				}
			}
			
		}		
	} else {
		$error = '2';	
		$error_text = 'maintenance';
		if ($show_data['text']) {
			$error_text = $show_data['text'];
		}		
	}
	
	$json = array(
		'error' => $error,
		'error_text' => $error_text,
		'error_fields' => $error_fields,
		'data' => $data,
	);	
	echo pn_json_encode($json);
	exit;
	
}

add_action('userapi_v1_bid_info', 'the_userapi_v1_bid_info', 10, 2);
function the_userapi_v1_bid_info($ui, $api_login) {	
	global $wpdb, $bids_data; 	
	
	$data = array();
	$error_fields = array();
	$error = '1';
	$error_text = 'no bid exists';	
	
	$show_data = pn_exchanges_output('api');
	if (1 == $show_data['work']) {
		
		$bid_id = intval(is_param_post('id'));
		$hashed = is_bid_hash(is_param_post('hash'));
		$where = '';
		if ($bid_id > 0) {
			$where = "id = '$bid_id'";
		} elseif ($hashed) {
			$where = "hashed='$hashed'";
		}
		
		if (strlen($where) > 0) {
			$bids_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE $where AND api_login = '$api_login'");	
			if (isset($bids_data->id)) {
				$direction_id = intval($bids_data->direction_id);
				$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE direction_status = '1' AND auto_status = '1' AND id = '$direction_id'");
				if (isset($direction->id)) {	
					$currency_id_give = intval($direction->currency_id_give);
					$currency_id_get = intval($direction->currency_id_get);	
					$vd1 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_give' AND auto_status = '1' AND currency_status = '1'");
					$vd2 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_get' AND auto_status = '1' AND currency_status = '1'");
					if (isset($vd1->id) and isset($vd2->id)) {
			
						$error = '0';
						$error_text = '';
						
						$data = array(
							'url' => get_bids_url($bids_data->hashed),
							'id' => $bids_data->id,
							'hash' => $bids_data->hashed,
						);
							
						$data['status'] = $bids_data->status;
						$data['status_title'] = get_direction_tempdata($bids_data->status, 'naps_title');
						$data['psys_give'] = pn_strip_input(ctv_ml($bids_data->psys_give));
						$data['psys_get'] = pn_strip_input(ctv_ml($bids_data->psys_get));
						$data['currency_code_give'] = is_site_value($bids_data->currency_code_give);
						$data['currency_code_get'] = is_site_value($bids_data->currency_code_get);
						$data['amount_give'] = is_sum($bids_data->sum1dc);
						$data['amount_get'] = is_sum($bids_data->sum2c);
						$data['course_give'] = is_sum($bids_data->course_give);
						$data['course_get'] = is_sum($bids_data->course_get);						
						
						$data['api_actions'] = array(
							'type' => 'default',
							'cancel' => 'api',
							'pay' => 'api',
							'pay_amount' => is_sum(is_isset($bids_data, 'sum1dc')),
							'instruction' => apply_filters('comment_text', _bid_pay_instruction($bids_data->status, $bids_data, $direction, $vd1, $vd2)),
						);	
						
						$data = apply_filters('api_create_bid_data', $data, $bids_data, $direction, $vd1, $vd2, $api_login);

						$st = get_status_sett('merch', 1);
						if (!in_array($bids_data->status, $st)) {
							$data['api_actions']['type'] = 'finished';
						}
			
					}
				}
			}
		}
		
	} else {
		$error = '2';	
		$error_text = 'maintenance';
		if ($show_data['text']) {
			$error_text = $show_data['text'];
		}		
	}
	
	$json = array(
		'error' => $error,
		'error_text' => $error_text,
		'error_fields' => $error_fields,
		'data' => $data,
	);	
	echo pn_json_encode($json);
	exit;
	
}

add_action('userapi_v1_cancel_bid', 'the_userapi_v1_cancel_bid', 10, 2);
function the_userapi_v1_cancel_bid($ui, $api_login) {	
	global $wpdb;	
	
	$data = '';
	$error = '1';
	$error_text = 'no bid exists';	
	
	$show_data = pn_exchanges_output('api');
	if (1 == $show_data['work']) {
		
		$bid_id = intval(is_param_post('id'));
		$hashed = is_bid_hash(is_param_post('hash'));
		$where = '';
		if ($bid_id > 0) {
			$where = "id = '$bid_id'";
		} elseif ($hashed) {
			$where = "hashed='$hashed'";
		}		
		
		if (strlen($where) > 0) {
			$st = get_status_sett('cancel', 1);
			$bids_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE $where AND api_login = '$api_login'");	
			if (isset($bids_data->id)) {
				$old_status = $bids_data->status;
				if ('cancel' == $old_status) {
					$data = $old_status;
					$error = '0';
					$error_text = '';					
				} elseif (in_array($old_status, $st)) {
					$direction_id = intval($bids_data->direction_id);
					$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE direction_status = '1' AND auto_status = '1' AND id = '$direction_id'");
					if (isset($direction->id)) {				
						$allow = apply_filters('allow_canceledbids', 1, $bids_data, $direction);
						if ($allow) {	
						
							$arr = array('status' => 'cancel', 'edit_date' => current_time('mysql'));
							$result = $wpdb->update($wpdb->prefix . 'exchange_bids', $arr, array('id' => $bids_data->id));
							if ($result) {
								
								$bids_data = pn_object_replace($bids_data, $arr);
								
								$ch_data = array(
									'bid' => $bids_data,
									'set_status' => 'cancel',
									'place' => 'api',
									'who' => 'user',
									'old_status' => $old_status,
									'direction' => $direction
								);
								$ch_data = _change_bid_status($ch_data); 
								
								$data = $ch_data['bid']->status;
								$error = '0';
								$error_text = '';
								
							}
							
						} else {
							$error = '2';
							$error_text = 'action disabled';
						}
					}
				}
			}			
		}
		
	} else {
		$error = '2';	
		$error_text = 'maintenance';
		if ($show_data['text']) {
			$error_text = $show_data['text'];
		}		
	}
	
	$json = array(
		'error' => $error,
		'error_text' => $error_text,
		'data' => $data,
	);
	echo pn_json_encode($json);
	exit;
	
}

add_action('userapi_v1_pay_bid', 'the_userapi_v1_pay_bid', 10, 2);
function the_userapi_v1_pay_bid($ui, $api_login) {	
	global $wpdb;	
	
	$data = '';
	$error = '1';
	$error_text = 'no bid exists';	
	
	$show_data = pn_exchanges_output('api');
	if (1 == $show_data['work']) {
		
		$bid_id = intval(is_param_post('id'));
		$hashed = is_bid_hash(is_param_post('hash'));
		$where = '';
		if ($bid_id > 0) {
			$where = "id = '$bid_id'";
		} elseif ($hashed) {
			$where = "hashed='$hashed'";
		}		
		
		if (strlen($where) > 0) {
			$st = get_status_sett('payed', 1);
			$bids_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE $where AND api_login = '$api_login'");	
			if (isset($bids_data->id)) {
				$old_status = $bids_data->status;
				if ('payed' == $old_status) {
					$data = $old_status;
					$error = '0';
					$error_text = '';
				} elseif (in_array($old_status, $st)) {				
					$direction_id = intval($bids_data->direction_id);
					$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE direction_status = '1' AND auto_status = '1' AND id = '$direction_id'");
					if (isset($direction->id)) {				
						$allow = apply_filters('allow_payedbids', 1, $bids_data, $direction);
						if ($allow) {	
						
							$arr = array('status' => 'payed', 'edit_date' => current_time('mysql'));
							$result = $wpdb->update($wpdb->prefix . 'exchange_bids', $arr, array('id' => $bids_data->id));
							if ($result) {
								
								$bids_data = pn_object_replace($bids_data, $arr);
									
								$ch_data = array(
									'bid' => $bids_data,
									'set_status' => 'payed',
									'place' => 'api',
									'who' => 'user',
									'old_status' => $old_status,
									'direction' => $direction
								);
								$ch_data = _change_bid_status($ch_data);								
									
								$data = $ch_data['bid']->status;
								$error = '0';
								$error_text = '';
								
							}
							
						} else {
							$error = '2';
							$error_text = 'action disabled';
						}
					}
				}	
			}			
		}
		
	} else {
		$error = '2';	
		$error_text = 'maintenance';
		if ($show_data['text']) {
			$error_text = $show_data['text'];
		}		
	}
	
	$json = array(
		'error' => $error,
		'error_text' => $error_text,
		'data' => $data,
	);
	echo pn_json_encode($json);
	exit;
	
}

add_action('userapi_v1_success_bid', 'the_userapi_v1_success_bid', 10, 2);
function the_userapi_v1_success_bid($ui, $api_login) {	
	global $wpdb;	
	
	$data = '';
	$error = '1';
	$error_text = 'no bid exists';	
	
	$show_data = pn_exchanges_output('api');
	if (1 == $show_data['work']) {
		
		$bid_id = intval(is_param_post('id'));
		$hashed = is_bid_hash(is_param_post('hash'));
		$where = '';
		if ($bid_id > 0) {
			$where = "id = '$bid_id'";
		} elseif ($hashed) {
			$where = "hashed='$hashed'";
		}		
		
		if (strlen($where) > 0) {
			$bids_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE $where AND api_login = '$api_login'");	
			if (isset($bids_data->id)) {
				$old_status = $bids_data->status;
				if ('success' == $old_status) {
					$data = $old_status;
					$error = '0';
					$error_text = '';
				} else {				
					$direction_id = intval($bids_data->direction_id);
					$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE direction_status = '1' AND auto_status = '1' AND id = '$direction_id'");
					if (isset($direction->id)) {				
						$allow = apply_filters('allow_successbids', 1, $bids_data, $direction);
						if ($allow) {	
						
							$arr = array('status' => 'success', 'edit_date' => current_time('mysql'));
							$result = $wpdb->update($wpdb->prefix . 'exchange_bids', $arr, array('id' => $bids_data->id));
							if ($result) {
								
								$bids_data = pn_object_replace($bids_data, $arr);
									
								$ch_data = array(
									'bid' => $bids_data,
									'set_status' => 'success',
									'place' => 'api',
									'who' => 'user',
									'old_status' => $old_status,
									'direction' => $direction
								);
								$ch_data = _change_bid_status($ch_data);								
									
								$data = $ch_data['bid']->status;
								$error = '0';
								$error_text = '';
								
							}
							
						} else {
							$error = '2';
							$error_text = 'action disabled';
						}
					}
				}	
			}			
		}
		
	} else {
		$error = '2';	
		$error_text = 'maintenance';
		if ($show_data['text']) {
			$error_text = $show_data['text'];
		}		
	}
	
	$json = array(
		'error' => $error,
		'error_text' => $error_text,
		'data' => $data,
	);
	echo pn_json_encode($json);
	exit;
	
}

add_action('userapi_v1_get_partner_info', 'the_userapi_v1_get_partner_info', 10, 2);
function the_userapi_v1_get_partner_info($ui, $api_login) {	
	global $wpdb, $premiumbox;	
	
	$data = array();
	$error = '1';
	$error_text = 'no active partner program';	
	
	if (function_exists('pp_caps')) {
		$user_id = intval(is_isset($ui, 'ID'));
		if ($user_id > 0) {
			
			$error = '0';
			$error_text = '';			
			
			$balance = get_partner_money($user_id, array('0', '1'));
			$min_payout = is_sum($premiumbox->get_option('partners', 'minpay'), 2);
			$cur_type = cur_type();
			$items = array();
			$currencies = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' AND currency_status = '1' AND p_payout = '1' ORDER BY reserv_order ASC");		
			foreach ($currencies as $item) { 
				$reserve = is_sum($item->currency_reserv); 
				$payout_com = is_sum($item->payout_com);
				$paysum = is_sum(convert_sum($balance, $cur_type, $item->currency_code_title));
				if ($reserve >= $paysum and $balance > 0) {	
					$items[] = array(
						'id' => $item->id,
						'title' => get_currency_title($item),
						'comission' => $payout_com . '%',
						'amount' => sum_after_comis($paysum, $payout_com),
					);			
				}
			}	
			$data = array(
				'partner_id' => $user_id,
				'balance' => $balance,
				'min_payout' => $min_payout,
				'items' => $items,
			);
		} else {
			$error_text = 'no partner id';	
		}
	}	
	
	$json = array(
		'error' => $error,
		'error_text' => $error_text,
		'data' => $data,
	);
	echo pn_json_encode($json);
	exit;
	
}

add_action('userapi_v1_get_partner_links', 'the_userapi_v1_get_partner_links', 10, 2);
function the_userapi_v1_get_partner_links($ui, $api_login) {	
	global $wpdb, $premiumbox;	
	
	$data = array();
	$error = '1';
	$error_text = 'no active partner program';	
	
	if (function_exists('pp_caps')) {
		$user_id = intval(is_isset($ui,'ID'));
		if ($user_id > 0) {
			
			$error = '0';
			$error_text = '';			
			
			$start_time = intval(is_param_post('start_time'));
			$end_time = intval(is_param_post('end_time'));
			$search_ip = pn_sfilter(pn_strip_input(is_param_post('ip')));

			$where = '';
			if ($start_time > 0) {
				$start_date = date('Y-m-d H:i:s', $start_time);
				$where .= " AND pdate >= '$start_date'";
			}
			if ($end_time > 0) {
				$end_date = date('Y-m-d H:i:s', $end_time);
				$where .= " AND pdate <= '$end_date'";
			}
			if ($search_ip) {
				$where .= " AND pip = '$search_ip'";
			}
			$limit = intval(is_param_post('limit'));
			if ($limit < 1) { $limit = 100; } if ($limit > 500) { $limit = 500; }
			$offset = intval(is_param_post('offset'));
			if ($offset < 1) { $offset = 0; }
			
			$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "plinks WHERE user_id = '$user_id' $where ORDER BY pdate DESC LIMIT $offset, $limit");
			foreach ($items as $item) { 
				$now = array(
					'id' => $item->id,
					'time' => strtotime($item->pdate), 
					'date' => $item->pdate, 
					'browser' => pn_strip_input($item->pbrowser),
					'ip' => pn_strip_input($item->pip),
					'referrer' => pn_strip_input($item->prefer),
					'user_hash' => pn_strip_input($item->user_hash),
					'query_string' => pn_strip_input($item->query_string),
				);
				$data['items'][] = apply_filters('api_get_partner_links', $now, $item);
			}

		} else {
			$error_text = 'no partner id';	
		}
	}	
	
	$json = array(
		'error' => $error,
		'error_text' => $error_text,
		'data' => $data,
	); 
	echo pn_json_encode($json);
	exit;
	
}

add_action('userapi_v1_get_exchanges', 'the_userapi_v1_get_exchanges', 10, 2);
function the_userapi_v1_get_exchanges($ui, $api_login) {	
	global $wpdb, $premiumbox;	
	
	$data = array();
	$error = '0';
	$error_text = '';			
			
	$start_time = intval(is_param_post('start_time'));
	$end_time = intval(is_param_post('end_time'));
	$search_ip = pn_sfilter(pn_strip_input(is_param_post('ip')));

	$where = '';
	if ($start_time > 0) {
		$start_date = date('Y-m-d H:i:s', $start_time);
		$where .= " AND edit_date >= '$start_date'";
	}
	if ($end_time > 0) {
		$end_date = date('Y-m-d H:i:s', $end_time);
		$where .= " AND edit_date <= '$end_date'";
	}
	if ($search_ip) {
		$where .= " AND user_ip = '$search_ip'";
	}	

	$id = pn_strip_input(is_param_post('id'));
	$ids = array();
	if ($id) {
		$id_arr = explode(',', $id);
		$ids_db = create_data_for_db($id_arr, 'int');
		if ($ids_db) {
			$where .= " AND id IN($ids_db)";
		}
	}
	
	$hashed = is_bid_hash(is_param_post('hash'));
	if ($hashed) {
		$where .= " AND hashed = '$hashed'";
	}
	
	$api_id = pn_strip_input(is_param_post('api_id'));
	$api_ids = array();
	if ($api_id) {
		$api_id_arr = explode(',', $api_id);
		$api_id_db = create_data_for_db($api_id_arr, 'apiid');
		if ($api_id_db) {
			$where .= " AND id IN($api_id_db)";
		}
	}	

	$log_module = 0;
	$status_history = intval(is_param_post('status_history'));
	if ($status_history) {
		$query = $wpdb->query("CHECK TABLE ". $wpdb->prefix ."bid_logs");
		if (1 == $query) {
			$log_module = 1;
		}
	}
	
	$limit = intval(is_param_post('limit'));
	if ($limit < 1) { $limit = 100; } if ($limit > 500) { $limit = 500; }
	$offset = intval(is_param_post('offset'));
	if ($offset < 1) { $offset = 0; }			
				
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status != 'auto' AND api_login = '$api_login' $where ORDER BY edit_date DESC LIMIT $offset, $limit"); 
	foreach ($items as $item) { 
		$item_id = $item->id;
				
		$exchange_success = 0;
		if ('success' == $item->status) {
			$exchange_success = 1;
		}
					
		$statuses = array();
		if ($log_module) {
			$item_logs = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bid_logs WHERE bid_id = '$item_id'");
			foreach ($item_logs as $item_log) {
				$statuses[] = array(
					'time' => strtotime($item_log->createdate),
					'date' => $item_log->createdate,
					'status' => is_status_name($item_log->new_status),
				);
			}
		}
					
		$now = array(
			'id' => $item_id,
			'api_id' => pn_strip_input($item->api_id),
			'time' => strtotime($item->edit_date), 
			'date' => $item->edit_date, 
			'psys_give' => pn_strip_input(ctv_ml($item->psys_give)),
			'psys_get' => pn_strip_input(ctv_ml($item->psys_get)),
			'currency_code_give' => is_site_value($item->currency_code_give),
			'currency_code_get' => is_site_value($item->currency_code_get),
			'course_give' => is_sum($item->course_give),
			'course_get' => is_sum($item->course_get),
			'amount_give' => is_sum($item->sum1dc),
			'amount_get' => is_sum($item->sum2c),
			'exchange_success' => $exchange_success,
			'user_hash' => pn_strip_input($item->user_hash),
			'user_ip' => pn_strip_input($item->user_ip),
			'status' => $item->status,
			'hash' => $item->hashed,
		);
		if ($log_module) {
			$now['statuses'] = $statuses;
		}		
		$data['items'][] = apply_filters('api_get_partner_exchanges', $now, $item);
				
	}	
	
	$json = array(
		'error' => $error,
		'error_text' => $error_text,
		'data' => $data,
	);  
	echo pn_json_encode($json);
	exit;
	
}

add_action('userapi_v1_get_partner_exchanges', 'the_userapi_v1_get_partner_exchanges', 10, 2);
function the_userapi_v1_get_partner_exchanges($ui, $api_login) {	
	global $wpdb, $premiumbox;	
	
	$data = array();
	$error = '1';
	$error_text = 'no active partner program';	
	
	if (function_exists('pp_caps')) {
		$user_id = intval(is_isset($ui,'ID'));
		if ($user_id > 0) {
			
			$error = '0';
			$error_text = '';			
			
			$start_time = intval(is_param_post('start_time'));
			$end_time = intval(is_param_post('end_time'));
			$search_ip = pn_sfilter(pn_strip_input(is_param_post('ip')));

			$where = '';
			if ($start_time > 0) {
				$start_date = date('Y-m-d H:i:s', $start_time);
				$where .= " AND edit_date >= '$start_date'";
			} 
			if ($end_time > 0) {
				$end_date = date('Y-m-d H:i:s', $end_time);
				$where .= " AND edit_date <= '$end_date'";
			}
			if ($search_ip) {
				$where .= " AND user_ip = '$search_ip'";
			}	

			$id = pn_strip_input(is_param_post('id'));
			$ids = array();
			if ($id) {
				$id_arr = explode(',', $id);
				$ids_db = create_data_for_db($id_arr, 'int');
				if ($ids_db) {
					$where .= " AND id IN($ids_db)";
				}
			}
			
			$hashed = is_bid_hash(is_param_post('hash'));
			if ($hashed) {
				$where .= " AND hashed = '$hashed'";
			}			

			$log_module = 0;
			$status_history = intval(is_param_post('status_history'));
			if ($status_history) {
				$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . "bid_logs");
				if (1 == $query) {
					$log_module = 1;
				}
			}
			
			$limit = intval(is_param_post('limit'));
			if ($limit < 1) { $limit = 100; } if ($limit > 500) { $limit = 500; }
			$offset = intval(is_param_post('offset'));
			if ($offset < 1) { $offset = 0; }			
				
			$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE ref_id = '$user_id' AND status != 'auto' $where ORDER BY edit_date DESC LIMIT $offset, $limit");
			foreach ($items as $item) { 
				$item_id = $item->id;
				
				$exchange_success = 0;
				if ('success' == $item->status) {
					$exchange_success = 1;
				}
					
				$statuses = array();
				if ($log_module) {
					$item_logs = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bid_logs WHERE bid_id = '$item_id'");
					foreach ($item_logs as $item_log) {
						$statuses[] = array(
							'time' => strtotime($item_log->createdate),
							'date' => $item_log->createdate,
							'status' => is_status_name($item_log->new_status),
						);
					}
				}
					
				$now = array(
					'id' => $item_id,
					'time' => strtotime($item->edit_date), 
					'date' => $item->edit_date, 
					'psys_give' => pn_strip_input(ctv_ml($item->psys_give)),
					'psys_get' => pn_strip_input(ctv_ml($item->psys_get)),
					'currency_code_give' => is_site_value($item->currency_code_give),
					'currency_code_get' => is_site_value($item->currency_code_get),
					'course_give' => is_sum($item->course_give),
					'course_get' => is_sum($item->course_get),
					'amount_give' => is_sum($item->sum1dc),
					'amount_get' => is_sum($item->sum2c),
					'exchange_success' => $exchange_success,
					'accrued' => intval($item->pcalc),
					'partner_reward' => is_sum($item->partner_sum),
					'user_hash' => pn_strip_input($item->user_hash),
					'user_ip' => pn_strip_input($item->user_ip),
					'status' => $item->status,
					'hash' => $item->hashed,
				);
				if ($log_module) {
					$now['statuses'] = $statuses;
				}				
				$data['items'][] = apply_filters('api_get_partner_exchanges', $now, $item);
				
			}

		} else {
			$error_text = 'no partner id';	
		}
	}	
	
	$json = array(
		'error' => $error,
		'error_text' => $error_text,
		'data' => $data,
	);  
	echo pn_json_encode($json);
	exit;
	
}

add_action('userapi_v1_get_partner_payouts', 'the_userapi_v1_get_partner_payouts', 10, 2);
function the_userapi_v1_get_partner_payouts($ui, $api_login) {	
	global $wpdb, $premiumbox;	
	
	$data = array();
	$error = '1';
	$error_text = 'no active partner program';	
	
	if (function_exists('pp_caps')) {
		$user_id = intval(is_isset($ui,'ID'));
		if ($user_id > 0) {
			
			$error = '0';
			$error_text = '';			
			
			$start_time = intval(is_param_post('start_time'));
			$end_time = intval(is_param_post('end_time'));

			$where = '';
			if ($start_time > 0) {
				$start_date = date('Y-m-d H:i:s', $start_time);
				$where .= " AND pay_date >= '$start_date'";
			}
			if ($end_time > 0) {
				$end_date = date('Y-m-d H:i:s', $end_time);
				$where .= " AND pay_date <= '$end_date'";
			}
			
			$id = pn_strip_input(is_param_post('id'));
			$ids = array();
			if ($id) {
				$id_arr = explode(',', $id);
				$ids_db = create_data_for_db($id_arr, 'int');
				if ($ids_db) {
					$where .= " AND id IN($ids_db)";
				}
			}
			
			$limit = intval(is_param_post('limit'));
			if ($limit < 1) { $limit = 100; } if ($limit > 500) { $limit = 500; }
			$offset = intval(is_param_post('offset'));
			if ($offset < 1) { $offset = 0; }			

			$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "user_payouts WHERE auto_status = '1' AND user_id = '$user_id' $where ORDER BY pay_date DESC LIMIT $offset, $limit");
			foreach ($items as $item) { 
				$now = array(
					'id' => $item->id,
					'time' => strtotime($item->pay_date), 
					'date' => $item->pay_date, 
					'method_id' => intval($item->currency_id),
					'account' => pn_strip_input($item->pay_account),
					'pay_amount' => pn_strip_input($item->pay_sum),
					'pay_currency_code' => is_site_value($item->currency_code_title),
					'original_amount' => pn_strip_input($item->pay_sum_or),
					'original_currency_code' => cur_type(),
					'status' => pn_strip_input($item->status),
				);
				$data['items'][] = apply_filters('api_get_partner_payouts', $now, $item);
			}			

		} else {
			$error_text = 'no partner id';	
		}
	}	
	
	$json = array(
		'error' => $error,
		'error_text' => $error_text,
		'data' => $data,
	);  
	echo pn_json_encode($json);
	exit;
	
}

add_action('userapi_v1_add_partner_payout', 'the_userapi_v1_add_partner_payout', 10, 2);
function the_userapi_v1_add_partner_payout($ui, $api_login) {	
	global $wpdb, $premiumbox;	
	
	$data = array();
	$error = '1';
	$error_text = 'no active partner program';	
	
	if (function_exists('pp_caps')) {
		$user_id = intval(is_isset($ui,'ID'));
		if ($user_id > 0) {
			
			$error = '0';
			$error_text = '';			
			
			$currency_id = intval(is_param_post('method_id')); if ($currency_id < 1) { $currency_id = 0; }	
			$account = pn_strip_input(is_param_post('account'));

			$log = create_user_payout($currency_id, $account, $ui);
			if ('success' == $log['status']) {
				
				$item = is_isset($log, 'item');
				$data['payout_id'] = is_isset($item, 'id');
					
			} else {
				$error = 1; 
				$error_text = $log['status_text'];
			}			

		} else {
			$error_text = 'no partner id';	
		}
	}	
	
	$json = array(
		'error' => $error,
		'error_text' => $error_text,
		'data' => $data,
	);  
	echo pn_json_encode($json);
	exit;
	
}