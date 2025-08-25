<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('pn_user_register_data', 'geoip_user_register_data');
function geoip_user_register_data($array) {
	
	$array['user_country'] = get_user_country();
	
	return $array;
}

add_filter('all_user_editform', 'geoip_all_user_editform', 10, 2);
function geoip_all_user_editform($options, $data) {
	
	$user_id = $data->ID;
		
	if (current_user_can('edit_users') or current_user_can('administrator')) {
		$countries = get_countries();
		$list = array();
		$list['NaN'] = '--' . __('No item', 'pn') . '--';
		foreach ($countries as $country_attr => $country_title) {
			$list[$country_attr] = mb_substr($country_title, 0, 35);
		}

		$n_options = array();
		$n_options['user_country'] = array(
			'view' => 'select_search',
			'title' => __('Country', 'pn'),
			'options' => $list,
			'default' => is_country_attr($data->user_country),
			'name' => 'user_country',
			'work' => 'input',
		);
		$options = pn_array_insert($options, 'user_ip', $n_options, 'after');
	}			
	
	return $options;
}

add_action('all_user_editform_post', 'geoip_all_user_editform_post'); 
function geoip_all_user_editform_post($new_user_data) {
	
	if (current_user_can('edit_users') or current_user_can('administrator')) {
		$new_user_data['user_country'] = is_country_attr(is_param_post('user_country'));
	}
	
	return $new_user_data;
}

add_action('tab_direction_tab7', 'geoip_tab_direction_tab', 30, 2);
function geoip_tab_direction_tab($data, $data_id) {
	global $wpdb;	

	$en_country = get_option('geoip_country');
	if (!is_array($en_country)) { $en_country = array(); }
	
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Prohibited countries', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				
				<?php
				$arr = pn_strip_input_array(pn_json_decode(is_isset($data, 'not_country')));	
				
				$checked = 0;
				if (in_array('NaN', $arr)) {
					$checked = 1;
				}	
				$scroll_lists = array();
				$scroll_lists[] = array(
					'title' => __('is not determined', 'pn') . ' (NaN)',
					'checked' => $checked,
					'value' => 'NaN',
				);

				$i_scroll_lists = array();
				foreach ($en_country as $attr) {
					$checked = 0;
					if (in_array($attr, $arr)) {
						$checked = 1;
					}	
					$i_scroll_lists[] = array(
						'title' => get_country_title($attr) . ' (' . $attr . ')',
						'checked' => $checked,
						'value' => $attr,
					);
				}
				$i_scroll_lists = pn_array_sort($i_scroll_lists, 'title', 'asc');
				$i_scroll_lists = pn_array_sort($i_scroll_lists, 'checked', 'desc', 'num');
				$scroll_lists = array_merge($scroll_lists, $i_scroll_lists);
				echo get_check_list($scroll_lists, 'not_country[]', array('NaN' => 'bred'), '300', 1);
				?>				
				
					<div class="premium_clear"></div>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Allowed countries', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				
				<?php
				$scroll_lists = array();
					
				$arr = pn_strip_input_array(pn_json_decode(is_isset($data, 'only_country')));	
					
				$checked = 0;
				if (in_array('NaN', $arr)) {
					$checked = 1;
				}	
				$scroll_lists[] = array(
					'title' => __('is not determined', 'pn') . ' (NaN)',
					'checked' => $checked,
					'value' => 'NaN',
				);	

				$i_scroll_lists = array();
				foreach ($en_country as $attr) {
					$checked = 0;
					if (in_array($attr, $arr)) {
						$checked = 1;
					}	
					$i_scroll_lists[] = array(
						'title' => get_country_title($attr) . ' (' . $attr . ')',
						'checked' => $checked,
						'value' => $attr,
					);
				}
				$i_scroll_lists = pn_array_sort($i_scroll_lists, 'title', 'asc');
				$i_scroll_lists = pn_array_sort($i_scroll_lists, 'checked', 'desc', 'num');
				$scroll_lists = array_merge($scroll_lists, $i_scroll_lists);
				echo get_check_list($scroll_lists, 'only_country[]', array('NaN' => 'bred'), '300', 1);
				?>				
				
				<div class="premium_clear"></div>
			</div>
		</div>		
	</div>			
	<?php 		
}

