<?php

if (!defined('ABSPATH')) exit();

/*
Documentation: PDF
*/

class M_PAYSCROW_C {
    private array $cfg = [
        'base_url' => 'https://api.payscrow-cascade.io',
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

    function order_link($data) {

        $r = $this->request('POST', '/api/v1/form/create', ['json' => $data]);

        $r['pd'] = !empty($r['json']['form_data']['session_id']) ? $r['json']['form_data'] : [];

        return $r;
    }

    function order_req($data) {

        $r = $this->request('POST', '/api/v1/order/', ['json' => $data]);

        $r['pd'] = !empty($r['json']['id']) ? $r['json'] : [];

        return $r;
    }

    function get_payment($id) {

        $r = $this->request('GET', "/api/v1/order/{$id}");

        $r['pd'] = !empty($r['json']['order']['id']) ? $r['json']['order'] : [];

        return $r;
    }

    function order_by_client_id($id) {

        $r = $this->request('GET', "/api/v1/order/search-by-client-order-id", ['params' => ['id' => $id]]);

        $r['pd'] = !empty($r['json']['orders']['items'][0]) ? $r['json']['orders']['items'][0] : [];

        return $r;
    }

    function get_payments($data = []) {

        $tz = new DateTimeZone('UTC');

        $data = array_merge([
            'size' => 100,
            'order_side' => 'Buy',
            'start_date' => (new DateTime('now', $tz))->modify('-1 day')->format('Y-m-d H:i:s'),
            'end_date' => (new DateTime('now', $tz))->format('Y-m-d H:i:s'),
        ], $data);

        $pd_data = [];
        $r = $this->request('GET', "/api/v1/order/", ['params' => $data]);

        if (!empty($r['json']['orders']['items']) && is_array($r['json']['orders']['items'])) {
            $pd_data = array_column($r['json']['orders']['items'], null, 'client_order_id');
        }

        $r['pd'] = $pd_data;

        return $r;
    }

    function payment_methods($add_new = '') {

        $pd_data = [];
        $r = $this->request('GET', '/api/v1/finance/payment_methods');

        if (!empty($r['json']['payment_methods'])) {
            $r['json']['payment_methods'] = array_filter($r['json']['payment_methods'], fn($item) => strcasecmp($item['method_type'], 'NSPKLink') !== 0);
            $r['json']['payment_methods'] = array_column($r['json']['payment_methods'], null, 'method_id');

            foreach ($r['json']['payment_methods'] as $item) {
                $method_id = pn_strip_input($item['method_id']);
                $method_name = pn_strip_input($item['method_name']);
                $method_type = pn_strip_input($item['method_type']);
                $method_currency = pn_strip_input($item['method_currency']);

                $pd_data[$method_id] = "[{$method_type}, {$method_currency}] {$method_name}";
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

        $priority_keys = [
            '8fe3669a-a448-4053-bc4b-43bb51cb3e9d', // BankCard
            '560a0a52-3531-44b7-8f7b-5b6dbe5c04e1', // TransBankCard
            '2ec6dbd6-49a5-45d0-bd6d-b0134ee4639a', // SBP
            'c154be74-5e4e-4edb-85c2-d2aa175165f5', // TransSBP
            '465b838e-1411-4435-a8d5-b9e0b533e9b6', // BankAccount
            'd3a8d0f7-a8c8-43c7-b77e-61018efc4c4d', // NSPKLink
        ];
        $priority = array_intersect_key($pd_data, array_flip($priority_keys));
        $pd_data = $priority + array_diff_key($pd_data, $priority);

        $r['pd'] = $pd_data;

        return $r;
    }

    private function request($method, $path, $options = []) {
        global $premiumbox;

        $options = array_merge(array_fill_keys(['headers', 'params', 'data', 'json', 'form', 'secure_headers', 'secure_body', 'secure_response', 'curl_opt'], []), $options);
        $headers = array_merge($this->cfg['headers'], $options['headers']);

        ////////////////////////////////////////
        $headers = array_merge([
            'X-Api-Key' => $this->cfg['API_KEY']
        ], $headers);

        $options['secure_headers'] = array_merge([
            'X-Api-Key'
        ], $options['secure_headers']);
        ////////////////////////////////////////

        $url = ('/' === $path[0] ? $this->cfg['base_url'] : '') . $path . ($options['params'] ? '?' . http_build_query($options['params']) : '');

        if ($options['json']) {
            $headers['Content-Type'] = 'application/json';
            $body = pn_json_encode($options['json']);
        } elseif ($options['data']) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            $body = http_build_query($options['data']);
        } elseif ($options['form']) {
            $headers['Content-Type'] = 'multipart/form-data';
            $body = $options['form'];
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
        $curl_opt = $options['curl_opt'] ? array_replace($curl_opt, $options['curl_opt']) : $curl_opt;

        $ch = curl_init();
        curl_setopt_array($ch, $curl_opt);
        $ch = apply_filters($this->cfg['curl_opt'], $ch, $this->cfg['m_name'], $this->cfg['m_id']);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $status_code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

        if (has_action($this->cfg['error_log'])) {
            if ($this->cfg['DEBUG']) $options['secure_headers'] = $options['secure_body'] = $options['secure_response'] = [];

            $url_log = ('/' === $path[0] ? $this->cfg['base_url'] : '') . $path . ($options['params'] ? '?' . urldecode(http_build_query($options['params'])) : '');

            $headers_log = $headers ? pn_json_encode(array_replace($headers, array_intersect_key(array_fill_keys($options['secure_headers'], '***'), $headers))) ?: false : false;

            $body_log = $options['data'] + $options['json'] + $options['form'];
            $body_log = $body_log ? pn_json_encode(array_replace($body_log, array_intersect_key(array_fill_keys($options['secure_body'], '***'), $body_log))) ?: false : false;

            $response_log = pn_json_decode($response);
            $response_log = $response_log ? pn_json_encode(array_replace($response_log, array_intersect_key(array_fill_keys($options['secure_response'], '***'), $response_log))) ?: $response : $response;

            $error_log = implode(' ', array_filter([$errno ? "curl_{$errno}" : null, $status_code && 200 != $status_code ? "http_{$status_code}" : null,]));

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
