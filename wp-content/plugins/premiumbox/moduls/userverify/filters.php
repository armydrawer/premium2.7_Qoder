<?php
if (!defined('ABSPATH')) { exit(); } 

add_action('delete_user', 'delete_user_userverify');
function delete_user_userverify($user_id) {
	global $wpdb;
	
	$usves = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "verify_bids WHERE user_id = '$user_id'");
	foreach ($usves as $item) {
		$id = $item->id;
		$res = apply_filters('item_usve_delete_before', pn_ind(), $id, $item);
		if ($res['ind']) {
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "verify_bids WHERE id = '$id'");
			do_action('item_usve_delete', $id, $item, $result);
		}
	}
}

add_filter('_icon_indicators', 'userverify_icon_indicators');
function userverify_icon_indicators($lists) {
	
	$plugin = get_plugin_class();
	$lists['userverify'] = array(
		'title' => __('Requests for identity verification', 'pn'),
		'img' => $plugin->plugin_url . 'images/userverify.png',
		'url' => admin_url('admin.php?page=all_usve&filter=1')
	);
	
	return $lists;
}

add_filter('_icon_indicator_userverify', 'def_icon_indicator_userverify');
function def_icon_indicator_userverify($count) {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_userverify')) {
		$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "verify_bids WHERE auto_status = '1' AND status = '1'");
	}	
	
	return $count;
}

add_filter('user_discount', 'userverify_user_discount', 99, 3);
function userverify_user_discount($sk, $user_id, $ui) {
	
	$plugin = get_plugin_class();	
	if ($user_id) {
		if (isset($ui->user_verify) and 1 == $ui->user_verify) {
			$verifysk = is_sum($plugin->get_option('usve', 'verifysk'));
			$sk = is_sum($sk + $verifysk);
		}
	}
	
	return $sk;
}  

/* users */
add_filter("pntable_trclass_all_users", 'userverify_pntable_trclass_all_users', 10, 2);
function userverify_pntable_trclass_all_users($tr_class, $item) {
	
	if (1 == is_isset($item, 'user_verify')) {
		$tr_class[] = 'tr_green';
	}
	
	return $tr_class;	
}

add_filter("pntable_bulkactions_all_users", 'userverify_pntable_bulkactions_all_users');
function userverify_pntable_bulkactions_all_users($actions) {
	
	$new_actions = array(
		'verify'    => __('Verified', 'pn'),
		'unverify'    => __('Unverified', 'pn'),	
	);
	if (current_user_can('administrator') or current_user_can('pn_userverify')) {
		$actions = pn_array_insert($actions, 'delete', $new_actions, 'before');
	}
	
	return $actions;
}

add_action('pntable_users_action', 'pntable_users_action_verify', 10, 2);
function pntable_users_action_verify($action, $post_ids) {
	global $wpdb;	
	
	if (current_user_can('administrator') or current_user_can('pn_userverify')) {
		if ('verify' == $action) {		
			foreach ($post_ids as $id) {
				$id = intval($id);
				$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "users WHERE ID = '$id' AND user_verify != '1'");
				if (isset($item->ID)) {
					$wpdb->query("UPDATE " . $wpdb->prefix . "users SET user_verify = '1' WHERE ID = '$id'");
					do_action('item_users_verify', $id, $item);
				}
			}									
		}
		if ('unverify' == $action) {		
			foreach ($post_ids as $id) {
				$id = intval($id);
				$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "users WHERE ID = '$id' AND user_verify != '0'");
				if (isset($item->ID)) {
					$wpdb->query("UPDATE " . $wpdb->prefix . "users SET user_verify = '0' WHERE ID = '$id'");
					do_action('item_users_unverify', $id, $item);
				}
			}								
		}	
	}
}

add_filter("pntable_submenu_all_users", 'userverify_pntable_submenu_all_users', 10, 3);
function userverify_pntable_submenu_all_users($options) {
	
	$options['filter'] = array(
		'options' => array(
			'1' => __('verified users', 'pn'),
			'2' => __('unverified users', 'pn'),
		),
	);
	
	return $options;
}

