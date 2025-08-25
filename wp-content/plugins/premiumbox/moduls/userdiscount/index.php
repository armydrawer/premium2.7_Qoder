<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Discount from users[:en_US][ru_RU:]Скидки пользователей[:ru_RU]
description: [en_US:]Discount from users[:en_US][ru_RU:]Скидки пользователей[:ru_RU]
version: 2.7.0
category: [en_US:]Users[:en_US][ru_RU:]Пользователи[:ru_RU]
cat: user
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if (!function_exists('bd_all_moduls_active_userdiscount')) {
	add_action('pn_plugin_activate', 'bd_all_moduls_active_userdiscount');
	add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_userdiscount');
	function bd_all_moduls_active_userdiscount() {
		global $wpdb;
			
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'user_discount'");
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `user_discount` varchar(50) NOT NULL default '0'");
		}		
		
	}
}

if (!function_exists('get_user_discount')) {
	function get_user_discount($user_id, $ui = '') {
	    global $wpdb, $pn_user_discounts;

		$user_id = intval($user_id);
		$discount = 0;

        if (isset($pn_user_discounts[$user_id])) {
            
            $discount = $pn_user_discounts[$user_id];
            
        } else {
    		if (!isset($ui->ID)) {
    		    $ui = get_userdata($user_id);
    		}
        	if (isset($ui->user_discount)) {
    		    $user_discount = is_sum($ui->user_discount);
        		if ($user_discount > 0) {
    		        $discount = $user_discount;
    			}
        	}
    		
    		$discount = apply_filters('user_discount', $discount, $user_id, $ui);
    		$pn_user_discounts[$user_id] = $discount;
        }
		
		return $discount;
	}
}

if (!function_exists('lk_widget_options_userdiscount')) {
	add_action('lk_widget_options', 'lk_widget_options_userdiscount', 10, 2);
	function lk_widget_options_userdiscount($instance, $class) {
	?>
		<p>
			<label for="<?php echo $class->get_field_id('discount'); ?>"><strong><?php _e('show user discount', 'pn'); ?>: </strong></label><br />
			<select name="<?php echo $class->get_field_name('discount'); ?>" style="width: 100%" autocomplete="off" id="<?php $class->get_field_id('discount'); ?>">
				<option value="0"><?php _e('No', 'pn'); ?></option>
				<option value="1" <?php selected(is_isset($instance,'discount'), '1'); ?>><?php _e('Yes', 'pn'); ?></option>
			</select>
		</p>
	<?php	
	}
}

if (!function_exists('userdiscount_widget_user_form_array')) {
	add_filter('widget_user_form_array', 'userdiscount_widget_user_form_array', 10, 2);
	function userdiscount_widget_user_form_array($array, $instance) {
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);
		$show_user_discount = intval(is_isset($instance, 'discount'));
		if ($show_user_discount) {
		
			$discount_block = '
			<!-- before user_discount -->
			
			<div class="uswidin">
				<div class="uswidinleft"><span>' . __('Your discount', 'pn'). '</span></div>  
				<div class="uswidinright"><span>[user_discount]%</span></div>
					<div class="clear"></div>
			</div>	
			
			<!-- after user_discount -->
			';				
		
			$array['[discount_block]'] = $discount_block; 
		}
		
		$array['[user_discount]'] = get_user_discount($user_id, $ui); 			
		
		return $array;
	}
}

if (!function_exists('userdiscount_widget_user_form_temp')) {
	add_filter('widget_user_form_temp', 'userdiscount_widget_user_form_temp', 10, 2);
	function userdiscount_widget_user_form_temp ($temp_form, $instance) {
		
		$show_user_discount = intval(is_isset($instance, 'discount'));
		if ($show_user_discount) {
		
			$new_form = '
			<!-- after title -->	
								
			[discount_block]
			';
			
			$temp_form = str_replace('<!-- after title -->', $new_form, $temp_form);
		
		}
		
		return $temp_form;
	}
}

if (!function_exists('userdiscount_all_user_editform')) {
	add_filter('all_user_editform', 'userdiscount_all_user_editform', 10, 2);
	function userdiscount_all_user_editform($options, $db_data) {
		
		$user_id = $db_data->ID;
		
		$options['userdiscount_title'] = array(
			'view' => 'h3',
			'title' => __('User discount', 'pn'),
			'submit' => __('Save', 'pn'),
		);	
		if (current_user_can('edit_users') or current_user_can('administrator')) { 
			$options['user_discount'] = array(
				'view' => 'input',
				'title' => __('Personal discount', 'pn'),
				'default' => is_sum($db_data->user_discount),
				'name' => 'user_discount',
				'work' => 'sum',
			);
		}
		$options['all_discount'] = array(
			'view' => 'textfield',
			'title' => __('Discount (%)','pn'),
			'default' => get_user_discount($user_id) . '%',
		);			
		
		return $options;
	}
}

if (!function_exists('userdiscount_all_user_editform_post')) {
	add_action('all_user_editform_post', 'userdiscount_all_user_editform_post'); 
	function userdiscount_all_user_editform_post($new_user_data) {

		if (current_user_can('edit_users') or current_user_can('administrator')) {
			$new_user_data['user_discount'] = is_sum(is_param_post('user_discount'));
		}
		
		return $new_user_data;
	}
}

