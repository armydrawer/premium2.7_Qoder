<?php
if (!defined('ABSPATH')) { exit(); }

add_action('wp_dashboard_setup', 'stats_wp_dashboard_setup');
function stats_wp_dashboard_setup() {
	
	wp_add_dashboard_widget('stats_dashboard_widget', __('Statistics', 'pn'), 'stats_dashboard_widget_function');
	
}

function stats_dashboard_widget_function() {
	
	$date = current_time('Y-m-d 00:00:00');
	$array = array(
		'total_users' => __('Total users', 'pn'),
		'today_users' => __('Registered users today', 'pn'),
		'count_exchanges' => __('Number of exchanges today', 'pn'),
		'amount_exchanges' => __('Amount of exchanges today', 'pn'),
		'total_reserve' => __('Total amount of reserves', 'pn'),
	);
	$html = '';
	$lists = apply_filters('lists_stats_widget', $array); 
	foreach ($lists as $list_k => $list_v) {
		
		if ('total_users' == $list_k) {
			$html .= '<div class="widget_stats_line"><span>' . $list_v . ':</span> ' . is_out_sum(get_user_for_site(), 0, 'all') . '</div>';
		} elseif ('today_users' == $list_k) {
			$html .= '<div class="widget_stats_line"><span>' . $list_v . ':</span> ' . is_out_sum(get_user_for_site($date), 0, 'all') . '</div>';
		} elseif ('count_exchanges' == $list_k) {
			$html .= '<div class="widget_stats_line"><span>' . $list_v . ':</span> ' . is_out_sum(get_count_exchanges($date,''), 0, 'all') . '</div>';
		} elseif ('amount_exchanges' == $list_k) {
			$html .= '<div class="widget_stats_line"><span>' . $list_v . ':</span> ' . is_out_sum(get_sum_exchanges($date, '', cur_type()), 2, 'all') . ' ' . cur_type() . '</div>';
		} elseif ('total_reserve' == $list_k) {	
			$html .= '<div class="widget_stats_line"><span>' . $list_v . ':</span> ' . is_out_sum(get_general_reserve(cur_type()), 2, 'reserv') . ' ' . cur_type() . '</div>';							
		} else {
			$html .= apply_filters('show_stats_widget', '', $list_k, $list_v);
		}		
		
	}	
	
	echo $html;
}