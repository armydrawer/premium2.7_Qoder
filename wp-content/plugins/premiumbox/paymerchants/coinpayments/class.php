<?php

if (!class_exists('AP_CoinPaymentsAPI')) {
	class AP_CoinPaymentsAPI {
		
		private $m_name = "";
		private $m_id = "";
		private $private_key = '';
		private $public_key = '';
	
		function __construct($m_name, $m_id, $private_key, $public_key) {
			
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->private_key = $private_key;
			$this->public_key = $public_key;
			
		}	
	
		public function create_adress($currency = '', $ipn_url = '') {
			
			return $this->request('get_callback_address', array('currency' => $currency, 'ipn_url' => $ipn_url));
		}		
	
		public function get_balance($all = FALSE) {		
		
			return $this->request('balances', array('all' => $all ? 1:0));
		}	
	
		public function get_transfer($amount, $currency, $address, $auto_confirm, $set_params = '') {
			
			if (!is_array($set_params)) { $set_params = array(); }
			
			$amount = is_sum($amount);
			
			$currency = strtoupper(trim((string)$currency));
			
			$address = trim((string)$address);		
			
			$params = array();
			$params['amount'] = $amount;
			$params['currency'] = $currency;
			$params['address'] = $address;
			$params['auto_confirm'] = $auto_confirm;
			foreach ($set_params as $set_param_key => $set_param_value) {
				$params[$set_param_key] = $set_param_value;
			}
			
			return $this->request('create_withdrawal', $params);
		}

		public function get_transfer_info($id) {
			
			$id = trim((string)$id);		
			
			$params = array();
			$params['id'] = $id;
			
			return $this->request('get_withdrawal_info', $params);
		}		
	
		private function request($cmd, $req = array()) {
			
			$req['version'] = 1;
			$req['cmd'] = $cmd;
			$req['key'] = $this->public_key;
			$req['format'] = 'json'; 
			
			$post_data = http_build_query($req, '', '&');
			
			$hmac = hash_hmac('sha512', $post_data, $this->private_key);
			
			$headers = array(
				'HMAC: '. $hmac
			);
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, 'https://www.coinpayments.net/api.php');
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
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
				
				do_action('save_paymerchant_error', $this->m_name, $this->m_id, $cmd, $headers, $post_data, $result, $err);
				
				if (PHP_INT_SIZE < 8 && version_compare(PHP_VERSION, '5.4.0') >= 0) {
					$res = @json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
				} else {	
					$res = @json_decode($result, true);
				}
		
				return $res;				 
			}			
			
			return '';
		}
	}
}