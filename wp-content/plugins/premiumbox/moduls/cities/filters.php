<?php
if (!defined('ABSPATH')) { exit(); }

add_action('init', 'remove_xmlcity_direction', 0);
function remove_xmlcity_direction() {
	
	remove_action('tab_direction_tab12', 'txtxmlcity_tab_direction_tab12', 15, 2);
	
}

add_action('list_tabs_direction', 'cities_list_tabs_direction', 500); 
function cities_list_tabs_direction($list_tabs) {
	
	$n_list_tabs = array();
	$n_list_tabs['cities'] = __('Cities', 'pn');
	
	return pn_array_insert($list_tabs, 'tab2', $n_list_tabs);
}

function get_cities_html($cities) {
	
	$temp = '';
	$temp .= get_cities_line('', '');
	foreach ($cities as $c_key => $c_val) {
		$temp .= get_cities_line($c_key, $c_val);
	}
	
	return $temp;	
}
  
function get_cities_line($c_key, $c_val) {
	global $pn_admin_cities, $wpdb;

	if (!is_array($pn_admin_cities)) {
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "cities WHERE auto_status = '1'");
		$cities = array();
		foreach ($items as $item) {
			$cities[] = array(
				'title' => pn_strip_input(ctv_ml($item->title)) . pn_item_status($item),
				'xml' => is_xml_value($item->xml_value),
			);
		}
		$pn_admin_cities = pn_array_sort($cities, 'title', 'asc');
	} 
	
	$temp = '
	<div class="construct_line js_cities_line">
		<div class="construct_item_line">
			<div class="construct_item">	
				<div class="construct_title">
					'. __('City', 'pn') .'
				</div>
				<div class="construct_input">
					<select name="" autocomplete="off" data-name="xml" class="js_cities_val">
						<option value="0">-- ' . __('No item', 'pn') . ' --</option>';
						
						foreach ($pn_admin_cities as $ac) {
							$temp .= '<option value="' . $ac['xml'] . '" ' . selected($ac['xml'], $c_key, false) . '>' . $ac['title'] . ' (' . $ac['xml'] . ')</option>';
						}
						
					$temp .= '	
					</select>
				</div>
			</div>';
			
			if ($c_key) {
				$temp .= '
				<div class="construct_add js_cities_add">' . __('Save', 'pn') . '</div>
				<div class="construct_del js_cities_del">' . __('Delete', 'pn') . '</div>
				';
			} else {
				$temp .= '
				<div class="construct_add js_cities_add">' . __('Add new', 'pn') . '</div>
				';
			}
			
			$temp .= '
			<div class="premium_clear"></div>
		</div>
		<div class="construct_item_line">
			<div class="construct_item">	
				<div class="construct_title">
					' . __('Add to rate', 'pn') . ' (' . __('Send', 'pn') . ')
				</div>
				<div class="construct_input">
					<input type="text" name="" style="width: 200px;" data-name="r1" class="js_cities_elem" value="' . pn_strip_input(is_isset($c_val, '1')) . '" />
				</div>
			</div>
			<div class="construct_item">	
				<div class="construct_title">
					' . __('Add to rate', 'pn') . ' (' . __('Receive', 'pn') . ')
				</div>
				<div class="construct_input">
					<input type="text" name="" style="width: 200px;" data-name="r2" class="js_cities_elem" value="' . pn_strip_input(is_isset($c_val, '2')) . '" />
				</div>
			</div>		
			
			<div class="construct_item">	
				<div class="construct_title">
					'. __('Minimum amount', 'pn') .'
				</div>
				<div class="construct_input">
					<input type="text" name="" style="width: 200px;" data-name="min" class="js_cities_elem" value="' . is_sum(is_isset($c_val, 'min')) . '" />
				</div>
			</div>
			<div class="construct_item">	
				<div class="construct_title">
					'. __('Maximum amount', 'pn') .'
				</div>
				<div class="construct_input">
					<input type="text" name="" style="width: 200px;" data-name="max" class="js_cities_elem" value="' . is_sum(is_isset($c_val, 'max')) . '" />
				</div>
			</div>		
		
			<div class="premium_clear"></div>	
		</div>
		<div class="construct_item_line">
			<div class="construct_item">	
				<div class="construct_title">
					' . __('Profit', 'pn') . ' (' . __('With give amount', 'pn') . ')
				</div>
				<div class="construct_input">
					<input type="text" name="" style="width: 200px;" data-name="pr1" class="js_cities_elem" value="' . is_sum(is_isset($c_val, 'pr1')) . '" />%
				</div>
			</div>
			<div class="construct_item">	
				<div class="construct_title">
					' . __('Profit', 'pn') . ' (' . __('With receive amount', 'pn') . ')
				</div>
				<div class="construct_input">
					<input type="text" name="" style="width: 200px;" data-name="pr2" class="js_cities_elem" value="' . is_sum(is_isset($c_val, 'pr2')) . '" />%
				</div>
			</div>				
		
			<div class="premium_clear"></div>	
		</div>		
		<div class="construct_item_line">
			<div class="construct_item">	
				<div class="construct_title">
					'. __('Tags for parameter param', 'pn') .'
				</div>
				<div class="construct_input">
					<input type="text" name="" style="width: 400px; max-width: 100%;" data-name="p" class="js_cities_elem" value="' . pn_strip_input(is_isset($c_val, 'p')) . '" />
				</div>
			</div>		
			
			<div class="premium_clear"></div>	
		</div>
	</div>
	';
	
	return $temp;
}

