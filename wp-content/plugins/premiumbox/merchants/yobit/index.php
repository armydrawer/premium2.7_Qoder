<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Yobit[:en_US][ru_RU:]Yobit[:ru_RU]
description: [en_US:]Yobit merchant[:en_US][ru_RU:]мерчант Yobit[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_yobit')) {
	class merchant_yobit extends Ext_Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			add_filter('bcc_keys', array($this, 'set_keys'));
			add_filter('qr_keys', array($this, 'set_keys'));
		}	

		function get_map() {
			
			$map = array(
				'API_KEY'  => array(
					'title' => '[en_US:]API Key[:en_US][ru_RU:]API Key[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'API_SECRET'  => array(
					'title' => '[en_US:]API secret[:en_US][ru_RU:]API secret[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),							
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('API_KEY', 'API_SECRET');
			
			return $arrs;
		}		

		function options($options, $data, $m_id, $place) { 
		
			$m_defin = $this->get_file_data($m_id);
			
			$options = pn_array_unset($options, array('check_api', 'note', 'enableip', 'resulturl', 'help_resulturl', 'workstatus', 'invalid_ctype'));

			$options['need_confirm'] = array(
				'view' => 'input',
				'title' => __('Required number of transaction confirmations', 'pn'),
				'default' => is_isset($data, 'need_confirm'),
				'name' => 'need_confirm',
				'work' => 'int',
			);
			$options['need_confirm_warning'] = array(
				'view' => 'warning',
				'default' => __('(Recommended!) Set the value to 0 so that the order is considered paid only after receiving the required number of confirmations on the stock! <br /> (NOT recommended!) If you set a value other than 0, the exchanger will change the status of the order to "Paid" according to this setting, regardless of the transaction status that is displayed in the exchanges payment history.','pn'),
			);			
			
			$text = '
			<div><strong>Cron:</strong> <a href="' . get_mlink($m_id . '_cron' . chash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_cron' . chash_url($m_id)) . '</a></div>			
			';		
			
			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;	
		}			

		function merch_type($m_id) {
			
			return 'address';  
		}	

		function prepare_code($currency, $currency_id) {
			global $wpdb;
			
			$currency_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id'");
			if (isset($currency_data->id)) {
				$xml_value = mb_strtoupper(is_xml_value($currency_data->xml_value));
				return $xml_value;
			}			
			
			return $currency;
		}

		function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
			global $wpdb, $bids_data;

			$item_id = $bids_data->id;		
			$currency = strtoupper($bids_data->currency_code_give);
			$currency_id_give = $bids_data->currency_id_give;
					
			$dest_tag = pn_strip_input($bids_data->dest_tag);	
			$to_account = pn_strip_input($bids_data->to_account);
			if (!$to_account) {
					
				$show_error = intval(is_isset($m_data, 'show_error'));
						
				$need_currency = $this->prepare_code($currency, $currency_id_give);
						
				try {
					$class = new Yobit($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'API_SECRET'));
					$result = $class->get_address($need_currency);
					if (isset($result['address'])) { 
						$to_account = pn_strip_input(is_isset($result, 'address'));
						$dest_tag = pn_strip_input(is_isset($result, 'memo'));
					}
				} catch (Exception $e) { 
					$this->logs($e->getMessage());	
				}
						
				if ($to_account) {
							
					$arr = array();
					$arr['to_account'] = $to_account;
					$arr['dest_tag'] = $dest_tag;
					$bids_data = update_bid_tb_array($item_id, $arr, $bids_data);
							
					$notify_tags = array();
					$notify_tags['[bid_id]'] = $item_id;
					$notify_tags['[address]'] = $to_account;
					$notify_tags['[sum]'] = $pay_sum;
					$notify_tags['[dest_tag]'] = $dest_tag;
					$notify_tags['[currency_code_give]'] = $bids_data->currency_code_give;
					$notify_tags['[count]'] = $this->confirm_count($m_id, $m_defin, $m_data);									

					$admin_locale = get_admin_lang();
					$now_locale = get_locale();
					set_locale($admin_locale);

					$user_send_data = array(
						'admin_email' => 1,
					);
					$result_mail = apply_filters('premium_send_message', 0, 'generate_merchaddress2', $notify_tags, $user_send_data); 
							
					set_locale($now_locale);

					$user_send_data = array(
						'user_email' => $bids_data->user_email,
					);	
					$user_send_data = apply_filters('user_send_data', $user_send_data, 'generate_merchaddress', $bids_data);	
					$result_mail = apply_filters('premium_send_message', 0, 'generate_merchaddress', $notify_tags, $user_send_data);					
							
				}
			}
			
			if ($to_account) {
				return 1;
			}
			
			return 0;			
		} 

		function confirm_count($m_id, $m_defin, $m_data) {
			
			return intval(is_isset($m_data, 'need_confirm'));
		}

		function cron($m_id, $m_defin, $m_data) {
			global $wpdb;

			$show_error = intval(is_isset($m_data, 'show_error'));	
			$need_confirm = intval(is_isset($m_data, 'need_confirm'));
			
			try {
				$class = new Yobit($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'API_SECRET'));
				$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN('new','coldpay','techpay') AND m_in = '$m_id'");
				foreach ($items as $item) {
					$id = $item->id;
					$trans_in = pn_strip_input(is_isset($item, 'trans_in'));
					$to_account = pn_strip_input(is_isset($item, 'to_account'));
					$dest_tag = pn_strip_input(is_isset($item, 'dest_tag'));
					$currency = strtoupper($item->currency_code_give);
					$currency_id_give = $item->currency_id_give;
					$currency_prepare = $this->prepare_code($currency, $currency_id_give);
					if ($to_account) {	
						$orders = $class->get_address_info($currency_prepare, $to_account);
						foreach ($orders as $order) {
							$memo = pn_strip_input(is_isset($order, 'memo'));
							if (!$memo and !$dest_tag or $memo == $dest_tag) {
								$res_status = $order['status'];
								$res_txid = $order['txid'];
								$confirmations = 0;
								if (isset($order['confirmations'])) {
									$confirmations = intval($order['confirmations']);
								}
									
								$realpay_st = array('completed');
								$coldpay_st = array('processing', 'confirmed');
								$coldpay_st_need = array('processing', 'confirmed');
									
								$data = get_data_merchant_for_id($id, $item);
									
								$now_status = '';
								if (in_array($res_status, $realpay_st)) {
									$now_status = 'realpay';
								}
								if (in_array($res_status, $coldpay_st)) {
									$now_status = 'coldpay';
								}				
								if (in_array($res_status, $coldpay_st_need) and $confirmations >= $need_confirm and $need_confirm > 0) {
									$now_status = 'realpay';
								}
									
								do_action('merchant_confirm_count', $id, $confirmations, $data['bids_data'], $data['direction_data'], $need_confirm, $this->name);
									
								if ($now_status) {
											
									$in_sum = $order['amount'];
									$in_sum = is_sum($in_sum, 8);
									$err = $data['err'];
									$status = $data['status'];
									$bid_m_id = $data['m_id'];
									$bid_m_script = $data['m_script'];  
											
									$bid_currency = strtoupper($data['currency']);
											
									$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
												
									$bid_sum = is_sum($data['pay_sum'], 12);	
									$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
											
									$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
									$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
									$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
									$invalid_check = intval(is_isset($m_data, 'check'));								
											
									if (!check_txid_in($bid_m_id, $res_txid, $id)) {
										if (!check_trans_in($bid_m_id, $res_txid, $id)) {
											if(0 == $err and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name){
												// if($bid_currency == $currency or $invalid_ctype > 0){
													if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {
														
														$params = array( 
															'pay_purse' => $pay_purse,
															'txid_in' => $res_txid,
															'sum' => $in_sum,
															'bid_sum' => $bid_sum,
															'bid_status' => array('new', 'techpay', 'coldpay'),
															'bid_corr_sum' => $bid_corr_sum,
															// 'currency' => $currency,
															// 'bid_currency' => $bid_currency,
															'invalid_ctype' => $invalid_ctype,
															'invalid_minsum' => $invalid_minsum,
															'invalid_maxsum' => $invalid_maxsum,
															'invalid_check' => $invalid_check,
															'm_place' => $bid_m_id . '_cron',
															'm_id' => $m_id,
															'm_data' => $m_data,
															'm_defin' => $m_defin,
														);
														set_bid_status($now_status, $id, $params, $data['direction_data']); 
														break;
														
													} 
												// } 										
											} 
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

new merchant_yobit(__FILE__, 'Yobit');