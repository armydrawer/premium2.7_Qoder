<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Diffpay[:en_US][ru_RU:]Diffpay[:ru_RU]
description: [en_US:]Merchant Diffpay[:en_US][ru_RU:]Мерчант Diffpay[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_diffpay')) {
    class merchant_diffpay extends Ext_Merchant_Premiumbox {

        function __construct($file, $title) {

            parent::__construct($file, $title, 1);

            $ids = $this->get_ids('merchants', $this->name);
            foreach ($ids as $id) {
                add_action('premium_merchant_' . $id . '_status' . hash_url($id), array($this, 'merchant_status'));
            }

            add_filter('sum_to_pay', array($this, 'sum_to_pay'), 100, 2);
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

            $options = pn_array_unset($options, array('pagenote', 'note', 'check_api', 'check', 'invalid_minsum', 'invalid_maxsum'));

            $options['merch_line'] = array(
                'view' => 'line',
            );

            $payment_methods = array();
            $payment_methods[0] = __('Config file is not configured', 'pn');

            if (1 == $place and is_isset($m_defin, 'DOMAIN') and is_isset($m_defin, 'API_KEY')) {

                $api = new Diffpay($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'API_KEY'));

                $r = $api->payment_method();

                if (!isset($r['detail'])) {
                    $payment_methods[0] = '-- ' . __('Select method', 'pn') . ' --';

                    ksort($r);

                    array_walk($r, function (&$item) {
                        uasort($item, function ($a, $b) {
                            if ($a['method'] == 'ALL') return -1;
                            if ($b['method'] == 'ALL') return 1;

                            return strcasecmp($a['name'], $b['name']);
                        });
                    });

                    foreach ($r as $currencies) {
                        foreach ($currencies as $item) {
                            $method = pn_strip_input($item['method']);
                            $incomeCurrency = pn_strip_input($item['incomeCurrency']);
                            $name = pn_strip_input($item['name']);
                            $payment_methods[$incomeCurrency . ':::' . $method] = '[' . $incomeCurrency . '] ' . $name;
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
                return is_sum($sum, 2);
            }

            return $sum;
        }

        function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $bids_data;

            if (!$bids_data->to_account) {

                $currency_code_give = mb_strtoupper($bids_data->currency_code_give);

                $show_error = intval(is_isset($m_data, 'show_error'));
                $pm_exp = explode(':::', pn_strip_input(is_isset($m_data, 'payment_method')));
                $pm_currency = is_isset($pm_exp, 0);
                $pm_bank = is_isset($pm_exp, 1);

                $arr = array();
              
                $api = new Diffpay($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'API_KEY'));

                $data = array(
                    'pricing' => array(
                        'local' => array(
                            'amount' => $pay_sum,
                            'currency' => $pm_currency,
                        ),
                    ),
                    'selectedProvider' => array(
                        'method' => $pm_bank,
                    ),
                    'invoiceId' => $bids_data->id,
                    'callbackUrl' => get_mlink($m_id . '_status' . hash_url($m_id)),
                );

                $r_tr = $api->new_transaction($data);

                $to_account = '';
                $dest_tag = '';
                $trans_in = '';

                if (isset($r_tr['id'])) {
                    for ($i = 0; $i < 2; $i++) {
                        sleep(2);

                        $r_req = $api->get_requisites($r_tr['id']);

                        if (isset($r_req['requisite']['accountNumber'], $r_req['requisite']['accountName'], $r_req['requisite']['description'])) {
                            $is_sbp = 'SBP' == mb_strtoupper($r_req['requisite']['method']);
                            $to_account = preg_replace('/\D/', '', $r_req['requisite']['accountNumber']);

                            if ($is_sbp) {
                                $to_account = (10 == mb_strlen($to_account)) ? '+7' . $to_account : '+' . $to_account;
                            }

                            $to_account = pn_strip_input($to_account);
                            $trans_in = pn_strip_input($r_tr['id']);

                            $dest_tag_arr = array_values(array_filter(array(
                                'card_holder' => !empty($r_req['requisite']['accountName']) ? pn_strip_input($r_req['requisite']['accountName']) : null,
                                'bank' => !empty($r_req['requisite']['description']) ? pn_strip_input($r_req['requisite']['description']) : null,
                            )));
                            $dest_tag = empty($dest_tag_arr) ? '' : (1 == count($dest_tag_arr) ? $dest_tag_arr[0] : sprintf('%s (%s)', ...$dest_tag_arr));

                            break;
                        }
                    }
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

                    $api = new Diffpay($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'API_KEY'));
                    $r = $api->get_transaction($bids_data->trans_in);

                    if (isset($r['transaction']['id'])) {

                        $api->set_paid($bids_data->trans_in);

                        $params = array(
                            'bid_status' => get_status_sett('merch', 1),
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

            if (isset($post['transaction']['id'])) {
                $this->merch_cron($m_id, $m_defin, $m_data, $post['transaction']['id']);
            }

            echo 'OK';
            exit;
        }

        function cron($m_id, $m_defin, $m_data) {
			
            $this->merch_cron($m_id, $m_defin, $m_data, '');
			
        }

        function merch_cron($m_id, $m_defin, $m_data, $order_id) {
            global $wpdb;

            $show_error = intval(is_isset($m_data, 'show_error'));
            $order_id = pn_strip_input($order_id);

            try {
                $where = '';
                if ($order_id) {
                    $where = " AND trans_in = '$order_id'";
                }

                $api = new Diffpay($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'API_KEY'));
                $workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'));
                $workstatus_db = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'), 1);
                $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($workstatus_db) AND m_in = '$m_id' AND trans_in <> '0' $where");

                foreach ($items as $item) {
                    $item_id = $item->id;
                    $trans_in = $item->trans_in;

                    $r = $api->get_transaction($trans_in);
                    $tx = is_isset($r, 'transaction');

                    if (empty($trans_in) or empty(is_isset($tx, 'id'))) {
                        continue;
                    }

                    $tx_id = pn_strip_input($tx['id']);
                    $tx_status = mb_strtoupper($r['status']);
                    $tx_currency = mb_strtoupper($tx['pricing']['local']['currency']);
                    $tx_sum = is_sum($tx['pricing']['local']['amount'], 2);

                    if ('COMPLETED' == $tx_status) {
                        $data = get_data_merchant_for_id($item_id, $item);

                        $err = $data['err'];
                        $status = $data['status'];
                        $bid_m_id = $data['m_id'];
                        $bid_m_script = $data['m_script'];

                        $bid_currency = $data['currency'];

                        $bid_sum = is_sum($data['pay_sum'], 2);
                        $bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);

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

new merchant_diffpay(__FILE__, 'Diffpay');