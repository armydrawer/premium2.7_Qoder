<?php
if (!defined('ABSPATH')) exit();

if (!function_exists('list_user_notify_registerform')) {
    add_filter('list_user_notify', 'list_user_notify_registerform');
    function list_user_notify_registerform($places) {

        $places['registerform'] = __('Registration form', 'pn');

        return $places;
    }
}

if (!function_exists('def_list_notify_tags_registerform')) {
    add_filter('list_notify_tags_registerform', 'def_list_notify_tags_registerform');
    function def_list_notify_tags_registerform($tags) {

        $tags['login'] = array(
            'title' => __('Login', 'pn'),
            'start' => '[login]'
        );
        $tags['pass'] = array(
            'title' => __('Password', 'pn'),
            'start' => '[pass]'
        );
        $tags['email'] = array(
            'title' => __('E-mail', 'pn'),
            'start' => '[email]'
        );

        return $tags;
    }
}

if (!function_exists('def_pn_adminpage_title_all_mail_temps') and is_admin()) {
    add_filter('pn_adminpage_title_all_mail_temps', 'def_adminpage_title_all_mail_temps');
    function def_adminpage_title_all_mail_temps($page) {

        return __('E-mail templates', 'pn');
    }
}

if (!function_exists('def_pn_adminpage_content_all_mail_temps') and is_admin()) {
    add_action('pn_adminpage_content_all_mail_temps', 'def_adminpage_content_all_mail_temps');
    function def_adminpage_content_all_mail_temps() {

        $place = pn_strip_input(is_param_get('place'));

        $form = new PremiumForm();

        $selects = array();
        $selects[] = array(
            'link' => admin_url("admin.php?page=all_mail_temps"),
            'title' => '--' . __('Test', 'pn') . '--',
            'default' => '',
        );

        $places_admin = apply_filters('list_admin_notify', array(), 'email');
        if (!is_array($places_admin)) {
            $places_admin = array();
        }

        if (count($places_admin) > 0) {
            $selects[] = array(
                'link' => admin_url("admin.php?page=all_mail_temps&place=admin_notify"),
                'title' => '---' . __('Admin notification', 'pn'),
                'atts' => array('style' => 'background: #faf9c4'),
                'default' => 'admin_notify',
            );
        }

        foreach ($places_admin as $key => $val) {
            $selects[] = array(
                'link' => admin_url("admin.php?page=all_mail_temps&place=" . $key),
                'title' => $val,
                'default' => $key,
            );
        }

        $places_user = apply_filters('list_user_notify', array(), 'email');
        if (!is_array($places_user)) {
            $places_user = array();
        }

        if (count($places_user) > 0) {
            $selects[] = array(
                'link' => admin_url("admin.php?page=all_mail_temps&place=user_notify"),
                'title' => '---' . __('Users notification', 'pn'),
                'atts' => array('style' => 'background: #faf9c4;'),
                'default' => 'user_notify',
            );
        }

        foreach ($places_user as $key => $val) {
            $selects[] = array(
                'link' => admin_url("admin.php?page=all_mail_temps&place=" . $key),
                'title' => $val,
                'default' => $key,
            );
        }

        $form->select_box($place, $selects, __('Setting up', 'pn'));

        $plugin = get_plugin_class();

        if (isset($places_admin[$place]) or isset($places_user[$place])) {

            $data = get_notify_data('email', $place);

            $options = array();
            $options['top_title'] = array(
                'view' => 'h3',
                'title' => __('Templates', 'pn'),
                'submit' => __('Save', 'pn'),
            );
            $options['hidden_block'] = array(
                'view' => 'hidden_input',
                'name' => 'block',
                'default' => $place,
            );

            $options['send'] = array(
                'view' => 'select',
                'title' => __('To send', 'pn'),
                'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
                'default' => is_isset($data, 'send'),
                'name' => 'send',
            );

            $options['subject'] = array(
                'view' => 'inputbig',
                'title' => __('Subject of e-mail', 'pn'),
                'default' => is_isset($data, 'subject'),
                'name' => 'subject',
                'ml' => 1,
            );

            $options['mail'] = array(
                'view' => 'inputbig',
                'title' => __('Sender e-mail', 'pn'),
                'default' => is_isset($data, 'mail'),
                'name' => 'mail',
            );
            $options['mail_warning'] = array(
                'view' => 'warning',
                'default' => __('Use only existing e-mail address (for example - info@site.ru)', 'pn'),
            );

            $options['name'] = array(
                'view' => 'inputbig',
                'title' => __('Sender name', 'pn'),
                'default' => is_isset($data, 'name'),
                'name' => 'name',
            );

            if (isset($places_admin[$place])) {
                $options['to'] = array(
                    'view' => 'inputbig',
                    'title' => __('Administrator e-mail', 'pn'),
                    'default' => is_isset($data, 'to'),
                    'name' => 'to',
                );
                $options['tomailhelp'] = array(
                    'view' => 'help',
                    'title' => __('More info', 'pn'),
                    'default' => __('If the recipient has several e-mail address, e-mail address should be comma-separated', 'pn'),
                );
            }

            $tags = array(
                'sitename' => array(
                    'title' => __('Website name', 'pn'),
                    'start' => '[sitename]',
                ),
                'site_url' => array(
                    'title' => __('Website url', 'pn'),
                    'start' => '[site_url]',
                ),
            );
            $tags = apply_filters('list_notify_tags_' . $place, $tags);

            $options['text'] = array(
                'view' => 'editor',
                'title' => __('Text', 'pn'),
                'default' => is_isset($data, 'text'),
                'tags' => $tags,
                'rows' => '20',
                'name' => 'text',
                'formatting_tags' => 1,
                'ml' => 1,
            );

            $params_form = array(
                'filter' => 'all_mail_temps_option',
                'button_title' => __('Save', 'pn'),
            );
            $form->init_form($params_form, $options);

        } else {

            $options = array();
            $options['top_title'] = array(
                'view' => 'h3',
                'title' => __('Settings', 'pn'),
                'submit' => __('Save', 'pn'),
            );

            $options['from_mail'] = array(
                'view' => 'inputbig',
                'title' => __('Sender e-mail', 'pn'),
                'default' => $plugin->get_option('email', 'mail'),
                'name' => 'from_mail',
            );
            $options['from_warning'] = array(
                'view' => 'warning',
                'default' => __('Use only existing e-mail like info@site.ru', 'pn'),
            );
            $options['from_name'] = array(
                'view' => 'inputbig',
                'title' => __('Sender name', 'pn'),
                'default' => $plugin->get_option('email', 'name'),
                'name' => 'from_name',
            );
            $options['to_mail'] = array(
                'view' => 'inputbig',
                'title' => __('Administrator e-mail', 'pn'),
                'default' => $plugin->get_option('email', 'tomail'),
                'name' => 'to_mail',
            );
            $options['tomailhelp'] = array(
                'view' => 'help',
                'title' => __('More info', 'pn'),
                'default' => __('If the recipient has several e-mail address, e-mail address should be comma-separated', 'pn'),
            );

            $options['enable'] = array(
                'view' => 'select',
                'title' => __('Enable SMTP', 'pn'),
                'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
                'default' => $plugin->get_option('smtp', 'enable'),
                'name' => 'enable',
            );
            $options['secure'] = array(
                'view' => 'select',
                'title' => __('SMTP connection type', 'pn'),
                'options' => array('0' => __('SSL', 'pn'), '1' => __('TLS', 'pn'), '2' => __('No', 'pn')),
                'default' => $plugin->get_option('smtp', 'secure'),
                'name' => 'secure',
            );
            $options['host'] = array(
                'view' => 'inputbig',
                'title' => __('SMTP Host', 'pn'),
                'default' => $plugin->get_option('smtp', 'host'),
                'name' => 'host',
            );
            $options['port'] = array(
                'view' => 'inputbig',
                'title' => __('SMTP Port', 'pn'),
                'default' => $plugin->get_option('smtp', 'port'),
                'name' => 'port',
            );
            $options['username'] = array(
                'view' => 'inputbig',
                'title' => __('SMTP Username', 'pn'),
                'default' => $plugin->get_option('smtp', 'username'),
                'name' => 'username',
            );
            $options['password'] = array(
                'view' => 'inputbig',
                'title' => __('SMTP Password', 'pn'),
                'default' => $plugin->get_option('smtp', 'password'),
                'name' => 'password',
                'atts' => array('type' => 'password'),
            );

            $options['timeout'] = [
                'view' => 'inputbig',
                'title' => __('Timeout (sec.)', 'pn'),
                'default' => $plugin->get_option('smtp', 'timeout'),
                'name' => 'timeout',
            ];
            $options['timeout_help'] = [
                'view' => 'help',
                'title' => __('More info', 'pn'),
                'default' => __('Timeout is the period when the website awaits a response from a third-party service. If no response is received in the preset period, the website will continue running without response. If the duration is not specified or is 0, the standard 20-second timeout is applied. There is no universal value for the timeout, since it depends on the operation speed of a specific service.', 'pn'),
            ];

            $options['debug'] = array(
                'view' => 'select',
                'title' => __('Debug mode', 'pn'),
                'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
                'default' => $plugin->get_option('smtp', 'debug'),
                'name' => 'debug',
            );

            $help = '
			<p>
				<strong>' . __('SMTP Host', 'pn') . '</strong>: smtp.yandex.ru<br />
				<strong>' . __('SMTP Port', 'pn') . '</strong>: 465
			</p>
			';
            $options['yahelp'] = array(
                'view' => 'help',
                'title' => __('Info for yandex', 'pn'),
                'default' => $help,
            );

            $params_form = array(
                'form_link' => pn_link('all_email_settings'),
                'button_title' => __('Save', 'pn'),
            );
            $form->init_form($params_form, $options);

            $options = array();
            $options['top_title'] = array(
                'view' => 'h3',
                'title' => __('Send test e-mail', 'pn'),
                'submit' => __('Send a message', 'pn'),
            );
            $options['to'] = array(
                'view' => 'inputbig',
                'title' => __('Your e-mail', 'pn'),
                'default' => '',
                'name' => 'to',
                'atts' => array('autocomplete' => 'off'),
                'work' => 'email',
            );

            $params_form = array(
                'form_link' => pn_link('all_email_send_test'),
                'button_title' => __('Send a message', 'pn'),
            );
            $form->init_form($params_form, $options);

        }

    }
}

