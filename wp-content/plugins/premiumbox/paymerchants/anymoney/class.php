<?php
/*
https://docs.any.money/ru/
*/

if (!class_exists('AP_AnyMoney')) {
	class AP_AnyMoney
	{
		private $m_name = "";
		private $m_id = "";
		private $api_key = '';
		private $merchant_id = '';

		function __construct($m_name, $m_id, $api_key, $merchant_id)
		{
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->api_key = trim($api_key);
			$this->merchant_id = trim($merchant_id);
		}

		function get_pwcurrency() {
			
			$data = array(
				"method" => "pwcurrency.list", 
				"jsonrpc" => "2.0",
				"id" => '1'
			);
			$res = $this->request($data);
			
			return $res;
		}

		function get_balance($currency = '') {
			
			$currency = trim($currency);
			$data = array(
				"method" => "balance", 
				"jsonrpc" => "2.0",
				"id" => '1'
			);
			if ($currency) {
				$data['params'] = array("curr" => $currency);
			}
			$res = $this->request($data);
			$data = array();
			if (isset($res['result']) and is_array($res['result'])) {
				foreach ($res['result'] as $k => $v) {
					$data[$k] = $v;
				}
			}	
			
			return $data;
		}

		function payout($amount, $currency, $order_id, $payway, $to = '', $callback_url = '', $in_curr = '') {
			
			$in_curr = trim($in_curr);
			
			$pay_data = array();
			$pay_data['error'] = 1;
			$pay_data['trans_id'] = 0;		
			
			$data = array(
				'method' => 'payout.create',
				'params' => array('amount' => $amount, 'externalid' => 'out_' . $order_id, 'out_curr' => $currency, 'payway' => $payway, 'callback_url' => $callback_url),
				"jsonrpc" => "2.0",
				"id" => '1'
			);
			if ($in_curr) {
				$data['params']['in_curr'] = $in_curr;
			}
			if ('cash' == strtolower($payway)) {
				$data['params']['contact'] = $to;
				$data['params']['region'] = '';
			} else {
				$data['params']['payee'] = $to;
			}
			$res = $this->request($data);
			if (isset($res['result'], $res['result']['token'])) {
				$pay_data['error'] = 0;
				$pay_data['trans_id'] = $res['result']['token'];
			}
			
			return $pay_data;		
		}

		function get_history_payout($count) {
			
			$data = array(
				'method' => 'history.payout',
				'params' => array('count' => $count),
				"jsonrpc" => "2.0",
				"id" => '1'
			);
			$res = $this->request($data);
			$data = array();
			if (isset($res['result'], $res['result']['data']) and is_array($res['result']['data'])) {
				foreach ($res['result']['data'] as $d) {
					$data[$d['externalid']] = $d;
				}
				
				return $data;
			}
			
			return 'notapi';
		}	

		function request($data) {
			
			$utc_now = strval(((int)round(microtime(true) * 1000)));

			$json_data = json_encode($data);

			$params = is_isset($data, 'params');
			
			$headers = array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($json_data),
				'x-merchant: ' . $this->merchant_id,
				'x-signature: ' . $this->sign_data($this->api_key, $params ?: array(), $utc_now),
				'x-utc-now-ms: ' . $utc_now
			);			
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, 'https://api.any.money/');
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_TIMEOUT, 20);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
				$ch = apply_filters('curl_ap', $ch, $this->m_name, $this->m_id);
				
				$err  = curl_errno($ch);
				$result = curl_exec($ch);
				
				curl_close($ch);
				
				do_action('save_paymerchant_error', $this->m_name, $this->m_id, 'https://api.any.money/', $headers, $json_data, $result, $err);
				
				$res = @json_decode($result, true);
		
				return $res;				 
			}			

			return '';
		}

		function sign_data($key, $data, $utc_now) {
			
			ksort($data);
			$s = '';
			foreach ($data as $k => $value) {
				if (in_array(gettype($value), array('array', 'object', 'NULL')) ){
					continue;
				}
				if (is_bool($value)) {
					$s .= $value ? "true" : "false";
				} else {
					$s .= $value;
				}
			}
			$s .= $utc_now;
			
			return hash_hmac('sha512', strtolower($s), $key);
		}
	}
}