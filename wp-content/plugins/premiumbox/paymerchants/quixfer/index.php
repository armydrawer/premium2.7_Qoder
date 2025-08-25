<?php

if (!defined('ABSPATH')) exit();

/*
title: [en_US:]Quixfer[:en_US][ru_RU:]Quixfer[:ru_RU]
description: [en_US:]Quixfer automatic payouts[:en_US][ru_RU:]авто выплаты Quixfer[:ru_RU]
version: 2.7.1
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) return;

if (!class_exists('paymerchant_quixfer')) {
    class paymerchant_quixfer extends Ext_AutoPayut_Premiumbox {
        private array $disable_opts = ['ext_webhook_url', 'note', 'checkpay'];
        private array $sum_format = [4, 'down'];
        private array $tx_statuses = [
            'success' => ['COMPLETED'],
            'payouterror' => ['CANCELED', 'REFUNDED', 'BLOCKLIST'],
        ];

        function __construct($file, $title) {
            parent::__construct($file, $title, !in_array('ext_cron_url', $this->disable_opts));

            $this->tx_statuses = $this->tx_statuses ? array_replace(...array_map(fn($statuses, $processor) => array_fill_keys($statuses, $processor), $this->tx_statuses, array_keys($this->tx_statuses))) : [];

            if ($enabled_url = array_diff(['ext_webhook_url'], $this->disable_opts)) {
                foreach ($this->get_ids('paymerchants', $this->name) as $id) {
                    if (in_array('ext_webhook_url', $enabled_url)) add_action("premium_merchant_ap_{$id}_webhook" . hash_url($id, 'ap'), [$this, '_webhook_url']);
                }
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
            global $premiumbox;

            $m_define = $this->get_file_data($id);
            $m_data = get_paymerch_data($id);

            $options = pn_array_unset($options, $this->disable_opts);
            if (in_array('ext_cron_url', $this->disable_opts)) $options = pn_array_unset($options, ['cronhash']);
            if (in_array('ext_webhook_url', $this->disable_opts)) $options = pn_array_unset($options, ['enableip', 'resulturl', 'help_resulturl']);

            $options['ext_line'] = ['view' => 'line'];

            $work_status = sprintf('<strong class="bred">%s</strong>', __('Config file is not configured', 'pn'));

            ////////////////////////////////////////

            $payment_methods = [0 => __('Config file is not configured', 'pn')];
            $currency_fields = sprintf('<strong class="bred">%s</strong>', __('Config file is not configured', 'pn'));

            if (1 == $place && 'success' == $this->settingtext('success', $id)) {
                $work_status = sprintf('<strong class="bred">%s</strong>', mb_strtoupper(__('error', 'pn')));
                $payment_methods = [0 => mb_strtoupper(__('error', 'pn'))];
                $currency_fields = sprintf('<strong class="bred">%s</strong>', mb_strtoupper(__('error', 'pn')));

                $api = new P_QUIXFER($this->name, $id, $m_define, $m_data);

                $r = $api->currencies();

                if ($r['pd']) {
                    $payment_methods = [0 => sprintf('-- %s --', __('Automatically', 'pn'))];
                    $currency_fields = $r['pd'];

                    foreach ($r['pd'] as $val) {
                        $disabled = !$val['withdraw_active'] ? __('inactive', 'premium') : '';
                        $fields = implode(', ', array_map(fn($f) => mb_strtolower(preg_replace('/_(?:in|out)$/', '', $f['field_id'])), $val['fields_out']));
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
                'cron_url' => !in_array('ext_cron_url', $this->disable_opts) ? '<strong>' . __('Cron file', 'pn') . ':</strong> <a href="' . get_mlink("ap_{$id}_cron" . chash_url($id, 'ap')) . '" target="_blank">' . get_mlink("ap_{$id}_cron" . chash_url($id, 'ap')) . '</a>' : null,
                'webhook_url' => !in_array('ext_webhook_url', $this->disable_opts) ? '<strong>Webhook URL:</strong> <a href="' . get_mlink("ap_{$id}_webhook" . hash_url($id, 'ap')) . '" target="_blank">' . get_mlink("ap_{$id}_webhook" . hash_url($id, 'ap')) . '</a>' : null,
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

                    foreach ($method['fields_out'] as $field_id => $field_info) {
                        if (!isset($field_data[$field_id])) {
                            $id = mb_strtolower(preg_replace('/_(?:in|out)$/', '', $field_id));
                            $title = pn_strip_input($field_info["field_name_{$glk}"] ?? $field_info['field_name_en'] ?? $field_id);
                            $fields = [];

                            if ('card_number' == $id) {
                                $fields[] = __('Into account', 'pn');
                            } elseif ('full_name' == $id) {
                                $fields[] = sprintf('%s %s', __('Unique ID', 'pn'), 'get_cardholder');
                                $fields[] = sprintf('%s %s %s %s', __('Personal information', 'pn'), __('Last name', 'pn'), __('First name', 'pn'), __('Second name', 'pn'));
                            } elseif ('phone_number' == $id) {
                                $fields[] = sprintf('%s %s', __('Unique ID', 'pn'), 'get_phone');
                                $fields[] = __('From account', 'pn');
                            } else {
                                $fields[] = sprintf('%s %s', __('Unique ID', 'pn'), "get_{$id}");
                            }

                            if ($fields) $title .= ' (' . implode(sprintf(' %s ', __('or', 'pn')), $fields) . ')';

                            $field_data[$field_id] = [
                                'code' => "get_{$id}",
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

                        $xml_class = !$method['withdraw_active'] ? 'inactive-method' : '';
                        echo '<td class="' . $xml_class . '">' . pn_strip_input($method['xml']) . '</td>';

                        foreach ($fields as $field_id) {
                            $has_field = isset($method['fields_out'][$field_id]);
                            $field_class = $has_field ? 'field-yes' : 'field-no';
                            $field_symbol = $has_field ? '+' : '-';
                            echo '<td class="' . $field_class . '">' . $field_symbol . '</td>';
                        }

                        echo '<td>' . number_format_i18n($method['min_withdraw']) . '</td>
                            <td>' . number_format_i18n($method['max_withdraw']) . '</td>
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

        function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {
            global $wpdb;

            // BID DATA
            $tx = null;
            $item_id = $item->id;
            $currency_code_get = mb_strtoupper($item->currency_code_get);

            // M DATA
            $pay_sum = $this->_sum_format(is_paymerch_sum($item, $paymerch_data), $m_id);
            $pm = pn_strip_input(is_isset($paymerch_data, 'payment_method'));
            if (!$pm) {
                $currency_id_get = $item->currency_id_get;
                $cd = get_currency_data([$currency_id_get]);
                $pm = is_xml_value(isset($cd[$currency_id_get]) ? $cd[$currency_id_get]->xml_value : $pm);
            }

            if (!$error && !$this->set_ap_status($item, $test)) {
                $error[] = 'Database error';
            }

            if (!$error) {
                try {
                    $api = new P_QUIXFER($this->name, $m_id, $m_defin, $paymerch_data);

                    $data = [
                        'external_id' => "ap_{$item_id}",
                        'amount' => $pay_sum,
                        'bank' => $pm,
                        'currency' => $currency_code_get,
                    ];

                    $r = $api->currencies();

                    if ($r['pd'] && isset($r['pd'][$pm])) {
                        $fields = array_keys($r['pd'][$pm]['fields_out']);

                        foreach ($fields as $field_id) {
                            $id = mb_strtolower(preg_replace('/_(?:in|out)$/', '', $field_id));

                            if ('card_number' == $id) {
                                $data[$field_id] = preg_replace('/\D/', '', $item->account_get);
                            } elseif ('full_name' == $id) {
                                $data[$field_id] = pn_strip_input(is_isset($unmetas, 'get_cardholder') ?: is_isset($unmetas, 'cardholder') ?: implode(' ', array_filter([$item->last_name, $item->first_name, $item->second_name])));
                            } elseif ('phone_number' == $id) {
                                $data[$field_id] = pn_strip_input(is_isset($unmetas, 'get_phone') ?: is_isset($unmetas, 'phone') ?: $item->account_get);
                            } else {
                                $data[$field_id] = pn_strip_input(is_isset($unmetas, "get_{$id}") ?: is_isset($unmetas, $id));
                            }
                        }
                    }

                    $tx = $api->create_order($data)['pd'];

                    if ($tx) {
                        $tx = $api->get_payment($tx['order_id'])['pd'];
                    } else {
                        $error[] = __('Payout error', 'pn');
                        $error[] = pn_json_encode($tx);
                        $pay_error = 1;
                    }
                } catch (Exception $e) {
                    $error[] = pn_json_encode(['line' => $e->getLine(), 'file' => realpath($e->getFile()), 'message' => $e->getMessage()]);
                    $pay_error = 1;
                }
            }

            if ($error) {
                $this->reset_ap_status($error, $pay_error, $item, $place, $m_id, $test);
            } else {
                $tx_id = !empty($tx['order_id']) ? pn_strip_input($tx['order_id']) : null;
                $tx_status = !empty($tx['status']) ? mb_strtoupper($tx['status']) : null;
                $tx_amount = !empty($tx['amount']) ? $this->_sum_format($tx['amount'], $m_id) : null;
                $tx_hash = '';

                $new_status = ($tx_status && 'success' == is_isset($this->tx_statuses, $tx_status) ? 'success' : 'coldsuccess');

                $params = [
                    'out_sum' => $tx_amount,
                    'trans_out' => $tx_id,
                    'txid_out' => $tx_hash,
                    'm_place' => "{$m_id}_{$modul_place}",
                    'system' => 'admin' == $place ? 'user' : 'system',
                    'm_id' => $m_id,
                    'm_defin' => $m_defin,
                    'm_data' => $paymerch_data,
                ];
                set_bid_status($new_status, $item_id, $params, $direction);

                if ('admin' == $place) {
                    $text = ('success' == $new_status ? __('Automatic payout is done', 'pn') : __('Payment is successfully created. Waiting for confirmation.', 'pn'));
                    pn_display_mess($text, '', 'success');
                }
            }
        }

        function _webhook_url() {
            $m_id = key_for_url('_webhook', 'ap_');
            $m_define = $this->get_file_data($m_id);
            $m_data = get_paymerch_data($m_id);

            $this->webhook($m_id, $m_define, $m_data);
        }

        function webhook($m_id, $m_define, $m_data) {

            $data = pn_json_decode(file_get_contents('php://input')) ?? [];

            do_action('paymerchant_secure', $this->name, $data, $m_id, $m_define, $m_data);

            wp_send_json_success();
        }

        function cron($m_id, $m_defin, $m_data) {

            $this->_payment_check(__FUNCTION__, $m_id, $m_defin, $m_data);

        }

        private function _payment_check($place, $m_id, $m_define, $m_data, $tx_info = false) {
            global $wpdb;

            try {
                $tx_id = is_array($tx_info) ? is_isset($tx_info, 'tx_id') : $tx_info;

                $workstatus = ['coldsuccess'];

                $where = [
                    $wpdb->prepare("`m_out` = %s", $m_id),
                    "`status` IN ('" . implode("','", $workstatus) . "')",
                    $tx_id ? $wpdb->prepare("`trans_out` = %s", $tx_id) : "`trans_out` <> '0'",
                ];

                $bids = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}exchange_bids WHERE " . implode(' AND ', $where));

                $api = new P_QUIXFER($this->name, $m_id, $m_define, $m_data);
                $history = (empty($bids) || $tx_id) ? null : $api->get_payments()['pd'];

                foreach ($bids as $bid) {
                    $bid_id = $bid->id;
                    $bid_tx_id = $bid->trans_out;

                    $tx = is_array($tx_info) ? $tx_info : ($history[$bid_tx_id] ?? $api->get_payment($bid_tx_id)['pd']);

                    $tx_id = !empty($tx['order_id']) ? pn_strip_input($tx['order_id']) : null;
                    $tx_status = !empty($tx['status']) ? mb_strtoupper($tx['status']) : null;
                    $tx_amount = !empty($tx['amount']) ? $this->_sum_format($tx['amount'], $m_id) : null;
                    $tx_hash = '';

                    $checked_fields = [$tx_id, $tx_status, $tx_amount];
                    if (count(array_filter($checked_fields)) !== count($checked_fields)) {
                        continue;
                    }

                    $new_status = $this->tx_statuses[$tx_status] ?? '';

                    if ('success' == $new_status) {

                        $params = [
                            'out_sum' => $tx_amount,
                            'trans_out' => $tx_id,
                            'txid_out' => $tx_hash,
                            'bid_status' => $workstatus,
                            'm_place' => "{$m_id}_{$place}",
                            'system' => 'system',
                            'm_id' => $m_id,
                            'm_defin' => $m_define,
                            'm_data' => $m_data,
                        ];
                        set_bid_status($new_status, $bid_id, $params);

                    } elseif ($new_status) {

                        $this->reset_cron_status($bid, is_status_name(is_isset($m_data, 'error_status')), $m_id);

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

            if (!$m_id || get_pscript($m_id) !== $this->name || !$this->sum_format) {
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

new paymerchant_quixfer(__FILE__, 'Quixfer');
