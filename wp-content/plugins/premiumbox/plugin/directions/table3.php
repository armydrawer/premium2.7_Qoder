<?php
if (!defined('ABSPATH')) { exit(); } 

add_action('premium_js','premium_js_exchange_table3');
function premium_js_exchange_table3() {
	
	if (3 == get_type_table()) {
?>	
jQuery(function($) {
	
	function get_table_exchange(id, id1, id2) {
		
		$('#js_submit_button').addClass('active');		
		$('.js_loader').show();
		var param = 'id=' + id + '&id1=' + id1 + '&id2=' + id2<?php echo apply_filters('param_ajax_exchange_table3', ''); ?>;
		$.ajax({
			type: "POST",
			url: "<?php echo get_pn_action('table3_change_select', 'post'); ?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{	
				$('.js_loader').hide();
				$('#js_html').html(res['html']);	
				if ($('#hexch_html').length > 0) {
					$('#hexch_html').html('');
				}	
				<?php do_action('live_change_html'); ?>
			}
		});		
		
	}	 
	
	$(document).on('change', '#js_left_sel', function() {
		
		var id1 = $('#js_left_sel').val();
		var id2 = $('#js_right_sel').val();
		get_table_exchange(1, id1, id2);
		
	});
	
	$(document).on('change', '#js_right_sel', function() {
		
		var id1 = $('#js_left_sel').val();
		var id2 = $('#js_right_sel').val();
		get_table_exchange(2, id1, id2);
		
	});
	
	$(document).on('click', '#js_reload_table', function() {
		
		var id1 = $('#js_right_sel').val();
		var id2 = $('#js_left_sel').val();
		get_table_exchange(1, id1, id2);	
		
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

add_filter('exchange_table_type3','get_exchange_table3', 10, 3);
function get_exchange_table3($temp, $def_cur_from = '', $def_cur_to = '') {
	global $wpdb;	

	$temp = '';
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);

	$cur_from = $def_cur_from;
	$cur_to = $def_cur_to;

	$from = $to = 0;
	if ($cur_from and $cur_to) {
		$vd1 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' AND currency_status = '1' AND xml_value = '$cur_from'");
		$vd2 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' AND currency_status = '1' AND xml_value = '$cur_to'");
		if (isset($vd1->id) and isset($vd2->id)) {
			$from = $vd1->id;
			$to = $vd2->id;	
		}
	} 
	
	if (!$from) {
		$where = get_directions_where('home');
		$direction_items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where ORDER BY to3_1 ASC");
		foreach ($direction_items as $direction) {
			$output = apply_filters('get_direction_output', 1, $direction, 'home');
			if ($output) {
				$from = $direction->currency_id_give;
				$to = $direction->currency_id_get;
				break;
			}	
		}		
	}
	
	$temp .='
	<div class="xchange_type_list">
		<div class="xchange_type_list_ins">
			<div class="xtl_table_wrap">
				<div class="xtl_table_wrap_ins">';
				
					$temp .= apply_filters('exchange_table3_head', '');
		
					$temp .='
					<div class="xtl_html_wrap">
						<div class="xtl_html_abs js_loader"></div>
						
						<div id="js_html">';

							$temp .= get_xtl_temp($from, $to, 1);
						
						$temp .='
						</div>
					</div>';		
					
				$temp .='	
				</div>
			</div>
		</div>
	</div>	
	';
	
	return $temp;
}

add_action('premium_siteaction_table3_change_select', 'def_premium_siteaction_table3_change_select');
function def_premium_siteaction_table3_change_select() {
	global $premiumbox;	
	
	_json_head(); 
	_method('post'); 
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = '0'; 
	$log['status_text']= '';
	$log['html'] = '';	
	
	$premiumbox->up_mode('post');
	
	$type_table = get_type_table();
	if (3 == $type_table) {	
	
		define('exchange_table_ajax', 3);

		$id = intval(is_param_post('id'));
		$id1 = intval(is_param_post('id1'));
		$id2 = intval(is_param_post('id2'));	

		$log['html'] = get_xtl_temp($id1, $id2, $id);
	}
	
	echo pn_json_encode($log);
	exit;
}

function get_xtl_temp($from, $to, $id) {
	global $wpdb, $premiumbox;
	
	$temp = '';
	
	if (2 != $id) { $id = 1; }
	
	$v = get_currency_data();	
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);

	$currency_id_give = $get_currency_id_give = intval($from);
	$currency_id_get = $get_currency_id_get = intval($to);	

	$where = get_directions_where('home');
	
	$v1 = $v2 = $img1 = $img2 = '';
	$tablenot = intval($premiumbox->get_option('exchange', 'tablenothome')); 
	$tableselect = intval($premiumbox->get_option('exchange', 'tableselecthome'));
	$directions1 = $directions2 = array();

	$direction = '';
	
	$dirs = array();
	$direction_items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where ORDER BY to3_1 ASC");
	foreach ($direction_items as $item) {
		if (isset($v[$item->currency_id_give], $v[$item->currency_id_get])) {
			$output = apply_filters('get_direction_output', 1, $item, 'home');
			if ($output) {
				$dirs[] = $item;
			}
		}
	}
	
	if ($currency_id_give and $currency_id_get) { 
		foreach ($dirs as $dir) {
			if ($dir->currency_id_give == $currency_id_give and $dir->currency_id_get == $currency_id_get) {
				$direction = $dir;
				break;
			}	
		}	
	} 	
	
	if (isset($direction->id)) { 
		foreach ($dirs as $dir) {
			if (1 == $id) {
				$directions1[$dir->currency_id_give] = $dir->currency_id_give;
				if ($dir->currency_id_give == $currency_id_give or 1 != $tableselect) {
					$directions2[$dir->currency_id_get] = $dir->currency_id_get;
				}
			} else {
				$directions2[$dir->currency_id_get] = $dir->currency_id_get;
				if ($dir->currency_id_get == $currency_id_get or 1 != $tableselect) {
					$directions1[$dir->currency_id_give] = $dir->currency_id_give;
				}
			}
		}	
	} else { 
		if (1 == $tablenot) { 
			if (1 == $id) { 
				foreach ($dirs as $dir) {
					if ($dir->currency_id_give == $currency_id_give) {
						$direction = $dir;
						break;
					}	
				}						
				if (isset($direction->id)) {
					foreach ($dirs as $dir) { 
						$directions1[$dir->currency_id_give] = $dir->currency_id_give;
						if ($dir->currency_id_give == $currency_id_give or 1 != $tableselect) {
							$directions2[$dir->currency_id_get] = $dir->currency_id_get;
						}
					}	
				} else {
					foreach ($dirs as $dir) {
						if ($dir->currency_id_get == $currency_id_get) {
							$direction = $dir;
							break;
						}	
					}				
					if (isset($direction->id)) {
						foreach ($dirs as $dir) { 
							$directions2[$dir->currency_id_get] = $dir->currency_id_get;
							if ($dir->currency_id_get == $currency_id_get or 1 != $tableselect) {
								$directions1[$dir->currency_id_give] = $dir->currency_id_give;
							}
						}						
					}	
				}
			} else { 
				foreach ($dirs as $dir) {
					if ($dir->currency_id_get == $currency_id_get) {
						$direction = $dir;
						break;
					}	
				}						
				if (isset($direction->id)) {
					foreach ($dirs as $dir) { 
						$directions2[$dir->currency_id_get] = $dir->currency_id_get;
						if ($dir->currency_id_get == $currency_id_get or 1 != $tableselect) {
							$directions1[$dir->currency_id_give] = $dir->currency_id_give;
						}
					}					
				} else {
					foreach ($dirs as $dir) {
						if ($dir->currency_id_give == $currency_id_give) {
							$direction = $dir;
							break;
						}	
					}										
					if (isset($direction->id)) {
						foreach ($dirs as $dir) { 
							$directions1[$dir->currency_id_give] = $dir->currency_id_give;
							if ($dir->currency_id_give == $currency_id_give or 1 != $tableselect) {
								$directions2[$dir->currency_id_get] = $dir->currency_id_get;
							}
						}						
					}					
				}
			}
		}
	}
	
	if (!isset($direction->id)) {
		$r = 0;
		foreach ($dirs as $dir) { $r++;
		
			if (1 == $tablenot) {
				if (1 == $r) {
					$get_currency_id_give = $dir->currency_id_give;
					$get_currency_id_get = $dir->currency_id_get;
					$direction = $dir;
				}
			} 
			
			if (1 == $tableselect) {
				if (1 == $id) {
					$directions1[$dir->currency_id_give] = $dir->currency_id_give;
					if ($get_currency_id_give == $dir->currency_id_give) {
						$directions2[$dir->currency_id_get] = $dir->currency_id_get;
					}	
				} elseif (2 == $id) {
					$directions2[$dir->currency_id_get] = $dir->currency_id_get;
					if ($get_currency_id_get == $dir->currency_id_get) {
						$directions1[$dir->currency_id_give] = $dir->currency_id_give;
					}
				}											
			} else {
				$directions1[$dir->currency_id_give] = $dir->currency_id_give;
				$directions2[$dir->currency_id_give] = $dir->currency_id_give;
				$directions1[$dir->currency_id_get] = $dir->currency_id_get;
				$directions2[$dir->currency_id_get] = $dir->currency_id_get;					
			}			
		}
	} 	
	
	if (count($directions1) < 1 or !isset($v[$currency_id_give]) or !isset($v[$currency_id_get])) {
		return 'table error';
	}		
			
	if (isset($direction->id)) {		
		$currency_id_give = $direction->currency_id_give;
		$currency_id_get = $direction->currency_id_get;		
	} 
	
	$vd1 = $v[$currency_id_give];
	$vd2 = $v[$currency_id_get];
			
	$tableicon = get_icon_for_table();
		
	$img1 = get_currency_logo($vd1, $tableicon);
	$img2 = get_currency_logo($vd2, $tableicon); 		
	
	$link_class = 'active';
	$error_html = '<div class="xtl_error resultfalse"><div class="xtl_error_ins">' . __('Selected direction does not exist', 'pn') . '</div></div>';
	$hide = 'style="display: none;"';
	$input_give = '';
	$input_get = '';
	$input_givecom = '';
	$input_getcom = '';		
	$currency_code_give = '';
	$currency_code_get = '';
	$leftcol_data = '';
	$rightcol_data = '';
	
	if (isset($direction->id)) {
		
		$error_html = '';	
		$hide = '';
		
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
		$calc_data = apply_filters('get_calc_data_params', $calc_data, 'table3');
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
				
		$hideerror = intval($premiumbox->get_option('exchange', 'tablehideerror'));
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
			if ($sum1c <= 0) {
				$sum1c_error = 'error';
				$sum1c_error_txt = __('amount must be greater than 0', 'pn');
			}
			if ($sum2 <= 0) {
				$sum2_error = 'error';
				$sum2_error_txt = __('amount must be greater than 0', 'pn');
			}
			if ($sum2c <= 0) {
				$sum2c_error = 'error';
				$sum2c_error_txt = __('amount must be greater than 0', 'pn');
			}	

		}

		$input_give = '
		<div class="xtl_input_wrap js_wrap_error ' . $sum1_error . '">					
			'. apply_filters('exchange_input', '', 'give', $cdata, $calc_data) .'						
			<div class="js_error js_sum1_error">' . $sum1_error_txt . '</div>
		</div>';						
					
		$input_givecom = '
		<div class="xtl_commis_wrap js_wrap_error ' . $sum1c_error . '" ' . $viv_com1 . '>										
			'. apply_filters('exchange_input', '', 'give_com', $cdata, $calc_data) .'
			<div class="xtl_commis_text">' . __('With fees', 'pn') . '</div>
			<div class="js_error js_sum1c_error">' . $sum1c_error_txt . '</div>				
				<div class="clear"></div>
		</div>';

		$input_get = '
		<div class="xtl_input_wrap js_wrap_error ' . $sum2_error . '">
			'. apply_filters('exchange_input', '', 'get', $cdata, $calc_data) .'
			<div class="js_error js_sum2_error">' . $sum2_error_txt . '</div>	
		</div>';
					
		$input_getcom = '
		<div class="xtl_commis_wrap js_wrap_error ' . $sum2c_error . '" ' . $viv_com2 . '>				
			'. apply_filters('exchange_input', '', 'get_com', $cdata, $calc_data) .'
			<div class="xtl_commis_text">' . __('With fees', 'pn') . '</div>
			<div class="js_error js_sum2c_error">' . $sum2c_error_txt . '</div>				
				<div class="clear"></div>
		</div>';		

		$link_class = '';
		
		$tbl_leftcol_data = array();
		$tbl_leftcol_data = apply_filters('tbl3_leftcol_data', $tbl_leftcol_data, $cdata, $vd1, $vd2, $direction, $user_id, $post_sum);				
		foreach ($tbl_leftcol_data as $value) {
			$leftcol_data .= $value; 
		}	
			
		$tbl_rightcol_data = array(
			'rate' => '
				<div class="xtl_line xtl_exchange_rate">
					' . __('Exchange rate', 'pn') . ': <span class="js_course_html">' . is_out_sum($course_give, $cdata['decimal_give'], 'course') . ' ' . $currency_code_give . ' = ' . is_out_sum($course_get, $cdata['decimal_get'], 'course') . ' ' . $currency_code_get . '</span>
				</div>						
			',
			'reserve' => '
				<div class="xtl_line xtl_exchange_reserve">
					' . __('Reserve', 'pn') . ': <span class="js_reserve_html">' . $reserve . ' ' . $cdata['currency_code_get'] . '</span>
				</div>						
			',
		);
		$tbl_rightcol_data = apply_filters('tbl3_rightcol_data', $tbl_rightcol_data, $cdata, $vd1, $vd2, $direction, $user_id, $post_sum);					
		foreach ($tbl_rightcol_data as $value) {
			$rightcol_data .= $value; 
		}
					
	}	
	
	$direction_id = intval(is_isset($direction, 'id'));
	
	$submit_html ='	
	<div class="xtl_submit_wrap">
		<div class="xtl_submit_ins">
			<a href="' . get_exchange_link(is_isset($direction, 'direction_name')) . '" class="xtl_submit js_exchange_link ' . $link_class . '" id="js_submit_button" data-direction-id="' . $direction_id . '">' . __('Exchange', 'pn') . '</a>
				<div class="clear"></div>
		</div>	
	</div>';	
	
	$class_select_table3 = array(
		'js_my_sel' => 'js_my_sel'
	);
	$class_select_table3 = apply_filters('class_select_table3', $class_select_table3);

	$select_give = '<select name="" id="js_left_sel" class="' . join(' ', $class_select_table3) . '" autocomplete="off">';
		foreach ($directions1 as $key => $currency_id) {
			$select_give .= '<option value="' . $currency_id . '" ' . selected($currency_id, $currency_id_give, false) . ' data-img="' . get_currency_logo($v[$currency_id], $tableicon) . '">' . get_currency_title($v[$currency_id]) . '</option>';		
		}
	$select_give .= '	
	</select>';																			

	$select_get = '<select name="" id="js_right_sel" class="' . join(' ', $class_select_table3) . '" autocomplete="off">';
		foreach ($directions2 as $key => $currency_id) {
			$select_get .= '<option value="' . $currency_id . '" ' . selected($currency_id, $currency_id_get, false) . ' data-img="' . get_currency_logo($v[$currency_id], $tableicon) . '">' . get_currency_title($v[$currency_id]) . '</option>';					
		}
	$select_get .= '		
	</select>';	
	
	$array = array(
		'[submit]' => $submit_html,
		'[error]' => $error_html,
		'[reload]' => '<a href="#" class="xtl_change" id="js_reload_table"></a>',
		'[hide]' => $hide,
		'[ico_give]' => $img1,
		'[ico_get]' => $img2,
		'[select_give]' => $select_give,
		'[select_get]' => $select_get,
		'[input_give]' => $input_give,
		'[input_get]' => $input_get,
		'[input_givecom]' => $input_givecom,
		'[input_getcom]' => $input_getcom,	
		'[leftcol_data]' => $leftcol_data,
		'[rightcol_data]' => $rightcol_data,
		'[title_left]' => apply_filters('exchange_table3_leftcol', '<div class="xtl_table_title"><div class="xtl_table_title_ins">' . __('You send', 'pn') . '</div></div>'),
		'[title_right]' => apply_filters('exchange_table3_rightcol', '<div class="xtl_table_title"><div class="xtl_table_title_ins">' . __('You receive', 'pn') . '</div></div>'),
	);
	
	$html = '
	<input type="hidden" name="" class="js_direction_id" value="' . $direction_id . '" />
	
	<div class="xtl_table_body">
		<div class="xtl_left_col">
			[title_left]
				
			<div class="xtl_selico_wrap">
				<div class="xtl_ico_wrap">
					<div class="xtl_ico currency_logo" style="background-image: url([ico_give]);"></div>
				</div>
				<div class="xtl_select_wrap">
					[select_give]
				</div>						
			</div>				

			[input_give]
			[input_givecom]	
			[leftcol_data]
		
		</div>
		<div class="xtl_center_col">
			[reload]
		</div>
		<div class="xtl_right_col">
			[title_right]
				
			<div class="xtl_selico_wrap">
				<div class="xtl_ico_wrap">
					<div class="xtl_ico currency_logo" style="background-image: url([ico_get]);"></div>
				</div>
				<div class="xtl_select_wrap">
					[select_get]
				</div>	
			</div>				

			[input_get]
			[input_getcom]
			[rightcol_data]	
	
		</div>
			<div class="clear"></div>
	</div>
				
	[error]
	[submit]
	';
	
	$html = apply_filters('tbl3_temp', $html);			
	$temp = replace_tags($array, $html);												

	return $temp;	
}