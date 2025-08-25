<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_add_discount', 'pn_adminpage_title_pn_add_discount');
	function pn_adminpage_title_pn_add_discount() {
		
		$id = intval(is_param_get('item_id'));
		if ($id) {
			return __('Edit discount', 'pn');
		} else {
			return __('Add discount', 'pn');
		}
		
	}

	add_action('pn_adminpage_content_pn_add_discount', 'def_pn_adminpage_content_pn_add_discount');
	function def_pn_adminpage_content_pn_add_discount() {
		global $wpdb;

		$form = new PremiumForm();

		$id = intval(is_param_get('item_id'));
		$data_id = 0;
		$data = '';
		
		if ($id) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_discounts WHERE id = '$id'");
			if (isset($data->id)) {
				$data_id = $data->id;
			}	
		}

		if ($data_id) {
			$title = __('Edit discount', 'pn');
		} else {
			$title = __('Add discount', 'pn');
		}
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_discount'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_discount'),
				'title' => __('Add new', 'pn')
			);	
		}
		$form->back_menu($back_menu, $data);	
		
		$options = array();
		$options['hidden_block'] = array(
			'view' => 'hidden_input',
			'name' => 'data_id',
			'default' => $data_id,
		);	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => $title,
			'submit' => __('Save', 'pn'),
		);	
		$options['sumec'] = array(
			'view' => 'input',
			'title' => __('Total amount of exchanges', 'pn') . ' (' . cur_type() . ')',
			'default' => is_isset($data, 'sumec'),
			'name' => 'sumec',
			'work' => 'input',
		);	
		$options['sumec_warning'] = array(
			'view' => 'warning',
			'title' => __('More info', 'pn'),
			'default' => __('The first level of the acumulative discount should always start with the value "0"', 'pn'),
		);	
		$options['discount'] = array(
			'view' => 'input',
			'title' => __('Discount (%)', 'pn'),
			'default' => is_isset($data, 'discount'),
			'name' => 'discount',
			'work' => 'input',
		);	
		
		$params_form = array(
			'filter' => 'pn_discount_addform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);		
	}
	
}	

add_action('premium_action_pn_add_discount', 'def_premium_action_pn_add_discount');
function def_premium_action_pn_add_discount() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_discount'));
		
	$data_id = intval(is_param_post('data_id')); 
	
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_discounts WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}		
			
	$array = array();
	$array['sumec'] = is_sum(is_param_post('sumec'));
	$array['discount'] = is_sum(is_param_post('discount'));

	$array = apply_filters('pn_discount_addform_post', $array, $last_data);
				
	if ($data_id) {	
		$res = apply_filters('item_discount_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {
			$result = $wpdb->update($wpdb->prefix . 'user_discounts', $array, array('id' => $data_id));
			do_action('item_discount_edit', $data_id, $array, $last_data, $result);
			$res_errors = _debug_table_from_db($result, 'user_discounts', $array);
			_display_db_table_error($form, $res_errors);			
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}
	} else {
		$res = apply_filters('item_discount_add_before', pn_ind(), $array);
		if ($res['ind']) {
			$result = $wpdb->insert($wpdb->prefix . 'user_discounts', $array);
			$data_id = $wpdb->insert_id;	
			if ($result) {
				do_action('item_discount_add', $data_id, $array);
			} else {
				$res_errors = _debug_table_from_db($result, 'user_discounts', $array);
				_display_db_table_error($form, $res_errors);					
			}
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}
	}

	$url = admin_url('admin.php?page=pn_add_discount&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
	
}