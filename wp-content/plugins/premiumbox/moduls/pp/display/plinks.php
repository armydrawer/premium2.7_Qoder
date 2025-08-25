<?php
if (!defined('ABSPATH')) { exit(); }

function plinks_page_shortcode($atts, $content) {
	global $wpdb, $premiumbox;
	
	$temp = '';
	
	$temp .= apply_filters('before_plinks_page', '');
	
	$pages = $premiumbox->get_option('partners', 'pages');
	if (!is_array($pages)) { $pages = array(); }	
	if (in_array('plinks', $pages)) {	
	
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		if ($user_id) {
		
			$lists = array(
				'date' => __('Date', 'pn'),
				'browser'    => __('Browser', 'pn'),
				'ip'  => __('IP', 'pn'),
				'ref' => __('Referral website', 'pn'),
				'qstring' => __('Query string', 'pn'),
			);
			
			$lists = apply_filters('lists_table_plinks', $lists);
			$lists = (array)$lists;
		
			$limit = apply_filters('limit_list_plinks', 15);
			$count = $wpdb->get_var("SELECT COUNT(id) FROM ". $wpdb->prefix ."plinks WHERE user_id = '$user_id'");
			$pagenavi = get_pagenavi_calc($limit, get_query_var('paged'), $count);
			
			$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "plinks WHERE user_id = '$user_id' ORDER BY pdate DESC LIMIT ". $pagenavi['offset'] .",".$pagenavi['limit']);		
		
			$date_format = get_option('date_format');
			$time_format = get_option('time_format');
			
			$table_list = '<table>';
			$table_list .= '<thead><tr>';
			foreach ($lists as $list_key => $list_val) {
				$table_list .= '<th class="th_'. $list_key .'">'. $list_val .'</th>';
			}
			$table_list .= '</tr></thead><tbody>';
			
			$s = 0;
			foreach ($datas as $item) { $s++;
				if ($s%2 == 0) { $odd_even = 'even'; } else { $odd_even = 'odd'; }
					
				$table_list .= '<tr>';
				foreach ($lists as $key => $title) {
					$table_list .= '<td>';
					
					$one_line = '';
					if ('date' == $key) {
						$one_line = get_pn_time($item->pdate, "{$date_format}, {$time_format}");
					}
					if ('browser' == $key) {
						$one_line = get_browser_name($item->pbrowser);
					}
					if ('ip' == $key) {
						$one_line = pn_strip_input($item->pip);
					}					
					if ('qstring' == $key) {
						if ($item->query_string) {
							$one_line = pn_strip_input($item->query_string);
						} else {
							$one_line = __('Unknown', 'pn');
						}
					}					
					if ('ref' == $key) {
						if ($item->prefer) {
							$one_line = pn_strip_input($item->prefer);
						} else {
							$one_line = __('Unknown', 'pn');
						}
					}
							
					$table_list .= apply_filters('body_list_plinks', $one_line, $item, $key, $title, $date_format, $time_format);
					$table_list .= '</td>';	
				}
			 	$table_list .= '</tr>';
			}
			
			if (0 == $s) {
				$table_list .= '<tr><td colspan="'. count($lists) .'"><div class="no_items"><div class="no_items_ins">' . __('No item', 'pn') . '</div></div></td></tr>';
			}	

			$table_list .= '</tbody></table>';
			
			$temp_html = '
			<div class="plinkstable pntable_wrap">
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
			
			$temp_html = apply_filters('div_list_plinks',$temp_html);
			$temp .= replace_tags($array, $temp_html);
		
		} else {
			$temp .= '<div class="resultfalse">' . __('Error! Page is available for authorized users only', 'pn') . '</div>';
		}
	
	} else {
		$temp .= '<div class="resultfalse">' . __('Error! Page is unavailable', 'pn') . '</div>';
	}	
	
	$after = apply_filters('after_plinks_page', '');
	$temp .= $after;

	return $temp;
}
add_shortcode('plinks_page', 'plinks_page_shortcode');