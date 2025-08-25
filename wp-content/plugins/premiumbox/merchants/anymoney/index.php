<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]AnyMoney[:en_US][ru_RU:]AnyMoney[:ru_RU]
description: [en_US:]AnyMoney merchant[:en_US][ru_RU:]мерчант AnyMoney[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_anymoney')) {
	class merchant_anymoney extends Ext_Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			$ids = $this->get_ids('merchants', $this->name);

			foreach ($ids as $id) {
				add_action('premium_merchant_' . $id . '_status' . hash_url($id), array($this, 'merchant_status'));
			}			
		}

		function get_map() {
			
			$map = array(
				'MERCHANT_ID'  => array(
					'title' => '[en_US:]Merchant ID[:en_US][ru_RU:]ID мерчанта[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'API_KEY'  => array(
					'title' => '[en_US:]API key[:en_US][ru_RU:]API ключ[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),				
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('MERCHANT_ID', 'API_KEY');
			
			return $arrs;
		}

		function options($options, $data, $m_id, $place) { 
			
			$m_defin = $this->get_file_data($m_id);
			
			$options = pn_array_unset($options, array('personal_secret', 'pagenote', 'note', 'check_api'));

			$options['anymoney_line'] = array(
				'view' => 'line',
			);					
			
			$s_curr = array();
			
			if (1 == $place) {
				try {
					$types = array();
					$types[''] = '--' . __('No', 'pn') . '--';
					$class = new AnyMoney($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'MERCHANT_ID'));
					$res = $class->get_pwcurrency();
					
					$res = pn_strip_input_array($res);
					update_option('anymoney_data', $res); 
					
					if (isset($res['result']) and is_array($res['result'])) {
						foreach ($res['result'] as $res_key => $res_arr) {
							$in = is_isset($res_arr, 'in');
							if (is_array($in)) {
								$n = array();
								foreach ($in as $curr => $curr_data) {
									$n[] = $curr;
									$s_curr[$curr] = $curr;
								}
								$types[$res_key] = $res_key . ' (' . implode(', ', $n) . ')';
							}
						}
					}
					
					$options['payment_type'] = array(
						'view' => 'select',
						'title' => __('Transaction type', 'pn'),
						'options' => $types,
						'default' => is_isset($data, 'payment_type'),
						'name' => 'payment_type',
						'work' => 'input',
					);					
				}
				catch (Exception $e)
				{
					$options['payment_type_text'] = array(
						'view' => 'textfield',
						'title' => '',
						'default' => $e->getMessage(),
					);							
				}
			} else {
				$options['payment_type'] = array(
					'view' => 'select',
					'options' => array(),
					'default' => is_isset($data, 'payment_type'),
					'name' => 'payment_type',
					'work' => 'input',
				);				
			}

			$options['payment_convert'] = array(
				'view' => 'input',
				'title' => __('Convert to', 'pn'),
				'default' => is_isset($data, 'payment_convert'),
				'name' => 'payment_convert',
				'work' => 'input',
			);
			$options['help_payment_convert'] = array(
				'view' => 'help',
				'title' => __('More info', 'pn'),
				'default' => join(', ', $s_curr),
			);
			$options['payment_comis'] = array(
				'view' => 'input',
				'title' => __('Fees percent', 'pn'),
				'default' => is_isset($data, 'payment_comis'),
				'name' => 'payment_comis',
				'work' => 'input',
			);
			$options['help_payment_comis'] = array(
				'view' => 'help',
				'title' => __('More info', 'pn'),
				'default' => __('Specify the percentage of the payment commission that AnyMoney takes and which will be transferred to the user. If the value is not specified or 0 (zero) is specified, then the user pays the commission in full. If the value is 100, then the commission is paid by the exchange office.', 'pn'),
			);
			$options['need_confirm'] = array(
				'view' => 'input',
				'title' => __('Required number of transaction confirmations', 'pn'),
				'default' => is_isset($data, 'need_confirm'),
				'name' => 'need_confirm',
				'work' => 'int',
			);
			$options['need_confirm_warning'] = array(
				'view' => 'warning',
				'default' => sprintf(__('(Recommended!) Set the value to 0 so that the order is considered paid only after receiving the required number of confirmations on the stock! <br /> (NOT recommended!) If you set a value other than 0, the exchanger will change the status of the order to "Paid" according to this setting, regardless of the transaction status that is displayed in the exchanges payment history.', 'pn'), 'AnyMoney'),
			);			
			
			$text = '
			<div><strong>Callback URL:</strong> <a href="' . get_mlink($m_id . '_status' . hash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_status' . hash_url($m_id)) . '</a></div>
			<div><strong>Cron:</strong> <a href="' . get_mlink($m_id . '_cron' . chash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_cron' . chash_url($m_id)) . '</a></div>			
			';

			$options['text_line'] = array(
				'view' => 'line',
			);			
			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;	
		}				

		function merch_type($m_id) {
			
			return 'link'; 
		}

		function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
			global $bids_data;	
			
			$payment_type = trim(is_isset($m_data, 'payment_type'));
			$payment_convert = trim(is_isset($m_data, 'payment_convert'));
			$payment_convert = str_replace('RUR', 'RUB', $payment_convert);
			$payment_comis = intval(is_isset($m_data, 'payment_comis'));
			if ($payment_comis > 100) { $payment_comis = 100; }
			if ($payment_comis < 1) { $payment_comis = 0; }
			$payment_comis = $payment_comis * '0.01';
				
			$pay_link = $this->get_pay_link($bids_data->id);
			if (!$pay_link) {
					
				$pay_sum = is_sum($pay_sum, 12); 
				$currency = mb_strtoupper($bids_data->currency_code_give);
				$currency = str_replace('RUR', 'RUB', $currency);
					
				try {
					$class = new AnyMoney($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'MERCHANT_ID'));
					$res = $class->get_invoice($pay_sum, $bids_data->id, $bids_data->user_email, $payment_type, $currency, $payment_convert, get_mlink($m_id . '_status' . hash_url($m_id)), $payment_comis, get_bids_url($bids_data->hashed));
					if (isset($res['result'], $res['result']['paylink']) and $res['result']['paylink']) {
								
						$address = '';
						if (isset($res['result']['address'])) {
							$address = pn_strip_input($res['result']['address']);
						}
								
						$arr = array();
						$arr['to_account'] = $address;
						$bids_data = update_bid_tb_array($bids_data->id, $arr, $bids_data);
								
						$pay_link = pn_strip_input($res['result']['paylink']);
						$this->update_pay_link($bids_data->id, $pay_link);
								
					} 
				}
				catch (Exception $e)
				{
					$this->logs($e->getMessage(), $m_id);
				}	

			}
			
			if ($pay_link) {
				return 1;
			}
			
			return 0;			
		}

		function merchant_status() {
			
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_secure', $this->name, '', $m_id, $m_defin, $m_data);
			
			$this->anymoney_cron($m_id, $m_defin, $m_data);
			
			echo 'OK';
			exit;
		}	

		function cron($m_id, $m_defin, $m_data) {
			
			$this->anymoney_cron($m_id, $m_defin, $m_data);	
			
		}
		
		function anymoney_cron($m_id, $m_defin, $m_data) {
			global $wpdb;
			
			$show_error = intval(is_isset($m_data, 'show_error'));	
			$need_confirm = intval(is_isset($m_data, 'need_confirm'));
			
			try {
				$class = new AnyMoney($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'MERCHANT_ID'));
				$orders = $class->get_history_invoice('200');
				
				if (is_array($orders)) {
					$workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay'));
					$workstatus_db = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay'), 1);
					$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($workstatus_db) AND m_in = '$m_id'");
					foreach ($items as $item) {
						$item_id = $item->id;
						if (isset($orders['in_' . $item_id])) {
							$order = $orders['in_' . $item_id];
							$currency = $order['in_curr'];
							$order_status = $order['status'];
							$address = pn_strip_input(is_isset($order, 'address'));
							$txid = pn_strip_input(is_isset($order, 'txid'));
							$trans_in = $order['token'];
							$confirmations = intval(is_isset($order, 'confirmations'));
							
							if (in_array($order_status, array('done', 'pending', 'wait', 'started'))) {							
								$data = get_data_merchant_for_id($item_id, $item);
									
								$in_sum = $order['in_amount'];
								$in_sum = is_sum($in_sum, 12);
								$err = $data['err'];
								$status = $data['status'];
								$bid_m_id = $data['m_id'];
								$bid_m_script = $data['m_script']; 
									
								$bid_currency = $data['currency'];
									
								$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
										
								$bid_sum = is_sum($data['pay_sum'], 12);	
								$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
									
								$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
								$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
								$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
								$invalid_check = intval(is_isset($m_data, 'check'));								
									
								if (!check_trans_in($bid_m_id, $order['token'], $item_id)) {
									if (0 == $err and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name) {
										if ($bid_currency == $currency or $invalid_ctype > 0) {
											if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {
												
												$params = array( 
													'pay_purse' => $pay_purse,
													'sum' => $in_sum,
													'bid_sum' => $bid_sum,
													'bid_status' => $workstatus,
													'bid_corr_sum' => $bid_corr_sum,
													'txid_in' => $txid,
													'trans_in' => $trans_in,
													'currency' => $currency,
													'bid_currency' => $bid_currency,
													'invalid_ctype' => $invalid_ctype,
													'invalid_minsum' => $invalid_minsum,
													'invalid_maxsum' => $invalid_maxsum,
													'invalid_check' => $invalid_check,
													'm_place' => $m_id,
													'm_id' => $m_id,
													'm_data' => $m_data,
													'm_defin' => $m_defin,
												);
												
												$now_status = 'coldpay';
												if ('done' == $order_status) {
													$now_status = 'realpay';
												}
												if ('done' != $order_status and $confirmations >= $need_confirm and $need_confirm > 0) {
													$now_status = 'realpay';
												}
												set_bid_status($now_status, $item_id, $params, $data['direction_data']);
													
											} 
										} 	 		 
									} 
								} 
							}
						}
					}
				}
			}
			catch (Exception $e)
			{
				$this->logs($e->getMessage(), $m_id);
				if ($show_error and current_user_can('administrator')) {
					die($e->getMessage());
				}
			}			
			
		}
	}
}

new merchant_anymoney(__FILE__, 'AnyMoney');