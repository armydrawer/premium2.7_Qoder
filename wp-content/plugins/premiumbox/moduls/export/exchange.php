<?php

if (!defined('ABSPATH')) exit();

if (is_admin()) {

    add_filter('pn_adminpage_title_pn_export_exchange', 'pn_admin_title_pn_export_exchange');
    function pn_admin_title_pn_export_exchange($page) {

        return __('Exchanges export', 'pn');
    }

    add_action('pn_adminpage_content_pn_export_exchange', 'def_pn_admin_content_pn_export_exchange');
    function def_pn_admin_content_pn_export_exchange() {
        global $wpdb;
        ?>
        <div class="premium_body">
            <form method="post" target="_blank" action="<?php the_pn_link('export_exchange', 'post'); ?>">
                <div class="premium_standart_line">
                    <div class="premium_stline_left">
                        <div class="premium_stline_left_ins"><?php _e('Start date', 'pn'); ?></div>
                    </div>
                    <div class="premium_stline_right">
                        <div class="premium_stline_right_ins">
                            <div class="premium_wrap_standart">
                                <input type="text" name="date1" class="js_datepicker" autocomplete="off" value=""/>
                                <div class="premium_clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="premium_clear"></div>
                </div>
                <div class="premium_standart_line">
                    <div class="premium_stline_left">
                        <div class="premium_stline_left_ins"><?php _e('End date', 'pn'); ?></div>
                    </div>
                    <div class="premium_stline_right">
                        <div class="premium_stline_right_ins">
                            <div class="premium_wrap_standart">
                                <input type="text" name="date2" class="js_datepicker" autocomplete="off" value=""/>
                                <div class="premium_clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="premium_clear"></div>
                </div>
                <?php
                $currencies = list_currency(__('All currency', 'pn'));
                ?>
                <div class="premium_standart_line">
                    <div class="premium_stline_left">
                        <div class="premium_stline_left_ins"><?php _e('Currency Send', 'pn'); ?></div>
                    </div>
                    <div class="premium_stline_right">
                        <div class="premium_stline_right_ins">
                            <div class="premium_wrap_standart">
                                <select name="currency_id_give" autocomplete="off">
                                    <?php foreach ($currencies as $currency_id => $currency_title) { ?>
                                        <option value="<?php echo $currency_id; ?>"><?php echo $currency_title; ?></option>
                                    <?php } ?>
                                </select>
                                <div class="premium_clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="premium_clear"></div>
                </div>
                <div class="premium_standart_line">
                    <div class="premium_stline_left">
                        <div class="premium_stline_left_ins"><?php _e('Currency Receive', 'pn'); ?></div>
                    </div>
                    <div class="premium_stline_right">
                        <div class="premium_stline_right_ins">
                            <div class="premium_wrap_standart">
                                <select name="currency_id_get" autocomplete="off">
                                    <?php foreach ($currencies as $currency_id => $currency_title) { ?>
                                        <option value="<?php echo $currency_id; ?>"><?php echo $currency_title; ?></option>
                                    <?php } ?>
                                </select>
                                <div class="premium_clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="premium_clear"></div>
                </div>
                <div class="premium_standart_line">
                    <div class="premium_stline_left">
                        <div class="premium_stline_left_ins"><?php _e('Select data', 'pn'); ?></div>
                    </div>
                    <div class="premium_stline_right">
                        <div class="premium_stline_right_ins">
                            <div class="premium_wrap_standart">
                                <?php
                                $scroll_lists = array();

                                $array = array(
                                        'id' => __('Identifier', 'pn'),
                                        'create_date' => __('Date', 'pn'),
                                        'edit_date' => __('Edit date', 'pn'),
                                        'cgive' => __('Currency Send', 'pn'),
                                        'cget' => __('Currency Receive', 'pn'),
                                        'course_give' => __('Rate Send', 'pn'),
                                        'course_get' => __('Rate Receive', 'pn'),
                                        'sum1' => __('Amount To send', 'pn'),
                                        'dop_com1' => __('Add. fees amount Send', 'pn'),
                                        'sum1dc' => __('Amount Send with add. fees', 'pn'),
                                        'com_ps1' => __('PS fees Send', 'pn'),
                                        'sum1c' => __('Amount Send with add. fees and PS fees', 'pn'),
                                        'sum1r' => __('Amount Send for reserve', 'pn'),
                                        'sum2t' => __('Amount at the Exchange Rate', 'pn'),
                                        'sum2' => __('Amount (discount included)', 'pn'),
                                        'dop_com2' => __('Add. fees amount Receive', 'pn'),
                                        'sum2dc' => __('Amount Receive with add. fees', 'pn'),
                                        'com_ps2' => __('PS fees Receive', 'pn'),
                                        'sum2c' => __('Amount Receive with add. fees and PS fees', 'pn'),
                                        'sum2r' => __('Amount Receive for reserve', 'pn'),
                                        'exsum' => __('Amount in internal currency needed for exhange', 'pn'),
                                        'profit' => __('Profit', 'pn'),
                                        'account_give' => __('Account To send', 'pn'),
                                        'account_get' => __('Account To receive', 'pn'),
                                        'to_account' => __('Merchant account', 'pn'),
                                        'from_account' => __('Automatic payout account', 'pn'),
                                        'txid_in' => __('Merchant txid', 'pn'),
                                        'txid_out' => __('Auto payout txid', 'pn'),
                                        'trans_in' => __('Merchant transaction ID', 'pn'),
                                        'trans_out' => __('Auto payout transaction ID', 'pn'),
                                        'last_name' => __('Last name', 'pn'),
                                        'first_name' => __('First name', 'pn'),
                                        'second_name' => __('Second name', 'pn'),
                                        'user_email' => __('E-mail', 'pn'),
                                        'user_phone' => __('Mobile phone number', 'pn'),
                                        'user_telegram' => __('Telegram', 'pn'),
                                        'user_skype' => __('Skype', 'pn'),
                                        'user' => __('User', 'pn'),
                                        'user_discount' => __('User discount', 'pn'),
                                        'user_discount_sum' => __('User discount amount', 'pn'),
                                        'user_ip' => __('User IP', 'pn'),
                                        'hash' => __('Hash', 'pn'),
                                        'link' => __('Link', 'pn'),
                                        'status' => __('Status', 'pn'),
                                        'locale' => __('Language', 'pn'),
                                        'm_in' => __('Merchant', 'pn'),
                                        'm_out' => __('Automatic payout', 'pn'),
                                        'ref_id' => __('Referral ID', 'pn'),
                                        'partner_sum' => sprintf('%s (%s)', __('Partner earned', 'pn'), cur_type()),
                                );
                                $array = apply_filters('list_export_bids', $array);
                                foreach ($array as $key => $val) {
                                    $checked = 0;
                                    $scroll_lists[] = array(
                                            'title' => $val,
                                            'checked' => $checked,
                                            'value' => $key,
                                    );
                                }
                                echo get_check_list($scroll_lists, 'data[]', '', '', 1);
                                ?>
                                <div class="premium_clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="premium_clear"></div>
                </div>
                <div class="premium_standart_line">
                    <div class="premium_stline_left">
                        <div class="premium_stline_left_ins"><?php _e('Bid status', 'pn'); ?></div>
                    </div>
                    <div class="premium_stline_right">
                        <div class="premium_stline_right_ins">
                            <div class="premium_wrap_standart">
                                <?php
                                $scroll_lists = array();

                                $bid_status_list = list_bid_status();
                                foreach ($bid_status_list as $key => $val) {
                                    $checked = 0;
                                    $scroll_lists[] = array(
                                            'title' => $val,
                                            'checked' => $checked,
                                            'value' => $key,
                                    );
                                }
                                echo get_check_list($scroll_lists, 'bs[]', '', '', 1);
                                ?>
                                <div class="premium_clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="premium_clear"></div>
                </div>
                <div class="premium_standart_line">
                    <div class="premium_stline_left"></div>
                    <div class="premium_stline_right">
                        <div class="premium_stline_right_ins">
                            <div class="premium_wrap_standart">
                                <input type="submit" name="" class="button" value="<?php _e('Download', 'pn'); ?>"/>
                                <div class="premium_clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="premium_clear"></div>
                </div>
            </form>
        </div>
        <?php
    }

}

