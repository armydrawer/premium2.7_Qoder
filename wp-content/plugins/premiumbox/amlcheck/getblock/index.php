<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]GetBlock[:en_US][ru_RU:]GetBlock[:ru_RU]
description: [en_US:]GetBlock[:en_US][ru_RU:]GetBlock[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_AML_Premiumbox')) { return; }

if (!class_exists('amlcheck_getblock')) {
	class amlcheck_getblock extends Ext_AML_Premiumbox {

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
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('api_key');
			
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
			
			$class = new GetBlockAML($this->name, $m_id, is_isset($bid, 'id'), $api_key);
			$assets = $this->get_assets();
	
			if ('give' == $giveget) { 
				$currency_code = is_site_value(is_isset($bid, 'currency_code_give'));
				$currency_code = $this->currency_code(is_isset($bid, 'currency_id_give'), $v, $currency_code);
			} else {
				$currency_code = is_site_value(is_isset($bid, 'currency_code_get'));
				$currency_code = $this->currency_code(is_isset($bid, 'currency_id_get'), $v, $currency_code);
			}
			
			if (isset($assets[$currency_code])) {	
			
				$hash = trim(is_isset($ch_data, 'hash'));
				if (!$hash) {
					$res = $class->verify_addr($account, $currency_code, $giveget . time());
					if (isset($res['result'], $res['result']['check'], $res['result']['check']['hash']) and $res['result']['check']['hash']) {
						$hash = pn_strip_input($res['result']['check']['hash']);
						amlcheck_sleep($m_id);
					}	
				}
				
				$data['hash'] = $hash;
				
				if ($hash) {
					$res = $class->info($hash, '2' . $giveget . time());
					if (isset($res['result'], $res['result']['check'])) {
						$data['status'] = 2;
						if (isset($res['result']['check']['status'], $res['result']['check']['report']) and 'SUCCESS' == $res['result']['check']['status']) {
							$data['status'] = 1;
							$res_score = is_sum(is_isset($res['result']['check']['report'], 'riskscore')) * 100;
							$res_signals = is_isset($res['result']['check']['report'], 'signals');
							$signals = array();
							$opts = get_aml_options($this->name);
							foreach ($opts as $opt_name => $opt_title) {
								$signals[$opt_name] = is_sum(is_isset($res_signals, $opt_name));
							}	
							$data['score'] = $res_score;
							$data['signals'] = $signals;
							if (isset($res['result']['check']['shareLink'])) {
								$data['link'] = pn_strip_input($res['result']['check']['shareLink']);
							}
						} 
					}	
				}
			}			
			
			return $data;
		}

		function check_trans($data, $m_id, $ch_data, $bid, $v, $address, $trans_in, $m_defin, $m_data) {
			
			$api_key = pn_strip_input(is_isset($m_defin, 'api_key'));

			$class = new GetBlockAML($this->name, $m_id, is_isset($bid, 'id'), $api_key);
			$assets = $this->get_assets();
			
            $currency_code = is_site_value(is_isset($bid, 'currency_code_give'));
            $currency_id = intval(is_isset($bid, 'currency_id_give'));
			$currency_code = $this->currency_code($currency_id, $v, $currency_code);

			if (isset($assets[$currency_code])) {
				
				$hash = trim(is_isset($ch_data, 'hash'));
				if (!$hash) {
					$res = $class->verify_trans($address, $currency_code, $trans_in, 'm' . time());
					if (isset($res['result'], $res['result']['check'], $res['result']['check']['hash']) and $res['result']['check']['hash']) {
						$hash = pn_strip_input($res['result']['check']['hash']);
						amlcheck_sleep($m_id);
					}	
				}	
				
				$data['hash'] = $hash;
				if ($hash) {
					$res = $class->info($hash, '2m' . time());
					if (isset($res['result'], $res['result']['check'])) {
						$data['status'] = 2;
						if (isset($res['result']['check']['status'], $res['result']['check']['report']) and 'SUCCESS' == $res['result']['check']['status']) {
							$data['status'] = 1;
							$res_score = is_sum(is_isset($res['result']['check']['report'], 'riskscore')) * 100;
							$res_signals = is_isset($res['result']['check']['report'], 'signals');
							$signals = array();
							$opts = get_aml_options($this->name);
							foreach ($opts as $opt_name => $opt_title) {
								$signals[$opt_name] = is_sum(is_isset($res_signals, $opt_name));
							}	
							$data['score'] = $res_score;
							$data['signals'] = $signals;
							if (isset($res['result']['check']['shareLink'])) {
								$data['link'] = pn_strip_input($res['result']['check']['shareLink']);
							}
						} 
					}	
				}					
			
			}			
			
			return $data;
		}		
		
		function aml_options() {
			
			$arr = array(
				'exchange_licensed' => 'exchange licensed',
				'p2p_exchange_licensed' => 'p2p exchange licensed',
				'seized_assets' => 'seized_assets',
				'other' => 'other',
				'transparent' => 'transparent',
				'atm' => 'Atm',
				'exchange_unlicensed' => 'exchange_unlicensed',
				'p2p_exchange_unlicensed' => 'p2p_exchange_unlicensed',
				'liquidity_pools' => 'liquidity_pools',
				'dark_service' => 'Dark Service',
				'dark_market' => 'Dark Market',
				'enforcement_action' => 'enforcement_action',
				'exchange_fraudulent' => 'Exchange Fraudulent',
				'exchange_mlrisk_high' => 'Exchange Mlrisk high',
				'exchange_mlrisk_low' => 'Exchange Mlrisk low',
				'exchange_mlrisk_moderate' => 'Exchange Mlrisk moderate',
				'exchange_mlrisk_veryhigh' => 'Exchange Mlrisk veryhigh',
				'gambling' => 'Gambling',
				'illegal_service' => 'Illegal Service',
				'marketplace' => 'Marketplace',
				'miner' => 'Miner',
				'mixer' => 'Mixer',    
				'payment' => 'Payment',
				'wallet' => 'Wallet',
				'p2p_exchange_mlrisk_high' => 'p2p_exchange_mlrisk_high',
				'p2p_exchange_mlrisk_low' => 'p2p exchange mlrisk low',     
				'stolen_coins' => 'Stolen coins',
				'ransom' => 'Ransom',
				'scam' => 'Scam',
				'child_exploitation' => 'child_exploitation',
				'sanctions' => 'sanctions',
				'terrorism_financing' => 'terrorism_financing',	
			);
			
			return $arr;			
		}		
		
		function currency_code($currency_id, $v, $currency_code = '') { 
			
			$currency_code = mb_strtoupper($currency_code);
			
			if (isset($v[$currency_id])) {
				$vd = $v[$currency_id];
				$xml_value = mb_strtoupper(is_xml_value($vd->xml_value));
			
				if ('USDTERC' == $xml_value or 'USDTERC20' == $xml_value) { 
					$currency_code = 'USDT_ERC20';
				} elseif ('USDTTRC' == $xml_value or 'USDTTRC20' == $xml_value) {	
					$currency_code = 'USDT_TRC20';
				} elseif ('USDCTRC20' == $xml_value) {	
					$currency_code = 'USDC_TRC20';	
				} elseif ('USDCERC20' == $xml_value) {	
					$currency_code = 'USDC_ERC20';	
				} elseif ('USDTBEP20' == $xml_value) {	
					$currency_code = 'USDT_BEP20';
				} elseif ('USDCBEP20' == $xml_value) {	
					$currency_code = 'USDC_BEP20';			
				}
				
			}
			
			return $currency_code;			
		}

		function get_assets() {
			
			$assets = array(
				'BTC' => 'BTC',
				'BCH' => 'BCH',
				'BSV' => 'BSV',
				'ETH' => 'ETH',
				'ETC' => 'ETC',
				'LTC' => 'LTC',
				'TRX' => 'TRX',
				'XRP' => 'XRP',
				'XLM' => 'XLM',
				'MATIC' => 'MATIC',
				'USDT_ERC20' => 'USDT_ERC20',
				'USDT_TRC20' => 'USDT_TRC20',
				'USDC_ERC20' => 'USDC_ERC20',
				'USDC_TRC20' => 'USDC_TRC20',
				'USDT_BEP20' => 'USDT_BEP20',
				'USDC_BEP20' => 'USDC_BEP20',		
			);		
			
			return $assets;
		}

		function setting_assets() {
			
			$assets = array(
				'BTC' => 'BTC',
				'BCH' => 'BCH',
				'BSV' => 'BSV',
				'ETH' => 'ETH',
				'ETC' => 'ETC',
				'LTC' => 'LTC',
				'TRX' => 'TRX',
				'XRP' => 'XRP',
				'XLM' => 'XLM',
				'MATIC' => 'MATIC',
				'USDT_ERC20' => 'USDT_ERC20',
				'USDT_TRC20' => 'USDT_TRC20',
				'USDC_ERC20' => 'USDC_ERC20',
				'USDC_TRC20' => 'USDC_TRC20',
				'USDT_BEP20' => 'USDT_BEP20',
				'USDC_BEP20' => 'USDC_BEP20',		
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
				'button_title' => __('Test','pn'),
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

			$options = array();
			$options['test3_title'] = array(
				'view' => 'h3',
				'title' => __('Check hash', 'pn'),
				'submit' => __('Test', 'pn'),
			);
			$options['hash'] = array(
				'view' => 'inputbig',
				'title' => __('Hash', 'pn'),
				'default' => '',
				'name' => 'hash',
			);	
			$params_form = array(
				'form_link' => $this->test_action_link($id, 3),
				'button_title' => __('Test', 'pn'),
			);
			$form->init_form($params_form, $options);			
			
		}

		function test_post($res, $m_id, $test_type, $m_defin) {
			
			$api_key = pn_strip_input(is_isset($m_defin, 'api_key'));
			
			$class = new GetBlockAML($this->name, $m_id, 0, $api_key);
			
			if (1 == $test_type) {
				
				$res = $class->verify_addr(is_param_post('address'), is_param_post('currency'), 't' . time());
	
			} elseif (2 == $test_type) {
				
				$res = $class->verify_trans(is_param_post('address'), is_param_post('currency'), is_param_post('txid'), 't' . time());
	
			} elseif (3 == $test_type) {
				
				$res = $class->info(is_param_post('hash'), 't' . time());
	
			}
			
			return $res;
		}		
		
	}
}

new amlcheck_getblock(__FILE__, 'GetBlock');