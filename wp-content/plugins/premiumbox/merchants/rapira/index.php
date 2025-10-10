<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Rapira Crypto[:en_US][ru_RU:]Rapira Crypto[:ru_RU]
description: [en_US:]Rapira Crypto merchant[:en_US][ru_RU:]мерчант Rapira Crypto[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_rapira')) {
	class merchant_rapira extends Ext_Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			add_filter('bcc_keys', array($this, 'set_keys'));
			add_filter('qr_keys', array($this, 'set_keys'));
		}	

		function get_map() {
			
			$map = array(
				'PRIVATE_KEY'  => array(
					'title' => '[en_US:]Private Key[:en_US][ru_RU:]Private Key[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'UID'  => array(
					'title' => '[en_US:]UID[:en_US][ru_RU:]UID[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'host_type' => array(
					'title' => '[en_US:]Host[:en_US][ru_RU:]Host[:ru_RU]',
					'options' => array('0' => 'rapira.net', '1' => 'rapira.org'),
					'view' => 'select',
				),			
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('PRIVATE_KEY', 'UID');
			
			return $arrs;
		}		

		function options($options, $data, $m_id, $place) { 
		
			$m_defin = $this->get_file_data($m_id);
			
			$options = pn_array_unset($options, array('check_api', 'note', 'enableip', 'resulturl', 'help_resulturl', 'workstatus'));

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

		function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
			global $wpdb, $bids_data;

			$item_id = $bids_data->id;		
			$currency = strtoupper($bids_data->currency_code_give);
					
			$dest_tag = pn_strip_input($bids_data->dest_tag);	
			$to_account = pn_strip_input($bids_data->to_account);
			if (!$to_account) {
				
				$show_error = intval(is_isset($m_data, 'show_error'));
						
				if ('USDT' == $currency or 'BTC' == $currency or 'ETH' == $currency or 'USDC' == $currency or 'BNB' == $currency or 'DAI' == $currency or 'TRX' == $currency) { 
					$currency_id_give = $bids_data->currency_id_give;
					$currency_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_give'");
					if (isset($currency_data->id)) {
						$xml_value = mb_strtoupper(is_xml_value($currency_data->xml_value));
						if ('USDTERC' == $xml_value or 'USDTERC20' == $xml_value) { 
							$currency = 'usdt-erc20';
						} elseif ('USDTTRC' == $xml_value or 'USDTTRC20' == $xml_value) {	
							$currency = 'usdt-trc20';
						} elseif ('USDTBEP' == $xml_value or 'USDTBEP20' == $xml_value) {	
							$currency = 'usdt-bep20';
						} elseif ('BTCBEP20' == $xml_value) {	
							$currency = 'btc-bep20';
						} elseif ('ETHBEP20' == $xml_value) {	
							$currency = 'eth-bep20';
						} elseif ('USDCERC20' == $xml_value or 'USDCERC' == $xml_value) {	
							$currency = 'usdc-erc20';
						} elseif ('USDCBEP20' == $xml_value or 'USDCBEP' == $xml_value) {	
							$currency = 'usdc-bep20';
						} elseif ('USDCTRC20' == $xml_value or 'USDCTRC' == $xml_value) {	
							$currency = 'usdc-trc20';														
						} elseif ('DAIERC20' == $xml_value or 'DAIERC' == $xml_value) {	
							$currency = 'dai-erc20'; 
						} elseif ('BNBBEP20' == $xml_value or 'BNBBEP' == $xml_value) {	
							$currency = 'bnb-bep20'; 													
						} elseif ('DAIBEP20' == $xml_value or 'DAIBEP' == $xml_value) {	
							$currency = 'dai-bep20'; 
						} 
					}
				}						
						
				try {
					$class = new Rapira($this->name, $m_id, is_isset($m_defin, 'PRIVATE_KEY'), is_isset($m_defin, 'UID'), is_isset($m_defin, 'host_type'));
					$result = $class->create_address($currency);
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
				$class = new Rapira($this->name, $m_id, is_isset($m_defin, 'PRIVATE_KEY'), is_isset($m_defin, 'UID'), is_isset($m_defin, 'host_type'));
				$orders = $class->get_history_deposits(100);

				if (is_array($orders) and count($orders) > 0) {
					$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN('new','coldpay','techpay') AND m_in = '$m_id'");
					foreach ($items as $item) {
						$id = $item->id;
						$trans_in = pn_strip_input(is_isset($item, 'trans_in'));
						$to_account = pn_strip_input(is_isset($item, 'to_account'));
						$dest_tag = pn_strip_input(is_isset($item, 'dest_tag'));
						
						foreach ($orders as $order_key => $order) {
							$res_address = trim(is_isset($order, 'address'));
							$currency = strtoupper($order['unit']);
							$memo = pn_strip_input(is_isset($order,'memo'));
							if (!$memo and !$dest_tag or $memo == $dest_tag) {
								if (
									$res_address and $res_address == $to_account or
									$res_address and strtoupper($res_address) == strtoupper($to_account) and 'USDT' == $currency
								) {
									$res_status = strtoupper($order['status']);
									$res_txid = $order['txid'];
									$confirmations = 0;
									if (isset($order['confirmations'], $order['confirmations'])) {
										$confirmations = intval($order['confirmations']);
									}
									
									/*
									0 PENDING_CONFIRMATIONS,
									1 PENDING_AML,
									2 MANUAL_CHECK,
									4 SUCCESS,
									3 FAILED,
									5 REJECTED,
									6 RETURNED;
									*/									
									
									$realpay_st = array('SUCCESS');
									$coldpay_st = array('PENDING_CONFIRMATIONS', 'PENDING_AML', 'MANUAL_CHECK', 'MEMPOOL');
									$coldpay_st_need = array('PENDING_CONFIRMATIONS', 'PENDING_AML', 'MANUAL_CHECK', 'MEMPOOL');
									
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
										$in_sum = is_sum($in_sum, 12);
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
											if (0 == $err and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name) {
												if ($bid_currency == $currency or $invalid_ctype > 0) {
													if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {
														
														unset($orders[$order_key]);
														
														$params = array( 
															'pay_purse' => $pay_purse,
															'txid_in' => $res_txid,
															'sum' => $in_sum,
															'bid_sum' => $bid_sum,
															'bid_status' => array('new', 'techpay', 'coldpay'),
															'bid_corr_sum' => $bid_corr_sum,
															'currency' => $currency,
															'bid_currency' => $bid_currency,
															'invalid_ctype' => $invalid_ctype,
															'invalid_minsum' => $invalid_minsum,
															'invalid_maxsum' => $invalid_maxsum,
															'invalid_check' => $invalid_check,
															'm_place' => $bid_m_id.'_cron',
															'm_id' => $m_id,
															'm_data' => $m_data,
															'm_defin' => $m_defin,
														);
														set_bid_status($now_status, $id, $params, $data['direction_data']); 

														break;
														
													} else {
														$this->logs($id . ' The payment amount is less than the provisions', $m_id);
													}
												} else {
													$this->logs($id . ' Wrong currency', $m_id);
												}										
											} else {
												$this->logs($id . ' bid error', $m_id);
											}
										} else {
											$this->logs($id . ' Error check trans in!', $m_id);
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

new merchant_rapira(__FILE__, 'Rapira Crypto');