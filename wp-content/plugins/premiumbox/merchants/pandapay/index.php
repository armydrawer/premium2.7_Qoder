<?php

if (!defined('ABSPATH')) exit();

/*
title: [en_US:]PandaPay[:en_US][ru_RU:]PandaPay[:ru_RU]
description: [en_US:]PandaPay merchant[:en_US][ru_RU:]мерчант PandaPay[:ru_RU]
version: 2.7.2
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_pandapay')) {
    class merchant_pandapay extends Ext_Merchant_Premiumbox {
        private array $disable_opts = ['note', 'pagenote', 'check_api', 'check', 'invalid_ctype'];
        private array $sum_to_pay = [3];

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
        }

        function get_map() {
            return [
                'API_KEY' => [
                    'title' => '[en_US:]API key[:en_US][ru_RU:]API ключ[:ru_RU]',
                    'view' => 'input',
                    'hidden' => true,
                ],
                'SECRET_KEY' => [
                    'title' => '[en_US:]Secret key[:en_US][ru_RU:]Секретный ключ[:ru_RU]',
                    'view' => 'input',
                    'hidden' => true,
                ],
            ];
        }

        function settings_list() {
            return [['API_KEY', 'SECRET_KEY']];
        }

        function options($options, $data, $id, $place) {
            $m_define = $this->get_file_data($id);
            $m_data = get_merch_data($id);

            $options = pn_array_unset($options, $this->disable_opts);

            $options[] = ['view' => 'line'];

            $text_check_api = [0 => '<strong class="bred">' . mb_strtoupper(__('error', 'pn')) . '</strong>'];

            if (1 == $place && 'success' == $this->settingtext('success', $id)) {
                $api = new M_PANDAPAY($this->name, $id, $m_define, $m_data);

                $r = $api->order('none');

                if (400 == $r['status_code']) {
                    $text_check_api[0] = '<strong class="bgreen">' . mb_strtoupper(__('ok', 'pn')) . '</strong>';
                }
            }

            $options['countries'] = [
                'view' => 'user_func',
                'name' => 'countries',
                'func' => get_class($this) . '::_countries',
                'func_data' => $data,
                'work' => 'input_array',
            ];

            $options['add_countries'] = [
                'view' => 'inputbig',
                'title' => __('Add new', 'pn'),
                'default' => is_isset($data, 'add_countries'),
                'name' => 'add_countries',
                'work' => 'input',
            ];

            $options['add_countries_help'] = [
                'view' => 'help',
                'title' => __('Help', 'pn'),
                'default' => 'RUS,AZE,KGZ',
            ];

            $options['payment_method'] = [
                'view' => 'select',
                'title' => __('Payment method', 'pn') . ' <span class="bred">*</span>',
                'options' => [0 => '-- ' . __('Select method', 'pn') . ' --'] + M_PANDAPAY::payment_methods(is_isset($data, 'add_payment_methods')),
                'default' => is_isset($data, 'payment_method'),
                'name' => 'payment_method',
                'work' => 'input',
            ];

            $options['add_payment_methods'] = [
                'view' => 'inputbig',
                'title' => __('Add new', 'pn'),
                'default' => is_isset($data, 'add_payment_methods'),
                'name' => 'add_payment_methods',
                'work' => 'input',
            ];

            $options['add_payment_methods_help'] = [
                'view' => 'help',
                'title' => __('Help', 'pn'),
                'default' => 'SBP,card,accountNumber',
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

        static function _countries($data) {
            $countries = is_isset($data, 'countries');
            $countries = is_array($countries) ? pn_strip_input_array($countries) : [];
            $add_countries = is_isset($data, 'add_countries');

            ?>
            <div class="premium_standart_line">
                <div class="premium_stline_left">
                    <div class="premium_stline_left_ins"><?= __('Allowed countries', 'pn') ?>
                        <span class="bred">*</span></div>
                </div>
                <div class="premium_stline_right">
                    <div class="premium_stline_right_ins">
                        <div class="premium_wrap_standart">
                            <?php
                            $scroll_lists = [];
                            $lists = list_checks_top(M_PANDAPAY::countries($add_countries), $countries);
                            foreach ($lists as $key => $title) {
                                $scroll_lists[] = [
                                    'title' => $title,
                                    'checked' => in_array($key, $countries),
                                    'value' => $key,
                                ];
                            }
                            echo get_check_list($scroll_lists, 'countries[]', [], 200, 1);
                            ?>
                            <div class="premium_clear"></div>
                        </div>
                    </div>
                </div>
                <div class="premium_clear"></div>
            </div>
            <?php
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
            $recipient = implode(' ', array_filter(array_map('pn_strip_input', array_map('trim', [$bids_data->last_name, $bids_data->first_name, $bids_data->second_name]))));

            // M DATA
            $countries = is_isset($m_data, 'countries');
            $countries = is_array($countries) ? pn_strip_input_array($countries) : [];
            $pm = pn_strip_input(is_isset($m_data, 'payment_method'));

            $api = new M_PANDAPAY($this->name, $m_id, $m_defin, $m_data);

            $data = [
                'amount_rub' => $pay_sum,
                'countries' => $countries,
                'idempotency_key' => wp_generate_uuid4(),
                'merchant_order_id' => 'm_' . $bids_data->id,
                'receipt' => $recipient,
                'requisite_type' => $pm,
            ];
            $r = $api->create_order($data)['json'];

            if (empty($r['uuid'])) {
                return false;
            }

            $tx = $r;
            $tx_id = !empty($tx['uuid']) ? pn_strip_input($tx['uuid']) : null;
            $tx_method = !empty($tx['requisite_data']['type']) ? mb_strtoupper($tx['requisite_data']['type']) : null;
            $tx_purse = !empty($tx['requisite_data']['requisites']) ? pn_strip_input($tx['requisite_data']['requisites']) : null;
            $tx_cardholder = !empty($tx['requisite_data']['owner_full_name']) ? pn_strip_input($tx['requisite_data']['owner_full_name']) : null;
            $tx_bank_name = !empty($tx['requisite_data']['bank_name_ru']) ? pn_strip_input($tx['requisite_data']['bank_name_ru']) : null;
            $tx_country_name = !empty($tx['requisite_data']['country_name_ru']) ? pn_strip_input($tx['requisite_data']['country_name_ru']) : null;

            $checked_fields = [$tx_id, $tx_purse];
            if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                return false;
            }

            $to_account = preg_replace('/\D/', '', $tx_purse);

            if ('SBP' == mb_strtoupper($tx_method)) {
                $to_account = '+' . $to_account;
            }

            $dt_data = array_values(array_filter([
                'cardholder' => !empty($tx_cardholder) ? $tx_cardholder : null,
                'bank_name' => !empty($tx_bank_name) ? $tx_bank_name : null,
                'country_name' => !empty($tx_country_name) ? $tx_country_name : null,
            ]));
            $dest_tag = $dt_data ? (1 == count($dt_data) ? $dt_data[0] : "{$dt_data[0]} (" . implode(', ', array_slice($dt_data, 1)) . ")") : '';

            if ($to_account) {
                $update_data = [
                    'trans_in' => pn_strip_input($tx_id),
                    'to_account' => pn_strip_input($to_account),
                    'dest_tag' => pn_strip_input($dest_tag),
                ];
                $bids_data = update_bid_tb_array($bids_data->id, $update_data, $bids_data);
            }

            return $bids_data->to_account;
        }

        function myaction($m_id, $pay_sum, $direction) {
            global $bids_data;

            if (get_mscript($m_id) !== $this->name || empty($bids_data->id) || empty($bids_data->trans_in)) {
                return $m_id;
            }

            $bid_tx_id = $bids_data->trans_in;

            $m_define = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            $api = new M_PANDAPAY($this->name, $m_id, $m_define, $m_data);

            $r = $api->order($bid_tx_id)['json'];

            if (empty($r['uuid'])) {
                return $m_id;
            }

            $tx = $r;
            $tx_id = !empty($tx['uuid']) ? pn_strip_input($tx['uuid']) : null;
            $tx_status = !empty($tx['status']) ? mb_strtoupper($tx['status']) : null;

            $checked_fields = [$tx_id, $tx_status];
            if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                return $m_id;
            }

            $api->confirm($bid_tx_id);

            $params = [
                'bid_status' => get_status_sett('merch', 1),
                'm_place' => $m_id . '_' . __FUNCTION__,
                'm_id' => $m_id,
                'm_data' => $m_data,
                'm_defin' => $m_define,
            ];
            set_bid_status('payed', $bids_data->id, $params, $direction);

            if (in_array($tx_status, ['COMPLETED'])) {
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

            $tx_id = !empty($data['uuid']) ? pn_strip_input($data['uuid']) : null;
            $tx_status = !empty($data['status']) ? mb_strtoupper($data['status']) : null;

            $checked_fields = [$tx_id, $tx_status];
            if (count(array_filter($checked_fields)) !== count($checked_fields) || !in_array($tx_status, ['COMPLETED'])) {
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

                $api = new M_PANDAPAY($this->name, $m_id, $m_define, $m_data);
                $history = $tx_id ? null : $api->orders()['json'];

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

                    $tx = is_array($tx_info) ? $tx_info : ($history['orders'][$bid_tx_id] ?? null);

                    if (empty($tx['uuid'])) {
                        $r = $api->order($bid_tx_id)['json'];
                        $tx = $r ?? null;
                    }

                    $tx_id = !empty($tx['uuid']) ? pn_strip_input($tx['uuid']) : null;
                    $tx_status = !empty($tx['status']) ? mb_strtoupper($tx['status']) : null;
                    $tx_amount = !empty($tx['amount_rub']) ? is_sum($tx['amount_rub']) : null;
                    $tx_currency = !empty($tx['currency']) ? mb_strtoupper($tx['currency']) : null;
                    $tx_hash = '';
                    $tx_purse_from = '';
                    $tx_to_account = '';

                    $checked_fields = [$tx_id, $tx_status, $tx_amount, $tx_currency];
                    if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                        continue;
                    }

                    $realpays = ['COMPLETED'];
                    $coldpays = [];
                    $new_status = in_array($tx_status, $realpays) ? 'realpay' : (in_array($tx_status, $coldpays) ? 'coldpay' : false);

                    if (!$new_status) {
                        continue;
                    }

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
                        $this->logs(sprintf('%s | Error check trans in!', $bid_id), $m_id);
                        continue;
                    }

                    if ($err || !$bid_m_id || $bid_m_id != $m_id || !$bid_m_script || $bid_m_script != $this->name) {
                        $this->logs(sprintf('%s | Bid error', $bid_id), $m_id);
                        continue;
                    }

                    if (0 === $invalid_check && $bid_account != $tx_purse_from) {
                        $this->logs(sprintf('%s | Another account wallet is expected: %s != %s', $bid_id, $bid_account, $tx_purse_from), $m_id);
                        continue;
                    }

                    if (0 === $invalid_ctype && $bid_currency != $tx_currency) {
                        $this->logs(sprintf('%s | Wrong type of currency: %s != %s', $bid_id, $bid_currency, $tx_currency), $m_id);
                        continue;
                    }

                    if (0 === $invalid_minsum && $tx_amount < $bid_corr_sum) {
                        $this->logs(sprintf('%s | The payment amount is less than the provisions: %s < %s', $bid_id, $tx_amount, $bid_corr_sum), $m_id);
                        continue;
                    }

                    if (0 === $invalid_maxsum && $tx_amount > $bid_sum) {
                        $this->logs(sprintf('%s | The payment amount is greater than the provisions: %s > %s', $bid_id, $tx_amount, $bid_sum), $m_id);
                        continue;
                    }

                    $params = [
                        'sum' => $tx_amount,
                        'bid_sum' => $bid_sum,
                        'bid_corr_sum' => $bid_corr_sum,
                        'pay_purse' => $tx_purse_from,
                        'to_account' => $tx_to_account,
                        'trans_in' => $tx_id,
                        'txid_in' => $tx_hash,
                        'currency' => $tx_currency,
                        'bid_currency' => $bid_currency,
                        'invalid_ctype' => $invalid_ctype,
                        'invalid_minsum' => $invalid_minsum,
                        'invalid_maxsum' => $invalid_maxsum,
                        'invalid_check' => $invalid_check,
                        'bid_status' => $workstatus,
                        'm_place' => $m_id . '_' . $place,
                        'm_id' => $m_id,
                        'm_data' => $m_data,
                        'm_defin' => $m_define,
                    ];
                    set_bid_status($new_status, $bid_id, $params, $data['direction_data']);
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

    }
}

new merchant_pandapay(__FILE__, 'PandaPay');
