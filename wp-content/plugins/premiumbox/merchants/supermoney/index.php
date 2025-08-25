<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Super Money[:en_US][ru_RU:]Super Money[:ru_RU]
description: [en_US:]Merchant Super Money[:en_US][ru_RU:]Мерчант Super Money[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_supermoney')) {
    class merchant_supermoney extends Ext_Merchant_Premiumbox {

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
                'CLIENT_ID' => array(
                    'title' => '[en_US:]Client ID[:en_US]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
                'CLIENT_SECRET' => array(
                    'title' => '[en_US:]Client secret[:en_US]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
                'SIGN_SECRET' => array(
                    'title' => '[en_US:]Signature secret[:en_US]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
            );

            return $map;
        }

        function settings_list() {

            $arrs = array();
            $arrs[] = array('CLIENT_ID', 'CLIENT_SECRET', 'SIGN_SECRET');

            return $arrs;
        }

        function options($options, $data, $m_id, $place) {

            $m_defin = $this->get_file_data($m_id);

            $options = pn_array_unset($options, array('note', 'pagenote', 'check_api', 'check'));

            $options['merch_line'] = array(
                'view' => 'line',
            );

            $payment_methods = array();
            $payment_methods[0] = __('Config file is not configured', 'pn');

            if (1 == $place and is_isset($m_defin, 'CLIENT_ID') and is_isset($m_defin, 'CLIENT_SECRET') and is_isset($m_defin, 'SIGN_SECRET')) {

                $api = new M_SUPERMONEY($this->name, $m_id, is_isset($m_defin, 'CLIENT_ID'), is_isset($m_defin, 'CLIENT_SECRET'), is_isset($m_defin, 'SIGN_SECRET'));

                $r = $api->banks();

                if (isset($r['items']) and is_array($r['items']) and count($r['items'])) {
                    $payment_methods = array();

                    foreach ($r['items'] as $method) {
                        $name = pn_strip_input($method['name']);
                        $currency = pn_strip_input($method['currency']);

                        $payment_methods["CARD:::{$currency}:::ANY"] = "[CARD, $currency] " . __('Random', 'pn');
                        $payment_methods["CARD:::{$currency}:::{$name}"] = "[CARD, $currency] {$name}";

                        if ('RUB' == $currency) {
                            $payment_methods["SBP:::{$currency}:::ANY"] = "[SBP, $currency] " . __('Random', 'pn');
                            $payment_methods["SBP:::{$currency}:::{$name}"] = "[SBP, $currency] {$name}";

                            $payment_methods["ACCOUNT:::{$currency}:::ANY"] = "[ACCOUNT, $currency] " . __('Random', 'pn');
                            $payment_methods["ACCOUNT:::{$currency}:::{$name}"] = "[ACCOUNT, $currency] {$name}";
                        }
                    }

                    uksort($payment_methods, function ($a, $b) {
                        $aIsAny = strpos($a, 'ANY') !== false;
                        $bIsAny = strpos($b, 'ANY') !== false;

                        if ($aIsAny and $bIsAny) {
                            return strcasecmp($a, $b);
                        }

                        if ($aIsAny) {
                            return -1;
                        }

                        if ($bIsAny) {
                            return 1;
                        }

                        return strcasecmp($a, $b);
                    });

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
                return is_sum($sum, 2);
            }

            return $sum;
        }

        function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
			global $bids_data;

            if (!$bids_data->to_account) {

                $show_error = intval(is_isset($m_data, 'show_error'));
                $pm_exp = explode(':::', pn_strip_input(is_isset($m_data, 'payment_method')));
                $pm_type = is_isset($pm_exp, 0);
                $pm_currency = is_isset($pm_exp, 1);
                $pm_bank = is_isset($pm_exp, 2);

                $arr = array();
				
				$api = new M_SUPERMONEY($this->name, $m_id, is_isset($m_defin, 'CLIENT_ID'), is_isset($m_defin, 'CLIENT_SECRET'), is_isset($m_defin, 'SIGN_SECRET'));

                $data = array(
                    'extId' => 'm_' . $bids_data->id,
					'currency' => $pm_currency,
                    'amount' => $pay_sum,
                    'callbackUrl' => get_mlink($m_id . '_status' . hash_url($m_id)),
                );

                if ('ANY' !== $pm_bank) {
                    $data['bank'] = $pm_bank;
                }

                if ('SBP' == $pm_type) {
                    $r = $api->transaction_sbp($data);
                } elseif ('ACCOUNT' == $pm_type) {
                    $r = $api->transaction_account($data);
                } else {
                    $r = $api->transaction_card($data);
                }

                $to_account = '';
                $dest_tag = '';
                $trans_in = '';

                if (isset($r['id'], $r['bankName'], $r['owner'], $r['paymentMethod'])) {
                    $paymentMethod = mb_strtoupper($r['paymentMethod']);

					if ('SBP' == $paymentMethod) {
                        $to_account = '+' . preg_replace('/\D/', '', $r['phoneNumber']);
                    } elseif ('ACCOUNT' == $paymentMethod) {
                        $to_account = preg_replace('/\D/', '', $r['accountNumber']);
                    } else {
                        $to_account = preg_replace('/\D/', '', $r['cardNumber']);
                    }

                    $to_account = pn_strip_input($to_account);
                    $trans_in = pn_strip_input($r['id']);

                    $dest_tag_arr = array_values(array_filter(array(
                        'card_holder' => !empty($r['owner']) ? pn_strip_input($r['owner']) : null,
						'bank' => !empty($r['bankName']) ? pn_strip_input($r['bankName']) : null,
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

                    $api = new M_SUPERMONEY($this->name, $m_id, is_isset($m_defin, 'CLIENT_ID'), is_isset($m_defin, 'CLIENT_SECRET'), is_isset($m_defin, 'SIGN_SECRET'));
                    $r = $api->get_transaction($bids_data->trans_in);

                    if (isset($r['id'])) {

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

            status_header(200);

            if (isset($post['id'])) {
                $this->merch_cron($m_id, $m_defin, $m_data, $post['id']);
            }

            echo pn_json_encode(array('status' => 'SUCCESS'));
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

                $api = new M_SUPERMONEY($this->name, $m_id, is_isset($m_defin, 'CLIENT_ID'), is_isset($m_defin, 'CLIENT_SECRET'), is_isset($m_defin, 'SIGN_SECRET'));
                $history = $api->get_transactions();

                $workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'));
                $workstatus_db = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'), 1);
                $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($workstatus_db) AND m_in = '$m_id' AND trans_in <> '0' $where");

                foreach ($items as $item) {
                    $item_id = $item->id;
                    $trans_id = $item->trans_in;

                    $tx = is_isset($history, $trans_id);
                    if (empty($tx)) {
                        $tx = $api->get_transaction($trans_id);
                    }

                    if (empty($trans_id) or empty($tx['id'])) {
                        continue;
                    }

                    $tx_id = pn_strip_input($tx['id']);
                    $tx_status = mb_strtoupper($tx['status']);
                    $tx_sum = is_sum($tx['amount'], 2);
                    $tx_currency = mb_strtoupper($tx['currency']);

                    if (in_array($tx_status, array('SUCCESSFUL'))) {
                        $data = get_data_merchant_for_id($item_id, $item);

                        $err = $data['err'];
                        $status = $data['status'];
                        $bid_m_id = $data['m_id'];
                        $bid_m_script = $data['m_script'];
                        $bid_currency = $data['currency'];
                        $bid_sum = is_sum($data['pay_sum'], 2);
                        $bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
                        $bid_corr_sum = is_sum($bid_corr_sum, 2);

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
                                            'm_place' => $m_id . '_cron',
                                            'm_id' => $m_id,
                                            'm_data' => $m_data,
                                            'm_defin' => $m_defin,
                                        );

                                        set_bid_status('realpay', $item_id, $params, $data['direction_data']);

                                    } else {
                                        $this->logs($item_id . ' The payment amount is less than the provisions', $m_id);
                                    }
                                } else {
                                    $this->logs($item_id . ' In the application the wrong status', $m_id);
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

new merchant_supermoney(__FILE__, 'Super Money');