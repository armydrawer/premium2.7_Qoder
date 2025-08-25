<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]GoldEx[:en_US][ru_RU:]GoldEx[:ru_RU]
description: [en_US:]GoldEx automatic payouts[:en_US][ru_RU:]авто выплаты GoldEx[:ru_RU]
version: 2.6.4
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) return;

if (!class_exists('paymerchant_goldex')) {
    class paymerchant_goldex extends Ext_AutoPayut_Premiumbox {

        function __construct($file, $title) {

            parent::__construct($file, $title, 1);

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

            $options = pn_array_unset($options, array('note', 'checkpay', 'enableip', 'resulturl'));

            $options['line_error_status'] = array(
                'view' => 'line',
            );

            $payment_methods = array(0 => __('Config file is not configured', 'premium'));

            if (1 == $place and is_isset($m_defin, 'TOKEN')) {

                $api = new AP_GOLDEX($this->name, $m_id, is_isset($m_defin, 'TOKEN'));

                $r = $api->exchange_rate();

                if (!empty(['data'])) {
                    $payment_methods = array();

                    foreach ($r['data'] as $item) {
                        $item_id = intval($item['id']);
                        $rate = is_sum($item['value']);
                        $c_type = pn_strip_input($item['currency']['type']);
                        $c_name = pn_strip_input($item['currency']['name']);
                        $minValueFIAT = is_sum($item['minValueFIAT']);
                        $maxValueFIAT = is_sum($item['maxValueFIAT']);

                        if ($item['blocked'] or $item['currency']['blocked']) continue;
                        if (empty($rate)) continue;

                        $payment_methods[$item_id] = sprintf('[%s, %s] %s %s-%s', $item_id, $c_type, $c_name, $minValueFIAT, $maxValueFIAT);
                    }

                    #asort($payment_methods);
                    $payment_methods = array(0 => '-- ' . __('Select method', 'pn') . ' --') + $payment_methods;
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

                    $api = new AP_GOLDEX($this->name, $m_id, is_isset($m_defin, 'TOKEN'));

                    $r = $api->balance();
                    if (isset($r['data']['balance'])) {
                        $sum = $r['data']['balance'];
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
            $out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);

            $pm = intval(is_isset($paymerch_data, 'payment_method'));

            if (!$account) {
                $error[] = __('Wrong client wallet', 'pn');
            }

            if (0 == count($error)) {
                $result = $this->set_ap_status($item, $test);
                if ($result) {

                    try {
                        $api = new AP_GOLDEX($this->name, $m_id, is_isset($m_defin, 'TOKEN'));

                        $cardholder = is_isset(array_values(array_filter(array(
                            'get_cardholder' => trim(is_isset($unmetas, 'get_cardholder')),
                            'cardholder' => trim(is_isset($unmetas, 'cardholder')),
                        ))), 0);

                        $bankname = is_isset(array_values(array_filter(array(
                            'get_bankname' => trim(is_isset($unmetas, 'get_bankname')),
                            'bankname' => trim(is_isset($unmetas, 'bankname')),
                        ))), 0);

                        $iban = is_isset(array_values(array_filter(array(
                            'get_iban' => trim(is_isset($unmetas, 'get_iban')),
                            'iban' => trim(is_isset($unmetas, 'iban')),
                        ))), 0);

                        $revTagWiseTag = is_isset(array_values(array_filter(array(
                            'get_revTagWiseTag' => trim(is_isset($unmetas, 'get_revTagWiseTag')),
                            'revTagWiseTag' => trim(is_isset($unmetas, 'revTagWiseTag')),
                        ))), 0);

                        $inn = is_isset(array_values(array_filter(array(
                            'get_inn' => trim(is_isset($unmetas, 'get_inn')),
                            'inn' => trim(is_isset($unmetas, 'inn')),
                        ))), 0);

                        $phone = is_isset(array_values(array_filter(array(
                            'get_phone' => trim(is_isset($unmetas, 'get_phone')),
                            'phone' => trim(is_isset($unmetas, 'phone')),
                        ))), 0);
                        $phone = (11 == mb_strlen($phone) ? '+' : (10 == mb_strlen($phone) ? '+7' : '')) . $phone;

                        $user_email = trim(is_isset($item, 'user_email'));
                        $other = sprintf('%s: %s %s', __('Currency get', 'pn'), pn_strip_input($item->psys_get), is_site_value($item->currency_code_get));

                        $data = array(
                            'exchangeRateId' => $pm,
                            'amount' => $sum,
                            'cardNumber' => $account,
                            'requestId' => 'ap_' . $item_id,
                            'other' => $other,
                        );

                        if ($cardholder) $data['cardHolder'] = $cardholder;
                        if ($bankname) $data['externalBankName'] = $bankname;
                        if ($iban) $data['iban'] = $iban;
                        if ($revTagWiseTag) $data['revTagWiseTag'] = $revTagWiseTag;
                        if ($inn) $data['inn'] = $inn;
                        if ($phone) $data['phoneNumber'] = $phone;
                        if ($user_email) $data['email'] = $user_email;

                        $r = $api->new_request($data);
                        if (isset($r['data']['id'])) {
                            $trans_id = $r['data']['id'];
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

            $api = new AP_GOLDEX($this->name, $m_id, is_isset($m_defin, 'TOKEN'));
            $history = $api->get_requests();

            $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id' AND trans_out <> '0' $where");

            foreach ($items as $item) {
                $item_id = $item->id;
                $trans_id = 'ap_' . $item_id;

                $tx = is_isset($history, $trans_id);

                if (empty($tx)) {
                    $r = $api->get_request($trans_id);
                    $tx = is_isset($r, 'data');
                    if (!empty($tx)) $tx = is_isset($tx, 0);
                }

                if (empty($tx['id'])) {
                    continue;
                }

                $tx_id = $tx['id'];
                $tx_status = mb_strtoupper($tx['status']);

                if ('CLOSED' == $tx_status) {

                    $params = array(
                        'system' => 'system',
                        'bid_status' => array('coldsuccess'),
                        'trans_out' => $tx_id,
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

new paymerchant_goldex(__FILE__, 'GoldEx');
