<?php
/*
https://epaycoreru.docs.apiary.io/#
*/

if (!class_exists('EpayCore')) {
	class EpayCore {
		
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

		function get_info($batch) {
			
			$batch = trim($batch);
			
			$data = array(
				'batch' => $batch,
			);
			$res = $this->request('/v1/info', $data);
			if (isset($res[0])) {
				return $res[0];
			}
			
			return '';
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