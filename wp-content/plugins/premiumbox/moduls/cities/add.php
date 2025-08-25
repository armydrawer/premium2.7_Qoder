<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_all_add_cities', 'def_adminpage_title_all_add_cities');
	function def_adminpage_title_all_add_cities() {
		global $db_data, $wpdb;
			
		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$db_data = '';
			
		if ($item_id) {
			$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "cities WHERE id = '$item_id'");
			if (isset($db_data->id)) {
				$data_id = $db_data->id;
			}	
		}	
			
		if ($data_id) {
			return __('Edit', 'pn');
		} else {
			return __('Add', 'pn');
		}	
		
	}

	add_action('pn_adminpage_content_all_add_cities', 'def_pn_adminpage_content_all_add_cities');
	function def_pn_adminpage_content_all_add_cities() {
		global $db_data, $wpdb;

		$form = new PremiumForm();

		$data_id = intval(is_isset($db_data, 'id'));
		if ($data_id) {
			$title = __('Edit', 'pn');
		} else {
			$title = __('Add', 'pn');
		}

		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=all_cities'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);			
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=all_add_cities'),
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
		$options['title'] = array(
			'view' => 'inputbig',
			'title' => __('Title city', 'pn'),
			'default' => is_isset($db_data, 'title'),
			'name' => 'title',
			'work' => 'input',
			'ml' => 1,
		);	
		$options['xml_value'] = array(
			'view' => 'inputbig',
			'title' => __('XML name', 'pn'),
			'default' => is_isset($db_data, 'xml_value'),
			'name' => 'xml_value',
			'work' => 'input',
		);	
		$options['status'] = array(
			'view' => 'select',
			'title' => __('Status', 'pn'),
			'options' => array('1' => __('published', 'pn'), '0' => __('moderating', 'pn')),
			'default' => is_isset($db_data, 'status'),
			'name' => 'status',
			'work' => 'int',
		);	
		$params_form = array(
			'filter' => 'all_cities_addform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	

	}

}

add_action('premium_action_all_add_cities', 'def_premium_action_all_add_cities');
function def_premium_action_all_add_cities() {
	global $wpdb;

	_method('post');
			
	$form = new PremiumForm();
	$form->send_header();
			
	pn_only_caps(array('administrator'));
				
	$data_id = intval(is_param_post('data_id'));
			
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "cities WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}		
			
	$array = array();
	$array['title'] = pn_strip_input(is_param_post_ml('title'));
	$array['xml_value'] = mb_strtoupper(is_xml_value(is_param_post('xml_value')));
	$array['status'] = intval(is_param_post('status'));

	$ui = wp_get_current_user();
	$user_id = intval(is_isset($ui, 'ID'));

	$array['edit_date'] = current_time('mysql');
	$array['edit_user_id'] = $user_id;
	$array['auto_status'] = 1;
	$array = apply_filters('all_cities_addform_post', $array, $last_data);
							
	if ($data_id) {
		$res = apply_filters('item_cities_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {
			$result = $wpdb->update($wpdb->prefix . 'cities', $array, array('id' => $data_id));
			do_action('item_cities_edit', $data_id, $array, $last_data, $result);
			$res_errors = _debug_table_from_db($result, 'cities', $array);
			_display_db_table_error($form, $res_errors);
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}
	} else {
		$res = apply_filters('item_cities_add_before', pn_ind(), $array);
		if ($res['ind']) {
			$array['create_date'] = current_time('mysql');
			$result = $wpdb->insert($wpdb->prefix . 'cities', $array);
			$data_id = $wpdb->insert_id;
			if ($result) {
				do_action('item_cities_add', $data_id, $array);
			} else {
				$res_errors = _debug_table_from_db($result, 'cities', $array);
				_display_db_table_error($form, $res_errors);					
			}
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}		
	}

	$url = admin_url('admin.php?page=all_add_cities&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);

}