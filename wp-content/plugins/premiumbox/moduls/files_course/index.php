<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Exchange rate from file[:en_US][ru_RU:]Парсер курса обмена из файла[:ru_RU]
description: [en_US:]Exchange rate from file[:en_US][ru_RU:]Парсер курса обмена из файла[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_filecourse');
add_action('pn_plugin_activate', 'bd_all_moduls_active_filecourse');
function bd_all_moduls_active_filecourse() {
	global $wpdb;
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'filecourse'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `filecourse` varchar(250) NOT NULL default '0'");
    }	
}

add_filter('standart_course_direction', 'filecourse_standart_course_direction', 10, 2);
function filecourse_standart_course_direction($ind, $item) {
	
	if ($item->filecourse > 0) {
		$ind = 1;
	}
	
	return $ind;
}

add_action('tab_direction_tab2', 'tab_direction_tab_filecourse',11,2);
function tab_direction_tab_filecourse($data, $data_id) {
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange rate from file (line)', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="filecourse" autocomplete="off" value="<?php echo intval(is_isset($data, 'filecourse')); ?>" />
			</div>
		</div>
	</div>	
<?php 
}

add_filter('pn_direction_addform_post', 'filecourse_pn_direction_addform_post');
function filecourse_pn_direction_addform_post($array) {
	
	$array['filecourse'] = intval(is_param_post('filecourse'));
	
	return $array;
}
 
add_action('item_direction_edit', 'filecourse_item_direction_edit', 1, 2);
add_action('item_direction_add', 'filecourse_item_direction_edit', 1, 2);
function filecourse_item_direction_edit($data_id, $array) {
	
	if ($data_id) {
		$filecourse = intval(is_param_post('filecourse'));
		if ($filecourse > 0 and function_exists('fcourse_request_cron')) {
			fcourse_request_cron();
		}
	}	
	
}

add_action('item_direction_delete', 'item_direction_delete_filecourse');
function item_direction_delete_filecourse($item_id) {
	global $premiumbox;
	
	unset_array_option($premiumbox, 'pn_fcourse_courses', $item_id);
	
}

function get_filecourse() {
	global $premiumbox;	
	
	$arr = array();
	$url = trim($premiumbox->get_option('fcourse', 'url'));
	if ($url) {
		$curl = get_curl_parser($url, '', 'moduls', 'fcourse');
		$string = $curl['output'];
		if (!$curl['err']) {
			$lines = explode("\n",$string);
			$r = 0;
			foreach ($lines as $line) { $r++;
				$pars_line = explode(':', $line);
				if (isset($pars_line[1])) {
					$course = trim($pars_line[1]);
					$course_arr = explode('=', $course);
					$arr[$r] = array(
						'line' => $r,
						'title' => pn_strip_input($pars_line[0]),
						'give' => is_sum(is_isset($course_arr, 0)),
						'get' => is_sum(is_isset($course_arr, 1)),
					);
				}					
			}
		}
	}	
	
	return $arr;
}

function fcourse_request_cron() {
	global $wpdb, $premiumbox;
	
	if (!$premiumbox->is_up_mode()) {
		$in_file = get_filecourse();
		$now_date = current_time('mysql');
		
		$courses = get_array_option($premiumbox, 'pn_fcourse_courses');
		if (!is_array($courses)) { $courses = array(); }
		
		$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' AND filecourse > 0");
		foreach ($directions as $direction) {
			$key = $direction->filecourse;
			$direction_id = $direction->id;
			if (isset($in_file[$key])) {
				$ncurs1 = $in_file[$key]['give'];
				$ncurs2 = $in_file[$key]['get'];
				if ($ncurs1 > 0 and $ncurs2 > 0) {
					$courses[$direction_id]['give'] = $ncurs1;
					$courses[$direction_id]['get'] = $ncurs2;
				}				
			}							
		}	
		
		update_array_option($premiumbox, 'pn_fcourse_courses', $courses);
		
		do_action('request_fcourse');
	}	
	
}

add_filter('list_cron_func', 'fcourse_list_cron_func');
function fcourse_list_cron_func($filters) {
	
	$filters['fcourse_request_cron'] = array(
		'title' => __('Parsing exchange rates from file', 'pn'),
		'file' => '10min',
		'site' => 'none', 
	);
	
	return $filters;
}

