<?php
if (!defined('ABSPATH')) exit();

add_filter('pn_caps', 'directions_pn_caps');
function directions_pn_caps($pn_caps) {

    $pn_caps['pn_directions'] = __('Use exchange direction', 'pn');
    $pn_caps['pn_directions_merchant'] = __('Work with merchants in exchange direction', 'pn');

    return $pn_caps;
}

add_action('admin_menu', 'admin_menu_directions');
function admin_menu_directions() {
    global $premiumbox;

    if (current_user_can('administrator') or current_user_can('pn_directions')) {
        add_menu_page(__('Exchange directions', 'pn'), __('Exchange directions', 'pn'), 'read', "pn_directions", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('directions'), 610);
        add_submenu_page("pn_directions", __('Add exchange direction', 'pn'), __('Add exchange direction', 'pn'), 'read', "pn_add_directions", array($premiumbox, 'admin_temp'));
        add_submenu_page("pn_directions", __('Sorting exchange direction', 'pn'), __('Sorting exchange direction', 'pn'), 'read', "pn_sort_directions", array($premiumbox, 'admin_temp'));
        add_submenu_page("pn_directions", __('Exchange direction templates', 'pn'), __('Exchange direction templates', 'pn'), 'read', "pn_directions_temp", array($premiumbox, 'admin_temp'));
        add_submenu_page("pn_directions", __('Custom fields', 'pn'), __('Custom fields', 'pn'), 'read', "pn_cf", array($premiumbox, 'admin_temp'));
        add_submenu_page("pn_directions", __('Add custom field', 'pn'), __('Add custom field', 'pn'), 'read', "pn_add_cf", array($premiumbox, 'admin_temp'));
        add_submenu_page("pn_directions", __('Sort custom fields', 'pn'), __('Sort custom fields', 'pn'), 'read', "pn_sort_cf", array($premiumbox, 'admin_temp'));
    }

}

add_action('item_psys_delete', 'directions_item_psys_delete');
function directions_item_psys_delete($id) {
    global $wpdb;

    $wpdb->update($wpdb->prefix . 'directions', array('psys_id_give' => 0, 'direction_status' => 0), array('psys_id_give' => $id));
    $wpdb->update($wpdb->prefix . 'directions', array('psys_id_get' => 0, 'direction_status' => 0), array('psys_id_get' => $id));
}

add_action('item_currency_delete', 'directions_item_currency_delete');
function directions_item_currency_delete($id) {
    global $wpdb;

    $wpdb->update($wpdb->prefix . 'directions', array('currency_id_give' => 0, 'direction_status' => 0), array('currency_id_give' => $id));
    $wpdb->update($wpdb->prefix . 'directions', array('currency_id_get' => 0, 'direction_status' => 0), array('currency_id_get' => $id));
}

add_action('item_currency_deactive', 'directions_item_currency_deactive');
function directions_item_currency_deactive($id) {
    global $wpdb;

    $wpdb->query("UPDATE " . $wpdb->prefix . "directions SET direction_status = '0' WHERE currency_id_give = '$id' OR currency_id_get = '$id'");
}

add_action('item_currency_edit', 'directions_item_currency_edit', 1, 2);
function directions_item_currency_edit($data_id, $array) {
    global $wpdb;

    if ($data_id > 0) {
        if (isset($array['currency_status']) and 0 == $array['currency_status']) {
            $wpdb->query("UPDATE " . $wpdb->prefix . "directions SET direction_status = '0' WHERE currency_id_give = '$data_id' OR currency_id_get = '$data_id'");
        }
        $wpdb->update($wpdb->prefix . 'directions', array('psys_id_give' => $array['psys_id']), array('currency_id_give' => $data_id));
        $wpdb->update($wpdb->prefix . 'directions', array('psys_id_get' => $array['psys_id']), array('currency_id_get' => $data_id));
    }

}

add_action('item_direction_delete', 'def_pn_direction_delete');
function def_pn_direction_delete($id) {
    global $wpdb;

    $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions_meta WHERE item_id = '$id'");
    foreach ($items as $item) {
        $item_id = $item->id;
        $res = apply_filters('item_directionmeta_delete_before', pn_ind(), $item_id, $item);
        if ($res['ind']) {
            $result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "directions_meta WHERE id = '$item_id'");
            do_action('item_directionmeta_delete', $item_id, $item, $result);
        }
    }

    delete_direction_txtmeta($id);
}

