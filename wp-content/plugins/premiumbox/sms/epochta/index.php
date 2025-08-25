<?php
/*
title: [en_US:]E-pochta[:en_US][ru_RU:]E-pochta[:ru_RU]
description: [en_US:]E-pochta[:en_US][ru_RU:]E-pochta[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_SmsGate_Premiumbox')) { return; }

if (!class_exists('smsgate_epochta')) {
	class smsgate_epochta extends Ext_SmsGate_Premiumbox {
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title);
		}
 
		function get_map() {
			
			$map = array(
				'EPOCHTA_NAME' => array(
					'title' => '[en_US:]Epochta login[:en_US][ru_RU:]Логин от аккантуа Epochta[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'EPOCHTA_PASS' => array(
					'title' => '[en_US:]Epochta password[:en_US][ru_RU:]Пароль от аккантуа Epochta[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'EPOCHTA_SENDER' => array(
					'title' => '[en_US:]SMS sender name[:en_US][ru_RU:]Имя отправителя SMS[:ru_RU]',
					'view' => 'input',
				),
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('EPOCHTA_NAME', 'EPOCHTA_PASS', 'EPOCHTA_SENDER');
			
			return $arrs;
		}

		function send($data, $text, $phone = '') {
			
			$ind = 0;
			if (is_isset($data, 'EPOCHTA_NAME') and is_isset($data, 'EPOCHTA_PASS') and is_isset($data, 'EPOCHTA_SENDER')) {
				$text = trim($text);
				$phone = trim($phone);
				$phones = explode(',', $phone);
				foreach ($phones as $phone_no) {
					$phone_no = trim($phone_no);
					if ($phone_no) {
								
$src = '<?xml version="1.0" encoding="UTF-8"?>   
<SMS> 
<operations>  
<operation>SEND</operation> 
</operations> 
<authentification>    
<username>' . is_isset($data, 'EPOCHTA_NAME') . '</username>   
<password>' . is_isset($data, 'EPOCHTA_PASS') . '</password>   
</authentification>   
<message> 
<sender>' . is_isset($data, 'EPOCHTA_SENDER') . '</sender>    
<text>' . $text . '</text>   
</message>    
<numbers> 
<number>' . $phone_no . '</number>   
</numbers>    
</SMS>';   								
								
						$c_options = array(
							CURLOPT_POST => true,  
							CURLOPT_HEADER => false,
							CURLOPT_CONNECTTIMEOUT => 20,
							CURLOPT_TIMEOUT => 20,
							CURLOPT_POSTFIELDS => array('XML' => $src), 
						);			
						$result = get_curl_parser('http://atompark.com/members/sms/xml.php', $c_options, 'smsgate', 'epochta'); 
						$ind = 1;
						
					}
				}
			}	
			
			return $ind;
		}
	}
}

new smsgate_epochta(__FILE__, 'E-pochta');