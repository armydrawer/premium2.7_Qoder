<?php
if (!defined('ABSPATH')) { exit(); }
	
add_action('premium_js','premium_js_exchange_action'); 
function premium_js_exchange_action() {
	global $premiumbox;
	
	$hidesavedata = intval($premiumbox->get_option('exchange', 'hidesavedata'));
?>	
jQuery(function($) {
	
	$(document).on('click', '.js_amount', function() {
		
		var amount = $(this).attr('data-val');
		var id = $(this).attr('data-id');
		$('input.js_' + id + ':not(:disabled)').val('0').trigger('keyup');
		$('input.js_' + id + ':not(:disabled)').val(amount).trigger('change');
		$('.js_' + id + '_html').html(amount);
		
	});		
	
	function cache_exchange_data(thet) {
		
		var ind = 0;
		if (thet.hasClass('check_cache')) {
			<?php if (!$hidesavedata) { ?>
			
				ind = 1;
				
				var not_check_data = 0;
				if ($('#not_check_data').length > 0) {
					if ($('#not_check_data').prop('checked')) {
						not_check_data = 1;
					}
				} else {
					not_check_data = '<?php echo intval(get_pn_cookie('not_check_data')); ?>';	
				}
				if (not_check_data == 1) {
					ind = 0;
				}				
			
			<?php } ?>
		} else {
			ind = 1;
		}
		if (ind == 1) {
			var id = thet.attr('cash-id');
			var v = thet.val();
			$(document).PHPCookie('set', {key: "cache_" + id, value: v, domain: '<?php echo PN_SITE_URL; ?>', days: '30'});	
		}
		
	}
	
	$(document).ChangeInput({ 
		trigger: '.cache_data',
		success: function(obj) {
			
			cache_exchange_data(obj);
			
		}
	});	
	
	$(document).on('change', '#not_check_data', function() {
		
		if ($(this).prop('checked')) {
			$(document).PHPCookie('set', {key: "not_check_data", value: 1, domain: '<?php echo PN_SITE_URL; ?>', days: '30'});
			$('.check_cache').each(function() {
				var id = $(this).attr('cash-id');
				$(document).PHPCookie('set', {key: "cache_" + id, value: '', domain: '<?php echo PN_SITE_URL; ?>', days: '30'});
			});	
		} else {
			$(document).PHPCookie('set', {key: "not_check_data", value: 0, domain: '<?php echo PN_SITE_URL; ?>', days: '30'});
			$('.check_cache').each(function() {
				var id = $(this).attr('cash-id');
				var v = $(this).val();
				$(document).PHPCookie('set', {key: "cache_" + id, value: v, domain: '<?php echo PN_SITE_URL; ?>', days: '30'});
			});		
		}
		
	});
	
	$(document).on('change', 'select, textarea, input:not(.js_sum_val)', function() {
		
		$(this).parents('.js_wrap_error').removeClass('error');
		
	});
	
	$(document).on('click', 'input, textarea, select', function() {
		
		$(this).parents('.js_wrap_error').removeClass('error');
		
	});	
	
	$(document).on('click', '.ajax_post_bids input[type=submit]', function() {
		
		var count_window = $('.window_message').length;
		if (count_window > 0) {
			
			$(document).JsWindow('show', {
				window_class: 'update_window',
				close_class: 'js_direction_window_close_no',
				title: '<?php _e('Attention!', 'pn'); ?>',
				content: $('.window_message').html(),
				shadow: 1,
				enable_button: 1,
				button_title: '<?php _e('OK', 'pn'); ?>',
				button_class: 'js_window_close js_direction_window_close'
			});			
			
			return false;
		} 
		
	});
	
    $(document).on('click', '.js_direction_window_close', function() {
		
		$('.ajax_post_bids').submit();
		
    });	
	
	var res_timer = '';
	function start_res_timer() {
		
		$('.res_timer').html('0');
		clearInterval(res_timer);
		
		res_timer = setInterval(function() { 
			if ($('.res_timer').length > 0) {
				var num_t = parseInt($('.res_timer').html());
				num_t = num_t + 1;
				$('.res_timer').html(num_t);				
			}
		}, 1000);
	}
	
    $('.ajax_post_bids').ajaxForm({
        dataType:  'json',
		beforeSubmit: function(a, f, o) {
			
			f.addClass('thisactive');
			$('.thisactive input[type=submit], .thisactive input[type=button]').attr('disabled', true);
			$('.ajax_post_bids_res').html('<div class="resulttrue"><?php echo esc_attr(__('Processing. Please wait', 'pn')); ?> (<span class="res_timer">0</span>)</div>');
			
			start_res_timer();
			
			$('.ajax_post_bids_res').find('.js_wrap_error').removeClass('error');
			
			<?php do_action('ajax_post_form_process', 'site', 'bidsform'); ?>
        },
		error: function(res, res2, res3) {
			
			$('.ajax_post_bids_res').html('<div class="resultfalse"><?php echo esc_attr(__('Script error', 'pn')); ?></div>');
			<?php do_action('pn_js_error_response', 'form'); ?>
			
		},		
        success: function(res) { 
		
			if (res['error_fields']) {
				$.each(res['error_fields'], function(index, value) {
					add_error_field(index, value);
				});					
			}		
			
			if (res['status'] && res['status'] == 'error') {
				$('.ajax_post_bids_res').html('<div class="resultfalse"><div class="resultclose"></div>' + res['status_text'] + '</div>');
				if ($('.js_wrap_error.error').length > 0) {
					var ftop = $('.js_wrap_error.error:first').offset().top - 100;
					$('body, html').animate({scrollTop: ftop}, 500);
				}
			}
			
			if (res['status'] && res['status'] == 'success') {
				$('.ajax_post_bids_res').html('<div class="resulttrue"><div class="resultclose"></div>' + res['status_text'] + ' (<span class="res_timer">0</span>)</div>');
				start_res_timer();
			}				
		
			if (res['url']) {
				window.location.href = res['url']; 
			}
			
			<?php do_action('ajax_post_form_result', 'site', 'bidsform'); ?>
		
		    $('.thisactive input[type=submit], .thisactive input[type=button]').attr('disabled', false);
			$('.thisactive').removeClass('thisactive');
			
        }
    });	
	
	function add_error_field(id, text) {
		
		$('.js_' + id).parents('.js_wrap_error').addClass('error');
		$('.js_' + id).parents('.js_line_wrapper').show();
		if (text.length > 0) {
			$('.js_' + id + '_error').html(text).show();
		}
		
	}	
	function remove_error_field(id) {
		
		$('.js_' + id).parents('.js_wrap_error').removeClass('error');
		
	}	

	function calc_set_value(the_obj, the_num) {
		
		<?php echo apply_filters('js_calc_set_value', '$(the_obj).val(the_num);'); ?>
	}
	
	function calc_set_html(the_obj, the_num) {
		
		<?php echo apply_filters('js_calc_set_html', '$(the_obj).html(the_num);'); ?>
	}	
	
	function collect_other_data() {
		
		var set_data = 'sd=1';
		$('.js_changecalc').each(function() {
			var nv = $(this).attr('name');
			if (nv.length > 0) {
				var tv = $(this).attr('type');
				var vv = $(this).val();
				if (tv == 'checkbox') {
					if (!$(this).prop('checked')) {
						vv = 0;
					}
				} 
				set_data = set_data + '&' + nv + '=' + vv;
			}
		});	
		
		return encodeURIComponent(set_data);
	}
	
	function go_calc(obj, dej) {
		
		var cd = collect_other_data();
		var sum = obj.val().replace(/,/g,'.');
		var id = $('.js_direction_id:first').val();
		var param = <?php echo apply_filters("go_exchange_calc_js","'id=' + id + '&sum=' + sum + '&dej=' + dej + '&cd=' + cd"); ?>;
		
		<?php do_action('exchange_before_calc'); ?>
		
		$('.exch_ajax_wrap_abs, .js_exchange_widget_abs, .js_loader').show();
		
		if (dej == 1) {
			calc_set_value('input.js_sum1:not(:focus)', sum);
			calc_set_html('.js_sum1_html', sum);
		} else if (dej == 2) {
			calc_set_value('input.js_sum2:not(:focus)', sum);
			calc_set_html('.js_sum2_html', sum);					
		} else if (dej == 3) {
			calc_set_value('input.js_sum1c:not(:focus)', sum);
			calc_set_html('.js_sum1c_html', sum);
		} else if (dej == 4) {
			calc_set_value('input.js_sum2c:not(:focus)', sum);
			calc_set_html('.js_sum2c_html', sum);				
		}

		remove_error_field('sum1');
		remove_error_field('sum2');
		remove_error_field('sum1c');
		remove_error_field('sum2c');		
		
        $.ajax({
            type: "POST",
            url: "<?php echo get_pn_action('exchange_calculator'); ?>",
            data: param,
	        dataType: 'json',
 			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},           
			success: function(res) { 
			
				var changed = res['changed'];
			
				if (dej !== 1 || changed == 1) {
					calc_set_value('input.js_sum1', res['sum1']);
				}
				
				if (dej !== 2 || changed == 1) {
					calc_set_value('input.js_sum2', res['sum2']);	
				}
				
				if (dej !== 3 || changed == 1) {
					calc_set_value('input.js_sum1c', res['sum1c']);
				}
				
				if (dej !== 4 || changed == 1) {
					calc_set_value('input.js_sum2c', res['sum2c']);
				}

				calc_set_html('.js_sum1_html', res['sum1']);
				calc_set_html('.js_sum2_html', res['sum2']);
				calc_set_html('.js_sum1c_html', res['sum1c']);
				calc_set_html('.js_sum2c_html', res['sum2c']);								
				
				$('.js_comis_text1').html(res['comis_text1']);
				$('.js_comis_text2').html(res['comis_text2']);				
				
				if (res['error_fields']) {
					$.each(res['error_fields'], function(index, value) {
						add_error_field(index, value);
					});					
				}				
				
				if (res['course_html'] && res['course_html'].length > 0) {
					$('.js_course_html').html(res['course_html']);
					$('input.js_course_html').val(res['course_html']);
				}			
				
				if (res['reserve_html'] && res['reserve_html'].length > 0) {
					$('.js_reserve_html').html(res['reserve_html']);
					$('input.js_reserve_html').val(res['reserve_html']);
				}
				
				if (res['user_discount'] && res['user_discount'].length > 0) {
					$('.js_direction_user_discount').html(res['user_discount']);
					$('input.js_direction_user_discount').html(res['user_discount']);
				}	
				
				if (res['viv_com1'] && res['viv_com1'] == 1) {
					$('.js_viv_com1').show();
				} else {
					$('.js_viv_com1').hide();
				}
				
				if (res['viv_com2'] && res['viv_com2'] == 1) {
					$('.js_viv_com2').show();
				} else {
					$('.js_viv_com2').hide();
				}			

				<?php do_action('go_exchange_calc_js_response'); ?>
				
				$('.exch_ajax_wrap_abs, .js_exchange_widget_abs, .js_loader').hide();
            }
		});	
		
	}

	<?php
	$changetime = intval(apply_filters('calc_changetime', 1600));
	?>

	$(document).ChangeInput({ 
		trigger: '.js_sum1',
		changetime: '<?php echo $changetime; ?>',
		success: function(obj) {
			go_calc(obj, 1);
		}
	});

	$(document).ChangeInput({ 
		trigger: '.js_sum2',
		changetime: '<?php echo $changetime; ?>',
		success: function(obj) {
			go_calc(obj, 2);
		}
	});

	$(document).ChangeInput({ 
		trigger: '.js_sum1c',
		changetime: '<?php echo $changetime; ?>',
		success: function(obj) {
			go_calc(obj, 3);
		}
	});

	$(document).ChangeInput({ 
		trigger: '.js_sum2c',
		changetime: '<?php echo $changetime; ?>',
		success: function(obj) {
			go_calc(obj, 4);
		}
	});
	
	$(document).on('change','.js_changecalc',function() {
		go_calc($('.js_sum1'), 1);
	});		

	function set_input_decimal(obj) {
		
		var dec = obj.attr('data-decimal');
		var sum = obj.val().replace(new RegExp(",",'g'), '.');
		var len_arr = sum.split('.');
		var len_data = len_arr[1];
		if (len_data !== undefined) {
			var len = len_data.length;
			if (len > dec) {
				var new_data = len_data.substr(0, dec);
				obj.val(len_arr[0] + '.' + new_data);
			}
		}	
		
	}	
	
	$(document).on('change', '.js_decimal', function() {
		set_input_decimal($(this));
	});

	$(document).on('keyup', '.js_decimal', function() {
		set_input_decimal($(this));
	});
	
	<?php do_action('exchange_action_jquery'); ?>
});	
<?php	
} 
 