add_filter('pn_direction_addform_post', 'geoip_pn_direction_addform_post');
function geoip_pn_direction_addform_post($array) {

	$not_country = pn_json_encode(pn_strip_input_array(is_param_post('not_country')));
	$array['not_country'] = $not_country;
	
	$only_country = pn_json_encode(pn_strip_input_array(is_param_post('only_country')));
	$array['only_country'] = $only_country;	
	
	return $array;
}

add_action('set_exchange_filters', 'geoip_set_exchange_filters');
function geoip_set_exchange_filters($lists) {
	
	$lists[] = array(
		'title' => __('Filter by country of user', 'pn'),
		'name' => 'napsgeoip',
		'none' => 'api',
	);
	
	return $lists;
}

add_filter('get_directions_where', 'geoip_get_directions_where',1, 2);
function geoip_get_directions_where($where, $place) {
	global $premiumbox;
	
	$ind = $premiumbox->get_option('exf_' . $place . '_napsgeoip');
	$user_country = get_user_country();
	if (1 == $ind) {
		$where .= "AND not_country NOT LIKE '%\"{$user_country}\"%' ";
	}
	
	return $where;
}

add_filter('error_bids', 'error_bids_geoip', 750, 2);  
function error_bids_geoip($error_bids, $direction) {

	if (!_is('is_api')) {
		$user_country = get_user_country();
		$not_country = pn_strip_input_array(pn_json_decode(is_isset($direction, 'not_country')));	
		if (in_array($user_country, $not_country)) {
			$error_bids['error_text'][] = __('Error! For your country exchange is denied', 'pn');			
		}	
		
		$yes_country = pn_strip_input_array(pn_json_decode(is_isset($direction, 'only_country')));	
		if (count($yes_country) > 0 and !in_array($user_country, $yes_country)) {
			$error_bids['error_text'][] = __('Error! For your country exchange is denied', 'pn');			
		}	
		$error_bids['bid']['user_country'] = $user_country;
	}
		
	return $error_bids;
}

add_filter('change_bids_filter_list', 'geoip_change_bids_filter_list'); 
function geoip_change_bids_filter_list($lists) {
	global $wpdb;
	
	$options = array(
		'0' => '--' . __('All', 'pn') . '--',
		'NaN' => __('is not determined', 'pn'),
	);
	
	$countries = get_option('geoip_country');
	if (!is_array($countries)) { $countries = array(); }

	$n_options = array();
	foreach ($countries as $attr) {
		$n_options[$attr] = get_country_title($attr);
	}
	
	asort($n_options);
	
	$options = array_merge($options, $n_options);
		
	$lists['other']['country'] = array(
		'title' => __('User country', 'pn'),
		'name' => 'country',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);	
	
	return $lists;
}

add_filter('where_request_sql_bids', 'napsgeoip_where_request_sql_bids', 10, 2);
function napsgeoip_where_request_sql_bids($where, $pars_data) {
	global $wpdb;

	$pr = $wpdb->prefix;
	$sql_operator = is_sql_operator($pars_data);
	$country = is_country_attr(is_isset($pars_data, 'country'));
	if ($country) { 
		$where .= " {$sql_operator} {$pr}exchange_bids.user_country = '$country'";
	}	
	
	return $where;
}

add_filter('onebid_icons', 'onebid_icons_geoip', 99, 3);
function onebid_icons_geoip($onebid_icon, $item, $v = '') {
	 
	if (isset($item->user_country)) {
		
		$country = get_country_title($item->user_country);
		
		$user_cou = $item->user_country;	
		if ('NaN' == $user_cou or !$user_cou) {
			$user_cou = __('N/A', 'pn');
		}		
		$country_attr = '<span class="item_country_attr">' . $user_cou . '</span>';
		
		$onebid_icon['napsgeoip'] = array(
			'type' => 'text',
			'title' => __('User country', 'pn') . ': ' . $country,
			'label' => $country_attr,
		);	
	}
	
	return $onebid_icon; 
}

