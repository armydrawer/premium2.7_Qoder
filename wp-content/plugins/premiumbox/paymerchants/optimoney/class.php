<?php

if (!defined('ABSPATH')) exit();

/*
Documentation: https://docs.optimoney.com/Bkb1E5oj3p6RBrU2bGTf
*/

class P_OPTIMONEY {
    private array $cfg = [
        'base_url' => 'https://api.optimoney.com',
        'API_KEY' => null,
        'SECRET_KEY' => null,
        'opts' => [
            'timeout' => 5,
            'headers' => ['Accept' => 'application/json'],
            'params' => [],
            'data' => [],
            'json' => [],
            'form' => [],
            'secure_headers' => ['x-access-key', 'x-signature-timestamp', 'x-signature'],
            'secure_body' => [],
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
        ], [
            'API_KEY' => trim(is_isset($m_define, 'API_KEY')),
            'SECRET_KEY' => trim(is_isset($m_define, 'SECRET_KEY')),
        ], $this->cfg['types'][strtoupper(get_class($this)[0])] ?? []);
    }

    function create_tx($data) {

        $r = $this->_request('POST', '/api/v1/client/wallets/transfer-to-wallet', ['json' => $data]);

        $r['pd'] = !empty($r['json']['data']['id']) ? $r['json']['data'] : [];

        return $r;
    }

    function get_tx($id) {

        $r = $this->_request('GET', "/api/v1/client/transactions/transaction/{$id}");

        $r['pd'] = !empty($r['json']['data']['id']) ? $r['json']['data'] : [];

        return $r;
    }

    function get_txs($data = []) {

        $data = array_merge([
            'per_page' => 100,
        ], $data);

        $r = $this->_request('GET', "/api/v1/client/transactions/transaction", ['params' => $data]);

        $r['pd'] = !empty($r['json']['data']['items']) ? array_column($r['json']['data']['items'], null, 'id') : [];

        return $r;
    }

    function balance($force_refresh = false) {

        $cache_name = sprintf('%s_%s', $this->cfg['m_id'], __FUNCTION__);
        if (!$force_refresh && !empty(self::$cache[$cache_name])) {
            return self::$cache[$cache_name];
        }

        $pd = [];
        $r = $this->wallets();

        foreach ($r['pd'] as $k => $item) {
            $code = mb_strtolower(pn_strip_input($item['wallet_number'] ?? '' ?: ''));
            $amount = is_sum($item['balance'] ?? '' ?: '');

            if (!$code) continue;

            $pd[$code] = $amount;
        }

        natcasesort($pd);

        $r['pd'] = $pd;

        if (!empty($r['pd'])) {
            self::$cache[$cache_name] = $r;
        }

        return $r;
    }

    function payment_methods_list($add_new = '') {

        $pd_data = [];
        $r = $this->wallets();

        foreach ($r['pd'] as $k => $item) {
            $wallet_number = pn_strip_input($item['wallet_number'] ?? '' ?: '');
            $public_name = pn_strip_input($item['public_name'] ?? '' ?: '');
            $currency = pn_strip_input($item['currency'] ?? '' ?: '');
            $status = absint($item['status'] ?? '' ?: '');
            $balance = is_sum($item['balance'] ?? '' ?: '');

            if (!$wallet_number || !$public_name || !$currency) continue;

            $title_parts = [];
            $title_parts[] = "[{$currency}] {$wallet_number} ({$public_name})";

            $title_parts[] = sprintf('%s: %s', __('Current balance', 'pn'), number_format_i18n($balance, ($p = strrpos($balance, '.')) === false ? 0 : strlen($balance) - $p - 1));

            if (2 == $status) {
                $title_parts[] = __('inactive', 'premium');
            }

            $pd_data[$wallet_number] = implode(' | ', $title_parts);
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

    function wallets() {

        $r = $this->_request('GET', '/api/v1/client/wallets/wallet');

        $r['pd'] = !empty($r['json']['data']['items']) ? array_column($r['json']['data']['items'], null, 'wallet_number') : [];

        return $r;
    }

    private function _request($method, $path, $options = []) {
        global $premiumbox;

        $method = strtoupper($method);
        $o = array_merge($this->cfg['opts'], $options);

        ////////////////////////////////////////
        $timestamp = (string)time();
        $body = $o['json'] ? pn_json_encode($o['json']) : '';
        $url = ('/' === $path[0] ? $this->cfg['base_url'] : '') . $path;
        $data = "{$method}{$url}{$timestamp}{$this->cfg['API_KEY']}{$body}";

        $signature = base64_encode(
            hash_hmac('sha256', $data, $this->cfg['SECRET_KEY'], true)
        );

        $o['headers'] = array_merge([
            'x-access-key' => $this->cfg['API_KEY'],
            'x-signature-timestamp' => $timestamp,
            'x-signature' => $signature,
        ], $o['headers']);
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
