<?php

if (!class_exists('CoinPaymentsAPI')) {
	class CoinPaymentsAPI {
		
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
	
		function request($cmd, $req = array()) {
			
			$req['version'] = 1;
			$req['cmd'] = $cmd;
			$req['key'] = $this->public_key;
			$req['format'] = 'json'; 
			
			$post_data = http_build_query($req, '', '&');
			
			$hmac = hash_hmac('sha512', $post_data, $this->private_key);
			
			$headers = array(
				'HMAC: '. $hmac,
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
				$ch = apply_filters('curl_merch', $ch, $this->m_name, $this->m_id);
				
				$err  = curl_errno($ch);
				$result = curl_exec($ch);
				$info = curl_getinfo($ch);
				
				curl_close($ch);
				
				do_action('save_merchant_error', $this->m_name, $this->m_id, 'https://www.coinpayments.net/api.php', $headers, $post_data, $result, $err);
				
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