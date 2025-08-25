<?php
if (!defined('ABSPATH')) exit();

if (!function_exists('pn_session_start')) {
    function pn_session_start() {
        $session_id = session_id();
        if (!$session_id) {
            @session_start();
        }
    }
}

if (!function_exists('get_charset')) {
    function get_charset() {
        return get_bloginfo('charset');
    }
}

if (!function_exists('the_charset')) {
    function the_charset() {
        echo get_charset();
    }
}

if (!function_exists('pn_clear_request')) {
    function pn_clear_request() {

        if (strpos($_SERVER['REQUEST_URI'], "eval(") ||
            strpos($_SERVER['REQUEST_URI'], "CONCAT") ||
            strpos($_SERVER['REQUEST_URI'], "UNION+SELECT") ||
            strpos($_SERVER['REQUEST_URI'], "base64")) {
            $protocol = wp_get_server_protocol();
            header("$protocol 414 Request-URI Too Long");
            header("Status: 414 Request-URI Too Long");
            header("Connection: Close");
            exit;
        }

        $request_uri = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $request_uri = $_SERVER['REQUEST_URI'];
        }

        $pars = parse_url($request_uri);
        $path = '';
        if (isset($pars['path'])) {
            $path = $pars['path'];
        }

        $path_arr = explode('/', $path);
        $end = trim(end($path_arr));
        if (strlen($end) < 1 or !strstr($end, '.')) {
            $path = trailingslashit($path);
        }

        if (isset($pars['query'])) {
            $path .= '?' . $pars['query'];
        }

        $_SERVER['REQUEST_URI'] = $path;
    }
}

if (!function_exists('current_user_cans')) {
    function current_user_cans($capability = '') {

        $capability_arr = array();
        if (is_array($capability)) {
            $capability_arr = $capability;
        } elseif (is_string($capability)) {
            $capability_arr = explode(',', $capability);
            $capability_arr = array_map('trim', $capability_arr);
        }

        $capability_arr = array_unique($capability_arr);
        foreach ($capability_arr as $cap) {
            $cap = pn_string($cap);
            if ($cap) {
                if (current_user_can($cap)) {
                    return 1;
                    break;
                }
            }
        }

        return 0;
    }
}

if (!function_exists('get_random_password')) {
    function get_random_password($length = 12, $use_numbers = true, $use_chars = true, $special_chars = false, $extra_special_chars = false) {

        $chars = '';
        if ($use_numbers) {
            $chars .= '0123456789';
        }

        if ($use_chars) {
            $chars .= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        if ($special_chars) {
            $chars .= '!@#$%^&*()';
        }

        if ($extra_special_chars) {
            $chars .= '-_ []{}<>~`+=,.;:/?|';
        }

        if (strlen($chars) < 1) {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        }

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return apply_filters('get_random_password', $password, $length, $use_numbers, $use_chars, $special_chars, $extra_special_chars);
    }
}

if (!function_exists('pn_php_vers')) {
    function pn_php_vers() {

        $php_vers_arr = explode('.', phpversion());
        $vers = is_isset($php_vers_arr, 0) . '.' . is_isset($php_vers_arr, 1);
        if ('7.0' == $vers) {
            $vers = '5.6';
        }

        return $vers;
    }
}

if (!function_exists('get_premium_script')) {
    function get_premium_script() {

        $url = plugin_basename(__FILE__);
        $parts = explode('/', $url);
        $plugin_folder = $parts[0];

        return $plugin_folder;
    }
}

if (!function_exists('get_premium_url')) {
    function get_premium_url() {
        return without_path(plugin_dir_url(__FILE__));
    }
}

if (!function_exists('get_premium_dir')) {
    function get_premium_dir() {
        return rtrim(without_path(plugin_dir_path(__FILE__)), '/');
    }
}

if (!function_exists('xframe_sameorigin')) {
    function xframe_sameorigin() {
        header('X-Frame-Options: SAMEORIGIN');
    }
}

if (!function_exists('xrobots_noindex')) {
    function xrobots_noindex() {
        header('X-Robots-Tag: noindex');
    }
}

if (!function_exists('xframe_alloworigin')) {
    function xframe_alloworigin() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
    }
}

if (!function_exists('pn_display_mess')) {
    function pn_display_mess($title, $text = '', $species = 'error') {

        header('Content-Type: text/html; charset=' . get_charset());

        $title = trim($title);
        $text = trim($text);
        if (strlen($text) < 1) {
            $text = $title;
        }

        $html = '<html ' . get_language_attributes() . '><head><title>' . $title . '</title>' . apply_filters('premium_other_head', '', 'error_message') . '</head><body class="' . implode(' ', get_body_class()) . '">';

        if ('error' == $species) {
            $text_html = '<p style="text-align: center; color: #ff0000; padding: 20px 0;">' . $text . '</p>';
        } else {
            $text_html = '<p style="text-align: center; color: green; padding: 20px 0;">' . $text . '</p>';
        }

        $html .= apply_filters('premium_display_mess', $text_html, $title, $text, $species);
        $html .= '</body></html>';

        echo $html;
        exit;
    }
}

if (!function_exists('get_request_query')) {
    function get_request_query() {

        $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $site_url = str_replace('https://', 'http://', PN_SITE_URL);
        $request = str_replace($site_url, '', $url);
        $req_arr = explode('?', $request);
        $req_query = '/' . $req_arr[0];

        return $req_query;
    }
}

if (!function_exists('get_request_dir')) {
    function get_request_dir() {

        $request = ltrim(get_request_query(), '/');
        $request_arr = explode('/', $request);

        return trim($request_arr[0], '/');
    }
}

if (!function_exists('pn_strip_symbols')) {
    function pn_strip_symbols($txt, $symbols = '') {

        if (is_array($txt) or is_object($txt) or is_null($txt)) {
            return '';
        }
        $symbols = preg_quote($symbols);
        $txt = preg_replace("/[^A-Za-z0-9$symbols]/", '', $txt);

        return $txt;
    }
}

if (!function_exists('pn_strip_input')) {
    function pn_strip_input($item) {

        if (is_array($item) or is_object($item) or is_null($item)) {
            return '';
        }

        $item = trim(esc_html(strip_tags(stripslashes($item))));

        $pn_strip_input = array(
            'select' => 'sеlect',
            'insert' => 'insеrt',
            'union' => 'uniоn',
            'loadfile' => 'lоadfile',
            'load_file' => 'lоad_file',
            'outfile' => 'оutfile',
            'cookie' => 'coоkie',
            'concat' => 'cоncat',
            'update' => 'updаte',
            'eval' => 'еval',
            'base64' => 'bаse64',
            'delete' => 'dеlete',
            'truncate' => 'truncаte',
            'replace' => 'rеplace',
            'infile' => 'infilе',
            'handler' => 'hаndler',
            'include' => 'inсlude',
            'script' => 'sсript',
            'shell_exec' => 'shell_еxec',
            'exec' => 'еxec',
            'passthru' => 'pаssthru',
            'system' => 'systеm',
            'proc_open' => 'proc_оpen',
        );

        $pn_strip_input = apply_filters('pn_strip_input', $pn_strip_input);
        $pn_strip_input = (array)$pn_strip_input;
        foreach ($pn_strip_input as $key => $value) {
            $item = preg_replace("/\b({$key})\b/iu", $value, $item);
        }

        return $item;
    }
}

if (!function_exists('pn_strip_input_array')) {
    function pn_strip_input_array($array) {

        $new_array = array();
        if (is_array($array)) {
            foreach ($array as $key => $val) {
                $key = pn_strip_input($key);
                if (is_array($val)) {
                    $new_array[$key] = pn_strip_input_array($val);
                } else {
                    $new_array[$key] = pn_strip_input($val);
                }
            }
        }

        return $new_array;
    }
}

if (!function_exists('pn_strip_text')) {
    function pn_strip_text($item) {

        if (is_array($item) or is_object($item) or is_null($item)) {
            return '';
        }

        $item = trim(stripslashes($item));
        $allow_tag = apply_filters('pn_allow_tag', '<u>,<strong>,<em>,<a>,<del>,<ins>,<code>,<img>,<h1>,<h2>,<h3>,<h4>,<h5>,<b>,<i>,<table>,<tbody>,<thead>,<tr>,<th>,<td>,<span>,<p>,<div>,<ul>,<li>,<ol>,<center>,<br>,<blockquote>,<meta>');
        $allow_tag = trim($allow_tag);
        if ($allow_tag) {
            $item = strip_tags($item, $allow_tag);
        } else {
            $item = strip_tags($item);
        }

        $pn_strip_text = array(
            'select' => 'sеlect',
            'insert' => 'insеrt',
            'union' => 'uniоn',
            'loadfile' => 'lоadfile',
            'load_file' => 'lоad_file',
            'outfile' => 'оutfile',
            'cookie' => 'coоkie',
            'concat' => 'cоncat',
            'update' => 'updаte',
            'eval' => 'еval',
            'base64' => 'bаse64',
            'delete' => 'dеlete',
            'truncate' => 'truncаte',
            'replace' => 'rеplace',
            'infile' => 'infilе',
            'handler' => 'hаndler',
            'include' => 'inсlude',
            'script' => 'sсript',
            'shell_exec' => 'shell_еxec',
            'exec' => 'еxec',
            'passthru' => 'pаssthru',
            'system' => 'systеm',
            'proc_open' => 'proc_оpen',
        );

        $pn_strip_text = apply_filters('pn_strip_text', $pn_strip_text);
        $pn_strip_text = (array)$pn_strip_text;
        foreach ($pn_strip_text as $key => $value) {
            $item = preg_replace("/\b({$key})\b/iu", $value, $item);
        }

        return $item;
    }
}

if (!function_exists('pn_strip_text_array')) {
    function pn_strip_text_array($array) {
        $new_array = array();
        if (is_array($array)) {
            foreach ($array as $key => $val) {
                if (is_array($val)) {
                    $new_array[$key] = pn_strip_text_array($val);
                } else {
                    $new_array[$key] = pn_strip_text($val);
                }
            }
        }

        return $new_array;
    }
}

if (!function_exists('pn_string')) {
    function pn_string($arg) {

        if (is_string($arg) or is_int($arg) or is_float($arg)) {
            $arg = trim($arg);
            return $arg;
        }

        return '';
    }
}

if (!function_exists('pn_maxf_mb')) {
    function pn_maxf_mb($text, $length) {

        $text = pn_string($text);
        $length = intval($length);
        if (mb_strlen($text) > $length) {
            return mb_substr($text, 0, $length);
        }

        return $text;
    }
}

if (!function_exists('pn_maxf')) {
    function pn_maxf($text, $length) {

        $text = pn_string($text);
        $length = intval($length);
        if (strlen($text) > $length) {
            return substr($text, 0, $length);
        }

        return $text;
    }
}

if (!function_exists('add_pn_cookie')) {
    function add_pn_cookie($key, $arg, $time = '', $httponly = 1) {

        $time = intval($time);
        if ($time < 1) {
            $time = current_time('timestamp') + YEAR_IN_SECONDS;
        }
        $httponly = intval($httponly);
        $httponly_bool = false;
        if ($httponly) {
            $httponly_bool = true;
        }
        setcookie($key, $arg, $time, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), $httponly_bool);
        $_COOKIE[$key] = $arg;

    }
}

if (!function_exists('get_pn_cookie')) {
    function get_pn_cookie($key) {

        if (isset($_COOKIE[$key])) {
            return $_COOKIE[$key];
        } else {
            return '';
        }

    }
}

if (!function_exists('add_time_cookie')) {
    function add_time_cookie($key, $arg, $time = '', $httponly = 0) {

        $time = intval($time);
        if ($time < 1) {
            $time = current_time('timestamp') + YEAR_IN_SECONDS;
        }
        $httponly = intval($httponly);
        $new_arg = $arg . '|' . $time;
        add_pn_cookie($key, $new_arg, '', $httponly);

    }
}

if (!function_exists('get_time_cookie')) {
    function get_time_cookie($key) {

        $arg = pn_strip_input(get_pn_cookie($key));
        $arg_arr = explode('|', $arg);
        $value = trim($arg_arr[0]);
        $end_time = intval(is_isset($arg_arr, 1));
        $now_time = current_time('timestamp');
        if ($end_time >= $now_time) {
            return $value;
        } else {
            add_pn_cookie($key, '', '', 0);
        }

        return '';
    }
}

if (!function_exists('is_captcha')) {
    function is_captcha($form) {

        $form = pn_string($form);
        if (strlen($form) > 0) {
            $plugin = get_plugin_class();
            return intval($plugin->get_option('captcha', $form));
        }

        return 0;
    }
}

if (!function_exists('is_isset')) {
    function is_isset($where, $look) {

        if (is_object($where)) {
            if (isset($where->$look)) {
                return $where->$look;
            }
        } elseif (is_array($where)) {
            if (isset($where[$look])) {
                return $where[$look];
            }
        }

        return '';
    }
}

if (!function_exists('is_param_get')) {
    function is_param_get($arg) {
        if (isset($_GET[$arg])) {
            return $_GET[$arg];
        } else {
            return '';
        }
    }
}

if (!function_exists('is_param_post')) {
    function is_param_post($arg) {
        if (isset($_POST[$arg])) {
            return $_POST[$arg];
        } else {
            return '';
        }
    }
}

if (!function_exists('is_param_req')) {
    function is_param_req($arg) {
        if (isset($_REQUEST[$arg])) {
            return $_REQUEST[$arg];
        } else {
            return '';
        }
    }
}

if (!function_exists('is_debug_mode')) {
    function is_debug_mode() {

        if (WP_DEBUG) {
            return 1;
        }

        return 0;
    }
}

if (!function_exists('pn_only_caps')) {
    function pn_only_caps($caps, $method = '') {

        $caps = (array)$caps;
        $method = trim($method);
        if (!$method) {
            $method = trim(is_param_post('form_method'));
        }
        if ('post' != $method) {
            $method = 'get';
        }

        $access = 0;
        if (current_user_cans($caps)) {
            $access = 1;
        }
        if (!$access) {
            if ('post' == $method) {
                $log = array();
                $log['status'] = 'error';
                $log['status_code'] = '1';
                $log['status_text'] = __('Error! Insufficient privileges', 'premium');
                echo pn_json_encode($log);
                exit;
            } else {
                pn_display_mess(__('Error! Insufficient privileges', 'premium'));
            }
        }

    }
}

if (!function_exists('pn_json_decode')) {
    function pn_json_decode($arr) {

        return @json_decode($arr, true);
    }
}

