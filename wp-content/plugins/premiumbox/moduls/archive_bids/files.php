<?php
if (!defined('ABSPATH')) exit();

add_filter('list_stat_userxch', 'archive_bids_list_stat_userxch', 10);
function archive_bids_list_stat_userxch($list_stat_userxch) {
    global $wpdb, $premiumbox;

    $show_files = intval($premiumbox->get_option('archivebids', 'loadhistory'));
    if (1 == $show_files) {

        $list_stat_userxch['archive'] = array(
            'title' => __('Download operations archive', 'pn'),
            'content' => '<a href="' . get_request_link('archivebids', 'html', get_locale()) . '" target="_blank">' . __('Download', 'pn') . '</a>',
        );

    }

    return $list_stat_userxch;
}

add_filter('list_stat_paccount', 'archive_list_stat_paccount', 10);
function archive_list_stat_paccount($list_stat_userxch) {
    global $wpdb, $premiumbox;

    $show_files = intval($premiumbox->get_option('archivebids', 'loadhistory'));
    if (1 == $show_files) {

        $list_stat_userxch['archive'] = array(
            'title' => __('Download operations archive', 'pn'),
            'content' => '<a href="' . get_request_link('archivepbids', 'html', get_locale()) . '" target="_blank">' . __('Download', 'pn') . '</a>',
        );

    }

    return $list_stat_userxch;
}

add_filter('pntable_columns_all_users', 'archive_pntable_columns_all_users');
add_filter('pntable_columns_pn_pexch', 'archive_pntable_columns_all_users');
function archive_pntable_columns_all_users($columns) {

    if (current_user_can('administrator') or current_user_can('pn_archive')) {
        $columns['archive'] = __('Archived orders', 'pn');
    }

    return $columns;
}

add_filter('pntable_column_all_users', 'archive_pntable_column_all_users', 10000, 3);
function archive_pntable_column_all_users($empty, $column_name, $item) {

    if ('archive' == $column_name) {
        return '<a href="' . get_request_link('archivebids', 'html', get_locale(), array('user_id' => $item->ID)) . '" class="button" target="_blank">' . __('Download', 'pn') . '</a>';
    }

    return $empty;
}

add_filter('pntable_column_pn_pexch', 'archive_pntable_column_pn_pexch', 10000, 3);
function archive_pntable_column_pn_pexch($empty, $column_name, $item) {

    if ('archive' == $column_name) {
        return '<a href="' . get_request_link('archivepbids', 'html', get_locale(), array('user_id' => $item->ref_id)) . '" class="button" target="_blank">' . __('Download', 'pn') . '</a>';
    }

    return $empty;
}

