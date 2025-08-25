<?php
if (!defined('ABSPATH')) exit();

if (is_admin()) {

    add_filter('pn_adminpage_title_pn_bestchange', 'pn_adminpage_title_pn_bestchange');
    function pn_adminpage_title_pn_bestchange($title) {

        return __('BestChange parser', 'pn');
    }

    add_action('pn_adminpage_content_pn_bestchange', 'def_adminpage_content_pn_bestchange');
    function def_adminpage_content_pn_bestchange() {
        global $premiumbox, $wpdb;

        $data = get_option('bestchange');
        if (!is_array($data)) $data = array();

        $form = new PremiumForm();

        $options = array();
        $options['top_title'] = array(
            'view' => 'h3',
            'title' => __('Settings', 'pn'),
            'submit' => __('Save', 'pn'),
        );
        $options['server'] = array(
            'view' => 'select',
            'title' => __('Server', 'pn'),
            'default' => $premiumbox->get_option('bcbroker', 'server'),
            'options' => array('0' => 'api.bestchange.ru', '1' => 'api.bestchange.net', '2' => 'api.bestchange.com'),
            'name' => 'server',
        );
        /*$options['server_help'] = [
            'view' => 'help',
            'title' => __('Example', 'pn'),
            'default' => '0 = api.bestchange.ru, 1 = api.bestchange.net, 2 = api.bestchange.com, domain',
        ];*/

        $options['timeout'] = array(
            'view' => 'inputbig',
            'title' => __('Timeout (sec.)', 'pn'),
            'default' => $premiumbox->get_option('bcbroker', 'timeout'),
            'name' => 'timeout',
        );
        $options['timeout_help'] = array(
            'view' => 'help',
            'title' => __('More info', 'pn'),
            'default' => __('Timeout is the period when the website awaits a response from a third-party service. If no response is received in the preset period, the website will continue running without response. If the duration is not specified or is 0, the standard 20-second timeout is applied. There is no universal value for the timeout, since it depends on the operation speed of a specific service.', 'pn'),
        );

        $options['hideid'] = array(
            'view' => 'textarea',
            'title' => __('Black list of exchangers ID (separate coma)', 'pn'),
            'default' => is_isset($data, 'hideid'),
            'name' => 'hideid',
            'rows' => 5,
            'atts' => array('autocomplete' => 'off'),
        );
        $options['onlyid'] = array(
            'view' => 'textarea',
            'title' => __('White list of exchangers ID (separate coma)', 'pn'),
            'default' => is_isset($data, 'onlyid'),
            'name' => 'onlyid',
            'rows' => 5,
            'atts' => array('autocomplete' => 'off'),
        );
        $options['delold'] = array(
            'view' => 'select',
            'title' => __('Delete old data', 'pn'),
            'default' => is_isset($data, 'delold'),
            'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
            'name' => 'delold',
        );
        $options['secury'] = array(
            'view' => 'select',
            'title' => __('Disable security', 'pn'),
            'default' => is_isset($data, 'secury'),
            'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
            'name' => 'secury',
        );
        $options['type'] = array(
            'view' => 'select',
            'title' => __('Type', 'pn'),
            'default' => $premiumbox->get_option('bcbroker', 'type'),
            'options' => array('0' => 'CURL', '1' => 'FILE_GET_CONTENTS'),
            'name' => 'type',
        );


        $params_form = array(
            'filter' => 'pn_bestchange_options',
            'data' => $data,
            'form_link' => pn_link('pn_bestchange_save', 'post'),
            'button_title' => __('Save', 'pn'),
        );
        $form->init_form($params_form, $options);

        $options = array();
        $options['top_title'] = array(
            'view' => 'h3',
            'title' => '',
            'submit' => __('Save', 'pn'),
        );

        $options['bestchange_settings'] = array(
            'view' => 'user_func',
            'name' => 'bestchange_settings',
            'func_data' => array(),
            'func' => '_bestchange_settings_init',
        );

        $params_form = array(
            'filter' => 'pn_bestchange',
            'data' => $data,
            'form_link' => pn_link('pn_bestchange', 'post'),
            'button_title' => __('Save', 'pn'),
        );
        $form->init_form($params_form, $options);

    }

    function _bestchange_settings_init($data) {
        global $wpdb, $premiumbox;
        ?>
        <div class="premium_standart_line">
            <div class="premium_stline_left">
                <div class="premium_stline_left_ins"><?php _e('select currencies', 'pn'); ?></div>
            </div>
            <div class="premium_stline_right">
                <div class="premium_stline_right_ins">
                    <div class="premium_wrap_standart">
                        <?php
                        $lists = array();
                        $path = $premiumbox->upload_dir . '/bestchange/bm_cy.dat';

                        if (is_file($path)) {
                            $fdata = @file_get_contents($path);
                            $lists = explode("\n", $fdata);
                        }

                        $in_w = array();
                        $works = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bestchange_currency_codes");
                        foreach ($works as $work) {
                            $in_w[$work->currency_code_id] = $work->currency_code_id;
                        }

                        $scroll_lists = array();
                        $new_lists = array();
                        foreach ($lists as $val) {
                            $in = explode(";", $val);
                            $title = get_tgncp($in[2]) . ' (' . get_tgncp($in[3]) . ') [' . get_tgncp($in[0]) . ']';
                            $title = pn_strip_input($title);
                            $new_lists[$in[0]] = $title;
                        }

                        asort($new_lists);

                        $new_lists = list_checks_top($new_lists, $in_w);

                        foreach ($new_lists as $key => $title) {
                            $checked = 0;
                            if (in_array($key, $in_w)) {
                                $checked = 1;
                            }
                            $scroll_lists[] = array(
                                'title' => $title,
                                'checked' => $checked,
                                'value' => $key,
                            );
                        }
                        echo get_check_list($scroll_lists, 'pars[]', '', '500', 1);
                        ?>
                        <div class="premium_clear"></div>
                    </div>
                </div>
            </div>
            <div class="premium_clear"></div>
        </div>
        <div class="premium_standart_line">
            <div class="premium_stline_left">
                <div class="premium_stline_left_ins"><?php _e('select cities', 'pn'); ?></div>
            </div>
            <div class="premium_stline_right">
                <div class="premium_stline_right_ins">
                    <div class="premium_wrap_standart">
                        <?php
                        $lists = array();
                        $path = $premiumbox->upload_dir . '/bestchange/bm_cities.dat';

                        if (is_file($path)) {
                            $fdata = @file_get_contents($path);
                            $lists = explode("\n", $fdata);
                        }

                        $in_w = array();
                        $works = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bestchange_cities");
                        foreach ($works as $work) {
                            $in_w[$work->city_id] = $work->city_id;
                        }

                        $scroll_lists = array();
                        $new_lists = array();
                        foreach ($lists as $val) {
                            $in = explode(";", $val);
                            $title = get_tgncp($in[1]) . ' [' . get_tgncp($in[0]) . ']';
                            $title = pn_strip_input($title);
                            $new_lists[$in[0]] = $title;
                        }

                        asort($new_lists);

                        $new_lists = list_checks_top($new_lists, $in_w);

                        foreach ($new_lists as $key => $title) {
                            $checked = 0;
                            if (in_array($key, $in_w)) {
                                $checked = 1;
                            }
                            $scroll_lists[] = array(
                                'title' => $title,
                                'checked' => $checked,
                                'value' => $key,
                            );
                        }
                        echo get_check_list($scroll_lists, 'city[]', '', '500', 1);
                        ?>
                        <div class="premium_clear"></div>
                    </div>
                </div>
            </div>
            <div class="premium_clear"></div>
        </div>
        <?php
    }

}

