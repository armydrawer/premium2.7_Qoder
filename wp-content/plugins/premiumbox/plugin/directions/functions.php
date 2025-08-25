<?php
if (!defined('ABSPATH')) { exit(); }

function the_exchange_home($def_cur_from = '', $def_cur_to = '') {

	echo get_exchange_table($def_cur_from, $def_cur_to);
}

function get_exchange_table($def_cur_from = '', $def_cur_to = '') {

	$temp = '';

	$arr = array(
		'from' => $def_cur_from,
		'to' => $def_cur_to,
		'direction_id' => 0,
	);
	$arr = apply_filters('get_exchange_table_data', $arr);

	$type_table = get_type_table();
	if (100 == $type_table) {
		$show_data = pn_exchanges_output('exchange');
	} else {
		$show_data = pn_exchanges_output('home');
	}

	if (strlen($show_data['text']) > 0) {
		$temp .= '<div class="resultfalse home_resultfalse"><div class="resultfalse_close"></div>' . $show_data['text'] . '</div>';
	}

	if (1 == $show_data['show']) {
		$html = apply_filters('exchange_table_type', '', $type_table ,$arr['from'] ,$arr['to'], $arr['direction_id']);
		$temp .= apply_filters('exchange_table_type' . $type_table, $html ,$arr['from'] ,$arr['to'], $arr['direction_id']);
	}

	return $temp;
}

add_filter('get_exchange_table_data', 'def_get_exchange_table_data', 100);
function def_get_exchange_table_data($arr) {

	$cur_from = is_xml_value(is_param_get('cur_from'));
	$cur_to = is_xml_value(is_param_get('cur_to'));
	if ($cur_from or $cur_to) {
		$arr['from'] = $cur_from;
		$arr['to'] = $cur_to;
		$arr['direction_id'] = 0;
	}

	return $arr;
}

add_filter('get_directions_table1', 'def_get_directions_table1', 10, 5);
function def_get_directions_table1($directions, $place, $where, $v, $currency_id_give = '') {
	global $wpdb;

	$currency_id_give = intval($currency_id_give);
	if ($currency_id_give > 0) {
		$where .= " AND currency_id_give = '$currency_id_give'";
	}

	$directions = array();
	$dirs = array();

	$directions_arr = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."directions WHERE $where");
	foreach ($directions_arr as $dir) {
		if (isset($v[$dir->currency_id_give], $v[$dir->currency_id_get])) {
			$output = apply_filters('get_direction_output', 1, $dir, $place);
			if (1 == $output) {
				$dirs[] = array(
					'order' => intval($v[$dir->currency_id_get]->t1_2),
					'd' => $dir,
				);
			}
		}
	}

	$dirs = pn_array_sort($dirs, 'order', 'asc', 'num');

	foreach ($dirs as $dir_data) {
		$directions[$dir_data['d']->currency_id_give][] = $dir_data['d'];
	}

	return $directions;
}

add_filter('table_exchange_widget', 'def_table_exchange_widget', 10, 4);
function def_table_exchange_widget($dir_id, $place, $cur_from = '', $cur_to = '') {
	global $wpdb;

	$dir_id = intval($dir_id);
	$place = trim($place);
	if ('intable' == $place and !$dir_id) {
		$cur_from = strtoupper(is_xml_value($cur_from));
		$cur_to = strtoupper(is_xml_value($cur_to));
		if ($cur_from and $cur_to) {
			$v = get_currency_data();
			$where = get_directions_where('home');
			$directions_arr = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where");
			foreach ($directions_arr as $dir) {
				if (isset($v[$dir->currency_id_give], $v[$dir->currency_id_get])) {
					$vd1 = $v[$dir->currency_id_give];
					$vd2 = $v[$dir->currency_id_get];
					$output = apply_filters('get_direction_output', 1, $dir, 'home');
					if (1 == $output) {
						if ($cur_from == strtoupper($vd1->xml_value) and $cur_to == strtoupper($vd2->xml_value)) {
							$dir_id = $dir->id;
							break;
						}
					}
				}
			}
		}
	}

	return $dir_id;
}

function set_directions_data($place, $error_page = 0, $id = 0, $direction_name = '', $cur1 = 0, $cur2 = 0, $cur_place_id = 0) {
	global $wpdb, $direction_data, $premiumbox;

	$id = intval($id);
	$error_page = intval($error_page);
	$direction_name = is_direction_name($direction_name);
	$cur1 = intval($cur1);
	$cur2 = intval($cur2);
	$cur_place_id = intval($cur_place_id);

	$where = get_directions_where($place);
	$where_now = '';
	$set = 0;
	if ($id > 0) {
		$where_now .= " AND id='$id'";
		$set = 1;
	} elseif ($direction_name) {
		$where_now .= " AND direction_name='$direction_name'";
		$set = 1;
	} elseif ($cur1 and $cur2) {
		$where_now .= " AND currency_id_give='$cur1' AND currency_id_get='$cur2'";
		$set = 1;
	}
	if (1 == $set) {

		$v = get_currency_data();

		$dirs = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where $where_now");
		$dir = '';
		foreach ($dirs as $d) {
			$output = apply_filters('get_direction_output', 1, $d, $place);
			if ($output) {
				$currency_id_give = $d->currency_id_give;
				$currency_id_get = $d->currency_id_get;
				if (isset($v[$currency_id_give]) and isset($v[$currency_id_get])) {
					$dir = $d;
					break;
				}
			}
		}

		if (!isset($dir->id)) {
			$tablenot = intval($premiumbox->get_option('exchange', 'tablenot'));
			if (1 == $tablenot and 1 != $error_page) {
				if (0 != $cur_place_id and $cur1 and $cur2) {
					if (1 == $cur_place_id) {
						$direction_items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where AND currency_id_give = '$cur1' ORDER BY site_order1 ASC");
						foreach ($direction_items as $direction) {
							$output = apply_filters('get_direction_output', 1, $direction, $place);
							if ($output) {
								$currency_id_give = $direction->currency_id_give;
								$currency_id_get = $direction->currency_id_get;
								if (isset($v[$currency_id_give]) and isset($v[$currency_id_get])) {
									$dir = $direction;
									break;
								}
							}
						}
					} else {
						$direction_items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where AND currency_id_get = '$cur2' ORDER BY site_order1 ASC");
						foreach ($direction_items as $direction) {
							$output = apply_filters('get_direction_output', 1, $direction, $place);
							if ($output) {
								$currency_id_give = $direction->currency_id_give;
								$currency_id_get = $direction->currency_id_get;
								if (isset($v[$currency_id_give]) and isset($v[$currency_id_get])) {
									$dir = $direction;
									break;
								}
							}
						}
					}
				}
				if (!isset($dir->id)) {
					$direction_items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where ORDER BY site_order1 ASC");
					foreach ($direction_items as $direction) {
						$output = apply_filters('get_direction_output', 1, $direction, $place);
						if ($output) {
							$currency_id_give = $direction->currency_id_give;
							$currency_id_get = $direction->currency_id_get;
							if (isset($v[$currency_id_give]) and isset($v[$currency_id_get])) {
								$dir = $direction;
								break;
							}
						}
					}
				}
			}
		}

		if (isset($dir->id)) {
			$currency_id_give = intval($dir->currency_id_give);
			$currency_id_get = intval($dir->currency_id_get);
			$vd1 = $v[$currency_id_give];
			$vd2 = $v[$currency_id_get];
			if (isset($vd1->id) and isset($vd2->id)) {
				$direction_data = array();

				$direction_data['direction_id'] = intval($dir->id);
				$direction_data['item_give'] = get_currency_title($vd1);
				$direction_data['item_get'] = get_currency_title($vd2);
				$direction_data['currency_id_give'] = $vd1->id;
				$direction_data['currency_id_get'] = $vd2->id;
				$direction_data['vd1'] = $vd1;
				$direction_data['vd2'] = $vd2;
				$direction_data['direction'] = $dir;

				if (!is_object($direction_data)) {
					$direction_data = (object)$direction_data;
				}
			}
		}
	}
}

