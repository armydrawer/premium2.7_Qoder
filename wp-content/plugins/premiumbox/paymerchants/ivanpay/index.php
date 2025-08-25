<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]IvanPay[:en_US][ru_RU:]IvanPay[:ru_RU]
description: [en_US:]IvanPay automatic payouts[:en_US][ru_RU:]авто выплаты IvanPay[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_ivanpay')) {
    class paymerchant_ivanpay extends Ext_AutoPayut_Premiumbox {

        function __construct($file, $title) {

            parent::__construct($file, $title, 1);

            $ids = $this->get_ids('paymerchants', $this->name);
            foreach ($ids as $id) {
                add_action('premium_merchant_ap_' . $id . '_callback' . hash_url($id, 'ap'), array($this, 'merchant_callback'));
            }

        }

        function get_map() {

            $map = array(
                'DOMAIN' => array(
                    'title' => '[en_US:]Domain[:en_US][ru_RU:]Домен[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 0,
                ),
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
            $arrs[] = array('DOMAIN', 'API_KEY');

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
            $payment_methods[0] = __('Config file is not configured', 'pn');

            if (1 == $place and is_isset($m_defin, 'DOMAIN') and is_isset($m_defin, 'API_KEY')) {

                $api = new AP_IVANPAY($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'API_KEY'));

                $r = $api->getAvailableCurrencies();

                if (isset($r['currencies']) and is_array($r['currencies']) and count($r['currencies'])) {
                    $payment_methods = array();

                    foreach ($r['currencies'] as $method) {
                        $name = pn_strip_input($method['name']);
                        $code = pn_strip_input($method['code']);

                        if ('IVANPAY_SBP' == $code) {
                            $payment_methods['SBP:::' . $code] = '[SBP] ' . $name;
                            continue;

                        }

                        $payment_methods['CARD:::' . $code] = '[CARD] ' . $name;
                        $payment_methods['SBP:::' . $code] = '[SBP] ' . $name;
                    }

                    asort($payment_methods);
                    $payment_methods = array(0 => '-- ' . __('Select method', 'pn') . ' --') + $payment_methods;
                }

                $balance_currencies = array();
                $r = $api->getBalances();

                if (isset($r['balances']['balance'])) {
                    foreach ($r['balances']['balance'] as $k => $item) {
                        $balance_currencies[] = pn_strip_input($k);
                    }
                }

                sort($balance_currencies);
                $premiumbox->update_option('ap_' . $m_id, 'balance_currencies', $balance_currencies);

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
                    $api = new AP_IVANPAY($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'API_KEY'));

                    $r = $api->getBalances();
                    if (isset($r['balances']['balance']) and array_key_exists(mb_strtolower($purse), $r['balances']['balance'])) {
                        $sum = is_sum($r['balances']['balance'][mb_strtolower($purse)]);
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

            $account = preg_replace('/\D/', '', $item->account_get);

            $currency_code_get = mb_strtoupper($item->currency_code_get);
            $out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 0, 'down');

            $pm_exp = explode(':::', pn_strip_input(is_isset($paymerch_data, 'payment_method')));
            $pm_type = is_isset($pm_exp, 0);
            $pm_currency = is_isset($pm_exp, 1);

            if (!$account) {
                $error[] = __('Wrong client wallet', 'pn');
            }

            if (0 == count($error)) {
                $result = $this->set_ap_status($item, $test);
                if ($result) {
                    try {
                        $api = new AP_IVANPAY($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'API_KEY'));

                        $data = array(
                            'currency' => $pm_currency,
                            'amount' => $sum,
                            'ext_id' => 'ap_' . $item->id,
                            'ip' => $item->user_ip,
                            'user_agent' => $item->user_agent,
                            'email' => $item->user_email,
                            'webhook_url' => get_mlink('ap_' . $m_id . '_callback' . hash_url($m_id, 'ap')),
                        );

                        $is_sbp = 'SBP' == $pm_type;

                        if ($is_sbp) {
                            $bankname_arr = array_values(array_filter(array(
                                'get_bankname' => trim(is_isset($unmetas, 'get_bankname')),
                                'bankname' => trim(is_isset($unmetas, 'bankname')),
                                'dir_bankname' => trim(pn_strip_input($item->psys_get) . ' ' . is_site_value($item->currency_code_get)),
                            )));

                            $data['phone_number'] = '+' . (10 == mb_strlen($account) ? '7' : '') . $account;
                            $data['sbp_bank_code'] = $bankname_arr[0];
                            $r = $api->createOutgoingSbpPay($data);
                        } else {
                            $data['card_number'] = $account;
                            $r = $api->createOutgoingPay($data);
                        }

                        if (isset($r['payment']['id'])) {
                            $trans_id = $r['payment']['id'];
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

            if (isset($post['id'])) {
                $this->this_ap_cron($m_id, $m_defin, $m_data, $post['id']);
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

            $api = new AP_IVANPAY($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'API_KEY'));
            $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id' AND trans_out <> '0' $where");

            foreach ($items as $item) {
                $item_id = $item->id;
                $trans_id = $item->trans_out;

                $r = $api->checkPayment($trans_id);
                $tx = is_isset($r, 'payment');

                if (!$trans_id or !isset($tx['id'])) {
                    continue;
                }

                $tx_status = mb_strtoupper($tx['status']);

                if ('COMPLETED' == $tx_status) {

                    $params = array(
                        'system' => 'system',
                        'bid_status' => array('coldsuccess'),
                        'm_place' => 'cron ' . $m_id,
                        'm_id' => $m_id,
                        'm_defin' => $m_defin,
                        'm_data' => $m_data,
                    );
                    set_bid_status('success', $item_id, $params);

                } elseif (in_array($tx_status, array('FAILED'))) {

                    $this->reset_cron_status($item, $error_status, $m_id);

                }
            }
        }
    }
}

new paymerchant_ivanpay(__FILE__, 'IvanPay');