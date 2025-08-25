<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Rapira Crypto[:en_US][ru_RU:]Rapira Crypto[:ru_RU]
description: [en_US:]Rapira Crypto automatic payouts[:en_US][ru_RU:]авто выплаты Rapira Crypto[:ru_RU]
version: 2.7.4
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) return;

if (!class_exists('paymerchant_rapira')) {
    class paymerchant_rapira extends Ext_AutoPayut_Premiumbox {

        function __construct($file, $title) {

            parent::__construct($file, $title, 1);

        }

        function get_map() {

            $map = array(
                'PRIVATE_KEY' => array(
                    'title' => '[en_US:]Private Key[:en_US][ru_RU:]Private Key[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
                'UID' => array(
                    'title' => '[en_US:]UID[:en_US][ru_RU:]UID[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
                'host_type' => array(
                    'title' => '[en_US:]Host[:en_US][ru_RU:]Host[:ru_RU]',
                    'options' => array('0' => 'rapira.net', '1' => 'rapira.org'),
                    'view' => 'select',
                ),
            );

            return $map;
        }

        function settings_list() {

            $arrs = array();
            $arrs[] = array('PRIVATE_KEY', 'UID');

            return $arrs;
        }

        function options($options, $data, $m_id, $place) {
            global $premiumbox;

            $m_defin = $this->get_file_data($m_id);

            $options = pn_array_unset($options, array('note', 'checkpay', 'enableip', 'resulturl'));

            $options['line_error_status'] = array(
                'view' => 'line',
            );

            if (1 == $place and is_isset($m_defin, 'PRIVATE_KEY') and is_isset($m_defin, 'UID')) {

                $api = new AP_Rapira($this->name, $m_id, is_isset($m_defin, 'PRIVATE_KEY'), is_isset($m_defin, 'UID'), is_isset($m_defin, 'host_type'));

                $balance_currencies = $api->get_coins();

                sort($balance_currencies);
                $premiumbox->update_option('ap_' . $m_id, 'balance_currencies', $balance_currencies);

            }

            $options['currency'] = array(
                'view' => 'input',
                'title' => __('Currency XML-name', 'pn'),
                'default' => is_isset($data, 'currency'),
                'name' => 'currency',
                'work' => 'input',
            );

            $options['from'] = array(
                'view' => 'input',
                'title' => __('Conversion currency', 'pn'),
                'default' => is_isset($data, 'from'),
                'name' => 'from',
                'work' => 'input',
            );

            $text = '
			<div><strong>CRON:</strong> <a href="' . get_mlink('ap_' . $m_id . '_cron' . chash_url($m_id, 'ap')) . '" target="_blank">' . get_mlink('ap_' . $m_id . '_cron' . chash_url($m_id, 'ap')) . '</a></div>
			';
            $options[] = array(
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
                $purses[$m_id . '_' . $currency] = $currency;
            }

            return $purses;
        }

        function update_reserve($code, $m_id, $m_defin) {

            $sum = 0;

            $purses = $this->get_reserve_lists($m_id, $m_defin);
            $purse = trim(is_isset($purses, $code));
            if ($purse) {
                try {
                    $api = new AP_Rapira($this->name, $m_id, is_isset($m_defin, 'PRIVATE_KEY'), is_isset($m_defin, 'UID'), is_isset($m_defin, 'host_type'));
                    $res = $api->get_balance();
                    if (isset($res[$purse])) {
                        $sum = $res[$purse];
                    }
                } catch (Exception $e) {
                    $this->logs($e->getMessage(), $m_id);
                }
            }

            return $sum;
        }

        function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {

            $trans_id = 0;
            $item_id = $item->id;

            $currency_code_give = strtoupper($item->currency_code_give);
            $currency_code_get = strtoupper($item->currency_code_get);

            $currency_id_give = intval($item->currency_id_give);
            $currency_id_get = intval($item->currency_id_get);

            $account = str_replace(' ', '', trim($item->account_get));
            if (!$account) {
                $error[] = __('Wrong client wallet', 'pn');
            }

            $out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 12);

            $dest_tag = trim(is_isset($unmetas, 'dest_tag'));

            $currency = pn_strip_input(is_isset($paymerch_data, 'currency'));
            $from = pn_strip_input(is_isset($paymerch_data, 'from'));

            if (!$currency) {
                global $wpdb;

                $currency_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_get'");
                if (isset($currency_data->id)) {
                    $currency = mb_strtoupper(is_xml_value($currency_data->xml_value));
                }
            }

            if (0 == count($error)) {
                $result = $this->set_ap_status($item, $test);
                if ($result) {

                    try {
                        $api = new AP_Rapira($this->name, $m_id, is_isset($m_defin, 'PRIVATE_KEY'), is_isset($m_defin, 'UID'), is_isset($m_defin, 'host_type'));
                        $nonce = $item_id;
                        $res = $api->create_payout($currency, $account, $sum, $nonce, $dest_tag, $from);
                        if (isset($res['withdrawRecordId'])) {
                            $trans_id = $res['withdrawRecordId'];
                        } else {
                            $error[] = __('Not create order', 'pn');
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
                    'system' => 'user',
                    'out_sum' => $out_sum,
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
            global $wpdb;

            $error_status = is_status_name(is_isset($m_data, 'error_status'));

            $api = new AP_Rapira($this->name, $m_id, is_isset($m_defin, 'PRIVATE_KEY'), is_isset($m_defin, 'UID'), is_isset($m_defin, 'host_type'));

            $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id'");
            foreach ($items as $item) {
                $item_id = $item->id;
                $trans_id = $item->trans_out;
                if ($trans_id) {
                    $res = $api->get_history_payout($trans_id);
                    if (isset($res['status'])) {
                        $res_status = strtoupper($res['status']);
                        $txid = trim(is_isset($res, 'transactionNumber'));

                        /*
                        0 PENDING_CONFIRMATIONS,
                        1 PENDING_AML,
                        2 MANUAL_CHECK,
                        4 SUCCESS,
                        3 FAILED,
                        5 REJECTED,
                        6 RETURNED;
                        */

                        $st_success = array('SUCCESS');
                        $st_error = array('FAILED', 'REJECTED', 'RETURNED', 'REJECT');
                        if (in_array($res_status, $st_success)) {

                            $params = array(
                                'system' => 'system',
                                'bid_status' => array('coldsuccess'),
                                'm_place' => 'cron ' . $m_id,
                                'm_id' => $m_id,
                                'm_defin' => $m_defin,
                                'm_data' => $m_data,
                            );
                            if ($txid) {
                                $params['txid_out'] = $txid;
                            }
                            set_bid_status('success', $item->id, $params);

                        } elseif (in_array($res_status, $st_error)) {

                            $this->reset_cron_status($item, $error_status, $m_id);

                        }

                    }
                }
            }
        }
    }
}

new paymerchant_rapira(__FILE__, 'Rapira');