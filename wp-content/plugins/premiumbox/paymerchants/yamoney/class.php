<?php

if (!class_exists('AP_YaMoney')) {
	class AP_YaMoney
	{
		
		public $token;
		public $app_id;
		public $app_key;
		public $m_name;
		public $m_id;
		
		function __construct($m_name, $m_id, $app_id, $app_key)
		{
			$this->app_id = trim($app_id);
			$this->app_key = trim($app_key);
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->token = $this->get_token();	
		}		
	
		function get_token() {
			global $premiumbox;
			
			$token = premium_decrypt(get_option('token_ap_' . $this->m_id), EXT_SALT);
			
			if (!$token) {
				$file = $premiumbox->plugin_dir . '/paymerchants/' . $this->m_name . '/dostup/access_token_' . $this->m_id . '.php'; 
				if (is_file($file)) {
					$token = @file_get_contents($file);
				}
			}
			
			if (!$token) {
				$file = $premiumbox->plugin_dir . '/paymerchants/' . $this->m_name . '/dostup/access_token.php';
				if (is_file($file)) {
					$token = @file_get_contents($file);
				}	
			}
			
			return trim($token);
		}

		function update_token($token) {
			
			$token = trim(esc_html(strip_tags($token)));
			$token = premium_encrypt($token, EXT_SALT);
			update_option('token_ap_' . $this->m_id, $token);
			
		}				
	
		function info($token = '') {
			
			return $this->request('https://yoomoney.ru/api/account-info', array(), $token);
		}	
	
		function get_card_key($account) {
			
			$account = trim((string)$account);
			$card_key = '';
			
			$post = array(
				'skr_destinationCardNumber' => $account,
				'skr_successUrl' => '',
				'skr_errorUrl' => ''
			);			
			
			$post_data = http_build_query($post);
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, 'https://paymentcard.yoomoney.ru/gates/card/storeCard');
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
				// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
				curl_setopt($ch, CURLOPT_HEADER, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 20);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
				$ch = apply_filters('curl_ap', $ch, $this->m_name, $this->m_id);
				
				$err  = curl_errno($ch);
				$result = curl_exec($ch);
				
				curl_close($ch);
				
				do_action('save_paymerchant_error', $this->m_name, $this->m_id, 'https://paymentcard.yoomoney.ru/gates/card/storeCard', '', $post_data, $result, $err);
				
				if (preg_match('/Location: (.+)/i', $result, $loc) and $loc = preg_replace('/.*\?/', '', $loc[1])) {
					parse_str($loc, $locd);
					if (!empty($locd['skr_destinationCardSynonim'])) {
						$card_key = $locd['skr_destinationCardSynonim'];
					}
				}								 
			}		
			
			return $card_key;
		}
	
		function addPay($purse, $sum, $pay_type = 2, $comment = '', $label = '') {
			
			$array = array(
				'pattern_id' => 'p2p',
				'to' => $purse,
				'comment' => $comment,
				'message' => $comment,
				'label' => $label,
			);
			
			if (1 == $pay_type) { //Сумма к оплате (столько заплатит отправитель)
				$array['amount'] = $sum;
			} elseif (2 == $pay_type) { //Сумма к получению (придет на счет получателя счет после оплаты)
				$array['amount_due'] = $sum;
			}				
			
			$res = $this->request( 
				'https://yoomoney.ru/api/request-payment', 
				$array
			);
			
			if (isset($res['request_id'])) {
				return $res['request_id'];
			}
			
			return 0;
		}

		function processPay($request_id) {
			
			$data = array();
			$data['error'] = 1;
			$data['payment_id'] = 0;
			
			$res = $this->request( 
				'https://yoomoney.ru/api/process-payment', 
				array(
					'request_id' => $request_id,
				)
			);
			
			if (isset($res['payment_id'])) {
				$data['error'] = 0;
				$data['payment_id'] = $res['payment_id'];
			}
			
			return $data;
		}	
	
		function requestPay($card_key, $amount, $pay_type = 2) {

			$pay_type = intval($pay_type);
			$card_key = trim($card_key);
		
			$array = array(
				"pattern_id" => "6686",
				"skr_destinationCardSynonim" => $card_key,
			);
			
			if (1 == $pay_type) { //Сумма к оплате (столько заплатит отправитель)
				$array['sum'] = $amount;
			} elseif (2 == $pay_type) { //Сумма к получению (придет на счет получателя счет после оплаты)
				$array['net_sum'] = $amount;
			}		
			
			$res = $this->request( 
				'https://yoomoney.ru/api/request-payment', 
				$array
			);
		
			if (isset($res['request_id'])) {
				return $res['request_id'];
			}
			
			return 0;	
		}
	
		function auth() {
			
			$code = is_param_get('code');
		
			$res = $this->request(
				'https://yoomoney.ru/oauth/token', 
				array(
					'code' => $code,
					'client_id' => $this->app_id,
					'grant_type' => 'authorization_code',
					'redirect_uri' => get_mlink('ap_' . $this->m_id . '_verify'),
					'client_secret' => $this->app_key,
				)
			);
			
			if (isset($res['access_token'])) {
				return $res['access_token'];
			}
			
			return '';
		}		
	
		function request($url, $post, $now_token = '') {
			
			$post = (array)$post;
			
			$token = '';
			if ($now_token) {
				$token = $now_token;
			} elseif ($this->token) {
				$token = $this->token;
			}		

			$post_data = '';
			
			$headers = array(
				'Authorization: Bearer ' . $token,
			);			
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, $url);
				if (is_array($post) and count($post) > 0) {
					$post_data = http_build_query($post);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
				}
				if ($token) {
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				}
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
				
				do_action('save_paymerchant_error', $this->m_name, $this->m_id, $url, $headers, $post_data, $result, $err);
				
				$res = @json_decode($result, true);
		
				return $res;				 
			}					
			
			return '';
		}	
	}
}