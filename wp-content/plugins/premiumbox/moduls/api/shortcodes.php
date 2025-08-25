<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('pn_other_tags', 'api_pn_other_tags', 10);
function api_pn_other_tags($tags) {
				
	$tags['from_api'] = array(
		'title' => __('API only', 'pn'),
		'start' => '[from_api]',
		'end' => '[/from_api]',
	);
	$tags['from_notapi'] = array(
		'title' => __('Not API only', 'pn'),
		'start' => '[from_notapi]',
		'end' => '[/from_notapi]',
	);
	
	return $tags;
}	

function shortcode_from_api($atts, $content = "") {
	
	if (_is('is_api')) {
		return do_shortcode($content);
	} 
	
}
add_shortcode('from_api', 'shortcode_from_api');

function shortcode_from_notapi($atts, $content = "") {
	
	if (!_is('is_api')) {
		return do_shortcode($content);
	} 
	
}
add_shortcode('from_notapi', 'shortcode_from_notapi');