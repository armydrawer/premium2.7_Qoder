<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('contactform_filelds', 'premiumbox_contactform_filelds', 11);
function premiumbox_contactform_filelds($items) {
	
	$n_items = array();
	$n_items['exchange_id'] = array(
		'name' => 'exchange_id',
		'title' => __('Exchange ID', 'pn'),
		'req' => 0,
		'value' => '', 
		'type' => 'input',
	);		
	$items = pn_array_insert($items, 'email', $n_items);
		
	return $items;
}

add_filter('list_notify_tags_contactform', 'premiumbox_mailtemp_tags_contactform');
add_filter('list_notify_tags_contactform_auto', 'premiumbox_mailtemp_tags_contactform');
function premiumbox_mailtemp_tags_contactform($tags) {
		
	$tags['exchange_id'] = array(
		'title' => __('Exchange ID', 'pn'),
		'start' => '[exchange_id]',
	);	

	return $tags;
}

add_filter('notify_tags_contactform', 'premiumbox_notify_tags_contactform');
function premiumbox_notify_tags_contactform($notify_tags) {
	
	$exchange_id = pn_maxf_mb(pn_strip_input(is_param_post('exchange_id')), 300);
	$notify_tags['[exchange_id]'] = $exchange_id;
	
	return $notify_tags;
}