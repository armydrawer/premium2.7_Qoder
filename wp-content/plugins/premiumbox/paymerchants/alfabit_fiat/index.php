<?php

if (!defined('ABSPATH')) exit();

/*
title: [en_US:]AlfaBit Fiat[:en_US][ru_RU:]AlfaBit Fiat[:ru_RU]
description: [en_US:]AlfaBit Fiat automatic payouts[:en_US][ru_RU:]авто выплаты AlfaBit Fiat[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) return;

if (!class_exists('paymerchant_alfabit_fiat')) {
    class paymerchant_alfabit_fiat extends Ext_AutoPayut_Premiumbox {
        private array $disable_opts = ['checkpay'];
        private array $sum_format = [];
        private array $tx_statuses = [
            'success' => ['SUCCESS'],
            'payouterror' => ['FAILED', 'INVOICENOTCREATED', 'INVOICENOTPAYED', 'INVOICECHECKBLOCKED', 'TRANSFERBLOCKED', 'EXCHANGEBLOCKED', 'WITHDRAWBLOCKED'],
        ];

        function __construct($file, $title) {
            parent::__construct($file, $title, !in_array('ext_cron_url', $this->disable_opts));

            $this->tx_statuses = $this->tx_statuses ? array_replace(...array_map(fn($statuses, $processor) => array_fill_keys($statuses, $processor), $this->tx_statuses, array_keys($this->tx_statuses))) : [];

            if ($enabled_url = array_diff(['ext_webhook_url'], $this->disable_opts)) {
                foreach ($this->get_ids('paymerchants', $this->name) as $id) {
                    if (in_array('ext_webhook_url', $enabled_url)) add_action("premium_merchant_ap_{$id}_webhook" . hash_url($id, 'ap'), [$this, '_webhook_url']);
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
            global $premiumbox;

            $m_define = $this->get_file_data($id);
            $m_data = get_paymerch_data($id);

            $options = pn_array_unset($options, $this->disable_opts);
            if (in_array('ext_cron_url', $this->disable_opts)) $options = pn_array_unset($options, ['cronhash']);
            if (in_array('ext_webhook_url', $this->disable_opts)) $options = pn_array_unset($options, ['enableip', 'resulturl', 'help_resulturl']);

            $options['ext_line'] = ['view' => 'line'];

            $work_status = sprintf('<strong class="bred">%s</strong>', __('Config file is not configured', 'pn'));

            ////////////////////////////////////////

            $payment_methods = [0 => __('Config file is not configured', 'pn')];

            if (1 == $place && 'success' == $this->settingtext('success', $id)) {
                $work_status = sprintf('<strong class="bred">%s</strong>', mb_strtoupper(__('error', 'pn')));
                $payment_methods = [0 => mb_strtoupper(__('error', 'pn'))];

                $api = new P_ALFABITF($this->name, $id, $m_define, $m_data);

                $r = $api->payment_methods_list(is_isset($data, 'add_payment_method'));

                if ($r['pd']) {
                    $payment_methods = [0 => sprintf('-- %s --', __('Select method', 'pn'))] + $r['pd'];
                }

                $r = $api->balance();

                $premiumbox->update_option("ap_{$id}", 'balance_cc', array_keys($r['pd']));

                if (200 == $r['status_code']) {
                    $work_status = sprintf('<strong class="bgreen">%s</strong>', mb_strtoupper(__('ok', 'pn')));
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

            $options = $this->_add_field($options, $data, 'payment_method');

            ////////////////////////////////////////

            $options['work_status'] = ['view' => 'textfield', 'title' => __('Work status', 'pn'), 'default' => $work_status];

            $text_add_info = array_filter([
                'cron_url' => !in_array('ext_cron_url', $this->disable_opts) ? sprintf('<strong>%s:</strong> <a href="%s" target="_blank">%2$s</a>', __('Cron file', 'pn'), get_mlink("ap_{$id}_cron" . chash_url($id, 'ap'))) : null,
                'webhook_url' => !in_array('ext_webhook_url', $this->disable_opts) ? sprintf('<strong>%s:</strong> <a href="%s" target="_blank">%2$s</a>', 'Webhook URL', get_mlink("ap_{$id}_webhook" . hash_url($id, 'ap'))) : null,
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

                $api = new P_ALFABITF($this->name, $m_id, $m_defin, $m_data);

                return $this->_sum_format(is_isset($api->balance()['pd'], $purse), $m_id);
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
            $account = pn_strip_input(preg_replace('/\D/', '', is_isset($unmetas, 'get_phone') ?: is_isset($unmetas, 'phone') ?: $item->account_get));

            // M DATA
            $pay_sum = $this->_sum_format(is_paymerch_sum($item, $paymerch_data), $m_id);
            $note = trim(pn_maxf_mb(get_text_paymerch($m_id, $item, $pay_sum), 150));
            $pm = pn_strip_input(is_isset($paymerch_data, 'payment_method'));
            [$pm_currency, $pm_pm, $pm_alias, $pm_code] = array_pad(explode(':::', $pm, 4), 4, null);

            if (!$error && !$account) {
                $error[] = __('Wrong client wallet', 'pn');
            }

            if (!$error && !$this->set_ap_status($item, $test)) {
                $error[] = 'Database error';
            }

            if (!$error) {
                try {
                    $api = new P_ALFABITF($this->name, $m_id, $m_defin, $paymerch_data);

                    $r_pms = $api->payment_methods();

                    $data = [
                        'currency' => $pm_currency,
                        'paymentMethod' => $pm_pm,
                        'providerOutAliasCode' => $pm_alias,
                        'code' => $pm_code,
                        'amount' => $pay_sum,
                        'recipient' => $account,
                        'callbackUrl' => apply_filters('custom_url', get_mlink("ap_{$m_id}_webhook" . hash_url($m_id, 'ap')), 'webhook', $this->name, $m_id),
                        'details' => [],
                    ];

                    if ($note) $data['comment'] = $note;

                    $fields = $r_pms['pd'][$pm_code]['requiredFields'] ?? [];
                    if ($fields) {
                        foreach ($fields as $item) {
                            $id = mb_strtolower($item);
                            $val = is_isset($unmetas, "get_{$id}") ?: is_isset($unmetas, $id);

                            if ('first_name' == $id) {
                                $data['details'][$item] = pn_strip_input($val ?: $item->first_name);
                            } elseif ('last_name' == $id) {
                                $data['details'][$item] = pn_strip_input($val ?: $item->last_name);
                            } elseif ('email' == $id) {
                                $data['details'][$item] = pn_strip_input($val ?: $item->user_email);
                            } else {
                                $data['details'][$item] = pn_strip_input($val);
                            }
                        }
                    }

                    if (!$data['details']) unset($data['details']);

                    $r = $api->create_tx($data);

                    if ($r['pd']) {
                        $tx = $api->get_tx($r['pd']['uid'])['pd'];
                    } else {
                        if ($r['json']) unset($r['text']);
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
                $tx_id = pn_strip_input(is_isset($tx, 'uid'));
                $tx_status = mb_strtoupper(is_isset($tx, 'status'));
                $tx_amount = $this->_sum_format(is_isset($tx, 'amountOutFact'), $m_id);
                $tx_hash = pn_strip_input(is_isset($tx, 'txId'));

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

        function _webhook_url() {
            $m_id = key_for_url('_webhook', 'ap_');
            $m_define = $this->get_file_data($m_id);
            $m_data = get_paymerch_data($m_id);

            $this->webhook($m_id, $m_define, $m_data);
        }

        private function webhook($m_id, $m_define, $m_data) {

            $data = pn_json_decode(file_get_contents('php://input')) ?? [];

            do_action('paymerchant_secure', $this->name, $data, $m_id, $m_define, $m_data);

            $tx_id = pn_strip_input(is_isset($data, 'uid'));
            $tx_status = mb_strtoupper(is_isset($data, 'status'));
            $tx_dir = mb_strtoupper(is_isset($data, 'type'));

            $checked_fields = [$tx_id, $tx_status, $tx_dir];
            if (count(array_filter($checked_fields)) !== count($checked_fields) || 'WITHDRAW' != $tx_dir || empty($this->tx_statuses[$tx_status])) {
                wp_send_json_success();
            }

            $this->_payment_check(__FUNCTION__, $m_id, $m_define, $m_data, $tx_id);

            wp_send_json_success();
        }

        function cron($m_id, $m_defin, $m_data) {

            $this->_payment_check(__FUNCTION__, $m_id, $m_defin, $m_data);

        }

        private function _payment_check($place, $m_id, $m_define, $m_data, $tx_info = false) {
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

                $api = new P_ALFABITF($this->name, $m_id, $m_define, $m_data);
                $history = (empty($bids) || $tx_id) ? null : $api->get_txs()['pd'];

                foreach ($bids as $bid) {
                    $bid_id = $bid->id;
                    $bid_tx_id = $bid->trans_out;

                    $tx = is_array($tx_info) ? $tx_info : ($history[$bid_tx_id] ?? $api->get_tx($bid_tx_id)['pd']);

                    $tx_id = pn_strip_input(is_isset($tx, 'uid'));
                    $tx_status = mb_strtoupper(is_isset($tx, 'status'));
                    $tx_amount = $this->_sum_format(is_isset($tx, 'amountOutFact'), $m_id);
                    $tx_hash = pn_strip_input(is_isset($tx, 'txId'));

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

        function _sum_format($sum, $m_id) {

            if (!$m_id || get_mscript($m_id) !== $this->name) {
                return $sum;
            }

            $sum = is_sum($sum, ...array_slice($this->sum_format, 1));
            return is_string($sum) && is_isset($this->sum_format, 0) ? (float)$sum : $sum;
        }

        private function _add_field($options, $data, $name, $help = false) {

            $options["add_{$name}"] = [
                'view' => 'textarea',
                'title' => __('Add new', 'pn'),
                'default' => is_isset($data, "add_{$name}"),
                'name' => "add_{$name}",
                'rows' => 5,
                'work' => 'text',
            ];

            if (!$help) {
                $help = [__('Code', 'pn'), __('Code', 'pn') . '=' . __('Title', 'pn')];
            }

            $options["add_{$name}_help"] = ['view' => 'help', 'title' => __('Example', 'pn'), 'default' => implode('<br/>', $help)];

            return $options;
        }

    }
}

new paymerchant_alfabit_fiat(__FILE__, 'AlfaBit Fiat');
