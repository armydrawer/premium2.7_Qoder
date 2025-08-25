<?php
if (!defined('ABSPATH')) { exit(); }

function payouts_page_shortcode($atts, $content) {
	global $wpdb, $premiumbox;
	
	$temp = '';
	
	$temp .= apply_filters('before_payouts_page', '');
	
	$pages = $premiumbox->get_option('partners', 'pages');
	if (!is_array($pages)) { $pages = array(); }	
	if (in_array('payouts', $pages)) {	
	
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		if ($user_id) {
			
			$minpay = is_sum($premiumbox->get_option('partners', 'minpay'), 2);
			$balans = get_partner_money($user_id, array('0', '1'));
			if ($balans >= $minpay) {
				$dbalans = $balans;
				$dis = '';
			} else {
				$dbalans = 0;
				$dis = 'disabled="disabled"';
			}		
			
			$cur_type = cur_type();
			
			$ptext = pn_strip_text(ctv_ml($premiumbox->get_option('partners', 'payouttext')));
			if (strlen($ptext) < 1) { $ptext = sprintf(__('Minimum withdrawal amount is <span class="red">%1$s %2$s</span>. All payments to be done right after admin verifies your account. Actually it takes less than 24 hours after submitting withdrawal request.', 'pn'), '[minpay]', '[currency]'); }
			
			$ptext = str_replace('[minpay]', $minpay, $ptext);
			$ptext = str_replace('[currency]', $cur_type, $ptext);
			$ptext = str_replace('[dbalans]', $dbalans, $ptext);
			
			$paytext = '
			<div class="paytext">
				<div class="paytext_ins">
					'. apply_filters('comment_text', $ptext) .'
				</div>
			</div>
			';
			
			$currency_html = '
			<select name="currency_id" id="pay_currency_id" autocomplete="off">
				<option value="0">--' . __('No item', 'pn') . '--</option>';		
				$currencies = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' AND currency_status = '1' AND p_payout = '1' ORDER BY reserv_order ASC");	
				foreach ($currencies as $item) { 
					$reserv = $item->currency_reserv; 
					$payout_com = is_sum($item->payout_com);
					$paysum = is_sum(convert_sum($dbalans, $cur_type, $item->currency_code_title));
					if ($reserv >= $paysum) {	
						$com_text = '';
						if ($payout_com > 0) {
							$com_text = ' (' . __('Fee of payment system for payout of funds to partner', 'pn') . ' - ' . $payout_com . '%)';
						}
					
						$currency_html .= '
						<option value="' . $item->id . '">'. sum_after_comis($paysum, $payout_com) . ' ' . get_currency_title($item) . '' . $com_text . '</option>
						';			
					}
				}							
			$currency_html .= '
			</select>';

			$lists = array(
				'pay_date' => __('Date', 'pn'),
				'pay_account' => __('Wallet', 'pn'),
				'pay_sum' => __('Amount', 'pn'),
				'pay_sum_or' => __('Amount', 'pn') . '(' . $cur_type . ')',
				'pay_status' => __('Status', 'pn'),
				'del_status' => '',
			);
			$lists = apply_filters('lists_table_payouts', $lists);
			$lists = (array)$lists;			
			
			$limit = apply_filters('limit_list_payouts', 15);
			$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "user_payouts WHERE auto_status = '1' AND user_id = '$user_id'");
			$pagenavi = get_pagenavi_calc($limit, get_query_var('paged'), $count);
			
			$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "user_payouts WHERE auto_status = '1' AND user_id = '$user_id' ORDER BY pay_date DESC LIMIT " . $pagenavi['offset'] . "," . $pagenavi['limit']);		

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
			foreach ($datas as $item) {  $s++;
				if ($s%2 == 0) { $odd_even = 'even'; } else { $odd_even = 'odd'; }
				
				$currency_id = $item->currency_id;
						
				if (isset($v[$currency_id])) {
					$vd = $v[$currency_id];
					$decimal = $vd->currency_decimal;	
				} else {
					$decimal = 12;
				}				
				
				$table_list .= '<tr>';
				foreach ($lists as $key => $title) {
					$table_list .= '<td>';
					
					$one_line = '';
					
					if ('pay_date' == $key) {
						$one_line = get_pn_time($item->pay_date, "{$date_format}, {$time_format}");
					}
					
					if ('pay_account' == $key) {
						$valut_title = pn_strip_input(ctv_ml($item->psys_title));
						$one_line = '<span class="ptvaluts">' . $valut_title . '</span><br />'. pn_strip_input($item->pay_account);
					}
					
					if ('pay_sum' == $key) {
						$one_line = is_out_sum($item->pay_sum, $decimal, 'all') .' '. is_site_value($item->currency_code_title);
					}
					
					if ('pay_sum_or' == $key) {
						$one_line = is_out_sum($item->pay_sum_or, 12, 'all') .' '. $cur_type;
					}
					
					$status = $item->status;
					if ('pay_status' == $key) {
						$status_title = get_payuot_status($status);
						$pst = $status + 1;
						$one_line = '<span class="paystatus pst' . $pst . '">' . $status_title . '</span>'; 
					}
					
					if ('del_status' == $key) {
						$link = '-';
						if (0 == $status) {
							$link = '<a href="'. get_pn_action('delete_payoutlink', 'get') . '&item_id=' . $item->id . '" class="delpay_link" title="' . __('Cancel payment', 'pn') . '">' . __('Cancel payment', 'pn') . '</a>';
						}	
						$one_line = $link; 
					}	
					
					$table_list .= apply_filters('body_list_payouts', $one_line, $item, $key, $title, $date_format, $time_format, $v);
					$table_list .= '</td>';	
				}
			 	$table_list .= '</tr>';
			}
			
			if (0 == $s) {
				$table_list .= '<tr><td colspan="'. count($lists) .'"><div class="no_items"><div class="no_items_ins">' . __('No items', 'pn') . '</div></div></td></tr>';
			}	

			$table_list .= '</tbody></table>';
			
			$temp_html = '
			[paytext]
			[form]
				<div class="paydiv">
					<div class="paydiv_ins">					
						<div class="pay_left_col">
							'. __('Wallet', 'pn') .'
						</div>
						<div class="pay_center_col">
							<div class="pay_select">
								[currency]
							</div>
							<div class="pay_input">							
								[account_input]
							</div>
						</div>
						<div class="pay_right_col">
							[submit]
						</div>
							<div class="clear"></div>							
					</div>
				</div>
				[result]
			[/form]
				
			<div class="paytable pntable_wrap">
				<div class="pntable_wrap_ins">
					<div class="pntable_wrap_title">
						<div class="pntable_wrap_title_ins">
							'. __('Orders', 'pn') .'
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
			
			$array = array(
				'[form]' => '<form method="post" class="ajax_post_form" action="' . get_pn_action('payoutform') . '">',
				'[/form]' => '</form>',
				'[currency]' => $currency_html,
				'[result]' => '<div class="resultgo"></div>',
				'[submit]' => '<input type="submit" formtarget="_top" ' . $dis . ' value="' . __('Make a request', 'pn') . '" />',
				'[account_input]' => '<input type="text" name="account" value="" />',
				'[paytext]' => $paytext,
				'[pagenavi]' => get_pagenavi($pagenavi),
				'[table_list]' => $table_list,
			);
			$array = apply_filters('array_list_payouts', $array);
			
			$temp_html = apply_filters('div_list_payouts', $temp_html);
			$temp .= replace_tags($array, $temp_html);			
		
		} else {
			$temp .= '<div class="resultfalse">' . __('Error! Page is available for authorized users only', 'pn') . '</div>';
		}
	} else {
		$temp .= '<div class="resultfalse">' . __('Error! Page is unavailable', 'pn') . '</div>';
	}		
	
	$after = apply_filters('after_payouts_page', '');
	$temp .= $after;

	return $temp;
}
add_shortcode('payouts_page', 'payouts_page_shortcode');

