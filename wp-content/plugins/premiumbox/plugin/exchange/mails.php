<?php
if (!defined('ABSPATH')) exit();

add_filter('list_admin_notify', 'list_admin_notify_bids');
function list_admin_notify_bids($places_admin) {

    $bid_status_list = list_bid_status();
    $bid_status_list['realdelete'] = __('Completely deleted order', 'pn');
    foreach ($bid_status_list as $k => $v) {
        $t = $v;
        if ('realdelete' != $k) {
            $t = sprintf(__('Status of order is "%s"', 'pn'), $v);
        }
        $places_admin[$k . '_bids1'] = $t;
    }

    return $places_admin;
}

add_filter('list_user_notify', 'list_user_notify_bids');
function list_user_notify_bids($places_admin) {

    $bid_status_list = list_bid_status();
    $bid_status_list['realdelete'] = __('Completely deleted order', 'pn');
    foreach ($bid_status_list as $k => $v) {
        $t = $v;
        if ('realdelete' != $k) {
            $t = sprintf(__('Status of order is "%s"', 'pn'), $v);
        }
        $places_admin[$k . '_bids2'] = $t;
    }

    return $places_admin;
}

add_action('init', 'def_bid_mailtemp_init');
function def_bid_mailtemp_init() {

    $bid_status_list = list_bid_status();
    $bid_status_list['realdelete'] = __('Completely deleted order', 'pn');
    foreach ($bid_status_list as $k => $v) {
        add_filter('list_notify_tags_' . $k . '_bids1', 'def_mailtemp_tags_bids');
        add_filter('list_notify_tags_' . $k . '_bids2', 'def_mailtemp_tags_bids');
    }

}

