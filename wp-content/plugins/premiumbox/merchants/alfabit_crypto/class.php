<?php

/*
https://alfabit.gitbook.io/alfabit-pay/api-reference
*/

if (!class_exists('M_ALFABIT')) {
    class M_ALFABIT {

        private $m_name = "";
        private $m_id = "";
        private $base_url = "https://pay.alfabit.org";
        private $api_key = "";

        function __construct($m_name, $m_id, $api_key) {
			
            $this->m_name = trim($m_name);
            $this->m_id = trim($m_id);
            $this->api_key = trim($api_key);
			
        }

        function assets_currencies() {
			
            return $this->request('/api/v1/integration/assets/currencies');
        }

        function merchant() {
			
            return $this->request('/api/v1/integration/merchant');
        }

        function assets_exchangerate() {
			
            return $this->request('/api/v1/integration/assets/exchange-rate');
        }

        function invoice_wo_amount($data) {
			
            return $this->request('/api/v1/integration/orders/invoice/without-amount', $data, 1);
        }

        function order($uid) {
			
            return $this->request('/api/v1/integration/orders/' . $uid);
        }

        private function request($method, $data = array(), $is_post = 0) {
			
            $is_post = intval($is_post);

            $headers = array(
                "Accept: application/json",
                "Content-Type: application/json",
                "x-api-key: " . $this->api_key,
            );

            $url = $this->base_url . $method;
            if (!$is_post and is_array($data) and count($data) > 0) {
                $url .= '?' . http_build_query($data);
            }

            $json_data = '';

            if ($ch = curl_init()) {
				
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
                curl_setopt($ch, CURLOPT_URL, $url);

                if ($is_post and is_array($data)) {
                    $json_data = pn_json_encode($data);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
                }

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
                curl_setopt($ch, CURLOPT_ENCODING, '');
                $ch = apply_filters('curl_merch', $ch, $this->m_name, $this->m_id);

                $err = curl_errno($ch);
                $result = curl_exec($ch);
                $info = curl_getinfo($ch);

                curl_close($ch);

                do_action('save_merchant_error', $this->m_name, $this->m_id, $url, $headers, $json_data, $result, $err);

                $res = @json_decode($result, true);

                return $res;
            }

            return '';
        }
    }
}
