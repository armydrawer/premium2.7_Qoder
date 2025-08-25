<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_iac_add', 'def_adminpage_title_pn_iac_add');
	function def_adminpage_title_pn_iac_add() {
		
		$id = intval(is_param_get('item_id'));
		if ($id) {
			return __('Edit adjustment', 'pn');
		} else {
			return __('Add adjustment', 'pn');
		}
		
	}

	add_action('pn_adminpage_content_pn_iac_add', 'def_adminpage_content_pn_iac_add');
	function def_adminpage_content_pn_iac_add() {
		global $wpdb;

		$form = new PremiumForm();

		$id = intval(is_param_get('item_id'));
		$data_id = 0;
		$data = '';
		
		if ($id) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "iac WHERE id = '$id'");
			if (isset($data->id)) {
				$data_id = $data->id;
			}	
		}

		if ($data_id) {
			$title = __('Edit adjustment', 'pn');
		} else {
			$title = __('Add adjustment', 'pn');
		}
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_iac'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_iac_add'),
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
		$options['create_date'] = array(
			'view' => 'datetime',
			'title' => __('Creation date', 'pn'),
			'default' => is_isset($data, 'create_date'),
			'name' => 'create_date',
		);		
		$options['title'] = array(
			'view' => 'inputbig',
			'title' => __('Comment', 'pn'),
			'default' => is_isset($data, 'title'),
			'name' => 'title',
		);
		$options['amount'] = array(
			'view' => 'input',
			'title' => __('Amount', 'pn'),
			'default' => is_isset($data, 'amount'),
			'name' => 'amount',
		);
		$currency_codes = list_currency_codes('--' . __('No item', 'pn') . '--');
		$options['currency_code_id'] = array(
			'view' => 'select_search',
			'title' => __('Currency code', 'pn'),
			'options' => $currency_codes,
			'default' => is_isset($data, 'currency_code_id'),
			'name' => 'currency_code_id',
		);	
		$options['user_id'] = array(
			'view' => 'input',
			'title' => __('User ID', 'pn'),
			'default' => is_isset($data, 'user_id'),
			'name' => 'user_id',
		);
		$options['bid_id'] = array(
			'view' => 'input',
			'title' => __('Bid id', 'pn'),
			'default' => is_isset($data, 'bid_id'),
			'name' => 'bid_id',
		);
		$options['status'] = array(
			'view' => 'select',
			'title' => __('Status', 'pn'),
			'options' => array('1' => __('ok', 'pn'), '0' => __('moderating', 'pn')),
			'default' => is_isset($data, 'status'),
			'name' => 'status',
		);		
		$params_form = array(
			'filter' => 'pn_iac_addform',
			'data' => $data,
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);		
	} 

}

add_action('premium_action_pn_iac_add', 'def_premium_action_pn_iac_add');
function def_premium_action_pn_iac_add() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator'));
		
	$data_id = intval(is_param_post('data_id'));
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "iac WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}

	$array = array();
	$array['create_date'] = get_pn_time(is_param_post('create_date'), 'Y-m-d H:i:s');
	$array['title'] = pn_strip_input(is_param_post('title'));
	$array['amount'] = is_sum(is_param_post('amount'));
	$array['user_id'] = intval(is_param_post('user_id'));
	$array['bid_id'] = intval(is_param_post('bid_id'));
	$array['status'] = intval(is_param_post('status'));
	
	$currency_codes = list_currency_codes('--' . __('No item', 'pn') . '--');
	$currency_code_id = intval(is_param_post('currency_code_id'));
	if ($currency_code_id < 1 or !isset($currency_codes[$currency_code_id])) {
		$form->error_form(__('Error! Currency code not entered', 'pn'));
	}
	$array['currency_code_id'] = $currency_code_id;
	
	$array = apply_filters('pn_iac_addform_post', $array, $last_data);
			
	if ($data_id) {
		$res = apply_filters('item_iac_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {	
			$result = $wpdb->update($wpdb->prefix . 'iac', $array, array('id' => $data_id));
			do_action('item_iac_edit', $data_id, $array, $last_data, $result);
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}		
	} else {
		$res = apply_filters('item_iac_add_before', pn_ind(), $array);
		if ($res['ind']) {	
			$result = $wpdb->insert($wpdb->prefix . 'iac', $array);
			$data_id = $wpdb->insert_id;
			do_action('item_iac_add', $data_id, $array);
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}		
	}

	$url = admin_url('admin.php?page=pn_iac_add&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
	
}	