<?php
if (!defined('ABSPATH')) { exit(); } 

function get_exchangestep_title($place = 'all') {
	global $wpdb, $bids_data;
	
	$place = trim($place);
	if (isset($bids_data->id)) {
		if ('auto' == $bids_data->status) {
			$item_title1 = pn_strip_input(ctv_ml($bids_data->psys_give)) . ' ' . pn_strip_input($bids_data->currency_code_give);
			$item_title2 = pn_strip_input(ctv_ml($bids_data->psys_get)) . ' ' . pn_strip_input($bids_data->currency_code_get);
		    $title = sprintf(__('Exchange %1$s to %2$s', 'pn'), $item_title1, $item_title2);
			return apply_filters('get_exchangestep_auto_title', $title, $bids_data->direction_id, $item_title1, $item_title2, $place);
		} else {
			$title = __('Order ID', 'pn') . ' ' . $bids_data->id;
			return apply_filters('get_exchangestep_title', $title, $bids_data->id, $place);
		}
	} else {
		return __('Error 404', 'pn');
	}	
}

add_action('template_redirect', 'bids_initialization', 10); 
function bids_initialization() {
	global $wpdb, $bids_data, $wp_query;

	if (is_pn_page('hst')) {  
		$is_404 = 1;
		$bids_data = array();
		$hashed = is_bid_hash(get_query_var('hashed'));
		if ($hashed) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE hashed = '$hashed'");
			if (isset($data->id)) {
				$bids_data = $data;
				$is_404 = 0;
			}
		} 
		if ($is_404) {
			status_header(404);
			$wp_query->set_404();	
		}		
	}
	
	$bids_data = (object)$bids_data;
}

add_filter('wp_title' , 'bid_wp_title', 100);
function bid_wp_title($title) {
	
	if (is_pn_page('hst')) {
		return get_exchangestep_title('title');
	}	
	
	return $title;			
}

add_action('wp_before_admin_bar_render', 'wp_before_admin_bar_render_exchangestep', 1);
function wp_before_admin_bar_render_exchangestep() {
	global $wp_admin_bar, $bids_data;
		
	if (current_user_can('administrator') or current_user_can('pn_bids')) {
		if (isset($bids_data->id)) {
			
			$wp_admin_bar->add_menu( array(
				'id'     => 'show_bids',
				'href' => admin_url('admin.php?page=pn_bids&bidid=' . $bids_data->id),
				'title'  => __('Go to order', 'pn'),	
			));	
			
			if (current_user_can('administrator') or current_user_can('pn_directions')) {
				
				$wp_admin_bar->add_menu( array(
					'id'     => 'edit_directions',
					'parent' => 'show_bids',
					'href' => admin_url('admin.php?page=pn_add_directions&item_id=' . $bids_data->direction_id),
					'title'  => __('Edit direction exchange', 'pn'),	
				));				
				
			}
			
			if (current_user_can('administrator') or current_user_can('pn_currency')) {
			
				$item_title1 = pn_strip_input(ctv_ml($bids_data->psys_give)) . ' ' . pn_strip_input($bids_data->currency_code_give);
				$item_title2 = pn_strip_input(ctv_ml($bids_data->psys_get)) . ' ' . pn_strip_input($bids_data->currency_code_get);
						
				$wp_admin_bar->add_menu( array(
					'id'     => 'edit_currency1',
					'parent' => 'show_bids',
					'href' => admin_url('admin.php?page=pn_add_currency&item_id=' . $bids_data->currency_id_give),
					'title'  => sprintf(__('Edit "%s"', 'pn'), $item_title1),	
				));
				$wp_admin_bar->add_menu( array(
					'id'     => 'edit_currency2',
					'parent' => 'show_bids',
					'href' => admin_url('admin.php?page=pn_add_currency&item_id=' . $bids_data->currency_id_get),
					'title'  => sprintf(__('Edit "%s"', 'pn'), $item_title2),	
				));

			}
			
		}
	}
}

