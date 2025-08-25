<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_add_payouts', 'def_adminpage_title_pn_add_payouts');
	function def_adminpage_title_pn_add_payouts() {
		global $db_data, $wpdb;
	
		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$db_data = '';
			
		if ($item_id) {
			$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_payouts WHERE id = '$item_id'");
			if (isset($db_data->id)) {
				$data_id = $db_data->id;
			}	
		}	
			
		if ($data_id) {
			return __('Edit payout', 'pn');
		} else {
			return __('Add payout', 'pn');
		}		
	}

	add_action('pn_adminpage_content_pn_add_payouts', 'def_adminpage_content_pn_add_payouts');
	function def_adminpage_content_pn_add_payouts() {
		global $db_data, $wpdb;

		$form = new PremiumForm();

		$data_id = intval(is_isset($db_data, 'id'));
		if ($data_id) {
			$title = __('Edit payout', 'pn');
		} else {
			$title = __('Add payout', 'pn');
		}
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_payouts'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_payouts'),
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
			'submit' => __('Save', 'pn'),
		);
		$options['pay_date'] = array(
			'view' => 'datetime',
			'title' => __('Date', 'pn'),
			'default' => is_isset($db_data, 'pay_date'),
			'name' => 'pay_date',
			'work' => 'datetime',
		);		
		$options['user_id'] = array(
			'view' => 'input',
			'title' => __('User', 'pn'),
			'default' => is_isset($db_data, 'user_id'),
			'name' => 'user_id',
			'work' => 'int',
		);		
		$options['pay_sum'] = array(
			'view' => 'inputbig',
			'title' => __('Amount', 'pn'),
			'default' => is_isset($db_data, 'pay_sum'),
			'name' => 'pay_sum',
			'work' => 'input',
		);
		$options['pay_sum_or'] = array(
			'view' => 'inputbig',
			'title' => __('Amount', 'pn').' '.cur_type(),
			'default' => is_isset($db_data, 'pay_sum_or'),
			'name' => 'pay_sum_or',
			'work' => 'input',
		);	
		$currencies = list_currency(__('No item', 'pn'));
		$options['currency_id'] = array(
			'view' => 'select_search',
			'title' => __('Currency', 'pn'),
			'options' => $currencies,
			'default' => is_isset($db_data, 'currency_id'),
			'name' => 'currency_id',
			'work' => 'int',
		);
		$options['pay_account'] = array(
			'view' => 'inputbig',
			'title' => __('Account', 'pn'),
			'default' => is_isset($db_data, 'pay_account'),
			'name' => 'pay_account',
			'work' => 'input',
		);
		$options['comment'] = array(
			'view' => 'textarea',
			'title' => __('Admin comment', 'pn'),
			'default' => is_isset($db_data, 'comment'),
			'name' => 'comment',
			'rows' => '10',
			'ml' => 1,
		);		
		$options['status'] = array(
			'view' => 'select',
			'title' => __('Status', 'pn'),
			'options' => array('0' => __('Request in progress', 'pn'), '1' => __('Request completed', 'pn'), '2' => __('Request rejected', 'pn'), '3' => __('Request is cancelled by user', 'pn')),
			'default' => is_isset($db_data, 'status'),
			'name' => 'status',
		);			
		$params_form = array(
			'filter' => 'pn_payouts_addform',
			'data' => $db_data,
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
	}

}

add_action('premium_action_pn_add_payouts', 'def_premium_action_pn_add_payouts');
function def_premium_action_pn_add_payouts() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_pp'));

	$data_id = intval(is_param_post('data_id'));
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_payouts WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}
		
	$array = array();
	$array['pay_date'] = get_pn_time(is_param_post('pay_date'), 'Y-m-d H:i:s');
	$array['user_id'] = $user_id = intval(is_param_post('user_id'));
	$array['user_login'] = '';
	$ui = get_userdata($user_id);
	if (isset($ui->user_login)) {
		$array['user_login'] = is_user($ui->user_login);
	}
	$array['currency_id'] = $currency_id = intval(is_param_post('currency_id'));
	$array['psys_title'] = '';
	$array['currency_code_id'] = '';
	$array['currency_code_title'] = '';
	$currency = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' AND id = '$currency_id'");
	if (isset($currency->id)) {
		$array['psys_title'] = $currency->psys_title;
		$array['currency_code_id'] = $currency->currency_code_id;
		$array['currency_code_title'] = $currency->currency_code_title;
	} else {
		$form->error_form(__('Error! Currency does not exist or disabled', 'pn'));
	}

	$array['pay_sum'] = is_sum(is_param_post('pay_sum'));
	$array['pay_sum_or'] = is_sum(is_param_post('pay_sum_or'));
	$array['pay_account'] = pn_strip_input(is_param_post('pay_account'));
	$array['comment'] = pn_strip_input(is_param_post_ml('comment'));
	$array['status'] = intval(is_param_post('status'));
		
	$ui = wp_get_current_user();
	$user_id = intval(is_isset($ui, 'ID'));
		
	$array['edit_date'] = current_time('mysql');
	$array['edit_user_id'] = $user_id;
	$array['auto_status'] = 1;		
	$array = apply_filters('pn_payouts_addform_post', $array, $last_data);
				
	if ($data_id) {
		$res = apply_filters('item_user_payouts_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {		
			$result = $wpdb->update($wpdb->prefix . 'user_payouts', $array, array('id' => $data_id));
			do_action('item_user_payouts_edit', $data_id, $array, $last_data, $result);
			if ($result) {
				$update = 1;
				if (isset($last_data->currency_id)) {
					update_currency_reserve($last_data->currency_id);
					if ($last_data->currency_id == $array['currency_id']) {
						$update = 0;
					}
				}						
				if (1 == $update) {
					update_currency_reserve($array['currency_id']);
				}
			}
			$res_errors = _debug_table_from_db($result, 'user_payouts', $array);
			_display_db_table_error($form, $res_errors);
		} else { $form->error_form(is_isset($res, 'error')); }
	} else {
		$res = apply_filters('item_user_payouts_add_before', pn_ind(), $array);
		if ($res['ind']) {
			$result = $wpdb->insert($wpdb->prefix . 'user_payouts', $array);
			$data_id = $wpdb->insert_id;
			if (function_exists('update_currency_reserve')) {
				update_currency_reserve($array['currency_id']);
			}
			do_action('item_user_payouts_add', $data_id, $array);
			$res_errors = _debug_table_from_db($result, 'user_payouts', $array);
			_display_db_table_error($form, $res_errors);
		} else { $form->error_form(is_isset($res, 'error')); }
	}

	$url = admin_url('admin.php?page=pn_add_payouts&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
	
}	