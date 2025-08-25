<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('direction_instruction_tags', 'usve_directions_tags', 10, 2); 
function usve_directions_tags($tags, $key) {
	
	$tags['verification_status'] = array(
		'title' => __('User verification status', 'pn'),
		'start' => '[verification_status]',
	);
	$tags['verification_link'] = array(
		'title' => __('Link for user verification', 'pn'),
		'start' => '[verification_link]',
	);	
	$tags['verify_amount'] = array(
		'title' => __('Amount for verification', 'pn'),
		'start' => '[verify_amount]',
	);		
	
	return $tags;
}

add_filter('direction_instruction', 'usve_quicktags_direction_instruction', 10, 5);
function usve_quicktags_direction_instruction($instruction, $txt_name, $direction, $vd1, $vd2) {
	global $bids_data, $direction_data, $premiumbox;	
	
	$ui = wp_get_current_user();
	$user_verify = intval(is_isset($ui, 'user_verify'));
	$user_verify_text = __('Unverified user', 'pn');
	if (1 == $user_verify) {
		$user_verify_text = __('Verified user', 'pn');
	}
	$instruction = str_replace('[verification_status]', $user_verify_text, $instruction);
	
	$verification_link = '<a href="' . $premiumbox->get_page('userverify') . '" target="_blank">' . __('Link for user verification' ,'pn') . '</a>';
	$instruction = str_replace('[verification_link]', $verification_link, $instruction);
	
	$direction_id = 0;
	if (isset($direction_data->direction_id)) {
		$direction_id = $direction_data->direction_id;
	} elseif (isset($bids_data->direction_id)) {
		$direction_id = $bids_data->direction_id;
	}

	if ($direction_id) {
		$verify_sum = is_sum(get_direction_meta($direction_id, 'verify_sum'));
		$instruction = str_replace(array('[amount]', '[verify_amount]'), $verify_sum, $instruction);
	}
	
	return $instruction;
}

add_filter('list_directions_temp', 'usve_list_directions_temp');
function usve_list_directions_temp($temps) {
	
	$temps['notverify_text'] = __('Notification of need to pass identity verification', 'pn');
	$temps['notverify_bysum'] = __('Notification of need to pass identity verification (by exchange amount)', 'pn');
	
	return $temps;
}

add_filter('directions_temp_notupdate', 'def_directions_temp_notupdate');
add_filter('directions_temp_notpaymerchant', 'def_directions_temp_notupdate');
function def_directions_temp_notupdate($array) {
	
	$array[] = 'notverify_text';
	$array[] = 'notverify_bysum';
	
	return $array;
}
 
if (!function_exists('list_tabs_direction_verify')) {
	add_filter('list_tabs_direction', 'list_tabs_direction_verify');
	function list_tabs_direction_verify($list_tabs) {
		
		$list_tabs['verify'] = __('Verification', 'pn');
		
		return $list_tabs;
	}
}
 
