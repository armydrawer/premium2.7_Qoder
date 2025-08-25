<?php

if (!defined('ABSPATH')) exit();

/*
Documentation: https://trustix.pw/api_docs (auth)
*/

class M_TRUSTIXPAY {
    private array $cfg = [
        'base_url' => 'https://trustix.pw',
        'API_KEY' => null,
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
    }

    function create_tx($data) {

        $r = $this->_request('POST', '/api/v1/create_invoice', ['json' => $data]);

        $r['pd'] = !empty($r['json']['invoice']['uuid']) ? $r['json']['invoice'] : [];

        return $r;
    }

    function get_tx($id) {

        $r = $this->_request('GET', "/api/v1/get_invoice", ['params' => ['uuid' => $id]]);

        $r['pd'] = !empty($r['json']['invoice']['uuid']) ? $r['json']['invoice'] : [];

        return $r;
    }

    function get_txs() {

        $r = [];
        $r['pd'] = [];

        return $r;
    }

    private function _request($method, $path, $options = []) {
        global $premiumbox;

        $method = strtoupper($method);
        $opts = array_merge(array_fill_keys(['headers', 'params', 'data', 'json', 'form', 'secure_headers', 'secure_body', 'secure_response', 'curl_opt'], []), $options);
        $headers = array_merge($this->cfg['headers'], $opts['headers']);

        ////////////////////////////////////////
        $headers = array_merge([
            'Authorization' => "Bearer {$this->cfg['API_KEY']}",
        ], $headers);

        $opts['secure_headers'] = array_merge([
            'Authorization'
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