function set_exchange_shortcode($place = '', $side_id = '') {
	global $wpdb, $premiumbox, $direction_data;

	$array = array();

	if (isset($direction_data->direction_id) and $direction_data->direction_id > 0) {

		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);

		$direction_id = intval($direction_data->direction_id);
		$vd1 = $direction_data->vd1;
		$vd2 = $direction_data->vd2;
		$direction = $direction_data->direction;
		$cdata = array();

		$side_id = intval($side_id);
		if (2 != $side_id) { $side_id = 1; }

		/* message */
		$text = get_direction_descr('timeline_txt', $direction, $vd1, $vd2);
		$text = apply_filters('direction_instruction', $text, 'timeline_txt', $direction, $vd1, $vd2);

		$message = '';
		if (strlen($text) > 0) {
			$message = '
			<div class="notice_message">
				<div class="notice_message_ins">
					<div class="notice_message_abs"></div>
					<div class="notice_message_close"></div>
					<div class="notice_message_text">
						<div class="notice_message_text_ins">
							<div class="text">
							' . apply_filters('comment_text', $text) . '
							</div>
						</div>
					</div>
				</div>
			</div>';
		}
		/* end message */

		/* frozen */
		$frozen = '';
		if (2 == $direction->direction_status) {
			$text = get_direction_descr('frozen_txt', $direction, $vd1, $vd2);
			$text = apply_filters('direction_instruction', $text, 'frozen_txt', $direction, $vd1, $vd2);

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
		/* end frozen */

		/* window */
		$text = get_direction_descr('window_txt', $direction, $vd1, $vd2);
		$text = apply_filters('direction_instruction', $text, 'window_txt', $direction, $vd1, $vd2);

		$window_txt = '';
		if (strlen($text) > 0) {
			$window_txt = '
			<div class="window_message" style="display: none;">
				<div class="window_message_ins">
					' . apply_filters('comment_text', $text) . '
				</div>
			</div>';
		}
		/* end window */

		/* description */
		$text = get_direction_descr('description_txt', $direction, $vd1, $vd2);
		$text = apply_filters('direction_instruction', $text, 'description_txt', $direction, $vd1, $vd2);

		$description = '';
		if (strlen($text) > 0) {
			$title = get_exchange_title();
			$description = '
			<div class="warning_message" itemscope itemtype="https://schema.org/Article">
				<div class="warning_message_ins">
					<div class="warning_message_abs"></div>
					<div class="warning_message_close"></div>
					<div class="warning_message_title">
						<div class="warning_message_title_ins">
							<span itemprop="name">' . $title . '</span>
						</div>
					</div>
					<div class="warning_message_text">
						<div class="warning_message_text_ins" itemprop="articleBody">
							<div class="text">
								' . apply_filters('comment_text', $text) . '
							</div>
						</div>
					</div>
				</div>
			</div>';
		}
		/* end description */

		/* submit */
		$now_cl = 'xchange';
		if ('exchange_html_list' != $place) {
			$now_cl = 'hexch';
		}
		$submit = '
		<div class="' . $now_cl . '_submit_div">
			<input type="submit" formtarget="_top" class="' . $now_cl . '_submit" name="" value="' . __('Exchange', 'pn') . '" /> 
				<div class="clear"></div>
		</div>';
		/* end submit */

		/* check */
		$not_check_data = intval(get_pn_cookie('not_check_data'));

		$now_cl = 'xchange_checkdata_div';
		if ('exchange_html_list' != $place) {
			$now_cl = 'hexch_checkdata_div';
		}

		$remember = '';
		$hidesavedata = intval($premiumbox->get_option('exchange', 'hidesavedata'));
		if (!$hidesavedata) {

			$remember = '
			<div class="' . $now_cl . '">
				<label><input type="checkbox" id="not_check_data" name="not_check_data" ' . checked($not_check_data, 1, false) . ' autocomplete="off" value="1" /> ' . __('Do not remember entered data', 'pn') . '</label>
			</div>
			';

		}

		$check = '';
		$hidecheckrule = intval($premiumbox->get_option('exchange', 'hidecheckrule'));
		if (!$hidecheckrule) {
			$enable_step2 = intval($premiumbox->get_option('exchange', 'enable_step2'));
			if (0 == $enable_step2) {
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

				$check = '	
				<div class="' . $now_cl . '">
					<label><input type="checkbox" name="check_rule" autocomplete="off" value="1" /> ' . $toslink_title . '</label>
				</div>
				';
			}
		}
		/* end check */

		$tableicon = get_icon_for_table();

		$currency_logo_give = get_currency_logo($vd1, $tableicon);
		$currency_logo_get = get_currency_logo($vd2, $tableicon);

		/* selects */
		$directions1 = $directions2 = array();

		$currency_id_give = $vd1->id;
		$currency_id_get = $vd2->id;

		$select_give = $select_get = '';

		$tableselect = intval($premiumbox->get_option('exchange', 'tableselect'));

		$v = get_currency_data();

		if ('exchange_html_list' == $place) {
			$pl_id = 'exchange';
		} else {
			$pl_id = 'home';
		}
		$where = get_directions_where($pl_id);
		$directions_arr = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where ORDER BY to3_1 ASC");
		foreach ($directions_arr as $nd) {
			$output = apply_filters('get_direction_output', 1, $nd, $pl_id);
			if ($output) {
				if (1 == $tableselect) {
					if (1 == $side_id) { /* если выбрана левая сторона */
						$directions1[$nd->currency_id_give] = $nd;
						if ($nd->currency_id_give == $currency_id_give) {
							$directions2[$nd->currency_id_get] = $nd;
						}
					} else { /* если выбрана правая сторона */
						$directions2[$nd->currency_id_get] = $nd;
						if ($nd->currency_id_get == $currency_id_get) {
							$directions1[$nd->currency_id_give] = $nd;
						}
					}
				} else {
					$directions1[$nd->currency_id_give] = $nd;
					$directions2[$nd->currency_id_get] = $nd;
				}
			}
		}

		$select_give = '
		<select name="" class="js_my_sel" autocomplete="off" id="select_give">';
			foreach ($directions1 as $key => $np) {
				$select_give .= '<option value="' . $key . '" ' . selected($key, $currency_id_give, false) . ' data-img="' . get_currency_logo(is_isset($v, $key), $tableicon) . '" data-logo="' . get_currency_logo(is_isset($v, $key), 1) . '" data-logo-next="' . get_currency_logo(is_isset($v, $key), 2) . '">' . get_currency_title(is_isset($v, $key)) . '</option>';
			}
		$select_give .= '
		</select>';

		$select_get = '
		<select name="" class="js_my_sel" autocomplete="off" id="select_get">';
			foreach ($directions2 as $key => $np) {
				$select_get .= '<option value="' . $key . '" ' . selected($key, $currency_id_get, false) . ' data-img="' . get_currency_logo(is_isset($v, $key), $tableicon) . '" data-logo="' . get_currency_logo(is_isset($v, $key), 1) . '" data-logo-next="' . get_currency_logo(is_isset($v, $key), 2) . '">' . get_currency_title(is_isset($v, $key)) . '</option>';
			}
		$select_get .= '
		</select>';
		/* end selects */

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
		$calc_data = apply_filters('get_calc_data_params', $calc_data, 'exchangeform');
		$cdata = get_calc_data($calc_data, 1);

		$currency_code_give = $cdata['currency_code_give'];
		$currency_code_get = $cdata['currency_code_get'];
		$psys_give = $cdata['psys_give'];
		$psys_get = $cdata['psys_get'];

		$reserve = is_out_sum($cdata['reserve'], $vd2->currency_decimal, 'reserv');

		$viv_com1 = $cdata['viv_com1'];
		$viv_com2 = $cdata['viv_com2'];

		$viv_com1_style = 'style="display: none;"'; /* не выводим поле доп.комиссии */
		if ($viv_com1) {
			$viv_com1_style = '';
		}

		$viv_com2_style = 'style="display: none;"'; /* не выводим поле доп.комиссии */
		if ($viv_com2) {
			$viv_com2_style = '';
		}

		$comis_text1 = $cdata['comis_text1'];
		$comis_text2 = $cdata['comis_text2'];

		$sum1 = $cdata['sum1'];
		$sum1c = $cdata['sum1c'];
		$sum2 = $cdata['sum2'];
		$sum2c = $cdata['sum2c'];

		$sum1_error = $sum2_error = $sum1c_error = $sum2c_error = '';
		$sum1_error_txt = $sum2_error_txt = $sum1c_error_txt = $sum2c_error_txt = '';

		$user_discount = $cdata['user_discount'];
		$user_discount_html = '';
		$user_discounttext_html = '';
		$us = '';
		if ($user_discount > 0) {
			$us = '<p><span class="span_skidka">' . __('Your discount', 'pn') . ': <span class="js_direction_user_discount">' . $user_discount . '</span>%</span></p>';
			$user_discount_html = '<span class="js_direction_user_discount">' . $user_discount . '</span>%';
			$user_discounttext_html = '<div class="user_discount_div"><span class="user_discount_label">' . __('Your discount', 'pn') . '</span>: <span class="js_direction_user_discount">' . $user_discount . '</span>%</div>';
		}

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

		$formhideerror = intval($premiumbox->get_option('exchange', 'formhideerror'));
		if (!$formhideerror) {

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

		$now_cl = 'xchange_sum_input';
		if ('exchange_html_list' != $place) {
			$now_cl = 'hexch_curs_input';
		}

		$input_give = '
		<div class="' . $now_cl . ' js_wrap_error ' . $sum1_error . '">';
			$input_give .= apply_filters('exchange_input', '', 'give', $cdata, $calc_data);
			$input_give .= '
			<div class="js_error js_sum1_error">' . $sum1_error_txt . '</div>					
		</div>				
		';

		$input_get = '
		<div class="' . $now_cl . ' js_wrap_error ' . $sum2_error . '">';
			$input_get .= apply_filters('exchange_input', '', 'get', $cdata, $calc_data);
			$input_get .= '
			<div class="js_error js_sum2_error">' . $sum2_error_txt . '</div>					
		</div>				
		';

		if ('exchange_html_list' == $place) {
			$com_give_text = '
			<div class="xchange_sumandcom js_viv_com1" ' . $viv_com1_style . '>
				<span class="js_comis_text1">' . $comis_text1 . '</span>
			</div>';

			$com_get_text = '
			<div class="xchange_sumandcom js_viv_com2" ' . $viv_com2_style . '>
				<span class="js_comis_text2">' . $comis_text2 . '</span>
			</div>';

			$com_give ='
			<div class="xchange_sum_input js_wrap_error ' . $sum1c_error . '">';
				$com_give .= apply_filters('exchange_input', '', 'give_com', $cdata, $calc_data);
				$com_give .= '
				<div class="js_error js_sum1c_error">' . $sum1c_error_txt . '</div>
			</div>';

			$com_get ='
			<div class="xchange_sum_input js_wrap_error ' . $sum2c_error . '">';
				$com_get .= apply_filters('exchange_input', '', 'get_com', $cdata, $calc_data);
				$com_get .= '
				<div class="js_error js_sum2c_error">' . $sum2c_error_txt . '</div>
			</div>';
		} else {
			$com_give = '
			<div class="hexch_curs_input hexch_sum_input js_wrap_error ' . $sum1c_error . '">';
				$com_give .= apply_filters('exchange_input', '', 'give_com', $cdata, $calc_data);
				$com_give .= '
				<div class="js_error js_sum1c_error">' . $sum1c_error_txt . '</div>
			</div>				
			';

			$com_give_text = '
			<div class="hexch_sumandcom js_viv_com1" ' . $viv_com1_style . '>
				<span class="js_comis_text1">' . $comis_text1 . '</span>
			</div>				
			';

			$com_get = '
			<div class="hexch_curs_input hexch_sum_input js_wrap_error js_wrap_error_br ' . $sum2c_error . '">';
				$com_get .= apply_filters('exchange_input', '', 'get_com', $cdata, $calc_data);
				$com_get .= '
				<div class="js_error js_sum2c_error">' . $sum2c_error_txt . '</div>
			</div>				
			';

			$com_get_text = '
			<div class="hexch_sumandcom js_viv_com2" ' . $viv_com2_style . '>
				<span class="js_comis_text2">' . $comis_text2 . '</span>
			</div>				
			';
		}

		$vz1 = array();
		if ($min1 > 0) {
			$vz1[] = '<span class="js_amount" data-id="sum1" data-val="' . $min1 . '">' . __('min', 'pn') . '.: ' . is_out_sum($min1, $vd1->currency_decimal, 'tbl') . ' ' . $currency_code_give . '</span>';
		}
		if (is_numeric($max1)) {
			$vz1[] = '<span class="js_amount" data-id="sum1" data-val="' . $max1 . '">' . __('max', 'pn') . '.: ' . is_out_sum($max1, $vd1->currency_decimal, 'tbl') . ' ' . $currency_code_give . '</span>';
		}

		$minmax_give_html = '';
		if (count($vz1) > 0) {
			$minmax_give_html = '<p class="span_give_max">' . implode(' ', $vz1) . '</p>';
		}

		$vz2 = array();
		if ($min2 > 0) {
			$vz2[] = '<span class="js_amount" data-id="sum2" data-val="' . $min2 . '">' . __('min', 'pn') . '.: ' . is_out_sum($min2, $vd2->currency_decimal, 'tbl') . ' ' . $currency_code_get . '</span>';
		}
		if (is_numeric($max2)) {
			$vz2[] = '<span class="js_amount" data-id="sum2" data-val="' . $max2 . '">' . __('max', 'pn') . '.: ' . is_out_sum($max2, $vd2->currency_decimal, 'tbl') . ' ' . $currency_code_get . '</span>';
		}

		$minmax_get_html = '';
		if (count($vz2) > 0) {
			$minmax_get_html = '<p class="span_get_max">' . implode(' ', $vz2) . '</p>';
		}

		$meta1 = $meta2 = $meta1d = $meta2d = '';

		$course_html = '<span class="js_course_html">' . is_out_sum($cdata['course_give'], $cdata['decimal_give'], 'course') . ' ' . $currency_code_give . ' = ' . is_out_sum($cdata['course_get'], $cdata['decimal_get'], 'course') . ' ' . $currency_code_get . '</span>';

		if ('exchange_html_list' == $place) {

			$meta1d = '<div class="xchange_info_line">' . __('Exchange rate', 'pn') . ': ' . $course_html . '</div>';
			if ($minmax_give_html) {
				$meta1 = '<div class="xchange_info_line">' . $minmax_give_html . '</div>';
			}

			if ($us) {
				$meta2d = '<div class="xchange_info_line">' . $us . '</div>';
			}

			if ($minmax_get_html) {
				$meta2 = '<div class="xchange_info_line">' . $minmax_get_html . '</div>';
			}

		} else {

			$meta1 = '
			<div class="hexch_info_line">
				' . $minmax_give_html . '
			</div>';

			$meta2 = '
			<div class="hexch_info_line">
				' . $minmax_get_html . '
			</div>';

		}

		$otherdir = '';
		$show_otherdir = intval($premiumbox->get_option('exchange', 'otherdir'));
		if ($show_otherdir > 0) {
			$currency_id_give = $direction->currency_id_give;
			$currency_id_get = $direction->currency_id_get;
			$where = '';
			if (1 == $show_otherdir) {
				$where = " AND currency_id_give = '$currency_id_give'";
			} elseif (2 == $show_otherdir) {
				$where = " AND currency_id_get = '$currency_id_get'";
			}
			$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' AND direction_status IN('1','2') $where ORDER BY to3_1 ASC");
			if (count($directions) > 0) {
				$otherdir = '
				<div class="other_directions_wrap">
					<div class="other_directions">
						<div class="other_directions_title"><span>' . __('Other directions of exchange', 'pn') . '</span></div>
						<div class="other_directions_in">';

							$i = 0;
							$r = 0;
							$s = 0;
							foreach ($directions as $dir) {
								$v1 = is_isset($v, $dir->currency_id_give);
								$v2 = is_isset($v, $dir->currency_id_get);
								if (isset($v1->id, $v2->id)) { $r++; $s++; $i++;

									$otherdir .= '
									<a href="' . get_exchange_link($dir->direction_name) . '" class="other_direction cldir' . $r . ' aldir' . $s . '">
										<div class="other_direction_ins">
											<div class="other_direction_data">	
												<div class="other_direction_title"><div class="other_direction_logo currency_logo" style="background-image: url(' . get_currency_logo($v1) . ');"></div>	
													<span class="other_direction_title_give">' . get_currency_title($v1) . '</span>
												</div>
											</div>
											<div class="other_direction_arr"></div>
											<div class="other_direction_data">	
												<div class="other_direction_title"><div class="other_direction_logo currency_logo" style="background-image: url(' . get_currency_logo($v2) . ');"></div>	
													<span class="other_direction_title_get">' . get_currency_title($v2) . '</span>
												</div>
											</div>	
												<div class="clear"></div>
										</div>
									</a>
									';

									if (0 == $r%3) { $r = 0; }
									if (0 == $s%2) { $s = 0; }
									if (20 == $i) { break; }
								}
							}

							$otherdir .= '
							<div class="clear"></div>
						</div>
					</div>
				</div>';
			}
		}

		$array = array(
			'[timeline]' => $message,
			'[frozen]' => $frozen,
			'[description]' => $description,
			'[window]' => $window_txt,
			'[other_filter]' => apply_filters('exchange_other_filter', '', $direction, $vd1, $vd2, $cdata),
			'[result]' => '<div class="ajax_post_bids_res"></div>',
			'[check]' => apply_filters('exchange_check_filter', $check, $direction, $vd1, $vd2, $cdata),
			'[remember]' => $remember,
			'[submit]' => $submit,
			'[filters]' => apply_filters('exchange_step1', '', $direction, $vd1, $vd2, $cdata),
			'[reserve]' => '<span class="js_reserve_html">'. $reserve .' '. $currency_code_get .'</span>',
			'[course]' => $course_html,
			'[psys_give]' => $psys_give,
			'[vtype_give]' => $currency_code_give,
			'[currency_code_give]' => $currency_code_give,
			'[psys_get]' => $psys_get,
			'[vtype_get]' => $currency_code_get,
			'[currency_code_get]' => $currency_code_get,
			'[currency_logo_give]' => $currency_logo_give,
			'[currency_logo_get]' => $currency_logo_get,
			'[user_discount]' => $user_discount_html,
			'[user_discount_html]' => $user_discounttext_html,
			'[select_give]' => $select_give,
			'[select_get]' => $select_get,
			'[minmax_give]' => $minmax_give_html,
			'[minmax_get]' => $minmax_get_html,
			'[meta1]' => $meta1,
			'[meta2]' => $meta2,
			'[meta1d]' => $meta1d,
			'[meta2d]' => $meta2d,
			'[otherdir]' => $otherdir,
			'[input_give]' => $input_give,
			'[input_get]' => $input_get,
			'[com_give]' => $com_give,
			'[com_give_text]' => $com_give_text,
			'[com_get]' => $com_get,
			'[com_get_text]' => $com_get_text,
			'[account_give]' => '',
			'[account_get]' => '',
			'[give_field]' => get_doppole_wline($vd1, $direction, 1, $place),
			'[get_field]' => get_doppole_wline($vd2, $direction, 2, $place),
			'[com_class_give]' => $viv_com1_style,
			'[com_class_get]' => $viv_com2_style,
			'[direction_field]' => get_direction_wline($direction, $place),
		);

		$array = apply_filters($place, $array, $direction, $vd1, $vd2, $cdata);
	}

	return $array;
}

function list_direction_fields($direction) {
	global $wpdb;

	$fields = array();

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);

	$f = get_user_fields();

	$direction_id = $direction->id;
	$datas = $wpdb->get_results("
	SELECT * FROM " . $wpdb->prefix . "direction_custom_fields LEFT OUTER JOIN " . $wpdb->prefix . "cf_directions ON(" . $wpdb->prefix . "direction_custom_fields.id = " . $wpdb->prefix ."cf_directions.cf_id) 
	WHERE " . $wpdb->prefix . "direction_custom_fields.auto_status='1' AND " . $wpdb->prefix . "direction_custom_fields.status='1' AND " . $wpdb->prefix . "cf_directions.direction_id = '$direction_id' ORDER BY cf_order ASC");
	foreach ($datas as $data) {

		$data_id = $data->cf_id;
		$cf_now = 'cf' . $data_id;
		$title = pn_strip_input(ctv_ml($data->cf_name));
		$cf_req = $data->cf_req;
		$value = pn_strip_input(get_pn_cookie('cache_' . $cf_now));
		$cf_auto = $data->cf_auto;
		$vid = $data->vid;

		if ($user_id and strlen($value) < 1) {
			if (isset($f[$cf_auto])) {
				$value = strip_uf(is_isset($ui, $cf_auto), $cf_auto);
			}
		}

		$atts = array(
			'name' => $cf_now,
			'cash-id' => $cf_now,
			'id' => $cf_now,
			'autocomplete' => 'off',
			'label' => $title,
			'req' => $cf_req,
			'format' => $cf_auto,
		);

		if (0 == $vid) {
			$atts['type'] = 'text';
			$atts['value'] = get_purse($value, $data);
			$atts['class'] = 'js_' . $cf_now . ' cache_data check_cache';
		} elseif (1 == $vid) {
			$atts['type'] = 'select';
			$atts['value'] = intval($value);
			$atts['class'] = 'js_my_sel js_' . $cf_now . ' cache_data check_cache';
			$options = array('0' => __('No selected', 'pn'));
			$datas = explode("\n", ctv_ml($data->datas));
			foreach ($datas as $key => $da) {
				$key = $key + 1;
				$da = pn_strip_input($da);
				if (strlen($da) > 0) {
					$options[$key] = $da;
				}
			}
			$atts['options'] = $options;
		} elseif (2 == $vid) {
			$atts['type'] = 'textarea';
			$atts['value'] = $value;
			$atts['class'] = 'js_' . $cf_now . ' cache_data check_cache';
		}

		$atts = apply_filters('atts_field_cf', $atts, $data);
		$fields[$cf_now] = $atts;

	}

	$fields = apply_filters('list_direction_fields', $fields, $direction);

	return $fields;
}

function get_direction_wline($direction, $place) {
	global $wpdb;

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);

	$temp = '';

	$fields = list_direction_fields($direction);

	if ('exchange_html_list_ajax' == $place) {
		$class = 'hexch';
	} else {
		$class = 'xchange';
	}

	if (count($fields) > 0) {

		$temp .= '
		<div class="' . $class . '_pers">
			<div class="' . $class . '_pers_ins">								
				<div class="' . $class . '_pers_title">
					<div class="' . $class . '_pers_title_ins">
						<span>' . apply_filters('exchange_personaldata_title', __('User data', 'pn')) . '</span>
					</div>
				</div>
				<div class="' . $class . '_pers_div">
					<div class="' . $class . '_pers_div_ins">';

					foreach ($fields as $field_k => $field_v) {

						$req_html = '';
						if (isset($field_v['req']) and 1 == $field_v['req']) {
							$req_html = '<span class="req">*</span>';
						}

						$help_span = '';
						$h_div = '';
						$has_help_cl = '';
						$tooltip = trim(is_isset($field_v, 'tooltip'));
						if (strlen($tooltip) > 0) {
							$help_span = '<span class="help_tooltip_label"></span>';
							$h_div = '
							<div class="info_window js_window">
								<div class="info_window_ins">
									<div class="info_window_abs"></div>
									'. apply_filters('comment_text', $tooltip) .'
								</div>
							</div>															
							';
							$has_help_cl = 'has_help';
						}

						$type = is_isset($field_v, 'type');
						$standart = array('text', 'textarea', 'select', 'checkbox');
						if (in_array($type, $standart)) {

							$wrap_class = is_isset($field_v, 'wrap_class');

							$temp .= '
							<div class="' . $class . '_pers_line js_line_wrapper ' . $wrap_class . '">
								<div class="' . $class . '_pers_line_ins">';

									$label = trim(is_isset($field_v, 'label'));
									if (strlen($label) > 0) {
										$temp .= '
										<div class="' . $class . '_pers_label">
											<div class="' . $class . '_pers_label_ins">
												<label for="' . is_isset($field_v, 'id') . '"><span class="' . $class . '_label">' . $label . '' . $req_html . ': ' . $help_span . '</span></label>
											</div>
										</div>';
									}

									$choice = is_isset($field_v, 'choice');
									$choice_html = '';
									if (is_array($choice) and count($choice) > 0) {
										if (isset($field_v['class'])) {
											$field_v['class'] .= ' js_choice_input';
										} else {
											$field_v['class'] = 'js_choice_input';
										}
										$choice_html = '
										<div class="js_choice_link">
											<div class="js_choice_link_ins">
												<div class="js_choice_ul">';
												foreach ($choice as $choi) {
													$choice_html .= '<div class="js_choice_line" data-choice="' . $choi['value'] . '">' . $choi['title'] . '</div>';
												}
												$choice_html .= '
												</div>
											</div>
										</div>										
										';
									}

									$temp .= '
									<div class="' . $class . '_pers_input js_wrap_error js_window_wrap">
									';

										if ('text' == $type) {
											$atts = pn_array_unset($field_v, array('options', 'tooltip', 'req', 'label', 'choice', 'format', 'wrap_class'));
											$atts_inline = get_inline_atts($atts);
											$temp .= '<input ' . $atts_inline . '/>';
											$temp .= $h_div;
											$temp .= '<div class="js_error js_' . is_isset($field_v, 'id') . '_error"></div>';
										} elseif ('checkbox' == $type) {
											$atts = pn_array_unset($field_v, array('options', 'tooltip', 'req', 'label', 'text', 'choice', 'format', 'wrap_class'));
											$atts_inline = get_inline_atts($atts);
											$temp .= '<label><input ' . $atts_inline . '/> ' . is_isset($field_v, 'text') . '</label>';
										} elseif ('textarea' == $type) {
											$atts = pn_array_unset($field_v, array('options', 'tooltip', 'req', 'value', 'type', 'label', 'choice', 'format', 'wrap_class'));
											$atts_inline = get_inline_atts($atts);
											$temp .= '<textarea ' . $atts_inline . '/>' . is_isset($field_v, 'value') . '</textarea>';
											$temp .= $h_div;
											$temp .= '<div class="js_error js_' . is_isset($field_v, 'id') . '_error"></div>';
										} elseif ('select' == $type) {
											$atts = pn_array_unset($field_v, array('options', 'tooltip', 'req', 'value', 'type', 'label', 'choice', 'format', 'wrap_class'));
											$atts_inline = get_inline_atts($atts);
											$value = is_isset($field_v, 'value');
											$options = is_isset($field_v, 'options');
											$temp .= '
											<select ' . $atts_inline . '>';
												if (is_array($options)) {
													foreach ($options as $option_k => $option_v) {
														$temp .= '<option value="' . $option_k . '" ' . selected($option_k, $value, false) . '>' . $option_v . '</option>';
													}
												}
											$temp .= '	
											</select>	
											';
											$temp .= '<div class="js_error js_' . is_isset($field_v, 'id') . '_error"></div>';
										}
										$temp .= $choice_html;
										$temp .= apply_filters('cf_line_inside', '', $field_k, $field_v, $direction);

									$temp .= '
										<div class="clear"></div>
									</div>
								</div>
							</div>	
							';

						} else {

							$temp .= apply_filters('cf_line', '', $field_k, $field_v, $direction);

						}

					}

					$temp .= '
						<div class="clear"></div>
					</div>
				</div>											
			</div>
		</div>
		';

	}

	return $temp;
}

