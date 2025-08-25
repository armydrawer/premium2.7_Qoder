<?php
if (!class_exists('AP_Heleket')) {
	class AP_Heleket {
		
		private $m_name = "";
		private $m_id = "";
		private $merchant_id = "";
		private $api_key = "";
		
		function __construct($m_name, $m_id, $merchant_id='', $api_key='') {
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->merchant_id = trim($merchant_id);
			$this->api_key = trim($api_key);
		}

		function create_payout($amount, $currency, $network, $order_id, $address, $is_subtract=1, $payout_api='', $from_currency='', $memo='', $get_prio='') {
			$is_subtract = intval($is_subtract);
			$currency = strtoupper($currency);
			
			$data = array(
				'amount' => $amount,
				'currency' => $currency,
				'network' => $network,
				'order_id' => $order_id,
				'address' => $address,
				'is_subtract' => $is_subtract,
			);	
			
			$from_currency = trim($from_currency);
			if ($from_currency) {
				$data['from_currency'] = $from_currency;
			}
			
			$memo = trim($memo);
			if ($memo) {
				$data['memo'] = $memo;
			}
			
			$get_prio = trim($get_prio);
			if ($get_prio) {
				$data['priority'] = $get_prio;
			}			
			
			$res = $this->request('/v1/payout', $data, $payout_api);
			if (isset($res['state'], $res['result'], $res['result']['uuid']) and $res['state'] == 0 and is_array($res['result'])) {
				return $res['result'];
			}	
				return '';			
		}		
		
		function get_payout_status($uuid, $payout_api) {
			$data = array(
				'uuid' => $uuid
			);
			$res = $this->request('/v1/payout/info', $data, $payout_api);
			if (isset($res['state'], $res['result'], $res['result']['uuid']) and $res['state'] == 0 and is_array($res['result'])) {
				return $res['result'];
			}
				return '';
		}		

		function get_balance() {
			$balance = array();
			$data = array();
			$res = $this->request('/v1/balance', $data);
			if (isset($res['state'], $res['result'], $res['result'][0], $res['result'][0]['balance']['merchant']) and $res['state'] == 0 and is_array($res['result'][0]['balance']['merchant'])) {
				foreach ($res['result'][0]['balance']['merchant'] as $v) {
					$curr = strtoupper($v['currency_code']);
					$balance[$curr] = is_sum($v['balance']);
				}
			}
				return $balance;
		}		
		
		function request($method, $data=array(), $payout_api='') {

			if (!is_array($data)) { $data = array(); }

			$json_data = json_encode($data);

			$api_key = $this->api_key;
			$payout_api = trim($payout_api);
			if ($payout_api) {
				$api_key = $payout_api;
			}

			$headers = array(
				"Content-Type: application/json",
				"Accept: application/json",
				'merchant: '. $this->merchant_id,
				'sign: '. md5(base64_encode($json_data) . $api_key),
			);
			
			$url = 'https://api.heleket.com'. $method;

			if ($ch = curl_init()) {
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, $url);
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
				
				do_action('save_paymerchant_error', $this->m_name, $this->m_id, $url, $headers, $json_data, $result, $err);
				
				$res = @json_decode($result, true);
				return $res;

			}
			
			return '';
		}
	}
}