function def_mailtemp_tags_bids($tags) {

    $tags['bid_delete_time'] = array(
        'title' => __('Order deleting time', 'pn'),
        'start' => '[bid_delete_time]',
    );
    $tags['frozen_date'] = array(
        'title' => __('Payout holding time', 'pn'),
        'start' => '[frozen_date]',
    );
    $tags['exchange_id'] = array(
        'title' => __('Order ID', 'pn'),
        'start' => '[exchange_id]',
    );
    $tags['create_date'] = array(
        'title' => __('Creation date', 'pn'),
        'start' => '[create_date]',
    );
    $tags['edit_date'] = array(
        'title' => __('Edit date', 'pn'),
        'start' => '[edit_date]',
    );
    $tags['course_give'] = array(
        'title' => __('Rate Send', 'pn'),
        'start' => '[course_give]',
    );
    $tags['course_get'] = array(
        'title' => __('Rate Receive', 'pn'),
        'start' => '[course_get]',
    );
    $tags['psys_give'] = array(
        'title' => __('PS name Send', 'pn'),
        'start' => '[psys_give]',
    );
    $tags['psys_get'] = array(
        'title' => __('PS name Receive', 'pn'),
        'start' => '[psys_get]',
    );
    $tags['currency_code_give'] = array(
        'title' => __('Currency code Send', 'pn'),
        'start' => '[currency_code_give]',
    );
    $tags['currency_code_get'] = array(
        'title' => __('Currency code Receive', 'pn'),
        'start' => '[currency_code_get]',
    );
    $tags['account_give'] = array(
        'title' => __('Account To send', 'pn'),
        'start' => '[account_give]',
    );
    $tags['account_get'] = array(
        'title' => __('Account To receive', 'pn'),
        'start' => '[account_get]',
    );
    $tags['first_name'] = array(
        'title' => __('First name', 'pn'),
        'start' => '[first_name]',
    );
    $tags['last_name'] = array(
        'title' => __('Last name', 'pn'),
        'start' => '[last_name]',
    );
    $tags['second_name'] = array(
        'title' => __('Second name', 'pn'),
        'start' => '[second_name]',
    );
    $tags['user_phone'] = array(
        'title' => __('Mobile phone number', 'pn'),
        'start' => '[user_phone]',
    );
    $tags['user_skype'] = array(
        'title' => __('Skype', 'pn'),
        'start' => '[user_skype]',
    );
    $tags['user_telegram'] = array(
        'title' => __('Telegram', 'pn'),
        'start' => '[user_telegram]',
    );
    $tags['user_email'] = array(
        'title' => __('E-mail', 'pn'),
        'start' => '[user_email]',
    );
    $tags['user_passport'] = array(
        'title' => __('Passport number', 'pn'),
        'start' => '[user_passport]',
    );
    $tags['to_account'] = array(
        'title' => __('Merchant account', 'pn'),
        'start' => '[to_account]',
    );
    $tags['dest_tag'] = array(
        'title' => __('Destination tag', 'pn'),
        'start' => '[dest_tag]',
    );
    $tags['bidurl'] = array(
        'title' => __('Exchange URL', 'pn'),
        'start' => '[bidurl]',
    );
    $tags['bidadminurl'] = array(
        'title' => __('Link to order for administrator', 'pn'),
        'start' => '[bidadminurl]',
    );
    $tags['trans_in'] = array(
        'title' => __('Merchant transaction ID', 'pn'),
        'start' => '[trans_in]',
    );
    $tags['trans_out'] = array(
        'title' => __('Auto payout transaction ID', 'pn'),
        'start' => '[trans_out]',
    );
    $tags['txid_in'] = array(
        'title' => __('Merchant txid', 'pn'),
        'start' => '[txid_in]',
    );
    $tags['txid_out'] = array(
        'title' => __('Auto payout txid', 'pn'),
        'start' => '[txid_out]',
    );
    $tags['sum1'] = array(
        'title' => __('Amount To send', 'pn'),
        'start' => '[sum1]',
    );
    $tags['sum1dc'] = array(
        'title' => __('Amount To send (add. fees)', 'pn'),
        'start' => '[sum1dc]',
    );
    $tags['sum1c'] = array(
        'title' => __('Amount Send (PS fee)', 'pn'),
        'start' => '[sum1c]',
    );
    $tags['sum2'] = array(
        'title' => __('Amount Receive', 'pn'),
        'start' => '[sum2]',
    );
    $tags['sum2dc'] = array(
        'title' => __('Amount To receive (add. fees)', 'pn'),
        'start' => '[sum2dc]',
    );
    $tags['sum2c'] = array(
        'title' => __('Amount Receive (PS fee)', 'pn'),
        'start' => '[sum2c]',
    );
    $tags['profit_sum1'] = array(
        'title' => sprintf(__('Profit (give) %s', 'pn'), 'S'),
        'start' => '[profit_sum1]',
    );
    $tags['profit_pers1'] = array(
        'title' => sprintf(__('Profit (give) %s', 'pn'), '%'),
        'start' => '[profit_pers1]',
    );
    $tags['profit_sum2'] = array(
        'title' => sprintf(__('Profit (get) %s', 'pn'), 'S'),
        'start' => '[profit_sum2]',
    );
    $tags['profit_pers2'] = array(
        'title' => sprintf(__('Profit (get) %s', 'pn'), '%'),
        'start' => '[profit_pers2]',
    );
    $tags['uniq'] = array(
        'title' => __('Unique ID', 'pn'),
        'start' => '[uniq id=""]',
    );
    $tags['dirtemp'] = array(
        'title' => __('Direction mail template', 'pn'),
        'start' => '[dirtemp]',
    );

    // Payment system commissions
    $tags['com_sum1'] = array(
        'title' => __('PS fee for the user', 'pn') . ' (S)',
        'start' => '[com_sum1]',
    );
    $tags['com_pers1'] = array(
        'title' => __('PS fee for the user', 'pn') . ' (%)',
        'start' => '[com_pers1]',
    );
    $tags['com_sum2'] = array(
        'title' => __('PS fee for the exchanger', 'pn') . ' (S)',
        'start' => '[com_sum2]',
    );
    $tags['com_pers2'] = array(
        'title' => __('PS fee for the exchanger', 'pn') . ' (%)',
        'start' => '[com_pers2]',
    );
    $tags['minsum1com'] = array(
        'title' => __('Min. amount of PS fee for user', 'pn'),
        'start' => '[minsum1com]',
    );
    $tags['minsum2com'] = array(
        'title' => __('Min. amount of PS fee for exchanger', 'pn'),
        'start' => '[minsum2com]',
    );

    // Exchange commissions
    $tags['com_box_sum1'] = array(
        'title' => __('Add. exchange fee from the sender', 'pn') . ' (S)',
        'start' => '[com_box_sum1]',
    );
    $tags['com_box_pers1'] = array(
        'title' => __('Add. exchange fee from the sender', 'pn') . ' (%)',
        'start' => '[com_box_pers1]',
    );
    $tags['com_box_sum2'] = array(
        'title' => __('Add. exchange fee from the recipient', 'pn') . ' (S)',
        'start' => '[com_box_sum2]',
    );
    $tags['com_box_pers2'] = array(
        'title' => __('Add. exchange fee from the recipient', 'pn') . ' (%)',
        'start' => '[com_box_pers2]',
    );
    $tags['com_box_min1'] = array(
        'title' => __('Min. amount of exchange fee from the sender', 'pn') . ' (S)',
        'start' => '[com_box_min1]',
    );
    $tags['com_box_min2'] = array(
        'title' => __('Min. amount of exchange fee from the recipient', 'pn') . ' (S)',
        'start' => '[com_box_min2]',
    );

    // Parser 2.0
    $tags['new_parser_actions_give'] = array(
        'title' => __('Parser 2.0 rate fee for Give', 'pn'),
        'start' => '[new_parser_actions_give]',
    );
    $tags['new_parser_actions_get'] = array(
        'title' => __('Parser 2.0 rate fee for Get', 'pn'),
        'start' => '[new_parser_actions_get]',
    );

    // Bestchange
    $tags['bc_step'] = array(
        'title' => __('Step for Bestchange parser', 'pn'),
        'start' => '[bc_step]',
    );

    return apply_filters('shortcode_notify_tags_bids', $tags);
}


