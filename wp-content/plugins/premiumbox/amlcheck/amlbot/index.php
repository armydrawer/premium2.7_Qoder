<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]AML Bot[:en_US][ru_RU:]AML Bot[:ru_RU]
description: [en_US:]AML Bot[:en_US][ru_RU:]AML Bot[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AML_Premiumbox')) { return; }

if (!class_exists('amlcheck_amlbot')) {
	class amlcheck_amlbot extends Ext_AML_Premiumbox {

		function __construct($file, $title)
		{
			
			parent::__construct($file, $title, 0);
				
		}

		function get_map() {
			
			$map = array(
				'access_id'  => array(
					'title' => '[en_US:]Access ID[:en_US][ru_RU:]Access ID[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'access_key'  => array(
					'title' => '[en_US:]Access key[:en_US][ru_RU:]Access key[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),				
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('access_id', 'access_key');
			
			return $arrs;
		}

		function options($options, $data, $m_id, $place) { 
			
			$m_defin = $this->get_file_data($m_id);
			
			$options = pn_array_unset($options, array('api_timeout'));

			$options['option_line'] = array(
				'view' => 'line',
			);							
			
			return $options;	
		}	
		
		function check_addr($data, $m_id, $ch_data, $bid, $v, $account, $giveget, $m_defin, $m_data) {
			
			$access_id = pn_strip_input(is_isset($m_defin, 'access_id'));
			$access_key = pn_strip_input(is_isset($m_defin, 'access_key'));

			$class = new AMLClass($this->name, $m_id, is_isset($bid, 'id'), $access_id, $access_key);
			$assets = $this->get_assets();	
			
			if ('give' == $giveget) {
				$currency_code = is_site_value(is_isset($bid, 'currency_code_give'));
				$currency_id = intval(is_isset($bid, 'currency_id_give'));
			} else {
				$currency_code = is_site_value(is_isset($bid, 'currency_code_get'));
				$currency_id = intval(is_isset($bid, 'currency_id_get'));
			}			
			
			$currency_code = $this->currency_code($currency_id, $v, $currency_code);
			if (isset($assets[$currency_code])) {
				
				$info = '';
				$hash = trim(is_isset($ch_data, 'hash'));
				if (!$hash) {
					$res = $class->verify_address($account, $currency_code);
					if (isset($res['result'], $res['data']) and isset($res['data']['status'], $res['data']['uid'])) {
						$data['hash'] = $hash = pn_strip_input($res['data']['uid']);
						if ('success' == $res['data']['status']) {
							$info = $res;
						}	
					}
				} 
				if ($hash) {
					if (!is_array($info)) {
						$data['status'] = 2;
						$res = $class->check_uid($hash);
						if ('success' == $res['data']['status']) {
							$info = $res;
						}
					} 
					if (is_array($info)) {
						$data['status'] = 1;
						$res_score = is_sum(is_isset($res['data'], 'riskscore')) * 100;
						$res_signals = is_isset($res['data'], 'signals');
						$signals = array();
						$opts = get_aml_options($this->name);
						foreach ($opts as $opt_name => $opt_title) {
							$signals[$opt_name] = is_sum(is_isset($res_signals, $opt_name));
						}	
						$data['score'] = $res_score;
						$data['signals'] = $signals;
						/*
						if (isset($res['data']['pdfReport'])) {
							$data['link'] = pn_strip_input($res['data']['pdfReport']);
						}			
						*/
					}
				}						
					 				
			}			
			
			return $data;
		}	
		
		function check_trans($data, $m_id, $ch_data, $bid, $v, $address, $trans_in, $m_defin, $m_data) {
			
			$access_id = pn_strip_input(is_isset($m_defin, 'access_id'));
			$access_key = pn_strip_input(is_isset($m_defin, 'access_key'));

			$class = new AMLClass($this->name, $m_id, is_isset($bid, 'id'), $access_id, $access_key);
			$assets = $this->get_assets();

			$currency_code = is_site_value(is_isset($bid, 'currency_code_give'));
			$currency_id = intval(is_isset($bid, 'currency_id_give'));
			
			$currency_code = $this->currency_code($currency_id, $v, $currency_code);
			if (isset($assets[$currency_code])) {
				
				$info = '';
				$hash = trim(is_isset($ch_data, 'hash'));
				if (!$hash) {
					$res = $class->verify_trans($address, $currency_code, $trans_in);
					if (isset($res['result'], $res['data']) and isset($res['data']['status'], $res['data']['uid'])) {
						$data['hash'] = $hash = pn_strip_input($res['data']['uid']);
						if ('success' == $res['data']['status']) {
							$info = $res;
						}	
					}
				} 
				if ($hash) {
					if (!is_array($info)) {
						$data['status'] = 2;
						$res = $class->check_uid($hash);
						if ('success' == $res['data']['status']) {
							$info = $res;
						}
					} 
					if (is_array($info)) {
						$data['status'] = 1;
						$res_score = is_sum(is_isset($res['data'], 'riskscore')) * 100;
						$res_signals = is_isset($res['data'], 'signals');
						$signals = array();
						$opts = get_aml_options($this->name);
						foreach ($opts as $opt_name => $opt_title) {
							$signals[$opt_name] = is_sum(is_isset($res_signals, $opt_name));
						}	
						$data['score'] = $res_score;
						$data['signals'] = $signals;
						if (isset($res['data']['pdfReport'])) {
							$data['link'] = pn_strip_input($res['data']['pdfReport']);
						}						
					}
				}				
									
			}			
			
			return $data;
		}		
		
		function aml_options() {
			
			$arr = array(
				'ransom' => 'Ransom',
				'dark_service' => 'Dark Service',
				'other' => 'Other',
				'stolen_coins' => 'Stolen coins',
				'infrastructure_as_a_service' => 'infrastructure as a service',
				'gambling' => 'Gambling',
				'scam' => 'Scam',
				'exchange_mlrisk_veryhigh' => 'exchange mlrisk veryhigh',
				'miner' => 'Miner',
				'token_smart_contract' => 'token smart contract',
				'exchange_mlrisk_low' => 'exchange mlrisk low',
				'illicit_actor_org' => 'illicit actor org',
				'ico' => 'Ico',
				'exchange_fraudulent' => 'Exchange Fraudulent',
				'p2p_exchange_mlrisk_low' => 'p2p exchange mlrisk low',
				'p2p_exchange' => 'p2p exchange',
				'dark_market' => 'Dark Market',
				'illegal_service' => 'Illegal Service',
				'payment' => 'Payment',
				'atm' => 'Atm',
				'exchange_mlrisk_high' => 'exchange mlrisk high',
				'lending_contract' => 'lending contract',
				'risky_exchange' => 'risky exchange',
				'child_exploitation' => 'child_exploitation',
				'wallet' => 'wallet',
				'marketplace' => 'marketplace',
				'exchange_mlrisk_moderate' => 'exchange mlrisk moderate',
				'p2p_exchange_mlrisk_high' => 'p2p exchange mlrisk high',
				'decentralized_exchange_contract' => 'decentralized exchange contract',
				'fraud_shop' => 'fraud shop',
				'enforcement_action' => 'enforcement action',   
				'protocol_privacy' => 'protocol privacy',
				'unnamed_service' => 'unnamed service',
				'seized_assets' => 'seized assets',		
				'mixer' => 'Mixer',
				'liquidity_pools' => 'liquidity pools',
				'terrorism_financing' => 'terrorism financing',
				'exchange' => 'exchange',
				'smart_contract' => 'smart contract',
				'sanctions' => 'sanctions',
				'high_risk_jurisdiction' => 'high risk jurisdiction',
				'merchant_services' => 'merchant services',	
			);
			
			return $arr;			
		}	

		function currency_code($currency_id, $v, $currency_code = '') {
			
			$currency_code = mb_strtoupper($currency_code);
			
			if (isset($v[$currency_id])) {
				$vd = $v[$currency_id];
				$xml_value = mb_strtoupper(is_xml_value($vd->xml_value));
				
				if ('USDT' == $xml_value or 'USDTOMNI' == $xml_value) { 
					$currency_code = 'TetherOMNI';
				} elseif ('USDTERC' == $xml_value or 'USDTERC20' == $xml_value) { 
					$currency_code = 'TetherERC20';
				} elseif ('USDTTRC' == $xml_value or 'USDTTRC20' == $xml_value) {	
					$currency_code = 'TRX';
				} elseif ('USDTBEP' == $xml_value or 'USDTBEP20' == $xml_value) {	
					$currency_code = 'BSC';		
				} elseif ('BNBBEP20' == $xml_value) {	
					$currency_code = 'BSC';
				} elseif ('USDCTRC20' == $xml_value) {	
					$currency_code = 'TRX';	
				} elseif ('USDCERC20' == $xml_value) {	
					$currency_code = 'ETH';	
				} elseif ('TUSDTRC20' == $xml_value) {	
					$currency_code = 'TRX';	
				} elseif ('TUSDERC20' == $xml_value) {	
					$currency_code = 'ETH';								
				}		
						
			}
	
			return $currency_code;			
		}
		
		function get_assets() {
			
			$assets = array(
				'BTC' => 'BTC',
				'ETH' => 'ETH',
				'LTC' => 'LTC',
				'BCH' => 'BCH',
				'XRP' => 'XRP',
				'ETC' => 'ETC',
				'BSV' => 'BSV',
				'TRX' => 'TRX',
				'BSC' => 'BSC',
				'MATIC' => 'MATIC',
				'DOGE' => 'DOGE',
				'ZEC' => 'ZEC',
				'ADA' => 'ADA',
				'SOL' => 'SOL',
				'ALGO' => 'ALGO',
				'TetherOMNI' => 'TetherOMNI',
				'TetherERC20' => 'TetherERC20'
			);
			
			return $assets;
		}		
		
		function setting_assets() {
			
			$assets = array(
				'BTC' => 'BTC',
				'ETH' => 'ETH',
				'LTC' => 'LTC',
				'BCH' => 'BCH',
				'XRP' => 'XRP',
				'ETC' => 'ETC',
				'BSV' => 'BSV',
				'TRX' => 'TRX',
				'BSC' => 'BSC',
				'MATIC' => 'MATIC',
				'DOGE' => 'DOGE',
				'ZEC' => 'ZEC',
				'ADA' => 'ADA',
				'SOL' => 'SOL',
				'ALGO' => 'ALGO',
				'TetherOMNI' => 'TetherOMNI',
				'TetherERC20' => 'TetherERC20'
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
			$types = array(
				'0' => 'deposit',
				'1' => 'withdrawal',
			);
			$options['type'] = array(
				'view' => 'select',
				'title' => __('Type', 'pn'),
				'options' => $types,
				'default' => '',
				'name' => 'type',
			);		
			$params_form = array(
				'form_link' => $this->test_action_link($id, 2),
				'button_title' => __('Test', 'pn'),
			);
			$form->init_form($params_form, $options);
			
			$options = array();
			$options['test3_title'] = array(
				'view' => 'h3',
				'title' => __('Check uid', 'pn'),
				'submit' => __('Test', 'pn'),
			);
			$options['uid'] = array(
				'view' => 'inputbig',
				'title' => __('UID', 'pn'),
				'default' => '',
				'name' => 'uid',
			);	
			$params_form = array(
				'form_link' => $this->test_action_link($id, 3),
				'button_title' => __('Test', 'pn'),
			);
			$form->init_form($params_form, $options);			
			
		}

		function test_post($res, $m_id, $test_type, $m_defin) {
			
			$access_id = pn_strip_input(is_isset($m_defin, 'access_id'));
			$access_key = pn_strip_input(is_isset($m_defin, 'access_key'));	

			$class = new AMLClass($this->name, $m_id, 0, $access_id, $access_key);
			
			if (1 == $test_type) {
				
				$res = $class->verify_address(is_param_post('address'), is_param_post('currency'));
				
			} elseif (2 == $test_type) {
				
				$res = $class->verify_trans(is_param_post('address'), is_param_post('currency'), is_param_post('txid'), intval(is_param_post('type')));	

			} elseif (3 == $test_type) {	
				
				$res = $class->check_uid(is_param_post('uid'));
				
			} 
			
			return $res;
		}		
		
	}
}

new amlcheck_amlbot(__FILE__, 'AML Bot');