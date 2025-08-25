<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Merchant copy[:en_US][ru_RU:]Копирование мерчантов и авто-выплат[:ru_RU]
description: [en_US:]Merchant copy[:en_US][ru_RU:]Копирование мерчантов и авто-выплат[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
new: 1
*/

add_action('premium_action_copy_merch', 'def_premium_action_copy_merch');
function def_premium_action_copy_merch() {
	global $wpdb, $premiumbox;	

	pn_only_caps(array('administrator', 'pn_merchants'));
			
	$form = new PremiumForm();	
			
	$item_id = intval(is_param_get('item_id'));
	if ($item_id) {
		$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exts WHERE id = '$item_id'");
		if (isset($data->id)) {
			$last_id = $data->id;	
			$ext_type = $data->ext_type;
			$ext_key = $data->ext_key;
			
			$array = array();
			foreach ($data as $key => $item) {
				if ('id' != $key) {
					$array[$key] = $item;
				}
				if ('ext_title' == $key) {
					$array[$key] = $item . ' [copy]';
				}	
			}
			$array['ext_key'] = _ext_set_key($data->ext_plugin, $ext_type, 0);
			$array['ext_status'] = 0;
			$wpdb->insert($wpdb->prefix . 'exts', $array);
			$new_id = $wpdb->insert_id;
			if ($new_id) {
				$file = $premiumbox->upload_dir . '/' . $ext_type . '/' . $ext_key . '.php';
				$new_file = $premiumbox->upload_dir . '/' . $ext_type . '/' . $array['ext_key'] . '.php';
				if (is_file($file)) {
					@copy($file, $new_file);
				}	
			}
			
			if ('merchants' == $ext_type) {
				$url = admin_url('admin.php?page=pn_merchants') . '&reply=true';
			} else {
				$url = admin_url('admin.php?page=pn_paymerchants') . '&reply=true';			
			}
			$form->answer_form($url);	
		}
	}
	
	echo 'Error!'; exit;			
}

add_filter('pntable_columns_pn_merchants', 'copy_pntable_columns_pn_merch', 1000);
add_filter('pntable_columns_pn_paymerchants', 'copy_pntable_columns_pn_merch', 1000);
function copy_pntable_columns_pn_merch($columns) {
	
	$columns['copy'] = __('Copy', 'pn');
	
	return $columns;
}

add_filter('pntable_column_pn_merchants', 'copy_pntable_column_pn_merch', 10, 3);
add_filter('pntable_column_pn_paymerchants', 'copy_pntable_column_pn_merch', 10, 3);
function copy_pntable_column_pn_merch($column,$column_name,$item) {
	
	if ('copy' == $column_name) {	
		$column = '<a href="' . pn_link('copy_merch') . '&item_id=' . $item->id . '" class="button">' . __('Copy', 'pn') . '</a>';
	} 
	
	return $column;
}

add_filter('pn_admin_backmenu_pn_add_merchants', 'copy_pn_admin_back_menu_pn_merch', 1000, 2);
add_filter('pn_admin_backmenu_pn_add_paymerchants', 'copy_pn_admin_back_menu_pn_merch', 1000, 2);
function copy_pn_admin_back_menu_pn_merch($back_menu, $db_data) {
	
	$data_id = intval(is_isset($db_data, 'id'));
	if ($data_id) {
		$back_menu['copy'] = array(
			'link' => pn_link('copy_merch') . '&item_id=' . $data_id,
			'title' => __('Copy', 'pn')
		);
	}
	
	return $back_menu;
}	