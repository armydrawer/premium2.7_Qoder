<?php

/*
https://docs.exnode.ru/#introduction
*/

if (!class_exists('Exnode')) {
    class Exnode {

        private $m_name = '';
        private $m_id = '';
        private $private_key = '';
        private $public_key = '';

        function __construct($m_name, $m_id, $private_key, $public_key) {

            $this->m_name = trim($m_name);
            $this->m_id = trim($m_id);
            $this->private_key = trim($private_key);
            $this->public_key = trim($public_key);

        }

        function get_status($tracker_id) {

            $info = array();
            $data = array();
            $data['tracker_id'] = $tracker_id;
            $res = $this->request('/api/transaction/get', $data);
            if (isset($res['transaction']) and is_array($res['transaction'])) {
                return $res['transaction'];
            }

            return $info;
        }

        function create_payout($currency_code, $sum, $client_transaction_id, $call_back_url, $receiver) {

            $currency_code = trim($currency_code);
            $client_transaction_id = trim($client_transaction_id);
            $call_back_url = trim($call_back_url);
            $receiver = trim($receiver);
            $data = array(
                'token' => $currency_code,
                'amount' => floatval($sum),
                'client_transaction_id' => $client_transaction_id,
                'receiver' => $receiver,
            );
            if ($call_back_url) {
                $data['call_back_url'] = $call_back_url;
            }

            return $res = $this->request('/api/transaction/create/out', $data);
        }

        function create_invoice($currency_code, $trans_id, $call_back_url, $client_id, $transaction_description, $address_type) {

            $currency_code = trim($currency_code);
            $trans_id = trim($trans_id);
            $call_back_url = trim($call_back_url);
            $client_id = trim($client_id);
            $transaction_description = trim($transaction_description);
            $data = array(
                'token' => $currency_code,
                'client_transaction_id' => $trans_id,
            );
            if ($call_back_url) {
                $data['call_back_url'] = $call_back_url;
            }
            if ($client_id) {
                $data['client_merchant_id'] = $client_id;
            }
            if ($transaction_description) {
                $data['transaction_description'] = $transaction_description;
            }
            if ($address_type) {
                $data['address_type'] = $address_type;
            }

            return $res = $this->request('/api/transaction/create/in', $data);
        }

        function get_balance($currency) {

            $currency = trim($currency);
            $balance = '0';
            $data = array();
            $data['token'] = $currency;
            $res = $this->request('/api/token/balance', $data);
            if (isset($res['balance'], $res['balance']['value'])) {
                $balance = is_sum($res['balance']['value']);
            }

            return $balance;
        }

        function list_currencies($api = 0) {

            $api = intval($api);
            $currencies = get_option('list_currencies_' . $this->m_name);
            if (!is_array($currencies)) {
                $currencies = array();
            }

            if (1 == $api or count($currencies) < 1) {
                $currencies = array();
                $res = $this->request('/user/token/fetch', array());
                if (isset($res['tokens']) and is_array($res['tokens'])) {
                    foreach ($res['tokens'] as $l) {
                        $code = pn_strip_input($l);
                        $currencies[$code] = $code;
                    }
                }
                $currencies = pn_strip_input_array($currencies);
                update_option('list_currencies_' . $this->m_name, $currencies);
            }

            return $currencies;
        }

        function request($method, $data = array()) {

            $url = 'https://my.exnode.io' . $method;

            if (!is_array($data)) {
                $data = array();
            }

            $json_data = '';

            $timestamp = time();

            $headers = array(
                "Content-Type: application/json",
                "ApiPublic: " . $this->public_key,
                "TimeStamp: " . $timestamp,
            );

            if ($ch = curl_init()) {

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
                curl_setopt($ch, CURLOPT_URL, $url);

                if (count($data) > 0) {
                    $json_data = json_encode($data);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
                    $sign = hash_hmac('sha512', $timestamp . $json_data, $this->private_key);
                    $headers[] = "Signature: " . $sign;
                } else {
                    $json_data = '';
                    $sign = hash_hmac('sha512', $timestamp . $json_data, $this->private_key);
                    $headers[] = "Signature: " . $sign;
                }

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 50);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 50);
                curl_setopt($ch, CURLOPT_ENCODING, '');
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
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