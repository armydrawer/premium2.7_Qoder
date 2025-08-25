<?php

if (!class_exists('WestWallet')) {
	class WestWallet {
		
		private $m_name = "";
		private $m_id = "";
		private $public_key = "";
		private $private_key = "";		
		
		function __construct($m_name, $m_id, $public_key, $private_key = '') {
			
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->public_key = trim($public_key);
			$this->private_key = trim($private_key);
			
		}

		function generate_adress($currency, $ipn_url = '', $label = '') {
			
			$currency = trim($currency);
			$ipn_url = trim($ipn_url);
			$label = trim($label);
			
			$address = array(
				'address' => '',
				'dest_tag' => '',
			);
			
			$data = array('currency' => $currency);
			if ($ipn_url) {
				$data['ipn_url'] = $ipn_url;
			}
			if ($label) {
				$data['label'] = $label;
			}
			
			$res = $this->request('address/generate', $data);
			if (is_array($res) and isset($res['address'], $res['dest_tag']) and $res['address']) {
				$address = array(
					'address' => trim($res['address']),
					'dest_tag' => trim($res['dest_tag']),
				);
			}
			
			return $address;
		}

		function get_search($id) {
			
			$id = trim($id);
			$data = array('id' => $id);
			$res = $this->request('wallet/transaction', $data);
			
			if (isset($res['id']) and $res['id'] and $res['id'] == $id) {
				return $res;
			}
			
			return array();
		}		
		
		function request($method, $json = '') {
			
			$url = 'https://api.westwallet.io/' . $method;
			$ts = current_time('timestamp'); //gmdate('U');
			
			$json_data = '';
			
			$headers = array(
				'X-API-KEY: '. $this->public_key,
				'X-ACCESS-TIMESTAMP: '. $ts,
			);			
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, $url);
				
				if (is_array($json)) {
					if ('wallet/transaction' == $method) {
						$json_data = json_encode($json, JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES);
					} else {
						$json_data = json_encode($json, JSON_UNESCAPED_SLASHES);
					}
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
					$headers[] = 'Content-Type: application/json';
				}				
				
				$headers[] = 'X-ACCESS-SIGN: '. hash_hmac("sha256", $ts. $json_data, $this->private_key);
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
				
				do_action('save_merchant_error', $this->m_name, $this->m_id, $url, $headers, $json_data, $result, $err);
				
				$res = @json_decode($result, true);
		
				return $res;				 
			}								
			
			return '';
		}	
	}
}