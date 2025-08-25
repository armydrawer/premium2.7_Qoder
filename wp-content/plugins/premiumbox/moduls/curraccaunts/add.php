<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_add_caccounts', 'pn_admin_title_pn_add_caccounts');
	function pn_admin_title_pn_add_caccounts() {
		
		$id = intval(is_param_get('item_id'));
		if ($id) {
			return __('Edit account', 'pn');
		} else {
			return __('Add account', 'pn');
		}
		
	}

	add_action('pn_adminpage_content_pn_add_caccounts', 'def_adminpage_content_pn_add_caccounts');
	function def_adminpage_content_pn_add_caccounts() {
		global $wpdb;

		$form = new PremiumForm();

		$id = intval(is_param_get('item_id'));
		$data_id = 0;
		$data = '';
		
		if ($id) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "curr_accounts WHERE id = '$id'");
			if (isset($data->id)) {
				$data_id = $data->id;
			}	
		}

		if ($data_id) {
			$title = __('Edit account', 'pn');
		} else {
			$title = __('Add account', 'pn');
		}
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_caccounts'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_caccounts'),
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
		$options['title'] = array(
			'view' => 'inputbig',
			'title' => __('Account title', 'pn'),
			'default' => is_isset($data, 'title'),
			'name' => 'title',
		);
		$options['tech_title'] = array(
			'view' => 'inputbig',
			'title' => __('Account title (technical)', 'pn'),
			'default' => is_isset($data, 'tech_title'),
			'name' => 'tech_title',
		);		
		$options['accountnum'] = array(
			'view' => 'inputbig',
			'title' => __('Account', 'pn'),
			'default' => is_isset($data, 'accountnum'),
			'name' => 'accountnum',
		);					
		$options['inday'] = array(
			'view' => 'input',
			'title' => __('Daily limit', 'pn'),
			'default' => is_isset($data, 'inday'),
			'name' => 'inday',
		);
		$options['inmonth'] = array(
			'view' => 'input',
			'title' => __('Monthly limit', 'pn'),
			'default' => is_isset($data, 'inmonth'),
			'name' => 'inmonth',
		);
		$options['text_comment'] = array(
			'view' => 'textarea',
			'title' => __('Comment', 'pn'),
			'default' => is_isset($data, 'text_comment'),
			'name' => 'text_comment',
			'rows' => '10',
		);	
		$options['accunique'] = array(
			'view' => 'select',
			'title' => __('Uniqueness', 'pn'),
			'options' => array('0' => __('no', 'pn'), '1' => __('yes', 'pn')),
			'default' => is_isset($data, 'accunique'),
			'name' => 'accunique',
		);		
		$options['status'] = array(
			'view' => 'select',
			'title' => __('Status', 'pn'),
			'options' => array('0' => __('inactive account', 'pn'), '1' => __('active account', 'pn')),
			'default' => is_isset($data, 'status'),
			'name' => 'status',
		);
		$params_form = array(
			'filter' => 'pn_caccounts_addform',
			'data' => $data,
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
		
	}

}

add_action('premium_action_pn_add_caccounts', 'def_premium_action_pn_add_caccounts');
function def_premium_action_pn_add_caccounts() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_caccounts'));
		
	$form = new PremiumForm();
		
	$data_id = intval(is_param_post('data_id')); 
				
	$array = array();

	$array['text_comment'] = pn_strip_input(is_param_post('text_comment'));

	$array['accountnum'] = $accountnum = pn_strip_input(is_param_post('accountnum'));
	if (!$accountnum) { $form->error_form(__('You did not enter your account', 'pn')); }
		
	$title = pn_strip_input(is_param_post('title'));
	if (strlen($title) < 1) {
		$title = $accountnum;
	}
	$array['title'] = $title;
	$array['tech_title'] = pn_strip_input(is_param_post('tech_title'));
	$array['accountnum_hash'] = premium_encrypt($accountnum, EXT_SALT);
	$array['inday'] = is_sum(is_param_post('inday'));
	$array['inmonth'] = is_sum(is_param_post('inmonth'));
	$array['accunique'] = intval(is_param_post('accunique'));
	$array['status'] = intval(is_param_post('status'));
				
	$array = apply_filters('pn_caccounts_addform_post', $array);
				
	if ($data_id) {
		$result = $wpdb->update($wpdb->prefix . 'curr_accounts', $array, array('id' => $data_id));
	} else {
		$wpdb->insert($wpdb->prefix . 'curr_accounts', $array);
		$data_id = $wpdb->insert_id;	
	}

	$url = admin_url('admin.php?page=pn_add_caccounts&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
	
}	