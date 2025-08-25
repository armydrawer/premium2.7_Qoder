<?php
/*
https://admin.vanilapay.com/api/doc/v2#/operations/getSiteAccounts
*/

if (!class_exists('AP_FireKassa')) {
	class AP_FireKassa {
		
		private $m_name = "";
		private $m_id = "";
		private $api_key = "";
		private $secret_key = "";
		private $host = '';

		function __construct($m_name, $m_id, $api_key, $secret_key, $host = '')
		{
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->api_key = trim($api_key);
			$this->secret_key = trim($secret_key);
			$host = trim(str_replace(array('http://','https://'), '', $host));
			if (strlen($host) < 1) {
				$host = 'admin.vanilapay.com';
			}
			$this->host = rtrim('https://' . $host, '/');		
		}		

		function create_card($data) {
			
			return $res = $this->request('/api/v2/deposit', $data, 1);
		}
		
		function create_invoice($data) {
			
			return $res = $this->request('/api/v2/invoices', $data, 1);
		}
		
		function paid_order($order_id) {
			
			$order_id = trim($order_id);
			$data = array();
			
			return $res = $this->request('/api/v2/transactions/' . $order_id . '/claim-paid', $data, 1);
		}		

		function create_payout($data) {
			
			return $res = $this->request('/api/v2/withdrawal', $data, 1);
		}

		function get_transactions($action, $id) {
			
			$get = array(
				'filter[action]' => $action,
				'filter[id]' => $id,
			);			
			$data = array();
			
			$trans = array();
			$res = $this->request('/api/v2/transactions?' . http_build_query($get), $data, 0);
			if (isset($res['items']) and is_array($res['items'])) {
				foreach ($res['items'] as $item) {
					$trans[is_isset($item,'id')] = $item;
				}
			}
			
			return $trans;
		}

		function get_invoice_status($id) {
			
			$data = array();
			
			return $res = $this->request('/api/v2/transactions/' . $id, $data, 0);
		}		

		function get_balance() {
			
			$balance = array();
			$data = array();
			$res = $this->request('/api/v2/accounts', $data, 0);
			if (isset($res['items']) and is_array($res['items'])) {
				foreach ($res['items'] as $item) {
					$code = trim($item['code']);
					$amount = is_sum($item['balance']);
					$balance[$code] = $amount;
				}
			}
			
			return $balance;
		}

		function get_methods() {
			
			$data = array();
			
			return $res = $this->request('/api/v2/card-methods', $data, 0);
		}
		
		function request($method, $data = array(), $post = 0) {

			$post = intval($post);

			$json_data = '';
			
			$headers = array(
				"Content-Type: application/json",
				"Authorization: Bearer " . $this->api_key,
				"Accept-Language: " . get_locale(),
			);			
			
			$url = $this->host . $method;
			
			$json_data = '';
			
			$sign = $method;			
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, $url);
				
				if (is_array($data) and $post) {
					$json_data = json_encode($data);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
					$sign .= $json_data;
				}

				if (strlen($this->secret_key) > 0) {
					$headers[] = "Signature: " . hash_hmac('sha512', $sign, $this->secret_key);	
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
				$info = curl_getinfo($ch);
				
				curl_close($ch);
				
				do_action('save_paymerchant_error', $this->m_name, $this->m_id, $url, $headers, $json_data, $result, $err);
				
				$res = @json_decode($result, true);
		
				return $res;
				
			}			
			
			return '';
		}
	}
}