if (!function_exists('pn_json_encode')) {
    function pn_json_encode($arr) {

        return @json_encode($arr, JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('replace_cyr')) {
    function replace_cyr($item) {

        $iso9_table = array(
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Ѓ' => 'G',
            'Ґ' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Є' => 'YE',
            'Ж' => 'ZH', 'З' => 'Z', 'Ѕ' => 'Z', 'И' => 'I', 'Й' => 'Y',
            'Ј' => 'J', 'І' => 'I', 'Ї' => 'YI', 'К' => 'K', 'Ќ' => 'K',
            'Л' => 'L', 'Љ' => 'L', 'М' => 'M', 'Н' => 'N', 'Њ' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
            'У' => 'U', 'Ў' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'TS',
            'Ч' => 'CH', 'Џ' => 'DH', 'Ш' => 'SH', 'Щ' => 'SHH', 'Ъ' => 'UU',
            'Ы' => 'YI', 'Ь' => 'UY', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'ѓ' => 'g',
            'ґ' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'є' => 'ye',
            'ж' => 'zh', 'з' => 'z', 'ѕ' => 'z', 'и' => 'i', 'й' => 'y',
            'ј' => 'j', 'і' => 'i', 'ї' => 'yi', 'к' => 'k', 'ќ' => 'k',
            'л' => 'l', 'љ' => 'l', 'м' => 'm', 'н' => 'n', 'њ' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ў' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
            'ч' => 'ch', 'џ' => 'dh', 'ш' => 'sh', 'щ' => 'shh', 'ь' => '',
            'ы' => 'yi', 'ъ' => "uu", 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        );
        $new_item = strtr($item, $iso9_table);

        return apply_filters('replace_cyr', $new_item, $item, $iso9_table);
    }
}

if (!function_exists('_add_site_host')) {
    function _add_site_host($url) {

        $url = pn_string($url);
        if ('/' == mb_substr($url, '0', '1')) {
            return rtrim(PN_SITE_URL, '/') . $url;
        }

        return $url;
    }
}

if (!function_exists('get_safe_url')) {
    function get_safe_url($url) {

        $sdata = parse_url(PN_SITE_URL);
        $list_safe_url = apply_filters('list_safe_url', array(is_isset($sdata, 'host')));

        $data = parse_url($url);
        $link_url = trim(is_isset($data, 'host'));

        $new_url = PN_SITE_URL;
        if (strlen($link_url) < 1 or in_array($link_url, $list_safe_url)) {
            $new_url = $url;
        }

        $new_url = str_replace('return_url=', 'rtn_url=', $new_url);

        return $new_url;
    }
}

if (!function_exists('is_color')) {
    function is_color($color, $def = '') {

        $color = pn_string($color);
        if (preg_match("/^\#[a-zA-z0-9]{6}$/", $color, $matches)) {
            return $color;
        }

        return $def;
    }
}

if (!function_exists('is_extension_name')) {
    function is_extension_name($name) {

        $name = pn_string($name);
        if (preg_match("/^[a-zA-z0-9_]{1,250}$/", $name, $matches)) {
            return $name;
        }

        return '';
    }
}

if (!function_exists('is_extension_active')) {
    function is_extension_active($name, $folder, $extension_name) {

        $active = 0;
        $extended = get_option($name);
        if (!is_array($extended)) {
            $extended = array();
        }

        if (isset($extended[$folder])) {
            if (isset($extended[$folder][$extension_name])) {
                $active = 1;
            }
        }

        return $active;
    }
}

if (!function_exists('load_extended')) {
    function load_extended($plugin) {

        $now_time = current_time('timestamp');
        $now_time_check = $now_time - (3 * DAY_IN_SECONDS);

        $extended = get_option('pn_extended');
        if (!is_array($extended)) {
            $extended = array();
        }

        $extended_last = get_option('pn_extended_last');
        if (!is_array($extended_last)) {
            $extended_last = array();
        }

        $edit = 0;

        if (isset($extended['moduls']) and is_array($extended['moduls'])) {
            $exts = $extended['moduls'];
            asort($exts);
            foreach ($exts as $item) {
                $name_for_base = is_extension_name($item);
                if ($name_for_base) {
                    if (strpos($name_for_base, '_theme')) {
                        $name = str_replace('_theme', '', $name_for_base);
                        $file = get_template_directory() . '/moduls/' . $name . '/index.php';
                    } else {
                        $file = $plugin->plugin_dir . '/moduls/' . $name_for_base . '/index.php';
                    }

                    if (is_file($file)) {
                        $extended_last['moduls'][$name_for_base] = $now_time;
                        include_once($file);
                    } elseif (isset($extended['moduls'][$name_for_base])) {
                        unset($extended['moduls'][$name_for_base]);
                        $edit = 1;
                    }
                }
            }
        }
        if (isset($extended_last['moduls']) and is_array($extended_last['moduls'])) {
            foreach ($extended_last['moduls'] as $name => $time) {
                if ($now_time_check > $time) {
                    unset($extended_last['moduls'][$name]);
                }
            }
        }

        update_option('pn_extended_last', $extended_last);
        if ($edit) {
            update_option('pn_extended', $extended);
        }

    }
}

if (!function_exists('accept_extended_data')) {
    function accept_extended_data($file) {

        $data = array(
            'version' => '0.1',
            'description' => '',
            'category' => '',
            'cat' => '',
            'dependent' => '',
            'old_names' => '',
            'new' => 0,
        );

        $content = @file_get_contents($file, false, null, 0, 1500);
        $content = trim($content);
        if ($content) {
            $content = explode("/*", $content);
            if (isset($content[1])) {
                $content = explode("*/", $content[1]);
                $content = explode("\n", $content[0]);
                foreach ($content as $con) {
                    $con = trim($con);
                    if ($con) {
                        $item = explode(":", $con);
                        $val_name = '';
                        $val = array();
                        $r = 0;
                        foreach ($item as $arg) {
                            $r++;
                            if (1 == $r) {
                                $val_name = trim(strtolower($arg));
                            } else {
                                $val[] = $arg;
                            }
                        }
                        $val = trim(join(':', $val));

                        if ($val) {
                            $val_arr = array('title', 'version', 'description', 'cat', 'category', 'dependent', 'old_names', 'new');
                            if (in_array($val_name, $val_arr)) {
                                $data[$val_name] = $val;
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }
}

function pn_list_extended($folder = '') {

    $list = array();

    if ($folder) {
        $abc_files = array();
        $plugin = get_plugin_class();

        $pn_extended = get_option('pn_extended');

        $folder_plugin = $plugin->plugin_dir . '/' . $folder . "/";
        if (is_dir($folder_plugin)) {
            foreach (glob($folder_plugin . "*/", GLOB_ONLYDIR) as $extanded) {
                if (is_file($extanded . '/index.php')) {
                    $name = explode('/', str_replace('\\', '/', rtrim($extanded, '/')));
                    $name = is_extension_name(end($name));
                    if ($name) {
                        $data = accept_extended_data($extanded . '/index.php');
                        if (isset($data['title'])) {
                            $abc_files[$name] = $data;
                            $abc_files[$name]['place'] = 'plugin';
                            if (isset($pn_extended[$folder]) and in_array($name, $pn_extended[$folder])) {
                                $abc_files[$name]['status'] = 'active';
                            } else {
                                $abc_files[$name]['status'] = 'deactive';
                            }
                            $abc_files[$name]['name'] = $name;
                        }
                    }
                }
            }
        }

        $folder_theme = get_template_directory() . '/' . $folder . "/";
        if (is_dir($folder_theme)) {
            foreach (glob($folder_theme . "*/", GLOB_ONLYDIR) as $extanded) {
                if (is_file($extanded . '/index.php')) {
                    $name = explode('/', str_replace('\\', '/', rtrim($extanded, '/')));
                    $name = is_extension_name(end($name));
                    if ($name) {
                        $name .= '_theme';
                        $data = accept_extended_data($extanded . '/index.php');
                        if (isset($data['title'])) {
                            $abc_files[$name] = $data;
                            $abc_files[$name]['place'] = 'theme';
                            if (isset($pn_extended[$folder]) and in_array($name, $pn_extended[$folder])) {
                                $abc_files[$name]['status'] = 'active';
                            } else {
                                $abc_files[$name]['status'] = 'deactive';
                            }
                            $abc_files[$name]['name'] = $name;
                        }
                    }
                }
            }
        }

        ksort($abc_files);
        return $abc_files;
    }

    return $list;
}

if (!function_exists('has_extanded_script')) {
    function has_extanded_script($plugin, $folder, $name) {

        if (strpos($name, '_theme')) {
            $name = str_replace('_theme', '', $name);
            $file = get_template_directory() . '/' . $folder . '/' . $name . '/index.php';
        } else {
            $file = $plugin->plugin_dir . '/' . $folder . '/' . $name . '/index.php';
        }

        if (is_file($file)) {
            return 1;
        }

        return 0;
    }
}

if (!function_exists('include_extanded')) {
    function include_extanded($plugin, $folder, $name) {
        global $pnexts;

        if (!isset($pnexts[$folder][$name])) {
            $pnexts[$folder][$name] = $name;

            if (strpos($name, '_theme')) {
                $name = str_replace('_theme', '', $name);
                $file = get_template_directory() . '/' . $folder . '/' . $name . '/index.php';
            } else {
                $file = $plugin->plugin_dir . '/' . $folder . '/' . $name . '/index.php';
            }

            if (is_file($file)) {
                include_once($file);
            }
        }
    }
}

if (!function_exists('extended_time_deactive')) {
    function extended_time_deactive($extended_last, $name, $old_names = '') {

        $times = array();
        if (isset($extended_last[$name])) {
            $times[] = trim($extended_last[$name]);
        }

        $old_names = explode(',', $old_names);
        foreach ($old_names as $oname) {
            $oname = trim($oname);
            if ($oname) {
                if (isset($extended_last[$oname])) {
                    $times[] = trim($extended_last[$oname]);
                }
            }
        }

        $time_deactive = '';
        if (count($times) > 0) {
            $time_deactive = max($times);
        }

        return $time_deactive;
    }
}

if (!function_exists('get_extension_name')) {
    function get_extension_name($path) {

        $name = explode('/', $path);
        $name = end($name);
        $name = is_extension_name($name);
        if (strstr($path, '/themes/')) {
            $name .= '_theme';
        }

        return $name;
    }
}

if (!function_exists('get_extension_num')) {
    function get_extension_num($name) {

        $num = preg_replace('/[^0-9]/', '', $name);

        return $num;
    }
}

if (!function_exists('get_extension_file')) {
    function get_extension_file($file) {

        return wp_normalize_path(dirname($file));
    }
}

if (!function_exists('get_extandeds')) {
    function get_extandeds() {
        global $wpdb, $pn_extendeds;

        if (!is_array($pn_extendeds)) {
            $pn_extendeds = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exts");
        }

        return $pn_extendeds;
    }
}

if (!function_exists('set_extandeds')) {
    function set_extandeds($plugin, $folder) {
        $extendeds = get_extandeds();
        foreach ($extendeds as $ext) {
            if ($ext->ext_type and $ext->ext_type == $folder) {
                $name_for_base = is_extension_name(is_isset($ext, 'ext_plugin'));
                if ($name_for_base) {
                    include_extanded($plugin, $folder, $name_for_base);
                }
            }
        }
    }
}

if (!function_exists('get_ext_plugin')) {
    function get_ext_plugin($key, $type) {
        $plugin = 'no';
        $extendeds = get_extandeds();
        foreach ($extendeds as $ext) {
            if ($ext->ext_type == $type and $ext->ext_key == $key and 1 == $ext->ext_status) {
                return $ext->ext_plugin;
            }
        }

        return $plugin;
    }
}

if (!function_exists('list_extandeds')) {
    function list_extandeds($folder) {

        $extendeds = get_extandeds();
        $items = array();
        foreach ($extendeds as $ext) {
            if ($ext->ext_type == $folder) {
                $ext_status = intval(is_isset($ext, 'ext_status'));
                $status = __('active', 'premium');
                if (0 == $ext_status) {
                    $status = __('inactive', 'premium');
                }
                $items[$ext->ext_key] = $ext->ext_title . ' [' . $status . ']';
            }
        }
        asort($items);

        return $items;
    }
}

if (!function_exists('list_extandeds_data')) {
    function list_extandeds_data($folder) {

        $extendeds = get_extandeds();
        $items = array();
        foreach ($extendeds as $ext) {
            if ($ext->ext_type == $folder) {
                $ext_status = intval(is_isset($ext, 'ext_status'));
                $status = __('active', 'premium');
                if (0 == $ext_status) {
                    $status = __('inactive', 'premium');
                }
                $items[$ext->ext_key] = array(
                    'title' => $ext->ext_title . ' [' . $status . ']',
                    'id' => $ext->id,
                );
            }
        }
        asort($items);

        return $items;
    }
}

if (!function_exists('file_safe_include')) {
    function file_safe_include($path) {

        $page_include = $path . ".php";
        if (is_file($page_include)) {
            include_once($page_include);
        }

    }
}

if (!function_exists('is_ssl_url')) {
    function is_ssl_url($url) {

        if (is_ssl()) {
            $url = str_replace('http://', 'https://', $url);
        } else {
            $url = str_replace('https://', 'http://', $url);
        }

        return $url;
    }
}

if (!function_exists('pn_create_nonce')) {
    function pn_create_nonce($nonce = '') {

        $key1 = AUTH_SALT;
        $key2 = NONCE_SALT;
        $nonce = intval($nonce);
        if (1 == $nonce) {
            return mb_substr(md5($key1 . $key2 . session_id()), 0, 10);
        } else {
            return mb_substr(md5($key1 . session_id() . $key2), 2, 12);
        }

    }
}

if (!function_exists('pn_verify_nonce')) {
    function pn_verify_nonce($word, $nonce = '') {

        $word = pn_string($word);
        if (pn_create_nonce($nonce) == $word) {
            return 1;
        } else {
            return 0;
        }

    }
}

if (!function_exists('pn_quicktags_script')) {
    function pn_quicktags_script($screen_id) {

        $link = PN_SITE_URL . 'premium_quicktags.js';
        $link .= '?place=' . $screen_id;

        return $link;
    }
}

if (!function_exists('get_mlink')) {
    function get_mlink($action) {

        $link = PN_SITE_URL . 'merchant-' . pn_strip_input($action) . '.html';

        return $link;
    }
}

if (!function_exists('get_api_link')) {
    function get_api_link($module, $vers, $endpoint) {

        $module = pn_string($module);
        $vers = pn_string($vers);
        $endpoint = pn_string($endpoint);

        $url = PN_SITE_URL . 'api/' . pn_strip_input($module) . '/' . pn_strip_input($vers) . '/' . pn_strip_input($endpoint) . '/';

        return $url;
    }
}

if (!function_exists('get_pn_action')) {
    function get_pn_action($action, $method = 'post') {

        $link = PN_SITE_URL . 'premium_site_action-' . pn_strip_input($action) . '.html';
        $link .= '?meth=' . $method . '&yid=' . pn_create_nonce(0) . '&ynd=0';

        if (function_exists('is_ml') and is_ml()) {
            $link .= '&lang=' . get_lang_key(get_locale());
        }

        return $link;
    }
}

if (!function_exists('pn_link')) {
    function pn_link($action = '', $method = '', $nonce = 1) {

        $nonce = intval($nonce);

        $action = trim($action);
        if (!$action) {
            $action = pn_strip_input(is_param_get('page'));
        }

        $method = trim($method);
        if ('post' != $method) {
            $method = 'get';
        }

        $link = PN_SITE_URL . 'premium_admin_action-' . $action . '.html';
        $link .= '?meth=' . $method . '&yid=' . pn_create_nonce($nonce) . '&ynd=' . $nonce;

        return $link;
    }
}

if (!function_exists('the_pn_link')) {
    function the_pn_link($action = '', $method = '', $nonce = 1) {

        echo pn_link($action, $method, $nonce);

    }
}

if (!function_exists('get_request_link')) {
    function get_request_link($action, $format = '', $lang = '', $args = '') {

        $format = pn_string($format);
        if (!$format) {
            $format = 'html';
        }
        if (!is_array($args)) {
            $args = array();
        }

        $lang = pn_string($lang);

        $link = PN_SITE_URL . 'request-' . pn_strip_input($action) . '.' . $format;

        if (is_ml() and $lang) {
            $args['lang'] = get_lang_key($lang);
        }

        $link = add_query_args($args, $link);

        return $link;
    }
}

if (!function_exists('_json_head')) {
    function _json_head() {
        header('Content-Type: application/json; charset=' . get_charset());
    }
}

if (!function_exists('_method')) {
    function _method($name) {

        $name = strtoupper($name);
        if ($name != $_SERVER['REQUEST_METHOD']) {
            $protocol = wp_get_server_protocol();
            header('Allow: ' . $name);
            header($protocol . ' 405 Method Not Allowed');
            header('Content-Type: text/plain; charset=' . get_charset());
            exit;
        }

    }
}

if (!function_exists('only_post')) {
    function only_post() {
        _method('post');
    }
}

if (!function_exists('_log_filter')) {
    function _log_filter($log, $name) {

        $log = apply_filters('before_ajax_form', $log, $name);
        $log = apply_filters($name . '_ajax_form', $log);

        return $log;
    }
}

if (!function_exists('get_sounds_premium')) {
    function get_sounds_premium() {

        $sounds = array();
        $dir = get_premium_dir() . "/audio/";
        $url = get_premium_url() . "audio/";
        if (is_dir($dir)) {
            $opendir = @opendir($dir);
            $abc_folders = array();
            while ($file = @readdir($opendir)) {
                if (strlen($file) > 0 and !strstr($file, '.') or strlen($file) < 1) {
                    $abc_folders[$file] = $file;
                }
            }

            asort($abc_folders);

            $new_sounds = array();
            foreach ($abc_folders as $folder) {
                $nf = $dir . $folder . '/';
                $ndir = @opendir($nf);
                while ($nfile = @readdir($ndir)) {
                    if ('.mp3' == substr($nfile, -4)) {
                        $new_sounds[$folder]['mp3'] = $url . $folder . '/' . $nfile;
                    }

                    if ('.ogg' == substr($nfile, -4)) {
                        $new_sounds[$folder]['ogg'] = $url . $folder . '/' . $nfile;
                    }
                }
            }

            $r = 0;
            foreach ($new_sounds as $key => $ns) {
                if (isset($ns['mp3']) and isset($ns['ogg'])) {
                    $r++;
                    $sounds[] = array(
                        'id' => $r,
                        'title' => $key,
                        'mp3' => $ns['mp3'],
                        'ogg' => $ns['ogg'],
                    );
                }
            }
        }

        return $sounds;
    }
}

if (!function_exists('_pn_debug')) {
    function _pn_debug() {
        if (WP_DEBUG) {
            error_reporting(E_ALL);
            @ini_set('display_errors', 1);
        }
    }
}

if (!function_exists('jserror_js_error_response')) {
    add_action('pn_js_error_response', 'jserror_js_error_response');
    function jserror_js_error_response($type) {
        ?>
        for (key in res) {
        console.log(key + ' = ' + res[key]);
        }
        <?php
    }
}

if (!function_exists('jserror_js_alert_response')) {
    add_action('pn_js_alert_response', 'jserror_js_alert_response');
    function jserror_js_alert_response() {
        ?>
        if (res['status_text']) {
        alert(res['status_text']);
        }
        <?php
    }
}

if (!function_exists('is_admin_newurl')) {
    function is_admin_newurl($item) {

        $item = pn_string($item);
        $new_item = pn_strip_symbols(replace_cyr($item));
        if (preg_match("/^[a-zA-z0-9]{3,250}$/", $new_item, $matches)) {
            $new_item = strtolower($new_item);
        } else {
            $new_item = '';
        }

        return apply_filters('is_admin_newurl', $new_item, $item);
    }
}

if (!function_exists('is_user')) {
    function is_user($item) {

        $item = pn_string($item);
        if (preg_match("/^[a-zA-z0-9]{3,30}$/", $item, $matches)) {
            $new_item = strtolower($item);
        } else {
            $new_item = '';
        }

        return apply_filters('is_user', $new_item, $item);
    }
}

if (!function_exists('get_user_role')) {
    function get_user_role($roles) {

        if (is_array($roles)) {
            foreach ($roles as $role) {
                return $role;
            }
        }

        return '';
    }
}

if (!function_exists('is_password')) {
    function is_password($item) {

        $item = pn_string($item);
        if (strlen($item) > 3 and strlen($item) < 50) {
            $new_item = $item;
        } else {
            $new_item = '';
        }

        return apply_filters('is_password', $new_item, $item);
    }
}

if (!function_exists('get_copy_date')) {
    function get_copy_date($year) {

        $time = current_time('timestamp');
        $y = date('Y', $time);
        if ($year != $y and $year < $y) {
            return $year . '-' . $y;
        } else {
            return $y;
        }
    }
}

if (!function_exists('esc_user_agent')) {
    function esc_user_agent($user_agent) {

        $new_user_agent = pn_maxf($user_agent, 200);
        $new_user_agent = pn_strip_input($new_user_agent);

        return apply_filters('esc_user_agent', $new_user_agent, $user_agent);
    }
}

if (!function_exists('get_user_agent')) {
    function get_user_agent() {

        $user_agent = is_isset($_SERVER, 'HTTP_USER_AGENT');
        return esc_user_agent($user_agent);
    }
}

if (!function_exists('pn_real_ip')) {
    function pn_real_ip() {

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ips = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ips = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } else {
            $ips = $_SERVER['REMOTE_ADDR'];
        }

        $ips_arr = explode(',', $ips);
        $ip = trim($ips_arr[0]);
        $ip = preg_replace('/[^0-9a-fA-F:.]/', '', $ip);
        $ip = pn_maxf($ip, 140);

        return apply_filters('pn_real_ip', $ip, $ips_arr);
    }
}

if (!function_exists('pn_has_ip')) {
    function pn_has_ip($list_ip, $ip = '') {

        $ip = pn_string($ip);
        if (!$ip) {
            $ip = pn_real_ip();
        }

        $tip = explode('.', $ip);
        if (is_array($list_ip)) {
            $items = $list_ip;
        } else {
            $list_ip = pn_string($list_ip);
            $items = explode("\n", $list_ip);
        }

        if ($ip and is_array($items) and count($items) > 0) {
            foreach ($items as $item_ip) {
                $item_ip = trim($item_ip);
                if ($item_ip) {
                    $item_ip_arr = explode('.', $item_ip);
                    if (count($item_ip_arr) > 0) {
                        $yes = 1;
                        foreach ($item_ip_arr as $k => $v) {
                            if (strlen($v) > 0) {
                                if ($v != is_isset($tip, $k)) {
                                    $yes = 0;
                                }
                            }
                        }
                        if ($yes) {
                            return 1;
                        }
                    }
                }
            }
        }

        return 0;
    }
}

if (!function_exists('get_sum_color')) {
    function get_sum_color($sum, $max = 'bgreen', $min = 'bred', $zero = '') {

        if (0 == $sum) {
            return '<span class="' . $zero . '">' . $sum . '</span>';
        } elseif ($sum > 0) {
            return '<span class="' . $max . '">' . $sum . '</span>';
        } else {
            return '<span class="' . $min . '">' . $sum . '</span>';
        }

    }
}

if (!function_exists('count_urls')) {
    function count_urls($text) {

        $count = 0;
        if (preg_match_all('/(http:\/\/|https:\/\/)?(www)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.-\?\%\&]*)*\/?/i', $text, $matches)) {
            $count = count($matches[0]);
        }

        return $count;
    }
}

if (!function_exists('strstr_array')) {
    function strstr_array($string, $arr_word) {

        $string = trim($string);
        $string = mb_strtolower($string);
        if (is_array($arr_word)) {
            foreach ($arr_word as $word) {
                $word = trim($word);
                $word = mb_strtolower($word);
                if (strlen($word) > 0) {
                    if (strstr($string, $word)) {
                        return 1;
                    }
                }
            }
        }

        return 0;
    }
}

if (!function_exists('array_key_first')) {
    function array_key_first(array $arr) {

        foreach ($arr as $key => $unused) {
            return $key;
        }

        return '';
    }
}

if (!function_exists('check_array_map')) {
    function check_array_map($array, $map) {

        if (is_array($array) and is_array($map)) {
            $new_array = array();
            foreach ($map as $map_key) {
                $new_array[$map_key] = is_isset($array, $map_key);
            }

            return $new_array;
        }

        return $array;
    }
}

if (!function_exists('_ext_set_key')) {
    function _ext_set_key($script, $type, $data_id, $r = 1) {
        global $wpdb;

        $r = intval($r);
        if (strlen($script) < 2) {
            $script = 'none';
        }
        $now_script = $script;
        if (1 != $r) {
            $now_script .= $r;
        }
        $cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exts WHERE ext_type = '$type' AND ext_key = '$now_script' AND id != '$data_id'");
        if ($cc > 0) {
            $r++;
            return _ext_set_key($script, $type, $data_id, $r);
        } else {
            return $now_script;
        }
    }
}

if (!function_exists('uniq_data_key')) {
    function uniq_data_key($script, $item, $r = 1) {

        $r = intval($r);
        $now_script = $script;
        if (1 != $r) {
            $now_script .= $r;
        }
        if (isset($item[$now_script])) {
            $r++;
            return uniq_data_key($script, $item, $r);
        } else {
            return $now_script;
        }

    }
}

if (!function_exists('pn_strstr_array')) {
    function pn_strstr_array($string, $arr_word) {

        $string = trim($string);
        $string = mb_strtolower($string);
        if (is_array($arr_word)) {
            foreach ($arr_word as $word) {
                $word = trim($word);
                $word = mb_strtolower($word);
                if (strlen($word) > 0) {
                    if (strstr($string, $word)) {
                        return 1;
                    }
                }
            }
        }

        return 0;
    }
}

if (!function_exists('pn_array_sort')) {
    function pn_array_sort($array, $key = '', $order = 'asc', $type = 'text') {

        $key = pn_string($key);
        $order = pn_string($order);
        $type = pn_string($type);
        $order = strtolower($order);
        if ('asc' != $order) {
            $order = 'desc';
        }

        if (strlen($key) > 0) {
            $d_array = array();
            foreach ($array as $array_key => $array_value) {
                $d_array[$array_key] = is_isset($array_value, $key);
            }
            if ('asc' == $order) {
                if ('num' == $type) {
                    asort($d_array, SORT_NUMERIC);
                } else {
                    asort($d_array);
                }
            } else {
                if ('num' == $type) {
                    arsort($d_array, SORT_NUMERIC);
                } else {
                    arsort($d_array);
                }
            }
            $new_array = array();
            foreach ($d_array as $d_array_key => $d_array_value) {
                $new_array[$d_array_key] = $array[$d_array_key];
            }

            return $new_array;
        }

        return $array;
    }
}

if (!function_exists('premium_encrypt')) {
    function premium_encrypt($string, $hash, $only_crypt = 0) {

        $string = pn_string($string);
        $hash = pn_string($hash);
        $only_crypt = intval($only_crypt);
        if (function_exists('openssl_encrypt') and $hash) {
            $cipher = "AES-128-CBC";
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext_raw = openssl_encrypt($string, $cipher, $hash, OPENSSL_RAW_DATA, $iv);
            $hmac = hash_hmac('sha256', $ciphertext_raw, $hash, true);
            $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);
            return 'pnhash:' . $ciphertext;
        }

        if ($only_crypt) {
            return '';
        } else {
            return $string;
        }

    }
}

if (!function_exists('premium_decrypt')) {
    function premium_decrypt($string, $hash, $only_crypt = 0) {

        $string = pn_string($string);
        $hash = pn_string($hash);
        $only_crypt = intval($only_crypt);
        if (strlen($string) > 0 and strstr($string, 'pnhash:') and function_exists('openssl_decrypt') and $hash) {
            $ciphertext = str_replace('pnhash:', '', $string);
            $c = base64_decode($ciphertext);
            $cipher = "AES-128-CBC";
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = substr($c, 0, $ivlen);
            $sha2len = 32;
            $hmac = substr($c, $ivlen, $sha2len);
            $ciphertext_raw = substr($c, $ivlen + $sha2len);
            $plaintext = openssl_decrypt($ciphertext_raw, $cipher, $hash, OPENSSL_RAW_DATA, $iv);
            $calcmac = hash_hmac('sha256', $ciphertext_raw, $hash, true);
            if (hash_equals($hmac, $calcmac)) {
                return $plaintext;
            }
        }

        if ($only_crypt) {
            return '';
        } else {
            return $string;
        }
    }
}

if (!function_exists('pn_db_insert')) {
    function pn_db_insert($db_table, $sqls, $chunk = 0) {
        global $wpdb;

        $inserts = array();
        $db_table = trim($db_table);
        $chunk = intval($chunk);
        if ($chunk < 1) {
            $chunk = apply_filters('chunk_db_part', 100);
        }

        if (is_array($sqls) and $db_table) {
            $sqls_chunk = array_chunk($sqls, $chunk);
            foreach ($sqls_chunk as $sqls) {
                $values = array();
                $arr = array();
                $names = array();
                foreach ($sqls as $sql_data) {
                    if (is_array($sql_data)) {
                        foreach ($sql_data as $sql_key => $sql_value) {
                            $names[$sql_key] = "`" . $sql_key . "`";
                        }
                    }
                }

                $s = -1;
                foreach ($sqls as $sql_data) {
                    $s++;
                    if (is_array($sql_data)) {
                        $arr[$s] = array();
                        foreach ($names as $key_name => $key_db) {
                            $arr_value = '';
                            if (isset($sql_data[$key_name])) {
                                $arr_value = $sql_data[$key_name];
                            }
                            $arr[$s][] = "'" . addslashes($arr_value) . "'";
                        }
                        $values[] = "(" . implode(',', $arr[$s]) . ")";
                    }
                }

                if (count($values) > 0 and count($names) > 0) {
                    $return_query = "INSERT INTO $db_table (" . implode(',', $names) . ") VALUES" . implode(', ', $values);
                    $wpdb->query($return_query);
                }
            }
        }

        return $inserts;
    }
}

if (!function_exists('pn_object_replace')) {
    function pn_object_replace($object = '', $array = '') {

        $object_array = (array)$object;
        if (is_array($object_array) and is_array($array)) {
            foreach ($array as $arr_k => $arr_v) {
                $object_array[$arr_k] = $arr_v;
            }
            $object = (object)$object_array;
        }

        return $object;
    }
}

if (!function_exists('pn_array_unset')) {
    function pn_array_unset($array, $key) {

        if (is_array($key)) {
            foreach ($key as $key_k) {
                if (isset($array[$key_k])) {
                    unset($array[$key_k]);
                }
            }
        } else {
            if (isset($array[$key])) {
                unset($array[$key]);
            }
        }

        return $array;
    }
}

if (!function_exists('pn_array_insert')) {
    function pn_array_insert($array, $key, $new_array = '', $method = '') {

        $key = pn_string($key);
        $method = pn_string($method);
        if ('before' != $method) {
            $method = 'after';
        }
        if (is_array($array) and is_array($new_array)) {
            $set_array = array();
            if ($key and isset($array[$key])) {
                foreach ($array as $array_key => $array_value) {
                    $array_key = pn_string($array_key);
                    if ($array_key == $key and 'before' == $method) {
                        foreach ($new_array as $new_array_key => $new_array_value) {
                            $set_array[$new_array_key] = $new_array_value;
                        }
                    }
                    $set_array[$array_key] = $array_value;
                    if ($array_key == $key and 'after' == $method) {
                        foreach ($new_array as $new_array_key => $new_array_value) {
                            $set_array[$new_array_key] = $new_array_value;
                        }
                    }
                }
            } else {
                $set_array = $array;
                foreach ($new_array as $new_array_key => $new_array_value) {
                    $set_array[$new_array_key] = $new_array_value;
                }
            }

            return $set_array;
        }

        return $array;
    }
}

if (!function_exists('list_checks_top')) {
    function list_checks_top($lists, $m_arr) {

        $new_lists = array();
        foreach ($m_arr as $m) {
            if (isset($lists[$m])) {
                $new_lists[$m] = $lists[$m];
            }
        }
        foreach ($lists as $list_k => $list_v) {
            if (!in_array($list_k, $m_arr)) {
                $new_lists[$list_k] = $list_v;
            }
        }

        return $new_lists;
    }
}

if (!function_exists('get_check_list')) {
    function get_check_list($lists, $name, $class = array(), $max_height = '', $search = 0) {

        $search = intval($search);
        $max_height = intval($max_height);
        if ($max_height < 1) {
            $max_height = 200;
        }

        $html = '<div class="checkbox_all_div">';

        $all_ch = 'checked="checked"';
        if (is_array($lists)) {
            foreach ($lists as $list) {
                $nc = intval(is_isset($list, 'checked'));
                if (!$nc) {
                    $all_ch = '';
                }
            }
        }

        if (1 == $search) {
            $html .= '<div class="checkbox_all_searchdiv"><input type="search" name="" placeholder="' . __('Search...', 'premium') . '" class="checkbox_all_search" autocomplete="off" value="" /></div>';
        }

        $html .= '<div><label style="font-weight: 500;"><input class="checkbox_all" type="checkbox" ' . $all_ch . ' name="" autocomplete="off" value="0"> <span class="' . is_isset($class, '0') . '">' . __('Check all/Uncheck all', 'premium') . '</span></label></div>';
        $html .= '<div class="checkbox_all_ins" style="max-height: ' . $max_height . 'px;">';

        if (is_array($lists)) {
            foreach ($lists as $list) {
                $ch = '';
                $lch = intval(is_isset($list, 'checked'));
                if ($lch) {
                    $ch = 'checked="checked"';
                }

                $now_name = $name;
                if (isset($list['name'])) {
                    $now_name = trim($list['name']);
                }

                $search = is_isset($list, 'title');
                if (isset($list['search'])) {
                    $search = $list['search'];
                }
                $html .= '<div style="padding: 1px 0;" class="checkbox_once_div"><label><input type="checkbox" class="checkbox_once" name="' . $now_name . '" ' . $ch . ' ' . is_isset($list, 'atts') . ' autocomplete="off" value="' . is_isset($list, 'value') . '"> <span class="' . is_isset($class, is_isset($list, 'value')) . ' in_check" data-s="' . esc_attr($search) . '">' . is_isset($list, 'title') . '</span></label></div>';
            }
        }

        $html .= '<div class="premium_clear"></div></div>';
        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('pn_enable_filetype')) {
    function pn_enable_filetype() {

        $filetype = array(
            '.gif' => 'image/gif',
            '.jpg' => 'image/jpeg',
            '.jpeg' => 'image/jpeg',
            '.jpe' => 'image/jpeg',
            '.png' => 'image/png',
        );
        $filetype = apply_filters('pn_enable_filetype', $filetype);

        return $filetype;
    }
}

if (!function_exists('pn_mime_filetype')) {
    function pn_mime_filetype($file) {

        $filetype = '';
        if (function_exists('mime_content_type')) {
            $filetype = mime_content_type($file['tmp_name']);
            if ('image/png' == $filetype) {
                $filetype = '.png';
            } elseif ('image/jpeg' == $filetype) {
                $filetype = '.jpg';
            } elseif ('image/gif' == $filetype) {
                $filetype = '.gif';
            }
        }
        if (!$filetype) {
            $filetype = strtolower(strrchr($file['name'], "."));
        }

        return apply_filters('pn_mime_filetype', $filetype, $file);
    }
}

if (!function_exists('pn_max_upload')) {
    function pn_max_upload() {

        $max_upload_size = wp_max_upload_size();
        if (!$max_upload_size) {
            $max_upload_size = 0;
        }

        $max_mb = 0;
        if ($max_upload_size > 0) {
            $max_mb = ($max_upload_size / 1024 / 1024);
        }

        $max_mb = apply_filters('pn_max_upload', $max_mb);

        return $max_mb;
    }
}

if (!function_exists('get_sklon')) {
    function get_sklon($num, $text1, $text2, $text3) {

        $num = abs($num);
        $nums = $num % 100;

        if ($nums > 4 and $nums < 21) {
            return str_replace('%', $num, $text3);
        }

        $nums = $num % 10;
        if (0 == $nums or $nums > 4) {
            return str_replace('%', $num, $text3);
        }

        if (1 == $nums) {
            return str_replace('%', $num, $text1);
        }

        return str_replace('%', $num, $text2);
    }
}

if (!function_exists('get_month_title')) {
    function get_month_title($arg, $months = array()) {

        $arg = intval($arg);

        if (!is_array($months) or is_array($months) and count($months) < 7) {
            $months = array('',
                'Jan.',
                'Feb.',
                'Mar.',
                'Apr.',
                'May',
                'June',
                'July',
                'Aug.',
                'Sep.',
                'Oct.',
                'Nov.',
                'Dec.'
            );
        }

        return is_isset($months, $arg);
    }
}

if (!function_exists('is_older_browser')) {
    function is_older_browser() {

        $older_browser = false;

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0')) {
                $older_browser = true;
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0')) {
                $older_browser = true;
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0')) {
                $older_browser = true;
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 9.0')) {
                $older_browser = true;
            }
        }

        $older_browser = apply_filters('is_older_browser', $older_browser);

        return $older_browser;
    }
}

if (!function_exists('get_browser_name')) {
    function get_browser_name($user_agent, $unknown = 'Unknown') {

        $user_agent = (string)$user_agent;
        if (false !== strpos($user_agent, "Firefox")) {
            $browser = 'Firefox';
        } elseif (false !== strpos($user_agent, "OPR")) {
            $browser = 'Opera';
        } elseif (false !== strpos($user_agent, "Chrome")) {
            $browser = 'Chrome';
        } elseif (false !== strpos($user_agent, "MSIE")) {
            $browser = 'Internet Explorer';
        } elseif (false !== strpos($user_agent, "Safari")) {
            $browser = 'Safari';
        } else {
            $browser = $unknown;
        }

        $browser = apply_filters('get_browser_name', $browser, $user_agent);

        return $browser;
    }
}

if (!function_exists('pn_site_name')) {
    function pn_site_name() {

        return pn_strip_input(get_bloginfo('sitename'));
    }
}

if (!function_exists('pn_ind')) {
    function pn_ind() {

        $arr = array();
        $arr['ind'] = 1;
        $arr['error'] = '';

        return $arr;
    }
}

if (!function_exists('get_uniq_words')) {
    function get_uniq_words($keys) {

        $keys_arr = explode(',', $keys);
        $new_keys = array();
        foreach ($keys_arr as $key) {
            $key = trim($key);
            if (strlen($key) > 0) {
                $new_keys[] = $key;
            }
        }
        $new_keys = array_unique($new_keys);

        return implode(',', $new_keys);
    }
}

if (!function_exists('get_cptgn')) {
    function get_cptgn($text) {

        $text = pn_string($text);
        $txt = iconv('UTF-8', 'CP1251', $text);

        return $txt;
    }
}

if (!function_exists('get_tgncp')) {
    function get_tgncp($text) {

        $text = pn_string($text);
        $txt = iconv('CP1251', 'UTF-8', $text);

        return $txt;
    }
}

if (!function_exists('rez_exp')) {
    function rez_exp($text) {

        $text = trim($text);
        $text = str_replace(array(';', '"'), '', $text);

        return $text;
    }
}

if (!function_exists('rep_dot')) {
    function rep_dot($text) {

        $text = str_replace('.', ',', $text);

        return $text;
    }
}

if (!function_exists('get_exvar')) {
    function get_exvar($zn, $arr) {

        return is_isset($arr, $zn);
    }
}

if (!function_exists('pn_download_file')) {
    function pn_download_file($file, $file_name, $unlink = 0) {

        $s = wp_check_filetype($file, null);
        $unlink = intval($unlink);
        if (is_file($file)) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: ' . is_isset($s, 'type') . '; charset=' . get_charset());
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $file_name);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            if ($unlink) {
                unlink($file);
            }
            exit;
        }

    }
}

if (!function_exists('get_request_action')) {
    function get_request_action() {

        $action = false;
        if (isset($_REQUEST['action']) and -1 != $_REQUEST['action']) {
            $action = $_REQUEST['action'];
        }
        if (isset($_REQUEST['action2']) and -1 != $_REQUEST['action2']) {
            $action = $_REQUEST['action2'];
        }

        return $action;
    }
}

if (!function_exists('premium_table_list')) {
    function premium_table_list() {

        $page = pn_strip_input(is_param_get('page'));
        $class_name = $page . '_Table_List';
        if (class_exists($class_name)) {
            $table = new $class_name();
            $table->display();
        } else {
            echo 'Class not found';
        }
    }
}

if (!function_exists('pn_admin_prepare_lost')) {
    function pn_admin_prepare_lost($lost) {

        $losted = array();
        if (is_array($lost)) {
            $losted = $lost;
        } elseif (is_string($lost)) {
            $l = explode(',', $lost);
            foreach ($l as $lk => $lv) {
                $lv = trim($lv);
                if ($lv) {
                    $losted[$lk] = $lv;
                }
            }
        }

        return $losted;
    }
}

if (!function_exists('is_pn_date')) {
    function is_pn_date($date, $format = 'd.m.Y') {

        $date = pn_string($date);
        $format = preg_quote($format);
        $format = str_replace(array('d', 'm'), '[0-9]{1,2}', $format);
        $format = str_replace(array('Y'), '[0-9]{4}', $format);
        if (preg_match("/^$format/", $date, $matches)) {
            return $date;
        }

        return '';
    }
}

if (!function_exists('get_pn_date')) {
    function get_pn_date($date, $format = 'd.m.Y') {

        $date = pn_strip_input($date);
        if ($date and '0000-00-00' != $date) {
            $time = strtotime($date);
            return date($format, $time);
        }

        return '';
    }
}

if (!function_exists('get_pn_time')) {
    function get_pn_time($date, $format = 'd.m.Y H:i') {

        $date = pn_strip_input($date);
        if ($date and '0000-00-00 00:00:00' != $date) {
            $time = strtotime($date);
            return date($format, $time);
        }

        return '';
    }
}

if (!function_exists('pn_sfilter')) {
    function pn_sfilter($arg) {

        $arg = trim((string)$arg);
        $arg = str_replace('%', '', $arg);

        return $arg;
    }
}

if (!function_exists('pn_admin_filter_data')) {
    function pn_admin_filter_data($url = '', $lost = '') {

        $url = trim($url);
        if (!$url) {
            $url = is_param_post('_wp_http_referer');
        }
        $url = esc_url($url);

        $losted = pn_admin_prepare_lost($lost);

        $url = remove_query_args($losted, $url);

        return $url;
    }
}

if (!function_exists('add_query_args')) {
    function add_query_args($data, $string) {

        $string = pn_string($string);
        $string = str_replace(array('&#038;', '&amp;'), '&', $string);

        if (!is_array($data)) {
            $data = array();
        }

        $parse_url = parse_url($string);

        $url = '';
        $scheme = trim(is_isset($parse_url, 'scheme'));
        if ($scheme) {
            $url .= $scheme . '://';
        }
        $url .= trim(is_isset($parse_url, 'host'));
        $url .= trim(is_isset($parse_url, 'path'));
        $query = trim(is_isset($parse_url, 'query'));

        if ($query) {
            parse_str($query, $pars_query);
        } else {
            $pars_query = array();
        }

        foreach ($data as $key => $value) {
            $key = pn_string($key);
            $value = pn_string($value);
            $pars_query[$key] = $value;
        }
        $query = http_build_query($pars_query, '', '&');

        if ($query) {
            $url .= '?' . $query;
        }

        return $url;
    }
}

if (!function_exists('remove_query_args')) {
    function remove_query_args($key, $string) {

        $string = pn_string($string);
        $string = str_replace(array('&#038;', '&amp;'), '&', $string);

        if (is_array($key)) {
            $key_arr = $key;
        } else {
            $key = pn_string($key);
            $key_arr = explode(',', $key);
        }

        $parse_url = parse_url($string);

        $url = '';
        $scheme = trim(is_isset($parse_url, 'scheme'));
        if ($scheme) {
            $url .= $scheme . '://';
        }
        $url .= trim(is_isset($parse_url, 'host'));
        $url .= trim(is_isset($parse_url, 'path'));
        $query = trim(is_isset($parse_url, 'query'));

        if ($query) {
            parse_str($query, $pars_query);
            foreach ($key_arr as $key_del) {
                $key_del = trim($key_del);
                if (strlen($key_del) > 0) {
                    if (isset($pars_query[$key_del])) {
                        unset($pars_query[$key_del]);
                    }
                }
            }
            $query = http_build_query($pars_query, '', '&');
        }
        if ($query) {
            $url .= '?' . $query;
        }

        return $url;
    }
}

if (!function_exists('remove_unused_shortcode')) {
    function remove_unused_shortcode($content) {

        $content = preg_replace("!\[(.*?)\]!si", '', $content);

        return $content;
    }
}

if (!function_exists('replace_tags')) {
    function replace_tags($array, $content, $replace = 0) {

        $replace = intval($replace);
        $arr_key = $arr_value = array();
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $arr_key[] = $key;
                $arr_value[] = $value;
            }
        }
        $content = str_replace($arr_key, $arr_value, $content);
        if ($replace) {
            $content = remove_unused_shortcode($content);
        }

        return $content;
    }
}

if (!function_exists('is_reviews_hash')) {
    function is_reviews_hash($hash) {

        $hash = pn_strip_input($hash);
        if (preg_match("/^[a-zA-z0-9]{15,32}$/", $hash, $matches)) {
            $r = $hash;
        } else {
            $r = 0;
        }

        return $r;
    }
}

if (!function_exists('is_phone')) {
    function is_phone($phone) {

        $phone = pn_string($phone);
        $new_phone = preg_replace('/[^+0-9]/', '', $phone);
        $new_phone = apply_filters('is_phone', $new_phone, $phone);

        return $new_phone;
    }
}

if (!function_exists('is_place_url')) {
    function is_place_url($url, $class = 'current') {

        $http = 'http://';
        if (is_ssl()) {
            $http = 'https://';
        }
        $url_site = $http . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        if ($url == $url_site) {
            return $class;
        }

        return '';
    }
}

if (!function_exists('pn_allow_uv')) {
    function pn_allow_uv($key) {

        $plugin = get_plugin_class();
        $uf = $plugin->get_option('user_fields');

        return intval(is_isset($uf, $key));
    }
}

if (!function_exists('pn_change_uv')) {
    function pn_change_uv($key) {

        $plugin = get_plugin_class();
        $uf = $plugin->get_option('user_fields_change');

        return intval(is_isset($uf, $key));
    }
}

if (!function_exists('strip_uf')) {
    function strip_uf($value, $filter) {

        $value = trim($value);
        $new_value = '';
        if ('user_phone' == $filter) {
            $new_value = is_phone($value);
        } elseif ('user_email' == $filter) {
            $new_value = is_email($value);
        } elseif ('user_website' == $filter) {
            $new_value = esc_url($value);
        } else {
            $new_value = pn_strip_input($value);
        }
        $new_value = pn_maxf_mb($new_value, 500);
        $new_value = apply_filters('strip_uf', $new_value, $value, $filter);

        return $new_value;
    }
}

if (!function_exists('get_curl_parser')) {
    function get_curl_parser($url, $options = array(), $place = '', $pointer = '', $pointer2 = '') {

        if (!is_array($options)) {
            $options = array();
        }

        $arg = array(
            'output' => '',
            'err' => 1,
            'info' => '',
            'code' => '',
        );

        if ($ch = curl_init()) {

            $curl_options = array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_REFERER => '',
                CURLOPT_TIMEOUT => 20,
                CURLOPT_CONNECTTIMEOUT => 20,
                CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36 OPR/60.0.3255.27",
            );
            foreach ($options as $k => $v) {
                $curl_options[$k] = $v;
            }
            $curl_options = apply_filters('_curl_parser', $curl_options, $place, $pointer, $pointer2);
            curl_setopt_array($ch, $curl_options);

            $arg['output'] = curl_exec($ch);
            $arg['err'] = curl_errno($ch);
            $arg['info'] = curl_getinfo($ch);
            $arg['code'] = is_isset($arg['info'], 'http_code');

            curl_close($ch);
        } else {
            $arg['err'] = '901';
        }

        return $arg;
    }
}

