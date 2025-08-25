<?php

if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Quixfer[:en_US][ru_RU:]Quixfer[:ru_RU]
description: [en_US:]Quixfer merchant[:en_US][ru_RU:]мерчант Quixfer[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_Merchant_Premiumbox')) return;

if (!class_exists('merchant_quixfer')) {
    class merchant_quixfer extends Ext_Merchant_Premiumbox {
        private array $m_types;
        private array $disable_opts = ['ext_success_url', 'ext_fail_url', 'ext_webhook_url', 'note', 'check_api', 'check'];
        private array $sum_format = [4, 'down'];
        private array $tx_statuses = [
            'realpay' => ['COMPLETED'],
            'coldpay' => ['PROCESSING']
        ];

        function __construct($file, $title) {
            parent::__construct($file, $title, !in_array('ext_cron_url', $this->disable_opts));

            $this->m_types = [
                'link' => 'Payment link'
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
            ];
        }

        function settings_list() {
            return [['API_KEY', 'SECRET_KEY']];
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

            $payment_methods = [0 => __('Config file is not configured', 'pn')];
            $currency_fields = sprintf('<strong class="bred">%s</strong>', __('Config file is not configured', 'pn'));

            if (1 == $place && 'success' == $this->settingtext('success', $id)) {
                $work_status = sprintf('<strong class="bred">%s</strong>', mb_strtoupper(__('error', 'pn')));
                $payment_methods = [0 => mb_strtoupper(__('error', 'pn'))];
                $currency_fields = sprintf('<strong class="bred">%s</strong>', mb_strtoupper(__('error', 'pn')));

                $api = new M_QUIXFER($this->name, $id, $m_define, $m_data);

                $r = $api->currencies();

                if ($r['pd']) {
                    $payment_methods = [0 => sprintf('-- %s --', __('Automatically', 'pn'))];
                    $currency_fields = $r['pd'];

                    foreach ($r['pd'] as $val) {
                        $disabled = !$val['receive_active'] ? __('inactive', 'premium') : '';
                        $fields = implode(', ', array_map(fn($f) => mb_strtolower(preg_replace('/_(?:in|out)$/', '', $f['field_id'])), $val['fields_in']));
                        $payment_methods[$val['xml']] = "[{$val['currency']}] {$val['xml']}" . ($fields ? " ($fields)" : "") . ($disabled ? " ($disabled)" : "");
                    }
                }

                if (200 == $r['status_code']) {
                    $work_status = sprintf('<strong class="bgreen">%s</strong>', mb_strtoupper(__('ok', 'pn')));
                }
            }

            $options['payment_method'] = [
                'view' => 'select_search',
                'title' => __('Payment method', 'pn'),
                'options' => $payment_methods,
                'default' => is_isset($data, 'payment_method'),
                'name' => 'payment_method',
                'work' => 'input',
            ];

            $options['currency_fields'] = [
                'view' => 'user_func',
                'name' => 'currency_fields',
                'func' => get_class($this) . '::_currency_fields',
                'func_data' => ['data' => $data, 'currencies' => $currency_fields],
            ];

            ////////////////////////////////////////

            $options['work_status'] = ['view' => 'textfield', 'title' => __('Work status', 'pn'), 'default' => $work_status];

            $text_add_info = array_filter([
                'cron_url' => !in_array('ext_cron_url', $this->disable_opts) ? '<strong>' . __('Cron file', 'pn') . ':</strong> <a href="' . get_mlink("{$id}_cron" . chash_url($id)) . '" target="_blank">' . get_mlink("{$id}_cron" . chash_url($id)) . '</a>' : null,
                'webhook_url' => !in_array('ext_webhook_url', $this->disable_opts) ? '<strong>Webhook URL:</strong> <a href="' . get_mlink("{$id}_webhook" . hash_url($id)) . '" target="_blank">' . get_mlink("{$id}_webhook" . hash_url($id)) . '</a>' : null,
                'success_url' => !in_array('ext_success_url', $this->disable_opts) ? '<strong>Success URL:</strong> <a href="' . get_mlink("{$id}_success") . '" target="_blank">' . get_mlink("{$id}_success") . '</a>' : null,
                'fail_url' => !in_array('ext_fail_url', $this->disable_opts) ? '<strong>Fail URL:</strong> <a href="' . get_mlink("{$id}_fail") . '" target="_blank">' . get_mlink("{$id}_fail") . '</a>' : null,
            ]);

            if ($text_add_info) {
                $options['add_info'] = ['view' => 'textfield', 'title' => '', 'default' => implode('<br/>', $text_add_info)];
            }

            return $options;
        }

        static function _currency_fields($args) {
            //$_data = is_isset($args, 'data');
            $_currencies = is_isset($args, 'currencies');
            $glk = get_lang_key(get_admin_lang());
            ?>
            <div class="premium_standart_line">

                <?php

                echo '<style>
.payment-methods-table-wrapper {
    overflow-x: auto;
}

.payment-methods-table {
    border-collapse: collapse;
    width: 100%;
}

.payment-methods-table th {
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    padding: 4px 3px;
    text-align: center;
    font-weight: bold;
    color: #333;
    white-space: nowrap;
}

.payment-methods-table td {
    border: 1px solid #ddd;
    padding: 3px;
    text-align: center;
    transition: background-color 0.2s ease;
    white-space: nowrap;
}

.payment-methods-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.payment-methods-table tr:hover td {
    background-color: #e6f3ff !important;
}

.payment-methods-table .field-yes {
    background-color: #d4edda;
    color: #155724;
}

.payment-methods-table .field-no,
.payment-methods-table .inactive-method {
    background-color: #f8d7da;
    color: #721c24;
}

.payment-methods-table tr:hover .field-yes {
    background-color: #c3e6cb !important;
}

.payment-methods-table tr:hover .field-no,
.payment-methods-table tr:hover .inactive-method {
    background-color: #f5c6cb !important;
}

.payment-methods-table .currency-cell {
    background-color: #e9ecef;
    font-weight: bold;
    vertical-align: middle;
}

.payment-methods-table tr:hover .currency-cell {
    background-color: #dee2e6 !important;
}
</style>';

                if (!is_array($_currencies)) {
                    echo '
                        <div class="payment-methods-table-wrapper">
                            <table class="payment-methods-table">
                                <thead><tr><th>' . __('Custom currency fields', 'pn') . '</th></tr></thead>
                                <tbody><tr><td colspan="4" class="inactive-method">' . pn_strip_input($_currencies) . '</td></tr></tbody>
                            </table>
                        </div>
                        </div>
                    ';
                    return;
                }

                $grouped_data = [];
                $field_data = [];

                foreach ($_currencies as $method) {
                    $grouped_data[$method['currency']][] = $method;

                    foreach ($method['fields_in'] as $field_id => $field_info) {
                        if (!isset($field_data[$field_id])) {
                            $id = mb_strtolower(preg_replace('/_(?:in|out)$/', '', $field_id));
                            $title = pn_strip_input($field_info["field_name_{$glk}"] ?? $field_info['field_name_en'] ?? $field_id);
                            $fields = [];

                            if ('card_number' == $id) {
                                $fields[] = __('From account', 'pn');
                            } elseif ('full_name' == $id) {
                                $fields[] = sprintf('%s %s', __('Unique ID', 'pn'), 'give_cardholder');
                                $fields[] = sprintf('%s %s %s %s', __('Personal information', 'pn'), __('Last name', 'pn'), __('First name', 'pn'), __('Second name', 'pn'));
                            } elseif ('phone_number' == $id) {
                                $fields[] = sprintf('%s %s', __('Unique ID', 'pn'), 'give_phone');
                                $fields[] = __('From account', 'pn');
                            } else {
                                $fields[] = sprintf('%s %s', __('Unique ID', 'pn'), "give_{$id}");
                            }

                            if ($fields) $title .= ' (' . implode(sprintf(' %s ', __('or', 'pn')), $fields) . ')';

                            $field_data[$field_id] = [
                                'code' => "give_{$id}",
                                'name' => $title,
                                'count' => 0,
                            ];
                        }
                        $field_data[$field_id]['count']++;
                    }
                }

                $fields = array_keys($field_data);
                usort($fields, fn($a, $b) => $field_data[$b]['count'] - $field_data[$a]['count'] ?: strcmp($a, $b));

                $total_columns = count($fields) + 4;

                echo '<div class="payment-methods-table-wrapper">
                <table class="payment-methods-table">
                    <thead>
                        <tr><th colspan="' . $total_columns . '">' . __('Custom currency fields', 'pn') . '</th></tr>
                        <tr>
                            <th>' . __('Currency name', 'pn') . '</th>
                            <th>XML</th>';

                foreach ($fields as $field_id) {
                    echo '<th title="' . $field_data[$field_id]['name'] . '">' . $field_data[$field_id]['code'] . '</th>';
                }

                echo '<th>' . __('min.', 'pn') . '</th>
                            <th>' . __('max.', 'pn') . '</th>
                        </tr>
                    </thead>
                    <tbody>';

                $is_first_group = true;
                foreach ($grouped_data as $currency => $methods) {
                    if (!$is_first_group) {
                        echo '<tr>
                            <th>' . __('Currency name', 'pn') . '</th>
                            <th>XML</th>';
                        foreach ($fields as $field_id) {
                            echo '<th title="' . $field_data[$field_id]['name'] . '">' . $field_data[$field_id]['code'] . '</th>';
                        }
                        echo '<th>' . __('min.', 'pn') . '</th>
                            <th>' . __('max.', 'pn') . '</th>
                        </tr>';
                    }
                    $is_first_group = false;

                    foreach ($methods as $index => $method) {
                        echo '<tr>';

                        if (!$index) {
                            echo '<td rowspan="' . count($methods) . '" class="currency-cell">' . pn_strip_input($currency) . '</td>';
                        }

                        $xml_class = !$method['receive_active'] ? 'inactive-method' : '';
                        echo '<td class="' . $xml_class . '">' . pn_strip_input($method['xml']) . '</td>';

                        foreach ($fields as $field_id) {
                            $has_field = isset($method['fields_in'][$field_id]);
                            $field_class = $has_field ? 'field-yes' : 'field-no';
                            $field_symbol = $has_field ? '+' : '-';
                            echo '<td class="' . $field_class . '">' . $field_symbol . '</td>';
                        }

                        echo '<td>' . number_format_i18n($method['min_receive']) . '</td>
                            <td>' . number_format_i18n($method['max_receive']) . '</td>
                        </tr>';
                    }
                }

                echo '</tbody>
                    <tfoot>
                        <tr>
                            <th>' . __('Currency name', 'pn') . '</th>
                            <th>XML</th>';
                foreach ($fields as $field_id) {
                    echo '<th title="' . $field_data[$field_id]['name'] . '">' . $field_data[$field_id]['code'] . '</th>';
                }
                echo '<th>' . __('min.', 'pn') . '</th>
                            <th>' . __('max.', 'pn') . '</th>
                        </tr>
                    </tfoot>
                </table>
                </div>';
                ?>

                <div class="premium_clear"></div>
            </div>
            <?php
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

        function init_link($m_id, $pay_sum, $direction, $m_defin, $m_data) {
            global $bids_data;

            if ($this->get_pay_link($bids_data->id)) {
                return true;
            }

            // BID DATA
            $currency_code_give = mb_strtoupper($bids_data->currency_code_give);
            $currency_id_give = $bids_data->currency_id_give;
            $currency_id_get = $bids_data->currency_id_get;
            $unmetas = @unserialize($bids_data->unmetas);

            // M DATA
            $pm = pn_strip_input(is_isset($m_data, 'payment_method'));
            $cd = get_currency_data([$currency_id_give, $currency_id_get]);
            if (!$pm) {
                $pm = is_xml_value(isset($cd[$currency_id_give]) ? $cd[$currency_id_give]->xml_value : $pm);
            }

            $api = new M_QUIXFER($this->name, $m_id, $m_defin, $m_data);

            $data = [
                'external_id' => "m_{$bids_data->id}",
                'amount' => $pay_sum,
                'bank' => $pm,
                'currency' => $currency_code_give,
                'sender_email' => $bids_data->user_email,
                'client_ip' => $bids_data->user_ip,
                'client_useragent' => $bids_data->user_agent,
                'client_recipient_data' => pn_strip_input(implode(', ', array_filter([$bids_data->account_give, $bids_data->account_get]))),
                'client_recipient_xml' => is_xml_value(isset($cd[$currency_id_get]) ? $cd[$currency_id_get]->xml_value : ''),
                'success_url' => apply_filters('custom_url', get_bids_url($bids_data->hashed), 'bid', $this->name, $m_id),
            ];

            $r = $api->currencies();

            if ($r['pd'] && isset($r['pd'][$pm])) {
                $fields = array_keys($r['pd'][$pm]['fields_in']);

                foreach ($fields as $field_id) {
                    $id = mb_strtolower(preg_replace('/_(?:in|out)$/', '', $field_id));

                    if ('card_number' == $id) {
                        $data[$field_id] = preg_replace('/\D/', '', $bids_data->account_give);
                    } elseif ('full_name' == $id) {
                        $data[$field_id] = pn_strip_input(is_isset($unmetas, 'give_cardholder') ?: is_isset($unmetas, 'cardholder') ?: implode(' ', array_filter([$bids_data->last_name, $bids_data->first_name, $bids_data->second_name])));
                    } elseif ('phone_number' == $id) {
                        $data[$field_id] = pn_strip_input(is_isset($unmetas, 'give_phone') ?: is_isset($unmetas, 'phone') ?: $bids_data->account_give);
                    } else {
                        $data[$field_id] = pn_strip_input(is_isset($unmetas, "give_{$id}") ?: is_isset($unmetas, $id));
                    }
                }
            }

            $tx = $api->create_order($data)['pd'];

            $tx_id = !empty($tx['order_id']) ? pn_strip_input($tx['order_id']) : null;
            $tx_url = !empty($tx['payment_url']) ? pn_strip_input($tx['payment_url']) : null;

            $checked_fields = [$tx_id, $tx_url];
            if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                return false;
            }

            $this->update_pay_link($bids_data->id, $tx_url);

            $update_data = [
                'trans_in' => $tx_id,
            ];
            $bids_data = update_bid_tb_array($bids_data->id, $update_data, $bids_data);

            return true;
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

        function webhook($m_id, $m_define, $m_data) {

            $data = pn_json_decode(file_get_contents('php://input')) ?? [];

            do_action('merchant_secure', $this->name, $data, $m_id, $m_define, $m_data);

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

                $api = new M_QUIXFER($this->name, $m_id, $m_define, $m_data);
                $history = (empty($bids) || $tx_id) ? null : $api->get_payments()['pd'];

                foreach ($bids as $bid) {
                    $bid_id = $bid->id;
                    $bid_tx_id = $bid->trans_in;

                    $tx = is_array($tx_info) ? $tx_info : ($history[$bid_tx_id] ?? $api->get_payment($bid_tx_id)['pd']);

                    $tx_id = !empty($tx['order_id']) ? pn_strip_input($tx['order_id']) : null;
                    $tx_status = !empty($tx['status']) ? mb_strtoupper($tx['status']) : null;
                    $tx_amount = !empty($tx['amount']) ? $this->_sum_format($tx['amount'], $m_id) : null;
                    $tx_currency = !empty($tx['currency']) ? mb_strtoupper($tx['currency']) : null;
                    $tx_hash = '';
                    $tx_purse_from = '';
                    $tx_purse = !empty($tx['truncated_requisites']) ? pn_strip_input($tx['truncated_requisites']) : null;

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

            if (!$m_id || get_mscript($m_id) !== $this->name || !$this->sum_format) {
                return $sum;
            }

            return (float)is_sum($sum, ...$this->sum_format);
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

            if (!$help) {
                $help = [__('Code', 'pn'), __('Code', 'pn') . '=' . __('Title', 'pn')];
            }

            $options["add_{$name}_help"] = ['view' => 'help', 'title' => __('Example', 'pn'), 'default' => implode('<br/>', $help)];

            return $options;
        }

    }
}

new merchant_quixfer(__FILE__, 'Quixfer');
