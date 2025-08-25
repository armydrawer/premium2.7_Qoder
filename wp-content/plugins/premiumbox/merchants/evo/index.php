<?php

if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Evo[:en_US][ru_RU:]Evo[:ru_RU]
description: [en_US:]Evo merchant[:en_US][ru_RU:]мерчант Evo[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_evo')) {
    class merchant_evo extends Ext_Merchant_Premiumbox {
        private array $disable_opts = ['note', 'check_api', 'check'];
        private array $sum_to_pay = [2];
        private array $tx_statuses = [
            'realpay' => ['SUCCESS'],
            'coldpay' => [],
        ];

        function __construct($file, $title) {
            parent::__construct($file, $title, !in_array('ext_cron', $this->disable_opts));

            if (!in_array('ext_webhook', $this->disable_opts)) {
                $ids = $this->get_ids('merchants', $this->name);
                foreach ($ids as $id) {
                    add_action('premium_merchant_' . $id . '_webhook' . hash_url($id), [$this, 'webhook']);
                }
            }

            if ($this->sum_to_pay) {
                add_filter('sum_to_pay', [$this, 'sum_to_pay'], 100, 2);
                add_filter('sum_from_pay', [$this, 'sum_to_pay'], 100, 2);
                add_filter('merchant_bid_sum', [$this, 'sum_to_pay'], 100, 2);
            }

            add_filter('after_set_merchant', [$this, 'after_set_merchant'], 10, 2);
            add_filter('recalc_pay_sum', [$this, 'recalc_pay_sum'], 50, 4);
        }

        function get_map() {
            return [
                'BASE_URL' => [
                    'title' => '[en_US:]Domain[:en_US][ru_RU:]Домен[:ru_RU]',
                    'view' => 'input',
                    'hidden' => false,
                ],
                'API_KEY' => [
                    'title' => '[en_US:]API key <span class="bred">*</span>[:en_US][ru_RU:]API ключ <span class="bred">*</span>[:ru_RU]',
                    'view' => 'input',
                    'hidden' => true,
                ],
            ];
        }

        function settings_list() {
            return [['API_KEY']];
        }

        function options($options, $data, $id, $place) {
            $m_define = $this->get_file_data($id);
            $m_data = get_merch_data($id);

            $options = pn_array_unset($options, $this->disable_opts);
            if (in_array('ext_cron', $this->disable_opts)) $options = pn_array_unset($options, ['cronhash']);
            if (in_array('ext_webhook', $this->disable_opts)) $options = pn_array_unset($options, ['enableip', 'resulturl', 'help_resulturl']);
            if (!in_array($this->merch_type($id), ['address', 'coupon'])) $options = pn_array_unset($options, ['pagenote']);

            $options[] = ['view' => 'line'];

            $text_check_api = [0 => '<strong class="bred">' . mb_strtoupper(__('error', 'pn')) . '</strong>'];

            if (1 == $place && 'success' == $this->settingtext('success', $id)) {
                $api = new M_EVO($this->name, $id, $m_define, $m_data);

                $r = $api->get_orders('00000000-0000-0000-0000-000000000000');

                if (200 == $r['status_code']) {
                    $text_check_api[0] = '<strong class="bgreen">' . mb_strtoupper(__('ok', 'pn')) . '</strong>';
                }
            }

            $add_payment_method = is_isset($data, 'add_payment_method');
            $options['payment_method'] = [
                'view' => 'select',
                'title' => __('Payment method', 'pn'),
                'options' => [0 => '-- ' . __('All', 'pn') . ' --'] + M_EVO::payment_methods($add_payment_method),
                'default' => is_isset($data, 'payment_method'),
                'name' => 'payment_method',
                'work' => 'input',
            ];

            $example = ['SBP=SBP', 'BANK_CARD', __('Code', 'pn'), __('Code', 'pn') . '=' . __('Title', 'pn')];
            $options = $this->_add_field($options, $data, 'payment_method', $example);

            $options['recalc_change_sum'] = [
                'view' => 'select',
                'title' => __('Order recalculation upon changing payment amount', 'pn'),
                'options' => [
                    0 => __('No', 'pn'),
                    4 => __('Yes, always', 'pn'),
                    1 => __('Yes, if payment amount changed', 'pn'),
                    2 => __('Yes, if payment amount increased', 'pn'),
                    3 => __('Yes, if payment amount decreased', 'pn'),
                ],
                'default' => is_isset($data, 'recalc_change_sum'),
                'name' => 'recalc_change_sum',
                'work' => 'int',
            ];

            $options[] = ['view' => 'textfield', 'title' => __('Work status', 'pn'), 'default' => implode('<br/>', $text_check_api)];

            $text_add_info = array_values(array_filter([
                'webhook' => !in_array('ext_webhook', $this->disable_opts) ? '<strong>Webhook:</strong> <a href="' . get_mlink($id . '_webhook' . hash_url($id)) . '" target="_blank">' . get_mlink($id . '_webhook' . hash_url($id)) . '</a>' : null,
                'cron' => !in_array('ext_cron', $this->disable_opts) ? '<strong>' . __('Cron file', 'pn') . ':</strong> <a href="' . get_mlink($id . '_cron' . chash_url($id)) . '" target="_blank">' . get_mlink($id . '_cron' . chash_url($id)) . '</a>' : null,
            ]));

            if ($text_add_info) {
                $options[] = ['view' => 'textfield', 'title' => '', 'default' => implode('<br/>', $text_add_info)];
            }

            return $options;
        }

        function merch_type($m_id) {

            return 'mypaid';
        }

        function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {

            $type = $this->merch_type($m_id);

            return $this->{"init_$type"}($m_id, $pay_sum, $direction, $m_defin, $m_data);
        }

        function init_mypaid($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $bids_data;

            if ($bids_data->to_account) {
                return true;
            }

            // BID DATA
            $timestamp = time();
            $currency_code_give = mb_strtoupper($bids_data->currency_code_give);

            // M DATA
            $pm = pn_strip_input(is_isset($m_data, 'payment_method'));
            $recalc_change_sum = absint(is_isset($m_data, 'recalc_change_sum'));

            $api = new M_EVO($this->name, $m_id, $m_defin, $m_data);

            $data = [
                'customId' => "m_{$bids_data->id}_{$timestamp}",
                'fiatSum' => $pay_sum,
                'fiatCurrencyCode' => $currency_code_give,
                'cryptoCurrencyCode' => 'USDT',
            ];
            if ($pm) $data['paymentMethod'] = $pm;

            $r = $api->payin($data)['json'];

            $tx_id = !empty($r['id']) ? pn_strip_input($r['id']) : null;

            if (empty($tx_id)) {
                return false;
            }

            for ($i = 0; $i < 8; $i++) {
                sleep(2);

                $r = $api->get_orders($tx_id)['pd'];
                $tx = $r[$tx_id] ?? null;

                if (empty($tx['id'])) continue;

                $tx_id = !empty($tx['id']) ? pn_strip_input($tx['id']) : null;
                $tx_method = !empty($tx['paymentMethod']) ? mb_strtoupper($tx['paymentMethod']) : null;
                $tx_purse_phone = !empty($tx['requisites']['recipient_phone_number']) ? pn_strip_input($tx['requisites']['recipient_phone_number']) : null;
                $tx_purse_card = !empty($tx['requisites']['recipient_card_number']) ? pn_strip_input($tx['requisites']['recipient_card_number']) : null;
                $tx_purse = 'SBP' == $tx_method ? $tx_purse_phone : $tx_purse_card;
                $tx_cardholder = !empty($tx['requisites']['recipient_full_name']) ? pn_strip_input($tx['requisites']['recipient_full_name']) : null;
                $tx_bank_name = !empty($tx['requisites']['recipient_bank']) ? pn_strip_input($tx['requisites']['recipient_bank']) : null;
                $tx_amount = isset($tx['fiatSum']) ? $this->sum_to_pay($tx['fiatSum'], $m_id) : null;

                $checked_fields = [$tx_id, $tx_purse, $tx_amount];
                if (count(array_filter($checked_fields)) === count($checked_fields)) {
                    break;
                }
            }

            $checked_fields = [$tx_id, $tx_purse, $tx_amount];
            if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                return false;
            }

            if ($tx_purse) {

                $to_account = preg_replace('/\D/', '', $tx_purse);
                if ('SBP' == $tx_method) {
                    $to_account = '+' . (10 == mb_strlen($to_account) ? '7' : '') . $to_account;
                }

                $to_account = pn_strip_input($to_account);

                $dt_data = array_values(array_filter([
                    'cardholder' => !empty($tx_cardholder) ? $tx_cardholder : null,
                    'bank_name' => !empty($tx_bank_name) ? $tx_bank_name : null,
                ]));
                $tx_dest_tag = pn_strip_input($dt_data ? (1 == count($dt_data) ? $dt_data[0] : "{$dt_data[0]} (" . implode(', ', array_slice($dt_data, 1)) . ")") : '');

                if ($to_account) {

                    $update_data = [
                        'trans_in' => $tx_id,
                        'to_account' => $to_account,
                        'dest_tag' => $tx_dest_tag,
                    ];

                    if ($pay_sum != $tx_amount) {
                        $sum_keys = [0 => 'sum1dc', 1 => 'sum1c', 2 => 'sum1r', 3 => 'sum1'];
                        $sum_key = $sum_keys[is_sfp_merchant($m_id)] ?? 'sum1dc';
                        $update_data[$sum_key] = $tx_amount;

                        if ($recalc_change_sum && function_exists('recalculation_bid')) {
                            update_bids_meta($bids_data->id, 'need_first_recalc', true);
                        }
                    }

                    $bids_data = update_bid_tb_array($bids_data->id, $update_data, $bids_data);
                }
            }

            return !empty($bids_data->to_account);
        }

        function myaction($m_id, $pay_sum, $direction) {
            global $bids_data;

            if (get_mscript($m_id) !== $this->name || empty($bids_data->id) || empty($bids_data->trans_in)) {
                return $m_id;
            }

            $m_define = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            $api = new M_EVO($this->name, $m_id, $m_define, $m_data);

            $r = $api->get_orders($bids_data->trans_in)['pd'];
            $tx = $r[$bids_data->trans_in] ?? null;

            $tx_id = !empty($tx['id']) ? pn_strip_input($tx['id']) : null;
            $tx_status = !empty($tx['orderStatus']) ? mb_strtoupper($tx['orderStatus']) : null;

            $checked_fields = [$tx_id, $tx_status];
            if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                return $m_id;
            }

            $params = [
                'bid_status' => get_status_sett('merch', 1),
                'm_place' => $m_id . '_' . __FUNCTION__,
                'm_id' => $m_id,
                'm_data' => $m_data,
                'm_defin' => $m_define,
            ];
            set_bid_status('payed', $bids_data->id, $params, $direction);

            if (in_array($tx_status, array_merge(...array_values($this->tx_statuses)))) {
                $this->payment_check(__FUNCTION__, $m_id, $m_define, $m_data, $tx_id);
            }

            return $m_id;
        }

        function webhook() {
            $m_id = key_for_url('_webhook');
            $m_define = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            $data = pn_json_decode(file_get_contents('php://input')) ?? [];

            do_action('merchant_secure', $this->name, $data, $m_id, $m_define, $m_data);

            $webhook_type = !empty($data['webhook_type']) ? mb_strtoupper($data['webhook_type']) : null;

            if ('ORDER' !== $webhook_type) {
                wp_send_json_success();
            }

            $tx_id = !empty($data['order']['id']) ? pn_strip_input($data['order']['id']) : null;
            $tx_status = !empty($data['order']['order_status']) ? mb_strtoupper($data['order']['order_status']) : null;
            $tx_dir = !empty($data['order']['order_type']) ? mb_strtoupper($data['order']['order_type']) : null;

            $checked_fields = [$tx_id, $tx_status, $tx_dir];
            if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                wp_send_json_success();
            }

            if ('PAYIN' != $tx_dir || !in_array($tx_status, array_merge(...array_values($this->tx_statuses)))) {
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

                $invalid_check = !in_array('check', $this->disable_opts) ? intval(is_isset($m_data, 'check')) : null;
                $invalid_ctype = !in_array('invalid_ctype', $this->disable_opts) ? intval(is_isset($m_data, 'invalid_ctype')) : null;
                $invalid_minsum = !in_array('invalid_minsum', $this->disable_opts) ? intval(is_isset($m_data, 'invalid_minsum')) : null;
                $invalid_maxsum = !in_array('invalid_maxsum', $this->disable_opts) ? intval(is_isset($m_data, 'invalid_maxsum')) : null;

                $api = new M_EVO($this->name, $m_id, $m_define, $m_data);
                $history = $tx_id ? null : $api->get_orders()['pd'];

                $workstatus = _merch_workstatus($m_id, ['new', 'techpay', 'coldpay', 'payed']);

                $where = [
                    $wpdb->prepare("`m_in` = %s", $m_id),
                    "`status` IN ('" . implode("','", $workstatus) . "')",
                    "`trans_in` <> '0'",
                ];

                if ($tx_id) {
                    $where[] = $wpdb->prepare("`trans_in` = %s", $tx_id);
                }

                $bids = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}exchange_bids WHERE " . implode(' AND ', $where));

                foreach ($bids as $bid) {
                    $bid_id = $bid->id;
                    $bid_tx_id = $bid->trans_in;

                    $tx = is_array($tx_info) ? $tx_info : ($history[$bid_tx_id] ?? null);

                    if (empty($tx['id'])) {
                        $r = $api->get_orders($bid_tx_id)['pd'];
                        $tx = $r[$bid_tx_id] ?? null;
                    }

                    $tx_id = !empty($tx['id']) ? pn_strip_input($tx['id']) : null;
                    $tx_status = !empty($tx['orderStatus']) ? mb_strtoupper($tx['orderStatus']) : null;
                    $tx_amount = !empty($tx['finalFiatSum']) ? $this->sum_to_pay($tx['finalFiatSum'], $m_id) : null;
                    $tx_currency = !empty($tx['fiatCurrencyCode']) ? mb_strtoupper($tx['fiatCurrencyCode']) : null;
                    $tx_hash = '';
                    $tx_purse_from = '';
                    $tx_purse = '';

                    $checked_fields = [$tx_id, $tx_status, $tx_amount, $tx_currency];
                    if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                        continue;
                    }

                    $new_status = array_search(true, array_map(fn($value) => in_array($tx_status, $value), $this->tx_statuses)) ?: '';

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

                        if (0 === $invalid_check && $bid_account != $tx_purse_from) {
                            $this->logs("{$bid_id} | Another account wallet is expected: {$bid_account} != {$tx_purse_from}", $m_id);
                            continue;
                        }

                        if (0 === $invalid_ctype && $bid_currency != $tx_currency) {
                            $this->logs("{$bid_id} | Wrong type of currency: {$bid_currency} != {$tx_currency}", $m_id);
                            continue;
                        }

                        if (0 === $invalid_minsum && $tx_amount < $bid_corr_sum) {
                            $this->logs("{$bid_id} | The payment amount is less than the provisions: {$tx_amount} < {$bid_corr_sum}", $m_id);
                            continue;
                        }

                        if (0 === $invalid_maxsum && $tx_amount > $bid_sum) {
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

        function after_set_merchant($bid, $direction) {

            $m_id = $bid->m_in;
            if (!$m_id || get_mscript($m_id) !== $this->name || !get_bids_meta($bid->id, 'need_first_recalc')) {
                return $bid;
            }

            $m_data = get_merch_data($m_id);

            $recalc_change_sum = absint(is_isset($m_data, 'recalc_change_sum'));

            $bid = recalculation_bid($bid->id, $bid, $recalc_change_sum, 0, $direction);
            $bid = pn_object_replace($bid, ['hashdata' => @serialize(bid_hashdata($bid->id, $bid))]);

            delete_bids_meta($bid->id, 'need_first_recalc');

            return $bid;
        }

        function recalc_pay_sum($sum_pay, $m_in, $direction, $bid) {

            if (!$m_in || get_mscript($m_in) !== $this->name || !get_bids_meta($bid->id, 'need_first_recalc')) {
                return $sum_pay;
            }

            return get_sfp($bid, $m_in);
        }

    }
}

new merchant_evo(__FILE__, 'Evo');