add_action('tab_direction_cities', 'cities_tab_direction_cities', 10, 2);
function cities_tab_direction_cities($data, $data_id) {
	
	if ($data_id) {
		$cities = pn_json_decode(is_isset($data, 'cities'));
		if (!is_array($cities)) { $cities = array(); }
		?>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">	
			<div class="cities_html" data-id="<?php echo $data_id; ?>">
				<?php 
				if (function_exists('get_cities_html')) {
					echo get_cities_html($cities); 
				}
				?>
			</div>
		</div>
	</div>
<script type="text/javascript"> 
jQuery(function($) {
	
	$(document).on('click', '.js_cities_add', function() { 
	
		var par_div = $(this).parents('.cities_html');
		var data_id = par_div.attr('data-id');
		var par = $(this).parents('.js_cities_line');
		var item_id = par.find('.js_cities_val').val();
		var param_other = '';
		
		par_div.find('input, select').attr('disabled', true);
		par_div.find('.js_cities_add, .js_cities_del').addClass('active');
		
		par.find('.js_cities_elem').each(function() {
			param_other += '&'+$(this).attr('data-name') + '=' + $(this).val();
		});
		
		var param = 'data_id=' + data_id + '&item_id=' + item_id + param_other;

		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('cities_ajax_add', 'post'); ?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{		
				if (res['html']) {
					par_div.html(res['html']);
				} 
			}
		});		

		return false;
	});	
	
	$(document).on('click', '.js_cities_del', function() { 
	
		var par_div = $(this).parents('.cities_html');
		var data_id = par_div.attr('data-id');
		var par = $(this).parents('.js_cities_line');
		var item_id = par.find('.js_cities_val').val();
		
		par_div.find('input, select').attr('disabled', true);
		par_div.find('.js_cities_add, .js_cities_del').addClass('active');
		
		var param = 'data_id=' + data_id + '&item_id=' + item_id;

		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('cities_ajax_del', 'post'); ?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{		
				if (res['html']) {
					par_div.html(res['html']);
				} 
			}
		});		

		return false;
	});	
	
});
</script>	
		<?php
	} else {
		_e('You can set cities only for the created exchange direction', 'pn');
	}	
}

