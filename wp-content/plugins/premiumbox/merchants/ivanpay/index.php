<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]IvanPay[:en_US][ru_RU:]IvanPay[:ru_RU]
description: [en_US:]Merchant IvanPay[:en_US][ru_RU:]Мерчант IvanPay[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_ivanpay')) {
    class merchant_ivanpay extends Ext_Merchant_Premiumbox {

        function __construct($file, $title) {

            parent::__construct($file, $title, 1);

            $ids = $this->get_ids('merchants', $this->name);
            foreach ($ids as $id) {
                add_action('premium_merchant_' . $id . '_status' . hash_url($id), array($this, 'merchant_status'));
            }

            add_filter('sum_to_pay', array($this, 'sum_to_pay'), 100, 2);
            add_filter('change_bid_status', [$this, 'change_bid_status'], 2500);

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

            $m_defin = $this->get_file_data($m_id);

            $options = pn_array_unset($options, array('note', 'pagenote', 'check_api', 'check', 'invalid_ctype'));

            $options['merch_line'] = array(
                'view' => 'line',
            );

            $payment_methods = array();
            $payment_methods[0] = __('Config file is not configured', 'pn');

            if (1 == $place and is_isset($m_defin, 'DOMAIN') and is_isset($m_defin, 'API_KEY')) {

                $api = new M_IVANPAY($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'API_KEY'));

                $r = $api->getAvailableCurrencies();

                if (isset($r['currencies']) and is_array($r['currencies']) and count($r['currencies'])) {
                    $payment_methods = array();

                    foreach ($r['currencies'] as $method) {
                        $name = pn_strip_input($method['name']);
                        $code = pn_strip_input($method['code']);
                        $payment_methods['CARD:::' . $code] = '[CARD] ' . $name;

                        if (in_array($code, array('IVANPAY_CARD', 'IVANPAY_SBP'))) {
                            $payment_methods['SBP:::' . $code] = '[SBP] ' . $name;
                        }
                    }

                    asort($payment_methods);
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

            return 'mypaid';
        }

        function sum_to_pay($sum, $m_in) {

            $script = get_mscript($m_in);
            if ($script and $script == $this->name) {
                return is_sum($sum, 0, 'down');
            }

            return $sum;
        }

        function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $bids_data;

            if (!$bids_data->to_account) {

                $unmetas = maybe_unserialize($bids_data->unmetas);

                $show_error = intval(is_isset($m_data, 'show_error'));
                $pm_exp = explode(':::', pn_strip_input(is_isset($m_data, 'payment_method')));
                $pm_type = is_isset($pm_exp, 0);
                $pm_currency = is_isset($pm_exp, 1);

                $arr = array();

                $api = new M_IVANPAY($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'API_KEY'));

                $recipient_arr = array_values(array_filter(array(
                    'give_cardholder' => trim(is_isset($unmetas, 'give_cardholder')),
                    'cardholder' => trim(is_isset($unmetas, 'cardholder')),
                    'fullname' => pn_strip_input(implode(' ', array_filter(array($bids_data->last_name, $bids_data->first_name, $bids_data->second_name)))),
                )));

                $data = array(
                    'currency' => $pm_currency,
                    'amount' => $pay_sum,
                    'card_number' => preg_replace('/\D/', '', $bids_data->account_give),
                    'card_holder' => isset($recipient_arr[0]) ? $recipient_arr[0] : 'N/A',
                    'ext_id' => 'm_' . $bids_data->id,
                    'ip' => $bids_data->user_ip,
                    'user_agent' => $bids_data->user_agent,
                    'email' => $bids_data->user_email,
                    'webhook_url' => get_mlink($m_id . '_status' . hash_url($m_id)),
                );

                $is_sbp = 'SBP' == $pm_type;

                if ($is_sbp) {
                    $r = $api->createIncomingSbpPayFast($data);
                } else {
                    $r = $api->createIncomingPayFast($data);
                }

                $to_account = '';
                $dest_tag = '';
                $trans_in = '';

                if (isset($r['payment']['id'], $r['payment']['card_receiver']['card_number'], $r['payment']['card_receiver']['card_holder'], $r['payment']['card_receiver']['currency_name'])) {
                    $to_account = preg_replace('/\D/', '', $r['payment']['card_receiver']['card_number']);

                    if ($is_sbp) {
                        $to_account = '+' . (10 == mb_strlen($to_account) ? '7' : '') . $to_account;
                    }

                    $to_account = pn_strip_input($to_account);
                    $trans_in = pn_strip_input($r['payment']['id']);

                    $dest_tag_arr = array_values(array_filter(array(
                        'card_holder' => !empty($r['payment']['card_receiver']['card_holder']) ? pn_strip_input($r['payment']['card_receiver']['card_holder']) : null,
                        'bank' => !empty($r['payment']['card_receiver']['currency_name']) ? pn_strip_input($r['payment']['card_receiver']['currency_name']) : null,
                    )));
                    $dest_tag = empty($dest_tag_arr) ? '' : (1 == count($dest_tag_arr) ? $dest_tag_arr[0] : sprintf('%s (%s)', ...$dest_tag_arr));
                }

                if ($to_account) {
                    $arr['trans_in'] = $trans_in;
                    $arr['to_account'] = $to_account;
                    $arr['dest_tag'] = $dest_tag;
                    $bids_data = update_bid_tb_array($bids_data->id, $arr, $bids_data);
                }
            }

            if ($bids_data->to_account) {
                return 1;
            }

            return 0;
        }

        function myaction($m_id, $pay_sum, $direction) {
            global $bids_data;

            $script = get_mscript($m_id);
            if ($script and $script == $this->name) {
                $m_defin = $this->get_file_data($m_id);
                $m_data = get_merch_data($m_id);
                if ($bids_data->trans_in) {

                    $api = new M_IVANPAY($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'API_KEY'));
                    $r = $api->checkPayment($bids_data->trans_in);

                    if (isset($r['payment']['id'])) {

                        $api->checkIncomePaymentAlarm($bids_data->trans_in);

                        $st = get_status_sett('merch', 1);
                        $params = array(
                            'bid_status' => $st,
                            'm_place' => $m_id,
                            'm_id' => $m_id,
                            'm_data' => $m_data,
                            'm_defin' => $m_defin,
                        );
                        set_bid_status('payed', $bids_data->id, $params, $direction);

                    }

                }
            }
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

                $api = new M_IVANPAY($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'API_KEY'));
                $workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'));
                $workstatus_db = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'), 1);
                $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($workstatus_db) AND m_in = '$m_id' AND trans_in <> '0' $where");

                foreach ($items as $item) {
                    $item_id = $item->id;
                    $trans_in = $item->trans_in;

                    $r = $api->checkPayment($trans_in);
                    $tx = is_isset($r, 'payment');

                    if (!$trans_in or !isset($tx['id'])) {
                        continue;
                    }

                    $tx_id = pn_strip_input($tx['id']);
                    $tx_status = mb_strtoupper($tx['status']);
                    $tx_sum = is_sum($tx['real_amount'], 0, 'down');

                    if (in_array($tx_status, array('COMPLETED'))) {
                        $data = get_data_merchant_for_id($item_id, $item);

                        $err = $data['err'];
                        $status = $data['status'];
                        $bid_m_id = $data['m_id'];
                        $bid_m_script = $data['m_script'];

                        $bid_currency = $data['currency'];

                        $bid_sum = is_sum($data['pay_sum'], 0, 'down');
                        $bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
                        $bid_corr_sum = is_sum($bid_corr_sum, 0, 'down');

                        $invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
                        $invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));

                        if (!check_trans_in($bid_m_id, $tx_id, $item_id)) {
                            if (0 == $err and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name) {
                                if ($tx_sum >= $bid_corr_sum or $invalid_minsum > 0) {

                                    $params = array(
                                        'sum' => $tx_sum,
                                        'bid_sum' => $bid_sum,
                                        'bid_status' => $workstatus,
                                        'bid_corr_sum' => $bid_corr_sum,
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

        function change_bid_status($data) {
            $bid = $data['bid'];
            $set_status = $data['set_status'];
            $stop_action = intval(is_isset($data, 'stop'));

            if ($stop_action || 'success' != $set_status || empty($bid->txid_out)) {
                return $data;
            }

            $m_id = $bid->m_in;
            if (!$m_id || get_mscript($m_id) !== $this->name) {
                return $data;
            }

            $m_define = $this->get_file_data($m_id);

            $api = new M_IVANPAY($this->name, $m_id, is_isset($m_define, 'DOMAIN'), is_isset($m_define, 'API_KEY'));

            $_data = [
                'paymentExtID' => 'm_' . $bid->id,
                'ext_transaction' => [
                    'amount' => (float)$bid->out_sum,
                    'currency' => $bid->currency_code_get,
                    'address' => $bid->account_get,
                    'txid' => $bid->txid_out,
                ],
            ];
            $api->setPaymentExtTransactionInfo($_data);

            return $data;
        }
    }
}

new merchant_ivanpay(__FILE__, 'IvanPay');
