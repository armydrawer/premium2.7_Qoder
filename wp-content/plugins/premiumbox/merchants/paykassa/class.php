<?php

/*
https://paykassa.pro/docs/#api-API
*/

if (!class_exists('PayKassa')) {
	class PayKassa {
		
		private $m_name = "";
		private $m_id = "";
		private $sci_id = "";
		private $sci_key = "";
		private $api_id = "";
		private $api_key = "";

		function __construct($m_name, $m_id, $sci_id = '', $sci_key = '', $api_id = '', $api_key = '') {
			
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->sci_id = trim($sci_id);	
			$this->sci_key = trim($sci_key);
			$this->api_id = trim($api_id);	
			$this->api_key = trim($api_key);	
			
		}		
		
		function create_invoice($order_id, $amount, $currency, $system, $comment) {
			
			$data = array();
			$data['func'] = 'sci_create_order_get_data';
			$data['sci_id'] = $this->sci_id;
			$data['sci_key'] = $this->sci_key;
			$data['order_id'] = $order_id;
			$data['amount'] = $amount;
			$data['currency'] = $currency;
			$data['system'] = $system;
			$data['comment'] = $comment;
			$data['phone'] = false;
			$data['paid_commission'] = 'shop';
			
			return $res = $this->request('sci/0.4/index.php', $data);
		}		
		
		function sci_confirm_order($hash) {
			
			$data = array();
			$data['func'] = 'sci_confirm_order';
			$data['sci_id'] = $this->sci_id;
			$data['sci_key'] = $this->sci_key;
			$data['private_hash'] = $hash;
			
			return $res = $this->request('sci/0.4/index.php', $data);
		}			

		function sci_confirm_transaction_notification($hash) {
			
			$data = array();
			$data['func'] = 'sci_confirm_transaction_notification';
			$data['sci_id'] = $this->sci_id;
			$data['sci_key'] = $this->sci_key;
			$data['private_hash'] = $hash;
			
			return $res = $this->request('sci/0.4/index.php', $data);			
		}
		
		function create_payout($amount, $currency, $system, $number, $tag, $priority) {
			
			$data = array();
			$data['func'] = 'api_payment';
			$data['api_id'] = $this->api_id;
			$data['api_key'] = $this->api_key;
			$data['shop_id'] = $this->sci_id;
			$data['amount'] = $amount;
			$data['currency'] = $currency;
			$data['system'] = $system;
			$data['number'] = $number;
			$data['tag'] = $tag;
			$data['high'] = $priority;
			
			return $res = $this->request('api/0.9/index.php', $data);
		}
		
		function get_balance() {
			
			$balance = array();
			$data = array();
			$data['func'] = 'api_get_shop_balance';
			$data['api_id'] = $this->api_id;
			$data['api_key'] = $this->api_key;
			$data['shop_id'] = $this->sci_id;
			$res = $this->request('api/0.9/index.php', $data);
			if (isset($res['data']) and $res['data']) {
				foreach ($res['data'] as $m => $amount) {
					$balance[pn_strip_input($m)] = is_sum($amount);
				}
			}
			
			return $balance;
		}		
		
		function request($method, $data = array()) {

			$url = 'https://paykassa.app/' . $method;

			if (!is_array($data)) { $data = array(); }
			
			$json_data = '';

			$headers = array(
				"Content-type: application/x-www-form-urlencoded",
			);		
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				
				if (count($data) > 0) {
					$json_data = http_build_query($data);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
				}	
				
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_TIMEOUT, 20);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
				curl_setopt($ch, CURLOPT_ENCODING, '');
				curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
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