add_action('tab_direction_verify', 'tab_direction_verify_userverify',10,2);
function tab_direction_verify_userverify($data, $data_id) {
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('User verification', 'pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Verified users only', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$verify = intval(is_isset($data, 'verify'));
				?>									
				<select name="verify" autocomplete="off"> 
					<option value="0" <?php selected($verify, 0); ?>><?php _e('No', 'pn'); ?></option>
					<option value="1" <?php selected($verify, 1); ?>><?php _e('Yes', 'pn'); ?></option>
					<option value="2" <?php selected($verify, 2); ?>><?php _e('If exchange amount is more than', 'pn'); ?></option>
				</select>		
			</div>							
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange amount for Send', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$verify_sum = get_direction_meta($data_id, 'verify_sum');
				?>									
				<input type="text" name="verify_sum" style="width: 100%;" value="<?php echo is_sum($verify_sum); ?>" />				
			</div>		
		</div>
	</div>	
<?php
}   
 
add_filter('pn_direction_addform_post', 'userverify_pn_direction_addform_post');
function userverify_pn_direction_addform_post($array) {
	
	$array['verify'] = intval(is_param_post('verify'));
	
	return $array;
} 
 
add_action('item_direction_edit', 'item_direction_edit_userverify');
add_action('item_direction_add', 'item_direction_edit_userverify');
function item_direction_edit_userverify($data_id) {	

	$verify_sum = is_sum(is_param_post('verify_sum'));
	update_direction_meta($data_id, 'verify_sum', $verify_sum);	
	
}

add_filter('file_xml_lines', 'file_xml_lines_userverify', 100, 4);
function file_xml_lines_userverify($lines, $ob, $vd1, $vd2) {
	
	$verify = intval(is_isset($ob, 'verify'));
	if ($verify) {
		if (isset($lines['param'])) {
			$lines['param'] = $lines['param'] . ', verifying';
		} else {
			$lines['param'] = 'verifying';
		}
	}
	
	return $lines;
} 

add_filter('exchange_other_filter', 'userverify_exchange_other_filter', 100, 5);
function userverify_exchange_other_filter($html, $direction, $vd1, $vd2, $cdata) {
	global $premiumbox;	

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	$user_verify = intval(is_isset($ui, 'user_verify'));
	if (0 == $user_verify) {		
		$direction_id = $direction->id;
		$verify = intval(is_isset($direction, 'verify'));
		if (1 == $verify) {
			$text = get_direction_descr('notverify_text', $direction, $vd1, $vd2);
			$text = apply_filters('direction_instruction', $text, 'notverify_text', $direction, $vd1, $vd2);
			if (strlen($text) > 0) {
				$html .= '<div class="notice_message_wrap notverify_message_wrap"><div class="notice_message notverify_message"><div class="notverify_message_ins"><div class="text">' . apply_filters('comment_text', $text) . '</div></div></div></div>';
			}
		}
		if (2 == $verify) {
			$sum1 = $cdata['sum1'];
			$verify_sum = is_sum(get_direction_meta($direction_id, 'verify_sum'));
			$cl = ' style="display: none;"';
			if ($sum1 >= $verify_sum) {
				$cl = '';
			}
			$text = get_direction_descr('notverify_bysum', $direction, $vd1, $vd2);
			$text = apply_filters('direction_instruction', $text, 'notverify_bysum', $direction, $vd1, $vd2);

			$html .= '<input type="hidden" name="" value="' . $verify_sum . '" class="verifybysum" />';
			
			if (strlen($text) > 0) {
				$html .= '
				<div class="notice_message_wrap notverify_message_wrap verifybysum_wrap" ' . $cl . '><div class="notice_message notverify_message"><div class="notverify_message_ins"><div class="text">' . apply_filters('comment_text', $text) . '</div></div></div></div>
				';
			}	
		}
	}
	
	return $html;
}

add_action('go_exchange_calc_js_response', 'userverify_go_exchange_calc_js_response');
function userverify_go_exchange_calc_js_response() {
	
	$text = esc_attr(__('pass identity verification', 'pn'));
?>
if ($('.verifybysum').length > 0) {
	if (res['sum1']) {
		var verifybysum = $('.verifybysum').val().replace(/,/g, '.');
		verifybysum = verifybysum * 1;
		var res_sum1 = res['sum1'] * 1;
		if (res_sum1 >= verifybysum) {
			$('.verifybysum_wrap').show();
			add_error_field('sum1', '<?php echo $text; ?>');
			add_error_field('sum2', '<?php echo $text; ?>');
			add_error_field('sum1c', '<?php echo $text; ?>');
			add_error_field('sum2c', '<?php echo $text; ?>');
		} else {
			$('.verifybysum_wrap').hide();				
		}
	}
}
<?php	
}

add_filter('cf_auto_form_value', 'cf_auto_form_value_userverify', 99, 5);
function cf_auto_form_value_userverify($cauv,$value,$op_item, $direction, $cdata) {
	global $wpdb;
	
	$cf_auto = $op_item->cf_auto;
	$sum = $cdata['sum1'];

	$verify = intval(is_isset($direction,'verify'));
	$verify_sum = is_sum(is_isset($direction,'verify_sum'));
	if (1 == $verify or 2 == $verify and $sum >= $verify_sum) {
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);
		if ($user_id) {
			if (isset($ui->user_verify)) {	
				if (1 == $ui->user_verify) {
					$err = 0;
					if (strtolower(is_isset($ui, $cf_auto)) != strtolower($value) and pn_verify_uv($cf_auto)) {
						$err = 1;
					} 	
					if (1 == $err) {
						$cauv = array(
							'error' => 1,
							'error_text' => __('not verified', 'pn')
						);						
					}
				}
			}
		}
	}
	
	return $cauv;
} 
	
add_filter('change_bid_status', 'verify_change_bidstatus', 100); 
function verify_change_bidstatus($data) {   
	global $wpdb, $premiumbox;

	$place = $data['place'];
	$set_status = $data['set_status'];
	$bid = $data['bid'];
	$who = $data['who'];
	$old_status = $data['old_status'];
	$direction = $data['direction'];

	$stop_action = intval(is_isset($data, 'stop'));

	if ('new' == $set_status and !$stop_action) {
		$show_error = intval($premiumbox->get_option('usve', 'create_not'));
		if (1 == $show_error) {
			if (1 != $bid->user_verify) {
				$direction_id = $bid->direction_id;
				if (!isset($direction->id)) {
					$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$direction_id' AND auto_status = '1'");
				}
				if (isset($direction->id)) {
					$verify = intval(is_isset($direction, 'verify'));
					$sum = is_sum($bid->sum1);
					$verify_sum = is_sum(get_direction_meta($direction->id, 'verify_sum'));
					if (1 == $verify or 2 == $verify and $sum >= $verify_sum) {
						
						$array = array();
						$array['edit_date'] = current_time('mysql');
						$array['status'] = 'coldnew';
						$wpdb->update($wpdb->prefix . 'exchange_bids', $array, array('id' => $bid->id));
							
						$old_status = $bid->status;
						$bid = pn_object_replace($bid, $array);
						$data['bid'] = $bid;
						$data['stop'] = 1;
						
						$ch_data = array(
							'bid' => $bid,
							'set_status' => 'coldnew',
							'place' => 'verify_module',
							'who' => 'user',
							'old_status' => $old_status,
							'direction' => $direction
						);
						_change_bid_status($ch_data);						
						
					}
				}
			}
		}
	}
	
	return $data;
}

