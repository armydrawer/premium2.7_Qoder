<?php

/*
txt, https://u.is/docs/api.html
*/

if (!class_exists('M_UTOPIA_C')) {
    class M_UTOPIA_C {

        private $m_name = "";
        private $m_id = "";
        private $base_url = "";
        private $token = "";

        function __construct($m_name, $m_id, $domain, $token) {

            $this->m_name = trim($m_name);
            $this->m_id = trim($m_id);
            $base_url = trim($domain);
            $this->base_url = rtrim((parse_url($base_url, PHP_URL_SCHEME) ? '' : 'https://') . $base_url, '/') . '/api/1.0';
            $this->token = trim($token);

        }

        function useVoucher($id) {

            $data = array('voucherid' => $id);
            return $this->request('useVoucher', $data);
        }

        function getFinanceHistory($id) {

            $data = array('referenceNumber' => $id);
            $r = $this->request('getFinanceHistory', $data);

            return (isset($r['result'][0]) and is_array($r['result'])) ? $r['result'][0] : array();
        }

        private function request($method, $params = array()) {

            $headers = array(
                "Accept: application/json",
                "Content-Type: application/json",
            );

            $data = array(
                'method' => $method,
                'token' => $this->token,
                'params' => $params,
            );

            $url = $this->base_url;

            $json_data = pn_json_encode($data);

            if ($ch = curl_init()) {

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
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
