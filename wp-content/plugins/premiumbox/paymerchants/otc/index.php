<?php
if (!defined('ABSPATH')) exit();

/*
title: [en_US:]OTC[:en_US][ru_RU:]OTC[:ru_RU]
description: [en_US:]OTC automatic payouts[:en_US][ru_RU:]авто выплаты OTC[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) return;

if (!class_exists('paymerchant_otc')) {
    class paymerchant_otc extends Ext_AutoPayut_Premiumbox {

        public $currency_lists = '';
        public $bank_lists = '';

        function __construct($file, $title) {
            parent::__construct($file, $title, 1);

            $ids = $this->get_ids('paymerchants', $this->name);
            foreach ($ids as $id) {
                add_action('premium_merchant_ap_' . $id . '_callback' . hash_url($id, 'ap'), array($this, 'merchant_callback'));
            }

            $this->currency_lists = array('RUB', 'USDT', 'EUR', 'KZT', 'AZN', 'USD', 'UZS', 'KGS', 'INR');
            $this->bank_lists = array(
                /*'0' => array(
                    'title' => 'Киви (карта)',
                    'name' => 'Киви',
                ),*/
                '1' => array(
                    'title' => 'Альфа кешин (карта)',
                    'name' => 'Альфа кешин',
                ),
                '5' => array(
                    'title' => 'Тинькофф (карта/сбп)',
                    'name' => 'Тинькофф',
                ),
                '4' => array(
                    'title' => 'Сбер (карта/сбп)',
                    'name' => 'Сбер',
                ),
                '3' => array(
                    'title' => 'Тинькофф кешин (карта)',
                    'name' => 'Тинькофф кешин',
                ),
                '2' => array(
                    'title' => 'Все банки РФ (карта)',
                    'name' => 'Все банки РФ',
                ),
                '6' => array(
                    'title' => 'KZT (карта)',
                    'name' => 'KZT',
                ),
                '7' => array(
                    'title' => 'EUR (карта)',
                    'name' => 'EUR',
                ),
                '8' => array(
                    'title' => 'AZN (карта)',
                    'name' => 'AZN',
                ),
                '9' => array(
                    'title' => 'USD (карта)',
                    'name' => 'USD',
                ),
                '16' => array(
                    'title' => 'UZS (карта)',
                    'name' => 'UZS',
                ),
                '17' => array(
                    'title' => 'KGS (карта)',
                    'name' => 'KGS',
                ),
                '18' => array(
                    'title' => 'INR (карта)',
                    'name' => 'INR',
                ),

                '10' => array(
                    'title' => 'Банк ВТБ (сбп)',
                    'name' => 'Банк ВТБ',
                ),
                '11' => array(
                    'title' => 'АЛЬФА-БАНК (сбп)',
                    'name' => 'АЛЬФА-БАНК',
                ),
                '12' => array(
                    'title' => 'Райффайзенбанк (сбп)',
                    'name' => 'Райффайзенбанк',
                ),
                '13' => array(
                    'title' => 'Банк ОТКРЫТИЕ (сбп)',
                    'name' => 'Банк ОТКРЫТИЕ',
                ),
                '14' => array(
                    'title' => 'Газпромбанк (сбп)',
                    'name' => 'Газпромбанк',
                ),
                '15' => array(
                    'title' => 'Промсвязьбанк (сбп)',
                    'name' => 'Промсвязьбанк',
                ),
                '19' => array(
                    'title' => 'Озон Банк (сбп)',
                    'name' => 'Озон Банк',
                ),
                '20' => array(
                    'title' => 'НКО ЮМани (сбп)',
                    'name' => 'НКО ЮМани',
                ),
                '21' => array(
                    'title' => 'Яндекс Банк (сбп)',
                    'name' => 'Яндекс Банк',
                ),
                '22' => array(
                    'title' => 'ОТП Банк (сбп)',
                    'name' => 'ОТП Банк',
                ),
                '23' => array(
                    'title' => 'МТС Банк (сбп)',
                    'name' => 'МТС Банк',
                ),
                '24' => array(
                    'title' => 'Совкомбанк (сбп)',
                    'name' => 'Совкомбанк',
                ),
                '25' => array(
                    'title' => 'РНКБ (сбп)',
                    'name' => 'РНКБ',
                ),
                '26' => array(
                    'title' => 'Банк ВБРР (сбп)',
                    'name' => 'Банк ВБРР',
                ),
                '27' => array(
                    'title' => 'Россельхозбанк (сбп)',
                    'name' => 'Россельхозбанк',
                ),
                '28' => array(
                    'title' => 'АК БАРС БАНК (сбп)',
                    'name' => 'АК БАРС БАНК',
                ),
                '29' => array(
                    'title' => 'Банк ЗЕНИТ (сбп)',
                    'name' => 'Банк ЗЕНИТ',
                ),
                '30' => array(
                    'title' => 'Почта Банк (сбп)',
                    'name' => 'Почта Банк',
                ),
                '31' => array(
                    'title' => 'Ингосстрах Банк (сбп)',
                    'name' => 'Ингосстрах Банк',
                ),
                '32' => array(
                    'title' => 'Московский кредитный банк (сбп)',
                    'name' => 'Московский кредитный банк',
                ),
                '33' => array(
                    'title' => 'Вайлдберриз Банк (сбп)',
                    'name' => 'Вайлдберриз Банк',
                ),
                '34' => array(
                    'title' => 'КБ Солидарность (сбп)',
                    'name' => 'КБ Солидарность',
                ),
            );
        }

        function get_map() {

            $map = array(
                'API_KEY' => array(
                    'title' => '[en_US:]API key[:en_US][ru_RU:]API ключ[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
                'SECRET_KEY' => array(
                    'title' => '[en_US:]Secret key[:en_US][ru_RU:]Секретный ключ[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
            );

            return $map;
        }

        function settings_list() {

            $arrs = array();
            $arrs[] = array('API_KEY', 'SECRET_KEY');

            return $arrs;
        }

        function options($options, $data, $m_id, $place) {

            $options = pn_array_unset($options, array('checkpay', 'note'));

            $options['line_error_status'] = array(
                'view' => 'line',
            );

            $list = array();
            $banks = $this->bank_lists;
            foreach ($banks as $bank_id => $bank) {
                $list[$bank_id] = is_isset($bank, 'title');
            }
            natcasesort($list);

            $options['method_id'] = array(
                'view' => 'select',
                'title' => __('Payment method', 'pn'),
                'default' => is_isset($data, 'method_id'),
                'options' => $list,
                'name' => 'method_id',
                'work' => 'int',
            );

            $options['usdtfrom'] = array(
                'view' => 'select',
                'title' => __('From USDT', 'pn'),
                'default' => is_isset($data, 'usdtfrom'),
                'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
                'name' => 'usdtfrom',
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

        function get_reserve_lists($m_id, $m_defin) {

            $currencies = $this->currency_lists;

            $purses = array();

            foreach ($currencies as $currency) {
                $purses[$m_id . '_' . strtolower($currency)] = strtoupper($currency);
            }

            return $purses;
        }

        function update_reserve($code, $m_id, $m_defin) {

            $sum = 0;
            $purse = strtoupper(trim(str_replace($m_id . '_', '', $code)));
            if ($purse) {

                try {

                    $class = new AP_OTC($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'SECRET_KEY'));
                    $res = $class->get_balance();

                    if (isset($res[$purse])) {
                        $sum = is_sum($res[$purse]);
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

            $currency_code_give = strtoupper($item->currency_code_give);
            $currency_code_get = strtoupper($item->currency_code_get);

            $currency_id_give = intval($item->currency_id_give);
            $currency_id_get = intval($item->currency_id_get);

            $account = trim($item->account_get);

            if (!$account) {
                $error[] = __('Wrong client wallet', 'pn');
            }

            $out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);

            $usdtfrom = intval(is_isset($paymerch_data, 'usdtfrom'));
            if ($usdtfrom) {
                $currency_code_get = 'USDT';
            }

            $bank = '';
            $bank_id = '';
            $method_id = intval(is_isset($paymerch_data, 'method_id'));
            $banks = $this->bank_lists;
            if (!isset($banks[$method_id])) {
                $method_id = 0;
            }
            $bank_data = is_isset($banks, $method_id);
            if (isset($bank_data['id'])) {
                $bank_id = $bank_data['id'];
            } elseif (isset($bank_data['name'])) {
                $bank = $bank_data['name'];
            }
            $bank_name = trim(is_isset($unmetas, 'bankname'));
            if (strlen($bank_name) > 0) {
                $bank = $bank_name;
            }

            $class = new AP_OTC($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'SECRET_KEY'));

            $card_data = array();
            $cardExpiration = trim(is_isset($unmetas, 'get_cardexpire')); // for EUR, AZN
            if ($cardExpiration) {
                $card_data['cardExpiration'] = $cardExpiration;
            }
            $cardholder = trim(is_isset($unmetas, 'get_cardholder')); // for EUR, USD
            if ($cardholder) {
                $card_data['cardholder'] = $cardholder;
            }
            $ifsccode = trim(is_isset($unmetas, 'get_ifsccode')); // for INR
            if ($ifsccode) {
                $card_data['ifscCode'] = $ifsccode;
            }

            $notification_url = get_mlink('ap_' . $m_id . '_callback' . hash_url($m_id, 'ap')) . '?order_id=' . $item_id;
            $card_data['callbackURL'] = $notification_url;

            if (0 == count($error)) {
                $result = $this->set_ap_status($item, $test);
                if ($result) {
                    try {
                        $trans_id = $class->send('ap' . $item_id, $currency_code_get, $sum, $account, $bank, $bank_id, $card_data);
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

                if ('admin' == $place) {
                    pn_display_mess(__('Automatic payout is done', 'pn'), __('Automatic payout is done', 'pn'), 'true');
                }
            }
        }

        function merchant_status() {

            $m_id = key_for_url('_callback', 'ap_');
            $m_defin = $this->get_file_data($m_id);
            $m_data = get_paymerch_data($m_id);

            do_action('paymerchant_secure', $this->name, '', $m_id, $m_defin, $m_data);

            $this->this_ap_cron($m_id, $m_defin, $m_data, is_param_get('order_id'));

            echo 'OK';
            exit;
        }

        function cron($m_id, $m_defin, $m_data) {

            $this->this_ap_cron($m_id, $m_defin, $m_data, 0);

        }

        function this_ap_cron($m_id, $m_defin, $m_data, $order_id) {
            global $wpdb;

            $error_status = is_status_name(is_isset($m_data, 'error_status'));
            $order_id = intval($order_id);

            $where = '';
            if ($order_id) {
                $where = " AND id = '$order_id'";
            }

            $class = new AP_OTC($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'SECRET_KEY'));
            $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id' $where");

            foreach ($items as $item) {
                $item_id = $item->id;
                $trans_id = $item->trans_out;
                $order_id = 'ap' . $item_id;
                $orders = $class->get_transactions($order_id);
                if (isset($orders[$order_id])) {
                    $order = $orders[$order_id];
                    if (isset($order['orderStats'], $order['orderStats']['statusName'])) {
                        $check_status = mb_strtolower($order['orderStats']['statusName']);

                        if ('выплачена' == $check_status) {

                            $params = array(
                                'system' => 'system',
                                'bid_status' => array('coldsuccess'),
                                'm_place' => 'cron ' . $m_id,
                                'm_id' => $m_id,
                                'm_defin' => $m_defin,
                                'm_data' => $m_data,
                            );
                            set_bid_status('success', $item->id, $params);

                        } elseif ('отмененная' == $check_status or 'частичная выплата' == $check_status) {

                            $this->reset_cron_status($item, $error_status, $m_id);

                        }
                    }
                }
            }
        }
    }
}

new paymerchant_otc(__FILE__, 'OTC');