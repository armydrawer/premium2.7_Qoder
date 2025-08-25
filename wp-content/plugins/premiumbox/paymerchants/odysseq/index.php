<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Odysseq[:en_US][ru_RU:]Odysseq[:ru_RU]
description: [en_US:]Odysseq automatic payouts[:en_US][ru_RU:]авто выплаты Odysseq[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_odysseq')) {
	class paymerchant_odysseq extends Ext_AutoPayut_Premiumbox {
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			$ids = $this->get_ids('paymerchants', $this->name);
			foreach ($ids as $m_id) {
				add_action('premium_merchant_ap_' . $m_id . '_status' . hash_url($m_id, 'ap'), array($this, 'merchant_status'));
			}			
		}

		function get_map() {
			
			$map = array(
				'TOKEN'  => array(
					'title' => '[en_US:]Token[:en_US][ru_RU:]Token[:ru_RU]',
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
			
			$options = pn_array_unset($options, array('note', 'checkpay'));			

			$text = '
			<div><strong>Callback URL:</strong> <a href="' . get_mlink('ap_' . $m_id . '_status' . hash_url($m_id, 'ap')) . '" target="_blank">' . get_mlink('ap_' . $m_id . '_status' . hash_url($m_id, 'ap')) . '</a></div>
			<div><strong>CRON:</strong> <a href="' . get_mlink('ap_'. $m_id .'_cron' . chash_url($m_id, 'ap')) . '" target="_blank">' . get_mlink('ap_' . $m_id . '_cron' . chash_url($m_id, 'ap')) . '</a></div>
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
			$purses[$m_id . '_card'] = 'Card';
			$purses[$m_id . '_wallet'] = 'Wallet';

			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin) { 
		
			$sum = 0;
			$purse = trim(str_replace($m_id . '_', '', $code)); 
			if ($purse) {
				try {
					$class = new AP_Odysseq($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
					$res = $class->get_balance();
							
					if (isset($res[$purse])) {
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

		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {
			
			$item_id = $item->id;			
			$trans_id = 0;				
			
			$currency = mb_strtolower($item->currency_code_get);
			$currency = str_replace('RUR', 'RUB', $currency);
						
			$account = $item->account_get;
					
			if (!$account) {
				$error[] = __('Wrong client wallet', 'pn');
			}			
					
			$out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);			
			
			if (0 == count($error)) {
				$result = $this->set_ap_status($item, $test);
				if ($result) {
					try {
						$class = new AP_Odysseq($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
						$trans_status = $class->send('ap' . $item_id, $sum, $account);
						if (!$trans_status) {
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
			
			$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id'");
			foreach ($items as $item) { 
				$trans_out = trim($item->trans_out);
				try {
					$class = new AP_Odysseq($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
					$data = $class->status('ap' . $item->id);
					if (isset($data['paymentInfo'], $data['paymentInfo']['status'])) {
						$status = strtoupper($data['paymentInfo']['status']);
						$st_success = array('SUCCESS');
						$st_error = array('CANCELED');
						
						if (in_array($status, $st_success)) {
							
							$params = array(
								'system' => 'system',
								'bid_status' => array('coldsuccess'),
								'm_place' => 'cron ' . $m_id . '_cron',
								'm_id' => $m_id,
								'm_defin' => $m_defin,
								'm_data' => $m_data,
							); 
							set_bid_status('success', $item->id, $params);	
										
						} elseif (in_array($status, $st_error)) {
										
							update_bids_meta($item->id, 'in_progress', 0);
							$this->reset_cron_status($item, $error_status, $m_id);
										
						}						
					}	
				}
				catch(Exception $e) {
					$this->logs($e->getMessage(), $m_id);		
				}
			}
		}
		
		function merchant_status() {
			global $wpdb;	
			
			$m_id = key_for_url('_status', 'ap_');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_paymerch_data($m_id);
			
			$callback = file_get_contents('php://input');
			$post = @json_decode($callback, true);
			
			do_action('paymerchant_secure', $this->name, $post, $m_id, $m_defin, $m_data);
			
			$error_status = is_status_name(is_isset($m_data, 'error_status'));
			
			if (isset($post['orderId'], $post['type'])) {
				if ('OUT' == $post['type']) {
					$order_id = str_replace('ap', '', $post['orderId']);
					$order_id = intval($order_id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id' AND id = '$order_id'");
					if (isset($item->id)) {
						try {
							$class = new AP_Odysseq($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
							$data = $class->status('ap' . $item->id);
							if (isset($data['paymentInfo'], $data['paymentInfo']['status'])) {
								$status = strtoupper($data['paymentInfo']['status']);
								$st_success = array('SUCCESS');
								$st_error = array('CANCELED');
									
								if (in_array($status, $st_success)) {
										
									$params = array(
										'system' => 'system',
										'bid_status' => array('coldsuccess'),
										'm_place' => 'cron ' . $m_id . '_cron',
										'm_id' => $m_id,
										'm_defin' => $m_defin,
										'm_data' => $m_data,
									); 
									set_bid_status('success', $item->id, $params);	
													
								} elseif (in_array($status, $st_error)) {
													
									$this->reset_cron_status($item, $error_status, $m_id);
													
								}						
							}	
						}
						catch(Exception $e) {
							$this->logs($e->getMessage(), $m_id);			
						}					
					}
				}
			}			

			echo '{"status":200}';
			exit;
		}		
	}
}

new paymerchant_odysseq(__FILE__, 'Odysseq');