add_action('item_cf_delete', 'def_item_cf_delete', 10, 2);
function def_item_cf_delete($id, $item) {
    global $wpdb;

    $wpdb->query("DELETE FROM " . $wpdb->prefix . "cf_directions WHERE cf_id = '$id'");

}

add_action('item_direction_delete', 'cf_item_direction_delete', 10, 2);
function cf_item_direction_delete($id, $item) {
    global $wpdb;

    $wpdb->query("DELETE FROM " . $wpdb->prefix . "cf_directions WHERE direction_id = '$id'");

}

add_action('item_direction_copy', 'cf_item_direction_copy', 10, 2);
function cf_item_direction_copy($last_id, $data_id) {
    global $wpdb;

    $cf_directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "cf_directions WHERE direction_id = '$last_id'");
    foreach ($cf_directions as $dir) {
        $arr = array();
        $arr['direction_id'] = $data_id;
        $arr['cf_id'] = $dir->cf_id;
        $wpdb->insert($wpdb->prefix . 'cf_directions', $arr);
    }

}

add_filter('direction_instruction_tags', 'def_directions_tags', 10, 2);
function def_directions_tags($tags, $key) {

    $in_page = array('description_txt', 'timeline_txt', 'window_txt', 'frozen_txt');
    if (!in_array($key, $in_page)) {

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
        $tags['uniq'] = array(
            'title' => __('Unique ID', 'pn'),
            'start' => '[uniq id=""]',
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

    } else {

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

    }

    return $tags;
}

add_filter('direction_instruction', 'def_direction_instruction', 10, 6);
function def_direction_instruction($instruction, $txt_name, $direction, $vd1, $vd2, $bids_data = '') {
    global $wpdb, $premiumbox;

    if (isset($bids_data->id)) {

        $instruction = str_replace(array('[bid_id]', '[exchange_id]'), $bids_data->id, $instruction);
        $instruction = str_replace('[create_date]', $bids_data->create_date, $instruction);
        $instruction = str_replace('[edit_date]', $bids_data->edit_date, $instruction);
        $instruction = str_replace('[course_give]', is_sum($bids_data->course_give), $instruction);
        $instruction = str_replace('[course_get]', is_sum($bids_data->course_get), $instruction);
        $instruction = str_replace('[account_give]', pn_strip_input($bids_data->account_give), $instruction);
        $instruction = str_replace('[account_get]', pn_strip_input($bids_data->account_get), $instruction);
        $instruction = str_replace('[first_name]', pn_strip_input($bids_data->first_name), $instruction);
        $instruction = str_replace('[last_name]', pn_strip_input($bids_data->last_name), $instruction);
        $instruction = str_replace('[second_name]', pn_strip_input($bids_data->second_name), $instruction);
        $instruction = str_replace('[user_phone]', pn_strip_input($bids_data->user_phone), $instruction);
        $instruction = str_replace('[user_skype]', pn_strip_input($bids_data->user_skype), $instruction);
        $instruction = str_replace('[user_telegram]', pn_strip_input($bids_data->user_telegram), $instruction);
        $instruction = str_replace('[user_email]', pn_strip_input($bids_data->user_email), $instruction);
        $instruction = str_replace('[user_passport]', pn_strip_input($bids_data->user_passport), $instruction);
        $instruction = str_replace('[to_account]', get_shtd_to_account($bids_data), $instruction);
        $instruction = str_replace('[dest_tag]', pn_strip_input($bids_data->dest_tag), $instruction);
        $instruction = str_replace('[bidurl]', get_bids_url($bids_data->hashed), $instruction);

        $bid_trans_in = pn_strip_input($bids_data->txid_in);
        if (!$bid_trans_in) {
            $bid_trans_in = pn_strip_input($bids_data->trans_in);
        }

        $bid_trans_out = pn_strip_input($bids_data->txid_out);
        if (!$bid_trans_out) {
            $bid_trans_out = pn_strip_input($bids_data->trans_out);
        }

        $instruction = str_replace('[bid_trans_in]', $bid_trans_in, $instruction);
        $instruction = str_replace('[bid_trans_out]', $bid_trans_out, $instruction);

        $instruction = str_replace('[trans_in]', pn_strip_input($bids_data->trans_in), $instruction);
        $instruction = str_replace('[trans_out]', pn_strip_input($bids_data->trans_out), $instruction);
        $instruction = str_replace('[txid_in]', pn_strip_input($bids_data->txid_in), $instruction);
        $instruction = str_replace('[txid_out]', pn_strip_input($bids_data->txid_out), $instruction);
        $instruction = str_replace(array('[sum_dc]', '[sum1c]'), $bids_data->sum1c, $instruction);
        $instruction = str_replace('[sum1]', $bids_data->sum1, $instruction);
        $instruction = str_replace('[sum1dc]', $bids_data->sum1dc, $instruction);
        $instruction = str_replace('[sum2]', $bids_data->sum2, $instruction);
        $instruction = str_replace('[sum2dc]', $bids_data->sum2dc, $instruction);
        $instruction = str_replace('[sum2c]', $bids_data->sum2c, $instruction);

        $unmetas = @unserialize($bids_data->unmetas);
        if (is_array($unmetas)) {
            foreach ($unmetas as $un_key => $un_value) {
                $instruction = str_replace('[uniq id="' . $un_key . '"]', pn_strip_input(ctv_ml($un_value)), $instruction);
            }
        }

        $instruction = str_replace('[frozen_date]', get_pn_time($bids_data->touap_date), $instruction);
        if (strstr($instruction, '[bid_delete_time]')) {
            $bid_delete_time = apply_filters('bid_delete_time', '---', $bids_data);
            $instruction = str_replace('[bid_delete_time]', $bid_delete_time, $instruction);
        }

        if (!isset($direction->id)) {
            $direction = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}directions WHERE id = %d", $bids_data->direction_id));
        }

        if (isset($direction->id)) {
            $com_box_sum1 = is_isset($direction, 'com_box_sum1');
            $com_box_pers1 = is_isset($direction, 'com_box_pers1');
            $com_box_sum2 = is_isset($direction, 'com_box_sum2');
            $com_box_pers2 = is_isset($direction, 'com_box_pers2');
            $bc_step = 0;

            $dcombysum_exists = apply_filters('db_constructs_itemtype', '', 'dcombysum');
            if ($dcombysum_exists) {
                $cc = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}constructs WHERE itemtype = 'dcombysum' AND item_id = %s", $bids_data->direction_id));
                if ($cc) {
                    $dcombysum_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}constructs WHERE itemtype = 'dcombysum' AND item_id = %s AND (%s -0.0) >= amount ORDER BY (amount -0.0) DESC", $bids_data->direction_id, is_sum(is_isset($bids_data, 'sum1'))));
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

            $instruction = str_replace(array_keys($replacements), array_values($replacements), $instruction);
        }

    }

    if (isset($vd1->id, $vd2->id)) {

        $instruction = str_replace('[psys_give]', pn_strip_input(ctv_ml($vd1->psys_title)), $instruction);
        $instruction = str_replace('[psys_get]', pn_strip_input(ctv_ml($vd2->psys_title)), $instruction);
        $instruction = str_replace('[currency_code_give]', pn_strip_input($vd1->currency_code_title), $instruction);
        $instruction = str_replace('[currency_code_get]', pn_strip_input($vd2->currency_code_title), $instruction);

    }

    return $instruction;
}

