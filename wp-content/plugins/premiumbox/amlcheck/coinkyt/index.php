<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]CoinKyt[:en_US][ru_RU:]CoinKyt[:ru_RU]
description: [en_US:]CoinKyt[:en_US][ru_RU:]CoinKyt[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AML_Premiumbox')) { return; }

if (!class_exists('amlcheck_coinkyt')) {
	class amlcheck_coinkyt extends Ext_AML_Premiumbox {

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

			$class = new CoinKytAML($this->name, $m_id, is_isset($bid, 'id'), $api_key);
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
				$res = $class->check_addr($account, $curr_data['token'], $curr_data['blockchain']);
				if (isset($res['risk'])) {
					
					$data['status'] = 1;
					$res_score = is_sum($res['risk']);
					$res_signals = $res['risks'];
					$signals = array();
					$opts = get_aml_options($this->name);
					foreach ($opts as $opt_name => $opt_title) {
						$signals[$opt_name] = is_sum(is_isset($res_signals, $opt_name));
					}	
					$data['score'] = $res_score;
					$data['signals'] = $signals;							
			
				} 												
			}					
			
			return $data;
		}	
		
		function check_trans($data, $m_id, $ch_data, $bid, $v, $address, $trans_in, $m_defin, $m_data) {
			
			$api_key = pn_strip_input(is_isset($m_defin, 'api_key'));

			$class = new CoinKytAML($this->name, $m_id, is_isset($bid, 'id'), $api_key);
			$assets = $this->get_assets();	
			
			$currency_code = is_site_value(is_isset($bid, 'currency_code_give'));
			$currency_id = intval(is_isset($bid, 'currency_id_give'));
					
			if (isset($assets[$currency_code])) {
				$curr_data = $this->currency_code($currency_id, $v);	
				$res = $class->check_trans($trans_in, $curr_data['token'], $curr_data['blockchain']);                                                            
				if (isset($res['risk'])) {
					
					$data['status'] = 1;
					$res_score = is_sum($res['risk']);
					$res_signals = $res['risks'];
					$signals = array();
					$opts = get_aml_options($this->name);
					foreach ($opts as $opt_name => $opt_title) {
						$signals[$opt_name] = is_sum(is_isset($res_signals, $opt_name));
					}	
					$data['score'] = $res_score;
					$data['signals'] = $signals;
												 
				}				
			}			
			
			return $data;
		}			
		
		function aml_options() {
			
			$arr = array(
				'p2p_exchange_unlicensed' => 'P2P Exchange unlicensed',
				'exchange_unlicensed' => 'Exchange unlicensed',
				'atm' => 'ATM',
				'decentralized_exchange' => 'Decentralized exchange',
				'p2p_exchange_licensed' => 'P2P Exchange licensed',
				'exchange_licensed' => 'Exchange licensed',
				'other' => 'Other',
				'unknown_owner' => 'Unknown owner',
				'rewards/fees' => 'Rewards/Fees',
				'miner' => 'Miner',
				'online_marketplace' => 'Online marketplace',
				'online_wallet' => 'Online wallet',
				'payment_systеm' => 'Payment system',
				'scam_crypto_exchange' => 'Scam crypto exchange',
				'darknet_marketplace' => 'Darknet marketplace',
				'darknet_service' => 'Darknet service',
				'scam' => 'Scam',
				'gambling' => 'Gambling',
				'stolen_assets' => 'Stolen assets',
				'mixing_service' => 'Mixing service',
				'ransom' => 'Ransom',
				'sanctions' => 'Sanctions',
				'terrorism_financing' => 'Terrorism financing',
				'illegal_service' => 'Illegal service',	
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
				'blockchain' => $xml_value,
				'token' => '',
			);	
			
			if ($xml_value) {
				if ('USDTERC20' == $xml_value) { 
					$arr = array(
						'blockchain' => 'ETH',
						'token' => 'USDT',
					);
				} elseif ('USDCERC20' == $xml_value) {	
					$arr = array(
						'blockchain' => 'ETH',
						'token' => 'USDC',
					);			
				} elseif ('USDTTRC20' == $xml_value) {
					$arr = array(
						'blockchain' => 'TRX',
						'token' => 'USDT',
					);			
				} elseif ('USDCTRC20' == $xml_value) {
					$arr = array(
						'blockchain' => 'TRX',
						'token' => 'USDC',
					);							
				}
			}	
			
			$arr['blockchain'] = strtolower($arr['blockchain']);
			
			return $arr;			
		}	

		function get_assets() {
			
			$assets = array(
				'ETH' => 'ETH',
				'USDT' => 'USDT',
				'USDC' => 'USDC',
				'TRX' => 'TRX',
				'BTC' => 'BTC',
			);	
			
			return $assets;
		}

		function setting_assets() {
			
			$assets = array(
				'ETH' => 'ETH',
				'TRX' => 'TRX',
				'BTC' => 'BTC',
				'USDTERC20' => 'USDTERC20',
				'USDCERC20' => 'USDCERC20',
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
			$options['txid'] = array(
				'view' => 'inputbig',
				'title' => __('TxID', 'pn'),
				'default' => '',
				'name' => 'txid',
			);
			$options['currency'] = array(
				'view' => 'select',
				'title' => __('Crypto currency', 'pn'),
				'options' => $assets,
				'default' => '',
				'name' => 'currency',
			);		
			$params_form = array(
				'form_link' => $this->test_action_link($id, 2),
				'button_title' => __('Test', 'pn'),
			);
			$form->init_form($params_form, $options);			
			
		}

		function test_post($res, $m_id, $test_type, $m_defin) {
			
			$api_key = pn_strip_input(is_isset($m_defin, 'api_key'));
			
			$class = new CoinKytAML($this->name, $m_id, 0, $api_key);
			
			if (1 == $test_type) {
				
				$address = is_param_post('address');
				$currency = is_param_post('currency');		
					
				$arr = $this->currency_code(0, '', $currency);
				
				$res = $class->check_addr($address, $arr['token'], $arr['blockchain']); 				
				
			} elseif (2 == $test_type) {
				
				$currency = is_param_post('currency');	
				$txid = is_param_post('txid');		
					
				$arr = $this->currency_code(0, '', $currency);
				
				$res = $class->check_trans($txid, $arr['token'], $arr['blockchain']);
				
			}
			
			return $res;
		}		
		
	}
}

new amlcheck_coinkyt(__FILE__, 'CoinKyt');