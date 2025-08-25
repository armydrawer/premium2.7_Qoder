<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Utopia Voucher[:en_US][ru_RU:]Utopia Voucher[:ru_RU]
description: [en_US:]Utopia Voucher automatic payouts[:en_US][ru_RU:]авто выплаты Utopia Voucher[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) return;

if (!class_exists('paymerchant_utopia_coupon')) {
    class paymerchant_utopia_coupon extends Ext_AutoPayut_Premiumbox {

        function __construct($file, $title) {
            parent::__construct($file, $title, 1);

            add_filter('list_user_notify', array($this, 'user_mailtemp'));
            add_filter('list_notify_tags_' . $this->name . '_paycoupon', array($this, 'mailtemp_tags_paycoupon'));
        }

        function get_map() {

            $map = array(
                'DOMAIN' => array(
                    'title' => '[en_US:]Node URL[:en_US][ru_RU:]Node URL[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 0,
                ),
                'TOKEN' => array(
                    'title' => '[en_US:]Token[:en_US][ru_RU:]Token[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
            );

            return $map;
        }

        function settings_list() {

            $arrs = array();
            $arrs[] = array('DOMAIN', 'TOKEN');

            return $arrs;
        }

        function options($options, $data, $m_id, $place) {

            $options = pn_array_unset($options, array('note', 'checkpay', 'enableip', 'resulturl'));

            $options['bindlogin'] = array(
                'view' => 'select',
                'title' => __('User email', 'pn'),
                'options' => array('0' => __('Invoice Receive', 'pn'), '1' => __('User email', 'pn')),
                'default' => intval(is_isset($data, 'bindlogin')),
                'name' => 'bindlogin',
                'work' => 'int',
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

        function user_mailtemp($places_admin) {

            $places_admin[$this->name . '_paycoupon'] = sprintf(__('%s automatic payout', 'pn'), $this->clear_title);

            return $places_admin;
        }

        function mailtemp_tags_paycoupon($tags) {

            $tags['bid_id'] = array(
                'title' => __('Order ID', 'pn'),
                'start' => '[bid_id]',
            );
            $tags['voucher_id'] = array(
                'title' => __('Coupon code', 'pn'),
                'start' => '[voucher_id]',
            );
            $tags['txid_out'] = array(
                'title' => __('Auto payout TxID', 'pn'),
                'start' => '[txid_out]',
            );

            return $tags;
        }

        function get_reserve_lists($m_id, $m_defin) {

            $purses = array();
            $currencies = array('CRP', 'USD');
            foreach ($currencies as $currency) {
                $purses[$m_id . '_' . strtolower($currency)] = strtoupper($currency);
            }

            return $purses;
        }

        function update_reserve($code, $m_id, $m_defin) {

            $sum = 0;
            $purses = $this->get_reserve_lists($m_id, $m_defin);
            $purse = trim(is_isset($purses, $code));
            if ($purse) {

                try {
                    $api = new AP_UTOPIA_C($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'TOKEN'));

                    $r = $api->getBalance($purse);
                    if (isset($r['result'])) {
                        $sum = is_sum($r['result']);
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

            $currency = mb_strtoupper($item->currency_code_get);
            $out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data));

            $bindlogin = intval(is_isset($paymerch_data, 'bindlogin'));
            $account = 1 == $bindlogin ? $item->user_email : $item->account_get;

            $allow_currencies = array('CRP', 'USD', 'UUSD');
            if (!in_array($currency, $allow_currencies)) {
                $error[] = __('Wrong currency code', 'pn');
            }

            if (!is_email($account)) {
                $error[] = __('Invalid email address.');
            }

            if (0 == count($error)) {
                $result = $this->set_ap_status($item, $test);
                if ($result) {

                    try {
                        $api = new AP_UTOPIA_C($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'TOKEN'));

                        $data = array(
                            'amount' => $sum,
                            'currency' => $currency,
                        );

                        $r = $api->createVoucher($data);

                        if (isset($r['result'])) {
                            $trans_id = $r['result'];
                        } else {
                            $error[] = __('Payout error', 'pn');
                            $pay_error = 1;
                        }

                    } catch (Exception $e) {
                        $error[] = $e->getMessage();
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
            $bindlogin = intval(is_isset($m_data, 'bindlogin'));

            $where = '';
            $order_id = pn_strip_input($order_id);
            if ($order_id) {
                $where = " AND `trans_out` = '$order_id'";
            }

            $api = new AP_UTOPIA_C($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'TOKEN'));
            $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id' AND trans_out <> '0' $where");

            foreach ($items as $item) {
                $item_id = $item->id;
                $trans_id = $item->trans_out;
                $account = 1 == $bindlogin ? $item->user_email : $item->account_get;

                $tx = $api->getFinanceHistory($trans_id);

                if (empty($tx['voucher_id'])) {
                    continue;
                }

                $tx_id = pn_strip_input($tx['id']);
                $tx_hash = pn_strip_input($tx['hash']);
                $tx_voucher = pn_strip_input($tx['voucher_id']);
                $tx_status = intval($tx['result_code']) . '.' . intval($tx['state']);

                if (in_array($tx_status, array('0.0'))) {

                    $bid_locale = $item->bid_locale;
                    $now_locale = get_locale();
                    set_locale($bid_locale);

                    $notify_tags = array();
                    $notify_tags['[bid_id]'] = $item_id;
                    $notify_tags['[voucher_id]'] = $tx_voucher;
                    $notify_tags['[txid_out]'] = $tx_hash;
                    $notify_tags = apply_filters('notify_tags_' . $this->name . '_paycoupon', $notify_tags);

                    $user_send_data = array('user_email' => $account);
                    $user_send_data = apply_filters('user_send_data', $user_send_data, $this->name . '_paycoupon', $item);
                    $result_mail = apply_filters('premium_send_message', 0, $this->name . '_paycoupon', $notify_tags, $user_send_data, $item->bid_locale);

                    set_locale($now_locale);

                    $coupon_data = array('coupon' => $tx_voucher, 'coupon_code' => $tx_id);
                    do_action('merchant_create_coupon', $coupon_data, $item, $this->name, 'cron ' . $m_id);

                    $params = array(
                        'system' => 'system',
                        'bid_status' => array('coldsuccess'),
                        'trans_out' => $tx_id,
                        'txid_out' => $tx_hash,
                        'm_place' => 'cron ' . $m_id,
                        'm_id' => $m_id,
                        'm_defin' => $m_defin,
                        'm_data' => $m_data,
                    );
                    set_bid_status('success', $item_id, $params);

                } elseif (in_array($tx_status, array('0.1', '1.0', '1.1'))) {

                    $this->reset_cron_status($item, $error_status, $m_id);

                }
            }
        }

    }
}

new paymerchant_utopia_coupon(__FILE__, 'Utopia Voucher');
