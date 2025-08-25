<?php
if (!defined('ABSPATH')) { exit(); } 

add_action('premium_js','premium_js_exchange_table2');
function premium_js_exchange_table2() {
	
	if (2 == get_type_table()) {
?>	
jQuery(function($) {
 	
	function start_icon() {
		
		$(".js_icon_left:hidden").each(function() {
			var id = $(this).attr('data-type');
			var len = $('.js_item_left.js_vtype_' + id).length;
			if (1 > len) {
				$(this).remove();
			}
		});
		
		$(".js_icon_left").show();
		
		$(".js_icon_right:hidden").each(function() {
			var id = $(this).attr('data-type');
			var len = $('.js_item_right.js_vtype_' + id).length;
			if (1 > len) {
				$(this).remove();
			}
		});
		
		$(".js_icon_right").show();
		
	}
	
	start_icon();
	 
    $(document).on('click', ".js_icon_left", function () {
		
        if (!$(this).hasClass('active')) {
			
			var vtype = $(this).attr('data-type');
			$(".js_icon_left").removeClass('active');
			$(this).addClass('active');
	
			if (vtype == 0) {
				$('.js_item_left').removeClass('not');
			} else {
				$('.js_item_left').addClass('not');
				$('.js_item_left.js_vtype_' + vtype).removeClass('not');
			}
			
        }
		
        return false;
    });
	
    $(document).on('click', ".js_icon_right", function () {
		
        if (!$(this).hasClass('active')) {
		    
			var vtype = $(this).attr('data-type');
			$(".js_icon_right").removeClass('active');
			$(this).addClass('active');
	
			if (vtype == 0) {
				$('.js_item_right').removeClass('not');
			} else {
				$('.js_item_right').addClass('not');
				$('.js_item_right.js_vtype_' + vtype).removeClass('not');
			}
			
        }
		
        return false;
    });		

function go_change_ps(ind) {
	
	var id1 = $('.js_item_left.active').attr('data-id');
	var id2 = $('.js_item_right.active').attr('data-id');
	
	var c1 = $('.js_icon_left.active').attr('data-id');
	var c2 = $('.js_icon_right.active').attr('data-id');
	
	if (ind == 2) {
		var cur1 = $(".js_item_sel1").val();
		var cur2 = $(".js_item_sel2").val();
	} else {
		var cur1 = 0;
		var cur2 = 0;
	}		
	
	$('.js_loader').show();
	
	var param = 'id1=' + id1 + '&id2=' + id2 + '&cur1=' + cur1 + '&cur2=' + cur2 + '&c1=' + c1 + '&c2=' + c2<?php echo apply_filters('param_ajax_exchange_table2', ''); ?>;
    $.ajax({
        type: "POST",
        url: "<?php echo get_pn_action('table2_change'); ?>",
        dataType: 'json',
		data: param,
 		error: function(res, res2, res3) {
			<?php do_action('pn_js_error_response', 'ajax'); ?>
		},      
		success: function(res)
        {
			$('.js_loader').hide();
			if (res['status'] == 'success') {
				$('#js_html').html(res['html']);
				
				<?php do_action('live_change_html'); ?>
			} 
			
			if (res['status'] == 'error') {
				$('#js_html').html('<div class="xtp_error"><div class="xtp_error_ins">' + res['status_text'] + '</div></div>');
			}

			if ($('#hexch_html').length > 0) {
				$('#hexch_html').html('');
			}			
        }
    });
	
    return false;	
}	

    $(document).on('click', ".js_item_left", function () {
		
        if (!$(this).hasClass('active')) {
			$(".js_item_left").removeClass('active');
			$(this).addClass('active');
			go_change_ps(1);
        }
		
        return false;
    });	
	
    $(document).on('click', ".js_item_right", function () {
		
        if (!$(this).hasClass('active')) {
			$(".js_item_right").removeClass('active');
			$(this).addClass('active');
			go_change_ps(1);
        }
		
        return false;
    });	
	
    $(document).on('change', ".js_item_sel", function () {
		
		go_change_ps(2);
		
        return false;
    });	
	
	$(document).on('click', '#js_submit_button', function() {
		
		if ($(this).hasClass('active')) {
			return false;
		}
		
	});
});	
<?php	
	}
	
}