add_action('premium_action_cities_ajax_add', 'def_premium_action_cities_ajax_add');
function def_premium_action_cities_ajax_add() {
	global $wpdb;

	_method('post');
	_json_head();
		
	$log = array();
	$log['status'] = 'success';	
		
	$data_id = intval(is_param_post('data_id'));
	if (current_user_cans('administrator, pn_directions') and $data_id > 0) {
		$item_id = is_xml_value(is_param_post('item_id'));
		$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$data_id'");
		if (isset($data->id)) {
			$cities = pn_json_decode(is_isset($data, 'cities'));
			if (!is_array($cities)) { $cities = array(); }
			$in_db = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "cities WHERE auto_status = '1'");
			$in_dbs = array();
			foreach ($in_db as $in) {
				$in_dbs[is_xml_value($in->xml_value)] = 1;
			}	
			
			$cities[$item_id] = array(
				'1' => pn_strip_input(is_param_post('r1')),
				'2' => pn_strip_input(is_param_post('r2')),
				'min' => is_sum(is_param_post('min')),
				'max' => is_sum(is_param_post('max')),
				'pr1' => is_sum(is_param_post('pr1')),
				'pr2' => is_sum(is_param_post('pr2')),
				'p' => pn_strip_input(is_param_post('p')),
			);
			
			foreach ($cities as $city_xml => $cities_data) {
				if (!isset($in_dbs[$city_xml])) {
					unset($cities[$city_xml]);
				}
			}
			
			$array = array();
			$array['cities'] = pn_json_encode($cities);
			$wpdb->update($wpdb->prefix . 'directions', $array, array('id' => $data->id));
			
			$log['html'] = get_cities_html($cities);
		}
	} 
		
	echo pn_json_encode($log);
	exit;
}

add_action('premium_action_cities_ajax_del', 'def_premium_action_cities_ajax_del');
function def_premium_action_cities_ajax_del() {
	global $wpdb;

	_method('post');
	_json_head();
		
	$log = array();
	$log['status'] = 'success';	
		
	$data_id = intval(is_param_post('data_id'));
	if (current_user_cans('administrator, pn_directions')) {
		$item_id = is_xml_value(is_param_post('item_id'));	
		$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$data_id'");
		if (isset($data->id)) {	
			$cities = pn_json_decode(is_isset($data, 'cities'));
			if (!is_array($cities)) { $cities = array(); }
			$in_db = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "cities WHERE auto_status = '1'");
			$in_dbs = array();
			foreach ($in_db as $in) {
				$in_dbs[is_xml_value($in->xml_value)] = 1;
			}	
			
			if ($item_id and isset($cities[$item_id])) {
				unset($cities[$item_id]);
			}
			
			foreach ($cities as $city_xml => $cities_data) {
				if (!isset($in_dbs[$city_xml])) {
					unset($cities[$city_xml]);
				}
			}
			$array = array();
			$array['cities'] = pn_json_encode($cities);
			$wpdb->update($wpdb->prefix . 'directions', $array, array('id' => $data->id));
			
			$log['html'] = get_cities_html($cities);	
		}
	}

	echo pn_json_encode($log);	
	exit;
}

function get_city_title($city_key) {
	global $wpdb, $pn_list_city_title;	

	if (!is_array($pn_list_city_title)) {
		$cities = array();
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "cities WHERE auto_status = '1'");
		foreach ($items as $item) {
			$cities[is_xml_value($item->xml_value)] = pn_strip_input($item->title);
		}	
		$pn_list_city_title = $cities;
	}
	if (isset($pn_list_city_title[$city_key])) {
		return ctv_ml($pn_list_city_title[$city_key]);
	}
	
	return $city_key;
} 
	
add_filter('change_bids_filter_list', 'city_change_bids_filter_list'); 
function city_change_bids_filter_list($lists) {
	
	$lists['other']['city'] = array(
		'title' => __('City', 'pn'),
		'name' => 'city',
		'value' => is_xml_value(is_param_get('city')),
		'view' => 'input',
		'work' => 'input',
	);	
	
	return $lists;
}

