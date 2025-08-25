<?php
if (!defined('ABSPATH')) exit();

add_action('premium_js', 'premium_js_exchange_table');
function premium_js_exchange_table() {

    if (in_array(get_type_table(), ['1', '4', '5'])) {
        // @formatter:off
    ?>
    jQuery(function ($) {

        function create_icons() {

            $('.js_icon_left').hide();
            $('.js_icon_left:first').show();

            $('.js_icon_left').each(function () {

                var vtype = $(this).attr('data-type');
                if ($('.js_item_left_' + vtype).length > 0) {
                    $('.js_icon_left_' + vtype).show();
                }

            });

            $('.js_icon_right').hide();
            $('.js_icon_right:first').show();

            $('.js_icon_right').each(function () {

                var vtype = $(this).attr('data-type');
                if ($('.js_item_right_' + vtype + ':visible').length > 0) {
                    $('.js_icon_right_' + vtype).show();
                }

            });

            if ($('.js_icon_right.active:visible').length == 0) {

                $('.js_item_right').show();
                $('.js_icon_right').removeClass('active');
                $('.js_icon_right:first').addClass('active');

            }

        }

        create_icons();

        function go_active_left_col() {

            if ($('.xtt_html_abs').length > 0) {
                if ($('.js_item_left:visible.active').length == 0) {
                    $('.js_item_left').removeClass('active');
                    $('.js_item_left:visible:first').addClass('active');
                }
                var valid = $('.js_item_left.active').attr('data-id');

                $('.xtt_html_abs').show();
                var param = 'id=' + valid;
                $.ajax({
                    type: "POST",
                    url: "<?php echo get_pn_action('table1_change', 'post'); ?>",
                    dataType: 'json',
                    data: param,
                    error: function (res, res2, res3) {
                        <?php do_action('pn_js_error_response', 'ajax'); ?>
                    },
                    success: function (res) {
                        $('.xtt_html_abs').hide();
                        if (res['status'] == 'success') {
                            $('#xtt_right_col_html').html(res['html']);
                        }
                        create_icons();
                    }
                });
            }

        }

        $(document).on('click', ".js_item_left", function () {

            if (!$(this).hasClass('active')) {
                $(".js_item_left").removeClass('active');
                $(this).addClass('active');
                go_active_left_col();
            }

            return false;
        });

        $(document).on('click', ".js_icon_left", function () {

            if (!$(this).hasClass('active')) {
                var vtype = $(this).attr('data-type');
                $(".js_icon_left").removeClass('active');
                $(this).addClass('active');
                if (vtype == 0) {
                    $('.js_item_left').show();
                } else {
                    $('.js_item_left').hide();
                    $('.js_item_left_' + vtype).show();
                }
                go_active_left_col();
            }

            return false;
        });

        $(document).on('click', ".js_icon_right", function () {

            if (!$(this).hasClass('active')) {
                var vtype = $(this).attr('data-type');
                $(".js_icon_right").removeClass('active');
                $(this).addClass('active');
                if (vtype == 0) {
                    $('.js_item_right').show();
                } else {
                    $('.js_item_right').hide();
                    $('.js_item_right_' + vtype).show();
                }
            }

            return false;
        });

        $(document).on('click', ".xtt_title_link", function () {

            $('.xtt_title_link').removeClass('active');
            $(this).addClass('active');
            var id = $(this).attr('data-id');

            $(document).PHPCookie('set', {
                key: "table5_select",
                value: id,
                domain: '<?php echo PN_SITE_URL; ?>',
                days: '30'
            });

            $('.js_check_reserve').each(function () {

                var data_now = $(this).attr('data-reserve');
                if (id == 'rate') {
                    data_now = $(this).attr('data-rate');
                }
                $(this).html(data_now);

            });

            return false;
        });

    });
    <?php
        // @formatter:on
    }
}