add_action('premium_siteaction_delete_payoutlink', 'def_premium_siteaction_delete_payoutlink');
function def_premium_siteaction_delete_payoutlink() {
	global $wpdb, $premiumbox;	
	
	$premiumbox->up_mode();
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	if (!$user_id) {
		pn_display_mess(__('Error! You must authorize', 'pn'));		
	}
		
	$id = intval(is_param_get('item_id'));	
	if ($id > 0) {
		$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_payouts WHERE auto_status = '1' AND user_id = '$user_id' AND status = '0' AND id = '$id'");
		if (isset($item->id)) {
			$res = apply_filters('item_user_payouts_not_before', pn_ind(), $id, $item);
			if ($res['ind']) {
				$arr = array();
				$arr['status'] = 3;		
				$result = $wpdb->update($wpdb->prefix . 'user_payouts', $arr, array('id' => $item->id));
				do_action('item_user_payouts_not', $id, $item, $result);		
			} else {	
				pn_display_mess(is_isset($res, 'error'));
			}
		}
	}
	
	$url = apply_filters('payouts_redirect', $premiumbox->get_page('payouts')); 
	wp_redirect($url);
	exit;
}

add_action('premium_siteaction_payoutform', 'def_premium_siteaction_payoutform');
function def_premium_siteaction_payoutform() {
	global $wpdb, $premiumbox;	
	
	_method('post');
	_json_head();
	
    $log = array();	
	$log['status'] = '';
	$log['status_text'] = '';
	$log['status_code'] = 0;	
	
	$premiumbox->up_mode('post');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	if (!$user_id) {
		$log['status'] = 'error'; 
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! You must authorize', 'pn');
		echo pn_json_encode($log);
		exit;		
	}
		 
	$currency_id = intval(is_param_post('currency_id')); if ($currency_id < 1) { $currency_id = 0; }
	$account = pn_strip_input(is_param_post('account'));
	
	$log = create_user_payout($currency_id, $account, $ui);	
	if (isset($log['item'])) {
		unset($log['item']);
	}
	
	echo pn_json_encode($log);
	exit;
}

