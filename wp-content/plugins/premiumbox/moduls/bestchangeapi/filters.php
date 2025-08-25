<?php
if (!defined('ABSPATH')) { exit(); }

add_action('item_direction_copy', 'item_direction_copy_bestchangeapi', 1, 2); 
function item_direction_copy_bestchangeapi($last_id, $new_id) {
	global $wpdb;
	
	$broker = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchangeapi_directions WHERE direction_id = '$last_id'"); 
	if (isset($broker->id)) {
		$arr = array();
		foreach ($broker as $k => $v) {
			if ('id' != $k) {
				$arr[$k] = $v;
			} 
		}
		$arr['direction_id'] = $new_id;		
		$wpdb->insert($wpdb->prefix . 'bestchangeapi_directions', $arr);
	}
}

add_filter('standart_course_direction', 'bestchangeapi_standart_course_direction', 10, 2);
function bestchangeapi_standart_course_direction($ind, $item) {
	
	if ($item->bestchangeapi_id > 0) {
		$ind = 1;
	}
	
	return $ind;
}

add_filter('list_tabs_direction', 'bestchangeapi_list_tabs_direction', 11, 2); 
function bestchangeapi_list_tabs_direction($list_tabs, $item) {
	
	if (current_user_can('administrator') or current_user_can('pn_bestchangeapi')) {
		$tab_title = '';
		$bestchange_id = intval(is_isset($item, 'bestchangeapi_id'));
		if ($bestchange_id > 0) {
			$tab_title = ' <span class="bgreen">*</span>';
		}	
		$new_list_tabs = array();
		$new_list_tabs['bestchangeapi'] = __('BestChange API parser', 'pn') . $tab_title;
		$list_tabs = pn_array_insert($list_tabs, 'tab2', $new_list_tabs);
	}	
	
	return $list_tabs;
}

