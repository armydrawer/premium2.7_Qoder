<?php

if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Payscrow[:en_US][ru_RU:]Payscrow[:ru_RU]
description: [en_US:]Payscrow automatic payouts[:en_US][ru_RU:]авто выплаты Payscrow[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) return;

if (!class_exists('paymerchant_payscrow')) {
    class paymerchant_payscrow extends Ext_AutoPayut_Premiumbox {
        private array $disable_opts = ['note', 'checkpay'];

        function __construct($file, $title) {
            parent::__construct($file, $title, !in_array('ext_cron', $this->disable_opts));

            if (!in_array('ext_webhook', $this->disable_opts)) {
                foreach ($this->get_ids('paymerchants', $this->name) as $id) {
                    add_action('premium_merchant_ap_' . $id . '_webhook' . hash_url($id, 'ap'), [$this, 'webhook']);
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
                'API_KEY' => [
                    'title' => '[en_US:]API key[:en_US][ru_RU:]API ключ[:ru_RU]',
                    'view' => 'input',
                    'hidden' => true,
                ],
                'SECRET_KEY' => [
                    'title' => '[en_US:]Signature key[:en_US][ru_RU:]Ключ подписи[:ru_RU]',
                    'view' => 'input',
                    'hidden' => true,
                ],
            ];
        }

        function settings_list() {
            return [['BASE_URL', 'API_KEY', 'SECRET_KEY']];
        }

        function options($options, $data, $id, $place) {
            global $premiumbox;

            $m_define = $this->get_file_data($id);
            $m_data = get_paymerch_data($id);

            $options = pn_array_unset($options, $this->disable_opts);

            $options[] = ['view' => 'line'];

            $payment_methods = [0 => __('Config file is not configured', 'pn')];
            $text_check_api = [0 => '<strong class="bred">' . mb_strtoupper(__('error', 'pn')) . '</strong>'];

            if (1 == $place && !$this->settingtext('', $id)) {
                $api = new PAY_PAYSCROW($this->name, $id, $m_define, $m_data);

                $r_balances = $api->balances();

                $balance_cc = [];
                if (isset($r_balances['json']['balances'])) {
                    $balance_cc = array_map('mb_strtoupper', array_column($r_balances['json']['balances'], 'coinName'));
                    sort($balance_cc);
                }
                $premiumbox->update_option('ap_' . $id, 'balance_cc', $balance_cc);

                $r_payment_methods = $api->payment_methods()['json'];

                $payment_methods = [];
                if (isset($r_payment_methods['paymentMethods'])) {
                    foreach ($r_payment_methods['paymentMethods'] as $method) {

                        $method_id = pn_strip_input($method['methodId']);
                        $type = pn_strip_input($method['type']);
                        $fiat_name = pn_strip_input($method['fiatName']);
                        $name = pn_strip_input($method['name']);
                        $available = intval($method['available']);

                        $payment_methods[$method_id] = "[{$type}, {$fiat_name}] {$name}" . (!$available ? ' (' . __('inactive', 'premium') . ')' : '');
                    }

                    asort($payment_methods);
                    $payment_methods = [0 => '-- ' . __('Select method', 'pn') . ' --'] + $payment_methods;
                }

                if (200 == $r_balances['status_code']) {
                    $text_check_api[0] = '<strong class="bgreen">' . mb_strtoupper(__('ok', 'pn')) . '</strong>';
                }
            }

            $options['payment_method'] = [
                'view' => 'select',
                'title' => __('Payment method', 'pn') . ' <span class="bred">*</span>',
                'options' => $payment_methods,
                'default' => is_isset($data, 'payment_method'),
                'name' => 'payment_method',
                'work' => 'input',
            ];

            $options['commission_type'] = [
                'view' => 'select',
                'title' => __('Pays commission', 'pn'),
                'options' => [
                    'ChargeMerchant' => __('merchant', 'pn'),
                    'ChargeCustomer' => __('user', 'pn')
                ],
                'default' => is_isset($data, 'commission_type'),
                'name' => 'commission_type',
                'work' => 'input',
            ];

            $options[] = ['view' => 'textfield', 'title' => __('Work status', 'pn'), 'default' => implode('<br/>', $text_check_api)];

            $text_add_info = array_values(array_filter([
                'webhook_url' => !in_array('ext_webhook', $this->disable_opts) ? '<strong>Webhook:</strong> <a href="' . get_mlink('ap_' . $id . '_webhook' . hash_url($id, 'ap')) . '" target="_blank">' . get_mlink('ap_' . $id . '_webhook' . hash_url($id, 'ap')) . '</a>' : null,
                'cron_url' => !in_array('ext_cron', $this->disable_opts) ? '<strong>' . __('Cron file', 'pn') . ':</strong> <a href="' . get_mlink('ap_' . $id . '_cron' . chash_url($id, 'ap')) . '" target="_blank">' . get_mlink('ap_' . $id . '_cron' . chash_url($id, 'ap')) . '</a>' : null,
            ]));

            if ($text_add_info) {
                $options[] = ['view' => 'textfield', 'title' => '', 'default' => implode('<br/>', $text_add_info)];
            }

            return $options;
        }

        function get_reserve_lists($m_id, $m_defin) {
            global $premiumbox;

            $balance_cc = $premiumbox->get_option('ap_' . $m_id, 'balance_cc') ?: [];
            if (!is_array($balance_cc)) $balance_cc = [];

            $purses = [];

            foreach ($balance_cc as $currency) {
                $purses[$m_id . '_' . mb_strtolower($currency)] = mb_strtoupper($currency);
            }

            return $purses;
        }

        function update_reserve($code, $m_id, $m_defin) {

            $purses = $this->get_reserve_lists($m_id, $m_defin);
            $purse = trim(is_isset($purses, $code));
            if (!$purse) {
                return 0;
            }

            try {
                $m_data = get_paymerch_data($m_id);

                $api = new PAY_PAYSCROW($this->name, $m_id, $m_defin, $m_data);

                $r = $api->balances()['json'];

                if (isset($r['balances'])) {
                    foreach ($r['balances'] as $balance) {
                        if ($balance['coinName'] != $purse) continue;

                        return is_sum($balance['availableBalance']);
                    }
                }
            } catch (Exception $e) {
                $this->logs(pn_json_encode(['line' => $e->getLine(), 'file' => realpath($e->getFile()), 'message' => $e->getMessage()]), $m_id);
            }

            return 0;
        }

        function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {

            // BID DATA
            $tx_id = 0;
            $item_id = $item->id;
            $currency_code_get = mb_strtoupper($item->currency_code_get);
            $cardholder = pn_strip_input(trim(is_isset($unmetas, 'get_cardholder') ?: is_isset($unmetas, 'cardholder') ?: implode(' ', array_filter([$item->last_name, $item->first_name, $item->second_name]))));
            $account = preg_replace('/\D/', '', pn_strip_input(trim(is_isset($unmetas, 'get_phone') ?: is_isset($unmetas, 'phone') ?: $item->account_get)));

            // M DATA
            $out_sum = $sum = intval(is_sum(is_paymerch_sum($item, $paymerch_data), 0, 'down'));
            $payment_method = pn_strip_input(is_isset($paymerch_data, 'payment_method'));
            $commission_type = pn_strip_input(is_isset($paymerch_data, 'commission_type'));
            if (!in_array($commission_type, ['ChargeMerchant', 'ChargeCustomer'])) $commission_type = 'ChargeMerchant';

            if (!$account) {
                $error[] = __('Wrong client wallet', 'pn');
            }

            if (!$error && !$this->set_ap_status($item, $test)) {
                $error[] = 'Database error';
            }

            if (!$error) {
                try {
                    $api = new PAY_PAYSCROW($this->name, $m_id, $m_defin, $paymerch_data);

                    $data = [
                        'externalOrderId' => 'ap_' . $item_id,
                        'orderSide' => 'Sell',
                        'basePaymentMethodId' => $payment_method,
                        'targetAmount' => $sum,
                        'feeType' => $commission_type,
                        'currencyType' => 'Fiat',
                        'currency' => $currency_code_get,
                        'customerPaymentAccount' => $account,
                    ];

                    if ($cardholder) {
                        $data['customerName'] = $cardholder;
                    }

                    $r = $api->orders_create($data)['json'];

                    if (isset($r['orderId'])) {
                        $tx_id = $r['orderId'];
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
                $params = [
                    'out_sum' => $out_sum,
                    'trans_out' => $tx_id,
                    'm_place' => $m_id . '_' . $modul_place,
                    'system' => 'user',
                    'm_id' => $m_id,
                    'm_defin' => $m_defin,
                    'm_data' => $paymerch_data,
                ];
                set_bid_status('coldsuccess', $item_id, $params, $direction);

                if ('admin' == $place) {
                    pn_display_mess(__('Payment is successfully created. Waiting for confirmation.', 'pn'), __('Payment is successfully created. Waiting for confirmation.', 'pn'), 'true');
                }
            }
        }

        function webhook() {
            $m_id = key_for_url('_webhook', 'ap_');
            $m_define = $this->get_file_data($m_id);
            $m_data = get_paymerch_data($m_id);

            $data = pn_json_decode(file_get_contents('php://input')) ?? [];

            do_action('paymerchant_secure', $this->name, $data, $m_id, $m_define, $m_data);

            $type = !empty($data['type']) ? mb_strtoupper($data['type']) : null;

            if ('SELLORDERUPDATE' == $type) {

                $tx_id = !empty($data['payload']['orderId']) ? pn_strip_input($data['payload']['orderId']) : null;
                $tx_status = !empty($data['payload']['orderStatus']) ? mb_strtoupper($data['payload']['orderStatus']) : null;

                $successes = ['COMPLETED'];
                $unsuccesses = ['FAILEDTOSERVICE', 'CANCELEDBYCUSTOMER', 'CANCELEDBYMERCHANT', 'CANCELEDBYTRADER', 'CANCELEDBYTIMEOUT', 'CANCELEDBYADMIN'];

                if (!isset($tx_id, $tx_status) || !in_array($tx_status, array_merge($successes, $unsuccesses))) {
                    wp_send_json_success();
                }

                $this->payment_check(__FUNCTION__, $m_id, $m_define, $m_data, $tx_id);
            }

            wp_send_json_success();
        }

        function cron($m_id, $m_defin, $m_data) {

            $this->payment_check(__FUNCTION__, $m_id, $m_defin, $m_data);

        }

        private function payment_check($place, $m_id, $m_define, $m_data, $tx_info = false) {
            global $wpdb;

            try {
                $tx_id = is_array($tx_info) ? is_isset($tx_info, 'tx_id') : $tx_info;

                $api = new PAY_PAYSCROW($this->name, $m_id, $m_define, $m_data);
                $history = $tx_id ? null : $api->orders_list()['json'];

                $workstatus = ['coldsuccess'];

                $where = [
                    $wpdb->prepare("`m_out` = %s", $m_id),
                    "`status` IN ('" . implode("','", $workstatus) . "')",
                    "`trans_out` <> '0'",
                ];

                if ($tx_id) {
                    $where[] = $wpdb->prepare("`trans_out` = %s", $tx_id);
                }

                $bids = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}exchange_bids WHERE " . implode(' AND ', $where));

                foreach ($bids as $bid) {
                    $bid_id = $bid->id;
                    $bid_tx_id = $bid->trans_out;

                    $tx = is_array($tx_info) ? $tx_info : ($history['orders'][$bid_tx_id] ?? null);

                    if (empty($tx['orderId'])) {
                        $r = $api->orders_getbyorderid($bid_tx_id)['json'];
                        $tx = $r['order'] ?? null;
                    }

                    $tx_id = !empty($tx['orderId']) ? pn_strip_input($tx['orderId']) : null;
                    $tx_status = !empty($tx['orderStatus']) ? mb_strtoupper($tx['orderStatus']) : null;

                    if (!isset($tx_id, $tx_status)) {
                        continue;
                    }

                    $successes = ['COMPLETED'];
                    $unsuccesses = ['FAILEDTOSERVICE', 'CANCELEDBYCUSTOMER', 'CANCELEDBYMERCHANT', 'CANCELEDBYTRADER', 'CANCELEDBYTIMEOUT', 'CANCELEDBYADMIN'];

                    if (in_array($tx_status, $successes)) {

                        $params = [
                            'trans_out' => $tx_id,
                            'm_place' => $m_id . '_' . $place,
                            'bid_status' => $workstatus,
                            'system' => 'system',
                            'm_id' => $m_id,
                            'm_defin' => $m_define,
                            'm_data' => $m_data,
                        ];
                        set_bid_status('success', $bid_id, $params);
                    } elseif (in_array($tx_status, $unsuccesses)) {

                        $error_status = is_status_name(is_isset($m_data, 'error_status'));
                        $this->reset_cron_status($bid, $error_status, $m_id);
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
    }
}

new paymerchant_payscrow(__FILE__, 'Payscrow');
