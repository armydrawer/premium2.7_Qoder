<?php

if (!class_exists('AP_MoneyGo')) {
	class AP_MoneyGo {
		
		private $m_name = "";
		private $m_id = "";
		private $client_id = "";
		private $client_secret = "";
		private $token = "";
		private $secret_key = "";

		function __construct($m_name, $m_id, $client_id, $client_secret, $token, $secret_key)
		{
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->client_id = trim($client_id);
			$this->client_secret = trim($client_secret);
			$this->secret_key = trim($secret_key);
			$this->token = $this->set_token($token);
		}		
		
		function set_token($token) {
			
			$token = trim($token);
			if (!$token) {
			
				$data = array(
					'grant_type' => 'client_credentials',
					'scope' => 'api',
					'client_id' => $this->client_id,
					'client_secret' => $this->client_secret,
				);
				$res = $this->request('token', $data, 1);
				if (isset($res['access_token'])) {
					return $res['access_token'];
				}
			
			}
			return $token;
		}
		
		function get_balance() {
			
			$data = array();
			$res = $this->request('api/wallets', $data, 0);
			$b = array();
			if (isset($res['status'],$res['data']) and is_array($res['data'])) {
				foreach ($res['data'] as $v) {
					if (isset($v['amount'], $v['number'])) {
						$b[$v['number']] = is_sum($v['amount']);
					}
				}
			}
			
			return $b;
		}

		function create_link($pay_id, $amount, $wallet_from, $wallet_to, $success_url, $cancel_url, $status_url, $description) {
			
			$link = '';
			
			$data = array(
				'wallet_to' => $wallet_to,
				'wallet_from' => $wallet_from,
				'amount' => $amount,
				'id' => $pay_id,
				'success_url' => $success_url,
				'cancel_url' => $cancel_url,
				'status_url' => $status_url
			);
	
			if (strlen($description) > 0) {
				$data['user_info'] = $description;
			}
	
			$data['signature'] = hash('sha256', json_encode(array('wallet_to' => $data['wallet_to'], 'wallet_from' => $data['wallet_from'], 'amount' => $data['amount'], 'id' => $data['id'], 'secret' => $this->secret_key)));
			$res = $this->request('api/processing/checkout', $data, 1);
			if (isset($res['data'], $res['data']['url'])) {
				$link = $res['data']['url'];
			}
			
			return $link;
		}

		function send($amount, $wallet_from, $wallet_to, $description = '') {
			
			$data = array(
				'wallet_to' => $wallet_to,
				'wallet_from' => $wallet_from,
				'amount' => $amount,
			);
			$description = trim($description);
			if ($description) {
				$data['description'] = $description;
			}
			$trans = '0';
			$res = $this->request('api/transaction/transfer', $data, 1);
			if (isset($res['status'],$res['data'],$res['data']['id']) and $res['status']) {
				$trans = intval($res['data']['id']);
			}
			
			return $trans;
		}

		function check_transaction($id) {
			
			$data = array(
				'external_id' => $id,
			);
			
			return $res = $this->request('api/transaction/show', $data, 0);
		}
		
		function request($method, $params = array(), $is_post = 0) {
			
			$is_post = intval($is_post);
			if (!is_array($params)) { $params = array(); }

			$json_data = '';

			$headers = array();			
			
			$url = 'https://api.money-go.com/' . $method;
			if (1 != $is_post and count($params) > 0) {
				$url .= '?' . http_build_query($params);
			}
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, $url);
				
				if ($is_post and count($params) > 0) {
					$json_data = json_encode($params); //, JSON_NUMERIC_CHECK
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
					curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
					
					$headers[] = 'content-type: application/json; charset=utf-8';
					$headers[] = 'accept: application/json, text/plain';
				}	
				
				if ($this->token) {
					$headers[] = 'Authorization: Bearer ' . $this->token;
					$headers[] = 'Authority: api.money-go.com';
				}				
				
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_TIMEOUT, 20);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
				curl_setopt($ch, CURLOPT_ENCODING, '');
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