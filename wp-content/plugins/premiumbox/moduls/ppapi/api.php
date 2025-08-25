<?php
if (!defined('ABSPATH')) { exit(); }

add_action('init', 'set_ppapi_page', 180);
function set_ppapi_page() {
		
	$dir = get_request_dir();
	$request = ltrim(get_request_query(), '/');	
	$matches = '';
		
	if ('api.html' == $request) {		
			
		global $pn_query;
		
		if (!is_array($pn_query)) { $pn_query = array(); }
		
		if (isset($pn_query['is_site'])) { unset($pn_query['is_site']); }
		
		$pn_query['is_api'] = 1;
			
		_pn_debug();
		
		xframe_alloworigin();
		
		header('Content-Type: application/json; charset=' . get_charset());
		
		status_header(200);
			
		ppapi_pn_plugin_api();	
		exit;
		
	} 
		
}

function ppapi_pn_plugin_api() {
	global $wpdb, $premiumbox;	
	
	$api_action = trim(pn_string(is_param_get('api_action')));
	$method = pn_strip_input(is_param_get('method'));
	$methods = array('get_info', 'get_links', 'get_payouts', 'get_exchanges', 'add_payout');
	$api_key = pn_maxf(pn_strip_input(is_param_get('api_key')), 300);
	
	if ('pp' == $api_action and strlen($api_key) > 0 and in_array($method, $methods)) {

		$workapikey = intval($premiumbox->get_option('partners', 'workppapikey'));
		if (1 == $workapikey or $premiumbox->is_up_mode()) {
			
			$json = array(
				'error' => 1,
				'error_text' => 'Api disabled',
			);	
			echo pn_json_encode($json);
			exit;
		}
		
		$where = '';
		if (2 == $workapikey) {
			$where = " AND workppapikey = '1'";
		}
		
		$ui = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "users WHERE ppapikey = '$api_key' AND user_bann = '0' $where");
		if (isset($ui->ID)) {
			
			$user_id = $ui->ID;
			
			$json = array(
				'error' => 3,
				'error_text' => 'Method not supported',
			);	
			
			$endpoint = str_replace('get_', 'get_partner_', $method);
			if ('add_payout' == $endpoint) { 
				$endpoint = 'add_partner_payout'; 
			}
			
			if ($endpoint) {
				do_action('userapi_v1_' . $endpoint, $ui, '');
			}
			
			echo pn_json_encode($json);
			exit;
		}
	}	
		
	$json = array(
		'error' => 1,
		'error_text' => 'Api disabled',
	);		
	echo pn_json_encode($json);
	exit;
} 