add_action('tab_direction_bestchangeapi', 'def_tab_direction_bestchangeapi');
function def_tab_direction_bestchangeapi($data) {	
	global $wpdb;
 
	$data_id = is_isset($data, 'id');
		
	$broker = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchangeapi_directions WHERE direction_id = '$data_id'"); 
		
	$v1 = intval(is_isset($broker, 'v1'));
	$v2 = intval(is_isset($broker, 'v2'));
	$city_id = intval(is_isset($broker, 'city_id'));
	$reset_course = intval(is_isset($broker, 'reset_course'));
	$convert_course = intval(is_isset($broker, 'convert_course'));
	$float_course = intval(is_isset($broker, 'float_course'));
	$status = intval(is_isset($broker, 'status'));
		
	$alls = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bestchangeapi_currency_codes ORDER BY currency_code_title ASC");
	$cities = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bestchangeapi_cities ORDER BY city_title ASC");
	
	$form = new PremiumForm();
 	?>	
		<div class="add_tabs_line">
			<div class="add_tabs_title"><?php _e('BestChange parser', 'pn'); ?></div>
			<div class="add_tabs_submit">
				<input type="submit" name="" class="button" value="<?php _e('Save', 'pn'); ?>" />
			</div>
		</div>	
		
		<div class="add_tabs_line">
			<div class="add_tabs_single long">
				<div class="add_tabs_sublabel"><span><?php _e('Enable parser', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<select name="bestchangeapi_status" autocomplete="off">
						<option value="0" <?php selected($status, 0); ?>><?php _e('No', 'pn'); ?></option>
						<option value="1" <?php selected($status, 1); ?>><?php _e('Yes', 'pn'); ?></option>
					</select>
				</div>			
			</div>
		</div>

		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Black list of exchangers ID (separate coma)', 'pn') ?></span></div>
				<div class="premium_wrap_standart">
					<input type="text" name="bestchangeapi_black_ids" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($broker, 'black_ids')); ?>" />
				</div>
			</div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('White list of exchangers ID (separate coma)', 'pn'); ?></span></div>	
				<div class="premium_wrap_standart">
					<input type="text" name="bestchangeapi_white_ids" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($broker, 'white_ids')); ?>" />
				</div>
			</div>
		</div>		
	
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('City', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					
					<?php 
					$atts = array();
					$atts['id'] = 'bestchangeapi_city_id';
					$option_data = array();
					$opts = array('0' => '--' . __('No item', 'pn') . '--');
					foreach ($cities as $city) {
						$opts[$city->city_id] = pn_strip_input($city->city_title);
					}	
					$form->select_search('bestchangeapi_city_id', $opts, $city_id, $atts, $option_data); 
					?>					
					
				</div>
			</div>
		</div>	
	
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Currency', 'pn'); ?> (<?php _e('Send', 'pn'); ?>)</span></div>
				<div class="premium_wrap_standart">
				
					<?php 
					$atts = array();
					$atts['id'] = 'bestchangeapi_v1';
					$option_data = array();
					$opts = array('0' => '--' . __('No item', 'pn') . '--');
					foreach ($alls as $all) {
						$opts[$all->currency_code_id] = pn_strip_input($all->currency_code_title);
					}	
					$form->select_search('bestchangeapi_v1', $opts, $v1, $atts, $option_data); 
					?>				
					
				</div>
			</div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Currency', 'pn'); ?> (<?php _e('Receive', 'pn'); ?>)</span></div>	
				<div class="premium_wrap_standart">
				
					<?php 
					$atts = array();
					$atts['id'] = 'bestchangeapi_v2';
					$option_data = array();
					$opts = array('0' => '--' . __('No item', 'pn') . '--');
					foreach ($alls as $all) {
						$opts[$all->currency_code_id] = pn_strip_input($all->currency_code_title);
					}	
					$form->select_search('bestchangeapi_v2', $opts, $v2, $atts, $option_data); 
					?>				
					
				</div>
			</div>
		</div>
		
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Float course', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<select name="bestchangeapi_float_course" autocomplete="off">
						<option value="0" <?php selected($float_course, 0); ?>><?php _e('auto', 'pn'); ?></option>
						<option value="1" <?php selected($float_course, 1); ?>><?php _e('1 = XXX', 'pn'); ?></option>
						<option value="2" <?php selected($float_course, 2); ?>><?php _e('XXX = 1', 'pn'); ?></option>
					</select>
				</div>
			</div>
		</div>		
		
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Position', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<input type="text" name="bestchangeapi_pars_position" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($broker, 'pars_position')); ?>" />
				</div>
			</div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Step', 'pn'); ?></span></div>	
				<div class="premium_wrap_standart">
					<input type="text" name="bestchangeapi_step" style="width: 100%;" value="<?php echo pn_parser_num(is_isset($broker, 'step')); ?>" />
				</div>
			</div>
		</div>		
		
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Min reserve for position', 'pn'); ?></span></div>	
				<div class="premium_wrap_standart">
					<input type="text" name="bestchangeapi_min_res" id="bestchangeapi_min_res" style="width: 100%;" value="<?php echo is_sum(is_isset($broker, 'min_res')); ?>" />
				</div>
			</div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Show rating', 'pn'); ?></span></div>	
				<div class="premium_wrap_standart">
					<a href="#" class="button js_displayrating_bestchangeapi" target="_blank"><?php _e('Show rating', 'pn'); ?></a>
						<div class="clear"></div>
				</div>
			</div>			
		</div>		
	
		<div class="add_tabs_line">
			<div class="add_tabs_label"><span></span></div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Min rate', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<input type="text" name="bestchangeapi_min_sum" style="width: 100%;" value="<?php echo is_sum(is_isset($broker, 'min_sum')); ?>" />
				</div>
			</div>
		</div>		
		
		<?php do_action('tab_bestchangeapi_min_sum', $data, $broker); ?>
		
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Max rate', 'pn'); ?></span></div>	
				<div class="premium_wrap_standart">
					<input type="text" name="bestchangeapi_max_sum" style="width: 100%;" value="<?php echo is_sum(is_isset($broker, 'max_sum')); ?>" />
				</div>
			</div>
		</div>		
		
		<?php do_action('tab_bestchangeapi_max_sum', $data, $broker); ?>
	
		<div class="add_tabs_line">
			<div class="add_tabs_single long">
				<div class="add_tabs_sublabel"><span><?php _e('Reset to standard rate', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<select name="bestchangeapi_reset_course" autocomplete="off">
						<option value="0" <?php selected($reset_course, 0); ?>><?php _e('No', 'pn'); ?></option>
						<option value="1" <?php selected($reset_course, 1); ?>><?php _e('Yes', 'pn'); ?></option>
					</select>
				</div>
			</div>
		</div>	
	
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Standard rate', 'pn'); ?> (<?php _e('Send', 'pn'); ?>)</span></div>
				<div class="premium_wrap_standart">
					<input type="text" name="bestchangeapi_standart_course_give" style="width: 100%;" value="<?php echo is_sum(is_isset($broker, 'standart_course_give')); ?>" />
				</div>
			</div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Standard rate', 'pn'); ?> (<?php _e('Receive', 'pn'); ?>)</span></div>	
				<div class="premium_wrap_standart">
					<input type="text" name="bestchangeapi_standart_course_get" style="width: 100%;" value="<?php echo is_sum(is_isset($broker, 'standart_course_get')); ?>" />
				</div>
			</div>
		</div>
		
		<?php do_action('tab_bestchangeapi_standart_course', $data, $broker); ?>
		
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Convert course to', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<select name="bestchangeapi_convert_course" autocomplete="off">
						<option value="0" <?php selected($convert_course,0); ?>><?php _e('No', 'pn'); ?></option>
						<option value="1" <?php selected($convert_course,1); ?>><?php _e('1 = XXX', 'pn'); ?></option>
						<option value="2" <?php selected($convert_course,2); ?>><?php _e('XXX = 1', 'pn'); ?></option>
					</select>
				</div>
			</div>
		</div>
<script type="text/javascript">
jQuery(function($) {
	
	function set_show_link() {
		var url = '<?php echo get_request_link('displayrating_bestchangeapi', 'html'); ?>?v1=' + $('#bestchangeapi_v1').val() + '&v2=' + $('#bestchangeapi_v2').val() + '&city_id=' + $('#bestchangeapi_city_id').val() + '&minres=' + $('#bestchangeapi_min_res').val();
		$('.js_displayrating_bestchangeapi').attr('href', url);
	}
	$(document).on('change', '#bestchangeapi_city_id, #bestchangeapi_v1, #bestchangeapi_v2, #bestchangeapi_min_res', function() {
		set_show_link();
	});
	set_show_link();

});
</script>		
	<?php   
}  

add_filter('pn_direction_addform_post', 'bestchangeapi_pn_direction_addform_post');
function bestchangeapi_pn_direction_addform_post($array) {
	
	if (current_user_can('administrator') or current_user_can('pn_bestchangeapi')) {
		$array['bestchangeapi_id'] = intval(is_param_post('bestchangeapi_status'));
	}
	
	return $array;
}

add_action('item_direction_edit', 'item_direction_edit_bestchangeapi', 10, 2);
add_action('item_direction_add', 'item_direction_edit_bestchangeapi', 10, 2);
function item_direction_edit_bestchangeapi($direction_id, $direction_array) {
	global $wpdb, $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_bestchangeapi')) {
		if ($direction_id) {
			$up = 0;
			$vid1 = intval(is_param_post('bestchangeapi_v1'));
			$vid2 = intval(is_param_post('bestchangeapi_v2'));
			if ($vid1 > 0 and $vid2 > 0) {
				$v1 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchangeapi_currency_codes WHERE currency_code_id = '$vid1'");
				$v2 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchangeapi_currency_codes WHERE currency_code_id = '$vid2'");
				if (isset($v1->id) and isset($v2->id)) {
					
					$arr = array();
					$arr['direction_id'] = $direction_id;
					$arr['v1'] = intval($v1->currency_code_id);
					$arr['v2'] = intval($v2->currency_code_id);
					$arr['currency_id_give'] = $direction_array['currency_id_give'];
					$arr['currency_id_get'] = $direction_array['currency_id_get'];
					$arr['city_id'] = intval(is_param_post('bestchangeapi_city_id'));
					$arr['pars_position'] = pn_strip_input(is_param_post('bestchangeapi_pars_position'));
					$arr['step'] = pn_parser_num(is_param_post('bestchangeapi_step'));
					$arr['min_res'] = is_sum(is_param_post('bestchangeapi_min_res'));
					$arr['min_sum'] = is_sum(is_param_post('bestchangeapi_min_sum'));
					$arr['max_sum'] = is_sum(is_param_post('bestchangeapi_max_sum'));
					$arr['standart_course_give'] = is_sum(is_param_post('bestchangeapi_standart_course_give'));
					$arr['standart_course_get'] = is_sum(is_param_post('bestchangeapi_standart_course_get'));
					$arr['reset_course'] = intval(is_param_post('bestchangeapi_reset_course'));
					$arr['convert_course'] = intval(is_param_post('bestchangeapi_convert_course'));
					$arr['float_course'] = intval(is_param_post('bestchangeapi_float_course'));
					$arr['status'] = intval(is_param_post('bestchangeapi_status'));
					$arr['black_ids'] = pn_strip_input(is_param_post('bestchangeapi_black_ids'));
					$arr['white_ids'] = pn_strip_input(is_param_post('bestchangeapi_white_ids'));
					
					$broker = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchangeapi_directions WHERE direction_id = '$direction_id'"); 
					$arr = apply_filters('pn_bcorrs_tab_addform_post', $arr, $broker, $direction_id, $direction_array);
					
					if (isset($broker->id)) {
						$wpdb->update($wpdb->prefix . "bestchangeapi_directions", $arr, array('id' => $broker->id));
						do_action('item_bcorrs_tab_edit', $broker->id, $arr, $broker, $direction_id, $direction_array);
					} else {
						$wpdb->insert($wpdb->prefix . "bestchangeapi_directions", $arr);
						$broker_id = $wpdb->insert_id;	
						do_action('item_bcorrs_tab_add', $broker_id, $arr, $direction_id, $direction_array);
					}
					
				} else {
					$up = 1;			
				}
			} else {
				$up = 1;
			}
			if (1 == $up) {
				$wpdb->query("DELETE FROM " . $wpdb->prefix . "bestchangeapi_directions WHERE direction_id = '$direction_id'");
				unset_array_option($premiumbox, 'pn_bestchangeapi_courses', $direction_id);				
			}
		}
	}
	
}

