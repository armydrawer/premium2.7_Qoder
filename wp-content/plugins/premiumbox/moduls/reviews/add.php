<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	if (!function_exists('def_adminpage_title_all_add_reviews')) {
		add_filter('pn_adminpage_title_all_add_reviews', 'def_adminpage_title_all_add_reviews');
		function def_adminpage_title_all_add_reviews() {
			global $db_data, $wpdb;
			
			$data_id = 0;
			$item_id = intval(is_param_get('item_id'));
			$db_data = '';
			
			if ($item_id) {
				$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "reviews WHERE id = '$item_id'");
				if (isset($db_data->id)) {
					$data_id = $db_data->id;
				}	
			}		
			
			if ($data_id) {
				return __('Edit review', 'pn');
			} else {
				return __('Add review', 'pn');
			}		
		}
	}

	if (!function_exists('def_pn_adminpage_content_all_add_reviews')) {
		add_action('pn_adminpage_content_all_add_reviews', 'def_pn_adminpage_content_all_add_reviews');
		function def_pn_adminpage_content_all_add_reviews() {
			global $wpdb, $db_data;

			$form = new PremiumForm();

			$data_id = intval(is_isset($db_data, 'id'));
			if ($data_id) {
				$title = __('Edit review', 'pn');
			} else {
				$title = __('Add review', 'pn');
			}	
			
			$langs = get_langs_ml();
			$the_lang = array();
			foreach ($langs as $lan) {
				$the_lang[$lan] = get_title_forkey($lan);
			}
			
			$back_menu = array();
			$back_menu['back'] = array(
				'link' => admin_url('admin.php?page=all_reviews'),
				'title' => __('Back to list', 'pn')
			);
			$back_menu['save'] = array(
				'link' => '#',
				'title' => __('Save', 'pn'),
				'atts' => array('class' => "savelink save_admin_ajax_form"),
			);			
			if ($data_id) {
				$back_menu['add'] = array(
					'link' => admin_url('admin.php?page=all_add_reviews'),
					'title' => __('Add new', 'pn')
				);	
				if (isset($db_data->review_status, $db_data->auto_status) and 'publish' == $db_data->review_status and 1 == $db_data->auto_status) {
					$back_menu['link'] = array(
						'link' => get_review_link($data_id, $db_data),
						'title' => __('View', 'pn'),
						'atts' => array("target" => "_blank"),
					);	
				}		
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
			$options['review_date'] = array(
				'view' => 'datetime',
				'title' => __('Publication date', 'pn'),
				'default' => is_isset($db_data, 'review_date'),
				'name' => 'review_date',
			);	
			$options['user_id'] = array(
				'view' => 'inputbig',
				'title' => __('User ID', 'pn'),
				'default' => is_isset($db_data, 'user_id'),
				'name' => 'user_id',
			);	
			$options['user_name'] = array(
				'view' => 'inputbig',
				'title' => __('User name', 'pn'),
				'default' => is_isset($db_data, 'user_name'),
				'name' => 'user_name',
			);	
			$options['user_email'] = array(
				'view' => 'inputbig',
				'title' => __('User e-mail', 'pn'),
				'default' => is_isset($db_data, 'user_email'),
				'name' => 'user_email',
			);
			$options['user_site'] = array(
				'view' => 'inputbig',
				'title' => __('Website', 'pn'),
				'default' => is_isset($db_data, 'user_site'),
				'name' => 'user_site',
			);
			$options['user_browser'] = array(
				'view' => 'inputbig',
				'title' => __('User browser', 'pn'),
				'default' => is_isset($db_data, 'user_browser'),
				'name' => 'user_browser',
			);
			$options['user_ip'] = array(
				'view' => 'inputbig',
				'title' => __('User ip', 'pn'),
				'default' => is_isset($db_data, 'user_ip'),
				'name' => 'user_ip',
			);			
			$options['review_text'] = array(
				'view' => 'textarea',
				'title' => __('Text', 'pn'),
				'default' => is_isset($db_data, 'review_text'),
				'name' => 'review_text',
				'rows' => '10',
			);
			$options['review_answer'] = array(
				'view' => 'textarea',
				'title' => __('Admin comment', 'pn'),
				'default' => is_isset($db_data, 'review_answer'),
				'name' => 'review_answer',
				'rows' => '10',
			);	
			if(is_ml()){
				$options['review_locale'] = array(
					'view' => 'select',
					'title' => __('Language', 'pn'),
					'options' => $the_lang,
					'default' => is_isset($db_data, 'review_locale'),
					'name' => 'review_locale',
				);
			}
			$options['review_status'] = array(
				'view' => 'select',
				'title' => __('Status', 'pn'),
				'options' => array('publish' => __('published review', 'pn'), 'moderation' => __('review is moderating', 'pn')),
				'default' => is_isset($db_data, 'review_status'),
				'name' => 'review_status',
			);	
			$params_form = array(
				'filter' => 'all_reviews_addform',
				'data' => $db_data,
			);
			$form->init_form($params_form, $options);	
			
		}
	}

}