add_filter('exchange_table_type1', 'get_exchange_table1', 10, 4);
add_filter('exchange_table_type4', 'get_exchange_table1', 10, 4);
add_filter('exchange_table_type5', 'get_exchange_table1', 10, 4);
function get_exchange_table1($temp, $def_cur_from = ' ', $def_cur_to = '', $def_direction_id = '') {
    global $wpdb, $premiumbox;

    $temp = '';

    $ui = wp_get_current_user();
    $user_id = intval($ui->ID);

    $cur_from = strtoupper(trim($def_cur_from));
    $cur_to = strtoupper(trim($def_cur_to));

    $v = get_currency_data();

    $where = get_directions_where('home');

    $all_vtypes = array();
    foreach ($v as $vs) {
        $currency_code = $vs->currency_code_title;
        $vt_arr = apply_filters('all_vtype_line', array($currency_code => $currency_code), $vs);
        foreach ($vt_arr as $vt_arr_key => $vt_arr_val) {
            $vt_arr_key = str_replace('.', '_', $vt_arr_key);
            $all_vtypes[$vt_arr_key] = $vt_arr_val;
        }
    }

    $directions = array();
    $dirs = array();
    $dirs_data = array();
    $directions_arr = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where");
    foreach ($directions_arr as $dir) {
        if (isset($v[$dir->currency_id_give], $v[$dir->currency_id_get])) {
            $output = apply_filters('get_direction_output', 1, $dir, 'home');
            if (1 == $output) {
                $dirs_data[$dir->id] = $dir;
                $dirs[$dir->id] = intval($v[$dir->currency_id_give]->t1_1);
            }
        }
    }

    asort($dirs);

    foreach ($dirs as $dir_id => $order) {
        $dir = $dirs_data[$dir_id];
        $directions[$dir->currency_id_give] = $dir;
    }

    $currency_id_give = 0;
    $r = 0;
    $f_cur_from = '';
    if (is_array($directions)) {
        foreach ($directions as $direction_data) {
            $r++;

            $dir_currency_id_give = $direction_data->currency_id_give;
            $vd_now = $v[$dir_currency_id_give];

            if (1 == $r) {
                $currency_id_give = $dir_currency_id_give;
                $f_cur_from = $vd_now->xml_value;
            }

            if ($cur_from) {
                if ($cur_from == strtoupper($vd_now->xml_value)) {
                    $currency_id_give = $dir_currency_id_give;
                }
            }

        }
    }

    $directions2 = apply_filters('get_directions_table1', array(), 'home', $where, $v, $currency_id_give);

    if (!$cur_from) {
        $cur_from = $f_cur_from;
    }

    $tableicon = get_icon_for_table();
    $type_table = get_type_table();

    $temp .= '	
	<div class="xchange_type_table tbl' . $type_table . '">
		<div class="xchange_type_table_ins">';

    $exchange_head = '';
    $hidecurrtype = get_hidecurrtype_table();
    if (0 == $hidecurrtype) {

        $exchange_head = '	
				<div class="xtt_icon_wrap">
					<div class="xtt_left_col_icon"><div class="xtt_left_col_icon_ins">
						<div class="tbl_icon active js_icon_left js_icon_left_0" data-type="0"><div class="tbl_icon_ins"><div class="tbl_icon_abs"></div>' . __('All', 'pn') . '</div></div>';

        foreach ($all_vtypes as $vtype_key => $vtype_title) {
            $exchange_head .= '<div class="tbl_icon js_icon_left js_icon_left_' . str_replace('.', '_', $vtype_key) . '" data-type="' . str_replace('.', '_', $vtype_key) . '" style="display: none;"><div class="tbl_icon_ins"><div class="tbl_icon_abs"></div>' . $vtype_title . '</div></div>';
        }

        $exchange_head .= '
						<div class="clear"></div>
					</div></div>
					<div class="xtt_right_col_icon"><div class="xtt_right_col_icon_ins">
						<div class="tbl_icon active js_icon_right js_icon_right_0" data-type="0"><div class="tbl_icon_ins"><div class="tbl_icon_abs"></div>' . __('All', 'pn') . '</div></div>';

        foreach ($all_vtypes as $vtype_key => $vtype_title) {
            $exchange_head .= '<div class="tbl_icon js_icon_right js_icon_right_' . str_replace('.', '_', $vtype_key) . '" data-type="' . str_replace('.', '_', $vtype_key) . '" style="display: none;"><div class="tbl_icon_ins"><div class="tbl_icon_abs"></div>' . $vtype_title . '</div></div>';
        }

        $exchange_head .= '
						<div class="clear"></div>
					</div></div>
						<div class="clear"></div>
				</div>';

    }
    $temp .= apply_filters('exchange_table_ctypes', $exchange_head, $all_vtypes);

    $temp .= '
			<div class="xtt_table_wrap">';

    $temp .= apply_filters('tbl' . $type_table . '_exchange_headname', '');

    $temp .= '
				<div class="xtt_table_body_wrap">
					<div class="xtt_html_abs"></div>';

    $temp .= '
						<div class="xtt_left_col_table js_col_one"><div class="xtt_left_col_table_ins">';
    $temp .= apply_filters('tbl' . $type_table . '_exchange_leftcol', '');

    $temp .= '<div id="xtt_left_col_html">' . get_table1_leftcol($directions, $v, 'table' . $type_table, $tableicon, $cur_from, 'tbl' . $type_table . '_leftcol_data') . '</div>';

    $temp .= '
						</div></div>
						
						<div class="xtt_right_col_table js_col_one"><div class="xtt_right_col_table_ins">';
    $temp .= apply_filters('tbl' . $type_table . '_exchange_rightcol', '');

    $temp .= '<div id="xtt_right_col_html">' . get_table1_rightcol($directions2, $currency_id_give, $v, 'table' . $type_table, $tableicon, 'tbl' . $type_table . '_rightcol_data', $cur_to) . '</div>';

    $temp .= '
						</div></div>';

    if (5 == $type_table) {

        $temp .= '
							<div class="xtt_data_col_clear"></div>
							<div class="xtt_data_col_table js_col_one"><div class="xtt_data_col_table_ins">';

        $temp .= apply_filters('tbl5_exchange_datacol', '');

        if (function_exists('table_exchange_widget')) {
            $temp .= table_exchange_widget($def_cur_from, $def_cur_to, $def_direction_id);
        }

        $temp .= '
							</div></div>
							';

    }

    $temp .= '		
					<div class="clear"></div>
				</div>';

    $temp .= '	
				<div class="clear"></div>
			</div>';

    $temp .= '		
		</div>
	</div>';

    return $temp;
}

