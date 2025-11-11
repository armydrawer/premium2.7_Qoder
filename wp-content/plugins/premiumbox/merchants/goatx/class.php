<?php

if (!defined('ABSPATH')) exit();

/*
Documentation: https://gist.github.com/Goatx-crp/8350ffedaa6e51c3f85907b550dc7a3c
*/

class M_GOATX {
    private array $cfg = [
        'base_url' => 'https://goatx.me',
        'LOGIN' => null,
        'API_KEY' => null,
        'CID' => null,
        'opts' => [
            'timeout' => 5,
            'headers' => ['Accept' => 'application/json'],
            'params' => [],
            'data' => [],
            'json' => [],
            'form' => [],
            'secure_headers' => [],
            'secure_body' => ['signature'],
            'secure_response' => [],
            'curl_opt' => [],
        ],
        ////////////////////////////////////////
        'm_name' => null,
        'm_id' => null,
        'DEBUG' => null,
        'types' => [
            'M' => ['curl_opt' => 'curl_merch', 'error_log' => 'save_merchant_error'],
            'P' => ['curl_opt' => 'curl_ap', 'error_log' => 'save_paymerchant_error'],
            'A' => ['curl_opt' => 'curl_amlcheck', 'error_log' => 'save_amlcheck_error'],
        ],
    ];
    private static array $cache = [];

    function __construct($m_name, $m_id, $m_define, $m_data) {
        $base_url = trim(is_isset($m_define, 'BASE_URL'));

        $this->cfg = array_merge($this->cfg, [
            'base_url' => $base_url ? rtrim((!str_contains($base_url, '://') ? 'https://' : '') . $base_url, '/') : $this->cfg['base_url'],
            'm_name' => trim($m_name),
            'm_id' => trim($m_id),
            'DEBUG' => WP_DEBUG || is_isset($m_data, 'show_error'),

            'LOGIN' => trim(is_isset($m_define, 'LOGIN')),
            'API_KEY' => trim(is_isset($m_define, 'API_KEY')),
            'CID' => trim(is_isset($m_define, 'CID')),
        ], $this->cfg['types'][strtoupper(get_class($this)[0])] ?? []);
    }

    function create_tx($data) {

        $data += [
            'contract' => $this->cfg['CID'],
            'signature' => hash('sha256', "{$this->cfg['LOGIN']}:{$data['sum']}:{$data['invid']}:{$this->cfg['API_KEY']}"),
        ];

        $r = $this->_request('POST', '/api/order/', ['json' => $data]);

        $r['pd'] = !empty($r['json']['invid']) ? $r['json'] : [];

        if ($r['pd']) {
            $r_currencies = $this->currencies();
            $r['pd']['cd'] = ($r_currencies['pd'][$r['pd']['currency']] ?? []);
        }

        return $r;
    }

    function get_tx($id) {

        $data = [
            'contract' => $this->cfg['CID'],
            'invid' => $id,
            'signature' => hash('sha256', "{$this->cfg['LOGIN']}:{$id}:{$this->cfg['API_KEY']}"),
        ];

        $r = $this->_request('POST', "/api/order/check/", ['json' => $data]);

        $r['pd'] = !empty($r['json']['invid']) ? $r['json'] : [];

        /*if ($r['pd']) {
            $r_currencies = $this->currencies();
            $r['pd']['cd'] = ($r_currencies['pd'][$r['pd']['currency']] ?? []);
        }*/

        return $r;
    }

    function get_txs() {

        $r = [];

        $r['pd'] = [];

        return $r;
    }

    function currencies($force_refresh = false) {

        $cache_name = sprintf('%s_%s', $this->cfg['m_id'], __FUNCTION__);
        if (!$force_refresh && !empty(self::$cache[$cache_name])) {
            return self::$cache[$cache_name];
        }

        $r = $this->_request('GET', "/api/data/currencies/");

        $r['pd'] = 200 == $r['status_code'] && !empty($r['json']) ? array_column($r['json'], null, 'code') : [];

        if ($r['pd']) {
            self::$cache[$cache_name] = $r;
        }

        return $r;
    }

