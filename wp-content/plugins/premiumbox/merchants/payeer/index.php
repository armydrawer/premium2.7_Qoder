<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Payeer[:en_US][ru_RU:]Payeer[:ru_RU]
description: [en_US:]Payeer merchant[:en_US][ru_RU:]мерчант Payeer[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_payeer')) {
	class merchant_payeer extends Ext_Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach ($ids as $id) {
				add_action('premium_merchant_' . $id . '_status' . hash_url($id), array($this, 'merchant_status'));
				add_action('premium_merchant_' . $id . '_fail', array($this, 'merchant_fail'));
				add_action('premium_merchant_' . $id . '_success', array($this, 'merchant_success'));
			}
			
		}

		function get_map() {
			
			$map = array(
				'PAYEER_SEKRET_KEY'  => array(
					'title' => '[en_US:]Secret key[:en_US][ru_RU:]Секретный ключ[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'PAYEER_SHOP_ID'  => array(
					'title' => '[en_US:]Merchant ID[:en_US][ru_RU:]ID магазина[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),				
			);
			
			return $map;
		}
		
		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('PAYEER_SHOP_ID', 'PAYEER_SEKRET_KEY');
			
			return $arrs;
		}			

		function options($options, $data, $m_id, $place) { 
			
			$options = pn_array_unset($options, array('pagenote', 'check_api', 'cronhash'));		
			
			$text = '
			<div><strong>RETURN URL:</strong> <a href="' . get_mlink($m_id . '_status' . hash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_status' . hash_url($m_id)) . '</a></div>
			<div><strong>SUCCESS URL:</strong> <a href="' . get_mlink($m_id . '_success') . '" target="_blank">' . get_mlink($m_id . '_success') . '</a></div>
			<div><strong>FAIL URL:</strong> <a href="' . get_mlink($m_id . '_fail') . '" target="_blank">' . get_mlink($m_id . '_fail') . '</a></div>		
			';

			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);									
			
			return $options;	
		}						

		function bidform($temp, $m_id, $pay_sum, $direction) {
			global $bids_data;
			
			$script = get_mscript($m_id);
			if ($script and $script == $this->name) {
				
				$m_defin = $this->get_file_data($m_id);

				$currency = pn_strip_input($bids_data->currency_code_give);
				$pay_sum = is_sum($pay_sum, 2);	
					
				$text_pay = get_text_pay($m_id, $bids_data, $pay_sum);
						
				$m_desc = base64_encode($text_pay);
				$m_amount = number_format($pay_sum, 2, '.', '');
				$arHash = array(
					is_isset($m_defin, 'PAYEER_SHOP_ID'),
					$bids_data->id,
					$m_amount,
					$currency,
					$m_desc,
					is_isset($m_defin, 'PAYEER_SEKRET_KEY')
				);
				$sign = strtoupper(hash('sha256', implode(":", $arHash)));
						
				$temp = '
				<form method="GET" action="//payeer.com/api/merchant/m.php" target="_blank">
					<input type="hidden" name="m_shop" value="' . is_isset($m_defin, 'PAYEER_SHOP_ID') . '">
					<input type="hidden" name="m_orderid" value="' . $bids_data->id . '">
					<input type="hidden" name="m_amount" value="' . $pay_sum . '">
					<input type="hidden" name="m_curr" value="' . $currency . '">
					<input type="hidden" name="m_desc" value="' . $m_desc . '">
					<input type="hidden" name="m_sign" value="' . $sign . '">
					<input type="submit" value="' . __('Make a payment', 'pn') . '" />
				</form>												
				';
			
			}
			return $temp;		
		}

		function merchant_fail() {
			
			$id = get_payment_id('m_orderid');
			redirect_merchant_action($id, $this->name);
			
		}

		function merchant_success() {
			
			$id = get_payment_id('m_orderid');
			redirect_merchant_action($id, $this->name, 1);
			
		}

		function merchant_status() {
	
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_secure', $this->name, '', $m_id, $m_defin, $m_data);
	
			if (isset($_POST["m_operation_id"]) && isset($_POST["m_sign"])) {

				$m_key = is_isset($m_defin, 'PAYEER_SEKRET_KEY');
				$arHash = array($_POST['m_operation_id'],
						$_POST['m_operation_ps'],
						$_POST['m_operation_date'],
						$_POST['m_operation_pay_date'],
						$_POST['m_shop'],
						$_POST['m_orderid'],
						$_POST['m_amount'],
						$_POST['m_curr'],
						$_POST['m_desc'],
						$_POST['m_status'],
						$m_key);
						
				$sign_hash = strtoupper(hash('sha256', implode(":", $arHash)));
				if ($_POST["m_sign"] == $sign_hash && "success" == $_POST['m_status']) { 			

					$trans_id = intval(is_param_post('transfer_id'));

					if (check_trans_in($m_id, $trans_id, $_POST['m_orderid'])) {
						$this->logs('Error check trans in!', $m_id);
						echo $_POST['m_orderid'] . "|error";
						exit;
					}				
				
					$currency = $_POST['m_curr'];
				
					$id = $_POST['m_orderid'];
					$data = get_data_merchant_for_id($id);
					
					$in_sum = $_POST['m_amount'];
					$in_sum = is_sum($in_sum, 2);
					$bid_err = $data['err'];
					$bid_status = $data['status'];
					$bid_m_id = $data['m_id'];
					$bid_m_script = $data['m_script'];
			
					if ($bid_err > 0) {
						$this->logs($id . ' The application does not exist or the wrong ID', $m_id);
						die('The application does not exist or the wrong ID');
					}			
			
					if ($bid_m_script and $bid_m_script != $this->name or !$bid_m_script) {	
						$this->logs($id . ' wrong script', $m_id);
						echo $_POST['m_orderid'] . "|error";
						exit;
					}			
					
					if ($bid_m_id and $m_id != $bid_m_id or !$bid_m_id) {
						$this->logs($id.' not a faithful merchant', $m_id);
						echo $_POST['m_orderid'] . "|error";
						exit;				
					}
					
					$pay_purse = is_pay_purse($_POST['client_account'], $m_data, $bid_m_id);
					
					$bid_currency = $data['currency'];
					
					$bid_sum = is_sum($data['pay_sum'], 2);
					$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
					
					$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
					$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
					$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
					$invalid_check = intval(is_isset($m_data, 'check'));					
					
					$workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay')); 
					if (in_array($bid_status, $workstatus)) {  
						if ($bid_currency == $currency or $invalid_ctype > 0) {
							if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {	
							
								$params = array(
									'pay_purse' => $pay_purse,
									'sum' => $in_sum,
									'bid_sum' => $bid_sum,
									'bid_corr_sum' => $bid_corr_sum,
									'bid_status' => $workstatus,
									'to_account' => is_isset($m_defin, 'PAYEER_SHOP_ID'),
									'trans_in' => $trans_id,
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
								set_bid_status('realpay', $id, $params, $data['direction_data']);	  						
										 
								echo $_POST['m_orderid'] . "|success";
								exit;
								
							} else {
								$this->logs($id . ' The payment amount is less than the provisions', $m_id);
							} 
						} else {
							$this->logs($id . ' Wrong type of currency', $m_id);
						}  
					} else {
						$this->logs($id . ' In the application the wrong status', $m_id);
					}
				} else {
					$this->logs('bad sign or not success', $m_id);
				}
				
				echo $_POST['m_orderid'] . "|error";
				exit;
			} else {
				$this->logs('no data', $m_id);
			}				
		}
	}
}

new merchant_payeer(__FILE__, 'Payeer');