if (!function_exists('def_premium_action_all_email_send_test')) {
    add_action('premium_action_all_email_send_test', 'def_premium_action_all_email_send_test');
    function def_premium_action_all_email_send_test() {

        _method('post');

        $form = new PremiumForm();
        $form->send_header();

        pn_only_caps(array('administrator', 'pn_change_notify'));

        $to = is_email(is_param_post('to'));
        if (!$to) {
            $form->error_form(__('Error! You have not entered an e-mail!', 'pn'));
        } else {
            $result = apply_filters('pn_email_send', 0, $to, 'Test MAIL send', 'Test MAIL send content');
        }

        $back_url = is_param_post('_wp_http_referer');
        $back_url = add_query_args(array('reply' => 'true'), $back_url);

        $form->answer_form($back_url);

    }
}

if (!function_exists('def_premium_action_all_email_settings')) {
    add_action('premium_action_all_email_settings', 'def_premium_action_all_email_settings');
    function def_premium_action_all_email_settings() {

        _method('post');

        $form = new PremiumForm();
        $form->send_header();

        pn_only_caps(array('administrator'));

        $plugin = get_plugin_class();

        $plugin->update_option('email', 'mail', pn_strip_input(is_param_post('from_mail')));
        $plugin->update_option('email', 'name', pn_strip_input(is_param_post('from_name')));
        $plugin->update_option('email', 'tomail', pn_strip_input(is_param_post('to_mail')));

        $plugin->update_option('smtp', 'enable', intval(is_param_post('enable')));
        $plugin->update_option('smtp', 'secure', intval(is_param_post('secure')));
        $plugin->update_option('smtp', 'host', pn_strip_input(is_param_post('host')));
        $plugin->update_option('smtp', 'port', pn_strip_input(is_param_post('port')));
        $plugin->update_option('smtp', 'username', pn_strip_input(is_param_post('username')));
        $plugin->update_option('smtp', 'password', pn_strip_input(is_param_post('password')));
        $plugin->update_option('smtp', 'timeout', absint(is_param_post('timeout')));
        $plugin->update_option('smtp', 'debug', intval(is_param_post('debug')));

        $back_url = is_param_post('_wp_http_referer');
        $back_url = add_query_args(array('reply' => 'true'), $back_url);

        $form->answer_form($back_url);

    }
}

