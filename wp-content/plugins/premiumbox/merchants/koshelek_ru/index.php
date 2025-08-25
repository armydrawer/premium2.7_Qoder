<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Koshelek.ru[:en_US][ru_RU:]Koshelek.ru[:ru_RU]
description: [en_US:]Koshelek merchant[:en_US][ru_RU:]мерчант Koshelek[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_koshelek')) {
    class merchant_koshelek extends Ext_Merchant_Premiumbox {

        function __construct($file, $title) {

            parent::__construct($file, $title, 1);

            add_filter('bcc_keys', array($this, 'set_keys'));
            add_filter('qr_keys', array($this, 'set_keys'));
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

            $m_defin = $this->get_file_data($m_id);

            $options = pn_array_unset($options, array('note', 'check_api', 'enableip', 'resulturl', 'help_resulturl', 'check'));

            $options['merch_line'] = array(
                'view' => 'line',
            );

            $options['network'] = array(
                'view' => 'input',
                'title' => __('Network', 'pn'),
                'default' => is_isset($data, 'network'),
                'name' => 'network',
                'work' => 'input',
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
			
			return 'address';  
		}

        function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {
			global $wpdb, $bids_data;

            $item_id = $bids_data->id;
            $currency_code_give = mb_strtoupper($bids_data->currency_code_give);
            $currency_id_give = $bids_data->currency_id_give;

            $dest_tag = pn_strip_input($bids_data->dest_tag);
            $to_account = pn_strip_input($bids_data->to_account);
            if (!$to_account) {

                $show_error = intval(is_isset($m_data, 'show_error'));

                $network = pn_strip_input(is_isset($m_data, 'network'));
                if (!$network) {
                    $currency_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_give'");
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
                            $network = $currency_code_give;
                        }
                    }
                }

                try {
                    $api = new M_KOSHELEK($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'SECRET_KEY'));

                    $data = array(
                        'Currency' => $currency_code_give,
                        'New' => 'true',
                        'Network' => $network,
                    );
                    $res = $api->create_address($data);
                    if (isset($res['address'])) {
                        $to_account = pn_strip_input(is_isset($res, 'address'));
                        $dest_tag = pn_strip_input(is_isset($res, 'tag'));
                    } 
                } catch (Exception $e) {
                    $this->logs($e->getMessage(), $m_id);
                }

                if ($to_account) {

                    $arr = array();
                    $arr['to_account'] = $to_account;
                    $arr['dest_tag'] = $dest_tag;
                    $bids_data = update_bid_tb_array($item_id, $arr, $bids_data);

					$notify_tags = array();
                    $notify_tags['[bid_id]'] = $item_id;
                    $notify_tags['[address]'] = $to_account;
                    $notify_tags['[sum]'] = $pay_sum;
                    $notify_tags['[dest_tag]'] = $dest_tag;
                    $notify_tags['[currency_code_give]'] = $bids_data->currency_code_give;
                    $notify_tags['[count]'] = $this->confirm_count($m_id, $m_defin, $m_data);

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

                $api = new M_KOSHELEK($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'SECRET_KEY'));
                $history = $api->get_transactions();

                $workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'));
                $workstatus_db = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'), 1);
                $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($workstatus_db) AND m_in = '$m_id' $where");

                foreach ($items as $item) {
                    $id = $item->id;
                    $to_account = pn_strip_input($item->to_account);
                    $dest_tag = pn_strip_input($item->dest_tag);

                    foreach ($history as $h_key => $tx) {
                        $tx_address = pn_strip_input($tx['destination']);
                        $tx_currency = mb_strtoupper($tx['currencyTitle']);
                        $tx_memo = pn_strip_input($tx['destinationTag']);
                        $tx_status = intval($tx['transactionStatus']);
                        $tx_hash = pn_strip_input($tx['cryptoTxId']);
                        $tx_sum = is_sum($tx['amount'], 12);

                        if (!$tx_memo and !$dest_tag or $tx_memo == $dest_tag) {
                            if ($tx_address and $tx_address == $to_account) {

                                $realpay_st = array(2);
                                $coldpay_st = array(0, 1, 5, 7, 8, 9, 10);

                                $data = get_data_merchant_for_id($id, $item);

                                $now_status = '';
                                if (in_array($tx_status, $realpay_st)) {
                                    $now_status = 'realpay';
                                }
                                if (in_array($tx_status, $coldpay_st)) {
                                    $now_status = 'coldpay';
                                }

                                if ($now_status) {

                                    $err = $data['err'];
                                    $status = $data['status'];
                                    $bid_m_id = $data['m_id'];
                                    $bid_m_script = $data['m_script'];

                                    $bid_currency = $data['currency'];

                                    $pay_purse = is_pay_purse('', $m_data, $bid_m_id);

                                    $bid_sum = is_sum($data['pay_sum'], 12);
                                    $bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);

                                    $invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
                                    $invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
                                    $invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));

                                    if (!check_trans_in($bid_m_id, $tx_hash, $id)) {
                                        if (0 == $err and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name) {
                                            if ($bid_currency == $tx_currency or $invalid_ctype > 0) {
                                                if ($tx_sum >= $bid_corr_sum or $invalid_minsum > 0) {

                                                    unset($history[$h_key]);

                                                    $params = array(
                                                        'pay_purse' => $pay_purse,
                                                        'trans_in' => $tx_hash,
                                                        'sum' => $tx_sum,
                                                        'bid_sum' => $bid_sum,
                                                        'bid_status' => $workstatus,
                                                        'bid_corr_sum' => $bid_corr_sum,
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
                                                    set_bid_status($now_status, $id, $params, $data['direction_data']);

                                                    break;

                                                } else {
                                                    $this->logs($id . ' The payment amount is less than the provisions', $m_id);
                                                }
                                            } else {
                                                $this->logs($id . ' In the application the wrong status', $m_id);
                                            }
                                        } else {
                                            $this->logs($id . ' bid error', $m_id);
                                        }
                                    } else {
                                        $this->logs($id . ' Error check trans in!', $m_id);
                                    }
                                }
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

new merchant_koshelek(__FILE__, 'Koshelek.ru');