<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('account_list_pages', 'iac_account_list_pages');
function iac_account_list_pages($list) {	
	
	$new_list = array();
	$new_list['iac'] = array(
		'title' => '',
		'url' => '',
		'type' => 'page',
	);
	$list = pn_array_insert($list, 'userxch', $new_list);
	
	return $list;
}

function iac_page_shortcode($atts, $content) {
	global $wpdb;

	$temp = '';
	
    $temp .= apply_filters('iac_domacc_page', '');
			
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);		
			
	if ($user_id) {
			
		$currency_codes = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency_codes WHERE auto_status = '1' AND iac_enable = '1' ORDER BY currency_code_title ASC");
		
		$lists = array(
			'wallet' => __('Wallet', 'pn'),
			'amount' => __('Amount', 'pn'),
		);
		$lists = apply_filters('lists_table_iac', $lists);
		$lists = (array)$lists;	

		$table_list = '<table>';
		$table_list .= '<thead><tr>';
			foreach ($lists as $list_key => $list_val) {
				$table_list .= '<th class="th_' . $list_key . '">' . $list_val . '</th>';
			}
		$table_list .= '</tr></thead><tbody>';
			
		$s = 0;
        foreach ($currency_codes as $item) { $s++;
			if (0 == $s%2) { $odd_even = 'even'; } else { $odd_even = 'odd'; }
			
			$currency_code = is_site_value($item->currency_code_title);
			
			$table_list .= '<tr>';
			foreach ($lists as $key => $title) {
				$table_list .= '<td>';
					
				$wallet = strtoupper($currency_code . '_' . $user_id);	
					
				$one_line = '';
				if ('wallet' == $key) {
					$one_line = '<span class="js_copy pn_copy" data-clipboard-text="' . $wallet . '">' . $wallet . '</span>';
				}					
				
				if ('amount' == $key) {
					$one_line = get_user_iac($user_id, $item->id) . ' ' . $currency_code;
				}	
					
				$table_list .= apply_filters('body_list_iac', $one_line, $item, $key, $title);
				$table_list .= '</td>';	
			}
			$table_list .= '</tr>';
	    }	

		if (0 == $s) {
			$table_list .= '<tr><td colspan="' . count($lists) . '"><div class="no_items"><div class="no_items_ins">' . __('No items', 'pn') . '</div></div></td></tr>';
		}	

		$table_list .= '</tbody></table>';			
		
		$array = array(
			'[table_list]' => $table_list,
		);	
		
		$temp_form = '
		<div class="iactable pntable_wrap">
			<div class="pntable_wrap_ins">
				<div class="pntable_wrap_title">
					<div class="pntable_wrap_title_ins">
						'. __('Internal account', 'pn') .'
					</div>
				</div>	
				<div class="pntable">
					<div class="pntable_ins">
						[table_list]
					</div>	
				</div>
			</div>
		</div>						
		';
		
		$temp_form = apply_filters('iac_form_temp', $temp_form);
		$temp .= replace_tags($array, $temp_form);		

	} else {
		$temp .= '<div class="resultfalse">' . __('Error! You must authorize', 'pn') . '</div>';
	}
	
    $temp .= apply_filters('after_iac_page', '');	
	
	return $temp;
}
add_shortcode('iac_page', 'iac_page_shortcode');