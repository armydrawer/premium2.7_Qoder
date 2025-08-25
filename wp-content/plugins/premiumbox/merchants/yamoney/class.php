<?php

if (!class_exists('YaMoney')) {
	class YaMoney
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
			
			$token = premium_decrypt(get_option('token_' . $this->m_id), EXT_SALT); 
			
			if (!$token) {
				$file = $premiumbox->plugin_dir . '/merchants/' . $this->m_name . '/dostup/access_token_' . $this->m_id . '.php';
				if (is_file($file)) {
					$token = @file_get_contents($file);
				}
			}
			
			if (!$token) {
				$file = $premiumbox->plugin_dir . '/merchants/' . $this->m_name . '/dostup/access_token.php';
				if (is_file($file)) {
					$token = @file_get_contents($file);
				}	
			}
			
			return trim($token);
		}
		
		function update_token($token) {
			
			$token = trim(esc_html(strip_tags($token)));
			$token = premium_encrypt($token, EXT_SALT);
			update_option('token_' . $this->m_id, $token);
			
		}	
		
		function info($token = '') {
			
			return $this->request('https://yoomoney.ru/api/account-info', array(), $token);
		}

		function operationHistory($sType = null, $sLabel = null, $sFromDate = null, $sTillDate = null, $iStartRecord = null, $iReconds = null, $bDetails = true) {
			
			return $this->request(
				'https://yoomoney.ru/api/operation-history', 
				array(
					'type' => $sType,
					'label' => $sLabel,
					'from' => $sFromDate,
					'till' => $sTillDate,
					'start_record' => $iStartRecord,
					'records' => $iReconds,
					'details' => $bDetails ? 'true' : 'false'
				)
			);
		}	
		
		function auth() {
			
			$code = is_param_get('code');
		
			$res = $this->request(
				'https://yoomoney.ru/oauth/token', 
				array(
					'code' => $code,
					'client_id' => $this->app_id,
					'grant_type' => 'authorization_code',
					'redirect_uri' => get_mlink($this->m_id . '_verify'),
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
			} elseif($this->token){
				$token = $this->token;
			}	

			$json_data = '';
			
			$headers = '';	
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, $url);
				if (is_array($post) and count($post) > 0) {
					$json_data = http_build_query($post);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
				}
				if ($token) {
					$headers = array(
						'Authorization: Bearer ' . $token
					);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				}
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