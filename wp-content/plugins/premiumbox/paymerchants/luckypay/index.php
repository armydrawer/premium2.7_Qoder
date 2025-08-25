<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]LuckyPay[:en_US][ru_RU:]LuckyPay[:ru_RU]
description: [en_US:]LuckyPay automatic payouts[:en_US][ru_RU:]авто выплаты LuckyPay[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }


if (!class_exists('paymerchant_luckypay')) {
    class paymerchant_luckypay extends Ext_AutoPayut_Premiumbox {

        function __construct($file, $title) {

            parent::__construct($file, $title, 1);

            $ids = $this->get_ids('paymerchants', $this->name);
            foreach ($ids as $id) {
                add_action('premium_merchant_ap_' . $id . '_callback' . hash_url($id, 'ap'), array($this, 'merchant_callback'));
            }
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

            $options = pn_array_unset($options, array('note', 'checkpay'));

            $options['line_error_status'] = array(
                'view' => 'line',
            );

            $payment_methods = array();
            $payment_methods[0] = __('Config file is not configured', 'premium');

            if (1 == $place and is_isset($m_defin, 'API_KEY')) {

                $api = new AP_LUCKYPAY($this->name, $m_id, is_isset($m_defin, 'API_KEY'));

                $r = $api->payment_methods();

                $payment_methods = array();

                foreach ($r as $method) {
                    $method_id = pn_strip_input($method['method_id']);
                    $method_type = pn_strip_input($method['method_type']);
                    $method_name = pn_strip_input($method['method_name']);
                    $method_currency = pn_strip_input($method['method_currency']);

                    $payment_methods[$method_id] = '[' . $method_type . ', ' . $method_currency . '] ' . $method_name;
                }

                asort($payment_methods);
                
                $payment_methods = array(0 => '-- ' . __('Select method', 'pn') . ' --') + $payment_methods;

            }

            $options['payment_method'] = array(
                'view' => 'select',
                'title' => __('Payment method', 'pn'),
                'options' => $payment_methods,
                'default' => is_isset($data, 'payment_method'),
                'name' => 'payment_method',
                'work' => 'input',
            );

            $text = '
            <div><strong>Callback URL:</strong> <a href="' . get_mlink('ap_' . $m_id . '_callback' . hash_url($m_id, 'ap')) . '" target="_blank">' . get_mlink('ap_' . $m_id . '_callback' . hash_url($m_id, 'ap')) . '</a></div>
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

            $currencies = array('available');

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
                    $api = new AP_LUCKYPAY($this->name, $m_id, is_isset($m_defin, 'API_KEY'));

                    $r = $api->balance();
                    if (isset($r['deposit']) and array_key_exists(mb_strtolower($purse), $r['deposit'])) {
                        $sum = is_sum($r['deposit'][mb_strtolower($purse)]);
                    }
					
                } catch (Exception $e) {
                    $this->logs($e->getMessage(), $m_id);
                }

            }

            return $sum;
        }

        function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {
            global $premiumbox;

            $item_id = $item->id;
            $trans_id = 0;

            $account = preg_replace('/\D/', '', $item->account_get);

            $currency_code_get = mb_strtoupper($item->currency_code_get);
            $out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 0, 'down');

            $pm = pn_strip_input(is_isset($paymerch_data, 'payment_method'));

            if (!$account) {
                $error[] = __('Wrong client wallet', 'pn');
            }

            if (0 == count($error)) {
                $result = $this->set_ap_status($item, $test);
                if ($result) {
                    try {
                        $api = new AP_LUCKYPAY($this->name, $m_id, is_isset($m_defin, 'API_KEY'));

                        $data = array(
                            'client_order_id' => 'ap_' . $item->id,
                            'order_side' => 'Sell',
                            'payment_method_id' => $pm,
                            'amount' => $sum,
                            'customer_payment_account' => $account,
                        );

                        $cardholder = trim(is_isset($unmetas, 'get_cardholder'));

                        if ($cardholder) {
                            $data['customer_name'] = $cardholder;
                        }

                        $r = $api->order($data);

                        if (isset($r['id'])) {
                            $trans_id = $r['id'];
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
                    pn_display_mess(__('Automatic payout is done', 'pn'), __('Automatic payout is done', 'pn'), 'true');
                }

            }
        }

        function merchant_callback() {

            $m_id = key_for_url('_callback', 'ap_');
            $m_defin = $this->get_file_data($m_id);
            $m_data = get_paymerch_data($m_id);

            $post = pn_json_decode(file_get_contents('php://input'));

            do_action('paymerchant_secure', $this->name, $post, $m_id, $m_defin, $m_data);

            if (isset($post['payload']['id'])) {
                $this->this_ap_cron($m_id, $m_defin, $m_data, $post['payload']['id']);
            }

            echo 'OK';
            exit;
        }

        function cron($m_id, $m_defin, $m_data) {
			
            $this->this_ap_cron($m_id, $m_defin, $m_data);
			
        }

        function this_ap_cron($m_id, $m_defin, $m_data, $order_id = '') {
            global $wpdb;

            $error_status = is_status_name(is_isset($m_data, 'error_status'));

            $where = '';
            $order_id = pn_strip_input($order_id);
            if ($order_id) {
                $where = " AND trans_out = '$order_id'";
            }

            $api = new AP_LUCKYPAY($this->name, $m_id, is_isset($m_defin, 'API_KEY'));
            $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id' AND trans_out <> '0' $where");

            foreach ($items as $item) {
                $item_id = $item->id;
                $trans_id = trim($item->trans_out);

                $r = $api->get_order($trans_id);
                $tx = is_isset($r, 'order');

                if (!isset($tx['status'])) {
                    continue;
                }

                $tx_status = mb_strtoupper($tx['status']);

                if (in_array($tx_status, array('COMPLETED'))) {

                    $params = array(
                        'system' => 'system',
                        'bid_status' => array('coldsuccess'),
                        'm_place' => 'cron ' . $m_id,
                        'm_id' => $m_id,
                        'm_defin' => $m_defin,
                        'm_data' => $m_data,
                    );
                    set_bid_status('success', $item_id, $params);

                } elseif (in_array($tx_status, array('CANCELEDBYSERVICE', 'CANCELEDBYTIMEOUT'))) {

                    $this->reset_cron_status($item, $error_status, $m_id);

                }
            }
        }
    }
}

new paymerchant_luckypay(__FILE__, 'LuckyPay');