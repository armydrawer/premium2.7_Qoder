<?php
if (!defined('ABSPATH')) exit();

add_filter('list_tabs_direction', 'list_tabs_direction_amlcheck', 10, 2);
function list_tabs_direction_amlcheck($list_tabs, $item) {

    $tab_title = '';

    $amlcheck = is_extension_name(is_isset($item, 'amlcheck'));

    $options = pn_json_decode(is_isset($item, 'amlcheck_opts'));
    if (!is_array($options)) $options = array();

    $give = intval(is_isset($options, 'give'));
    $get = intval(is_isset($options, 'get'));
    $merch = intval(is_isset($options, 'merch'));
    if ($amlcheck) {
        if ($give > 0 or $get > 0 or $merch > 0) {
            $tab_title = ' <span class="bgreen">*</span>';
        }
    }

    $list_tabs['amlcheck'] = __('AML', 'pn') . $tab_title;

    return $list_tabs;
}

add_action('tab_direction_amlcheck', 'def_tab_direction_amlcheck', 20, 2);
function def_tab_direction_amlcheck($data, $data_id) {

    $amlcheck = is_extension_name(is_isset($data, 'amlcheck'));

    $options = pn_json_decode(is_isset($data, 'amlcheck_opts'));
    if (!is_array($options)) $options = array();

    $give = intval(is_isset($options, 'give'));
    $get = intval(is_isset($options, 'get'));
    $merch = intval(is_isset($options, 'merch'));
    $give_error = intval(is_isset($options, 'give_error'));
    $get_error = intval(is_isset($options, 'get_error'));
    $merch_error = intval(is_isset($options, 'merch_error'));
    $give_sum = is_sum(is_isset($options, 'give_sum'));
    $get_sum = is_sum(is_isset($options, 'get_sum'));
    $merch_sum = is_sum(is_isset($options, 'merch_sum'));

    $lists = list_extandeds('amlcheck');
    ?>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('AML', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">
                <select name="amlcheck" autocomplete="off">
                    <option value="0">--<?php _e('No', 'pn'); ?>--</option>
                    <?php foreach ($lists as $m_key => $m_title) { ?>
                        <option value="<?php echo $m_key; ?>" <?php selected($m_key, $amlcheck); ?>><?php echo $m_title; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Checking account Give', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">
                <select name="amlcheck_give" autocomplete="off">
                    <option value="0" <?php selected($give, 0); ?>><?php _e('No', 'pn'); ?></option>
                    <option value="1" <?php selected($give, 1); ?>><?php _e('Yes, during application creation', 'pn'); ?></option>
                    <option value="2" <?php selected($give, 2); ?>><?php _e('Yes, upon payment', 'pn'); ?></option>
                    <option value="3" <?php selected($give, 3); ?>><?php _e('Yes, during auto payout', 'pn'); ?></option>
                </select>
            </div>
        </div>
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Exchange amount "from"', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">
                <input type="text" name="amlcheck_give_sum" style="width: 100%;" value="<?php echo is_sum($give_sum); ?>"/>
            </div>
        </div>
    </div>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Exceeding the risk', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">
                <select name="amlcheck_give_error" autocomplete="off">
                    <option value="0" <?php selected($give_error, 0); ?>><?php _e('Nothing', 'pn'); ?></option>
                    <option value="1" <?php selected($give_error, 1); ?>><?php _e('Error', 'pn'); ?></option>
                </select>
            </div>
        </div>
    </div>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Checking account Send', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">
                <select name="amlcheck_get" autocomplete="off">
                    <option value="0" <?php selected($get, 0); ?>><?php _e('No', 'pn'); ?></option>
                    <option value="1" <?php selected($get, 1); ?>><?php _e('Yes, during application creation', 'pn'); ?></option>
                    <option value="2" <?php selected($get, 2); ?>><?php _e('Yes, upon payment', 'pn'); ?></option>
                    <option value="3" <?php selected($get, 3); ?>><?php _e('Yes, during auto payout', 'pn'); ?></option>
                </select>
            </div>
        </div>
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Exchange amount "from"', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">
                <input type="text" name="amlcheck_get_sum" style="width: 100%;" value="<?php echo is_sum($get_sum); ?>"/>
            </div>
        </div>
    </div>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Exceeding the risk', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">
                <select name="amlcheck_get_error" autocomplete="off">
                    <option value="0" <?php selected($get_error, 0); ?>><?php _e('Nothing', 'pn'); ?></option>
                    <option value="1" <?php selected($get_error, 1); ?>><?php _e('Error', 'pn'); ?></option>
                </select>
            </div>
        </div>
    </div>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Checking TxID', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">
                <select name="amlcheck_merch" autocomplete="off">
                    <option value="0" <?php selected($merch, 0); ?>><?php _e('No', 'pn'); ?></option>
                    <option value="1" <?php selected($merch, 1); ?>><?php _e('Yes, upon payment', 'pn'); ?></option>
                    <option value="2" <?php selected($merch, 2); ?>><?php _e('Yes, during auto payout', 'pn'); ?></option>
                </select>
            </div>
        </div>
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Exchange amount "from"', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">
                <input type="text" name="amlcheck_merch_sum" style="width: 100%;" value="<?php echo is_sum($merch_sum); ?>"/>
            </div>
        </div>
    </div>
    <div class="add_tabs_line">
        <div class="add_tabs_single">
            <div class="add_tabs_sublabel"><span><?php _e('Exceeding the risk', 'pn'); ?></span></div>
            <div class="premium_wrap_standart">
                <select name="amlcheck_merch_error" autocomplete="off">
                    <option value="0" <?php selected($merch_error, 0); ?>><?php _e('Nothing', 'pn'); ?></option>
                    <option value="1" <?php selected($merch_error, 1); ?>><?php _e('Error', 'pn'); ?></option>
                </select>
            </div>
        </div>
    </div>
    <?php
}

add_filter('pn_direction_addform_post', 'amlcheck_direction_addform_post');
function amlcheck_direction_addform_post($array) {

    $array['amlcheck'] = is_extension_name(is_param_post('amlcheck'));

    $options = array();
    $options['give'] = intval(is_param_post('amlcheck_give'));
    $options['get'] = intval(is_param_post('amlcheck_get'));
    $options['merch'] = intval(is_param_post('amlcheck_merch'));
    $options['give_error'] = intval(is_param_post('amlcheck_give_error'));
    $options['get_error'] = intval(is_param_post('amlcheck_get_error'));
    $options['merch_error'] = intval(is_param_post('amlcheck_merch_error'));
    $options['give_sum'] = is_sum(is_param_post('amlcheck_give_sum'));
    $options['get_sum'] = is_sum(is_param_post('amlcheck_get_sum'));
    $options['merch_sum'] = is_sum(is_param_post('amlcheck_merch_sum'));

    $array['amlcheck_opts'] = pn_json_encode($options);

    return $array;
}

function get_aml_title($title) {

    $arr = array();
    if (isset($arr[$title])) {
        $title = $arr[$title];
    }
    $title = str_replace('_', ' ', $title);

    return $title;
}

function get_aml_score($score) {

    if ('no' == $score) {
        return '<span style="font-weight: 600;">' . __('not check', 'pn') . '</span>';
    }

    $score = intval($score);
    $color = '02900c';

    if ($score > 59) {
        $color = 'e2c501';
    }

    if ($score > 89) {
        $color = 'ff0000';
    }

    return '<span style="font-weight: 600; color: #' . $color . ';">' . $score . '%</span>';
}

function get_aml_status($status) {

    $status = intval($status);
    $st = array(
        '0' => __('not check', 'pn'),
        '1' => __('successful check', 'pn'),
        '2' => __('wait check', 'pn'),
        '3' => __('error check', 'pn'),
    );

    return is_isset($st, $status);
}

function get_aml_table($item, $places, $st) {

    $st = intval($st);

    if (is_string($places)) $places = array($places);
    if (!is_array($places)) $places = array();

    $titles = array(
        'aml_merch' => __('transaction', 'pn'),
        'aml_give' => __('account Give', 'pn'),
        'aml_get' => __('account Send', 'pn')
    );

    $html = array();

    $r = 0;
    foreach ($places as $place) {
        $data = pn_json_decode(is_isset($item, $place));
        if (!is_array($data)) {
            $data = array();
        }
        $data = apply_filters('aml_place_data', $data, $place, $item);
        if (is_array($data) and count($data) > 0) {
            $status = intval(is_isset($data, 'status'));
            if (1 == $status or 1 == $st) {
                $r++;

                $score = intval(is_isset($data, 'score'));
                $signals = is_isset($data, 'signals');
                $link = pn_strip_input(is_isset($data, 'link'));

                $signal = '<div class="aml_table"><div class="aml_table_ins"><table>';

                $signal .= '<tr><th>' . is_isset($titles, $place) . '</th><th>' . get_aml_score($score) . '</th></tr>';
                if ($st) {
                    $signal .= '<tr><th>' . __('status', 'pn') . '</th><th><strong>' . get_aml_status($status) . '</strong></th></tr>';
                }

                if (is_array($signals) and count($signals) > 0) {
                    foreach ($signals as $s_key => $s_value) {
                        $s_value = is_sum($s_value * 100);
                        $signal .= '<tr><td>' . get_aml_title($s_key) . '</td><td>' . $s_value . '%</td></tr>';
                    }
                }

                if ($link) {
                    $signal .= '<tr><th></th><th><a href="' . $link . '" target="_blank">' . __('full result of checking', 'pn') . '</a></th></tr>';
                }

                $signal .= '</table></div></div>';

                $html[] = $signal;

            }
        }
    }

    return implode('', $html);
}

function get_aml_data($item, $places, $st) {

    $st = intval($st);

    if (is_string($places)) $places = array($places);
    if (!is_array($places)) $places = array();

    $titles = array(
        'aml_merch' => __('transaction', 'pn'),
        'aml_give' => __('account Give', 'pn'),
        'aml_get' => __('account Send', 'pn')
    );

    $score = 'no';

    $html = array();

    $r = 0;
    foreach ($places as $place) {
        $data = pn_json_decode(is_isset($item, $place));
        if (!is_array($data)) {
            $data = array();
        }
        $data = apply_filters('aml_place_data', $data, $place, $item);
        if (is_array($data) and count($data) > 0) {
            $status = intval(is_isset($data, 'status'));
            if (1 == $status) {
                $r++; //or 1 == $st

                if ('no' == $score) {
                    $score = 0;
                }

                $now_score = intval(is_isset($data, 'score'));
                $signals = is_isset($data, 'signals');
                $link = pn_strip_input(is_isset($data, 'link'));
                $score = $score + $now_score;

                $signal = '<div class="amlrat_subwrap" style="padding: 0 0 5px 0; margin: 0 0 5px 0; border-bottom: 1px solid #ddd;">';
                if ($st) {
                    $signal .= '<div class="amlrat_status" style="padding: 0 0 5px 0;"><strong>' . __('status', 'pn') . ':</strong> ' . get_aml_status($status) . '</div>';
                } else {
                    $signal .= '<div class="amlrat_subtitle" style="padding: 0 0 5px 0;">' . is_isset($titles, $place) . ' - ' . get_aml_score($now_score) . '</div>';
                }

                if (is_array($signals) and count($signals) > 0) {
                    $signal .= '<div class="amlrat_cont">';
                    foreach ($signals as $s_key => $s_value) {
                        $s_value = is_sum($s_value * 100);
                        $signal .= '<div class="amlrat_line"><strong>' . get_aml_title($s_key) . '</strong>: ' . $s_value . '%</div>';
                    }
                    $signal .= '</div>';
                }

                if ($link) {
                    $signal .= '<div class="amlrat_link"><strong><a href="' . $link . '" target="_blank">' . __('full result of checking', 'pn') . '</a></strong></div>';
                }

                $signal .= '</div>';

                $html[] = $signal;

            }
        }
    }

    if ($score > 0 and $r > 0) {
        $score = $score / $r;
    }

    $html = '<span class="amlrat"><span class="amlrat_title" style="cursor: pointer;">' . get_aml_score($score) . '</span><span class="amlrat_abs" style="display: none;">' . implode('', $html) . '</span></span>';

    return $html;
}

add_filter('onebid_col1', 'onebid_col1_amlcheck', 0, 4);
function onebid_col1_amlcheck($actions, $item, $v, $direction) {

    $amlcheck = isset($direction->amlcheck) && is_extension_name($direction->amlcheck);
    $address = pn_strip_input($item->to_account);
    $trans_in = pn_strip_input($item->trans_in);
    $after_name = 'trans_in';
    $txid_in = pn_strip_input(is_isset($item, 'txid_in'));
    if ($txid_in) {
        $trans_in = $txid_in;
        $after_name = 'txid_in';
    }

    if (!$trans_in || !$address || !$amlcheck) {
        return $actions;
    }

    $nactions = array();
    $nactions['aml_trans_in'] = array(
        'type' => 'text',
        'title' => __('AML Risk', 'pn'),
        'label' => get_aml_data($item, 'aml_merch', 1) . ' [<a href="' . pn_link('amlcheck_action') . '&item_id=' . $item->id . '&set=0' . '">' . __('Check', 'pn') . '</a>]',
    );
    $actions = pn_array_insert($actions, $after_name, $nactions, 'after');

    return $actions;
}

add_filter('onebid_col2', 'onebid_col2_amlcheck', 0, 4);
function onebid_col2_amlcheck($actions, $item, $v, $direction) {

    $amlcheck = isset($direction->amlcheck) && is_extension_name($direction->amlcheck);
    $account_give = pn_strip_input($item->account_give);

    if (!$account_give || !$amlcheck) {
        return $actions;
    }

    $vd = is_isset($v, $item->currency_id_give);
    if (!isset($vd->cat_id) || !in_array($vd->cat_id, [0, 1])) {
        return $actions;
    }

    $nactions = array();
    $nactions['aml_account_give'] = array(
        'type' => 'text',
        'title' => __('AML Risk', 'pn'),
        'label' => get_aml_data($item, 'aml_give', 1) . ' [<a href="' . pn_link('amlcheck_action') . '&item_id=' . $item->id . '&set=1' . '">' . __('Check', 'pn') . '</a>]',
    );

    return pn_array_insert($actions, 'account_give', $nactions, 'after');
}

add_filter('onebid_col3', 'onebid_col3_amlcheck', 0, 4);
function onebid_col3_amlcheck($actions, $item, $v, $direction) {

    $amlcheck = isset($direction->amlcheck) && is_extension_name($direction->amlcheck);
    $account_get = pn_strip_input($item->account_get);

    if (!$account_get || !$amlcheck) {
        return $actions;
    }

    $vd = is_isset($v, $item->currency_id_get);
    if (!isset($vd->cat_id) || !in_array($vd->cat_id, [0, 1])) {
        return $actions;
    }

    $nactions = array();
    $nactions['aml_account_get'] = array(
        'type' => 'text',
        'title' => __('AML Risk', 'pn'),
        'label' => get_aml_data($item, 'aml_get', 1) . ' [<a href="' . pn_link('amlcheck_action') . '&item_id=' . $item->id . '&set=2' . '">' . __('Check', 'pn') . '</a>]',
    );

    return pn_array_insert($actions, 'account_get', $nactions, 'after');
}


add_filter('direction_instruction_tags', 'amlcheck_directions_tags', 1000, 2);
function amlcheck_directions_tags($tags, $key) {

    $in_page = array('description_txt', 'timeline_txt', 'window_txt', 'frozen_txt');
    if (!in_array($key, $in_page)) {

        $tags['aml_risk'] = array(
            'title' => __('AML Risk', 'pn'),
            'start' => '[aml_risk]',
        );

        $tags['aml_risk_table'] = array(
            'title' => __('AML Risk table', 'pn'),
            'start' => '[aml_risk_table]',
        );

    }

    return $tags;
}

add_filter('direction_instruction', 'amlcheck_direction_instruction', 100010, 3);
function amlcheck_direction_instruction($instruction, $txt_name, $direction) {
    global $wpdb, $premiumbox, $bids_data;

    $not_status = array('timeline_txt', 'description_txt', 'window_txt', 'frozen_txt');
    if (!in_array($txt_name, $not_status) and isset($bids_data->id)) {

        $instruction = str_replace(array('[amlrisk]', '[bitok_risk]', '[coinkyt_risk]', '[getblock_risk]'), '[aml_risk]', $instruction);

        if (strstr($instruction, '[aml_risk]')) {

            $aml_risk = '<div class="amlrat_span">' . __('AML Risk', 'pn') . ' - ' . get_aml_data($bids_data, array('aml_merch', 'aml_give', 'aml_get'), 0) . '</div>';
            $instruction = str_replace('[aml_risk]', $aml_risk, $instruction);

        }

        if (strstr($instruction, '[aml_risk_table]')) {

            $aml_risk_table = get_aml_table($bids_data, array('aml_merch', 'aml_give', 'aml_get'), 0);
            $instruction = str_replace('[aml_risk_table]', $aml_risk_table, $instruction);

        }

    }

    return $instruction;
}

add_action('wp_footer', 'amlcheck_js');
add_action('admin_footer', 'amlcheck_js');
function amlcheck_js() {
    ?>
    <script type="text/javascript">
        jQuery(function ($) {

            $(document).on('click', '.amlrat_title', function () {
                var rating_title = $(this).html();
                var rating_data = $(this).parents('.amlrat').find('.amlrat_abs').html();

                if (rating_data.length > 0) {
                    $(document).JsWindow('show', {
                        id: 'update_info',
                        window_class: 'update_window',
                        title: '<?php _e('AML Risk', 'pn'); ?> - ' + rating_title,
                        content: rating_data,
                        shadow: 1,
                    });
                }

                return false;
            });

        });
    </script>
    <?php
}

add_action('premium_action_amlcheck_action', 'def_amlcheck_action');
function def_amlcheck_action() {
    global $wpdb;

    if (current_user_can('administrator') or current_user_can('pn_bids')) {
        $bid_id = intval(is_param_get('item_id'));
        $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$bid_id'");
        if (isset($item->id)) {

            $direction_id = intval(is_isset($item, 'direction_id'));
            $direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$direction_id'");

            $set = intval(is_param_get('set'));

            if (1 == $set) {

                $checkbid = amlcheck_data($item, $direction, 'manual_give', 1, 'set_' . $item->status);

            } elseif (2 == $set) {

                $checkbid = amlcheck_data($item, $direction, 'manual_get', 1, 'set_' . $item->status);

            } else {

                $checkbid = amlcheck_data($item, $direction, 'manual_merch', 1, 'set_' . $item->status);

            }

            wp_redirect(admin_url('admin.php?page=pn_bids&bidid=' . $bid_id));
            exit;
        }
    }
}

add_filter('error_bids', 'amlcheck_error_bids', 1500, 4);
function amlcheck_error_bids($error_bids, $direction, $vd1, $vd2) {

    if (!empty($error_bids['error_fields'])) {
        return $error_bids;
    }

    $checkbid = amlcheck_data($error_bids['bid'], $direction, 'error', 1);
    $stop = 0;
    if (isset($checkbid['stop'])) {
        $stop = $checkbid['stop'];
        unset($checkbid['stop']);
    }
    $error_bids['bid'] = $checkbid;

    $aml_errors = amlcheck_checked($checkbid, $direction, 'error');

    if (isset($aml_errors['give'])) {
        $error_bids['error_fields']['account1'] = $aml_errors['give'];
    }

    if (isset($aml_errors['get'])) {
        $error_bids['error_fields']['account2'] = $aml_errors['get'];
    }

    return $error_bids;
}

add_filter('change_bid_status', 'amlcheck_change_bidstatus', 130);
function amlcheck_change_bidstatus($data) {
    global $wpdb;

    $place = $data['place'];
    $set_status = $data['set_status'];
    $bid = $data['bid'];
    $who = $data['who'];
    $old_status = $data['old_status'];
    $direction = $data['direction'];
    $stop_action = intval(is_isset($data, 'stop'));

    if ('admin_panel' == $place || $stop_action) {
        return $data;
    }

    if (!in_array($set_status, ['realpay', 'verify'])) {
        return $data;
    }

    if (!isset($direction->id)) {
        $direction_id = intval(is_isset($bid, 'direction_id'));
        $data['direction'] = $direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' AND id = '$direction_id'");
    }

    if (!isset($direction->id)) {
        return $data;
    }

    $checkbid = amlcheck_data($data['bid'], $direction, 'payed', 0, $set_status);
    $stop = 0;
    if (isset($checkbid['stop'])) {
        $stop = $checkbid['stop'];
        unset($checkbid['stop']);
    }
    $data['bid'] = (object)$checkbid;

    if ($stop) {
        $data['stop'] = 1;
        return $data;
    }

    $aml_errors = amlcheck_checked($checkbid, $direction, 'payed');
    if (!$aml_errors) {
        return $data;
    }

    $array = array();
    $array['edit_date'] = current_time('mysql');
    $array['status'] = 'amlerror';
    $wpdb->update($wpdb->prefix . 'exchange_bids', $array, array('id' => $data['bid']->id));

    $old_status = $data['bid']->status;
    $bid = pn_object_replace($bid, $array);
    $data['bid'] = $bid;
    $data['stop'] = 1;

    $ch_data = array(
        'bid' => $bid,
        'set_status' => 'amlerror',
        'place' => 'amlcheck',
        'who' => 'system',
        'old_status' => $old_status,
        'direction' => $direction
    );
    _change_bid_status($ch_data);

    return $data;
}

add_filter('autopayment_filter', 'amlcheck_ap_filter', 20, 8);
function amlcheck_ap_filter($au_filter, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $direction) {
    global $wpdb;

    if (!empty($au_filter['error'])) {
        return $au_filter;
    }

    $checkbid = amlcheck_data($item, $direction, 'beforepayout', 0, 'payout');
    $stop = 0;
    if (isset($checkbid['stop'])) {
        $stop = $checkbid['stop'];
        unset($checkbid['stop']);
    }

    if ($stop) {
        $au_filter['error'][] = 'AML wait';
        return $au_filter;
    }

    $aml_errors = amlcheck_checked($checkbid, $direction, 'beforepayout');
    if (!$aml_errors) {
        return $au_filter;
    }

    $au_filter['error'][] = 'AML error';
    foreach ($aml_errors as $k => $val) {
        $au_filter['error'][] = "{$k}: {$val}";
    }

    $array = array();
    $array['edit_date'] = current_time('mysql');
    $array['status'] = 'amlerror';
    $wpdb->update($wpdb->prefix . 'exchange_bids', $array, array('id' => $item->id));

    $old_status = $item->status;
    $item = pn_object_replace($item, $array);

    $ch_data = array(
        'bid' => $item,
        'set_status' => 'amlerror',
        'place' => 'amlcheck',
        'who' => 'system',
        'old_status' => $old_status,
        'direction' => $direction
    );
    _change_bid_status($ch_data);

    return $au_filter;
}