add_action('premium_request_test_geoip', 'def_test_geoip');
function def_test_geoip() {
	global $wpdb, $premiumbox;
	
	if (current_user_can('administrator')) {
		
		$ip = pn_real_ip();
		echo 'ip: ' . $ip . "\n";
		
		$type = intval($premiumbox->get_option('geoip', 'type'));
		$api_key = pn_strip_input($premiumbox->get_option('geoip', 'api_key'));
		$timeout = intval($premiumbox->get_option('geoip', 'timeout'));
		if ($timeout < 1) { $timeout = 30; }		
		
		$curl_options = array(
			CURLOPT_TIMEOUT => $timeout,
			CURLOPT_CONNECTTIMEOUT => $timeout,
		);				
			
		$curl = array();
			
		if (1 == $type) {
			$url = 'http://ip-api.com/php/' . $ip;
			$curl = get_curl_parser($url, $curl_options, 'geoip');	
		} elseif (2 == $type) {
			$url = 'https://api.2ip.ua/geo.json?ip=' . $ip;
			$curl = get_curl_parser($url, $curl_options, 'geoip');			
		} elseif (3 == $type) {  
			$url = 'http://api.sypexgeo.net/json/' . $ip;
			if ($api_key) {
				$url = 'http://api.sypexgeo.net/' . $api_key . '/json/' . $ip;
			}
			$curl = get_curl_parser($url, $curl_options, 'geoip');				
		}	

		print_r($curl);
		
	}
}	

add_action('init', 'geoip_detected_init', 0);
function geoip_detected_init() {  
	global $wpdb, $user_now_country;

	$user_now_country = '';
		
	if (!_is('is_admin') and !_is('is_script') and !_is('is_adminaction') and !_is('is_api')) {

		$plugin = get_plugin_class();
			
		$ip = pn_real_ip();
			
		$memory = intval($plugin->get_option('geoip', 'memory'));
		$type = intval($plugin->get_option('geoip', 'type'));
		$api_key = pn_strip_input($plugin->get_option('geoip', 'api_key'));
		$timeout = intval($plugin->get_option('geoip', 'timeout'));
		if ($timeout < 1) { $timeout = 30; }
			
		$in_memory = 0;
		$user_country = '';
			
		if (1 == $memory) {
			$data_memory = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "geoip_memory WHERE ip = '$ip'"); 
			if (isset($data_memory->country_attr)) {
				$user_country = $data_memory->country_attr;
				$in_memory = 1;
			}
		}
			
		if (!$user_country) {
				
			$curl_options = array(
				CURLOPT_TIMEOUT => $timeout,
				CURLOPT_CONNECTTIMEOUT => $timeout,
			);				
				
			if (1 == $type) {
				$url = 'http://ip-api.com/php/' . $ip;
				$curl = get_curl_parser($url, $curl_options, 'geoip');
				if (!$curl['err']) {
					$output = $curl['output'];
					$out = @unserialize($output);
					if (isset($out['countryCode'])) {
						$user_country = $out['countryCode'];
					}
				}	
			} elseif (2 == $type) {
				$url = 'https://api.2ip.ua/geo.json?ip=' . $ip;
				$curl = get_curl_parser($url, $curl_options, 'geoip');
				if (!$curl['err']) {
					$output = $curl['output'];
					$out = @json_decode($output, true);
					if (isset($out['country_code'])) {
						$user_country = $out['country_code'];
					}
				}				
			} elseif (3 == $type) {  
				$url = 'http://api.sypexgeo.net/json/' . $ip;
				if ($api_key) {
					$url = 'http://api.sypexgeo.net/' . $api_key . '/json/' . $ip;
				}
				$curl = get_curl_parser($url, $curl_options, 'geoip');
				if (!$curl['err']) {
					$output = $curl['output'];
					$out = @json_decode($output, true);
					if (isset($out['country'], $out['country']['iso'])) {
						$user_country = $out['country']['iso'];
					}
				}				
			}
				
		}
			
		$user_country = is_country_attr($user_country);

		if (1 == $memory and $user_country and 1 != $in_memory) {
			$arr = array();
			$arr['ip'] = $ip;
			$arr['country_attr'] = $user_country;
			$wpdb->insert($wpdb->prefix . "geoip_memory", $arr);
		}		
			
		$countries = get_countries();
			
		$en_country = get_option('geoip_country');
		if (!is_array($en_country)) { $en_country = array(); }
			
		if (isset($countries[$user_country]) and isset($en_country[$user_country])) {
			$user_now_country = $user_country;
		}
	}	
}

