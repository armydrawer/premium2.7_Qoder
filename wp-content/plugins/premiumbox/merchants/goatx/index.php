<?php

if (!defined('ABSPATH')) exit();

/*
title: [en_US:]GoatX[:en_US][ru_RU:]GoatX[:ru_RU]
description: [en_US:]GoatX merchant[:en_US][ru_RU:]мерчант GoatX[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_goatx')) {
    class merchant_goatx extends Ext_Merchant_Premiumbox {
        private array $m_types;
        private array $disable_opts = ['ext_success_url', 'ext_fail_url', 'note', 'check_api', 'check'];
        private array $sum_format = [false, 2, 'down'];
        private array $tx_statuses = [
            'realpay' => ['CONFIRMED'],
        ];

        function __construct($file, $title) {
            parent::__construct($file, $title, !in_array('ext_cron_url', $this->disable_opts));

            $this->m_types = [
                'mypaid' => 'Requisites'
            ];
            $this->tx_statuses = $this->tx_statuses ? array_replace(...array_map(fn($statuses, $processor) => array_fill_keys($statuses, $processor), $this->tx_statuses, array_keys($this->tx_statuses))) : [];

            if ($enabled_url = array_diff(['ext_webhook_url', 'ext_success_url', 'ext_fail_url'], $this->disable_opts)) {
                foreach ($this->get_ids('merchants', $this->name) as $id) {
                    if (in_array('ext_webhook_url', $enabled_url)) add_action("premium_merchant_{$id}_webhook" . hash_url($id), [$this, '_webhook_url']);
                    if (in_array('ext_success_url', $enabled_url)) add_action("premium_merchant_{$id}_success", [$this, '_success_url']);
                    if (in_array('ext_fail_url', $enabled_url)) add_action("premium_merchant_{$id}_fail", [$this, '_fail_url']);
                }
            }

            if ($this->sum_format) {
                add_filter('sum_to_pay', [$this, '_sum_format'], 100, 2);
                add_filter('sum_from_pay', [$this, '_sum_format'], 100, 2);
                add_filter('merchant_bid_sum', [$this, '_sum_format'], 100, 2);
            }

            add_filter('change_bid_status', [$this, '_change_bid_status'], 2500);
        }

        function get_map() {
            return [
                'BASE_URL' => [
                    'title' => '[en_US:]Domain[:en_US][ru_RU:]Домен[:ru_RU]',
                    'view' => 'input',
                    'hidden' => false,
                ],
                'LOGIN' => [
                    'title' => '[en_US:]Login <span class="bred">*</span>[:en_US][ru_RU:]Логин <span class="bred">*</span>[:ru_RU]',
                    'view' => 'input',
                    'hidden' => true,
                ],
                'API_KEY' => [
                    'title' => '[en_US:]API key <span class="bred">*</span>[:en_US][ru_RU:]API ключ <span class="bred">*</span>[:ru_RU]',
                    'view' => 'input',
                    'hidden' => true,
                ],
                'CID' => [
                    'title' => '[en_US:]Contract ID <span class="bred">*</span>[:en_US][ru_RU:]ID контракта <span class="bred">*</span>[:ru_RU]',
                    'view' => 'input',
                    'hidden' => true,
                ],
            ];
        }

        function settings_list() {
            return [['LOGIN', 'API_KEY', 'CID']];
        }

        function options($options, $data, $id, $place) {
            global $wpdb;

            $m_define = $this->get_file_data($id);
            $m_data = get_merch_data($id);

            $options = pn_array_unset($options, $this->disable_opts);
            if (!in_array($this->merch_type($id, $m_data), ['address', 'coupon'])) $options = pn_array_unset($options, ['pagenote']);
            if (in_array('ext_cron_url', $this->disable_opts)) $options = pn_array_unset($options, ['cronhash']);
            if (in_array('ext_webhook_url', $this->disable_opts)) $options = pn_array_unset($options, ['enableip', 'resulturl', 'help_resulturl']);

            $options['ext_line'] = ['view' => 'line'];

            if (count($this->m_types) > 1) {
                $merch_type = is_isset($data, 'merch_type') ?: $this->merch_type($id, $m_data);
                $options['merch_type'] = [
                    'view' => 'select',
                    'title' => __('Merchant type', 'pn') . ' <span class="bred">*</span>',
                    'options' => $this->m_types,
                    'default' => $merch_type,
                    'name' => 'merch_type',
                    'work' => 'input',
                ];

                if (1 == $place) {
                    $bids_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}exchange_bids WHERE m_in = %s", $id));
                    if ($bids_count) {
                        $options['merch_type']['name'] .= '_disabled';
                        $options['merch_type']['atts'] = ['disabled' => 'disabled'];
                        $options['merch_type_hidden'] = ['view' => 'hidden_input', 'name' => 'merch_type', 'default' => $merch_type];
                    }
                }
            }

            $work_status = sprintf('<strong class="bred">%s</strong>', __('Config file is not configured', 'pn'));

            ////////////////////////////////////////

            if (1 == $place && 'success' == $this->settingtext('success', $id)) {
                $work_status = sprintf('<strong class="bred">%s</strong>', mb_strtoupper(__('error', 'pn')));

                $api = new M_GOATX($this->name, $id, $m_define, $m_data);

                $r = $api->get_tx('test');

                if (404 == $r['status_code']) {
                    $work_status = sprintf('<strong class="bgreen">%s</strong>', mb_strtoupper(__('ok', 'pn')));
                }
            }

            $options['payment_method'] = [
                'view' => 'select_search',
                'title' => __('Payment method', 'pn'),
                'options' => [
                    0 => __('Card', 'pn'),
                    'sbp' => __('Phone', 'pn'),
                ],
                'default' => is_isset($data, 'payment_method'),
                'name' => 'payment_method',
                'work' => 'input',
            ];

            ////////////////////////////////////////

            $options['work_status'] = ['view' => 'textfield', 'title' => __('Work status', 'pn'), 'default' => $work_status];

            $text_add_info = array_filter([
                'cron_url' => !in_array('ext_cron_url', $this->disable_opts) ? sprintf('<strong>%s:</strong> <a href="%s" target="_blank">%2$s</a>', __('Cron file', 'pn'), get_mlink("{$id}_cron" . chash_url($id))) : null,
                'webhook_url' => !in_array('ext_webhook_url', $this->disable_opts) ? sprintf('<strong>%s:</strong> <a href="%s" target="_blank">%2$s</a>', 'Webhook URL', get_mlink("{$id}_webhook" . hash_url($id))) : null,
                'success_url' => !in_array('ext_success_url', $this->disable_opts) ? sprintf('<strong>%s:</strong> <a href="%s" target="_blank">%2$s</a>', 'Success URL', get_mlink("{$id}_success")) : null,
                'fail_url' => !in_array('ext_fail_url', $this->disable_opts) ? sprintf('<strong>%s:</strong> <a href="%s" target="_blank">%2$s</a>', 'Fail URL', get_mlink("{$id}_fail")) : null,
            ]);

            if ($text_add_info) {
                $options['add_info'] = ['view' => 'textfield', 'title' => '', 'default' => implode('<br/>', $text_add_info)];
            }

            return $options;
        }

        function merch_type($m_id, $m_data = false) {

            $m_data = $m_data ?: get_merch_data($m_id);
            $merch_type = is_isset($m_data, 'merch_type');

            return isset($this->m_types[$merch_type]) ? $merch_type : array_key_first($this->m_types);
        }

        function init($m_id, $pay_sum, $direction, $m_defin, $m_data) {

            $type = $this->merch_type($m_id, $m_data);

            return $this->{"init_{$type}"}($m_id, $pay_sum, $direction, $m_defin, $m_data);
        }

        private function init_mypaid($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $bids_data, $premiumbox;

            if ($bids_data->to_account) {
                return true;
            }

            // BID DATA
            //

            // M DATA
            $pm = is_isset($m_data, 'payment_method');

            $api = new M_GOATX($this->name, $m_id, $m_defin, $m_data);

            $data = [
                'invid' => wp_generate_uuid4(),
                'sum' => $pay_sum,
                'way' => $pm ?: 'card',
                'client_id' => $bids_data->user_id,
            ];

            $tx = $api->create_tx($data)['pd'];

            $tx_id = pn_strip_input($tx['invid'] ?? '' ?: '');
            $tx_method = mb_strtoupper($tx['way'] ?? '' ?: '');
            $tx_to_account = pn_strip_input(($tx['requisite']['phone_number'] ?? '' ?: '') ?: $tx['requisite']['card_number'] ?? '' ?: '');
            $tx_to_account = pn_strip_input(('SBP' == $tx_method ? '+' : '') . preg_replace('/\D/', '', $tx_to_account));

            $checked_fields = [$tx_id, $tx_to_account];
            if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                return false;
            }

            $dt_data = array_filter([
                'cardholder' => pn_strip_input($tx['requisite']['fio'] ?? '' ?: ''),
                'bank_name' => pn_strip_input(($tx['cd']['bank']['name'] ?? '' ?: '') ?: $tx['requisite']['bank'] ?? '' ?: ''),
                'is_sbp' => pn_strip_input('SBP' == $tx_method ? $tx_method : ''),
            ]);
            $tx_dest_tag = pn_strip_input($dt_data ? array_shift($dt_data) . ($dt_data ? ' (' . implode(', ', $dt_data) . ')' : '') : '');

            if ($tx_to_account) {
                $update_data = [
                    'trans_in' => $tx_id,
                    'to_account' => $tx_to_account,
                    'dest_tag' => $tx_dest_tag,
                ];
                $bids_data = update_bid_tb_array($bids_data->id, $update_data, $bids_data);
            }

            return !empty($bids_data->to_account);
        }

        function myaction($m_id, $pay_sum, $direction) {
            global $bids_data;

            if (!$m_id || get_mscript($m_id) !== $this->name || empty($bids_data->trans_in)) {
                return $m_id;
            }

            $bid_tx_id = $bids_data->trans_in;

            $m_define = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            $api = new M_GOATX($this->name, $m_id, $m_define, $m_data);

            $tx = $api->get_tx($bid_tx_id)['pd'];

            $tx_id = pn_strip_input($tx['invid'] ?? '' ?: '');
            $tx_status = mb_strtoupper($tx['status'] ?? '' ?: '');

            $checked_fields = [$tx_id, $tx_status];
            if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                return $m_id;
            }

            $params = [
                'bid_status' => get_status_sett('merch', true),
                'm_place' => sprintf('%s_%s', $m_id, __FUNCTION__),
                'm_id' => $m_id,
                'm_data' => $m_data,
                'm_defin' => $m_define,
            ];
            set_bid_status('payed', $bids_data->id, $params, $direction);

            if (!empty($this->tx_statuses[$tx_status])) {
                $this->_payment_check(__FUNCTION__, $m_id, $m_define, $m_data, $tx_id);
            }

            return $m_id;
        }

        function _success_url() {
            redirect_merchant_action(is_param_get('order_id'), $this->name, 1);
        }

        function _fail_url() {
            redirect_merchant_action(is_param_get('order_id'), $this->name);
        }

        function _webhook_url() {
            $m_id = key_for_url('_webhook');
            $m_define = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            $this->webhook($m_id, $m_define, $m_data);
        }

        private function webhook($m_id, $m_define, $m_data) {

            $data = pn_json_decode(file_get_contents('php://input')) ?? [];

            do_action('merchant_secure', $this->name, $data, $m_id, $m_define, $m_data);

            $tx_id = pn_strip_input($data['invid'] ?? '' ?: '');
            $tx_status = mb_strtoupper($data['status'] ?? '' ?: '');

            $checked_fields = [$tx_id, $tx_status];
            if (count(array_filter($checked_fields)) !== count($checked_fields) || empty($this->tx_statuses[$tx_status])) {
                wp_send_json_success();
            }

            $this->_payment_check(__FUNCTION__, $m_id, $m_define, $m_data, $tx_id);

            wp_send_json_success();
        }

        function cron($m_id, $m_defin, $m_data) {

            $this->_payment_check(__FUNCTION__, $m_id, $m_defin, $m_data);

        }

        private function _payment_check($place, $m_id, $m_define, $m_data, $tx_info = false) {
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

                $api = new M_GOATX($this->name, $m_id, $m_define, $m_data);
                $history = (empty($bids) || $tx_id) ? null : $api->get_txs()['pd'];

                foreach ($bids as $bid) {
                    $bid_id = $bid->id;
                    $bid_tx_id = $bid->trans_in;

                    $tx = is_array($tx_info) ? $tx_info : ($history[$bid_tx_id] ?? $api->get_tx($bid_tx_id)['pd']);

                    $tx_id = pn_strip_input($tx['invid'] ?? '' ?: '');
                    $tx_status = mb_strtoupper($tx['status'] ?? '' ?: '');
                    $tx_amount = $this->_sum_format($tx['incoming_amount'] ?? '' ?: '', $m_id);
                    $tx_currency = mb_strtoupper(pn_strip_input($tx['requisite']['asset'] ?? '' ?: ''));
                    $tx_hash = '';
                    $tx_purse_from = '';
                    $tx_purse = '';

                    $checked_fields = [$tx_id, $tx_status, $tx_amount];
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

        function _sum_format($sum, $m_id) {

            if (!$m_id || get_mscript($m_id) !== $this->name) {
                return $sum;
            }

            $sum = is_sum($sum, ...array_slice($this->sum_format, 1));
            return is_string($sum) && is_isset($this->sum_format, 0) ? (float)$sum : $sum;
        }

        private function _add_field($options, $data, $name, $help = false) {

            $options["add_{$name}"] = [
                'view' => 'textarea',
                'title' => __('Add new', 'pn'),
                'default' => is_isset($data, "add_{$name}"),
                'name' => "add_{$name}",
                'rows' => 5,
                'work' => 'text',
            ];

            if (!$help) {
                $help = [__('Code', 'pn'), __('Code', 'pn') . '=' . __('Title', 'pn')];
            }

            $options["add_{$name}_help"] = ['view' => 'help', 'title' => __('Example', 'pn'), 'default' => implode('<br/>', $help)];

            return $options;
        }

        function _change_bid_status($data) {
            $bid = $data['bid'];
            $set_status = $data['set_status'];
            $stop_action = intval(is_isset($data, 'stop'));

            if ($stop_action || 'cancel' != $set_status || empty($bid->trans_in)) {
                return $data;
            }

            $m_id = $bid->m_in;
            if (!$m_id || get_mscript($m_id) !== $this->name) {
                return $data;
            }

            $m_define = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            $api = new M_GOATX($this->name, $m_id, $m_define, $m_data);

            $api->cancel_order($bid->trans_in);

            return $data;
        }

    }
}

new merchant_goatx(__FILE__, 'GoatX');
