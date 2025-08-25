<?php
if (!defined('ABSPATH')) exit();

function amlcheck_setting_list($data, $db_data, $place) {

    $options = array();
    $options['top_title'] = array(
        'view' => 'h3',
        'title' => __('Settings', 'pn'),
        'submit' => __('Save', 'pn'),
    );
    $options['hidden_block'] = array(
        'view' => 'hidden_input',
        'name' => 'item_id',
        'default' => $db_data->id,
    );

    $options['apierror_score'] = array(
        'view' => 'input',
        'title' => __('Risk if the api dont work', 'pn'),
        'default' => is_isset($data, 'apierror_score'),
        'name' => 'apierror_score',
        'work' => 'int',
    );

    $opts = get_aml_options($db_data->ext_plugin);

    $options['addr_title'] = array(
        'view' => 'h3',
        'title' => __('Address settings', 'pn'),
        'submit' => __('Save', 'pn'),
    );
    $options['addr_max'] = array(
        'view' => 'input',
        'title' => __('Critical level of risk for address', 'pn'),
        'default' => is_isset($data, 'addr_max'),
        'name' => 'addr_max',
        'work' => 'int',
    );
    $options['addr_line'] = array(
        'view' => 'line',
    );
    foreach ($opts as $opt_name => $opt_title) {
        if ($opt_name) {
            $options['addr_max_' . $opt_name] = array(
                'view' => 'input',
                'title' => __('Max. share of risk', 'pn') . ' (' . str_replace('_', '', $opt_title) . ')',
                'default' => is_isset($data, 'addr_max_' . $opt_name),
                'name' => 'addr_max_' . $opt_name,
                'work' => 'int',
            );
        }
    }
    $options['txid_title'] = array(
        'view' => 'h3',
        'title' => __('TxID settings', 'pn'),
        'submit' => __('Save', 'pn'),
    );
    $options['txid_max'] = array(
        'view' => 'input',
        'title' => __('Critical level of risk for TxID', 'pn'),
        'default' => is_isset($data, 'txid_max'),
        'name' => 'txid_max',
        'work' => 'int',
    );
    $options['txid_line'] = array(
        'view' => 'line',
    );
    foreach ($opts as $opt_name => $opt_title) {
        if ($opt_name) {
            $options['txid_max_' . $opt_name] = array(
                'view' => 'input',
                'title' => __('Max. share of risk', 'pn') . ' (' . str_replace('_', '', $opt_title) . ')',
                'default' => is_isset($data, 'txid_max_' . $opt_name),
                'name' => 'txid_max_' . $opt_name,
                'work' => 'int',
            );
        }
    }
    $options['other_title'] = array(
        'view' => 'h3',
        'title' => __('Other settings', 'pn'),
        'submit' => __('Save', 'pn'),
    );
    $options['api_timeout'] = array(
        'view' => 'input',
        'title' => __('Waiting time for verification results (sec., max up to 25-30 sec.)', 'pn'),
        'default' => is_isset($data, 'api_timeout'),
        'name' => 'api_timeout',
        'work' => 'int',
    );
    $options['curl_timeout'] = array(
        'view' => 'input',
        'title' => __('Timeout (sec.) work script', 'pn'),
        'default' => is_isset($data, 'curl_timeout'),
        'name' => 'curl_timeout',
        'work' => 'int',
    );
    $options['curl_timeout_help'] = array(
        'view' => 'help',
        'title' => __('More info', 'pn'),
        'default' => __('Timeout is the period when the website awaits a response from a third-party service. If no response is received in the preset period, the website will continue running without response. If the duration is not specified or is 0, the standard 20-second timeout is applied. There is no universal value for the timeout, since it depends on the operation speed of a specific service.', 'pn'),
    );
    $options['disable_logs'] = array(
        'view' => 'select',
        'title' => __('Disable logs', 'pn'),
        'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
        'default' => is_isset($data, 'disable_logs'),
        'name' => 'disable_logs',
        'work' => 'int',
    );

    $options['enableip'] = array(
        'view' => 'textarea',
        'title' => __('Authorized IP (at the beginning of a new line)', 'pn'),
        'default' => is_isset($data, 'enableip'),
        'name' => 'enableip',
        'rows' => '8',
        'work' => 'text',
    );
    $options['resulturl'] = array(
        'view' => 'inputbig',
        'title' => __('Status/Result URL hash', 'pn'),
        'default' => is_isset($data, 'resulturl'),
        'name' => 'resulturl',
        'work' => 'symbols',
    );
    $options['help_resulturl'] = array(
        'view' => 'help',
        'title' => __('More info', 'pn'),
        'default' => __('We recommend to use unique hashes at least 50 characters long, and containing Latin characters and numbers in random order. Create or generate a hash. For example ImYkwGjhuWyNasq2fdQJzVvCpis8umbx. When setting up the merchant on the side of the payment system as the status address (typically, this is the Status URL or Return URL), specify the URL with already specified hash. You can find the Status/Result URL with the specified hash below.', 'pn'),
    );

    $text = '
	<div><strong>Callback URL:</strong> <a href="' . get_amlcheck_callback_url($db_data->ext_key, is_isset($data, 'resulturl')) . '" target="_blank">' . get_amlcheck_callback_url($db_data->ext_key, is_isset($data, 'resulturl')) . '</a></div>			
	';

    $options['callback_url'] = array(
        'view' => 'textfield',
        'title' => '',
        'default' => $text,
    );

    $options = apply_filters('_amlcheck_options', $options, $db_data->ext_plugin, $data, $db_data->ext_key, $place);

    return $options;
}

