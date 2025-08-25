<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_add_dgroups', 'pn_admin_title_pn_add_dgroups');
	function pn_admin_title_pn_add_dgroups(){
		$id = intval(is_param_get('item_id'));
		if($id){
			return __('Edit','pn');
		} else {
			return __('Add','pn');
		}
	}

	add_action('pn_adminpage_content_pn_add_dgroups', 'def_pn_admin_content_pn_add_dgroups');
	function def_pn_admin_content_pn_add_dgroups() {
		global $wpdb;

		$form = new PremiumForm();

		$id = intval(is_param_get('item_id'));
		$data_id = 0;
		$data = '';
		if ($id) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "dgroups WHERE id = '$id'");
			if (isset($data->id)) {
				$data_id = $data->id;
			}	
		}
		if ($data_id) {
			$title = __('Edit', 'pn');
		} else {
			$title = __('Add', 'pn');
		}
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_dgroups'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_dgroups'),
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
			'title' => __('Title', 'pn'),
			'default' => is_isset($data, 'title'),
			'name' => 'title',
			'work' => 'input',
		);						

		$params_form = array(
			'filter' => 'pn_dgroups_addform',
			'data' => $data,
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
																			
	} 

}

add_action('premium_action_pn_add_dgroups', 'def_premium_action_pn_add_dgroups');
function def_premium_action_pn_add_dgroups() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_directions'));
		
	$data_id = intval(is_param_post('data_id'));
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "dgroups WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}	
		
	$array = array();
	$array['title'] = pn_strip_input(is_param_post('title'));

	$array = apply_filters('pn_dgroups_addform_post', $array, $last_data);
		
	if ($data_id) {	
		$res = apply_filters('item_dgroups_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {
			$result = $wpdb->update($wpdb->prefix . 'dgroups', $array, array('id' => $data_id));
			do_action('item_dgroups_edit', $data_id, $array, $last_data, $result);
			$res_errors = _debug_table_from_db($result, 'dgroups', $array);
			_display_db_table_error($form, $res_errors);
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}
	} else {	
		$res = apply_filters('item_dgroups_add_before', pn_ind(), $array);
		if ($res['ind']) {
			$result = $wpdb->insert($wpdb->prefix . 'dgroups', $array);
			$data_id = $wpdb->insert_id;
			if ($result) {
				do_action('item_dgroups_add', $data_id, $array);
			} else {
				$res_errors = _debug_table_from_db($result, 'dgroups', $array);
				_display_db_table_error($form, $res_errors);
			}
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}
	}

	$url = admin_url('admin.php?page=pn_add_dgroups&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
	
}