if (!function_exists('def_premium_action_all_mail_temps')) {
    add_action('premium_action_all_mail_temps', 'def_premium_action_all_mail_temps');
    function def_premium_action_all_mail_temps() {

        _method('post');

        $form = new PremiumForm();
        $form->send_header();

        pn_only_caps(array('administrator', 'pn_change_notify'));

        $block = pn_strip_input(is_param_post('block'));

        if ($block) {

            $notify = array();

            $notify['send'] = intval(is_param_post('send'));
            $notify['subject'] = pn_strip_input(is_param_post_ml('subject'));
            $notify['mail'] = pn_strip_input(is_param_post('mail'));
            $notify['name'] = pn_strip_input(is_param_post('name'));
            $notify['to'] = pn_strip_input(is_param_post('to'));
            $notify['text'] = pn_strip_text(is_param_post_ml('text'));
            $notify = apply_filters('email_temps_array', $notify);

            update_notify_data('email', $block, $notify);

        }

        do_action('all_mail_temps_option_post');

        $back_url = is_param_post('_wp_http_referer');
        $back_url = add_query_args(array('reply' => 'true'), $back_url);

        $form->answer_form($back_url);

    }
}

if (!function_exists('mailtemps_wp_mail')) {
    add_filter('wp_mail', 'mailtemps_wp_mail');
    function mailtemps_wp_mail($data) {

        $plugin = get_plugin_class();
        $mail = pn_strip_input($plugin->get_option('email', 'mail'));
        $name = pn_strip_input($plugin->get_option('email', 'name'));
        if ($mail and $name and strlen($data['headers']) < 2) {
            $data['headers'] = "From: $name <" . $mail . ">\r\n";
        }

        return $data;
    }
}