function get_amlcheck_callback_url($m_id, $hash) {

    $hash = trim($hash);
    $arr = array(
        'm_name' => $m_id,
    );
    if ($hash) {
        $arr['hash'] = $hash;
    }

    return get_request_link('amlcheck_callback', '', get_locale(), $arr);
}

add_filter('curl_amlcheck', 'timeout_amlcheck_curl', 10, 3);
function timeout_amlcheck_curl($ch, $m_name, $m_id) {

    $m_data = get_amlcheck_data($m_id);
    $timeout = intval(is_isset($m_data, 'curl_timeout'));
    if ($timeout > 0) {

        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

    }

    return $ch;
}

function amlcheck_sleep($m_id) {

    $m_data = get_amlcheck_data($m_id);
    $sleep = intval(is_isset($m_data, 'api_timeout'));
    if ($sleep < 5) {
        $sleep = 5;
    }
    if ($sleep > 0) {
        sleep($sleep);
    }

}

function get_amlcheck_data($m_id) {

    $data = array();
    $extandeds = get_extandeds();
    foreach ($extandeds as $ext) {
        if ('amlcheck' == $ext->ext_type and $ext->ext_key == $m_id) {
            $data = pn_json_decode($ext->ext_options);
            if (!is_array($data)) $data = array();
            $data['ext_status'] = $ext->ext_status;
        }
    }

    return $data;
}

function get_aml_options($m_name) {

    return apply_filters('get_aml_options', array(), $m_name);
}

add_action('premium_action_pn_amlcheck_testlink', 'amlcheck_testpage');
function amlcheck_testpage() {

    _method('post');

    $form = new PremiumForm();
    $form->send_header();

    pn_only_caps(array('administrator', 'pn_amlcheck'));

    $m_name = is_extension_name(is_param_get('m_name'));
    $m_id = is_extension_name(is_param_get('m_id'));
    $test_type = is_extension_name(is_param_get('test_type'));

    $res = apply_filters('amlcheck_testpage', 'no aml modul', $m_name, $m_id, $test_type);

    $form->error_form(print_r($res, true));
}