add_action('premium_siteaction_create_bid', 'def_premium_siteaction_create_bid');
function def_premium_siteaction_create_bid() {
	global $wpdb, $premiumbox;	
	
	_method('post');
	_json_head(); 
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error','pn');
	$log['error_fields'] = array();

	$log = _log_filter($log, 'exchangeform');

	$show_data = pn_exchanges_output('exchange');
	if (1 != $show_data['work']) {
		$error_text = __('Maintenance', 'pn');
		if ($show_data['text']) {
			$error_text = $show_data['text'];
		}
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = $error_text;
		echo pn_json_encode($log);
		exit;
	}

	$hidecheckrule = intval($premiumbox->get_option('exchange', 'hidecheckrule'));
	if (!$hidecheckrule) {
		
		$check_rule = intval(is_param_post('check_rule'));
		$enable_step2 = intval($premiumbox->get_option('exchange', 'enable_step2'));
		if (!$check_rule and 0 == $enable_step2) {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Error! You have not accepted the terms and conditions of the site', 'pn');
			echo pn_json_encode($log);
			exit;		
		}
		
	}
	
	if ($log['status_code'] < 1) {
		
		$enable_step2 = intval($premiumbox->get_option('exchange', 'enable_step2'));
		$create_new = 0;
		if (0 == $enable_step2) {
			$create_new = 1;
		}
		
		$info = _create_bid_auto(is_param_post('sum1'), 1, $create_new, 'exchange_button');
		if ($info['error']) {
			
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = $info['status_text'];
			$log['error_fields'] = $info['error_fields'];
			
		} else {
			
			$log['url'] = $info['data']['url'];
			$log['status'] = 'success';
			$log['status_text'] = $info['status_text'];
			
		}
	}	
	
	echo pn_json_encode($log);
	exit;
}

