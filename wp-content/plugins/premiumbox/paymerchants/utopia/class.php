<?php

if (!class_exists('AP_Utopia')) {
    class AP_Utopia {

        private $m_name = "";
        private $m_id = "";
        public $base_url = '';
        private $token = '';

        function __construct($m_name, $m_id, $domain, $token) {

            $this->m_name = trim($m_name);
            $this->m_id = trim($m_id);
            $base_url = trim($domain);
            $this->base_url = rtrim((parse_url($base_url, PHP_URL_SCHEME) ? '' : 'https://') . $base_url, '/') . '/api/1.0';
            $this->token = trim($token);

        }

        function get_balance($currency) {

            $data = array(
                'currency' => $currency,
            );
            $res = $this->request('getBalance', $data);
            if (isset($res['result'])) {
                return is_sum($res['result']);
            }

            return '0';
        }

        function send($to, $amount, $currency, $comment) {
			
            $currency = in_array($currency, ['USD', 'UUSD']) ? 'USD' : $currency;

            $data = array(
                'to' => $to,
                'currency' => $currency,
                'amount' => $amount,
                'cardid' => '',
            );
            $comment = trim($comment);
            if ($comment) {
                $data['comment'] = $comment;
            }

            $ref_id = '0';
            $res = $this->request('sendPayment', $data);
            if (isset($res['result']) and !strstr($res['result'], '{')) {
                $ref_id = $res['result'];
            }

            return $ref_id;
        }

        function check($ref_id) {

            $trans = '0';

            if ($ref_id) {
                $data = array(
                    'referenceNumber' => $ref_id,
                    'fullInfo' => "true",
                );
                $res = $this->request('getTransactionIdByReferenceNumber', $data);
                if (isset($res['result'])) {
                    $trans = $res['result'];
                }
            }

            return $trans;
        }

        function request($method, $params = array(), $extra_data = array()) {

            $data = array('token' => $this->token, 'method' => $method);

            if (is_array($params) and count($params) > 0) {
                $data['params'] = $params;
            }

            if (is_array($extra_data) and count($extra_data) > 0) {
                $data = array_merge($data, $extra_data);
            }

            $json_data = json_encode($data);

            $headers = array(
                "Content-Type: application/json; charset=utf-8",
                "Accept: application/json",
                "Content-Length: " . strlen($json_data),
            );

            $url = $this->base_url;

            if ($ch = curl_init()) {

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
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

                curl_close($ch);

                do_action('save_paymerchant_error', $this->m_name, $this->m_id, $url, $headers, $json_data, $result, $err);

                $res = @json_decode($result, true);

                return $res;

            }

            return '';
        }

    }
}
