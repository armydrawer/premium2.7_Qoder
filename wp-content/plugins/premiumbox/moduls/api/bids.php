<?php 
if (!defined('ABSPATH')) { exit(); }

add_filter('onebid_col4', 'onebid_col_api', 10, 3);
function onebid_col_api($nactions, $item, $v) {
	
	$actions = array();
	$api_login = is_api_key($item->api_login);
	$api_id = pn_strip_input($item->api_id);
	if ($api_login) {
		$actions['api_login'] = array(
			'type' => 'text',
			'title' => __('API login', 'pn'),
			'label' => $api_login,
		);
		$callback_url = str_replace('&amp;', '&', pn_strip_input(get_bids_meta($item->id, 'api_callback_url')));
		if ($callback_url) {
			$actions['api_callback_url'] = array(
				'type' => 'text',
				'title' => __('Callback url', 'pn'),
				'label' => '<a href="' . $callback_url . '" target="_blank">' . $callback_url . '</a>', 
			);			
		}		
	}
	if ($api_id) {
		$actions['api_id'] = array(
			'type' => 'text',
			'title' => __('API id', 'pn'),
			'label' => $api_id,
		);	
	}
	
	return pn_array_insert($nactions, 'last_name', $actions, 'before');
}
 
add_filter('array_data_create_bids', 'api_array_data_create_bids', 8, 5);
function api_array_data_create_bids($array, $direction, $vd1, $vd2, $cdata) {
	
	if (_is('is_api')) {
		if (is_extension_active('pn_extended', 'moduls', 'pp')) {
			$array['ref_id'] = intval(is_param_post('partner_id'));
		}
		$headers = array();
		$headers_arrs = getallheaders();
		foreach ($headers_arrs as $head_k => $head_v) {
			$headers[strtolower($head_k)] = $head_v;
		}
		$api_login = is_api_key(is_isset($headers, 'api-login'));
		if ($api_login) {
			$array['api_login'] = $api_login;
			$array['api_id'] = pn_maxf(pn_strip_input(is_param_post('api_id')), 100);
		}
	} 
	
	return $array;
}

add_filter('error_bids', 'api_error_bids', 20, 4); 
function api_error_bids($error_bids, $direction, $vd1, $vd2) {
	global $wpdb;

	$bid = $error_bids['bid'];
	$api_id = pn_strip_input(is_isset($bid, 'api_id'));
	$api_login = is_api_key(is_isset($bid, 'api_login')); 
	
	$api = intval(is_isset($direction, 'api'));
	$enable = 0;
	if (0 == $api or 1 == $api and _is('is_api') or 2 == $api and !_is('is_api')) {
		$enable = 1;
	}
	
	if ($enable) {
		if ($api_id > 0) {
			$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE api_id = '$api_id' AND api_login = '$api_login'");
			if ($count > 0) {
				$error_bids['error_text'][] = 'api id not unique';
			}
		}		
	}
	
	if (!$enable) {
		$error_bids['error_text'][] = __('Error! Direction prohibited by settings API', 'pn');
	}	

	return $error_bids;
}

add_filter('change_bids_filter_list', 'api_change_bids_filter_list'); 
function api_change_bids_filter_list($lists) {
	
	$lists['other']['api_login'] = array(
		'title' => __('API login', 'pn'),
		'name' => 'api_login',
		'view' => 'input',
		'work' => 'input',
	);
	$lists['other']['api_id'] = array(
		'title' => __('API id', 'pn'),
		'name' => 'api_id',
		'view' => 'input',
		'work' => 'input',
	); 	
	
	return $lists;
}

