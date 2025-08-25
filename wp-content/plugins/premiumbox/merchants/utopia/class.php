<?php

if (!class_exists('Utopia')) {
    class Utopia {

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

        function create_address() {

            $data = array();
            $res = $this->request('requestNewPublicKeyPaymentAlias', $data);
            $address = '';
            if (isset($res['result'], $res['result']['request_id'])) {
                $request_id = $res['result']['request_id'];
                $r = 0;
                while ($r++ < 5) {
                    usleep(1 * 1000000);
                    $data = array(
                        'requestId' => $request_id,
                    );
                    $res2 = $this->request('getMyPublicKeyPaymentAliases', $data);
                    if (isset($res2['result'][0]['alias'])) {
                        $address = $res2['result'][0]['alias'];
                        break;
                    }
                }
            }

            return $address;
        }

        function check_transaction($alias, $currency) {
			
            $currency = in_array($currency, ['USD', 'UUSD']) ? 'USD' : $currency;

            $data = array(
                'filters' => 'INCOMING_TRANSFERS',
                'destinationPk' => $alias,
                'currency' => $currency,
            );
            $trans = array();
            $res = $this->request('getFinanceHistory', $data);
            if (isset($res['result'], $res['result'][0])) {
                $trans = $res['result'][0];
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
                $ch = apply_filters('curl_merch', $ch, $this->m_name, $this->m_id);

                $err = curl_errno($ch);
                $result = curl_exec($ch);

                curl_close($ch);

                do_action('save_merchant_error', $this->m_name, $this->m_id, $url, $headers, $json_data, $result, $err);

                $res = @json_decode($result, true);

                return $res;

            }

            return '';
        }

    }
}