add_action('premium_siteaction_confirm_bid', 'def_premium_siteaction_confirm_bid');
function def_premium_siteaction_confirm_bid() {
	global $wpdb, $premiumbox;	
	
	_method('post');
	_json_head(); 
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error', 'pn');	

	$log = _log_filter($log, 'confirmbid');

	$show_data = pn_exchanges_output('exchange');
	if (1 != $show_data['work']) {
		$error_text = __('Maintenance', 'pn');
		if (strlen($show_data['text']) > 0) {
			$error_text = $show_data['text'];
		}
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = $error_text;
		echo pn_json_encode($log);
		exit;
	}
	
	$hidecheckrule = intval($premiumbox->get_option('exchange', 'hidecheckrule'));
	if (!$hidecheckrule) {
		
		$check_rule = intval(is_param_post('check_rule'));
		if (!$check_rule) {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Error! You have not accepted the terms and conditions of the User Agreement', 'pn');
			echo pn_json_encode($log);
			exit;		
		}	
		
	}
	
	$enable_step2 = intval($premiumbox->get_option('exchange', 'enable_step2'));
	if (1 != $enable_step2) {
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Error! Step disabled', 'pn');
		echo pn_json_encode($log);
		exit;		
	}
	
	$hashed = is_bid_hash(is_param_post('hash'));
	
	if (!$hashed) {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! System error', 'pn');
		echo pn_json_encode($log);
		exit;		
	}
	
	$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE hashed = '$hashed' AND status = 'auto'");
	if (!isset($item->id)) {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! System error', 'pn');
		echo pn_json_encode($log);
		exit;		
	}
	
	if (!is_true_userhash($item)) {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! You cannot control this order in another browser', 'pn');
		echo pn_json_encode($log);
		exit;		
	}	
	
	$currency_id_give = intval($item->currency_id_give);
	$currency_id_get = intval($item->currency_id_get);
	
	$vd1 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' AND id = '$currency_id_give' AND currency_status = '1'");
	$vd2 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' AND id = '$currency_id_get' AND currency_status = '1'");

	if (!isset($vd1->id) or !isset($vd2->id)) {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! System error', 'pn');
		echo pn_json_encode($log);
		exit;		
	}

	$direction_id = intval($item->direction_id);
	
	$direction_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE direction_status = '1' AND auto_status = '1' AND id = '$direction_id'");
	if (!isset($direction_data->id)) {
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Error! The direction do not exist', 'pn');
		echo pn_json_encode($log);
		exit;		
	}
	
	$direction = array();
	foreach ($direction_data as $direction_key => $direction_val) {
		$direction[$direction_key] = $direction_val;
	}
	
	$naps_meta = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions_meta WHERE item_id = '$direction_id'");
	foreach ($naps_meta as $naps_item) {
		$direction[$naps_item->meta_key] = $naps_item->meta_value;
	}	
	$direction = (object)$direction; 		
	
	if ($log['status_code'] < 1) {
	
		$res = _create_bid_new($item, $direction, $vd1, $vd2, 1, 'exchange_button');
		if ($res['error']) {

			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = $res['error_text'];

		} else {
		
			$log['url'] = get_bids_url($item->hashed);
			$log['status'] = 'success';
			$log['status_text'] = __('Your order successfully created', 'pn');

		}

	}
	
	echo pn_json_encode($log);
	exit;
}

