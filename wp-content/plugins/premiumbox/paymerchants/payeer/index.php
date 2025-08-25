<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Payeer[:en_US][ru_RU:]Payeer[:ru_RU]
description: [en_US:]Payeer automatic payouts[:en_US][ru_RU:]авто выплаты Payeer[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_payeer')) {
	class paymerchant_payeer extends Ext_AutoPayut_Premiumbox {
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title);
		}

		function get_map() {
			
			$map = array(
				'ACCOUNT_NUMBER'  => array(
					'title' => '[en_US:]Wallet number[:en_US][ru_RU:]Номер кошелька[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'API_ID'  => array(
					'title' => '[en_US:]API ID[:en_US][ru_RU:]API ID[:ru_RU]',
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
			$arrs[] = array('ACCOUNT_NUMBER', 'API_ID', 'API_KEY');
			
			return $arrs;
		}

		function options($options, $data, $id, $place) {
			
			$options = pn_array_unset($options, array('checkpay', 'resulturl', 'error_status', 'enableip', 'cronhash'));
						
			$n_options = array();
			$n_options[] = array(
				'view' => 'warning',
				'default' => sprintf(__('Use only latin symbols in payment notes. Maximum: %s characters.', 'pn'), 100),
			);		
			$options = pn_array_insert($options, 'note', $n_options);
			
			return $options;
		}	

		function get_reserve_lists($m_id, $m_defin) {
			
			$purses = array(
				$m_id . '_1' => 'EUR',
				$m_id . '_2' => 'USD',
				$m_id . '_3' => 'RUB',
			);
			
			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin) { 
		
			$sum = 0;
				
			$purses = $this->get_reserve_lists($m_id, $m_defin);	
			$purse = trim(is_isset($purses, $code));
			if ($purse) {
				try {
					
					$payeer = new AP_Payeer(is_isset($m_defin, 'ACCOUNT_NUMBER'), is_isset($m_defin, 'API_ID'), is_isset($m_defin, 'API_KEY'));
					if ($payeer->isAuth())
					{
		
						$arBalance = $payeer->getBalance();
						$sum = trim((string)$arBalance['balance'][$purse]['BUDGET']);
																
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
			
			$currency = mb_strtoupper($item->currency_code_get);
			$currency = str_replace(array('RUR'), 'RUB', $currency);
					
			$enable = array('USD', 'RUB', 'EUR');
			if (!in_array($currency, $enable)) {
				$error[] = __('Wrong currency code', 'pn'); 
			}						
						
			$account = $item->account_get;
			$account = mb_strtoupper($account);
			if (!$account) {
				$error[] = __('Wrong client wallet', 'pn');
			}							

			$out_sum = $trans_sum = is_paymerch_sum($item, $paymerch_data);
			
			$sum = 0;
			if ($trans_sum > 0) {
				// $sum = $trans_sum / 0.9905;
				$sum = $trans_sum * 1.0095;
			}

			$sum = is_sum($sum, 2, 'up');
							
			if (0 == count($error)) {

				$result = $this->set_ap_status($item, $test);
				if ($result) {
					
					$notice = get_text_paymerch($m_id, $item, $trans_sum);
					if (!$notice) { $notice = sprintf(__('ID order %s', 'pn'), $item->id); }
					$notice = trim(pn_maxf($notice, 100));
						
					try {
						
						$payeer = new AP_Payeer(is_isset($m_defin, 'ACCOUNT_NUMBER'), is_isset($m_defin, 'API_ID'), is_isset($m_defin, 'API_KEY'));
						if ($payeer->isAuth()) {
									
							$arTransfer = $payeer->transfer(array(
								'curIn' => $currency,
								'sum' => $sum,
								'curOut' => $currency,
								// 'to' => 'test@gmail.com',
								// 'to' => '+01112223344',
								'to' => $account,
								'comment' => $notice,
								// 'anonim' => 'Y',
								// 'protect' => 'Y',
								// 'protectPeriod' => '3',
								// 'protectCode' => '12345',
							));								
									
							if (empty($arTransfer['errors']) and isset($arTransfer['historyId'])) {
								$trans_id = $arTransfer['historyId'];
							} else {
								$this->logs(print_r($arTransfer, true), $m_id, $item->id);
								$error[] = __('Payout error', 'pn');
								$pay_error = 1;
							}
							
						} else {
							$pay_error = 1;
							$error[] = 'Error interfaice';
						}
						
					}
					catch (Exception $e)
					{
						$error[] = $e->getMessage();
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
					'from_account' => is_isset($m_defin, 'ACCOUNT_NUMBER'),
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

new paymerchant_payeer(__FILE__, 'Payeer');