<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]AnyMoney[:en_US][ru_RU:]AnyMoney[:ru_RU]
description: [en_US:]AnyMoney automatic payouts[:en_US][ru_RU:]авто выплаты AnyMoney[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_anymoney')) {
	class paymerchant_anymoney extends Ext_AutoPayut_Premiumbox {
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			$this->currency_list = array('USD', 'RUB', 'BCHABC', 'BTC', 'UAH', 'LTC', 'ETH', 'USDT', 'EUR');
			
			$ids = $this->get_ids('paymerchants', $this->name);
			foreach ($ids as $id) {
				add_action('premium_merchant_ap_'. $id .'_status' . hash_url($id, 'ap'), array($this,'merchant_status'));
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

		function options($options, $data, $id, $place) {
			
			$m_defin = $this->get_file_data($id);
			
			$options = pn_array_unset($options, 'note');			
			
			$s_curr = array();
			
			if (1 == $place) {
			
				try {
					$types = array();
					$types[''] = '--' . __('No', 'pn') . '--';
					$class = new AP_AnyMoney($this->name, $id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'MERCHANT_ID'));
					$res = $class->get_pwcurrency();
					
					update_option('anymoney_data', $res);
					
					if (isset($res['result']) and is_array($res['result'])) {
						foreach ($res['result'] as $res_key => $res_arr) {
							$in = is_isset($res_arr, 'out');
							if (is_array($in)) {
								$n = array();
								foreach ($in as $curr => $curr_data) {
									$n[] = $curr;
									$s_curr[$curr] = $curr;
								}
								$types[$res_key] = $res_key . ' (' . join(', ', $n) . ')';
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
				'title' => __('Converting from another currency (specify code)', 'pn'),
				'default' => is_isset($data, 'payment_convert'),
				'name' => 'payment_convert',
				'work' => 'input',
			);			
			$options['help_payment_convert'] = array(
				'view' => 'help',
				'title' => __('More info', 'pn'),
				'default' => implode(', ', $s_curr),
			);
			
			$text = '
			<div><strong>Callback URL:</strong> <a href="' . get_mlink('ap_' . $id . '_status' . hash_url($id, 'ap')) . '" target="_blank" rel="noreferrer noopener">' . get_mlink('ap_' . $id . '_status' . hash_url($id, 'ap')) . '</a></div>
			<div><strong>CRON:</strong> <a href="' . get_mlink('ap_' . $id .'_cron' . chash_url($id, 'ap')) . '" target="_blank" rel="noreferrer noopener">'. get_mlink('ap_' . $id . '_cron' . chash_url($id, 'ap')) . '</a></div>
			';
			$options[] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;
		}				

		function get_reserve_lists($m_id, $m_defin) {
			
			$purses = array();
			foreach ($this->currency_list as $curr) {
				$purses[$m_id . '_' . strtolower($curr)] = $curr;				
			}
			
			return $purses;
		}		
		
		function update_reserve($code, $m_id, $m_defin) { 
		
			$sum = 0;
			$purses = $this->get_reserve_lists($m_id, $m_defin);
			$purse = trim(is_isset($purses, $code));
			if ($purse) {
						
				try{
					
					$class = new AP_AnyMoney($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'MERCHANT_ID'));
					$res = $class->get_balance();
							
					$rezerv = '-1';
								
					if (isset($res[$purse])) {
						$rezerv = $res[$purse];
					}	
							
					if ('-1' != $rezerv) {
						$sum = $rezerv;
					}								 
						
				}
				catch (Exception $e)
				{
					$this->logs($e->getMessage(), $m_id);		
				} 				

			}
			
			return $sum;
		}		

		function search_in_history($item_id, $m_defin, $m_id) {
			
			$search_text = '';
						
			try {
				$class = new AP_AnyMoney($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'MERCHANT_ID'));
				$hres = $class->get_history_payout('200'); 
				if ('notapi' != $hres) {
					if (isset($hres['out_' . $item_id])) {
						$search_text = sprintf(__('Payment ID %s has already been paid', 'pn'), $item_id);	
					} 
				} else {
					$search_text = __('Failed to retrieve payment history', 'pn');
				}							
			}
			catch(Exception $e) {
				$search_text = $e->getMessage();
			}					
			
			return $search_text;
		}

		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {
			
			$item_id = $item->id;
			$trans_id = 0;			
			
			$currency = mb_strtoupper($item->currency_code_get);
			$currency = str_replace(array('RUR'), 'RUB', $currency);						
					
			$account = $item->account_get;
			if (!$account) {
				$error[] = __('Wrong client wallet', 'pn');
			}					
					
			$out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data));
			
			$payment_type = pn_strip_input(is_isset($paymerch_data, 'payment_type'));
			if (!$payment_type) {
				$error[] = __('Transaction type is not selected', 'pn');
			}
			
			$payment_convert = trim(is_isset($paymerch_data, 'payment_convert'));
			$payment_convert = str_replace('RUR', 'RUB', $payment_convert);
			
			$any_data = get_option('anymoney_data');
			if (isset($any_data[$payment_type]) and isset($any_data[$payment_type][$currency])) {
				$min = is_sum($any_data[$payment_type][$currency]['out']['tech_min']);
				$max = is_sum($any_data[$payment_type][$currency]['out']['tech_max']);
				
				if ($sum < $min) {
					$error[] = sprintf(__('Minimum payment amount is %s', 'pn'), $min);
				}
				if ($sum > $max and $max > 0) {
					$error[] = sprintf(__('Maximum payment amount is %s', 'pn'), $max);
				}				
			}
					
			if (0 == count($error)) {
				$result = $this->set_ap_status($item, $test);
				if ($result) {				
					try {
						$class = new AP_AnyMoney($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'MERCHANT_ID'));
						$res = $class->payout($sum, $currency, $item_id, $payment_type, $account, get_mlink('ap_' . $m_id . '_status' . hash_url($m_id, 'ap')), $payment_convert);
						if (1 == $res['error']) {
							$error[] = __('Payout error', 'pn');
							$pay_error = 1;
						} else {
							$trans_id = $res['trans_id'];
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
					'from_account' => is_isset($m_defin, 'MERCHANT_ID'),
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

		function merchant_status() {
			
			$m_id = key_for_url('_status', 'ap_');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_paymerch_data($m_id);

			do_action('paymerchant_secure', $this->name, '', $m_id, $m_defin, $m_data);
			
			$this->anymoney_cron($m_id, $m_defin, $m_data);
			
			echo 'OK';
			exit;
		}

		function cron($m_id, $m_defin, $m_data) {
			
			$this->anymoney_cron($m_id, $m_defin, $m_data);
			
		}

		function anymoney_cron($m_id, $m_defin, $m_data) {
			global $wpdb;
			
			$error_status = is_status_name(is_isset($m_data, 'error_status'));
			
			$class = new AP_AnyMoney($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'MERCHANT_ID'));
			$orders = $class->get_history_payout('200');
			if (is_array($orders)) {
				$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id'");
				foreach ($items as $item) {
					$item_id = $item->id;
					if (isset($orders['out_' . $item_id])) {
						$order = $orders['out_' . $item_id];
						$check_status = $order['status'];
						
						if ('done' == $check_status) {
							
							$params = array(
								'system' => 'system',
								'trans_out' => is_isset($order, 'txid'),
								'bid_status' => array('coldsuccess'),
								'm_place' => 'cron ' . $m_id,
								'm_id' => $m_id,
								'm_defin' => $m_defin,
								'm_data' => $m_data,
							);
							set_bid_status('success', $item->id, $params);
								
						} elseif (!in_array($check_status, array('pending', 'wait', 'started'))) {
							
							$this->reset_cron_status($item, $error_status, $m_id);
								
						}	
					}
				}
			}			
		}		
	}
}

new paymerchant_anymoney(__FILE__, 'AnyMoney');