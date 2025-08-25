<?php
if (!defined('ABSPATH')) { exit(); }

add_action('pn_adminpage_content_pn_bestchangeapi', 'bcbroker_adminpage_content_pn_bestchangeapi', 0);
function bcbroker_adminpage_content_pn_bestchangeapi() {
	
	$form = new PremiumForm();
	$text = __('Cron URL for updating rates in BestChange API parser module', 'pn') . '<br /><a href="' . get_cron_link('bestchangeapi_upload_data') . '" target="_blank">' . get_cron_link('bestchangeapi_upload_data')  . '</a>';
	$form->substrate($text);
	
}

function bestchangeapi_upload_data() {
	global $wpdb, $premiumbox;
	
	if (!$premiumbox->is_up_mode()) {
		if (function_exists('set_directions_bestchangeapi')) {
			set_directions_bestchangeapi(is_param_get('test'));
		}
	}
}

add_filter('list_cron_func', 'bestchangeapi_list_cron_func');
function bestchangeapi_list_cron_func($filters) {
	
	$filters['bestchangeapi_upload_data'] = array(
		'title' => __('BestChange API parser', 'pn'),
		'file' => 'now',
	);
	
	return $filters;
}