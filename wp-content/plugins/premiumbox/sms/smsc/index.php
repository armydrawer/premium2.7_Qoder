<?php
/*
title: [en_US:]SMSC[:en_US][ru_RU:]SMSC[:ru_RU]
description: [en_US:]SMSC[:en_US][ru_RU:]SMSC[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_SmsGate_Premiumbox')) { return; }

if (!class_exists('smsgate_smsc')) {
	class smsgate_smsc extends Ext_SmsGate_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);
		}

		function get_map() {
			
			$map = array(
				'SMSC_NAME'  => array(
					'title' => '[en_US:]Smsc.ru login[:en_US][ru_RU:]Логин от аккаунта Smsc.ru[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'SMSC_PASS'  => array(
					'title' => '[en_US:]Smsc.ru password[:en_US][ru_RU:]Пароль от аккаунта Smsc.ru[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'SMSC_SENDER'  => array(
					'title' => '[en_US:]SMS sender name[:en_US][ru_RU:]Имя отправителя SMS[:ru_RU]',
					'view' => 'input',
				),
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('SMSC_NAME', 'SMSC_PASS', 'SMSC_SENDER');
			
			return $arrs;
		}

		function send($data, $text, $phone = '') {
			
			$ind = 0;
			if (is_isset($data, 'SMSC_NAME') and is_isset($data, 'SMSC_PASS') and is_isset($data, 'SMSC_SENDER')) {
				
				$text = trim($text);
				$text = iconv('UTF-8', 'CP1251', $text);
					
				$phone = trim($phone);
				$phones = explode(',', $phone);
				foreach ($phones as $phone_no) {
					$phone_no = trim($phone_no);
					if ($phone_no) {
						$c_options = array(
							CURLOPT_FOLLOWLOCATION => false,
							CURLOPT_POST => true,
							CURLOPT_HEADER => false,
							CURLOPT_CONNECTTIMEOUT => 20,
							CURLOPT_TIMEOUT => 20,
							CURLOPT_POSTFIELDS => array(
								'login' => is_isset($data, 'SMSC_NAME'),
								'psw' => is_isset($data, 'SMSC_PASS'),
								'sender' => is_isset($data, 'SMSC_SENDER'),
								'phones' => $phone_no,
								'mes' => $text
							),   
						);
						$result = get_curl_parser('http://smsc.ru/sys/send.php', $c_options, 'smsgate', 'smsc');
						$ind = 1;
					}
				}
			}
			
			return $ind;
		}
	}
}

new smsgate_smsc(__FILE__, 'SMSC');