add_filter('bid_delete_time', 'def_bid_delete_time', 10, 2);
function def_bid_delete_time($bid_delete_time, $bids_data) {

    if ('auto' == $bids_data->status) {
        $createdate = $bids_data->create_date;
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $createtime = strtotime($createdate);
        $count_minute = get_logs_sett('delete_auto_bids');
        if (!$count_minute) {
            $count_minute = 15;
        }
        $second = $count_minute * 60;
        $del_time = $createtime + $second;
        $bid_delete_time = date("{$date_format}, H:i:s", $del_time);
    }

    return $bid_delete_time;
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'functions');
$premiumbox->include_path(__FILE__, 'table1');
$premiumbox->include_path(__FILE__, 'table2');
$premiumbox->include_path(__FILE__, 'table3');
$premiumbox->include_path(__FILE__, 'table100');
$premiumbox->include_path(__FILE__, 'widget');
$premiumbox->include_path(__FILE__, 'list');
$premiumbox->include_path(__FILE__, 'add');
$premiumbox->include_path(__FILE__, 'sort');
$premiumbox->include_path(__FILE__, 'temps');
$premiumbox->include_path(__FILE__, 'add_cf');
$premiumbox->include_path(__FILE__, 'list_cf');
$premiumbox->include_path(__FILE__, 'sort_cf');
$premiumbox->include_path(__FILE__, 'maps');
$premiumbox->include_path(__FILE__, 'seo');