add_action('premium_siteaction_table2_change', 'def_premium_siteaction_table2_change');
function def_premium_siteaction_table2_change() {
	global $wpdb, $premiumbox;	
	
	_json_head();
	_method('post'); 
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = '0'; 
	$log['status_text']= '';
		
	$premiumbox->up_mode('post');	
		
	if (2 == get_type_table()) {	
		
		define('exchange_table_ajax', 2);

		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);
		
		$pid1 = intval(is_param_post('id1'));
		$pid2 = intval(is_param_post('id2'));
		$cur1 = intval(is_param_post('cur1'));
		$cur2 = intval(is_param_post('cur2'));
		$c1 = intval(is_param_post('c1'));
		$c2 = intval(is_param_post('c2'));
		
		$v = get_currency_data();	
		
		$direction = '';
		
		$r = 0;
		$where = get_directions_where("home");
		$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where AND psys_id_give = '$pid1' AND psys_id_get = '$pid2'");
		foreach ($directions as $dir) { 
			$output = apply_filters('get_direction_output', 1, $dir, 'home');
			if ($output) { 
				$currency_id_give = $dir->currency_id_give;
				$currency_id_get = $dir->currency_id_get;
				if (isset($v[$currency_id_give], $v[$currency_id_get])) {  $r++;
					$vd1 = $v[$currency_id_give];
					$vd2 = $v[$currency_id_get];
					
					if (1 == $r) {
						$direction = $dir;
					}
					
					if ($cur1 > 0 and $cur2 > 0) {
						if ($cur1 == $vd1->currency_code_id and $cur2 == $vd2->currency_code_id) {
							$direction = $dir;
							break;
						}	
					} elseif ($c1 > 0 and $c2 > 0) {
						if ($c1 == $vd1->currency_code_id and $c2 == $vd2->currency_code_id) {
							$direction = $dir;
							break;
						}
					} elseif ($c1 > 0) {
						if ($c1 == $vd1->currency_code_id) {
							$direction = $dir;
							break;
						} 
					} elseif ($c2 > 0) {
						if ($c2 == $vd2->currency_code_id) {
							$direction = $dir;
							break;
						}						
					}					
				}
			}
		}
		
		$log['status'] = 'success';
		$log['html'] = get_xtp_temp($direction, $pid1, $pid2, $c1, $c2);
	}
	
	echo pn_json_encode($log);
	exit;
}

