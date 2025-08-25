<?php

/*
https://api.koshelek.ru/, https://api.koshelek.ru/swagger/
*/

if (!class_exists('M_KOSHELEK')) {
    class M_KOSHELEK {

        private $m_name = "";
        private $m_id = "";
        private $base_url = "https://api.koshelek.ru";
        private $public_key = "";
        private $secret_key = "";

        function __construct($m_name, $m_id, $public_key, $secret_key) {
			
            $this->m_name = trim($m_name);
            $this->m_id = trim($m_id);
            $this->public_key = trim($public_key);
            $this->secret_key = trim($secret_key);
			
        }

        function market_currencies() {
			
            return $this->request('/api/v1/market/currencies');
        }

        function create_address($data) {
			
            return $this->request('/api/v1/account/address', $data);
        }

        function get_transactions() {
            /*
            transactionStatus =
            AwaitingConfirmation = 0,
            Processing = 1,
            Completed = 2,
            Cancelled = 3,
            Declined = 4,
            AdminCheck = 5,
            InQueue = 7,
            AwaitingManualCheck = 8,
            AwaitingUserConfirmation = 9,
            AdminProcessing = 10,
            NeedContactSupport = 11.
            */

            $trans = array();
            $res = $this->request('/api/v1/account/transactions', array('Limit' => 1000));

            if (isset($res['transactionList']) and is_array($res['transactionList'])) {
                foreach ($res['transactionList'] as $item) {
                    if (0 != $item['transactionType']) {
                        continue;
                    }

                    $trans[is_isset($item, 'id')] = $item;
                }
            }

            return $trans;
        }

        private function request($method, $data = array(), $is_post = 0) {
			
            $is_post = intval($is_post);

            $headers = array(
                "Accept: application/json",
                "Content-Type: application/json",
                "X-ClientId: " . $this->public_key,
            );

            $url = $this->base_url . $method . '?signature=' . hash_hmac('sha256', http_build_query($data, '', '&'), $this->secret_key);
            if (count($data) > 0) {
                $url .= '&' . http_build_query($data);
            }

            $json_data = '';

            if ($ch = curl_init()) {
				
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
                curl_setopt($ch, CURLOPT_URL, $url);

                if ($is_post and is_array($data)) {
                    curl_setopt($ch, CURLOPT_POST, true);
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
