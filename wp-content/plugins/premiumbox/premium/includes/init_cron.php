<?php 
if (!defined('ABSPATH')) { exit(); }

//* * * */02 *  wget --spider http://site.ru/cron.html > /dev/null

if (!function_exists('check_hash_cron')) {
	function check_hash_cron() {
		
		$cron_hash = '';
		if (defined('PN_HASH_CRON') and is_string(PN_HASH_CRON)) {
			$cron_hash = trim(PN_HASH_CRON);
		}	
		
		if ($cron_hash) {
			$get_cron_hash = pn_string(is_param_get('hcron'));
			if (!$get_cron_hash or $get_cron_hash != $cron_hash) {
				return 0;
			}
		}
		
		return 1;		
	}
}

if (!function_exists('get_hash_cron')) {
	function get_hash_cron($zn) {
		
		$cron_hash = '';
		if (defined('PN_HASH_CRON')) {
			$cron_hash = trim(PN_HASH_CRON);
		}
		
		if ($cron_hash) {
			return $zn . 'hcron=' . $cron_hash;
		}
		
		return '';
	}	
}

if (!function_exists('get_cron_link')) {
	function get_cron_link($action = '') {
		
		$action = trim($action);
		$site_url = PN_SITE_URL;
		$cron_link = $site_url . 'cron';
		if (strlen($action) > 0) {
			$cron_link .= '-' . $action;
		}
		$cron_link .= '.html';
		$cron_link .= get_hash_cron('?');
		
		return $cron_link;
	}
}

if (!function_exists('pn_cron_times')) {
	function pn_cron_times() {
		
		$cron_times = array();
		$cron_times['none'] = array(
			'time' => '-1',
			'title' => __('Never', 'premium'),
		);		
		$cron_times['now'] = array(
			'time' => 0,
			'title' => __('When handling', 'premium'),
		);
		$cron_times['1min'] = array(
			'time' => (1 * MINUTE_IN_SECONDS),
			'title' => __('Interval 1 minutes', 'premium'),
		);		
		$cron_times['2min'] = array(
			'time' => (2 * MINUTE_IN_SECONDS),
			'title' => __('Interval 2 minutes', 'premium'),
		);
		$cron_times['5min'] = array(
			'time' => (5 * MINUTE_IN_SECONDS),
			'title' => __('Interval 5 minutes', 'premium'),
		);
		$cron_times['10min'] = array(
			'time' => (11 * MINUTE_IN_SECONDS),
			'title' => __('Interval 10 minutes', 'premium'),
		);
		$cron_times['15min'] = array(
			'time' => (15 * MINUTE_IN_SECONDS),
			'title' => __('Interval 15 minutes', 'premium'),
		);		
		$cron_times['30min'] = array(
			'time' => (31 * MINUTE_IN_SECONDS),
			'title' => __('Interval 30 minutes', 'premium'),
		);
		$cron_times['1hour'] = array(
			'time' => (61 * MINUTE_IN_SECONDS),
			'title' => __('Interval 1 hour', 'premium'),
		);
		$cron_times['3hour'] = array(
			'time' => (3 * HOUR_IN_SECONDS),
			'title' => __('Interval 3 hours', 'premium'),
		);
		$cron_times['05day'] = array(
			'time' => (12 * HOUR_IN_SECONDS),
			'title' => __('Interval 12 hours', 'premium'),
		);
		$cron_times['1day'] = array(
			'time' => DAY_IN_SECONDS,
			'title' => __('Interval 24 hours', 'premium'),
		);
		$cron_times['1month'] = array(
			'time' => MONTH_IN_SECONDS,
			'title' => __('Interval 1 month', 'premium'),
		);
		$cron_times['3month'] = array(
			'time' => (3 * MONTH_IN_SECONDS),
			'title' => __('Interval 3 months', 'premium'),
		);		
		$cron_times = apply_filters('cron_times', $cron_times);
		
		return $cron_times;
	}
}