if (!function_exists('pn_send_smtp_email')) {
    add_filter('phpmailer_init', 'pn_send_smtp_email', 11);
    function pn_send_smtp_email($phpmailer) {

        $plugin = get_plugin_class();
        if (1 == $plugin->get_option('smtp', 'enable')) {

            $timeout = absint($plugin->get_option('smtp', 'timeout'));
            if (!$timeout) $timeout = 3;

            $phpmailer->Timeout = $timeout;
            $phpmailer->isSMTP();
            $phpmailer->Host = $plugin->get_option('smtp', 'host');
            $username = trim($plugin->get_option('smtp', 'username'));
            $password = trim($plugin->get_option('smtp', 'password'));
            if ($username and $password) {
                $phpmailer->SMTPAuth = true;
                $phpmailer->Username = $username;
                $phpmailer->Password = $password;
            }
            $phpmailer->Port = $plugin->get_option('smtp', 'port');

            $from_mail = pn_strip_input($plugin->get_option('email', 'mail'));
            if ($from_mail) {
                $phpmailer->From = $from_mail;
                $phpmailer->Sender = $from_mail;
            } else {
                $phpmailer->From = $username;
            }

            $phpmailer->FromName = pn_strip_input($plugin->get_option('email', 'name'));
            $secure_types = array('0' => 'ssl', '1' => 'tls', '2' => '');
            $secure = intval($plugin->get_option('smtp', 'secure'));
            if (isset($secure_types[$secure])) {
                $phpmailer->SMTPSecure = $secure_types[$secure];
            }

            $debug = intval($plugin->get_option('smtp', 'debug'));
            if (1 == $debug) {
                if ('all_email_send_test' == _is('is_adminaction') and is_admin()) {
                    $phpmailer->SMTPDebug = 2;
                    $phpmailer->Debugoutput = function ($str, $level) {
                        $form = new PremiumForm();
                        $form->error_form("debug level $level; message: $str");
                    };
                }
            }

        }

        return $phpmailer;
    }
}

if (!function_exists('email_premium_send_message')) {
    add_filter('premium_send_message', 'email_premium_send_message', 10, 4);
    function email_premium_send_message($result, $method, $notify_tags, $user_send_data) {

        if (!is_array($notify_tags)) {
            $notify_tags = array();
        }
        $notify_tags['[sitename]'] = pn_site_name();
        $notify_tags['[site_url]'] = PN_SITE_URL;

        $data = get_notify_data('email', $method);
        if (isset($data['send'])) {
            $send = intval(is_isset($data, 'send'));
            if ($send) {

                $plugin = get_plugin_class();

                $from_mail = is_email($data['mail']);
                if (strlen($from_mail) < 1) {
                    $from_mail = is_email($plugin->get_option('email', 'mail'));
                }

                $from_name = pn_strip_input($data['name']);
                if (strlen($from_name) < 1) {
                    $from_name = pn_strip_input($plugin->get_option('email', 'name'));
                }
                $from_name = replace_tags($notify_tags, $from_name, 1);

                $subject = pn_strip_input(ctv_ml($data['subject']));
                $subject = replace_tags($notify_tags, $subject, 1);

                $html = pn_strip_text(ctv_ml($data['text']));
                $html = replace_tags($notify_tags, $html, 0);
                $html = str_replace('[subject]', $subject, $html);
                $html = apply_filters('comment_text', $html);
                $html = remove_unused_shortcode($html);

                $to_mail = '';
                if (isset($user_send_data['user_email'])) {
                    $to_mail = pn_strip_input(is_isset($user_send_data, 'user_email'));
                } elseif (isset($user_send_data['admin_email'])) {
                    $to_mail = pn_strip_input(is_isset($data, 'to'));
                    if (!$to_mail) {
                        $to_mail = pn_strip_input($plugin->get_option('email', 'tomail'));
                    }
                }

                $to_mail = apply_filters('send_message_tomail', $to_mail, $method, $notify_tags, $user_send_data);

                if ($to_mail) {
                    $nresult = apply_filters('pn_email_send', 0, $to_mail, $subject, $html, $from_name, $from_mail);
                    if (1 == $nresult) {
                        return 1;
                    }
                }

            }
        }

        return $result;
    }
}