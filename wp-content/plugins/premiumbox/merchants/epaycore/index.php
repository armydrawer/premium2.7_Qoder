<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]EpayCore[:en_US][ru_RU:]EpayCore[:ru_RU]
description: [en_US:]EpayCore merchant[:en_US][ru_RU:]мерчант EpayCore[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_epaycore')) {
	class merchant_epaycore extends Ext_Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach ($ids as $id) {
				add_action('premium_merchant_' . $id . '_status' . hash_url($id), array($this, 'merchant_status'));
				add_action('premium_merchant_' . $id . '_fail', array($this, 'merchant_fail'));
				add_action('premium_merchant_' . $id . '_success', array($this, 'merchant_success'));
			}
			
		}

		function get_map() {
			
			$map = array(
				'SCI_ID'  => array(
					'title' => '[en_US:]SCI merchant id[:en_US][ru_RU:]SCI merchant id[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'SCI_PASS'  => array(
					'title' => '[en_US:]SCI password[:en_US][ru_RU:]SCI password[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),			
				'API_ID'  => array(
					'title' => '[en_US:]Api id[:en_US][ru_RU:]Api id[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'API_SECRET'  => array(
					'title' => '[en_US:]Api secret[:en_US][ru_RU:]Api secret[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),				
			);
			
			return $map;
		}
		
		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('SCI_ID', 'SCI_PASS');
			
			return $arrs;
		}		

		function options($options, $data, $m_id, $place) { 
			
			$options = pn_array_unset($options, array('pagenote', 'cronhash'));
			
			$options['private_line'] = array(
				'view' => 'line',
			);						
			
			$options['paycomiss'] = array(
				'view' => 'select',
				'title' => __('Pays commission', 'pn'),
				'options' => array('0' => __('merchant', 'pn'), '1' => __('user', 'pn')),
				'default' => is_isset($data, 'paycomiss'),
				'name' => 'paycomiss',
				'work' => 'int',
			);			
			
			$text = '
			<div><strong>STATUS URL:</strong> <a href="' . get_mlink($m_id . '_status' . hash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_status' . hash_url($m_id)) . '</a></div>
			<div><strong>SUCCESS URL:</strong> <a href="' . get_mlink($m_id . '_success') . '" target="_blank">' . get_mlink($m_id.'_success') . '</a></div>
			<div><strong>FAIL URL:</strong> <a href="' . get_mlink($m_id . '_fail') . '" target="_blank">' . get_mlink($m_id . '_fail') . '</a></div>		
			';

			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;	
		}										

		function bidform($temp, $m_id, $pay_sum, $direction) {
			global $bids_data;	
			
			$script = get_mscript($m_id);
			if ($script and $script == $this->name) {
				
				$m_defin = $this->get_file_data($m_id);
				$m_data = get_merch_data($m_id);
				
				$currency = strtoupper(pn_strip_input($bids_data->currency_code_give));
				$currency = str_replace('RUR', 'RUB', $currency);		

				$pay_sum = is_sum($pay_sum, 2);				
				$text_pay = get_text_pay($m_id, $bids_data, $pay_sum);
				$text_pay = trim(pn_maxf($text_pay, 155));
				
				$epc_merchant_id = is_isset($m_defin, 'SCI_ID');
				$order_id = $bids_data->id;
				
				$paycomiss = intval(is_isset($m_data, 'paycomiss'));
				$paycomiss = $paycomiss + 1;				
				
				$epc_sign = hash('sha256', $epc_merchant_id . ':' . $pay_sum . ':' . $currency . ':' . $order_id . ':' . is_isset($m_defin, 'SCI_PASS'));
								
				$temp = '
				<form action="https://api.epaycore.com/checkout/form" method="POST">
					<input type="hidden" name="epc_commission" value="' . $paycomiss . '">
					<input type="hidden" name="epc_merchant_id" value="' . $epc_merchant_id . '">
					<input type="hidden" name="epc_amount" value="' . $pay_sum . '">
					<input type="hidden" name="epc_currency_code" value="' . $currency . '">
					<input type="hidden" name="epc_order_id" value="' . $order_id . '">
					<input type="hidden" name="epc_success_url" value="' . get_mlink($m_id . '_success') . '">
					<input type="hidden" name="epc_cancel_url" value="' . get_mlink($m_id . '_fail') . '">
					<input type="hidden" name="epc_status_url" value="' . get_mlink($m_id . '_status' . hash_url($m_id)) . '">
					<input type="hidden" name="epc_descr" value="' . $text_pay . '">
					<input type="hidden" name="epc_sign" value="' . $epc_sign . '">
					<input type="submit" value="' . __('Make a payment', 'pn') . '" />
				</form>
				';				
			}	
			
			return $temp;				
		}

		function merchant_fail() {
			
			$id = get_payment_id('order_id');
			redirect_merchant_action($id, $this->name);
			
		}

		function merchant_success() {
			
			$id = get_payment_id('order_id');
			redirect_merchant_action($id, $this->name, 1);
			
		}

		function merchant_status() {
	
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_secure', $this->name, '', $m_id, $m_defin, $m_data);	
	
			$epc_commission = is_param_post('epc_commission');
			$epc_merchant_id = is_param_post('epc_merchant_id');
			$epc_merchant_name = is_param_post('epc_merchant_name');
			$epc_order_id = is_param_post('epc_order_id');
			$epc_created_at = is_param_post('epc_created_at');
			$epc_amount = is_param_post('epc_amount');
			$epc_currency_code = is_param_post('epc_currency_code');
			$epc_dst_account = is_param_post('epc_dst_account'); //получатель
			$epc_src_account = is_param_post('epc_src_account'); //отправитель
			$epc_batch = is_param_post('epc_batch');
			$epc_sign = is_param_post('epc_sign');		
			
			$my_epc_sign = hash('sha256', $epc_merchant_id . ':' . $epc_order_id . ':' . $epc_created_at . ':' . $epc_amount . ':' . $epc_currency_code . ':' . $epc_dst_account . ':' . $epc_src_account . ':' . $epc_batch . ':' . is_isset($m_defin, 'SCI_PASS'));

			if ($my_epc_sign != $epc_sign) {
				$this->logs('Invalid control signature', $m_id); 
				die('Invalid control signature');
			}
			
			$my_epc_merchant_id = is_isset($m_defin, 'SCI_ID');

			if ($my_epc_merchant_id != $epc_merchant_id) {
				$this->logs('Invalid merchant', $m_id);
				die('Invalid merchant');
			}
			
			$check_history = intval(is_isset($m_data, 'check_api'));
			$show_error = intval(is_isset($m_data, 'show_error'));
			if (1 == $check_history) {
				try {
					$class = new EpayCore($this->name, $m_id, is_isset($m_defin, 'API_ID'), is_isset($m_defin, 'API_SECRET') );
					$res = $class->get_info($epc_batch);
					if (isset($res['type'], $res['status']) and 9 == intval($res['type']) and 4 == intval($res['status'])) {
						
						$epc_batch = $res['batch'];
						$epc_amount = $res['amount'];
						$epc_src_account = $res['system']['account'];
						$epc_currency_code = $res['currency']['code'];
						
					} else {
						$this->logs('Error history', $m_id);
						die('Error history');
					}
				}
				catch(Exception $e) {
					$this->logs($e->getMessage(), $m_id);
					if ($show_error and current_user_can('administrator')) {
						die($e->getMessage());
					} else {
						die('Fatal error');
					}
				}		
			}
			
			$epc_order_id = intval($epc_order_id);
			$epc_batch = pn_strip_input($epc_batch);
			
			if (check_trans_in($m_id, $epc_batch, $epc_order_id)) {
				$this->logs($epc_order_id . ' Error check trans in!', $m_id);
				die('Error check trans in!');
			}			
			
			$id = $epc_order_id;
			$data = get_data_merchant_for_id($id);
				
			$in_sum = $epc_amount;	
			$in_sum = is_sum($in_sum, 2);
			
			$bid_err = $data['err'];
			$bid_status = $data['status'];
			$bid_m_id = $data['m_id'];
			$bid_m_script = $data['m_script'];
			
			if ($bid_err > 0) {
				$this->logs($id . ' The application does not exist or the wrong ID', $m_id);
				die('The application does not exist or the wrong ID');
			}			
			
			if ($bid_m_script and $bid_m_script != $this->name or !$bid_m_script) {	
				$this->logs($id . ' wrong script', $m_id);
				die('wrong script');
			}			
			
			if ($bid_m_id and $m_id != $bid_m_id or !$bid_m_id) {
				$this->logs($id . ' not a faithful merchant', $m_id);
				die('not a faithful merchant');				
			}			
			
			$pay_purse = is_pay_purse($epc_src_account, $m_data, $bid_m_id);
				
			$bid_currency = strtoupper($data['currency']);
			$bid_currency = str_replace('RUR', 'RUB', $bid_currency);
			
			$bid_sum = is_sum($data['pay_sum'], 2);
			$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
				
			$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
			$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
			$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
			$invalid_check = intval(is_isset($m_data, 'check'));		

			$epc_currency_code = strtoupper($epc_currency_code);
			
			$workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay')); 
			if (in_array($bid_status, $workstatus)) {
				if ($bid_currency == $epc_currency_code or $invalid_ctype > 0) {
					if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {		
						
						$params = array(
							'pay_purse' => $pay_purse,
							'sum' => $in_sum,
							'bid_sum' => $bid_sum,
							'bid_corr_sum' => $bid_corr_sum,
							'bid_status' => $workstatus,
							'to_account' => $epc_dst_account,
							'trans_in' => $epc_batch,
							'currency' => $epc_currency_code,
							'bid_currency' => $bid_currency,
							'invalid_ctype' => $invalid_ctype,
							'invalid_minsum' => $invalid_minsum,
							'invalid_maxsum' => $invalid_maxsum,
							'invalid_check' => $invalid_check,
							'm_place' => $bid_m_id,
							'm_id' => $m_id,
							'm_data' => $m_data,
							'm_defin' => $m_defin,
						);
						set_bid_status('realpay', $id, $params, $data['direction_data']); 	 								
			
						echo 'OK';
						exit;
						
					} else {
						$this->logs($id . ' The payment amount is less than the provisions', $m_id);
					}
				} else {
					$this->logs($id . ' Wrong type of currency', $m_id);
				}
			} else {
				$this->logs($id . ' In the application the wrong status', $m_id);
			}	
		}
	}
}

new merchant_epaycore(__FILE__, 'EpayCore');