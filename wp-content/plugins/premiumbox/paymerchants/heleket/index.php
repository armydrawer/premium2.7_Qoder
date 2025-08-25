<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Heleket[:en_US][ru_RU:]Heleket[:ru_RU]
description: [en_US:]Heleket automatic payouts[:en_US][ru_RU:]авто выплаты Heleket[:ru_RU]
version: 2.7.0
*/

if(!class_exists('Ext_AutoPayut_Premiumbox')){ return; }

if(!class_exists('paymerchant_heleket')){
	class paymerchant_heleket extends Ext_AutoPayut_Premiumbox {
		
		public $currency_lists = '';
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			$this->currency_lists = array('USDT','TRX','BTC','ETH','LTC','DASH','TON');		
		}

		function get_map() {
			$map = array(
				'MERCHANT_ID'  => array(
					'title' => '[en_US:]Merchant id[:en_US][ru_RU:]Merchant id[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),			
				'API_KEY'  => array(
					'title' => '[en_US:]Api key[:en_US][ru_RU:]Api key[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'PAY_API_KEY'  => array(
					'title' => '[en_US:]Payment api key[:en_US][ru_RU:]Payment api key[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),				
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('MERCHANT_ID','API_KEY','PAY_API_KEY');
			return $arrs;
		}
		
		function options($options, $data, $m_id, $place){
			
			$m_defin = $this->get_file_data($m_id);
			
			$options = pn_array_unset($options, array('checkpay','note','resulturl','enableip'));		
			
			$options['line_error_status'] = array(
				'view' => 'line',
			);
			
			$options['get_prio'] = array(
				'view' => 'select',
				'title' => __('Priority','pn'),
				'options' => array('' => 'recommended', 'economy' => 'economy', 'high' => 'high', 'highest' => 'highest'),
				'default' => is_isset($data, 'get_prio'),
				'name' => 'get_prio',
				'work' => 'input',
			);			
			
			$options['is_subtract'] = array(
				'view' => 'select',
				'title' => __('Comission','pn'),
				'options' => array('0'=>__('With amount','pn'), '1'=>__('With balance','pn')),
				'default' => is_isset($data, 'is_subtract'),
				'name' => 'is_subtract',
				'work' => 'int',
			);			
			
			$options['currency_code'] = array(
				'view' => 'input',
				'title' => __('Currency code','pn'),
				'default' => is_isset($data, 'currency_code'),
				'name' => 'currency_code',
				'work' => 'input',
			);
			$options['currency_code_help'] = array(
				'view' => 'help',
				'title' => __('Help','pn'),
				'default' => 'USDT,TRX,BTC,ETH,LTC,DASH,TON',
			);			
			$options['network'] = array(
				'view' => 'input',
				'title' => __('Network','pn'),
				'default' => is_isset($data, 'network'),
				'name' => 'network',
				'work' => 'input',
			);
			$options['network_help'] = array(
				'view' => 'help',
				'title' => __('Help','pn'),
				'default' => 'TRON,BTC,ETH,LTC,DASH,TON',
			);

			$options['from_currency'] = array(
				'view' => 'input',
				'title' => __('Currency code (Auto conversion)','pn'),
				'default' => is_isset($data, 'from_currency'),
				'name' => 'from_currency',
				'work' => 'input',
			);			

			$text = '
			<div><strong>CRON:</strong> <a href="'. get_mlink('ap_'. $m_id .'_cron' . chash_url($m_id, 'ap')) .'" target="_blank">'. get_mlink('ap_'. $m_id .'_cron' . chash_url($m_id, 'ap')) .'</a></div>
			';
			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;
		}			

		function get_reserve_lists($m_id, $m_defin){
			
			$currencies = $this->currency_lists;
			
			$purses = array();
			
			foreach($currencies as $currency){
				$purses[$m_id . '_' . strtolower($currency)] = strtoupper($currency);
			} 
			
			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin){ 
			$sum = 0;
			$purse = strtoupper(trim(str_replace($m_id . '_','',$code))); 
			if($purse){
				
				try {

					$class = new AP_Heleket($this->name, $m_id, is_isset($m_defin, 'MERCHANT_ID'), is_isset($m_defin, 'API_KEY'));
					$res = $class->get_balance();
						
					if (isset($res[$purse])) {
						$sum = is_sum($res[$purse]);
					}
					
				}
				catch (Exception $e)
				{
					$this->logs($e->getMessage(), $m_id);		
				} 				
			}
			return $sum;
		}		

		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin){
			
			$item_id = $item->id;			
			$trans_id = 0;				
			
			$currency_code_give = strtoupper($item->currency_code_give);
			$currency_code_get = strtoupper($item->currency_code_get);
			
			$currency_id_give = intval($item->currency_id_give);
			$currency_id_get = intval($item->currency_id_get);			
							
			$account = $item->account_get;
					
			if (!$account) {
				$error[] = __('Wrong client wallet','pn');
			}			
					
			$out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 8);

			$currency_code = is_site_value(is_isset($paymerch_data, 'currency_code'));
			$get_prio = is_site_value(is_isset($paymerch_data, 'get_prio'));
			$from_currency = is_site_value(is_isset($paymerch_data, 'from_currency'));
			$network = is_site_value(is_isset($paymerch_data, 'network'));
			$is_subtract = intval(is_isset($paymerch_data, 'is_subtract'));
			
			$dest_tag = trim(is_isset($unmetas,'dest_tag'));
			
			if(count($error) == 0){
				$result = $this->set_ap_status($item, $test);
				if($result){
					try {
						$class = new AP_Heleket($this->name, $m_id, is_isset($m_defin, 'MERCHANT_ID'), is_isset($m_defin, 'API_KEY'));
						$res = $class->create_payout($sum, $currency_code, $network, $item_id, $account, $is_subtract, is_isset($m_defin, 'PAY_API_KEY'), $from_currency, $dest_tag, $get_prio);
						if (isset($res['uuid'])) {
							$trans_id = $res['uuid'];
						} else {
							$error[] = __('Payout error','pn');
							$pay_error = 1;	
						}
					}
					catch (Exception $e)
					{
						$error[] = $e;
						$pay_error = 1;
					} 		
				} else {
					$error[] = 'Database error';
				}				
			}
					
			if(count($error) > 0){
				
				$this->reset_ap_status($error, $pay_error, $item, $place, $m_id, $test);
				
			} else {	
						
				$params = array(
					'trans_out' => $trans_id,
					'out_sum' => $out_sum,
					'system' => 'user',
					'm_place' => $modul_place. ' ' .$m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);
				set_bid_status('coldsuccess', $item_id, $params, $direction); 	
						
				if($place == 'admin'){
					pn_display_mess(__('Automatic payout is done','pn'),__('Automatic payout is done','pn'),'true');
				} 		
			}
		}	

		function cron($m_id, $m_defin, $m_data) {
		global $wpdb;
			
			$error_status = is_status_name(is_isset($m_data, 'error_status'));
			
			$class = new AP_Heleket($this->name, $m_id, is_isset($m_defin, 'MERCHANT_ID'), is_isset($m_defin, 'API_KEY'));
			$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status = 'coldsuccess' AND m_out='$m_id'");
			foreach ($items as $item) {
				$order_id = $item->id;
				$trans_out = trim($item->trans_out);
				if ($trans_out) {
					$order = $class->get_payout_status($trans_out, is_isset($m_defin, 'PAY_API_KEY'));
					if (isset($order['uuid'],$order['status'])) {
						$check_status = strtolower($order['status']); 
						$st = array('paid');
							
						if(in_array($check_status, $st)){
									
							$params = array(
								'txid_out' => pn_strip_input(is_isset($order,'txid')),
								'system' => 'system',
								'bid_status' => array('coldsuccess'),
								'm_place' => 'cron ' .$m_id,
								'm_id' => $m_id,
								'm_defin' => $m_defin,
								'm_data' => $m_data,
							);
							set_bid_status('success', $item->id, $params);
									
						} elseif(in_array($check_status, array('fail','cancel','system_error'))){
									
							$this->reset_cron_status($item, $error_status, $m_id);
									
						}		
					}
				}	
			}
		}		
	}
}

new paymerchant_heleket(__FILE__, 'Heleket');