<?php
if (!defined('ABSPATH')) { exit(); } 

add_filter('account_list_pages', 'account_list_pages_userwallets', 0);
function account_list_pages_userwallets($account_list_pages) {
	
	$account_list_pages['userwallets'] = array(
		'type' => 'page',			
	);
	
	return $account_list_pages;
}

function userwallets_page_shortcode($atts, $content) {
	global $wpdb, $premiumbox;
	
	$temp = apply_filters('before_userwallets_page', '');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	if ($user_id) {
	
		$lists = array(
			'ps' => __('Payment system', 'pn'),
			'acc' => __('Account number', 'pn'),
			'action' => '',
		);
		$lists = apply_filters('lists_table_userwallets', $lists);
		$lists = (array)$lists;	
	
		$limit = apply_filters('limit_list_userwallets', 15);
		$count = $wpdb->get_var("SELECT COUNT(" . $wpdb->prefix . "user_wallets.id) FROM " . $wpdb->prefix . "user_wallets LEFT OUTER JOIN " . $wpdb->prefix . "currency ON(" . $wpdb->prefix . "user_wallets.currency_id = " . $wpdb->prefix . "currency.id) WHERE " . $wpdb->prefix . "user_wallets.user_id = '$user_id' AND " . $wpdb->prefix . "user_wallets.auto_status = '1'");
		$pagenavi = get_pagenavi_calc($limit, get_query_var('paged'), $count);
		$datas = $wpdb->get_results("SELECT *, " . $wpdb->prefix . "user_wallets.id AS user_wallet_id FROM " . $wpdb->prefix . "user_wallets LEFT OUTER JOIN " . $wpdb->prefix . "currency ON(" . $wpdb->prefix . "user_wallets.currency_id = " . $wpdb->prefix . "currency.id) WHERE " . $wpdb->prefix . "user_wallets.user_id = '$user_id' AND " . $wpdb->prefix . "user_wallets.auto_status = '1' ORDER BY " . $wpdb->prefix . "user_wallets.id DESC LIMIT " . $pagenavi['offset'] . "," . $pagenavi['limit']);	
		$pagenavi_html = get_pagenavi($pagenavi);
	
		$table_list = '<table>';
		$table_list .= '<thead><tr>';
			foreach($lists as $list_key => $list_val) {
				$table_list .= '<th class="th_' . $list_key . '">' . $list_val . '</th>';
			}
		$table_list .= '</tr></thead><tbody>';
			
		$s = 0;
        foreach ($datas as $item) { $s++;
			if (0 == $s%2) { $odd_even = 'even'; } else { $odd_even = 'odd'; }
			
			$table_list .= '<tr>';
			foreach ($lists as $key => $title) {
				$table_list .= '<td>';
					
				$one_line = '';
				if ('ps' == $key) {
					$one_line = get_currency_title($item);
				}	
				
				if ('acc' == $key) {
					$one_line = pn_strip_input($item->accountnum);
				}
				
				if ('action' == $key) {
					$one_line = '<a href="' . get_pn_action('delete_userwallets', 'get') . '&item_id=' . $item->user_wallet_id . '" class="delpay_link" title="' . __('Delete', 'pn') . '">' . __('Delete', 'pn') . '</a>';
				}
					
				$table_list .= apply_filters('body_list_userwallets', $one_line, $item, $key, $title);
				$table_list .= '</td>';	
			}
			$table_list .= '</tr>';
	    }	

		if (0 == $s) {
			$table_list .= '<tr><td colspan="' . count($lists) . '"><div class="no_items"><div class="no_items_ins">' . __('No items', 'pn') . '</div></div></td></tr>';
		}	

		$table_list .= '</tbody></table>';		
	
		$add_text = pn_strip_text(ctv_ml($premiumbox->get_option('usve', 'addacctext')));
		if (strlen($add_text) < 1) { $add_text = __('To add an account to the payment system, click the "add account" button.', 'pn'); }
	
		$array = array(
			'[pagenavi]' => $pagenavi_html,
			'[table_list]' => $table_list,
			'[submit]' => '<input type="submit" class="js_add_userwallet" data-currid="0" data-redir="' . $premiumbox->get_page('userwallets') . '" data-account="" name="" value="' . __('Add account', 'pn') . '" />',
			'[add_text]' => apply_filters('comment_text', $add_text),
		);	
		$shortcode_temp = '
			<div class="userwallets_form">
				<div class="userwallets_form_ins">
					<div class="userwallets_text">
						<div class="text">
							[add_text]
							<div class="clear"></div>
						</div>	
					</div>
					<div class="userwallets_addbutton">
						[submit]
					</div>
				</div>	
			</div>
			
			<div class="userwallets pntable_wrap">
				<div class="pntable_wrap_ins">
					<div class="pntable_wrap_title">
						<div class="pntable_wrap_title_ins">
							'. __('Your accounts', 'pn') .'
						</div>
					</div>	
					<div class="pntable">
						<div class="pntable_ins">
							[table_list]
						</div>	
					</div>
							
					[pagenavi]
				</div>
			</div>			
		';
		$shortcode_temp = apply_filters('userwallets_page_temp',$shortcode_temp);
		$temp .= replace_tags($array, $shortcode_temp);

	} else {
		$temp .= '<div class="resultfalse">' . __('Error! Page is available for authorized users only', 'pn') . '</div>';
	}
	
	$temp .= apply_filters('after_userwallets_page', '');
	
	return $temp;
}
add_shortcode('userwallets', 'userwallets_page_shortcode');

add_action('premium_siteaction_delete_userwallets', 'def_premium_siteaction_delete_userwallets');
function def_premium_siteaction_delete_userwallets() {
	global $wpdb, $premiumbox;	
	
	$premiumbox->up_mode('get');
	
	$return_page = $premiumbox->get_page('userwallets');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	if (!$user_id) {
		pn_display_mess(__('Error! You must authorize', 'pn'));		
	}	
	
	$id = intval(is_param_get('item_id'));
	$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE user_id = '$user_id' AND id = '$id' AND auto_status = '1'");
	if (isset($item->id)) {
		$res = apply_filters('item_userwallets_delete_before', pn_ind(), $item->id, $item);
		if ($res['ind']) {	
		
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "user_wallets WHERE id = '$id'");
			do_action('item_userwallets_delete', $item->id, $item, $result);
			
		} else {
			pn_display_mess(is_isset($res, 'error'));				
		}
	} 
	
	wp_redirect($return_page);
	exit;			
}