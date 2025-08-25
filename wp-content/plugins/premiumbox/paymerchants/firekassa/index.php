<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]FireKassa[:en_US][ru_RU:]FireKassa[:ru_RU]
description: [en_US:]FireKassa automatic payouts[:en_US][ru_RU:]авто выплаты FireKassa[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_firekassa')) {
	class paymerchant_firekassa extends Ext_AutoPayut_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			$ids = $this->get_ids('paymerchants', $this->name);
			foreach ($ids as $id) {
				add_action('premium_merchant_ap_' . $id . '_status' . hash_url($id, 'ap'), array($this, 'merchant_status'));
			}			
		}	
		
		function get_map() {
			
			$map = array(
				'API_URL'  => array(
					'title' => '[en_US:]API url[:en_US][ru_RU:]API url[:ru_RU]',
					'view' => 'input',	
				),			
				'API_KEY'  => array(
					'title' => '[en_US:]API key[:en_US][ru_RU:]API ключ[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),	
				'SECRET_KEY'  => array(
					'title' => '[en_US:]Secret key[:en_US][ru_RU:]Секретный ключ[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),				
			);
			
			return $map;
		}
		
		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('API_KEY');
			
			return $arrs;
		}

		function get_card_methods($place, $m_id) {
			
			$methods = get_option($m_id . '_methods');
			if (!is_array($methods)) { $methods = array(); }
			
			if (1 == $place) {
				$m_defin = $this->get_file_data($m_id);
				$api_key = trim(is_isset($m_defin, 'API_KEY'));
				$secret_key = trim(is_isset($m_defin, 'SECRET_KEY'));
				if ($api_key and $secret_key) {
					$class = new AP_FireKassa($this->name, $m_id, $api_key, $secret_key, is_isset($m_defin, 'API_URL'));
					$lists = $class->get_methods();
					if (is_array($lists) and count($lists) > 0) {
						$methods = array();
						foreach ($lists as $list) {
							if (isset($list['out'], $list['site_account'], $list['name']) and $list['out']) {
								$methods[pn_strip_input($list['site_account'])] = replace_cyr($list['name']);
							}
						}
						update_option($m_id . '_methods', $methods);
					}
				}
			} 
			
			if (count($methods) < 1) {
				$methods = array(
					'sber' => 'Sber',
					'tinkoff' => 'Tinkoff',
				);
			}
			
			return $methods;
		}

		function options($options, $data, $m_id, $place) {
			
			$m_defin = $this->get_file_data($m_id);
			
			$options = pn_array_unset($options, array('note', 'checkpay'));			
			
			$options['typepay'] = array(
				'view' => 'select',
				'title' => __('Type', 'pn'),
				'options' => $this->get_card_methods($place, $m_id),
				'default' => is_isset($data, 'typepay'),
				'name' => 'typepay',
				'work' => 'input',
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
			
			$purses = array();
			
			$methods = $this->get_card_methods(0, $m_id);
			foreach ($methods as $mt_id => $mt_title) {
				$purses[$m_id . '_' . $mt_id] = $mt_title;
			}				
			
			return $purses;
		}		
		
		function update_reserve($code, $m_id, $m_defin) {
			
			$sum = 0;
			$purse = trim(str_replace($m_id . '_', '', $code));
			if ($purse) {
								
				try {
					
					$class = new AP_FireKassa($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'SECRET_KEY'), is_isset($m_defin,'API_URL'));
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
			
			$currency = mb_strtoupper($item->currency_code_get);
			$currency = str_replace(array('RUR'), 'RUB', $currency);						
					
			$account = str_replace('+', '', $item->account_get);
			if (!$account) {
				$error[] = __('Wrong client wallet', 'pn');
			}					
					
			$out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);
					
			$method = '';
			$m = $this->get_card_methods(0, $m_id);
			$typepay = pn_strip_input(is_isset($paymerch_data, 'typepay'));
			if (isset($m[$typepay])) {
				$method = $typepay;
			} else {
				$method = array_key_first($m);
			}					
			$methods = $this->get_card_methods(0, $m_id);	
				
			if (0 == count($error)) {
				$result = $this->set_ap_status($item, $test);
				if ($result) {				
					try {
						$class = new AP_FireKassa($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'SECRET_KEY'), is_isset($m_defin,'API_URL'));
						
						$notification_url = get_mlink('ap_' . $m_id . '_status' . hash_url($m_id, 'ap')) . '?order_id=' . $item_id;
						 
						$data = array(
							'order_id' => 'ap' . $item_id,
							'method' => 'card',
							'amount' => $sum,
							'account' => $account,
							'site_account' => $method,
							'notification_url' => $notification_url,
						);
						
						$data['ext_txn'] = $item->id;
						$data['ext_date'] = current_time('Y-m-d\TH:i:s');
						$data['ext_phone'] = $item->user_phone;
						$data['ext_photo'] = '';
						$data['ext_last_name'] = $item->last_name;
						$data['ext_first_name'] = $item->first_name;
						$data['ext_middle_name'] = $item->second_name;
						$data['ext_email'] = is_isset($item, 'user_email');
						$data['ext_ip'] = is_isset($item, 'user_ip');
						$data['ext_user_agent'] = is_isset($item, 'user_agent');
						
						$currency_id_give = intval($item->currency_id_give);
						global $wpdb;
						
						$currency_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_give'");
						if (isset($currency_data->id)) {
							$data['ext_sender_system'] = get_currency_title($currency_data);
							$data['ext_sender'] = $item->account_give;
							$data['ext_c_from'] = $currency_data->xml_value;
						}						
						
						$n_data = array();
						foreach ($data as $data_k => $data_v) {
							if (strlen($data_v) > 0) {
								$n_data[$data_k] = $data_v;
							}
						}						
						
						$res = $class->create_payout($n_data);
						
						if (isset($res['id'])) {
							$trans_id = $res['id'];
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
				set_bid_status('coldsuccess', $item_id, $params,$direction);  					
						 
				if ('admin' == $place) {
					pn_display_mess(__('Automatic payout is done', 'pn'), __('Automatic payout is done', 'pn'), 'true');
				} 
				
			}			
		}

		function merchant_status() {
			
			$m_id = key_for_url('_status', 'ap_');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_paymerch_data($m_id);

			do_action('paymerchant_secure', $this->name, '', $m_id, $m_defin, $m_data);
			
			$this->this_ap_cron($m_id, $m_defin, $m_data, is_param_get('order_id'));
			
			echo 'OK';
			exit;
		}

		function cron($m_id, $m_defin, $m_data) {
			
			$this->this_ap_cron($m_id, $m_defin, $m_data, 0);	
			
		}

		function this_ap_cron($m_id, $m_defin, $m_data, $order_id) {
			global $wpdb;
			
			$error_status = is_status_name(is_isset($m_data, 'error_status'));
			$order_id = intval($order_id);	
			
			$where = '';
			if ($order_id) {
				$where = " AND id = '$order_id'";
			}
			$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id' $where");
			$list = array();
			foreach ($items as $item) {
				$trans_out = trim($item->trans_out);
				if ($trans_out) {
					$list[] = $trans_out;
				}
			}
			if (count($list) > 0) {
				$class = new AP_FireKassa($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'SECRET_KEY'), is_isset($m_defin, 'API_URL'));
				$ids = join(',', $list);
				$action = 'withdrawal';
				$orders = $class->get_transactions($action, $ids);
				foreach ($items as $item) {
					$order_id = $item->id;
					$trans_id = trim($item->trans_out);
					if (isset($orders[$trans_id])) {
						$order = $orders[$trans_id];
			
						$order_status = $order['status'];
						$order_payment_id = $order['payment_id'];
						
						if (in_array($order_status, array('paid'))) {
									
							$params = array(
								'system' => 'system',
								'bid_status' => array('coldsuccess'),
								'trans_out' => $order_payment_id,
								'm_place' => 'cron ' . $m_id . '_cron',
								'm_id' => $m_id,
								'm_defin' => $m_defin,
								'm_data' => $m_data,
							);
							set_bid_status('success', $item->id, $params);
									
						} elseif (in_array($order_status,array('cancel', 'error'))) {
										
							$this->reset_cron_status($item, $error_status, $m_id);
									
						}					
					
					}
				}
			}			
		}		
	}
}

new paymerchant_firekassa(__FILE__, 'FireKassa');