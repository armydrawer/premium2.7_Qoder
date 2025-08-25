<?php

/*
https://globalotc.io/api_doc/
*/

if (!class_exists('AP_OTC')) {
    class AP_OTC {

        private $m_name = "";
        private $m_id = "";
        private $api_key = "";
        private $secret_key = "";

        function __construct($m_name, $m_id, $api_key, $secret_key) {
            $this->m_name = trim($m_name);
            $this->m_id = trim($m_id);
            $this->api_key = trim($api_key);
            $this->secret_key = trim($secret_key);
        }

        function get_balance() {

            $data = array(
                'balance' => 1,
            );

            return $res = $this->request('get_balance', $data);
        }

        function send($order_id, $currencyFrom, $sum, $wallet, $bank = '', $bank_id = '', $card_data = '') {

            $currencyFrom = trim($currencyFrom);
            if (!is_array($card_data)) {
                $card_data = array();
            }
            $bank = trim($bank);
            $bank_id = trim($bank_id);
            $data = array(
                'currencyFrom' => $currencyFrom,
                'sum' => $sum * 1,
                'wallet' => $wallet,
                'orderUID' => trim($order_id),
            );
            if ($bank) {
                $data['bank'] = $bank;
            }
            if ($bank_id) {
                $data['bankSbpCode'] = $bank_id;
            }
            foreach ($card_data as $card_data_k => $card_data_v) {
                $data[$card_data_k] = $card_data_v;
            }
            if ('USDT' == $currencyFrom) {
                $data['sumInRub'] = $data['sum'];
                $data['sum'] = 1;
            }
            $trans = '0';
            $res = $this->request('create_withdraw', $data, 1);
            if (isset($res['status'], $res['orderID']) and $res['status'] and $res['orderID'] > 0) {
                $trans = intval($res['orderID']);
            }

            return $trans;
        }

        function get_transactions($id) {

            $id = trim($id);
            $data = array();
            $res = $this->request('fetch_order_by_uid/' . $id, $data);
            $trans = array();
            if (isset($res['data'])) {
                foreach ($res['data'] as $d) {
                    $trans[$d['uid']] = $d;
                }
            }

            return $trans;
        }

        function request($method, $data = array(), $sign = 0) {

            $sign = intval($sign);
            $json_data = '';

            $url = 'https://globalotc.io/api/v2/payment/' . $method;

            if ($ch = curl_init()) {

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
                curl_setopt($ch, CURLOPT_URL, $url);

                if (is_array($data) and count($data) > 0) {
                    $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
                }

                $headers = array(
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "Content-Length: " . strlen($json_data),
                    "Authorization: " . $this->api_key
                );

                if ($sign) {
                    $signature = hash_hmac('sha512', $data['sum'] . ';' . $data['wallet'] . ';' . $data['orderUID'] . ';', $this->secret_key);
                    $headers[] = "Signature: " . $signature;
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