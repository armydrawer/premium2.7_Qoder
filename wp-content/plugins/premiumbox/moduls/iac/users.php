<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('all_user_editform', 'iac_all_user_editform', 20, 2);
function iac_all_user_editform($options, $data) { 
	global $wpdb;
	
	$user_id = $data->ID;
	
	$options[] = array(
		'view' => 'h3',
		'title' => __('Internal account', 'pn'),
		'submit' => __('Save','pn'),
	);	
	$currency_codes = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency_codes WHERE auto_status = '1' AND iac_enable = '1' ORDER BY currency_code_title ASC");
	foreach ($currency_codes as $currency_code) {
		$curr_title = is_site_value($currency_code->currency_code_title);
		$options['iac_' . str_replace('.', '_', $curr_title)] = array(
			'view' => 'textfield',
			'title' => strtoupper($curr_title . '_' . $user_id),
			'default' => get_user_iac($user_id, $currency_code->id) . ' ' . $curr_title,
		);
	}
	
	return $options;
}