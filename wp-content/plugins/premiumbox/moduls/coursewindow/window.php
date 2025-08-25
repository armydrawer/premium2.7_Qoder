<?php
if (!defined('ABSPATH')) { exit(); }

add_action('premium_js', 'premium_js_courselogs', 1000);
function premium_js_courselogs() {	
	global $wpdb, $premiumbox;
	
	$out_bids = intval($premiumbox->get_option('courselogs', 'out_bids'));
	$out_course = intval($premiumbox->get_option('courselogs', 'out_course'));
	if (1 == $out_bids or 1 == $out_course) {
?>	
jQuery(function($) { 

	$('#last_events_option').on('change', function() {
		if ($(this).prop('checked')) {
			var hidecourselogs = 1;
			$('.last_events').hide();
		} else {
			var hidecourselogs = 0;
			$('.last_events').show();
		}
		$(document).PHPCookie('set', {key: "hidecourselogs", value: hidecourselogs, domain: '<?php echo PN_SITE_URL; ?>', days: '30'});
	});
	
	$(document).on('click', '.levents_close', function() {
		$(this).parents('.levents').hide();
	});
	
});	
<?php	
	}
}

add_action('wp_footer', 'wp_footer_courselogs');
function wp_footer_courselogs() {
	global $wpdb, $premiumbox;	

	$out_bids = intval($premiumbox->get_option('courselogs', 'out_bids'));
	$out_course = intval($premiumbox->get_option('courselogs', 'out_course'));
	if (!function_exists('is_mobile') or function_exists('is_mobile') and !is_mobile()) {
		if (1 == $out_bids or 1 == $out_course) {	
			$hidecourselogs = intval(get_pn_cookie('hidecourselogs'));
			$style = '';
			if (1 == $hidecourselogs) {
				$style = 'style="display: none;"';
			}
			$place = intval($premiumbox->get_option('courselogs','place'));
			$cl = '';
			if (1 == $place) {
				$cl = 'toright';
			}
	?>
	<div class="last_events_wrap only_web <?php echo $cl; ?>">
		<div class="last_events_div">
			<div id="last_events" class="last_events" <?php echo $style; ?>></div>
			<div class="last_events_option">
				<label><input type="checkbox" name="leven" <?php checked($hidecourselogs, 1); ?> id="last_events_option" autocomplete="off" value="1" /> <?php _e('Disable notifications', 'pn'); ?></label>
			</div>
		</div>
	</div>
	<?php  
		}
	}
}

add_action('go_exchange_calc_js_response', 'courselogs_go_exchange_calc_js_response');
function courselogs_go_exchange_calc_js_response() {
?>
	if (res['curs_give_html'] && res['curs_give_html'].length > 0) {
		$('.js_curs_give_html').html(res['curs_give_html']);
		$('input.js_curs_give_html').val(res['curs_give_html']);
	}
	
	if (res['curs_get_html'] && res['curs_get_html'].length > 0) {
		$('.js_curs_get_html').html(res['curs_get_html']);
		$('input.js_curs_get_html').val(res['curs_get_html']);
	}	
<?php	
} 
 
add_filter('log_exchange_changes', 'courselogs_log_exchange_changes', 10, 3);
function courselogs_log_exchange_changes($log, $cdata, $calc_data) {
	
	$log['curs_give_html'] = is_isset($cdata, 'course_give') . ' ';
	$log['curs_get_html'] = is_isset($cdata, 'course_get') . ' ';
	
	return $log;
}

add_filter('exchange_html_list', 'courselogs_exchange_html_list', 10, 5);
add_filter('exchange_html_list_ajax', 'courselogs_exchange_html_list', 10, 5);
function courselogs_exchange_html_list($array, $direction, $vd1, $vd2, $cdata) {
	
	$course_data = '
	<input type="hidden" name="" class="js_curs_give_html" value="' . is_isset($cdata, 'course_give') . '" / >
	<input type="hidden" name="" class="js_curs_get_html" value="' . is_isset($cdata, 'course_get') . '" / >
	';
	
	if (isset($array['[result]'])) {
		$array['[result]'] .= $course_data;
	}
	
	return $array;
}

add_filter('globalajax_site_request', 'globalajax_site_request_courselogs');
function globalajax_site_request_courselogs($params) {
	
	$time = current_time('timestamp');

	$params .= "+ '&ltime={$time}' + '&dirid=' + $('.js_direction_id').val() + '&lcourse1=' + $('.js_curs_give_html').val() + '&lcourse2=' + $('.js_curs_get_html').val()";

	return $params;
}

