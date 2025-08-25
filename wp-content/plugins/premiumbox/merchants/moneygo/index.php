<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]MoneyGo[:en_US][ru_RU:]MoneyGo[:ru_RU]
description: [en_US:]MoneyGo merchant[:en_US][ru_RU:]мерчант MoneyGo[:ru_RU]
version: 2.7.2
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_moneygo')) {
    class merchant_moneygo extends Ext_Merchant_Premiumbox {

        function __construct($file, $title) {
            parent::__construct($file, $title, 1);

            $ids = $this->get_ids('merchants', $this->name);
            foreach ($ids as $id) {
                add_action('premium_merchant_' . $id . '_status' . hash_url($id), array($this, 'merchant_status'));
                add_action('premium_merchant_' . $id . '_fail', array($this, 'merchant_fail'));
                add_action('premium_merchant_' . $id . '_success', array($this, 'merchant_success'));
            }

        }

        function get_map() {

            $map = array(
                'CLIENT_ID' => array(
                    'title' => '[en_US:]Client ID[:en_US]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
                'CLIENT_SECRET' => array(
                    'title' => '[en_US:]Client Secret[:en_US]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
                'MERCHANT_SECRET' => array(
                    'title' => '[en_US:]Form Secret Key[:en_US]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
                'U_WALLET' => array(
                    'title' => '[en_US:]U-wallet[:en_US][ru_RU:]U-кошелек[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
                'E_WALLET' => array(
                    'title' => '[en_US:]E-wallet[:en_US][ru_RU:]E-кошелек[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
                'R_WALLET' => array(
                    'title' => '[en_US:]R-wallet[:en_US][ru_RU:]R-кошелек[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
            );

            return $map;
        }

        function settings_list() {

            $arrs = array();
            $arrs[] = array('CLIENT_ID', 'CLIENT_SECRET', 'MERCHANT_SECRET');

            return $arrs;
        }

        function options($options, $data, $m_id, $place) {

            $options = pn_array_unset($options, array('pagenote', 'show_error', 'check_api'));

            $options['private_line'] = array(
                'view' => 'line',
            );

            $text = '
			<div><strong>STATUS URL:</strong> <a href="' . get_mlink($m_id . '_status' . hash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_status' . hash_url($m_id)) . '</a></div>
			<div><strong>SUCCESS URL:</strong> <a href="' . get_mlink($m_id . '_success') . '" target="_blank">' . get_mlink($m_id . '_success') . '</a></div>
			<div><strong>FAIL URL:</strong> <a href="' . get_mlink($m_id . '_fail') . '" target="_blank">' . get_mlink($m_id . '_fail') . '</a></div>		
			<div><strong>Cron:</strong> <a href="' . get_mlink($m_id . '_cron' . chash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_cron' . chash_url($m_id)) . '</a></div>
			';

            $options['text'] = array(
                'view' => 'textfield',
                'title' => '',
                'default' => $text,
            );

            return $options;
        }

        function merch_type($m_id) {

            return 'link';
        }

        function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $bids_data;

            $currency = mb_strtoupper($bids_data->currency_code_give);
            $currency = str_replace('RUR', 'RUB', $currency);

            $pay_link = $this->get_pay_link($bids_data->id);
            if (!$pay_link) {

                $pay_sum = is_sum($pay_sum, 2);
                $text_pay = get_text_pay($m_id, $bids_data, $pay_sum);
                if (!$text_pay) {
                    $text_pay = sprintf(__('ID order %s', 'pn'), $bids_data->id);
                }
                $text_pay = trim(pn_maxf($text_pay, 250));

                $from_wallet = trim($bids_data->account_give);
                $to_wallet = '';
                if ('RUB' == $currency) {
                    $to_wallet = is_isset($m_defin, 'R_WALLET');
                } elseif ('USD' == $currency) {
                    $to_wallet = is_isset($m_defin, 'U_WALLET');
                } elseif ('EUR' == $currency) {
                    $to_wallet = is_isset($m_defin, 'E_WALLET');
                }

                try {
                    $class = new MoneyGo($this->name, $m_id, is_isset($m_defin, 'CLIENT_ID'), is_isset($m_defin, 'CLIENT_SECRET'), is_isset($m_defin, 'TOKEN'), is_isset($m_defin, 'MERCHANT_SECRET'));
                    $status_url = get_mlink($m_id . '_status' . hash_url($m_id)) . '?order_id=' . $bids_data->id;
                    $success_url = get_mlink($m_id . '_success') . '?order_id=' . $bids_data->id;
                    $fail_url = get_mlink($m_id . '_fail') . '?order_id=' . $bids_data->id;
                    $pay_link = $class->create_link($bids_data->id, $pay_sum, $from_wallet, $to_wallet, $success_url, $fail_url, $status_url, $text_pay);
                    if ($pay_link) {

                        $pay_link = pn_strip_input($pay_link);
                        $this->update_pay_link($bids_data->id, $pay_link);

                    }
                } catch (Exception $e) {
                    $this->logs($e->getMessage(), $m_id);
                }

            }

            if ($pay_link) {
                return 1;
            }

            return 0;
        }

        function merchant_fail() {

            $id = get_payment_id('order_id');
            redirect_merchant_action($id, $this->name);

        }

        function merchant_success() {

            $id = get_payment_id('order_id');
            redirect_merchant_action($id, $this->name, 1);

        }

        function merchant_status() {

            $m_id = key_for_url('_status');
            $m_defin = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            do_action('merchant_secure', $this->name, '', $m_id, $m_defin, $m_data);

            $order_id = is_param_get('order_id');
            if ($order_id) {
                $this->m_cron($m_id, $m_defin, $m_data, $order_id);
            }

            echo 'OK';
            exit;
        }

        function cron($m_id, $m_defin, $m_data) {

            $this->m_cron($m_id, $m_defin, $m_data);

        }

        function m_cron($m_id, $m_defin, $m_data, $order_id = 0) {
            global $wpdb;

            $show_error = intval(is_isset($m_data, 'show_error'));
            $need_confirm = intval(is_isset($m_data, 'need_confirm'));
            $order_id = intval($order_id);
            $where = '';
            if ($order_id > 0) {
                $where = " AND id = '$order_id'";
            }

            try {
                $class = new MoneyGo($this->name, $m_id, is_isset($m_defin, 'CLIENT_ID'), is_isset($m_defin, 'CLIENT_SECRET'), is_isset($m_defin, 'TOKEN'), is_isset($m_defin, 'MERCHANT_SECRET'));
                $workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay'));
                $workstatus_db = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay'), 1);
                $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($workstatus_db) AND m_in = '$m_id' $where");
                foreach ($items as $item) {
                    $item_id = $item->id;
                    $res = $class->check_transaction($item_id);
                    if (isset($res['data'], $res['data']['type'], $res['data']['status'], $res['data']['processing_pay']) and 'WITHDRAWAL' == $res['data']['type'] and 'SUCCESS' == $res['data']['status']) {
                        $order = $res['data']['processing_pay'];
                        $currency = $order['currency_to'];
                        $order_status = $order['status'];
                        $transaction_id = pn_strip_input(is_isset($order, 'transaction_id'));
                        $wallet_from = $order['wallet_from'];

                        $realpays = ['SUCCESS'];
                        $deleted = ['FAIL'];

                        if (in_array($order_status, $realpays)) $new_status = 'realpay';
                        elseif (in_array($order_status, $deleted)) $new_status = 'mercherror';
                        else $new_status = '';

                        if (in_array($new_status, ['realpay', 'coldpay'])) {
                            $data = get_data_merchant_for_id($item_id, $item);

                            $in_sum = $order['amount'];
                            $in_sum = is_sum($in_sum, 2);
                            $err = $data['err'];
                            $status = $data['status'];
                            $bid_m_id = $data['m_id'];
                            $bid_m_script = $data['m_script'];

                            $bid_currency = $data['currency'];

                            $pay_purse = is_pay_purse($wallet_from, $m_data, $bid_m_id);

                            $bid_sum = is_sum($data['pay_sum'], 2);
                            $bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);

                            $invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
                            $invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
                            $invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
                            $invalid_check = intval(is_isset($m_data, 'check'));

                            if (!check_trans_in($bid_m_id, $transaction_id, $item_id)) {
                                if (0 == $err and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name) {
                                    if ($bid_currency == $currency or $invalid_ctype > 0) {
                                        if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {

                                            $params = array(
                                                'pay_purse' => $pay_purse,
                                                'sum' => $in_sum,
                                                'bid_sum' => $bid_sum,
                                                'bid_status' => $workstatus,
                                                'bid_corr_sum' => $bid_corr_sum,
                                                'trans_in' => $transaction_id,
                                                'currency' => $currency,
                                                'bid_currency' => $bid_currency,
                                                'invalid_ctype' => $invalid_ctype,
                                                'invalid_minsum' => $invalid_minsum,
                                                'invalid_maxsum' => $invalid_maxsum,
                                                'invalid_check' => $invalid_check,
                                                'm_place' => $m_id,
                                                'm_id' => $m_id,
                                                'm_data' => $m_data,
                                                'm_defin' => $m_defin,
                                            );

                                            set_bid_status($new_status, $item_id, $params, $data['direction_data']);

                                        }
                                    }
                                }
                            }
                        } elseif (in_array($new_status, ['mercherror'])) {

                            $params = [
                                'm_place' => $m_id,
                                'm_id' => $m_id,
                                'm_data' => $m_data,
                                'm_defin' => $m_defin,
                            ];
                            set_bid_status($new_status, $item_id, $params, '');

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

new merchant_moneygo(__FILE__, 'MoneyGo');