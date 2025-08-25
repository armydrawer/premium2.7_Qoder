<?php
if (!defined('ABSPATH')) { exit(); }
 
add_filter('list_tabs_direction', 'list_tabs_direction_cacc');
function list_tabs_direction_cacc($list_tabs) {
	
	$list_tabs['cacc'] = __('Currency accounts', 'pn');
	
	return $list_tabs;
}
  
add_action('tab_direction_cacc', 'direction_tab_cacc', 10, 2);
function direction_tab_cacc($data, $data_id) {	
	global $wpdb;

	$lists = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "curr_accounts ORDER BY title ASC");

 	$data_id = intval(is_isset($data, 'id'));
	$arr = get_direction_meta($data_id, 'curraccs');
	$method = intval(is_isset($arr, 'method'));
	$in = pn_json_decode(is_isset($arr, 'in'));
	if (!is_array($in)) { $in = array(); }
	?>
		<div class="add_tabs_line">
			<div class="add_tabs_title"><?php _e('Currency accounts', 'pn'); ?></div>
			<div class="add_tabs_submit">
				<input type="submit" name="" class="button" value="<?php _e('Save', 'pn'); ?>" />
			</div>
		</div>
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Method', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<select name="curraccs_method" autocomplete="off"> 
						<option value="0" <?php selected($method, 0); ?>><?php _e('No', 'pn'); ?></option>
						<option value="1" <?php selected($method, 1); ?>><?php _e('By chance', 'pn'); ?></option>
						<option value="2" <?php selected($method, 2); ?>><?php _e('Once in a direction a day', 'pn'); ?></option>
						<option value="3" <?php selected($method, 3); ?>><?php _e('Once per direction per month', 'pn'); ?></option>
					</select>
				</div>			
			</div>
			<div class="add_tabs_single">			
			</div>
		</div>	
		<div class="add_tabs_line">
			<div class="add_tabs_single long">
				<div class="add_tabs_sublabel"><span><?php _e('Accounts', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();					
					foreach ($lists as $list) {
						$checked = 0;
						if (in_array($list->id, $in)) {
							$checked = 1;
						}	
						$scroll_lists[] = array(
							'title' => pn_strip_input($list->title),
							'checked' => $checked,
							'value' => $list->id,
						);
					}
					echo get_check_list($scroll_lists, 'curraccs_in[]', '', '400', 1);
					?>				
						<div class="premium_clear"></div>
				</div>
			</div>
		</div>	
	<?php 
}  
 
add_action('item_direction_edit', 'item_direction_edit_cacc', 10, 2);
add_action('item_direction_add', 'item_direction_edit_cacc', 10, 2);
function item_direction_edit_cacc($data_id, $array) {
	
	if ($data_id) {

		$arr = array();
		$arr['method'] = intval(is_param_post('curraccs_method'));
		$in = array();
		$curraccs_in = is_param_post('curraccs_in');
		if (is_array($curraccs_in)) {
			foreach ($curraccs_in as $curra) {
				$curra = intval($curra);
				if ($curra) {
					$in[$curra] = $curra;
				}
			}
		}
		$arr['in'] = pn_json_encode($in);
		update_direction_meta($data_id, 'curraccs', $arr);		

	}
	
}

add_filter('after_set_merchant', 'cacc_after_set_merchant', 10, 2);
function cacc_after_set_merchant ($item, $direction) {
	
	if (isset($item->id)) {
		$to_account = pn_strip_input($item->to_account);
		if (strlen($to_account) < 1) {
			$data_id = intval(is_isset($direction, 'id'));
			$arr = get_direction_meta($data_id, 'curraccs');
			$method = intval(is_isset($arr, 'method'));
			$in = pn_json_decode(is_isset($arr, 'in'));
			if (!is_array($in)) { $in = array(); }
			if ($method > 0) {
				$sum_to_pay = apply_filters('sum_to_pay', is_sum($item->sum1dc), $item->m_in, $direction, $item);
				$account = get_curr_account($item, $sum_to_pay, $direction, $method, $in);
				if (strlen($account) < 1) {
					$account = '';
				} 
				$item = update_bid_tb($item->id, 'to_account', $account, $item);
			}
		}
	}	
	
	return $item;
}
  
function get_curr_account($bids_data, $sum_to_pay, $direction, $method, $in) {
	global $wpdb;	
	
	$bid_sum = is_sum(is_isset($bids_data, 'sum1dc'));
	$direction_id = $direction->id;
	if (count($in) > 0) {
		shuffle($in);
		$now_id = intval($in[0]);
		$day_date = current_time('Y-m-d 00:00:00');
		$month_date = current_time('Y-m-01 00:00:00');
	
		$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "curr_accounts WHERE id = '$now_id'"); 
		if (isset($item->id)) {
			$error = 0;
			$item_id = $item->id;
			$accountnum = $item->accountnum;
			$accountnum_dehash = premium_decrypt($item->accountnum_hash, EXT_SALT);
			if ($accountnum != $accountnum_dehash) {
				$error = 1;
			}	
			$status = intval($item->status);
			if (1 != $status and 1 != $error) {
				$error = 1;
			}
			$accunique = intval($item->accunique);
			if ($accunique and 1 != $error) {
				$now_count = get_active_vaccount($accountnum, $bids_data->id);
				if ($now_count > 0) {
					$error = 1;
				}
			}			
			$max_day = is_sum($item->inday);
			if ($max_day > 0 and 1 != $error) {
				$now = get_vaccount_sum($accountnum, 'in', $day_date);
				$now = $now + $bid_sum;
				$now = is_sum($now);
				if ($now > $max_day) {
					$error = 1;
				}
			}
			$max_month = is_sum($item->inmonth);
			if ($max_month > 0 and 1 != $error) {
				$now = get_vaccount_sum($accountnum, 'in', $month_date);
				$now = $now + $bid_sum;
				$now = is_sum($now);
				if ($now > $max_month) {
					$error = 1;
				}
			}	
			if (2 == $method and 1 != $error) {
				$now = get_vaccount_count($accountnum, 'in', $day_date, $direction_id);
				if ($now > 0) {
					$error = 1;					
				}
			}
			if (3 == $method and 1 != $error) {
				$now = get_vaccount_count($accountnum, 'in', $month_date, $direction_id);
				if ($now > 0) {
					$error = 1;					
				}
			}			
			
			if ($error > 0) {
				unset($in[0]);
				return get_curr_account($bids_data, $sum_to_pay, $direction, $method, $in);				
			}
			
			return $accountnum;
		} 		
	}
	
	return '';
}