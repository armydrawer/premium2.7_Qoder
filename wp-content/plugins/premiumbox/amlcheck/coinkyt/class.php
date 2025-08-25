<?php 

/*
https://explorer.coinkyt.com/openapi/docs
*/

if (!class_exists('CoinKytAML')) {
	class CoinKytAML {
		
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
		
		function check_addr($address, $token, $bchain) {
			
			$get = array(
				'token' => $token,
				'blockchain' => $bchain,
				'address' => $address,
			);
			$return = array();
			$res = $this->request('address', $get);
			if (isset($res['risk_score'])) {
				
				$return['risk'] = is_sum($res['risk_score'] * 100);
				$risks = array();
				
				if (isset($res['directs']) and is_array($res['directs']) and count($res['directs'])) {
					foreach ($res['directs'] as $d) {
						if (isset($d['type_label'])) {
							$entity_category = str_replace(' ', '_', strtolower(pn_strip_input(is_isset($d, 'type_label'))));
							$total_count = is_sum(is_isset($d, 'total_count'));
							if ($total_count > 0) {
								$total_count = is_sum($total_count / 100);
							}
							$risks[$entity_category] = $total_count;
						}
					}
				}				
				
				if (isset($res['indirects']) and is_array($res['indirects']) and count($res['indirects'])) {
					$risks = array();
					foreach ($res['indirects'] as $d) {
						if (isset($d['type_label'])) {
							$entity_category = str_replace(' ', '_', strtolower(pn_strip_input(is_isset($d, 'type_label'))));
							$total_count = is_sum(is_isset($d, 'total_count'));
							if ($total_count > 0) {
								$total_count = is_sum($total_count / 100);
							}
							$risks[$entity_category] = $total_count;
						}
					}
				}
				
				$return['risks'] = $risks;

			} elseif(isset($res['detail']) and 'Empty address' == $res['detail']) {
				
				$return['risk'] = 0;
				$return['risks'] = array();
				
			}
			
			return $return;
		}

		function check_trans($transaction_id, $token, $bchain) {
			
			$get = array(
				'token' => $token,
				'blockchain' => $bchain,
				'transaction' => $transaction_id,
			);		
			$return = array();
			$res = $this->request('transaction', $get);
			if (isset($res['risk_score'])) {
				
				$return['risk'] = is_sum($res['risk_score'] * 100);
				$risks = array();
				
				if (isset($res['directs']) and is_array($res['directs']) and count($res['directs'])) {
					foreach ($res['directs'] as $d) {
						if (isset($d['type_label'])) {
							$entity_category = str_replace(' ', '_', strtolower(pn_strip_input(is_isset($d, 'type_label'))));
							$total_count = is_sum(is_isset($d, 'total_count'));
							if ($total_count > 0) {
								$total_count = is_sum($total_count / 100);
							}
							$risks[$entity_category] = $total_count;
						}
					}
				}				
				
				if (isset($res['indirects']) and is_array($res['indirects']) and count($res['indirects'])) {
					$risks = array();
					foreach ($res['indirects'] as $d) {
						if (isset($d['type_label'])) {
							$entity_category = str_replace(' ', '_', strtolower(pn_strip_input(is_isset($d, 'type_label'))));
							$total_count = is_sum(is_isset($d, 'total_count'));
							if ($total_count > 0) {
								$total_count = is_sum($total_count / 100);
							}
							$risks[$entity_category] = $total_count;
						}
					}
				}
				
				$return['risks'] = $risks;

			} elseif(isset($res['detail']) and 'Empty address' == $res['detail']) {
				
				$return['risk'] = 0;
				$return['risks'] = array();
				
			}
			
			return $return;
		}		
		
		function request($method, $get = array()) {

			$url = 'https://explorer.coinkyt.com/openapi/v1/' . trim($method, '/');
			if (is_array($get) and count($get) > 0) {
				$url .= '?' . http_build_query($get);
			}

			$headers = array(
				"Accept: application/json",
				"X-API-Key: " . $this->api_key,
			);	

			$json_data = '';
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'PremiumExchanger/2.7.0');
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);	
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_TIMEOUT, 20);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
				curl_setopt($ch, CURLOPT_ENCODING, '');
				curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
				$ch = apply_filters('curl_amlcheck', $ch, $this->m_name, $this->m_id);
				
				$err  = curl_errno($ch);
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