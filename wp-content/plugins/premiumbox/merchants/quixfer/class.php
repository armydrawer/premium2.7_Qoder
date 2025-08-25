<?php

if (!defined('ABSPATH')) exit();

/*
Documentation: https://quixfer.cc/user/settings/api
*/

class M_QUIXFER {
    private array $cfg = [
        'base_url' => 'https://api.quixfer.cc',
        'API_KEY' => null,
        'SECRET_KEY' => null,
        ////////////////////////////////////////
        'm_name' => null,
        'm_id' => null,
        'DEBUG' => null,
        'timeout' => 5,
        'headers' => ['Accept' => 'application/json'],
        'types' => [
            'M' => ['curl_opt' => 'curl_merch', 'error_log' => 'save_merchant_error'],
            'P' => ['curl_opt' => 'curl_ap', 'error_log' => 'save_paymerchant_error'],
            'A' => ['curl_opt' => 'curl_amlcheck', 'error_log' => 'save_amlcheck_error'],
        ],
    ];

    function __construct($m_name, $m_id, $m_define, $m_data) {
        $base_url = trim(is_isset($m_define, 'BASE_URL'));
        $this->cfg['base_url'] = $base_url ? rtrim((!str_contains($base_url, '://') ? 'https://' : '') . $base_url, '/') : $this->cfg['base_url'];
        $this->cfg['m_name'] = trim($m_name);
        $this->cfg['m_id'] = trim($m_id);
        $this->cfg['DEBUG'] = WP_DEBUG || is_isset($m_data, 'show_error');
        $this->cfg += $this->cfg['types'][strtoupper(get_class($this)[0])] ?? [];

        $this->cfg['API_KEY'] = trim(is_isset($m_define, 'API_KEY'));
        $this->cfg['SECRET_KEY'] = trim(is_isset($m_define, 'SECRET_KEY'));
    }

    function create_order($data) {

        $r = $this->_request('POST', '/api/v3/buyUsdt/createOrder', ['json' => $data]);

        $r['pd'] = !empty($r['json']['order']['order_id']) ? $r['json']['order'] : [];

        return $r;
    }

    function get_payment($id) {

        $r = $this->_request('GET', '/api/v3/buyUsdt/getOrder', ['params' => ['order_id' => $id]]);

        $r['pd'] = !empty($r['json']['order']['order_id']) ? $r['json']['order'] : [];

        return $r;
    }

    function get_payments() {

        return ['pd' => []];
    }

    function currencies() {

        $r = $this->_request('GET', "/api/v3/currency-required-fields");
        $pd = [];

        if (200 == $r['status_code']) {
            $r['json'] = array_column($r['json'], null, 'xml');

            uasort($r['json'], fn($a, $b) => $a['currency'] <=> $b['currency'] ?: $a['xml'] <=> $b['xml']);

            array_walk($r['json'], function (&$item) {
                $item['fields_in'] = array_column($item['fields_in'] ?? [], null, 'field_id');
                $item['fields_out'] = array_column($item['fields_out'] ?? [], null, 'field_id');

                ksort($item['fields_in']);
                ksort($item['fields_out']);
            });

            $pd = $r['json'];
        }

        $r['pd'] = $pd;

        return $r;
    }

    private function _request($method, $path, $options = []) {
        global $premiumbox;

        $method = strtoupper($method);
        $opts = array_merge(array_fill_keys(['headers', 'params', 'data', 'json', 'form', 'secure_headers', 'secure_body', 'secure_response', 'curl_opt'], []), $options);
        $headers = array_merge($this->cfg['headers'], $opts['headers']);

        ////////////////////////////////////////
        $timestamp = strval(time());

        $headers = array_merge([
            'Apikey' => $this->cfg['API_KEY'],
            'Timestamp' => $timestamp,
            'Signature' => hash_hmac('sha256', "{$this->cfg['API_KEY']}{$timestamp}", $this->cfg['SECRET_KEY']),
        ], $headers);

        $opts['secure_headers'] = array_merge([
            'Apikey', 'Timestamp', 'Signature'
        ], $opts['secure_headers']);
        ////////////////////////////////////////

        $url = ('/' === $path[0] ? $this->cfg['base_url'] : '') . $path . ($opts['params'] ? '?' . http_build_query($opts['params']) : '');

        if ($opts['json']) {
            $headers['Content-Type'] = 'application/json';
            $body = pn_json_encode($opts['json']);
        } elseif ($opts['data']) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            $body = http_build_query($opts['data']);
        } elseif ($opts['form']) {
            $headers['Content-Type'] = 'multipart/form-data';
            $body = $opts['form'];
        }

        $curl_opt = [
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => sprintf('%s/%s', $premiumbox->plugin_name, $premiumbox->plugin_version),
            CURLOPT_HTTPHEADER => array_map(fn($k, $v) => "$k: $v", array_keys($headers), $headers),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->cfg['timeout'],
            CURLOPT_TIMEOUT => $this->cfg['timeout'],
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        $curl_opt += (isset($body) ? [CURLOPT_POSTFIELDS => $body] : []);
        $curl_opt = $opts['curl_opt'] ? array_replace($curl_opt, $opts['curl_opt']) : $curl_opt;

        $ch = curl_init();
        curl_setopt_array($ch, $curl_opt);
        $ch = apply_filters($this->cfg['curl_opt'], $ch, $this->cfg['m_name'], $this->cfg['m_id']);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $status_code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

        if (has_action($this->cfg['error_log'])) {
            if ($this->cfg['DEBUG']) $opts['secure_headers'] = $opts['secure_body'] = $opts['secure_response'] = [];

            $url_log = ('/' === $path[0] ? $this->cfg['base_url'] : '') . $path . ($opts['params'] ? '?' . urldecode(http_build_query($opts['params'])) : '');

            $headers_log = $headers ? pn_json_encode(array_replace($headers, array_intersect_key(array_fill_keys($opts['secure_headers'], '***'), $headers))) ?: false : false;

            $body_log = $opts['data'] + $opts['json'] + $opts['form'];
            $body_log = $body_log ? pn_json_encode(array_replace($body_log, array_intersect_key(array_fill_keys($opts['secure_body'], '***'), $body_log))) ?: false : false;

            $response_log = pn_json_decode($response);
            $response_log = $response_log ? pn_json_encode(array_replace($response_log, array_intersect_key(array_fill_keys($opts['secure_response'], '***'), $response_log))) ?: $response : $response;

            $error_log = ($errors = array_filter([$errno ? "curl_{$errno}" : null, $status_code && ($status_code < 200 || $status_code > 299) ? "http_{$status_code}" : null])) ? implode(' ', ["method_{$method}", ...$errors]) : '';

            do_action($this->cfg['error_log'], $this->cfg['m_name'], $this->cfg['m_id'], $url_log, $headers_log, $body_log, $response_log, $error_log);
        }

        return [
            'status_code' => $status_code,
            'text' => $response,
            'json' => pn_json_decode($response),
            'curl_errno' => $errno,
            'curl_error' => curl_error($ch),
        ];
    }
}
