<?php

/*
PDF
*/

if (!class_exists('PAY_PAYSCROW_C')) {
    class PAY_PAYSCROW_C {

        private $m_name = "";
        private $m_id = "";
        private $base_url = 'https://api.payscrow-cascade.io';
        private $api_key = "";

        function __construct($m_name, $m_id, $api_key) {

            $this->m_name = trim($m_name);
            $this->m_id = trim($m_id);
            $this->api_key = trim($api_key);

        }

        function order($data) {

            return $this->request('/api/v1/order/', $data, 1);
        }

        function get_order($id) {

            return $this->request('/api/v1/order/' . $id);
        }

        function get_orders($data = array()) {

            $tz = new DateTimeZone('UTC');

            $data = array_merge(array(
                'size' => 100,
                'order_side' => 'Sell',
                'start_date' => (new DateTime('now', $tz))->modify('-1 day')->format('Y-m-d H:i:s'),
                'end_date' => (new DateTime('now', $tz))->format('Y-m-d H:i:s'),
            ), $data);

            $r = $this->request('/api/v1/order/', $data);

            $history = array();

            if (isset($r['orders']['items']) and is_array($r['orders']['items']) and count($r['orders']['items'])) {
                $keys = array_column($r['orders']['items'], 'id');
                $history = array_combine($keys, $r['orders']['items']);
            }

            return $history;
        }

        function balance() {

            return $this->request('/api/v1/finance/balance');
        }

        function payment_methods() {

            $r = $this->request('/api/v1/finance/payment_methods');

            $payment_methods = array();

            if (isset($r['payment_methods']) and is_array($r['payment_methods']) and count($r['payment_methods'])) {
                $keys = array_column($r['payment_methods'], 'method_id');
                $payment_methods = array_combine($keys, $r['payment_methods']);
            }

            return $payment_methods;
        }

        private function request($method, $data = array(), $is_post = 0) {

            $is_post = intval($is_post);

            $headers = array(
                "Accept: application/json",
                "Content-Type: application/json",
                "X-API-Key: " . $this->api_key,
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
