<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Odysseq[:en_US][ru_RU:]Odysseq[:ru_RU]
description: [en_US:]Odysseq merchant[:en_US][ru_RU:]мерчант Odysseq[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_odysseq')) {
	class merchant_odysseq extends Ext_Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach ($ids as $id) {
				add_action('premium_merchant_' . $id . '_status' . hash_url($id), array($this, 'merchant_status'));
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
			
			$options = pn_array_unset($options, array('personal_secret', 'pagenote', 'note', 'check_api', 'enableip'));

			$options['paymethod'] = array(
				'view' => 'select',
				'title' => __('Payment method', 'pn'),
				'options' => array('0' => 'Qiwi', '1' => 'Qiwi card', '2' => 'Contact'),
				'default' => is_isset($data, 'paymethod'),
				'name' => 'paymethod',
				'work' => 'int',
			);			
			
			$text = '
			<div><strong>Callback URL:</strong> <a href="' . get_mlink($m_id . '_status' . hash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_status' . hash_url($m_id)) . '</a></div>
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
			
			$paymethod = intval(is_isset($m_data, 'paymethod'));
				
			$pay_link = $this->get_pay_link($bids_data->id);
			if (!$pay_link) {
					
				$pay_sum = is_sum($pay_sum, 2); 
				$currency = mb_strtoupper($bids_data->currency_code_give);
				
				$order_id = $bids_data->id;
				$amount = $pay_sum;
				$card = $bids_data->account_give;
				$info = array(
					'userIp' => pn_real_ip(),
					'userAgent' => pn_maxf(pn_strip_input(is_isset($_SERVER, 'HTTP_USER_AGENT')), 250),
					'userEmail' => $bids_data->user_email,
					'clientWallet' => $bids_data->account_give,
					'currencyTo' => $vd2->xml_value,
					'recepientWallet' => $bids_data->account_get,
				);
				$successUrl = $failUrl = get_bids_url($bids_data->hashed);
				
				try {
					$class = new Odysseq($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
					$res = '';
					if (1 == $paymethod) { //card
						$res = $class->invoice_card($order_id, $pay_sum, $card, $info, $successUrl, $failUrl);
					} elseif (2 == $paymethod) {	//contact
						$res = $class->invoice_contact($order_id, $pay_sum, $card, $bids_data->first_name, $bids_data->second_name, $bids_data->last_name, $info, $successUrl, $failUrl);	
					} else { 
						$res = $class->invoice_wallet($order_id, $pay_sum, $info, $successUrl, $failUrl);
					}
							
					if (isset($res['paymentInfo'], $res['paymentInfo']['forwardingPayUrl'])) { 
								
						$pay_link = pn_strip_input($res['paymentInfo']['forwardingPayUrl']);
						$this->update_pay_link($bids_data->id, $pay_link);
								
					} 
				}
				catch (Exception $e)
				{
					$this->logs($e->getMessage(), $m_id);
				}

			}
			
			if ($pay_link) {
				return 1;
			}
			
			return 0;
		}
		
		function cron($m_id, $m_defin, $m_data) {
			global $wpdb;

			$show_error = intval(is_isset($m_data, 'show_error'));	
			
			$paymethod = intval(is_isset($m_data, 'paymethod'));
			$pay_m = 'in.qiwi';
			if (1 == $paymethod) {
				$pay_m = 'in.card';
			} elseif (2 == $paymethod) {
				$pay_m = 'in.contact';
			}			
			
			try {
				$class = new Odysseq($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
				$workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay'));
				$workstatus_db = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay'), 1);
				$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($workstatus_db) AND m_in = '$m_id'");
				foreach ($items as $item) {
					
					$order_id = $item->id;
					$data = get_data_merchant_for_id($order_id, $item);
					$bid_m_id = $data['m_id'];
					$bid_m_script = $data['m_script'];
					$bid_err = $data['err'];
					
					if ($bid_err > 0) {
						$this->logs($order_id . ' The application does not exist or the wrong ID', $m_id);
					}
						
					if ($bid_m_script and $bid_m_script != $this->name or !$bid_m_script) {	
						$this->logs($order_id . ' wrong script', $m_id);
					}			
						
					if ($bid_m_id and $m_id != $bid_m_id or !$bid_m_id) {
						$this->logs($order_id . ' not a faithful merchant', $m_id);			
					}
					
					$system_data = $class->status($order_id);
						
					if (isset($system_data['paymentInfo'], $system_data['paymentInfo']['type'], $system_data['paymentInfo']['status'], $system_data['paymentInfo']['paymentType']) and 'IN' == $system_data['paymentInfo']['type'] and $system_data['paymentInfo']['paymentType'] == $pay_m) {
						/*
						WAITING|SENDING|SUCCESS|CANCELED
						*/
						$pay_status = strtoupper($system_data['paymentInfo']['status']);
						if ('SUCCESS' == $pay_status) {
						
							$currency = 'RUB';
							$in_sum = $system_data['paymentInfo']['amount'];
							$in_sum = is_sum($in_sum, 2);
							$bid_status = $data['status'];
						
							$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
						
							$bid_currency = $data['currency'];
							$bid_currency = str_replace('RUR', 'RUB', $bid_currency);
						
							$bid_sum = is_sum($data['pay_sum'], 2);
							$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
						
							$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
							$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
							$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
							$invalid_check = intval(is_isset($m_data, 'check'));
						
							if ($bid_currency == $currency or $invalid_ctype > 0) {
								if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {		
	
									$params = array(
										'sum' => $in_sum,
										'bid_sum' => $bid_sum,
										'bid_status' => $workstatus,
										'bid_corr_sum' => $bid_corr_sum,
										'pay_purse' => $pay_purse,
										'currency' => $currency,
										'bid_currency' => $bid_currency,
										'invalid_ctype' => $invalid_ctype,
										'invalid_minsum' => $invalid_minsum,
										'invalid_maxsum' => $invalid_maxsum,
										'invalid_check' => $invalid_check,
										'm_place' => $bid_m_id,
										'm_id' => $m_id,
										'm_data' => $m_data,
										'm_defin' => $m_defin,
									);
									set_bid_status('realpay', $order_id, $params, $data['direction_data']);  
									
								} else {
									$this->logs($order_id . ' The payment amount is less than the provisions', $m_id);
								}
							} else {
								$this->logs($order_id . ' Wrong type of currency', $m_id);
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
		
		function merchant_status() {
			
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			$callback = file_get_contents('php://input');
			$post = @json_decode($callback, true);
			
			do_action('merchant_secure', $this->name, $post, $m_id, $m_defin, $m_data);
			
			$paymethod = intval(is_isset($m_data, 'paymethod'));
			$pay_m = 'in.qiwi';
			if (1 == $paymethod) {
				$pay_m = 'in.card';
			} elseif (2 == $paymethod) {
				$pay_m = 'in.contact';
			}
			
			if (isset($post['orderId'], $post['type'])) {
				if ('IN' == $post['type']) {
					
					$class = new Odysseq($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
					$system_data = $class->status($post['orderId']);			
			
					if (isset($system_data['paymentInfo'], $system_data['paymentInfo']['type'], $system_data['paymentInfo']['paymentType']) and 'IN' == $system_data['paymentInfo']['type'] and $system_data['paymentInfo']['paymentType'] == $pay_m) {
			
						$order_id = intval($post['orderId']);
						$data = get_data_merchant_for_id($order_id);
						$bid_m_id = $data['m_id'];
						$bid_m_script = $data['m_script'];
						$bid_err = $data['err'];
						
						if ($bid_err > 0) {
							$this->logs($order_id . ' The application does not exist or the wrong ID', $m_id);
						}
						
						if ($bid_m_script and $bid_m_script != $this->name or !$bid_m_script) {	
							$this->logs($order_id . ' wrong script', $m_id);
						}			
						
						if ($bid_m_id and $m_id != $bid_m_id or !$bid_m_id) {
							$this->logs($order_id . ' not a faithful merchant', $m_id);			
						}
						
						$currency = 'RUB';
						$pay_status = strtoupper($system_data['paymentInfo']['status']);
						$in_sum = $system_data['paymentInfo']['amount'];
						$in_sum = is_sum($in_sum, 2);
						$bid_status = $data['status'];
						
						$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
						
						$bid_currency = $data['currency'];
						$bid_currency = str_replace('RUR', 'RUB', $bid_currency);
						
						$bid_sum = is_sum($data['pay_sum'], 2);
						$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
						
						$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
						$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
						$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
						$invalid_check = intval(is_isset($m_data, 'check'));
						
						/*
						WAITING|SENDING|SUCCESS|CANCELED
						*/
						$workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay'));
						if (in_array($bid_status, $workstatus)) { 
							if ($bid_currency == $currency or $invalid_ctype > 0) {
								if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {	
								
									$now_status = '';
									if ('SUCCESS' == $pay_status) {
										$now_status = 'realpay';
									}
									if ($now_status) {	
										$params = array(
											'sum' => $in_sum,
											'bid_sum' => $bid_sum,
											'bid_status' => $workstatus,
											'bid_corr_sum' => $bid_corr_sum,
											'pay_purse' => $pay_purse,
											'currency' => $currency,
											'bid_currency' => $bid_currency,
											'invalid_ctype' => $invalid_ctype,
											'invalid_minsum' => $invalid_minsum,
											'invalid_maxsum' => $invalid_maxsum,
											'invalid_check' => $invalid_check,
											'm_place' => $bid_m_id,
											'm_id' => $m_id,
											'm_data' => $m_data,
											'm_defin' => $m_defin,
										);
										set_bid_status($now_status, $order_id, $params, $data['direction_data']);  
									}
									
								} else {
									$this->logs($order_id . ' The payment amount is less than the provisions', $m_id);
								}
							} else {
								$this->logs($order_id . ' Wrong type of currency', $m_id);
							}
						} else {
							$this->logs($order_id . ' In the application the wrong status', $m_id);
						}
					}
				}
			}			

			echo '{"status":200}';
			exit;
		}		
	}
}

new merchant_odysseq(__FILE__, 'Odysseq');