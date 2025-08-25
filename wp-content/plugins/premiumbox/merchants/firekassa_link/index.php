<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]FireKassa Link[:en_US][ru_RU:]FireKassa Link[:ru_RU]
description: [en_US:]FireKassa merchant (payment page)[:en_US][ru_RU:]Мерчант FireKassa (переход к оплате)[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_firekassalink')) {
	class merchant_firekassalink extends Ext_Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach ($ids as $id) {
				add_action('premium_merchant_' . $id . '_status' . hash_url($id), array($this, 'merchant_status'));
				add_action('premium_merchant_' . $id . '_fail', array($this, 'merchant_fail'));
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

		function options($options, $data, $m_id, $place) { 
			
			$m_defin = $this->get_file_data($m_id);
			
			$options = pn_array_unset($options, array('pagenote', 'note', 'check_api', 'workstatus'));

			$options['merch_line'] = array(
				'view' => 'line',
			);			
			
			/*
			$options['ttl'] = array(
				'view' => 'input',
				'title' => __('Life time','pn'),
				'default' => is_isset($data, 'ttl'),
				'name' => 'ttl',
				'work' => 'int',
			);			
			*/
			$text = '
			<div><strong>Cron:</strong> <a href="' . get_mlink($m_id . '_cron' . chash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_cron' . chash_url($m_id)) . '</a></div>			
			';

			$options['text_line'] = array(
				'view' => 'line',
			);			
			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;	
		}				

		function merch_type($m_id) {
			
			return 'link'; 
		}

		function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
			global $bids_data;	
			
			$pay_link = $this->get_pay_link($bids_data->id);
			if (!$pay_link) {
					
				$pay_sum = is_sum($pay_sum, 2); 
				$currency = mb_strtoupper($bids_data->currency_code_give);
				$currency = str_replace('RUR','RUB', $currency);
					
				$class = new FireKassa($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'SECRET_KEY'), is_isset($m_defin, 'API_URL'));
						
				$ttl = intval(is_isset($m_data, 'ttl'));
				if ($ttl < 600) { $ttl = 1890; }
						
				$success_url = get_bids_url($bids_data->hashed);
				$fail_url = get_mlink($m_id . '_fail') . '?order_id=' . $bids_data->id;
				$notification_url = get_mlink($m_id . '_status' . hash_url($m_id)) . '?order_id=' . $bids_data->id;
						
				$data = array(
					'order_id' => $bids_data->id,
					'payment_methods' => array('card'),
					'amount' => $pay_sum,
					'notification_url' => $notification_url, 
					'success_url' => $success_url, 
					'fail_url' => $fail_url, 
					'return_url' => $success_url, 
					'ttl' => $ttl,
				);
						
				$data['account'] = $bids_data->account_give;		
				$data['ext_txn'] = $bids_data->id;
				$data['ext_date'] = current_time('Y-m-d\TH:i:s');
				$data['ext_phone'] = $bids_data->user_phone;
				$data['ext_photo'] = '';
				$data['ext_last_name'] = $bids_data->last_name;
				$data['ext_first_name'] = $bids_data->first_name;
				$data['ext_middle_name'] = $bids_data->second_name;
				$data['ext_email'] = is_isset($bids_data, 'user_email');
				$data['ext_ip'] = is_isset($bids_data, 'user_ip');
				$data['ext_user_agent'] = is_isset($bids_data, 'user_agent');
						
				global $wpdb;
				
				$currency_id_get = intval($bids_data->currency_id_get);
				$currency_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_get'");
				if (isset($currency_data->id)) {
					$data['ext_recipient_system'] = get_currency_title($currency_data);
					$data['ext_recipient'] = $bids_data->account_get;
					$data['ext_c_to'] = $currency_data->xml_value;
				}
						
				$n_data = array();
				foreach ($data as $data_k => $data_v) {
					if (is_string($data_v) and strlen($data_v) > 0 or !is_string($data_v)) {
						$n_data[$data_k] = $data_v;
					}
				}						
						
				$res = $class->create_invoice($n_data);
				if (isset($res['payment_url'], $res['id'])) {
								
					$pay_link = pn_strip_input($res['payment_url']);
					$this->update_pay_link($bids_data->id, $pay_link);
							
					$arr = array();
					$arr['trans_in'] = pn_strip_input($res['id']);
					$bids_data = update_bid_tb_array($bids_data->id, $arr, $bids_data);	
								
				} 
			}
			
			if ($pay_link) {
				return 1;
			}
			
			return 0;			
		}

		function merchant_fail() {
			
			$id = get_payment_id('order_id');
			redirect_merchant_action($id, $this->name);
			
		}

		function merchant_status() {
			
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_secure', $this->name, '', $m_id, $m_defin, $m_data);
			
			$this->merch_cron($m_id, $m_defin, $m_data, is_param_get('order_id'));
			
			echo 'OK';
			exit;
		}	

		function cron($m_id, $m_defin, $m_data) {
			
			$this->merch_cron($m_id, $m_defin, $m_data, 0);	
			
		}
		
		function merch_cron($m_id, $m_defin, $m_data, $order_id) {
			global $wpdb;
			
			$show_error = intval(is_isset($m_data, 'show_error'));	
			$order_id = intval($order_id);
			
			try {
				$where = '';
				if ($order_id) {
					$where = " AND id = '$order_id'";
				}
				$workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay'));
				$workstatus_db = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay'), 1);
				$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($workstatus_db) AND m_in = '$m_id' $where");
				$list = array();
				foreach ($items as $item) {
					$trans_in = trim($item->trans_in);
					if ($trans_in) {
						$list[] = $trans_in;
					}
				}
				if (count($list) > 0) {
					$class = new FireKassa($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'SECRET_KEY'), is_isset($m_defin, 'API_URL'));
					$ids = join(',', $list);
					$action = 'deposit';
					$orders = $class->get_transactions($action, $ids);
					foreach ($items as $item) {
						$item_id = $item->id;
						$trans_in = trim($item->trans_in);
						if (isset($orders[$trans_in])) {
							$order = $orders[$trans_in];
							$order_status = $order['status'];
							$order_currency = strtoupper($order['currency']);
							if (in_array($order_status, array('paid', 'partially-paid', 'overpaid'))) {	
							
								$data = get_data_merchant_for_id($item_id, $item);
									
								$in_sum = $order['payment_amount'];
								$in_sum = is_sum($in_sum, 2);
								$err = $data['err'];
								$status = $data['status'];
								$bid_m_id = $data['m_id'];
								$bid_m_script = $data['m_script']; 
									
								$bid_currency = $data['currency'];
									
								$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
										
								$bid_sum = is_sum($data['pay_sum'], 2);	
								$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
									
								$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
								$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
								$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
								$invalid_check = intval(is_isset($m_data, 'check'));								
									
								if (0 == $err and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name) {
									if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {
										if ($bid_currency == $order_currency or $invalid_ctype > 0) {	
										
											$params = array( 
												'pay_purse' => $pay_purse,
												'sum' => $in_sum,
												'bid_sum' => $bid_sum,
												'bid_status' => $workstatus,
												'bid_corr_sum' => $bid_corr_sum,
												'trans_in' => $order['payment_id'],
												'currency' => $order_currency,
												'bid_currency' => $bid_currency,
												'invalid_ctype' => $invalid_ctype,
												'invalid_minsum' => $invalid_minsum,
												'invalid_maxsum' => $invalid_maxsum,
												'invalid_check' => $invalid_check,
												'm_place' => $m_id,
												'm_id' => $m_id,
												'm_data' => $m_data,
												'm_defin' => $m_defin,
											);
													
											$now_status = 'realpay';
											set_bid_status($now_status, $item_id, $params, $data['direction_data']);
											
										}	
									} 
								} 
							}
						}
					}
				}
			}
			catch (Exception $e)
			{
				$this->logs($e->getMessage(), $m_id);
				if ($show_error and current_user_can('administrator')) {
					die($e->getMessage());
				}
			}			
			
		}
	}
}

new merchant_firekassalink(__FILE__, 'FireKassa Link');