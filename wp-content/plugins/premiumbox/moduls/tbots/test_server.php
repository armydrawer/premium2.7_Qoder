<?php 
if (!defined('ABSPATH')) { exit(); }

add_action('premium_action_tapibot_testserver', 'def_premium_action_tapibot_testserver');
function def_premium_action_tapibot_testserver() {	
	global $wpdb;

	pn_only_caps(array('administrator', 'pn_tapibot'));
			
	$id = intval(is_param_get('id'));
	if ($id > 0) {
		$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibots WHERE id = '$id'");
		if (isset($data->id)) {
			
			$api_server = pn_strip_input($data->api_server);
			$api_version = pn_strip_input($data->api_version);
			$api_lang = pn_strip_input($data->api_lang);
			$api_login = pn_strip_input($data->api_login);
			$api_key = pn_strip_input($data->api_key);
			$api_partner_id = pn_strip_input($data->api_partner_id);
				
			$res = tapibot_command(0, 'test', $api_server, $api_version, $api_lang, $api_login, $api_key, $api_partner_id);
			print_r($res);
			exit;

		}
	}	
	
	pn_display_mess(__('Error! No server', 'pn'), __('Error! No server', 'pn'), 'error');
}