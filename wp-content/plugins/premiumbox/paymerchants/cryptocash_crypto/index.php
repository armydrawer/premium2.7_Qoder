<?php

if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Crypto-Cash Crypto[:en_US][ru_RU:]Crypto-Cash Crypto[:ru_RU]
description: [en_US:]Crypto-Cash Crypto automatic payouts[:en_US][ru_RU:]авто выплаты Crypto-Cash Crypto[:ru_RU]
version: 2.7.2
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) return;

if (!class_exists('paymerchant_cryptocash_crypto')) {
    class paymerchant_cryptocash_crypto extends Ext_AutoPayut_Premiumbox {
        private array $disable_opts = ['note', 'checkpay'];
        private array $tx_statuses = [
            'success' => ['PAID'],
            'payouterror' => ['CANCELED'],
        ];

        function __construct($file, $title) {
            parent::__construct($file, $title, !in_array('ext_cron', $this->disable_opts));

            $this->tx_statuses = $this->tx_statuses ? array_replace(...array_map(fn($statuses, $processor) => array_fill_keys($statuses, $processor), $this->tx_statuses, array_keys($this->tx_statuses))) : [];

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
            global $premiumbox;

            $m_define = $this->get_file_data($id);
            $m_data = get_paymerch_data($id);

            $options = pn_array_unset($options, $this->disable_opts);
            if (in_array('ext_cron', $this->disable_opts)) $options = pn_array_unset($options, ['cronhash']);
            if (in_array('ext_webhook', $this->disable_opts)) $options = pn_array_unset($options, ['enableip', 'resulturl', 'help_resulturl']);

            $options['ext_line'] = ['view' => 'line'];

            $currency = [0 => __('Config file is not configured', 'pn')];
            $text_check_api = [0 => '<strong class="bred">' . mb_strtoupper(__('error', 'pn')) . '</strong>'];

            if (1 == $place && 'success' == $this->settingtext('success', $id)) {
                $api = new P_CRYPTOCASH_C($this->name, $id, $m_define, $m_data);

                $r = $api->currencies(is_isset($data, 'add_currency'));

                if ($r['pd_tickers']) {
                    $currency = [0 => '-- ' . __('Automatically', 'pn') . ' --'] + $r['pd_tickers'];
                }

                $r = $api->balance();

                $premiumbox->update_option("ap_{$id}", 'balance_cc', array_keys($r['pd']));

                if (200 == $r['status_code']) {
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

                $api = new P_CRYPTOCASH_C($this->name, $m_id, $m_defin, $m_data);

                return is_sum(is_isset($api->balance()['pd'], $purse));
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
            $account = $item->account_get;
            $dest_tag = trim(is_isset($unmetas, 'dest_tag'));

            // M DATA
            $pay_sum = is_sum(is_paymerch_sum($item, $paymerch_data));
            $_currency = pn_strip_input(is_isset($paymerch_data, 'currency'));
            if (!$_currency) {
                $currency_id_get = $item->currency_id_get;
                $cd = get_currency_data([$currency_id_get]);
                $_currency = is_xml_value(isset($cd[$currency_id_get]) ? $cd[$currency_id_get]->xml_value : $_currency);
            }

            if (!$error && !$account) {
                $error[] = __('Wrong client wallet', 'pn');
            }

            if (!$error && !$this->set_ap_status($item, $test)) {
                $error[] = 'Database error';
            }

            if (!$error) {
                try {
                    $api = new P_CRYPTOCASH_C($this->name, $m_id, $m_defin, $paymerch_data);

                    $data = [
                        'address' => $account,
                        'amount' => $pay_sum,
                        'ticker' => $_currency,
                        'externalId' => "ap_{$item_id}",
                    ];

                    if ($dest_tag) {
                        $data['memo'] = $dest_tag;
                    }

                    $r = $api->buy($data)['json'];

                    if (!empty($r['data']['item']['id'])) {
                        $tx = $api->get_payment($r['data']['item']['id'])['pd'];
                        $tx['tx_id'] = $r['data']['item']['id'];
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
                $tx_status = !empty($tx['status']) ? mb_strtoupper($tx['status']) : null;
                $tx_amount = !empty($tx['amount']) ? is_sum($tx['amount']) : null;
                $tx_hash = !empty($tx['hash']) ? pn_strip_input($tx['hash']) : null;

                $new_status = ($tx_status && 'success' == is_isset($this->tx_statuses, $tx_status) ? 'success' : 'coldsuccess');

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
                    $text = ('success' == $new_status ? __('Automatic payout is done', 'pn') : __('Payment is successfully created. Waiting for confirmation.', 'pn'));
                    pn_display_mess($text, '', 'success');
                }
            }
        }

        function webhook() {
            $m_id = key_for_url('_webhook', 'ap_');
            $m_define = $this->get_file_data($m_id);
            $m_data = get_paymerch_data($m_id);

            $data = pn_json_decode(file_get_contents('php://input')) ?? [];

            do_action('paymerchant_secure', $this->name, $data, $m_id, $m_define, $m_data);

            $decoded_data = !empty($data['data']) ? base64_decode($data['data']) : null;

            if (empty($decoded_data)) {
                wp_send_json_success();
            }

            $tx_id = !empty($decoded_data['id']) ? pn_strip_input($decoded_data['id']) : null;
            $tx_status = !empty($decoded_data['status']) ? mb_strtoupper($decoded_data['status']) : null;
            $tx_dir = !empty($decoded_data['transactionType']) ? mb_strtoupper($decoded_data['transactionType']) : null;

            $checked_fields = [$tx_id, $tx_status, $tx_dir];
            if (count(array_filter($checked_fields)) !== count($checked_fields) || 'BUY' != $tx_dir || empty($this->tx_statuses[$tx_status])) {
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

                $api = new P_CRYPTOCASH_C($this->name, $m_id, $m_define, $m_data);
                $history = (empty($bids) || $tx_id) ? null : $api->get_payments()['pd'];

                foreach ($bids as $bid) {
                    $bid_id = $bid->id;
                    $bid_tx_id = $bid->trans_out;

                    $tx = is_array($tx_info) ? $tx_info : ($history[$bid_tx_id] ?? null);

                    if (empty($tx['id'])) {
                        $tx = $api->get_payment($bid_tx_id)['pd'];
                    }

                    $tx_id = !empty($tx['id']) ? pn_strip_input($tx['id']) : null;
                    $tx_status = !empty($tx['status']) ? mb_strtoupper($tx['status']) : null;
                    $tx_amount = !empty($tx['amount']) ? is_sum($tx['amount']) : null;
                    $tx_hash = !empty($tx['hash']) ? pn_strip_input($tx['hash']) : null;

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

new paymerchant_cryptocash_crypto(__FILE__, 'Crypto-Cash Crypto');
