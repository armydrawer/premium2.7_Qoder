<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Currency reserve limits[:en_US][ru_RU:]Лимит резерва для валют[:ru_RU]
description: [en_US:]Currency reserve limits[:en_US][ru_RU:]Лимит резерва для валют[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_currlimit');
add_action('pn_plugin_activate', 'bd_all_moduls_active_currlimit');
function bd_all_moduls_active_currlimit() {
	global $wpdb;	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'inday1'");
    if (0 == $query) { 
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `inday1` varchar(50) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'inday2'");
    if (0 == $query) { 
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `inday2` varchar(50) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'inmon1'");
    if (0 == $query) {  
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `inmon1` varchar(50) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'inmon2'");
    if (0 == $query) {  
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `inmon2` varchar(50) NOT NULL default '0'");
    }
	
}

add_filter('list_export_currency', 'currlimit_list_export_currency');
function currlimit_list_export_currency($array) {
	
	$array['inday1'] = __('Daily limit for Send', 'pn');
	$array['inday2'] = __('Daily limit for Receive', 'pn');
	$array['inmon1'] = __('Monthly limit for Send', 'pn');
	$array['inmon2'] = __('Monthly limit for Receive', 'pn');
	
	return $array;
}

add_filter('export_currency_filter', 'currlimit_export_currency_filter');
function currlimit_export_currency_filter($export_currency_filter) {
	
	$export_currency_filter['sum_arr'][] = 'inday1';
	$export_currency_filter['sum_arr'][] = 'inday2';
	$export_currency_filter['sum_arr'][] = 'inmon1';
	$export_currency_filter['sum_arr'][] = 'inmon2';
	
	return $export_currency_filter;
}

add_action('tab_currency_tab2', 'currlimit_tab_currency_tab2', 100, 2);
function currlimit_tab_currency_tab2($data, $data_id) {
	
	$form = new PremiumForm();
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Daily limit for Send', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="inday1" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'inday1')); ?>" />
			</div>	
			<?php $form->help(__('More info', 'pn'), __('Daily limit for currency purchase of currency. Unable to buy more currency more than previously set.', 'pn')); ?>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Daily limit for Receive', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="inday2" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'inday2')); ?>" />	
			</div>
			<?php $form->help(__('More info', 'pn'), __('Daily limit for currency sale. Unable to sell currency more than previously set.', 'pn')); ?>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Monthly limit for Send', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="inmon1" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'inmon1')); ?>" />
			</div>	
			<?php $form->help(__('More info', 'pn'), __('Monthly limit for currency purchase. Unable to buy currency more than previously set.', 'pn')); ?>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Monthly limit for Receive', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="inmon2" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'inmon2')); ?>" />	
			</div>
			<?php $form->help(__('More info', 'pn'), __('Monthly limit for currency sale. Unable to sell currency more than previously set.', 'pn')); ?>
		</div>
	</div>	
	<?php 		
}

add_filter('pn_currency_addform_post', 'currlimit_currency_addform_post');
function currlimit_currency_addform_post($array) {
	
	$array['inday1'] = is_sum(is_param_post('inday1'));
	$array['inday2'] = is_sum(is_param_post('inday2'));
	$array['inmon1'] = is_sum(is_param_post('inmon1'));
	$array['inmon2'] = is_sum(is_param_post('inmon2'));
	
	return $array;
}

add_filter('pntable_columns_pn_currency', 'currlimit_pntable_columns_pn_currency');
function currlimit_pntable_columns_pn_currency($columns) {
	
	$n_columns = array();
	$n_columns['inday1'] = __('Daily limit for Send', 'pn');
	$n_columns['inday2'] = __('Daily limit for Receive', 'pn');
	$columns = pn_array_insert($columns, 'decimal', $n_columns); 
	
	return $columns;
}

add_filter('pntable_column_pn_currency', 'currlimit_pntable_column_pn_currency', 10, 3);
function currlimit_pntable_column_pn_currency($html, $column_name, $item) {
	
	if ('inday1' == $column_name) {		
		return '<input type="text" style="width: 80px;" name="inday1[' . $item->id . ']" value="' . is_sum($item->inday1) . '" />';
	} elseif ('inday2' == $column_name) {		
		return '<input type="text" style="width: 80px;" name="inday2[' . $item->id . ']" value="' . is_sum($item->inday2) . '" />';		
	}
	
	return $html;
}

add_action('pntable_currency_save', 'currlimit_pntable_currency_save');
function currlimit_pntable_currency_save() {
	global $wpdb;
	
	if (isset($_POST['inday1']) and is_array($_POST['inday1'])) { 	
		foreach ($_POST['inday1'] as $id => $inday1) {
			$id = intval($id);
			$inday1 = is_sum($inday1);
			if ($inday1 <= 0) { $inday1 = 0; }			
			$wpdb->query("UPDATE " . $wpdb->prefix . "currency SET inday1 = '$inday1' WHERE id = '$id'");
		}		
	}
	
	if (isset($_POST['inday2']) and is_array($_POST['inday2'])) {		
		foreach ($_POST['inday2'] as $id => $inday2) {
			$id = intval($id);
			$inday2 = is_sum($inday2);
			if ($inday2 <= 0) { $inday2 = 0; }				
			$wpdb->query("UPDATE " . $wpdb->prefix . "currency SET inday2 = '$inday2' WHERE id = '$id'");
		}	
	}
	
}

add_filter('get_max_sum_give', 'currlimit_get_max_sum_give', 10, 4);
function currlimit_get_max_sum_give($max, $direction, $vd1, $vd2) {
	
	$time = current_time('timestamp');
	
	$inday = is_sum($vd1->inday1);
	if ($inday > 0) {
		$date = date('Y-m-d 00:00:00', $time);
		$sum_day = get_sum_currency($vd1->id, 'in', $date);
		$inday = $inday - $sum_day;
		if (is_numeric($max)) {
			if ($max > $inday) {
				$max = $inday;
			}	
		} else {
			$max = $inday;
		}
	}	
	
	$inmon = is_sum($vd1->inmon1);
	if ($inmon > 0) {
		$date = date('Y-m-01 00:00:00', $time);
		$sum_mon = get_sum_currency($vd1->id, 'in', $date);
		$inmon = $inmon - $sum_mon;
		if (is_numeric($max)) {
			if ($max > $inmon) {
				$max = $inmon;
			}	
		} else {
			$max = $inmon;
		}
	}	
	
	return $max;
}

add_filter('get_max_sum_get', 'currlimit_get_max_sum_get', 10, 4);
function currlimit_get_max_sum_get($max, $direction, $vd1, $vd2) {
	
	$time = current_time('timestamp');
	
	$inday = is_sum($vd2->inday2);
	if ($inday > 0) {
		$date = date('Y-m-d 00:00:00', $time);
		$sum_day = get_sum_currency($vd2->id, 'out', $date);
		$inday = $inday - $sum_day;
		
		if (is_numeric($max)) {
			if ($max > $inday) {
				$max = $inday;
			}	
		} else {
			$max = $inday;
		}
	}		
	
	$inmon = is_sum($vd2->inmon2);
	if ($inmon > 0) {
		$date = date('Y-m-01 00:00:00', $time);
		$sum_mon = get_sum_currency($vd2->id, 'out', $date);
		$inmon = $inmon - $sum_mon;
		
		if (is_numeric($max)) {
			if ($max > $inmon) {
				$max = $inmon;
			}	
		} else {
			$max = $inmon;
		}
	}	
	
	return $max;
}