add_action('init', 'geoip_init', 1);
function geoip_init() { 
	global $wpdb;

	if (!_is('is_admin') and !_is('is_script') and !_is('is_adminaction') and !_is('is_api')) {

		$plugin = get_plugin_class();
			
		$spider = 0;
		$agent = is_isset($_SERVER,'HTTP_USER_AGENT');
			
		if (preg_match("~(Google|Yahoo|Rambler|Bot|Yandex|Spider|Snoopy|Crawler|Finder|Mail|curl)~i", $agent)) {
			$spider = 1;
		} 
			
		$ip = pn_real_ip();
		$ip_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "geoip_ips WHERE theip = '$ip'");
		$thetype = 2;
		if (isset($ip_data->thetype)) {
			$thetype = intval($ip_data->thetype);
		}	
			
		if (0 == $thetype) {

			header('Content-Type: text/html; charset=' . get_charset());
				
			$temp = '
			<html ' . get_language_attributes() . '>
			<head profile="http://gmpg.org/xfn/11">
				<meta charset="' . get_charset() . '">
				<title>' . __('Your ip is blocked', 'pn') . '</title>
				<link rel="stylesheet" href="' . $plugin->plugin_url . 'moduls/geoip/sitestyle.css" type="text/css" media="screen" />
				'. apply_filters('premium_other_head', '', 'geoip') .'
			</head>
			<body class="' . implode(' ', get_body_class()) . '">';
					
				$temp_content = '
				<div id="container">
					<div class="title">' . __('Your ip is blocked', 'pn') . '</div>
					<div class="content">
						<div class="text">
							'. __('Access to the website is prohibited', 'pn') .'
						</div>	
					</div>
				</div>';
				$temp .= apply_filters('geoip_blockip_temp', $temp_content, $ip);
				
			$temp .= '
			</body>
			</html>
			';
				
			echo $temp;
			exit;
		}	
			
		if (1 != $thetype and 1 != $spider) {

			$blocked = $plugin->get_option('geoip', 'blocked');
			if (!is_array($blocked)) { $blocked = array(); }
				
			$user_now_country = get_user_country();
			if ($user_now_country and in_array($user_now_country, $blocked)) {

				$title = pn_strip_input(ctv_ml($plugin->get_option('geoip', 'title')));
				if (strlen($title) < 1) { $title = __('Your country is blocked', 'pn'); }
				$text = pn_strip_text(ctv_ml($plugin->get_option('geoip', 'text')));
				if (strlen($text) < 1) { $text = __('Your country is blocked', 'pn'); }

				header('Content-Type: text/html; charset=' . get_charset());
						
				$temp = '
				<html ' . get_language_attributes() . '>
				<head profile="http://gmpg.org/xfn/11">
					<meta charset="' . get_charset() . '">
					<title>' . $title . '</title>
					<link rel="stylesheet" href="' . $plugin->plugin_url . 'moduls/geoip/sitestyle.css" type="text/css" media="screen" />
					'. apply_filters('premium_other_head', '', 'geoip') .'
				</head>
				<body class="' . implode(' ', get_body_class()) . '">';
					
					$temp_content = '
					<div id="container">
						<div class="title">' . $title . '</div>
						<div class="content">
							<div class="text">
								'. apply_filters('comment_text', $text) .'
							</div>	
						</div>
					</div>
					';
					$temp .= apply_filters('geoip_bloccountry_temp', $temp_content, $title, $text);
						
				$temp .= '	
				</body>
				</html>
				';
					
				echo $temp;
				exit;
			}		
		}
	}	
}

