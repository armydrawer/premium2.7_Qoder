<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Paypal[:en_US][ru_RU:]Paypal[:ru_RU]
description: [en_US:]Paypal merchant[:en_US][ru_RU:]мерчант Paypal[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_paypal')) {
	class merchant_paypal extends Ext_Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
		
			$ids = $this->get_ids('merchants', $this->name);
			foreach ($ids as $id) {
				add_action('premium_merchant_' . $id . '_status', array($this, 'merchant_status'));
				add_action('premium_merchant_' . $id . '_fail', array($this, 'merchant_fail'));
				add_action('premium_merchant_' . $id . '_success', array($this, 'merchant_success'));
			}
			
		}	
		
		function get_map() {
			
			$map = array(
				'PAYPAL_BUSINESS_ACCOUNT'  => array(
					'title' => '[en_US:]Business account login[:en_US][ru_RU:]Логин безнес аккаунта[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),				
			);
			
			return $map;
		}
		
		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('PAYPAL_BUSINESS_ACCOUNT');
			
			return $arrs;
		}		

		function options($options, $data, $m_id, $place) { 
			
			$options = pn_array_unset($options, array('pagenote', 'resulturl', 'check_api', 'cronhash'));		
			
			$text = '
			<div><strong>Status URL:</strong> <a href="' . get_mlink($m_id . '_status') . '" target="_blank">' . get_mlink($m_id . '_status') . '</a></div>
			<div><strong>Success URL:</strong> <a href="' . get_mlink($m_id . '_success') . '" target="_blank">' . get_mlink($m_id . '_success') . '</a></div>
			<div><strong>Fail URL:</strong> <a href="' . get_mlink($m_id . '_fail') . '" target="_blank">' . get_mlink($m_id . '_fail') . '</a></div>			
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
						
				$temp = '
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_xclick" />
					<input type="hidden" name="notify_url" value="' . get_mlink($m_id . '_status') . '" />
					<input type="hidden" name="currency_code" value="' . $currency . '" />
					<input type="hidden" name="business" value="' . is_isset($m_defin, 'PAYPAL_BUSINESS_ACCOUNT') . '" />
					<input type="hidden" name="return" value="' . get_mlink($m_id . '_success') . '" />
					<input type="hidden" name="rm" value="0" />
					<input type="hidden" name="cancel_return" value="' . get_mlink($m_id . '_fail') . '" />
					<input type="hidden" name="charset" value="UTF-8" />
					<input type="hidden" name="item_number" value="' . $bids_data->id . '" />
					<input type="hidden" name="item_name" value="' . $text_pay . '" />
					<input type="hidden" name="amount" value="' . $pay_sum . '" />
					<input type="submit" value="' . __('Make a payment', 'pn') . '" />
				</form>													
				';				
		
			}
			
			return $temp;				
		}

		function merchant_fail() {
			
			$id = get_payment_id('item_number');
			redirect_merchant_action($id, $this->name);
			
		}

		function merchant_success() {
			
			$id = get_payment_id('item_number');
			redirect_merchant_action($id, $this->name, 1);
			
		}

		function merchant_status() {
			
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_secure', $this->name, '', $m_id, $m_defin, $m_data);

			if (isset($_POST["ipn_track_id"], $_POST["item_number"], $_POST["mc_gross"]) and is_numeric($_POST["mc_gross"]) and $_POST["mc_gross"] > 0) {

				$aResponse = array();

				foreach ($_POST as $sKey => $sValue) {
					if (get_magic_quotes_gpc()) {
						$sKey = stripslashes($sKey);
						$sValue = stripslashes($sValue);
					}

					$aResponse[] = $sKey . "=" . $sValue;
					$aResponseUrl[] = $sKey . "=" . urlencode($sValue);
				}

				$json_data = "cmd=_notify-validate&" . implode("&", $aResponseUrl);

				$sResponse = '';

				if ($ch = curl_init()) {
					
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
					curl_setopt($ch, CURLOPT_URL, "https://www.paypal.com/cgi-bin/webscr");
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
					// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
					curl_setopt($ch, CURLOPT_HEADER, false);
					curl_setopt($ch, CURLOPT_TIMEOUT, 20);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
					$ch = apply_filters('curl_merch', $ch, $this->name, $m_id);
					
					$err  = curl_errno($ch);
					$result = curl_exec($ch);
					
					curl_close($ch);
					
					do_action('save_merchant_error', $this->name, $m_id, "https://www.paypal.com/cgi-bin/webscr", '', $json_data, $result, $err);
					
					$sResponse = $result;			 
				}

				if ("VERIFIED" == $sResponse) {
					
					if (check_trans_in($m_id, $_POST["ipn_track_id"], $_POST["item_number"])) {
						$this->logs('Error ipn_track_id!', $m_id);
						die('Error ipn_track_id!');
					}					
					
					$id = $_POST["item_number"];
					$data = get_data_merchant_for_id($id);
					
					$in_sum = $_POST["mc_gross"];
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
						$this->logs('wrong script', $m_id);
						die('wrong script');
					}			
					
					if ($bid_m_id and $m_id != $bid_m_id or !$bid_m_id) {
						$this->logs('not a faithful merchant', $m_id);
						die('not a faithful merchant');				
					}
					
					$currency = $_POST["mc_currency"];
					
					$payer_purse = $_POST["payer_email"];					
					$pay_purse = is_pay_purse($payer_purse, $m_data, $bid_m_id);
					
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
									'to_account' => is_isset($m_defin, 'PAYPAL_BUSINESS_ACCOUNT'),
									'trans_in' => $_POST["ipn_track_id"],
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
													 
								die( 'Completed' );
										
							} else {
								$this->logs('The payment amount is less than the provisions', $m_id);
								die('The payment amount is less than the provisions');
							}
						} else {
							$this->logs('Wrong type of currency', $m_id);
							die('Wrong type of currency');
						}
					} else {
						$this->logs('In the application the wrong status', $m_id);
						die('In the application the wrong status');
					}		
				} 
			} else {
				$this->logs('no data', $m_id);
			}				
		}
	}
}

new merchant_paypal(__FILE__, 'Paypal');