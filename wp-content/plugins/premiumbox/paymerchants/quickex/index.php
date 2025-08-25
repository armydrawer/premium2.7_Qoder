<?php

if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Quickex[:en_US][ru_RU:]Quickex[:ru_RU]
description: [en_US:]Quickex automatic payouts[:en_US][ru_RU:]авто выплаты Quickex[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) return;

if (!class_exists('paymerchant_quickex')) {
    class paymerchant_quickex extends Ext_AutoPayut_Premiumbox {
        private array $disable_opts = ['ext_webhook', 'note', 'checkpay'];
        private array $sum_to_pay = [];
        private array $tx_statuses = [
            'success' => ['WITHDRAWAL_COMPLETED'],
            'coldsuccess' => ['FUNDS_WITHDRAWAL_START'],
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
            ];
        }

        function settings_list() {
            return [[]];
        }

        function options($options, $data, $id, $place) {
            global $premiumbox;

            $m_define = $this->get_file_data($id);
            $m_data = get_paymerch_data($id);

            $options = pn_array_unset($options, $this->disable_opts);
            if (in_array('ext_cron', $this->disable_opts)) $options = pn_array_unset($options, ['cronhash']);
            if (in_array('ext_webhook', $this->disable_opts)) $options = pn_array_unset($options, ['enableip', 'resulturl', 'help_resulturl']);

            $options['ext_line'] = ['view' => 'line'];

            $text_check_api = [0 => '<strong class="bred">' . mb_strtoupper(__('error', 'pn')) . '</strong>'];

            if (1 == $place && 'success' == $this->settingtext('success', $id)) {
                $api = new P_QUICKEX($this->name, $id, $m_define, $m_data);

                $r = $api->instruments();

                if (200 == $r['status_code']) {
                    $text_check_api[0] = '<strong class="bgreen">' . mb_strtoupper(__('ok', 'pn')) . '</strong>';
                }
            }

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

        function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {
            global $wpdb;

            // BID DATA
            $tx = null;
            $item_id = $item->id;
            $account = pn_strip_input($item->account_get);
            $bid_tx_id = pn_strip_input($item->trans_in);

            // M DATA
            $pay_sum = $this->sum_to_pay(is_paymerch_sum($item, $paymerch_data), $m_id);

            if (!$error && !$account) {
                $error[] = __('Wrong client wallet', 'pn');
            }

            if (!$error && !$this->set_ap_status($item, $test)) {
                $error[] = 'Database error';
            }

            if (!$error) {
                try {
                    $api = new P_QUICKEX($this->name, $m_id, $m_defin, $paymerch_data);

                    $tx = $api->get_payment($bid_tx_id, $account)['pd'];

                    if (!empty($tx['orderId'])) {
                        $tx['tx_id'] = $tx['orderId'];
                    } else {
                        $error[] = __('Payout error', 'pn');
                        $error[] = pn_json_encode($tx);
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
                $tx_withdrawals = !empty($tx['withdrawals']) ? $tx['withdrawals'] : [];
                $tx_status = !empty($tx['orderEvents'][0]['kind']) ? mb_strtoupper($tx['orderEvents'][0]['kind']) : null;
                $tx_amount = $tx_withdrawals ? array_sum(array_map(fn($v) => $this->sum_to_pay($v['amount'], $m_id), $tx_withdrawals)) : null;
                $tx_hash = $tx_withdrawals ? pn_strip_input($tx_withdrawals[0]['txId']) : '';

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

            $data = pn_json_decode(file_get_contents('php://input')) ?? [];

            do_action('paymerchant_secure', $this->name, $data, $m_id, $m_define, $m_data);

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

                $api = new P_QUICKEX($this->name, $m_id, $m_define, $m_data);
                $history = (empty($bids) || $tx_id) ? null : $api->get_payments()['pd'];

                foreach ($bids as $bid) {
                    $bid_id = $bid->id;
                    $bid_tx_id = $bid->trans_out;
                    $bid_tx_id_in = $bid->trans_in;
                    $bid_account_get = $bid->account_get;

                    $tx = is_array($tx_info) ? $tx_info : ($history[$bid_tx_id] ?? null);

                    if (empty($tx['none'])) {
                        $tx = $api->get_payment($bid_tx_id_in, $bid_account_get)['pd'];
                    }

                    $tx_id = !empty($tx['orderId']) ? pn_strip_input($tx['orderId']) : null;
                    $tx_withdrawals = !empty($tx['withdrawals']) ? $tx['withdrawals'] : [];
                    $tx_status = !empty($tx['orderEvents'][0]['kind']) ? mb_strtoupper($tx['orderEvents'][0]['kind']) : null;
                    $tx_amount = $tx_withdrawals ? array_sum(array_map(fn($v) => $this->sum_to_pay($v['amount'], $m_id), $tx_withdrawals)) : null;
                    $tx_hash = $tx_withdrawals ? pn_strip_input($tx_withdrawals[0]['txId']) : '';

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

new paymerchant_quickex(__FILE__, 'Quickex');
