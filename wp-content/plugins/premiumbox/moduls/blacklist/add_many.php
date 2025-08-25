<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_all_add_blacklist_many', 'def_adminpage_title_all_add_blacklist_many');
	function def_adminpage_title_all_add_blacklist_many() {
		
		return __('Add list', 'pn');
	}

	add_action('pn_adminpage_content_all_add_blacklist_many', 'def_adminpage_content_all_add_blacklist_many');
	function def_adminpage_content_all_add_blacklist_many() {
		global $wpdb;
	
		$title = __('Add list', 'pn');
		
		$form = new PremiumForm();
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=all_blacklist'),
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
			'title' => __('Value (on a new line)', 'pn'),
			'default' => '',
			'name' => 'items',
			'rows' => '15',
		);	
		$options['meta_key'] = array(
			'view' => 'select',
			'title' => __('Type', 'pn'),
			'options' => array('0' => __('account', 'pn'), '5' => __('real account', 'pn'), '1' => __('e-mail', 'pn'), '2' => __('mobile phone number', 'pn'), '3' => __('skype', 'pn'), '4' => __('ip', 'pn')),
			'default' => '',
			'name' => 'meta_key',
		);
		$options['black_type'] = array(
			'view' => 'select',
			'title' => __('Method', 'pn'),
			'options' => array('0' => __('from general settings', 'pn'), '1' => __('throw an error', 'pn'), '2' => __('stop auto payments', 'pn')),
			'default' => '',
			'name' => 'black_type',
		);			
		$options['comment_text'] = array(
			'view' => 'textarea',
			'title' => __('Comment', 'pn'),
			'default' => '',
			'name' => 'comment_text',
			'rows' => '10',
		);

		$params_form = array(
			'filter' => 'all_add_blacklist_many_addform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
		
	}

}

add_action('premium_action_all_add_blacklist_many', 'def_premium_action_all_add_blacklist_many');
function def_premium_action_all_add_blacklist_many() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_blacklist'));

	$items = explode("\n", is_param_post('items'));
	if (is_array($items)) {	
		$meta_key = intval(is_param_post('meta_key'));
		$black_type = intval(is_param_post('black_type'));
		$comment_text = pn_strip_text(is_param_post('comment_text'));
		foreach ($items as $item) {
			$meta_value = pn_strip_input(str_replace('+', '', $item));
			if ($meta_value) {
				$array = array();
				$array['meta_value'] = $meta_value;
				$array['meta_key'] = $meta_key;
				$array['black_type'] = $black_type;
				$array['comment_text'] = $comment_text;
				$array = apply_filters('all_blacklist_addform_post', $array, '');
				$res = apply_filters('item_blacklist_add_before', pn_ind(), $array);
				if ($res['ind']) {
					$result = $wpdb->insert($wpdb->prefix . 'blacklist', $array);
					$data_id = $wpdb->insert_id;
					if ($result) {
						do_action('item_blacklist_add', $data_id, $array);
					} else {
						$res_errors = _debug_table_from_db($result, 'blacklist', $array);
						_display_db_table_error($form, $res_errors);					
					}
				}
			}
		}
	}

	$url = admin_url('admin.php?page=all_blacklist&reply=true');
	$form->answer_form($url);
	
}		