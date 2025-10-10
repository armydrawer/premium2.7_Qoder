<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Exnode[:en_US][ru_RU:]Exnode[:ru_RU]
description: [en_US:]Exnode merchant[:en_US][ru_RU:]мерчант Exnode[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_exnode')) {
    class merchant_exnode extends Ext_Merchant_Premiumbox {

        function __construct($file, $title) {
            parent::__construct($file, $title, 0);

            $ids = $this->get_ids('merchants', $this->name);
            foreach ($ids as $id) {
                add_action('premium_merchant_' . $id . '_callback' . hash_url($id), array($this, 'merchant_callback'));
            }

            add_filter('bcc_keys', array($this, 'set_keys'));
            add_filter('qr_keys', array($this, 'set_keys'));

        }

        function get_map() {

            $map = array(
                'PRIVATE_KEY' => array(
                    'title' => '[en_US:]Private key[:en_US][ru_RU:]Private key[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
                'PUBLIC_KEY' => array(
                    'title' => '[en_US:]Public key[:en_US][ru_RU:]Public key[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
            );

            return $map;
        }

        function settings_list() {

            $arrs = array();
            $arrs[] = array('PRIVATE_KEY', 'PUBLIC_KEY');

            return $arrs;
        }

        function get_methods($place, $m_id) {

            $m_defin = $this->get_file_data($m_id);
            $class = new Exnode($this->name, $m_id, is_isset($m_defin, 'PRIVATE_KEY'), is_isset($m_defin, 'PUBLIC_KEY'));
            $methods = $class->list_currencies($place);

            return $methods;
        }

        function options($options, $data, $m_id, $place) {

            $options = pn_array_unset($options, array('personal_secret', 'check_api', 'workstatus', 'cronhash'));

            $options['currency_code'] = array(
                'view' => 'select',
                'title' => __('Currency code', 'pn'),
                'options' => $this->get_methods($place, $m_id),
                'default' => is_isset($data, 'currency_code'),
                'name' => 'currency_code',
                'work' => 'input',
            );

            $options['address_type'] = array(
                'view' => 'select',
                'title' => __('Currency code', 'pn'),
                'options' => array('0' => 'STATIC', '1' => 'SINGLE'),
                'default' => is_isset($data, 'address_type'),
                'name' => 'address_type',
                'work' => 'input',
            );

            return $options;
        }

        function merch_type($m_id) {

            return 'address';
        }

        function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $wpdb, $bids_data;

            $to_account = pn_strip_input($bids_data->to_account);
            $dest_tag = '';
            $trans_in = '';

            if (!$to_account) {

                $currency_code = strtoupper(pn_strip_input(is_isset($m_data, 'currency_code')));
                $currencies = $this->get_methods(0, $m_id);
                if (!isset($currencies[$currency_code])) {
                    if (count($currencies) > 0) {
                        $currency_code = array_key_first($currencies);
                    } else {
                        $currency_code = 'no';
                    }
                }

                $item_id = $bids_data->id;
                $currency = strtoupper($bids_data->currency_code_give);
                $currency_id_give = $bids_data->currency_id_give;

                $show_error = intval(is_isset($m_data, 'show_error'));

                $address_types = array('0' => 'STATIC', '1' => 'SINGLE');
                $address_type_option = intval(is_isset($m_data, 'address_type'));
                $address_type = is_isset($address_types, $address_type_option);

                try {

                    $pay_sum = is_sum($pay_sum, 8);
                    $class = new Exnode($this->name, $m_id, is_isset($m_defin, 'PRIVATE_KEY'), is_isset($m_defin, 'PUBLIC_KEY'));

                    $call_back_url = get_mlink($m_id . '_callback' . hash_url($m_id));
                    $client_id = '';
                    $transaction_description = get_text_pay($m_id, $bids_data, $pay_sum);

                    $res = $class->create_invoice($currency_code, $item_id, $call_back_url, $client_id, $transaction_description, $address_type);
                    if (isset($res['tracker_id'], $res['refer'])) {
                        $to_account = pn_strip_input($res['refer']);
                        $dest_tag = pn_strip_input(is_isset($res, 'dest_tag'));
                        $trans_in = pn_strip_input($res['tracker_id']);
                    }

                } catch (Exception $e) {

                    $this->logs($e->getMessage(), $m_id);

                }

                if ($to_account) {

                    $arr = array();
                    $arr['to_account'] = $to_account;
                    $arr['dest_tag'] = $dest_tag;
                    $arr['trans_in'] = $trans_in;
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

        function merchant_callback() {
            global $wpdb;

            $m_id = key_for_url('_callback');
            $m_defin = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            $callback = file_get_contents('php://input');
            $post = @json_decode($callback, true);

            do_action('merchant_secure', $this->name, $post, $m_id, $m_defin, $m_data);

            $show_error = intval(is_isset($m_data, 'show_error'));
            $currency_code = strtoupper(pn_strip_input(is_isset($m_data, 'currency_code')));

            $tracker_id = trim(is_isset($post, 'tracker_id'));
            if (strlen($tracker_id) > 0) {
                $class = new Exnode($this->name, $m_id, is_isset($m_defin, 'PRIVATE_KEY'), is_isset($m_defin, 'PUBLIC_KEY'));
                $order = $class->get_status($tracker_id);
                if (isset($order['amount'], $order['status'], $order['token_major_name'], $order['type'], $order['client_transaction_id']) and 'IN' == $order['type']) {
                    $order_id = intval($order['client_transaction_id']);
                    if ($order_id > 0) {
                        $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN('new','techpay','coldpay') AND m_in = '$m_id' AND id = '$order_id'");
                        if (isset($item->id)) {
                            $order_id = $item->id;
                            $to_account = strtoupper(pn_strip_input(is_isset($item, 'to_account')));
                            $dest_tag = pn_strip_input(is_isset($item, 'dest_tag'));

                            $order_amount = is_sum($order['amount']);
                            $order_currency = strtoupper($order['token_major_name']);
                            $order_memo = pn_strip_input(is_isset($order, 'dest_tag'));
                            $order_txid = pn_strip_input(is_isset($order, 'hash'));
                            $order_status = strtoupper(is_isset($order, 'status'));
                            $order_address = strtoupper(is_isset($order, 'receiver'));

                            if (!$order_memo and !$dest_tag or $order_memo == $dest_tag) {
                                if ($to_account and $to_account == $order_address) {

                                    $realpay_st = array('SUCCESS');
                                    $coldpay_st = array();

                                    $data = get_data_merchant_for_id($order_id, $item);

                                    $now_status = '';
                                    if (in_array($order_status, $realpay_st)) {
                                        $now_status = 'realpay';
                                    }
                                    if (count($coldpay_st) > 0 and in_array($order_status, $coldpay_st)) {
                                        $now_status = 'coldpay';
                                    }

                                    if ($now_status) {

                                        $in_sum = $order_amount;
                                        $in_sum = is_sum($in_sum, 8);
                                        $err = $data['err'];
                                        $status = $data['status'];
                                        $bid_m_id = $data['m_id'];
                                        $bid_m_script = $data['m_script'];

                                        $bid_currency = $data['currency'];

                                        $pay_purse = is_pay_purse('', $m_data, $bid_m_id);

                                        $bid_sum = is_sum($data['pay_sum'], 8);
                                        $bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);

                                        $invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
                                        $invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
                                        $invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
                                        $invalid_check = intval(is_isset($m_data, 'check'));

                                        if (0 == $err and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name) {
                                            if ($bid_currency == $order_currency or $invalid_ctype > 0) {
                                                if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {

                                                    $params = array(
                                                        'pay_purse' => $pay_purse,
                                                        'txid_in' => $order_txid,
                                                        'trans_in' => is_isset($order, 'tracker_id'),
                                                        'sum' => $in_sum,
                                                        'bid_sum' => $bid_sum,
                                                        'bid_status' => array('new', 'techpay', 'coldpay'),
                                                        'bid_corr_sum' => $bid_corr_sum,
                                                        'currency' => $order_currency,
                                                        'bid_currency' => $bid_currency,
                                                        'invalid_ctype' => $invalid_ctype,
                                                        'invalid_minsum' => $invalid_minsum,
                                                        'invalid_maxsum' => $invalid_maxsum,
                                                        'invalid_check' => $invalid_check,
                                                        'm_place' => $bid_m_id . '_callback',
                                                        'm_id' => $m_id,
                                                        'm_data' => $m_data,
                                                        'm_defin' => $m_defin,
                                                    );
                                                    set_bid_status($now_status, $order_id, $params, $data['direction_data']);

                                                } else {
                                                    $this->logs($order_id . ' The payment amount is less than the provisions', $m_id);
                                                }
                                            } else {
                                                $this->logs($order_id . ' In the application the wrong status', $m_id);
                                            }
                                        } else {
                                            $this->logs($order_id . ' bid error', $m_id);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                echo 'OK';
                exit;

            }

            echo 'No tracker id';
            exit;
        }

    }
}

new merchant_exnode(__FILE__, 'Exnode');