if (!function_exists('get_multicurl_parser')) {
    function get_multicurl_parser($p_links, $opts) {

        $multi = curl_multi_init();

        foreach ($p_links as $i_key => $data) {
            $id = $i_key;
            if (isset($data['id']) and $data['id']) {
                $id = $data['id'];
            }
            $url = $data['url'];
            $post_data = trim(is_isset($data, 'post_data'));
            $headers = array();
            if (isset($data['headers']) and is_array($data['headers'])) {
                $headers = $data['headers'];
            }

            $ch = curl_init();

            $curl_setopt = array(
                CURLOPT_URL => $url,
            );
            foreach ($opts as $opt_key => $opt_value) {
                $curl_setopt[$opt_key] = $opt_value;
            }

            if (is_array($headers) and count($headers) > 0) {
                $curl_options[CURLOPT_HTTPHEADER] = $headers;
            }

            if ($post_data) {
                $curl_setopt[CURLOPT_POST] = true;
                $curl_setopt[CURLOPT_POSTFIELDS] = $post_data;
            }

            $curl_setopt = apply_filters('_curl_setopt_array_multicurl', $curl_setopt);

            $setopt = array();
            if (isset($data['setopt']) and is_array($data['setopt'])) {
                foreach ($data['setopt'] as $setopt_key => $setopt_value) {
                    $curl_setopt[$setopt_key] = $setopt_value;
                }
            }

            curl_setopt_array($ch, $curl_setopt);
            curl_multi_add_handle($multi, $ch);

            $p_links[$id]['resource'] = $ch;
        }

        $mrc = curl_multi_exec($multi, $active);

        $active = null;

        do {
            $mrc = curl_multi_exec($multi, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active and $mrc == CURLM_OK) {
            if (-1 == curl_multi_select($multi)) {
                continue;
            }
            do {
                $mrc = curl_multi_exec($multi, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        foreach ($p_links as $i_key => $data) {
            $resource = is_isset($data, 'resource');
            if ($resource) {
                $p_links[$i_key]['output'] = curl_multi_getcontent($resource);
                $p_links[$i_key]['error'] = curl_errno($resource);
                $p_links[$i_key]['info'] = curl_getinfo($resource);
                curl_multi_remove_handle($multi, $resource);
            }
        }

        curl_multi_close($multi);

        return $p_links;
    }
}

if (!function_exists('delete_ext_files')) {
    function delete_ext_files($path = '', $true = '') {

        if (!is_array($true)) {
            $true = array('.gif', '.jpg', '.jpeg', '.jpe', '.png', '.csv', '.htaccess', '.txt', '.xml', '.dat', '.svg');
        }
        if (is_dir($path)) {
            $dir = @opendir($path);
            while ($file = @readdir($dir)) {
                if (is_file($path . $file)) {
                    $ext = strtolower(strrchr($file, "."));
                    if (!in_array($ext, $true)) {
                        @unlink($path . $file);
                    }
                } elseif (is_dir($path . $file)) {
                    if ('.' != substr($file, 0, 1)) {
                        delete_ext_files($path . $file . '/', $true);
                    }
                }
            }
        }
    }
}

if (!function_exists('files_del_dir')) {
    function files_del_dir($directory, $type = '.png') {

        $type = pn_string($type);
        if (is_dir($directory) and $type) {
            foreach (glob($directory . "*" . $type) as $file) {
                @unlink($file);
            }
        }

    }
}

if (!function_exists('full_del_dir')) {
    function full_del_dir($directory) {

        if (is_dir($directory)) {
            $dir = @opendir($directory);
            while ($file = @readdir($dir)) {
                if (is_file($directory . "/" . $file)) {
                    @unlink($directory . "/" . $file);
                } elseif (is_dir($directory . "/" . $file) and "." != $file and ".." != $file) {
                    full_del_dir($directory . "/" . $file);
                }
            }
            @closedir($dir);
            @rmdir($directory);
        }

    }
}

if (!function_exists('get_session_id')) {
    function get_session_id() {

        $session_key = pn_strip_input(get_pn_cookie('premium_session_id'));
        if ($session_key) {
            $data = pn_maxf(pn_strip_input(is_isset($_SERVER, 'HTTP_USER_AGENT')), 300) . $session_key;
            $session_salt = mb_substr(AUTH_SALT, 0, 6) . mb_substr(NONCE_SALT, 10, 14);
            if (defined('PN_SESSION_SALT')) {
                $session_salt = PN_SESSION_SALT;
            }
            return pn_strip_input(hash_hmac('sha256', $data, $session_salt));
        } else {
            $session_key = pn_strip_input(md5(session_id()));
        }

        return $session_key;
    }
}

if (!function_exists('get_theme_option')) {
    function get_theme_option($option_name, $array = '') {

        if (!is_array($array)) {
            $array = array();
        }
        $option_name = pn_string($option_name);

        $change = get_option($option_name);
        $now_change = array();
        foreach ($array as $opt) {
            $now_change[$opt] = ctv_ml(is_isset($change, $opt));
        }

        return $now_change;
    }
}

if (!function_exists('get_caps_name')) {
    function get_caps_name($name) {

        $name = pn_strip_input($name);
        if ($name) {
            $newname = mb_strtoupper(mb_substr($name, 0, 1)) . mb_strtolower(mb_substr($name, 1, mb_strlen($name)));
            return $newname;
        }

        return '';
    }
}

if (!function_exists('get_contact')) {
    function get_contact($value, $key, $title = '') {
        $key = trim($key);
        $title = trim($title);
        if ('telegram' == $key) {
            if (strlen($title) < 1) {
                $title = '@' . pn_strip_input(str_replace('@', '', $value));
            }
            $value = '<a href="https://t.me/' . pn_strip_input(str_replace('@', '', $value)) . '">' . $title . '</a>';
        } elseif ('tm' == $key) {
            if (strlen($title) < 1) {
                $title = '@' . pn_strip_input(str_replace('@', '', $value));
            }
            $value = '<a href="tg://resolve?domain=' . pn_strip_input(str_replace('@', '', $value)) . '">' . $title . '</a>';
        } elseif ('viber' == $key) {
            if (strlen($title) < 1) {
                $title = pn_strip_input($value);
            }
            $value = '<a href="viber://chat?number=' . pn_strip_input($value) . '">' . $title . '</a>';
        } elseif ('whatsapp' == $key) {
            if (strlen($title) < 1) {
                $title = pn_strip_input($value);
            }
            $value = '<a href="https://api.whatsapp.com/send?phone=' . pn_strip_input($value) . '">' . $title . '</a>';
        } elseif ('jabber' == $key) {
            if (strlen($title) < 1) {
                $title = pn_strip_input($value);
            }
            $value = '<a href="xmpp:' . pn_strip_input($value) . '">' . $title . '</a>';
        } elseif ('skype' == $key) {
            if (strlen($title) < 1) {
                $title = pn_strip_input($value);
            }
            $value = '<a href="skype:' . pn_strip_input($value) . '?add" title="' . __('Add to skype') . '">' . $title . '</a>';
        } elseif ('email' == $key) {
            if (strlen($title) < 1) {
                $title = antispambot(pn_strip_input($value));
            }
            $value = '<a href="mailto:' . antispambot(pn_strip_input($value)) . '">' . $title . '</a>';
        } else {
            $value = pn_strip_input($value);
        }

        return $value;
    }
}

if (!function_exists('get_blog_url')) {
    function get_blog_url() {

        $sof = get_option('show_on_front');
        if ('page' == $sof) {
            $blog_url = get_permalink(get_option('page_for_posts'));
        } else {
            $blog_url = get_site_url_ml();
        }

        return $blog_url;
    }
}

if (!function_exists('_span_break_words')) {
    function _span_break_words($text, $count = 30) {

        $count = intval($count);
        $count = apply_filters('count_span_break_words', $count);
        $text_arr = explode(' ', $text);
        $new_text = '';
        foreach ($text_arr as $ta) {
            if (strlen($ta) >= $count) {
                $new_text .= '<span class="break_words">' . $ta . '</span>' . ' ';
            } else {
                $new_text .= $ta . ' ';
            }
        }

        return $new_text;
    }
}

if (!function_exists('get_pn_excerpt')) {
    function get_pn_excerpt($item, $count = 15) {

        if (function_exists('ctv_ml')) {
            $excerpt = pn_strip_text(ctv_ml($item->post_excerpt));
            if (strlen($excerpt) > 0) {
                return $excerpt;
            } else {
                return wp_trim_words(pn_strip_text(ctv_ml($item->post_content)), $count);
            }
        } else {
            $excerpt = pn_strip_text($item->post_excerpt);
            if (strlen($excerpt) > 0) {
                return $excerpt;
            } else {
                return wp_trim_words(pn_strip_text($item->post_content), $count);
            }
        }

    }
}

if (!function_exists('get_form_replace_array')) {
    function get_form_replace_array($form_name = '', $prefix = '', $place = 'shortcode') {

        $array = array();
        $array = apply_filters('replace_array_' . $form_name, $array, $prefix, $place);

        return $array;
    }
}

if (!function_exists('get_form_fields')) {
    function get_form_fields($form_name = '', $place = 'shortcode') {

        $ui = wp_get_current_user();

        $items = array();
        $items = apply_filters($form_name . '_filelds', $items, $ui, $place);
        $items = apply_filters('get_form_filelds', $items, $form_name, $ui, $place);

        return $items;
    }
}

if (!function_exists('get_inline_atts')) {
    function get_inline_atts($atts) {

        $atts_inline = '';
        if (is_array($atts)) {
            foreach ($atts as $att_v => $att_t) {
                $atts_inline .= $att_v . ' = "' . $att_t . '" ';
            }
        }

        return $atts_inline;
    }
}

if (!function_exists('prepare_form_fileds')) {
    function prepare_form_fileds($items, $filter, $prefix) {
        global $form_field_num;

        $form_field_num = intval($form_field_num);
        $form_field_num++;

        $ui = wp_get_current_user();
        $html = '';
        if (is_array($items)) {
            foreach ($items as $name => $data) {
                $type = trim(is_isset($data, 'type'));
                $name = trim(is_isset($data, 'name'));
                $title = trim(is_isset($data, 'title'));
                $req = intval(is_isset($data, 'req'));
                $atts = is_isset($data, 'atts');
                if (!is_array($atts)) {
                    $atts = array();
                }
                $value = is_isset($data, 'value');
                $tooltip = pn_string(ctv_ml(is_isset($data, 'tooltip')));
                $hidden = intval(is_isset($data, 'hidden'));

                $div_class = array(
                    'form_field_line' => 'form_field_line',
                    $prefix . '_line' => $prefix . '_line',
                    'type_' . $type => 'type_' . $type,
                    'field_name_' . $name => 'field_name_' . $name,
                );
                if ($hidden) {
                    $div_class['hidden_line'] = 'hidden_line';
                }

                $req_html = '';
                if ($req) {
                    $req_html = ' <span class="req">*</span>';
                }

                $tooltip_div = '';
                $tooltip_span = '';
                $tooltip_class = '';
                if (strlen($tooltip) > 0) {
                    $tooltip_span = '<span class="field_tooltip_label"></span>';
                    $div_class['has_tooltip'] = 'has_tooltip';
                    $tooltip_div = '<div class="field_tooltip_div"><div class="field_tooltip_abs"></div><div class="field_tooltip">' . apply_filters('comment_text', $tooltip) . '</div></div>';
                }

                if (strlen($title) > 0) {
                    $div_class['has_title'] = 'has_title';
                }

                if (isset($atts['class'])) {
                    $atts['class'] .= ' ' . $prefix . '_' . $type;
                } else {
                    $atts['class'] = $prefix . '_' . $type;
                }

                if (!isset($atts['autocomplete'])) {
                    $atts['autocomplete'] = 'off';
                }

                if (isset($atts['id'])) {
                    $field_id = 'id="' . $atts['id'] . '"';
                    unset($atts['id']);
                } else {
                    $field_id = 'id="form_field_id-' . $form_field_num . '-' . $name . '"';
                }
                if (isset($atts['name'])) {
                    unset($atts['name']);
                }
                if (isset($atts['value'])) {
                    unset($atts['value']);
                }

                $input_atts = '';
                foreach ($atts as $atts_k => $atts_v) {
                    $input_atts .= ' ' . esc_attr($atts_k) . '="' . esc_attr($atts_v) . '"';
                }

                $line = '
				<div class="' . implode(' ', $div_class) . '">';
                if (strlen($title) > 0) {
                    $line .= '<div class="form_field_label ' . $prefix . '_label"><label for="form_field_id-' . $form_field_num . '-' . $name . '"><span class="form_field_label_ins">' . $title . '' . $req_html . ':' . $tooltip_span . '</span></label></div>';
                }
                $line .= '
					<div class="form_field_ins ' . $prefix . '_line_ins">
				';

                if ('text' == $type) {
                    $line .= '
					<textarea ' . $field_id . ' ' . $input_atts . ' name="' . $name . '">' . $value . '</textarea>							
					';
                } elseif ('input' == $type) {
                    $line .= '
					<input type="text" ' . $field_id . ' ' . $input_atts . ' name="' . $name . '" value="' . $value . '" />						
					';
                } elseif ('password' == $type) {
                    $line .= '
					<input type="password" ' . $field_id . ' ' . $input_atts . ' name="' . $name . '" value="' . $value . '" />						
					';
                } elseif ('select' == $type) {
                    $options = (array)is_isset($data, 'options');
                    if (!is_array($options)) {
                        $options = array();
                    }

                    $line .= '
					<select ' . $field_id . ' ' . $input_atts . ' name="' . $name . '">';
                    foreach ($options as $key => $title) {
                        $line .= '<option value="' . $key . '" ' . selected($value, $key, false) . '>' . $title . '</option>';
                    }
                    $line .= '		
					</select>												
					';
                }

                $line .= '
						' . $tooltip_div . '
						<div class="form_field_errors"><div class="form_field_errors_ins"></div></div>
					</div>';

                $line .= '	
					<div class="form_field_clear ' . $prefix . '_line_clear"></div>
				</div>
				';

                $line = apply_filters('form_field_line', $line, $filter, $data, $prefix, $ui);
                $html .= apply_filters($filter, $line, $data, $prefix, $ui);
            }
        }

        return $html;
    }
}

if (!function_exists('pn_item_status')) {
    function pn_item_status($item = '', $tb = 'status', $statused = '') {

        $tb_status = intval(is_isset($item, $tb));
        if (isset($statused[$tb_status])) {
            return ' (' . $statused[$tb_status] . ')';
        }
        if (0 == $tb_status) {
            return ' (' . __('deactive item', 'premium') . ')';
        }

        return '';
    }
}

if (!function_exists('pn_item_basket')) {
    function pn_item_basket($item = '', $tb = 'auto_status', $in_trash = 0) {

        $trash_status = intval(is_isset($item, $tb));
        if ($trash_status == $in_trash) {
            return ' (' . __('item in basket', 'premium') . ')';
        }

        return '';
    }
}

if (!function_exists('update_pn_meta')) {
    function update_pn_meta($table, $id, $key, $value) {
        global $wpdb;

        $id = intval($id);
        $table = trim($table);
        $key = pn_maxf(trim($key), 200);
        if (is_array($value)) {
            $value = serialize($value);
        } else {
            $value = trim($value);
        }

        if ($table) {
            $option_data = $wpdb->get_row("SELECT id FROM " . $wpdb->prefix . $table . " WHERE item_id = '$id' AND meta_key = '$key'");
            $arr = array();
            $arr['item_id'] = $id;
            $arr['meta_key'] = $key;
            $arr['meta_value'] = $value;

            if (isset($option_data->id)) {
                $meta_id = $option_data->id;
                $result = $wpdb->update($wpdb->prefix . $table, $arr, array('id' => $meta_id));
            } else {
                $wpdb->insert($wpdb->prefix . $table, $arr);
                $result = $wpdb->insert_id;
            }

            return $result;
        }

        return 0;
    }
}

if (!function_exists('get_pn_meta')) {
    function get_pn_meta($table, $id, $key, $to_array = 0) {
        global $wpdb;

        $id = intval($id);
        $to_array = intval($to_array);
        $table = trim($table);
        $key = pn_maxf(trim($key), 200);
        if ($table) {
            $data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . $table . " WHERE item_id = '$id' AND meta_key = '$key'");
            if (isset($data->meta_value)) {
                $value = maybe_unserialize($data->meta_value);
                if ($to_array and !is_array($value)) {
                    $value = array();
                }

                return $value;
            }
        }

        if ($to_array) {
            return array();
        }

        return '';
    }
}

if (!function_exists('delete_pn_meta')) {
    function delete_pn_meta($table, $id, $key) {
        global $wpdb;

        $id = intval($id);
        $table = trim($table);
        $key = trim($key);
        if ($table) {
            return $wpdb->query("DELETE FROM " . $wpdb->prefix . $table . " WHERE item_id = '$id' AND meta_key = '$key'");
        }

        return 0;
    }
}

if (!function_exists('get_userpage_pn')) {
    function get_userpage_pn($page_id, $class = 'act') {

        if (is_page($page_id)) {
            return $class;
        } else {
            return false;
        }

    }
}

if (!function_exists('is_pn_page')) {
    function is_pn_page($page_name) {
        global $is_pn_page;

        if (isset($is_pn_page[$page_name])) {
            return $is_pn_page[$page_name];
        } else {
            $plugin = get_plugin_class();
            $pages = get_option($plugin->page_name);
            if (isset($pages[$page_name]) and is_page($pages[$page_name])) {
                $zn = 1;
            } else {
                $zn = 0;
            }
            $is_pn_page[$page_name] = $zn;

            return $zn;
        }
    }
}

if (!function_exists('is_status_name')) {
    function is_status_name($item) {

        $item = pn_string($item);
        $new_item = '';
        if (preg_match("/^[a-zA-z0-9]{3,35}$/", $item, $matches)) {
            $new_item = $item;
        }

        return $new_item;
    }
}

if (!function_exists('create_data_for_db')) {
    function create_data_for_db($status_arr, $strip = '') {

        $strip = trim($strip);
        $join_arr = array();
        if (is_array($status_arr)) {
            foreach ($status_arr as $st) {
                if ('status' == $strip) {
                    $st = is_status_name($st);
                } elseif ('int' == $strip) {
                    $st = intval($st);
                } else {
                    $st = trim($st);
                }
                if (strlen($st) > 0) {
                    $join_arr[] = "'" . $st . "'";
                }
            }
        }
        $join_arr = array_unique($join_arr);
        if (count($join_arr) > 0) {
            return implode(',', $join_arr);
        } else {
            return 0;
        }
    }
}

if (!function_exists('add_phpf_data')) {
    function add_phpf_data($string) {

        $file_data = '<?php /*';
        $file_data .= $string;
        $file_data .= '*/ ?>';

        return $file_data;
    }
}

if (!function_exists('get_phpf_data')) {
    function get_phpf_data($data) {

        $data = str_replace(array('<?php /*', '*/ ?>'), '', $data);

        return $data;
    }
}

if (!function_exists('delete_txtmeta')) {
    function delete_txtmeta($folder, $data_id, $plugin = '') {
        if ($folder) {
            $dir = is_isset($plugin, 'upload_dir') . '/' . $folder . '/';

            $file = $dir . $data_id . '.php';
            if (is_file($file)) {
                @unlink($file);
            }

        }

        return '';
    }
}

if (!function_exists('copy_txtmeta')) {
    function copy_txtmeta($folder, $data_id, $new_id, $plugin = '') {
        if ($folder) {
            $dir = is_isset($plugin, 'upload_dir') . '/' . $folder . '/';
            if (!is_dir($dir)) {
                @mkdir($dir, 0777);
            }
            $file = $dir . $data_id . '.php';
            $newfile = $dir . $new_id . '.php';
            if (is_file($file)) {
                @copy($file, $newfile);
            }
        }

        return '';
    }
}

if (!function_exists('get_txtmeta')) {
    function get_txtmeta($folder, $data_id, $key, $plugin = '') {
        if ($folder) {
            $dir = is_isset($plugin, 'upload_dir') . '/' . $folder . '/';

            $my_dir = wp_upload_dir();
            $old_dir = $my_dir['basedir'] . '/' . $folder . '/';

            $file = $dir . $data_id . '.php';
            $old_file = $old_dir . $data_id . '.txt';

            $data = '';

            if (is_file($file)) {
                $data = @file_get_contents($file);
            } elseif (is_file($old_file)) {
                $data = @file_get_contents($old_file);
            }
            $data = get_phpf_data($data);
            $data = trim($data);

            $array = @unserialize($data);

            $string = trim(stripslashes(str_replace('&star;', '*', is_isset($array, $key))));

            return $string;
        }

        return '';
    }
}

if (!function_exists('update_txtmeta')) {
    function update_txtmeta($folder, $data_id, $key, $value, $plugin = '') {
        if ($folder) {
            $dir = is_isset($plugin, 'upload_dir') . '/' . $folder . '/';

            $my_dir = wp_upload_dir();
            $old_dir = $my_dir['basedir'] . '/' . $folder . '/';

            if (!is_dir($dir)) {
                @mkdir($dir, 0777);
            }

            $file = $dir . $data_id . '.php';
            $old_file = $old_dir . $data_id . '.txt';

            $data = '';

            if (is_file($file)) {
                $data = @file_get_contents($file);
            } elseif (is_file($old_file)) {
                $data = @file_get_contents($old_file);
            }
            $data = get_phpf_data($data);
            $data = trim($data);

            $array = @unserialize($data);
            if (!is_array($array)) {
                $array = array();
            }

            $value = str_replace('*', '&star;', $value);
            $array[$key] = addslashes($value);

            $apd = @serialize($array);
            $file_data = add_phpf_data($apd);

            $file_open = @fopen($file, 'w');
            @fwrite($file_open, $file_data);
            @fclose($file_open);

            if (is_file($file)) {
                return 1;
            }
        }

        return 0;
    }
}

if (!function_exists('is_out_sum')) {
    function is_out_sum($sum, $decimal = 12, $place = 'all') {

        return apply_filters('is_out_sum', $sum, $decimal, $place);
    }
}

if (!function_exists('is_sum')) {
    function is_sum($sum, $cs = 12, $mode = 'standart') {

        $sum = pn_string($sum);
        $sum = str_replace(',', '.', $sum);
        $sum = preg_replace('/[^0-9-.E]/', '', $sum);
        $cs = apply_filters('is_sum_cs', $cs);
        $cs = intval($cs);
        if ($cs < 0) {
            $cs = 0;
        }
        if ($sum) {

            if (strlen($sum) > 0 and strstr($sum, 'E')) {
                $sum = sprintf("%0.20F", $sum);
                $sum = rtrim($sum, '0');
            }

            $s_arr = explode('.', $sum);
            $s_ceil = trim(is_isset($s_arr, 0));
            $s_double = trim(is_isset($s_arr, 1));
            $cs_now = mb_strlen($s_double);

            if ($cs > $cs_now) {
                $cs = $cs_now;
            }

            if ('standart' == $mode) {
                $new_sum = sprintf("%0.{$cs}F", $sum);
            } elseif ('up' == $mode) {
                $new_sum = $s_ceil . '.' . mb_substr($s_double, 0, $cs);
                $new_sum = rtrim($new_sum, '.');
                $f_num = intval(ltrim(mb_substr($s_double, $cs, $cs_now), '0'));
                if ($f_num > 0) {
                    if ($cs < 1) {
                        $s = 1;
                    } else {
                        $s = '0.';
                        $nr = 0;
                        $ncs = $cs - 1;
                        while ($nr++ < $ncs) {
                            $s .= '0';
                        }
                        $s .= '1';
                    }
                    $new_sum = $new_sum + $s;
                    $new_sum = sprintf("%0.{$cs}F", $sum);
                }
            } elseif ('down' == $mode) {
                $new_sum = $s_ceil . '.' . mb_substr($s_double, 0, $cs);
            } elseif ('ceil' == $mode) {
                $f_num = intval($s_double);
                if ($f_num > 0) {
                    $new_sum = $s_ceil + 1;
                } else {
                    $new_sum = $s_ceil;
                }
            }

            if (strlen($new_sum) > 0 and strstr($new_sum, '.')) {
                $new_sum = rtrim($new_sum, '0');
                $new_sum = rtrim($new_sum, '.');
            }

            return apply_filters('is_sum', $new_sum, $sum, $cs, $mode);
        } else {
            return '0';
        }

        return $sum;
    }
}

if (!function_exists('get_user_country')) {
    function get_user_country() {
        global $user_now_country;

        $country = is_country_attr($user_now_country);
        if (!$country) {
            $country = 'NaN';
        }

        return $country;
    }
}

if (!function_exists('is_country_attr')) {
    function is_country_attr($item) {

        $item = pn_string($item);
        if ('NaN' == $item) {
            return $item;
        }
        if (preg_match("/^[a-zA-z]{2,3}$/", $item, $matches)) {
            $new_item = mb_strtoupper($item);
        } else {
            $new_item = 0;
        }

        return $new_item;
    }
}

if (!function_exists('get_country_title')) {
    function get_country_title($attr) {

        $attr = is_country_attr($attr);
        if ($attr and 'NaN' != $attr) {
            $country = get_countries();
            if (isset($country[$attr])) {
                return pn_strip_input($country[$attr]);
            } else {
                return __('is not determined', 'premium');
            }
        } else {
            return __('is not determined', 'premium');
        }
    }
}

if (!function_exists('get_countries')) {
    function get_countries($lang = '') {

        $country = "
[en_US:]Australia[:en_US][ru_RU:]Австралия[:ru_RU];AU
[en_US:]Austria[:en_US][ru_RU:]Австрия[:ru_RU];AT
[en_US:]Azerbaijan[:en_US][ru_RU:]Азербайджан[:ru_RU];AZ
[en_US:]Aland Islands[:en_US][ru_RU:]Аландские острова[:ru_RU];AX
[en_US:]Albania[:en_US][ru_RU:]Албания[:ru_RU];AL
[en_US:]Algeria[:en_US][ru_RU:]Алжир[:ru_RU];DZ
[en_US:]Minor outlying Islands (USA)[:en_US][ru_RU:]Внешние малые острова (США)[:ru_RU];UM
[en_US:]U.S. virgin Islands[:en_US][ru_RU:]Американские Виргинские острова[:ru_RU];VI
[en_US:]American Samoa[:en_US][ru_RU:]Американское Самоа[:ru_RU];AS
[en_US:]Anguilla[:en_US][ru_RU:]Ангилья[:ru_RU];AI
[en_US:]Angola[:en_US][ru_RU:]Ангола[:ru_RU];AO
[en_US:]Andorra[:en_US][ru_RU:]Андорра[:ru_RU];AD
[en_US:]Antarctica[:en_US][ru_RU:]Антарктида[:ru_RU];AQ
[en_US:]Antigua and Barbuda[:en_US][ru_RU:]Антигуа и Барбуда[:ru_RU];AG
[en_US:]Argentina[:en_US][ru_RU:]Аргентина[:ru_RU];AR
[en_US:]Armenia[:en_US][ru_RU:]Армения[:ru_RU];AM
[en_US:]Aruba[:en_US][ru_RU:]Аруба[:ru_RU];AW
[en_US:]Afghanistan[:en_US][ru_RU:]Афганистан[:ru_RU];AF
[en_US:]Bahamas[:en_US][ru_RU:]Багамы[:ru_RU];BS
[en_US:]Bangladesh[:en_US][ru_RU:]Бангладеш[:ru_RU];BD
[en_US:]Barbados[:en_US][ru_RU:]Барбадос[:ru_RU];BB
[en_US:]Bahrain[:en_US][ru_RU:]Бахрейн[:ru_RU];BH
[en_US:]Belize[:en_US][ru_RU:]Белиз[:ru_RU];BZ
[en_US:]Belarus[:en_US][ru_RU:]Белоруссия[:ru_RU];BY
[en_US:]Belgium[:en_US][ru_RU:]Бельгия[:ru_RU];BE
[en_US:]Benin[:en_US][ru_RU:]Бенин[:ru_RU];BJ
[en_US:]Bermuda[:en_US][ru_RU:]Бермуды[:ru_RU];BM
[en_US:]Bulgaria[:en_US][ru_RU:]Болгария[:ru_RU];BG
[en_US:]Bolivia[:en_US][ru_RU:]Боливия[:ru_RU];BO
[en_US:]Bosnia and Herzegovina[:en_US][ru_RU:]Босния и Герцеговина[:ru_RU];BA
[en_US:]Botswana[:en_US][ru_RU:]Ботсвана[:ru_RU];BW
[en_US:]Brazil[:en_US][ru_RU:]Бразилия[:ru_RU];BR
[en_US:]British Indian ocean territory[:en_US][ru_RU:]Британская территория в Индийском океане[:ru_RU];IO
[en_US:]British virgin Islands[:en_US][ru_RU:]Британские Виргинские острова[:ru_RU];VG
[en_US:]Brunei[:en_US][ru_RU:]Бруней[:ru_RU];BN
[en_US:]Burkina Faso[:en_US][ru_RU:]Буркина Фасо[:ru_RU];BF
[en_US:]Burundi[:en_US][ru_RU:]Бурунди[:ru_RU];BI
[en_US:]Bhutan[:en_US][ru_RU:]Бутан[:ru_RU];BT
[en_US:]Vanuatu[:en_US][ru_RU:]Вануату[:ru_RU];VU
[en_US:]The Vatican[:en_US][ru_RU:]Ватикан[:ru_RU];VA
[en_US:]UK[:en_US][ru_RU:]Великобритания[:ru_RU];GB
[en_US:]Hungary[:en_US][ru_RU:]Венгрия[:ru_RU];HU
[en_US:]Venezuela[:en_US][ru_RU:]Венесуэла[:ru_RU];VE
[en_US:]East Timor[:en_US][ru_RU:]Восточный Тимор[:ru_RU];TL
[en_US:]Vietnam[:en_US][ru_RU:]Вьетнам[:ru_RU];VN
[en_US:]Gabon[:en_US][ru_RU:]Габон[:ru_RU];GA
[en_US:]Haiti[:en_US][ru_RU:]Гаити[:ru_RU];HT
[en_US:]Guyana[:en_US][ru_RU:]Гайана[:ru_RU];GY
[en_US:]Gambia[:en_US][ru_RU:]Гамбия[:ru_RU];GM
[en_US:]Ghana[:en_US][ru_RU:]Гана[:ru_RU];GH
[en_US:]Guadeloupe[:en_US][ru_RU:]Гваделупа[:ru_RU];GP
[en_US:]Guatemala[:en_US][ru_RU:]Гватемала[:ru_RU];GT
[en_US:]Guinea[:en_US][ru_RU:]Гвинея[:ru_RU];GN
[en_US:]Guinea-Bissau[:en_US][ru_RU:]Гвинея-Бисау[:ru_RU];GW
[en_US:]Germany[:en_US][ru_RU:]Германия[:ru_RU];DE
[en_US:]Gibraltar[:en_US][ru_RU:]Гибралтар[:ru_RU];GI
[en_US:]Honduras[:en_US][ru_RU:]Гондурас[:ru_RU];HN
[en_US:]Hong Kong[:en_US][ru_RU:]Гонконг[:ru_RU];HK
[en_US:]Grenada[:en_US][ru_RU:]Гренада[:ru_RU];GD
[en_US:]Greenland[:en_US][ru_RU:]Гренландия[:ru_RU];GL
[en_US:]Greece[:en_US][ru_RU:]Греция[:ru_RU];GR
[en_US:]Georgia[:en_US][ru_RU:]Грузия[:ru_RU];GE
[en_US:]GUAM[:en_US][ru_RU:]Гуам[:ru_RU];GU
[en_US:]Denmark[:en_US][ru_RU:]Дания[:ru_RU];DK
[en_US:]DR Congo[:en_US][ru_RU:]ДР Конго[:ru_RU];CD
[en_US:]Djibouti[:en_US][ru_RU:]Джибути[:ru_RU];DJ
[en_US:]Dominica[:en_US][ru_RU:]Доминика[:ru_RU];DM
[en_US:]Dominican Republic[:en_US][ru_RU:]Доминиканская Республика[:ru_RU];DO
[en_US:]The European Union[:en_US][ru_RU:]Европейский союз[:ru_RU];EU
[en_US:]Egypt[:en_US][ru_RU:]Египет[:ru_RU];EG
[en_US:]Zambia[:en_US][ru_RU:]Замбия[:ru_RU];ZM
[en_US:]Western Sahara[:en_US][ru_RU:]Западная Сахара[:ru_RU];EH
[en_US:]Zimbabwe[:en_US][ru_RU:]Зимбабве[:ru_RU];ZW
[en_US:]Israel[:en_US][ru_RU:]Израиль[:ru_RU];IL
[en_US:]India[:en_US][ru_RU:]Индия[:ru_RU];IN
[en_US:]Indonesia[:en_US][ru_RU:]Индонезия[:ru_RU];ID
[en_US:]Jordan[:en_US][ru_RU:]Иордания[:ru_RU];JO
[en_US:]Iraq[:en_US][ru_RU:]Ирак[:ru_RU];IQ
[en_US:]Iran[:en_US][ru_RU:]Иран[:ru_RU];IR
[en_US:]Ireland[:en_US][ru_RU:]Ирландия[:ru_RU];IE
[en_US:]Iceland[:en_US][ru_RU:]Исландия[:ru_RU];IS
[en_US:]Spain[:en_US][ru_RU:]Испания[:ru_RU];ES
[en_US:]Italy[:en_US][ru_RU:]Италия[:ru_RU];IT
[en_US:]Yemen[:en_US][ru_RU:]Йемен[:ru_RU];YE
[en_US:]The DPRK[:en_US][ru_RU:]КНДР[:ru_RU];KP
[en_US:]Cape Verde[:en_US][ru_RU:]Кабо-Верде[:ru_RU];CV
[en_US:]Kazakhstan[:en_US][ru_RU:]Казахстан[:ru_RU];KZ
[en_US:]Cayman Islands[:en_US][ru_RU:]Каймановы острова[:ru_RU];KY
[en_US:]Cambodia[:en_US][ru_RU:]Камбоджа[:ru_RU];KH
[en_US:]Cameroon[:en_US][ru_RU:]Камерун[:ru_RU];CM
[en_US:]Canada[:en_US][ru_RU:]Канада[:ru_RU];CA
[en_US:]Qatar[:en_US][ru_RU:]Катар[:ru_RU];QA
[en_US:]Kenya[:en_US][ru_RU:]Кения[:ru_RU];KE
[en_US:]Cyprus[:en_US][ru_RU:]Кипр[:ru_RU];CY
[en_US:]Kyrgyzstan[:en_US][ru_RU:]Киргизия[:ru_RU];KG
[en_US:]Kiribati[:en_US][ru_RU:]Кирибати[:ru_RU];KI
[en_US:]China[:en_US][ru_RU:]КНР[:ru_RU];CN
[en_US:]Cocos Islands[:en_US][ru_RU:]Кокосовые острова[:ru_RU];CC
[en_US:]Colombia[:en_US][ru_RU:]Колумбия[:ru_RU];CO
[en_US:]Comoros[:en_US][ru_RU:]Коморы[:ru_RU];KM
[en_US:]Costa Rica[:en_US][ru_RU:]Коста-Рика[:ru_RU];CR
[en_US:]Côte d'ivoire[:en_US][ru_RU:]Кот-д’Ивуар[:ru_RU];CI
[en_US:]Cuba[:en_US][ru_RU:]Куба[:ru_RU];CU
[en_US:]Kuwait[:en_US][ru_RU:]Кувейт[:ru_RU];KW
[en_US:]Laos[:en_US][ru_RU:]Лаос[:ru_RU];LA
[en_US:]Latvia[:en_US][ru_RU:]Латвия[:ru_RU];LV
[en_US:]Lesotho[:en_US][ru_RU:]Лесото[:ru_RU];LS
[en_US:]Liberia[:en_US][ru_RU:]Либерия[:ru_RU];LR
[en_US:]Lebanon[:en_US][ru_RU:]Ливан[:ru_RU];LB
[en_US:]Libya[:en_US][ru_RU:]Ливия[:ru_RU];LY
[en_US:]Lithuania[:en_US][ru_RU:]Литва[:ru_RU];LT
[en_US:]Liechtenstein[:en_US][ru_RU:]Лихтенштейн[:ru_RU];LI
[en_US:]Luxembourg[:en_US][ru_RU:]Люксембург[:ru_RU];LU
[en_US:]Mauritius[:en_US][ru_RU:]Маврикий[:ru_RU];MU
[en_US:]Mauritania[:en_US][ru_RU:]Мавритания[:ru_RU];MR
[en_US:]Madagascar[:en_US][ru_RU:]Мадагаскар[:ru_RU];MG
[en_US:]Mayotte[:en_US][ru_RU:]Майотта[:ru_RU];YT
[en_US:]Macau[:en_US][ru_RU:]Аомынь[:ru_RU];MO
[en_US:]Macedonia[:en_US][ru_RU:]Македония[:ru_RU];MK
[en_US:]Malawi[:en_US][ru_RU:]Малави[:ru_RU];MW
[en_US:]Malaysia[:en_US][ru_RU:]Малайзия[:ru_RU];MY
[en_US:]Mali[:en_US][ru_RU:]Мали[:ru_RU];ML
[en_US:]The Maldives[:en_US][ru_RU:]Мальдивы[:ru_RU];MV
[en_US:]Malta[:en_US][ru_RU:]Мальта[:ru_RU];MT
[en_US:]Morocco[:en_US][ru_RU:]Марокко[:ru_RU];MA
[en_US:]Martinique[:en_US][ru_RU:]Мартиника[:ru_RU];MQ
[en_US:]Marshall Islands[:en_US][ru_RU:]Маршалловы Острова[:ru_RU];MH
[en_US:]Mexico[:en_US][ru_RU:]Мексика[:ru_RU];MX
[en_US:]Mozambique[:en_US][ru_RU:]Мозамбик[:ru_RU];MZ
[en_US:]Moldova[:en_US][ru_RU:]Молдавия[:ru_RU];MD
[en_US:]Monaco[:en_US][ru_RU:]Монако[:ru_RU];MC
[en_US:]Mongolia[:en_US][ru_RU:]Монголия[:ru_RU];MN
[en_US:]Montserrat[:en_US][ru_RU:]Монтсеррат[:ru_RU];MS
[en_US:]Myanmar[:en_US][ru_RU:]Мьянма[:ru_RU];MM
[en_US:]Namibia[:en_US][ru_RU:]Намибия[:ru_RU];NA
[en_US:]Nauru[:en_US][ru_RU:]Науру[:ru_RU];NR
[en_US:]Nepal[:en_US][ru_RU:]Непал[:ru_RU];NP
[en_US:]Niger[:en_US][ru_RU:]Нигер[:ru_RU];NE
[en_US:]Nigeria[:en_US][ru_RU:]Нигерия[:ru_RU];NG
[en_US:]Netherlands Antilles[:en_US][ru_RU:]Нидерландские Антильские острова[:ru_RU];AN
[en_US:]The Netherlands[:en_US][ru_RU:]Нидерланды[:ru_RU];NL
[en_US:]Nicaragua[:en_US][ru_RU:]Никарагуа[:ru_RU];NI
[en_US:]Niue[:en_US][ru_RU:]Ниуэ[:ru_RU];NU
[en_US:]New Caledonia[:en_US][ru_RU:]Новая Каледония[:ru_RU];NC
[en_US:]New Zealand[:en_US][ru_RU:]Новая Зеландия[:ru_RU];NZ
[en_US:]Norway[:en_US][ru_RU:]Норвегия[:ru_RU];NO
[en_US:]UAE[:en_US][ru_RU:]ОАЭ[:ru_RU];AE
[en_US:]Oman[:en_US][ru_RU:]Оман[:ru_RU];OM
[en_US:]Christmas Island[:en_US][ru_RU:]Остров Рождества[:ru_RU];CX
[en_US:]Cook Islands[:en_US][ru_RU:]Острова Кука[:ru_RU];CK
[en_US:]Heard and McDonald[:en_US][ru_RU:]Херд и Макдональд[:ru_RU];HM
[en_US:]Pakistan[:en_US][ru_RU:]Пакистан[:ru_RU];PK
[en_US:]Palau[:en_US][ru_RU:]Палау[:ru_RU];PW
[en_US:]Palestine[:en_US][ru_RU:]Палестина[:ru_RU];PS
[en_US:]Panama[:en_US][ru_RU:]Панама[:ru_RU];PA
[en_US:]Papua New Guinea[:en_US][ru_RU:]Папуа — Новая Гвинея[:ru_RU];PG
[en_US:]Paraguay[:en_US][ru_RU:]Парагвай[:ru_RU];PY
[en_US:]Peru[:en_US][ru_RU:]Перу[:ru_RU];PE
[en_US:]Pitcairn Islands[:en_US][ru_RU:]Острова Питкэрн[:ru_RU];PN
[en_US:]Poland[:en_US][ru_RU:]Польша[:ru_RU];PL
[en_US:]Portugal[:en_US][ru_RU:]Португалия[:ru_RU];PT
[en_US:]Puerto Rico[:en_US][ru_RU:]Пуэрто-Рико[:ru_RU];PR
[en_US:]Republic Of The Congo[:en_US][ru_RU:]Республика Конго[:ru_RU];CG
[en_US:]Reunion[:en_US][ru_RU:]Реюньон[:ru_RU];RE
[en_US:]Russia[:en_US][ru_RU:]Россия[:ru_RU];RU
[en_US:]Rwanda[:en_US][ru_RU:]Руанда[:ru_RU];RW
[en_US:]Romania[:en_US][ru_RU:]Румыния[:ru_RU];RO
[en_US:]USA[:en_US][ru_RU:]США[:ru_RU];US
[en_US:]Salvador[:en_US][ru_RU:]Сальвадор[:ru_RU];SV
[en_US:]Samoa[:en_US][ru_RU:]Самоа[:ru_RU];WS
[en_US:]San Marino[:en_US][ru_RU:]Сан-Марино[:ru_RU];SM
[en_US:]Sao Tome and Principe[:en_US][ru_RU:]Сан-Томе и Принсипи[:ru_RU];ST
[en_US:]Saudi Arabia[:en_US][ru_RU:]Саудовская Аравия[:ru_RU];SA
[en_US:]Swaziland[:en_US][ru_RU:]Свазиленд[:ru_RU];SZ
[en_US:]Svalbard and Jan Mayen[:en_US][ru_RU:]Шпицберген и Ян-Майен[:ru_RU];SJ
[en_US:]Northern Mariana Islands[:en_US][ru_RU:]Северные Марианские острова[:ru_RU];MP
[en_US:]Seychelles[:en_US][ru_RU:]Сейшельские Острова[:ru_RU];SC
[en_US:]Senegal[:en_US][ru_RU:]Сенегал[:ru_RU];SN
[en_US:]Saint Vincent and the Grenadines[:en_US][ru_RU:]Сент-Винсент и Гренадины[:ru_RU];VC
[en_US:]Saint Kitts and Nevis[:en_US][ru_RU:]Сент-Китс и Невис[:ru_RU];KN
[en_US:]Saint Lucia[:en_US][ru_RU:]Сент-Люсия[:ru_RU];LC
[en_US:]Saint Pierre and Miquelon[:en_US][ru_RU:]Сен-Пьер и Микелон[:ru_RU];PM
[en_US:]Serbia[:en_US][ru_RU:]Сербия[:ru_RU];RS
[en_US:]Serbia and Montenegro (operated until September 2006)[:en_US][ru_RU:]Сербия и Черногория (действовал до сентября 2006 года)[:ru_RU];CS
[en_US:]Singapore[:en_US][ru_RU:]Сингапур[:ru_RU];SG
[en_US:]Syria[:en_US][ru_RU:]Сирия[:ru_RU];SY
[en_US:]Slovakia[:en_US][ru_RU:]Словакия[:ru_RU];SK
[en_US:]Slovenia[:en_US][ru_RU:]Словения[:ru_RU];SI
[en_US:]Solomon Islands[:en_US][ru_RU:]Соломоновы Острова[:ru_RU];SB
[en_US:]Somalia[:en_US][ru_RU:]Сомали[:ru_RU];SO
[en_US:]Sudan[:en_US][ru_RU:]Судан[:ru_RU];SD
[en_US:]Suriname[:en_US][ru_RU:]Суринам[:ru_RU];SR
[en_US:]Sierra Leone[:en_US][ru_RU:]Сьерра-Леоне[:ru_RU];SL
[en_US:]The USSR was valid until September 1992)[:en_US][ru_RU:]СССР (действовал до сентября 1992 года)[:ru_RU];SU
[en_US:]Tajikistan[:en_US][ru_RU:]Таджикистан[:ru_RU];TJ
[en_US:]Thailand[:en_US][ru_RU:]Таиланд[:ru_RU];TH
[en_US:]The Republic Of China[:en_US][ru_RU:]Китайская Республика[:ru_RU];TW
[en_US:]Tanzania[:en_US][ru_RU:]Танзания[:ru_RU];TZ
[en_US:]In[:en_US][ru_RU:]Того[:ru_RU];TG
[en_US:]Tokelau[:en_US][ru_RU:]Токелау[:ru_RU];TK
[en_US:]Tonga[:en_US][ru_RU:]Тонга[:ru_RU];TO
[en_US:]Trinidad and Tobago[:en_US][ru_RU:]Тринидад и Тобаго[:ru_RU];TT
[en_US:]Tuvalu[:en_US][ru_RU:]Тувалу[:ru_RU];TV
[en_US:]Tunisia[:en_US][ru_RU:]Тунис[:ru_RU];TN
[en_US:]Turkmenistan[:en_US][ru_RU:]Туркмения[:ru_RU];TM
[en_US:]Turkey[:en_US][ru_RU:]Турция[:ru_RU];TR
[en_US:]Uganda[:en_US][ru_RU:]Уганда[:ru_RU];UG
[en_US:]Uzbekistan[:en_US][ru_RU:]Узбекистан[:ru_RU];UZ
[en_US:]Ukraine[:en_US][ru_RU:]Украина[:ru_RU];UA
[en_US:]Uruguay[:en_US][ru_RU:]Уругвай[:ru_RU];UY
[en_US:]Faroe Islands[:en_US][ru_RU:]Фарерские острова[:ru_RU];FO
[en_US:]Micronesia[:en_US][ru_RU:]Микронезия[:ru_RU];FM
[en_US:]Fiji[:en_US][ru_RU:]Фиджи[:ru_RU];FJ
[en_US:]Philippines[:en_US][ru_RU:]Филиппины[:ru_RU];PH
[en_US:]Finland[:en_US][ru_RU:]Финляндия[:ru_RU];FI
[en_US:]Falkland Islands[:en_US][ru_RU:]Фолклендские острова[:ru_RU];FK
[en_US:]France[:en_US][ru_RU:]Франция[:ru_RU];FR
[en_US:]French Guiana[:en_US][ru_RU:]Французская Гвиана[:ru_RU];GF
[en_US:]French Polynesia[:en_US][ru_RU:]Французская Полинезия[:ru_RU];PF
[en_US:]French Southern and Antarctic lands[:en_US][ru_RU:]Французские Южные и Антарктические Территории[:ru_RU];TF
[en_US:]Croatia[:en_US][ru_RU:]Хорватия[:ru_RU];HR
[en_US:]CAR[:en_US][ru_RU:]ЦАР[:ru_RU];CF
[en_US:]Chad[:en_US][ru_RU:]Чад[:ru_RU];TD
[en_US:]Montenegro[:en_US][ru_RU:]Черногория[:ru_RU];ME
[en_US:]Czech Republic[:en_US][ru_RU:]Чехия[:ru_RU];CZ
[en_US:]Chile[:en_US][ru_RU:]Чили[:ru_RU];CL
[en_US:]Switzerland[:en_US][ru_RU:]Швейцария[:ru_RU];CH
[en_US:]Sweden[:en_US][ru_RU:]Швеция[:ru_RU];SE
[en_US:]Sri Lanka[:en_US][ru_RU:]Шри-Ланка[:ru_RU];LK
[en_US:]Ecuador[:en_US][ru_RU:]Эквадор[:ru_RU];EC
[en_US:]Equatorial Guinea[:en_US][ru_RU:]Экваториальная Гвинея[:ru_RU];GQ
[en_US:]Eritrea[:en_US][ru_RU:]Эритрея[:ru_RU];ER
[en_US:]Estonia[:en_US][ru_RU:]Эстония[:ru_RU];EE
[en_US:]Ethiopia[:en_US][ru_RU:]Эфиопия[:ru_RU];ET
[en_US:]South Africa[:en_US][ru_RU:]ЮАР[:ru_RU];ZA
[en_US:]The Republic Of Korea[:en_US][ru_RU:]Республика Корея[:ru_RU];KR
[en_US:]South Georgia and the South sandwich Islands[:en_US][ru_RU:]Южная Георгия и Южные Сандвичевы острова[:ru_RU];GS
[en_US:]Jamaica[:en_US][ru_RU:]Ямайка[:ru_RU];JM
[en_US:]Japan[:en_US][ru_RU:]Япония[:ru_RU];JP
[en_US:]Bouvet Island[:en_US][ru_RU:]Остров Буве[:ru_RU];BV
[en_US:]Norfolk Island[:en_US][ru_RU:]Остров Норфолк[:ru_RU];NF
[en_US:]St. Helena Island[:en_US][ru_RU:]Остров Святой Елены[:ru_RU];SH
[en_US:]Turks and Caicos Islands[:en_US][ru_RU:]Тёркс и Кайкос[:ru_RU];TC
[en_US:]Wallis and Futuna[:en_US][ru_RU:]Уоллис и Футуна[:ru_RU];WF
";

        $lang = trim($lang);
        if (!$lang) {
            $lang = get_locale();
        }

        $array = array();
        $country = explode("\n", $country);
        foreach ($country as $cou) {
            $data = explode(';', $cou);
            $title = ctv_ml(trim(is_isset($data, 0)), $lang);
            $attr = trim(is_isset($data, 1));
            if ($title and $attr) {
                $array[$attr] = $title;
            }
        }

        asort($array);

        return $array;
    }
}

if (!function_exists('is_valid_credit_card')) {
    function is_valid_credit_card($s) {

        $s = strrev(preg_replace('/[^\d]/', '', $s));

        $sum = 0;
        for ($i = 0, $j = strlen($s); $i < $j; $i++) {
            if (0 == $i % 2) {
                $val = $s[$i];
            } else {
                $val = $s[$i] * 2;
                if ($val > 9) {
                    $val -= 9;
                }
            }
            $sum += $val;
        }

        return (0 == $sum % 10);
    }
}

if (!function_exists('card_scheme_detected')) {
    function card_scheme_detected($card = '') {

        $card = trim($card);
        $scheme = '';

        $f = mb_substr($card, 0, 1);
        $t = mb_substr($card, 0, 2);
        if ('4' == $f) {
            $scheme = 'Visa';
        } elseif ('5' == $f) {
            $mc_arr = array('51', '52', '53', '54', '55');
            if (in_array($t, $mc_arr)) {
                $scheme = 'MasterCard';
            } else {
                $scheme = 'Maestro';
            }
        } elseif ('2' == $f) {
            $scheme = 'Mir';
        } elseif ('6' == $f) {
            if ('60' == $t) {
                $scheme = 'Discover';
            } elseif ('62' == $t) {
                $scheme = 'China UnionPay';
            } elseif ('63' == $t or '67' == $t) {
                $scheme = 'Maestro';
            }
        }

        return apply_filters('card_scheme_detected', $scheme, $card, $f, $t);
    }
}

if (!function_exists('pn_header_lastmodifier')) {
    function pn_header_lastmodifier($time = '') {

        $lastmodified_unix = intval($time);
        if (!$lastmodified_unix) {
            $lastmodified_unix = current_time('timestamp');
        }
        $lastmodified = gmdate("D, d M Y H:i:s \G\M\T", $lastmodified_unix);

        $IfModifiedSince = 0;
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $IfModifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        }
        if ($IfModifiedSince >= $lastmodified_unix) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
            exit;
        }

        header('Last-Modified: ' . $lastmodified);
    }
}

