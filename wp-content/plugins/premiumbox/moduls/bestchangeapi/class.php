<?php
if (!defined('ABSPATH')) exit();

if (!class_exists('BestChangeAPI')) {
    class BestChangeAPI {

        private $base_url = "https://www.bestchange.app";
        private $api_key = "";
        private $timeout = 20;
        private $lang = '';

        function __construct($api_key, $timeout, $BASE_URL = '') {
            global $premiumbox;

            $this->api_key = trim($api_key);
            $this->timeout = intval($timeout);
            if ($this->timeout < 0) $this->timeout = 20;

            $base_url = apply_filters('curl_bestchangeapi_domain', trim($BASE_URL));
            $this->base_url = $base_url ? rtrim((!str_contains($base_url, '://') ? 'https://' : '') . $base_url, '/') : $this->base_url;

            $lang_id = intval($premiumbox->get_option('bestchangeapi', 'lang'));
            $langs = array('0' => 'ru', '1' => 'en');
            $this->lang = is_isset($langs, $lang_id);

        }

        function get_currencies() {

            return $this->request('/currencies/' . $this->lang);
        }

        function get_cities() {

            return $this->request('/cities/' . $this->lang);
        }

        function get_changers() {

            return $this->request('/changers/' . $this->lang);
        }

        function get_rates($rates) {

            $get = array();
            if (is_array($rates)) {
                foreach ($rates as $rate) {
                    $rate_arr = explode('-', $rate);
                    $rate1 = intval(is_isset($rate_arr, 0));
                    $rate2 = intval(is_isset($rate_arr, 1));
                    if ($rate1 and $rate2) {
                        $get[] = $rate;
                    }
                }
                $get = array_unique($get);
            }
            if (count($get) > 0) {
                $rates = array();
                $gets = array_chunk($get, 490);
                foreach ($gets as $get_k => $get_request_arr) {
                    $res = $this->request('/rates/' . implode('+', $get_request_arr));
                    if (isset($res['rates']) and is_array($res['rates'])) {
                        $rates = array_merge($rates, $res['rates']);
                    }
                }
                return array(
                    'rates' => $rates,
                );
            }

            return array();
        }

        function _create_log($url, $headers, $json_data, $result, $error) {
            global $wpdb, $premiumbox;

            $log = intval($premiumbox->get_option('bestchangeapi', 'log'));
            if ($log) {
                $arr = array();
                $arr['create_date'] = current_time('mysql');
                $arr['url'] = pn_strip_input($url);
                $arr['headers'] = pn_strip_input(print_r($headers, true));
                $arr['json_data'] = pn_strip_input(print_r($json_data, true));
                $arr['result'] = addslashes(pn_maxf(pn_strip_input(print_r($result, true)), 5000));
                $arr['error'] = pn_strip_input(print_r($error, true));
                $wpdb->insert($wpdb->prefix . 'bestchangeapi_logs', $arr);
            }

        }

        private function request($method) {
            global $premiumbox;

            $headers = array(
                "Accept: application/json",
                "Content-Type: application/json",
            );

            $url = $this->base_url . '/v2/' . $this->api_key . '/' . ltrim($method, '/');

            if ($ch = curl_init()) {

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, sprintf('%s/%s', $premiumbox->plugin_name, $premiumbox->plugin_version));
                curl_setopt($ch, CURLOPT_URL, $url);

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
                curl_setopt($ch, CURLOPT_ENCODING, '');

                $ch = apply_filters('curl_bestchangeapi', $ch);

                $err = curl_errno($ch);
                $result = curl_exec($ch);
                $info = curl_getinfo($ch);

                curl_close($ch);

                $res = @json_decode($result, true);

                $this->_create_log($url, $headers, '', $result, $err);

                return $res;

            }

            return '';
        }
    }
}
