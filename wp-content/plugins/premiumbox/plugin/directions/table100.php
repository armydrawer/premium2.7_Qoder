<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('exchange_table_type100', 'get_exchange_table100', 10, 4);
function get_exchange_table100($temp, $def_cur_from = '', $def_cur_to = '', $direction_id = '') {	

	$temp = apply_filters('before_exchange_page', '', 'home');
	
	$temp .= '
	<form method="post" class="ajax_post_bids" action="' . get_pn_action('create_bid') . '">
		<div class="exch_ajax_wrap">
			<div class="exch_ajax_wrap_abs"></div>
			<div id="exch_html">' . get_exchange_html($direction_id) . '</div>
		</div>
	</form>
	';
	
	$temp .= apply_filters('after_exchange_page', '', 'home');	

	return $temp;
}