add_filter('tbl1_exchange_headname', 'def_tbl1_exchange_headname');
add_filter('tbl4_exchange_headname', 'def_tbl1_exchange_headname');
function def_tbl1_exchange_headname($exchange_headname) {

    $insert = apply_filters('insert_table_col_title', 0);
    if (1 != $insert) {

        $new_exchange_headname = '
		<div class="xtt_table_title_wrap">
			<div class="xtt_left_col_title">
				<div class="xtt_table_title1">
					<span>' . __('You send', 'pn') . '</span>
				</div>
			</div>
			<div class="xtt_right_col_title">
				<div class="xtt_table_title2">
					<span>' . __('You receive', 'pn') . '</span>
				</div>
				<div class="xtt_table_title3">
					<span>' . __('Rate', 'pn') . '</span>
				</div>
				<div class="xtt_table_title4">
					<span>' . __('Reserve', 'pn') . '</span>
				</div>			
					<div class="clear"></div>
			</div>
				<div class="clear"></div>
		</div>';

        $exchange_headname .= $new_exchange_headname;

    }

    return $exchange_headname;
}

add_filter('tbl1_exchange_leftcol', 'def_tbl1_exchange_leftcol');
add_filter('tbl4_exchange_leftcol', 'def_tbl1_exchange_leftcol');
add_filter('tbl5_exchange_leftcol', 'def_tbl1_exchange_leftcol');
function def_tbl1_exchange_leftcol($exchange_headname) {

    $insert = apply_filters('insert_table_col_title', 0);
    if (1 == $insert) {

        $exchange_headname .= '
		<div class="xtt_left_incol_title">
			<div class="xtt_table_title1">
				<span>' . __('You send', 'pn') . '</span>
			</div>
				<div class="clear"></div>
		</div>
		';

    }

    return $exchange_headname;
}

add_filter('tbl1_exchange_rightcol', 'def_tbl1_exchange_rightcol');
add_filter('tbl4_exchange_rightcol', 'def_tbl1_exchange_rightcol');
function def_tbl1_exchange_rightcol($exchange_headname) {

    $insert = apply_filters('insert_table_col_title', 0);
    if (1 == $insert) {

        $exchange_headname .= '
		<div class="xtt_right_incol_title">
			<div class="xtt_table_title2">
				<span>' . __('You receive', 'pn') . '</span>
			</div>
			<div class="xtt_table_title3">
				<span>' . __('Rate', 'pn') . '</span>
			</div>
			<div class="xtt_table_title4">
				<span>' . __('Reserve', 'pn') . '</span>
			</div>			
				<div class="clear"></div>
		</div>
		';

    }

    return $exchange_headname;
}

