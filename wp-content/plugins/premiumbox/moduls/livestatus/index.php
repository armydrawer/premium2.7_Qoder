<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Online updating of order status[:en_US][ru_RU:]Обновление статуса заявки в онлайн режиме [:ru_RU]
description: [en_US:]Online updating of order status[:en_US][ru_RU:]Обновление статуса заявки в онлайн режиме в панели управления[:ru_RU]
version: 2.7.0
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if (!function_exists('bids_globalajax_admin_request')) {
	add_filter('globalajax_admin_request', 'bids_globalajax_admin_request');
	function bids_globalajax_admin_request($params) {
		
		$page = trim(is_param_get('page'));
		if ('pn_bids' == $page) {
			$params .= "+ '&bids_ids=' + $('#visible_ids').val()";
		}
		
		return $params;
	}
}
 
add_filter('globalajax_admin_data', 'bids_globalajax_admin_data');
function bids_globalajax_admin_data($log) {
	global $wpdb;

	if (isset($_POST['bids_ids'])) {
		if (current_user_can('administrator') or current_user_can('pn_bids')) {
			$bids_ids = is_param_post('bids_ids');
			$bids_ids_parts = explode(',', $bids_ids);
			$ins = create_data_for_db($bids_ids_parts, 'int');
			if ($ins) {
				$bids = array();
				$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id IN($ins)");
				foreach ($items as $item) {
					$bids[$item->id] = array(
						'status' => $item->status,
						'title' => get_bid_status($item->status),
					);
				}
				$log['status_bids'] = $bids;
			}
		}
	}
	
	return $log;
}

add_action('globalajax_admin_result', 'bids_globalajax_admin_result');
function bids_globalajax_admin_result() {
?>
if (res['status_bids']) {
	for (key in res['status_bids']) {
		if (!$('#bidid_' + key).find('.stname').hasClass('st_' + res['status_bids'][key].status)) {
			$('#bidid_' + key).find('.stname').removeClass().addClass('stname').addClass('st_' + res['status_bids'][key].status);
			$('#bidid_' + key).find('.stname').html(res['status_bids'][key].title);
			$('#bidid_' + key).find('.stname').effect("bounce", "slow");
		}
	}	
}
<?php
}