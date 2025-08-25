<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Internal account[:en_US][ru_RU:]Внутренний счет[:ru_RU]
description: [en_US:]merchant for internal account[:en_US][ru_RU:]мерчант для внутреннего счета[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_iac')) {
	class merchant_iac extends Ext_Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
		}

		function options($options, $data, $id, $place) {
			
			$options = pn_array_unset($options, 'corr');
			$options = pn_array_unset($options, 'enableip');
			$options = pn_array_unset($options, 'cronhash');
			$options = pn_array_unset($options, 'resulturl');
			$options = pn_array_unset($options, 'help_resulturl');
			$options = pn_array_unset($options, 'check_api');
			$options = pn_array_unset($options, 'center_title');
			$options = pn_array_unset($options, 'check');
			$options = pn_array_unset($options, 'invalid_ctype');
			$options = pn_array_unset($options, 'invalid_minsum');
			$options = pn_array_unset($options, 'invalid_maxsum');
			$options = pn_array_unset($options, 'show_error');
			$options = pn_array_unset($options, 'pagenote');
			$options = pn_array_unset($options, 'sfp');
			$options = pn_array_unset($options, 'workstatus');
			
			return $options;
		}		

		function merch_type($m_id) {
			
			return 'myaction'; 
		}

		function myaction($m_id, $pay_sum, $direction) {
			global $bids_data, $wpdb;	
			
			$script = get_mscript($m_id);
			if ($script and $script == $this->name) {
				$m_defin = $this->get_file_data($m_id);
				$m_data = get_merch_data($m_id);
				
				$pay_sum = is_sum($pay_sum);				
				$text_pay = get_text_pay($m_id, $bids_data, $pay_sum);
				if (strlen($text_pay) < 1) { $text_pay = 'Bid id ' . $bids_data->id; }
				
				$ui = wp_get_current_user();
				$user_id = intval($ui->ID);	
				if ($user_id and function_exists('get_user_iac')) {
					$amount = is_sum($pay_sum);
					$now_sum = get_user_iac($user_id, $bids_data->currency_code_id_give);
					if ($now_sum >= $amount) {
							
						$arr = array();
						$arr['create_date'] = current_time('mysql');
						$arr['title'] = $text_pay;
						$arr['amount'] = -1 * $amount;
						$arr['currency_code_id'] = $bids_data->currency_code_id_give;
						$arr['user_id'] = $user_id;
						$arr['bid_id'] = $bids_data->id;
						$arr['status'] = 1;
						$result = $wpdb->insert($wpdb->prefix . 'iac', $arr);
								
						if ($result) {
								
							$params = array(
								'sum' => $amount,
								'bid_sum' => $amount,
								'bid_corr_sum' => $amount,
								'm_place' => $m_id,
								'm_id' => $m_id,
								'm_data' => $m_data,
								'm_defin' => $m_defin,
							);
							set_bid_status('realpay', $bids_data->id, $params, $direction);

						} else {
							pn_display_mess(__('System error','pn'));
						}
						
					} else {
						pn_display_mess(__('Not enough money', 'pn'));
					}
				} else {
					pn_display_mess(__('Error! You must authorize', 'pn'));
				}
			}
		}
	}
}

new merchant_iac(__FILE__, 'Internal account');