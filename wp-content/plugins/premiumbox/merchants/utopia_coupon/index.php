<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Utopia Voucher[:en_US][ru_RU:]Utopia Voucher[:ru_RU]
description: [en_US:]Utopia Voucher merchant[:en_US][ru_RU:]мерчант Utopia Voucher[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_utopia_coupon')) {
    class merchant_utopia_coupon extends Ext_Merchant_Premiumbox {

        function __construct($file, $title) {

            parent::__construct($file, $title, 1);

            $ids = $this->get_ids('merchants', $this->name);
            foreach ($ids as $id) {
                add_action('premium_merchant_' . $id . '_status', array($this, 'merchant_status'));
            }
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

            $m_defin = $this->get_file_data($m_id);

            $options = pn_array_unset($options, array('note', 'check_api', 'enableip', 'resulturl', 'help_resulturl', 'check', 'workstatus'));

            $options['merch_line'] = array(
                'view' => 'line',
            );

            $text = '
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

            return 'coupon';
        }

        function bidaction($temp, $m_id, $pay_sum, $direction) {
            global $bids_data;

            $script = get_mscript($m_id);
            if ($script and $script == $this->name) {

                $error_code = intval(is_param_get('err'));

                $errors = array(
                    1 => __('You have not entered a coupon code or pin', 'pn'),
                    2 => __('API error', 'pn'),
                    3 => __('Coupon is not valid', 'pn'),
                );

                if (isset($errors[$error_code])) {
                    $temp .= $this->zone_error($errors[$error_code]);
                }

                $pagenote = get_pagenote($m_id, $bids_data, $pay_sum);
                if (strlen($pagenote) < 1) {
                    $pagenote = __('In order to pay an ID order', 'pn') . ' <b>' . $bids_data->id . '</b>, ' . __('enter coupon code valued', 'pn') . ' <b><span class="js_copy copy_item" data-clipboard-text="' . $pay_sum . '">' . $pay_sum . '</span> ' . is_site_value($bids_data->currency_code_give) . '</b>:';
                }

                $list_data = array(
                    'code' => array(
                        'title' => __('Code', 'pn'),
                        'name' => 'code',
                    )
                );

                $temp .= $this->zone_form($pagenote, $list_data, '', get_mlink($m_id . '_status'), $bids_data->hashed);

            }

            return $temp;
        }

        function merchant_status() {
            global $wpdb;

            $m_id = key_for_url('_status');
            $m_defin = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            $hashed = is_bid_hash(is_param_post('hash'));
            $code = pn_strip_input(trim(is_param_post('code')));

            if ($hashed) {
                $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE hashed = '$hashed'");
                if (isset($item->id)) {
                    $id = $item->id;
                    $data = get_data_merchant_for_id($id, $item);
                    $bid_err = $data['err'];
                    $bid_status = $data['status'];
                    $bid_m_id = $data['m_id'];
                    $bid_m_script = $data['m_script'];

                    if (0 == $bid_err and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name) {
                        $st = get_status_sett('merch', 1);
                        if (in_array($bid_status, $st)) {

                            $bid_sum = is_sum($data['pay_sum']);
                            $bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);

                            if ($code) {
                                try {
                                    $api = new M_UTOPIA_C($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'TOKEN'));

                                    $r = $api->useVoucher($code);

                                    if (isset($r['result'])) {

                                        $trans_in = pn_strip_input($r['result']);
                                        $pay_purse = is_pay_purse($code, $m_data, $bid_m_id);

                                        $params = array(
                                            'trans_in' => $trans_in,
                                            'sum' => $bid_sum,
                                            'bid_sum' => $bid_sum,
                                            'bid_status' => $st,
                                            'bid_corr_sum' => $bid_corr_sum,
                                            'pay_purse' => $pay_purse,
                                            'm_place' => $bid_m_id,
                                        );
                                        set_bid_status('coldpay', $id, $params, $data['direction_data']);

                                        wp_redirect(get_bids_url($item->hashed));
                                        exit;

                                    } else {
                                        $this->error_back($hashed, 3);
                                    }
                                } catch (Exception $e) {
                                    $this->logs($e->getMessage(), $m_id);
                                    $show_error = intval(is_isset($m_data, 'show_error'));
                                    if ($show_error and current_user_can('administrator')) {
                                        die($e->getMessage());
                                    }
                                    $this->error_back($hashed, 2);
                                }
                            } else {
                                $this->error_back($hashed, 1);
                            }
                        } else {
                            $this->error_back($hashed, 1);
                        }
                    } else {
                        pn_display_mess(__('Error 3!', 'pn'));
                    }
                } else {
                    pn_display_mess(__('Error 2!', 'pn'));
                }
            } else {
                pn_display_mess(__('Error 1!', 'pn'));
            }
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

                $api = new M_UTOPIA_C($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'TOKEN'));
                $workstatus = _merch_workstatus($m_id, array('techpay', 'coldpay'));
                $workstatus_db = _merch_workstatus($m_id, array('techpay', 'coldpay'), 1);
                $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($workstatus_db) AND m_in = '$m_id' AND trans_in <> '0' $where");

                foreach ($items as $item) {
                    $item_id = $item->id;
                    $trans_in = $item->trans_in;
                    $pay_ac = $item->pay_ac;

                    $tx = $api->getFinanceHistory($trans_in);

                    if (!$trans_in or empty($tx['voucher_id'])) {
                        continue;
                    }

                    $tx_id = pn_strip_input($tx['id']);
                    $tx_hash = pn_strip_input($tx['hash']);
                    $tx_voucher = pn_strip_input($tx['voucher_id']);
                    $tx_status = intval($tx['result_code']) . '.' . intval($tx['state']);
                    $tx_currency = mb_strtoupper($tx['currency']);
                    $tx_sum = is_sum($tx['amount'], 12);

                    if ($pay_ac != $tx_voucher) {
                        continue;
                    }

                    if (in_array($tx_status, array('0.0'))) {
                        $data = get_data_merchant_for_id($item_id, $item);

                        $err = $data['err'];
                        $status = $data['status'];
                        $bid_m_id = $data['m_id'];
                        $bid_m_script = $data['m_script'];
                        $bid_currency = $data['currency'];
                        $bid_currency = in_array($bid_currency, ['USD', 'UUSD']) ? 'UUSD' : $bid_currency;
                        $bid_sum = is_sum($data['pay_sum'], 12);
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
                                            'txid_in' => $tx_hash,
                                            'currency' => $tx_currency,
                                            'bid_currency' => $bid_currency,
                                            'invalid_ctype' => $invalid_ctype,
                                            'invalid_minsum' => $invalid_minsum,
                                            'invalid_maxsum' => $invalid_maxsum,
                                            'm_place' => $bid_m_id . '_cron',
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

new merchant_utopia_coupon(__FILE__, 'Utopia Voucher');
