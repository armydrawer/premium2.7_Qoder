<?php
if (!defined('ABSPATH')) { exit(); }
	
add_filter('list_admin_notify', 'list_admin_notify_parsererrform', 100, 2);
function list_admin_notify_parsererrform($places_admin, $place = '') {
	
	$places_admin['parsererrform'] = __('Rates parsing error', 'pn');
	
	return $places_admin;
}

add_filter('list_notify_tags_parsererrform', 'def_mailtemp_tags_parsererrform');
function def_mailtemp_tags_parsererrform($tags) {
	
	$tags['errors'] = array(
		'title' => __('Parsing errors', 'pn'),
		'start' => '[errors]',
	);
	
	return $tags;
}