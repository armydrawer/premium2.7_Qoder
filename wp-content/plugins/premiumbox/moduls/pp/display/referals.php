<?php
if (!defined('ABSPATH')) { exit(); } 

function preferals_page_shortcode($atts, $content) {
	global $wpdb, $premiumbox;
	
	$temp = '';
	
	$temp .= apply_filters('before_preferals_page', '');
	
	$pages = $premiumbox->get_option('partners', 'pages');
	if (!is_array($pages)) { $pages = array(); }	
	if (in_array('preferals', $pages)) {
	
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		if ($user_id) {

			$lists = array(
				'referal_name' => __('User', 'pn'),
				'registered' => __('Registration date', 'pn'),
			);
			$lists = apply_filters('lists_table_preferals', $lists);
			$lists = (array)$lists;		
		
			$limit = apply_filters('limit_list_preferals', 15);
			$count = $wpdb->get_var("SELECT COUNT(ID) FROM " . $wpdb->prefix . "users WHERE ref_id = '$user_id'");
			$pagenavi = get_pagenavi_calc($limit, get_query_var('paged'), $count);
			
			$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "users WHERE ref_id = '$user_id' ORDER BY user_registered DESC LIMIT " . $pagenavi['offset'] . "," . $pagenavi['limit']);		
		
			$date_format = get_option('date_format');
			$time_format = get_option('time_format');
				
			$table_list = '<table>';
			$table_list .= '<thead><tr>';
			foreach ($lists as $list_key => $list_val) {
				$table_list .= '<th class="th_' . $list_key . '">' . $list_val . '</th>';
			}
			$table_list .= '</tr></thead><tbody>';	
				
			$s = 0;
			foreach ($datas as $item) { $s++;
				if ($s%2 == 0) { $odd_even = 'even'; } else { $odd_even = 'odd'; }
					
				$table_list .= '<tr>';
				foreach ($lists as $key => $title) {
					$table_list .= '<td>';
					
					$one_line = '';
					
					if ('referal_name' == $key) {
						$one_line = is_user($item->user_login);
					}
					
					if ('registered' == $key) {
						$one_line = get_pn_time($item->user_registered, "{$date_format}, {$time_format}");
					}			
						
					$table_list .= apply_filters('body_list_preferals', $one_line, $item, $key, $title, $date_format, $time_format);
					$table_list .= '</td>';	
				}
			 	$table_list .= '</tr>';
			}
				
			if (0 == $s) {
				$table_list .= '<tr><td colspan="' . count($lists) . '"><div class="no_items"><div class="no_items_ins">' . __('No items', 'pn') . '</div></div></td></tr>';
			}	

			$table_list .= '</tbody></table>';			
			
			$temp_html = '
			<div class="preferalstable pntable_wrap">
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
			
			$temp_html = apply_filters('div_list_preferals', $temp_html);
			$temp .= replace_tags($array, $temp_html);
		
		} else {
			$temp .= '<div class="resultfalse">' . __('Error! Page is available for authorized users only', 'pn') . '</div>';
		}
	
	} else {
		$temp .= '<div class="resultfalse">' . __('Error! Page is unavailable', 'pn') . '</div>';
	}
	
	$after = apply_filters('after_preferals_page', '');
	$temp .= $after;

	return $temp;
}
add_shortcode('preferals_page', 'preferals_page_shortcode');