add_filter('coldnew_to_new', 'verify_coldnew_to_new', 10, 2);
function verify_coldnew_to_new($ind, $item) {
	global $premiumbox, $wpdb;	
	
	if (1 == $ind) {
		if (1 != $item->user_verify) {
			$direction_id = $item->direction_id;
			$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$direction_id' AND auto_status = '1'");
			if (isset($direction->id)) {
				$verify = intval(is_isset($direction, 'verify'));
				$sum = is_sum($item->sum1);
				$verify_sum = is_sum(get_direction_meta($direction->id, 'verify_sum'));
				if (1 == $verify or 2 == $verify and $sum >= $verify_sum) {
					return 0;
				}
			}
		}
	}
	
	return $ind;
}

add_action('item_users_verify', 'coldnew_item_users_verify');
function coldnew_item_users_verify($user_id) {
	global $premiumbox, $wpdb;

	$wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET user_verify = '1' WHERE user_id = '$user_id' AND status IN('new','coldnew','techpay')");
	
	$show_error = intval($premiumbox->get_option('usve', 'create_not'));
	if (1 == $show_error) {
		coldnew_to_new();
	}
}

add_action('item_users_unverify', 'coldnew_item_users_unverify');
function coldnew_item_users_unverify($user_id) {
	global $wpdb;
	
	$wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET user_verify = '0' WHERE user_id = '$user_id' AND status IN('new','coldnew','techpay')");
}

if (!function_exists('coldnew_to_new')) {
	function coldnew_to_new() {
		global $wpdb;
		
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldnew'");
		foreach ($items as $item) {
			$ind = apply_filters('coldnew_to_new', 1, $item);
			if (1 == $ind) {
				
				$array = array();
				$array['edit_date'] = current_time('mysql');
				$array['status'] = 'new';
				$array = apply_filters('array_data_bids_new', $array, $item);
				$wpdb->update($wpdb->prefix . 'exchange_bids', $array, array('id' => $item->id));
				
				$bid = pn_object_replace($item, $array);
				
				$ch_data = array(
					'bid' => $bid,
					'set_status' => 'new',
					'place' => 'verify_module',
					'who' => 'system',
					'old_status' => 'coldnew',
					'direction' => ''
				);
				_change_bid_status($ch_data);
				
			}
		}		
	}
}

add_filter('error_bids', 'error_bids_verify', 99, 5); 
function error_bids_verify($error_bids, $direction, $vd1, $vd2, $cdata) {
	global $premiumbox;

	$user_id = intval(is_isset($error_bids['bid'], 'user_id'));
	$ui = get_userdata($user_id);
	$error_bids['bid']['user_verify'] = intval(is_isset($ui, 'user_verify'));

	$show_error = intval($premiumbox->get_option('usve', 'create_not'));
	if (1 != $show_error) {
		$sum = $cdata['sum1'];
		$verify = intval(is_isset($direction, 'verify'));
		$verify_sum = is_sum(is_isset($direction, 'verify_sum'));
		if (1 == $verify or 2 == $verify and $sum >= $verify_sum) {
			$user_verify = intval(is_isset($ui, 'user_verify'));
			if (1 != $user_verify) {
				$error_text = pn_strip_text(ctv_ml($premiumbox->get_option('usve', 'errortext')));
				if (strlen($error_text) < 2) {
					$error_text = sprintf(__('Error! Exchange is available for verified users only. Pass user verification by link: <a href="%1s" target="_blank" rel="noreferrer noopener">%2s</a>', 'pn'), $premiumbox->get_page('userverify'), $premiumbox->get_page('userverify'));
				}
				$error_bids['error_text'][] = $error_text;
			}
		}
	}
	
	return $error_bids;
}

add_filter('onebid_icons','onebid_icons_verify', 10, 2);
function onebid_icons_verify($onebid_icon, $item) {
	global $premiumbox;
	
	if (isset($item->user_verify) and 1 == $item->user_verify) {
		$onebid_icon['userverify'] = array(
			'type' => 'label',
			'title' => __('Verified user', 'pn'),
			'image' => $premiumbox->plugin_url . 'images/userverify.png',
		);	
	}
	
	return $onebid_icon;
} 