function list_currency_fields($vd, $direction, $side_id) {
	global $wpdb;

	$fields = array();

	$currency_id = $vd->id;

	/* accounts */
	if (1 == $side_id) {
		$show_account = apply_filters('form_bids_account_give', $vd->show_give, $direction, $vd);
		$label_account = __('From account', 'pn');
		$name_account = 'account1';
	} else {
		$show_account = apply_filters('form_bids_account_get', $vd->show_get, $direction, $vd);
		$label_account = __('Into account', 'pn');
		$name_account = 'account2';
	}
	if ($show_account) {
		$account_field = array(
			'name' => $name_account,
			'type' => 'text',
			'cash-id' => 'account' . $currency_id,
			'id' => 'account' . $side_id,
			'autocomplete' => 'off',
			'label' => $label_account,
			'value' => get_purse(get_pn_cookie('cache_account' . $currency_id), $vd),
			'class' => 'js_account' . $side_id . ' cache_data check_cache',
			'req' => 1,
			'format' => 'purse',
		);
		$account_field = apply_filters('atts_field_account', $account_field, $vd, $direction, $side_id);
		$fields[$name_account] = $account_field;
	}
	/* end accounts */

	/* currency fields */
	$where = '';
	if (1 == $side_id) {
		$where .= " AND " . $wpdb->prefix . "cf_currency.place_id IN('0','1')";
		$orderby = 'cf_order_give';
	} else {
		$where .= " AND " . $wpdb->prefix . "cf_currency.place_id IN('0','2')";
		$orderby = 'cf_order_get';
	}
	$sql ="
	SELECT * FROM " . $wpdb->prefix . "currency_custom_fields
	LEFT OUTER JOIN " . $wpdb->prefix . "cf_currency
	ON(" . $wpdb->prefix . "currency_custom_fields.id = " . $wpdb->prefix . "cf_currency.cf_id)
	WHERE " . $wpdb->prefix . "currency_custom_fields.auto_status='1' AND " . $wpdb->prefix . "currency_custom_fields.status='1' AND " . $wpdb->prefix . "cf_currency.currency_id = '$currency_id' $where
	ORDER BY $orderby ASC
	";
	$datas = $wpdb->get_results($sql);
	foreach ($datas as $data) {

		$place_id = $data->place_id;
		$data_id = $data->cf_id;
		$cf_now = 'cfgive' . $data_id;
		if (2 == $place_id) {
			$cf_now = 'cfget' . $data_id;
		}

		$title = pn_strip_input(ctv_ml($data->cf_name));
		$cf_req = $data->cf_req;
		$value = pn_strip_input(get_pn_cookie('cache_' . $cf_now));
		$vid = $data->vid;
		$atts = array(
			'name' => $cf_now,
			'cash-id' => $cf_now,
			'id' => $cf_now,
			'autocomplete' => 'off',
			'label' => $title,
			'req' => $cf_req,
			'format' => '',
		);
		if (0 == $vid) {
			$atts['type'] = 'text';
			$atts['value'] = get_purse($value, $data);
			$atts['class'] = 'js_' . $cf_now . ' cache_data check_cache';
		} elseif (1 == $vid) {
			$atts['type'] = 'select';
			$atts['value'] = intval($value);
			$atts['class'] = 'js_my_sel js_' . $cf_now . ' cache_data check_cache';
			$options = array('0' => __('No selected', 'pn'));
			$datas = explode("\n", ctv_ml($data->datas));
			foreach ($datas as $key => $da) {
				$key = $key + 1;
				$da = pn_strip_input($da);
				if (strlen($da) > 0) {
					$options[$key] = $da;
				}
			}
			$atts['options'] = $options;
		} elseif (2 == $vid) {
			$atts['type'] = 'textarea';
			$atts['value'] = $value;
			$atts['class'] = 'js_' . $cf_now . ' cache_data check_cache';
		}
		$atts = apply_filters('atts_field_cfc', $atts, $data, $side_id);
		$fields[$cf_now] = $atts;

	}
	/* end currency fields */

	$fields = apply_filters('list_currency_fields', $fields, $vd, $direction, $side_id);

	return $fields;
}