add_filter('exchange_table_type2','get_exchange_table2', 10, 3);
function get_exchange_table2($temp, $def_cur_from = '', $def_cur_to = '') {
	global $wpdb, $premiumbox;	

	$temp = '';
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);

	$cur_from = strtoupper(trim($def_cur_from));
	$cur_to = strtoupper(trim($def_cur_to));
	
	$v = get_currency_data();

	$all_vtypes = array();
	foreach ($v as $vs) {
		$all_vtypes[str_replace('.', '_', $vs->currency_code_title)] = $vs->currency_code_id;
	}	
	
	$direction = '';
	
	$where = get_directions_where('home');
	$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where");
	$r = 0;
	foreach ($directions as $dir) { 
		$output = apply_filters('get_direction_output', 1, $dir, 'home');	
		if ($output) {
			$currency_id_give = $dir->currency_id_give;
			$currency_id_get = $dir->currency_id_get;
			if (isset($v[$currency_id_give], $v[$currency_id_get])) { $r++;
				$vd1 = $v[$currency_id_give];
				$vd2 = $v[$currency_id_get];
			
				if (1 == $r) {
					$direction = $dir;
				}
				
				if (
					$cur_from and $cur_from == strtoupper($vd1->xml_value) and $cur_to and $cur_to == strtoupper($vd2->xml_value)
					or $cur_from and $cur_from == strtoupper($vd1->xml_value) and !$cur_to
					or $cur_to and $cur_to == strtoupper($vd2->xml_value) and !$cur_from
				) {	
					$direction = $dir;	
				}
			}	
		}
	}
		
	$temp .= '
	<div class="xchange_type_plitka">
		<div class="xchange_type_plitka_ins">';				
				
			$exchange_table_head = '';
			$hidecurrtype = get_hidecurrtype_table();		
			if (0 == $hidecurrtype) {
					
				$exchange_table_head ='
				<div class="xtp_icon_wrap">
					<div class="xtp_left_col_icon">
					
						<div class="tbl_icon active js_icon_left" data-type="0" data-id="0"><div class="tbl_icon_ins"><div class="tbl_icon_abs"></div>' . __('All', 'pn') . '</div></div>
						';
					
						foreach ($all_vtypes as $av => $cid) {
							$exchange_table_head .= '<div class="tbl_icon js_icon_left js_icon_left_' . $av . '" data-id="' . $cid . '" data-type="' . $av . '" style="display: none;"><div class="tbl_icon_ins"><div class="tbl_icon_abs"></div>' . $av . '</div></div>';
						}
					
						$exchange_table_head .= '
							<div class="clear"></div>
					</div>
					<div class="xtp_right_col_icon">

						<div class="tbl_icon active js_icon_right" data-type="0" data-id="0"><div class="tbl_icon_ins"><div class="tbl_icon_abs"></div>' . __('All', 'pn') . '</div></div>
						';
								
						foreach ($all_vtypes as $av => $cid) {
							$exchange_table_head .= '<div class="tbl_icon js_icon_right js_icon_right_' . $av . '" data-id="' . $cid . '" data-type="' . $av . '" style="display: none;"><div class="tbl_icon_ins"><div class="tbl_icon_abs"></div>' . $av . '</div></div>';
						}							
								
						$exchange_table_head .= '
							<div class="clear"></div>
					</div>
						<div class="clear"></div>
				</div>';
					
			}
				
			$temp .= apply_filters('exchange_table2_head', $exchange_table_head, $all_vtypes, $all_vtypes);
					
			$temp .='
			<div class="xtp_table_wrap">
				<div class="xtp_table_wrap_ins">';
				
					$temp .= '
					<div class="xtp_html_wrap">
						<div class="xtp_html_abs js_loader"></div>
						<div id="js_html">
					';
				
						$temp .= get_xtp_temp($direction);
				
						$temp .= '
						</div>
					</div>';				
					
					$temp .='
					<div id="js_error_div"></div>		
				</div>
			</div>';
				
		$temp .='	
		</div>
	</div>				
	';	
	
	return $temp;
}

