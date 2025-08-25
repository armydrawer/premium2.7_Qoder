<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Exnode[:en_US][ru_RU:]Exnode[:ru_RU]
description: [en_US:]Exnode automatic payouts[:en_US][ru_RU:]авто выплаты Exnode[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_exnode')) {
	class paymerchant_exnode extends Ext_AutoPayut_Premiumbox {
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 0);
			
			$ids = $this->get_ids('paymerchants', $this->name);
			foreach ($ids as $id) {
				add_action('premium_merchant_ap_' . $id . '_callback' . hash_url($id, 'ap'), array($this, 'merchant_callback'));
			}					
		}

		function get_map() {
			
			$map = array(
				'PRIVATE_KEY'  => array(
					'title' => '[en_US:]Private key[:en_US][ru_RU:]Private key[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),			
				'PUBLIC_KEY'  => array(
					'title' => '[en_US:]Public key[:en_US][ru_RU:]Public key[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),				
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('PRIVATE_KEY', 'PUBLIC_KEY');
			
			return $arrs;
		}
		
		function get_methods($place, $m_id) {
			
			$m_defin = $this->get_file_data($m_id);
			$class = new AP_Exnode($this->name, $m_id, is_isset($m_defin, 'PRIVATE_KEY'), is_isset($m_defin, 'PUBLIC_KEY'));
			$methods = $class->list_currencies($place);
			
			return $methods;
		}		
		
		function options($options, $data, $m_id, $place) {
			
			$options = pn_array_unset($options, array('checkpay', 'note', 'cronhash'));		
			
			$options['line_error_status'] = array(
				'view' => 'line',
			);
			
			$options['currency_code'] = array(
				'view' => 'select',
				'title' => __('Currency code', 'pn'),
				'options' => $this->get_methods($place, $m_id),
				'default' => is_isset($data, 'currency_code'),
				'name' => 'currency_code',
				'work' => 'input',
			);					
			
			return $options;
		}			

		function get_reserve_lists($m_id, $m_defin) {
			
			$currencies = $this->get_methods(0, $m_id);
			
			$purses = array();
			
			foreach ($currencies as $currency) {
				$purses[$m_id . '_' . strtolower($currency)] = strtoupper($currency);
			} 
			
			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin) { 
		
			$sum = 0;
			$purse = strtoupper(trim(str_replace($m_id . '_', '', $code))); 
			if ($purse) {
				
				try {

					$class = new AP_Exnode($this->name, $m_id, is_isset($m_defin, 'PRIVATE_KEY'), is_isset($m_defin, 'PUBLIC_KEY'));
					$balance = $class->get_balance($purse);
					$sum = is_sum($balance);
					
				}
				catch (Exception $e)
				{
					$this->logs($e->getMessage(), $m_id);		
				} 				
			}
			
			return $sum;
		}		

		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {
			
			$item_id = $item->id;			
			$trans_id = 0;				
			
			$currency_code_give = strtoupper($item->currency_code_give);
			$currency_code_get = strtoupper($item->currency_code_get);
			
			$currency_id_give = intval($item->currency_id_give);
			$currency_id_get = intval($item->currency_id_get);			
							
			$account = $item->account_get;
					
			if (!$account) {
				$error[] = __('Wrong client wallet', 'pn');
			}			
					
			$out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 8);

			$currency_code = strtoupper(pn_strip_input(is_isset($paymerch_data, 'currency_code')));
			$currencies = $this->get_methods(0, $m_id);
			if (!isset($currencies[$currency_code])) {
				if (count($currencies) > 0) {
					$currency_code = array_key_first($currencies);
				} else {
					$currency_code = 'no';
				}
			}		

			$dest_tag = trim(is_isset($unmetas, 'dest_tag'));
			
			if (0 == count($error)) {
				$result = $this->set_ap_status($item, $test);
				if ($result) {
					try {
						$class = new AP_Exnode($this->name, $m_id, is_isset($m_defin, 'PRIVATE_KEY'), is_isset($m_defin, 'PUBLIC_KEY'));
						
						$call_back_url = get_mlink('ap_' . $m_id . '_callback' . hash_url($m_id, 'ap'));
						$res = $class->create_payout($currency_code, $sum, 'ap' . $item_id, $call_back_url, $account, $dest_tag);
						if (isset($res['tracker_id'])) {
							$trans_id = $res['tracker_id'];
						} else {
							$error[] = __('Payout error', 'pn');
							$pay_error = 1;	
						}
					}
					catch (Exception $e)
					{
						$error[] = $e;
						$pay_error = 1;
					} 		
				} else {
					$error[] = 'Database error';
				}				
			}
					
			if (count($error) > 0) {
				
				$this->reset_ap_status($error, $pay_error, $item, $place, $m_id, $test);
				
			} else {	
						
				$params = array(
					'trans_out' => $trans_id,
					'out_sum' => $out_sum,
					'system' => 'user',
					'm_place' => $modul_place. ' ' .$m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);
				set_bid_status('coldsuccess', $item_id, $params, $direction); 	
						
				if ('admin' == $place) {
					pn_display_mess(__('Automatic payout is done', 'pn'),__('Automatic payout is done', 'pn'), 'true');
				} 		
			}
		}	

		function merchant_callback() {
			global $wpdb;
		
			$m_id = key_for_url('_callback', 'ap_');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_paymerch_data($m_id);
			
			$callback = file_get_contents('php://input');
			$post = @json_decode($callback, true);
			
			do_action('paymerchant_secure', $this->name, $post, $m_id, $m_defin, $m_data);
			
			$error_status = is_status_name(is_isset($m_data, 'error_status'));
			
			$tracker_id = trim(is_isset($post, 'tracker_id'));
			if (strlen($tracker_id) > 0) {
				$class = new AP_Exnode($this->name, $m_id, is_isset($m_defin, 'PRIVATE_KEY'), is_isset($m_defin, 'PUBLIC_KEY'));
				$order = $class->get_status($tracker_id);
				if (isset($order['amount'], $order['status'], $order['token_major_name'], $order['type'], $order['client_transaction_id']) and 'OUT' == $order['type']) {
					$order_id = intval(str_replace('ap', '', $order['client_transaction_id']));
					if ($order_id > 0) {				
						$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id' AND id = '$order_id'");
						if (isset($item->id)) {
							$order_id = $item->id;	
							$check_status = strtoupper($order['status']); 
							$st = array('SUCCESS');
										
							if (in_array($check_status, $st)) {
												
								$params = array(
									'txid_out' => is_isset($order, 'hash'),
									'trans_out' => is_isset($order, 'tracker_id'),
									'system' => 'system',
									'bid_status' => array('coldsuccess'),
									'm_place' => $m_id . '_callback',
									'm_id' => $m_id,
									'm_defin' => $m_defin,
									'm_data' => $m_data,
								);
								set_bid_status('success', $item->id, $params);
												
							} elseif (in_array($check_status, array('ERROR'))) {
												
								$this->reset_cron_status($item, $error_status, $m_id);
												
							}		
								
						}			
					}
				}
			
				echo 'OK';
				exit;
			
			}
			
			echo 'No tracker id';
			exit;			
		}					
	}
}

new paymerchant_exnode(__FILE__, 'Exnode');