add_filter('globalajax_site_data', 'globalajax_site_data_courselogs');
function globalajax_site_data_courselogs($log) {
	global $wpdb, $premiumbox;
	
	$courselogs = array();
	$count = 0;
	$hidecourselogs = intval(get_pn_cookie('hidecourselogs'));
	if (1 != $hidecourselogs) {
		$now_time = current_time('timestamp');
		$yesterday = $now_time - (12 * 60 * 60);
		$ltime = intval(is_param_post('ltime'));
		if ($ltime < $yesterday) { $ltime = $yesterday; }
		$ldate = date('Y-m-d H:i:s', $ltime);
		
		$count = intval($premiumbox->get_option('courselogs', 'count')); 
		if ($count < 1) { $count = 3; }
		
		$v = get_currency_data();
		
		$dir_id = intval(is_param_post('dirid'));
		$lcourse1 = is_sum(is_param_post('lcourse1'));
		$lcourse2 = is_sum(is_param_post('lcourse2'));
		
		$nlogs = array();
		$out_bids = intval($premiumbox->get_option('courselogs', 'out_bids'));
		$out_course = intval($premiumbox->get_option('courselogs', 'out_course'));
		if (1 == $out_bids) {
			$bids = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'success' AND edit_date >= '$ldate' ORDER BY edit_date DESC LIMIT 1");
			foreach ($bids as $bid) {
				$time = strtotime($bid->edit_date);
				$nlogs[$time][] = array(
					'type' => 'bid',
					'date' => $bid->edit_date,
					'data' => $bid,
				);
			}
		}
		if (1 == $out_course and $dir_id > 0 and $lcourse1 > 0 and $lcourse2 > 0) {
			$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE direction_status IN('1','2') AND auto_status = '1' AND id = '$dir_id'");
			if (isset($direction->id)) {
				$currency_id_give = intval($direction->currency_id_give);
				$currency_id_get = intval($direction->currency_id_get);
				if (isset($v[$currency_id_give]) and isset($v[$currency_id_get])) {
					$dir_c = is_course_direction($direction, $v[$currency_id_give], $v[$currency_id_get], 'coursewindow');
					$course_give = is_isset($dir_c, 'give'); 
					$course_get = is_isset($dir_c, 'get');				
					if ($course_give != $lcourse1 or $course_get != $lcourse2) {
						$nlogs[$now_time][] = array(
							'type' => 'course',
							'data' => $direction,
							'course1' => $course_give,
							'course2' => $course_get,
							'lcourse1' => $lcourse1,
							'lcourse2' => $lcourse2,
						);
					}
				}
			}
		}
		
		krsort($nlogs); //ksort
		
		$nlogs_work = array();
		foreach ($nlogs as $nlog) {
			foreach ($nlog as $nl) { 
				$nlogs_work[] = $nl;
			}
		}	
		
		foreach ($nlogs_work as $item) {
			$html = '';
			$vd1 = $vd2 = '';
			if ('course' == $item['type']) {
				$key = str_replace('.', '', 'c_' . $item['course1'] . '_' . $item['course2']);
				$title = __('Exchange rate has changed', 'pn');
				if (isset($v[$item['data']->currency_id_give]) and isset($v[$item['data']->currency_id_get])) {
					$vd1 = $v[$item['data']->currency_id_give];
					$vd2 = $v[$item['data']->currency_id_get];
					$toup_class = '';
					$c1 = $item['course1'];
					$c2 = $item['course2'];
					$lc1 = $item['lcourse1'];
					$lc2 = $item['lcourse2'];					
					$nc1 = $nc2 = 0;
					if ($c1 == $lc1) {
						$nc1 = $c2;
						$nc2 = $lc2;
					} else {
						$nc1 = $c1;
						$nc2 = $lc1;						
					}
					if ($nc1 > $nc2) {
						$toup_class = 'levents_up';
					} else {
						$toup_class = 'levents_down';
					}
					
					$html = '
					<div class="levents levents_' . $item['type'] . ' ' . $toup_class . '" id="levents_' . $key . '">
						<div class="levents_close"></div>
						<div class="levents_ins">
							<div class="levents_title">' . $title . '</div>
							<div class="levents_line"><span>' . is_out_sum($item['course1'], $vd1->currency_decimal, 'course') . '</span> ' . get_currency_title($vd1) . '</div>
							<div class="levents_arr"></div>
							<div class="levents_line"><span>' . is_out_sum($item['course2'], $vd2->currency_decimal, 'course') . '</span> ' . get_currency_title($vd2) . '</div>
						</div>
					</div>				
					';		
				}
			} elseif ('bid' == $item['type']) {
				$key = 'b_' . $item['data']->id;
				$title = __('Exchange completed', 'pn');
				if (isset($v[$item['data']->currency_id_give]) and isset($v[$item['data']->currency_id_get])) {
					$vd1 = $v[$item['data']->currency_id_give];
					$vd2 = $v[$item['data']->currency_id_get];				
					$html = '
					<div class="levents levents_' . $item['type'] . '" id="levents_' . $key . '">
						<div class="levents_close"></div>
						<div class="levents_ins">
							<div class="levents_title">' . $title . '</div>
							<div class="levents_line"><span>' . is_out_sum(is_sum($item['data']->sum1dc), $vd1->currency_decimal, 'course') . '</span> ' . get_currency_title($vd1) . '</div>
							<div class="levents_arr"></div>
							<div class="levents_line"><span>' . is_out_sum(is_sum($item['data']->sum2c), $vd1->currency_decimal, 'course') . '</span> ' . get_currency_title($vd2) . '</div>
						</div>
					</div>				
					';	
				}
			}
			$html = apply_filters('courselogs_list', $html, $item['type'], $item['data'], $vd1, $vd2);
			if ($html) {
				$courselogs[$key] = $html;
			}
			break;
		}
	}
	
	$log['courselogs'] = $courselogs;
	$log['courselogs_count'] = $count;
	
	return $log;
}

add_action('globalajax_site_result', 'globalajax_site_result_courselogs');
function globalajax_site_result_courselogs() {
?>	
	var c_count = res['courselogs_count'];
	var courselogs = res['courselogs'];
	for (var c_key in courselogs){
		var c_data = courselogs[c_key];
		if ($('#levents_' + c_key).length == 0) {
			$('#last_events').append(c_data);
			var c_len = $('#last_events .levents').length;	
			$('#levents_' + c_key).fadeIn(800);
			if (c_len > c_count) {
				$('.levents:first').remove();
			}
		}		
	}
<?php	
} 