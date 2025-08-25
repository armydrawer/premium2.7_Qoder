<?php
if (!defined('ABSPATH')) exit();

if (is_admin()) {

    add_filter('pn_adminpage_title_pn_bestchangeapi', 'pn_adminpage_title_pn_bestchangeapi');
    function pn_adminpage_title_pn_bestchangeapi($title) {
        return __('BestChange parser', 'pn');
    }

    add_action('pn_adminpage_content_pn_bestchangeapi', 'def_adminpage_content_pn_bestchangeapi');
    function def_adminpage_content_pn_bestchangeapi() {
        global $premiumbox, $wpdb;

        $form = new PremiumForm();

        $options = array();
        $options['top_title'] = array(
            'view' => 'h3',
            'title' => __('Settings', 'pn'),
            'submit' => __('Save', 'pn'),
        );
        $options['BASE_URL'] = [
            'view' => 'inputbig',
            'title' => __('Domain', 'pn'),
            'default' => $premiumbox->get_option('bestchangeapi', 'BASE_URL'),
            'name' => 'BASE_URL',
        ];

        $options['BASE_URL_help'] = [
            'view' => 'help',
            'title' => __('Example', 'pn'),
            'default' => 'https://www.bestchange.app/, https://mirror1.bestchange.app/, https://mirror2.bestchange.app/',
        ];

        $options['api_key'] = array(
            'view' => 'inputbig',
            'title' => __('Api Key', 'pn'),
            'default' => $premiumbox->get_option('bestchangeapi', 'api_key'),
            'name' => 'api_key',
        );
        $options['timeout'] = array(
            'view' => 'input',
            'title' => __('Timeout (sec.)', 'pn'),
            'default' => $premiumbox->get_option('bestchangeapi', 'timeout'),
            'name' => 'timeout',
        );
        $options['timeout_help'] = array(
            'view' => 'help',
            'title' => __('More info', 'pn'),
            'default' => __('Timeout is the period when the website awaits a response from a third-party service. If no response is received in the preset period, the website will continue running without response. If the duration is not specified or is 0, the standard 20-second timeout is applied. There is no universal value for the timeout, since it depends on the operation speed of a specific service.', 'pn'),
        );
        $options['lang'] = array(
            'view' => 'select',
            'title' => __('Site version', 'pn'),
            'default' => $premiumbox->get_option('bestchangeapi', 'lang'),
            'options' => array('0' => 'ru', '1' => 'en'),
            'name' => 'lang',
        );
        $options['log'] = array(
            'view' => 'select',
            'title' => __('Logging', 'pn'),
            'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
            'default' => $premiumbox->get_option('bestchangeapi', 'log'),
            'name' => 'log',
        );
        $options['checkposition'] = array(
            'view' => 'select',
            'title' => __('Position', 'pn'),
            'default' => $premiumbox->get_option('bestchangeapi', 'checkposition'),
            'options' => array('0' => 'Rate', '1' => 'Rankrate'),
            'name' => 'checkposition',
        );
        $options['hideid'] = array(
            'view' => 'textarea',
            'title' => __('Black list of exchangers ID (separate coma)', 'pn'),
            'default' => $premiumbox->get_option('bestchangeapi', 'hideid'),
            'name' => 'hideid',
            'rows' => 5,
            'atts' => array('autocomplete' => 'off'),
        );
        $options['onlyid'] = array(
            'view' => 'textarea',
            'title' => __('White list of exchangers ID (separate coma)', 'pn'),
            'default' => $premiumbox->get_option('bestchangeapi', 'onlyid'),
            'name' => 'onlyid',
            'rows' => 5,
            'atts' => array('autocomplete' => 'off'),
        );
        $options['delold'] = array(
            'view' => 'select',
            'title' => __('Delete old data', 'pn'),
            'default' => $premiumbox->get_option('bestchangeapi', 'delold'),
            'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
            'name' => 'delold',
        );
        $options['secury'] = array(
            'view' => 'select',
            'title' => __('Disable security', 'pn'),
            'default' => $premiumbox->get_option('bestchangeapi', 'secury'),
            'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
            'name' => 'secury',
        );
        $params_form = array(
            'filter' => 'pn_bestchangeapi_options',
            'form_link' => pn_link('pn_bestchangeapi_save', 'post'),
            'button_title' => __('Save', 'pn'),
        );
        $form->init_form($params_form, $options);

        $options = array();
        $options['top_title'] = array(
            'view' => 'h3',
            'title' => '',
            'submit' => __('Save', 'pn'),
        );

        $options['bestchangeapi_settings'] = array(
            'view' => 'user_func',
            'name' => 'bestchangeapi_settings',
            'func_data' => array(),
            'func' => '_bestchangeapi_settings_init',
        );

        $params_form = array(
            'filter' => 'pn_bestchangeapi',
            'form_link' => pn_link('pn_bestchangeapi', 'post'),
            'button_title' => __('Save', 'pn'),
        );
        $form->init_form($params_form, $options);

    }

    function _bestchangeapi_settings_init($data) {
        global $wpdb, $premiumbox;

        $BASE_URL = pn_strip_input($premiumbox->get_option('bestchangeapi', 'BASE_URL'));
        $api_key = pn_strip_input($premiumbox->get_option('bestchangeapi', 'api_key'));
        $timeout = intval($premiumbox->get_option('bestchangeapi', 'timeout'));
        $class = new BestChangeAPI($api_key, $timeout, $BASE_URL);
        ?>
        <div class="premium_standart_line">
            <div class="premium_stline_left">
                <div class="premium_stline_left_ins"><?php _e('select currencies', 'pn'); ?></div>
            </div>
            <div class="premium_stline_right">
                <div class="premium_stline_right_ins">
                    <div class="premium_wrap_standart">
                        <?php
                        $in_w = array();
                        $works = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bestchangeapi_currency_codes");
                        foreach ($works as $work) {
                            $in_w[$work->currency_code_id] = $work->currency_code_id;
                        }

                        $scroll_lists = array();
                        $new_lists = array();
                        $items = $class->get_currencies();

                        if (isset($items['currencies']) and is_array($items['currencies'])) {
                            foreach ($items['currencies'] as $item) {
                                $new_lists[intval($item['id'])] = pn_strip_input($item['name']) . ' [' . intval($item['id']) . ']';
                            }
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
                        $in_w = array();
                        $works = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bestchangeapi_cities");
                        foreach ($works as $work) {
                            $in_w[$work->city_id] = $work->city_id;
                        }

                        $scroll_lists = array();
                        $new_lists = array();
                        $items = $class->get_cities();

                        if (isset($items['cities']) and is_array($items['cities'])) {
                            foreach ($items['cities'] as $item) {
                                $new_lists[intval($item['id'])] = pn_strip_input($item['name']) . ' [' . intval($item['id']) . ']';
                            }
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

add_action('premium_action_pn_bestchangeapi_save', 'def_premium_action_pn_bestchangeapi_save');
function def_premium_action_pn_bestchangeapi_save() {
    global $premiumbox;

    _method('post');

    $form = new PremiumForm();
    $form->send_header();

    pn_only_caps(array('administrator', 'pn_bestchangeapi'));

    $premiumbox->update_option('bestchangeapi', 'checkposition', intval(is_param_post('checkposition')));

    $premiumbox->update_option('bestchangeapi', 'hideid', pn_strip_input(is_param_post('hideid')));
    $premiumbox->update_option('bestchangeapi', 'onlyid', pn_strip_input(is_param_post('onlyid')));

    $premiumbox->update_option('bestchangeapi', 'BASE_URL', pn_strip_input(is_param_post('BASE_URL')));
    $premiumbox->update_option('bestchangeapi', 'api_key', pn_strip_input(is_param_post('api_key')));
    $premiumbox->update_option('bestchangeapi', 'type', intval(is_param_post('type')));

    $premiumbox->update_option('bestchangeapi', 'log', intval(is_param_post('log')));

    $premiumbox->update_option('bestchangeapi', 'lang', intval(is_param_post('lang')));
    $premiumbox->update_option('bestchangeapi', 'timeout', intval(is_param_post('timeout')));

    $premiumbox->update_option('bestchangeapi', 'delold', intval(is_param_post('delold')));
    $premiumbox->update_option('bestchangeapi', 'secury', intval(is_param_post('secury')));

    $back_url = is_param_post('_wp_http_referer');
    $back_url .= '&reply=true';

    $form->answer_form($back_url);

}

add_action('premium_action_pn_bestchangeapi', 'def_premium_action_pn_bestchangeapi');
function def_premium_action_pn_bestchangeapi() {
    global $wpdb, $premiumbox;

    _method('post');

    $form = new PremiumForm();
    $form->send_header();

    pn_only_caps(array('administrator', 'pn_bestchangeapi'));

    $BASE_URL = pn_strip_input($premiumbox->get_option('bestchangeapi', 'BASE_URL'));
    $api_key = pn_strip_input($premiumbox->get_option('bestchangeapi', 'api_key'));
    $timeout = intval($premiumbox->get_option('bestchangeapi', 'timeout'));
    $class = new BestChangeAPI($api_key, $timeout, $BASE_URL);

    $pars = is_param_post('pars');
    if (!is_array($pars)) {
        $pars = array();
    }
    $wpdb->query("DELETE FROM " . $wpdb->prefix . "bestchangeapi_currency_codes");

    $items = $class->get_currencies();
    if (isset($items['currencies']) and is_array($items['currencies'])) {
        foreach ($items['currencies'] as $item) {
            $id = intval($item['id']);
            $name = pn_strip_input($item['name']);
            if (in_array($id, $pars)) {
                $arr = array();
                $arr['currency_code_id'] = $id;
                $arr['currency_code_title'] = $name;
                $wpdb->insert($wpdb->prefix . "bestchangeapi_currency_codes", $arr);
            }
        }
    }

    $city = is_param_post('city');
    if (!is_array($city)) {
        $city = array();
    }
    $wpdb->query("DELETE FROM " . $wpdb->prefix . "bestchangeapi_cities");

    $items = $class->get_cities();
    if (isset($items['cities']) and is_array($items['cities'])) {
        foreach ($items['cities'] as $item) {
            $id = intval($item['id']);
            $name = pn_strip_input($item['name']);
            if (in_array($id, $city)) {
                $arr = array();
                $arr['city_id'] = $id;
                $arr['city_title'] = $name;
                $wpdb->insert($wpdb->prefix . "bestchangeapi_cities", $arr);
            }
        }
    }

    $back_url = is_param_post('_wp_http_referer');
    $back_url .= '&reply=true';

    $form->answer_form($back_url);

} 	