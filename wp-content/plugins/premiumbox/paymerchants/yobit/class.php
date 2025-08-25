<?php
/*
https://yobit.net/ru/api/
*/

if (!class_exists('AP_Yobit')) {
	class AP_Yobit {
		
		private $m_name = '';
		private $m_id = '';
		private $api_key = '';
		private $api_secret = '';
		private $host = 'https://yobit.net/tapipe/';

		function __construct($m_name, $m_id, $api_key, $api_secret) {
			
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->api_key = trim($api_key);	
			$this->api_secret = trim($api_secret);
			
		}		
		
		/*
		add - комиссия добавляется сверху к указанной сумме amount (т.е с пользователя будет списано (amount+fee), а отправлено ровно amount)
		sub - комиссия вычитается из указанной суммы amount (т.е с пользователя будет списано (amount),а отправлено (amount-fee) )
		*/		
		
		function create_payout($currency_code, $address, $amount, $feemode) {
			
			$currency_code = trim($currency_code);
			$address = trim($address);
			$data = array(
				'coinName' => $currency_code,
				'address' => $address,
				'amount' => $amount,
				'feemode' => $feemode,
			);		
			$res = $this->request('WithdrawCoinsToAddress', $data);
			$trans_id = '0';
			if (isset($res['success'], $res['wid']) and $res['wid'] and $res['success']) {
				$trans_id = pn_strip_input($res['wid']);
			}
			
			return $trans_id;		
		}		
		
		function get_payout_info($currency_code, $wid) {
			
			$data = array();
			$data['coinName'] = $currency_code;
			$data['wid'] = $wid;
			$res = $this->request('GetWithdrawalInfo', $data);
			if (isset($res['success'], $res['return'], $res['return']['transaction']) and is_array($res['return']['transaction']) and $res['success']) {
				return $res['return']['transaction'];
			}
			
			return array();			
		}
		
		function get_balance() {
			
			$balance = array();
			$data = array();
			$res = $this->request('getInfo', $data);
			if (isset($res['success'], $res['return'], $res['return']['funds_incl_orders']) and is_array($res['return']['funds_incl_orders']) and $res['success']) {
				foreach ($res['return']['funds_incl_orders'] as $currency => $amount) {
					$currency = strtolower(pn_strip_input($currency));
					$balance[$currency] = is_sum($amount);
				}
			}
			
			return $balance;		
		}		
		
		function list_currencies($api = 0) {
			
			$api = intval($api);
			$currencies = get_option('list_currencies_' . $this->m_name);
			if (!is_array($currencies)) { $currencies = array(); }
			
			if (1 == $api or count($currencies) < 1) {
				$currencies = array();
				$data = array();
				$res = $this->request('', $data, 'https://yobit.net/tapipe/info/');
				if (isset($res['currencies']) and is_array($res['currencies'])) {
					foreach ($res['currencies'] as $code => $d) {
						$code = strtolower(pn_strip_input($code));	
						$currencies[$code] = pn_strip_input(is_isset($d, 'fullname'));
					}
				}		
				update_option('list_currencies_' . $this->m_name, $currencies);
			}
			
			return $currencies;
		}
		
		function list_tcurrencies($api = 0) {
			
			$api = intval($api);
			$currencies = get_option('list_tcurrencies_' . $this->m_name);
			if (!is_array($currencies)) { $currencies = array(); }
			
			if (1 == $api or count($currencies) < 1) {
				$currencies = array();
				$data = array();
				$res = $this->request('', $data, 'https://yobit.net/tapipe/info/');
				if (isset($res['defi']) and is_array($res['defi'])) {
					foreach ($res['defi'] as $code => $d) {
						$code1 = strtoupper(pn_strip_input($d['ticker1']));	
						$code2 = strtoupper(pn_strip_input($d['ticker2']));
						$currencies[$code1] = $code1;
						$currencies[$code2] = $code2;
					}
				}		
				update_option('list_tcurrencies_' . $this->m_name, $currencies);
			}
			
			return $currencies;
		}		

		function defi($method, $data) {
			
			return $this->request($method, $data);		
		}		
		
		function info() {
			
			$data = array();
			
			return $res = $this->request('', $data, 'https://yobit.net/tapipe/info/');
		}		
		
		function request($method, $data = array(), $url = '') {

			$url = trim($url);
			if (!is_array($data)) { $data = array(); }
			
			if ($url) {
				$headers = array();
				$host = $url;
				$json_data = '';
			} else {
				$data['method'] = $method;
				
				$json_data = http_build_query($data, '', '&');
				$sign = hash_hmac("sha512", $json_data, $this->api_secret);

				$headers = array(
					"Key: " . $this->api_key,
					"Sign: " . $sign,
				);	

				$host = $this->host;
			}	
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, $host);
				if ($json_data) {
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
				}
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_TIMEOUT, 20);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
				curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
				curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
				$ch = apply_filters('curl_ap', $ch, $this->m_name, $this->m_id);
				
				$err  = curl_errno($ch);
				$result = curl_exec($ch);
				$info = curl_getinfo($ch);
				
				curl_close($ch);
				
				do_action('save_paymerchant_error', $this->m_name, $this->m_id, $host, $headers, $json_data, $result, $err);
				
				$res = @json_decode($result, true);
		
				return $res;
				
			}			
			
			return '';
		}
		
	}
}	