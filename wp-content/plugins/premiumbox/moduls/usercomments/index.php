<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]User comments[:en_US][ru_RU:]Комментарии пользователей[:ru_RU]
description: [en_US:]User comments[:en_US][ru_RU:]Комментарии пользователей[:ru_RU]
version: 2.7.0
category: [en_US:]Other[:en_US][ru_RU:]Остальное[:ru_RU]
cat: other
*/

add_filter('all_user_editform', 'uscom_user_editform', 10, 2);
function uscom_user_editform($options, $db_data) {
	global $wpdb;
	
	$user_id = $db_data->ID;
	if (current_user_can('edit_users') or current_user_can('administrator')) {
		
		$comment_count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "comment_system WHERE itemtype = 'user' AND item_id = '$user_id'");
		
		$n_options = array();
		$n_options['system_comment'] = array(
			'view' => 'textfield',
			'title' => __('Comment', 'pn'),
			'default' => _comment_label('user_comment', $user_id, $comment_count),
		);				
				
		$options = pn_array_insert($options, 'user_ip', $n_options, 'before');
	}	
	
	return $options;
}

add_filter('pntable_columns_all_users', 'uscom_table_columns_users'); 
function uscom_table_columns_users($columns) {
	
	if (current_user_can('edit_users') or current_user_can('administrator')) {
		$n_columns = array();
		$n_columns['admin_comment'] = __('Comment', 'pn');
		$columns = pn_array_insert($columns, '', $n_columns);
	}
	
	return $columns;
}

add_filter('pntable_column_all_users', 'uscom_table_column_users', 10, 3); 
function uscom_table_column_users($empty, $column_name, $item) {
	
	if ('admin_comment' == $column_name) {
		$has_comment = intval(is_isset($item, 'has_comment'));	
		return _comment_label('user_comment', $item->ID, $has_comment);
	}		
	
	return $empty;	
}

add_filter('pntable_select_sql_all_users', 'uscom_select_sql');
function uscom_select_sql ($select_sql) {
	global $wpdb;	
	
	if (current_user_can('edit_users') or current_user_can('administrator')) {
		$select_sql .= ", (SELECT COUNT(" . $wpdb->prefix . "comment_system.id) FROM " . $wpdb->prefix . "comment_system WHERE itemtype = 'user' AND item_id = tbl_users.ID) AS has_comment ";
	}
	
	return $select_sql;
}

add_filter('csl_get_user_comment', 'def_csl_get_user_comment', 10, 2);
function def_csl_get_user_comment($log, $id) {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('edit_users')) {
		$comment = '';
		$last = '';
			
		$id = intval($id);	
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "comment_system WHERE item_id = '$id' AND itemtype = 'user' ORDER BY comment_date DESC");
		foreach ($items as $item) { 
			$last .= '
			<div class="one_comment">
				<div class="one_comment_author"><span class="one_comment_del js_csl_del" data-db="user_comment" data-id="' . $item->id . '"></span><a href="' . pn_edit_user_link($item->user_id) . '" target="_blank">' . pn_strip_input($item->user_login) . '</a>, <span class="one_comment_date">' . get_pn_time($item->comment_date, 'd.m.Y, H:i:s') . '</span></div>
				<div class="one_comment_text">
					'. pn_strip_input($item->text_comment) .'
				</div>
			</div>
			';
		}
			
		$log['status'] = 'success';
		$log['comment'] = '';
		$log['count'] = count($items);
		$log['last'] = $last;
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Authorisation Error', 'pn');
	}
		
	return $log;
}

add_filter('csl_add_user_comment', 'def_csl_add_user_comment', 10, 2);
function def_csl_add_user_comment($log, $id) {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('edit_users')) {
		$id = intval($id);
		$ui = wp_get_current_user();
		$text = pn_strip_input(is_param_post('comment'));
		$log['status'] = 'success';
		if (strlen($text) > 0) {
			$arr = array();
			$arr['comment_date'] = current_time('mysql');
			$arr['user_id'] = $ui->ID;
			$arr['user_login'] = pn_strip_input($ui->user_login);
			$arr['text_comment'] = $text;
			$arr['itemtype'] = 'user';
			$arr['item_id'] = $id;
			$wpdb->insert($wpdb->prefix . 'comment_system', $arr);
		} 
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Authorisation Error', 'pn');
	}			
	
	return $log;
}

add_filter('csl_del_user_comment', 'def_csl_del_user_comment', 10, 2);
function def_csl_del_user_comment($log, $id) {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('edit_users')) {
		$id = intval($id);
		$log['status'] = 'success';
		$wpdb->query("DELETE FROM " . $wpdb->prefix . "comment_system WHERE itemtype = 'user' AND id = '$id'");
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Authorisation Error', 'pn');
	}		
		
	return $log;
}