add_filter('exchange_table2_part', 'def_exchange_table2_part', 10, 6);
function def_exchange_table2_part($temp, $title, $p, $place = '', $current = '', $c_id = '') {
	
	$place = trim($place);
	
	$temp = '
	<div class="xtp_table">
		<div class="xtp_table_ins">	
			<div class="xtp_table_title">
				<div class="xtp_table_title_ins">
					<span>' . $title . '</span>
				</div>
			</div>
				<div class="clear"></div>						
			<div class="xtp_table_list">
				<div class="xtp_table_list_ins">';
							
					$tableicon = get_icon_for_table();
						
					$new_array = array();
					foreach ($p as $psys_id => $data) {
						$now_data = $data['data'];
						if ('left' == $place) {
							$new_array[$psys_id] = intval($now_data->t2_1);
						} else {
							$new_array[$psys_id] = intval($now_data->t2_2);
						}
					}
					
					asort($new_array);
					
					$np = array();
					foreach ($new_array as $psys_id => $sort) {
						$np[$psys_id] = $p[$psys_id];
					}
						
					foreach ($np as $psys_id => $data) {
						
						$item = $data['data'];
						$cl = '';
						if ($current == $psys_id) {
							$cl .= ' active';
						}	

						$class = is_isset($data, 'class'); 
						if (!is_array($class)) { $class = array(); }
						$class = array_unique($class);
											
						$classes = implode(' ', $class);
						
						$cid = is_isset($data, 'cid'); 
						if (!is_array($cid)) { $cid = array(); }
						
						$c_id = intval($c_id);
						if ($c_id > 0) {
							if (!in_array($c_id, $cid)) {
								$cl .= ' not';
							}
						}	
											
						$temp .= '
						<div class="xtp_item js_item js_item_' . $place . ' ' . $cl . ' ' . $classes . '" data-id="' . $psys_id . '" title="' . pn_strip_input(ctv_ml($item->psys_title)) . '">
							<div class="xtp_item_ins">';

								$icon = '
								<div class="xtp_item_abs"></div>
								<div class="xtp_item_ico currency_logo" style="background-image: url(' . get_psys_logo($item, $tableicon) . ');"></div>
								';
								$temp .= apply_filters('exchange_table2_part_icon', $icon, $item);

							$temp .= '	
							</div>
						</div>
						';								
					} 
													
				$temp .= '
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</div>
	';
	
	return $temp;
}

