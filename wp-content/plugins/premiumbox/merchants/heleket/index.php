<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Heleket[:en_US][ru_RU:]Heleket[:ru_RU]
description: [en_US:]Heleket merchant[:en_US][ru_RU:]мерчант Heleket[:ru_RU]
version: 2.7.0
*/

if(!class_exists('Ext_Merchant_Premiumbox')){ return; }

if(!class_exists('merchant_heleket')) {
	class merchant_heleket extends Ext_Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach($ids as $id) {
				add_action('premium_merchant_'. $id .'_status' . hash_url($id), array($this,'merchant_status'));
			}	

			add_filter('bcc_keys',array($this,'set_keys'));
			add_filter('qr_keys',array($this,'set_keys'));
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
			);
			return $map;
		}

		function settings_list(){
			$arrs = array();
			$arrs[] = array('MERCHANT_ID','API_KEY');
			return $arrs;
		}

		function options($options, $data, $m_id, $place) { 
			
			$options = pn_array_unset($options, array('personal_secret','check_api','note','workstatus'));					
			
			$options['typepay'] = array(
				'view' => 'select',
				'title' => __('Type','pn'),
				'options' => array('0' => 'Invoice', '1' => 'Address'),
				'default' => is_isset($data, 'typepay'),
				'name' => 'typepay',
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
				'default' => 'USDT,TRX,BTC,ETH,LTC,DASH',
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
				'default' => 'TRON,BTC,ETH,LTC,DASH',
			);
			/*
			$options['convert_to'] = array(
				'view' => 'input',
				'title' => __('Convert to','pn'),
				'default' => is_isset($data, 'convert_to'),
				'name' => 'convert_to',
				'work' => 'input',
			);
			*/

			$text = '
			<div><strong>Status URL:</strong> <a href="'. get_mlink($m_id .'_status' . hash_url($m_id)) .'" target="_blank">'. get_mlink($m_id .'_status' . hash_url($m_id)) .'</a></div>
			<div><strong>Cron:</strong> <a href="'. get_mlink($m_id.'_cron' . chash_url($m_id)) .'" target="_blank">'. get_mlink($m_id.'_cron' . chash_url($m_id)) .'</a></div>		
			';

			$options['text_line'] = array(
				'view' => 'line',
			);			
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
			
			$currency_code = strtoupper(pn_strip_input(is_isset($m_data, 'currency_code')));
			$network = strtoupper(pn_strip_input(is_isset($m_data, 'network')));
			$convert_to = strtoupper(pn_strip_input(is_isset($m_data, 'convert_to')));
			$typepay = intval(is_isset($m_data, 'typepay'));

			$item_id = $bids_data->id;		
			$currency = strtoupper($bids_data->currency_code_give);
			$currency_id_give = $bids_data->currency_id_give;
			$to_account = pn_strip_input($bids_data->to_account);
			$dest_tag = '';
			$trans_in = '';

			if (!$to_account) {
					
				$show_error = intval(is_isset($m_data, 'show_error'));
						
				try {
					$pay_sum = is_sum($pay_sum, 12);
							
					$item_id = $bids_data->id;
							
					$callback_url = get_mlink($m_id .'_status' . hash_url($m_id)) . '?order_id=' . $bids_data->id;
					$return_url = get_bids_url($bids_data->hashed);
							
					$class = new Heleket($this->name, $m_id, is_isset($m_defin,'MERCHANT_ID'), is_isset($m_defin,'API_KEY'));
					if ($typepay) {
						$res = $class->create_address($currency_code, $network, $item_id, $callback_url);
					} else {
						$res = $class->create_invoice($pay_sum, $currency_code, $network, $item_id, $return_url, $callback_url, 43200, $convert_to);
					}
					if(isset($res['uuid'],$res['address'])){ 
						$to_account = pn_strip_input($res['address']);
						$dest_tag = '';
						$trans_in = pn_strip_input($res['uuid']);
					} 
				} catch (Exception $e) { 
					$this->logs($e->getMessage(), $m_id);	
				}
						
				if ($to_account) {
							
					$arr = array();
					$arr['to_account'] = $to_account;
					$arr['dest_tag'] = $dest_tag;
					$arr['trans_in'] = $trans_in;
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

		function merchant_status() {
			
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			$callback = file_get_contents('php://input');
			$post = @json_decode($callback, true);
			
			do_action('merchant_secure', $this->name, $post, $m_id, $m_defin, $m_data);
			
			$uuid = trim(is_isset($post, 'uuid'));
			$this->status_bids(is_param_get('order_id'), $m_id, $m_defin, $m_data, $uuid);
			
		}

		function cron($m_id, $m_defin, $m_data) {
			$this->status_bids('', $m_id, $m_defin, $m_data, '');
		}

		function status_bids($order_id, $m_id, $m_defin, $m_data, $uuid='') {
		global $wpdb;
		
			$order_id = intval($order_id);
			$uuid = trim($uuid);

			$where = '';
			if ($order_id) {
				$where = " AND id = '$order_id'";
			}	

			$show_error = intval(is_isset($m_data, 'show_error'));
			$currency_code = strtoupper(pn_strip_input(is_isset($m_data, 'currency_code')));
			$network = strtoupper(pn_strip_input(is_isset($m_data, 'network')));	
			
			try {
				$class = new Heleket($this->name, $m_id, is_isset($m_defin,'MERCHANT_ID'), is_isset($m_defin,'API_KEY'));
				$items = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE status IN('new','techpay','coldpay') AND m_in = '$m_id' $where");
				foreach ($items as $item) {
					$id = $item->id;
					
					$to_account = strtoupper(pn_strip_input(is_isset($item,'to_account')));
					$dest_tag = pn_strip_input(is_isset($item,'dest_tag'));
					$trans_in = trim(is_isset($item,'trans_in'));
					if ($uuid) {
						$trans_in = $uuid;
					}					
					if ($trans_in) {
						$order = $class->get_status($trans_in);	
						if (isset($order['status'], $order['uuid'], $order['order_id']) and $order['order_id'] == $id) {
							
							$order_amount = is_sum($order['amount']);
							$order_address = strtoupper(trim(is_isset($order,'address')));
							$order_currency = strtoupper($order['currency']);
							$order_network = strtoupper($order['network']);
							$order_memo = '';
							$order_from = $order['from'];
							$order_txid = pn_strip_input(is_isset($order,'txid'));
							$order_uuid = pn_strip_input(is_isset($order,'uuid'));
							$order_status = strtolower(is_isset($order,'status'));
							if (isset($order['payment_amount'])) {
								$order_amount = is_sum($order['payment_amount']);
							}

							if (!$order_memo and !$dest_tag or $order_memo == $dest_tag) {
								if(
									$order_address and $order_address == $to_account
								){
									
									$realpay_st = array('paid','wrong_amount','paid_over');
									$coldpay_st = array('confirm_check','wrong_amount_waiting');
									
									$data = get_data_merchant_for_id($id, $item);
										
									$now_status = '';
									if (in_array($order_status, $realpay_st)) {
										$now_status = 'realpay';
									}
									if (in_array($order_status, $coldpay_st)) {
										$now_status = 'coldpay';
									}								
										
									if ($now_status) {
												
										$in_sum = $order_amount;
										$in_sum = is_sum($in_sum, 12);
										$err = $data['err'];
										$status = $data['status'];
										$bid_m_id = $data['m_id'];
										$bid_m_script = $data['m_script'];  
											
										$bid_currency = strtoupper($currency_code);									
												
										$pay_purse = is_pay_purse($order_from, $m_data, $bid_m_id);
													
										$bid_sum = is_sum($data['pay_sum'], 12);	
										$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
												
										$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
										$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
										$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
										$invalid_check = intval(is_isset($m_data, 'check'));								
																					
										if($err == 0 and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name){
											if($bid_currency == $order_currency or $invalid_ctype > 0){										
												if($in_sum >= $bid_corr_sum or $invalid_minsum > 0){

													$params = array( 
														'pay_purse' => $pay_purse,
														'trans_in' => $order_uuid,
														'txid_in' => $order_txid,
														'sum' => $in_sum,
														'bid_sum' => $bid_sum,
														'bid_status' => array('new','techpay','coldpay'),
														'bid_corr_sum' => $bid_corr_sum,
														'currency' => $order_currency,
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

												} else {
													$this->logs($id . ' The payment amount is less than the provisions', $m_id);
												}
											} else {
												$this->logs($id.' In the application the wrong status', $m_id);
											}										
										} else {
											$this->logs($id . ' bid error', $m_id);
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
				if($show_error and current_user_can('administrator')){
					die($e->getMessage());
				}
			}				
			
		}	
	}
}

new merchant_heleket(__FILE__, 'Heleket');