add_filter('where_request_sql_bids', 'city_where_request_sql_bids', 10, 2);
function city_where_request_sql_bids($where, $pars_data) {
	global $wpdb;

	$pr = $wpdb->prefix;
	$sql_operator = is_sql_operator($pars_data);
	$city = is_xml_value(is_isset($pars_data, 'city'));
	if ($city) { 
		$where .= " {$sql_operator} {$pr}exchange_bids.city = '$city'";
	}	
	
	return $where;
}

function get_cities() {
	global $wpdb, $pn_list_city;	

	if (!is_array($pn_list_city)) {
		$cities = array();
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "cities WHERE auto_status = '1' AND status = '1'");
		foreach ($items as $item) {
			$cities[is_xml_value($item->xml_value)] = $item;
		}	
		$pn_list_city = $cities;
	}
	
	return $pn_list_city;
}

function get_array_cities($json_encode) {
	
	$array = array();
	
	$lists = pn_json_decode($json_encode);
	if (!is_array($lists)) { $lists = array(); }

	$cities = get_cities();

	$num = array('0','1','2','3','4','5','6','7','8','9');

	foreach ($lists as $xml_value => $data) {
		if (isset($cities[$xml_value])) {
			$k1 = pn_strip_input(is_isset($data, 1));
			$fs = mb_substr($k1, 0, 1);
			if (in_array($fs, $num)) { $k1 = '+' . $k1; }

			$k2 = pn_strip_input(is_isset($data, 2));
			$fs = mb_substr($k2, 0, 1);
			if (in_array($fs, $num)) { $k2 = '+' . $k2; }

			$array[$xml_value] = array(
				'1' => $k1,
				'2' => $k2,
				'min' => is_sum(is_isset($data, 'min')),
				'max' => is_sum(is_isset($data, 'max')),
				'pr1' => is_sum(is_isset($data, 'pr1')),
				'pr2' => is_sum(is_isset($data, 'pr2')),
				'p' => pn_strip_input(is_isset($data, 'p')),
				'title' => pn_strip_input(ctv_ml($cities[$xml_value]->title)),
			);
		}
	}	
	
	return $array;
}

add_filter('list_direction_fields', 'city_list_direction_fields', 10, 2);
function city_list_direction_fields($fields, $direction) {
	
	$options = get_array_cities(is_isset($direction, 'cities'));	
	if (count($options) > 0) {
		
		$id = 'city';
		$value = is_xml_value(is_param_get('city'));
		if (!$value) {
			$value = is_xml_value(get_pn_cookie('cache_' . $id));
		}
		$value = mb_strtoupper($value);
		
		$options = pn_array_sort($options, 'title', 'asc');
		$noptions = array();
		$noptions['0'] = __('No selected', 'pn');
		foreach ($options as $options_k => $option_d) {
			$noptions[$options_k] = is_isset($option_d, 'title');
		}		
		
		$field['city'] = array(
			'type' => 'select',
			'name' => 'city',
			'id' => 'city',
			'cash-id' => 'city',
			'autocomplete' => 'off',
			'value' => $value,
			'options' => $noptions,
			'label' => __('Your city', 'pn'),
			'req' => 1,
			'class' => 'cache_data check_cache js_my_sel js_changecalc js_' . $id . '',
			'cd' => '1',
		);	

		$fields = array_merge($field, $fields);		
		
	}	
	
	return $fields;
}

add_filter('get_calc_data_params', 'city_get_calc_data_params', 100, 3);
function city_get_calc_data_params($calc_data, $place = '', $bid = '') {

	$place = trim($place);
	if ('calculator' == $place) {
		if (isset($calc_data['cd'])) {
			$calc_data['city'] = is_xml_value(is_isset($calc_data['cd'], 'city'));		
		}		
	} elseif ('recalc' == $place) {
		$calc_data['city'] = is_xml_value(is_isset($bid, 'city'));
	} elseif ('action' == $place) {
		$calc_data['city'] = is_xml_value(is_param_post('city'));
	} else {
		$value = is_xml_value(is_param_get('city'));
		if (!$value) {
			$value = is_xml_value(get_pn_cookie('cache_city'));
		}
		$calc_data['city'] = $value;
	}
	
	return $calc_data;
}

