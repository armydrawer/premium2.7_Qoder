<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]PerfectMoney[:en_US][ru_RU:]PerfectMoney[:ru_RU]
description: [en_US:]PerfectMoney automatic payouts[:en_US][ru_RU:]авто выплаты PerfectMoney[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_perfectmoney')) {
	class paymerchant_perfectmoney extends Ext_AutoPayut_Premiumbox {
		
		function __construct($file, $title) 
		{
			parent::__construct($file, $title);	
			
			add_filter('list_user_notify', array($this, 'user_mailtemp')); 
			add_filter('list_notify_tags_perfectmoney_paycoupon', array($this, 'mailtemp_tags_paycoupon'));			
		}

		function get_map() {
			
			$map = array(
				'ACCOUNT_ID'  => array(
					'title' => '[en_US:]Account ID[:en_US][ru_RU:]ID аккаунта[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'PHRASE'  => array(
					'title' => '[en_US:]Account password[:en_US][ru_RU:]Пароль от аккаунта[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'U_ACCOUNT'  => array(
					'title' => '[en_US:]USD wallet number [:en_US][ru_RU:]USD номер кошелька[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'E_ACCOUNT'  => array(
					'title' => '[en_US:]EUR wallet number[:en_US][ru_RU:]EUR номер кошелька[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'G_ACCOUNT'  => array(
					'title' => '[en_US:]GOLD wallet number[:en_US][ru_RU:]Gold номер кошелька[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'B_ACCOUNT'  => array(
					'title' => '[en_US:]BTC wallet[:en_US][ru_RU:]BTC номер кошелька[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),				
			);
			
			return $map;
		}
		
		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('ACCOUNT_ID', 'PHRASE');
			
			return $arrs;
		}				
		
		
		function user_mailtemp($places_admin) {
			
			$places_admin['perfectmoney_paycoupon'] = sprintf(__('%s automatic payout', 'pn'), 'Perfectmoney E-Vouchers');
			
			return $places_admin;
		}

		function mailtemp_tags_paycoupon($tags) {
			
			$tags['id'] = array(
				'title' => __('Coupon code', 'pn'),
				'start' => '[id]',
			);
			$tags['num'] = array(
				'title' => __('Activation code', 'pn'),
				'start' => '[num]',
			);
			$tags['bid_id'] = array(
				'title' => __('Order ID', 'pn'),
				'start' => '[bid_id]',
			);
			
			return $tags;
		}		
		
		function options($options, $data, $id, $place) {
			
			$options = pn_array_unset($options, array('resulturl', 'error_status', 'cronhash', 'enableip'));
						
			$n_options = array();
			$n_options['warning'] = array(
				'view' => 'warning',
				'default' => sprintf(__('Use only latin symbols in payment notes. Maximum: %s characters.', 'pn'), 100),
			);
			$options = pn_array_insert($options, 'note', $n_options);
						
			$opt = array(
				'0' => __('Account', 'pn'),
				'1' => __('E-Vouchers', 'pn'),
			);
			$options['variant'] = array(
				'view' => 'select',
				'title' => __('Transaction type', 'pn'),
				'options' => $opt,
				'default' => intval(is_isset($data, 'variant')),
				'name' => 'variant',
				'work' => 'int',
			);					
			
			return $options;
		}			

		function get_reserve_lists($m_id, $m_defin) {
			
			$purses = array(
				$m_id . '_1' => is_isset($m_defin, 'U_ACCOUNT'),
				$m_id . '_2' => is_isset($m_defin, 'E_ACCOUNT'),
				$m_id . '_3' => is_isset($m_defin, 'G_ACCOUNT'),
				$m_id . '_4' => is_isset($m_defin, 'B_ACCOUNT'),
			);
			
			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin) { 
		
			$sum = 0;
				
			$purses = $this->get_reserve_lists($m_id, $m_defin);
			$purse = trim(is_isset($purses, $code));
			if ($purse) {
						
				try{
					
					$class = new AP_PerfectMoney($this->name, $m_id, is_isset($m_defin, 'ACCOUNT_ID'), is_isset($m_defin, 'PHRASE'));
					$res = $class->get_balance();
					if (is_array($res)) {
										
						foreach ($res as $pursename => $amount) {
							if ($pursename == $purse) {
								$sum = trim((string)$amount);
								break;
							}
						}						
								
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
				$class = new AP_PerfectMoney($this->name, $m_id, is_isset($m_defin, 'ACCOUNT_ID'), is_isset($m_defin, 'PHRASE'));
				$hres = $class->getHistory(date('d.m.Y', strtotime('-2 day')), date('d.m.Y', strtotime('+2 day')), 'paymentid', 'rashod');
				if (0 == $hres['error']) {
					$histories = $hres['responce'];
					if (isset($histories[$item_id])) {
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
			$coupon = '';
			$coupon_num = '';
			$trans_id = 0;				
			
			$variant = intval(is_isset($paymerch_data, 'variant'));
			
			$currency = mb_strtoupper($item->currency_code_get);
			$currency = str_replace(array('GLD', 'OAU'),'G', $currency);
			$currency = str_replace(array('USD'), 'U', $currency);
			$currency = str_replace(array('EUR'), 'E', $currency);
			$currency = str_replace(array('BTC'), 'B', $currency);
					
			$enable = array('G', 'U', 'E', 'B');
			if (!in_array($currency, $enable)) {
				$error[] = __('Wrong currency code', 'pn'); 
			}						
						
			$account = $item->account_get;
					
			if (0 == $variant) {
				$account = mb_strtoupper($account);
				if (!preg_match("/^{$currency}[0-9]{0,20}$/", $account, $matches)) {
					$error[] = __('Wrong client wallet', 'pn');
				}
			} else {
				if (!is_email($account)) {
					$error[] = __('Wrong client wallet', 'pn');
				}						
			}
					
			$site_purse = '';
			if ('G' == $currency) {
				$site_purse = is_isset($m_defin, 'G_ACCOUNT');
			} elseif ('U' == $currency) {
				$site_purse = is_isset($m_defin, 'U_ACCOUNT');
			} elseif ('E' == $currency) {
				$site_purse = is_isset($m_defin, 'E_ACCOUNT');
			} elseif ('B' == $currency) {
				$site_purse = is_isset($m_defin, 'B_ACCOUNT');						
			} 
					
			$site_purse = mb_strtoupper($site_purse);
			if (!preg_match("/^{$currency}[0-9]{0,20}$/", $site_purse, $matches)) {
				$error[] = __('Your account set on website does not match with currency code', 'pn');
			}			

			$out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);					
			
			if (0 == count($error)) {

				$result = $this->set_ap_status($item, $test);
				if ($result) {
					
					$notice = get_text_paymerch($m_id, $item, $sum);
					if (!$notice) { $notice = sprintf(__('Order ID %s', 'pn'), $item->id); }
					$notice = trim(pn_maxf($notice, 100));
						
					try {
						
						$class = new AP_PerfectMoney($this->name, $m_id, is_isset($m_defin, 'ACCOUNT_ID'), is_isset($m_defin, 'PHRASE'));
						if (0 == $variant) {
							$res = $class->SendMoney($site_purse, $account, $sum, $item_id, $notice);
						} else {
							$res = $class->CreateVaucher($site_purse, $sum);
						}
						if (1 == $res['error']) {
							$error[] = __('Payout error', 'pn');
							$pay_error = 1;
						} else {
							$trans_id = $res['trans_id'];
							if (1 == $variant) {
								$coupon = $res['code'];
								$coupon_num = $res['num'];
							}
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
						
				if (1 == $variant) {
						
					$bid_locale = $item->bid_locale;
					$now_locale = get_locale();
					set_locale($bid_locale);

					$notify_tags = array();
					$notify_tags['[id]'] = $coupon;
					$notify_tags['[num]'] = $coupon_num;
					$notify_tags['[bid_id]'] = $item_id;
					$notify_tags = apply_filters('notify_tags_perfectmoney_paycoupon', $notify_tags);		

					$user_send_data = array(
						'user_email' => $account,
					);
					$user_send_data = apply_filters('user_send_data', $user_send_data, 'perfectmoney_paycoupon', $item);
					$result_mail = apply_filters('premium_send_message', 0, 'perfectmoney_paycoupon', $notify_tags, $user_send_data);

					set_locale($now_locale);									
							
					$coupon_data = array(
						'coupon' => $coupon,
						'coupon_code' => $coupon_num,
					);							
					do_action('merchant_create_coupon', $coupon_data, $item, 'perfectmoney', $place);						
							
				}	
				
				$params = array(
					'from_account' => $site_purse,
					'out_sum' => $out_sum,
					'trans_out' => $trans_id,
					'system' => 'user',
					'm_place' => $modul_place. ' ' .$m_id,
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

new paymerchant_perfectmoney(__FILE__, 'PerfectMoney');