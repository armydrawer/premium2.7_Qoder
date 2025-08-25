<?php 

/*
https://kyt-api.bitok.org/swagger/
https://docs.google.com/document/d/14SEHYUFWHBWwk09FoVdVFAmJp2MIJ3DfVA_klAT2MSw/edit#heading=h.tccqs58leuwa
*/

if (!class_exists('BitokAML')) {
	class BitokAML {
		
		private $m_name = "";
		private $m_id = "";
		private $api_key = "";
		private $api_secret = "";
		private $api_url = "https://kyt-api.bitok.org/v1/";
		public $bid_id = 0;
		
		function __construct($m_name, $m_id, $bid_id, $api_key, $api_secret) {
			
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->api_key = trim($api_key);
			$this->api_secret = trim($api_secret);
			$this->bid_id = intval($bid_id);
			
		}

		function get_risk($risk) {
			
			if ('low' == $risk) {
				return '25';
			} elseif ('medium' == $risk) {
				return '50';
			} elseif ('high' == $risk) {
				return '75';
			} elseif ('severe' == $risk) {
				return '100';
			}
			
			return '0';
		}

		function check_risk($id) {
			
			$return = array(
				'status' => 'noapi',
				'risk' => '0',
			);
			
			$res = $this->request('/manual-checks/' . $id . '/', '');
			if (isset($res['check_status'])) {
				$return['status'] = pn_strip_input($res['check_status']);
				if (isset($res['risk_score'])) {
					$return['risk'] = is_sum($res['risk_score'] * 100);
				} elseif (isset($res['risk_level'])) {
					$return['risk'] = $this->get_risk($res['risk_level']);
				} elseif (isset($res['address']['risk_level'])) {
					$return['risk'] = $this->get_risk($res['address']['risk_level']);
				}
			}
			
			return $return;
		}

		function check_trans($id) {
			
			$return = array();
			$res = $this->request('/manual-checks/' . $id . '/transfer-exposure/', '');
			if (isset($res['direct_interaction'], $res['direct_interaction']['entity_category']) and $res['direct_interaction']['entity_category']) {
				$entity_category = pn_strip_input($res['direct_interaction']['entity_category']);
				$return[$entity_category] = '1';
			} elseif (isset($res['indirect_interaction']) and is_array($res['indirect_interaction'])) {
				foreach ($res['indirect_interaction'] as $d) {
					$entity_category = pn_strip_input(is_isset($d, 'entity_category'));
					$return[$entity_category] = is_sum(is_isset($d, 'value_share'));
				}
			}
			
			return $return;
		}

		function check_addr($id) {
			
			$return = array();
			$res = $this->request('/manual-checks/' . $id . '/address-exposure/', '');
			if (isset($res['entity_category']) and $res['entity_category']) {
				$entity_category = pn_strip_input($res['entity_category']);
				$return[$entity_category] = '1';
			} elseif (isset($res['exposure']) and is_array($res['exposure'])) {
				foreach ($res['exposure'] as $d) {
					$entity_category = pn_strip_input(is_isset($d, 'entity_category'));
					$return[$entity_category] = is_sum(is_isset($d, 'value_share'));
				}
			}
			
			return $return;
		}

		function verify_trans($network, $token_id, $tx_hash, $output_address) {
			
			$data = array();
			$data['network'] = $network;
			$data['token_id'] = $token_id;
			$data['tx_hash'] = $tx_hash;
			$data['output_address'] = $output_address;
			$data['direction'] = 'incoming';
			
			$res = $this->request('/manual-checks/check-transfer/', $data);
			
			return $res;
		}

		function verify_addr($network, $token_id, $address) {
			
			$data = array();
			$data['network'] = $network;
			$data['token_id'] = $token_id;
			$data['address'] = $address;
			
			$res = $this->request('/manual-checks/check-address/', $data);
			
			return $res;
		}		
		
		function request($method, $post = array()) {

			$url = $this->api_url . ltrim($method, '/');
			$parse_url = parse_url($url);
			$endpoint = trim(is_isset($parse_url, 'path'));

			if (!is_array($post)) { $post = array(); }
			
			$timestamp = current_time('U') . '000';
			
			$json_data = '';
			
			$signature = 'GET' . "\n";
			$signature .= $endpoint . "\n";
			$signature .= $timestamp;
			
			if (count($post) > 0) {
				$signature = 'POST' . "\n";
				$signature .= $endpoint . "\n";
				$signature .= $timestamp . "\n";
				$json_data = json_encode($post);
				$signature .= $json_data; 
			}
			
			$sign = base64_encode(hash_hmac('sha256', $signature, $this->api_secret, true));

			$headers = array(
				"Content-Type: application/json",
				"Accept: application/json",
				"API-KEY-ID: " . $this->api_key,
				"API-TIMESTAMP: " . $timestamp,
				"API-SIGNATURE: " . $sign,
			);		
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'PremiumExchanger/2.7.0');
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				
				if (count($post) > 0) {
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
				$ch = apply_filters('curl_amlcheck', $ch, $this->m_name, $this->m_id);
				
				$err  = curl_errno($ch);
				$result = curl_exec($ch);
				$info = curl_getinfo($ch);
				
				curl_close($ch);
				
				$res = @json_decode($result, true);
				
				do_action('save_amlcheck_error', $this->m_name, $this->m_id, $url, $headers, $json_data, $result, $err, $this->bid_id);
		
				return $res;
			}			
			
			return '';
		}
	}
}