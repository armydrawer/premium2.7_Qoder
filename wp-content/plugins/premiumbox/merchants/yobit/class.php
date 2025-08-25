<?php

/*
https://yobit.net/ru/api/
*/

if (!class_exists('Yobit')) {
	class Yobit {
		
		private $m_name = '';
		private $m_id = '';
		private $api_key = '';
		private $api_secret = '';
		private $host = 'https://yobit.net/tapipe/';

		function __construct($m_name, $m_id, $api_key, $api_secret) {
			
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->api_key = trim($api_key);	
			$this->api_secret = trim($api_secret);
			
		}		
		
		function get_address_info($currency_code, $address) {
			
			$data = array();
			$data['coinName'] = $currency_code;
			$data['address'] = $address;
			$res = $this->request('GetDepositsInfo', $data);
			if (isset($res['success'], $res['return'], $res['return']['transactions']) and is_array($res['return']['transactions']) and $res['success']) {
				return $res['return']['transactions'];
			}
			
			return array();
		}		
		
		function get_address($currency_code) {
			
			$currency_code = trim($currency_code);
			$data = array(
				'coinName' => $currency_code,
				'need_new' => 1,
			);		
			$res = $this->request('GetDepositAddress', $data);
			if (isset($res['success'], $res['return']) and $res['success']) {
				return $res['return'];
			}
			
			return array();		
		}		
		
		function request($method, $data = array(), $url = '') {

			$url = trim($url);
			if (!is_array($data)) { $data = array(); }
			
			if ($url) {
				$headers = array();
				$host = $url;
				$json_data = '';
			} else {
				$data['method'] = $method;
				
				$json_data = http_build_query($data, '', '&');
				$sign = hash_hmac("sha512", $json_data, $this->api_secret);

				$headers = array(
					"Key: " . $this->api_key,
					"Sign: " . $sign,
				);	

				$host = $this->host;
			}	
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, $host);
				
				if ($json_data) {
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
				}
				
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_TIMEOUT, 20);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
				curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
				curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
				$ch = apply_filters('curl_merch', $ch, $this->m_name, $this->m_id);
				
				$err  = curl_errno($ch);
				$result = curl_exec($ch);
				$info = curl_getinfo($ch);
				
				curl_close($ch);
				
				do_action('save_merchant_error', $this->m_name, $this->m_id, $host, $headers, $json_data, $result, $err);
				
				$res = @json_decode($result, true);
		
				return $res;
				
			}			
			
			return '';
		}
		
	}
}	