add_filter('notify_tags_bids', 'def_notify_tags_bids', 99, 3);
function def_notify_tags_bids($notify_tags, $item, $direction = null) {
    global $wpdb;

    if (!isset($direction->id)) {
        $direction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}directions WHERE id = %d", $item->direction_id));
    }

    if (!isset($direction->id)) {
        return $notify_tags;
    }

    $com_box_sum1 = is_isset($direction, 'com_box_sum1');
    $com_box_pers1 = is_isset($direction, 'com_box_pers1');
    $com_box_sum2 = is_isset($direction, 'com_box_sum2');
    $com_box_pers2 = is_isset($direction, 'com_box_pers2');
    $bc_step = 0;

    $dcombysum_exists = apply_filters('db_constructs_itemtype', '', 'dcombysum');
    if ($dcombysum_exists) {
        $cc = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}constructs WHERE itemtype = 'dcombysum' AND item_id = %s", $item->direction_id));
        if ($cc) {
            $dcombysum_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}constructs WHERE itemtype = 'dcombysum' AND item_id = %s AND (%s -0.0) >= amount ORDER BY (amount -0.0) DESC", $item->direction_id, is_sum(is_isset($item, 'sum1'))));
            if ($dcombysum_data) {
                $options = pn_json_decode($dcombysum_data->itemsettings);
                $com_box_sum1 = is_isset($options, 'com_box_sum1');
                $com_box_pers1 = is_isset($options, 'com_box_pers1');
                $com_box_sum2 = is_isset($options, 'com_box_sum2');
                $com_box_pers2 = is_isset($options, 'com_box_pers2');
            }
        }
    }

    if (intval(is_isset($direction, 'bestchangeapi_id'))) {
        $broker = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}bestchangeapi_directions WHERE direction_id = %d", $direction->id));
        if ($broker) {
            $bc_step = is_isset($broker, 'step');
        }
    } elseif (intval(is_isset($direction, 'bestchange_id'))) {
        $broker = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}bestchange_directions WHERE direction_id = %d", $direction->id));
        if ($broker) {
            $bc_step = is_isset($broker, 'step');
        }
    }

    $replacements = array(
        '[com_sum1]' => is_sum(is_isset($direction, 'com_sum1')),
        '[com_pers1]' => is_sum(is_isset($direction, 'com_pers1')),
        '[com_sum2]' => is_sum(is_isset($direction, 'com_sum2')),
        '[com_pers2]' => is_sum(is_isset($direction, 'com_pers2')),
        '[minsum1com]' => is_sum(is_isset($direction, 'minsum1com')),
        '[minsum2com]' => is_sum(is_isset($direction, 'minsum2com')),

        '[com_box_sum1]' => is_sum($com_box_sum1),
        '[com_box_pers1]' => is_sum($com_box_pers1),
        '[com_box_sum2]' => is_sum($com_box_sum2),
        '[com_box_pers2]' => is_sum($com_box_pers2),
        '[com_box_min1]' => is_sum(is_isset($direction, 'com_box_min1')),
        '[com_box_min2]' => is_sum(is_isset($direction, 'com_box_min2')),

        '[new_parser_actions_give]' => pn_parser_num(is_isset($direction, 'new_parser_actions_give')),
        '[new_parser_actions_get]' => pn_parser_num(is_isset($direction, 'new_parser_actions_get')),

        '[bc_step]' => pn_parser_num($bc_step),
    );

    return array_replace($notify_tags, $replacements);
}