add_filter('get_calc_data', 'city_get_calc_data', 100, 2); 
function city_get_calc_data($cdata, $calc_data) {
	
	$direction = $calc_data['direction'];
	$set_course = intval(is_isset($calc_data, 'set_course'));
	$city = mb_strtoupper(is_xml_value(is_isset($calc_data, 'city')));
	$vd1 = $calc_data['vd1'];
	$vd2 = $calc_data['vd2'];
	
	$cities = get_array_cities(is_isset($direction, 'cities'));
	if (count($cities) > 0) {
		if (isset($cities[$city])) {
				
			$d1 = $vd1->currency_decimal;
			$d2 = $vd2->currency_decimal;
				
			$c1 = $cdata['course_give'];
			$c2 = $cdata['course_get'];
				
			$t_data = $cities[$city];

			$s1 = mcalc_calc($c1 . $t_data[1]);
			$cdata['course_give'] = is_sum($s1, $d1); 

			$s2 = mcalc_calc($c2 . $t_data[2]);
			$cdata['course_get'] = is_sum($s2, $d2);

			$min = is_sum(is_isset($t_data, 'min'));
			if ($min > 0) {
				$cdata['min_give'] = $min;
			}
			
			$max = is_sum(is_isset($t_data, 'max'));
			if ($max > 0) {
				$cdata['max_give'] = $max;
			}
			
			$pr1 = is_sum(is_isset($t_data, 'pr1'));
			$pr2 = is_sum(is_isset($t_data, 'pr2'));
			if ($pr1 > 0 or $pr2 > 0) {
				$cdata['profit_sum1'] = '0';
				$cdata['profit_pers1'] = $pr1;
				$cdata['profit_sum2'] = '0';
				$cdata['profit_pers2'] = $pr2;
			}
			
		}
	}
	
	return $cdata;
}

add_filter('is_course_direction', 'city_is_course_direction', 90, 5); 
function city_is_course_direction($arr, $direction, $vd1, $vd2, $place) {
	
	if ('coursewindow' == $place) {
		$c1 = $arr['give'];
		$c2 = $arr['get'];
		$city = is_xml_value(get_pn_cookie('cache_city'));
		$cities = get_array_cities(is_isset($direction,'cities'));
		if (count($cities) > 0 and $c1 > 0 and $c2 > 0) {
			if (isset($cities[$city])) {
				$d1 = $vd1->currency_decimal;
				$d2 = $vd2->currency_decimal;

				$t_data = $cities[$city];

				$s1 = mcalc_calc($c1 . $t_data[1]);
				$arr['give'] = is_sum($s1, $d1); 

				$s2 = mcalc_calc($c2 . $t_data[2]);
				$arr['get'] = is_sum($s2, $d2);
			}
		}
	}	
	
	return $arr;
}

add_filter('error_bids','city_error_bids',10, 2);  
function city_error_bids($error_bids, $direction) {
	
	$cities = get_array_cities(is_isset($direction, 'cities'));
	if (count($cities) > 0) {
		$city = mb_strtoupper(is_xml_value(is_param_post('city')));
		$error_bids['bid']['city'] = $city;
		if (strlen($city) < 2 or !isset($cities[$city])) {
			$error_bids['error_text'][] = __('Need choise city', 'pn');	
			$error_bids['error_fields']['city'] = __('Need choise city', 'pn');
		}	
	}
	
	return $error_bids;
}

