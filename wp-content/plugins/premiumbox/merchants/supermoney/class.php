<?php
if (!defined('ABSPATH')) exit();

/*
https://honeymoney-1.gitbook.io/rukovodstvo-merchanta
*/

if (!class_exists('M_SUPERMONEY')) {
    class M_SUPERMONEY {

        private $m_name = "";
        private $m_id = "";
        private $base_url = "https://api-v2.moneyhoney.io";
        private $token = "";
        private $sign_secret = "";

        function __construct($m_name, $m_id, $BASE_URL, $token, $sign_secret) {
            $base_url = trim($BASE_URL);
            $this->base_url = $base_url ? rtrim((!str_contains($base_url, '://') ? 'https://' : '') . $base_url, '/') : $this->base_url;

            $this->m_name = trim($m_name);
            $this->m_id = trim($m_id);
            $this->token = trim($token);
            $this->sign_secret = trim($sign_secret);

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

        private function get_sign($url, $request_json, $secret, $is_post) {

            $q = parse_url($url, PHP_URL_QUERY);
            $signatureString = ($is_post && $request_json ? pn_json_encode($request_json) : '') . parse_url($url, PHP_URL_PATH) . ($q ? "?{$q}" : '');
            return hash_hmac('sha256', $signatureString, $secret);
        }

        private function request($method, $data = array(), $is_post = 0) {

            $is_post = intval($is_post);

            $headers = array(
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer " . $this->token,
            );

            $url = $this->base_url . $method;
            if (!$is_post and is_array($data) and count($data) > 0) {
                $url .= '?' . http_build_query($data);
            }

            if (!empty($this->token)) {
                $headers[] = "X-Signature: " . $this->get_sign($url, $data, $this->sign_secret, $is_post);
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
