<?php

if (!defined('ABSPATH')) exit();

/*
title: [en_US:]NicePay[:en_US][ru_RU:]NicePay[:ru_RU]
description: [en_US:]NicePay automatic payouts[:en_US][ru_RU:]авто выплаты NicePay[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) return;

if (!class_exists('paymerchant_nicepay')) {
    class paymerchant_nicepay extends Ext_AutoPayut_Premiumbox {
        private array $disable_opts = ['note', 'checkpay'];
        private array $sum_to_pay = [2];
        private array $tx_statuses = [
            'success' => ['PAID'],
        ];

        function __construct($file, $title) {
            parent::__construct($file, $title, !in_array('ext_cron', $this->disable_opts));

            $this->tx_statuses = array_replace(...array_map(fn($statuses, $processor) => array_fill_keys($statuses, $processor), $this->tx_statuses, array_keys($this->tx_statuses)));

            if (!in_array('ext_webhook', $this->disable_opts)) {
                foreach ($this->get_ids('paymerchants', $this->name) as $id) {
                    add_action("premium_merchant_ap_{$id}_webhook" . hash_url($id, 'ap'), [$this, 'webhook']);
                }
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
            global $premiumbox;

            $m_define = $this->get_file_data($id);
            $m_data = get_paymerch_data($id);

            $options = pn_array_unset($options, $this->disable_opts);
            if (in_array('ext_cron', $this->disable_opts)) $options = pn_array_unset($options, ['cronhash']);
            if (in_array('ext_webhook', $this->disable_opts)) $options = pn_array_unset($options, ['enableip', 'resulturl', 'help_resulturl']);

            $options['ext_line'] = ['view' => 'line'];

            $payment_methods = [0 => __('Config file is not configured', 'pn')];
            $text_check_api = [0 => '<strong class="bred">' . mb_strtoupper(__('error', 'pn')) . '</strong>'];

            if (1 == $place && 'success' == $this->settingtext('success', $id)) {
                $api = new P_NICEPAY($this->name, $id, $m_define, $m_data);

                $r = $api->payoutMethods(is_isset($data, 'add_payment_method'));

                if ($r['pd']) {
                    $payment_methods = [0 => '-- ' . __('Select method', 'pn') . ' --'] + $r['pd'];
                }

                $r = $api->balance();

                $premiumbox->update_option("ap_{$id}", 'balance_cc', array_keys($r['pd']));

                if (200 == $r['status_code'] && !empty($r['pd'])) {
                    $text_check_api[0] = '<strong class="bgreen">' . mb_strtoupper(__('ok', 'pn')) . '</strong>';
                }
            }

            $options['payment_method'] = [
                'view' => 'select_search',
                'title' => __('Payment method', 'pn') . ' <span class="bred">*</span>',
                'options' => $payment_methods,
                'default' => is_isset($data, 'payment_method'),
                'name' => 'payment_method',
                'work' => 'input',
            ];

            $example = ['BTC=Bitcoin', 'USDT', __('Code', 'pn'), __('Code', 'pn') . '=' . __('Title', 'pn')];
            $options = $this->_add_field($options, $data, 'currency', $example);

            /*$options['commission_type'] = [
                'view' => 'select',
                'title' => __('Pays commission', 'pn'),
                'options' => [
                    0 => __('merchant', 'pn'),
                    1 => __('user', 'pn')
                ],
                'default' => is_isset($data, 'commission_type'),
                'name' => 'commission_type',
                'work' => 'int',
            ];*/

            $options['work_status'] = ['view' => 'textfield', 'title' => __('Work status', 'pn'), 'default' => implode('<br/>', $text_check_api)];

            $text_add_info = array_filter([
                'webhook_url' => !in_array('ext_webhook', $this->disable_opts) ? '<strong>Webhook:</strong> <a href="' . get_mlink("ap_{$id}_webhook" . hash_url($id, 'ap')) . '" target="_blank">' . get_mlink("ap_{$id}_webhook" . hash_url($id, 'ap')) . '</a>' : null,
                'cron_url' => !in_array('ext_cron', $this->disable_opts) ? '<strong>' . __('Cron file', 'pn') . ':</strong> <a href="' . get_mlink("ap_{$id}_cron" . chash_url($id, 'ap')) . '" target="_blank">' . get_mlink("ap_{$id}_cron" . chash_url($id, 'ap')) . '</a>' : null,
            ]);

            if ($text_add_info) {
                $options['add_info'] = ['view' => 'textfield', 'title' => '', 'default' => implode('<br/>', $text_add_info)];
            }

            return $options;
        }

        function get_reserve_lists($m_id, $m_defin) {
            global $premiumbox;

            $balance_cc = $premiumbox->get_option("ap_{$m_id}", 'balance_cc') ?: [];
            if (!is_array($balance_cc)) $balance_cc = [];

            return $balance_cc ? array_combine(array_map(fn($c) => "{$m_id}_{$c}", $balance_cc), array_map('mb_strtoupper', $balance_cc)) : [];
        }

        function update_reserve($code, $m_id, $m_defin) {

            $purses = $this->get_reserve_lists($m_id, $m_defin);
            $purse = mb_strtolower(trim(is_isset($purses, $code)));
            if (!$purse) {
                return 0;
            }

            try {
                $m_data = get_paymerch_data($m_id);

                $api = new P_NICEPAY($this->name, $m_id, $m_defin, $m_data);

                return $this->sum_to_pay(is_isset($api->balance()['pd'], $purse) / 100, $m_id);
            } catch (Exception $e) {
                $this->logs(pn_json_encode(['line' => $e->getLine(), 'file' => realpath($e->getFile()), 'message' => $e->getMessage()]), $m_id);
            }

            return 0;
        }

        function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {
            global $wpdb;

            // BID DATA
            $tx = null;
            $item_id = $item->id;
            $account = pn_strip_input(is_isset($unmetas, 'get_phone') ?: is_isset($unmetas, 'phone') ?: $item->account_get);
            $cardholder = pn_strip_input(is_isset($unmetas, 'get_cardholder') ?: is_isset($unmetas, 'cardholder') ?: implode(' ', array_filter([$item->last_name, $item->first_name])));
            $bank_name = pn_strip_input(is_isset($unmetas, 'get_bankname') ?: is_isset($unmetas, 'bankname') ?: sprintf('%s %s', pn_strip_input($item->psys_get), is_site_value($item->currency_code_get)));
            $dt_data = array_filter([
                'cardholder' => !empty($cardholder) ? $cardholder : null,
                'bank_name' => !empty($bank_name) ? $bank_name : null,
            ]);
            $comment = pn_strip_input($dt_data ? array_shift($dt_data) . ($dt_data ? ' (' . implode(', ', $dt_data) . ')' : '') : '');
            $comment = pn_maxf_mb($comment, 30);

            // M DATA
            $pay_sum = $this->sum_to_pay(is_paymerch_sum($item, $paymerch_data), $m_id);
            $pm = pn_strip_input(is_isset($paymerch_data, 'payment_method'));
            //$commission_type = absint(is_isset($paymerch_data, 'commission_type'));
            //if (!in_array($commission_type, [0, 1])) $commission_type = 0;

            if (!$error && !$account) {
                $error[] = __('Wrong client wallet', 'pn');
            }

            if (!$error && !$this->set_ap_status($item, $test)) {
                $error[] = 'Database error';
            }

            if (!$error) {
                try {
                    $api = new P_NICEPAY($this->name, $m_id, $m_defin, $paymerch_data);

                    $data = [
                        'order_id' => "ap_{$item_id}",
                        'balance' => 'USDT',
                        'method' => $pm,
                        'wallet' => $account,
                        'amount' => 0,
                        'amountTo' => $pay_sum * 100,
                        'comment' => $comment,
                        'fee_merchant' => true,
                        'webhook_url' => apply_filters('custom_url', get_mlink("ap_{$m_id}_webhook" . hash_url($m_id, 'ap')), 'webhook', $this->name, $m_id),
                    ];

                    $r = $api->payout($data)['json'];

                    if (!empty($r['data']['payout_id'])) {
                        $tx = $api->payoutInfo($r['data']['payout_id'])['pd'];
                        $tx['tx_id'] = $r['data']['payout_id'];
                    } else {
                        $error[] = __('Payout error', 'pn');
                        $error[] = pn_json_encode($r);
                        $pay_error = 1;
                    }
                } catch (Exception $e) {
                    $error[] = pn_json_encode(['line' => $e->getLine(), 'file' => realpath($e->getFile()), 'message' => $e->getMessage()]);
                    $pay_error = 1;
                }
            }

            if ($error) {
                $this->reset_ap_status($error, $pay_error, $item, $place, $m_id, $test);
            } else {
                $tx_id = !empty($tx['tx_id']) ? pn_strip_input($tx['tx_id']) : null;
                $tx_status = !empty($tx['result']) ? mb_strtoupper($tx['result']) : null;
                $tx_amount = !empty($tx['final_amount']) ? $this->sum_to_pay($tx['final_amount'] / 100, $m_id) : null;
                $tx_hash = '';

                $new_status = $this->tx_statuses[$tx_status] ?? 'coldsuccess';

                $params = [
                    'out_sum' => $tx_amount,
                    'trans_out' => $tx_id,
                    'txid_out' => $tx_hash,
                    'm_place' => "{$m_id}_{$modul_place}",
                    'system' => 'admin' == $place ? 'user' : 'system',
                    'm_id' => $m_id,
                    'm_defin' => $m_defin,
                    'm_data' => $paymerch_data,
                ];
                set_bid_status($new_status, $item_id, $params, $direction);

                if ('admin' == $place) {
                    if ('success' == $new_status) {
                        pn_display_mess(__('Automatic payout is done', 'pn'), __('Automatic payout is done', 'pn'), 'true');
                    } else {
                        pn_display_mess(__('Payment is successfully created. Waiting for confirmation.', 'pn'), __('Payment is successfully created. Waiting for confirmation.', 'pn'), 'true');
                    }
                }
            }
        }

        function webhook() {
            $m_id = key_for_url('_webhook', 'ap_');
            $m_define = $this->get_file_data($m_id);
            $m_data = get_paymerch_data($m_id);

            $data = $_GET ?? [];

            do_action('paymerchant_secure', $this->name, $data, $m_id, $m_define, $m_data);

            $tx_id = !empty($data['payout_id']) ? pn_strip_input($data['payout_id']) : null;
            $tx_status = !empty($data['result']) ? mb_strtoupper($data['result']) : null;

            $checked_fields = [$tx_id, $tx_status];
            if (count(array_filter($checked_fields)) !== count($checked_fields) || 'SUCCESS_PAYOUT' != $tx_status) {
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

                $workstatus = ['coldsuccess'];

                $where = [
                    $wpdb->prepare("`m_out` = %s", $m_id),
                    "`status` IN ('" . implode("','", $workstatus) . "')",
                    $tx_id ? $wpdb->prepare("`trans_out` = %s", $tx_id) : "`trans_out` <> '0'",
                ];

                $bids = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}exchange_bids WHERE " . implode(' AND ', $where));

                $api = new P_NICEPAY($this->name, $m_id, $m_define, $m_data);
                $history = (empty($bids) || $tx_id) ? null : $api->get_payments()['pd'];

                foreach ($bids as $bid) {
                    $bid_id = $bid->id;
                    $bid_tx_id = $bid->trans_out;

                    $tx = is_array($tx_info) ? $tx_info : ($history[$bid_tx_id] ?? null);

                    if (empty($tx['none'])) {
                        $tx = $api->payoutInfo($bid_tx_id)['pd'];
                    }

                    $tx_id = !empty($tx['payout_id']) ? pn_strip_input($tx['payout_id']) : null;
                    $tx_status = !empty($tx['result']) ? mb_strtoupper($tx['result']) : null;
                    $tx_amount = !empty($tx['final_amount']) ? $this->sum_to_pay($tx['final_amount'] / 100, $m_id) : null;
                    $tx_hash = '';

                    $checked_fields = [$tx_id, $tx_status, $tx_amount];
                    if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                        continue;
                    }

                    $new_status = $this->tx_statuses[$tx_status] ?? '';

                    if ('success' == $new_status) {

                        $params = [
                            'out_sum' => $tx_amount,
                            'trans_out' => $tx_id,
                            'txid_out' => $tx_hash,
                            'bid_status' => $workstatus,
                            'm_place' => "{$m_id}_{$place}",
                            'system' => 'system',
                            'm_id' => $m_id,
                            'm_defin' => $m_define,
                            'm_data' => $m_data,
                        ];
                        set_bid_status($new_status, $bid_id, $params);

                    } elseif ($new_status) {

                        $this->reset_cron_status($bid, is_status_name(is_isset($m_data, 'error_status')), $m_id);

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

            if (!$m_id || get_pscript($m_id) !== $this->name) {
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

new paymerchant_nicepay(__FILE__, 'NicePay');
