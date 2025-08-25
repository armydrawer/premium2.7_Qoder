<?php
if (!defined('ABSPATH')) { exit(); }

add_action('pn_api_page', 'modul_api_page', 10, 3); 
function modul_api_page($module, $version, $endpoint) {
	global $wpdb, $premiumbox;

	if ('userapi' == $module and 'v1' == $version) {
		$api = intval($premiumbox->get_option('api', 'method'));
		if (!$premiumbox->is_up_mode() and $api > 0) {
			
			$headers = array();
			$headers_arrs = getallheaders();
			foreach ($headers_arrs as $head_k => $head_v) {
				$headers[strtolower($head_k)] = $head_v;
			}
			
			$api_login = is_api_key(is_isset($headers, 'api-login'));
			$api_key = is_api_key(is_isset($headers, 'api-key'));
			$api_lang = is_lang_attr(is_isset($headers, 'api-lang'));
			
			if ($api_lang) {
				set_locale($api_lang);
			}
			
			$api_logs = intval($premiumbox->get_option('api', 'logs'));
			if ($api_logs) {
				
				$arr = array();
				$arr['create_date'] = current_time('mysql');
				$arr['api_login'] = $api_login;
				$arr['api_key'] = $api_key;
				$arr['api_action'] = pn_strip_input($endpoint);
				$arr['ip'] = pn_strip_input(pn_real_ip());
				$arr['post_data'] = pn_strip_input(print_r($_POST, true));	
				$arr['headers_data'] = pn_strip_input(print_r($headers, true));	
				$wpdb->insert($wpdb->prefix . 'api_logs', $arr);
				
			}			
			if ($api_login and $api_key) {
				$api_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "api WHERE api_key = '$api_key' AND api_login = '$api_login'");
				if (isset($api_data->id)) {
					$api_user_id = $api_data->user_id;
					$enable_ip = trim($api_data->enable_ip);
					$api_actions = pn_json_decode(is_isset($api_data, 'api_actions'));
					if (!is_array($api_actions)) { $api_actions = array(); } 
					$ui = get_userdata($api_user_id);
					$user_id = intval(is_isset($ui, 'ID'));
					$user_status = intval(is_isset($ui, 'user_bann'));
					$work_api = intval(is_isset($ui, 'work_api'));
					if ($user_status < 1) {
						if (1 == $api or 2 == $api and 1 == $work_api) {
							if ($enable_ip and !pn_has_ip($enable_ip)) {
								$json = array(
									'error' => 2,
									'error_text' => 'IP blocked',
								);								
								echo pn_json_encode($json);
								exit;
							}

							$json = array(
								'error' => 3,
								'error_text' => 'Method not supported',
							);

							if ($user_id > 0) {
								$api_lists = array();
								$api_all_lists = apply_filters('api_all_methods', array());
								$enable = $premiumbox->get_option('api', 'enabled_method');
								if (!is_array($enable)) { $enable = array(); }
								foreach ($api_all_lists as $api_all_list_k => $api_all_list_v) {
									if (isset($enable[$api_all_list_k])) {
										$api_lists[$api_all_list_k] = $api_all_list_v;
									}
								}								
							} else {
								$api_lists = apply_filters('api_all_methods', array());
							}				

							if (isset($api_lists[$endpoint]) and isset($api_actions[$endpoint])) {

								do_action('userapi_v1_' . $endpoint, $ui, $api_login);
					
							}

							echo pn_json_encode($json);
							exit;
						}
					}
				}
			}
		}
	}
}