add_action('premium_js', 'premium_js_exchange_checkrule');
function premium_js_exchange_checkrule() {
?>
jQuery(function($) { 
	
	var res_timer = 1;
	function start_res_timer() {
		$('.res_timer').html('0');
		
		if (res_timer == 1) {
			res_timer = 0;
			setInterval(function() { 
				if ($('.res_timer').length > 0) {
					var num_t = parseInt($('.res_timer').html());
					num_t = num_t + 1;
					$('.res_timer').html(num_t);
				}
			},1000);
		}
	}	
	
	$('#check_rule_step').on('change',function() {
		
		if ($(this).prop('checked')) {
			$('#check_rule_step_input').prop('disabled', false);
		} else {
			$('#check_rule_step_input').prop('disabled', true);
		}
		
	});

	$('#check_rule_step_input').on('click',function() {
		
		$(this).parents('.ajax_post_form').find('.resultgo').html('<div class="resulttrue"><?php echo esc_attr(__('Processing. Please wait', 'pn')); ?> (<span class="res_timer">0</span>)</div>');
		start_res_timer();
		
	});	
			
});		
<?php 
} 

add_action('premium_js', 'premium_js_iam_pay_bids');
function premium_js_iam_pay_bids() {
?>
jQuery(function($) { 
	
	$('.iam_pay_bids').on('click', function() {
		if (!confirm("<?php echo esc_attr(__('Are you sure that you paid your order?', 'pn')); ?>")) {
			return false;
		}
	});		
			
});		
<?php 
} 

add_action('autocheck_bid_loader', 'def_autocheck_bid_loader');
function def_autocheck_bid_loader() {
?>
	$('.block_check_payment_abs').html(nowdata);
	$('.block_check_payment').show();
	var wid = $('.block_check_payment').width();
	if (wid > 1) {
		var onepr = wid / second;
		var nwid = onepr * nowdata;
		if (nwid > wid) { nwid = wid; }
		$('.block_check_payment_ins').animate({'width': nwid}, 500);
	}
<?php	
}
				
add_action('premium_js', 'premium_js_exchange_timer');
function premium_js_exchange_timer() {
?>
jQuery(function($) {
	
	if ($('.check_payment_hash').length > 0) {
		var nowdata = 0;
		var redir = 0;
			
		function check_payment_now() {
			var second = parseInt($('.check_payment_hash').attr('data-time'));
		
			nowdata = parseInt(nowdata) + 1;

			<?php do_action('autocheck_bid_loader'); ?>
				
			if (nowdata >= second) {
				if (redir == 0) {
					var durl = $('.check_payment_hash').attr('data-hash');
					redir = 1;
					if (durl.length > 0) {
						$('.exchange_status_abs').show();
						
						var param = 'hashed=' + durl;
						$.ajax({
							type: "POST",
							url: "<?php echo get_pn_action('refresh_status_bids'); ?>&auto_check=1",
							dataType: 'json',
							data: param,
							error: function(res, res2, res3) {
								<?php do_action('pn_js_error_response', 'ajax'); ?>
							},			
							success: function(res)
							{
								$('.exchange_status_abs').hide();
								if (res['html']) {
									$('#exchange_status_html').html(res['html']);
									<?php do_action('live_change_html'); ?>
									redir = 0;
									nowdata = 0;
								} 
							}
						});	
					}					
				}
			}
		}
		setInterval(check_payment_now,1000);
	}
	
});		
<?php 
} 

add_action('premium_siteaction_refresh_status_bids', 'def_premium_siteaction_refresh_status_bids');
function def_premium_siteaction_refresh_status_bids() {
	global $wpdb, $bids_data, $premiumbox;
	
	_json_head();
	_method('post'); 
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = '0'; 
	$log['status_text']= '';
	$log['html'] = ' ';	
	
	$premiumbox->up_mode('post');
	
	$hashed = is_bid_hash(is_param_post('hashed'));
	if ($hashed) {
		$bids_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE hashed = '$hashed'");
		if (isset($bids_data->id)) {
			$html = apply_filters('exchangestep_' . is_status_name($bids_data->status), '', $bids_data);
			$html .= apply_filters('exchangestep_all', '', is_status_name($bids_data->status), $bids_data);
		} else {
			$html = '<div class="resultfalse">' . __('Error! Order does not exist', 'pn') . '</div>';
		}
		$log['html'] = $html;
	} 	
	
	echo pn_json_encode($log);
	exit;
}

function exchangestep_page_shortcode($atts, $content) {
	global $wpdb, $bids_data;
	
	$temp = '<div class="resultfalse">' . __('Error! Order does not exist', 'pn') . '</div>';

	if (isset($bids_data->id)) {
			
		$temp = apply_filters('before_exchangestep_page', '', $bids_data);
		$temp .= '
		<div class="exchange_status_html">
			<div class="exchange_status_abs"></div>
			<div id="exchange_status_html">';	
				$temp .= apply_filters('exchangestep_' . is_status_name($bids_data->status), '', $bids_data);
				$temp .= apply_filters('exchangestep_all', '', is_status_name($bids_data->status), $bids_data);
			$temp .= '
			</div>
		</div>';	
		$temp .= apply_filters('after_exchangestep_page', '', $bids_data);
			
	}
	
	return $temp;
}
add_shortcode('exchangestep', 'exchangestep_page_shortcode');

