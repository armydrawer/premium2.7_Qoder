<?php

if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Quickex[:en_US][ru_RU:]Quickex[:ru_RU]
description: [en_US:]Quickex merchant[:en_US][ru_RU:]мерчант Quickex[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_quickex')) {
    class merchant_quickex extends Ext_Merchant_Premiumbox {
        private array $disable_opts = ['ext_webhook', 'note', 'check_api', 'check'];
        private array $sum_to_pay = [];
        private array $tx_statuses = [
            'realpay' => ['DEPOSIT_REGISTERED', 'FUNDS_WITHDRAWAL_START', 'WITHDRAWAL_COMPLETED'],
            'coldpay' => ['INCOMING_FUNDS_DETECTED'],
        ];

        function __construct($file, $title) {
            parent::__construct($file, $title, !in_array('ext_cron', $this->disable_opts));

            $this->tx_statuses = $this->tx_statuses ? array_replace(...array_map(fn($statuses, $processor) => array_fill_keys($statuses, $processor), $this->tx_statuses, array_keys($this->tx_statuses))) : [];

            if (!in_array('ext_webhook', $this->disable_opts)) {
                foreach ($this->get_ids('merchants', $this->name) as $id) {
                    add_action("premium_merchant_{$id}_webhook" . hash_url($id), [$this, 'webhook']);
                }
            }

            if ($this->sum_to_pay) {
                add_filter('sum_to_pay', [$this, 'sum_to_pay'], 100, 2);
                add_filter('sum_from_pay', [$this, 'sum_to_pay'], 100, 2);
                add_filter('merchant_bid_sum', [$this, 'sum_to_pay'], 100, 2);
            }
        }

        function get_map() {
            return [
                'BASE_URL' => [
                    'title' => '[en_US:]Domain[:en_US][ru_RU:]Домен[:ru_RU]',
                    'view' => 'input',
                    'hidden' => false,
                ],
                'AFFILIATE_ID' => [
                    'title' => '[en_US:]Affiliate ID <span class="bred">*</span>[:en_US][ru_RU:]Партнерский ID <span class="bred">*</span>[:ru_RU]',
                    'view' => 'input',
                    'hidden' => false,
                ],
                'MARKUP' => [
                    'title' => '[en_US:]Markup (%) <span class="bred">*</span>[:en_US][ru_RU:]Наценка (%) <span class="bred">*</span>[:ru_RU]',
                    'view' => 'input',
                    'hidden' => false,
                ],
                /*'PUBLIC_KEY' => [
                    'title' => '[en_US:]Public key <span class="bred">*</span>[:en_US][ru_RU:]Публичный ключ <span class="bred">*</span>[:ru_RU]',
                    'view' => 'input',
                    'hidden' => true,
                ],
                'SECRET_KEY' => [
                    'title' => '[en_US:]Secret key <span class="bred">*</span>[:en_US][ru_RU:]Секретный ключ <span class="bred">*</span>[:ru_RU]',
                    'view' => 'input',
                    'hidden' => true,
                ],*/
            ];
        }

        function settings_list() {
            return [[]];
        }

        function options($options, $data, $id, $place) {
            $m_define = $this->get_file_data($id);
            $m_data = get_merch_data($id);

            $options = pn_array_unset($options, $this->disable_opts);
            if (!in_array($this->merch_type($id, $m_data), ['address', 'coupon'])) $options = pn_array_unset($options, ['pagenote']);
            if (in_array('ext_cron', $this->disable_opts)) $options = pn_array_unset($options, ['cronhash']);
            if (in_array('ext_webhook', $this->disable_opts)) $options = pn_array_unset($options, ['enableip', 'resulturl', 'help_resulturl']);

            $options['ext_line'] = ['view' => 'line'];

            $currencies_give = [0 => __('Config file is not configured', 'pn')];
            $currencies_get = [0 => __('Config file is not configured', 'pn')];
            $text_check_api = [0 => '<strong class="bred">' . mb_strtoupper(__('error', 'pn')) . '</strong>'];

            if (1 == $place && 'success' == $this->settingtext('success', $id)) {
                $currencies_get = $currencies_give = [0 => '-- ' . __('Automatically', 'pn') . ' --'];

                $api = new M_QUICKEX($this->name, $id, $m_define, $m_data);

                $r = $api->instruments();

                if ($r['pd_codes']) {
                    $currencies_get += $r['pd_codes'];
                    $currencies_give += $r['pd_codes'];
                }

                if (200 == $r['status_code']) {
                    $text_check_api[0] = '<strong class="bgreen">' . mb_strtoupper(__('ok', 'pn')) . '</strong>';
                }
            }

            $options['currencies_give'] = [
                'view' => 'select_search',
                'title' => sprintf('%s "%s"', __('Currency name', 'pn'), __('Send', 'pn')),
                'options' => $currencies_give,
                'default' => is_isset($data, 'currencies_give'),
                'name' => 'currencies_give',
                'work' => 'input',
            ];

            $options['currencies_get'] = [
                'view' => 'select_search',
                'title' => sprintf('%s "%s"', __('Currency name', 'pn'), __('Receive', 'pn')),
                'options' => $currencies_get,
                'default' => is_isset($data, 'currencies_get'),
                'name' => 'currencies_get',
                'work' => 'input',
            ];

            $options['work_status'] = ['view' => 'textfield', 'title' => __('Work status', 'pn'), 'default' => implode('<br/>', $text_check_api)];

            $text_add_info = array_filter([
                'webhook_url' => !in_array('ext_webhook', $this->disable_opts) ? '<strong>Webhook URL:</strong> <a href="' . get_mlink("{$id}_webhook" . hash_url($id)) . '" target="_blank">' . get_mlink("{$id}_webhook" . hash_url($id)) . '</a>' : null,
                'cron_url' => !in_array('ext_cron', $this->disable_opts) ? '<strong>' . __('Cron file', 'pn') . ':</strong> <a href="' . get_mlink("{$id}_cron" . chash_url($id)) . '" target="_blank">' . get_mlink("{$id}_cron" . chash_url($id)) . '</a>' : null,
            ]);

            if ($text_add_info) {
                $options['add_info'] = ['view' => 'textfield', 'title' => '', 'default' => implode('<br/>', $text_add_info)];
            }

            return $options;
        }

        function merch_type($m_id, $m_data = false) {
            return 'address';
        }

        function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {

            $type = $this->merch_type($m_id, $m_data);

            return $this->{"init_$type"}($m_id, $pay_sum, $direction, $m_defin, $m_data);
        }

        function init_address($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $bids_data;

            if ($bids_data->to_account) {
                return true;
            }

            // BID DATA
            $unmetas = @unserialize($bids_data->unmetas);
            $dest_tag = trim(is_isset($unmetas, 'get_dest_tag') ?: is_isset($unmetas, 'dest_tag'));
            $account_get = $bids_data->account_get;

            // M DATA
            $c_give_exp = explode(':::', pn_strip_input(is_isset($m_data, 'currencies_give')), 2);
            $c_give_currency = is_isset($c_give_exp, 0);
            $c_give_network = is_isset($c_give_exp, 1);

            $c_get_exp = explode(':::', pn_strip_input(is_isset($m_data, 'currencies_get')), 2);
            $c_get_currency = is_isset($c_get_exp, 0);
            $c_get_network = is_isset($c_get_exp, 1);

            $api = new M_QUICKEX($this->name, $m_id, $m_defin, $m_data);

            if (!$c_give_currency || !$c_give_network || !$c_get_currency || !$c_get_network) {
                $r = $api->instruments()['pd'];

                $currency_id_give = $bids_data->currency_id_give;
                $currency_id_get = $bids_data->currency_id_get;
                $cd = get_currency_data([$currency_id_give, $currency_id_get]);

                if (!$c_give_currency || !$c_give_network) {
                    $xml_value = is_isset($r, is_xml_value(isset($cd[$currency_id_give]) ? $cd[$currency_id_give]->xml_value : mb_strtoupper($bids_data->currency_code_give)));
                    if ($xml_value) {
                        $c_give_currency = is_isset($xml_value, 'currencyTitle');
                        $c_give_network = is_isset($xml_value, 'networkTitle');
                    }
                }

                if (!$c_get_currency || !$c_get_network) {
                    $xml_value = is_isset($r, is_xml_value(isset($cd[$currency_id_get]) ? $cd[$currency_id_get]->xml_value : mb_strtoupper($bids_data->currency_code_get)));
                    if ($xml_value) {
                        $c_get_currency = is_isset($xml_value, 'currencyTitle');
                        $c_get_network = is_isset($xml_value, 'networkTitle');
                    }
                }
            }

            $data = [
                'instrumentFrom' => [
                    'currencyTitle' => $c_give_currency,
                    'networkTitle' => $c_give_network
                ],
                'instrumentTo' => [
                    'currencyTitle' => $c_get_currency,
                    'networkTitle' => $c_get_network
                ],
                'destinationAddress' => $account_get,
                'claimedDepositAmount' => $pay_sum,
            ];
            if ($dest_tag) $data['destinationAddressMemo'] = $dest_tag;

            $r = $api->create($data)['json'];

            if (empty($r['orderId'])) {
                return false;
            }

            $tx = $r;
            $tx_id = !empty($tx['orderId']) ? pn_strip_input($tx['orderId']) : null;
            $tx_to_account = !empty($tx['depositAddress']['depositAddress']) ? pn_strip_input($tx['depositAddress']['depositAddress']) : null;
            $tx_dest_tag = !empty($tx['depositAddress']['depositAddressMemo']) ? pn_strip_input($tx['depositAddress']['depositAddressMemo']) : '';

            $checked_fields = [$tx_id, $tx_to_account];
            if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                return false;
            }

            $update_data = [
                'trans_in' => $tx_id,
                'to_account' => $tx_to_account,
                'dest_tag' => $tx_dest_tag,
            ];
            $bids_data = update_bid_tb_array($bids_data->id, $update_data, $bids_data);

            $notify_tags = [
                '[bid_id]' => $bids_data->id,
                '[address]' => $tx_to_account,
                '[sum]' => $pay_sum,
                '[dest_tag]' => $tx_dest_tag,
                '[currency_code_give]' => $bids_data->currency_code_give,
                '[count]' => $this->confirm_count($m_id, $m_defin, $m_data),
            ];

            $admin_locale = get_admin_lang();
            $now_locale = get_locale();
            set_locale($admin_locale);

            $user_send_data = ['admin_email' => 1];
            apply_filters('premium_send_message', 0, 'generate_merchaddress2', $notify_tags, $user_send_data);

            set_locale($now_locale);

            $user_send_data = ['user_email' => $bids_data->user_email];
            $user_send_data = apply_filters('user_send_data', $user_send_data, 'generate_merchaddress', $bids_data);
            apply_filters('premium_send_message', 0, 'generate_merchaddress', $notify_tags, $user_send_data);

            return !empty($bids_data->to_account);
        }

        function webhook() {
            $m_id = key_for_url('_webhook');
            $m_define = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            $data = pn_json_decode(file_get_contents('php://input')) ?? [];

            do_action('merchant_secure', $this->name, $data, $m_id, $m_define, $m_data);

            wp_send_json_success();
        }

        function cron($m_id, $m_defin, $m_data) {

            $this->payment_check(__FUNCTION__, $m_id, $m_defin, $m_data);

        }

        private function payment_check($place, $m_id, $m_define, $m_data, $tx_info = false) {
            global $wpdb;

            try {
                $tx_id = is_array($tx_info) ? is_isset($tx_info, 'tx_id') : $tx_info;

                $check_statuses = apply_filters('set_bid_status_for_verify', ['realpay']);
                $invalid_check = !in_array('check', $this->disable_opts) ? intval(is_isset($m_data, 'check')) : null;
                $invalid_ctype = !in_array('invalid_ctype', $this->disable_opts) ? intval(is_isset($m_data, 'invalid_ctype')) : null;
                $invalid_minsum = !in_array('invalid_minsum', $this->disable_opts) ? intval(is_isset($m_data, 'invalid_minsum')) : null;
                $invalid_maxsum = !in_array('invalid_maxsum', $this->disable_opts) ? intval(is_isset($m_data, 'invalid_maxsum')) : null;
                $workstatus = _merch_workstatus($m_id, ['new', 'techpay', 'coldpay', 'payed']);

                $where = [
                    $wpdb->prepare("`m_in` = %s", $m_id),
                    "`status` IN ('" . implode("','", $workstatus) . "')",
                    $tx_id ? $wpdb->prepare("`trans_in` = %s", $tx_id) : "`trans_in` <> '0'",
                ];

                $bids = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}exchange_bids WHERE " . implode(' AND ', $where));

                $api = new M_QUICKEX($this->name, $m_id, $m_define, $m_data);
                $history = (empty($bids) || $tx_id) ? null : $api->get_payments()['pd'];

                foreach ($bids as $bid) {
                    $bid_id = $bid->id;
                    $bid_tx_id = $bid->trans_in;
                    $bid_account_get = $bid->account_get;

                    $tx = is_array($tx_info) ? $tx_info : ($history[$bid_tx_id] ?? null);

                    if (empty($tx['none'])) {
                        $tx = $api->get_payment($bid_tx_id, $bid_account_get)['pd'];
                    }

                    $tx_id = !empty($tx['orderId']) ? pn_strip_input($tx['orderId']) : null;
                    $tx_deposits = !empty($tx['deposits']) ? $tx['deposits'] : [];
                    $tx_status = !empty($tx['orderEvents'][0]['kind']) ? mb_strtoupper($tx['orderEvents'][0]['kind']) : null;
                    $tx_amount = $tx_deposits ? array_sum(array_map(fn($v) => $this->sum_to_pay($v['amount'], $m_id), $tx_deposits)) : null;
                    $tx_currency = $tx_deposits ? mb_strtoupper($tx_deposits[0]['instrument']['currencyTitle']) : null;
                    $tx_hash = $tx_deposits ? pn_strip_input($tx_deposits[0]['txId']) : '';
                    $tx_purse_from = '';
                    $tx_purse = '';

                    $checked_fields = [$tx_id, $tx_status];
                    if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                        continue;
                    }

                    $new_status = $this->tx_statuses[$tx_status] ?? '';

                    if (in_array($new_status, ['realpay', 'coldpay'])) {

                        $data = get_data_merchant_for_id($bid_id, $bid);
                        $err = $data['err'];
                        $bid_m_id = $data['m_id'];
                        $bid_m_script = $data['m_script'];
                        $bid_currency = $data['currency'];
                        $bid_sum = $data['pay_sum'];
                        $bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $bid_m_id);
                        $bid_account = apply_filters('pay_purse_merchant', $bid->account_give);
                        $tx_purse_from = $tx_purse_from ? is_pay_purse($tx_purse_from, $m_data, $bid_m_id) : $tx_purse_from;

                        if (check_trans_in($bid_m_id, $tx_id, $bid_id)) {
                            $this->logs("{$bid_id} | Error check trans in!", $m_id);
                            continue;
                        }

                        if ($err || !$bid_m_id || $bid_m_id != $m_id || !$bid_m_script || $bid_m_script != $this->name) {
                            $this->logs("{$bid_id} | Bid error", $m_id);
                            continue;
                        }

                        if (in_array($new_status, $check_statuses) && 0 === $invalid_check && $bid_account != $tx_purse_from) {
                            $this->logs("{$bid_id} | Another sender purse: {$bid_account} != {$tx_purse_from}", $m_id);
                            continue;
                        }

                        if (in_array($new_status, $check_statuses) && 0 === $invalid_ctype && $bid_currency != $tx_currency) {
                            $this->logs("{$bid_id} | Wrong type of currency: {$bid_currency} != {$tx_currency}", $m_id);
                            continue;
                        }

                        if (in_array($new_status, $check_statuses) && 0 === $invalid_minsum && $tx_amount < $bid_corr_sum) {
                            $this->logs("{$bid_id} | The payment amount is less than the provisions: {$tx_amount} < {$bid_corr_sum}", $m_id);
                            continue;
                        }

                        if (in_array($new_status, $check_statuses) && 0 === $invalid_maxsum && $tx_amount > $bid_sum) {
                            $this->logs("{$bid_id} | The payment amount is greater than the provisions: {$tx_amount} > {$bid_sum}", $m_id);
                            continue;
                        }

                        $params = [
                            'sum' => 'coldpay' == $new_status ? null : $tx_amount,
                            'bid_sum' => $bid_sum,
                            'bid_corr_sum' => $bid_corr_sum,
                            'pay_purse' => $tx_purse_from,
                            'to_account' => $tx_purse,
                            'trans_in' => $tx_id,
                            'txid_in' => $tx_hash,
                            'currency' => $tx_currency,
                            'bid_currency' => $bid_currency,
                            'invalid_ctype' => $invalid_ctype,
                            'invalid_minsum' => $invalid_minsum,
                            'invalid_maxsum' => $invalid_maxsum,
                            'invalid_check' => $invalid_check,
                            'bid_status' => $workstatus,
                            'm_place' => "{$m_id}_{$place}",
                            'm_id' => $m_id,
                            'm_data' => $m_data,
                            'm_defin' => $m_define,
                        ];
                        set_bid_status($new_status, $bid_id, $params, $data['direction_data']);

                    } elseif ($new_status) {

                        $params = [
                            'm_place' => "{$m_id}_{$place}",
                            'm_id' => $m_id,
                            'm_data' => $m_data,
                            'm_defin' => $m_define,
                        ];
                        set_bid_status($new_status, $bid_id, $params);

                    }
                }
            } catch (Exception $e) {
                $this->logs(pn_json_encode(['line' => $e->getLine(), 'file' => realpath($e->getFile()), 'message' => $e->getMessage()]), $m_id);

                $show_error = intval(is_isset($m_data, 'show_error'));
                if ($show_error && current_user_can('administrator')) {
                    die($e->getMessage());
                }
            }
        }

        /* OTHER SETTINGS */

        function sum_to_pay($sum, $m_id) {

            if (!$m_id || get_mscript($m_id) !== $this->name) {
                return $sum;
            }

            return (float)is_sum($sum, ...$this->sum_to_pay);
        }

        function _add_field($options, $data, $name, $help = false) {

            $options["add_{$name}"] = [
                'view' => 'textarea',
                'title' => __('Add new', 'pn'),
                'default' => is_isset($data, "add_{$name}"),
                'name' => "add_{$name}",
                'rows' => 5,
                'work' => 'text',
            ];

            if ($help) {
                $options["add_{$name}_help"] = ['view' => 'help', 'title' => __('Example', 'pn'), 'default' => implode('<br/>', $help)];
            }

            return $options;
        }

    }
}

new merchant_quickex(__FILE__, 'Quickex');
