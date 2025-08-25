<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [ru_RU:]Проверка на количество обменов[:ru_RU][en_US]Checking for the number of exchanges[:en_US]
description: [ru_RU:]Проверка на количество обменов[:ru_RU][en_US]Checking for the number of exchanges[:en_US]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

add_action('tab_direction_tab7', 'chexch_tab_direction_tab7', 30, 2);
function chexch_tab_direction_tab7($data, $data_id) {
	
	$count = get_direction_meta($data_id, 'checkexch_count');
	$count = intval($count);
	?>		
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('the number of exchanges that the user should have', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="checkexch_count" style="width: 200px;" value="<?php echo $count; ?>" />
			</div>
		</div>	
	</div>	
	<?php 		
} 
 
add_action('item_direction_edit', 'item_direction_edit_chexch', 10, 2);
add_action('item_direction_add', 'item_direction_edit_chexch', 10, 2);
function item_direction_edit_chexch($data_id, $array) {
	
	$checkexch_count = intval(is_param_post('checkexch_count'));
	if ($checkexch_count < 0) { $checkexch_count = 0; }
	update_direction_meta($data_id, 'checkexch_count', $checkexch_count);
	
}	

add_filter('error_bids', 'error_bids_chexch', 30, 2);  
function error_bids_chexch($error_bids, $direction) {
	global $wpdb;

	$direction_id = $direction->id;
	$count = intval(is_isset($direction, 'checkexch_count'));		
		
	$error = 0;
	if ($count > 0) {	
		$n_item = is_isset($error_bids['bid'], 'user_email');
		if ($n_item) {
			$now_cou = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE user_email = '$n_item' AND status = 'success'");
			if ($now_cou < $count) {
				$error = 1;			
			}		
		}
	}	
	
	if ($count > 0 and 1 != $error) {	
		$n_item = is_isset($error_bids['bid'], 'account_give');
		if ($n_item) {
			$now_cou = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE account_give = '$n_item' AND status = 'success'");
			if ($now_cou < $count) {
				$error = 1;				
			}
		}
	}	

	if ($error) {
		$error_bids['error_text'][] = __('Error! For your exchange denied', 'pn');			
	}		
	
	return $error_bids;
}