add_filter('exchangestep_all', 'get_exchangestep_all', 1, 2);
function get_exchangestep_all($temp, $status) {
	global $wpdb, $premiumbox, $bids_data;
	
	$temp = '';

	if (isset($bids_data->id)) {
		
		$direction_id = intval($bids_data->direction_id);
		
		$item_id = intval($bids_data->id);
		
		$hashed = is_bid_hash($bids_data->hashed);
		
		$currency_id_give = intval($bids_data->currency_id_give);
		$currency_id_get = intval($bids_data->currency_id_get);
		
		$vd1 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_give' AND auto_status = '1'");
		$vd2 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_get' AND auto_status = '1'");		
		
		$m_st = get_status_sett('merch', 1);
		$p_st = get_status_sett('paymerch', 1);
		
		if ('auto' == $status or in_array($status, $m_st)) {
			$where = get_directions_where('exchange');
			$direction_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where AND id = '$direction_id'");
			if (isset($direction_data->id)) {
				$output = apply_filters('get_direction_output', 1, $direction_data, 'exchange');
				if (1 != $output) {
					$direction_data = array();
				}
			}			
		} else {
			$direction_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE direction_status IN('1','2') AND auto_status = '1' AND id = '$direction_id'");
		}		
		
		if (!isset($direction_data->id)) {
			return '<div class="exch_error"><div class="exch_error_ins">' . __('Exchange direction is disabled', 'pn') . '</div></div>';
		}		
		
		$direction = array();
		foreach ($direction_data as $direction_key => $direction_val) {
			$direction[$direction_key] = $direction_val;
		}
		$direction_meta = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions_meta WHERE item_id = '$direction_id'");
		foreach ($direction_meta as $direction_item) {
			$direction[$direction_item->meta_key] = $direction_item->meta_value;
		}	
		$direction = (object)$direction;		
		
		$dmetas = @unserialize($bids_data->dmetas);
		$metas = @unserialize($bids_data->metas);		
		
		$status = is_status_name($bids_data->status);
		
		$is_true = is_true_userhash($bids_data);
		
		$browser_error = '';
		$cancel_button = '';
		$pay_button = '';
		$action_or_error = '';		
		
		if (in_array($status, $m_st)) {
		
			if ($is_true) {	

				$sum_to_pay = apply_filters('sum_to_pay', is_sum($bids_data->sum1dc), $bids_data->m_in, $direction, $bids_data);

				$cancel_button = apply_filters('merchant_cancel_button', '<a href="' . get_pn_action('canceledbids') . '&hash=' . is_bid_hash($bids_data->hashed) . '" class="cancel_paybutton">' . __('Cancel a order', 'pn') . '</a>', $sum_to_pay, $direction, $vd1, $vd2);
				$pay_button = apply_filters('merchant_payed_button', '<a href="' . get_pn_action('payedbids') . '&hash=' . is_bid_hash($bids_data->hashed) . '" class="success_paybutton iam_pay_bids">' . __('Paid', 'pn') . '</a>', $sum_to_pay, $direction, $vd1, $vd2);

				$action_or_error = '
				<div class="block_paybutton">
					<div class="block_paybutton_ins">';	
						
						$action_or_error .= $cancel_button;
						$action_or_error .= $pay_button;
										
						$action_or_error .= '
							<div class="clear"></div>
					</div>
				</div>
				';	
				
			} else {	
			
				$browser_error = $action_or_error = '
				<div class="block_change_browse block_change_browser">
					<div class="block_change_browser_ins">	
						<p>' . __('Error! You cannot control this order in another browser', 'pn') . '</p>	
					</div>
				</div>					
				';	
				
			}	

		}

		$merchant_action = '';
		if ($is_true) {
			$merchant_action = apply_filters('merchant_formstep_after', '', $bids_data->m_in, $direction, $vd1, $vd2); /* only web */
		}	
			
		$text = get_direction_descr('timeline_txt', $direction, $vd1, $vd2);
		$text = apply_filters('direction_instruction', $text, 'timeline_txt', $direction, $vd1, $vd2, $bids_data);
		
		$timeline = '';		
		if (strlen($text) > 0) {
			if ('auto' == $status or in_array($status, $m_st)) {
			
				$timeline = '
				<div class="notice_message">
					<div class="notice_message_ins">
						<div class="notice_message_abs"></div>
						<div class="notice_message_close"></div>
						<div class="notice_message_text">
							<div class="notice_message_text_ins">
								<div class="text">
									'. apply_filters('comment_text', $text) .'
								</div>
							</div>
						</div>
					</div>
				</div>
				';
			
			}
		}					
		
		$frozen = '';
		if (2 == $direction->direction_status) {
			$text = get_direction_descr('frozen_txt', $direction, $vd1, $vd2);
			$text = apply_filters('direction_instruction', $text, 'frozen_txt', $direction, $vd1, $vd2, $bids_data);
			
			if (strlen($text) > 0) {

				$frozen = '
				<div class="notice_message frozen_message">
					<div class="notice_message_ins">
						<div class="notice_message_abs"></div>
						<div class="notice_message_close"></div>
						<div class="notice_message_text">
							<div class="notice_message_text_ins">
								<div class="text">
									'. apply_filters('comment_text', $text) .'
								</div>	
							</div>
						</div>
					</div>
				</div>';
					
			}	
		}		

		$instruct = '';
		if ($is_true) {
			$instruction = _bid_pay_instruction($status, $bids_data, $direction, $vd1, $vd2);
			if (strlen($instruction) > 0) {
				
				$instruct = '
				<div class="block_instruction st_' . $status . '">
					<div class="block_instruction_ins">
						<div class="text">
							' . apply_filters('comment_text', $instruction) . '
						</div>	
					</div>	
				</div>			
				';				
				
			}
		}	

		$status_text = get_direction_tempdata($status, 'naps_status');
		$status_title = get_direction_tempdata($status, 'naps_title'); 		
		
		$date_format = get_option('date_format');
		$time_format = get_option('time_format');
		$create_date = get_pn_time($bids_data->create_date, "{$date_format}, {$time_format}");	
		$status_date = get_pn_time($bids_data->edit_date, "{$date_format}, {$time_format}");

		$an_hidden = intval($premiumbox->get_option('exchange', 'an_hidden'));
		if (!$is_true) {
			$an_hidden = 4;
		}
		
		$pay_com1 = pn_strip_input($direction->pay_com1);
		$pay_com2 = pn_strip_input($direction->pay_com2);
		
		$com_ps1 = pn_strip_input($bids_data->com_ps1);
		if (1 == $pay_com1) {
			$com_ps1 = 0;
		}
		 
		$comis_text1 = get_comis_text($com_ps1, $bids_data->dop_com1, ctv_ml(is_isset($vd1, 'psys_title')), is_isset($vd1, 'currency_code_title'), 1, 0);
		
		$com_ps2 = pn_strip_input($bids_data->com_ps2);		
		if (1 == $pay_com2) {
			$com_ps2 = 0;
		}		
		
		$comis_text2 = get_comis_text($com_ps2, $bids_data->dop_com2, ctv_ml(is_isset($vd2, 'psys_title')), is_isset($vd2, 'currency_code_title'), 2, 0);		
		
		$com_give_text = $com_get_text = '';
		if ($comis_text1) {
			$com_give_text ='
			<div class="block_xchdata_comm">
				'. $comis_text1 .'
			</div>	
			';
		}
		
		if ($comis_text2) {
			$com_get_text ='
			<div class="block_xchdata_comm">
				'. $comis_text2 .'
			</div>	
			';
		}		
		
		$give_field = $get_field = '';
	
		if (isset($dmetas[1]) and is_array($dmetas[1])) {
			foreach ($dmetas[1] as $value) {					
				$title = pn_strip_input(ctv_ml(is_isset($value, 'title')));
				$data = trim(pn_strip_input(is_isset($value, 'data')));
				if (strlen($data) > 0) {
					$give_field .= '<div class="block_xchdata_line"><span>' . $title . ':</span> ' . get_secret_value($data, $an_hidden) . '</div>';
				}
			}
		}

		if (isset($dmetas[2]) and is_array($dmetas[2])) {
			foreach ($dmetas[2] as $value) {					
				$title = pn_strip_input(ctv_ml(is_isset($value, 'title')));
				$data = trim(pn_strip_input(is_isset($value, 'data')));				
				if (strlen($data) > 0) {
					$get_field .= '<div class="block_xchdata_line"><span>' . $title . ':</span> ' . get_secret_value($data, $an_hidden) . '</div>';
				}
			}
		}		

		$personal_data = '';
		
		$personal_metas = array();
		$dir_fields = get_user_fields();
		foreach ($dir_fields as $dir_field_key => $dir_field) {
			$value = trim(is_isset($bids_data, $dir_field_key));
			if (strlen($value) > 0) {
				$personal_metas[] = array(
					'title' => $dir_field['title'],
					'data' => $value,
					'id' => $dir_field_key,
				);
			}						
		}		
		
		if (is_array($metas) and count($metas) > 0) {
			foreach ($metas as $value) {				
				$personal_metas[] = $value;
			}	
		}	
		
		if (is_array($personal_metas) and count($personal_metas) > 0) {
				
			$personal_data = '
			<div class="block_persdata">
				<div class="block_persdata_ins">
					<div class="block_persdata_title">
						<div class="block_persdata_title_ins">
							<span>' . __('Personal data', 'pn') . '</span>
						</div>
					</div>
					<div class="block_persdata_info">';	
						foreach ($personal_metas as $value) {				
							$title = pn_strip_input(ctv_ml(is_isset($value, 'title')));
							$data = pn_strip_input(is_isset($value, 'data'));
							if (strlen($data) > 0) {			
								$personal_data .= '<div class="block_persdata_line"><span>' . $title . ':</span> ' . get_secret_value($data, $an_hidden) . '</div>';			
							}	
						}
					$personal_data .= '
					</div>	
				</div>
			</div>';
				
		}		
		
		$account_give = $account_get = '';
		if ($bids_data->account_give) {
			$txt = pn_strip_input(ctv_ml(is_isset($vd1, 'txt1')));
			if (strlen($txt) < 1) { $txt = __('From account', 'pn'); }
			$account = $bids_data->account_give;
			$account = apply_filters('show_user_account', $account, $bids_data, $direction, $vd1);	
			$account_give = '<div class="block_xchdata_line break_words"><span>' . $txt . ':</span> ' . get_secret_value($account, $an_hidden) . '</div>';
		}	

		if ($bids_data->account_get) {
			$txt = pn_strip_input(ctv_ml(is_isset($vd2, 'txt2')));
			if (strlen($txt) < 1) { $txt = __('Into account', 'pn'); }
			$account = $bids_data->account_get;
			$account = apply_filters('show_user_account', $account, $bids_data, $direction, $vd2);	
			$account_get = '<div class="block_xchdata_line break_words"><span>' . $txt . ':</span> ' . get_secret_value($account, $an_hidden) . '</div>';
		}							
		
		$js_autocheck_time = get_direction_tempdata($status, 'naps_timer_second'); if (!$js_autocheck_time) { $js_autocheck_time = 30; }
		$js_autocheck = get_direction_tempdata($status, 'naps_timer');		
		$js_autocheck = apply_filters('js_autocheck', $js_autocheck, $bids_data);
		$enable_autocheck = 0;
		if (1 == $js_autocheck) {
			$enable_autocheck = 1;
			if (isset($_GET['auto_check'])) {
				$enable_autocheck = intval($_GET['auto_check']);
			} 
		} elseif (2 == $js_autocheck) {
			$enable_autocheck = 0;
			if (isset($_GET['auto_check'])) {
				$enable_autocheck = intval($_GET['auto_check']);
			}
		}				
		
		$refresh_ind = '';
		$refresh_button = '';		
		$autocheck_html = '';		
		if ($js_autocheck > 0) {	
			if ($enable_autocheck) {
				
				$refresh_ind = '
				<div class="block_check_payment">
					<div class="block_check_payment_ins">
						<div class="block_check_payment_abs"></div>
						<div class="block_check_payment_ins"></div>
					</div>	
				</div>				
				';
				
				$refresh_button = '<a href="' . get_bids_url($bids_data->hashed) . '?auto_check=0" class="merch_paybutton refresh_button refresh_button_disable">' . __('Disable refreshing', 'pn') . '</a>';				
				
				$autocheck_html .= $refresh_ind;
				$autocheck_html .= '	
				<div class="block_warning_merch">
					<div class="block_warning_merch_ins">
						<p>'. sprintf(__('Page refreshes every %s seconds.', 'pn'), $js_autocheck_time) .'</p>
					</div>
				</div>';				
								
				$autocheck_html .= '		
				<div class="block_paybutton_merch">
					<div class="block_paybutton_merch_ins">	
						' . $refresh_button . '
					</div>
				</div>					
				';	
								
			} else {
						
				$refresh_button = '<a href="' . get_bids_url($bids_data->hashed) . '?auto_check=1" class="merch_paybutton refresh_button refresh_button_enable">' . __('Enable refreshing', 'pn') . '</a>';
						
				$autocheck_html .= '
				<div class="block_warning_merch">
					<div class="block_warning_merch_ins">
						<p>' . __('Attention! Click "Refresh page", if you want to activate automatic page refreshing.', 'pn') . '</p>
						<p>' . sprintf(__('The page will refresh every %s seconds.', 'pn'), $js_autocheck_time) . '</p>	
					</div>
				</div>';
						
				$autocheck_html .= '	
				<div class="block_paybutton_merch">
					<div class="block_paybutton_merch_ins">			
						' . $refresh_button . '			
					</div>
				</div>					
				';	

			}
		}				
		
		$check_rule = '';
		$disable_button = '';
		$hidecheckrule = intval($premiumbox->get_option('exchange', 'hidecheckrule'));
		if (!$hidecheckrule) {
			
			$disable_button = 'disabled="disabled"';
			$toslink = pn_strip_input(ctv_ml($premiumbox->get_option('toslink')));
			if ($toslink) {
				$toslink_title = sprintf(__('I read and agree with <a href="%s" target="_blank">the terms and conditions</a>', 'pn'), $toslink);
			} else {
				$toslink_title = __('I read and agree with the terms and conditions', 'pn');
			}
			$tostext = pn_strip_text(ctv_ml($premiumbox->get_option('exchange', 'tostext')));
			if (strlen($tostext) > 0) {
				$toslink_title = $tostext;
			}			
			
			$check_rule = '<label><input type="checkbox" id="check_rule_step" name="check_rule" autocomplete="off" value="1" /> ' . $toslink_title . '</label>';
		
		}
		$submit = '<input type="submit" name="" formtarget="_top" id="check_rule_step_input" ' . $disable_button . ' value="' . __('Create order', 'pn') . '" />';	

		$array = array(
			'[result]' => '<div class="ajax_post_bids_res"><div class="resultgo"></div></div>',
			'[submit]' => $submit,
			'[check_rule]' => $check_rule,
			'[timeline]' => $timeline,
			'[status]' => $status,
			'[status_title]' => $status_title,
			'[status_date]' => $status_date,
			'[status_text]' => $status_text,
			'[create_date]' => $create_date,	
			'[instruction]' => $instruct,
			'[frozen]' => $frozen,
			'[com_give_text]' => $com_give_text,
			'[com_get_text]' => $com_get_text,			
			'[ps_give]' => pn_strip_input(ctv_ml($bids_data->psys_give)),
			'[ps_get]' => pn_strip_input(ctv_ml($bids_data->psys_get)),
			'[vtype_give]' => is_site_value($bids_data->currency_code_give),
			'[vtype_get]' => is_site_value($bids_data->currency_code_get),
			'[give_currency]' => get_currency_title($vd1),
			'[get_currency]' => get_currency_title($vd2),
			'[give_currency_logo]' => get_currency_logo($vd1),
			'[get_currency_logo]' => get_currency_logo($vd2),
			'[personal_data]' => $personal_data,
			'[give_field]' => $give_field,
			'[get_field]' => $get_field,	
			'[sum_to_pay]' => is_sum($bids_data->sum1c),
			'[sum_give]' => is_sum($bids_data->sum1dc),
			'[sum_get]' => is_sum($bids_data->sum2c),			
			'[account_give]' => $account_give,
			'[account_get]' => $account_get,
			'[action_or_error]' => $action_or_error,	
			'[browser_error]' => $browser_error,	
			'[cancel_button]' => $cancel_button,
			'[pay_button]' => $pay_button,
			'[refresh_ind]' => 	$refresh_ind,
			'[refresh_button]' => $refresh_button,
			'[merchant_action]' => $merchant_action,
			'[autocheck]' => $autocheck_html,
		);
		
		$array = apply_filters('exchangestep_' . $status . '_html_list', $array, $bids_data, $direction, $vd1, $vd2);
		$array = apply_filters('exchangestep_all_html_list', $array, $bids_data, $direction, $vd1, $vd2);
	
		$html = '';
	
		if ('auto' == $status) {
	
			$html = '
			[frozen]
			[timeline]

			<div class="block_xchangedata">
				<div class="block_xchangedata_ins">
				
					[instruction]
				
					<div class="block_xchdata_wrap">
						<div class="block_xchdata">
							<div class="block_xchdata_ins">
								<div class="block_xchdata_title otd give">
									<span>' . __('Send', 'pn') . '</span>
								</div>
								
								[com_give_text]
								
								<div class="block_xchdata_info">
									<div class="block_xchdata_info_left">
										<div class="block_xchdata_line"><span>' . __('Amount', 'pn') . ':</span> [sum_give] [give_currency]</div>
										[account_give]
										[give_field]
									</div>
									<div class="block_xchdata_info_right">
										<div class="block_xchdata_ico currency_logo" style="background-image: url([give_currency_logo]);"></div>
										<div class="block_xchdata_text">[give_currency]</div>
											<div class="clear"></div>
									</div>
										<div class="clear"></div>
								</div>						
							</div>
						</div>
						
						<div class="block_xchdata">
							<div class="block_xchdata_ins">
								<div class="block_xchdata_title pol get">							
									<span>'. __('Receive','pn') .'</span>
								</div>
								
								[com_get_text]
								
								<div class="block_xchdata_info">
									<div class="block_xchdata_info_left">
										<div class="block_xchdata_line"><span>' . __('Amount', 'pn') . ':</span> [sum_get] [get_currency]</div>
										[account_get]
										[get_field]
									</div>
									<div class="block_xchdata_info_right">
										<div class="block_xchdata_ico currency_logo" style="background-image: url([get_currency_logo]);"></div>
										<div class="block_xchdata_text">[get_currency]</div>
											<div class="clear"></div>							
									</div>
										<div class="clear"></div>
								</div>		
							</div>
						</div>
					</div>
					
					[personal_data]						

					<div class="block_checked_rule">
						[check_rule]
					</div>							
					
					<div class="block_submitbutton">
						[submit]
					</div>
					
					[result]
				</div>
			</div>
			';	

		} else {

			$html = '
			[timeline]

			<div class="block_statusbids block_status_[status]">
				<div class="block_statusbids_ins">
					
					<div class="block_statusbid_title">
						<div class="block_statusbid_title_ins">
							<span>[status_title]</span>
						</div>
					</div>
						
					[instruction]
						
					<div class="block_payinfo">
						<div class="block_payinfo_give">
							<div class="block_xchdata_line"><span>' . __('Send', 'pn') . ':</span> [sum_give] [give_currency]</div>
							[account_give]
						</div>
						<div class="block_payinfo_get">
							<div class="block_xchdata_line"><span>' . __('Receive', 'pn') . ':</span> [sum_get] [get_currency]</div>
							[account_get]
						</div>
					</div>					
						
					[merchant_action]	
						
					<div class="block_status">
						<div class="block_status_ins">
							<div class="block_status_time" title="' . __('Creation time', 'pn') . ': [create_date]"><span>' . __('Status change time', 'pn') . ':</span> [status_date]</div>
							<div class="block_status_text"><span class="block_status_text_info">' . __('Status of order', 'pn') . ':</span> <span class="block_status_bids bstatus_[status]">[status_text]</span></div>
						</div>
					</div>	
						
					[action_or_error]
						
					[autocheck]

				</div>
			</div>		
			';	
				
		}
		
		$html = apply_filters('exchangestep_' . $status . '_html', $html, $bids_data, $direction, $vd1, $vd2);
		$html = apply_filters('exchangestep_all_html', $html, $bids_data, $direction, $vd1, $vd2);
		if ($enable_autocheck) {
			$temp .= '<div class="check_payment_hash" data-time="' . $js_autocheck_time . '" data-hash="' . is_bid_hash($bids_data->hashed) . '"></div>';
		}
		if ('auto' == $status) {
			$temp .= '
			<form action="' . get_pn_action('confirm_bid') . '" class="ajax_post_form" method="post">
				<input type="hidden" name="hash" value="' . $hashed . '" />
			';		
		}
		
		$temp .= replace_tags($array, $html);
		
		if ('auto' == $status) {
			$temp .= '
			</form>
			';		
		}
	}
	
	return $temp;
}

