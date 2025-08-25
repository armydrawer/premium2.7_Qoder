<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Bitconce[:en_US][ru_RU:]Bitconce[:ru_RU]
description: [en_US:]Bitconce automatic payouts[:en_US][ru_RU:]авто выплаты Bitconce[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_bitconce')) {
	class paymerchant_bitconce extends Ext_AutoPayut_Premiumbox {
		
		public $currency_lists = '';
		public $directions = '';
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			$this->currency_lists = array('RUB', 'BTC');
			$this->directions = array(
				'0' => '--',
				'1' => 'qiwi',
				'2' => 'yandex',
				'3' => 'banks',
				'4' => 'SBP',
			);			
		}

		function get_map() {
			
			$map = array(
				'TOKEN'  => array(
					'title' => '[en_US:]Token[:en_US]',
					'view' => 'input',
					'hidden' => 1,
				),				
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('TOKEN');
			
			return $arrs;
		}
		
		function options($options, $data, $m_id, $place) {
			
			$m_defin = $this->get_file_data($m_id);
			
			$options = pn_array_unset($options, array('checkpay', 'note', 'resulturl', 'enableip'));		
			
			$options['line_error_status'] = array(
				'view' => 'line',
			);

			$options['direction'] = array(
				'view' => 'select',
				'title' => __('Direction', 'pn'),
				'options' => $this->directions,
				'default' => intval(is_isset($data, 'direction')),
				'name' => 'direction',
				'work' => 'int',
			);			

			$text = '
			<div><strong>CRON:</strong> <a href="' . get_mlink('ap_' . $m_id . '_cron' . chash_url($m_id, 'ap')) . '" target="_blank">' . get_mlink('ap_' . $m_id . '_cron' . chash_url($m_id, 'ap')) . '</a></div>
			';
			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;
		}			

		function get_reserve_lists($m_id, $m_defin) {
			
			$currencies = $this->currency_lists;
			
			$purses = array();
			
			foreach ($currencies as $currency) {
				$purses[$m_id . '_' . strtolower($currency)] = strtoupper($currency);
			} 
			
			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin) { 
		
			$sum = 0;
			$purse = strtolower(trim(str_replace($m_id . '_', '', $code))); 
			if ($purse) {
				
				try {
					
					$class = new AP_Bitconce($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
					$res = $class->get_balance();

					if (isset($res[$purse])) {
						$sum = is_sum($res[$purse]);
					}
					
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
					
			$out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2, 'down');

			$class = new AP_Bitconce($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
			$direction = intval(is_isset($paymerch_data, 'direction'));
			$dir = '';
			$bank_name = '';
			if ($direction and isset($this->directions[$direction])) {
				$dir = $this->directions[$direction];
			}			
			if (4 == $direction) {
				$dir = 'sbp';
				$bn_arr = array('sberbank', 'tinkoff', 'raiffeisenbank');
				$bank_name = strtolower(trim(is_isset($unmetas, 'bankname')));
				if (!in_array($bank_name, $bn_arr)) {
					$bank_name = 'sberbank';
				}
			}

			if (0 == count($error)) {
				$result = $this->set_ap_status($item, $test);
				if ($result) {
					try {
						$res = $class->get_payout($item_id, $account, $sum, $dir, $bank_name);
						if (isset($res['status'],$res['orders'],$res['orders'][0])) {
							$trans_id = intval(is_isset($res['orders'][0],'id'));
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
					'm_place' => $modul_place . ' ' . $m_id,
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
			
			$class = new AP_Bitconce($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
			
			$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id'");
			foreach ($items as $item) {
				$order_id = $item->id;
				$order = $class->get_status($order_id);
				if (isset($order['orders'], $order['orders'][0])) {
					$trans_out = $order['orders'][0]['exchange_ids'];
					$check_status = $order['orders'][0]['status']; 
					$st = array('Ready', 'Finished');
					
					if (in_array($check_status, $st)) {
							
						$params = array(
							'trans_out' => $trans_out,
							'system' => 'system',
							'bid_status' => array('coldsuccess'),
							'm_place' => 'cron ' . $m_id,
							'm_id' => $m_id,
							'm_defin' => $m_defin,
							'm_data' => $m_data,
						);
						set_bid_status('success', $item->id, $params);
							
					} elseif (in_array($check_status, array('Autocanceled', 'Canceled'))) {
							
						$this->reset_cron_status($item, $error_status, $m_id);
							
					}
				}	
			}
		}		
	}
}

new paymerchant_bitconce(__FILE__, 'Bitconce');