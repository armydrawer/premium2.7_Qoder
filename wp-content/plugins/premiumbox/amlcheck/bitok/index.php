<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]BitOk[:en_US][ru_RU:]BitOk[:ru_RU]
description: [en_US:]BitOk[:en_US][ru_RU:]BitOk[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AML_Premiumbox')) { return; }

if (!class_exists('amlcheck_bitok')) {
	class amlcheck_bitok extends Ext_AML_Premiumbox {

		function __construct($file, $title)
		{
			
			parent::__construct($file, $title, 0);
				
		}

		function get_map() {
			
			$map = array(
				'api_key'  => array(
					'title' => '[en_US:]Api key[:en_US][ru_RU:]Api key[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'api_secret'  => array(
					'title' => '[en_US:]Api secret[:en_US][ru_RU:]Api secret[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),			
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('api_key', 'api_secret');
			
			return $arrs;
		}

		function options($options, $data, $m_id, $place) { 
			
			$options['option_line'] = array(
				'view' => 'line',
			);			
			
			return $options;	
		}
		
		function check_addr($data, $m_id, $ch_data, $bid, $v, $account, $giveget, $m_defin, $m_data) {
			
			$api_key = pn_strip_input(is_isset($m_defin, 'api_key'));
			$api_secret = pn_strip_input(is_isset($m_defin, 'api_secret'));

			$class = new BitokAML($this->name, $m_id, is_isset($bid, 'id'), $api_key, $api_secret);
			$assets = $this->get_assets();	
			
			if ('give' == $giveget) {
				$currency_code = is_site_value(is_isset($bid, 'currency_code_give'));
				$currency_id = intval(is_isset($bid, 'currency_id_give'));
			} else {
				$currency_code = is_site_value(is_isset($bid, 'currency_code_get'));
				$currency_id = intval(is_isset($bid, 'currency_id_get'));
			}		

			if (isset($assets[$currency_code])) {
				$curr_data = $this->currency_code($currency_id, $v);
				
				$hash = trim(is_isset($ch_data, 'hash'));
				if (!$hash) {
					$res = $class->verify_addr($curr_data['network'], $curr_data['token_id'], $account);
					if (isset($res['id']) and $res['id']) {
						$hash = pn_strip_input($res['id']);
						amlcheck_sleep($m_id);
					}	
				}
				
				$data['hash'] = $hash;
				
				if ($hash) {
					$res2 = $class->check_risk($hash);
					if (isset($res2['status'])) {
						$data['status'] = 2;
						if ('checked' == $res2['status']) {
							$data['status'] = 1;
							$res_score = is_sum($res2['risk']);
							$res_signals = $class->check_addr($hash);
							$signals = array();
							$opts = get_aml_options($this->name);
							foreach ($opts as $opt_name => $opt_title) {
								$signals[$opt_name] = is_sum(is_isset($res_signals, $opt_name));
							}	
							$data['score'] = $res_score;
							$data['signals'] = $signals;
						} 
					}	
				}				
												
			}			
			
			return $data;
		}
		
		function check_trans($data, $m_id, $ch_data, $bid, $v, $address, $trans_in, $m_defin, $m_data) {
			
			$api_key = pn_strip_input(is_isset($m_defin, 'api_key'));
			$api_secret = pn_strip_input(is_isset($m_defin, 'api_secret'));

			$class = new BitokAML($this->name, $m_id, is_isset($bid, 'id'), $api_key, $api_secret);
			$assets = $this->get_assets();			
			
			$currency_code = is_site_value(is_isset($bid, 'currency_code_give'));
			$currency_id = intval(is_isset($bid, 'currency_id_give'));
				
			if (isset($assets[$currency_code])) {
				$curr_data = $this->currency_code($currency_id, $v);
				
				$hash = trim(is_isset($ch_data, 'hash'));
				if (!$hash) {
					$res = $class->verify_trans($curr_data['network'], $curr_data['token_id'], $trans_in, $address);
					if (isset($res['id']) and $res['id']) {
						$hash = pn_strip_input($res['id']);
						amlcheck_sleep($m_id);
					}	
				}
				
				$data['hash'] = $hash;
				
				if ($hash) {
					$res2 = $class->check_risk($hash);
					if (isset($res2['status'])) {
						$data['status'] = 2;
						if ('checked' == $res2['status']) {
							$data['status'] = 1;
							$res_score = is_sum($res2['risk']);
							$res_signals = $class->check_trans($hash);
							$signals = array();
							$opts = get_aml_options($this->name);
							foreach ($opts as $opt_name => $opt_title) {
								$signals[$opt_name] = is_sum(is_isset($res_signals, $opt_name));
							}	
							$data['score'] = $res_score;
							$data['signals'] = $signals;
						} 
					}	
				}				
				
			}			
			
			return $data;
		}	
		
		function aml_options() {
			
			$arr = array(
				'seized_funds' => 'seized_funds',
				'iaas' => 'iaas',
				'personal_wallet' => 'personal_wallet',
				'custodial_wallet' => 'custodial_wallet',
				'lending' => 'lending',
				'bridge' => 'bridge',
				'ico' => 'ico',
				'token_contract' => 'token_contract',
				'smart_contract' => 'smart_contract',
				'nft_marketplace' => 'nft_marketplace',
				'privacy_protocol' => 'privacy_protocol',
				'fraud_shop' => 'fraud_shop',
				'online_pharmacy' => 'online_pharmacy',
				'unnamed_service' => 'unnamed_service',
				'unnamed_wallet' => 'unnamed_wallet',
				'dust' => 'dust',
				'undefined' => 'undefined',
				'marketplace' => 'marketplace',
				'dex' => 'dex',
				'atm' => 'atm',
				'gambling' => 'gambling',
				'high_risk_jurisdiction' => 'high_risk_jurisdiction',    
				'mixer' => 'mixer',
				'enforcement_action' => 'enforcement_action',
				'darknet_market' => 'darknet_market',
				'illegal_service' => 'illegal_service',     
				'scam' => 'scam',
				'stolen_funds' => 'stolen_funds',
				'terrorist_financing' => 'terrorist_financing',
				'child_abuse_material' => 'child_abuse_material',
				'sanctions' => 'sanctions',
				'ransomware' => 'ransomware',
				'other' => 'other',
				'exchange' => 'exchange',
				'p2p_exchange' => 'p2p_exchange',
				'high_risk_exchange' => 'high_risk_exchange',
				'mining' => 'mining',
				'mining_pool' => 'mining_pool',
				'payment_service_provider' => 'payment_service_provider',	
			);
			
			return $arr;			
		}		

		function currency_code($currency_id, $v, $xml_value = '') {
			
			$xml_value = trim($xml_value);
	
			if (!$xml_value and isset($v[$currency_id])) {
				$vd = $v[$currency_id];
				$xml_value = mb_strtoupper(is_xml_value($vd->xml_value));
			}
	
			$arr = array(
				'network' => $xml_value,
				'token_id' => 'native',
			);	
	
			if ($xml_value) {
				if ('USDTERC20' == $xml_value) { 
					$arr = array(
						'network' => 'ETH',
						'token_id' => '0xdac17f958d2ee523a2206206994597c13d831ec7',
					);
				} elseif ('USDCERC20' == $xml_value) {	
					$arr = array(
						'network' => 'ETH',
						'token_id' => '0xa0b86991c6218b36c1d19d4a2e9eb0ce3606eb48',
					);			
				} elseif ('DAI' == $xml_value) {
					$arr = array(
						'network' => 'ETH',
						'token_id' => '0x6b175474e89094c44da98b954eedeac495271d0f',
					);				
				} elseif ('LINK' == $xml_value) {	
					$arr = array(
						'network' => 'ETH',
						'token_id' => '0x514910771af9ca656af840dff83e8264ecf986ca',
					);							
				} elseif ('MATIC' == $xml_value) {
					$arr = array(
						'network' => 'ETH',
						'token_id' => '0x7d1afa7b718fb893db30a3abc0cfc608aacfebb0',
					);			
				} elseif ('BNBBEP20' == $xml_value or 'BNBBEP' == $xml_value) {
					$arr = array(
						'network' => 'ETH',
						'token_id' => '0xb8c77482e45f1f44de1745f52c74426c631bdd52',
					);			
				} elseif ('WETH' == $xml_value) {
					$arr = array(
						'network' => 'ETH',
						'token_id' => '0xc02aaa39b223fe8d0a0e5c4f27ead9083c756cc2',
					);			
				} elseif ('USDTTRC20' == $xml_value) {
					$arr = array(
						'network' => 'TRX',
						'token_id' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
					);			
				} elseif ('USDCTRC20' == $xml_value) {
					$arr = array(
						'network' => 'TRX',
						'token_id' => 'TEkxiTehnzSmSe2XqrBj4w32RUN966rdz8',
					);							
				}
			}	
	
			return $arr;			
		}	

		function get_assets() {
			
			$assets = array(
				'ETH' => 'ETH',
				'USDT' => 'USDT',
				'USDC' => 'USDC',
				'DAI' => 'DAI',
				'LINK' => 'LINK',
				'MATIC' => 'MATIC',
				'BNB' => 'BNB',
				'WETH' => 'WETH',
				'TRX' => 'TRX',
				'BTC' => 'BTC',
				'LTC' => 'LTC',
			);	
			
			return $assets;
		}

		function setting_assets() {
			
			$assets = array(
				'ETH' => 'ETH',
				'USDTERC20' => 'USDTERC20',
				'USDCERC20' => 'USDCERC20',
				'DAI' => 'DAI',
				'LINK' => 'LINK',
				'MATIC' => 'MATIC',
				'BNBBEP20' => 'BNBBEP20',
				'WETH' => 'WETH',
				'TRX' => 'TRX',
				'BTC' => 'BTC',
				'LTC' => 'LTC',
				'USDTTRC20' => 'USDTTRC20',
				'USDCTRC20' => 'USDCTRC20',
			);
			
			return $assets;			
		}
		
		function test($id) {
			
			$form = new PremiumForm();
			$assets = $this->setting_assets();
			
			$options = array();
			$options['test1_title'] = array(
				'view' => 'h3',
				'title' => __('Check address', 'pn'),
				'submit' => __('Test', 'pn'),
			);
			$options['address'] = array(
				'view' => 'inputbig',
				'title' => __('Address', 'pn'),
				'default' => '',
				'name' => 'address',
			);
			$options['currency'] = array(
				'view' => 'select',
				'title' => __('Crypto currency', 'pn'),
				'options' => $assets,
				'default' => '',
				'name' => 'currency',
			);		
			$params_form = array(
				'form_link' => $this->test_action_link($id, 1),
				'button_title' => __('Test', 'pn'),
			);
			$form->init_form($params_form, $options);

			$options = array();
			$options['test2_title'] = array(
				'view' => 'h3',
				'title' => __('Check transaction', 'pn'),
				'submit' => __('Test', 'pn'),
			);
			$options['address'] = array(
				'view' => 'inputbig',
				'title' => __('Address', 'pn'),
				'default' => '',
				'name' => 'address',
			);
			$options['currency'] = array(
				'view' => 'select',
				'title' => __('Crypto currency', 'pn'),
				'options' => $assets,
				'default' => '',
				'name' => 'currency',
			);
			$options['txid'] = array(
				'view' => 'inputbig',
				'title' => __('TxID', 'pn'),
				'default' => '',
				'name' => 'txid',
			);		
			$params_form = array(
				'form_link' => $this->test_action_link($id, 2),
				'button_title' => __('Test', 'pn'),
			);
			$form->init_form($params_form, $options);			
			
		}

		function test_post($res, $m_id, $test_type, $m_defin) {
			
			$api_key = pn_strip_input(is_isset($m_defin, 'api_key'));
			$api_secret = pn_strip_input(is_isset($m_defin, 'api_secret'));
			
			$class = new BitokAML($this->name, $m_id, 0, $api_key, $api_secret);
			
			if (1 == $test_type) {
				
				$address = is_param_post('address');
				$currency = is_param_post('currency');		
					
				$arr = $this->currency_code(0, '', $currency);
				
				$res = $class->verify_addr($arr['network'], $arr['token_id'], $address);
				if (isset($res['id'])) {
					
					amlcheck_sleep($m_id);
					
					$res = $class->check_risk($res['id']);
					
				}				
				
			} elseif (2 == $test_type) {
			
				$address = is_param_post('address');
				$currency = is_param_post('currency');	
				$txid = is_param_post('txid');		
					
				$arr = $this->currency_code(0, '', $currency);
				
				$res = $class->verify_trans($arr['network'], $arr['token_id'], $txid, $address);
				if (isset($res['id'])) {
					
					amlcheck_sleep($m_id);
					
					$res = $class->check_risk($res['id']);
					
				}
				
			}
			
			return $res;
		}		
		
	}
}

new amlcheck_bitok(__FILE__, 'BitOk');