add_filter("pntable_columns_all_usfield", 'geoip_pntable_columns_all_usfield', 100);
function geoip_pntable_columns_all_usfield($columns) {
	
	$n_columns = array();
	$n_columns['country'] = __('Country', 'pn');
	$columns = pn_array_insert($columns, 'lang', $n_columns);
	
	return $columns;
}

add_filter("pntable_column_all_usfield", 'geoip_pntable_column_all_usfield', 10, 3);
function geoip_pntable_column_all_usfield($return, $column_name,$item) {
	
	if ('country' == $column_name) {
		$country = @unserialize(is_isset($item, 'country'));
		if (!is_array($country)) { $country = array(); }
			
		$countrs = array();
		foreach ($country as $cou) {
			$countrs[] = get_country_title($cou);
		}
		if (0 == count($countrs)) { 
			return __('All', 'pn');
		} else {
			return implode(', ', $countrs);
		}
	}
		
	return $return;
}

add_filter("all_usfield_addform", 'geoip_all_usfield_addform', 10, 2);
function geoip_all_usfield_addform($options, $data) {
	
	$n_options = array();
	$n_options['country'] = array(
		'view' => 'user_func',
		'name' => 'country',
		'func_data' => $data,
		'func' => 'all_usfield_addform_country',
		'work' => 'input_array',
	);		
	$options = pn_array_insert($options, 'locale', $n_options);
		
	return $options;
}

function all_usfield_addform_country($data) {
	?>
	<div class="premium_standart_line"> 
		<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Display field based on user location (IP address detection)', 'pn'); ?></div></div>
		<div class="premium_stline_right"><div class="premium_stline_right_ins">
			<div class="premium_wrap_standart">
				<?php
				$scroll_lists = array();
					
				$def = @unserialize(is_isset($data, 'country'));
				if (!is_array($def)) { $def = array(); }
					
				$en_country = get_option('geoip_country');
				if (!is_array($en_country)) { $en_country = array(); }
					
				$en_country = list_checks_top($en_country, $def);
					
				$checked = 0;
				if (in_array('NaN',$def) or 0 == count($def)) {
					$checked = 1;
				}	
				$scroll_lists[] = array(
					'title' => __('is not determined', 'pn') . ' (NaN)',
					'checked' => $checked,
					'value' => 'NaN',
				);	

				$i_scroll_lists = array();
				foreach ($en_country as $attr) {
					$checked = 0;
					if (in_array($attr,$def) or 0 == count($def)) {
						$checked = 1;
					}	
					$i_scroll_lists[] = array(
						'title' => get_country_title($attr),
						'checked' => $checked,
						'value' => $attr,
					);
				}
				$i_scroll_lists = pn_array_sort($i_scroll_lists, 'title', 'asc');
				$scroll_lists = array_merge($scroll_lists, $i_scroll_lists);
				echo get_check_list($scroll_lists, 'country[]', array('NaN' => 'bred'), '', 1);
				?>
				<div class="premium_clear"></div>
			</div>
		</div></div>
			<div class="premium_clear"></div>
	</div>	
	<?php			
} 

add_filter("all_usfield_addform_post", 'geoip_all_usfield_addform_post');
function geoip_all_usfield_addform_post($array) {

	$country = is_param_post('country');
	$item = array();
	if (is_array($country)) {
		foreach ($country as $v) {
			$v = is_country_attr($v);
			if ($v) {
				$item[] = $v;
			}
		}
	}
	if (count($item)) {
		$array['country'] = @serialize($item);
	} else {
		$array['country'] = '';
	}

	return $array;
}