function amlcheck_checked($bid, $direction, $place) {

    $errors = array();
    $bid = (array)$bid;
    $place = trim($place);
    $amlcheck = is_extension_name($direction->amlcheck);

    if (!$amlcheck) {
        return $errors;
    }

    $options = pn_json_decode($direction->amlcheck_opts);
    if (!is_array($options)) $options = array();

    // 0 - no
    // 1 - Да, во время создания заявки / кроме merch
    // 2 - Да, при оплате
    // 3 - Да, перед автовыплатой
    $give = intval(is_isset($options, 'give')); // 0, 1, 2, 3
    $get = intval(is_isset($options, 'get')); // 0, 1, 2, 3
    $merch = intval(is_isset($options, 'merch')); // 0, 1, 2

    $give_error = intval(is_isset($options, 'give_error')); // 0 - nothing, 1 - error
    $get_error = intval(is_isset($options, 'get_error')); // 0 - nothing, 1 - error
    $merch_error = intval(is_isset($options, 'merch_error')); // 0 - nothing, 1 - error

    $aml_give = pn_json_decode(is_isset($bid, 'aml_give'));
    if (!is_array($aml_give)) $aml_give = array();

    $aml_get = pn_json_decode(is_isset($bid, 'aml_get'));
    if (!is_array($aml_get)) $aml_get = array();

    $aml_merch = pn_json_decode(is_isset($bid, 'aml_merch'));
    if (!is_array($aml_merch)) $aml_merch = array();

    $m_name = get_ext_plugin($amlcheck, 'amlcheck');
    $m_data = get_amlcheck_data($amlcheck);
    $m_status = intval(is_isset($m_data, 'ext_status'));

    if (!$m_name || !$m_status) {
        return $errors;
    }

    $opts = get_aml_options($m_name);
    $addr_max = is_sum(is_isset($m_data, 'addr_max'));
    $txid_max = is_sum(is_isset($m_data, 'txid_max'));

    if (
        1 == $give and 1 == $give_error and 'error' == $place or
        2 == $give and 1 == $give_error and 'payed' == $place or
        3 == $give and 1 == $give_error and 'beforepayout' == $place
    ) {
        $now = $aml_give;
        $status = intval(is_isset($now, 'status'));
        if (0 == $status && isset($now['status'])) {
            $errors['give'] = 'AML module API error!';
        }
        if (2 == $status) {
            $errors['give'] = __('AML module API status pending!', 'pn');
        }
        if (3 == $status or 1 == $status) {
            $score = is_sum(is_isset($now, 'score'));
            $signals = is_isset($now, 'signals');
            if ($score >= $addr_max and $score > 0) {
                $errors['give'] = sprintf(__('address has a negative rating (%s)', 'pn'), $score . '%');
            }
            foreach ($opts as $opt_name => $opt_title) {
                if ($opt_name) {
                    $en_score = is_sum(is_isset($m_data, 'addr_max_' . $opt_name));
                    $n_score = is_sum(is_isset($signals, $opt_name));
                    $n_score = is_sum($n_score * 100);
                    if ($n_score >= $en_score and $en_score > 0) {
                        $errors['give'] = sprintf(__('address has a negative rating (%s)', 'pn'), $opt_title . ':' . $n_score . '%');
                    }
                }
            }
        }
    }

    if (
        1 == $get and 1 == $get_error and 'error' == $place or
        2 == $get and 1 == $get_error and 'payed' == $place or
        3 == $get and 1 == $get_error and 'beforepayout' == $place
    ) {
        $now = $aml_get;
        $status = intval(is_isset($now, 'status'));
        if (0 == $status && isset($now['status'])) {
            $errors['get'] = 'AML module API error!';
        }
        if (2 == $status) {
            $errors['get'] = __('AML module API status pending!', 'pn');
        }
        if (3 == $status or 1 == $status) {
            $score = is_sum(is_isset($now, 'score'));
            $signals = is_isset($now, 'signals');
            if ($score >= $addr_max and $score > 0) {
                $errors['get'] = sprintf(__('address has a negative rating (%s)', 'pn'), $score . '%');
            }
            foreach ($opts as $opt_name => $opt_title) {
                if ($opt_name) {
                    $en_score = is_sum(is_isset($m_data, 'addr_max_' . $opt_name));
                    $n_score = is_sum(is_isset($signals, $opt_name));
                    $n_score = is_sum($n_score * 100);
                    if ($n_score >= $en_score and $en_score > 0) {
                        $errors['get'] = sprintf(__('address has a negative rating (%s)', 'pn'), $opt_title . ':' . $n_score . '%');
                    }
                }
            }
        }
    }

    if (
        1 == $merch and 1 == $merch_error and 'payed' == $place or
        2 == $merch and 1 == $merch_error and 'beforepayout' == $place
    ) {
        $now = $aml_merch;
        $status = intval(is_isset($now, 'status'));
        if (0 == $status && isset($now['status'])) {
            $errors['merch'] = 'AML module API error!';
        }
        if (2 == $status) {
            $errors['merch'] = __('AML module API status pending!', 'pn');
        }
        if (3 == $status or 1 == $status) {
            $score = is_sum(is_isset($now, 'score'));
            $signals = is_isset($now, 'signals');
            if ($score >= $txid_max and $score > 0) {
                $errors['merch'] = sprintf(__('txid has a negative rating (%s)', 'pn'), $score . '%');
            }
            foreach ($opts as $opt_name => $opt_title) {
                if ($opt_name) {
                    $en_score = is_sum(is_isset($m_data, 'txid_max_' . $opt_name));
                    $n_score = is_sum(is_isset($signals, $opt_name));
                    $n_score = is_sum($n_score * 100);
                    if ($n_score >= $en_score and $en_score > 0) {
                        $errors['merch'] = sprintf(__('txid has a negative rating (%s)', 'pn'), $opt_title . ':' . $n_score . '%');
                    }
                }
            }
        }
    }

    return $errors;
}