add_filter('where_request_sql_bids', 'where_request_sql_bids_api', 0, 2); 
function where_request_sql_bids_api($where, $pars_data) {
	global $wpdb;	
	
	$sql_operator = is_sql_operator($pars_data);
	
	$api_login = pn_strip_input(is_isset($pars_data, 'api_login'));
	if ($api_login) {
		$where .= " {$sql_operator} {$wpdb->prefix}exchange_bids.api_login = '$api_login'";
	}
	$api_id = pn_strip_input(is_isset($pars_data, 'api_id'));
	if ($api_id) {
		$where .= " {$sql_operator} {$gcdb->prefix}exchange_bids.api_id = '$api_id'";
	}	
	
	return $where;
}

add_action('tab_direction_tab7', 'api_tab_direction_tab', 1, 2);
function api_tab_direction_tab($data, $data_id) {
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('API version', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$api = intval(is_isset($data, 'api')); 
				?>														
				<select name="api" autocomplete="off"> 
					<option value="0" <?php selected($api, 0); ?>><?php _e('for all', 'pn'); ?></option>
					<option value="1" <?php selected($api, 1); ?>><?php _e('API only', 'pn'); ?></option>
					<option value="2" <?php selected($api, 2); ?>><?php _e('Website only', 'pn'); ?></option>
				</select>
			</div>
		</div>
	</div>		
	<?php 		
}

add_filter('pn_direction_addform_post', 'api_direction_addform_post');
function api_direction_addform_post($array) {
	
	$array['api'] = intval(is_param_post('api'));
	
	return $array;
} 

add_filter('get_direction_output', 'api_get_direction_output', 10, 2);
function api_get_direction_output($ind, $dir) {
	
	$api = intval(is_isset($dir, 'api'));
	$enable = 0;
	if (0 == $api or 1 == $api and _is('is_api') or 2 == $api and !_is('is_api')) {
		$enable = 1;
	}
	
	if (!$enable) {
		return 0;
	}
	
	return $ind;
}

add_action('item_direction_delete', 'api_item_direction_delete', 10, 3);
function api_item_direction_delete($id, $item, $result) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "api_callbacks WHERE bid_id = '$id'");
}

add_filter('change_bid_status', 'api_change_bidstatus', 5000);   
function api_change_bidstatus($data) { 
	global $wpdb, $premiumbox;

	$place = $data['place'];
	$set_status = $data['set_status'];
	$bid = $data['bid'];
	$who = $data['who'];
	$old_status = $data['old_status'];
	$direction = $data['direction'];
	
	$stop_action = intval(is_isset($data, 'stop'));
	if (!$stop_action) {
		if ($set_status != $old_status and isset($bid->id) and !in_array($set_status, array('archived', 'realdelete'))) {
			$callback_url = str_replace('&amp;', '&', pn_strip_input(get_bids_meta($bid->id, 'api_callback_url')));
			if ($callback_url) {
				$callback_log = intval($premiumbox->get_option('api', 'callbacks'));				
				if ($ch = curl_init()) {			
					$post = array();
					$post['bid_id'] = $bid->id;
					$post['status'] = $set_status;
					$post = apply_filters('callback_api_post_data', $post, $bid);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0');
					curl_setopt($ch, CURLOPT_URL, $callback_url);						
					if (count($post) > 0) {
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
					}			
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
					curl_setopt($ch, CURLOPT_TIMEOUT, 20);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
					curl_setopt($ch, CURLOPT_ENCODING, '');
										
					$err  = curl_errno($ch);
					$result = curl_exec($ch);
					$info = curl_getinfo($ch);			
										
					curl_close($ch);
								
					if ($callback_log) {
						$arr = array();
						$arr['create_date'] = current_time('mysql');
						$arr['callback_url'] = $callback_url;
						$arr['post_data'] = http_build_query($post);
						$arr['bid_id'] = $bid->id;
						$arr['ip'] = pn_strip_input(pn_real_ip());
						$arr['result_data'] = pn_strip_input(print_r($result, true));	
						$wpdb->insert($wpdb->prefix . 'api_callbacks', $arr);					
					}				
				}							
			}			
		}
	}
	
	return $data;
}