    function cancel_order($id) {

        $data = [
            'signature' => hash('sha256', "{$this->cfg['LOGIN']}:{$id}:{$this->cfg['API_KEY']}"),
        ];

        return $this->_request('POST', "/api/order/{$id}/cancel/", ['json' => $data]);
    }

    private function _request($method, $path, $options = []) {
        global $premiumbox;

        $method = strtoupper($method);
        $o = array_merge($this->cfg['opts'], $options);

        ////////////////////////////////////////
        ///
        ////////////////////////////////////////

        if ($o['json']) {
            $o['headers'] = array_merge(['Content-Type' => 'application/json'], $o['headers']);
            $body = pn_json_encode($o['json']);
        } elseif ($o['data']) {
            $o['headers'] = array_merge(['Content-Type' => 'application/x-www-form-urlencoded'], $o['headers']);
            $body = http_build_query($o['data']);
        } elseif ($o['form']) {
            $o['headers'] = array_merge(['Content-Type' => 'multipart/form-data'], $o['headers']);
            $body = $o['form'];
        }

        $o = array_map(fn($v) => is_array($v) ? array_filter($v, fn($item) => !is_null($item)) : $v, $o);

        $curl_opt = [
            CURLOPT_URL => ('/' === $path[0] ? $this->cfg['base_url'] : '') . $path . ($o['params'] ? '?' . http_build_query($o['params']) : ''),
            CURLOPT_USERAGENT => sprintf('%s/%s', $premiumbox->plugin_name, $premiumbox->plugin_version),
            CURLOPT_HTTPHEADER => array_map(fn($k, $v) => "$k: $v", array_keys($o['headers']), $o['headers']),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $o['timeout'],
            CURLOPT_TIMEOUT => $o['timeout'],
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        $curl_opt += (isset($body) ? [CURLOPT_POSTFIELDS => $body] : []);
        $curl_opt = $o['curl_opt'] ? array_replace($curl_opt, $o['curl_opt']) : $curl_opt;

        $ch = curl_init();
        curl_setopt_array($ch, $curl_opt);
        $ch = apply_filters($this->cfg['curl_opt'], $ch, $this->cfg['m_name'], $this->cfg['m_id']);

        $response = curl_exec($ch);
        $curl_code = curl_errno($ch);
        $curl_message = curl_error($ch);
        $status_code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

        if (has_action($this->cfg['error_log'])) {
            if ($this->cfg['DEBUG']) $o['secure_headers'] = $o['secure_body'] = $o['secure_response'] = [];

            $params = $o['params'] ? array_replace($o['params'], array_intersect_key(array_fill_keys((in_array('*', $o['secure_body']) ? array_keys($o['params']) : $o['secure_body']), '***'), $o['params'])) : [];
            $url_log = ('/' === $path[0] ? $this->cfg['base_url'] : '') . $path . ($params ? '?' . urldecode(http_build_query($params)) : '');

            $headers_log = $o['headers'] ? pn_json_encode(array_replace($o['headers'], array_intersect_key(array_fill_keys($o['secure_headers'], '***'), $o['headers']))) ?: false : false;

            $body_log = $o['data'] + $o['json'] + $o['form'];
            $body_log = $body_log ? pn_json_encode(array_replace($body_log, array_intersect_key(array_fill_keys((in_array('*', $o['secure_body']) ? array_keys($body_log) : $o['secure_body']), '***'), $body_log))) ?: false : false;

            $response_log = pn_json_decode($response);
            $response_log = $response_log ? pn_json_encode(array_replace($response_log, array_intersect_key(array_fill_keys((in_array('*', $o['secure_response']) ? array_keys($response_log) : $o['secure_response']), '***'), $response_log))) ?: $response : $response;

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