add_filter("pntable_searchwhere_all_users", 'userverify_pntable_searchwhere_all_users');
function userverify_pntable_searchwhere_all_users($where) {
	
	$filter = intval(is_param_get('filter'));
	if (1 == $filter) {
		$where .= " AND tbl_users.user_verify = '1'";
	} elseif (2 == $filter) {
		$where .= " AND tbl_users.user_verify = '0'";
	}
	
	return $where;
}

add_filter("pntable_columns_all_users", 'userverify_pntable_columns_all_users', 100);
function userverify_pntable_columns_all_users($columns) {
	
	$columns['user_verify'] = __('Identity verification', 'pn');
	
	return $columns;
}

add_filter("pntable_column_all_users", 'userverify_pntable_column_all_users', 10, 3);
function userverify_pntable_column_all_users($return, $column_name, $item) {
	
	if ('user_verify' == $column_name) {
		if (isset($item->user_verify) and 1 == $item->user_verify) {
			return '<span class="bgreen">' . __('verified', 'pn') . '</span>';
		} else {
			return '<span class="bred">' . __('not verified', 'pn') . '</span>';
		}		
	}
	
	return $return;
}

add_filter('all_user_editform', 'verify_all_user_editform', 1000, 2);
function verify_all_user_editform($options, $db_data) {
	global $wpdb;
		
	$user_id = $db_data->ID;
		
	if (current_user_can('administrator') or current_user_can('pn_userverify')) { 
	
		$options['user_verify_h3'] = array(
			'view' => 'h3',
			'title' => __('Verification', 'pn'),
			'submit' => __('Save', 'pn'),
		);	
		
		$options['user_verify'] = array(
			'view' => 'select',
			'title' => __('Status', 'pn'),
			'options' => array('0' => __('not verified', 'pn'), '1' => __('verified', 'pn')),
			'default' => intval($db_data->user_verify),
			'name' => 'user_verify',
		);		
			
		$uv_files = '';
		if (1 == $db_data->user_verify) {
			$fields = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_field LEFT OUTER JOIN " . $wpdb->prefix . "uv_field_user ON(" . $wpdb->prefix . "uv_field.id = ". $wpdb->prefix ."uv_field_user.uv_field) WHERE user_id='$user_id' AND ".$wpdb->prefix."uv_field.fieldvid='1' ORDER BY uv_order ASC");
			foreach ($fields as $field) {
				$uv_files .= '<div><strong>'. pn_strip_input(ctv_ml($field->title)) .':</strong> <a href="'. get_usve_doc_view($field->id) .'" target="_blank">'. __('View', 'pn') .'</a> | <a href="'. get_usve_doc($field->id) .'" target="_blank">'. __('Download', 'pn') .'</a></div>';
			}
		}
		if ($uv_files) {
			$options['verification_files'] = array(
				'view' => 'textfield',
				'title' => __('Verification files', 'pn'),
				'default' => $uv_files,
			);	
		}
		
	}
		
	return $options;
}

add_filter('all_user_editform_post', 'verify_all_user_editform_post', 10, 3); 
function verify_all_user_editform_post($new_user_data, $user_id, $user_data) {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_userverify')) { 
		$new_user_data['user_verify'] = $user_verify = intval(is_param_post('user_verify'));
		$old_user_verify = intval(is_isset($user_data, 'user_verify'));
		if (1 == $user_verify and 0 == $old_user_verify) {
			do_action('item_users_verify', $user_id, $user_data);
		}
		if (0 == $user_verify and 1 == $old_user_verify) {
			do_action('item_users_unverify', $user_id, $user_data);
		}			
	}
	
	return $new_user_data;
}

function pn_verify_uv($key) {
	
	$plugin = get_plugin_class();	
	$uf = $plugin->get_option('usve', 'verify_fields');
	
	return intval(is_isset($uf, $key));
}

add_filter('disabled_account_form_line', 'userverify_disabled_account_form_line', 99, 3);
function userverify_disabled_account_form_line($disabled, $name, $ui) {
	
	if (isset($ui->user_verify) and 1 == $ui->user_verify) {
		if (pn_verify_uv($name)) {
			return 1;
		}
	}
	
	return $disabled;
}