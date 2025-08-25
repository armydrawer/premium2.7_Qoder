<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Coinpayments[:en_US][ru_RU:]Coinpayments[:ru_RU]
description: [en_US:]Coinpayments merchant[:en_US][ru_RU:]мерчант Coinpayments[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_coinpayments')) {
	class merchant_coinpayments extends Ext_Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach ($ids as $id) {
				add_action('premium_merchant_' . $id . '_status' . hash_url($id), array($this, 'merchant_status'));
			}

			add_filter('bcc_keys', array($this, 'set_keys'));
			add_filter('qr_keys', array($this, 'set_keys'));			
		}	
		
		function get_map() {
			
			$map = array(
				'CONFIRM_COUNT'  => array(
					'title' => '[en_US:]The required number of transaction confirmations[:en_US][ru_RU:]Количество подтверждения платежа, чтобы считать его выполненым[:ru_RU]',
					'view' => 'input',	
				),
				'PUBLIC_KEY'  => array(
					'title' => '[en_US:]Public key[:en_US][ru_RU:]Публичный Ключ[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),	
				'PRIVAT_KEY'  => array(
					'title' => '[en_US:]Privat key[:en_US][ru_RU:]Приватный Ключ[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'SECRET'  => array(
					'title' => '[en_US:]Password №1. Any characters with no spaces (responsible for the security of payment)[:en_US][ru_RU:]Пароль №1. Любые символы без пробелов (отвечает за безопасность платежа)[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'SECRET2'  => array(
					'title' => '[en_US:]Password №2. Any characters with no spaces (responsible for the security of payment)[:en_US][ru_RU:]Пароль №2. Любые символы без пробелов (отвечает за безопасность платежа)[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),				
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('CONFIRM_COUNT', 'PUBLIC_KEY', 'PRIVAT_KEY', 'SECRET', 'SECRET2');
			
			return $arrs;
		}									
		
		function options($options, $data, $id, $place) { 
		
			$options = pn_array_unset($options, array('check_api', 'note', 'cronhash', 'workstatus'));				

			return $options;
		}

		function merch_type($m_id) {
			
			return 'address';  
		}

		function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
			global $wpdb, $bids_data;
			
			$item_id = $bids_data->id;
			$currency = $bids_data->currency_code_give;
			$to_account = pn_strip_input($bids_data->to_account);
			
			if (!$to_account) {
					
				$PUBLIC_KEY = is_isset($m_defin, 'PUBLIC_KEY');
				$PRIVAT_KEY = is_isset($m_defin, 'PRIVAT_KEY');

				$show_error = intval(is_isset($m_data, 'show_error'));
						
				$ipn_url = get_mlink($m_id . '_status' . hash_url($m_id)) . '?invoice_id=' . $item_id . '&secret=' . urlencode(is_isset($m_defin, 'SECRET')) . '&secret2=' . urlencode(is_isset($m_defin, 'SECRET2'));
						
				try {
					$class = new CoinPaymentsAPI($this->name, $m_id, $PRIVAT_KEY, $PUBLIC_KEY);
					$result = $class->create_adress($currency, $ipn_url);
					if (isset($result['result']) and isset($result['result']['address'])) { 
						$to_account = pn_strip_input($result['result']['address']);
						$dest_tag = pn_strip_input(is_isset($result['result'],'dest_tag'));
					} 
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
			
			return intval(is_isset($m_defin, 'CONFIRM_COUNT'));
		}
		
		function merchant_status() {
			
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_secure', $this->name, '', $m_id, $m_defin, $m_data);
	
			$sAddress = isset($_REQUEST['address']) ? $_REQUEST['address'] : null; 
			$secret = isset($_REQUEST['secret']) ? $_REQUEST['secret'] : null; 
			$secret2 = isset($_REQUEST['secret2']) ? $_REQUEST['secret2'] : null; 
			$currency = isset($_REQUEST['currency']) ? $_REQUEST['currency'] : '';
			$invoice_id = isset($_REQUEST['invoice_id']) ? $_REQUEST['invoice_id'] : null; 
			$sTransferHash = isset($_REQUEST['txn_id']) ? $_REQUEST['txn_id'] : null;
			$iConfirmCount = isset($_REQUEST['confirms']) ? $_REQUEST['confirms'] - 0 : 0;
			$in_sum = isset($_REQUEST['amount']) ? $_REQUEST['amount'] : null; 

			if (urldecode($secret) != is_isset($m_defin, 'SECRET')) {
				$this->logs('wrong secret!', $m_id);
				die('wrong secret!');
			}

			if (urldecode($secret2) != is_isset($m_defin, 'SECRET2')) {
				$this->logs('wrong secret!', $m_id);
				die('wrong secret!');
			}
  
			$currency = strtoupper($currency);
  
			$id = intval($invoice_id);
			$data = get_data_merchant_for_id($id);
			
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

			if (check_trans_in($m_id, $sTransferHash, $id)) {
				$this->logs($id . ' Error check trans in!', $m_id);
				die('Error check trans in!');
			}			
			
			$pay_purse = is_pay_purse('', $m_data, $bid_m_id);
			
			$bid_currency = $data['currency'];
			
			$bid_sum = $data['pay_sum'];
			$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
			
			$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
			$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
			$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
			$invalid_check = intval(is_isset($m_data, 'check'));			
				 
			if ($bid_currency == $currency or $invalid_ctype > 0) {
				if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {		
						
					$conf_count = intval(is_isset($m_defin, 'CONFIRM_COUNT'));
					do_action('merchant_confirm_count', $id, $iConfirmCount, $data['bids_data'], $data['direction_data'], $conf_count, $m_id);
						
					$now_status = '';
						
					if ($iConfirmCount >= $conf_count) {
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
							'bid_status' => array('new', 'techpay', 'coldpay'),
							'bid_corr_sum' => $bid_corr_sum,
							'trans_in' => $sTransferHash,
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
									 	
						die('ok');								
					}
		
				} else {
					$this->logs($id . ' Wrong type of currency', $m_id);
					die('Wrong type of currency');
				}
			} else {
				$this->logs($id . ' In the application the wrong status', $m_id);
				die('In the application the wrong status');
			}
		}
	}
}

new merchant_coinpayments(__FILE__, 'Coinpayments');