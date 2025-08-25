<?php
if (!defined('ABSPATH')) exit();

/*
https://documenter.getpostman.com/view/3204073/2sA3kXG1YU
*/

if (!class_exists('M_ABCEXCRYPTO')) {
    class M_ABCEXCRYPTO {

        private $m_name = "";
        private $m_id = "";
        private $base_url = "https://gateway.abcex.io";
        private $api_key = "";

        function __construct($m_name, $m_id, $api_key) {

            $this->m_name = trim($m_name);
            $this->m_id = trim($m_id);
            $this->api_key = trim($api_key);

        }

        function networks() {

            $r = $this->request('/api/v1/currency-network/list');

            $networks = array();
            if (is_array($r) and count($r) and isset($r[0]['typeCurrency'])) {
                //$r = array_filter($r, fn($item) => 'token' == $item['typeCurrency']);
                $networks = array_unique(array_column($r, 'networkId'));
                $networks = array_combine($networks, $networks);
                asort($networks);
            }

            return $networks;
        }

        function wallets() {

            $r = $this->request('/api/v1/wallets/balances', array('limit' => 100));

            $wallets = array();
            if (is_array($r['data']) and count($r['data'])) {
                $wallets = array_filter($r['data'], fn($item) => 'user' == $item['type'] and $item['isCoin'] and false === mb_stripos($item['currencyId'], 'TRX'));
                usort($wallets, fn($a, $b) => strcasecmp($a['currencyId'], $b['currencyId']));
                $wallets = array_column($wallets, 'currencyId', 'id');
            }

            return $wallets;
        }

        function get_address($data) {

            $r = $this->request('/api/v1/wallet/get-new-crypto-address', $data);

            if (is_array($r) and count($r)) {
                foreach ($r as $val) {
                    if ($val['network'] == $data['networkId']) {
                        return $val['address'];
                    }
                }
            }

            return false;
        }

        function transactions($data = array()) {

            $data = array_merge(array(
                'filter.method' => 'crypto',
                'filter.direction' => 'in',
                'filter.status' => '$in:completed,processing',
                'limit' => 100,
            ), $data);

            $r = $this->request('/api/v1/wallet/transactions/list/my', $data);

            $history = array();
            if (is_array($r['data']) and count($r['data'])) {
                $history = array_combine(array_column($r['data'], 'addressTo'), $r['data']);
            }

            return $history;
        }

        private function request($method, $data = array(), $is_post = 0) {

            $is_post = intval($is_post);

            $headers = array(
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Bearer " . $this->api_key,
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
