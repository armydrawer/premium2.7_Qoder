<?php

if (!defined('ABSPATH')) exit();

/*
Documentation: https://nicepay.io/docs/
*/

class P_NICEPAY {
    private array $cfg = [
        'base_url' => 'https://nicepay.io',
        'MERCHANT_ID' => null,
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

        $this->cfg['MERCHANT_ID'] = trim(is_isset($m_define, 'MERCHANT_ID'));
        $this->cfg['SECRET_KEY'] = trim(is_isset($m_define, 'SECRET_KEY'));
    }

    function payout($data) {

        return $this->request('POST', '/public/api/payout', ['json' => $data]);
    }

    function balance($force_refresh = false) {

        $cache_name = sprintf('%s_%s', $this->cfg['m_id'], __FUNCTION__);
        if (!$force_refresh && !empty(self::$cache[$cache_name])) {
            return self::$cache[$cache_name];
        }

        $data = [];
        $r = $this->request('POST', '/public/api/getBalances');

        if (!empty($r['json']['data']) && 'success' == $r['json']['status']) {
            foreach ($r['json']['data'] as $k => $val) {
                $_k = mb_strtolower(pn_strip_input($k));
                $_val = is_sum($val);

                $data[$_k] = $_val;
            }

            natcasesort($data);
        }

        $r['pd'] = $data;

        if (!empty($r['pd'])) {
            self::$cache[$cache_name] = $r;
        }

        return $r;
    }

    function payoutMethods($add_new = '') {

        $data = [];
        $r = $this->request('GET', '/public/api/payoutMethods');

        if (!empty($r['json']['data']) && 'success' == $r['json']['status']) {
            foreach ($r['json']['data'] as $item) {
                $code = pn_strip_input($item['code']);
                $currency = pn_strip_input($item['currency']);
                $name = pn_strip_input($item['name']);
                $note = pn_strip_input($item['note']);
                $note = $note ? " ({$note})" : '';

                $data[$code] = "[{$currency}] {$name}{$note}";
            }
        }

        $lines = preg_split('/\R+/u', $add_new, -1, PREG_SPLIT_NO_EMPTY);
        if ($lines) {
            $lines = array_unique(array_filter(array_map('pn_strip_input', $lines)));
            foreach ($lines as $line) {
                $parts = explode('=', $line, 2);
                $key = pn_strip_input($parts[0]);
                $value = isset($parts[1]) ? pn_strip_input($parts[1]) : $key;
                if (!$key || !$value) continue;

                $data[$key] = $value;
            }
        }

        unset($data['wallet_express_usdt']);

        natcasesort($data);

        $r['pd'] = $data;

        return $r;
    }

    function payoutInfo($id) {

        $r = $this->request('POST', '/public/api/payoutInfo', ['json' => ['payout' => $id]]);

        $r['pd'] = !empty($r['json']['data']['payout_id']) ? $r['json']['data'] : [];

        return $r;
    }

    function get_payments() {

        return ['pd' => []];
    }

    private function request($method, $path, $options = []) {
        global $premiumbox;

        $options = array_merge(array_fill_keys(['headers', 'params', 'data', 'json', 'form', 'secure_headers', 'secure_body', 'secure_response', 'curl_opt'], []), $options);
        $headers = array_merge($this->cfg['headers'], $options['headers']);

        ////////////////////////////////////////
        $options['json'] = array_merge([
            'merchant_id' => $this->cfg['MERCHANT_ID'],
            'secret' => $this->cfg['SECRET_KEY'],
        ], $options['json']);

        $options['secure_body'] = ['merchant_id', 'secret'];
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

            $error_log = implode(' ', array_filter([$errno ? 'curl_' . $errno : null, $status_code && 200 != $status_code ? 'http_' . $status_code : null,]));

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