function create_user_payout($currency_id, $account, $ui) {
	global $wpdb, $premiumbox;
	
	$log = array();
	
	$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' AND currency_status = '1' AND p_payout = '1' AND id = '$currency_id'");	
	if (isset($item->id)) {
		$reserve = is_sum($item->currency_reserv); 
		$payout_com = $item->payout_com;
		$account = get_purse($account, $item);
		if ($account) {
				
			$arr = array();
			$arr['pay_date'] = current_time('mysql');
			$arr['user_id'] = $ui->ID;
			$arr['user_login'] = is_user($ui->user_login);
			$arr['pay_sum'] = 0;
			$arr['pay_sum_or'] = 0;
			$arr['psys_title'] = pn_strip_input($item->psys_title);
			$arr['currency_id'] = $item->id;
			$arr['currency_code_id'] = $item->currency_code_id;
			$arr['currency_code_title'] = is_site_value($item->currency_code_title);
			$arr['pay_account'] = $account;
			$arr['status'] = 0;	
					
			$arr['edit_date'] = $arr['create_date'] = current_time('mysql');
			$arr['auto_status'] = 1;
					
			$res = apply_filters('item_user_payouts_add_before', pn_ind(), $arr);
			if ($res['ind']) { 
				$minpay = is_sum($premiumbox->get_option('partners', 'minpay'), 2);
				$balans = get_partner_money($ui->ID, array('0', '1'));
				if ($balans >= $minpay and $balans > 0) {
					$pay_sum = is_sum(convert_sum($balans, cur_type(), $item->currency_code_title));
					if ($reserve >= $pay_sum) {
						$arr['pay_sum'] = sum_after_comis($pay_sum, $payout_com);
						$arr['pay_sum_or'] = $balans;
						$wpdb->insert($wpdb->prefix . 'user_payouts', $arr);				
						$insert_id = $wpdb->insert_id;
					
						$arr['id'] = $insert_id;
						$payoutuser_item = (object)$arr;
							
						do_action('item_user_payouts_wait', $insert_id, $payoutuser_item, 1);

						$notify_tags = array();
						$notify_tags['[user]'] = is_user($ui->user_login);
						$notify_tags['[sum]'] = $arr['pay_sum'] . ' ' . get_currency_title($item);
						$notify_tags['[ctype]'] = cur_type();

						$user_send_data = array(
							'admin_email' => 1,
						);
						$result_mail = apply_filters('premium_send_message', 0, 'payout', $notify_tags, $user_send_data);					 
							
						$log['item'] = $arr;	
						$log['status'] = 'success';
						$log['status_text'] = __('Payout is successfully requested', 'pn');
						$log['url'] = apply_filters('payouts_redirect', $premiumbox->get_page('payouts'));
					} else {
						$log['status'] = 'error'; 
						$log['status_code'] = 1;
						$log['status_text'] = __('Error! You are unable to make a selected currency transaction', 'pn');
					}						
				} else {
					$log['status'] = 'error'; 
					$log['status_code'] = 1;
					$log['status_text'] = __('Error! There is not enough money on your balance', 'pn');		
				}		
			} else {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = is_isset($res, 'error');					
			}	
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! Invalid wallet account', 'pn');
		}							
	} else {
		$log['status'] = 'error'; 
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! Selected currency can not be ordered payment', 'pn');			
	}	
	
	return $log;
}