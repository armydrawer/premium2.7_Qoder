<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_add_bidstatus', 'pn_admin_title_pn_add_bidstatus');
	function pn_admin_title_pn_add_bidstatus(){
		$id = intval(is_param_get('item_id'));
		if($id){
			return __('Edit order status','pn');
		} else {
			return __('Add order status','pn');
		}
	}

	add_action('pn_adminpage_content_pn_add_bidstatus', 'def_pn_admin_content_pn_add_bidstatus');
	function def_pn_admin_content_pn_add_bidstatus() {
		global $wpdb;

		$form = new PremiumForm();

		$id = intval(is_param_get('item_id'));
		$data_id = 0;
		$data = '';
		if ($id) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bidstatus WHERE id = '$id'");
			if (isset($data->id)) {
				$data_id = $data->id;
			}	
		}
		if ($data_id) {
			$title = __('Edit order status', 'pn');
		} else {
			$title = __('Add order status', 'pn');
		}
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_bidstatus'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_bidstatus'),
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
			'title' => __('Displayed name', 'pn'),
			'default' => is_isset($data, 'title'),
			'name' => 'title',
			'work' => 'input',
			'ml' => 1,
		);			

		$options['bg_color'] = array(
			'view' => 'colorpicker',
			'title' => __('Color', 'pn'),
			'default' => is_isset($data, 'bg_color'),
			'name' => 'bg_color',
		);			

		$params_form = array(
			'filter' => 'pn_bidstatus_addform',
			'data' => $data,
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
																			
	} 

}

add_action('premium_action_pn_add_bidstatus', 'def_premium_action_pn_add_bidstatus');
function def_premium_action_pn_add_bidstatus() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_bidstatus'));
		
	$data_id = intval(is_param_post('data_id'));
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bidstatus WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}	
		
	$array = array();
	$array['title'] = pn_strip_input(is_param_post_ml('title'));
	$array['bg_color'] = pn_strip_input(is_param_post('bg_color'));

	$array = apply_filters('pn_bidstatus_addform_post', $array, $last_data);
		
	if ($data_id) {	
		$res = apply_filters('item_bidstatus_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {
			$result = $wpdb->update($wpdb->prefix . 'bidstatus', $array, array('id' => $data_id));
			do_action('item_bidstatus_edit', $data_id, $array, $last_data, $result);
			$res_errors = _debug_table_from_db($result, 'bidstatus', $array);
			_display_db_table_error($form, $res_errors);
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}
	} else {	
		$res = apply_filters('item_bidstatus_add_before', pn_ind(), $array);
		if ($res['ind']) {
			$result = $wpdb->insert($wpdb->prefix . 'bidstatus', $array);
			$data_id = $wpdb->insert_id;
			if ($result) {
				do_action('item_bidstatus_add', $data_id, $array);
			} else {
				$res_errors = _debug_table_from_db($result, 'bidstatus', $array);
				_display_db_table_error($form, $res_errors);
			}
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}
	}

	$url = admin_url('admin.php?page=pn_add_bidstatus&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
	
}