add_action('premium_action_pn_bestchange_save', 'def_premium_action_pn_bestchange_save');
function def_premium_action_pn_bestchange_save() {
    global $premiumbox;

    _method('post');

    $form = new PremiumForm();
    $form->send_header();

    pn_only_caps(array('administrator', 'pn_bestchange'));

    $arr = array();
    $arr['hideid'] = pn_strip_input(is_param_post('hideid'));
    $arr['onlyid'] = pn_strip_input(is_param_post('onlyid'));
    $arr['delold'] = intval(is_param_post('delold'));
    $arr['secury'] = intval(is_param_post('secury'));
    update_option('bestchange', $arr);

    $premiumbox->update_option('bcbroker', 'server', intval(is_param_post('server')));
    $premiumbox->update_option('bcbroker', 'timeout', intval(is_param_post('timeout')));
    $premiumbox->update_option('bcbroker', 'type', intval(is_param_post('type')));

    $back_url = is_param_post('_wp_http_referer');
    $back_url .= '&reply=true';

    $form->answer_form($back_url);

}

add_action('premium_action_pn_bestchange', 'def_premium_action_pn_bestchange');
function def_premium_action_pn_bestchange() {
    global $wpdb, $premiumbox;

    _method('post');

    $form = new PremiumForm();
    $form->send_header();

    pn_only_caps(array('administrator', 'pn_bestchange'));

    $path = $premiumbox->upload_dir . '/bestchange/bm_cy.dat';
    $lists = array();
    if (is_file($path)) {
        $fdata = file_get_contents($path);
        $lists = explode("\n", $fdata);
    }

    $pars = is_param_post('pars');
    if (!is_array($pars)) {
        $pars = array();
    }
    $wpdb->query("DELETE FROM " . $wpdb->prefix . "bestchange_currency_codes");

    foreach ($lists as $val) {
        $in = explode(";", $val);
        if (in_array($in[0], $pars)) {
            $arr = array();
            $arr['currency_code_id'] = intval($in[0]);
            $arr['currency_code_title'] = pn_strip_input(get_tgncp($in[2])) . ' (' . pn_strip_input(get_tgncp($in[3])) . ')';
            $wpdb->insert($wpdb->prefix . "bestchange_currency_codes", $arr);
        }
    }

    $path = $premiumbox->upload_dir . '/bestchange/bm_cities.dat';
    $lists = array();
    if (is_file($path)) {
        $fdata = file_get_contents($path);
        $lists = explode("\n", $fdata);
    }

    $city = is_param_post('city');
    if (!is_array($city)) {
        $city = array();
    }
    $wpdb->query("DELETE FROM " . $wpdb->prefix . "bestchange_cities");

    foreach ($lists as $val) {
        $in = explode(";", $val);
        if (in_array($in[0], $city)) {
            $arr = array();
            $arr['city_id'] = intval($in[0]);
            $arr['city_title'] = pn_strip_input(get_tgncp($in[1]));
            $wpdb->insert($wpdb->prefix . "bestchange_cities", $arr);
        }
    }

    $back_url = is_param_post('_wp_http_referer');
    $back_url .= '&reply=true';

    $form->answer_form($back_url);

} 	