<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_add_currency_codes', 'def_adminpage_title_pn_add_currency_codes');
	function def_adminpage_title_pn_add_currency_codes() {
		global $db_data, $wpdb;	
		
		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$db_data = '';
		
		if ($item_id) {
			$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_codes WHERE id = '$item_id'");
			if (isset($db_data->id)) {
				$data_id = $db_data->id;
			}	
		}		
		
		if ($data_id) {
			return __('Edit currency code', 'pn');
		} else {
			return __('Add currency code', 'pn');
		}	
	}

	add_action('pn_adminpage_content_pn_add_currency_codes', 'def_adminpage_content_pn_add_currency_codes');
	function def_adminpage_content_pn_add_currency_codes() {
		global $db_data, $wpdb;

		$form = new PremiumForm();

		$data_id = intval(is_isset($db_data, 'id'));
		if ($data_id) {
			$title = __('Edit currency code', 'pn');
		} else {
			$title = __('Add currency code', 'pn');
		}	

		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_currency_codes'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_currency_codes'),
				'title' => __('Add new', 'pn')
			);	
		}
		$form->back_menu($back_menu, $db_data);

		$options = array();
		$options['hidden_block'] = array(
			'view' => 'hidden_input',
			'name' => 'data_id',
			'default' => $data_id,
		);	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => $title,
			'submit' => __('Save','pn'),
		);	
		$options['currency_code_title'] = array(
			'view' => 'input',
			'title' => __('Currency code', 'pn'),
			'default' => is_isset($db_data, 'currency_code_title'),
			'name' => 'currency_code_title',
		);
		if (isset($db_data->id)) {
			$options['internal_rate_show'] = array(
				'view' => 'textfield',
				'title' => __('Internal rate per', 'pn'). ' 1 ' . cur_type(),
				'default' => '1 ' . cur_type() . ' = ' . is_cc_rate($db_data->id, $db_data) . ' ' . is_isset($db_data, 'currency_code_title'),
			);	
		}
		if (current_user_can('administrator') or current_user_can('pn_change_ir')) {
			$standart_course_cc = is_cc_check_rate($db_data);
			if (!$standart_course_cc) {
				$options['internal_rate'] = array(
					'view' => 'inputbig',
					'title' => __('Internal rate per', 'pn') . ' 1 ' . cur_type(),
					'default' => is_isset($db_data, 'internal_rate'),
					'name' => 'internal_rate',
				);
			}	
		}
		$params_form = array(
			'filter' => 'pn_currency_code_addform',
			'data' => $db_data,
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
						
	}

}

add_action('premium_action_pn_add_currency_codes', 'def_premium_action_pn_add_currency_codes');
function def_premium_action_pn_add_currency_codes() {
	global $wpdb;

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_currency'));		
		
	$data_id = intval(is_param_post('data_id')); 
		
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_codes WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}		
			
	$array = array();
	$array['currency_code_title'] = $currency_code_title = is_site_value(is_param_post('currency_code_title'));
	if (strlen($currency_code_title) < 1) { $form->error_form(__('Error! You did not enter the name', 'pn')); }

	if (current_user_can('administrator') or current_user_can('pn_change_ir')) {
		$array['internal_rate'] = is_sum(is_param_post('internal_rate'));
		if ($array['internal_rate'] <= 0) { $array['internal_rate'] = 1; }
	}
			 
	$ui = wp_get_current_user();
	$user_id = intval(is_isset($ui, 'ID'));

	$array['edit_date'] = current_time('mysql');
	$array['edit_user_id'] = $user_id;
	$array['auto_status'] = 1;		
	$array = apply_filters('pn_currency_code_addform_post', $array, $last_data);
				
	$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "currency_codes WHERE currency_code_title = '$currency_code_title' AND id != '$data_id'");
	if ($cc > 0) {
		$form->error_form(__('Error! This currency code already exists', 'pn'));
	}
			
	if ($data_id) {
		$res = apply_filters('item_currency_code_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {
			$result = $wpdb->update($wpdb->prefix . 'currency_codes', $array, array('id' => $data_id));
			do_action('item_currency_code_edit', $data_id, $array, $last_data, $result);
			$res_errors = _debug_table_from_db($result, 'currency_codes', $array);
			_display_db_table_error($form, $res_errors);			
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}
	} else {
		$res = apply_filters('item_currency_code_add_before', pn_ind(), $array);
		if ($res['ind']) {
			$array['create_date'] = current_time('mysql');
			$result = $wpdb->insert($wpdb->prefix . 'currency_codes', $array);
			$data_id = $wpdb->insert_id;
			if ($result) {
				do_action('item_currency_code_add', $data_id, $array);
			} else {
				$res_errors = _debug_table_from_db($result, 'currency_codes', $array);
				_display_db_table_error($form, $res_errors);					
			}
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}
	}

	$url = admin_url('admin.php?page=pn_add_currency_codes&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
	
}	