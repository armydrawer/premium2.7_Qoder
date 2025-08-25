<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]AdvCash[:en_US][ru_RU:]AdvCash[:ru_RU]
description: [en_US:]AdvCash merchant[:en_US][ru_RU:]мерчант AdvCash[:ru_RU]
version: 2.7.0
*/

if(!class_exists('Ext_Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_advcash')){
	class merchant_advcash extends Ext_Merchant_Premiumbox { 
	
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
				'ACCOUNT_EMAIL'  => array(
					'title' => '[en_US:]Account email[:en_US][ru_RU:]Email владельца счета[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'SCI_NAME'  => array(
					'title' => '[en_US:]SCI name[:en_US][ru_RU:]Название SCI[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'SCI_SECRET'  => array(
					'title' => '[en_US:]SCI secret[:en_US][ru_RU:]Пароль от SCI[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'API_NAME'  => array(
					'title' => '[en_US:]API name[:en_US][ru_RU:]Имя API[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'API_PASSWORD'  => array(
					'title' => '[en_US:]Password API[:en_US][ru_RU:]Пароль API[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),				
			);
			
			return $map;
		}
		
		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('ACCOUNT_EMAIL', 'SCI_NAME', 'SCI_SECRET');
			
			return $arrs;
		}			

		function options($options, $data, $m_id, $place) { 
			
			$options = pn_array_unset($options, array('pagenote', 'cronhash'));
			
			$text = '
			<div><strong>Status URL:</strong> <a href="' . get_mlink($m_id . '_status' . hash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_status' . hash_url($m_id)) . '</a></div>
			<div><strong>Success URL:</strong> <a href="' . get_mlink($m_id . '_success') . '" target="_blank">' . get_mlink($m_id . '_success') . '</a></div>
			<div><strong>Fail URL:</strong> <a href="' . get_mlink($m_id . '_fail') . '" target="_blank">' . get_mlink($m_id . '_fail') . '</a></div>		
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

				$amount = is_sum($pay_sum, 2);
				$text_pay = get_text_pay($m_id, $bids_data, $amount);
				
				$currency = pn_strip_input(str_replace('RUB', 'RUR', $bids_data->currency_code_give));
				$orderId = $bids_data->id;
				$ac_account_email = is_isset($m_defin, 'ACCOUNT_EMAIL');
				$ac_sci_name = is_isset($m_defin, 'SCI_NAME');
				$sign = hash('sha256', $ac_account_email . ":" . $ac_sci_name . ":" . $amount . ":" . $currency . ":" . is_isset($m_defin, 'SCI_SECRET') . ":" . $orderId);
										
				$temp = '
				<form name="MerchantPay" action="https://wallet.advcash.com/sci/" method="post">
					<input type="hidden" name="ac_account_email" value="' . $ac_account_email . '" /> 
					<input type="hidden" name="ac_sci_name" value="' . $ac_sci_name . '" />  
					<input type="hidden" name="ac_order_id" value="' . $orderId . '" /> 
					<input type="hidden" name="ac_sign" value="' . $sign . '" />			
							
					<input type="hidden" name="ac_amount" value="' . $amount . '" />
					<input type="hidden" name="ac_currency" value="' . $currency . '" />
					<input type="hidden" name="ac_comments" value="' . $text_pay . '" />
							
					<input type="submit" value="' . __('Make a payment', 'pn') . '" />
				</form>												
				';				
			}
			return $temp;
		}

		function merchant_fail() {

			$id = get_payment_id('ac_order_id');
			redirect_merchant_action($id, $this->name);

		}

		function merchant_success() {

			$id = get_payment_id('ac_order_id');
			redirect_merchant_action($id, $this->name, 1);

		}
	
		function merchant_status() {
			
			$m_id = key_for_url('_status');
			
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_secure', $this->name, '', $m_id, $m_defin, $m_data);
			
			$transactionId = is_param_req('ac_transfer');
			$paymentDate = is_param_req('ac_start_date');
			$sciName = is_param_req('ac_sci_name');
			$payer = is_param_req('ac_src_wallet');
			$destWallet = is_param_req('ac_dest_wallet');
			$orderId = is_param_req('ac_order_id');
			$amount = is_param_req('ac_amount');
			$currency = is_param_req('ac_merchant_currency');
			$hash = is_param_req('ac_hash'); 
			$pay_status = is_param_req('ac_transaction_status');		

			if ($hash != strtolower(hash('sha256', $transactionId . ':' . $paymentDate . ':' . $sciName . ':' . $payer . ':' . $destWallet . ':' . $orderId . ':' . $amount . ':' . $currency . ':' . is_isset($m_defin, 'SCI_SECRET')))) {
				$this->logs('Error control sign', $m_id); 
				die('Error control sign');	
			}	
			
			$check_history = intval(is_isset($m_data, 'check_api')); 
			$show_error = intval(is_isset($m_data, 'show_error'));
			if (1 == $check_history) {
				try {
					$merchantWebService = new MerchantWebService();
					$arg0 = new authDTO();
					$arg0->apiName = is_isset($m_defin, 'API_NAME');
					$arg0->accountEmail = is_isset($m_defin, 'ACCOUNT_EMAIL');
					$arg0->authenticationToken = $merchantWebService->getAuthenticationToken(is_isset($m_defin, 'API_PASSWORD'));

					$arg1 = $transactionId;

					$findTransaction = new findTransaction();
					$findTransaction->arg0 = $arg0;
					$findTransaction->arg1 = $arg1;
					
					$findTransactionResponse = $merchantWebService->findTransaction($findTransaction);
					if (isset($findTransactionResponse->return)) {
						$result = $findTransactionResponse->return;
						
						$payer = is_isset($result, 'walletSrcId');
						$destWallet = is_isset($result, 'walletDestId');
						$orderId = is_isset($result, 'orderId');
						$amount = is_isset($result, 'amount');
						$currency = is_isset($result, 'currency');
						$pay_status = is_isset($result,'status');
					}
				}
				catch(Exception $e) {
					$this->logs($e->getMessage(), $m_id);
					if ($show_error and current_user_can('administrator')) {
						die($e->getMessage());
					}
					die('Hisory error!');
				}		
			}			
			
			$currency = strtoupper($currency);
			
			$order_id = intval($orderId);
			$data = get_data_merchant_for_id($order_id);
			$bid_m_id = $data['m_id'];
			$bid_m_script = $data['m_script'];
			$bid_err = $data['err'];
			
			if ($bid_err > 0) {
				$this->logs($order_id . ' The application does not exist or the wrong ID', $m_id);
				die('The application does not exist or the wrong ID');
			}
			
			if ($bid_m_script and $bid_m_script != $this->name or !$bid_m_script) {	
				$this->logs($order_id . ' wrong script', $m_id);
				die('wrong script');
			}			
			
			if ($bid_m_id and $m_id != $bid_m_id or !$bid_m_id) {
				$this->logs($order_id . ' wrong script', $m_id);
				die('wrong script');				
			}
			
			if (check_trans_in($m_id, $transactionId, $orderId)) {
				$this->logs($order_id . ' Error check trans in!', $m_id);
				die('Error check trans in!');
			}
			
			$in_sum = $amount;
			$in_sum = is_sum($in_sum, 2);
			$bid_status = $data['status'];
			
			$pay_purse = is_pay_purse($payer, $m_data, $bid_m_id);
			
			$bid_currency = $data['currency'];
			$bid_currency = str_replace('RUB', 'RUR', $bid_currency);
			
			$bid_sum = is_sum($data['pay_sum'], 2);
			$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
			
			$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
			$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
			$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
			$invalid_check = intval(is_isset($m_data, 'check'));
			
			/*
			PENDING, PROCESS, CONFIRMED, COMPLETED, CANCELED
			*/
			$workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay')); 
			if (in_array($bid_status, $workstatus)) { 
				if ($bid_currency == $currency or $invalid_ctype > 0) {
					if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {		
						$now_status = '';
						if ('PENDING' == $pay_status) {
							$now_status = 'coldpay';
						} elseif ('PROCESS' == $pay_status) {
							$now_status = 'coldpay';
						} elseif ('COMPLETED' == $pay_status) {
							$now_status = 'realpay';
						}
						if ($now_status) {	
						
							$params = array(
								'sum' => $in_sum,
								'bid_sum' => $bid_sum,
								'bid_status' => $workstatus,
								'bid_corr_sum' => $bid_corr_sum,
								'pay_purse' => $pay_purse,
								'to_account' => $destWallet,
								'trans_in' => $transactionId,
								'currency' => $currency,
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
							set_bid_status($now_status, $order_id, $params, $data['direction_data']); 
							
						}
								
						die('Completed');
					} else {
						$this->logs($order_id . ' The payment amount is less than the provisions', $m_id);
						die('The payment amount is less than the provisions');
					}
				} else {
					$this->logs($order_id . ' Wrong type of currency', $m_id);
					die('Wrong type of currency');
				}
			} else {
				$this->logs($order_id . ' In the application the wrong status', $m_id);
				die('In the application the wrong status');
			}			
		}		
	}
}

new merchant_advcash(__FILE__, 'AdvCash');		