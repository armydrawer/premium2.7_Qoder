<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Bitconce Link[:en_US][ru_RU:]Bitconce Link[:ru_RU]
description: [en_US:]Bitconce merchant (payment page)[:en_US][ru_RU:]Мерчант Bitconce (переход к оплате)[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_bitconcelink')) {
	class merchant_bitconcelink extends Ext_Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
			
			add_filter('sum_to_pay', array($this, 'sum_to_pay'), 100, 3);
		}

		function get_map() {
			
			$map = array(
				'TOKEN'  => array(
					'title' => 'Token',
					'view' => 'input',
					'hidden' => 1,
				),				
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('TOKEN');
			
			return $arrs;
		}
		
		function bank_titles() {
			
			$arr = array(
				'0' => __('All', 'pn'),
				'1' => 'sberbank',
				'2' => 'otkritie',
				'3' => 'tinkoff',
				'4' => 'raiffeisenBank',
				'5' => 'alfa',
				'6' => 'vtb',
				'7' => 'visa',
				'8' => 'mastercard',
				'9' => 'maestro',
				'10' => 'mir',
				'11' => 'mkb',
				'12' => 'sovcombank',
				'13' => 'gazprombank',
				'14' => 'psb',
			);
			
			return $arr;
		}		

		function options($options, $data, $id, $place) {  

			$options = pn_array_unset($options, array('pagenote', 'note', 'check_api', 'enableip', 'invalid_ctype', 'check', 'resulturl', 'help_resulturl', 'workstatus'));

			$options['bank_name'] = array(
				'view' => 'select',
				'title' => __('Bank', 'pn'),
				'options' => $this->bank_titles(),
				'default' => is_isset($data, 'bank_name'),
				'name' => 'bank_name',
				'work' => 'int',
			);
			
			$options['bank_sbp'] = array(
				'view' => 'select',
				'title' => __('SBP', 'pn'),
				'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
				'default' => is_isset($data, 'bank_sbp'),
				'name' => 'bank_sbp',
				'work' => 'int',
			);			

			$options['bank_name_other'] = array(
				'view' => 'select',
				'title' => __('Issue a card of another bank if there is no one assigned', 'pn'),
				'options' => array('0' =>__('No', 'pn'), '1' =>__('Yes', 'pn')),
				'default' => is_isset($data, 'bank_name_other'),
				'name' => 'bank_name_other',
				'work' => 'int',
			);

			$text = '
			<div><strong>Cron:</strong> <a href="' . get_mlink($id . '_cron' . chash_url($id)) . '" target="_blank">' . get_mlink($id . '_cron' . chash_url($id)) . '</a></div>			
			';		
			
			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);				
			
			return $options;	
		}				

		function merch_type($m_id) {
			
			return 'link'; 
		}
		
		function sum_to_pay($sum, $m_in, $direction) {
			
			$script = get_mscript($m_in);
			if($script and $script == $this->name){
				return is_sum($sum, 0, 'down');
			}
			
			return $sum;
		}		

		function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
			global $bids_data;	
			
			$bank_name_option = intval(is_isset($m_data, 'bank_name'));
			$bank_name_other = intval(is_isset($m_data, 'bank_name_other'));
			$bank_sbp = intval(is_isset($m_data, 'bank_sbp'));
			
			$pay_link = $this->get_pay_link($bids_data->id);
			if (!$pay_link) {	
			
				$banks = $this->bank_titles();
				$bank_name = '';
				if ($bank_name_option > 0 and isset($banks[$bank_name_option])) {
					$bank_name = $banks[$bank_name_option];
				} 
				
				$sbp = 0;
				if (in_array($bank_name_option, array('0', '1', '3'))) {
					$sbp = $bank_sbp;
				}				
				
				$pay_sum = is_sum($pay_sum, 2); 
				$currency = mb_strtoupper($bids_data->currency_code_give);
				$currency = str_replace('RUR', 'RUB', $currency);
							
				$client_email = $bids_data->user_email;
				$client_ip = $bids_data->user_ip;
				$success_url = get_bids_url($bids_data->hashed);
					
				$class = new Bitconce($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
				$res = $class->create_order($pay_sum, $bids_data->account_give, $bank_name, $sbp, $client_email, $client_ip, $success_url, $bank_name_other);
				if (isset($res['data']) and isset($res['data']['id'], $res['data']['requisites'], $res['data']['status'])) {
					$order_status = strtolower($res['data']['status']);
					$order_id = intval($res['data']['id']);
					$order_link = pn_strip_input($res['data']['requisites']);
					if ('seller requisite' == $order_status and $order_id and $order_link) {
								
						$pay_link = pn_strip_input($order_link);
						$this->update_pay_link($bids_data->id, $pay_link);
								
						$arr = array();
						$arr['trans_in'] = pn_strip_input($order_id);
						$bids_data = update_bid_tb_array($bids_data->id, $arr, $bids_data);
								
					}
				} 		
						
			}
			
			if ($pay_link) {
				return 1;
			}
			
			return 0;			
		}	

		function cron($m_id, $m_defin, $m_data) {
			global $wpdb;

			$show_error = intval(is_isset($m_data, 'show_error'));	
			
			try {
				$class = new Bitconce($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
				$orders = $class->get_orders(200);

				if (is_array($orders)) {
					$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN ('new','payed') AND m_in = '$m_id'");
					foreach ($items as $item) {
						$id = $item->id;
						$to_account = pn_maxf_mb(pn_strip_input(is_isset($item, 'to_account')), 500);
						$trans_in = intval(is_isset($item, 'trans_in'));
						if (isset($orders[$trans_in])) {
							$order = $orders[$trans_in];
							$order_status = strtolower(pn_strip_input(is_isset($order, 'status')));
							$now_status = '';
							if ('finished' == $order_status) {
								$now_status = 'realpay';
							} elseif ('paid' == $order_status) {
								$now_status = 'payed';
							}
							if ($now_status) {
							
								$data = get_data_merchant_for_id($id, $item);
								$in_sum = is_isset($order, 'fiat_amount');
								$in_sum = is_sum($in_sum, 0, 'down');
								$err = $data['err'];
								$status = $data['status'];
								$bid_m_id = $data['m_id'];
								$bid_m_script = $data['m_script']; 
										
								$bid_currency = $data['currency'];
										
								$pay_purse = is_pay_purse('', $m_data, $m_id);
											
								$bid_sum = is_sum($data['pay_sum'], 0, 'down');	
								$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $m_id);
								$bid_corr_sum = is_sum($bid_corr_sum, 0, 'down');
										
								$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
								$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
								$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
								$invalid_check = intval(is_isset($m_data, 'check'));								
								
								if (0 == $err and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name) {
									if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {
										
										$params = array( 
											'pay_purse' => $pay_purse,
											'sum' => $in_sum,
											'bid_sum' => $bid_sum,
											'bid_status' => array('new', 'payed'),
											'bid_corr_sum' => $bid_corr_sum,
											'invalid_ctype' => $invalid_ctype,
											'invalid_minsum' => $invalid_minsum,
											'invalid_maxsum' => $invalid_maxsum,
											'invalid_check' => $invalid_check,
											'm_place' => $m_id,
											'm_id' => $m_id,
											'm_data' => $m_data,
											'm_defin' => $m_defin,
										);
										set_bid_status($now_status, $id, $params, $data['direction_data']);
										
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
				if ($show_error and current_user_can('administrator')) {
					die($e->getMessage());
				}
			}			
		}
	}
}

new merchant_bitconcelink(__FILE__, 'Bitconce Link');