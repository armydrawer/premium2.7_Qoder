<?php 

/*
https://getblock.net/api/documentation
*/

if (!class_exists('GetBlockAML')) {
	class GetBlockAML {
		
		private $m_name = "";
		private $m_id = "";
		private $api_key = "";
		public $bid_id = 0;
		
		function __construct($m_name, $m_id, $bid_id, $api_key) {
			
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->api_key = trim($api_key);
			$this->bid_id = intval($bid_id);
			
		}

		function verify_trans($addr, $currency, $txid, $id) {
			
			$data = array(
				'tx' => $txid,
				'addr' => trim($addr),
				'currency' => strtoupper(trim($currency)),
			);
			$res = $this->request('checkup.checktx', $id, $data);
			
			return $res;
		}

		function verify_addr($addr, $currency, $id) {
			
			$data = array(
				'addr' => trim($addr),
				'currency' => strtoupper(trim($currency)),
			);
			$res = $this->request('checkup.checkaddr', $id, $data);
			
			return $res;
		}

		function info($hash, $id) {
			
			$data = array(
				'hash' => trim($hash),
			);
			$res = $this->request('checkup.getresult', $id, $data);
			
			return $res;
		}		

		function request($method, $id, $params = '') {

			$data = array(
				"jsonrpc" => "2.0",
				"id" => $id,
				"method" => $method,
			);
			if (is_array($params) and count($params) > 0) {
				$data['params'] = $params;
			}

			$json_data = json_encode($data);
			
			$headers = array(
				'Content-Type: application/json',
				'Authorization: Bearer ' . $this->api_key,
				'cache-control: no-cache',
			);			
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'PremiumExchanger/2.7.0');
				curl_setopt($ch, CURLOPT_URL, 'https://api.getblock.net/rpc/v1/request');
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_TIMEOUT, 20);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20); 
				$ch = apply_filters('curl_amlcheck', $ch, $this->m_name, $this->m_id);
				
				$err  = curl_errno($ch);
				$result = curl_exec($ch);
				
				curl_close($ch);
				
				$res = @json_decode($result, true);
			
				do_action('save_amlcheck_error', $this->m_name, $this->m_id, 'https://api.getblock.net/rpc/v1/request', $headers, $json_data, $result, $err, $this->bid_id);
		
				return $res;				 
			}
			
			return '';
		}
	}
}