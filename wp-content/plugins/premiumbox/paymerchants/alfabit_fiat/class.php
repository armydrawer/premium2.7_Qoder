<?php

if (!defined('ABSPATH')) exit();

/*
Documentation: https://alfabit.gitbook.io/alfabit-pay/api-reference, https://pay.alfabit.org/api/integration#/
*/

class P_ALFABITF {
    private array $cfg = [
        'base_url' => 'https://pay.alfabit.org',
        'API_KEY' => null,
        'opts' => [
            'timeout' => 5,
            'headers' => ['Accept' => 'application/json'],
            'params' => [],
            'data' => [],
            'json' => [],
            'form' => [],
            'secure_headers' => ['x-api-key'],
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

            'API_KEY' => trim(is_isset($m_define, 'API_KEY')),
        ], $this->cfg['types'][strtoupper(get_class($this)[0])] ?? []);
    }

    function create_tx($data) {

        $r = $this->_request('POST', '/api/v1/integration/orders/fiat-withdraw', ['json' => $data]);

        $r['pd'] = !empty($r['json']['data']['uid']) ? $r['json']['data'] : [];

        return $r;
    }

    function get_tx($id) {

        $r = $this->_request('GET', "/api/v1/integration/orders/{$id}");

        $r['pd'] = !empty($r['json']['data']['uid']) ? $r['json']['data'] : [];

        return $r;
    }

    function get_txs($data = []) {

        $data = array_merge([
            'limit' => 100,
            'types' => ['withdraw'],
            'asset_type' => 'FIAT', // custom
        ], $data);

        if (isset($data['asset_type']) && 'FIAT' == $data['asset_type']) {
            unset($data['asset_type']);
            $data['assets'] = array_keys($this->currencies()['pd']);
        }

        $r = $this->_request('GET', "/api/v2/integration/orders", ['params' => $data]);

        $r['pd'] = 200 == $r['status_code'] && !empty($r['json']['data']) ? array_column($r['json']['data'], null, 'uid') : [];

        return $r;
    }

    function balance($force_refresh = false) {

        $cache_name = sprintf('%s_%s', $this->cfg['m_id'], __FUNCTION__);
        if (!$force_refresh && !empty(self::$cache[$cache_name])) {
            return self::$cache[$cache_name];
        }

        $pd = [];
        $r = $this->_request('GET', '/api/v1/integration/merchant/balances');

        if (200 == $r['status_code'] && !empty($r['json']['data'])) {
            foreach ($r['json']['data'] as $k => $item) {
                $type = pn_strip_input($item['type']);

                if ('FIAT' != $type) continue;

                $code = mb_strtolower(pn_strip_input($item['code']));
                $amount = is_sum($item['balance']);

                $pd[$code] = $amount;
            }

            natcasesort($pd);
        }

        $r['pd'] = $pd;

        if (!empty($r['pd'])) {
            self::$cache[$cache_name] = $r;
        }

        return $r;
    }

    function payment_methods() {

        $r = $this->_request('GET', '/api/v1/integration/merchant/fiat-config');

        $r['pd'] = 200 == $r['status_code'] && !empty($r['json']['data']) ? array_column($r['json']['data'], null, 'code') : [];
        array_walk($r['pd'], fn(&$item) => $item['providersOut'] = array_column($item['providersOut'] ?? [], null, 'aliasCode'));

        return $r;
    }

    function payment_methods_list($add_new = '') {

        $pd_data = [];
        $r = $this->payment_methods();

        foreach ($r['pd'] as $k => $item) {
            if (empty($item['providersOut'])) continue;
            if ('withdraw' != $item['paymentType']) continue;

            $name = pn_strip_input($item['name']);
            $code = pn_strip_input($item['code']);
            $paymentMethod = pn_strip_input($item['paymentMethod']);
            $currency = pn_strip_input($item['currency']);
            $amountMin = is_sum($item['amountMin']);
            $amountMax = is_sum($item['amountMax']);
            $feePercent = is_sum($item['feePercent']);
            $minFeeAmount = is_sum($item['minFeeAmount']);
            $requiredFields = pn_strip_input_array($item['requiredFields']);

            if (empty($name) || empty($code) || empty($paymentMethod) || empty($currency)) continue;

            foreach ($item['providersOut'] as $method) {
                $aliasCode = pn_strip_input($method['aliasCode']);
                $m_name = pn_strip_input($method['name']);

                if (empty($aliasCode) || empty($m_name)) continue;

                $title_parts = [];
                $title_parts[] = "[{$currency}] {$name} ({$m_name})";

                $limits = [];
                if ($amountMin) {
                    $limits[] = sprintf('%s: %s', __('min.', 'pn'), number_format_i18n($amountMin, ($p = strrpos($amountMin, '.')) === false ? 0 : strlen($amountMin) - $p - 1));
                }
                if ($amountMax) {
                    $limits[] = sprintf('%s: %s', __('max.', 'pn'), number_format_i18n($amountMax, ($p = strrpos($amountMax, '.')) === false ? 0 : strlen($amountMax) - $p - 1));
                }
                if ($feePercent) {
                    $limits[] = sprintf('%s: %s%%', __('PS fees amount', 'pn'), number_format_i18n($feePercent, ($p = strrpos($feePercent, '.')) === false ? 0 : strlen($feePercent) - $p - 1));
                }
                if ($minFeeAmount) {
                    $limits[] = sprintf('%s: %s', __('Min. amount of fees', 'pn'), number_format_i18n($minFeeAmount, ($p = strrpos($minFeeAmount, '.')) === false ? 0 : strlen($minFeeAmount) - $p - 1));
                }
                if ($limits) {
                    $title_parts[] = implode(', ', $limits);
                }

                if ($requiredFields) {
                    $title_parts[] = implode(', ', $requiredFields);
                }

                $pd_data["$currency:::$paymentMethod:::$aliasCode:::$code"] = implode(' | ', $title_parts);
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

    function currencies() {

        $pd_data = [];
        $r = $this->_request('GET', '/api/v1/integration/assets/currencies');

        if (200 == $r['status_code'] && !empty($r['json']['data'])) {
            $pd_data = array_filter($r['json']['data'], fn($item) => 'FIAT' == $item['currencyType'] && "{$item['assetCode']}EXTERNAL" == $item['publicCode']);
            $pd_data = array_column($pd_data, null, 'assetCode');
        }

        $r['pd'] = $pd_data;

        return $r;
    }

    private function _request($method, $path, $options = []) {
        global $premiumbox;

        $method = strtoupper($method);
        $o = array_merge($this->cfg['opts'], $options);

        ////////////////////////////////////////
        $o['headers'] = array_merge([
            'x-api-key' => $this->cfg['API_KEY'],
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
