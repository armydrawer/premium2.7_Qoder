<?php
if (!defined('ABSPATH')) { exit(); }

function list_bid_limit() {
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	
	$limit = get_user_meta($user_id, 'list_bid_limit', true);
	$limit = intval($limit);
	if ($limit < 1) { $limit = apply_filters('list_bid_limit_default', 10, $user_id); }
	$limit = intval($limit);
	if (isset($_GET['crazy'])) {
		$limit = 1;
	}	
	
	return $limit;
}		

add_action('premium_action_bids_filter_count', 'pn_premium_action_bids_filter_count');
function pn_premium_action_bids_filter_count() {
	
	_method('post');
	_json_head();
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error', 'pn');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	
	if (current_user_can('administrator') or current_user_can('pn_bids')) {
		
		$list_bid_limit = intval(is_param_post('count'));
		if ($list_bid_limit < 1) { $limit = apply_filters('list_bid_limit_default', 10, $user_id); }
		update_user_meta( $user_id, 'list_bid_limit', $list_bid_limit) or add_user_meta($user_id, 'list_bid_limit', $list_bid_limit, true);
		$log['status'] = 'success';
		
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Authorisation Error', 'pn');
	}
	
	echo pn_json_encode($log);	
	exit;
}

add_action('premium_action_bids_filter_change', 'pn_premium_action_bids_filter_change');
function pn_premium_action_bids_filter_change() {
	
	_method('post');
	_json_head(); 
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error','pn');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	if (current_user_can('administrator') or current_user_can('pn_bids')) {
	
		$lists = apply_filters('change_bids_filter_list', array());
		$lists = (array)$lists;
		$user_filter_change = array();
		foreach ($lists as $vn_list) {
			foreach ($vn_list as $key => $val) {
				$name = trim(is_isset($val, 'name'));
				$options = is_isset($val, 'options');
				$work = trim(is_isset($val, 'work'));
				if ($name) {
					$save_val = '';
					if ('input' == $work) {
						$urlen_val = urlencode(is_param_post($name));
						$save_val = pn_maxf_mb(pn_strip_input($urlen_val), 1000);
					} elseif ('int' == $work) {
						$urlen_val = urlencode(is_param_post($name));
						$save_val = intval($urlen_val);
					} elseif ('sum' == $work) {
						$urlen_val = urlencode(is_param_post($name));
						$save_val = is_sum($urlen_val);					
					} elseif ('options' == $work) {
						$urlen_val = is_param_post($name);
						$en_options = array();
						if (is_array($options)) {
							foreach ($options as $k => $v) {
								$en_options[] = $k;
							}
						}
						if (is_array($urlen_val)) {
							$save_val = array();
							foreach ($urlen_val as $va) {
								$va = urlencode($va);
								if (in_array($va, $en_options)) {
									$save_val[] = $va;
								}
							}
						} else {
							$save_val = '';
							$urlen_val = urlencode($urlen_val);
							if (in_array($urlen_val, $en_options)) {
								$save_val = $urlen_val;
							}
						}
					}
					$user_filter_change[$name] = $save_val;
				}
			}
		}
	
		update_user_meta($user_id, 'user_filter_change', $user_filter_change) or add_user_meta($user_id, 'user_filter_change', $user_filter_change, true);
	
		$log['status'] = 'success';
	
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Authorisation Error', 'pn');
	}
	
	echo pn_json_encode($log);	
	exit;
}

add_action('premium_action_bids_filter_restore', 'pn_premium_action_bids_filter_restore');
function pn_premium_action_bids_filter_restore() {
	
	_method('post');
	_json_head();  
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error', 'pn');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	if (current_user_can('administrator') or current_user_can('pn_bids')) {
	
		$data = array();
		$user_filter_change = get_user_meta($user_id, 'user_filter_change', true);
		$lists = apply_filters('change_bids_filter_list', array());
		$lists = (array)$lists;
		foreach ($lists as $vn_list) {
			foreach ($vn_list as $key => $val) {
				$name = trim(is_isset($val, 'name'));
				$options = is_isset($val, 'options');
				$work = trim(is_isset($val, 'work'));
				if ($name) {
					$urlen_val = is_isset($user_filter_change, $name);
					$urlen_val = urldecode($urlen_val);
					$save_val = '';
					if ('input' == $work) {
						$save_val = pn_maxf_mb(pn_strip_input($urlen_val), 1000);
					} elseif ('int' == $work) {
						$save_val = intval($urlen_val);
					} elseif ('sum' == $work) {
						$save_val = is_sum($urlen_val);					
					} elseif ('options' == $work) {
						$en_options = array();
						if (is_array($options)) {
							foreach ($options as $k => $v) {
								$en_options[] = $k;
							}
						}
						if (is_array($urlen_val)) {
							$save_val = array();
							foreach ($urlen_val as $va) {
								if (in_array($va, $en_options)) {
									$save_val[] = $va;
								}
							}
						} else {
							$save_val = '';
							if (in_array($urlen_val, $en_options)) {
								$save_val = $urlen_val;
							}
						}
					}
					if ($save_val) {
						$data[$name] = $save_val;
					}					
				}
			}
		}		
		
		$log['values'] = $data;
		$log['status'] = 'success';
		
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Authorisation Error', 'pn');
	}
	
	echo pn_json_encode($log);	
	exit;
}	

add_action('premium_action_bids_filter_html', 'pn_premium_action_bids_filter_html');
function pn_premium_action_bids_filter_html() {

	_method('post');
	_json_head(); 
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error', 'pn');	
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	if (current_user_can('administrator') or current_user_can('pn_bids')) {
	
		$url = urldecode(is_param_post('url'));
		$log['html'] = get_bids_html($url);
		$log['status'] = 'success';
		
	} else {
		$log['status'] = 'error';
		$log['status_text'] = 1;
		$log['status_text'] = __('Authorisation Error', 'pn');
	}
	
	echo pn_json_encode($log);	
	exit;
}

function bids_actions() {
	global $wpdb, $premiumbox;

	$action = get_request_action();
	if (isset($_POST['id']) and is_array($_POST['id'])) {
		
		$edit_date = current_time('mysql');
	
		if (current_user_can('administrator') or current_user_can('pn_bids_delete')) {
			if ('realdelete' == $action) { 
				foreach ($_POST['id'] as $id) {
					$id = intval($id); 
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$id'");
					if (isset($item->id)) {
						$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$id'");
						if (1 == $result) {
							
							$wpdb->query("DELETE FROM " . $wpdb->prefix . "bids_meta WHERE item_id = '$id'");						
							$old_status = $item->status;

							$ch_data = array(
								'bid' => $item,
								'set_status' => 'realdelete',
								'place' => 'admin_panel',
								'who' => 'user',
								'old_status' => $old_status,
								'direction' => ''
							);
							_change_bid_status($ch_data);
 	 						
						}
					}
				}	 
			}
		}	

		if (current_user_can('administrator') or current_user_can('pn_bids_change')) {
			
			$sts = array();
			$bid_status_list = list_bid_status();
			if (is_array($bid_status_list)) {
				foreach ($bid_status_list as $bsl_key => $bsl_val) {
					$sts[] = $bsl_key;
				}
			}
			if (in_array($action, $sts)) {
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$id' AND status != '$action'");
					if (isset($item->id)) {
						$arr = array('status' => $action, 'edit_date' => $edit_date);
						$result = $wpdb->update($wpdb->prefix . 'exchange_bids', $arr, array('id' => $id));
						if (1 == $result) {
							
							$old_status = $item->status;
							$item = pn_object_replace($item, $arr);
							
							$ch_data = array(
								'bid' => $item,
								'set_status' => $action,
								'place' => 'admin_panel',
								'who' => 'user',
								'old_status' => $old_status,
								'direction' => ''
							);
							_change_bid_status($ch_data);							

						}
					}
				}
			}	
			
			do_action('bidstatus_admin_action', $_POST['id'], $action);
		}
	}
}

