<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) { 

	add_filter('pn_adminpage_title_pn_add_caccounts_many', 'def_adminpage_title_pn_add_caccounts_many');
	function def_adminpage_title_pn_add_caccounts_many() {
		
		return __('Add list', 'pn');
	}

	add_action('pn_adminpage_content_pn_add_caccounts_many', 'def_adminpage_content_pn_add_caccounts_many');
	function def_adminpage_content_pn_add_caccounts_many() {
		global $wpdb;

		$form = new PremiumForm();

		$title = __('Add list', 'pn');

		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_caccounts'),
			'title' => __('Back to list', 'pn')
		);
		$form->back_menu($back_menu, '');

		$options = array();	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => $title,
			'submit' => __('Save', 'pn'),
		);	
		$options['items'] = array(
			'view' => 'textarea',
			'title' => __('Accounts (at the beginning of a new line)', 'pn'),
			'default' => '',
			'name' => 'items',
			'rows' => '20',
		);
		$options['text_comment'] = array(
			'view' => 'textarea',
			'title' => __('Comment', 'pn'),
			'default' => '',
			'name' => 'text_comment',
			'rows' => '10',
		);
		$options['accunique'] = array(
			'view' => 'select',
			'title' => __('Uniqueness', 'pn'),
			'options' => array('0' => __('no', 'pn'), '1' => __('yes', 'pn')),
			'default' => 0,
			'name' => 'accunique',
		);		
		$options['status'] = array(
			'view' => 'select',
			'title' => __('Status', 'pn'),
			'options' => array('0' => __('inactive account', 'pn'), '1' => __('active account', 'pn')),
			'default' => 1,
			'name' => 'status',
		);	
		$params_form = array(
			'filter' => 'pn_add_caccounts_many_addform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);					
	}

}

add_action('premium_action_pn_add_caccounts_many', 'def_premium_action_pn_add_caccounts_many');
function def_premium_action_pn_add_caccounts_many() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_caccounts'));
		
	$items = explode("\n",is_param_post('items'));
	if (is_array($items)) {	
		$status = intval(is_param_post('status'));
		$accunique = intval(is_param_post('accunique'));
		$comment = pn_strip_input(is_param_post('text_comment'));		
		foreach ($items as $item) {
			$accountnum = pn_strip_input($item);
			if ($accountnum) {
				$array = array(
					'title' => $accountnum,
					'accountnum' => $accountnum,
					'accountnum_hash' => premium_encrypt($accountnum, EXT_SALT),
					'accunique' => $accunique,
					'status' => $status,
					'text_comment' => $comment,
				);
				$wpdb->insert($wpdb->prefix . 'curr_accounts', $array);
				$data_id = $wpdb->insert_id;		
			}
		}
	}

	$url = admin_url('admin.php?page=pn_caccounts&reply=true');
	$form->answer_form($url);
	
}	