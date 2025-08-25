<?php

/*
https://honeymoney-1.gitbook.io/rukovodstvo-merchanta
*/

if (!class_exists('M_SUPERMONEY')) {
    class M_SUPERMONEY {

        private $m_name = "";
        private $m_id = "";
        private $base_url = "";
        private $main_url = "https://api-v2.moneyhoney.io";
        private $auth_url = "https://identity.moneyhoney.io";
        private $client_id = "";
        private $client_secret = "";
        private $sign_secret = "";
        private $token = "";

        function __construct($m_name, $m_id, $client_id, $client_secret, $sign_secret) {

            $this->m_name = trim($m_name);
            $this->m_id = trim($m_id);
            $this->base_url = $this->main_url;
            $this->client_id = trim($client_id);
            $this->client_secret = trim($client_secret);
            $this->sign_secret = trim($sign_secret);
            $this->set_token();

        }

        function transaction_card($data) {
			
            return $this->request('/v2/merchant/transactions', $data, 1);
        }

        function transaction_sbp($data) {
			
            return $this->request('/v2/merchant/transactions/sbp', $data, 1);
        }

        function transaction_account($data) {
			
            return $this->request('/v2/merchant/transactions/account', $data, 1);
        }

        function get_transaction($id) {
			
            return $this->request('/v2/merchant/transactions/' . $id);
        }

        function get_transactions($data = array()) {
			
            $data = array_merge(array(
                'type' => 'DEPOSIT',
                'pageSize' => 1000,
            ), $data);

            $r = $this->request('/v2/merchant/transactions', $data);

            $history = array();

            if (isset($r['items']) and is_array($r['items']) and count($r['items'])) {
                $keys = array_column($r['items'], 'id');
                $history = array_combine($keys, $r['items']);
            }

            return $history;
        }

        function banks($data = array()) {
			
            $data = array_merge(array(
                'pageSize' => 999999,
            ), $data);

            return $this->request('/v2/merchant/banks', $data);
        }

        private function set_token() {
			
            if ($this->token) {
                return $this->token;
            }

            $this->base_url = $this->auth_url;

            $data = array(
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => 'client_credentials',
            );

            $r = $this->request('/realms/honeymoney/protocol/openid-connect/token', $data, 1);

            $this->base_url = $this->main_url;

            if (isset($r['access_token'])) {
                $this->token = trim($r['access_token']);
            }

            return $this->token;
        }

        private function get_sign($url, $request_json, $secret) {
			
            $signatureString = pn_json_encode($request_json) . parse_url($url, PHP_URL_PATH) . parse_url($url, PHP_URL_QUERY);
			
            return hash_hmac('sha256', $signatureString, $secret);
        }

        private function request($method, $data = array(), $is_post = 0) {

            $is_post = intval($is_post);

            $headers = array(
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer " . $this->token,
            );

            if (empty($this->token)) {
                $headers = array(
                    "Content-Type: application/x-www-form-urlencoded",
                );
            }

            $url = $this->base_url . $method;
            if (!$is_post and is_array($data) and count($data) > 0) {
                $url .= '?' . http_build_query($data);
            }

            if (!empty($this->token)) {
                $headers[] = "X-Signature: " . $this->get_sign($url, $data, $this->sign_secret);
            }

            $json_data = '';

            if ($ch = curl_init()) {

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
                curl_setopt($ch, CURLOPT_URL, $url);

                if ($is_post and is_array($data)) {
                    $json_data = empty($this->token) ? http_build_query($data) : pn_json_encode($data);
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