add_action('premium_action_bids_action_ajax', 'pn_premium_action_bids_action_ajax');
function pn_premium_action_bids_action_ajax() {
	
	_method('post');
	_json_head(); 
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error','pn');
	 
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	if (current_user_can('administrator') or current_user_can('pn_bids')) {
	
		$param = trim(is_param_post('_referrer'));

		bids_actions();

		$log['html'] = get_bids_html($param);
		$log['status'] = 'success';
		
	} else {
		$log['status'] = 'error';
		$log['status_text'] = 1;
		$log['status_text'] = __('Authorisation Error', 'pn');
	}	
	
	echo pn_json_encode($log);
	exit;
}	

add_filter('change_bids_filter_list', 'def_change_bids_filter_list', 0);
function def_change_bids_filter_list($lists) {
	global $wpdb;
	
	/*********/
	$options = array(
		'0' => '--' . __('All', 'pn') . '--',
		'1' => __('Except paid orders', 'pn'),
		'2' => __('Paid orders', 'pn'),
	);
	$lists['status']['paystatus'] = array(
		'title' => __('Payment status', 'pn'),
		'name' => 'paystatus',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);
	/*********/
		
	/*********/
	$options = array(
		'0' => '--' . __('All', 'pn') . '--',
		'1' => __('Exact amount', 'pn'),
		'2' => __('Overpayment', 'pn'),
	);		
	$lists['status']['exceed_pay'] = array(
		'title' => __('Amount of payment via merchant', 'pn'),
		'name' => 'exceed_pay',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);

	$statused = list_bid_status();
	if (!is_array($statused)) { $statused = array(); }
	
	$lists['status']['bidstatus'] = array(
		'title' => __('Status of order', 'pn'),
		'name' => 'bidstatus',
		'options' => $statused,
		'view' => 'multi',
		'work' => 'options',
	);
	$lists['status']['status_clear1'] = array(
		'view' => 'clear',
	);	
	/*********/		
		
	/*********/
	$lists['sum']['bidid'] = array(
		'title' => __('Order ID', 'pn'),
		'name' => 'bidid',
		'view' => 'input',
		'work' => 'int',
	);
	/*********/		
		
	/*********/
	$lists['sum']['startdate'] = array(
		'title' => __('Start date', 'pn'),
		'name' => 'startdate',
		'view' => 'date',
		'work' => 'input',
	);
	/*********/			

	/*********/
	$lists['sum']['enddate'] = array(
		'title' => __('End date', 'pn'),
		'name' => 'enddate',
		'view' => 'date',
		'work' => 'input',
	);
	/*********/	
		
	/*********/
	$lists['sum']['min_sum1'] = array(
		'title' => __('Min. amount Giving', 'pn'),
		'name' => 'min_sum1',
		'view' => 'input',
		'work' => 'sum',
	);
	/*********/		

	/*********/
	$lists['sum']['min_sum2'] = array(
		'title' => __('Min. amount Receiving', 'pn'),
		'name' => 'min_sum2',
		'view' => 'input',
		'work' => 'sum',
	);
	/*********/

	/*********/
	$options = array(
		'0' => '--' . __('All', 'pn') . '--',
	);
	$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions ORDER BY site_order1 ASC");
	foreach ($directions as $nap) { 
		$options[$nap->id]= pn_strip_input($nap->tech_name) . pn_item_status($nap, 'direction_status', array('0' => __('inactive direction', 'pn'), '2' => __('hold direction', 'pn'))) . pn_item_basket($nap); 
	}
				
	$lists['currency']['direction_id'] = array(
		'title' => __('Exchange direction', 'pn'),
		'name' => 'direction_id',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);
	/*********/		

	/*********/
	$currencies = list_currency(__('All', 'pn'));
	
	$options = array();
	foreach ($currencies as $key => $curr) { 
		$options[$key] = $curr;
	}
			
	$lists['currency']['v1'] = array(
		'title' => __('Currency name Giving', 'pn'),
		'name' => 'v1',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);

	$lists['currency']['v2'] = array(
		'title' => __('Currency name Receiving', 'pn'),
		'name' => 'v2',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);
	/*********/

	/*********/
	$psys = list_psys(__('All', 'pn'));
	$options = array();
	foreach ($psys as $ps_key => $ps_title) { 
		$options[$ps_key] = $ps_title;
	}
		
	$lists['currency']['psys1'] = array(
		'title' => __('PS name Giving', 'pn'),
		'name' => 'psys1',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);

	$lists['currency']['psys2'] = array(
		'title' => __('PS name Receiving', 'pn'),
		'name' => 'psys2',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',	
	);
	/*********/

	/*********/
	$vtype = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency_codes ORDER BY currency_code_title ASC");
	$options = array(
		'0' => '--' . __('All', 'pn') . '--',
	);
	foreach ($vtype as $item) { 
		$options[$item->id] = is_site_value($item->currency_code_title) . pn_item_basket($item);
	}
			
	$lists['currency']['vtype1'] = array(
		'title' => __('Currency code Giving', 'pn'),
		'name' => 'vtype1',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);

	$lists['currency']['vtype2'] = array(
		'title' => __('Currency code Receiving', 'pn'),
		'name' => 'vtype2',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);
	/*********/				
		
	/*********/	
	$lists['user']['iduser'] = array(
		'title' => __('User ID', 'pn'),
		'name' => 'iduser',
		'view' => 'input',
		'work' => 'int',
	);
	/*********/		
		
	/*********/		
	$lists['user']['user_login'] = array(
		'title' => __('User login', 'pn'),
		'name' => 'user_login',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/		
		
	/*********/
	$lists['user']['user_email'] = array(
		'title' => __('E-mail', 'pn'),
		'name' => 'user_email',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/
			
	/*********/	
	$lists['user']['user_skype'] = array(
		'title' => __('User skype', 'pn'),
		'name' => 'user_skype',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/
	
	/*********/	
	$lists['user']['user_telegram'] = array(
		'title' => __('User telegram', 'pn'),
		'name' => 'user_telegram',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/	

	/*********/	
	$lists['user']['user_phone'] = array(
		'title' => __('Mobile phone number', 'pn'),
		'name' => 'user_phone',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/
			
	/*********/	
	$lists['user']['user_passport'] = array(
		'title' => __('User passport number', 'pn'),
		'name' => 'user_passport',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/

	/*********/	
	$lists['user']['user_ip'] = array(
		'title' => __('User IP', 'pn'),
		'name' => 'user_ip',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/

	/*********/	
	$lists['user']['user_agent'] = array(
		'title' => __('User agent', 'pn'),
		'name' => 'user_agent',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/	

	$lists['user']['first_name'] = array(
		'title' => __('First name', 'pn'),
		'name' => 'first_name',
		'view' => 'input',
		'work' => 'input',
	); 
	
	$lists['user']['last_name'] = array(
		'title' => __('Last name', 'pn'),
		'name' => 'last_name',
		'view' => 'input',
		'work' => 'input',
	); 
	
	$lists['user']['second_name'] = array(
		'title' => __('Second name', 'pn'),
		'name' => 'second_name',
		'view' => 'input',
		'work' => 'input',
	); 

	/*********/		
	$lists['user']['ac1'] = array(
		'title' => __('Account To send', 'pn'),
		'name' => 'ac1',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/

	/*********/		
	$lists['user']['ac2'] = array(
		'title' => __('Account To receive', 'pn'),
		'name' => 'ac2',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/	
		
	/*********/		
	$lists['other']['to_account'] = array(
		'title' => __('Merchant account', 'pn'),
		'name' => 'to_account',
		'view' => 'input',
		'work' => 'input',
	);
		
	$lists['other']['from_account'] = array(
		'title' => __('Automatic payout account', 'pn'),
		'name' => 'from_account',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/

	/*********/		
	$lists['other']['dest_tag'] = array(
		'title' => __('Destination tag', 'pn'),
		'name' => 'dest_tag',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/

	/*********/		
	$lists['other']['trans_in'] = array(
		'title' => __('Merchant transaction ID', 'pn'),
		'name' => 'trans_in',
		'view' => 'input',
		'work' => 'input',
	);
		
	$lists['other']['trans_out'] = array(
		'title' => __('Auto payout transaction ID', 'pn'),
		'name' => 'trans_out',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/	
	
	/*********/		
	$lists['other']['txid_in'] = array(
		'title' => __('Merchant TxID', 'pn'),
		'name' => 'txid_in',
		'view' => 'input',
		'work' => 'input',
	);
		
	$lists['other']['txid_out'] = array(
		'title' => __('Auto payout TxID', 'pn'),
		'name' => 'txid_out',
		'view' => 'input',
		'work' => 'input',
	);
	/*********/		

	$lists['other']['pay_ac'] = array(
		'title' => __('Real account', 'pn'),
		'name' => 'pay_ac',
		'view' => 'input',
		'work' => 'input',
	);	

	/*********/
	if (is_ml()) {
		$options = array(
			'0' => '--' . __('All', 'pn') . '--',
		);
		$langs = get_langs_ml();
		foreach ($langs as $key) {
			$options[$key] = get_title_forkey($key);
		}
				
		$lists['other']['lan'] = array(
			'title' => __('Language', 'pn'),
			'name' => 'lan',
			'options' => $options,
			'view' => 'select',
			'work' => 'options',
		);
	}
	/*********/	
	
	$lists['system']['onematch'] = array(
		'title' => __('Filter orders if at least one match is found', 'pn'),
		'name' => 'onematch',
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'view' => 'select',
		'work' => 'options',
	);	
	
	return $lists;
}

function get_bids_html($url, $page_ind = 0) {
	global $wpdb, $premiumbox;	

	$temp = '';
	$page_ind = intval($page_ind);
	
	if (current_user_can('administrator') or current_user_can('pn_bids')) {

		$pr = $wpdb->prefix;
		
		parse_str($url, $pars_data);

		$where = '';
		$sql_operator = is_sql_operator($pars_data);
		
		$paystatus = intval(is_isset($pars_data, 'paystatus'));
		if (1 == $paystatus) { 
			$where = " {$sql_operator} {$pr}exchange_bids.status IN('new','coldnew','cancel','delete','techpay','error','scrpayerror','payouterror','my','coldpay','coldsuccess','success')";
		} elseif (2 == $paystatus) {
			$where = " {$sql_operator} {$pr}exchange_bids.status IN('payed','realpay','verify')";
		}
		
		$bidstatus = is_isset($pars_data, 'bidstatus');
		if (is_array($bidstatus)) { 
			$where = " {$sql_operator} {$pr}exchange_bids.status = '1'";
			if (count($bidstatus) > 0) {
				$in_bs = array();
				foreach ($bidstatus as $bs) {
					$bs = is_status_name($bs);
					if ($bs) {
						$in_bs[] = "'". $bs ."'";
					}
				}
				if (count($in_bs) > 0) {
					$in_bs_join = implode(',', $in_bs);
					$where = " {$sql_operator} {$pr}exchange_bids.status IN($in_bs_join)";
				}
			}
		} else {
			$bidstatus = is_status_name($bidstatus);
			if ($bidstatus) {
				$where = " {$sql_operator} {$pr}exchange_bids.status = '$bidstatus'";
			}
		}
		
		$startdate = is_pn_date(is_isset($pars_data, 'startdate'));
		if ($startdate) {
			$startdate = get_pn_date($startdate, 'Y-m-d 00:00');
			$where .= " {$sql_operator} {$pr}exchange_bids.edit_date >= '$startdate'";
		}
		$enddate = is_pn_date(is_isset($pars_data, 'enddate'));
		if ($enddate) {
			$enddate = get_pn_date($enddate, 'Y-m-d 00:00');
			$where .= " {$sql_operator} {$pr}exchange_bids.edit_date <= '$enddate'";
		}
		$min_sum1 = is_sum(is_isset($pars_data, 'min_sum1'));
		if ($min_sum1 > 0) {
			$where .= " {$sql_operator} {$pr}exchange_bids.sum1dc >= ('$min_sum1' -0.0)";
		}
		$min_sum2 = is_sum(is_isset($pars_data, 'min_sum2'));
		if ($min_sum2 > 0) {
			$where .= " {$sql_operator} {$pr}exchange_bids.sum2c >= ('$min_sum2' -0.0)";
		}
		$direction_id = intval(is_isset($pars_data, 'direction_id'));
		if ($direction_id > 0) {
			$where .= " {$sql_operator} {$pr}exchange_bids.direction_id = '$direction_id'";
		}		
		$v1 = intval(is_isset($pars_data, 'v1'));
		if ($v1 > 0) {
			$where .= " {$sql_operator} {$pr}exchange_bids.currency_id_give = '$v1'";
		}
		$v2 = intval(is_isset($pars_data, 'v2'));
		if ($v2 > 0) {
			$where .= " {$sql_operator} {$pr}exchange_bids.currency_id_get = '$v2'";
		}
		$psys1 = intval(is_isset($pars_data, 'psys1'));
		if ($psys1 > 0) {
			$where .= " {$sql_operator} {$pr}exchange_bids.psys_id_give = '$psys1'";
		}
		$psys2 = intval(is_isset($pars_data, 'psys2'));
		if ($psys2 > 0) {
			$where .= " {$sql_operator} {$pr}exchange_bids.psys_id_get = '$psys2'";
		}
		$vtype1 = intval(is_isset($pars_data, 'vtype1'));
		if ($vtype1 > 0) {
			$where .= " {$sql_operator} {$pr}exchange_bids.currency_code_id_give = '$vtype1'";
		}		
		$vtype2 = intval(is_isset($pars_data, 'vtype2'));
		if ($vtype2 > 0) {
			$where .= " {$sql_operator} {$pr}exchange_bids.currency_code_id_get = '$vtype2'";
		}
		$iduser = intval(is_isset($pars_data, 'iduser'));
		if ($iduser > 0) {
			$where .= " {$sql_operator} {$pr}exchange_bids.user_id='$iduser'";
		} 
		$user_login = is_user(is_isset($pars_data, 'user_login'));
		if ($user_login) {
			$where .= " {$sql_operator} {$pr}exchange_bids.user_login LIKE '%$user_login%'";
		}
		$user_email = is_email(is_isset($pars_data, 'user_email'));
		if ($user_email) {
			$where .= " {$sql_operator} {$pr}exchange_bids.user_email LIKE '%$user_email%'";
		}		
		$user_skype = pn_strip_input(pn_sfilter(is_isset($pars_data, 'user_skype')));
		if ($user_skype) {
			$where .= " {$sql_operator} {$pr}exchange_bids.user_skype LIKE '%$user_skype%'";
		}
		$first_name = pn_strip_input(pn_sfilter(is_isset($pars_data, 'first_name')));
		if ($first_name) {
			$where .= " {$sql_operator} {$pr}exchange_bids.first_name LIKE '%$first_name%'";
		}
		$last_name = pn_strip_input(pn_sfilter(is_isset($pars_data, 'last_name')));
		if ($last_name) {
			$where .= " {$sql_operator} {$pr}exchange_bids.last_name LIKE '%$last_name%'";
		}
		$second_name = pn_strip_input(pn_sfilter(is_isset($pars_data, 'second_name')));
		if ($second_name) {
			$where .= " {$sql_operator} {$pr}exchange_bids.second_name LIKE '%$second_name%'";
		}
		$user_telegram = pn_strip_input(pn_sfilter(is_isset($pars_data, 'user_telegram')));
		if ($user_telegram) {
			$where .= " {$sql_operator} {$pr}exchange_bids.user_telegram LIKE '%$user_telegram%'";
		}		
		$user_phone = pn_strip_input(pn_sfilter(is_isset($pars_data, 'user_phone')));
		if ($user_phone) {
			$where .= " {$sql_operator} {$pr}exchange_bids.user_phone LIKE '%$user_phone%'";
		}
		$user_passport = pn_strip_input(pn_sfilter(is_isset($pars_data, 'user_passport')));
		if ($user_passport) {
			$where .= " {$sql_operator} {$pr}exchange_bids.user_passport LIKE '%$user_passport%'";
		}		
		$user_ip = pn_strip_input(pn_sfilter(is_isset($pars_data, 'user_ip')));
		if ($user_ip) {
			$where .= " {$sql_operator} {$pr}exchange_bids.user_ip LIKE '%$user_ip%'";
		}
		$user_agent = pn_strip_input(pn_sfilter(is_isset($pars_data, 'user_agent')));
		if ($user_agent) {
			$where .= " {$sql_operator} {$pr}exchange_bids.user_agent LIKE '%$user_agent%'";
		}		
		$lan = is_lang_attr(is_isset($pars_data, 'lan'));
		if ($lan) {
			$where .= " {$sql_operator} {$pr}exchange_bids.bid_locale = '$lan'";
		}		
		$ac1 = pn_strip_input(pn_sfilter(is_isset($pars_data, 'ac1')));
		if ($ac1) {
			$where .= " {$sql_operator} {$pr}exchange_bids.account_give LIKE '%$ac1%'";
		}		
		$ac2 = pn_strip_input(pn_sfilter(is_isset($pars_data, 'ac2')));
		if ($ac2) {
			$where .= " {$sql_operator} {$pr}exchange_bids.account_get LIKE '%$ac2%'";
		}
		$to_account = pn_strip_input(pn_sfilter(is_isset($pars_data, 'to_account')));
		if ($to_account) {
			$where .= " {$sql_operator} {$pr}exchange_bids.to_account LIKE '%$to_account%'";
		}
		$from_account = pn_strip_input(pn_sfilter(is_isset($pars_data, 'from_account')));
		if ($from_account) {
			$where .= " {$sql_operator} {$pr}exchange_bids.from_account LIKE '%$from_account%'";
		}
		$dest_tag = pn_strip_input(pn_sfilter(is_isset($pars_data, 'dest_tag')));
		if ($dest_tag) {
			$where .= " {$sql_operator} {$pr}exchange_bids.dest_tag LIKE '%$dest_tag%'";
		}
		$trans_in = pn_strip_input(pn_sfilter(is_isset($pars_data, 'trans_in')));
		if ($trans_in) {
			$where .= " {$sql_operator} {$pr}exchange_bids.trans_in = '$trans_in'";
		}
		$trans_out = pn_strip_input(pn_sfilter(is_isset($pars_data, 'trans_out')));
		if ($trans_out) {
			$where .= " {$sql_operator} {$pr}exchange_bids.trans_out = '$trans_out'";
		}
		$txid_in = pn_strip_input(pn_sfilter(is_isset($pars_data, 'txid_in')));
		if ($txid_in) {
			$where .= " {$sql_operator} {$pr}exchange_bids.txid_in = '$txid_in'";
		}
		$txid_out = pn_strip_input(pn_sfilter(is_isset($pars_data, 'txid_out')));
		if ($txid_out) {
			$where .= " {$sql_operator} {$pr}exchange_bids.txid_out = '$txid_out'";
		}		
		$pay_ac = pn_strip_input(pn_sfilter(is_isset($pars_data, 'pay_ac')));
		if ($pay_ac) {
			$where .= " {$sql_operator} {$pr}exchange_bids.pay_ac = '$pay_ac'";
		}		
		
		$exceed_pay = intval(is_isset($pars_data, 'exceed_pay'));  
		if (1 == $exceed_pay) {
			$where .= " {$sql_operator} {$pr}exchange_bids.exceed_pay = '0'";
		} elseif (2 == $exceed_pay) {	
			$where .= " {$sql_operator} {$pr}exchange_bids.exceed_pay = '1'";
		}		
		
		$bidid = intval(is_isset($pars_data, 'bidid'));
		if ($bidid > 0) {
			$where = " {$sql_operator} {$pr}exchange_bids.id='$bidid'";
		}
		
		$where = apply_filters('where_request_sql_bids', $where, $pars_data);
		
		$paged = intval(is_isset($pars_data, 'page_num'));
	
		$limit = list_bid_limit();
		
		$url_new = admin_url('admin.php?page=pn_bids&') . $url;
		if ($page_ind) {
			$url_new = rtrim(get_site_url(), '/') . $url; 
		}

		$ui = wp_get_current_user();
		$mini_navi = intval(is_isset($ui, 'mini_navi'));
		if (1 == $mini_navi) {
			$count_bids = 0;
			$pagenavi = get_pagenavi_calc($limit, $paged, '-1');
		} else {
			$count_bids = $wpdb->get_var("SELECT COUNT({$pr}exchange_bids.id) FROM {$pr}exchange_bids WHERE status != 'auto' $where");
			$pagenavi = get_pagenavi_calc($limit, $paged, $count_bids);
		}
		
		$select = apply_filters('select_sql_bids', '');
			
		$sql = "SELECT *, {$pr}exchange_bids.id AS bid_id {$select} FROM {$pr}exchange_bids WHERE status != 'auto' $where ORDER BY id DESC LIMIT {$pagenavi['offset']}, {$pagenavi['limit']}";
			
		$statused = get_statusbids_for_admin();

		$datablock = '
		<div class="bids_datablock">
		';
			
			$data_blocks = array();
				
			if (current_user_can('administrator') or current_user_can('pn_bids_change')) {
				
				$data_blocks['check'] = '
				<div class="bids_action_check">
					<input type="checkbox" name="" class="check_all" autocomplete="off" value="1" />
				</div>				
				';					
					
				$data_blocks['actions'] = '
				<div class="bids_action_select">
					<select name="action" class="sel_action" autocomplete="off">
						<option value="0">' . __('Actions', 'pn') . '</option>';
						
						foreach ($statused as $key => $data) {
							$style = '';
							$title = $data['title'];
							$background = trim(is_isset($data, 'background'));
							$color = trim(is_isset($data, 'color'));
							if ($background) {
								$style .= 'background: ' . $background . ';';
							}
							if ($color) {
								$style .= 'color: ' . $color . ';';
							}								
									
							$data_blocks['actions'] .= '<option value="' . $key . '" style="' . $style . '">' . $title . '</option>';
						}
						
					$data_blocks['actions'] .= '
					</select>
				</div>				
				';						
				$data_blocks['apply'] = '
				<input type="submit" name="submit" formtarget="_top" class="bids_action_apply js_bids_action" value="' . __('Apply', 'pn') . '" />
				';
			}
				
			$data_blocks['loader'] = '
			<div class="apply_loader"></div>
			';				
			$data_blocks['pagenavi'] = '
			<div class="bids_pagenavi">';

				$data_blocks['pagenavi'] .= get_pagenavi($pagenavi, 'notstandart', $url_new, 'page_num');

			$data_blocks['pagenavi'] .='
				<div class="premium_clear"></div>
			</div>
			';				
			if (1 != $mini_navi) {
				$data_blocks['total'] = '
				<div class="bids_datablock_count">
					<strong>' . __('Total orders', 'pn') . '</strong>: ' . $count_bids . '
				</div>
				';					
			}
			$data_blocks = apply_filters('bids_datablock', $data_blocks);
			if (is_array($data_blocks)) {
				foreach ($data_blocks as $db) {
					$datablock .= $db;
				}
			}	

		$datablock .='	
			<div class="premium_clear"></div>
		</div>';
			
		$temp .= $datablock;
			
		$cl = '';
			
		if ($count_bids > 0 or 1 == $mini_navi) {
			$v = get_currency_data();
			$cl = 'style="display: none;"';
			$items = $wpdb->get_results($sql);
			foreach ($items as $item) { 
				$temp .= apply_filters('get_bid_item', '', $item, $v);
			}
		}
			
		$temp .= '<div class="nobids" id="nobids" ' . $cl . '>' . __('No orders', 'pn') . '</div>';			
			
		$temp .= $datablock; 
		
		if (is_debug_mode()) { 
			$temp .= '<div class="tech_url">' . $url_new . '<hr />' . $sql . '</div>';
		}
	}
	
	return $temp;
} 

add_filter('get_bid_item','def_get_bid_item', 0, 3);
function def_get_bid_item($temp, $item, $v){
	global $wpdb, $premiumbox;

	if (!is_object($item)) { return __('No object', 'pn'); }

	$temp = '';
	
	$bid_id = $item->bid_id;
	
	$podmena = is_substitution($item);		
	
	$locale = pn_strip_input($item->bid_locale);		
	
	$dmetas = @unserialize($item->dmetas);
	$metas = @unserialize($item->metas);
	
	$direction_id = intval(is_isset($item, 'direction_id'));
	$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$direction_id'");
	
	$temp = '
	<div class="one_bids" id="bidid_' . $bid_id . '">
		<div class="one_bids_wrap">';
		
			$temp .= '
			<div class="one_bids_abs">';
				
				$onebid_icon = array(
					'checkbox' => array(
						'type' => 'checkbox',
						'checked' => '',
						'disabled' => '',
					),
				);
				if (count($podmena) > 0) {
					$onebid_icon['substitution'] = array(
						'type' => 'label',
						'title' => __('Attention! User details were spoofed', 'pn'),
						'image' => $premiumbox->plugin_url . 'images/podmena.gif',
					);
				}	
				$onebid_icon['bid_id'] = array(
					'type' => 'text',
					'title' => __('ID', 'pn'),
					'label' => __('ID', 'pn') . ': ' . $item->id,
					'link' => get_bids_url($item->hashed),
					'link_target' => '_blank',
				);				
				if (is_ml()) {
					$onebid_icon['language'] = array(
						'type' => 'label',
						'title' => __('Language', 'pn') . ': ' . get_title_forkey($locale),
						'image' => get_lang_icon($locale),
					);	
				}
				$onebid_icon = apply_filters('onebid_icons', $onebid_icon, $item, $v, $direction); 
				$onebid_icon = (array)$onebid_icon;
				
				$temp .= _onebid_thead_temp($onebid_icon, $item, $v, $direction);
			
				$temp .= '
				<div class="premium_clear"></div>
			</div>
				<div class="premium_clear"></div>
			';	 			
			
			$temp .= '
			<div class="one_bids_ins">			
			';
			
				$temp .= '
				<div class="bids_col">';
				
					$cols = array();
					$cols['status'] = array(
						'type' => 'text',
						'title' => '',
						'label' => '<span class="stname st_' . is_status_name($item->status) . '">' . get_bid_status($item->status) . '</span><div class="premium_clear"></div>',
					);
					$cols['rate'] = array(
						'type' => 'text',
						'title' => __('Rate', 'pn'),
						'label' => is_sum($item->course_give) . ' ' . pn_strip_input($item->currency_code_give) . ' = ' . is_sum($item->course_get) . ' ' . pn_strip_input($item->currency_code_get),
						'link' => admin_url('admin.php?page=pn_add_directions&item_id=' . $item->direction_id),
						'link_target' => '_blank',
					);
					$cols['createdate'] = array(
						'type' => 'text',
						'title' => __('Creation date', 'pn'),
						'label' => get_pn_time($item->create_date, 'd.m.Y H:i:s'),
					);
					$cols['editdate'] = array(
						'type' => 'text',
						'title' => __('Modification date', 'pn'),
						'label' => get_pn_time($item->edit_date, 'd.m.Y H:i:s'),
					);	
					
					$m_in = trim($item->m_in);
					if ($m_in) {
						$cols['merch'] = array(
							'type' => 'text',
							'title' => __('Merchant', 'pn'),
							'label' => $m_in,
						);			
					}					
					
					$to_account = pn_strip_input($item->to_account);
					if ($to_account) {
						$cols['to_account'] = array(
							'type' => 'text',
							'title' => __('Merchant account', 'pn'),
							'label' => $to_account,
							'copy' => $to_account,
							'sec' => 'to_account',
						);
					}
					
					$dest_tag = pn_strip_input($item->dest_tag);
					if ($dest_tag) {
						$cols['dest_tag'] = array(
							'type' => 'text',
							'title' => __('Destination tag', 'pn'),
							'label' => $dest_tag,
							'copy' => $dest_tag,
							'sec' => 'dest_tag',
						);
					}

					$from_account = pn_strip_input($item->from_account);
					if ($from_account) {
						$cols['from_account'] = array(
							'type' => 'text',
							'title' => __('Automatic payout account', 'pn'),
							'label' => $from_account,
							'copy' => $from_account,
							'sec' => 'from_account',
						);
					}					
					
					$trans_in = pn_strip_input($item->trans_in);
					if ($trans_in) {
						$cols['trans_in'] = array(
							'type' => 'text',
							'title' => __('Merchant transaction ID', 'pn'),
							'label' => $trans_in,
							'copy' => $trans_in,
							'sec' => 'trans_in',
						);
					}

					$txid_in = pn_strip_input($item->txid_in);
					if ($txid_in) {
						$cols['txid_in'] = array(
							'type' => 'text',
							'title' => __('Merchant TxID', 'pn'),
							'label' => $txid_in,
							'copy' => $txid_in,
							'sec' => 'txid_in',
						);
					}

					$trans_out = pn_strip_input($item->trans_out);
					if ($trans_out) {
						$cols['trans_out'] = array(
							'type' => 'text',
							'title' => __('Auto payout transaction ID', 'pn'),
							'label' => $trans_out,
							'copy' => $trans_out,
							'sec' => 'trans_out',
						);
					}					
											
					$txid_out = pn_strip_input($item->txid_out);
					if ($txid_out) {
						$cols['txid_out'] = array(
							'type' => 'text',
							'title' => __('Auto payout TxID', 'pn'),
							'label' => $txid_out,
							'copy' => $txid_out,
							'sec' => 'txid_out',
						);
					}						
				
					$pay_sum = is_sum($item->pay_sum);
					if ($pay_sum) {
						$cols['pay_sum'] = array(
							'type' => 'text',
							'title' => __('Real amount to pay', 'pn'),
							'label' => $pay_sum,
							'copy' => $pay_sum,
							'sec' => 'pay_sum',
						);
					}

					$pay_ac = pn_strip_input($item->pay_ac);
					if ($pay_ac) {
						$cols['pay_ac'] = array(
							'type' => 'text',
							'title' => __('Real account', 'pn'),
							'label' => $pay_ac,
							'copy' => $pay_ac,
							'sec' => 'pay_ac',
						);
					}					

					$out_sum = is_sum($item->out_sum);
					if ($out_sum) {
						$cols['out_sum'] = array(
							'type' => 'text',
							'title' => __('Real amount to auto payout', 'pn'),
							'label' => $out_sum,
							'copy' => $out_sum,
							'sec' => 'out_sum',
						);
					}						
					$temp .= _onebid_col_temp($cols, 'onebid_col1', $item, $v, $direction);										
					
					$temp .='
					<div class="premium_clear"></div>
				</div>
					<div class="abs_line al1"></div>
				';

				$temp .='
				<div class="bids_col">';
				
					$cols = array();
					$cols['currency_give'] = array(
						'type' => 'text',
						'title' => __('Send', 'pn'),
						'label' => pn_strip_input(ctv_ml($item->psys_give)) . ' ' . pn_strip_input($item->currency_code_give),
						'copy' => pn_strip_input(ctv_ml($item->psys_give)) . ' ' . pn_strip_input($item->currency_code_give),
					);	
					$cols['sum1dc'] = array(
						'type' => 'text',
						'title' => __('Amount (with add. fees)', 'pn'),
						'label' => is_sum($item->sum1dc) . ' ' . pn_strip_input($item->currency_code_give),
						'copy' => is_sum($item->sum1dc),
						'sec' => 'sum1dc',
						'class_wrap' => 'btbg_green',
					);
					$cols['sum1c'] = array(
						'type' => 'text',
						'title' => __('Amount (with add. fees and PS fees)', 'pn'),
						'label' => is_sum($item->sum1c) . ' ' . pn_strip_input($item->currency_code_give),
						'copy' => is_sum($item->sum1c),
						'sec' => 'sum1c',
					);	
					
					$account_give = pn_strip_input($item->account_give);
					if ($account_give) {
						$cols['account_give'] = array(
							'type' => 'text',
							'title' => __('From account', 'pn'),
							'label' => $account_give,
							'copy' => $account_give,
							'sec' => 'account_give',
							'class_wrap' => 'btbg_fiol',
						);
					}					
					if (isset($dmetas[1]) and is_array($dmetas[1])) {
						foreach ($dmetas[1] as $key => $value) {			
							$title = pn_strip_input(ctv_ml(is_isset($value, 'title')));
							$data = pn_strip_input(is_isset($value, 'data'));
							if ($data) {
								$cols['meta_give' . $key] = array(
									'type' => 'text',
									'title' => $title,
									'label' => $data,
									'copy' => $data,
								);								
							}
						}
					}							
					$temp .= _onebid_col_temp($cols, 'onebid_col2', $item, $v, $direction);						
						
					$temp .='
					<div class="premium_clear"></div>
				</div>
				<div class="abs_line al2"></div>
				';
				
				$temp .='
				<div class="bids_col">';
				
					$cols = array();
					$cols['currency_get'] = array(
						'type' => 'text',
						'title' => __('Receive', 'pn'),
						'label' => pn_strip_input(ctv_ml($item->psys_get)) . ' ' . pn_strip_input($item->currency_code_get),
						'copy' => pn_strip_input(ctv_ml($item->psys_get)) . ' ' . pn_strip_input($item->currency_code_get),
					);						
					$cols['sum2dc'] = array(
						'type' => 'text',
						'title' => __('Amount (with add. fees)', 'pn'),
						'label' => is_sum($item->sum2dc) .' ' . pn_strip_input($item->currency_code_get),
						'copy' => is_sum($item->sum2dc),
						'class_wrap' => 'btbg_green',
						'sec' => 'sum2dc',
					);
					$cols['sum2c'] = array(
						'type' => 'text',
						'title' => __('Amount (with add. fees and PS fees)', 'pn'),
						'label' => is_sum($item->sum2c) .' ' . pn_strip_input($item->currency_code_get),
						'copy' => is_sum($item->sum2c),
						'sec' => 'sum2c',
					);
					
					$account_get = pn_strip_input($item->account_get);
					if ($account_get) {
						$cols['account_get'] = array(
							'type' => 'text',
							'title' => __('Into account', 'pn'),
							'label' => $account_get,
							'copy' => $account_get,
							'sec' => 'account_get',
							'class_wrap' => 'btbg_fiol',
						);
					}					
					if (isset($dmetas[2]) and is_array($dmetas[2])) {
						foreach ($dmetas[2] as $key => $value) {			
							$title = pn_strip_input(ctv_ml(is_isset($value, 'title')));
							$data = pn_strip_input(is_isset($value, 'data'));
							if ($data) {
								$cols['meta_give' . $key] = array(
									'type' => 'text',
									'title' => $title,
									'label' => $data,
									'copy' => $data,
								);								
							}
						}
					}
					$temp .= _onebid_col_temp($cols, 'onebid_col3', $item, $v, $direction);				
						
					$temp .='
					<div class="premium_clear"></div>
				</div>
				<div class="abs_line al3"></div>	
				';
				
				$temp .='
				<div class="bids_col_bg four"></div>
				<div class="bids_col four">
				';				
					$cols = array();
					
					$dir_fields = get_user_fields();
					foreach ($dir_fields as $dir_field_key => $dir_field) {
						$data = pn_strip_input(is_isset($item, $dir_field_key));
						if ($data) {
							$cols[$dir_field_key] = array(
								'type' => 'text',
								'title' => $dir_field['title'],
								'label' => $data,
								'copy' => $data,
							);							
						}						
					}																
					
					if (is_array($metas)) {
						foreach ($metas as $key => $value) {		
							$title = pn_strip_input(ctv_ml(is_isset($value, 'title')));
							$data = pn_strip_input(is_isset($value, 'data'));
							if (strlen($data) > 0 and !isset($value['auto'])) {
								$cols['other_meta' . $key] = array(
									'type' => 'text',
									'title' => $title,
									'label' => $data,
									'copy' => $data,
								);									
							}	
						}						
					}	
					
					$cols['user_ip'] = array(
						'type' => 'text',
						'title' => __('User IP', 'pn'),
						'label' => pn_strip_input($item->user_ip),
						'copy' => pn_strip_input($item->user_ip),
					);
					$cols['user_agent'] = array(
						'type' => 'text',
						'title' => __('User agent', 'pn'),
						'label' => pn_strip_input($item->user_agent),
						'copy' => pn_strip_input($item->user_agent),
					);					
					$temp .= _onebid_col_temp($cols, 'onebid_col4', $item, $v, $direction);
			
				$temp .='	
					<div class="premium_clear"></div>
				</div>
					<div class="premium_clear"></div>
				';		

				$temp .= '
				<div class="one_bids_info js_info_block">
					<div class="bi_block">
				';
				
						$cols = array();
						$cols['title'] = array(
							'type' => 'html',
							'html' => '<div class="bi_bigtitle">'. __('Information', 'pn') .'</div>',
						);					

						$user_id = $item->user_id;
						if ($user_id) { $user = $user_id; } else { $user = __('Guest', 'pn'); }
						$cols['user_or_guest'] = array(
							'type' => 'text',
							'title' => __('User ID', 'pn'),
							'label' => $user,
						);
						$user_discount = is_sum($item->user_discount);
						$cols['user_discount'] = array(
							'type' => 'text',
							'title' => __('User discount (%)', 'pn'),
							'label' => $user_discount . '%',
							'copy' => $user_discount,
							'sec' => 'user_discount',
						);					
						$cols['user_discount_sum'] = array(
							'type' => 'text',
							'title' => __('User discount (amount)', 'pn'),
							'label' => is_sum($item->user_discount_sum) . ' ' . pn_strip_input($item->currency_code_get),
							'copy' => is_sum($item->user_discount_sum),
							'sec' => 'user_discount_sum',
						);
						$cols['profit'] = array(
							'type' => 'text',
							'title' => __('Profit', 'pn'),
							'label' => is_sum($item->profit) . ' ' . cur_type(),
							'copy' => is_sum($item->profit),
							'sec' => 'profit',
						);
						$cols['exsum'] = array(
							'type' => 'text',
							'title' => __('Amount in internal currency', 'pn'),
							'label' => is_sum($item->exsum) . ' ' . cur_type(),
							'copy' => is_sum($item->exsum),
							'sec' => 'exsum',
						);										
						$temp .= _onebid_hidecol_temp($cols, 'onebid_hidecol1', $item, $v, $direction);	
					
					$temp .= '
					</div>	
					<div class="bi_block">';
					
						$cols = array();
						$cols['title'] = array(
							'type' => 'html',
							'html' => '<div class="bi_bigtitle">' . __('Information "Sent"', 'pn') . '</div>',
						);					
						$cols['sum1'] = array(
							'type' => 'text',
							'title' => __('Amount To send', 'pn'),
							'label' => is_sum($item->sum1) . ' ' . pn_strip_input($item->currency_code_give),
							'copy' => is_sum($item->sum1),
							'sec' => 'sum1',							
						);
						$cols['dop_com1'] = array(
							'type' => 'text',
							'title' => __('Add. fees amount', 'pn'),
							'label' => is_sum($item->dop_com1) . ' ' . pn_strip_input($item->currency_code_give),
							'copy' => is_sum($item->dop_com1),
							'sec' => 'dop_com1',							
						);
						$cols['sum1dc'] = array(
							'type' => 'text',
							'title' => __('Amount To send (add. fees)', 'pn'),
							'label' => is_sum($item->sum1dc) . ' ' . pn_strip_input($item->currency_code_give),
							'copy' => is_sum($item->sum1dc),
							'sec' => 'sum1dc',							
						);
						$cols['com_ps1'] = array(
							'type' => 'text',
							'title' => __('PS fees amount', 'pn'),
							'label' => is_sum($item->com_ps1) . ' ' . pn_strip_input($item->currency_code_give),
							'copy' => is_sum($item->com_ps1),
							'sec' => 'com_ps1',							
						);
						$cols['sum1c'] = array(
							'type' => 'text',
							'title' => __('Amount To send (add.fee and PS fee)', 'pn'),
							'label' => is_sum($item->sum1c) . ' ' . pn_strip_input($item->currency_code_give),
							'copy' => is_sum($item->sum1c),
							'sec' => 'sum1c',							
						);
						$cols['sum1r'] = array(
							'type' => 'text',
							'title' => __('Amount for reserve','pn'),
							'label' => is_sum($item->sum1r) . ' ' . pn_strip_input($item->currency_code_give),
							'copy' => is_sum($item->sum1r),
							'sec' => 'sum1r',							
						);						
						$temp .= _onebid_hidecol_temp($cols, 'onebid_hidecol2', $item, $v, $direction);						
							
					$temp .= '
					</div>	
					<div class="bi_block">';
					
						$cols = array();
						$cols['title'] = array(
							'type' => 'html',
							'html' => '<div class="bi_bigtitle">' . __('Information "Received"', 'pn') . '</div>',
						);					
						$cols['sum2t'] = array(
							'type' => 'text',
							'title' => __('Amount at the Exchange Rate', 'pn'),
							'label' => is_sum($item->sum2t) . ' ' . pn_strip_input($item->currency_code_get),
							'copy' => is_sum($item->sum2t),
							'sec' => 'sum2t',							
						);
						$cols['sum2'] = array(
							'type' => 'text',
							'title' => __('Amount (discount included)', 'pn'),
							'label' => is_sum($item->sum2) . ' ' . pn_strip_input($item->currency_code_get),
							'copy' => is_sum($item->sum2),
							'sec' => 'sum2',							
						);	
						$cols['dop_com2'] = array(
							'type' => 'text',
							'title' => __('Add. fees amount', 'pn'),
							'label' => is_sum($item->dop_com2) . ' ' . pn_strip_input($item->currency_code_get),
							'copy' => is_sum($item->dop_com2),
							'sec' => 'dop_com2',							
						);
						$cols['sum2dc'] = array(
							'type' => 'text',
							'title' => __('Amount To receive (add. fees)', 'pn'),
							'label' => is_sum($item->sum2dc) . ' ' . pn_strip_input($item->currency_code_get),
							'copy' => is_sum($item->sum2dc),
							'sec' => 'sum2dc',							
						);
						$cols['com_ps2'] = array(
							'type' => 'text',
							'title' => __('PS fees amount', 'pn'),
							'label' => is_sum($item->com_ps2) . ' ' . pn_strip_input($item->currency_code_get),
							'copy' => is_sum($item->com_ps2),
							'sec' => 'com_ps2',							
						);
						$cols['sum2c'] = array(
							'type' => 'text',
							'title' => __('Amount To receive (add.fees and PS fees)', 'pn'),
							'label' => is_sum($item->sum2c) . ' ' . pn_strip_input($item->currency_code_get),
							'copy' => is_sum($item->sum2c),
							'sec' => 'sum2c',							
						);
						$cols['sum2r'] = array(
							'type' => 'text',
							'title' => __('Amount for reserve', 'pn'),
							'label' => is_sum($item->sum2r) . ' ' . pn_strip_input($item->currency_code_get),
							'copy' => is_sum($item->sum2r),
							'sec' => 'sum2r',							
						);						
						$temp .= _onebid_hidecol_temp($cols, 'onebid_hidecol3', $item, $v, $direction);					
								
					$temp .= '
					</div>	
					<div class="bi_block">';

					$temp .= _onebid_hidecol_temp(array(), 'onebid_hidecol4', $item, $v, $direction);
					
				$temp .='
					</div>
						<div class="premium_clear"></div>
				</div>';
				
			$temp .= '
			</div>
				<div class="premium_clear"></div>
			';
			
			$temp .= '
			<div class="action_bids_abs">';
			
				$onebid_actions = array();
				$onebid_actions['info'] = array(
					'type' => 'link',
					'title' => __('Information', 'pn'),
					'label' => __('info', 'pn'),
					'link' => '#',
					'link_target' => '',
					'link_class' => 'js_info',
				);
				
				if ($item->user_id) {
					$relat_link = admin_url('admin.php?page=pn_bids&iduser=' . $item->user_id);
				} else {
					$relat_link = admin_url('admin.php?page=pn_bids&user_email=' . pn_strip_input($item->user_email) . '&user_skype=' . pn_strip_input($item->user_skype) . '&user_phone=' . pn_strip_input($item->user_phone));
				}				
				
				$onebid_actions['relative'] = array(
					'type' => 'link',
					'title' => __('Similar exchanges', 'pn'),
					'label' => __('Similar', 'pn'),
					'link' => $relat_link,
					'link_target' => '_blank',
					'link_class' => '',
				);				
				
				$onebid_actions = apply_filters('onebid_actions', $onebid_actions, $item, $v, $direction);
				$onebid_actions = (array)$onebid_actions;
	
				$temp .= _onebid_thead_temp($onebid_actions, $item, $v, $direction);			
				
				$temp .= '
					<div class="premium_clear"></div>
			</div>';
			
	$temp .= '
		</div>
	</div>';
	
	return $temp;
}

function _onebid_thead_temp($actions, $item, $v, $direction) {
	
	$temp = '';
	
	foreach ($actions as $data) {
		$type = trim(is_isset($data, 'type'));
		if ('checkbox' == $type) {
			$checked = trim(is_isset($data, 'checked'));
			$ch = '';
			if ('true' == $checked) { $ch = 'checked="checked"'; }
			$disabled = trim(is_isset($data, 'disabled'));
			$di = '';
			if ('true' == $disabled) { $di = 'disabled="disabled"'; }
			$temp .= '
			<div class="bids_checkbox">
				<input type="checkbox" name="id[]" ' . $ch . ' ' . $di . ' class="check_one" autocomplete="off" value="' . $item->id . '" />
			</div>
			';
		} elseif ('label' == $type) {
			$temp .= '
			<div class="bids_label" title="' . is_isset($data, 'title') . '">
				<div class="bids_label_img">
					<img src="' . is_isset($data, 'image') . '" alt="" />
				</div>
			</div>
			';		
		} elseif ('text' == $type) {
			$link_target = trim(is_isset($data, 'link_target')); if ('_blank' != $link_target) { $link_target = '_self'; }
			$link = is_isset($data, 'link');
			$label = is_isset($data, 'label');
			$title = is_isset($data, 'title');
			$temp .= '<div class="bids_label_txt" title="' . $title . '">';
				if ($link) {
					$temp .= '<a href="' . $link . '" target="' . $link_target . '" rel="noreferrer noopener">';
				}
				$temp .= $label;
				if ($link) {
					$temp .= '</a>';
				}						
			$temp .= '</div>';
		} elseif ('link' == $type) {	
			$link_class = trim(is_isset($data, 'link_class'));
			$link_target = trim(is_isset($data, 'link_target')); if ('_blank' != $link_target) { $link_target = '_self'; }
			$link = is_isset($data, 'link');
			if ($link) {
				$label = is_isset($data, 'label');
				$title = is_isset($data, 'title');			
				$temp .= '<a href="' . $link . '" target="' . $link_target . '" rel="noreferrer noopener" title="' . $title . '" class="one_action_bid_link ' . $link_class . '">';		
				$temp .= $label;
				$temp .= '</a>';		
			}			
		} elseif ('html' == $type) {
			$temp .= is_isset($data, 'html');
		}
	}	
	
	return $temp;
}

function _onebid_col_temp($actions, $filter, $item, $v, $direction) {
	global $premiumbox;	
	
	$temp = '';
	
	$actions = apply_filters($filter, $actions, $item, $v, $direction);
	$actions = (array)$actions;	
	
	foreach ($actions as $data_key => $data) {
		$type = trim(is_isset($data, 'type'));
		if ('text' == $type) {
			$class_wrap = trim(is_isset($data, 'class_wrap'));
			$link_target = trim(is_isset($data, 'link_target')); if ('_blank' != $link_target) { $link_target = '_self'; }
			$link = is_isset($data, 'link');
			$label = is_isset($data, 'label');
			$copy = is_isset($data, 'copy');	
			$no_copy = intval($premiumbox->get_option('nocopydata'));
			$copy_html = '';
			if (strlen($copy) > 0 and 1 != $no_copy) {
				$copy_html = '<span class="bid_copy_item clpb_item" data-clipboard-text="' . $copy . '">[c]</span>';
			}
			
			$title = is_isset($data, 'title');
			$class = is_isset($data, 'class');
			$classes = array('onebid_item', 'item_bid_' . $data_key);
			if ($class) {
				$classes[] = $class;
			}
				
			$sec = check_sec(is_isset($data, 'sec'), $item, $v);
			if ($sec['subs']) {
				$classes[] = 'bred_dash';
			}	
				
			$temp .= '<div class="bids_text ' . $class_wrap . '">';
				
				if ($title) {
					$temp .= '<span class="bt_fix"><span class="bt">' . $title . ':</span></span> ';
				}
				if ($link) {
					$temp .= '<a href="' . $link . '" target="' . $link_target . '" rel="noreferrer noopener">';
				}
					
				$temp .= '<span class="' . implode(' ', $classes) . '">' . $label . '</span>' . $copy_html;
					
				if ($link) {
					$temp .= '</a>';
				}		
				
			$temp .= '<div class="premium_clear"></div></div>';
				
			if ($sec['subs']) {
				$temp .= '<div class="bids_text ' . $class_wrap . '">';
					
					$temp .= '<span class="bt_fix"><span class="bt">' . sprintf(__('Original "%s"', 'pn'), $title) . ':</span></span> ';
					$temp .= '<span class="bgreen">' . $sec['origin']	. '</span>';
						
				$temp .= '<div class="premium_clear"></div></div>';		
			}				
				
		} elseif ('html' == $type) {
			$temp .= is_isset($data, 'html');
		}
	}		
	
	return $temp;
}

function _onebid_hidecol_temp($actions, $filter, $item, $v, $direction) {
	global $premiumbox;		
	
	$temp = '';
	
	$actions = apply_filters($filter, $actions, $item, $v, $direction);
	$actions = (array)$actions;	
	
	foreach ($actions as $data_key => $data) {
		$type = trim(is_isset($data, 'type'));
		if ('text' == $type) {
			$link_target = trim(is_isset($data, 'link_target')); if ('_blank' != $link_target) { $link_target = '_self'; }
			$link = is_isset($data, 'link');
			$label = is_isset($data, 'label');
			$copy = is_isset($data, 'copy');	
			$no_copy = intval($premiumbox->get_option('nocopydata'));
			$copy_html = '';
			if (strlen($copy) > 0 and 1 != $no_copy) {
				$copy_html = '<span class="bid_copy_item clpb_item" data-clipboard-text="' . $copy . '">[c]</span>';
			}			
			$title = is_isset($data, 'title');
			$class = is_isset($data, 'class');
			$classes = array('onebid_item', 'item_bid_' . $data_key);
			if ($class) {
				$classes[] = $class;
			}
			
			$sec = check_sec(is_isset($data, 'sec'), $item, $v);
			if ($sec['subs']) {
				$classes[] = 'bred_dash';
			}
			
			$temp .= '<div class="bi_line"><div class="bi_title">' . $title . '</div><div class="bi_div">';
				if ($link) {
					$temp .= '<a href="' . $link . '" target="' . $link_target . '" rel="noreferrer noopener">';
				}
					$temp .= '<span class="' . implode(' ', $classes) . '">' . $label . '</span>' . $copy_html;
				if ($link) {
					$temp .= '</a>';
				}						
			$temp .= '</div><div class="premium_clear"></div></div>';
			
			if ($sec['subs']) {
				$temp .= '<div class="bi_line"><div class="bi_title bgreen">' . sprintf(__('Original "%s"', 'pn'), $title) . '</div><div class="bi_div">';
				
					$temp .= '<span class="bgreen">' . $sec['origin'] . '</span>';
					
				$temp .= '</div><div class="premium_clear"></div></div>';	
			}
			
		} elseif ('html' == $type) {
			$temp .= is_isset($data, 'html');
		}
	}	
	
	return $temp;
}

add_filter('onebid_col1', 'onebid_col1_exceedpay', 20, 4);
function onebid_col1_exceedpay($actions, $item, $v, $direction) {
	
	if (isset($actions['pay_sum'])) {
		if ($item->exceed_pay) {
			$actions['pay_sum']['class'] = 'bpur_dash';
			$actions['pay_sum']['label'] = is_sum($item->pay_sum) . ' (' . __('Overpayment', 'pn') . ')';
		}
	}
	
	return $actions;
}		