add_filter('get_calc_data', 'get_calc_data_fcourse', 50, 2);
function get_calc_data_fcourse($cdata, $calc_data) {
	global $fcourse_courses, $premiumbox;

	if (!is_array($fcourse_courses)) {
		$fcourse_courses = get_array_option($premiumbox, 'pn_fcourse_courses');
	}
	
	$direction = $calc_data['direction'];
	$vd1 = $calc_data['vd1'];
	$vd2 = $calc_data['vd2'];
	$set_course = intval(is_isset($calc_data, 'set_course'));
	
	if ($direction->filecourse > 0 and 1 != $set_course) {
		if (isset($fcourse_courses[$direction->id]) and isset($fcourse_courses[$direction->id]['give'], $fcourse_courses[$direction->id]['get'])) {
			$course_give = is_sum($fcourse_courses[$direction->id]['give'], $vd1->currency_decimal);
			$course_get = is_sum($fcourse_courses[$direction->id]['get'], $vd2->currency_decimal);
			if ($course_give > 0) {
				$cdata['course_give'] = $course_give;
			}
			if ($course_get > 0) {
				$cdata['course_get'] = $course_get;
			}
		} else {
			$cdata['course_give'] = 0;
			$cdata['course_get'] = 0;
		}
	}

	return $cdata;
}

add_filter('is_course_direction', 'fcourse_is_course_direction', 50, 5); 
function fcourse_is_course_direction($arr, $direction, $vd1, $vd2, $place) {
	global $fcourse_courses, $premiumbox;	

	if (!is_array($fcourse_courses)) {
		$fcourse_courses = get_array_option($premiumbox, 'pn_fcourse_courses');
	}
	if ($direction->filecourse > 0) {
		if (isset($fcourse_courses[$direction->id]) and isset($fcourse_courses[$direction->id]['give'], $fcourse_courses[$direction->id]['get'])) {
			
			if (isset($vd1->currency_decimal)) {
				$arr['give'] = is_sum($fcourse_courses[$direction->id]['give'], $vd1->currency_decimal);
			} else {
				$arr['give'] = is_sum($fcourse_courses[$direction->id]['give']);
			}
			
			if (isset($vd2->currency_decimal)) {
				$arr['get'] = is_sum($fcourse_courses[$direction->id]['get'], $vd2->currency_decimal);
			} else {
				$arr['get'] = is_sum($fcourse_courses[$direction->id]['get']);
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

add_action('admin_menu', 'admin_menu_fcourse');
function admin_menu_fcourse() {
	global $premiumbox;	
	
	add_submenu_page("pn_moduls", __('Exchange rate from file', 'pn'), __('Exchange rate from file', 'pn'), 'administrator', "pn_fcourse", array($premiumbox, 'admin_temp'));
	
}

add_filter('pn_adminpage_title_pn_fcourse', 'pn_admin_title_pn_fcourse');
function pn_admin_title_pn_fcourse($page) {
	
	return __('Exchange rate from file', 'pn');
} 

add_action('pn_adminpage_content_pn_fcourse', 'def_pn_admin_content_pn_fcourse');
function def_pn_admin_content_pn_fcourse() {
	global $wpdb, $premiumbox;

	$form = new PremiumForm();

	$text = '
	'. __('Cron URL for updating rates', 'pn') . '<br /><a href="' . get_cron_link('fcourse_request_cron') . '" target="_blank">' . get_cron_link('fcourse_request_cron') . '</a>
	';
	$form->substrate($text);
	
	$options = array();
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Exchange rate from file settings', 'pn'),
		'submit' => __('Save', 'pn'),
	);	
	$options['url'] = array(
		'view' => 'inputbig',
		'title' => __('URL file with exchange rates', 'pn'),
		'default' => $premiumbox->get_option('fcourse', 'url'),
		'name' => 'url',
	);		
	$params_form = array(
		'filter' => 'pn_fcourse_options',
		'button_title' => __('Save', 'pn'),
	);
	$form->init_form($params_form, $options);
	
}  

add_action('premium_action_pn_fcourse', 'def_premium_action_pn_fcourse');
function def_premium_action_pn_fcourse() {
	global $wpdb, $premiumbox;	
	
	_method('post');
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));
	
	$options = array('url');	
	foreach ($options as $key) {
		$premiumbox->update_option('fcourse', $key, pn_strip_input(is_param_post($key)));
	}		
	
	$url = admin_url('admin.php?page=pn_fcourse&reply=true');
	$form->answer_form($url);
	
} 