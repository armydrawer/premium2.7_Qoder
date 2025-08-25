<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Koshelek.ru[:en_US][ru_RU:]Koshelek.ru[:ru_RU]
description: [en_US:]Koshelek automatic payouts[:en_US][ru_RU:]авто выплаты Koshelek[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }


if (!class_exists('paymerchant_koshelek')) {
    class paymerchant_koshelek extends Ext_AutoPayut_Premiumbox {

        function __construct($file, $title) {
			
            parent::__construct($file, $title, 1);
			
        }

        function get_map() {
			
            $map = array(
                'API_KEY' => array(
                    'title' => '[en_US:]Public Key[:en_US][ru_RU:]Публичный ключ[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
                'SECRET_KEY' => array(
                    'title' => '[en_US:]Secret Key[:en_US][ru_RU:]Секретный ключ[:ru_RU]',
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
            global $premiumbox;

            $m_defin = $this->get_file_data($m_id);

            $options = pn_array_unset($options, array('note', 'checkpay', 'enableip', 'resulturl'));

            $options['line_error_status'] = array(
                'view' => 'line',
            );

            if (1 == $place and is_isset($m_defin, 'API_KEY') and is_isset($m_defin, 'SECRET_KEY')) {
                $api = new AP_KOSHELEK($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'SECRET_KEY'));

                $r = $api->get_balance();
                if (isset($r['userAccountList'][0]['balanceList'])) {
                    $curr = array();

                    foreach ($r['userAccountList'][0]['balanceList'] as $val) {
                        $curr[] = pn_strip_input($val['currencyTitle']);
                    }
                    sort($curr);
                    $premiumbox->update_option('ap_' . $this->name, 'balance_currencies', $curr);
                }

            }

            $options['network'] = array(
                'view' => 'input',
                'title' => __('Network', 'pn'),
                'default' => is_isset($data, 'network'),
                'name' => 'network',
                'work' => 'input',
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
            global $premiumbox;

            $currencies = $premiumbox->get_option('ap_' . $this->name, 'balance_currencies');
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
                    $api = new AP_KOSHELEK($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'SECRET_KEY'));

                    $r = $api->get_balance();
                    if (isset($r['userAccountList'][0]['balanceList'])) {
                        foreach ($r['userAccountList'][0]['balanceList'] as $val) {
                            if ($purse == $val['currencyTitle']) {
                                $sum = $val['availableBalance'];
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
			
            $item_id = $item->id;
            $trans_id = 0;

            $account = $item->account_get;
            $currency_code_get = mb_strtoupper($item->currency_code_get);
            $currency_id_get = intval($item->currency_id_get);
            $out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data));
            $dest_tag = trim(is_isset($unmetas, 'dest_tag'));

            $network = pn_strip_input(is_isset($paymerch_data, 'network'));

            if (!$account) {
                $error[] = __('Wrong client wallet', 'pn');
            }

            if (!$network) {
                global $wpdb;
				
                $currency_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_get'");
                if (isset($currency_data->id)) {
                    $xml_value = mb_strtoupper(is_xml_value($currency_data->xml_value));

                    if ('USDTPOLYGON' == $xml_value) {
                        $network = 'MATIC';
                    } elseif ('ETHBEP20' == $xml_value or 'USDTBEP20' == $xml_value or 'USDCBEP20' == $xml_value or 'SHIBBEP20' == $xml_value or 'BNBBEP20' == $xml_value) {
                        $network = 'BSC';
                    } elseif ('USDTERC20' == $xml_value or 'USDCERC20' == $xml_value or 'SHIBERC20' == $xml_value or 'TUSDERC20' == $xml_value) {
                        $network = 'ETH';
                    } elseif ('USDTSOL' == $xml_value or 'USDCSOL' == $xml_value) {
                        $network = 'SOL';
                    } elseif ('USDTTRC20' == $xml_value or 'USDCTRC20' == $xml_value) {
                        $network = 'TRX';
                    } else {
                        $network = $currency_code_get;
                    }
                }
            }

            if (0 == count($error)) {
                $result = $this->set_ap_status($item, $test);
                if ($result) {
                    try {
                        $api = new AP_KOSHELEK($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'SECRET_KEY'));

                        $data = array(
                            'Currency' => $currency_code_get,
                            'Amount' => $sum,
                            'Address' => $account,
                            'Network' => $network,
                        );

                        if ($dest_tag) {
                            $data['Tag'] = $dest_tag;
                        }

                        $trans_id = $api->send($data);
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
                    pn_display_mess(__('Payment is successfully created. Waiting for confirmation.', 'pn'), __('Payment is successfully created. Waiting for confirmation.', 'pn'), 'true');
                }
				
            }
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

            $api = new AP_KOSHELEK($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'SECRET_KEY'));
            $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id' $where");

            $res = $api->get_transactions();

            foreach ($items as $item) {
                $item_id = $item->id;
                $trans_id = $item->trans_out;

                $tx = is_isset($res, $trans_id);

                if (isset($tx['transactionStatus'])) {
                    $tx_status = intval($tx['transactionStatus']);
                    $tx_hash = pn_strip_input(is_isset($tx,'cryptoTxId'));

                    if (2 == $tx_status) {
						
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
						
                    } elseif (in_array($tx_status, array(3, 4))) {
						
                        $this->reset_cron_status($item, $error_status, $m_id);
						
                    }
                }
            }
        }
    }
}

new paymerchant_koshelek(__FILE__, 'Koshelek.ru');