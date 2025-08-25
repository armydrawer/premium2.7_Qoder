<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]PayKassa[:en_US][ru_RU:]PayKassa[:ru_RU]
description: [en_US:]PayKassa automatic payouts[:en_US][ru_RU:]авто выплаты PayKassa[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_paykassa')) {
	class paymerchant_paykassa extends Ext_AutoPayut_Premiumbox {
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title);				
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
					'title' => '[en_US:]Shop ID for payouts[:en_US][ru_RU:]ID магазина для выплат[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'API_ID'  => array(
					'title' => '[en_US:]API ID[:en_US][ru_RU:]API ID[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'API_PASS'  => array(
					'title' => '[en_US:]API password[:en_US][ru_RU:]API пароль[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),				
			);
			
			return $map;
		}
		
		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('API_ID', 'API_PASS', 'SHOP_ID');
			
			return $arrs;
		}	
		
		function options($options, $data, $id, $place) {
			
			$options = pn_array_unset($options, array('checkpay', 'resulturl', 'error_status', 'cronhash', 'enableip', 'note'));
			
			$paymethods = $this->_list_systems();			
			$options['paymethod'] = array(
				'view' => 'select',
				'title' => __('Transaction type', 'pn'),
				'options' => $paymethods,
				'default' => is_isset($data, 'paymethod'),
				'name' => 'paymethod',
				'work' => 'int',
			);
			
			$options['prio'] = array(
				'view' => 'select',
				'title' => __('Priority', 'pn'),
				'options' => array('0' => 'low', '1' => 'medium', '2' => 'high'),
				'default' => is_isset($data, 'prio'),
				'name' => 'prio',
				'work' => 'int',
			);			
			
			return $options;
		}			
		
		function get_reserve_lists($m_id, $m_defin) {
			
			$keys = array(
				"payeer_rub",
				"advcash_rub",
				"payeer_usd",
				"perfectmoney_usd",
				"advcash_usd",
				"bitcoin_btc",
				"ethereum_eth",
				"litecoin_ltc",
				"dogecoin_doge",
				"dash_dash",
				"bitcoincash_bch",
				"zcash_zec",
				"monero_xmr",
				"ethereumclassic_etc",
				"ripple_xrp",
				"berty_rub",
				"berty_usd",
				"neo_neo",
				"gas_gas",
				"bitcoinsv_bsv",
				"waves_waves",
				"tron_trx",
				"stellar_xlm",
				"binancecoin_bnb",
				"tron_trc20_usdt",
				"binancesmartchain_bep20_usdt",
				"ethereum_erc20_usdt",
				"binancesmartchain_bep20_btc",
				"binancesmartchain_bep20_eth",
				"binancesmartchain_bep20_doge",
				"binancesmartchain_bep20_busd",
				"binancesmartchain_bep20_usdc",
				"binancesmartchain_bep20_ada",
				"binancesmartchain_bep20_eos",
				"ethereum_erc20_busd",
				"ethereum_erc20_usdc",
				"binancesmartchain_bep20_shib",
				"ethereum_erc20_shib"
			);
			
			$purses = array();
			foreach ($keys as $key) {
				$key = trim($key);
				if ($key) {
					$purses[$m_id . '_' . $key] = $key;
				}	
			}
			
			return $purses;
		}			

		function update_reserve($code, $m_id, $m_defin) { 
		
			$sum = 0;
			try {
				
				$class = new AP_PayKassa($this->name, $m_id, is_isset($m_defin, 'SHOP_ID'), '', is_isset($m_defin, 'API_ID'), is_isset($m_defin, 'API_PASS'));
				$res = $class->get_balance();	
					
				$now_key = str_replace($m_id . '_', '', $code);
						
				if (isset($res[$now_key])) {
					$sum = is_sum($res[$now_key]);
				}		
						
			}
			catch (Exception $e)
			{
							
			} 									
			
			return $sum;
		}		
		
		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {
			
			$item_id = $item->id;			
			$trans_id = 0;	
			$txid_out = '';
			
			$system_id = intval(is_isset($paymerch_data, 'paymethod'));
			$paymethods = $this->_list_systems();
			if (!isset($paymethods[$system_id])) {
				$system_id = array_key_first($paymethods);
			}
			
			$prio = intval(is_isset($paymerch_data,'prio'));
			$priority = 'low';
			$prio_arr = array('0' => 'low', '1' => 'medium', '2' => 'high');
			if (isset($prio_arr[$prio])) {
				$priority = $prio_arr[$prio];
			}			
			
			$currency = mb_strtoupper($item->currency_code_get);
			$currency = str_replace(array('RUR'), 'RUB', $currency);
		
			$account = $item->account_get;
					
			$out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 8);
						
			if (0 == count($error)){

				$result = $this->set_ap_status($item, $test);
				if ($result) {
					
					$dest_tag = trim(is_isset($unmetas, 'dest_tag'));
						
					try{
						
						$class = new AP_PayKassa($this->name, $m_id, is_isset($m_defin, 'SHOP_ID'), '', is_isset($m_defin, 'API_ID'), is_isset($m_defin, 'API_PASS'));
						$res = $class->create_payout($sum, $currency, $system_id, $account, $dest_tag, $priority);

						if (isset($res['error']) and $res['error']) {       
							$error[] = 'Error: ' . is_isset($res, 'message'); 
							$pay_error = 1;
						} elseif (isset($res['data'], $res['data']['shop_id'])) {
							$trans_id = $res['data']['payment_id'];                       
							$txid_out = $res['data']['txid'];
						} else {
							$error[] = 'Class error';
							$pay_error = 1;
						}						
						
					}
					catch (Exception $e)
					{
						$error[] = $e->getMessage();
						$pay_error = 1;
					} 
							
				} else {
					$error[] = 'Database error';
				}
									
			}
					
			if (count($error) > 0) {
				
				$this->reset_ap_status($error, $pay_error, $item, $place, $m_id, $test);
				
			} else {	
				
				$params = array(
					'trans_out' => $trans_id,
					'txid_out' => $txid_out,
					'out_sum' => $out_sum,
					'm_place' => $modul_place . ' ' . $m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);
				set_bid_status('success', $item_id, $params, $direction); 						
						
				if ('admin' == $place) {
					pn_display_mess(__('Automatic payout is done', 'pn'), __('Automatic payout is done', 'pn'), 'true');
				} 	
			}
		}				
	}
}

new paymerchant_paykassa(__FILE__, 'PayKassa');