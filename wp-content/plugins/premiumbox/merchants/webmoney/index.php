<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Webmoney[:en_US][ru_RU:]Webmoney[:ru_RU]
description: [en_US:]webmoney merchant[:en_US][ru_RU:]мерчант webmoney[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_webmoney')) {
	class merchant_webmoney extends Ext_Merchant_Premiumbox {
		
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
				'WEBMONEY_WMZ_PURSE'  => array(
					'title' => '[en_US:]WMZ wallet number[:en_US][ru_RU:]WMZ кошелек[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WMZ_KEY'  => array(
					'title' => '[en_US:]Secret key Z wallet[:en_US][ru_RU:]Secret Key WMZ кошелька[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WMF_PURSE'  => array(
					'title' => '[en_US:]WMF wallet number[:en_US][ru_RU:]WMF кошелек[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WMF_KEY'  => array(
					'title' => '[en_US:]Secret key WMF wallet[:en_US][ru_RU:]Secret Key WMF кошелька[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WMT_PURSE'  => array(
					'title' => '[en_US:]WMT wallet number[:en_US][ru_RU:]WMT кошелек[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WMT_KEY'  => array(
					'title' => '[en_US:]Secret key WMT wallet[:en_US][ru_RU:]Secret Key WMT кошелька[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),				
				'WEBMONEY_WME_PURSE'  => array(
					'title' => '[en_US:]WME wallet number[:en_US][ru_RU:]WME кошелек[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WME_KEY'  => array(
					'title' => '[en_US:]Secret key WME wallet[:en_US][ru_RU:]Secret Key WME кошелька[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WMB_PURSE'  => array(
					'title' => '[en_US:]WMB wallet number[:en_US][ru_RU:]WMB кошелек[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WMB_KEY'  => array(
					'title' => '[en_US:]Secret key WMB wallet[:en_US][ru_RU:]Secret Key WMB кошелька[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WMG_PURSE'  => array(
					'title' => '[en_US:]WMG wallet number[:en_US][ru_RU:]WMG кошелек[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WMG_KEY'  => array(
					'title' => '[en_US:]Secret key WMG wallet[:en_US][ru_RU:]Secret Key WMG кошелька[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WMX_PURSE'  => array(
					'title' => '[en_US:]WMX wallet number[:en_US][ru_RU:]WMX кошелек[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WMX_KEY'  => array(
					'title' => '[en_US:]Secret key WMX wallet[:en_US][ru_RU:]Secret Key WMX кошелька[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WMK_PURSE'  => array(
					'title' => '[en_US:]WMK wallet number[:en_US][ru_RU:]WMK кошелек[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WMK_KEY'  => array(
					'title' => '[en_US:]Secret key WMK wallet[:en_US][ru_RU:]Secret Key WMK кошелька[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WML_PURSE'  => array(
					'title' => '[en_US:]WML wallet number[:en_US][ru_RU:]WML кошелек[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WML_KEY'  => array(
					'title' => '[en_US:]Secret key WML wallet[:en_US][ru_RU:]Secret Key WML кошелька[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WMH_PURSE'  => array(
					'title' => '[en_US:]WMH wallet number[:en_US][ru_RU:]WMH кошелек[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WEBMONEY_WMH_KEY'  => array(
					'title' => '[en_US:]Secret key WMH wallet[:en_US][ru_RU:]Secret Key WMH кошелька[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),				
			);
			
			return $map;
		}
		
		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('WEBMONEY_WMZ_PURSE', 'WEBMONEY_WMZ_KEY');
			$arrs[] = array('WEBMONEY_WMF_PURSE', 'WEBMONEY_WMF_KEY');
			$arrs[] = array('WEBMONEY_WMT_PURSE', 'WEBMONEY_WMT_KEY');
			$arrs[] = array('WEBMONEY_WME_PURSE', 'WEBMONEY_WME_KEY');
			$arrs[] = array('WEBMONEY_WMB_PURSE', 'WEBMONEY_WMB_KEY');
			$arrs[] = array('WEBMONEY_WMG_PURSE', 'WEBMONEY_WMG_KEY');
			$arrs[] = array('WEBMONEY_WMK_PURSE', 'WEBMONEY_WMK_KEY');
			$arrs[] = array('WEBMONEY_WML_PURSE', 'WEBMONEY_WML_KEY');
			$arrs[] = array('WEBMONEY_WMH_PURSE', 'WEBMONEY_WMH_KEY');
			
			return $arrs;
		}			

		function options($options, $data, $m_id, $place) {
			
			$options = pn_array_unset($options, array('pagenote', 'check_api', 'cronhash'));		
			
			$text = '
			<div><strong>Result URL:</strong> <a href="' . get_mlink($m_id . '_status' . hash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_status' . hash_url($m_id)) . '</a></div>
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
				$currency = str_replace(array('WMZ'), 'USD', $currency);
				$currency = str_replace(array('WME'), 'EUR', $currency);
				$currency = str_replace(array('WMK'), 'KZT', $currency);
				$currency = str_replace(array('WMB'), 'BYN', $currency);
				$currency = str_replace(array('WMG'), 'GLD', $currency);
				$currency = str_replace(array('WMX'), 'BTC', $currency);
				$currency = str_replace(array('WMH'), 'BCH', $currency);
				$currency = str_replace(array('WML'), 'LTC', $currency);
				$currency = str_replace(array('WMF'), 'ETH', $currency);
				$currency = str_replace(array('WMT'), 'USDT', $currency);
						
				$LMI_PAYEE_PURSE = 0;	
				if ('USD' == $currency) {
					$LMI_PAYEE_PURSE = is_isset($m_defin, 'WEBMONEY_WMZ_PURSE');
				} elseif ('ETH' == $currency) {
					$LMI_PAYEE_PURSE = is_isset($m_defin, 'WEBMONEY_WMF_PURSE');
				} elseif ('USDT' == $currency) {
					$LMI_PAYEE_PURSE = is_isset($m_defin, 'WEBMONEY_WMT_PURSE');	
				} elseif ('EUR' == $currency) {
					$LMI_PAYEE_PURSE = is_isset($m_defin, 'WEBMONEY_WME_PURSE');
				} elseif ('BYN' == $currency) {
					$LMI_PAYEE_PURSE = is_isset($m_defin, 'WEBMONEY_WMB_PURSE');	
				} elseif ('GLD' == $currency) {
					$LMI_PAYEE_PURSE = is_isset($m_defin, 'WEBMONEY_WMG_PURSE');
				} elseif ('BTC' == $currency) {
					$LMI_PAYEE_PURSE = is_isset($m_defin, 'WEBMONEY_WMX_PURSE');
				} elseif ('KZT' == $currency) {
					$LMI_PAYEE_PURSE = is_isset($m_defin, 'WEBMONEY_WMK_PURSE');			
				} elseif ('LTC' == $currency) {
					$LMI_PAYEE_PURSE = is_isset($m_defin, 'WEBMONEY_WML_PURSE');
				} elseif ('BCH' == $currency) {
					$LMI_PAYEE_PURSE = is_isset($m_defin, 'WEBMONEY_WMH_PURSE');				
				}		

				$pay_sum = is_sum($pay_sum, 2);		
				$text_pay = get_text_pay($m_id, $bids_data, $pay_sum);
							
				$temp = '
				<form name="MerchantPay" action="https://merchant.webmoney.ru/lmi/payment.asp" method="post" accept-charset="windows-1251">
					<input type="hidden" name="LMI_RESULT_URL" value="' . get_mlink($m_id . '_status' . hash_url($m_id)) . '" />
					<input type="hidden" name="LMI_SUCCESS_URL" value="' . get_mlink($m_id . '_success') . '" />
					<input type="hidden" name="LMI_SUCCESS_METHOD" value="POST" />
					<input type="hidden" name="LMI_FAIL_URL" value="' . get_mlink($m_id . '_fail') . '" />
					<input type="hidden" name="LMI_FAIL_METHOD" value="POST" />			    
					<input name="LMI_PAYMENT_NO" type="hidden" value="' . $bids_data->id . '" />
					<input name="LMI_PAYMENT_AMOUNT" type="hidden" value="' . $pay_sum . '" />
					<input name="LMI_PAYEE_PURSE" type="hidden" value="' . $LMI_PAYEE_PURSE . '" />
					<input name="LMI_PAYMENT_DESC" type="hidden" value="' . $text_pay . '" />
					<input name="sEmail" type="hidden" value="' . is_email($bids_data->user_email) . '" />				

					<input type="submit" value="Pay" />
				</form>			
				';				
			
			}
			return $temp;
		}

		function merchant_fail() {
			
			$id = get_payment_id('LMI_PAYMENT_NO');
			redirect_merchant_action($id, $this->name);
			
		}

		function merchant_success() {	
		
			$id = get_payment_id('LMI_PAYMENT_NO');
			redirect_merchant_action($id, $this->name, 1);	
			
		}

		function merchant_status() {
	
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_secure', $this->name, '', $m_id, $m_defin, $m_data);
	
			$dPaymentAmount = trim(is_param_post('LMI_PAYMENT_AMOUNT'));
			$iPaymentID = trim(is_param_post('LMI_PAYMENT_NO'));
			$bPaymentMode = trim(is_param_post('LMI_MODE'));
			$iPayerWMID = trim(is_param_post('LMI_PAYER_WM'));
			$sPayerPurse = trim(is_param_post('LMI_PAYER_PURSE'));
			$sEmail = trim(is_param_post('sEmail'));

			if (0 != $bPaymentMode) {
				$this->logs('Payments are not permitted in test mode', $m_id);
				die('Payments are not permitted in test mode');
			}

			if (isset($_POST['LMI_PREREQUEST'])) {
				$this->logs('LMI_PREREQUEST', $m_id);
				die( 'YES' );
			}

			$iSysInvsID = trim(is_param_post('LMI_SYS_INVS_NO'));
			$iSysTransID = trim(is_param_post('LMI_SYS_TRANS_NO'));
			$sSignature = trim(is_param_post('LMI_HASH'));
			$sSysTransDate = trim(is_param_post('LMI_SYS_TRANS_DATE'));

			if (!$sPayerPurse) {
				$this->logs('Purse empty', $m_id);
				die('Purse empty');
			}
	
			$constant = is_isset($m_defin, 'WEBMONEY_WM' . substr($sPayerPurse, 0, 1) . '_PURSE');
			$constant2 = is_isset($m_defin, 'WEBMONEY_WM' . substr($sPayerPurse, 0, 1) . '_KEY');
	
			if ($sSignature != strtoupper(hash('sha256', implode('', array($constant, $dPaymentAmount, $iPaymentID, $bPaymentMode, $iSysInvsID, $iSysTransID, $sSysTransDate, $constant2, $sPayerPurse, $iPayerWMID))))) {
				$this->logs('Invalid control signature', $m_id);
				die('Invalid control signature');
			}

			/*
			$iPaymentID - номер заказа
			$dPaymentAmount - сумма платежа
			$iPayerWMID - WMID плательщика
			$sPayerPurse - кошелек плательщика
			$sEmail - E-mail адрес плательщика
			$iSysInvsID - уникальный номер счета
			$iSysTransID - уникальный номер транзакции
			*/
	
			if (check_trans_in($m_id, $iSysTransID, $iPaymentID)) {
				$this->logs($iPaymentID . ' Error check trans in!', $m_id);
				die('Error check trans in!');
			}	
	
			$id = $iPaymentID;
			$data = get_data_merchant_for_id($id);
			
			$in_sum = $dPaymentAmount;	
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
				die('wrong script');
			}			
			
			if ($bid_m_id and $m_id != $bid_m_id or !$bid_m_id) {
				$this->logs($id.' not a faithful merchant', $m_id);
				die('not a faithful merchant');				
			}	
			
			$pay_purse = is_pay_purse($sPayerPurse, $m_data, $bid_m_id);
			
			$bid_currency = $data['currency'];
			
			$bid_currency = str_replace(array('WMZ', 'USD'), 'Z', $bid_currency);
			$bid_currency = str_replace(array('WMF', 'ETH'), 'F', $bid_currency);
			$bid_currency = str_replace(array('WMT', 'USDT'), 'T', $bid_currency);
			$bid_currency = str_replace(array('WME', 'EUR'), 'E', $bid_currency);
			$bid_currency = str_replace(array('WMB', 'BYN'), 'B', $bid_currency);
			$bid_currency = str_replace(array('WMG', 'GLD'), 'G', $bid_currency);
			$bid_currency = str_replace(array('WMX', 'BTC'), 'X', $bid_currency);
			$bid_currency = str_replace(array('WMK', 'KZT'), 'K', $bid_currency);
			$bid_currency = str_replace(array('WML', 'LTC'), 'L', $bid_currency);
			$bid_currency = str_replace(array('WMH', 'BCH'), 'H', $bid_currency);	
	
			$bid_sum = is_sum($data['pay_sum'], 2);
			$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
	
			$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
			$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
			$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
			$invalid_check = intval(is_isset($m_data, 'check'));	
	
			$fl = substr($sPayerPurse, 0, 1); 
	
			$workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay')); 
			if (in_array($bid_status, $workstatus)) { 
				if ($bid_currency == $fl or $invalid_ctype > 0) {
					if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {		
					
						$params = array(
							'pay_purse' => $pay_purse,
							'sum' => $in_sum,
							'bid_sum' => $bid_sum,
							'bid_corr_sum' => $bid_corr_sum,
							'bid_status' => $workstatus,
							'to_account' => $constant,
							'trans_in' => $iSysTransID,
							'currency' => $fl,
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
											 
						die('Completed');
								
					} else {
						$this->logs($id.' In the application the wrong status', $m_id);
						die('The payment amount is less than the provisions');
					}
				} else {
					$this->logs($id.' In the application the wrong status', $m_id);
					die('Wrong type of currency');
				}
			} else {
				$this->logs($id.' In the application the wrong status', $m_id);
				die('In the application the wrong status');
			}
		}		
	}
}

new merchant_webmoney(__FILE__, 'Webmoney');