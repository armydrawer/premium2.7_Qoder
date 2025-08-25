<?php
if (!defined('ABSPATH')) { exit(); }

/* 
title: [en_US:]Exchange rate limits[:en_US][ru_RU:]Лимиты курсов направлений обмена[:ru_RU]
description: [en_US:]Exchange rate limits[:en_US][ru_RU:]Лимиты курсов направлений обмена[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path); 

add_action('pn_plugin_activate', 'bd_all_moduls_active_dirclimits');
add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_dirclimits');
function bd_all_moduls_active_dirclimits() { 
	global $wpdb;	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'c_min1'"); 
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `c_min1` varchar(150) NOT NULL default '0'");
	}	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'c_min2'"); 
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `c_min2` varchar(150) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'c_max1'"); 
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `c_max1` varchar(150) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'c_max2'"); 
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `c_max2` varchar(150) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'c_st1'"); 
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `c_st1` varchar(150) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'c_st2'"); 
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `c_st2` varchar(150) NOT NULL default '0'");
	}			
	
}

add_filter('list_tabs_direction', 'dirclimits_list_tabs_direction', 0, 2);
function dirclimits_list_tabs_direction($list_tabs, $data) {
	
	$tab_title = '';
	$db_names = array('c_min1', 'c_min2', 'c_max1', 'c_max2', 'c_st1', 'c_st2');
	foreach ($db_names as $db_name) {
		$val = is_sum(is_isset($data, $db_name));
		if ($val > 0) {
			$tab_title = ' <span class="bgreen">*</span>';
			break;
		}
	}
	$new_list_tabs = array();
	$new_list_tabs['dirclimits'] = __('Exchange rate limitations', 'pn') . $tab_title;
	$list_tabs = pn_array_insert($list_tabs, 'tab2', $new_list_tabs); 
	
	return $list_tabs;
}

add_action('tab_direction_dirclimits', 'def_tab_direction_dirclimits', 200, 2);
function def_tab_direction_dirclimits($data, $data_id) {	
	?>
		<div class="add_tabs_line">
			<div class="add_tabs_submit">
				<input type="submit" name="" class="button" value="<?php _e('Save', 'pn'); ?>" />
			</div>
		</div>		
		<div class="add_tabs_line">
			<div class="add_tabs_label"><span><?php _e('Minimum rate', 'pn'); ?></span></div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Send', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<input type="text" name="c_min1" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'c_min1')); ?>" />
				</div>				
			</div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Receive', 'pn'); ?></span></div>	
				<div class="premium_wrap_standart">
					<input type="text" name="c_min2" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'c_min2')); ?>" />
				</div>				
			</div>
		</div>
		<div class="add_tabs_line">
			<div class="add_tabs_label"><span><?php _e('Maximum rate', 'pn'); ?></span></div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Send', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<input type="text" name="c_max1" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'c_max1')); ?>" />
				</div>				
			</div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Receive', 'pn'); ?></span></div>	
				<div class="premium_wrap_standart">
					<input type="text" name="c_max2" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'c_max2')); ?>" />
				</div>				
			</div>
		</div>			
		<div class="add_tabs_line">
			<div class="add_tabs_label"><span><?php _e('Standard rate', 'pn'); ?></span></div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Send', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<input type="text" name="c_st1" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'c_st1')); ?>" />
				</div>				
			</div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Receive', 'pn'); ?></span></div>	
				<div class="premium_wrap_standart">
					<input type="text" name="c_st2" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'c_st2')); ?>" />
				</div>				
			</div>
		</div>
	<?php  
}

add_filter('pn_direction_addform_post', 'dirclimits_pn_direction_addform_post');
function dirclimits_pn_direction_addform_post($array) {
	
	$array['c_min1'] = is_sum(is_param_post('c_min1'));
	$array['c_min2'] = is_sum(is_param_post('c_min2'));
	$array['c_max1'] = is_sum(is_param_post('c_max1'));
	$array['c_max2'] = is_sum(is_param_post('c_max2'));
	$array['c_st1'] = is_sum(is_param_post('c_st1'));
	$array['c_st2'] = is_sum(is_param_post('c_st2'));
	
	return $array;
}

add_filter('get_calc_data', 'get_calc_data_dirclimits', 600, 2);
function get_calc_data_dirclimits($cdata, $calc_data) {
	
	$direction = $calc_data['direction'];
	$vd1 = $calc_data['vd1'];
	$vd2 = $calc_data['vd2'];
	
	$min_sum = is_sum($direction->c_min1);
	$max_sum = is_sum($direction->c_max1);
	$min_sum2 = is_sum($direction->c_min2);
	$max_sum2 = is_sum($direction->c_max2);
	$def_course1 = is_sum($direction->c_st1);
	$def_course2 = is_sum($direction->c_st2);			
			
	$ncurs1 = $cdata['course_give'];
	if ($ncurs1 > $max_sum and $max_sum > 0 or $ncurs1 < $min_sum) {
		$ncurs1 = $def_course1;
	}	
	
	$ncurs2 = $cdata['course_get'];
	if ($ncurs2 > $max_sum2 and $max_sum2 > 0 or $ncurs2 < $min_sum2) {
		$ncurs2 = $def_course2;
	}	
			
	$cdata['course_give'] = $ncurs1;
	$cdata['course_get'] = $ncurs2;
	
	return $cdata;
}

add_filter('is_course_direction', 'dirclimits_is_course_direction', 100, 5); 
function dirclimits_is_course_direction($arr, $direction, $vd1, $vd2, $place) {
	
	$min_sum = is_sum($direction->c_min1);
	$max_sum = is_sum($direction->c_max1);
	$def_course1 = is_sum($direction->c_st1);
	$ncurs1 = $arr['give'];
	if ($ncurs1 > $max_sum and $max_sum > 0 or $ncurs1 < $min_sum) {
		$arr['give'] = $def_course1;
	}	
	$min_sum2 = is_sum($direction->c_min2);
	$max_sum2 = is_sum($direction->c_max2);
	$def_course2 = is_sum($direction->c_st2);
	$ncurs2 = $arr['get'];
	if ($ncurs2 > $max_sum2 and $max_sum2 > 0 or $ncurs2 < $min_sum2) {
		$arr['get'] = $def_course2;
	}		
	
	return $arr;
}