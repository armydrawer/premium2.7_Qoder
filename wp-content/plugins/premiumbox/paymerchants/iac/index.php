<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Internal account[:en_US][ru_RU:]Внутренний счет[:ru_RU]
description: [en_US:]auto payouts for internal account[:en_US][ru_RU:]авто выплаты для внутреннего счета[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_iac')) {
	class paymerchant_iac extends Ext_AutoPayut_Premiumbox {
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title);	
		}
		
		function options($options, $data, $id, $place) {
			
			$options = pn_array_unset($options, array('checkpay', 'resulturl', 'error_status', 'cronhash', 'enableip'));
			
			return $options;
		}		

		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {
			global $wpdb;
		
			$trans_id = 0;
			$item_id = $item->id;
			
			$currency_id = intval($item->currency_code_id_get);
			$user_id = intval($item->user_id);
			$account = trim(strtoupper($item->account_get));
			$currency_code = strtoupper($item->currency_code_get);
			
			if (strlen($account) < 1 and $user_id) {
				$account = strtoupper($currency_code . '_' . $user_id);
			}
			
			if (strlen($account) < 1) {
				$error[] = __('Wrong client wallet', 'pn');
			}			
			
			$account_arr = explode('_', $account); 
			$currency_code_from_account = strtoupper(trim(is_isset($account_arr, 0)));
			$user_id_from_account = intval(is_isset($account_arr, 1));
			
			if ($currency_code_from_account != $currency_code) {
				$error[] = __('Wrong client wallet', 'pn');
			}	

			$ui_check = get_userdata($user_id_from_account);
			if (!isset($ui_check->ID)) {
				$error[] = __('Wrong client ID', 'pn');
			}
					
			$out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data));

			$notice = get_text_paymerch($m_id, $item, $sum);
			if (strlen($notice) < 1) { $notice = 'Bid id ' . $item->id; }

			if (0 == count($error)) {
				$result = $this->set_ap_status($item, $test);
				if ($result) {
					
					$arr = array();
					$arr['create_date'] = current_time('mysql');
					$arr['title'] = $notice;
					$arr['amount'] = $sum;
					$arr['currency_code_id'] = $currency_id;
					$arr['user_id'] = $user_id_from_account;
					$arr['bid_id'] = $item_id;
					$arr['status'] = 1;
					$result = $wpdb->insert($wpdb->prefix . 'iac', $arr);
					$trans_id = $wpdb->insert_id;
						
					if (!$trans_id) {
						$error[] = __('Payout error', 'pn');
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
					'system' => 'user',
					'out_sum' => $out_sum,
					'm_place' => $modul_place. ' ' . $m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);
				set_bid_status('success', $item_id, $params, $direction);  						
						 
				if ('admin' == $place) {
					pn_display_mess(__('Automatic payout is done', 'pn'), __('Automatic payout is done', 'pn'), 'true');
				} 			
			}							
		}				
	}
}

new paymerchant_iac(__FILE__, 'Internal account');