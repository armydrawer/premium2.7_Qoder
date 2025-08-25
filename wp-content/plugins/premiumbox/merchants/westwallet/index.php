<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]WestWallet[:en_US][ru_RU:]WestWallet[:ru_RU]
description: [en_US:]WestWallet merchant[:en_US][ru_RU:]мерчант WestWallet[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_westwallet')) {
	class merchant_westwallet extends Ext_Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach ($ids as $id) {
				add_action('premium_merchant_' . $id . '_status' . hash_url($id), array($this, 'merchant_status'));
			}

			add_filter('bcc_keys',array($this, 'set_keys'));
			add_filter('qr_keys',array($this, 'set_keys'));			
		}		
		
		function get_map() {
			
			$map = array(
				'CONFIRM_COUNT'  => array(
					'title' => '[en_US:]The required number of transaction confirmations[:en_US][ru_RU:]Количество подтверждения платежа, чтобы считать его выполненым[:ru_RU]',
					'view' => 'input',	
				),
				'PUBLIC_KEY'  => array(
					'title' => '[en_US:]Public key[:en_US][ru_RU:]Публичный ключ[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),	
				'PRIVATE_KEY'  => array(
					'title' => '[en_US:]Private key[:en_US][ru_RU:]Приватный ключ[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),				
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('CONFIRM_COUNT', 'PUBLIC_KEY', 'PRIVATE_KEY');
			
			return $arrs;
		}							
		
		function options($options, $data, $m_id, $place) {
			
			$m_defin = $this->get_file_data($m_id);
			
			$options = pn_array_unset($options, array('check_api', 'note', 'cronhash', 'workstatus'));			

			$text = '
			<div><strong>CALLBACK:</strong> <a href="'. get_mlink($m_id . '_status' . hash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_status' . hash_url($m_id)) . '</a></div>
			';

			$options[] = array(
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

				$list = array(
					'USDTERC' => 'USDT',
					'USDTERC20' => 'USDT',
					'USDTTRC' => 'USDTTRC',
					'USDTTRC20' => 'USDTTRC',
					'USDTTON' => 'USDTTON',
					'TON' => 'TON',
					'NOT' => 'NOT',
					'NOTCOIN' => 'NOT',
					'USDT' => 'USDTOMNI',
					'USDTOMNI' => 'USDTOMNI',
					'USDTBEP' => 'USDTBEP',
					'USDTBEP20' => 'USDTBEP',
					'BNBBEP' => 'BNB20',
					'BNBBEP20' => 'BNB20',
					'USDCTRC' => 'USDCTRC',
					'USDCTRC20' => 'USDCTRC',
				);
				$currency_id_give = $bids_data->currency_id_give;
				$currency_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_give'");
				if (isset($currency_data->id)) {
					$xml_value = mb_strtoupper(is_xml_value($currency_data->xml_value));
					if (isset($list[$xml_value])) {
						$currency = $list[$xml_value];
					}
				}		
						
				$public_key = is_isset($m_defin, 'PUBLIC_KEY');
				$private_key = is_isset($m_defin, 'PRIVATE_KEY');

				$show_error = intval(is_isset($m_data, 'show_error'));
				
				try {
					
					$class = new WestWallet($this->name, $m_id, $public_key, $private_key);
					$data = $class->generate_adress($currency, get_mlink($m_id . '_status' . hash_url($m_id)), $item_id);
					$to_account = pn_strip_input(is_isset($data, 'address'));
					$dest_tag = pn_strip_input(is_isset($data, 'dest_tag'));
					
				} catch (Exception $e) { 
				
					$this->logs($e->getMessage(), $m_id);
					
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
			
			return intval(is_isset($m_defin,'CONFIRM_COUNT'));
		}
		
		function merchant_status() {
			global $wpdb;
		
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_secure', $this->name, '', $m_id, $m_defin, $m_data);
		
			$address = pn_sfilter(pn_maxf_mb(pn_strip_input(is_param_req('address')), 200)); 
			$id = intval(is_param_req('id'));
			$dest_tag = pn_strip_input(is_param_req('dest_tag'));
			$currency = strtoupper(is_param_req('currency'));
			$in_sum = is_sum(is_param_req('amount'));
			$status = pn_strip_input(is_param_req('status'));
			$confirmations = intval(is_param_req('blockchain_confirmations'));
			$blockchain_hash = pn_strip_input(is_param_req('blockchain_hash'));
			
			if ('completed' != $status and 'pending' != $status) {
				$this->logs('Status not completted - ' . $status, $m_id);
				die('Status not completted - '. $status);
			}
			
			$public_key = is_isset($m_defin, 'PUBLIC_KEY');
			$private_key = is_isset($m_defin, 'PRIVATE_KEY');
			$class = new WestWallet($this->name, $m_id, $public_key, $private_key);
			$sdata = $class->get_search($id);

			$address = pn_sfilter(pn_maxf_mb(pn_strip_input(is_isset($sdata, 'address')), 200)); 
			$id = pn_strip_input(is_isset($sdata, 'id'));
			$dest_tag = pn_strip_input(is_isset($sdata, 'dest_tag'));
			$currency = strtoupper(is_isset($sdata, 'currency'));
			$label = intval(is_isset($sdata, 'label'));
			$in_sum = is_sum(is_isset($sdata, 'amount'));
			$status = pn_strip_input(is_isset($sdata, 'status'));
			$confirmations = intval(is_isset($sdata, 'blockchain_confirmations'));
			$blockchain_hash = pn_strip_input(is_isset($sdata, 'blockchain_hash'));
			
			if ('completed' != $status and 'pending' != $status) {
				$this->logs('Status not completted for history - ' . $status, $m_id);
				die('Status not completted for history - ' . $status);
			}			
			
			$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE to_account = '$address' AND id = '$label' AND status IN('new','techpay','coldpay')");
			$id = intval(is_isset($item, 'id'));
			$data = get_data_merchant_for_id($id, $item);
			
			$bid_err = $data['err'];
			$bid_status = $data['status'];
			$bid_m_id = $data['m_id'];
			$bid_m_script = $data['m_script'];
			
			if ($bid_err > 0) {
				$this->logs($id . ' The application does not exist or the wrong ID', $m_id);
				die('The application does not exist or the wrong ID');
			}			
			
			if ($bid_m_script and $bid_m_script != $this->name or !$bid_m_script) {	
				$this->logs($id . ' wrong script', $m_id);
				die('wrong script');
			}			
			
			if ($bid_m_id and $m_id != $bid_m_id or !$bid_m_id) {
				$this->logs($id . ' not a faithful merchant', $m_id);
				die('not a faithful merchant');				
			}			
			
			$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
			
			$bid_currency = $data['currency'];
			
			if (isset($data['bids_data']->currency_id_give)) {
				
				$list = array(
					'USDTERC' => 'USDT',
					'USDTERC20' => 'USDT',
					'USDTTRC' => 'USDTTRC',
					'USDTTRC20' => 'USDTTRC',
					'USDTTON' => 'USDTTON',
					'TON' => 'TON',
					'NOT' => 'NOT',
					'NOTCOIN' => 'NOT',
					'USDT' => 'USDTOMNI',
					'USDTOMNI' => 'USDTOMNI',
					'USDTBEP' => 'USDTBEP',
					'USDTBEP20' => 'USDTBEP',
					'BNBBEP' => 'BNB20',
					'BNBBEP20' => 'BNB20',
					'USDCTRC' => 'USDCTRC',
					'USDCTRC20' => 'USDCTRC',
				);
				
				$currency_id_give = $data['bids_data']->currency_id_give;
				$currency_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_give'");
				if (isset($currency_data->id)) {
					$xml_value = mb_strtoupper(is_xml_value($currency_data->xml_value));
					if (isset($list[$xml_value])) {
						$bid_currency = $list[$xml_value];
					}
				}
				
			}		
			
			$bid_sum = $data['pay_sum'];
			$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
			
			$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
			$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
			$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
			$invalid_check = intval(is_isset($m_data, 'check'));			
				 
			if ($bid_currency == $currency or $invalid_ctype > 0) {
				if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {		
						
					$conf_count = intval(is_isset($m_defin, 'CONFIRM_COUNT'));
					do_action('merchant_confirm_count', $id, $confirmations, $data['bids_data'], $data['direction_data'], $conf_count, $bid_m_id);
						
					$now_status = '';
						
					if ($confirmations >= $conf_count and 'completed' == $status) {
						if ('new' == $bid_status or 'coldpay' == $bid_status) { 
							$now_status = 'realpay';
						}
					} else {
						if ('new' == $bid_status) {
							$now_status = 'coldpay';								
						}
					}	
					
					if ($now_status) {
						
						$params = array(
							'pay_purse' => $pay_purse,
							'sum' => $in_sum,
							'bid_sum' => $bid_sum,
							'bid_corr_sum' => $bid_corr_sum,
							'bid_status' => array('new', 'techpay', 'coldpay'),
							'txid_in' => $blockchain_hash,
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
						
						set_bid_status($now_status, $id, $params, $data['direction_data']); 	
						echo 'ok';	
						exit;
						
					}
									
				} else {
					
					$this->logs($id . ' The payment amount is less than the provisions', $m_id);
					die('The payment amount is less than the provisions');
					
				}
			} else {
				
				$this->logs($id . ' Wrong type of currency', $m_id);
				die('Wrong type of currency');
				
			}
		} 
	}
}

new merchant_westwallet(__FILE__, 'WestWallet');