if (!function_exists('get_array_option')) {
    function get_array_option($plugin, $option_name) {
        $dir = is_isset($plugin, 'upload_dir') . '/';
        if (is_dir($dir)) {

            $file = $dir . $option_name . '.php';

            $data = '';
            if (file_exists($file)) {
                $data = @file_get_contents($file);
            }
            $data = get_phpf_data($data);
            $data = trim($data);
            $data = str_replace('&star;', '*', $data);

            $array = pn_json_decode($data);
            if (!is_array($array)) {
                $array = array();
            }

            return $array;

        }

        return array();
    }
}

if (!function_exists('update_array_option')) {
    function update_array_option($plugin, $option_name, $array) {
        $dir = is_isset($plugin, 'upload_dir') . '/';
        if (is_dir($dir)) {

            $file = $dir . $option_name . '.php';

            $apd = pn_json_encode($array);
            $apd = str_replace('*', '&star;', $apd);
            $file_data = add_phpf_data($apd);

            $file_open = @fopen($file, 'w');
            @fwrite($file_open, $file_data);
            @fclose($file_open);

        }
    }
}

if (!function_exists('delete_array_option')) {
    function delete_array_option($plugin, $option_name) {

        update_array_option($plugin, $option_name, array());

    }
}

if (!function_exists('unset_array_option')) {
    function unset_array_option($plugin, $option_name, $key) {

        $data = get_array_option($plugin, $option_name);
        if (isset($data[$key])) {
            unset($data[$key]);
            update_array_option($plugin, $option_name, $data);
        }

    }
} 