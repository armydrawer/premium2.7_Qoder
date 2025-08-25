<?php

if (!defined('ABSPATH')) exit();

/*
title: [en_US:]NicePay[:en_US][ru_RU:]NicePay[:ru_RU]
description: [en_US:]NicePay merchant[:en_US][ru_RU:]мерчант NicePay[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_nicepay')) {
    class merchant_nicepay extends Ext_Merchant_Premiumbox {
        private array $disable_opts = ['check_api', 'check', 'invalid_minsum', 'invalid_maxsum'];
        private array $sum_to_pay = [2];
        private array $tx_statuses = [
            'realpay' => [5],
            'coldpay' => [4, 81, 93],
        ];

        function __construct($file, $title) {
            parent::__construct($file, $title, !in_array('ext_cron', $this->disable_opts));

            $this->tx_statuses = array_replace(...array_map(fn($statuses, $processor) => array_fill_keys($statuses, $processor), $this->tx_statuses, array_keys($this->tx_statuses)));

            if (!in_array('ext_webhook', $this->disable_opts)) {
                foreach ($this->get_ids('merchants', $this->name) as $id) {
                    add_action("premium_merchant_{$id}_webhook" . hash_url($id), [$this, 'webhook']);
                }
            }

            if ($this->sum_to_pay) {
                add_filter('sum_to_pay', [$this, 'sum_to_pay'], 100, 2);
                add_filter('sum_from_pay', [$this, 'sum_to_pay'], 100, 2);
                add_filter('merchant_bid_sum', [$this, 'sum_to_pay'], 100, 2);
            }

            foreach ($this->get_ids('merchants', $this->name) as $id) {
                add_action("premium_merchant_{$id}_success", [$this, 'merchant_success']);
                add_action("premium_merchant_{$id}_fail", [$this, 'merchant_fail']);
            }
        }

        function get_map() {
            return [
                'BASE_URL' => [
                    'title' => '[en_US:]Domain[:en_US][ru_RU:]Домен[:ru_RU]',
                    'view' => 'input',
                    'hidden' => false,
                ],
                'MERCHANT_ID' => [
                    'title' => '[en_US:]Merchant ID <span class="bred">*</span>[:en_US][ru_RU:]ID мерчанта <span class="bred">*</span>[:ru_RU]',
                    'view' => 'input',
                    'hidden' => true,
                ],
                'SECRET_KEY' => [
                    'title' => '[en_US:]Secret key <span class="bred">*</span>[:en_US][ru_RU:]Секретный ключ <span class="bred">*</span>[:ru_RU]',
                    'view' => 'input',
                    'hidden' => true,
                ],
            ];
        }

        function settings_list() {
            return [['MERCHANT_ID', 'SECRET_KEY']];
        }

        function options($options, $data, $id, $place) {
            global $wpdb;

            $m_define = $this->get_file_data($id);
            $m_data = get_merch_data($id);

            $options = pn_array_unset($options, $this->disable_opts);
            if (!in_array($this->merch_type($id, $m_data), ['address', 'coupon'])) $options = pn_array_unset($options, ['pagenote']);
            if (in_array('ext_cron', $this->disable_opts)) $options = pn_array_unset($options, ['cronhash']);
            if (in_array('ext_webhook', $this->disable_opts)) $options = pn_array_unset($options, ['enableip', 'resulturl', 'help_resulturl']);

            $options['ext_line'] = ['view' => 'line'];

            $payment_methods = [0 => __('Config file is not configured', 'pn')];
            $text_check_api = [0 => '<strong class="bred">' . mb_strtoupper(__('error', 'pn')) . '</strong>'];

            if (1 == $place && 'success' == $this->settingtext('success', $id)) {
                $api = new M_NICEPAY($this->name, $id, $m_define, $m_data);

                $r = $api->paymentMethods(is_isset($data, 'add_payment_method'));

                if ($r['pd']) {
                    $payment_methods = [0 => '-- ' . __('Select method', 'pn') . ' --'] + $r['pd'];
                }

                $r = $api->h2hPaymentInfo('none');

                if (200 == $r['status_code'] && isset($r['json']['data']['message']) && 'Payment not found' == $r['json']['data']['message']) {
                    $text_check_api[0] = '<strong class="bgreen">' . mb_strtoupper(__('ok', 'pn')) . '</strong>';
                }
            }

            $merch_type = is_isset($data, 'merch_type');

            $options['merch_type'] = [
                'view' => 'select',
                'title' => __('Merchant type', 'pn') . ' <span class="bred">*</span>',
                'options' => [
                    'link' => 'Payment link',
                    'mypaid' => 'Requisites (h2hOneRequestPayment)',
                ],
                'default' => $merch_type ?: $this->merch_type($id, $m_data),
                'name' => 'merch_type',
                'work' => 'input',
            ];

            $bids_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}exchange_bids WHERE m_in = %s", $id));

            if ($merch_type && $bids_count) {
                $options['merch_type']['name'] .= '_disabled';
                $options['merch_type']['atts'] = ['disabled' => 'disabled'];
                $options['merch_type_hidden'] = ['view' => 'hidden_input', 'name' => 'merch_type', 'default' => $merch_type];
            }

            $options['payment_method'] = [
                'view' => 'select_search',
                'title' => __('Payment method', 'pn') . ' <span class="bred">*</span>',
                'options' => $payment_methods,
                'default' => is_isset($data, 'payment_method'),
                'name' => 'payment_method',
                'work' => 'input',
            ];

            $example = [__('Code', 'pn'), __('Code', 'pn') . '=' . __('Title', 'pn')];
            $options = $this->_add_field($options, $data, 'payment_method', $example);

            $options['work_status'] = ['view' => 'textfield', 'title' => __('Work status', 'pn'), 'default' => implode('<br/>', $text_check_api)];

            $text_add_info = array_filter([
                'webhook_url' => !in_array('ext_webhook', $this->disable_opts) ? '<strong>Webhook URL:</strong> <a href="' . get_mlink("{$id}_webhook" . hash_url($id)) . '" target="_blank">' . get_mlink("{$id}_webhook" . hash_url($id)) . '</a>' : null,
                'cron_url' => !in_array('ext_cron', $this->disable_opts) ? '<strong>' . __('Cron file', 'pn') . ':</strong> <a href="' . get_mlink("{$id}_cron" . chash_url($id)) . '" target="_blank">' . get_mlink("{$id}_cron" . chash_url($id)) . '</a>' : null,
                'success_url' => '<strong>Success URL:</strong> <a href="' . get_mlink("{$id}_success") . '" target="_blank">' . get_mlink("{$id}_success") . '</a>',
                'fail_url' => '<strong>Fail URL:</strong> <a href="' . get_mlink("{$id}_fail") . '" target="_blank">' . get_mlink("{$id}_fail") . '</a>',
            ]);

            if ($text_add_info) {
                $options['add_info'] = ['view' => 'textfield', 'title' => '', 'default' => implode('<br/>', $text_add_info)];
            }

            return $options;
        }

        function merch_type($m_id, $m_data = false) {

            $m_data = $m_data ?: get_merch_data($m_id);
            $merch_type = is_isset($m_data, 'merch_type');

            return in_array($merch_type, ['link', 'mypaid']) ? $merch_type : 'link';
        }

        function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {

            $type = $this->merch_type($m_id, $m_data);

            return $this->{"init_$type"}($m_id, $pay_sum, $direction, $m_defin, $m_data);
        }

        function init_link($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $bids_data;

            if ($this->get_pay_link($bids_data->id)) {
                return true;
            }

            // BID DATA
            $currency_code_give = mb_strtoupper($bids_data->currency_code_give);

            // M DATA
            $note = get_text_pay($m_id, $bids_data, $pay_sum);
            $note = pn_maxf($note ?: __('Order ID', 'pn') . ": {$bids_data->id}", 150);
            $pm = pn_strip_input(is_isset($m_data, 'payment_method'));

            $api = new M_NICEPAY($this->name, $m_id, $m_defin, $m_data);

            $data = [
                'order_id' => "m_{$bids_data->id}",
                'customer' => $bids_data->user_id,
                'amount' => $pay_sum * 100,
                'currency' => $currency_code_give,
                'description' => $note,
                'method' => $pm,
                'success_url' => apply_filters('custom_url', get_mlink("{$m_id}_success") . "?order_id={$bids_data->id}", 'success', $this->name, $m_id),
                'fail_url' => apply_filters('custom_url', get_mlink("{$m_id}_fail") . "?order_id={$bids_data->id}", 'fail', $this->name, $m_id),
                'webhook_url' => apply_filters('custom_url', get_mlink("{$m_id}_webhook" . hash_url($m_id)), 'webhook', $this->name, $m_id),
            ];
            $r = $api->paymentUrl($data)['json'];

            if (empty($r['data']['payment_id'])) {
                return false;
            }

            $tx = $r['data'];
            $tx_id = !empty($tx['payment_id']) ? pn_strip_input($tx['payment_id']) : null;
            $tx_url = !empty($tx['link']) ? pn_strip_input($tx['link']) : null;

            $checked_fields = [$tx_id, $tx_url];
            if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                return false;
            }

            $this->update_pay_link($bids_data->id, $tx_url);

            $update_data = [
                'trans_in' => $tx_id,
            ];
            $bids_data = update_bid_tb_array($bids_data->id, $update_data, $bids_data);

            return true;
        }

        function init_mypaid($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $bids_data;

            if ($bids_data->to_account) {
                return true;
            }

            // BID DATA
            $currency_code_give = mb_strtoupper($bids_data->currency_code_give);

            // M DATA
            $pm = pn_strip_input(is_isset($m_data, 'payment_method'));

            $api = new M_NICEPAY($this->name, $m_id, $m_defin, $m_data);

            $data = [
                'order_id' => "m_{$bids_data->id}",
                'customer' => $bids_data->user_id,
                'amount' => $pay_sum * 100,
                'currency' => $currency_code_give,
                'method' => $pm,
                'customer_ip' => $bids_data->user_ip,
                'webhook_url' => apply_filters('custom_url', get_mlink("{$m_id}_webhook" . hash_url($m_id)), 'webhook', $this->name, $m_id),
            ];

            $r = $api->h2hOneRequestPayment($data)['json'];

            if (empty($r['data']['paymentId'])) {
                return false;
            }

            $tx = $r['data'];
            $tx_id = !empty($tx['paymentId']) ? pn_strip_input($tx['paymentId']) : null;
            $tx_method = !empty($tx['details']['type']) ? mb_strtoupper($tx['details']['type']) : null;
            $tx_to_account = !empty($tx['details']['wallet']) ? pn_strip_input($tx['details']['wallet']) : null;
            $tx_cardholder = !empty($tx['details']['comment']) ? pn_strip_input($tx['details']['comment']) : null;

            $checked_fields = [$tx_id, $tx_method, $tx_to_account];
            if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                return false;
            }

            if ('PHONE' == $tx_method) {
                $tx_to_account = preg_replace('/\D/', '', $tx_to_account);
                $code = (10 == mb_strlen($tx_to_account) ? '7' : '');
                $tx_to_account = "+{$code}{$tx_to_account}";
            }

            $tx_to_account = pn_strip_input($tx_to_account);

            $dt_data = array_filter([
                'cardholder' => !empty($tx_cardholder) ? $tx_cardholder : null,
            ]);
            $tx_dest_tag = pn_strip_input($dt_data ? array_shift($dt_data) . ($dt_data ? ' (' . implode(', ', $dt_data) . ')' : '') : '');

            if ($tx_to_account) {
                $update_data = [
                    'trans_in' => $tx_id,
                    'to_account' => $tx_to_account,
                    'dest_tag' => $tx_dest_tag,
                ];
                $bids_data = update_bid_tb_array($bids_data->id, $update_data, $bids_data);
            }

            return !empty($bids_data->to_account);
        }

        function myaction($m_id, $pay_sum, $direction) {
            global $bids_data;

            if (get_mscript($m_id) !== $this->name || empty($bids_data->id) || empty($bids_data->trans_in)) {
                return $m_id;
            }

            $bid_tx_id = $bids_data->trans_in;

            $m_define = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            $api = new M_NICEPAY($this->name, $m_id, $m_define, $m_data);

            $tx = $api->h2hPaymentInfo($bid_tx_id)['pd'];

            $tx_id = !empty($tx['paymentId']) ? pn_strip_input($tx['paymentId']) : null;
            $tx_status = !empty($tx['status']) ? mb_strtoupper($tx['status']) : null;

            $checked_fields = [$tx_id, $tx_status];
            if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                return $m_id;
            }

            $api->h2hConfirmPaid($tx_id);

            $params = [
                'bid_status' => get_status_sett('merch', 1),
                'm_place' => "{$m_id}_" . __FUNCTION__,
                'm_id' => $m_id,
                'm_data' => $m_data,
                'm_defin' => $m_define,
            ];
            set_bid_status('payed', $bids_data->id, $params, $direction);

            if (!empty($this->tx_statuses[$tx_status])) {
                $this->payment_check(__FUNCTION__, $m_id, $m_define, $m_data, $tx_id);
            }

            return $m_id;
        }

        function webhook() {
            $m_id = key_for_url('_webhook');
            $m_define = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            $data = $_GET ?? [];

            do_action('merchant_secure', $this->name, $data, $m_id, $m_define, $m_data);

            $tx_id = !empty($data['payment_id']) ? pn_strip_input($data['payment_id']) : null;
            $tx_status = !empty($data['result']) ? mb_strtoupper($data['result']) : null;

            $checked_fields = [$tx_id, $tx_status];
            if (count(array_filter($checked_fields)) !== count($checked_fields) || 'SUCCESS' != $tx_status) {
                wp_send_json_success();
            }

            $this->payment_check(__FUNCTION__, $m_id, $m_define, $m_data, $tx_id);

            wp_send_json_success();
        }

        function cron($m_id, $m_defin, $m_data) {

            $this->payment_check(__FUNCTION__, $m_id, $m_defin, $m_data);

        }

        private function payment_check($place, $m_id, $m_define, $m_data, $tx_info = false) {
            global $wpdb;

            try {
                $tx_id = is_array($tx_info) ? is_isset($tx_info, 'tx_id') : $tx_info;

                $check_statuses = apply_filters('set_bid_status_for_verify', ['realpay']);
                $invalid_check = !in_array('check', $this->disable_opts) ? intval(is_isset($m_data, 'check')) : null;
                $invalid_ctype = !in_array('invalid_ctype', $this->disable_opts) ? intval(is_isset($m_data, 'invalid_ctype')) : null;
                $invalid_minsum = !in_array('invalid_minsum', $this->disable_opts) ? intval(is_isset($m_data, 'invalid_minsum')) : null;
                $invalid_maxsum = !in_array('invalid_maxsum', $this->disable_opts) ? intval(is_isset($m_data, 'invalid_maxsum')) : null;
                $workstatus = _merch_workstatus($m_id, ['new', 'techpay', 'coldpay', 'payed']);

                $where = [
                    $wpdb->prepare("`m_in` = %s", $m_id),
                    "`status` IN ('" . implode("','", $workstatus) . "')",
                    $tx_id ? $wpdb->prepare("`trans_in` = %s", $tx_id) : "`trans_in` <> '0'",
                ];

                $bids = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}exchange_bids WHERE " . implode(' AND ', $where));

                $api = new M_NICEPAY($this->name, $m_id, $m_define, $m_data);
                $history = (empty($bids) || $tx_id) ? null : $api->get_payments()['pd'];

                foreach ($bids as $bid) {
                    $bid_id = $bid->id;
                    $bid_tx_id = $bid->trans_in;

                    $tx = is_array($tx_info) ? $tx_info : ($history[$bid_tx_id] ?? null);

                    if (empty($tx['none'])) {
                        $tx = $api->h2hPaymentInfo($bid_tx_id)['pd'];
                    }

                    $tx_id = !empty($tx['paymentId']) ? pn_strip_input($tx['paymentId']) : null;
                    $tx_status = !empty($tx['status']) ? mb_strtoupper($tx['status']) : null;
                    $tx_amount = !empty($tx['amountNum']) ? $this->sum_to_pay($tx['amountNum'], $m_id) : null;
                    $tx_currency = !empty($tx['currency']['currency']) ? mb_strtoupper($tx['currency']['currency']) : null;
                    $tx_hash = '';
                    $tx_purse_from = '';
                    $tx_purse = '';

                    $checked_fields = [$tx_id, $tx_status, $tx_currency];
                    if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                        continue;
                    }

                    $new_status = $this->tx_statuses[$tx_status] ?? '';

                    if (in_array($new_status, ['realpay', 'coldpay'])) {

                        $data = get_data_merchant_for_id($bid_id, $bid);
                        $err = $data['err'];
                        $bid_m_id = $data['m_id'];
                        $bid_m_script = $data['m_script'];
                        $bid_currency = $data['currency'];
                        $bid_sum = $data['pay_sum'];
                        $bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
                        $bid_account = apply_filters('pay_purse_merchant', $bid->account_give);
                        $tx_purse_from = $tx_purse_from ? is_pay_purse($tx_purse_from, $m_data, $bid_m_id) : $tx_purse_from;

                        if (check_trans_in($bid_m_id, $tx_id, $bid_id)) {
                            $this->logs("{$bid_id} | Error check trans in!", $m_id);
                            continue;
                        }

                        if ($err || !$bid_m_id || $bid_m_id != $m_id || !$bid_m_script || $bid_m_script != $this->name) {
                            $this->logs("{$bid_id} | Bid error", $m_id);
                            continue;
                        }

                        if (in_array($new_status, $check_statuses) && 0 === $invalid_check && $bid_account != $tx_purse_from) {
                            $this->logs("{$bid_id} | Another sender purse: {$bid_account} != {$tx_purse_from}", $m_id);
                            continue;
                        }

                        if (in_array($new_status, $check_statuses) && 0 === $invalid_ctype && $bid_currency != $tx_currency) {
                            $this->logs("{$bid_id} | Wrong type of currency: {$bid_currency} != {$tx_currency}", $m_id);
                            continue;
                        }

                        if (in_array($new_status, $check_statuses) && 0 === $invalid_minsum && $tx_amount < $bid_corr_sum) {
                            $this->logs("{$bid_id} | The payment amount is less than the provisions: {$tx_amount} < {$bid_corr_sum}", $m_id);
                            continue;
                        }

                        if (in_array($new_status, $check_statuses) && 0 === $invalid_maxsum && $tx_amount > $bid_sum) {
                            $this->logs("{$bid_id} | The payment amount is greater than the provisions: {$tx_amount} > {$bid_sum}", $m_id);
                            continue;
                        }

                        $params = [
                            'sum' => 'coldpay' == $new_status ? null : $tx_amount,
                            'bid_sum' => $bid_sum,
                            'bid_corr_sum' => $bid_corr_sum,
                            'pay_purse' => $tx_purse_from,
                            'to_account' => $tx_purse,
                            'trans_in' => $tx_id,
                            'txid_in' => $tx_hash,
                            'currency' => $tx_currency,
                            'bid_currency' => $bid_currency,
                            'invalid_ctype' => $invalid_ctype,
                            'invalid_minsum' => $invalid_minsum,
                            'invalid_maxsum' => $invalid_maxsum,
                            'invalid_check' => $invalid_check,
                            'bid_status' => $workstatus,
                            'm_place' => "{$m_id}_{$place}",
                            'm_id' => $m_id,
                            'm_data' => $m_data,
                            'm_defin' => $m_define,
                        ];
                        set_bid_status($new_status, $bid_id, $params, $data['direction_data']);

                    } elseif ($new_status) {

                        $params = [
                            'm_place' => "{$m_id}_{$place}",
                            'm_id' => $m_id,
                            'm_data' => $m_data,
                            'm_defin' => $m_define,
                        ];
                        set_bid_status($new_status, $bid_id, $params);

                    }
                }
            } catch (Exception $e) {
                $this->logs(pn_json_encode(['line' => $e->getLine(), 'file' => realpath($e->getFile()), 'message' => $e->getMessage()]), $m_id);

                $show_error = intval(is_isset($m_data, 'show_error'));
                if ($show_error && current_user_can('administrator')) {
                    die($e->getMessage());
                }
            }
        }

        /* OTHER SETTINGS */

        function sum_to_pay($sum, $m_id) {

            if (!$m_id || get_mscript($m_id) !== $this->name) {
                return $sum;
            }

            return (float)is_sum($sum, ...$this->sum_to_pay);
        }

        function _add_field($options, $data, $name, $help = false) {

            $options["add_{$name}"] = [
                'view' => 'textarea',
                'title' => __('Add new', 'pn'),
                'default' => is_isset($data, "add_{$name}"),
                'name' => "add_{$name}",
                'rows' => 5,
                'work' => 'text',
            ];

            if ($help) {
                $options["add_{$name}_help"] = ['view' => 'help', 'title' => __('Example', 'pn'), 'default' => implode('<br/>', $help)];
            }

            return $options;
        }

        function merchant_success() {
            redirect_merchant_action(is_param_get('order_id'), $this->name, 1);
        }

        function merchant_fail() {
            redirect_merchant_action(is_param_get('order_id'), $this->name);
        }

    }
}

new merchant_nicepay(__FILE__, 'NicePay');
