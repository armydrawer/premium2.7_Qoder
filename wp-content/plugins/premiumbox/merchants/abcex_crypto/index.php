<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]ABCEX Crypto[:en_US][ru_RU:]ABCEX Crypto[:ru_RU]
description: [en_US:]Merchant ABCEX Crypto[:en_US][ru_RU:]Мерчант ABCEX Crypto[:ru_RU]
version: 2.7.2
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_abcex_crypto')) {
    class merchant_abcex_crypto extends Ext_Merchant_Premiumbox {

        function __construct($file, $title) {

            parent::__construct($file, $title, 1);

            add_filter('bcc_keys', array($this, 'set_keys'));
            add_filter('qr_keys', array($this, 'set_keys'));

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

            $m_defin = $this->get_file_data($m_id);

            $options = pn_array_unset($options, array('note', 'check_api', 'enableip', 'resulturl'));

            $options['merch_line'] = array(
                'view' => 'line',
            );

            $wallets = array(0 => __('Config file is not configured', 'pn'));
            $networks = array(0 => __('Config file is not configured', 'pn'));

            if (1 == $place and is_isset($m_defin, 'API_KEY')) {

                $api = new M_ABCEXCRYPTO($this->name, $m_id, is_isset($m_defin, 'API_KEY'));

                $r_wallets = $api->wallets();
                $r_networks = $api->networks();

                if (count($r_wallets)) {
                    $wallets = array(0 => '-- ' . __('Make a choice', 'pn') . ' --') + $r_wallets;
                }

                if (count($r_networks)) {
                    $networks = array(0 => '-- ' . __('Make a choice', 'pn') . ' --') + $r_networks;
                }

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
            <div><strong>Cron:</strong> <a href="' . get_mlink($m_id . '_cron' . chash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_cron' . chash_url($m_id)) . '</a></div>			
            ';
            $options['text_line'] = array(
                'view' => 'line',
            );
            $options['text'] = array(
                'view' => 'textfield',
                'title' => '',
                'default' => $text,
            );

            return $options;
        }

        function merch_type($m_id) {

            return 'address';
        }

        function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $wpdb, $bids_data;

            $item_id = $bids_data->id;
            $currency_code_give = mb_strtoupper($bids_data->currency_code_give);
            $currency_id_give = $bids_data->currency_id_give;

            $trans_id = pn_strip_input($bids_data->trans_in);
            $dest_tag = pn_strip_input($bids_data->dest_tag);
            $to_account = pn_strip_input($bids_data->to_account);

            if (!$to_account) {

                $wallet = pn_strip_input(is_isset($m_data, 'wallet'));
                $network = pn_strip_input(is_isset($m_data, 'network'));

                try {
                    $api = new M_ABCEXCRYPTO($this->name, $m_id, is_isset($m_defin, 'API_KEY'));

                    $data = array(
                        'walletId' => $wallet,
                        'networkId' => $network,
                    );
                    $r_address = $api->get_address($data);

                    if ($r_address) {

                        $to_account = pn_strip_input($r_address);

                    }
                } catch (Exception $e) {
                    $this->logs($e->getMessage(), $m_id);
                }

                if ($to_account) {

                    $arr = array();
                    $arr['trans_in'] = $trans_id;
                    $arr['to_account'] = $to_account;
                    $arr['dest_tag'] = $dest_tag;
                    $bids_data = update_bid_tb_array($item_id, $arr, $bids_data);

                    $notify_tags = array();
                    $notify_tags['[bid_id]'] = $item_id;
                    $notify_tags['[address]'] = $to_account;
                    $notify_tags['[sum]'] = $pay_sum;
                    $notify_tags['[dest_tag]'] = $dest_tag;
                    $notify_tags['[currency_code_give]'] = $bids_data->currency_code_give;
                    $notify_tags['[count]'] = $this->confirm_count($m_id, $m_defin, $m_data);

                    $admin_locale = get_admin_lang();
                    $now_locale = get_locale();
                    set_locale($admin_locale);

                    $user_send_data = array(
                        'admin_email' => 1,
                    );
                    $result_mail = apply_filters('premium_send_message', 0, 'generate_merchaddress2', $notify_tags, $user_send_data);

                    set_locale($now_locale);

                    $user_send_data = array(
                        'user_email' => $bids_data->user_email,
                    );
                    $user_send_data = apply_filters('user_send_data', $user_send_data, 'generate_merchaddress', $bids_data);
                    $result_mail = apply_filters('premium_send_message', 0, 'generate_merchaddress', $notify_tags, $user_send_data);

                }
            }

            if ($to_account) {
                return 1;
            }

            return 0;
        }

        function cron($m_id, $m_defin, $m_data) {
            $this->merch_cron($m_id, $m_defin, $m_data);
        }

        function merch_cron($m_id, $m_defin, $m_data, $order_id = '') {
            global $wpdb;

            $show_error = intval(is_isset($m_data, 'show_error'));
            $network = pn_strip_input(is_isset($m_data, 'network'));
            $order_id = pn_strip_input($order_id);

            try {
                $where = '';
                if ($order_id) {
                    $where = " AND trans_in = '$order_id'";
                }

                $api = new M_ABCEXCRYPTO($this->name, $m_id, is_isset($m_defin, 'API_KEY'));
                $history = $api->transactions(array('filter.networkId' => $network));

                $workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'));
                $workstatus_db = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'), 1);
                $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($workstatus_db) AND m_in = '$m_id' AND to_account <> '' $where");

                foreach ($items as $item) {
                    $item_id = $item->id;
                    $to_account = $item->to_account;

                    $tx = is_isset($history, $to_account);
                    if (empty($tx)) {
                        $r = $api->transactions(array('filter.networkId' => $network, 'filter.addressTo' => $to_account));
                        $tx = is_isset($r, $to_account);
                    }

                    if (empty($to_account) or empty($tx['id'])) {
                        continue;
                    }

                    $tx_id = pn_strip_input($tx['id']);
                    $tx_hash = pn_strip_input(is_isset($tx, 'txId'));
                    $tx_purse_from = pn_strip_input($tx['addressFrom']);
                    $tx_status = mb_strtoupper($tx['status']);
                    $tx_currency = mb_strtoupper($tx['currencyId']);
                    $tx_sum = is_sum($tx['amount']);

                    $realpay_st = array('COMPLETED');
                    $coldpay_st = array('PROCESSING');

                    $now_status = '';
                    if (in_array($tx_status, $realpay_st)) {
                        $now_status = 'realpay';
                    } elseif (in_array($tx_status, $coldpay_st)) {
                        $now_status = 'coldpay';
                    }

                    if ($now_status) {
                        $data = get_data_merchant_for_id($item_id, $item);

                        $err = $data['err'];
                        $status = $data['status'];
                        $bid_m_id = $data['m_id'];
                        $bid_m_script = $data['m_script'];

                        $pay_purse = is_pay_purse($tx_purse_from, $m_data, $bid_m_id);
                        $bid_currency = $data['currency'];

                        $bid_sum = is_sum($data['pay_sum']);
                        $bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
                        $bid_corr_sum = is_sum($bid_corr_sum);

                        $invalid_check = intval(is_isset($m_data, 'check'));
                        $invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
                        $invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
                        $invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));

                        if (!check_trans_in($bid_m_id, $tx_id, $item_id)) {
                            if (0 == $err and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name) {
                                if ($bid_currency == $tx_currency or $invalid_ctype > 0) {
                                    if ($tx_sum >= $bid_corr_sum or $invalid_minsum > 0) {

                                        $params = array(
                                            'sum' => $tx_sum,
                                            'bid_sum' => $bid_sum,
                                            'bid_corr_sum' => $bid_corr_sum,
                                            'pay_purse' => $pay_purse,
                                            'trans_in' => $tx_id,
                                            'txid_in' => $tx_hash,
                                            'currency' => $tx_currency,
                                            'bid_currency' => $bid_currency,
                                            'invalid_check' => $invalid_check,
                                            'invalid_ctype' => $invalid_ctype,
                                            'invalid_minsum' => $invalid_minsum,
                                            'invalid_maxsum' => $invalid_maxsum,
                                            'bid_status' => $workstatus,
                                            'm_place' => $m_id . '_cron',
                                            'm_id' => $m_id,
                                            'm_data' => $m_data,
                                            'm_defin' => $m_defin,
                                        );

                                        set_bid_status($now_status, $item_id, $params, $data['direction_data']);

                                    } else {
                                        $this->logs($item_id . ' The payment amount is less than the provisions', $m_id);
                                    }
                                } else {
                                    $this->logs($item_id . ' Wrong type of currency', $m_id);
                                }
                            } else {
                                $this->logs($item_id . ' bid error', $m_id);
                            }
                        } else {
                            $this->logs($item_id . ' Error check trans in!', $m_id);
                        }
                    }
                }
            } catch (Exception $e) {
                $this->logs($e->getMessage(), $m_id);
                if ($show_error and current_user_can('administrator')) {
                    die($e->getMessage());
                }
            }
        }
    }
}

new merchant_abcex_crypto(__FILE__, 'ABCEX Crypto');
