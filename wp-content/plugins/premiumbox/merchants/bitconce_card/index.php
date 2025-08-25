<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Bitconce card[:en_US][ru_RU:]Bitconce карты[:ru_RU]
description: [en_US:]Bitconce card[:en_US][ru_RU:]Bitconce карты[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_bitconcecard')) {
	class merchant_bitconcecard extends Ext_Merchant_Premiumbox { 

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
				'20' => 'kaspi',
				'21' => 'jusan',
				'22' => 'eurasian',
				'23' => 'halyk',
				'24' => 'forte',
				'25' => 'bereke',
				'26' => 'centralcredit',
				'27' => 'simply',
				'28' => 'rbk',
				'29' => 'altyn',
				'30' => 'uzcard',
				'31' => 'humo',				
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
				'options' => array('0' =>__('No', 'pn'), '1' => __('Yes', 'pn')),
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
			
			return 'mypaid'; 
		}
		
		function sum_to_pay($sum, $m_in, $direction) {
			
			$script = get_mscript($m_in);
			if ($script and $script == $this->name) {
				return is_sum($sum, 0, 'down');
			}
			
			return $sum;
		}

		function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
			global $bids_data;
			
			$bank_name_option = intval(is_isset($m_data, 'bank_name'));
			$bank_name_other = intval(is_isset($m_data, 'bank_name_other'));
			$bank_sbp = intval(is_isset($m_data, 'bank_sbp'));
				
			$banks = $this->bank_titles();
			$bank_name = '';
			if ($bank_name_option > 0 and isset($banks[$bank_name_option])) {
				$bank_name = $banks[$bank_name_option];
			} 
				
			$sbp = 0;
			if (in_array($bank_name_option, array('0', '1', '3'))) {
				$sbp = $bank_sbp;
			}				
				
			if (!$bids_data->trans_in and !$bids_data->to_account) {
					
				$arr = array();
				
				$client_email = $bids_data->user_email;
				$client_ip = $bids_data->user_ip;
					
				$class = new Bitconce($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
				$res = $class->create_order($pay_sum, $bids_data->account_give, $bank_name, $sbp, $client_email, $client_ip, '', $bank_name_other);

				if (isset($res['data']) and isset($res['data']['id'], $res['data']['requisites'], $res['data']['status'])) {
					$order_status = strtolower($res['data']['status']);
					$order_id = intval($res['data']['id']);
					$order_card = pn_strip_input($res['data']['requisites']);
					$order_sbp = pn_strip_input(is_isset($res['data'], 'sbp_number'));
					if ($order_sbp) {
						$order_card = $order_sbp;
					}
					$order_owner = pn_strip_input(is_isset($res['data'], 'owner'));
					$order_bank = pn_strip_input(is_isset($res['data'], 'bankname'));
					if ('seller requisite' == $order_status and $order_id and $order_card) {
						$arr['to_account'] = $order_card;
						$arr['dest_tag'] = $order_owner . ' ' . $order_bank;
						$arr['trans_in'] = $order_id;
						$bids_data = update_bid_tb_array($bids_data->id, $arr, $bids_data);
					}
				} 
			}	
			
			if ($bids_data->to_account) {
				return 1;
			}
			
			return 0;			
		}		
		
		function myaction($m_id, $pay_sum, $direction) {
			global $bids_data;	
			
			$script = get_mscript($m_id);
			if ($script and $script == $this->name) {
				$m_defin = $this->get_file_data($m_id);
				if ($bids_data->trans_in) {
					$order_id = $bids_data->trans_in;
					$m_data = get_merch_data($m_id);
					$class = new Bitconce($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
					$o_id = $class->paid_order($order_id);
					if ($o_id == $order_id) {	

						$st = get_status_sett('merch', 1);
						$params = array( 
							'bid_status' => $st,
							'm_place' => $m_id,
							'm_id' => $m_id,
							'm_data' => $m_data,
							'm_defin' => $m_defin,
						);
						set_bid_status('payed', $bids_data->id, $params, $direction); 
									
						$this->cron($m_id, $m_defin, $m_data);
						
					} 
				}
			}
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

new merchant_bitconcecard(__FILE__, 'Bitconce Card');