function get_table5_current_select() {

    $select = pn_strip_input(get_pn_cookie('table5_select'));
    if (!$select) {
        $select = apply_filters('table5_current_select', 'reserve');
    }
    if ('reserve' != $select) {
        $select = 'rate';
    }

    return $select;
}

add_filter('tbl5_exchange_headname', 'def_tbl5_exchange_headname');
function def_tbl5_exchange_headname($exchange_headname) {

    $insert = apply_filters('insert_table_col_title', 0);
    if (1 != $insert) {

        $new_exchange_headname = '
		<div class="xtt_table_title_wrap">
			<div class="xtt_left_col_title">
				<div class="xtt_table_title1">
					<span>' . __('You send', 'pn') . '</span>
				</div>
			</div>
			<div class="xtt_right_col_title">
				<div class="xtt_table_title2">
					<span>' . __('You receive', 'pn') . '</span>
				</div>
				';

        $select = get_table5_current_select();

        $cl1 = $cl2 = '';
        if ('rate' == $select) {
            $cl1 = '';
            $cl2 = 'active';
        } else {
            $cl1 = 'active';
            $cl2 = '';
        }

        $new_exchange_headname .= '
				<div class="xtt_title_link_wrap">
					<a href="#" class="xtt_title_link xtt_title_link1 ' . $cl1 . '" data-id="reserve">' . __('Reserve', 'pn') . '</a>
					<a href="#" class="xtt_title_link xtt_title_link2 ' . $cl2 . '" data-id="rate">' . __('Rate', 'pn') . '</a>
				</div>	
				';

        $new_exchange_headname .= '
					<div class="clear"></div>
			</div>
				<div class="clear"></div>
		</div>';

        $exchange_headname .= $new_exchange_headname;

    }

    return $exchange_headname;
}

add_filter('tbl5_exchange_rightcol', 'def_tbl5_exchange_rightcol');
function def_tbl5_exchange_rightcol($exchange_headname) {

    $insert = apply_filters('insert_table_col_title', 0);
    if (1 == $insert) {

        $new_exchange_headname = '
		<div class="xtt_right_incol_title">
			<div class="xtt_table_title2">
				<span>' . __('You receive', 'pn') . '</span>
			</div>
			';

        $select = get_table5_current_select();

        $cl1 = $cl2 = '';
        if ('rate' == $select) {
            $cl1 = '';
            $cl2 = 'active';
        } else {
            $cl1 = 'active';
            $cl2 = '';
        }

        $new_exchange_headname .= '
			<div class="xtt_title_link_wrap">
				<a href="#" class="xtt_title_link xtt_title_link1 ' . $cl1 . '" data-id="reserve">' . __('Reserve', 'pn') . '</a>
				<a href="#" class="xtt_title_link xtt_title_link2 ' . $cl2 . '" data-id="rate">' . __('Rate', 'pn') . '</a>
			</div>	
			';

        $new_exchange_headname .= '
				<div class="clear"></div>
		</div>
		';

        $exchange_headname .= $new_exchange_headname;

    }

    return $exchange_headname;
}

