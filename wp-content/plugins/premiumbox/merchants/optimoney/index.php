<?php

if (!defined('ABSPATH')) exit();

/*
title: [en_US:]OptiMoney[:en_US][ru_RU:]OptiMoney[:ru_RU]
description: [en_US:]OptiMoney merchant[:en_US][ru_RU:]мерчант OptiMoney[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_optimoney')) {
    class merchant_optimoney extends Ext_Merchant_Premiumbox {
        private array $m_types;
        private array $disable_opts = ['check_api', 'check'];
        private array $sum_format = [false, 6, 'down'];
        private array $tx_statuses = [
            'realpay' => ['COMPLETED'],
        ];

        function __construct($file, $title) {
            parent::__construct($file, $title, !in_array('ext_cron_url', $this->disable_opts));

            $this->m_types = [
                'form' => 'Form'
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
        }

        function get_map() {
            return [
                'BASE_URL' => [
                    'title' => '[en_US:]Domain[:en_US][ru_RU:]Домен[:ru_RU]',
                    'view' => 'input',
                    'hidden' => false,
                ],
                'API_KEY' => [
                    'title' => '[en_US:]API key <span class="bred">*</span>[:en_US][ru_RU:]API ключ <span class="bred">*</span>[:ru_RU]',
                    'view' => 'input',
                    'hidden' => true,
                ],
                'SECRET_KEY' => [
                    'title' => '[en_US:]Secret key <span class="bred">*</span>[:en_US][ru_RU:]Секретный ключ <span class="bred">*</span>[:ru_RU]',
                    'view' => 'input',
                    'hidden' => true,
                ],
                'MID' => [
                    'title' => '[en_US:]Merchant ID <span class="bred">*</span>[:en_US][ru_RU:]ID Мерчанта <span class="bred">*</span>[:ru_RU]',
                    'view' => 'input',
                    'hidden' => false,
                ],
                'M_SECRET_KEY' => [
                    'title' => '[en_US:]Merchant API Key <span class="bred">*</span>[:en_US][ru_RU:]API ключ мерчанта <span class="bred">*</span>[:ru_RU]',
                    'view' => 'input',
                    'hidden' => true,
                ],
            ];
        }

        function settings_list() {
            return [['API_KEY', 'SECRET_KEY', 'MID', 'M_SECRET_KEY']];
        }

        function options($options, $data, $id, $place) {
            global $wpdb;

            $m_define = $this->get_file_data($id);
            $m_data = get_merch_data($id);

            $options = pn_array_unset($options, $this->disable_opts);
            if (!in_array($this->merch_type($id, $m_data), ['address', 'coupon'])) $options = pn_array_unset($options, ['pagenote']);
            if (in_array('ext_cron_url', $this->disable_opts)) $options = pn_array_unset($options, ['cronhash']);
            if (in_array('ext_webhook_url', $this->disable_opts)) $options = pn_array_unset($options, ['enableip', 'resulturl', 'help_resulturl']);

            if (count($this->m_types) > 1) {
                $merch_type = is_isset($data, 'merch_type') ?: $this->merch_type($id, $m_data);

                $m_options = [
                    'merch_type' => [
                        'view' => 'select',
                        'title' => __('Merchant type', 'pn') . ' <span class="bred">*</span>',
                        'options' => $this->m_types,
                        'default' => $merch_type,
                        'name' => 'merch_type',
                        'work' => 'input',
                    ],
                    'ext_line_m' => ['view' => 'line'],
                ];
                $options = pn_array_insert($options, 'top_title', $m_options);

                if (1 == $place) {
                    $bids_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}exchange_bids WHERE m_in = %s", $id));
                    if ($bids_count) {
                        $options['merch_type']['name'] .= '_disabled';
                        $options['merch_type']['atts'] = ['disabled' => 'disabled'];
                        $options['merch_type_hidden'] = ['view' => 'hidden_input', 'name' => 'merch_type', 'default' => $merch_type];
                    }
                }
            }

            $options['ext_line'] = ['view' => 'line'];

            $work_status = sprintf('<strong class="bred">%s</strong>', __('Config file is not configured', 'pn'));

            ////////////////////////////////////////

            if (1 == $place && 'success' == $this->settingtext('success', $id)) {
                $work_status = sprintf('<strong class="bred">%s</strong>', mb_strtoupper(__('error', 'pn')));

                $api = new M_OPTIMONEY($this->name, $id, $m_define, $m_data);

                $r = $api->get_tx('test');

                if (404 == $r['status_code']) {
                    $work_status = sprintf('<strong class="bgreen">%s</strong>', mb_strtoupper(__('ok', 'pn')));
                }
            }

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

        private function init_form($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $bids_data, $premiumbox;

            if ($bids_data->to_account) {
                return true;
            }

            $api = new M_OPTIMONEY($this->name, $m_id, $m_defin, $m_data);

            $update_data = [
                'trans_in' => pn_strip_input("m-{$bids_data->id}"),
                'to_account' => pn_strip_input($api->get_mid()),
            ];
            $bids_data = update_bid_tb_array($bids_data->id, $update_data, $bids_data);

            return !empty($bids_data->to_account);
        }

        function bidform($temp, $m_id, $pay_sum, $direction) {
            global $bids_data;

            if (!$m_id || get_mscript($m_id) !== $this->name) {
                return $temp;
            }

            $m_define = $this->get_file_data($m_id);
            $m_data = get_merch_data($m_id);

            // BID DATA
            $currency_code_give = mb_strtoupper($bids_data->currency_code_give);

            // M DATA
            $note = trim(pn_maxf_mb(get_text_pay($m_id, $bids_data, $pay_sum), 150));

            $api = new M_OPTIMONEY($this->name, $m_id, $m_define, $m_data);

            $data = [
                'payment_id' => "m-{$bids_data->id}",
                'amount' => $pay_sum,
                'currency' => $currency_code_give,
                'description' => $note,
                'success_url' => apply_filters('custom_url', get_mlink("{$m_id}_success") . "?order_id={$bids_data->id}", 'success', $this->name, $m_id),
                'fail_url' => apply_filters('custom_url', get_mlink("{$m_id}_fail") . "?order_id={$bids_data->id}", 'fail', $this->name, $m_id),
            ];

            return $api->create_tx($data);
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

            $tx_id = pn_strip_input($data['data']['payment_id'] ?? '' ?: '');
            $tx_status = mb_strtoupper($data['data']['status'] ?? '' ?: '');
            $tx_type = mb_strtoupper($data['event_type'] ?? '' ?: '');

            $checked_fields = [$tx_id, $tx_status, $tx_type];
            if (count(array_filter($checked_fields)) !== count($checked_fields) || 'PAYMENT_UPDATED' != $tx_type || empty($this->tx_statuses[$tx_status])) {
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

                $api = new M_OPTIMONEY($this->name, $m_id, $m_define, $m_data);
                $history = (empty($bids) || $tx_id) ? null : $api->get_txs()['pd'];

                foreach ($bids as $bid) {
                    $bid_id = $bid->id;
                    $bid_tx_id = $bid->trans_in;

                    $tx = is_array($tx_info) ? $tx_info : ($history[$bid_tx_id] ?? $api->get_tx($bid_tx_id)['pd']);

                    $tx_id = pn_strip_input($tx['payment_id'] ?? '' ?: '');
                    $tx_status = mb_strtoupper($tx['status_label'] ?? '' ?: '');
                    $tx_amount = $this->_sum_format($tx['amount'] ?? '' ?: '', $m_id);
                    $tx_currency = mb_strtoupper(pn_strip_input($tx['currency'] ?? '' ?: ''));
                    $tx_hash = '';
                    $tx_purse_from = '';
                    $tx_purse = '';

                    $checked_fields = [$tx_id, $tx_status, $tx_amount, $tx_currency];
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

    }
}

new merchant_optimoney(__FILE__, 'OptiMoney');