function amlcheck_data($bid, $direction, $place, $req, $next_action = '') {
    global $wpdb;

    $bid = (array)$bid;
    $bid_id = intval(is_isset($bid, 'id'));
    $place = trim($place);
    $next_action = trim($next_action);
    $req = intval($req);
    $update = array();
    $new_aml_give = $new_aml_get = $new_aml_merch = array();

    $amlcheck = is_extension_name($direction->amlcheck);

    $options = pn_json_decode($direction->amlcheck_opts);
    if (!is_array($options)) $options = array();

    $aml_give = pn_json_decode(is_isset($bid, 'aml_give'));
    if (!is_array($aml_give)) $aml_give = array();

    $aml_get = pn_json_decode(is_isset($bid, 'aml_get'));
    if (!is_array($aml_get)) $aml_get = array();

    $aml_merch = pn_json_decode(is_isset($bid, 'aml_merch'));
    if (!is_array($aml_merch)) $aml_merch = array();

    $give = intval(is_isset($options, 'give'));
    $get = intval(is_isset($options, 'get'));
    $merch = intval(is_isset($options, 'merch'));
    $give_sum = is_sum(is_isset($options, 'give_sum'));
    $get_sum = is_sum(is_isset($options, 'get_sum'));
    $merch_sum = is_sum(is_isset($options, 'merch_sum'));

    $sum_give = is_sum(is_isset($bid, 'sum1dc'));
    $sum_get = is_sum(is_isset($bid, 'sum2c'));
    $account_give = pn_strip_input(is_isset($bid, 'account_give'));
    $account_get = pn_strip_input(is_isset($bid, 'account_get'));
    $address = pn_strip_input(is_isset($bid, 'to_account'));
    $trans_in = pn_strip_input(is_isset($bid, 'trans_in'));
    $txid_in = pn_strip_input(is_isset($bid, 'txid_in'));
    if ($txid_in) $trans_in = $txid_in;

    $v = get_currency_data();

    $stop = 0;

    if (
        $account_give && (
            ('error' == $place && 1 == $give) ||
            ('payed' == $place && 2 == $give) ||
            ('beforepayout' == $place && 3 == $give) ||
            'manual_give' == $place ||
            (in_array($give, [2, 3]) && 'wait' == $place)
        ) && (
            $sum_give >= $give_sum ||
            'manual_give' == $place
        )
    ) {
        $ch_data = $req ? [] : $aml_give;
        $ch_status = intval(is_isset($ch_data, 'status'));
        if (in_array($ch_status, [0, 2])) {
            $new_aml_give = apply_filters('amlverify_addr', $ch_data, $amlcheck, $ch_data, $bid, $v, $account_give, 'give');
            $new_aml_give['nd'] = 1;
            $new_status = intval(is_isset($new_aml_give, 'status'));
            if (2 == $new_status) {
                if ($next_action) {
                    $new_aml_give['next_action'] = $next_action;
                }
                $stop = 1;
            }
        }
    }

    if (
        $account_get && (
            ('error' == $place && 1 == $get) ||
            ('payed' == $place && 2 == $get) ||
            ('beforepayout' == $place && 3 == $get) ||
            'manual_get' == $place ||
            (in_array($get, [2, 3]) && 'wait' == $place)
        ) && (
            $sum_get >= $get_sum ||
            'manual_get' == $place
        )
    ) {
        $ch_data = $req ? [] : $aml_get;
        $ch_status = intval(is_isset($ch_data, 'status'));
        if (in_array($ch_status, array(0, 2))) {
            $new_aml_get = apply_filters('amlverify_addr', $ch_data, $amlcheck, $ch_data, $bid, $v, $account_get, 'get');
            $new_aml_get['nd'] = 1;
            $new_status = intval(is_isset($new_aml_get, 'status'));
            if (2 == $new_status) {
                if ($next_action) {
                    $new_aml_get['next_action'] = $next_action;
                }
                $stop = 1;
            }
        }
    }

    if (
        ($address && $trans_in) && (
            ('payed' == $place && 1 == $merch) ||
            ('beforepayout' == $place && 2 == $merch) ||
            'manual_merch' == $place ||
            (in_array($merch, [1, 2]) && 'wait' == $place)
        ) && (
            $sum_give >= $merch_sum ||
            'manual_merch' == $place
        )
    ) {
        $ch_data = $req ? [] : $aml_merch;
        $ch_status = intval(is_isset($ch_data, 'status'));
        if (in_array($ch_status, array(0, 2))) {
            $new_aml_merch = apply_filters('amlverify_trans', $ch_data, $amlcheck, $ch_data, $bid, $v, $address, $trans_in);
            $new_aml_merch['nd'] = 1;
            $new_status = intval(is_isset($new_aml_merch, 'status'));
            if (2 == $new_status) {
                if ($next_action) {
                    $new_aml_merch['next_action'] = $next_action;
                }
                $stop = 1;
            }
        }
    }

    if ($new_aml_give) {
        $bid['aml_give'] = pn_json_encode(pn_strip_input_array($new_aml_give));
        $update['aml_give'] = $bid['aml_give'];
    }

    if ($new_aml_get) {
        $bid['aml_get'] = pn_json_encode(pn_strip_input_array($new_aml_get));
        $update['aml_get'] = $bid['aml_get'];
    }

    if ($new_aml_merch) {
        $bid['aml_merch'] = pn_json_encode(pn_strip_input_array($new_aml_merch));
        $update['aml_merch'] = $bid['aml_merch'];
    }

    if ($stop && $bid_id > 0 && 'amlwait' != $bid['status']) {
        $old_status = $bid['status'];

        $bid['edit_date'] = $update['edit_date'] = current_time('mysql');
        $bid['status'] = $update['status'] = 'amlwait';

        $ch_data = array(
            'bid' => (object)$bid,
            'set_status' => 'amlwait',
            'place' => 'amlcheck',
            'who' => 'system',
            'old_status' => $old_status,
            'direction' => $direction
        );
        _change_bid_status($ch_data);
    }

    if (count($update) > 0 and $bid_id > 0) {
        $wpdb->update($wpdb->prefix . "exchange_bids", $update, array('id' => $bid_id));
    }

    $bid['stop'] = $stop;

    return $bid;
}

