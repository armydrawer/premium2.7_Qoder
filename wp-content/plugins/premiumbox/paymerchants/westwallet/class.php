<?php

if (!class_exists('AP_WestWallet')) {
	class AP_WestWallet {
		
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

		function get_balance() {
			
			$res = $this->request('wallet/balances', '');
			$balans = array();
			
			if (is_array($res) and !isset($res['message'])) {
				foreach ($res as $b_key => $b_sum) {
					$balans[mb_strtoupper($b_key)] = is_sum($b_sum);
				}
			}
			
			return $balans;
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

		function send_money($currency, $amount, $address, $dest_tag = '', $description = '', $priority = '') {
			
			$data = array();
			$data['error'] = 1;
			$data['trans_id'] = 0;		
			
			$dest_tag = trim($dest_tag);
			$description = trim($description);
			$address = trim($address);
			$priority = trim($priority);
			
			$json = array(
				'currency' => $currency,
				'amount' => $amount,
				'address' => $address
			);
			
			if ($dest_tag) {
				$json['dest_tag'] = $dest_tag;
			}
	
			if ($description) {
				$json['description'] = $description;
			}
			
			if ($priority) {
				$json['priority'] = $priority;
			}
			
			$res = $this->request('wallet/create_withdrawal', $json);
			if (isset($res['id'], $res['status'])) {
				$data['error'] = 0;
				$data['trans_id'] = $res['id'];
			}		

			return $data;
		}		
		
		function request($method, $json = '') {
			
			$url = 'https://api.westwallet.io/' . $method;
			$ts = current_time('timestamp'); //gmdate('U');
			
			$json_data = '';
			
			$headers = array();
			
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
				
				$headers[] = 'X-API-KEY: '. $this->public_key;
				$headers[] = 'X-ACCESS-TIMESTAMP: '. $ts;
				$headers[] = 'X-ACCESS-SIGN: ' . hash_hmac("sha256", $ts . $json_data, $this->private_key);			
				
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