function get_direction_tempdata($status, $key) {
	global $premiumbox;

	$txts = array('naps_status', 'naps_title');
	if (in_array($key, $txts)) {
		$value = pn_strip_text(ctv_ml($premiumbox->get_option($key, 'status_' . $status)));
	} else {
		$value = intval($premiumbox->get_option($key, 'status_' . $status));
	}

	$def_status = array(
		'coldnew' => array(
			'naps_title' => __('Application pending verification', 'pn'),
			'naps_status' => __('Application pending verification', 'pn'),
		),	
		'new' => array(
			'naps_title' => __('How to make payment', 'pn'),
			'naps_status' => __('Accepted, waiting to be paid by client', 'pn'),
		),
		'amlwait' => array(
			'naps_title' => __('Waiting for aml check', 'pn'),
			'naps_status' => __('Waiting for aml check', 'pn'),
		),
		'techpay' => array(
			'naps_title' => __('How to make payment', 'pn'),
			'naps_status' => __('Accepted, waiting to be paid by client', 'pn'),
		),	
		'coldpay' => array(
			'naps_title' => __('Waiting for merchant confirmation', 'pn'),
			'naps_status' => __('Waiting for merchant confirmation', 'pn'),
		),		
		'payed' => array(
			'naps_title' => __('Order is paid', 'pn'),
			'naps_status' => __('Received confirmation of payment from client', 'pn'),
		),
		'verify' => array(
			'naps_title' => __('Order is on checking', 'pn'),
			'naps_status' => __('Order is on checking', 'pn'),
		),
		'realpay' => array(
			'naps_title' => __('Order is paid', 'pn'),
			'naps_status' => __('Order is paid', 'pn'),
		),
		'partpay' => array(
			'naps_title' => __('Partial paid', 'pn'),
			'naps_status' => __('Partial paid', 'pn'),
		),
		'amlerror' => array(
			'naps_title' => __('Aml check failed', 'pn'),
			'naps_status' => __('Aml check failed', 'pn'),
		),
		'merchwait' => array(
			'naps_title' => __('Waiting for details from the merchant', 'pn'),
			'naps_status' => __('Waiting for details from the merchant', 'pn'),
		),		
		'mercherror' => array(
			'naps_title' => __('Error', 'pn'),
			'naps_status' => __('Error', 'pn'),
		),
		'partpayout' => array(
			'naps_title' => __('Partial payout', 'pn'),
			'naps_status' => __('Partial payout', 'pn'),
		),
		'delete' => array(
			'naps_title' => __('The order is deleted', 'pn'),
			'naps_status' => __('The order is deleted', 'pn'),
		),
		'cancel' => array(
			'naps_title' => __('Refusal of payment', 'pn'),
			'naps_status' => __('User refused to make payment', 'pn'),
		),				
		'error' => array(
			'naps_title' => __('Error', 'pn'),
			'naps_status' => __('Error', 'pn'),
		),
		'coldsuccess' => array(
			'naps_title' => __('Waiting for automatic payments module confirmation', 'pn'),
			'naps_status' => __('Waiting for automatic payments module confirmation', 'pn'),
		),
		'success' => array(
			'naps_title' => __('The order is completed', 'pn'),
			'naps_status' => __('The order is completed', 'pn'),
		),
		'payouterror' => array(
			'naps_title' => __('Auto payout error', 'pn'),
			'naps_status' => __('Auto payout error', 'pn'),		
		),
		'scrpayerror' => array(
			'naps_title' => __('Auto payout error', 'pn'),
			'naps_status' => __('Auto payout error', 'pn'),		
		),		
	);				
		
	if (!$value and isset($def_status[$status][$key])) {
		$value = $def_status[$status][$key];
	}
	
	return $value;
}

function _bid_pay_instruction($status, $bids_data, $direction, $vd1, $vd2) {
	
	$instruction = '';
	$status_instruction = apply_filters('status_instruction', 1, 'status_' . $status, $direction, $vd1, $vd2);
	if (1 == $status_instruction) {
		$m_st = get_status_sett('merch', 1);
		$p_st = get_status_sett('paymerch', 1);
		$instruction = get_direction_descr('status_' . $status, $direction, $vd1, $vd2);
		if (in_array($status, $m_st)) {
			$instruction = apply_filters('instruction_merchant', $instruction, $bids_data, $direction, $vd1, $vd2);
		} elseif (in_array($status, $p_st)) {		
			$instruction = apply_filters('instruction_paymerchant', $instruction, $bids_data, $direction, $vd1, $vd2);
		}	
		$instruction = apply_filters('direction_instruction', $instruction, 'status_' . $status, $direction, $vd1, $vd2, $bids_data);		
	}
	
	return $instruction;
}