<?php

if (!class_exists('M_PAYSCROW')) {
    class M_PAYSCROW {

        private $m_name = "";
        private $m_id = "";
        private $base_url = "";
        private $api_key = "";
        private $secret_key = "";

        function __construct($m_name, $m_id, $base_url, $api_key, $secret_key) {

            $this->m_name = trim($m_name);
            $this->m_id = trim($m_id);

            $base_url = rtrim(trim($base_url), '/');

            $parse_url = parse_url($base_url);
            if (empty($parse_url['scheme'])) {
                $base_url = 'https://' . $base_url;
            }

            $this->base_url = $base_url;
            $this->api_key = trim($api_key);
            $this->secret_key = trim($secret_key);

        }

        function payment_methods() {
			
            return $this->request('/api/v1/Misc/ListPaymentMethods');
        }

        function create_order($data) {
			
            return $this->request('/api/v1/Orders/Create', $data, 1);
        }

        function create_smart_order($data) {
			
            return $this->request('/api/v1/Orders/SmartCreate', $data, 1);
        }

        function get_order($order_id) {
			
            $res = $this->request('/api/v1/Orders/GetByOrderId/' . $order_id);
			
            if (isset($res['order'], $res['order']['orderId'])) {
                return array($res['order']['orderId'] => $res['order']);
            }
			
            return array();
        }

        function get_orders($data = array()) {
			
            if (count($data)) {
                $res = $this->request('/api/v1/Orders/List', $data, 1);
            } else {
                $res = $this->request('/api/v1/Orders/List');
            }

            $orders = array();
			
            if (isset($res['orders'])) {
                foreach ($res['orders'] as $order) {
                    $orders[$order['orderId']] = $order;
                }
            }
			
            return $orders;
        }

        private function get_sign($method, $data = array(), $is_post = 0) {
			
            $sig_str = implode('/', array($method, $this->secret_key, $this->api_key));
			
            if ($is_post and is_array($data)) {
                $sig_str .= "\r\n" . pn_json_encode($data);
            }

            return hash('sha256', $sig_str);
        }

        private function request($method, $data = array(), $is_post = 0) {

            $is_post = intval($is_post);

            $headers = array(
                "Accept: application/json",
                "Content-Type: application/json",
                "X-API-Key: " . $this->api_key,
                "X-API-Sign: " . $this->get_sign($method, $data, $is_post),
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
