<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_add_coupons', 'pn_adminpage_title_pn_add_coupons');
	function pn_adminpage_title_pn_add_coupons() {
		
		$id = intval(is_param_get('item_id'));
		if ($id) {
			return __('Edit coupon', 'pn');
		} else {
			return __('Add coupon', 'pn');
		}
		
	}

	add_action('pn_adminpage_content_pn_add_coupons', 'def_pn_adminpage_content_pn_add_coupons');
	function def_pn_adminpage_content_pn_add_coupons() {
		global $wpdb;

		$form = new PremiumForm();

		$id = intval(is_param_get('item_id'));
		$data_id = 0;
		$data = '';
		
		if ($id) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "coupons WHERE id = '$id'");
			if (isset($data->id)) {
				$data_id = $data->id;
			}	
		}

		if ($data_id) {
			$title = __('Edit coupon', 'pn');
		} else {
			$title = __('Add coupon', 'pn');
		}
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_coupons'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_coupons'),
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
		
		$options['coupon_code'] = array(
			'view' => 'inputbig',
			'title' => __('Сoupon code', 'pn'),
			'default' => is_isset($data, 'coupon_code'),
			'name' => 'coupon_code',
			'work' => 'input',
		);	
		if ($data_id) {
			$options['coupon_code']['atts'] = array('disabled' => 'disabled');
		}
			
		$options['discount'] = array(
			'view' => 'input',
			'title' => __('Discount (%)', 'pn'),
			'default' => is_isset($data, 'discount'),
			'name' => 'discount',
			'work' => 'input',
		);	
		
		$options['coupon_type'] = array(
			'view' => 'select',
			'title' => __('Coupon type', 'pn'),
			'options' => array('0' => __('disposable', 'pn'), '1' => __('reusable', 'pn')),
			'default' => is_isset($data, 'coupon_type'),
			'name' => 'coupon_type',
		);

		$options['coupon_used'] = array(
			'view' => 'select',
			'title' => __('Used', 'pn'),
			'options' => array('0' => __('no', 'pn'), '1' => __('yes', 'pn')),
			'default' => is_isset($data, 'coupon_used'),
			'name' => 'coupon_used',
		);	

		$options['status'] = array(
			'view' => 'select',
			'title' => __('Status', 'pn'),
			'options' => array('1' => __('published', 'pn'), '0' => __('moderating', 'pn')),
			'default' => is_isset($data, 'status'),
			'name' => 'status',
		);		
		
		$params_form = array(
			'filter' => 'pn_coupons_addform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
	}
	
}	

add_action('premium_action_pn_add_coupons', 'def_premium_action_pn_add_coupons');
function def_premium_action_pn_add_coupons() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_coupons'));
		
	$data_id = intval(is_param_post('data_id')); 
	
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "coupons WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}		
			
	$array = array();
	$array['coupon_type'] = intval(is_param_post('coupon_type'));
	$array['coupon_used'] = intval(is_param_post('coupon_used'));
	$array['status'] = intval(is_param_post('status'));
	$discount = is_sum(is_param_post('discount'));
	if ($discount < 0) { $discount = 0; }
	$array['discount'] = $discount;
	
	if (isset($_POST['coupon_code'])) {
		$coupon_code = is_coupon(is_param_post('coupon_code'));
	} else {
		$coupon_code = is_coupon(is_isset($last_data, 'coupon_code'));
	}
	$coupon_code = unique_coupon($coupon_code, is_isset($last_data, 'id'));
	$array['coupon_code'] = $coupon_code;
	
	$array = apply_filters('pn_coupons_addform_post', $array, $last_data);
				
	if ($data_id) {	
		$res = apply_filters('item_coupons_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {
			$result = $wpdb->update($wpdb->prefix . 'coupons', $array, array('id' => $data_id));
			do_action('item_coupons_edit', $data_id, $array, $last_data, $result);
			$res_errors = _debug_table_from_db($result, 'coupons', $array);
			_display_db_table_error($form, $res_errors);			
		} else { $form->error_form(is_isset($res, 'error')); }
	} else {		
		$res = apply_filters('item_coupons_add_before', pn_ind(), $array);
		if ($res['ind']) {
			$result = $wpdb->insert($wpdb->prefix . 'coupons', $array);
			$data_id = $wpdb->insert_id;	
			if ($result) {
				do_action('item_coupons_add', $data_id, $array);
			} else {
				$res_errors = _debug_table_from_db($result, 'coupons', $array);
				_display_db_table_error($form, $res_errors);					
			}
		} else { $form->error_form(is_isset($res, 'error')); }
	}

	$url = admin_url('admin.php?page=pn_add_coupons&item_id='. $data_id .'&reply=true');
	$form->answer_form($url);
	
}