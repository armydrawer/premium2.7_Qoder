<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]AlfaBit Crypto[:en_US][ru_RU:]AlfaBit Crypto[:ru_RU]
description: [en_US:]AlfaBit Crypto automatic payouts[:en_US][ru_RU:]авто выплаты AlfaBit Crypto[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_alfabit_crypto')) {
    class paymerchant_alfabit_crypto extends Ext_AutoPayut_Premiumbox {

        function __construct($file, $title) {

            parent::__construct($file, $title, 1);

            $ids = $this->get_ids('paymerchants', $this->name);
            foreach ($ids as $id) {
                add_action('premium_merchant_ap_' . $id . '_callback' . hash_url($id, 'ap'), array($this, 'merchant_callback'));
            }
			
        }

        function get_map() {

            $map = array(
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
            $arrs[] = array('API_KEY');

            return $arrs;
        }

        function options($options, $data, $m_id, $place) {
            global $premiumbox;

            $m_defin = $this->get_file_data($m_id);

            $options = pn_array_unset($options, array('checkpay'));

            $options['line_error_status'] = array(
                'view' => 'line',
            );

            $currencies = array();
            $currencies[0] = __('Config file is not configured', 'pn');

            $convert_from = array();
            $convert_from[0] = __('No', 'pn');

            if (1 == $place and is_isset($m_defin, 'API_KEY')) {

                $api = new AP_ALFABIT($this->name, $m_id, is_isset($m_defin, 'API_KEY'));

                $allow_currencies = array();
                $r = $api->assets_currencies();

                if (isset($r['data']) and is_array($r['data'])) {
                    $currencies[0] = '-- ' . __('Automatically', 'pn') . ' --';

                    $r['data'] = array_filter($r['data'], function ($item) {
                        return !$item['isInternal'] && 'COIN' === $item['currencyType'];
                    });

                    usort($r['data'], function ($a, $b) {
                        return strcmp($a['publicName'], $b['publicName']);
                    });

                    foreach ($r['data'] as $item) {
                        $public_code = pn_strip_input($item['publicCode']);
                        $public_name = pn_strip_input($item['publicName']);
                        $currencies[$public_code] = '[' . $public_code . '] ' . $public_name;
                        $allow_currencies[] = $public_code;
                    }
                }
                $premiumbox->update_option('ap_' . $m_id, 'allow_currencies', $allow_currencies);


                $r = $api->assets();

                if (isset($r['data']) and is_array($r['data'])) {
                    $r['data'] = array_filter($r['data'], function ($item) {
                        return 'COIN' === $item['type'];
                    });

                    usort($r['data'], function ($a, $b) {
                        return strcasecmp($a['code'], $b['code']);
                    });

                    foreach ($r['data'] as $item) {
                        $code = pn_strip_input($item['code']);
                        $convert_from[$code] = $code;
                    }
                }


                $balance_currencies = array();
                $r = $api->balances();

                if (isset($r['data']) and is_array($r['data'])) {
                    foreach ($r['data'] as $item) {
                        $balance_currencies[] = pn_strip_input($item['code']);
                    }
                }

                sort($balance_currencies);
                $premiumbox->update_option('ap_' . $m_id, 'balance_currencies', $balance_currencies);
				
            }

            $options['convert_from'] = array(
                'view' => 'select',
                'title' => __('Converting from another currency (specify code)', 'pn'),
                'options' => $convert_from,
                'default' => is_isset($data, 'convert_from'),
                'name' => 'convert_from',
                'work' => 'input',
            );

            $options['currency'] = array(
                'view' => 'select',
                'title' => __('Currency name', 'pn'),
                'options' => $currencies,
                'default' => is_isset($data, 'currency'),
                'name' => 'currency',
                'work' => 'input',
            );

            $text = '
            <div><strong>Callback URL:</strong> <a href="' . get_mlink('ap_' . $m_id . '_callback' . hash_url($m_id, 'ap')) . '" target="_blank" rel="noreferrer noopener">' . get_mlink('ap_' . $m_id . '_callback' . hash_url($m_id, 'ap')) . '</a></div>
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
                    $api = new AP_ALFABIT($this->name, $m_id, is_isset($m_defin, 'API_KEY'));

                    $r = $api->balances();
                    if (isset($r['data'])) {
                        foreach ($r['data'] as $item) {
                            if ($purse == mb_strtoupper($item['code'])) {
                                $sum = is_sum($item['balance']);
                                break;
                            }
                        }
                    }
                } catch (Exception $e) {
                    $this->logs($e->getMessage(), $m_id);
                }

            }

            return $sum;
        }

        function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {
            global $wpdb, $premiumbox;

            $item_id = $item->id;
            $trans_id = 0;
            $currency_id_get = intval($item->currency_id_get);
            $account = $item->account_get;
            $out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data));
            $dest_tag = trim(is_isset($unmetas, 'dest_tag'));

            $currency = pn_strip_input(is_isset($paymerch_data, 'currency'));
            $convert_from = pn_strip_input(is_isset($paymerch_data, 'convert_from'));
            $show_error = intval(is_isset($paymerch_data, 'show_error'));
            $note = get_text_paymerch($m_id, $item, $sum);
            $note = trim(pn_maxf($note, 150));

            if (!$currency) {
                $currency_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_get'");
                if (isset($currency_data->id)) {
                    $currency = mb_strtoupper(is_xml_value($currency_data->xml_value));
                }
            }

            $allow_currencies = $premiumbox->get_option('ap_' . $m_id, 'allow_currencies');
            if (!is_array($allow_currencies)) {
                $allow_currencies = array();
            }

            if (!in_array($currency, $allow_currencies)) {
                $error[] = __('Wrong currency code', 'pn');
            }

            if (!$account) {
                $error[] = __('Wrong client wallet', 'pn');
            }

            if (0 == count($error)) {
                $result = $this->set_ap_status($item, $test);
                if ($result) {
                    try {
                        $api = new AP_ALFABIT($this->name, $m_id, is_isset($m_defin, 'API_KEY'));

                        $data = array(
                            'amount' => $sum,
                            'toCurrencyCode' => $currency,
                            'recipient' => $account,
                            'callbackUrl' => get_mlink('ap_' . $m_id . '_callback' . hash_url($m_id, 'ap')),
                        );

                        if ($note) {
                            $data['comment'] = $note;
                        }

                        if ($dest_tag) {
                            $data['requisitesMemoTag'] = $dest_tag;
                        }

                        if ($convert_from) {
                            $data['fromAssetCode'] = $convert_from;
                        }

                        if ($convert_from) {
                            $trans_id = $api->withdraw_with_exchange($data);
                        } else {
                            $trans_id = $api->withdraw($data);
                        }

                        if (!$trans_id) {
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

            if (isset($post['uid'])) {
                $this->this_ap_cron($m_id, $m_defin, $m_data, $post['uid']);
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
                $where = " AND `trans_out` = '$order_id'";
            }

            $api = new AP_ALFABIT($this->name, $m_id, is_isset($m_defin, 'API_KEY'));
            $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id' $where");

            foreach ($items as $item) {
                $item_id = $item->id;
                $trans_id = $item->trans_out;

                $res = $api->order($trans_id);
                $tx = is_isset($res, 'data');

                if (!$trans_id or !$tx) {
                    continue;
                }

                $tx_status = mb_strtoupper($tx['status']);
                $tx_hash = pn_strip_input($tx['txId']);

                if ('SUCCESS' == $tx_status) {

                    $params = array(
                        'system' => 'system',
                        'bid_status' => array('coldsuccess'),
                        'm_place' => 'cron ' . $m_id,
                        'm_id' => $m_id,
                        'm_defin' => $m_defin,
                        'm_data' => $m_data,
                    );
                    if ($tx_hash) {
                        $params['txid_out'] = $tx_hash;
                    }
                    set_bid_status('success', $item_id, $params);

                } elseif ('FAILED' == $tx_status) {

                    $this->reset_cron_status($item, $error_status, $m_id);

                }
            }
        }
    }
}

new paymerchant_alfabit_crypto(__FILE__, 'AlfaBit Crypto');