<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]AlfaBit Crypto[:en_US][ru_RU:]AlfaBit Crypto[:ru_RU]
description: [en_US:]AlfaBit Crypto merchant[:en_US][ru_RU:]мерчант AlfaBit Crypto[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_alfabit_crypto')) {
    class merchant_alfabit_crypto extends Ext_Merchant_Premiumbox {

        function __construct($file, $title) {

            parent::__construct($file, $title, 1);

            $ids = $this->get_ids('merchants', $this->name);
            foreach ($ids as $id) {
                add_action('premium_merchant_' . $id . '_status' . hash_url($id), array($this, 'merchant_status'));
            }

            add_filter('bcc_keys', array($this, 'set_keys'));
            add_filter('qr_keys', array($this, 'set_keys'));
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

            $m_defin = $this->get_file_data($m_id);

            $options = pn_array_unset($options, array('check_api', 'check'));

            $options['merch_line'] = array(
                'view' => 'line',
            );

            $currencies = array();
            $currencies[0] = __('Config file is not configured', 'pn');

            $assets = array();
            $assets[0] = __('Config file is not configured', 'pn');
            $assets_help = array();
			
            if (1 == $place and is_isset($m_defin, 'API_KEY')) {
                $api = new M_ALFABIT($this->name, $m_id, is_isset($m_defin, 'API_KEY'));

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
                    }
                }

                $r = $api->assets_exchangerate();

                if (isset($r['data']) and is_array($r['data'])) {
                    $tmp = array();
                    $assets[0] = '-- ' . __('No', 'pn') . ' --';

                    $r['data'] = array_filter($r['data'], function ($item) {
                        return 'COIN' == $item['fromCurrencyType'] && 'COIN' == $item['toCurrencyType'] && !$item['isTurnRate'];
                    });

                    foreach ($r['data'] as $item) {
                        $fromAssetCode = pn_strip_input($item['fromAssetCode']);
                        $toAssetCode = pn_strip_input($item['toAssetCode']);

                        $assets[$toAssetCode] = $toAssetCode;

                        if (!isset($tmp[$toAssetCode])) $tmp[$toAssetCode] = array();

                        $tmp[$toAssetCode][] = $fromAssetCode;
                    }

                    ksort($assets);
                    ksort($tmp);

                    foreach ($tmp as &$values) {
                        sort($values);
                    }

                    foreach ($tmp as $k => $item) {
                        $assets_help[] = implode(', ', $item) . ' -> ' . $k;
                    }

                }
            }

            $options['currency'] = array(
                'view' => 'select',
                'title' => __('Currency name', 'pn'),
                'options' => $currencies,
                'default' => is_isset($data, 'currency'),
                'name' => 'currency',
                'work' => 'input',
            );

            $options['asset'] = array(
                'view' => 'select',
                'title' => __('Convert to', 'pn'),
                'options' => $assets,
                'default' => is_isset($data, 'asset'),
                'name' => 'asset',
                'work' => 'input',
            );

            if (count($assets_help)) {
                $options['asset_help'] = array(
                    'view' => 'help',
                    'title' => __('Help', 'pn'),
                    'default' => implode('<br />', $assets_help),
                );
            }

            $text = '
            <div><strong>Callback URL:</strong> <a href="' . get_mlink($m_id . '_status' . hash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_status' . hash_url($m_id)) . '</a></div>
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
            $dest_tag = pn_strip_input($bids_data->dest_tag);
            $to_account = pn_strip_input($bids_data->to_account);
            $currency_id_give = $bids_data->currency_id_give;
            $trans_in = '';

            if (!$to_account) {
				
                $currency = pn_strip_input(is_isset($m_data, 'currency'));
                $asset = pn_strip_input(is_isset($m_data, 'asset'));
                $text_pay = get_text_pay($m_id, $bids_data, $pay_sum);
                $text_pay = trim(pn_maxf($text_pay, 300));

                if (!$currency) {
                    $currency_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_give'");
                    if (isset($currency_data->id)) {
                        $currency = mb_strtoupper(is_xml_value($currency_data->xml_value));
                    }
                }

                try {
                    $api = new M_ALFABIT($this->name, $m_id, is_isset($m_defin, 'API_KEY'));

                    $data = array(
                        'isBayerPaysService' => false,
                        'isAwaitRequisites' => true,
                        'currencyInCode' => $currency,
                        'comment' => $text_pay,
                        'publicComment' => $text_pay,
                        'callbackUrl' => get_mlink($m_id . '_status' . hash_url($m_id)),
                        'redirectUrl' => get_bids_url($bids_data->hashed),
                    );

                    if ($asset) {
                        $data['invoiceAssetCode'] = $asset;
                    }

                    $r = $api->invoice_wo_amount($data);
                    if (isset($r['data']['uid'], $r['data']['requisites'])) {
                        $to_account = pn_strip_input($r['data']['requisites']);
                        $trans_in = pn_strip_input($r['data']['uid']);

                        if (isset($r['data']['requisitesMemoTag']) and $r['data']['requisitesMemoTag']) {
                            $dest_tag = pn_strip_input($r['data']['requisitesMemoTag']);
                        }
                    } 
                } catch (Exception $e) {
                    $this->logs($e->getMessage(), $m_id);
                }

                if ($to_account) {

                    $arr = array();
                    $arr['to_account'] = $to_account;
                    $arr['dest_tag'] = $dest_tag;
                    $arr['trans_in'] = $trans_in;
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

        function merchant_status() {

            $m_id = key_for_url('_status');
            $m_defin = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            $post = pn_json_decode(file_get_contents('php://input'));

            do_action('merchant_secure', $this->name, $post, $m_id, $m_defin, $m_data);

            if (isset($post['uid'])) {
                $this->merch_cron($m_id, $m_defin, $m_data, $post['uid']);
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

                $api = new M_ALFABIT($this->name, $m_id, is_isset($m_defin, 'API_KEY'));
                $workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'));
                $workstatus_db = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay', 'payed'), 1);
                $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($workstatus_db) AND m_in = '$m_id' AND trans_in <> '0' $where");

                $r_assets = $api->assets_currencies();
                $r_assets = is_isset($r_assets, 'data');
                if (!$r_assets) $r_assets = array();

                foreach ($items as $item) {
                    $item_id = $item->id;
                    $trans_in = pn_strip_input($item->trans_in);
                    $to_account = pn_strip_input($item->to_account);
                    $dest_tag = pn_strip_input($item->dest_tag);

                    $res = $api->order($trans_in);
                    $tx = is_isset($res, 'data');

                    if (!$trans_in or !$tx) {
                        continue;
                    }

                    $tx_status = mb_strtoupper($tx['status']);
                    $tx_memo = pn_strip_input(is_isset($tx, 'requisitesMemoTag'));
                    $tx_sum = is_sum($tx['amountInFact'], 12);
                    $tx_hash = pn_strip_input($tx['txId']);
                    $tx_address = pn_strip_input($tx['requisites']);
                    $tx_is_done = intval($tx['isDone']);
                    $tx_publicCode = pn_strip_input($tx['currencyInCode']);
                    $tx_currency = '';

                    if (!$tx_is_done) {
                        continue;
                    }

                    if (($tx_memo or $dest_tag) and ($tx_memo != $dest_tag)) {
                        continue;
                    }

                    if (!$tx_address or $tx_address != $to_account) {
                        continue;
                    }

                    foreach ($r_assets as $asset) {
                        if ($asset['publicCode'] === $tx_publicCode) {
                            $tx_currency = mb_strtoupper($asset['assetCode']);
                            break;
                        }
                    }

                    if ('SUCCESS' == $tx_status) {
                        $data = get_data_merchant_for_id($item_id, $item);

                        $err = $data['err'];
                        $status = $data['status'];
                        $bid_m_id = $data['m_id'];
                        $bid_m_script = $data['m_script'];
                        $bid_currency = $data['currency'];
                        $bid_sum = is_sum($data['pay_sum'], 12);
                        $bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);

                        $invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
                        $invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
                        $invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));

                        if (!check_trans_in($bid_m_id, $tx_hash, $item_id)) {
                            if (0 == $err and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name) {
                                if ($bid_currency == $tx_currency or $invalid_ctype > 0) {
                                    if ($tx_sum >= $bid_corr_sum or $invalid_minsum > 0) {

                                        $params = array(
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

new merchant_alfabit_crypto(__FILE__, 'AlfaBit Crypto');