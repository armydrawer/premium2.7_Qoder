<?php
if (!defined('ABSPATH')) { exit(); }

function new_parser_upload_data() {
	global $premiumbox;
	
	if (!$premiumbox->is_up_mode()) {		
		
		$logs = array();
		$time_start = current_time('timestamp');
		$logs = parser_add_logs($logs, '--- Start loading ---', 0);
		
		$timeout = intval($premiumbox->get_option('newparser', 'timeout'));
		if ($timeout < 0) { $timeout = 5; }
		
		$links = apply_filters('work_parser_links', array());
		if (0 == count($links)) {
			$logs = parser_add_logs($logs, 'Not work links', 1);
		}	
		
		$p_errs = array();
		
		$delold = intval($premiumbox->get_option('newparser', 'delold'));
		if ($delold) {
			$parser_pairs = array();
		} else {
			$parser_pairs = get_array_option($premiumbox, 'pn_parser_pairs');
		}
		if (!is_array($parser_pairs)) { $parser_pairs = array(); }
		
		$vers = intval($premiumbox->get_option('newparser', 'parser'));
		
		if (1 == $vers and function_exists('curl_multi_init')) {
			
			$multi = curl_multi_init();
			$channels = array();
			
			foreach ($links as $birg_key => $data) {
				$url = trim(is_isset($data, 'url'));
				$title = trim(is_isset($data, 'title'));
				$post_data = trim(is_isset($data, 'post_data'));
				$headers = array();
				if (isset($data['headers'])) {
					$headers = $data['headers'];
				}
				if ($url) {
					
					$ch = curl_init($url);	
					
					$curl_options = array(
						CURLINFO_HEADER_OUT => 0,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_FOLLOWLOCATION => true,
						CURLOPT_MAXREDIRS => 2,
						CURLOPT_SSL_VERIFYPEER => 0,
						CURLOPT_SSL_VERIFYHOST => 0,
						CURLOPT_CONNECTTIMEOUT => $timeout,
						CURLOPT_TIMEOUT => $timeout,
						CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
						CURLOPT_ENCODING => '',
						CURLOPT_PROTOCOLS => CURLPROTO_HTTP|CURLPROTO_HTTPS,
						CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36 OPR/65.0.3467.72',
						CURLOPT_HTTPHEADER => array(
							'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
							'Cache-Control: no-cache',
							'Pragma: no-cache'
						)
					);

					if (is_array($headers) and count($headers) > 0) {
						$curl_options[CURLOPT_HTTPHEADER] = $headers;
					}
					
					if (strlen($post_data) > 0) {
						$curl_options[CURLOPT_POST] = true;
						$curl_options[CURLOPT_POSTFIELDS] = $post_data;
					}
					
					$curl_options = apply_filters('curl_options_parser', $curl_options, $birg_key);

					curl_setopt_array($ch, $curl_options);
					curl_multi_add_handle($multi, $ch);
					
					$channels[$birg_key] = array(
						'resource' => $ch,
						'url' => $url,
						'title' => $title,
					);
				}
			}

			$mrc = curl_multi_exec($multi, $active);

			$active = null;
			do {
				$mrc = curl_multi_exec($multi, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
 
			while ($active and $mrc == CURLM_OK) {
				if (-1 == curl_multi_select($multi)) {
					continue;
				}
				do {
					$mrc = curl_multi_exec($multi, $active);
				} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
 
			foreach ($channels as $birg_key => $chin) {
				$channel = is_isset($chin, 'resource');
				
				$output = curl_multi_getcontent($channel);
				$errno = curl_errno($channel);
				$info = curl_getinfo($channel);
				$code = intval(is_isset($info, 'http_code'));
				$url = trim(is_isset($chin, 'url'));
				$title = trim(is_isset($chin, 'title'));
				
				if (200 == $code) {
					$logs = parser_add_logs($logs, 'Link load', 0, $birg_key, $title, $info);
					$parser_pairs = apply_filters('set_parser_pairs', $parser_pairs, $output, $birg_key, $time_start);
				} else {
					$p_errs[] = 'Link error load ' . $url . ' , Cron error: ' . $code;
					$logs = parser_add_logs($logs, 'Cron error: ' . $errno . ', HTTP code: ' . $code, 1, $birg_key, $title, $info);
				}				
				
				curl_multi_remove_handle($multi, $channel);
			}
 
			curl_multi_close($multi);
			
		} else {
			
			foreach ($links as $birg_key => $data) {
				$url = trim(is_isset($data, 'url'));
				$title = trim(is_isset($data, 'title'));
				$post_data = trim(is_isset($data, 'post_data'));
				$headers = array();
				if (isset($data['headers'])) {
					$headers = $data['headers'];
				}
				if ($url) {
					$curl_options = array(
						CURLOPT_TIMEOUT => $timeout,
						CURLOPT_CONNECTTIMEOUT => $timeout,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_FOLLOWLOCATION => true,
						CURLOPT_MAXREDIRS => 2,
						CURLOPT_SSL_VERIFYPEER => 0,
						CURLOPT_SSL_VERIFYHOST => 0,
						CURLOPT_HTTPHEADER => array(
							'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
							'Cache-Control: no-cache',
							'Pragma: no-cache'
						),
					);
					
					if (is_array($headers) and count($headers) > 0) {
						$curl_options[CURLOPT_HTTPHEADER] = $headers;
					}
					
					if (strlen($post_data) > 0) {
						$curl_options[CURLOPT_POST] = true;
						$curl_options[CURLOPT_POSTFIELDS] = $post_data;
					}

					$curl = get_curl_parser($url, $curl_options, 'new_parser');
					if (!$curl['err']) {
						$output = $curl['output'];
						$logs = parser_add_logs($logs, 'Link load', 0, $birg_key, $title, $curl['info']);
						$parser_pairs = apply_filters('set_parser_pairs', $parser_pairs, $output, $birg_key, $time_start);
					} else {
						$p_errs[] = 'Link error load ' . $url . ' , Cron error: '. $curl['err'];
						$logs = parser_add_logs($logs, 'Cron error: ' . $curl['err'], 1, $birg_key, $title, $curl['info']);
					}
				}
			}
			
		}
		
		$parser_pairs = pn_strip_input_array($parser_pairs);
		update_array_option($premiumbox, 'pn_parser_pairs', $parser_pairs);		
		
		update_option('time_new_parser', $time_start);
		
		$time_end = current_time('timestamp');
		$work_time = $time_end - $time_start;	
		$logs = parser_add_logs($logs, '--- End loading by ' . $work_time . ' seconds ---', 0);
		
		parser_db_log($logs);

		if (count($p_errs) > 0) {
			
			$notify_tags = array();
			$notify_tags['[errors]'] = join('<br />', $p_errs);
			$notify_tags = apply_filters('notify_tags_parsererrform', $notify_tags);		

			$user_send_data = array(
				'admin_email' => 1,
			);
			$result_mail = apply_filters('premium_send_message', 0, 'parsererrform', $notify_tags, $user_send_data);
			
		}

		do_action('load_new_parser_courses', $parser_pairs, $time_start);
	}	
}

function parser_add_logs($logs, $log, $code, $title = '', $key = '', $info = '') {
	global $premiumbox;	
	
	$pl = intval($premiumbox->get_option('newparser', 'parser_log'));
	if (1 == $pl or 2 == $pl and 1 == $code) {
		$logs[] = array(
			'work_date' => current_time('mysql'),
			'log_comment' => pn_strip_input($log),
			'log_code' => intval($code),
			'title_birg' => pn_strip_input($title),
			'key_birg' => pn_strip_input($key),
			'the_info' => pn_strip_input(print_r($info, true)),
		);
	}
	
	return $logs;
}

function parser_db_log($logs) {
	global $wpdb;
	
	$time = current_time('timestamp') - (10 * DAY_IN_SECONDS);
	$ldate = date('Y-m-d H:i:s', $time);
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "parser_logs WHERE work_date < '$ldate'");  

	pn_db_insert($wpdb->prefix . 'parser_logs', $logs);
}

add_filter('list_cron_func', 'new_parser_list_cron_func');
function new_parser_list_cron_func($filters) {	

	$filters['new_parser_upload_data'] = array(
		'title' => __('Rates parser', 'pn'),
		'file' => '1hour', 
	);
	
	return $filters;
}