<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Yobit[:en_US][ru_RU:]Yobit[:ru_RU]
description: [en_US:]Yobit automatic payouts[:en_US][ru_RU:]авто выплаты Yobit[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_yobit')) {
	class paymerchant_yobit extends Ext_AutoPayut_Premiumbox {
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
		}

		function get_map() {
			
			$map = array(
				'API_KEY'  => array(
					'title' => '[en_US:]API Key[:en_US][ru_RU:]API Key[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'API_SECRET'  => array(
					'title' => '[en_US:]API secret[:en_US][ru_RU:]API secret[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),					
			);
			
			return $map;
		}
		
		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('API_KEY', 'API_SECRET');
			
			return $arrs;
		}
		
		function options($options, $data, $m_id, $place) {
			
			$options = pn_array_unset($options, array('checkpay', 'note', 'resulturl', 'enableip'));			
			
			$options['line_error_status'] = array(
				'view' => 'line',
			);			
			
			$m_defin = $this->get_file_data($m_id);
			$class = new AP_Yobit($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'API_SECRET'));
			
			$res = $class->list_currencies($place);
			
			$currencies = $class->list_tcurrencies($place);
			
			$options['buy'] = array(
				'view' => 'select',
				'title' => __('Buy additional amount of crypto missing on balance', 'pn'),
				'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn'), '2' => __('Buy and do not withdrawal', 'pn'), '3' => __('Buy entire amount', 'pn')),
				'default' => intval(is_isset($data, 'buy')),
				'name' => 'buy',
				'work' => 'int',
			);			
			
			$buycurr = array('MANUAL' => __('Automatically', 'pn'));
			foreach ($currencies as $curr_code => $curr_title) {
				$curr_code = strtoupper($curr_code);
				$buycurr[$curr_code] = $curr_code;
			}			
			
			$options['buycurr'] = array(
				'view' => 'select',
				'title' => __('Trading operation code', 'pn'),
				'options' => $buycurr,
				'default' => is_isset($data, 'buycurr'),
				'name' => 'buycurr',
				'work' => 'input',
			);

			$text = '
			<div><strong>CRON:</strong> <a href="' . get_mlink('ap_' . $m_id . '_cron' . chash_url($m_id, 'ap')) . '" target="_blank">' . get_mlink('ap_' . $m_id . '_cron' . chash_url($m_id, 'ap')) . '</a></div>
			';
			$options[] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;
		}			

		function get_reserve_lists($m_id, $m_defin) {
			
			$class = new AP_Yobit($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'API_SECRET'));
			$currencies = $class->list_currencies(0);
			
			$purses = array();
			
			foreach ($currencies as $currency => $title) {
				$purses[$m_id . '_' . $currency] = $title;
			} 
			
			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin) { 
		
			$sum = 0;
			$purse = strtolower(trim(str_replace($m_id . '_', '', $code)));  
			if ($purse) {
				try {
					$class = new AP_Yobit($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'API_SECRET'));
					$res = $class->get_balance();
					if (is_array($res) and isset($res[$purse])) {
						$sum = $res[$purse];						
					} 						 
				}
				catch (Exception $e)
				{
					$this->logs($e->getMessage(), $m_id);		
				} 				
			}
			
			return $sum;
		}		

		function prepare_code($currency, $currency_id) {
			global $wpdb;
			
			$currency_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id'");
			if (isset($currency_data->id)) {
				$xml_value = mb_strtoupper(is_xml_value($currency_data->xml_value));
				return $xml_value;
			}			
			
			return $currency;
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
			
			$buy = intval(is_isset($paymerch_data, 'buy')); /* settings */
			
			$class = new AP_Yobit($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'API_SECRET'));
			
			$currency_give = $this->prepare_code($currency_code_give, $currency_id_give);
			$currency_send = $this->prepare_code($currency_code_get, $currency_id_get);
			$sum_send = $sum;			
			
			if (0 == count($error)) {
				$info = $class->info();

				$currencies = is_isset($info, 'currencies');
				if (!is_array($currencies)) { $currencies = array(); }

				$curr_info = is_isset($currencies, $currency_send);
				if (!is_array($curr_info)) {
					$error[] = 'No info from currency ' . $currency_send;
				}

				$withdrawal_enabled = intval(is_isset($curr_info, 'withdrawal_enabled'));
				$fee_withdrawal = is_sum(is_isset($curr_info, 'fee_withdrawal'));

				if (!$withdrawal_enabled) {
					$error[] = 'Withdrawal currency disabled - ' . $currency_send;
				}			
			}
			
			if (0 == count($error)) {	
				if ($buy > 0) {
					
					$balanced = $class->get_balance();
					$balance = is_sum(is_isset($balanced, $currency_send));
					
					$buycurr = is_xml_value(is_isset($paymerch_data, 'buycurr'));
					if (!$buycurr) { $buycurr = 'MANUAL'; }
					
					$symbol1 = '';
					$symbol2 = '';
					if ('MANUAL' != $buycurr) {
						$symbol1 = strtoupper($currency_send . '_' . $buycurr);
						$symbol2 = strtoupper($buycurr . '_' . $currency_send);
					} else {	
						$symbol1 = strtoupper($currency_send . '_' . $currency_give);
						$symbol2 = strtoupper($currency_give . '_' . $currency_send);
					}
					
					$need_sum_send = is_sum($sum_send + $fee_withdrawal);
					
					$buy_sum = 0;
					if (1 == $buy or 2 == $buy) {
						if ($need_sum_send > $balance) {
							$buy_sum = $need_sum_send - $balance;
						}	
					}	
					if (3 == $buy) {
						$buy_sum = $need_sum_send;
					}	
					
					$buy_sum = is_sum($buy_sum, 8, 'up');
					if ($buy_sum > 0) {
						
						$pool = '';
						$defi = is_isset($info, 'defi');
						if (!is_array($defi)) { $defi = array(); }

						if (isset($defi[$symbol1])) {
							$pool = $symbol1;
						} elseif(isset($defi[$symbol2])) {
							$pool = $symbol2;
						}

						if (strlen($pool) > 0) {
						
							$d = array(
								'pool' => $pool,
								'GetCurrency' => $currency_send,
								'GetAmount' => $buy_sum,
							);
							$defi_res = $class->defi('DefiSwap', $d);
							if (isset($defi_res['success']) and 1 == $defi_res['success']) {
								sleep(5);
							} else {
								$error[] = __('Failed to buy cryptocurrency', 'pn');
							}
						
						} else {	
							$error[] = 'pair not trading ' . $symbol1;
						}						
						
					}
				
					if (2 == $buy) {
						$error[] = __('Cryptocurrency only', 'pn');
					}	
				}
			}			
					
			$dest_tag = trim(is_isset($unmetas, 'dest_tag'));		
			
			if (0 == count($error)) {
				$result = $this->set_ap_status($item, $test);
				if ($result) {
					try {
						$trans_id = $class->create_payout($currency_send, $account, $sum_send, 'add');
						if (!$trans_id) {
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
					pn_display_mess(__('Automatic payout is done', 'pn'), __('Automatic payout is done', 'pn'), 'true');
				} 
				
			}
		}	

		function cron($m_id, $m_defin, $m_data) {
			global $wpdb;
			
			$error_status = is_status_name(is_isset($m_data, 'error_status'));
			
			$class = new AP_Yobit($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'API_SECRET'));
			$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id'");
			foreach ($items as $item) {
				$currency = mb_strtoupper($item->currency_code_get);
				$currency_id_get = intval($item->currency_id_get);
				$trans_id = trim($item->trans_out);
				if ($trans_id) {
					$currency_prepare = $this->prepare_code($currency, $currency_id_get);
					$res = $class->get_payout_info($currency_prepare, $trans_id);
					if (isset($res['address'], $res['status'])) {
						$check_status = strtolower($res['status']); 
						$txt_id = pn_strip_input(is_isset($res, 'txid'));
						if ('completed' == $check_status) {
							
							$params = array(
								'txid_out' => $txt_id,
								'system' => 'system',
								'bid_status' => array('coldsuccess'),
								'm_place' => 'cron ' . $m_id,
								'm_id' => $m_id,
								'm_defin' => $m_defin,
								'm_data' => $m_data,
							);
							set_bid_status('success', $item->id, $params);
							
						} elseif (in_array($check_status, array('canceled'))) {
							
							$this->reset_cron_status($item, $error_status, $m_id);
							
						}
					}	
				}
			}
		}		
	}
}

new paymerchant_yobit(__FILE__, 'Yobit');