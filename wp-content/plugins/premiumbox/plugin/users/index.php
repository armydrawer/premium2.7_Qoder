<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('all_user_editform', 'premiumbox_all_user_editform', 10, 2);
function premiumbox_all_user_editform($options, $db_data) {
	
	$user_id = $db_data->ID;
	
	$options['user_exchange_data'] = array(
		'view' => 'h3',
		'title' => __('User data', 'pn'),
		'submit' => __('Save', 'pn'),
	);	
	
	if (current_user_can('pn_bids') or current_user_can('administrator')) {
		
		$options['link_orders'] = array(
			'view' => 'textfield',
			'title' => __('Orders', 'pn'),
			'default' => '<a href="' . admin_url('admin.php?page=pn_bids&iduser=' . $user_id) . '" class="button" target="_blank">' . __('User orders','pn') . '</a>',
		);	
		
	}	
	
	$options['exchange_list'] = array(
		'view' => 'textfield',
		'title' => __('User exchanges', 'pn'),
		'default' => get_user_count_exchanges($user_id) . ' (' . get_user_sum_exchanges($user_id) . ' ' . cur_type() . ')',
	);			
	
	return $options;
}

add_filter('pntable_columns_all_users', 'premiumbox_pntable_columns_all_users');
function premiumbox_pntable_columns_all_users($columns) {
	
	$columns['count_exchanges'] = __('User exchanges', 'pn');
	
	return $columns;
}

add_filter('pntable_column_all_users', 'premiumbox_pntable_column_all_users', 10, 3); 
function premiumbox_pntable_column_all_users($empty, $column_name, $item) {
	
	if ('count_exchanges' == $column_name) {
		return get_user_count_exchanges($item->ID) . '<br />(<strong>' . get_user_sum_exchanges($item->ID) . '</strong>&nbsp;' . cur_type() . ')';
	}		
	
	return $empty;	
}