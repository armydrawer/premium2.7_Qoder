<?php 
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_all_add_tapibot_words', 'def_adminpage_title_all_add_tapibot_words');
	function def_adminpage_title_all_add_tapibot_words() {
		global $db_data, $wpdb;
		
		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$db_data = '';
		
		if ($item_id) {
			$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibot_words WHERE id = '$item_id'");
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

	add_action('pn_adminpage_content_all_add_tapibot_words', 'def_pn_adminpage_content_all_add_tapibot_words');
	function def_pn_adminpage_content_all_add_tapibot_words() {
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
			'link' => admin_url('admin.php?page=all_tapibot_words'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=all_add_tapibot_words'),
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
		$options['enter_word'] = array(
			'view' => 'inputbig',
			'title' => __('Enter word', 'pn'),
			'default' => is_isset($db_data, 'enter_word'),
			'name' => 'enter_word',
		);
		$options['get_word'] = array(
			'view' => 'inputbig',
			'title' => __('Replace word', 'pn'),
			'default' => is_isset($db_data, 'get_word'),
			'name' => 'get_word',
		);		
		$params_form = array(
			'filter' => 'all_tapibot_words_addform',
			'data' => $db_data,
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
				
	}	
	
}

add_action('premium_action_all_add_tapibot_words', 'def_premium_action_all_add_tapibot_words');
function def_premium_action_all_add_tapibot_words() {
	global $wpdb;

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_tapibot'));
			
	$data_id = intval(is_param_post('data_id'));
		
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibot_words WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}		
		
	$array = array();
	$array['enter_word'] = $enter_word = pn_maxf(pn_strip_input(is_param_post('enter_word')), 230);
	if (strlen($array['enter_word']) < 1) {
		$form->error_form(__('Error! Title not entered Enter word', 'pn'));
	}	
	$l_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibot_words WHERE id != '$data_id' AND enter_word = '$enter_word'");
	if (isset($l_data->id)) {
		$form->error_form(__('Error! this word already exists', 'pn'));
	}
	$array['get_word'] = pn_maxf(pn_strip_input(is_param_post('get_word')), 230);
	if (strlen($array['get_word']) < 1) {
		$form->error_form(__('Error! Title not entered Replace word', 'pn'));
	}	
	
	$array = apply_filters('all_tapibot_words_addform_post', $array, $last_data);
				
	if ($data_id) {
		$res = apply_filters('item_tapibot_words_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {
			$result = $wpdb->update($wpdb->prefix . 'tapibot_words', $array, array('id' => $data_id));
			do_action('item_tapibot_words_edit', $data_id, $array, $last_data, $result);
			$res_errors = _debug_table_from_db($result, 'tapibot_words', $array);
			_display_db_table_error($form, $res_errors);
		} else { $form->error_form(is_isset($res, 'error')); }
	} else {
		$res = apply_filters('item_tapibot_words_add_before', pn_ind(), $array);
		if ($res['ind']) {
			$result = $wpdb->insert($wpdb->prefix . 'tapibot_words', $array);
			$data_id = $wpdb->insert_id;
			if ($result) {
				do_action('item_tapibot_words_add', $data_id, $array);
			} else {
				$res_errors = _debug_table_from_db($result, 'tapibot_words', $array);
				_display_db_table_error($form, $res_errors);					
			}
		} else { $form->error_form(is_isset($res, 'error')); }		
	}

	$url = admin_url('admin.php?page=all_add_tapibot_words&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
	
}	