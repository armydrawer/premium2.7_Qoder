<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Utopia[:en_US][ru_RU:]Utopia[:ru_RU]
description: [en_US:]Utopia automatic payouts[:en_US][ru_RU:]авто выплаты Utopia[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_utopia')) {
    class paymerchant_utopia extends Ext_AutoPayut_Premiumbox {

        function __construct($file, $title) {
            parent::__construct($file, $title, 1);
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

            $m_defin = $this->get_file_data($id);

            $options = pn_array_unset($options, array('checkpay', 'resulturl', 'enableip'));

            $text = '
			<div><strong>CRON:</strong> <a href="' . get_mlink('ap_' . $id . '_cron' . chash_url($id, 'ap')) . '" target="_blank">' . get_mlink('ap_' . $id . '_cron' . chash_url($id, 'ap')) . '</a></div>
			';
            $options['text'] = array(
                'view' => 'textfield',
                'title' => '',
                'default' => $text,
            );

            return $options;
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
            $sum = '0';
            $purse = strtoupper(trim(str_replace($m_id . '_', '', $code)));
            if ($purse) {
                try {

                    $class = new AP_Utopia($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'TOKEN'));
                    $sum = $class->get_balance($purse);

                } catch (Exception $e) {
                    $this->logs($e->getMessage(), $m_id);
                }
            }
            return $sum;
        }

        function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {

            $item_id = $item->id;
            $trans_id = 0;

            $currency_code_give = strtoupper($item->currency_code_give);
            $currency_code_get = strtoupper($item->currency_code_get);

            $currency_id_give = intval($item->currency_id_give);
            $currency_id_get = intval($item->currency_id_get);

            $account = $item->account_get;

            if (!$account) {
                $error[] = __('Wrong client wallet', 'pn');
            }

            $out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);

            $class = new AP_Utopia($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'TOKEN'));

            $notice = get_text_paymerch($m_id, $item, $sum);
            $notice = trim(pn_maxf($notice, 200));

            if (count($error) == 0) {
                $result = $this->set_ap_status($item, $test);
                if ($result) {
                    try {
                        $trans_id = $class->send($account, $sum, $currency_code_get, $notice);
                        if ($trans_id) {

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

                if ($place == 'admin') {
                    pn_display_mess(__('Automatic payout is done', 'pn'), __('Automatic payout is done', 'pn'), 'true');
                }

            }
        }

        function cron($m_id, $m_defin, $m_data) {
            global $wpdb;

            $error_status = is_status_name(is_isset($m_data, 'error_status'));

            $class = new AP_Utopia($this->name, $m_id, is_isset($m_defin, 'DOMAIN'), is_isset($m_defin, 'TOKEN'));
            $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id' AND trans_out <> '0'");

            foreach ($items as $item) {
                $trans_out = $item->trans_out;
                if ($trans_out) {
                    $tx = $class->check($trans_out);
                    if (is_isset($tx, 'referenceNumber')) {

                        $tx_id = pn_strip_input(is_isset($tx, 'id'));
                        $tx_hash = pn_strip_input(is_isset($tx, 'hash'));

                        $params = array(
                            'system' => 'system',
                            'bid_status' => array('coldsuccess'),
                            'trans_out' => $tx_id,
                            'm_place' => 'cron ' . $m_id,
                            'm_id' => $m_id,
                            'm_defin' => $m_defin,
                            'm_data' => $m_data,
                        );
                        if ($tx_hash) {
                            $params['txid_out'] = $tx_hash;
                        }
                        set_bid_status('success', $item->id, $params);

                    }
                }
            }
        }
    }
}

new paymerchant_utopia(__FILE__, 'Utopia');
