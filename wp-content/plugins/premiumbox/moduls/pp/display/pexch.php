<?php
if (!defined('ABSPATH')) { exit(); }

function pexch_page_shortcode($atts, $content) {
	global $wpdb, $premiumbox;
	
	$temp = '';
	
	$temp .= apply_filters('before_pexch_page', '');
	
	$pages = $premiumbox->get_option('partners', 'pages');
	if (!is_array($pages)) { $pages = array(); }	
	if (in_array('pexch', $pages)) {	
	
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		if ($user_id) {

			$lists = array(
				'id' => __('Exchange ID', 'pn'),
				'date' => __('Date', 'pn'),
				'user' => __('User', 'pn'),
				'partner_reward' => __('Reward', 'pn'),
			);
			$lists = apply_filters('lists_table_pexch', $lists);
			$lists = (array)$lists;		
		
			$limit = apply_filters('limit_list_pexch', 15);
			$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE ref_id = '$user_id' AND pcalc = '1' AND status = 'success'");
			$pagenavi = get_pagenavi_calc($limit, get_query_var('paged'), $count);
			
			$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE ref_id = '$user_id' AND pcalc = '1' AND status = 'success' ORDER BY create_date DESC LIMIT ". $pagenavi['offset'] .",".$pagenavi['limit']);		

			$date_format = get_option('date_format');
			$time_format = get_option('time_format');				
				
			$v = get_currency_data();	
				
			$table_list = '<table>';
			$table_list .= '<thead><tr>';
			foreach ($lists as $list_key => $list_val) {
				$table_list .= '<th class="th_' . $list_key . '">' . $list_val . '</th>';
			}
			$table_list .= '</tr></thead><tbody>';	
				
			$s = 0;
			foreach ($datas as $item) { $s++;
				if ($s%2 == 0) { $odd_even = 'even'; } else { $odd_even = 'odd'; }
					
				$currency_id_give = $item->currency_id_give;
				$currency_id_get = $item->currency_id_get;
						
				if (isset($v[$currency_id_give]) and isset($v[$currency_id_get])) {
					$vd1 = $v[$currency_id_give];
					$vd2 = $v[$currency_id_get];
					$decimal1 = $vd1->currency_decimal;
					$decimal2 = $vd2->currency_decimal;	
				} else {
					$decimal1 = 12;
					$decimal2 = 12;
				}
					
				$table_list .= '<tr>';
				foreach ($lists as $key => $title) {
					$table_list .= '<td>';
					
					$one_line = '';
					
					if ('id' == $key) {
						$one_line = $item->id;
					}		
					
					if ('date' == $key) {
						$one_line = get_pn_time($item->create_date, "{$date_format}, {$time_format}");
					}
					
					if ('user' == $key) {
						$uid = $item->user_id;
						if ($uid > 0) {
							$one_line = is_user($item->user_login); 
						} else {
							$one_line = __('Guest', 'pn');
						}
					}
					
					if ('bids_data' == $key) {
						$status = get_bid_status($item->status);
						$link = get_bids_url($item->hashed);
						$one_line = '<span class="uo_curs1"><span class="uosum">' . is_sum($item->course_give) . '</span> ' . is_site_value($item->currency_code_give) . '</span> <span class="uo_curs2"><span class="uosum">' . is_sum($item->course_get) . '</span> ' . is_site_value($item->currency_code_get) . '</span>';
					}
					
					if ('partner_reward' == $key) {
						$one_line = is_sum($item->partner_sum) . ' ' . cur_type();
					}

					$table_list .= apply_filters('body_list_pexch', $one_line, $item, $key, $title, $date_format, $time_format, $v);
					$table_list .= '</td>';	
				}
			 	$table_list .= '</tr>';
			}
						
			if (0 == $s) {
				$table_list .= '<tr><td colspan="' . count($lists) . '"><div class="no_items"><div class="no_items_ins">' . __('No item', 'pn') . '</div></div></td></tr>';
			}	

			$table_list .= '</tbody></table>';
			
			$temp_html = '
			<div class="userxchtable pntable_wrap">
				<div class="pntable_wrap_ins">			
					<div class="pntable">
						<div class="pntable_ins">
							[table_list]
						</div>	
					</div>
			
					[pagenavi]
				</div>
			</div>
			';
			
			$array = array(
				'[table_list]' => $table_list,
				'[pagenavi]' => get_pagenavi($pagenavi),
			);
			
			$temp_html = apply_filters('div_list_pexch', $temp_html);
			$temp .= replace_tags($array, $temp_html);			
		
		} else {
			$temp .= '<div class="resultfalse">' . __('Error! Page is available for authorized users only', 'pn') . '</div>';
		}
	} else {
		$temp .= '<div class="resultfalse">' . __('Error! Page is unavailable', 'pn') . '</div>';
	}	
	
	$after = apply_filters('after_pexch_page', '');
	$temp .= $after;

	return $temp;
}
add_shortcode('pexch_page', 'pexch_page_shortcode');