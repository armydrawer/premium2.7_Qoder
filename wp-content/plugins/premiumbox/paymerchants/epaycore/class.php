<?php
/*
https://epaycoreru.docs.apiary.io/#
*/

if (!class_exists('AP_EpayCore')) {
	class AP_EpayCore
	{
		private $m_name = "";
		private $m_id = "";
		private $api_id = '';
		private $api_secret = '';

		function __construct($m_name, $m_id, $api_id, $api_secret)
		{
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->api_id = trim($api_id);
			$this->api_secret = trim($api_secret);
		}

		function payout($descr, $account, $amount, $order_id, $currency) {
			
			$order_id = trim($order_id);
			$account = trim($account);
			$descr = trim($descr);
			
			$pay_data = array();
			$pay_data['error'] = 1;
			$pay_data['trans_id'] = 0;		
			
			$data = array(
				'account' => $account,
				'currency' => $currency,
				'amount' => $amount,
				'descr' => $descr,
				'payment_id' => $order_id,
			);
			$res = $this->request('/v1/transfer', $data);
			
			if (isset($res['batch'])) {
				$pay_data['error'] = 0;
				$pay_data['trans_id'] = $res['batch'];
			}
			
			return $pay_data;		
		}

		function get_history_payout($limit = 25) {
			
			$limit = intval($limit);
			
			$data = array(
				'limit' => $limit,
				'order' => 'created_desc',
				'type' => 8,
			);
			$res = $this->request('/v1/history', $data);
			if (isset($res['history']) and is_array($res['history'])) {
				$h = array();
				foreach ($res['history'] as $his) {
					$h[$his['batch']] = $his;
				}
				return $h;
			}
			
			return '';
		}		

		function get_balance() {
			
			$data = array();
			$res = $this->request('/v1/balance', $data);
			if (is_array($res) and isset($res['total'])) {
				foreach ($res as $cur => $amount) {
					$data[strtolower($cur)] = is_sum($amount);
				}
			}
			
			return $data;
		}

		function request($path, $data) {
			
			$data['api_id'] = $this->api_id;
			$data['api_secret'] = $this->api_secret;
			
			$json_data = json_encode($data);
			
			$url = 'https://api.epaycore.com' . $path;
			
			$headers = array(
				'Content-Type: application/json',
			);			
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, $url);
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
				
				do_action('save_paymerchant_error', $this->m_name, $this->m_id, $url, $headers, $json_data, $result, $err);
				
				$res = @json_decode($result, true);
		
				return $res;				 
			}			

			return '';
		}				
	}
}