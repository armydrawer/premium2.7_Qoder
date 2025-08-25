<?php

if (!class_exists('Bitconce')) {
	class Bitconce {
		
		private $m_name = "";
		private $m_id = "";
		private $token = "";
		
		function __construct($m_name, $m_id, $token = '') {
			
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->token = trim($token);
			
		}

		function get_balance() {
			
			$data = array();
			$res = $this->request('getAccountInfo', $data);
			
			$balance = array();
			if (isset($res['status'], $res['data']['balance'], $res['data']['balance_fiat']) and 'success' == $res['status']) {
				$balance = array(
					'fiat' => str_replace(array(' ', ','), array('', '.'), $res['data']['balance_fiat']),
					'btc'  => str_replace(array(' ', ','), array('', '.'), $res['data']['balance'])
				);
			}
			
			return $balance;
		}

		function create_order($amount, $account = '', $bank_name = '', $sbp = '', $client_email = '', $client_ip = '', $client_redirect = '', $next_with_error = 0) {
			
			$next_with_error = intval($next_with_error);
			$sbp = intval($sbp);
			$client_redirect = trim($client_redirect);
			
			$data = array();
			$data['fiat_amount'] = $amount;
			$account = trim($account);
			if ($account) {
				$data['fromreq'] = $account;
			}
			$bank_name = trim($bank_name);
			if ($bank_name) {
				$data['bank_name'] = $bank_name;
			}
			if ($sbp) {
				$data['sbp'] = $sbp;
			}
			$data['client_phone'] = $client_ip;
			$data['client_email'] = $client_email;
			if ($client_redirect) {
				$data['client_redirect'] = $client_redirect;
			}
			$res = $this->request('createOrder', $data, 1);
			if (isset($res['status'], $res['data'], $res['data']['id']) and 'success' == $res['status']) {
				return $res;
			} elseif (isset($res['status'], $res['description']) and 'error' == strtolower($res['status']) and 'no deal available. try later' == strtolower($res['description']) and 1 == $next_with_error) {
				return $this->create_order($amount, $account, '', $sbp, $client_email, $client_ip, $client_redirect, 0);
			}
			
			return array();
		}
		
		function paid_order($order_id) {
			
			$data = array();
			$data['order_id'] = $order_id;
			$res = $this->request('paidOrder', $data, 1);
			if (isset($res['status'], $res['data'], $res['data']['id']) and 'success' == $res['status']) {
				return intval($res['data']['id']);
			}		
			
			return 0;
		}

		function get_order($order_id) {
			
			$data = array();
			$data['order_id'] = $order_id;
			$res = $this->request('getOrderById', $data);
			if (isset($res['status'], $res['data']) and 'success' == $res['status']) {
				return $res['data'];
			}			
			
			return array();
		}

		function get_orders($limit = 100) {
			
			$limit = intval($limit);
			$res = $this->request('getOrders', array('amount' => $limit));
			$orders = array();
			if (isset($res['orders']) and is_array($res['orders'])) {
				foreach ($res['orders'] as $o) {
					$orders[$o['id']] = $o;
				}
			}
			
			return $orders;
		}

		function get_payout($order_id, $account, $amount) {
			
			$data = array();
			$data['custom_id'] = $order_id;
			$data['requisites'] = $account;
			$data['rub_amount'] = $amount;
			
			return $res = $this->request('createExOrder', $data, 1);
		}

		function get_status($order_id) {
			
			$data = array();
			$data['custom_id'] = $order_id;
			$data['mode'] = 'group';
			$data['status'] = 'All';
			
			return $res = $this->request('getExchangeOrdersSeller', $data);
		}		
		
		function request($method, $data = array(), $is_post = 0) {

			$is_post = intval($is_post);

			$headers = array(
				'Authorization: Bearer ' . $this->token
			);
			
			$json_data = '';
			$url = 'https://bitconce.top/api/' . $method . '/';
			if (!$is_post and is_array($data) and count($data) > 0) {
				$url .= '?' . http_build_query($data);
			}

			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, $url);
				if (is_array($data) and $is_post) {
					$json_data = $data;
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				}
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
				
				do_action('save_merchant_error', $this->m_name, $this->m_id, $url, $headers, $json_data, $result, $err);
				
				$res = @json_decode($result, true);
		
				return $res;				 
			}
			
			return '';
		}
	}
}