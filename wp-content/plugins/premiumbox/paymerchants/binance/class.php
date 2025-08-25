<?php

if (!class_exists('AP_Binance')) {
	class AP_Binance {
		
		private $m_name = "";
		private $m_id = "";
		private $api_key = "";
		private $api_secret = "";

		function __construct($m_name, $m_id, $api_key, $api_secret)
		{
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->api_key = trim($api_key);
			$this->api_secret = trim($api_secret);
		}	
		
		public function get_balance() {
			
			$res = $this->request('/api/v3/account', array(), 0);
			$purses = array();
			if (isset($res['balances'])) {
				foreach ($res['balances'] as $value) {
					$currency = strtoupper(trim(is_isset($value, 'asset')));
					$amount = is_sum(is_isset($value, 'free'));
					$purses[$currency] = $amount;
				}			
			}
			
			return $purses;
		}
		
		public function send_money($currency, $amount, $address, $network = '', $addressTag = '') {
			
			$currency = mb_strtoupper($currency);
			$addressTag = trim($addressTag);
			$network = trim($network);
			$data = array(
				'coin' => $currency,
				'address' => $address,
				'amount' => $amount
			);
			if ($addressTag) {
				$data['addressTag'] = $addressTag;
			}	
			if ($network) {
				$data['network'] = $network;
			}
			$res = $this->request('/sapi/v1/capital/withdraw/apply', $data, 1);
			
			return $res;
		}
		
		public function buy($execution_type, $symbol, $price, $start_volume, $timeInForce = '') {
			
			$execution_type = intval($execution_type);
			if (1 == $execution_type) { 
				$type = 'LIMIT'; 
			} else { 
				$type = 'MARKET'; 
			}
		
			$data = array(
				'newOrderRespType'=>'RESULT'
			);
			$data['symbol'] = strtoupper($symbol);
			$data['side'] = 'BUY';
			$data['quantity'] = $start_volume;
			$data['type'] = $type;
			if (1 == $execution_type) {
				$data['price'] = $price;
				$data['timeInForce'] = $timeInForce;
			}
			
			$res = $this->request('/api/v3/order', $data, 1);
			
			return $res;
		}	
		
		public function get_payout_transactions($startTime = '', $endTime = '', $currency = '') {
			
			$data = array();
			
			$currency = trim($currency);
			if ($currency) {
				$data['coin'] = $currency;
			}

			if ($startTime) {
				$data['startTime'] = $startTime . '000';
			}
			
			if ($endTime) {
				$data['endTime'] = $endTime . '000';
			}		
			
			$res = $this->request('/sapi/v1/capital/withdraw/history', $data, 0);
			
			$transactions = array();
			if (is_array($res)) {
				foreach ($res as $data) {
					if (isset($data['id'])) {
						$transactions[$data['id']] = $data;
					}
				}
			}
			
			return $transactions;
		}
		
		function coins_info() {
			
			$data = array();
			$res = $this->request('/sapi/v1/capital/config/getall', $data, 0);
			
			$info = array();
			if (is_array($res) and !isset($res['code'])) {
				$info = $res;
			}
		
			return $info;
		}

		function tradeFee() {
			
			$data = array();
			$res = $this->request('/sapi/v1/asset/tradeFee', $data, 0);
			if (is_array($res)) {
				foreach ($res as $fee) {
					if (isset($fee['symbol'])) {
						$data[$fee['symbol']] = $fee;
					}
				}
			}
			
			return $data;
		}	
		
		public function request($api_name, $data = array(), $post = 1) { 
		
			$post = intval($post);
			$api_name = trim($api_name);
			
			$data['timestamp'] = $this->get_time();
			
			$post_data = http_build_query($data, '', '&');
			$signature = hash_hmac('sha256', $post_data, $this->api_secret);
			
			$json_data = '';
			
			$headers = array(
				'X-MBX-APIKEY: '. $this->api_key
			);			
			
			$url = 'https://api.binance.com' . $api_name . '?' . $post_data . '&signature=' . $signature;
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, $url);
				if ($post) {
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
				}
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
		
		function exchangeInfo() {
			
			$info = array();
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, 'https://api.binance.com/api/v3/exchangeInfo');
				// curl_setopt($ch, CURLOPT_POST, true);
				// curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
				// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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
				
				do_action('save_paymerchant_error', $this->m_name, $this->m_id, 'https://api.binance.com/api/v3/exchangeInfo', '', '', $result, $err);
				
				$res = @json_decode($result, true);
		
				if (isset($res['symbols']) and is_array($res['symbols'])) {
					foreach ($res['symbols'] as $d) {
						$symbol = trim($d['symbol']);
						$status = trim($d['status']);
						$filters = array();
						foreach ($d['filters'] as $dfi) {
							$filters[$dfi['filterType']] = $dfi;
						}
						if ('TRADING' == $status) {
							$info[$symbol]['filters'] = $filters;
							$info[$symbol]['symb'] = $d['baseAssetPrecision'];
						}
					}
				}				 
			}			
			
			return $info;
		}	
		
		function get_time() {
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, 'https://api.binance.com/api/v1/time');
				// curl_setopt($ch, CURLOPT_POST, true);
				// curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
				// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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
				
				do_action('save_paymerchant_error', $this->m_name, $this->m_id, 'https://api.binance.com/api/v1/time', '', '', $result, $err);
				
				$res = @json_decode($result, true);
		
				return is_isset($res, 'serverTime');				 
			}			
			
			return '';
		}	
	}    
}