function get_doppole_wline($vd, $direction, $side_id, $place) {
	global $wpdb;

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);

	$temp = '';

	$fields = list_currency_fields($vd, $direction, $side_id);

	if ('exchange_html_list_ajax' == $place) {
		$class = 'hexch';
	} else {
		$class = 'xchange';
	}

	foreach ($fields as $field_k => $field_v) {

		$req_html = '';
		if (isset($field_v['req']) and 1 == $field_v['req']) {
			$req_html = '<span class="req">*</span>';
		}

		$help_span = '';
		$h_div = '';
		$has_help_cl = '';
		$tooltip = trim(is_isset($field_v, 'tooltip'));
		if (strlen($tooltip) > 0) {
			$help_span = '<span class="help_tooltip_label"></span>';
			$h_div = '
			<div class="info_window js_window">
				<div class="info_window_ins">
					<div class="info_window_abs"></div>
					' . apply_filters('comment_text', $tooltip) . '
				</div>
			</div>															
			';
			$has_help_cl = 'has_help';
		}

		$type = is_isset($field_v, 'type');
		$standart = array('text', 'textarea', 'select', 'checkbox');
		if (in_array($type, $standart)) {

			$wrap_class = is_isset($field_v, 'wrap_class');

			$temp .= '
			<div class="' . $class . '_curs_line js_line_wrapper ' . $wrap_class . '">
				<div class="' . $class . '_curs_line_ins">';

					$label = trim(is_isset($field_v,'label'));
					if (strlen($label) > 0) {
						$temp .= '
						<div class="' . $class . '_curs_label">
							<div class="' . $class . '_curs_label_ins">
								<label for="' . is_isset($field_v, 'id') . '"><span class="' . $class . '_label">' . $label . '' . $req_html . ': ' . $help_span . '</span></label>
							</div>
						</div>';
					}

					$choice = is_isset($field_v, 'choice');
					$choice_html = '';
					if (is_array($choice) and count($choice) > 0) {
						if (isset($field_v['class'])) {
							$field_v['class'] .= ' js_choice_input';
						} else {
							$field_v['class'] = 'js_choice_input';
						}
						$choice_html = '
						<div class="js_choice_link">
							<div class="js_choice_link_ins">
								<div class="js_choice_ul">';
								foreach ($choice as $choi) {
									$choice_html .= '<div class="js_choice_line" data-choice="' . $choi['value'] . '">' . $choi['title'] . '</div>';
								}
								$choice_html .= '
								</div>
							</div>
						</div>										
						';
					}

					$temp .= '
					<div class="' . $class . '_curs_input js_wrap_error js_window_wrap">
					';

						if ('text' == $type) {
							$atts = pn_array_unset($field_v, array('options', 'tooltip', 'req', 'label', 'choice', 'format', 'wrap_class'));
							$atts_inline = get_inline_atts($atts);
							$temp .= '<input ' . $atts_inline . '/>';
							$temp .= $h_div;
							$temp .= '<div class="js_error js_' . is_isset($field_v, 'id') . '_error"></div>';
						} elseif ('checkbox' == $type) {
							$atts = pn_array_unset($field_v, array('options', 'tooltip', 'req', 'label', 'text', 'choice', 'format', 'wrap_class'));
							$atts_inline = get_inline_atts($atts);
							$temp .= '<label><input ' . $atts_inline . '/> ' . is_isset($field_v, 'text') . '</label>';
						} elseif ('textarea' == $type) {
							$atts = pn_array_unset($field_v, array('options', 'tooltip', 'req', 'value', 'type', 'label', 'choice', 'format', 'wrap_class'));
							$atts_inline = get_inline_atts($atts);
							$temp .= '<textarea ' . $atts_inline . '/>' . is_isset($field_v, 'value') . '</textarea>';
							$temp .= $h_div;
							$temp .= '<div class="js_error js_' . is_isset($field_v, 'id') . '_error"></div>';
						} elseif ('select' == $type) {
							$atts = pn_array_unset($field_v, array('options', 'tooltip', 'req', 'value', 'type', 'label', 'choice', 'format', 'wrap_class'));
							$atts_inline = get_inline_atts($atts);
							$value = is_isset($field_v, 'value');
							$options = is_isset($field_v, 'options');
							$temp .= '
							<select ' . $atts_inline . '>';
								if (is_array($options)) {
									foreach ($options as $option_k => $option_v) {
										$temp .= '<option value="' . $option_k . '" ' . selected($option_k, $value, false) . '>' . $option_v . '</option>';
									}
								}
							$temp .= '	
							</select>	
							';
							$temp .= '<div class="js_error js_'. is_isset($field_v,'id') .'_error"></div>';
						}

						$temp .= $choice_html;
						$temp .= apply_filters('cfc_line_inside', '', $field_k, $field_v, $vd, $direction, $side_id);

					$temp .= '
						<div class="clear"></div>
					</div>
				</div>
			</div>	
			';

		} else {

			$temp .= apply_filters('cfc_line', '', $field_k, $field_v, $vd, $direction, $side_id);

		}

	}

	return $temp;
}