add_action('premium_siteaction_canceledbids', 'def_premium_siteaction_canceledbids');
function def_premium_siteaction_canceledbids() {
	global $wpdb;	
	
	$error_text = __('No bid exists', 'pn');
	
	$show_data = pn_exchanges_output('exchange');
	if (1 == $show_data['work']) {
		$hashed = is_bid_hash(is_param_get('hash'));
		if ($hashed) {
			$st = get_status_sett('cancel');
			$bids_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE hashed = '$hashed' AND status IN($st)");	
			if (isset($bids_data->id)) {
				if (is_true_userhash($bids_data)) {
					$direction_id = intval($bids_data->direction_id);
					$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE direction_status = '1' AND auto_status = '1' AND id = '$direction_id'");
					if (isset($direction->id)) {					
						$allow = apply_filters('allow_canceledbids', 1, $bids_data, $direction);
						if ($allow) {
							
							$arr = array('status' => 'cancel', 'edit_date' => current_time('mysql'));
							$result = $wpdb->update($wpdb->prefix . 'exchange_bids', $arr, array('id' => $bids_data->id));
							if ($result) {
								
								$old_status = $bids_data->status;
								$bids_data = pn_object_replace($bids_data, $arr);
								
								$ch_data = array(
									'bid' => $bids_data,
									'set_status' => 'cancel',
									'place' => 'exchange_button',
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
			
			$url = get_bids_url($hashed);
			wp_redirect($url);
			exit;			
		}
	} else {
		$error_text = __('Maintenance', 'pn');
		if (strlen($show_data['text']) > 0) {
			$error_text = $show_data['text'];
		}		
	}
	
	pn_display_mess($error_text, $error_text);
}

add_action('premium_siteaction_payedbids', 'def_premium_siteaction_payedbids');
function def_premium_siteaction_payedbids() { 
	global $wpdb, $premiumbox, $bids_data;
	
	$error_text = __('No bid exists', 'pn');
	
	$show_data = pn_exchanges_output('exchange');
	if (1 == $show_data['work']) {
		$hashed = is_bid_hash(is_param_get('hash'));
		if ($hashed) {
			$st = get_status_sett('payed');
			$bids_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."exchange_bids WHERE hashed = '$hashed' AND status IN($st)");	
			if (isset($bids_data->id)) {
				if (is_true_userhash($bids_data)) {
					$direction_id = intval($bids_data->direction_id);
					$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE direction_status = '1' AND auto_status = '1' AND id = '$direction_id'");
					if (isset($direction->id)) {					
						$allow = apply_filters('allow_payedbids', 1, $bids_data, $direction);
						if ($allow) {

							$arr = array('status' => 'payed', 'edit_date' => current_time('mysql'));
							$result = $wpdb->update($wpdb->prefix . 'exchange_bids', $arr, array('id' => $bids_data->id));
							if ($result) {
								
								$old_status = $bids_data->status;
								$bids_data = pn_object_replace($bids_data, $arr);
									
								$ch_data = array(
									'bid' => $bids_data,
									'set_status' => 'payed',
									'place' => 'exchange_button',
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
			
			$url = get_bids_url($hashed);
			wp_redirect($url);
			exit;			
		}
	} else {
		$error_text = __('Maintenance', 'pn');
		if (strlen($show_data['text']) > 0) {
			$error_text = $show_data['text'];
		}		
	}
	
	pn_display_mess($error_text, $error_text);	
}  