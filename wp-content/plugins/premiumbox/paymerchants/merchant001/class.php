<?php

/*
https://app.merchant001.io/merchant/api
*/

if (!class_exists('AP_MERCHANT001')) {
    class AP_MERCHANT001 {

        private $m_name = "";
        private $m_id = "";
        private $base_url = "https://api.merchant001.io";
        private $token = "";

        function __construct($m_name, $m_id, $token) {

            $this->m_name = trim($m_name);
            $this->m_id = trim($m_id);
            $this->token = trim($token);

        }

        function payment_methods() {

            return $this->request('/v2/payment-method/merchant/available-withdraw');
        }

        function get_balance() {

            $res = $this->request('/v1/transaction/merchant/balance');

            $balance = array();

            foreach ($res as $val) {
                if (isset($val['amount'], $val['currency']) and 'USDT' == $val['currency']) {
                    $balance = $val;
                    break;
                }
            }

            return $balance;
        }

        function send($data) {

            $trans = '';
            $res = $this->request('/v1/withdraw/merchant', $data, true);
            if (isset($res['id'])) {
                $trans = $res['id'];
            }
			
            return $trans;
        }

        function get_transaction($id) {

            return $this->request('/v1/transaction/merchant/' . $id);
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
                $ch = apply_filters('curl_ap', $ch, $this->m_name, $this->m_id);

                $err = curl_errno($ch);
                $result = curl_exec($ch);
                $info = curl_getinfo($ch);

                curl_close($ch);

                do_action('save_paymerchant_error', $this->m_name, $this->m_id, $url, $headers, $json_data, $result, $err);

                $res = @json_decode($result, true);

                return $res;
            }

            return '';
        }
    }
}