<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Redirection to exchange directions[:en_US][ru_RU:]Редирект на направления обмена[:ru_RU]
description: [en_US:]Redirection to exchange directions[:en_US][ru_RU:]Редирект на направления обмена[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('template_redirect', 'dirredirect_redirect', 9);
function dirredirect_redirect() {
	global $wpdb;
			
	if (isset($_GET['cur_from']) and isset($_GET['cur_to'])) { 
		$cur_from = is_xml_value(is_param_get('cur_from'));
		$cur_to = is_xml_value(is_param_get('cur_to'));
		if ($cur_from and $cur_to) {
			$vd1_filter_dirredirect = apply_filters('vd1_filter_dirredirect', '', $cur_from);
			$vd1 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' AND currency_status = '1' AND xml_value = '$cur_from' $vd1_filter_dirredirect");
			$vd2_filter_dirredirect = apply_filters('vd2_filter_dirredirect', '', $cur_to);
			$vd2 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' AND currency_status = '1' AND xml_value = '$cur_to' $vd2_filter_dirredirect");
			if (isset($vd1->id) and isset($vd2->id)) {
				$val1 = $vd1->id;
				$val2 = $vd2->id;
				$where = get_directions_where('exchange');
				$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where AND currency_id_give = '$val1' AND currency_id_get = '$val2'");
				foreach ($directions as $dir) {
					$output = apply_filters('get_direction_output', 1, $dir, 'exchange');
					if ($output) {
						$url = get_exchange_link($dir->direction_name);
						$args = array();
						if (is_array($_GET)) {
							foreach ($_GET as $get_k => $get_v) {
								if (!in_array($get_k, array('cur_from', 'cur_to', stand_refid()))) {
									$args[$get_k] = $get_v;
								}
							}
						}	
						if (count($args) > 0) {
							$url = add_query_args($args, $url);
						}
						$url = apply_filters('direction_redirect_url', $url, $dir, $vd1, $vd2);
						wp_redirect($url);
						exit;
					}
				}
			}
		}
	}	
}