add_action('item_direction_delete', 'item_direction_delete_bestchangeapi', 10, 2);
function item_direction_delete_bestchangeapi($item_id, $item) {
	global $wpdb, $premiumbox;	
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "bestchangeapi_directions WHERE direction_id = '$item_id'");
	unset_array_option($premiumbox, 'pn_bestchangeapi_courses', $item_id);
	
}

add_filter('get_calc_data', 'get_calc_data_bestchangeapi', 50, 2);
function get_calc_data_bestchangeapi($cdata, $calc_data) {
	global $bestchangeapi_courses, $premiumbox;
	
	if (!is_array($bestchangeapi_courses)) {
		$bestchangeapi_courses = get_array_option($premiumbox, 'pn_bestchangeapi_courses');
	}
	
	$direction = $calc_data['direction'];
	$vd1 = $calc_data['vd1'];
	$vd2 = $calc_data['vd2'];
	$set_course = intval(is_isset($calc_data,'set_course'));
	if (1 != $set_course) {
		if ($direction->bestchangeapi_id > 0) {
			if (isset($bestchangeapi_courses[$direction->id]) and isset($bestchangeapi_courses[$direction->id]['give'], $bestchangeapi_courses[$direction->id]['get'])) {
				$course_give = is_sum($bestchangeapi_courses[$direction->id]['give'], $vd1->currency_decimal);
				$course_get = is_sum($bestchangeapi_courses[$direction->id]['get'], $vd2->currency_decimal);
				$cdata['course_give'] = $course_give;
				$cdata['course_get'] = $course_get;
			} else {
				$cdata['course_give'] = 0;
				$cdata['course_get'] = 0;
			}
		}	
	}
	
	return $cdata;
}

