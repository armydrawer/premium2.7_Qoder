<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]EpayCore[:en_US][ru_RU:]EpayCore[:ru_RU]
description: [en_US:]EpayCore automatic payouts[:en_US][ru_RU:]авто выплаты EpayCore[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_epaycore')) {
	class paymerchant_epaycore extends Ext_AutoPayut_Premiumbox{
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);			
		}	
		
		function get_map() {
			
			$map = array(
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
			$arrs[] = array('API_ID', 'API_SECRET');
			
			return $arrs;
		}

		function options($options, $data, $m_id, $place) {
			
			$options = pn_array_unset($options, array('checkpay', 'resulturl', 'enableip'));							
			
			$text = '
			<div><strong>CRON:</strong> <a href="' . get_mlink('ap_' . $m_id . '_cron' . chash_url($m_id, 'ap')) . '" target="_blank" rel="noreferrer noopener">' . get_mlink('ap_' . $m_id . '_cron' . chash_url($m_id, 'ap')) . '</a></div>
			';
			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;
		}				

		function get_reserve_lists($m_id, $m_defin) {
			
			$purses = array();
			$purses[$m_id . '_usd'] = 'USD';
			$purses[$m_id . '_rub'] = 'RUB';
			$purses[$m_id . '_uah'] = 'UAH';
			
			return $purses;
		}		
		
		function update_reserve($code, $m_id, $m_defin) { 
		
			$sum = 0;
			
			$purse = trim(str_replace($m_id . '_', '', $code));
			if ($purse) {
							
				try {
						
					$class = new AP_EpayCore($this->name, $m_id, is_isset($m_defin, 'API_ID'), is_isset($m_defin, 'API_SECRET'));
					$balance = $class->get_balance();
										
					if (isset($balance[$purse])) {
						$sum = $balance[$purse];
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
			
			$currency = mb_strtolower($item->currency_code_get);
			$currency = str_replace('rur', 'rub', $currency);			
					
			$account = $item->account_get;
			if (!$account) {
				$error[] = __('No client wallet', 'pn');
			}					
					
			$out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);
					
			if (0 == count($error)) {
				$result = $this->set_ap_status($item, $test);
				if ($result) {	

					$notice = get_text_paymerch($m_id, $item, $sum);
					if (!$notice) { $notice = sprintf(__('Order ID %s', 'pn'), $item->id); }
					$notice = trim(pn_maxf($notice, 200));
				
					try {
						$class = new AP_EpayCore($this->name, $m_id, is_isset($m_defin, 'API_ID'), is_isset($m_defin, 'API_SECRET'));
						$res = $class->payout($notice, $account, $sum, $item_id, $currency);
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
			
			$class = new AP_EpayCore($this->name, $m_id, is_isset($m_defin, 'API_ID'), is_isset($m_defin, 'API_SECRET'));
			$orders = $class->get_history_payout(50);
			
			if (is_array($orders) and count($orders) > 0) {
				$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id'");
				foreach ($items as $item) {
					$item_id = $item->id;
					$trans_out = $item->trans_out;
					if (isset($orders[$trans_out])) {
						$order = $orders[$trans_out];
						$check_status = intval($order['status']);
						
						if (4 == $check_status) {
							
							$params = array(
								'system' => 'system',
								'bid_status' => array('coldsuccess'),
								'm_place' => 'cron ' . $m_id,
								'm_id' => $m_id,
								'm_defin' => $m_defin,
								'm_data' => $m_data,
							);
							set_bid_status('success', $item->id, $params);
								
						} elseif (2 == $check_status) {
							
							$this->reset_cron_status($item, $error_status, $m_id);
								
						}	
					}
				}
			}			
		}		
	}
}

new paymerchant_epaycore(__FILE__, 'EpayCore');