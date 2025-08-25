<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_add_vaccounts_many', 'def_adminpage_title_pn_add_vaccounts_many');
	function def_adminpage_title_pn_add_vaccounts_many() {
		
		return __('Add list', 'pn');
	}

	add_action('pn_adminpage_content_pn_add_vaccounts_many', 'def_adminpage_content_pn_add_vaccounts_many');
	function def_adminpage_content_pn_add_vaccounts_many() {
		global $wpdb;

		$form = new PremiumForm();

		$title = __('Add list', 'pn');
		$currencies = list_currency(__('No item', 'pn'));		
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_vaccounts'),
			'title' => __('Back to list', 'pn')
		);
		$form->back_menu($back_menu, '');

		$options = array();	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => $title,
			'submit' => __('Save', 'pn'),
		);	
		$options['currency_id'] = array(
			'view' => 'select_search',
			'title' => __('Currency name', 'pn'),
			'options' => $currencies,
			'default' => 0,
			'name' => 'currency_id',
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
			'filter' => 'pn_add_vaccounts_many_addform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);					
	}

}

add_action('premium_action_pn_add_vaccounts_many', 'def_premium_action_pn_add_vaccounts_many');
function def_premium_action_pn_add_vaccounts_many() {
	global $wpdb;	

	_method('post');
	
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_vaccounts'));
		
	$items = explode("\n", is_param_post('items'));
	if (is_array($items)) {
					
		$currency_id = intval(is_param_post('currency_id'));
		$status = intval(is_param_post('status'));	
			
		foreach ($items as $item) {
			$accountnum = pn_strip_input($item);
			$accunique = intval(is_param_post('accunique'));
			$comment = pn_strip_input(is_param_post('text_comment'));
			if ($accountnum) {
				
				$array = array(
					'currency_id' => $currency_id,
					'accountnum' => $accountnum,
					'accunique' => $accunique,
					'status' => $status,
					'text_comment' => $comment,
				);
				$wpdb->insert($wpdb->prefix . 'currency_accounts', $array);
				$data_id = $wpdb->insert_id;
				
			}
		}
	}

	$url = admin_url('admin.php?page=pn_vaccounts&reply=true');
	$form->answer_form($url);
	
}