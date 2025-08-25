<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Merchant001[:en_US][ru_RU:]Merchant001[:ru_RU]
description: [en_US:]Merchant001 automatic payouts[:en_US][ru_RU:]авто выплаты Merchant001[:ru_RU]
version: 2.6.1
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_merch001')) {
    class paymerchant_merch001 extends Ext_AutoPayut_Premiumbox {

        function __construct($file, $title) {

            parent::__construct($file, $title, 1);

            $ids = $this->get_ids('paymerchants', $this->name);
            foreach ($ids as $id) {
                add_action('premium_merchant_ap_' . $id . '_callback' . hash_url($id, 'ap'), array($this, 'merchant_callback'));
            }

        }

        function get_map() {

            $map = array(
                'TOKEN' => array(
                    'title' => '[en_US:]Token[:en_US][ru_RU:]Токен[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
            );

            return $map;
        }

        function settings_list() {

            $arrs = array();
            $arrs[] = array('TOKEN');

            return $arrs;
        }

        function options($options, $data, $m_id, $place) {

            $m_defin = $this->get_file_data($m_id);

            $options = pn_array_unset($options, array('note', 'checkpay'));

            $options['line_error_status'] = array(
                'view' => 'line',
            );

            $payment_methods = array();
            $payment_methods[0] = __('Config file is not configured', 'premium');

            if (1 == $place and is_isset($m_defin, 'TOKEN')) {

                $api = new AP_MERCHANT001($this->name, $m_id, is_isset($m_defin, 'TOKEN'));

                $r = $api->payment_methods();

                if (!isset($r['statusCode'], $r['message']) and count($r)) {
                    $payment_methods = array();
                    $payment_methods[0] = '-- ' . __('Select method', 'pn') . ' --';

                    if (isset($r['USDT'])) {
                        unset($r['USDT']);
                    }

                    uksort($r, function ($a, $b) {
                        return strcmp($a, $b);
                    });

                    array_walk($r, function (&$item) {
                        uasort($item, function ($a, $b) {
                            if ($a['incomeCurrency'] != $b['incomeCurrency']) {
                                return strcmp($a['incomeCurrency'], $b['incomeCurrency']);
                            } elseif ($a['outcomeCurrency'] != $b['outcomeCurrency']) {
                                return strcmp($a['outcomeCurrency'], $b['outcomeCurrency']);
                            } else {
                                return strcmp($a['name'], $b['name']);
                            }
                        });
                    });

                    foreach ($r as $currencies) {
                        foreach ($currencies as $method) {
                            $payment_methods[$method['method']] = '[' . $method['incomeCurrency'] . ' -> ' . $method['outcomeCurrency'] . '] ' . $method['name'];
                        }
                    }
                }

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

            $purses = array(
                $m_id . '_usdt' => 'USDT',
            );

            return $purses;
        }

        function update_reserve($code, $m_id, $m_defin) {

            $sum = 0;
            $purses = $this->get_reserve_lists($m_id, $m_defin);
            $purse = trim(is_isset($purses, $code));
            if ($purse) {

                try {
					
                    $api = new AP_MERCHANT001($this->name, $m_id, is_isset($m_defin, 'TOKEN'));

                    $balance = $api->get_balance();
                    if (isset($balance['amount'])) {
                        $sum = $balance['amount'];
                    }
					
                } catch (Exception $e) {
                    $this->logs($e->getMessage(), $m_id);
                }

            }

            return $sum;
        }

        function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {

            $item_id = $item->id;
            $trans_id = 0;

            $account = $item->account_get;
            //$currency_code_get = mb_strtoupper($item->currency_code_get);
            $out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);

            $show_error = intval(is_isset($paymerch_data, 'show_error'));
            $payment_method = pn_strip_input(is_isset($paymerch_data, 'payment_method'));

            if (!$account) {
                $error[] = __('Wrong client wallet', 'pn');
            }

            $api = new AP_MERCHANT001($this->name, $m_id, is_isset($m_defin, 'TOKEN'));

            if (0 == count($error)) {
				
                $payment_method_data = '';

                $r = $api->payment_methods();

                if (!isset($r['statusCode'], $r['message']) and is_array($r) and count($r)) {
					
                    if (isset($r['USDT'])) {
                        unset($r['USDT']);
                    }

                    foreach ($r as $currencies) {
                        foreach ($currencies as $method) {
                            if ($payment_method != $method['method']) {
                                continue;
                            }

                            $payment_method_data = $method;
                            break;
                        }
                    }
                }

                if (!is_array($payment_method_data)) {
                    $error[] = 'No payment method data';
                }

            }

            if (0 == count($error)) {
                $result = $this->set_ap_status($item, $test);
                if ($result) {
                    try {
						
                        $bankname_arr = array_values(array_filter(array(
                            'get_bankname' => trim(is_isset($unmetas, 'get_bankname')),
                            'bankname' => trim(is_isset($unmetas, 'bankname')),
                            'dir_bankname' => trim(pn_strip_input($item->psys_get) . ' ' . is_site_value($item->currency_code_get)),
                        )));

                        $data = array(
                            'outcomeAddress' => $account,
                            'balanceAmount' => array(
                                'currency' => $payment_method_data['incomeCurrency'], //'USDT',
                            ),
                            'withdrawAmount' => array(
                                'amount' => $sum,
                                'currency' => $payment_method_data['outcomeCurrency'], //$currency_code_get,
                            ),
                            'method' => $payment_method,
                            'callbackUrl' => get_mlink('ap_' . $m_id . '_callback' . hash_url($m_id, 'ap')),
                            'invoiceId' => $item_id,
                            'comment' => is_isset($bankname_arr, 0),
                        );

                        $trans_id = $api->send($data);
                        if (!$trans_id) {
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

            if (isset($post['transaction']['id'])) {
                $this->this_ap_cron($m_id, $m_defin, $m_data, $post['transaction']['id']);
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
                $where = " AND `trans_out` = '$order_id'";
            }

            $api = new AP_MERCHANT001($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
            $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id' AND trans_out <> '0' $where");

            foreach ($items as $item) {
                $item_id = $item->id;
                $trans_id = $item->trans_out;

                $res = $api->get_transaction($trans_id);
                $tx = is_isset($res, 'transaction');

                if (!$trans_id or !isset($tx['status'])) {
                    continue;
                }

                $tx_status = mb_strtoupper($tx['status']);

                if ('CONFIRMED' == $tx_status) {

                    $params = array(
                        'system' => 'system',
                        'bid_status' => array('coldsuccess'),
                        'm_place' => 'cron ' . $m_id,
                        'm_id' => $m_id,
                        'm_defin' => $m_defin,
                        'm_data' => $m_data,
                    );
                    set_bid_status('success', $item_id, $params);

                } elseif (in_array($tx_status, array('FAILED', 'EXPIRED', 'CANCELED'))) {

                    $this->reset_cron_status($item, $error_status, $m_id);

                }
            }
        }
    }
}

new paymerchant_merch001(__FILE__, 'Merchant001');