function get_xtp_temp($direction, $psys1 = '', $psys2 = '', $cur1 = '', $cur2 = '') {
	global $wpdb, $premiumbox;	

	$temp = '
	<input type="hidden" name="" class="js_direction_id" value="' . is_isset($direction, 'id') . '" />
	';

	$submit_class = 'active';

	$v = get_currency_data();

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);

	$psys_id_give_current = intval($psys1);
	$psys_id_get_current = intval($psys2);
	
	if (isset($direction->id)) {
		$psys_id_give_current = $direction->psys_id_give;
		$psys_id_get_current = $direction->psys_id_get;
	}	

	$psys_items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "psys WHERE auto_status = '1'");
	$p = $p1 = $p2 = array();
	foreach ($psys_items as $psys) {
		$p[$psys->id] = $psys;
	}

	$field1 = $field2 = array();

	$cur1 = intval($cur1);
	$cur2 = intval($cur2);

	$where = get_directions_where('home');
	$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where");
	foreach ($directions as $dir) { 
		$output = apply_filters('get_direction_output', 1, $dir, 'home');	
		if ($output) {
			$currency_id_give = $dir->currency_id_give;
			$currency_id_get = $dir->currency_id_get;
			$psys_id_give = $dir->psys_id_give;
			$psys_id_get = $dir->psys_id_get;
			if (isset($v[$currency_id_give], $v[$currency_id_get], $p[$psys_id_give], $p[$psys_id_get])) { 
				$vd1 = $v[$currency_id_give];
				$vd2 = $v[$currency_id_get];
				
				$vt1 = str_replace('.', '_', is_site_value($vd1->currency_code_title));
				$vt2 = str_replace('.', '_', is_site_value($vd2->currency_code_title));
				
				$p1[$psys_id_give]['data'] = $p[$psys_id_give];
				$p2[$psys_id_give]['data'] = $p[$psys_id_give];
				$p1[$psys_id_get]['data'] = $p[$psys_id_get];
				$p2[$psys_id_get]['data'] = $p[$psys_id_get];

				//$p1[$psys_id_give]['class'][] = 'js_psys_' . $psys_id_get;
				$p1[$psys_id_give]['class'][] = 'js_vtype_' . $vt1;
				$p1[$psys_id_give]['cid'][] = $vd1->currency_code_id;
				//$p2[$psys_id_get]['class'][] = 'js_psys_' . $psys_id_give;
				$p2[$psys_id_get]['class'][] = 'js_vtype_' . $vt2;
				$p2[$psys_id_get]['cid'][] = $vd2->currency_code_id;
				
				if ($psys_id_give_current == $psys_id_give and $psys_id_get_current == $psys_id_get) {
					$field1[$vd1->currency_code_title] = $vd1->currency_code_id;
					$field2[$vd2->currency_code_title] = $vd2->currency_code_id;
				}				
			}
		}
	}	

	$left_col = $right_col = '';

	if (isset($direction->id)) {
		
		$submit_class = '';
		
		$val1 = $direction->currency_id_give;
		$val2 = $direction->currency_id_get;												
		
		$vd1 = $v[$val1];
		$vd2 = $v[$val2];
		
		$cur_id1 = $vd1->currency_code_id;
		$cur_id2 = $vd2->currency_code_id;				
		
		$post_sum = is_sum(is_param_get('give_sum'));
        $dej = 1;

        $amt_from = is_sum(is_param_get('amt_from'));
        $amt_to = is_sum(is_param_get('amt_to'));
        if ($post_sum <= 0 && ($amt_from || $amt_to)) {
            if ($amt_from) {
                $post_sum = $amt_from;
                $dej = 3;
            } elseif ($amt_to) {
                $post_sum = $amt_to;
                $dej = 4;
            }
        }

		if ($post_sum <= 0) {
			$post_sum = is_sum(get_pn_cookie('cache_sum'));
		}
		$post_sum = apply_filters('start_amount_give', $post_sum, $direction);
		$calc_data = array(
			'vd1' => $vd1,
			'vd2' => $vd2,
			'direction' => $direction,
			'user_id' => $user_id,
			'ui' => $ui,
			'post_sum' => $post_sum,
            'dej' => $dej,
		);
		$calc_data = apply_filters('get_calc_data_params', $calc_data, 'table2');		
		$cdata = get_calc_data($calc_data, 1);

		$reserve = is_out_sum($cdata['reserve'], $vd2->currency_decimal, 'reserv');

		$currency_code_give = $cdata['currency_code_give'];
		$currency_code_get = $cdata['currency_code_get'];										
		
		$course_give = $cdata['course_give'];			
		$course_get = $cdata['course_get'];		
		
		$viv_com1 = 'style="display: none;"'; 
		if (1 == $cdata['viv_com1']) {
			$viv_com1 = '';
		}
		
		$viv_com2 = 'style="display: none;"'; 
		if (1 == $cdata['viv_com2']) {
			$viv_com2 = '';
		}		
		
		$sum1_error = $sum2_error = $sum1c_error = $sum2c_error = '';
		$sum1_error_txt = $sum2_error_txt = $sum1c_error_txt = $sum2c_error_txt = '';
	
		$sum1 = is_sum(is_isset($cdata, 'sum1'));
		$sum1c = is_sum(is_isset($cdata, 'sum1c'));
		$sum2 = is_sum(is_isset($cdata, 'sum2'));
		$sum2c = is_sum(is_isset($cdata, 'sum2c'));	
	
		$hideerror = intval($premiumbox->get_option('exchange','tablehideerror'));
		if (!$hideerror) {
			
			$minmax_data = array(
				'min_give' => $cdata['min_give'],
				'max_give' => $cdata['max_give'],
				'min_get' => $cdata['min_get'],
				'max_get' => $cdata['max_get'],				
			);
	
			$dir_minmax = get_direction_minmax($direction, $vd1, $vd2, $cdata['course_give'], $cdata['course_get'], $cdata['reserve'], 'calculator', $minmax_data); 
			$min1 = is_isset($dir_minmax, 'min_give');
			$max1 = is_isset($dir_minmax, 'max_give');
			$min2 = is_isset($dir_minmax, 'min_get');
			$max2 = is_isset($dir_minmax, 'max_get');										
			
			if ($sum1 < $min1) {
				$sum1_error = 'error';
				$sum1_error_txt = '<span class="js_amount" data-id="sum1" data-val="' . $min1 . '">' . __('min', 'pn') . '.: ' . is_out_sum($min1, $vd1->currency_decimal, 'tbl') . ' ' . $currency_code_give . '</span>';													
			}		
			
			if ($sum1 > $max1 and is_numeric($max1)) {
				$sum1_error = 'error';
				$sum1_error_txt = '<span class="js_amount" data-id="sum1" data-val="' . $max1 . '">' . __('max', 'pn') . '.: ' . is_out_sum($max1, $vd1->currency_decimal, 'tbl') . ' ' . $currency_code_give . '</span>';													
			}
			
			if ($sum2 < $min2) {
				$sum2_error = 'error';
				$sum2_error_txt = '<span class="js_amount" data-id="sum2" data-val="' . $min2 . '">' . __('min', 'pn') . '.: ' . is_out_sum($min2, $vd2->currency_decimal, 'tbl') . ' ' . $currency_code_get . '</span>';													
			}		
			
			if ($sum2 > $max2 and is_numeric($max2)) {
				$sum2_error = 'error';
				$sum2_error_txt = '<span class="js_amount" data-id="sum2" data-val="' . $max2 . '">' . __('max', 'pn') . '.: ' . is_out_sum($max2, $vd2->currency_decimal, 'tbl') . ' ' . $currency_code_get . '</span>';													
			}
			
			if ($sum1 <= 0) {
				$sum1_error = 'error';
				$sum1_error_txt = __('amount must be greater than 0', 'pn');
			}
			
			if ($sum2 <= 0) {
				$sum2_error = 'error';
				$sum2_error_txt = __('amount must be greater than 0', 'pn');
			}	
			
			if ($sum1c <= 0) {
				$sum1c_error = 'error';
				$sum1c_error_txt = __('amount must be greater than 0', 'pn');
			}
			
			if ($sum2c <= 0) {
				$sum2c_error = 'error';
				$sum2c_error_txt = __('amount must be greater than 0', 'pn');
			}	
		
		}
				
		$left_col = '
		<div class="xtp_calc">
			<div class="xtp_curs_wrap">
		';
				$left_col .= '
				<div class="xtp_input_wrap js_wrap_error ' . $sum1_error . '">';
					
					$left_col .= apply_filters('exchange_input', '', 'give', $cdata, $calc_data);
					
				$left_col .= '	
					<div class="js_error js_sum1_error">' . $sum1_error_txt . '</div>
				</div>';

				$left_col .= '
				<div class="xtp_select_wrap">
					<select name="0" class="js_my_sel js_item_sel js_item_sel1" autocomplete="off">';
						foreach ($field1 as $vt => $v_id1) {
							$left_col .= '
							<option value="' . $v_id1 . '" ' . selected($cur_id1, $v_id1, false) . '>' . $vt . '</option>
							';
						}
					$left_col .= '
					</select>
				</div>';
					
			$left_col .= '	
			</div>';
				
			$left_col .= '
			<div class="xtp_commis_wrap js_wrap_error '. $sum1c_error .'" '. $viv_com1 .'>';
				
				$left_col .= apply_filters('exchange_input', '', 'give_com', $cdata, $calc_data);
				
			$left_col .= '
				<div class="xtp_commis_text">' . __('With fees', 'pn') . '</div>
				<div class="js_error js_sum1c_error">' . $sum1c_error_txt . '</div>
				<div class="clear"></div>
			</div>';
				
			$tbl_leftcol_data = array();
			$tbl_leftcol_data = apply_filters('tbl2_leftcol_data', $tbl_leftcol_data, $cdata, $vd1, $vd2, $direction, $user_id, $post_sum);
			foreach ($tbl_leftcol_data as $value) {
				$left_col .= $value; 
			}				
				
		$left_col .= '
			<div class="clear"></div>
		</div>';
		
		$right_col = '
		<div class="xtp_calc">
			<div class="xtp_curs_wrap">
			';
				$right_col .= '
				<div class="xtp_input_wrap js_wrap_error ' . $sum2_error . '">';
					
					$right_col .= apply_filters('exchange_input', '', 'get', $cdata, $calc_data);

				$right_col .= '
					<div class="js_error js_sum2_error">' . $sum2_error_txt . '</div>
				</div>';
					
				$right_col .= '
				<div class="xtp_select_wrap">
					<select name="" class="js_my_sel js_item_sel js_item_sel2" autocomplete="off">';
						
						foreach ($field2 as $vt => $v_id2) {
							$right_col .= '
							<option value="' . $v_id2 . '" ' . selected($cur_id2, $v_id2, false) . '>' . $vt . '</option>
							';
						}
							
				$right_col .= '										
					</select>
				</div>';
					
			$right_col .= '	
			</div>';
				
			$right_col .= '
			<div class="xtp_commis_wrap js_wrap_error ' . $sum2c_error . '" ' . $viv_com2 . '>';

				$right_col .= apply_filters('exchange_input', '', 'get_com', $cdata, $calc_data);
					
				$right_col .= '
				<div class="xtp_commis_text">' . __('With fees', 'pn') . '</div>
				<div class="js_error js_sum2c_error">' . $sum2c_error_txt . '</div>
					<div class="clear"></div>
			</div>';				
						
			$tbl_rightcol_data = array(
				'rate' => '
				<div class="xtp_line xtp_exchange_rate">
					' . __('Exchange rate', 'pn') . ': <span class="js_course_html">' . is_out_sum($course_give, $cdata['decimal_give'], 'course') . ' ' . $currency_code_give . ' = ' . is_out_sum($course_get, $cdata['decimal_get'], 'course') . ' ' . $currency_code_get . '</span> 
				</div>					
				',
				'reserve' => '
				<div class="xtp_line xtp_exchange_reserve">
					' . __('Reserve', 'pn') . ': <span class="js_reserve_html">' . $reserve . ' ' . $cdata['currency_code_get'] . '</span> 
				</div>					
				',
			);
			$tbl_rightcol_data = apply_filters('tbl2_rightcol_data', $tbl_rightcol_data, $cdata, $vd1, $vd2, $direction, $user_id, $post_sum);
			foreach ($tbl_rightcol_data as $value) {
				$right_col .= $value; 
			}				
					
		$right_col .= '
			<div class="clear"></div>
		</div>';
		
	}
	
	$temp .='
	<div class="xtp_col_table_body">
		<div class="xtp_left_col_table">
			<div class="xtp_left_col_table_ins">';
			
			$temp .= apply_filters('exchange_table2_part', '',  __('You send', 'pn'), $p1, 'left', $psys_id_give_current, $cur1);
			$temp .= $left_col;
			
		$temp .= '	
			</div>
		</div>
		<div class="xtp_right_col_table">
			<div class="xtp_right_col_table_ins">';	
			
			$temp .= apply_filters('exchange_table2_part', '', __('You receive', 'pn'), $p2, 'right', $psys_id_get_current, $cur2);	
			$temp .= $right_col;
			
		$temp .='
			</div>
		</div>

			<div class="clear"></div>
	</div>';	
	
	if (!isset($direction->id)) {
		$temp .=' <div class="xtp_error resultfalse"><div class="xtp_error_ins">' . __('Selected direction does not exist', 'pn') . '</div></div>';
	}
	
	$temp .='
	<div class="xtp_submit_wrap">
		<a href="' . get_exchange_link(is_isset($direction, 'direction_name')) . '" class="xtp_submit js_exchange_link ' . $submit_class . '" id="js_submit_button" data-direction-id="' . is_isset($direction, 'id') . '">' . __('Exchange', 'pn') . '</a>
			<div class="clear"></div>							
	</div>';
	
	return $temp;
}