if (!function_exists('userdiscount_pntable_columns_all_users')) {
	add_filter('pntable_columns_all_users', 'userdiscount_pntable_columns_all_users');
	function userdiscount_pntable_columns_all_users($columns) {
		
		$columns['users_discount'] = __('Discount (%)', 'pn');
		
		return $columns;
	}
}

if (!function_exists('userdiscount_pntable_column_all_users')) {
	add_filter('pntable_column_all_users', 'userdiscount_pntable_column_all_users', 10, 3); 
	function userdiscount_pntable_column_all_users($empty, $column_name, $item) {
				
		if ('users_discount' == $column_name) {
			return get_user_discount($item->ID, $item) . '%';
		}
				
		return $empty;	
	}
}

if (!function_exists('userdiscount_list_export_directions')) {
	add_filter('list_export_directions', 'userdiscount_list_export_directions', 100);
	function userdiscount_list_export_directions($array) {
		
		$array['enable_user_discount'] = __('User discount', 'pn');
		$array['max_user_discount'] = __('Max. user discount', 'pn');
		
		return $array;
	}
}

if (!function_exists('userdiscount_export_directions_filter')) {
	add_filter('export_directions_filter', 'userdiscount_export_directions_filter', 10);
	function userdiscount_export_directions_filter($export_filter) {
		
		$export_filter['sum_arr'][] = 'max_user_discount';
		$export_filter['qw_arr'][] = 'enable_user_discount';
		
		return $export_filter;
	}
}

if (!function_exists('userdiscount_tab_direction_tab7')) {
	add_action('tab_direction_tab7', 'userdiscount_tab_direction_tab7', 10, 2);
	function userdiscount_tab_direction_tab7($data, $data_id) {		
	?>
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('User discount', 'pn'); ?></span></div>
				
				<div class="premium_wrap_standart">
					<?php 
					$enable_user_discount = is_isset($data, 'enable_user_discount'); 
					if (!is_numeric($enable_user_discount)) { $enable_user_discount = 1; }
					?>														
					<select name="enable_user_discount" autocomplete="off">
						<option value="1" <?php selected($enable_user_discount, 1); ?>><?php _e('Yes', 'pn'); ?></option>
						<option value="0" <?php selected($enable_user_discount, 0); ?>><?php _e('No', 'pn'); ?></option>
					</select>
				</div>			

				<?php do_action('tab_dir_udiscount', 1, $data, $data_id); ?>
			</div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Max. user discount', 'pn'); ?></span></div>
				
				<div class="premium_wrap_standart">
					<input type="text" name="max_user_discount" style="width: 100px;" value="<?php echo is_sum(is_isset($data, 'max_user_discount')); ?>" />%
				</div>	
			
				<?php do_action('tab_dir_udiscount', 2, $data, $data_id); ?>
			</div>
		</div>
	<?php
	}
}	

if (!function_exists('userdiscount_direction_addform_post')) {
	add_filter('pn_direction_addform_post', 'userdiscount_direction_addform_post');
	function userdiscount_direction_addform_post($array) {
		
		$array['enable_user_discount'] = intval(is_param_post('enable_user_discount'));
		$array['max_user_discount'] = is_sum(is_param_post('max_user_discount'));
		
		return $array;
	}
}

add_filter('get_calc_data', 'userdiscount_get_calc_data', 0, 2);
function userdiscount_get_calc_data($cdata, $calc_data) {
		
	$direction = $calc_data['direction'];
	$user_id = intval(is_isset($calc_data, 'user_id'));
	$ui = is_isset($calc_data, 'ui');
		
	$user_discount = 0;
		
	if (1 == $direction->enable_user_discount and $user_id > 0) {
		$user_discount = get_user_discount($user_id, $ui);
		if ($direction->max_user_discount > 0 and $user_discount > $direction->max_user_discount) {
			$user_discount = is_sum($direction->max_user_discount);
		}
	}

	$cdata['user_discount'] = $user_discount;	
		
	return $cdata;
}

add_filter('get_direction_minmax_rate', 'userdiscount_direction_minmax_rate', 10, 4); 
function userdiscount_direction_minmax_rate($rate, $direction, $vd1, $vd2) {
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	

	if (1 == $direction->enable_user_discount and $user_id > 0) {
		$user_discount = get_user_discount($user_id, $ui);
		if ($direction->max_user_discount > 0 and $user_discount > $direction->max_user_discount) {
			$user_discount = is_sum($direction->max_user_discount);
		}
		$rate = $rate + ($rate / 100 * $user_discount);
	}	
		
	return $rate;
}

add_filter('list_stat_userxch', 'userdiscount_list_stat_userxch');
function userdiscount_list_stat_userxch($list_stat) {
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);		
	
	$n_list_stat = array();
	$n_list_stat['discount'] = array(
		'title' => __('Personal discount', 'pn'),
		'content' => is_out_sum(get_user_discount($user_id), 2, 'all') . '%',
	);
	
	return pn_array_insert($list_stat, 'exchanges', $n_list_stat, 'before');
}