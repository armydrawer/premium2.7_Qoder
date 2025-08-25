<?php
/*
title: [en_US:]PerfectMoney[:en_US][ru_RU:]PerfectMoney[:ru_RU]
description: [en_US:]Checking account for verification[:en_US][ru_RU:]Проверка кошелька на верификацию[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Wchecks_Premiumbox')) { return; }

if(!class_exists('wchecks_perfectmoney')) {
	class wchecks_perfectmoney extends Ext_Wchecks_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);					
		}

		function set_check_account($ind, $purse, $check_id) {
			
			$ext_plugin = get_ext_plugin($check_id, 'wchecks');
			if (0 == $ind and $check_id and $ext_plugin == $this->name) {
				$result = $this->check_purse($purse);
				if (isset($result['type'])) {
					if ('verified' == $result['type']) {
						return 1;
					}
				} 	
			}
			
			return $ind;
		} 		
		
		function check_purse($purse, $r = 1) {
			global $premiumbox;	
			
			$purse = strtoupper($purse);
			$fz = mb_substr($purse, 0, 1);
			if ('G' == $fz) {
				$currency = 'OAU';
			} elseif ('E' == $fz) {
				$currency = 'EUR';
			} elseif ('B' == $fz) {
				$currency = 'BTC';				
			} else {
				$currency = 'USD';
			}			
			
			$perfetcmoney_domain = apply_filters('perfetcmoney_domain', 'perfectmoney.is');

			$set_cookie='details=1920x1200;Path=/;Domain=' . $perfetcmoney_domain . ';expires=Mon, 13-Oct-24 13:08:16 GMT';
			
			$path = $premiumbox->upload_dir . '/'; 			
			
			$c_options = array(
				CURLOPT_COOKIEFILE => $path . 'pmcheck_cookie.txt',
				CURLOPT_COOKIEJAR => $path . 'pmcheck_cookie.txt',
				CURLOPT_COOKIE => $set_cookie,
				CURLOPT_FAILONERROR => 1,
				CURLOPT_FOLLOWLOCATION => 0,
				CURLOPT_HEADER => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_CONNECTTIMEOUT => 20,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => "PAYEE_ACCOUNT=" . $purse . "&PAYEE_NAME=check_pm&PAYMENT_AMOUNT=0.1&PAYMENT_UNITS=" . $currency . "&PAYMENT_ID=1&PAYMENT_URL=http://check_pm.ru&NOPAYMENT_URL=http://check_pm.ru",
			);
			
			$result = get_curl_parser('https://' . $perfetcmoney_domain . '/api/step1.asp', $c_options, 'wchecks', 'perfectmoney');
			$out = $result['output'];
			if (mb_strpos($out, 'Account type:')) {
				$out = mb_substr($out, mb_strpos($out, 'Account type:'), mb_strlen($out));
				$out = mb_substr($out, mb_strpos($out, '<font'), mb_strlen($out));
				$out = mb_substr($out, 0, mb_strpos($out,' Trust Score'));
				$out = strip_tags($out);
				$out = explode(',', $out);
				return array("type" => strtolower($out[0]), "TS" => (float)$out[1]);
			} else {
				if ($r > 5) {
					return array("error" => "not account type");
				} else {
					return $this->check_purse($purse, ($r + 1));
				}
			}

			return array();
		}
	}
}

new wchecks_perfectmoney(__FILE__, 'Perfectmoney');