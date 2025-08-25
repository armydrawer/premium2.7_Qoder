<?php
if (!defined('ABSPATH')) { exit(); }

add_action('tab_direction_tab7', 'tab_direction_tab_coupons', 10, 2);
function tab_direction_tab_coupons($data, $data_id) {
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Discount coupons', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$disabled_coupons = intval(is_isset($data, 'disabled_coupons'));
				?>									
				<select name="disabled_coupons" autocomplete="off"> 
					<option value="0" <?php selected($disabled_coupons, 0); ?>><?php _e('Enable', 'pn'); ?></option>
					<option value="1" <?php selected($disabled_coupons, 1); ?>><?php _e('Disable', 'pn'); ?></option>
				</select>		
			</div>							
		</div>
	</div>	
<?php
}  
	 
add_filter('pn_direction_addform_post', 'coupons_pn_direction_addform_post');
function coupons_pn_direction_addform_post($array) {
	
	$array['disabled_coupons'] = intval(is_param_post('disabled_coupons'));
	
	return $array;
} 

add_filter('onebid_col4', 'onebid_col_coupons', 0, 3);
function onebid_col_coupons($actions, $item, $v) {
	
	$coupon = is_coupon($item->coupon);
	if ($coupon) {
		$actions['coupon'] = array(
			'type' => 'text',
			'title' => __('Coupon code', 'pn'),
			'label' => $coupon,
		);		
	}
	
	return $actions;
}

add_filter('change_bids_filter_list', 'coupons_change_bids_filter_list'); 
function coupons_change_bids_filter_list($lists) {
	
	$lists['other']['coupon_code'] = array(
		'title' => __('Coupon code', 'pn'),
		'name' => 'coupon_code',
		'view' => 'input',
		'work' => 'input',
	);
	
	return $lists;
}

add_filter('where_request_sql_bids', 'where_request_sql_bids_coupons', 0, 2); 
function where_request_sql_bids_coupons($where, $pars_data) {
	global $wpdb;	
	
	$sql_operator = is_sql_operator($pars_data);
	
	$coupon_code = is_coupon(is_isset($pars_data, 'coupon_code'));
	if ($coupon_code) {
		$where .= " {$sql_operator} {$wpdb->prefix}exchange_bids.coupon = '$coupon_code'";
	}	
	
	return $where;
}

add_filter('shortcode_notify_tags_bids', 'shortcode_notify_tags_bids_coupons');
function shortcode_notify_tags_bids_coupons($tags) {
	
	$tags['coupon'] = array(
		'title' => __('Сoupon code', 'pn'),
		'start' => '[coupon]',
	);	
	
	return $tags;
}

add_filter('notify_tags_bids', 'coupons_notify_tags_bids', 10000, 3);
function coupons_notify_tags_bids($notify_tags, $item, $direction) {
	
	$notify_tags['[coupon]'] = is_coupon(is_isset($item,'coupon'));
	
	return $notify_tags;
}

add_filter('list_direction_fields', 'coupons_list_direction_fields', 10000, 2);  
function coupons_list_direction_fields($fields, $direction) { 
	
	$disabled_coupons = intval(is_isset($direction, 'disabled_coupons'));
	if (1 != $disabled_coupons) {
	
		$fields['coupon_code'] = array(
			'type' => 'text',
			'name' => 'coupon_code',
			'id' => 'coupon_code',
			'autocomplete' => 'off',
			'value' => '',
			'label' => __('Discount coupon code', 'pn'),
			'req' => 0,
			'class' => 'js_changecalc js_coupon_code',
			'cd' => '1',
		);			
	
	}
	
	return $fields;
}

add_filter('get_calc_data_params', 'coupons_get_calc_data_params', 100, 3);
function coupons_get_calc_data_params($calc_data, $place, $bid = '') {

	$place = trim($place);
	if ('calculator' == $place) {
		if (isset($calc_data['cd'])) {
			$calc_data['coupon_code'] = is_coupon(is_isset($calc_data['cd'], 'coupon_code'));		
		}		
	} elseif ('recalc' == $place) {
		$calc_data['coupon_code'] = is_coupon(is_isset($bid, 'coupon'));
		$calc_data['coupon_code_bid_id'] = intval(is_isset($bid, 'id'));
	} elseif ('action' == $place) {
		$calc_data['coupon_code'] = is_coupon(is_param_post('coupon_code'));
	} else {
		$calc_data['coupon_code'] = '';
	}
	
	return $calc_data;
}

add_filter('get_calc_data', 'coupons_get_calc_data', 1, 2);
function coupons_get_calc_data($cdata, $calc_data) {
	global $wpdb;	
	
	$direction = $calc_data['direction'];
	$disabled_coupons = intval(is_isset($direction, 'disabled_coupons'));
	if (1 != $disabled_coupons) {
		$bid_id = intval(is_isset($calc_data, 'coupon_code_bid_id'));
		$coupon_code = is_coupon(is_isset($calc_data, 'coupon_code'));
		if ($coupon_code) {
			$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "coupons WHERE coupon_code = '$coupon_code' AND status = '1'");
			$goed = 0;
			if (isset($item->id)) {
				if (1 == $item->coupon_type) {
					$goed = 1;
				}
				if (0 == $item->coupon_type) {
					if (0 == $item->coupon_used or $item->coupon_used == $bid_id) {
						$goed = 1;
					}
				}
			}
			if ($goed) {
				$cdata['user_discount'] = is_sum($item->discount);
			} else {
				$cdata['coupon_code_error'] = 1;
			}			
		}
	}			
		
	return $cdata;
}

add_filter('log_exchange_changes', 'coupons_log_exchange_changes', 10, 3);
function coupons_log_exchange_changes($log, $cdata, $calc_data) {
	
	if (isset($cdata['coupon_code_error'])) {
		$log['error_fields']['coupon_code'] = __('coupon is invalid', 'pn');
	}
	
	return $log;
}

add_filter('array_data_create_bids', 'coupons_array_data_create_bids', 10, 2);
function coupons_array_data_create_bids($array, $direction) {
	
	$disabled_coupons = intval(is_isset($direction, 'disabled_coupons'));
	if (1 != $disabled_coupons) {
		$array['coupon'] = is_coupon(is_param_post('coupon_code'));
	}
	
	return $array;
}

add_filter('change_bid_status', 'coupons_change_bidstatus', 10);  
function coupons_change_bidstatus($data) { 
	global $wpdb, $premiumbox;

	$place = $data['place'];
	$set_status = $data['set_status'];
	$bid = $data['bid'];
	$who = $data['who'];
	$old_status = $data['old_status'];
	$direction = $data['direction'];

	$bid_id = $bid->id;
	$coupon = is_coupon(is_isset($bid, 'coupon'));
	$stop_action = intval(is_isset($data, 'stop'));
	if ($coupon and !$stop_action) {
		if ('auto' == $set_status) {
			$wpdb->query("UPDATE " . $wpdb->prefix . "coupons SET coupon_used = '$bid_id' WHERE coupon_code = '$coupon' AND status = '1'");
		}
		if ('realdelete' == $set_status) {
			$wpdb->query("UPDATE " . $wpdb->prefix . "coupons SET coupon_used = '0' WHERE coupon_code = '$coupon' AND status = '1'");
		}
	}
	
	return $data;
}