if (!function_exists('def_premium_action_all_add_reviews')) {
	add_action('premium_action_all_add_reviews', 'def_premium_action_all_add_reviews');
	function def_premium_action_all_add_reviews() {
		global $wpdb;	
			
		_method('post');
			
		$form = new PremiumForm();
		$form->send_header();
			
		pn_only_caps(array('administrator', 'pn_reviews'));
			
		$data_id = intval(is_param_post('data_id')); 
		$last_data = '';
		if ($data_id > 0) {
			$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "reviews WHERE id = '$data_id'");
			if (!isset($last_data->id)) {
				$data_id = 0;
			}
		}	
			
		$array = array();
		$array['review_date'] = get_pn_time(is_param_post('review_date'), 'Y-m-d H:i:s');
		$array['user_id'] = intval(is_param_post('user_id'));
		$array['user_name'] = pn_strip_input(is_param_post('user_name'));
		$array['user_email'] = is_email(is_param_post('user_email'));
		$array['user_site'] = esc_url(pn_strip_input(is_param_post('user_site')));
		$array['user_ip'] = pn_strip_input(is_param_post('user_ip'));
		$array['user_browser'] = pn_strip_input(is_param_post('user_browser'));

		$array['review_text'] = pn_strip_input(is_param_post('review_text'));
		$array['review_answer'] = pn_strip_input(is_param_post('review_answer'));
					
		$array['review_locale'] = pn_strip_input(is_param_post('review_locale'));
		$array['review_status'] = pn_strip_input(is_param_post('review_status'));
					
		$ui = wp_get_current_user();
		$user_id = intval(is_isset($ui, 'ID'));

		$array['edit_date'] = current_time('mysql');
		$array['edit_user_id'] = $user_id;
		$array['auto_status'] = 1;				
		$array = apply_filters('all_reviews_addform_post', $array, $last_data);
					
		if ($data_id) {
			$res = apply_filters('item_reviews_edit_before', pn_ind(), $data_id, $array, $last_data);
			if ($res['ind']) {
				$result = $wpdb->update($wpdb->prefix . 'reviews', $array, array('id' => $data_id));
				do_action('item_reviews_edit', $data_id, $array, $last_data, $result);
				$res_errors = _debug_table_from_db($result, 'reviews', $array);
				_display_db_table_error($form, $res_errors);				
			} else { 
				$form->error_form(is_isset($res, 'error')); 
			}
		} else {
			$res = apply_filters('item_reviews_add_before', pn_ind(), $array);
			if ($res['ind']) {
				$array['create_date'] = current_time('mysql');
				$result = $wpdb->insert($wpdb->prefix . 'reviews', $array);
				$data_id = $wpdb->insert_id;
				if ($result) {
					do_action('item_reviews_add', $data_id, $array);
				} else {
					$res_errors = _debug_table_from_db($result, 'reviews', $array);
					_display_db_table_error($form, $res_errors);					
				}
			} else { 
				$form->error_form(is_isset($res, 'error')); 
			}		
		}			

		$url = admin_url('admin.php?page=all_add_reviews&item_id=' . $data_id . '&reply=true');
		$form->answer_form($url);
		
	}	
}