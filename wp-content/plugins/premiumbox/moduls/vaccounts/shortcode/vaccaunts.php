<?php
if (!defined('ABSPATH')) { exit(); }
 
add_filter('direction_instruction_tags', 'caccount_directions_tags', 10, 2); 
function caccount_directions_tags($tags, $key) {
	
	$in_page = array('description_txt', 'timeline_txt', 'window_txt', 'frozen_txt', 'status_auto');
	if (!in_array($key, $in_page)) {
		
		$tags['num_schet'] = array(
			'title' => __('Currency account', 'pn'),
			'start' => '[num_schet currency_id="" display="" hide="0" copy="1"]',
		);
		
	}
	
	return $tags;
}
   
add_shortcode('num_schet', 'def_num_schet');
function def_num_schet($atts, $content) {
	global $wpdb, $bids_data;

	if (isset($bids_data->id)) {
		
		$n_atts = array();
		if (is_array($atts)) {
			foreach ($atts as $k => $v) {
				$n_atts[$k] = str_replace(array('&quot;', '&#039;'), '', $v);
			}
		}		

		$hide = intval(is_isset($n_atts, 'hide'));
		$currency_id = intval(is_isset($n_atts, 'currency_id'));
		if (!$currency_id) { $currency_id = $bids_data->currency_id_give; } 
		
		if (isset($n_atts['vid'])) {
			$display = intval(is_isset($n_atts, 'vid'));
		} else {
			$display = intval(is_isset($n_atts, 'display'));
		}
		
		if (isset($n_atts['copy'])) {
			$copy = intval(is_isset($n_atts, 'copy'));
		} else {
			$copy = 1;
		}
		
		$to_account = get_now_vaccount($currency_id, $display, array());
		if (!$hide) {
			if ($to_account) {
				$trim_words = array();
				$arr_words = explode(' ', $to_account);
				foreach ($arr_words as $arr_word) {
					$arr_word = trim($arr_word);
					if (strlen($arr_word) > 0) {
						$trim_words[] = $arr_word;
					}
				}
				if (0 == $copy) {
					return $to_account;
				} elseif (1 == $copy) {
					return '<span class="js_copy pn_copy num_schet" data-clipboard-text="' . esc_attr($to_account) . '">' . $to_account . '</span>';
				} else {
					$wd = '';
					foreach ($trim_words as $tr) {
						$wd .= '<span class="js_copy pn_copy num_schet" data-clipboard-text="' . esc_attr($tr) . '">' . $tr . '</span> ';
					}
					return $wd;
				}
			} else {
				return apply_filters('not_vaccaunt_now', '<span class="not_vaccaunt_now">' . __('Please contact us to provide your account number', 'pn') . '</span>');
			}
		}
		
	} else {
		return '***error num_schet***';
	}
}

function get_now_vaccount($currency_id, $display, $not) {
	global $wpdb, $bids_data;
	
	if (!isset($bids_data->id)) { return ''; }
	
	$bid_id = intval(is_isset($bids_data, 'id'));
	$to_account = pn_maxf_mb(pn_strip_input(is_isset($bids_data, 'to_account')), 500);
	$bid_sum = is_sum(is_isset($bids_data, 'sum1dc'));
	
	if (!is_array($not)) { $not = array(); }
	
	$where = '';
	if (count($not) > 0) {
		$notted = join(',', $not);
		$where = " AND id NOT IN($notted)";
	}
	
	$time = current_time('timestamp');
	$date1 = date('Y-m-d 00:00:00', $time);
	$date2 = date('Y-m-01 00:00:00', $time);
	
	if (0 == $display or 1 == $display) { /* показывать случайно */
	
		$val = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_accounts WHERE currency_id = '$currency_id' AND status = '1' $where ORDER BY RAND()"); 
		if (isset($val->id)) {
			$val_id = $val->id;
			$accountnum = pn_strip_input($val->accountnum);
			$max_day = pn_strip_input($val->inday);
			$max_month = pn_strip_input($val->inmonth);
			$accunique = intval($val->accunique);
			if ($accunique) {
				$now_count = get_active_vaccount($accountnum, $bid_id);
				if ($now_count > 0) {
					$not[] = "'{$val_id}'";
					return get_now_vaccount($currency_id, $display, $not);
				}
			}
			if ($max_day > 0) {
				$now_day = get_vaccount_sum($accountnum, 'in', $date1);
				$now_day = $now_day + $bid_sum;
				if ($now_day > $max_day) {
					$not[] = "'{$val_id}'";
					return get_now_vaccount($currency_id, $display, $not);
				}
			}
			if ($max_month > 0) {
				$now_month = get_vaccount_sum($accountnum, 'in', $date2);
				$now_month = $now_month + $bid_sum;
				if ($now_month > $max_month) {
					$not[] = "'{$val_id}'";
					return get_now_vaccount($currency_id, $display, $not);
				}
			}			
			
			$bids_data = update_bid_tb($bid_id, 'to_account', $accountnum, $bids_data);
			
			return $accountnum;
		} 	
		
	} elseif (2 == $display) { /* отображать счет постоянно в рамках одной заявки */
	
		if ($to_account) {
			
			$hashdata = @unserialize($bids_data->hashdata);
			if (!is_array($hashdata)) { $hashdata = array(); }
			
			if (!is_pn_crypt(is_isset($hashdata, 'to_account'), $to_account)) {
				return '***not_check_error***';
			} else {
				return $to_account;
			}
			
		} else {
			
			$val = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_accounts WHERE currency_id = '$currency_id' AND status = '1' $where ORDER BY RAND()");
			if (isset($val->id)) {
				$val_id = $val->id;
				$accountnum = pn_strip_input($val->accountnum);
				$max_day = pn_strip_input($val->inday);
				$max_month = pn_strip_input($val->inmonth);
				$accunique = intval($val->accunique);
				if ($accunique) {
					$now_count = get_active_vaccount($accountnum, $bid_id);
					if ($now_count > 0) {
						$not[] = "'{$val_id}'";
						return get_now_vaccount($currency_id, $display, $not);
					}
				}				
				if($max_day > 0){
					$now_day = get_vaccount_sum($accountnum, 'in', $date1);
					$now_day = $now_day + $bid_sum;
					if($now_day > $max_day){
						$not[] = "'{$val_id}'";
						
						return get_now_vaccount($currency_id, $display, $not);
					}
				}
				if($max_month > 0){
					$now_month = get_vaccount_sum($accountnum, 'in', $date2);
					$now_month = $now_month + $bid_sum;
					if($now_month > $max_month){
						$not[] = "'{$val_id}'";
						
						return get_now_vaccount($currency_id, $display, $not);
					}
				}			
				
				$array = array();
				$array['count_visit'] = $val->count_visit+1;
				$wpdb->update($wpdb->prefix . "currency_accounts", $array, array('id' => $val->id));	
				
				$bids_data = update_bid_tb($bid_id, 'to_account', $accountnum, $bids_data);
				
				return $accountnum;
			} 
			
		}
	} 
	
	return '';
}