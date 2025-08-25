<?php
if (!class_exists('Heleket')) {
	class Heleket {
		
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

		function create_invoice($amount, $currency, $network, $order_id, $url_return, $url_callback, $lifetime=7200, $to_currency='') {
			$lifetime = intval($lifetime);
			$currency = strtoupper($currency);
			$to_currency = trim($to_currency);
			
			$data = array(
				'amount' => $amount,
				'currency' => $currency,
				'network' => $network,
				'order_id' => $order_id,
				'url_return' => $url_return,
				'url_callback' => $url_callback,
				'is_payment_multiple' => false,
			);
			if ($lifetime) {
				$data['lifetime'] = $lifetime;
			}
			if ($to_currency) {
				$data['to_currency'] = $to_currency;
			}
			$res = $this->request('/v1/payment', $data);
			if (isset($res['state'], $res['result'], $res['result']['uuid']) and $res['state'] == 0 and is_array($res['result'])) {
				return $res['result'];
			}	
				return '';			
		}

		function create_address($currency, $network, $order_id, $url_callback) {
			$currency = strtoupper($currency);
			
			$data = array(
				'currency' => $currency,
				'network' => $network,
				'order_id' => $order_id,
				'url_callback' => $url_callback,
			);
			$res = $this->request('/v1/wallet', $data);
			if (isset($res['state'], $res['result'], $res['result']['uuid']) and $res['state'] == 0 and is_array($res['result'])) {
				return $res['result'];
			}	
				return '';			
		}			

		function get_status($uuid) {
			$data = array(
				'uuid' => $uuid
			);
			$res = $this->request('/v1/payment/info', $data);
			if (isset($res['state'], $res['result'], $res['result']['uuid']) and $res['state'] == 0 and is_array($res['result'])) {
				return $res['result'];
			}	
				return '';
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
				$ch = apply_filters('curl_merch', $ch, $this->m_name, $this->m_id);
				
				$err  = curl_errno($ch);
				$result = curl_exec($ch);
				curl_close($ch);
				
				do_action('save_merchant_error', $this->m_name, $this->m_id, $url, $headers, $json_data, $result, $err);
				
				$res = @json_decode($result, true);
				return $res;

			}
			
			return '';
		}
	}
}