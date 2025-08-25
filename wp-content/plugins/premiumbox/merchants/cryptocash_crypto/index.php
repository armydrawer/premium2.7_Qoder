<?php

if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Crypto-Cash Crypto[:en_US][ru_RU:]Crypto-Cash Crypto[:ru_RU]
description: [en_US:]Crypto-Cash Crypto merchant[:en_US][ru_RU:]мерчант Crypto-Cash Crypto[:ru_RU]
version: 2.7.2
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_cryptocash_crypto')) {
    class merchant_cryptocash_crypto extends Ext_Merchant_Premiumbox {
        private array $disable_opts = ['note', 'check_api', 'check'];
        private array $sum_to_pay = [];
        private array $tx_statuses = [
            'realpay' => ['PAID', 'UNDERPAID', 'OVERPAID'],
            'coldpay' => ['WAITING'],
            'amlerror' => ['AML KYC'],
            'mercherror' => ['AML FROZEN'],
            'verify' => ['CURRENCY MISMATCH'],
        ];

        function __construct($file, $title) {
            parent::__construct($file, $title, !in_array('ext_cron', $this->disable_opts));

            $this->tx_statuses = array_merge(...array_map(fn($statuses, $processor) => array_fill_keys($statuses, $processor), $this->tx_statuses, array_keys($this->tx_statuses)));

            if (!in_array('ext_webhook', $this->disable_opts)) {
                foreach ($this->get_ids('merchants', $this->name) as $id) {
                    add_action("premium_merchant_{$id}_webhook" . hash_url($id), [$this, 'webhook']);
                }
            }

            if ('address' == $this->merch_type('none')) {
                add_filter('bcc_keys', [$this, 'set_keys']);
                add_filter('qr_keys', [$this, 'set_keys']);
            }

            if ($this->sum_to_pay) {
                add_filter('sum_to_pay', [$this, 'sum_to_pay'], 100, 2);
                add_filter('sum_from_pay', [$this, 'sum_to_pay'], 100, 2);
                add_filter('merchant_bid_sum', [$this, 'sum_to_pay'], 100, 2);
            }
        }

        function get_map() {
            return [
                'BASE_URL' => [
                    'title' => '[en_US:]Domain[:en_US][ru_RU:]Домен[:ru_RU]',
                    'view' => 'input',
                    'hidden' => false,
                ],
                'PUBLIC_KEY' => [
                    'title' => '[en_US:]Public key <span class="bred">*</span>[:en_US][ru_RU:]Публичный ключ <span class="bred">*</span>[:ru_RU]',
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
            return [['PUBLIC_KEY', 'SECRET_KEY']];
        }

        function options($options, $data, $id, $place) {
            $m_define = $this->get_file_data($id);
            $m_data = get_merch_data($id);

            $options = pn_array_unset($options, $this->disable_opts);
            if (!in_array($this->merch_type($id), ['address', 'coupon'])) $options = pn_array_unset($options, ['pagenote']);
            if (in_array('ext_cron', $this->disable_opts)) $options = pn_array_unset($options, ['cronhash']);
            if (in_array('ext_webhook', $this->disable_opts)) $options = pn_array_unset($options, ['enableip', 'resulturl', 'help_resulturl']);

            $options['ext_line'] = ['view' => 'line'];

            $currency = [0 => __('Config file is not configured', 'pn')];
            $text_check_api = [0 => '<strong class="bred">' . mb_strtoupper(__('error', 'pn')) . '</strong>'];

            if (1 == $place && 'success' == $this->settingtext('success', $id)) {
                $api = new M_CRYPTOCASH_C($this->name, $id, $m_define, $m_data);

                $r = $api->currencies(is_isset($data, 'add_currency'));

                if ($r['pd_tickers']) {
                    $currency = [0 => '-- ' . __('Automatically', 'pn') . ' --'] + $r['pd_tickers'];
                }

                if (201 == $r['status_code']) {
                    $text_check_api[0] = '<strong class="bgreen">' . mb_strtoupper(__('ok', 'pn')) . '</strong>';
                }
            }

            $options['currency'] = [
                'view' => 'select_search',
                'title' => __('Currency name', 'pn'),
                'options' => $currency,
                'default' => is_isset($data, 'currency'),
                'name' => 'currency',
                'work' => 'input',
            ];

            $example = ['BTC=Bitcoin', 'USDT', __('Code', 'pn'), __('Code', 'pn') . '=' . __('Title', 'pn')];
            $options = $this->_add_field($options, $data, 'currency', $example);

            $options['work_status'] = ['view' => 'textfield', 'title' => __('Work status', 'pn'), 'default' => implode('<br/>', $text_check_api)];

            $text_add_info = array_filter([
                'webhook_url' => !in_array('ext_webhook', $this->disable_opts) ? '<strong>Webhook:</strong> <a href="' . get_mlink("{$id}_webhook" . hash_url($id)) . '" target="_blank">' . get_mlink("{$id}_webhook" . hash_url($id)) . '</a>' : null,
                'cron_url' => !in_array('ext_cron', $this->disable_opts) ? '<strong>' . __('Cron file', 'pn') . ':</strong> <a href="' . get_mlink("{$id}_cron" . chash_url($id)) . '" target="_blank">' . get_mlink("{$id}_cron" . chash_url($id)) . '</a>' : null,
            ]);

            if ($text_add_info) {
                $options['add_info'] = ['view' => 'textfield', 'title' => '', 'default' => implode('<br/>', $text_add_info)];
            }

            return $options;
        }

        function merch_type($m_id) {

            return 'address';
        }

        function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {

            $type = $this->merch_type($m_id);

            return $this->{"init_$type"}($m_id, $pay_sum, $direction, $m_defin, $m_data);
        }

        function init_address($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $bids_data;

            if ($bids_data->to_account) {
                return true;
            }

            // BID DATA

            // M DATA
            $_currency = pn_strip_input(is_isset($m_data, 'currency'));
            if (!$_currency) {
                $currency_id_give = $bids_data->currency_id_give;
                $cd = get_currency_data([$currency_id_give]);
                $_currency = is_xml_value(isset($cd[$currency_id_give]) ? $cd[$currency_id_give]->xml_value : $_currency);
            }

            $api = new M_CRYPTOCASH_C($this->name, $m_id, $m_defin, $m_data);

            $data = [
                'amount' => $pay_sum,
                'ticker' => $_currency,
                'externalId' => "m_{$bids_data->id}",
            ];
            $r = $api->sale($data)['json'];

            $tx_id = !empty($r['data']['item']['id']) ? pn_strip_input($r['data']['item']['id']) : null;
            $tx_to_account = !empty($r['data']['item']['address']) ? pn_strip_input($r['data']['item']['address']) : null;
            $tx_dest_tag = !empty($r['data']['item']['memo']) ? pn_strip_input($r['data']['item']['memo']) : '';

            $checked_fields = [$tx_id, $tx_to_account];
            if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                return false;
            }

            $update_data = [
                'trans_in' => $tx_id,
                'to_account' => $tx_to_account,
                'dest_tag' => $tx_dest_tag,
            ];
            $bids_data = update_bid_tb_array($bids_data->id, $update_data, $bids_data);

            $notify_tags = [
                '[bid_id]' => $bids_data->id,
                '[address]' => $tx_to_account,
                '[sum]' => $pay_sum,
                '[dest_tag]' => $tx_dest_tag,
                '[currency_code_give]' => $bids_data->currency_code_give,
                '[count]' => $this->confirm_count($m_id, $m_defin, $m_data),
            ];

            $admin_locale = get_admin_lang();
            $now_locale = get_locale();
            set_locale($admin_locale);

            $user_send_data = ['admin_email' => 1];
            $result_mail = apply_filters('premium_send_message', 0, 'generate_merchaddress2', $notify_tags, $user_send_data);

            set_locale($now_locale);

            $user_send_data = ['user_email' => $bids_data->user_email];
            $user_send_data = apply_filters('user_send_data', $user_send_data, 'generate_merchaddress', $bids_data);
            $result_mail = apply_filters('premium_send_message', 0, 'generate_merchaddress', $notify_tags, $user_send_data);

            return !empty($bids_data->to_account);
        }

        function webhook() {
            $m_id = key_for_url('_webhook');
            $m_define = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            $data = pn_json_decode(file_get_contents('php://input')) ?? [];

            do_action('merchant_secure', $this->name, $data, $m_id, $m_define, $m_data);

            $decoded_data = !empty($data['data']) ? base64_decode($data['data']) : null;

            if (empty($decoded_data)) {
                wp_send_json_success();
            }

            $tx_id = !empty($decoded_data['id']) ? pn_strip_input($decoded_data['id']) : null;
            $tx_status = !empty($decoded_data['status']) ? mb_strtoupper($decoded_data['status']) : null;
            $tx_dir = !empty($decoded_data['transactionType']) ? mb_strtoupper($decoded_data['transactionType']) : null;

            $checked_fields = [$tx_id, $tx_status, $tx_dir];
            if (count(array_filter($checked_fields)) !== count($checked_fields) || 'SALE' != $tx_dir || empty($this->tx_statuses[$tx_status])) {
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

                $api = new M_CRYPTOCASH_C($this->name, $m_id, $m_define, $m_data);
                $history = (empty($bids) || $tx_id) ? null : $api->get_payments()['pd'];

                foreach ($bids as $bid) {
                    $bid_id = $bid->id;
                    $bid_tx_id = $bid->trans_in;

                    $tx = is_array($tx_info) ? $tx_info : ($history[$bid_tx_id] ?? null);

                    if (empty($tx['id'])) {
                        $tx = $api->get_payment($bid_tx_id)['pd'];
                    }

                    $tx_id = !empty($tx['id']) ? pn_strip_input($tx['id']) : null;
                    $tx_status = !empty($tx['status']) ? mb_strtoupper($tx['status']) : null;
                    $tx_amount = !empty($tx['amount']) ? $this->sum_to_pay($tx['amount'], $m_id) : null;
                    $tx_currency = !empty($tx['pair']) ? mb_strtoupper(explode('/', $tx['pair'])[0]) : null;
                    $tx_hash = !empty($tx['hash']) ? pn_strip_input($tx['hash']) : null;
                    $tx_purse_from = '';
                    $tx_purse = '';

                    $checked_fields = [$tx_id, $tx_status, $tx_currency];
                    if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                        continue;
                    }

                    $new_status = $this->tx_statuses[$tx_status] ?? '';

                    if ('AML FROZEN' == $tx_status && !get_bids_meta($bid_id, 'refund')) {
                        update_bids_meta($bid_id, 'refund', true);

                        $unmetas = @unserialize($bid->unmetas);
                        $refund = pn_strip_input(is_isset($unmetas, 'give_refund') ?: is_isset($unmetas, 'refund'));

                        if ($refund) {
                            $data = [
                                'internalId' => $bid_tx_id,
                                'address' => $refund,
                            ];
                            $api->refund($data);
                        }
                    }

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

        function sum_to_pay($sum, $m_in) {

            if (!$m_in || get_mscript($m_in) !== $this->name) {
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

    }
}

new merchant_cryptocash_crypto(__FILE__, 'Crypto-Cash Crypto');
