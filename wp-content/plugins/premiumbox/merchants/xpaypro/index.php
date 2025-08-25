<?php 
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]XPayPro[:en_US][ru_RU:]XPayPro[:ru_RU]
description: [en_US:]Merchant XPayPro[:en_US][ru_RU:]Мерчант XPayPro[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_xpaypro')) {
    class merchant_xpaypro extends Ext_Merchant_Premiumbox {

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
                'API_KEY' => array(
                    'title' => '[en_US:]API key[:en_US][ru_RU:]API ключ[:ru_RU]',
                    'view' => 'input',
                    'hidden' => 1,
                ),
                'host_type' => array(
                    'title' => '[en_US:]Host[:en_US][ru_RU:]Host[:ru_RU]',
                    'options' => array(0 => 'api.xpaypro.dev', 1 => 'api.test.xpaypro.dev'),
                    'view' => 'select',
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
			
            $m_defin = $this->get_file_data($m_id);

            $options = pn_array_unset($options, array('pagenote', 'note', 'check_api', 'check'));

            $options['merch_line'] = array(
                'view' => 'line',
            );

            $options['payment_method'] = array(
                'view' => 'select',
                'title' => __('Payment method', 'pn'),
                'options' => array(
                    'BANK_CARD' => 'Bank card',
                    'SBP' => 'SBP',
                ),
                'default' => is_isset($data, 'payment_method'),
                'name' => 'payment_method',
                'work' => 'input',
            );

            $options['bank_name'] = array(
                'view' => 'select',
                'title' => __('Bank', 'pn'),
                'options' => array(
                    'BANK_ANY' => __('Random', 'pn'),
                    'SBERBANK' => 'Sberbank',
                    'TINKOFF' => 'Tinkoff',
                    'ROSBANK' => 'Rosbank',
                    'RAIFFEISENBANK' => 'Raiffeisenbank',
                ),
                'default' => is_isset($data, 'bank_name'),
                'name' => 'bank_name',
                'work' => 'input',
            );

            $text = '
            <div><strong>Status URL:</strong> <a href="' . get_mlink($m_id . '_status' . hash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_status' . hash_url($m_id)) . '</a></div>
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

                $payment_method = pn_strip_input(is_isset($m_data, 'payment_method'));
                $bank_name = pn_strip_input(is_isset($m_data, 'bank_name'));

                $arr = array();

                $api = new M_XPAYPRO($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'host_type'));

				$merchant_client_id = is_isset(array_values(array_filter(array(
					'user_id' => trim(is_isset($bids_data, 'user_id')),
					'user_email' => trim(is_isset($bids_data, 'user_email')),
					'user_ip' => trim(is_isset($bids_data, 'user_ip')),
				))), 0);

                $data = array(
                    'fiat_currency' => $currency_code_give,
                    'fiat_amount' => $pay_sum,
                    'crypto_currency' => 'USDT',
                    'payment_method' => $payment_method,
                    'bank_name' => $bank_name,
                    'merchant_tx_id' => $bids_data->id,
                    'merchant_client_id' => $merchant_client_id,
                );
                $res = $api->invoice($data);

                $trans_in = '';
                $to_account = '';
                $dest_tag = '';

                if (isset($res['tx']['tx_id'], $res['tx']['payment_requisite'])) {
                    $trans_in = pn_strip_input($res['tx']['tx_id']);
                    $to_account = pn_strip_input($res['tx']['payment_requisite']);

					$payment_requisite_full_name = '';
					if (isset($res['tx']['payment_requisite_full_name']) and $res['tx']['payment_requisite_full_name']) {
						$payment_requisite_full_name = pn_strip_input($res['tx']['payment_requisite_full_name']);
					}

					$payment_system = '';
					if (isset($res['tx']['payment_system']) and $res['tx']['payment_system']) {
						$payment_system = pn_strip_input($res['tx']['payment_system']);
					}

					$dest_tag_arr = array_values(array_filter(array($payment_requisite_full_name, $payment_system)));

					$dest_tag = '';
					if (1 == count($dest_tag_arr)) {
						$dest_tag = $dest_tag_arr[0];
					} elseif(2 == count($dest_tag_arr)) {
						$dest_tag = $dest_tag_arr[0] . ' (' . $dest_tag_arr[1] . ')';
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

                    $api = new M_XPAYPRO($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'host_type'));
                    $res = $api->get_transaction($bids_data->trans_in);

                    if (isset($res['tx']['tx_id'])) {
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

            $callback = file_get_contents('php://input');
            $post = @json_decode($callback, true);

            do_action('merchant_secure', $this->name, $post, $m_id, $m_defin, $m_data);

            if (isset($post['tx']['tx_id'])) {
                $this->merch_cron($m_id, $m_defin, $m_data, $post['tx']['tx_id']);
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

                $api = new M_XPAYPRO($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'host_type'));
                $workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'));
                $workstatus_db = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'), 1);
                $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($workstatus_db) AND m_in = '$m_id' AND trans_in <> '0' $where");

                foreach ($items as $item) {
                    $item_id = $item->id;
                    $trans_in = $item->trans_in;

                    $res = $api->get_transaction($trans_in);
                    $tx = is_isset($res, 'tx');

                    if ($trans_in and isset($tx['tx_id'])) {
                        $tx_id = pn_strip_input($tx['tx_id']);
                        $tx_status = mb_strtoupper($tx['tx_status']);
                        $tx_currency = mb_strtoupper($tx['in_currency']);
                        $tx_sum = is_sum($tx['in_amount_confirmed'], 2);

                        if (in_array($tx_status, array('TX_SUCCESS', 'TX_RECALCULATED'))) {
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

new merchant_xpaypro(__FILE__, 'XPayPro');