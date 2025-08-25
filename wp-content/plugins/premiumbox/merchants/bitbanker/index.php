<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Bitbanker[:en_US][ru_RU:]Bitbanker[:ru_RU]
description: [en_US:]Merchant Bitbanker[:en_US][ru_RU:]Мерчант Bitbanker[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_bitbanker')) {
    class merchant_bitbanker extends Ext_Merchant_Premiumbox {

        function __construct($file, $title) {

            parent::__construct($file, $title, 1);

            $ids = $this->get_ids('merchants', $this->name);
            foreach ($ids as $id) {
                add_action('premium_merchant_' . $id . '_status' . hash_url($id), array($this, 'merchant_status'));
            }

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

            $options = pn_array_unset($options, array('note', 'check_api', 'check'));

            $options['merch_line'] = array(
                'view' => 'line',
            );

            $networks = array();
            $networks[0] = __('Config file is not configured', 'pn');

            if (1 == $place and is_isset($m_defin, 'API_KEY')) {

                $api = new M_BITBANKER($this->name, $m_id, is_isset($m_defin, 'API_KEY'));

                $r = $api->currencies();

                if (is_array($r) and count($r) and isset($r[0]['id'])) {
                    $networks = array();

                    foreach ($r as $currency) {
                        if (!$currency['is_active']) continue;
                        if ($currency['is_fiat']) continue;
                        if (!$currency['is_deposit_enabled']) continue;
                        if (empty($currency['blockchain_networks'])) continue;

                        foreach ($currency['blockchain_networks'] as $network) {
                            if (!$network['is_deposit_enabled']) continue;
                            if (!$network['is_show_invoice']) continue;

                            $n_symbol = pn_strip_input($network['symbol']);
                            $c_name = pn_strip_input($currency['name']);
                            $c_symbol = pn_strip_input($currency['symbol']);

                            $title = $c_name . ' (' . $c_symbol . ')';

                            if (empty($networks[$n_symbol])) $networks[$n_symbol] = array();
                            if (in_array($title, $networks[$n_symbol])) continue;

                            $networks[$n_symbol][] = $title;
                        }
                    }

                    $networks_tmp = array();
                    foreach ($networks as $network_name => $currencies) {
                        sort($currencies);
                        $networks_tmp[$network_name] = mb_strtoupper($network_name) . ': ' . implode(', ', $currencies);
                    }
                    $networks = $networks_tmp;

                    asort($networks);
                    $networks = array(0 => '-- ' . __('Make a choice', 'pn') . ' --') + $networks;
                }

            }

            $options['network'] = array(
                'view' => 'select',
                'title' => __('Network', 'pn'),
                'options' => $networks,
                'default' => is_isset($data, 'network'),
                'name' => 'network',
                'work' => 'input',
            );

            $text = '
            <div><strong>Callback URL:</strong> <a href="' . get_mlink($m_id . '_status' . hash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_status' . hash_url($m_id)) . '</a></div>
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

                $network = pn_strip_input(is_isset($m_data, 'network'));

                try {
                    $api = new M_BITBANKER($this->name, $m_id, is_isset($m_defin, 'API_KEY'));

                    $data = array(
                        'payment_currencies' => array($currency_code_give),
                        'currency' => $currency_code_give,
                        'amount' => $pay_sum,
                        'description' => __('Order ID', 'pn') . ': ' . $item_id,
                        'language' => 'ru_RU' == get_locale() ? 'ru' : 'en',
                        'header' => pn_strip_input(get_bloginfo('sitename')),
                        'payer' => $bids_data->user_email,
                        'is_convert_payments' => false,
                        'payment_chains' => array($network),
                    );
                    $r = $api->invoices($data);

                    if (isset($r['id'], $r['addresses'][$currency_code_give]) and count($r['addresses'][$currency_code_give])) {
                        $addr = reset($r['addresses'][$currency_code_give]);

                        $trans_id = pn_strip_input(is_isset($r, 'id'));
                        $to_account = pn_strip_input($addr['address']);
                        $dest_tag = pn_strip_input(is_isset($addr, 'memo'));

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

                    $user_send_data = array('admin_email' => 1,);
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

        function merchant_status() {

            $m_id = key_for_url('_status');
            $m_defin = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            $post = pn_json_decode(file_get_contents('php://input'));

            do_action('merchant_secure', $this->name, $post, $m_id, $m_defin, $m_data);

            if (isset($post['id'])) {
                $this->merch_cron($m_id, $m_defin, $m_data, $post['id']);
            }

            echo 'OK';
            exit;
        }

        function cron($m_id, $m_defin, $m_data) {
            $this->merch_cron($m_id, $m_defin, $m_data);
        }

        function merch_cron($m_id, $m_defin, $m_data, $order_id = '') {
            global $wpdb;

            $show_error = intval(is_isset($m_data, 'show_error'));
            $order_id = pn_strip_input($order_id);

            try {
                $where = '';
                if ($order_id) {
                    $where = " AND trans_in = '$order_id'";
                }

                $api = new M_BITBANKER($this->name, $m_id, is_isset($m_defin, 'API_KEY'));
                $workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'));
                $workstatus_db = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'), 1);
                $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($workstatus_db) AND m_in = '$m_id' AND trans_in <> '0' $where");

                foreach ($items as $item) {
                    $item_id = $item->id;
                    $trans_in = $item->trans_in;

                    $tx = $api->get_invoice($trans_in);

                    if (empty($trans_in) or empty($tx['id'])) {
                        continue;
                    }

                    $tx_id = pn_strip_input($tx['id']);
                    $tx_currency = mb_strtoupper($tx['currency']);
                    $tx_sum = is_sum($tx['payed_amount']);
                    $tx_status = mb_strtoupper($tx_sum > 0 ? 'PAID' : 'NO');

                    if (in_array($tx_status, array('PAID'))) {
                        $data = get_data_merchant_for_id($item_id, $item);

                        $err = $data['err'];
                        $status = $data['status'];
                        $bid_m_id = $data['m_id'];
                        $bid_m_script = $data['m_script'];

                        $bid_currency = $data['currency'];

                        $bid_sum = is_sum($data['pay_sum']);
                        $bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
                        $bid_corr_sum = is_sum($bid_corr_sum);

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
                                            'bid_status' => $workstatus,
                                            'bid_corr_sum' => $bid_corr_sum,
                                            'trans_in' => $tx_id,
                                            'currency' => $tx_currency,
                                            'bid_currency' => $bid_currency,
                                            'invalid_ctype' => $invalid_ctype,
                                            'invalid_minsum' => $invalid_minsum,
                                            'invalid_maxsum' => $invalid_maxsum,
                                            'm_place' => $m_id,
                                            'm_id' => $m_id,
                                            'm_data' => $m_data,
                                            'm_defin' => $m_defin,
                                        );

                                        set_bid_status('realpay', $item_id, $params, $data['direction_data']);

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

new merchant_bitbanker(__FILE__, 'Bitbanker');
