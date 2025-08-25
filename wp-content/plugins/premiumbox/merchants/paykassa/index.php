<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]PayKassa[:en_US][ru_RU:]PayKassa[:ru_RU]
description: [en_US:]PayKassa merchant[:en_US][ru_RU:]мерчант PayKassa[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_paykassa')) {
	class merchant_paykassa extends Ext_Merchant_Premiumbox {
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title);
			
			$ids = $this->get_ids('merchants', $this->name);

			foreach($ids as $id) {
				add_action('premium_merchant_' . $id . '_status' . hash_url($id), array($this, 'merchant_status'));
				add_action('premium_merchant_' . $id . '_fail', array($this, 'merchant_fail'));
				add_action('premium_merchant_' . $id . '_success', array($this, 'merchant_success'));
			}
			
			add_filter('bcc_keys', array($this, 'set_keys'));
			add_filter('qr_keys', array($this, 'set_keys'));
		}
		
		function _list_systems() {
			
			return array(
				'11' => 'BitCoin(BTC)', 
				'12' => 'Ethereum(ETH)', 
				'14' => 'Litecoin(LTC)',
				'15' => 'Dogecoin(DOGE)', 
				'16' => 'Dash(DASH)', 
				'18' => 'BitcoinCash(BCH)', 
				'21' => 'EthereumClassic(ETC)', 
				'22' => 'Ripple(XRP)',
				'27' => 'TRON(TRX)', 
				'28' => 'Stellar(XLM)', 
				'29' => 'BinanceCoin(BNB)', 
				'30' => 'TRON_TRC20(USDT)', 
				'31' => 'BinanceSmartChain_BEP20(USDT, BUSD, USDC, ADA, EOS, BTC, ETH, DOGE, SHIB)',
				'32' => 'Ethereum_ERC20(USDT, BUSD, USDC, SHIB)',
			);
			
		}		
		
		function get_map() {
			
			$map = array(
				'SHOP_ID'  => array(
					'title' => '[en_US:]Shop ID[:en_US][ru_RU:]ID магазина[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'SHOP_PASS'  => array(
					'title' => '[en_US:]Shop secret key[:en_US][ru_RU:]Секеретный ключ магазина[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),				
			);
			
			return $map;
		}		
		
		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('SHOP_ID', 'SHOP_PASS');
			
			return $arrs;
		}						
		
		function options($options, $data, $m_id, $place) { 
			
			$options = pn_array_unset($options, array('check_api', 'cronhash', 'workstatus'));
			
			$paymethods = $this->_list_systems();	
			$options['paymethod'] = array(
				'view' => 'select',
				'title' => __('Payment method', 'pn'),
				'options' => $paymethods,
				'default' => is_isset($data, 'paymethod'),
				'name' => 'paymethod',
				'work' => 'int',
			);

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
			<div><strong>Status URL:</strong> <a href="' . get_mlink($m_id . '_status' . hash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_status' . hash_url($m_id)) . '</a></div>
			<div><strong>Success URL:</strong> <a href="' . get_mlink($m_id . '_success') . '" target="_blank">' . get_mlink($m_id . '_success') . '</a></div>
			<div><strong>Fail URL:</strong> <a href="' . get_mlink($m_id . '_fail') . '" target="_blank">' . get_mlink($m_id . '_fail') . '</a></div>		
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
			$to_account = pn_strip_input($bids_data->to_account);
			$dest_tag = '';

			if (!$to_account) {
					
				$show_error = intval(is_isset($m_data, 'show_error'));
						
				try {
							
					$system_id = intval(is_isset($m_data, 'paymethod'));
					$paymethods = $this->_list_systems();
					if (!isset($paymethods[$system_id])) {
						$system_id = array_key_first($paymethods);
					}		

					$pay_sum = is_sum($pay_sum, 8);

					$text_pay = get_text_pay($m_id, $bids_data, $pay_sum);
							
					$class = new PayKassa($this->name, $m_id, is_isset($m_defin, 'SHOP_ID'), is_isset($m_defin, 'SHOP_PASS'), '', '');
					$res = $class->create_invoice($item_id, $pay_sum, $currency, $system_id, $text_pay);
					if (isset($res['data'], $res['data']['wallet'])) { 
						$to_account = pn_strip_input(is_isset($res['data'], 'wallet'));
						$dest_tag = pn_strip_input(is_isset($res['data'], 'tag'));
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
			
			$need_confirm = intval(is_isset($m_data, 'need_confirm'));
			
			return $need_confirm;
		}

		function merchant_fail() {
			
			$id = get_payment_id('ac_order_id');
			redirect_merchant_action($id, $this->name);
			
		}

		function merchant_success() {
			
			$id = get_payment_id('ac_order_id');
			redirect_merchant_action($id, $this->name, 1);
			
		}
	
		function merchant_status() {
	
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_secure', $this->name, '', $m_id, $m_defin, $m_data);
	
			$show_error = intval(is_isset($m_data, 'show_error'));
			$need_confirm = intval(is_isset($m_data, 'need_confirm'));
			
			$sci_type = trim(is_param_post('type'));
			if ('sci_confirm_transaction_notification' != $sci_type) {
				echo pn_strip_input(is_param_post('order_id')) . '|success';
				exit;
			}
	
			try {
				
				$class = new PayKassa($this->name, $m_id, is_isset($m_defin, 'SHOP_ID'), is_isset($m_defin, 'SHOP_PASS'), '', '');
				
				$private_hash = pn_strip_input(is_param_post('private_hash'));
				if (strlen($private_hash) < 1) {
					die('no private hash');
				}
				
				$res = $class->sci_confirm_transaction_notification($private_hash);

				if (isset($res['error']) and $res['error']) {
					if ($show_error) {
						echo $res['message']; 
					} else {
						_e('Error!','pn');
					}					
				} elseif (isset($res['data'], $res['data']['shop_id'])) {
					
					if ($res['data']['shop_id'] != is_isset($m_defin, 'SHOP_ID')) {
						die('error shop id');
					}
					
					$id = intval($res["data"]["order_id"]);   
					$transaction_id = pn_strip_input($res["data"]["transaction"]);
					$txid = pn_strip_input($res["data"]["txid"]);               
					$currency = pn_strip_input($res["data"]["currency"]);       
					$amount = pn_strip_input($res["data"]["amount"]);         
					$system = pn_strip_input($res["data"]["system"]);         
					$address = pn_strip_input($res["data"]["address"]);	
					$address_from = pn_strip_input($res["data"]["address_from"]);
					$tag = pn_strip_input($res["data"]["tag"]);
					$confirmations = intval($res["data"]["confirmations"]);
					$required_confirmations = intval($res["data"]["required_confirmations"]);
					$status = strtolower($res["data"]["status"]);	

					$data = get_data_merchant_for_id($id);
					
					$in_sum = $amount;
					$in_sum = is_sum($in_sum, 8);
					$bid_err = $data['err'];
					$bid_status = $data['status'];
					$bid_m_id = $data['m_id'];
					$bid_m_script = $data['m_script'];
							
					if ($bid_err > 0) {
						$this->logs($id . ' The application does not exist or the wrong ID', $m_id);
						die('The application does not exist or the wrong ID');
					}					
					
					if ($bid_m_script and $bid_m_script != $this->name or !$bid_m_script) {	
						$this->logs('wrong script', $m_id);
						die('wrong script');
					}			
					
					if ($bid_m_id and $m_id != $bid_m_id or !$bid_m_id) {
						$this->logs('not a faithful merchant', $m_id);
						die('not a faithful merchant');				
					}					
			
					$bid_address = trim($data['bids_data']->to_account);
					$bid_tag = trim($data['bids_data']->dest_tag);
					
					if (!$bid_address or $bid_address != $address) {
						die('wrong address');	
					}
					
					if ($bid_tag and $bid_tag != $tag or $bid_tag and !$tag) {
						die('wrong tag');
					}			
			
					$pay_purse = is_pay_purse($address_from, $m_data, $bid_m_id);
			
					$bid_currency = $data['currency'];
					$bid_currency = str_replace('RUR', 'RUB', $bid_currency);
			
					$bid_sum = is_sum($data['pay_sum'], 8);
					$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
			
					$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
					$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
					$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
					$invalid_check = intval(is_isset($m_data, 'check'));
					
					$set_status = 'coldpay';
					if ('yes' == $status) {
						$set_status = 'realpay';
					}
					if ($need_confirm > 0 and $need_confirm >= $confirmations) {
						$set_status = 'realpay';
					}
			 
					if ($bid_currency == $currency or $invalid_ctype > 0) {
						if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {		
								
							$params = array(
								'sum' => $in_sum,
								'bid_sum' => $bid_sum,
								'bid_corr_sum' => $bid_corr_sum,
								'bid_status' => array('new', 'techpay', 'coldpay'),
								'pay_purse' => $pay_purse,
								'trans_in' => $transaction_id,
								'txid_in' => $txid,
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
							set_bid_status($set_status, $id, $params, $data['direction_data']); 
										
							echo $id . '|success'; // обязательно, для подтверждения зачисления платежа
										
						} else {
							die('The payment amount is less than the provisions');
						}
					} else {
						die('Wrong type of currency');
					}					
				}

			}
			catch( Exception $e ) {
				$this->logs($e->getMessage(), $m_id);
				if ($show_error and current_user_can('administrator')) {
					die(__('Error!', 'pn') . ' ' . $e->getMessage());
				} else {
					die(__('Error!', 'pn'));
				}
			}			
		}		
	}
}

new merchant_paykassa(__FILE__, 'PayKassa');		