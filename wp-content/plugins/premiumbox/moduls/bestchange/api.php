<?php
if (!defined('ABSPATH')) exit();

add_action('pn_adminpage_content_pn_bestchange', 'bcbroker_adminpage_content_pn_bestchange', 0);
function bcbroker_adminpage_content_pn_bestchange() {

    $form = new PremiumForm();
    $text = __('Cron URL for updating rates in BestChange parser module', 'pn') . '<br /><a href="' . get_cron_link('bestchange_upload_data') . '" target="_blank">' . get_cron_link('bestchange_upload_data') . '</a>';
    $form->substrate($text);

}

if (!function_exists('download_data_bestchange')) {
    function download_data_bestchange($server = 0, $timeout = 0, $type = 0) {
        global $premiumbox;

        $type = intval($type);
        $server = intval($server);
        if (1 == $server) {
            $url = 'http://api.bestchange.net/info.zip';
        } elseif (2 == $server) {
            $url = 'http://api.bestchange.com/info.zip';
        } else {
            $url = 'http://api.bestchange.ru/info.zip';
        }

        if ($timeout < 1) $timeout = 30;

        $pi = pathinfo($url);
        $ext = $pi['extension'];
        $name = $pi['filename'];

        $file_data = '';

        if (0 == $type) {
            if ($ch = curl_init()) {
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

                $ch = apply_filters('curl_bestchange', $ch);

                $file_data = curl_exec($ch);
                curl_close($ch);
            }
        } else {
            $arrContextOptions = array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
                "http" => array(
                    "timeout" => $timeout,
                ),
            );
            $file_data = file_get_contents($url, false, stream_context_create($arrContextOptions));
        }
        if ($file_data) {

            $path = $premiumbox->upload_dir . '/';
            $saveFile = $path . 'bestchange.' . $ext;
            $handle = @fopen($saveFile, 'wb');
            @fwrite($handle, $file_data);
            @fclose($handle);

            if (is_file($saveFile)) {
                $zip = new ZipArchive;
                if ($zip->open($saveFile) === TRUE) {
                    $zip->extractTo($path . '/bestchange/');
                    $zip->close();
                }
                delete_ext_files($path . '/bestchange/', array('.dat'));
            }
        }
    }
}

function bestchange_upload_data() {
    global $wpdb, $premiumbox;

    if (!$premiumbox->is_up_mode()) {
        if (function_exists('download_data_bestchange')) {
            download_data_bestchange($premiumbox->get_option('bcbroker', 'server'), $premiumbox->get_option('bcbroker', 'timeout'), $premiumbox->get_option('bcbroker', 'type'));
        }
        if (function_exists('set_directions_bestchange')) {
            set_directions_bestchange(is_param_get('test'));
        }
    }

}

add_filter('list_cron_func', 'bestchange_list_cron_func');
function bestchange_list_cron_func($filters) {

    $filters['bestchange_upload_data'] = array(
        'title' => __('BestChange parser', 'pn'),
        'file' => 'now',
    );

    return $filters;
}