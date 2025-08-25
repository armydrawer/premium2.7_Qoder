<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('def_premium_request_setc')) {
	add_action('premium_request_setc', 'def_premium_request_setc');
	function def_premium_request_setc() {
		
		_method('post');
			
		$cookie_key = pn_strip_input(is_param_post('key'));
		$cookie_value = pn_strip_input(is_param_post('value'));
		$days = is_sum(is_param_post('days'));
		if ($days > 365) { $days = 365; }
			
		if (strlen($cookie_key) > 0) {	
			$end_time = current_time('U') + ($days * DAY_IN_SECONDS);
			add_pn_cookie($cookie_key, $cookie_value, $end_time, 1);
		}
		
	}
}