add_filter('file_xml_lines', 'city_file_xml_lines', 10, 2);
function city_file_xml_lines($lines, $item) {
	
	$xml_city = '';
	$cities = get_array_cities(is_isset($item, 'cities'));
	if (count($cities) > 0) {
		$nc = array();
		foreach ($cities as $xml => $d) {
			$nc[] = $xml;
		}
		$xml_city = implode(',', $nc);
	} 
	$lines['cities'] = $xml_city;
	
	return $lines;
}

add_filter('file_xml_directions', 'file_xml_directions_cities', 20);
function file_xml_directions_cities($directions) {
	
	foreach ($directions as $key => $line) {
		if (isset($line['dir'])) {
			
			$dir = $line['dir'];
			$d1 = $line['d1'];
			$d2 = $line['d2'];
			$currency_code_give = $line['c1'];
			$currency_code_get = $line['c2'];
			
			$c1 = $line['in'];
			$c2 = $line['out'];
			
			$city = trim(is_isset($line,'city'));
			if ($city) {
				$cities = get_array_cities(is_isset($dir, 'cities'));
				if (isset($cities[$city])) {
					
					$t_data = $cities[$city];

					$s1 = mcalc_calc($c1 . $t_data[1]);
					$directions[$key]['in'] = is_sum($s1, $d1); 

					$s2 = mcalc_calc($c2 . $t_data[2]);
					$directions[$key]['out'] = is_sum($s2, $d2);
					
					$param = trim(is_isset($t_data, 'p'));
					if ($param) {
						$directions[$key]['param'] = $param;
					}
					
					$min = is_sum(is_isset($t_data, 'min'));
					if ($min > 0) {
						$directions[$key]['minamount'] = $min . ' ' . $currency_code_give;
					}
					
					$max = is_sum(is_isset($t_data, 'max'));
					if ($max > 0) {
						$directions[$key]['maxamount'] = $max . ' ' . $currency_code_give;
					}					

				}
			}
		}		
	}
	
	return $directions;
}

add_filter('onebid_col4', 'onebid_col4_cities', 10, 3);
function onebid_col4_cities($actions, $item, $v) {
	
	$city = is_xml_value($item->city);
	if ($city) {
		$n_actions = array();
		$n_actions['city'] = array(
			'type' => 'text',
			'title' => __('City', 'pn'),
			'label' => '<span class="onebid_item">' . $city . ' (' . get_city_title($city) . ')</span>',
		);
		$actions = array_merge($n_actions, $actions);
	}
	
	return $actions;
}

add_filter('shortcode_notify_tags_bids', 'shortcode_notify_tags_bids_cities');
function shortcode_notify_tags_bids_cities($tags) {
	
	$tags['city'] = array(
		'title' => __('City', 'pn'),
		'start' => '[city]',
	);	
	
	return $tags;
}

add_filter('direction_instruction_tags', 'cities_directions_tags', 10, 2); 
function cities_directions_tags($tags, $key) {
	
	$in_page = array('description_txt', 'timeline_txt', 'window_txt', 'frozen_txt');
	if (!in_array($key, $in_page)) {	
		$tags['city'] = array(
			'title' => __('City', 'pn'),
			'start' => '[city]',
		);		
	} 
	
	return $tags;
}  

add_filter('notify_tags_bids', 'cities_notify_tags_bids', 10000, 3);
function cities_notify_tags_bids($notify_tags, $item, $direction = '') {
	
	$notify_tags['[city]'] = get_city_title(is_xml_value(is_isset($item, 'city')));
	
	return $notify_tags;
}

add_filter('direction_instruction','cities_direction_instruction', 10, 6);
function cities_direction_instruction($instruction, $txt_name, $direction, $vd1, $vd2, $bids_data = '') {
	
	if (isset($bids_data->id)) {
		$instruction = str_replace('[city]', get_city_title(is_xml_value(is_isset($bids_data, 'city'))), $instruction);
	}	
	
	return $instruction;
}

add_filter('list_export_bids', 'cities_list_export_bids');
function cities_list_export_bids($array) {
	
	$array['city'] = __('City', 'pn');
	
	return $array;
}