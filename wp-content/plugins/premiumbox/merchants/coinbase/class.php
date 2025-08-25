<?php

/* 
https://commerce.coinbase.com/docs/api/ 
*/

if (!class_exists('CoinBase')) {
	class CoinBase {
		
		private $m_name = "";
		private $m_id = "";
		private $api_key = "";
		private $api_secret="";

		function __construct($m_name, $m_id, $api_key, $api_secret) {
			
			$this->m_name = trim($m_name);
			$this->m_id = trim($m_id);
			$this->api_key = trim($api_key);
			$this->api_secret = trim($api_secret);
			
		}
		
		/*
		$api_key - ключ API
		$name - название платежа(видно платильщику на странице оплаты)
		$description - описание платежа(видно платильщику на странице оплаты)
		$pricing_type - тип цены(no_price - любая цена, fixed_price - фиксированная цена)
		$local_price - массив с ценами в формате array("amount"=>"100.00", "currency"=>"USD"), где amount - число, currency - тип валюты(USD, BTC)
		$metadata - массив дополнительных полей в формате array("key"=>"value"...), где key - ключ, value - значение
		$redirect_url - ссылка для редиректа при оплате
		$cancel_url - ссылка для редиректа при отмене

		ответ:
		Ссылка для оплаты или false в случае ошибки
		*/	
		
		function add_link($name, $description, $pricing_type, $local_price = '', $metadata = '', $redirect_url = '', $cancel_url = '') {
			
			$name = trim($name);
			$description = trim($description);
			$pricing_type = trim($pricing_type);
			$redirect_url = trim($redirect_url);
			$cancel_url = trim($cancel_url);
			
			$post = array(
				'name' => $name,
				'description' => $description,
				'pricing_type' => $pricing_type
			);
			
			if (is_array($local_price)) {
				$post['local_price'] = $local_price;
			}
			
			if (is_array($metadata)) {
				$post['metadata'] = $metadata;
			}
			
			if ($redirect_url) {
				$post['redirect_url'] = $redirect_url;
			}
			
			if ($cancel_url) {
				$post['cancel_url'] = $cancel_url;
			}
			
			$headers = array(
				'Content-Type: application/json',
				'X-CC-Api-Key: ' . $this->api_key,
				'X-CC-Version: 2018-03-22'
			);
			
			$res = '';
			
			$json_data = json_encode($post);
			
			if ($ch = curl_init()) {
				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
				curl_setopt($ch, CURLOPT_URL, 'https://api.commerce.coinbase.com/charges');
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
				
				do_action('save_merchant_error', $this->m_name, $this->m_id, 'https://api.commerce.coinbase.com/charges', $headers, $json_data, $result, $err);
				
				$res = @json_decode($result, true);			 
			}			
			
			if (isset($res['data']) and isset($res['data']['hosted_url'])) {
				return $res['data']['hosted_url'];
			}
			
			return '';		
		}	
	}
}