if (!class_exists('Ext_AML_Premiumbox')) {
    class Ext_AML_Premiumbox extends Ext_Premium {

        public $callback = 0;

        function __construct($file, $title, $callback = 0) {

            global $premiumbox;
            parent::__construct($file, $title, 'amlcheck', $premiumbox);

            $this->callback = intval($callback);

            add_action('_amlcheck_options', array($this, 'get_options'), 10, 5);
            add_action('ext_amlcheck_delete', array($this, 'del_directions'), 10, 2);

            add_filter('get_aml_options', array($this, 'get_aml_options'), 10, 2);

            add_action('ext_amlcheck_test', array($this, 'the_test'), 10, 2);
            add_filter('amlcheck_testpage', array($this, 'the_testpage'), 10, 4);

            add_filter('amlverify_addr', array($this, '_check_addr'), 10, 7);
            add_filter('amlverify_trans', array($this, '_check_trans'), 10, 7);

            add_action('premium_request_amlcheck_callback', array($this, 'amlcheck_callback'));

        }

        function get_options($options, $name, $data, $id, $place) {

            if ($name == $this->name) {
                $options = $this->options($options, $data, $id, $place);
                if (!$this->callback) {
                    $options = pn_array_unset($options, array('enableip', 'resulturl', 'help_resulturl', 'callback_url'));
                }
            }

            return $options;
        }

        function options($options, $data, $id, $place) {

            return $options;
        }

        function get_aml_options($arr, $name) {

            if ($name == $this->name) {
                return $this->aml_options();
            }

            return $arr;
        }

        function aml_options() {

            return array();
        }

        function _check_addr($data, $m_id, $ch_data, $bid, $v, $account, $giveget) {

            if ($m_id) {
                $script = get_ext_plugin($m_id, 'amlcheck');
                if ($script and $script == $this->name) {
                    $m_defin = $this->get_file_data($m_id);
                    $m_data = get_amlcheck_data($m_id);

                    $apierror_score = intval(is_isset($m_data, 'apierror_score'));
                    if ($apierror_score < 1) $apierror_score = 100;

                    $data['status'] = 3;
                    $data['score'] = $apierror_score;

                    $signals = array();
                    $opts = get_aml_options($this->name);
                    foreach ($opts as $opt_name => $opt_title) {
                        $signals[$opt_name] = is_sum($apierror_score / 100);
                    }
                    $data['signals'] = $signals;
                    $data['link'] = '';

                    $data = $this->check_addr($data, $m_id, $ch_data, $bid, $v, $account, $giveget, $m_defin, $m_data);

                }
            }

            return $data;
        }

        function check_addr($data, $m_id, $ch_data, $bid, $v, $account, $giveget, $m_defin, $m_data) {

            return $data;
        }

        function _check_trans($data, $m_id, $ch_data, $bid, $v, $address, $trans_in) {

            if ($m_id) {
                $script = get_ext_plugin($m_id, 'amlcheck');
                if ($script and $script == $this->name) {
                    $m_defin = $this->get_file_data($m_id);
                    $m_data = get_amlcheck_data($m_id);

                    $apierror_score = intval(is_isset($m_data, 'apierror_score'));
                    if ($apierror_score < 1) $apierror_score = 100;

                    $data['status'] = 3;
                    $data['score'] = $apierror_score;

                    $signals = array();
                    $opts = get_aml_options($this->name);
                    foreach ($opts as $opt_name => $opt_title) {
                        $signals[$opt_name] = is_sum($apierror_score / 100);
                    }
                    $data['signals'] = $signals;
                    $data['link'] = '';

                    $data = $this->check_trans($data, $m_id, $ch_data, $bid, $v, $address, $trans_in, $m_defin, $m_data);

                }
            }

            return $data;
        }

        function check_trans($data, $m_id, $ch_data, $bid, $v, $address, $trans_in, $m_defin, $m_data) {

            return $data;
        }

        function the_test($name, $id) {
            if ($name == $this->name) {
                $this->test($id);
            }
        }

        function test($id) {

        }

        function test_action_link($id, $type) {

            return pn_link('pn_amlcheck_testlink', 'post') . '&m_name=' . $this->name . '&m_id=' . $id . '&test_type=' . intval($type);
        }

        function the_testpage($res, $m_name, $m_id, $test_type) {

            if ($m_name == $this->name) {

                $test_type = intval($test_type);

                $m_defin = $this->get_file_data($m_id);

                $res = $this->test_post($res, $m_id, $test_type, $m_defin);

            }

            return $res;
        }

        function test_post($res, $m_id, $test_type, $m_defin) {

            return $res;
        }

        function callback_error() {

            echo 'Error';
            exit;
        }

        function callback_success() {

            echo 'OK';
            exit;
        }

        function callback_id() {

            return pn_strip_input(is_param_post('clbk_id'));
        }

        function amlcheck_callback() {
            global $wpdb;

            $m_id = is_extension_name(is_param_get('m_name'));
            $hash = pn_strip_symbols(is_param_get('hash'));

            if ($m_id) {
                $script = get_ext_plugin($m_id, 'amlcheck');
                if ($script and $script == $this->name) {
                    if ($this->callback) {
                        $m_defin = $this->get_file_data($m_id);
                        $m_data = get_amlcheck_data($m_id);
                        $m_hash = pn_strip_symbols(is_isset($m_data, 'resulturl'));
                        if ($m_hash == $hash or !$m_hash) {

                            $callback = file_get_contents('php://input');
                            $post = @json_decode($callback, true);

                            do_action('save_amlcheck_error', $this->m_name, $m_id, '', '', $post, $_REQUEST, 0, 0);

                            $yes_ip = trim(is_isset($m_data, 'enableip'));
                            $user_ip = pn_real_ip();
                            if ($yes_ip and !pn_has_ip($yes_ip)) {
                                die(sprintf(__('IP adress (%s) is blocked', 'pn'), $user_ip));
                                exit;
                            }

                            $id = $this->callback_id();

                            amlcheck_cron();

                            $this->callback_success();

                        }
                    }

                    $this->callback_error();
                }
            }

        }

        function del_directions($script, $id) {
            global $wpdb;

            if ($script == $this->name) {
                $wpdb->query("UPDATE " . $wpdb->prefix . "directions SET amlcheck = '' WHERE amlcheck = '$id'");
            }
        }
    }
}