if (!function_exists('pn_cron_init')) {
	function pn_cron_init($place = '') {
		
		$now_time = current_time('timestamp');
		
		$pn_cron = get_option('pn_cron');
		if (!is_array($pn_cron)) { $pn_cron = array(); }
		
		$times = pn_cron_times();
		
		$update_times_all = is_isset($pn_cron, 'update_times');
		$update_times = is_isset($update_times_all, $place);
		
		$go_times = array();
		
		foreach ($times as $time_key => $time_data) {
			if ('none' != $time_key) {
				$timer_plus = intval(is_isset($time_data, 'time'));
				$last_time = intval(is_isset($update_times, $time_key));
				$action_time = $last_time + $timer_plus;
				if ($action_time < $now_time) {
					$go_times[] = $time_key;
				}
			}	
		}
		
		$actions = array();
		
		$cron_func = apply_filters('list_cron_func', array());
		$cron_func = (array)$cron_func;		
		
		foreach ($go_times as $time_key) {
			foreach ($cron_func as $func_name => $func_data) {
				$work_time = trim(is_isset($func_data, $place));
				$allways = intval(is_isset($func_data, 'allways'));
				$priority = intval(is_isset($func_data, 'priority'));
				if (isset($pn_cron[$place][$func_name]['work_time']) and 1 != $allways) {
					$work_time = trim($pn_cron[$place][$func_name]['work_time']);
				}
				if ($work_time == $time_key) {
					$actions[] = array(
						'priority' => $priority,
						'func_name' => $func_name,
					); 
					$pn_cron[$place][$func_name]['last_update'] = $now_time;
				}
			}
			$pn_cron['update_times'][$place][$time_key] = $now_time;		
		}			
		
		update_option('pn_cron', $pn_cron);

		$actions = pn_array_sort($actions, 'priority', 'desc', 'num');

		_do_cron_actions($actions, $place, $cron_func, 1);
					
	}
}

if (!function_exists('_do_cron_actions')) {
	function _do_cron_actions($actions, $place, $cron_func, $ind) {
		
		$cron_sleep = intval(PN_CRON_SLEEP);
		$c_actions = count($actions);
		if ($ind <= $c_actions) {
		
			$r = 0;
			foreach ($actions as $action) { $r++;
				if ($r == $ind) {
					
					if ($r > 1) {
						if ($cron_sleep > 0 and 'site' != $place) {
							sleep($cron_sleep);
						}
					}
					
					$now_action = trim(is_isset($action, 'func_name'));
					go_pn_cron_func($now_action, $place, 0, $cron_func);
					
				}
			}
		
			$new_ind = $ind + 1;
			_do_cron_actions($actions, $place, $cron_func, $new_ind);
		}
	}
}

if (!function_exists('go_pn_cron_func')) {
	function go_pn_cron_func($action = '', $place = '', $update_time = 0, $cron_func = '') {
		if ($action) {
			
			$funcs = array();
			if (!is_array($cron_func)) {
				$cron_func = apply_filters('list_cron_func', array());
				$cron_func = (array)$cron_func;
			}
			foreach ($cron_func as $func => $name) {
				$funcs[$func] = $func;
			}
			if (isset($funcs[$action])) {

				$is_site = 0;
				if (isset($cron_func[$action]['site'])) {
					$is_site = 1;
				}
					
				$is_file = 0;
				if (isset($cron_func[$action]['file'])) {
					$is_file = 1;
				}	
			
				if ($is_site and 'site' == $place or $is_file and 'file' == $place) {
			
					if (1 == $update_time){
						$pn_cron = get_option('pn_cron');
						if (!is_array($pn_cron)) { $pn_cron = array(); }
						$pn_cron[$place][$action]['last_update'] = current_time('timestamp');
						update_option('pn_cron', $pn_cron);
					}
				
					call_user_func($action);
				
				}
				
			} else {
			
				_e('Error! Invalid command for task scheduler (cron)', 'premium');
				exit;
				
			}
		}
	}
}

if (!function_exists('pn_cron_init_all')) {
	add_action('template_redirect', 'pn_cron_init_all', 10);
	function pn_cron_init_all() {
		pn_cron_init('site');	
	}	
}	