function get_table1_leftcol($directions, $v, $place, $tableicon, $cur_from, $filter_name) {

    $temp = '';
    if (is_array($directions)) {
        foreach ($directions as $direction_data) {

            $currency_id_give = $direction_data->currency_id_give;
            $vd1 = $v[$currency_id_give];

            $cl = array('js_item_left');
            if ($cur_from) {
                if ($cur_from == strtoupper($vd1->xml_value)) {
                    $cl[] = 'active';
                }
            }

            $dir_c = is_course_direction($direction_data, $vd1, '', $place);
            $course_give = is_isset($dir_c, 'give');

            $currency_code = is_site_value($vd1->currency_code_title);
            $ctypes = array($currency_code);
            $ctypes = apply_filters('exchange_table_ct', $ctypes, $vd1);
            foreach ($ctypes as $ctype) {
                $cl[] = 'js_item_left_' . str_replace('.', '_', $ctype);
            }

            $temp .= '
			<!-- one item -->
			<div class="' . implode(' ', $cl) . '" data-id="' . $currency_id_give . '">
				<div class="xtt_one_line_left">';

            $leftcol_data = array(
                'line_abs1' => '<div class="xtt_one_line_abs"></div>',
                'line_abs2' => '<div class="xtt_one_line_abs2"></div>',
                'icon' => '
						<div class="xtt_one_line_ico_left"> 
							<div class="xtt_change_ico currency_logo" style="background-image: url(' . get_currency_logo($vd1, $tableicon) . ');"></div>
						</div>',
                'title' => '
						<div class="xtt_one_line_name_left">
							<div class="xtt_one_line_name">
								' . get_currency_title($vd1) . '
							</div>
						</div>
						',
            );
            $leftcol_data = apply_filters($filter_name, $leftcol_data, $direction_data, $vd1, '', $course_give, $cur_from);
            foreach ($leftcol_data as $value) {
                $temp .= $value;
            }

            $temp .= '
						<div class="clear"></div>
				</div>	
			</div>
			<!-- end one item -->
			';
        }
    }

    return $temp;
}

function get_table1_rightcol($directions, $currency_id_give, $v, $place, $tableicon, $filter_name, $cur_to) {

    $temp = '';
    $cur_to = trim($cur_to);
    $type_table = get_type_table();
    if (isset($directions[$currency_id_give])) {
        foreach ($directions[$currency_id_give] as $direction_data) {
            $valsid1 = $direction_data->currency_id_give;
            $valsid2 = $direction_data->currency_id_get;
            $vd1 = is_isset($v, $valsid1);
            $vd2 = is_isset($v, $valsid2);

            $v_title1 = get_currency_title($vd1);
            $v_title2 = get_currency_title($vd2);

            $dir_c = is_course_direction($direction_data, $vd1, $vd2, $place);
            $course_give = is_isset($dir_c, 'give');
            $course_get = is_isset($dir_c, 'get');

            $cl = array('js_exchange_link', 'js_item_right');
            if ($cur_to and 5 == $type_table) {
                if ($cur_to == strtoupper($vd2->xml_value)) {
                    $cl[] = 'active';
                }
            }

            $currency_code = is_site_value($vd2->currency_code_title);
            $ctypes = array($currency_code);
            $ctypes = apply_filters('exchange_table_ct', $ctypes, $vd2);
            foreach ($ctypes as $ctype) {
                $cl[] = 'js_item_right_' . str_replace('.', '_', $ctype);
            }

            $temp .= '
			<!-- one item -->
			<a href="' . get_exchange_link($direction_data->direction_name) . '" class="' . implode(' ', $cl) . '" data-direction-id="' . $direction_data->id . '">
				<div class="xtt_one_line_right">
			';

            $rightcol_data = array(
                'line_abs1' => '<div class="xtt_one_line_abs"></div>',
                'line_abs2' => '<div class="xtt_one_line_abs2"></div>',
                'icon' => '
					<div class="xtt_one_line_ico_right"> 
						<div class="xtt_change_ico currency_logo" style="background-image: url(' . get_currency_logo($vd2, $tableicon) . ');"></div>
					</div>															
					',
                'title' => '
					<div class="xtt_one_line_name_right">
						<div class="xtt_one_line_name">
							' . $v_title2 . '
						</div>
					</div>														
					',
            );
            $rightcol_data = apply_filters($filter_name, $rightcol_data, $direction_data, $vd1, $vd2, $course_get, $cur_to);
            foreach ($rightcol_data as $value) {
                $temp .= $value;
            }

            $temp .= '
						<div class="clear"></div>
				</div>	
			</a>
			<!-- end one item -->											
			';
        }
    }

    return $temp;
}

add_filter('tbl1_leftcol_data', 'def_tbl1_leftcol_data', 10, 6);
function def_tbl1_leftcol_data($data, $direction_data, $vd1, $vd2, $course, $cur) {

    $data['course'] = '
	<div class="xtt_one_line_curs_left">
		<div class="xtt_one_line_curs">
			' . is_out_sum($course, $vd1->currency_decimal, 'course') . '
		</div>	
	</div>												
	';

    return $data;
}

