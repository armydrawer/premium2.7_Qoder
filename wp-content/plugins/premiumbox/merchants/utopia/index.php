<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Utopia[:en_US][ru_RU:]Utopia[:ru_RU]
description: [en_US:]Utopia merchant[:en_US][ru_RU:]мерчант Utopia[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_utopia')) {
    class merchant_utopia extends Ext_Merchant_Premiumbox {

        function __construct($file, $title) {
			
            parent::__construct($file, $title, 1);

            add_filter('bcc_keys', array($this, 'set_keys'));
            add_filter('qr_keys', array($this, 'set_keys'));
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

        function options($options, $data, $id, $place) {

            $options = pn_array_unset($options, array('personal_secret', 'check_api', 'note', 'invalid_ctype', 'resulturl', 'workstatus'));

            $text = '
			<div><strong>Cron:</strong> <a href="' . get_mlink($id . '_cron' . chash_url($id)) . '" target="_blank">' . get_mlink($id . '_cron' . chash_url($id)) . '</a></div>		
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
			
            return 'address';
        }

        function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $wpdb, $bids_data;

            $item_id = $bids_data->id;
            $currency = strtoupper($bids_data->currency_code_give);
            $to_account = pn_strip_input($bids_data->to_account);

            if (!$to_account) {

                $show_error = intval(is_isset($m_data, 'show_error'));

                try {
                    $class = new Utopia($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'TOKEN'));
                    $address = $class->create_address();
                    if ($address) {
                        $to_account = pn_strip_input($address);
                    }
                } catch (Exception $e) {
                    $this->logs($e->getMessage(), $m_id);
                }

                if ($to_account) {

                    $arr = array();
                    $arr['to_account'] = $to_account;
                    $arr['dest_tag'] = '';
                    $bids_data = update_bid_tb_array($item_id, $arr, $bids_data);

                    $notify_tags = array();
                    $notify_tags['[bid_id]'] = $item_id;
                    $notify_tags['[address]'] = $to_account;
                    $notify_tags['[sum]'] = $pay_sum;
                    $notify_tags['[dest_tag]'] = '';
                    $notify_tags['[currency_code_give]'] = $bids_data->currency_code_give;
                    $notify_tags['[count]'] = 0;

                    $admin_locale = get_admin_lang();
                    $now_locale = get_locale();
                    set_locale($admin_locale);

                    $user_send_data = array(
                        'admin_email' => 1,
                    );
                    $result_mail = apply_filters('premium_send_message', 0, 'generate_merchaddress2', $notify_tags, $user_send_data);

                    set_locale($now_locale);

                    $user_send_data = array(
                        'user_email' => $bids_data->user_email,
                    );
                    $user_send_data = apply_filters('user_send_data', $user_send_data, 'generate_merchaddress', $bids_data);
                    $result_mail = apply_filters('premium_send_message', 0, 'generate_merchaddress', $notify_tags, $user_send_data);

                }
            }
			
            if ($to_account) {
                return 1;
            }
			
            return 0;
        }

        function cron($m_id, $m_defin, $m_data) {
            global $wpdb;

            $show_error = intval(is_isset($m_data, 'show_error'));

            try {
                $class = new Utopia($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'TOKEN'));
                $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN('new','techpay','coldpay') AND m_in = '$m_id'");
                foreach ($items as $item) {
                    $id = $item->id;
                    $trans_in = pn_strip_input(is_isset($item, 'trans_in'));
                    $to_account = pn_strip_input(is_isset($item, 'to_account'));
                    $dest_tag = pn_strip_input(is_isset($item, 'dest_tag'));
                    $order_currency = strtoupper(trim($item->currency_code_give));
                    if ($to_account) {
                        $trans = $class->check_transaction($to_account, $order_currency);
                        if (isset($trans['amount'])) {
                            $currency = strtoupper($trans['currency']);
                            $tx_hash = pn_strip_input($trans['hash']);
                            $data = get_data_merchant_for_id($id, $item);

                            $in_sum = $trans['amount'];
                            $in_sum = is_sum($in_sum, 12);
                            $err = $data['err'];
                            $bid_m_id = $data['m_id'];
                            $bid_m_script = $data['m_script'];

                            $bid_currency = strtoupper($data['currency']);
                            $bid_currency = in_array($bid_currency, ['USD', 'UUSD']) ? 'UUSD' : $bid_currency;

                            $pay_purse = is_pay_purse('', $m_data, $bid_m_id);

                            $bid_sum = is_sum($data['pay_sum'], 12);
                            $bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);

                            $invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
                            $invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
                            $invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
                            $invalid_check = intval(is_isset($m_data, 'check'));

                            if ($err == 0 and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name) {
                                if ($bid_currency == $currency or $invalid_ctype > 0) {
                                    if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {

                                        $params = array(
                                            'trans_in' => $tx_hash,
                                            'pay_purse' => $pay_purse,
                                            'sum' => $in_sum,
                                            'bid_sum' => $bid_sum,
                                            'bid_status' => array('new', 'techpay', 'coldpay'),
                                            'bid_corr_sum' => $bid_corr_sum,
                                            'currency' => $currency,
                                            'bid_currency' => $bid_currency,
                                            'invalid_ctype' => $invalid_ctype,
                                            'invalid_minsum' => $invalid_minsum,
                                            'invalid_maxsum' => $invalid_maxsum,
                                            'invalid_check' => $invalid_check,
                                            'm_place' => $bid_m_id . '_cron',
                                            'm_id' => $m_id,
                                            'm_data' => $m_data,
                                            'm_defin' => $m_defin,
                                        );
                                        set_bid_status('realpay', $id, $params, $data['direction_data']);

                                    } else {
                                        $this->logs($id . ' The payment amount is less than the provisions', $m_id);
                                    }
                                } else {
                                    $this->logs($id . ' In the application the wrong status', $m_id);
                                }
                            } else {
                                $this->logs($id . ' bid error', $m_id);
                            }
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

new merchant_utopia(__FILE__, 'Utopia');