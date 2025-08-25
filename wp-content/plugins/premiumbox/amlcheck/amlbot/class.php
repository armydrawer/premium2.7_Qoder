<?php 

/*
https://www.notion.so/API-Docs-For-Web-services-ffc3cf6a90bc4c8fa1214a6e7f3becec
*/

if (!class_exists('AMLClass')) {
	class AMLClass {
		
		private $m_name = "";
		private $m_id = "";
		private $access_id = "";
		private $access_key = "";
		public $bid_id = 0;
		
		function __construct($m_name, $m_id, $bid_id, $access_id, $access_key) {
			
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->access_id = trim($access_id);
			$this->access_key = trim($access_key);
			$this->bid_id = intval($bid_id);
			
		}

		function verify_trans($address, $currency, $trans_id, $type = 0) {
			
			$type = intval($type);
			
			$data = array();
			$data['accessId'] = $this->access_id;
			$data['locale'] = 'en_US';
			$data['hash'] = $trans_id;
			$data['address'] = $address;
			if (1 == $type) {
				$data['direction'] = 'withdrawal';
			} else {
				$data['direction'] = 'deposit';
			}
			$data['asset'] = $currency;
			
			$res = $this->request('https://reserveapi.silencatech.com', $data, $data['hash']);
			
			return $res;
		}

		function verify_address($address, $currency) {
			
			$data = array();
			$data['accessId'] = $this->access_id;
			$data['locale'] = 'en_US';
			$data['hash'] = $address;
			$data['asset'] = $currency;
			
			$res = $this->request('https://reserveapi.silencatech.com', $data, $data['hash']);
			
			return $res;
		}

		function check_uid($uid) {
			
			$data = array();
			$data['accessId'] = $this->access_id;
			$data['locale'] = 'en_US';
			$data['uid'] = $uid;
			
			$res = $this->request('https://reserveapi.silencatech.com/recheck', $data, $data['uid']);
			
			return $res;
		}		
		
		function request($url, $data = '', $hash = '') {

            $headers = array();
			
			$json_data = '';

            if ($ch = curl_init()) {
				
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'PremiumExchanger/2.7.0');
                curl_setopt($ch, CURLOPT_URL, $url);

                if (is_array($data)) {
					$data['token'] = md5($hash . ':' . $this->access_key . ':' . $this->access_id);
					$json_data = http_build_query($data);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
                }

				if (count($headers) > 0) {
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				}
				
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
                curl_setopt($ch, CURLOPT_ENCODING, '');
                $ch = apply_filters('curl_amlcheck', $ch, $this->m_name, $this->m_id);

                $err = curl_errno($ch);
                $result = curl_exec($ch);
                $info = curl_getinfo($ch);

                curl_close($ch);

                do_action('save_amlcheck_error', $this->m_name, $this->m_id, $url, $headers, $json_data, $result, $err, $this->bid_id);

                $res = @json_decode($result, true);

                return $res;
            }

			return '';
		}
	}
}