add_action('premium_action_export_exchange', 'def_premium_action_export_exchange');
function def_premium_action_export_exchange() {
    global $wpdb, $premiumbox;

    pn_only_caps(array('administrator', 'pn_export_exchange'));

    $where = '';
    $datestart = is_pn_date(is_param_post('date1'));
    if ($datestart) {
        $dstart = get_pn_time($datestart, 'Y-m-d H:i:s');
        $where .= " AND edit_date >= '$dstart'";
    }

    $dateend = is_pn_date(is_param_post('date2'));
    if ($dateend) {
        $dend = get_pn_time($dateend, 'Y-m-d H:i:s');
        $where .= " AND edit_date <= '$dend'";
    }

    $currency_id_give = intval(is_param_post('currency_id_give'));
    if ($currency_id_give > 0) {
        $where .= " AND currency_id_give = '$currency_id_give'";
    }

    $currency_id_get = intval(is_param_post('currency_id_get'));
    if ($currency_id_get > 0) {
        $where .= " AND currency_id_get = '$currency_id_get'";
    }

    $bs = is_param_post('bs');
    $in = create_data_for_db($bs, 'status');
    if ($in) {
        $where .= " AND status IN($in)";
    }

    $path = $premiumbox->upload_dir . '/';

    $file = $path . 'bidsexport-' . date('Y-m-d-H-i') . '.csv';
    $fs = @fopen($file, 'w');

    $items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status != 'auto' $where ORDER BY id DESC");

    $data = is_param_post('data');

    $content = '';

    $array = array(
            'id' => __('Identifier', 'pn'),
            'create_date' => __('Date', 'pn'),
            'edit_date' => __('Edit date', 'pn'),
            'cgive' => __('Currency Send', 'pn'),
            'cget' => __('Currency Receive', 'pn'),
            'course_give' => __('Rate Send', 'pn'),
            'course_get' => __('Rate Receive', 'pn'),
            'sum1' => __('Amount To send', 'pn'),
            'dop_com1' => __('Add. fees amount Send', 'pn'),
            'sum1dc' => __('Amount Send with add. fees', 'pn'),
            'com_ps1' => __('PS fees Send', 'pn'),
            'sum1c' => __('Amount Send with add. fees and PS fees', 'pn'),
            'sum1r' => __('Amount Send for reserve', 'pn'),
            'sum2t' => __('Amount at the Exchange Rate', 'pn'),
            'sum2' => __('Amount (discount included)', 'pn'),
            'dop_com2' => __('Add. fees amount Receive', 'pn'),
            'sum2dc' => __('Amount Receive with add. fees', 'pn'),
            'com_ps2' => __('PS fees Receive', 'pn'),
            'sum2c' => __('Amount Receive with add. fees and PS fees', 'pn'),
            'sum2r' => __('Amount Receive for reserve', 'pn'),
            'exsum' => __('Amount in internal currency needed for exhange', 'pn'),
            'profit' => __('Profit', 'pn'),
            'account_give' => __('Account To send', 'pn'),
            'account_get' => __('Account To receive', 'pn'),
            'to_account' => __('Merchant account', 'pn'),
            'from_account' => __('Automatic payout account', 'pn'),
            'txid_in' => __('Merchant txid', 'pn'),
            'txid_out' => __('Auto payout txid', 'pn'),
            'trans_in' => __('Merchant transaction ID', 'pn'),
            'trans_out' => __('Auto payout transaction ID', 'pn'),
            'last_name' => __('Last name', 'pn'),
            'first_name' => __('First name', 'pn'),
            'second_name' => __('Second name', 'pn'),
            'user_email' => __('E-mail', 'pn'),
            'user_phone' => __('Mobile phone number', 'pn'),
            'user_skype' => __('Skype', 'pn'),
            'user_telegram' => __('Telegram', 'pn'),
            'user' => __('User', 'pn'),
            'user_discount' => __('User discount', 'pn'),
            'user_discount_sum' => __('User discount amount', 'pn'),
            'user_ip' => __('User IP', 'pn'),
            'hash' => __('Hash', 'pn'),
            'link' => __('Link', 'pn'),
            'status' => __('Status', 'pn'),
            'locale' => __('Language', 'pn'),
            'm_in' => __('Merchant', 'pn'),
            'm_out' => __('Automatic payout', 'pn'),
            'ref_id' => __('Referral ID', 'pn'),
            'partner_sum' => sprintf('%s (%s)', __('Partner earned', 'pn'), cur_type()),
    );
    $array = apply_filters('list_export_bids', $array);

    if (is_array($data)) {

        $en = array();
        $csv_title = '';
        $csv_key = '';
        foreach ($array as $k => $v) {
            if (in_array($k, $data)) {
                $en[] = $k;
                $csv_title .= '"' . get_cptgn($v) . '";';
            }
        }

        $content .= $csv_title . "\n";

        if (count($en) > 0) {
            foreach ($items as $item) {
                $line = '';

                foreach ($en as $key) {
                    $line .= '"';

                    if ('id' == $key) {
                        $line .= $item->id;
                    } elseif ('create_date' == $key) {
                        $line .= get_pn_time($item->create_date, 'd.m.Y H:i');
                    } elseif ('edit_date' == $key) {
                        $line .= get_pn_time($item->edit_date, 'd.m.Y H:i');
                    } elseif ('cgive' == $key) {
                        $line .= get_cptgn(ctv_ml($item->psys_give) . ' ' . $item->currency_code_give);
                    } elseif ('cget' == $key) {
                        $line .= get_cptgn(ctv_ml($item->psys_get) . ' ' . $item->currency_code_get);
                    } elseif ('account_give' == $key) {
                        $line .= get_cptgn($item->account_give);
                    } elseif ('account_get' == $key) {
                        $line .= get_cptgn($item->account_get);
                    } elseif ('to_account' == $key) {
                        $line .= get_cptgn($item->to_account);
                    } elseif ('from_account' == $key) {
                        $line .= get_cptgn($item->from_account);
                    } elseif ('trans_in' == $key) {
                        $line .= get_cptgn($item->trans_in);
                    } elseif ('trans_out' == $key) {
                        $line .= get_cptgn($item->trans_out);
                    } elseif ('last_name' == $key) {
                        $line .= get_cptgn($item->last_name);
                    } elseif ('first_name' == $key) {
                        $line .= get_cptgn($item->first_name);
                    } elseif ('second_name' == $key) {
                        $line .= get_cptgn($item->second_name);
                    } elseif ('user_email' == $key) {
                        $line .= get_cptgn($item->user_email);
                    } elseif ('user_phone' == $key) {
                        $line .= get_cptgn($item->user_phone);
                    } elseif ('user_skype' == $key) {
                        $line .= get_cptgn($item->user_skype);
                    } elseif ('user_telegram' == $key) {
                        $line .= get_cptgn($item->user_telegram);
                    } elseif ('user' == $key) {
                        $line .= get_cptgn($item->user_login);
                    } elseif ('user_discount' == $key) {
                        $line .= $item->user_discount;
                    } elseif ('user_discount_sum' == $key) {
                        $line .= $item->user_discount_sum;
                    } elseif ('user_ip' == $key) {
                        $line .= get_cptgn($item->user_ip);
                    } elseif ('hash' == $key) {
                        $line .= $item->hashed;
                    } elseif ('link' == $key) {
                        $line .= get_bids_url($item->hashed);
                    } elseif ('status' == $key) {
                        $line .= get_cptgn(get_bid_status($item->status));
                    } elseif ('locale' == $key) {
                        $line .= get_lang_key($item->bid_locale);
                    } elseif (in_array($key, ['m_in', 'm_out', 'ref_id'])) {
                        $val = is_isset($item, $key) ? is_isset($item, $key) : '';
                        $line .= $val;
                    } else {
                        $line .= rep_dot(get_cptgn(is_isset($item, $key)));
                    }

                    $line .= '";';
                }

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