add_filter('notify_tags_bids', 'notify_tags_bids__dirtemp', 100000, 3);
function notify_tags_bids__dirtemp($notify_tags, $item, $direction = '') {

    if (empty($direction->id)) {
        return $notify_tags;
    }

    $dirtemp = pn_strip_text(ctv_ml(is_isset($direction, 'mailtemp')));
    $notify_tags['[dirtemp]'] = replace_tags($notify_tags, $dirtemp, 1);

    return $notify_tags;
}


function goed_mail_to_changestatus_bids($item_id, $item, $name1 = '', $name2 = '', $direction = '') {
    global $wpdb;

    if (isset($item->id)) {

        if (!isset($direction->id)) {
            $direction_id = intval($item->direction_id);
            $direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$direction_id'");
        }

        $now_locale = get_locale();
        $admin_locale = get_admin_lang();
        $bid_locale = $item->bid_locale;

        $unmetas = @unserialize($item->unmetas);

        $maildata = is_isset($direction, 'maildata');
        $maildata = pn_json_decode($maildata);

        if ($name1) {

            set_locale($admin_locale);

            $notify_tags = array();
            $notify_tags['[id]'] = $item->id; /* deprecated */
            $notify_tags['[exchange_id]'] = $item->id;
            $notify_tags['[createdate]'] = pn_strip_input($item->create_date); /* deprecated */
            $notify_tags['[create_date]'] = pn_strip_input($item->create_date);
            $notify_tags['[edit_date]'] = pn_strip_input($item->edit_date);
            $notify_tags['[curs1]'] = $notify_tags['[course_give]'] = pn_strip_input($item->course_give);
            $notify_tags['[curs2]'] = $notify_tags['[course_get]'] = pn_strip_input($item->course_get);
            $notify_tags['[valut1]'] = $notify_tags['[psys_give]'] = pn_strip_input(ctv_ml($item->psys_give));
            $notify_tags['[valut2]'] = $notify_tags['[psys_get]'] = pn_strip_input(ctv_ml($item->psys_get));
            $notify_tags['[vtype1]'] = $notify_tags['[currency_code_give]'] = pn_strip_input($item->currency_code_give);
            $notify_tags['[vtype2]'] = $notify_tags['[currency_code_get]'] = pn_strip_input($item->currency_code_get);
            $notify_tags['[account1]'] = $notify_tags['[account_give]'] = pn_strip_input($item->account_give);
            $notify_tags['[account2]'] = $notify_tags['[account_get]'] = pn_strip_input($item->account_get);
            $notify_tags['[first_name]'] = pn_strip_input($item->first_name);
            $notify_tags['[last_name]'] = pn_strip_input($item->last_name);
            $notify_tags['[second_name]'] = pn_strip_input($item->second_name);
            $notify_tags['[user_phone]'] = pn_strip_input($item->user_phone);
            $notify_tags['[user_skype]'] = pn_strip_input($item->user_skype);
            $notify_tags['[user_telegram]'] = pn_strip_input($item->user_telegram);
            $notify_tags['[user_email]'] = pn_strip_input($item->user_email);
            $notify_tags['[user_passport]'] = pn_strip_input($item->user_passport);
            $notify_tags['[to_account]'] = get_shtd_to_account($item);
            $notify_tags['[dest_tag]'] = pn_strip_input($item->dest_tag);
            $notify_tags['[summ1]'] = $notify_tags['[sum1]'] = is_sum($item->sum1);
            $notify_tags['[summ1_dc]'] = $notify_tags['[sum1dc]'] = is_sum($item->sum1dc);
            $notify_tags['[summ1c]'] = $notify_tags['[sum1c]'] = is_sum($item->sum1c);
            $notify_tags['[summ2]'] = $notify_tags['[sum2]'] = is_sum($item->sum2);
            $notify_tags['[summ2_dc]'] = $notify_tags['[sum2dc]'] = is_sum($item->sum2dc);
            $notify_tags['[summ2c]'] = $notify_tags['[sum2c]'] = is_sum($item->sum2c);
            $notify_tags['[bidurl]'] = get_bids_url($item->hashed);
            $notify_tags['[bidadminurl]'] = admin_url('admin.php?page=pn_bids&bidid=' . $item->id);

            $bid_trans_in = pn_strip_input($item->txid_in);
            if (!$bid_trans_in) {
                $bid_trans_in = pn_strip_input($item->trans_in);
            }

            $bid_trans_out = pn_strip_input($item->txid_out);
            if (!$bid_trans_out) {
                $bid_trans_out = pn_strip_input($item->trans_out);
            }

            $notify_tags['[bid_trans_in]'] = $bid_trans_in;
            $notify_tags['[bid_trans_out]'] = $bid_trans_out;

            $notify_tags['[trans_in]'] = pn_strip_input($item->trans_in);
            $notify_tags['[trans_out]'] = pn_strip_input($item->trans_out);
            $notify_tags['[txid_in]'] = pn_strip_input($item->txid_in);
            $notify_tags['[txid_out]'] = pn_strip_input($item->txid_out);

            if (is_array($unmetas)) {
                foreach ($unmetas as $un_key => $un_value) {
                    $notify_tags['[uniq id="' . $un_key . '"]'] = pn_strip_input(ctv_ml($un_value));
                }
            }
            $notify_tags['[frozen_date]'] = get_pn_time($item->touap_date);
            $notify_tags['[bid_delete_time]'] = apply_filters('bid_delete_time', '---', $item);
            $notify_tags['[profit_sum1]'] = is_sum(is_isset($direction, 'profit_sum1'));
            $notify_tags['[profit_pers1]'] = is_sum(is_isset($direction, 'profit_pers1'));
            $notify_tags['[profit_sum2]'] = is_sum(is_isset($direction, 'profit_sum2'));
            $notify_tags['[profit_pers2]'] = is_sum(is_isset($direction, 'profit_pers2'));

            $notify_tags = apply_filters('notify_tags_bids', $notify_tags, $item, $direction);

            $user_send_data = array(
                'admin_email' => 1,
            );
            if (isset($maildata['email']) and $maildata['email']) {
                if (isset($user_send_data['admin_email'])) {
                    unset($user_send_data['admin_email']);
                }
                $user_send_data['user_email'] = $maildata['email'];
            }
            if (isset($maildata['phone']) and $maildata['phone']) {
                if (isset($user_send_data['admin_email'])) {
                    unset($user_send_data['admin_email']);
                }
                $user_send_data['user_phone'] = $maildata['phone'];
            }
            if (isset($maildata['telegram']) and $maildata['telegram']) {
                if (isset($user_send_data['admin_email'])) {
                    unset($user_send_data['admin_email']);
                }
                $user_send_data['user_telegram'] = $maildata['telegram'];
            }
            $result_mail = apply_filters('premium_send_message', 0, $name1, $notify_tags, $user_send_data);

        }

        if ($name2) {

            set_locale($bid_locale);

            $notify_tags = array();
            $notify_tags['[id]'] = $item->id; /* deprecated */
            $notify_tags['[exchange_id]'] = $item->id;
            $notify_tags['[createdate]'] = pn_strip_input($item->create_date); /* deprecated */
            $notify_tags['[create_date]'] = pn_strip_input($item->create_date);
            $notify_tags['[edit_date]'] = pn_strip_input($item->edit_date);
            $notify_tags['[curs1]'] = $notify_tags['[course_give]'] = pn_strip_input($item->course_give);
            $notify_tags['[curs2]'] = $notify_tags['[course_get]'] = pn_strip_input($item->course_get);
            $notify_tags['[valut1]'] = $notify_tags['[psys_give]'] = pn_strip_input(ctv_ml($item->psys_give));
            $notify_tags['[valut2]'] = $notify_tags['[psys_get]'] = pn_strip_input(ctv_ml($item->psys_get));
            $notify_tags['[vtype1]'] = $notify_tags['[currency_code_give]'] = pn_strip_input($item->currency_code_give);
            $notify_tags['[vtype2]'] = $notify_tags['[currency_code_get]'] = pn_strip_input($item->currency_code_get);
            $notify_tags['[account1]'] = $notify_tags['[account_give]'] = pn_strip_input($item->account_give);
            $notify_tags['[account2]'] = $notify_tags['[account_get]'] = pn_strip_input($item->account_get);
            $notify_tags['[first_name]'] = pn_strip_input($item->first_name);
            $notify_tags['[last_name]'] = pn_strip_input($item->last_name);
            $notify_tags['[second_name]'] = pn_strip_input($item->second_name);
            $notify_tags['[user_phone]'] = pn_strip_input($item->user_phone);
            $notify_tags['[user_skype]'] = pn_strip_input($item->user_skype);
            $notify_tags['[user_telegram]'] = pn_strip_input($item->user_telegram);
            $notify_tags['[user_email]'] = pn_strip_input($item->user_email);
            $notify_tags['[user_passport]'] = pn_strip_input($item->user_passport);
            $notify_tags['[to_account]'] = get_shtd_to_account($item);
            $notify_tags['[dest_tag]'] = pn_strip_input($item->dest_tag);
            $notify_tags['[summ1]'] = $notify_tags['[sum1]'] = is_sum($item->sum1);
            $notify_tags['[summ1_dc]'] = $notify_tags['[sum1dc]'] = is_sum($item->sum1dc);
            $notify_tags['[summ1c]'] = $notify_tags['[sum1c]'] = is_sum($item->sum1c);
            $notify_tags['[summ2]'] = $notify_tags['[sum2]'] = is_sum($item->sum2);
            $notify_tags['[summ2_dc]'] = $notify_tags['[sum2dc]'] = is_sum($item->sum2dc);
            $notify_tags['[summ2c]'] = $notify_tags['[sum2c]'] = is_sum($item->sum2c);
            $notify_tags['[bidurl]'] = get_bids_url($item->hashed);
            $notify_tags['[bidadminurl]'] = admin_url('admin.php?page=pn_bids&bidid=' . $item->id);

            $bid_trans_in = pn_strip_input($item->txid_in);
            if (!$bid_trans_in) {
                $bid_trans_in = pn_strip_input($item->trans_in);
            }

            $bid_trans_out = pn_strip_input($item->txid_out);
            if (!$bid_trans_out) {
                $bid_trans_out = pn_strip_input($item->trans_out);
            }

            $notify_tags['[bid_trans_in]'] = $bid_trans_in;
            $notify_tags['[bid_trans_out]'] = $bid_trans_out;

            $notify_tags['[trans_in]'] = pn_strip_input($item->trans_in);
            $notify_tags['[trans_out]'] = pn_strip_input($item->trans_out);
            $notify_tags['[txid_in]'] = pn_strip_input($item->txid_in);
            $notify_tags['[txid_out]'] = pn_strip_input($item->txid_out);

            if (is_array($unmetas)) {
                foreach ($unmetas as $un_key => $un_value) {
                    $notify_tags['[uniq id="' . $un_key . '"]'] = pn_strip_input(ctv_ml($un_value));
                }
            }
            $notify_tags['[frozen_date]'] = get_pn_time($item->touap_date);
            $notify_tags['[bid_delete_time]'] = apply_filters('bid_delete_time', '---', $item);
            $notify_tags['[profit_sum1]'] = is_sum(is_isset($direction, 'profit_sum1'));
            $notify_tags['[profit_pers1]'] = is_sum(is_isset($direction, 'profit_pers1'));
            $notify_tags['[profit_sum2]'] = is_sum(is_isset($direction, 'profit_sum2'));
            $notify_tags['[profit_pers2]'] = is_sum(is_isset($direction, 'profit_pers2'));

            $notify_tags = apply_filters('notify_tags_bids', $notify_tags, $item, $direction);

            $user_send_data = array(
                'user_email' => $item->user_email,
            );
            $user_send_data = apply_filters('user_send_data', $user_send_data, $name2, $item);
            $result_mail = apply_filters('premium_send_message', 0, $name2, $notify_tags, $user_send_data);

        }

        set_locale($now_locale);

    }
}

add_filter('change_bid_status', 'mail_change_bidstatus', 160);
function mail_change_bidstatus($data) {
    global $wpdb, $premiumbox;

    $place = $data['place'];
    $set_status = $data['set_status'];
    $bid = $data['bid'];
    $who = $data['who'];
    $old_status = $data['old_status'];
    $direction = $data['direction'];

    $item_id = $bid->id;

    $stop_action = intval(is_isset($data, 'stop'));
    if (!$stop_action) {

        $action1 = '';
        if ('admin_panel' != $place or 1 == $premiumbox->get_option('exchange', 'admin_mail')) {
            $action1 = $set_status . '_bids1';
        }
        $action2 = $set_status . '_bids2';
        goed_mail_to_changestatus_bids($item_id, $bid, $action1, $action2, $direction);

    }

    return $data;
}	