add_filter('tbl1_rightcol_data', 'def_tbl1_rightcol_data', 10, 6);
function def_tbl1_rightcol_data($data, $direction_data, $vd1, $vd2, $course, $cur) {

    $n_data = array();
    $n_data['course'] = '
	<div class="xtt_one_line_curs_right">
		<div class="xtt_one_line_curs">
			' . is_out_sum($course, $vd2->currency_decimal, 'course') . '
		</div>	
	</div>												
	';
    $n_data['reserve'] = '
	<div class="xtt_one_line_reserv_right">
		<div class="xtt_one_line_reserv">
			' . is_out_sum(get_direction_reserve($vd1, $vd2, $direction_data), $vd2->currency_decimal, 'reserv') . '
		</div>	
	</div>															
	';

    $data = pn_array_insert($data, 'title', $n_data);

    return $data;
}

add_filter('tbl4_rightcol_data', 'def_tbl4_rightcol_data', 10, 6);
function def_tbl4_rightcol_data($data, $direction_data, $vd1, $vd2, $course_get, $cur) {

    $dir_c = is_course_direction($direction_data, $vd1, $vd2, 'table4');
    $course_give = is_isset($dir_c, 'give');
    $course_get = is_isset($dir_c, 'get');

    $n_data = array();
    $n_data['course'] = '
	<div class="xtt_one_line_curs_right">
		<div class="xtt_one_line_curs">
			<span class="xtt_one_line_curs_give">' . is_out_sum($course_give, $vd1->currency_decimal, 'course') . '</span> <span class="xtt_one_line_curs_arrow">&rarr;</span> <span class="xtt_one_line_curs_get">' . is_out_sum($course_get, $vd2->currency_decimal, 'course') . '</span>
		</div>	
	</div>												
	';
    $n_data['reserve'] = '
	<div class="xtt_one_line_reserv_right">
		<div class="xtt_one_line_reserv">
			' . is_out_sum(get_direction_reserve($vd1, $vd2, $direction_data), $vd2->currency_decimal, 'reserv') . '
		</div>	
	</div>															
	';

    $data = pn_array_insert($data, 'title', $n_data);

    return $data;
}

add_filter('tbl5_rightcol_data', 'def_tbl5_rightcol_data', 10, 6);
function def_tbl5_rightcol_data($data, $direction_data, $vd1, $vd2, $course_get, $cur) {

    $dir_c = is_course_direction($direction_data, $vd1, $vd2, 'table4');
    $course_give = is_isset($dir_c, 'give');
    $course_get = is_isset($dir_c, 'get');

    $select = get_table5_current_select();;

    $reserve = is_out_sum(get_direction_reserve($vd1, $vd2, $direction_data), $vd2->currency_decimal, 'reserv');
    $rate = is_out_sum($course_give, $vd1->currency_decimal, 'course') . ' &rarr; ' . is_out_sum($course_get, $vd2->currency_decimal, 'course');

    if ('rate' == $select) {
        $val = $rate;
    } else {
        $val = $reserve;
    }

    $n_data = array();
    $n_data['reserve'] = '
	<div class="xtt_one_line_reserv_right">
		<div class="xtt_one_line_reserv">
			<span class="js_check_reserve" data-reserve="' . $reserve . '" data-rate="' . $rate . '">' . $val . '</span>
		</div>	
	</div>															
	';

    $data = pn_array_insert($data, 'title', $n_data);

    return $data;
}

add_action('premium_siteaction_table1_change', 'def_premium_siteaction_table1_change');
function def_premium_siteaction_table1_change() {
    global $wpdb, $premiumbox;

    _json_head();

    $log = array();
    $log['status'] = '';
    $log['status_code'] = '0';
    $log['status_text'] = '';

    $premiumbox->up_mode('post');

    $type_table = get_type_table();
    if (in_array($type_table, array('1', '4', '5'))) {
        $id = intval(is_param_post('id'));
        if ($id > 0) {

            $v = get_currency_data();

            $tableicon = get_icon_for_table();
            $where = get_directions_where('home');
            $html = '';

            $directions = apply_filters('get_directions_table1', array(), 'home', $where, $v, $id);

            $html .= get_table1_rightcol($directions, $id, $v, 'table' . $type_table, $tableicon, 'tbl' . $type_table . '_rightcol_data', '');

            $log['status'] = 'success';
            $log['html'] = $html;

        }
    }

    echo pn_json_encode($log);
    exit;
}