add_filter('is_course_direction', 'bestchangeapi_is_course_direction', 50, 5); 
function bestchangeapi_is_course_direction($arr, $direction, $vd1, $vd2, $place) {
	global $bestchangeapi_courses, $premiumbox;	
	
	if (!is_array($bestchangeapi_courses)) {
		$bestchangeapi_courses = get_array_option($premiumbox, 'pn_bestchangeapi_courses');
	}
	
	if ($direction->bestchangeapi_id > 0) {
		if (isset($bestchangeapi_courses[$direction->id]) and isset($bestchangeapi_courses[$direction->id]['give'], $bestchangeapi_courses[$direction->id]['get'])) {
			
			if (isset($vd1->currency_decimal)) {
				$arr['give'] = is_sum($bestchangeapi_courses[$direction->id]['give'], $vd1->currency_decimal);
			} else {
				$arr['give'] = is_sum($bestchangeapi_courses[$direction->id]['give']);
			}
			
			if (isset($vd2->currency_decimal)) {
				$arr['get'] = is_sum($bestchangeapi_courses[$direction->id]['get'], $vd2->currency_decimal);
			} else {
				$arr['get'] = is_sum($bestchangeapi_courses[$direction->id]['get']);
			}
			
			return $arr;
		} else {
			$arr = array(
				'give' => 0,
				'get' => 0,
			);
		}
	}
	
	return $arr;
}