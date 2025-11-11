<?php

if (!defined('ABSPATH')) exit();

/*
Documentation: https://bankoro.io/api/public/docs/
*/

class P_BANKORO {
    private array $cfg = [
        'base_url' => 'https://bankoro.io',
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
    private static array $cache = [];

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

    function create_tx($data) {

        $r = $this->_request('POST', '/api/user/balance/withdrawal/', ['json' => $data]);

        $r['pd'] = !empty($r['json']['external_id']) ? $r['json'] : [];

        return $r;
    }

    function symbols($data = [], $add_new = '') {

        $data = array_merge([
            'per_page' => 10000,
        ], $data);

        $pd_data = [];
        $r = $this->_request('GET', '/api/symbol/', ['params' => $data]);

        if (!empty($r['json']['data'])) {
            foreach ($r['json']['data'] as $item) {
                $symbol = pn_strip_input($item['symbol']);
                $short_name = pn_strip_input($item['short_name']);
                $b_short_name = pn_strip_input($item['blockchain']['short_name']);


                $pd_data[$symbol] = "[{$symbol}] {$short_name} {$b_short_name}";
            }
        }

        if ($lines = preg_split('/\R+/u', $add_new, -1, PREG_SPLIT_NO_EMPTY)) {
            $lines = array_unique(array_filter(array_map('pn_strip_input', $lines)));
            foreach ($lines as $line) {
                $parts = explode('=', $line, 2);
                $key = pn_strip_input($parts[0]);
                $value = isset($parts[1]) ? pn_strip_input($parts[1]) : $key;
                if (!$key || !$value) continue;

                $pd_data[$key] = $value;
            }
        }

        natcasesort($pd_data);

        $r['pd'] = $pd_data;

        return $r;
    }

    function balance($force_refresh = false) {

        $cache_name = sprintf('%s_%s', $this->cfg['m_id'], __FUNCTION__);
        if (!$force_refresh && !empty(self::$cache[$cache_name])) {
            return self::$cache[$cache_name];
        }

        $pd = [];
        $r = $this->_request('GET', '/api/user/balance/');

        if (200 == $r['status_code'] && !empty($r['json'])) {
            foreach ($r['json'] as $k => $val) {
                $_k = mb_strtolower(pn_strip_input($val['symbol_id']));
                $_val = is_sum($val['value']);

                $pd[$_k] = $_val;
            }

            natcasesort($pd);
        }

        $r['pd'] = $pd;

        if (!empty($r['pd'])) {
            self::$cache[$cache_name] = $r;
        }

        return $r;
    }

    function get_tx($id) {

        $data = [
            'external_id' => $id,
        ];

        $r = $this->get_txs($data);

        $r['pd'] = !empty($r['pd'][$id]) ? $r['pd'][$id] : [];

        return $r;
    }

    function get_txs($data = []) {

        $data = array_merge([
            'order_asc' => false,
            'type' => 'withdrawal',
            'per_page' => 100,
        ], $data);

        $r = $this->_request('GET', "/api/user/transaction/", ['params' => $data]);

        $r['pd'] = !empty($r['json']['data']) ? $this->group_by_first($r['json']['data'], 'external_id') : [];

        return $r;
    }

    private function group_by_first($data, $key) {
        $result = [];
        foreach ($data as $item) {
            $result[$item[$key] ?? 'null'] ??= $item;
        }
        return $result;
    }

    private function _request($method, $path, $options = []) {
        global $premiumbox;

        $method = strtoupper($method);
        $opts = array_merge(array_fill_keys(['headers', 'params', 'data', 'json', 'form', 'secure_headers', 'secure_body', 'secure_response', 'curl_opt'], []), $options);
        $headers = array_merge($this->cfg['headers'], $opts['headers']);

        ////////////////////////////////////////
        $timestamp = strval(time());
        ksort($opts['json']);

        $sign_data = [
            'params' => http_build_query($opts['params']),
            'body' => $opts['json'],
            'path' => $path,
        ];
        ksort($sign_data);

        $payload = $timestamp . json_encode($sign_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);

        $headers = array_filter(array_merge([
            'X-API-KEY-ID' => $this->cfg['API_KEY'],
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => hash_hmac('sha256', $payload, $this->cfg['SECRET_KEY']),
        ], $headers), fn($v) => !is_null($v));

        $opts['secure_headers'] = array_merge([
            'X-API-KEY-ID', 'X-SIGNATURE'
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
        $curl_code = curl_errno($ch);
        $curl_message = curl_error($ch);
        $status_code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

        if (has_action($this->cfg['error_log'])) {
            if ($this->cfg['DEBUG']) $opts['secure_headers'] = $opts['secure_body'] = $opts['secure_response'] = [];

            $url_log = ('/' === $path[0] ? $this->cfg['base_url'] : '') . $path . ($opts['params'] ? '?' . urldecode(http_build_query($opts['params'])) : '');

            $headers_log = $headers ? pn_json_encode(array_replace($headers, array_intersect_key(array_fill_keys($opts['secure_headers'], '***'), $headers))) ?: false : false;

            $body_log = $opts['data'] + $opts['json'] + $opts['form'];
            $body_log = $body_log ? pn_json_encode(array_replace($body_log, array_intersect_key(array_fill_keys($opts['secure_body'], '***'), $body_log))) ?: false : false;

            $response_log = pn_json_decode($response);
            $response_log = $response_log ? pn_json_encode(array_replace($response_log, array_intersect_key(array_fill_keys($opts['secure_response'], '***'), $response_log))) ?: $response : $response;

            $error_log = ($errors = array_filter([
                'curl_code' => $curl_code,
                'curl_message' => $curl_message,
                'http_code' => ($status_code && ($status_code < 200 || $status_code > 299)) ? $status_code : null
            ])) ? pn_json_encode(['method' => $method] + $errors) : '';

            do_action($this->cfg['error_log'], $this->cfg['m_name'], $this->cfg['m_id'], $url_log, $headers_log, $body_log, $response_log, $error_log);
        }

        return [
            'status_code' => $status_code,
            'text' => $response,
            'json' => pn_json_decode($response),
            'curl_code' => $curl_code,
            'curl_message' => $curl_message,
        ];
    }
}
