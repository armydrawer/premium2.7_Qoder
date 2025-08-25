<?php

if (!class_exists('AP_Odysseq')) {
	class AP_Odysseq {
		
		private $m_name = "";
		private $m_id = "";
		private $token = "";

		function __construct($m_name, $m_id, $token) 
		{
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->token = trim($token);
		}	

		function status($order_id) {
			
			$params = array(
				"orderId" => $order_id
			);
			
			$res = $this->request('payment.status', $params);
			
			return $res;
		}

		function send($order_id, $amount, $receiver) {
			
			$params = array(
				"orderId" => $order_id,
				"amount" => $amount,
				"receiver" => $receiver
			);
			
			$res = $this->request('payment.send', $params);
			
			$status = '';
			
			if (isset($res['success'],$res['paymentInfo']['status'])) {
				$status = strtolower($res['paymentInfo']['status']);
			}				
			
			return $status;
		}

		function get_balance() {
			
			$params = array();
			$res = $this->request('account.balance', $params);
			
			$balance = array();
			
			if (isset($res['balance']['toWallet'], $res['balance']['toCard'])) {
				$balance['card'] = is_sum($res['balance']['toCard']);
				$balance['wallet'] = is_sum($res['balance']['toWallet']); 
			}
			
			return $balance;	
		}

		public function request($action, $data = array()) {

			if (!is_array($data)) { $data = array(); }
			
			$json = array();
			$json['jsonrpc'] = '2.0';
			$json['method'] = $action;
			$json['id'] = time();
			if (is_array($data) and count($data) > 0) {
				$json['params'] = $data;
			}
			
			$json_data = json_encode($json);
			
			$headers = array(
				'Content-Type: application/json',
				'Cache-control: no-cache',
				'Authorization: Bearer '. $this->token,
			);
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, 'https://api.odysseq.com/partner/v1/json');
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
				
				do_action('save_paymerchant_error', $this->m_name, $this->m_id, $action, $headers, $json_data, $result, $err);
				
				$res = @json_decode($result, true);
				
				if (isset($res['result']) and is_array($res['result'])) {
					return $res['result'];		
				}
			}		
			
			return '';
		} 
	}
}