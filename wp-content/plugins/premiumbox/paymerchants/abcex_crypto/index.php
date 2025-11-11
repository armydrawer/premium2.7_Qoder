<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]ABCEX Crypto[:en_US][ru_RU:]ABCEX Crypto[:ru_RU]
description: [en_US:]ABCEX Crypto automatic payouts[:en_US][ru_RU:]авто выплаты ABCEX Crypto[:ru_RU]
version: 2.7.2
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) return;

if (!class_exists('paymerchant_abcex_crypto')) {
    class paymerchant_abcex_crypto extends Ext_AutoPayut_Premiumbox {

        function __construct($file, $title) {

            parent::__construct($file, $title, 1);

        }

        function get_map() {

            $map = array(
                'API_KEY' => array(
                    'title' => '[en_US:]API key[:en_US][ru_RU:]API ключ[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
            );

            return $map;
        }

        function settings_list() {

            $arrs = array();
            $arrs[] = array('API_KEY');

            return $arrs;
        }

        function options($options, $data, $m_id, $place) {
            global $premiumbox;

            $m_defin = $this->get_file_data($m_id);

            $options = pn_array_unset($options, array('note', 'checkpay', 'enableip', 'resulturl'));

            $options['line_error_status'] = array(
                'view' => 'line',
            );

            $wallets = array(0 => __('Config file is not configured', 'pn'));
            $networks = array(0 => __('Config file is not configured', 'pn'));

            if (1 == $place and is_isset($m_defin, 'API_KEY')) {

                $api = new AP_ABCEXCRYPTO($this->name, $m_id, is_isset($m_defin, 'API_KEY'));

                $allow_currencies = array();
                $balance_currencies = array();
                $r_wallets = $api->wallets();
                $r_networks = $api->networks();

                if (count($r_wallets)) {
                    foreach ($r_wallets as $wallet) {
                        $allow_currencies[] = pn_strip_input($wallet);
                        $balance_currencies[] = pn_strip_input($wallet);
                    }

                    $wallets = array(0 => '-- ' . __('Make a choice', 'pn') . ' --') + $r_wallets;
                }

                if (count($r_networks)) {
                    $networks = array(0 => '-- ' . __('Make a choice', 'pn') . ' --') + $r_networks;
                }

                $premiumbox->update_option('ap_' . $m_id, 'allow_currencies', $allow_currencies);

                sort($balance_currencies);
                $premiumbox->update_option('ap_' . $m_id, 'balance_currencies', $balance_currencies);

            }

            $options['wallet'] = array(
                'view' => 'select',
                'title' => __('Wallet', 'pn'),
                'options' => $wallets,
                'default' => is_isset($data, 'wallet'),
                'name' => 'wallet',
                'work' => 'input',
            );

            $options['network'] = array(
                'view' => 'select',
                'title' => __('Network', 'pn'),
                'options' => $networks,
                'default' => is_isset($data, 'network'),
                'name' => 'network',
                'work' => 'input',
            );

            $text = '
            <div><strong>CRON:</strong> <a href="' . get_mlink('ap_' . $m_id . '_cron' . chash_url($m_id, 'ap')) . '" target="_blank">' . get_mlink('ap_' . $m_id . '_cron' . chash_url($m_id, 'ap')) . '</a></div>
            ';
            $options['text'] = array(
                'view' => 'textfield',
                'title' => '',
                'default' => $text,
            );

            return $options;
        }

        function get_reserve_lists($m_id, $m_defin) {
            global $premiumbox;

            $currencies = $premiumbox->get_option('ap_' . $m_id, 'balance_currencies');
            if (!is_array($currencies)) {
                $currencies = array();
            }

            $purses = array();

            foreach ($currencies as $currency) {
                $purses[$m_id . '_' . mb_strtolower($currency)] = mb_strtoupper($currency);
            }

            return $purses;
        }

        function update_reserve($code, $m_id, $m_defin) {

            $sum = 0;
            $purses = $this->get_reserve_lists($m_id, $m_defin);
            $purse = trim(is_isset($purses, $code));
            if ($purse) {

                try {
                    $api = new AP_ABCEXCRYPTO($this->name, $m_id, is_isset($m_defin, 'API_KEY'));

                    $r = $api->balances();
                    if (isset($r)) {
                        foreach ($r as $item) {
                            if ($purse == mb_strtoupper($item['currencyId'])) {
                                $sum = is_sum($item['serviceBalance']);
                                break;
                            }
                        }
                    }
                } catch (Exception $e) {
                    $this->logs($e->getMessage(), $m_id);
                }

            }

            return $sum;
        }

        function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {
            global $wpdb, $premiumbox;

            $item_id = $item->id;
            $trans_id = 0;
            $currency_code_get = strtoupper($item->currency_code_get);
            $account = $item->account_get;
            $out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data));
            $dest_tag = trim(is_isset($unmetas, 'dest_tag'));

            $wallet = pn_strip_input(is_isset($paymerch_data, 'wallet'));
            $network = pn_strip_input(is_isset($paymerch_data, 'network'));
            $show_error = intval(is_isset($paymerch_data, 'show_error'));

            $allow_currencies = $premiumbox->get_option('ap_' . $m_id, 'allow_currencies');
            if (!is_array($allow_currencies)) {
                $allow_currencies = array();
            }

            if (!in_array($currency_code_get, $allow_currencies)) {
                $error[] = __('Wrong currency code', 'pn');
            }

            if (!$account) {
                $error[] = __('Wrong client wallet', 'pn');
            }

            if (0 == count($error)) {
                $result = $this->set_ap_status($item, $test);
                if ($result) {
                    try {
                        $api = new AP_ABCEXCRYPTO($this->name, $m_id, is_isset($m_defin, 'API_KEY'));

                        $data = array(
                            'walletId' => $wallet,
                            'networkId' => $network,
                            'volume' => $sum,
                            'address' => $account,
                        );

                        $r = $api->withdraw($data);

                        if (!empty($r['transactionId'])) {
                            $trans_id = $r['transactionId'];
                        } else {
                            $error[] = __('Payout error', 'pn');
                            $pay_error = 1;
                        }
                    } catch (Exception $e) {
                        $error[] = $e;
                        $pay_error = 1;
                    }
                } else {
                    $error[] = 'Database error';
                }
            }

            if (count($error) > 0) {

                $this->reset_ap_status($error, $pay_error, $item, $place, $m_id, $test);

            } else {

                $params = array(
                    'trans_out' => $trans_id,
                    'out_sum' => $out_sum,
                    'system' => 'user',
                    'm_place' => $modul_place . ' ' . $m_id,
                    'm_id' => $m_id,
                    'm_defin' => $m_defin,
                    'm_data' => $paymerch_data,
                );
                set_bid_status('coldsuccess', $item_id, $params, $direction);

                if ('admin' == $place) {
                    pn_display_mess(__('Payment is successfully created. Waiting for confirmation.', 'pn'), __('Payment is successfully created. Waiting for confirmation.', 'pn'), 'true');
                }

            }
        }

        function cron($m_id, $m_defin, $m_data) {

            $this->this_ap_cron($m_id, $m_defin, $m_data);

        }

        function this_ap_cron($m_id, $m_defin, $m_data, $order_id = '') {
            global $wpdb;

            $error_status = is_status_name(is_isset($m_data, 'error_status'));
            $network = pn_strip_input(is_isset($m_data, 'network'));

            $where = '';
            $order_id = pn_strip_input($order_id);
            if ($order_id) {
                $where = " AND `trans_out` = '$order_id'";
            }

            $api = new AP_ABCEXCRYPTO($this->name, $m_id, is_isset($m_defin, 'API_KEY'));
            $history = $api->transactions(array('filter.networkId' => $network));

            $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out='$m_id' $where");

            foreach ($items as $item) {
                $item_id = $item->id;
                $trans_id = $item->trans_out;

                $tx = is_isset($history, $trans_id);
                if (empty($tx)) {
                    $tx = $api->transaction($trans_id);
                }

                if (empty($trans_id) or empty($tx['status'])) {
                    continue;
                }

                $tx_status = mb_strtoupper($tx['status']);
                $tx_hash = pn_strip_input(is_isset($tx, 'txId'));

                if (in_array($tx_status, array('COMPLETED'))) {

                    $params = array(
                        'txid_out' => $tx_hash,
                        'system' => 'system',
                        'bid_status' => array('coldsuccess'),
                        'm_place' => 'cron ' . $m_id,
                        'm_id' => $m_id,
                        'm_defin' => $m_defin,
                        'm_data' => $m_data,
                    );

                    set_bid_status('success', $item_id, $params);

                } elseif (in_array($tx_status, array('CANCELED', 'REJECTED'))) {

                    $this->reset_cron_status($item, $error_status, $m_id);

                }
            }
        }
    }
}

new paymerchant_abcex_crypto(__FILE__, 'ABCEX Crypto');