add_filter('exchange_input', 'def_exchange_input', 10, 4);
function def_exchange_input($html, $place, $cdata, $calc_data) {

	$sum1 = is_sum(is_isset($cdata, 'sum1'));
	$sum1c = is_sum(is_isset($cdata, 'sum1c'));
	$sum2 = is_sum(is_isset($cdata, 'sum2'));
	$sum2c = is_sum(is_isset($cdata, 'sum2c'));

	if ('give' == $place) {
		$atts = array(
			'type' => 'text',
			'name' => 'sum1',
			'cash-id' => 'sum',
			'data-decimal' => $cdata['decimal_give'],
			'class' => 'js_sum_val js_decimal js_sum1 cache_data',
			'autocomplete' => 'off',
			'value' => $sum1,
		);
		if (1 == $cdata['dis1']) {
			$atts['disabled'] = 'disabled';
		}
		$atts = apply_filters('atts_suminput_sum1', $atts, $place, $cdata, $calc_data);
		$atts_inline = get_inline_atts($atts);

		$html = '<input ' . $atts_inline . '/>';
	} elseif ('give_com' == $place) {
		$atts = array(
			'type' => 'text',
			'name' => '',
			'data-decimal' => $cdata['decimal_give'],
			'class' => 'js_sum_val js_decimal js_sum1c',
			'autocomplete' => 'off',
			'value' => $sum1c,
		);
		if (1 == $cdata['dis1c']) {
			$atts['disabled'] = 'disabled';
		}
		$atts = apply_filters('atts_suminput_sum1c', $atts, $place, $cdata, $calc_data);
		$atts_inline = get_inline_atts($atts);

		$html = '<input ' . $atts_inline . '/>';
	} elseif ('get' == $place) {
		$atts = array(
			'type' => 'text',
			'name' => '',
			'data-decimal' => $cdata['decimal_get'],
			'class' => 'js_sum_val js_decimal js_sum2',
			'autocomplete' => 'off',
			'value' => $sum2,
		);
		if (1 == $cdata['dis2']) {
			$atts['disabled'] = 'disabled';
		}
		$atts = apply_filters('atts_suminput_sum2', $atts, $place, $cdata, $calc_data);
		$atts_inline = get_inline_atts($atts);

		$html = '<input ' . $atts_inline . '/>';
	} elseif ('get_com' == $place) {
		$atts = array(
			'type' => 'text',
			'name' => '',
			'data-decimal' => $cdata['decimal_get'],
			'class' => 'js_sum_val js_decimal js_sum2c',
			'autocomplete' => 'off',
			'value' => $sum2c,
		);
		if (1 == $cdata['dis2c']) {
			$atts['disabled'] = 'disabled';
		}
		$atts = apply_filters('atts_suminput_sum2c', $atts, $place, $cdata, $calc_data);
		$atts_inline = get_inline_atts($atts);

		$html = '<input ' . $atts_inline . '/>';
	}

	return $html;
}

add_action('premium_js','premium_js_choiceinput');
function premium_js_choiceinput() {
?>
jQuery(function($) {

    $(document).on('click', function(event) {
        if ($(event.target).closest(".js_choice_link").length) return;
        $('.js_choice_ul').hide();

        event.stopPropagation();
    });

	$(document).on('click', '.js_choice_link', function() {

		$(this).parents('.js_window_wrap').find('.js_choice_ul').show();

		return false;
	});

	$(document).on('click', '.js_choice_line', function() {

		var account = $(this).attr('data-choice');
		$(this).parents('.js_window_wrap').find('input').val(account).trigger("change");
		$('.js_choice_ul').hide();

		return false;
	});

});
<?php
} 