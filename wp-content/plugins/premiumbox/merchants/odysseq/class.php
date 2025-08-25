<?php

if (!class_exists('Odysseq')) {
	class Odysseq {
		
		private $m_name = "";
		private $m_id = "";
		private $token = "";

		function __construct($m_name, $m_id, $token) 
		{
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->token = trim($token);
		}	

		function invoice_wallet($order_id, $amount, $info, $successUrl, $failUrl) {
			
			$params = array(
				"orderId" => $order_id,
				"amount" => $amount,
				"successUrl" => $successUrl,
				"failUrl" => $failUrl,
			);
			if (is_array($info) and count($info) > 0) {
				$params['info'] = $info;
			}
			$res = $this->request('payment.receive', $params);
			
			return $res;			
		}

		function invoice_card($order_id, $amount, $card, $info, $successUrl, $failUrl) {
			
			$params = array(
				"orderId" => $order_id,
				"amount" => $amount,
				"cardTail" => mb_substr($card, -4),
				"clientIp" => is_isset($info, 'userIp'),
				"successUrl" => $successUrl,
				"failUrl" => $failUrl,
			);
			if (is_array($info) and count($info) > 0) {
				$params['info'] = $info;
			}
			$res = $this->request('payment.receive', $params);
			
			return $res;			
		}

		function invoice_contact($order_id, $amount, $card, $first_name, $middle_name, $last_name, $info, $successUrl, $failUrl) {
			
			$params = array(
				"orderId" => $order_id,
				"amount" => $amount,
				"successUrl" => $successUrl,
				"failUrl" => $failUrl,
				"client" => array(
					'firstName' => $first_name,
					'middleName' => $middle_name,
					'lastName' => $last_name,
					'cardNumber' => $card,
				),
			);
			if (is_array($info) and count($info) > 0) {
				$params['info'] = $info;
			}
			$res = $this->request('payment.contact', $params);
			
			return $res;			
		}

		function status($order_id) {
			
			$order_id = pn_strip_input($order_id);
			
			$params = array(
				"orderId" => $order_id
			);
			
			$res = $this->request('payment.status', $params);
			
			return $res;
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
				$ch = apply_filters('curl_merch', $ch, $this->m_name, $this->m_id);
				
				$err  = curl_errno($ch);
				$result = curl_exec($ch);
				
				curl_close($ch);
				
				do_action('save_merchant_error', $this->m_name, $this->m_id, 'https://api.odysseq.com/partner/v1/json', $headers, $json_data, $result, $err);
				
				$res = @json_decode($result, true);
				
				if (isset($res['result']) and is_array($res['result'])) {
					return $res['result'];		
				}
			}		
			
			return '';
		} 
	}
}