add_action('premium_request_archivebids', 'def_premium_archivebids');
function def_premium_archivebids() {
    global $wpdb, $premiumbox;

    $premiumbox->up_mode('get');

    $ui = wp_get_current_user();
    $user_id = intval($ui->ID);

    $loadhistory = intval($premiumbox->get_option('archivebids', 'loadhistory'));
    if ($user_id and 1 == $loadhistory or current_user_can('administrator') or current_user_can('pn_archive')) {

        $path = $premiumbox->upload_dir . '/';
        $file = $path . 'archive-' . $user_id . '-' . date('Y-m-d-H-i') . '.csv';
        $fs = @fopen($file, 'w');

        $where = '';
        if (current_user_can('administrator')) {
            $now_user_id = intval(is_param_get('user_id'));
            if ($now_user_id > 0) {
                $user_id = $now_user_id;
                $where .= " AND user_id = '$user_id'";
            }
        } else {
            $where .= " AND user_id = '$user_id'";
        }

        $datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "archive_exchange_bids WHERE id > 0 $where ORDER BY id DESC");

        $headers = [
            __('ID', 'pn'),
            __('Date', 'pn'),
            __('Rate', 'pn'),
            __('Send', 'pn'),
            __('Receive', 'pn'),
            __('Status', 'pn'),
            __('User IP', 'pn'),
            __('Referral ID', 'pn'),
            sprintf('%s (%s)', __('Partner earned', 'pn'), cur_type()),
        ];
        $content = get_cptgn(implode(';', $headers)) . "\n";

        $date_format = get_option('date_format');
        $time_format = get_option('time_format');

        if (is_array($datas)) {
            foreach ($datas as $item) {

                $arch = get_archive_info($item);

                $line = $item->bid_id . ';';
                $line .= get_pn_time($item->create_date, "{$date_format}, {$time_format}") . ';';
                $line .= sprintf('%s %s = %s %s;', is_sum(is_isset($arch, 'course_give')), is_site_value(is_isset($arch, 'currency_code_give')), is_sum(is_isset($arch, 'course_get')), is_site_value(is_isset($arch, 'currency_code_get')));
                $line .= sprintf('%s %s %s;', is_sum(is_isset($arch, 'sum1dc')), get_cptgn(pn_strip_input(ctv_ml(is_isset($arch, 'psys_give')))), is_site_value(is_isset($arch, 'currency_code_give')));
                $line .= sprintf('%s %s %s;', is_sum(is_isset($arch, 'sum2c')), get_cptgn(pn_strip_input(ctv_ml(is_isset($arch, 'psys_get')))), is_site_value(is_isset($arch, 'currency_code_get')));
                $line .= get_cptgn(get_bid_status($item->status)) . ';';
                $line .= (is_isset($arch, 'user_ip') ?: is_isset($item, 'user_ip')) . ';';
                $line .= (is_isset($arch, 'ref_id') ?: is_isset($item, 'ref_id')) . ';';
                $line .= rep_dot(get_cptgn(is_sum(is_isset($arch, 'partner_sum') ?: is_isset($item, 'partner_sum')))) . ';';
                $line .= "\n";

                $content .= $line;

            }
        }

        @fwrite($fs, $content);
        @fclose($fs);

        pn_download_file($file, basename($file), 1);
        pn_display_mess(__('Error! Unable to create file!', 'pn'));

    }
}

add_action('premium_request_archivepbids', 'def_premium_archivepbids');
function def_premium_archivepbids() {
    global $wpdb, $premiumbox;

    $premiumbox->up_mode('get');

    $ui = wp_get_current_user();
    $user_id = intval($ui->ID);

    $loadhistory = intval($premiumbox->get_option('archivebids', 'loadhistory'));
    if ($user_id and 1 == $loadhistory or current_user_can('administrator') or current_user_can('pn_archive')) {

        $path = $premiumbox->upload_dir . '/';
        $file = $path . 'archive-' . $user_id . '-' . date('Y-m-d-H-i') . '.csv';
        $fs = @fopen($file, 'w');

        $content = '';

        $where = '';
        if (current_user_can('administrator')) {
            $now_user_id = intval(is_param_get('user_id'));
            if ($now_user_id > 0) {
                $user_id = $now_user_id;
                $where .= " AND ref_id = '$user_id'";
            }
        } else {
            $where .= " AND ref_id = '$user_id'";
        }

        $datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "archive_exchange_bids WHERE status = 'success' $where ORDER BY create_date DESC");

        $content = get_cptgn(__('ID', 'pn') . ';' . __('Date', 'pn') . ';' . __('User', 'pn') . ';' . __('Reward', 'pn') . ';');
        $content .= "\n";

        $date_format = get_option('date_format');
        $time_format = get_option('time_format');

        if (is_array($datas)) {
            foreach ($datas as $item) {

                $arch = get_archive_info($item);

                $pcalc = intval(is_isset($arch, 'pcalc'));
                if ($pcalc > 0) {

                    $line = '';
                    $line .= $item->bid_id . ';';
                    $line .= get_pn_time($item->create_date, "{$date_format}, {$time_format}") . ';';
                    $uid = $item->user_id;
                    if ($uid > 0) {
                        $user = is_user(is_isset($arch, 'user_login'));
                    } else {
                        $user = get_cptgn(__('Guest', 'pn'));
                    }
                    $line .= $user . ';';
                    $line .= is_sum(is_isset($arch, 'partner_sum')) . ' ' . cur_type() . ';';
                    $line .= "\n";

                    $content .= $line;

                }

            }
        }

        @fwrite($fs, $content);
        @fclose($fs);

        pn_download_file($file, basename($file), 1);
        pn_display_mess(__('Error! Unable to create file!', 'pn'));

    }
}
