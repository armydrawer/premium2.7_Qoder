<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]MoneyGo[:en_US][ru_RU:]MoneyGo[:ru_RU]
description: [en_US:]MoneyGo automatic payouts[:en_US][ru_RU:]авто выплаты MoneyGo[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_moneygo')) {
	class paymerchant_moneygo extends Ext_AutoPayut_Premiumbox {
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 0);
		}

		function get_map() {
			
			$map = array(
				'CLIENT_ID'  => array(
					'title' => '[en_US:]Client ID[:en_US]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'CLIENT_SECRET'  => array(
					'title' => '[en_US:]Client Secret[:en_US]',
					'view' => 'input',
					'hidden' => 1,
				),
				'U_WALLET'  => array(
					'title' => '[en_US:]U-wallet[:en_US][ru_RU:]U-кошелек[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'E_WALLET'  => array(
					'title' => '[en_US:]E-wallet[:en_US][ru_RU:]E-кошелек[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'R_WALLET'  => array(
					'title' => '[en_US:]R-wallet[:en_US][ru_RU:]R-кошелек[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),				
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('CLIENT_ID', 'CLIENT_SECRET');
			
			return $arrs;
		}
		
		function options($options, $data, $m_id, $place) {
			
			$m_defin = $this->get_file_data($m_id);
			
			$options = pn_array_unset($options, array('checkpay', 'resulturl', 'cronhash', 'enableip'));					
			
			return $options;
		}			

		function get_reserve_lists($m_id, $m_defin) {
		
			$purses = array();
			$currencies = array('USD', 'EUR', 'RUB');
			foreach ($currencies as $currency) {
				$purses[$m_id . '_' . strtolower($currency)] = strtoupper($currency);
			} 
			
			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin) { 
		
			$sum = '0';
			$purse = strtoupper(trim(str_replace($m_id . '_', '', $code))); 
			if ($purse) {
				try {
					$now_purse = '';
					if ('USD' == $purse) {
						$now_purse = is_isset($m_defin, 'U_WALLET');
					} elseif ('EUR' == $purse) {
						$now_purse = is_isset($m_defin, 'E_WALLET');
					} elseif ('RUB' == $purse) {
						$now_purse = is_isset($m_defin, 'R_WALLET');
					}
					$class = new AP_MoneyGo($this->name, $m_id, is_isset($m_defin, 'CLIENT_ID'), is_isset($m_defin, 'CLIENT_SECRET'), is_isset($m_defin, 'TOKEN'), '');
					$balance = $class->get_balance();
					if (isset($balance[$now_purse])) {
						$sum = is_sum($balance[$now_purse]);
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
					
			$out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);

			$class = new AP_MoneyGo($this->name, $m_id, is_isset($m_defin, 'CLIENT_ID'), is_isset($m_defin, 'CLIENT_SECRET'), is_isset($m_defin, 'TOKEN'), '');
					
			$notice = get_text_paymerch($m_id, $item, $sum);
			$notice = trim(pn_maxf($notice, 200));

			$from_wallet = '';
			if ('RUB' == $currency_code_get) {
				$from_wallet = is_isset($m_defin, 'R_WALLET');
			} elseif ('USD' == $currency_code_get) {
				$from_wallet = is_isset($m_defin, 'U_WALLET');
			} elseif ('EUR' == $currency_code_get) {	
				$from_wallet = is_isset($m_defin, 'E_WALLET');
			}			
					
			if (0 == count($error)) {
				$result = $this->set_ap_status($item, $test);
				if ($result) {
					try {
						$trans_id = $class->send($sum, $from_wallet, $account, $notice);
						if ($trans_id) {

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
				set_bid_status('success', $item_id, $params, $direction); 	
						
				if ('admin' == $place) {
					pn_display_mess(__('Automatic payout is done', 'pn'), __('Automatic payout is done', 'pn'), 'true');
				} 	
				
			}
		}		
	}
}

new paymerchant_moneygo(__FILE__, 'MoneyGo');