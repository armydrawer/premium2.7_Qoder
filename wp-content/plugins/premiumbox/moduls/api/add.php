<?php 
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_all_add_api', 'def_adminpage_title_all_add_api');
	function def_adminpage_title_all_add_api() {
		global $db_data, $wpdb;
		
		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$db_data = '';
		
		if ($item_id) {
			$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "api WHERE id = '$item_id'");
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

	add_action('pn_adminpage_content_all_add_api', 'def_pn_adminpage_content_all_add_api');
	function def_pn_adminpage_content_all_add_api() {
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
			'link' => admin_url('admin.php?page=all_api'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=all_add_api'),
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
		
		$options['user_id'] = array(
			'view' => 'input',
			'title' => __('User id', 'pn'),
			'default' => is_isset($db_data, 'user_id'),
			'name' => 'user_id',
		);
		
		if ($data_id) {
			$options['api_login'] = array(
				'view' => 'input',
				'title' => __('API login', 'pn'),
				'default' => is_isset($db_data, 'api_login'),
				'name' => 'api_login',
				'atts' => array('disabled' => 'disabled', 'class' => 'long_input'),
			);			
			$options['api_key'] = array(
				'view' => 'input',
				'title' => __('API key', 'pn'),
				'default' => is_isset($db_data, 'api_key'),
				'name' => 'api_key',
				'atts' => array('disabled' => 'disabled', 'class' => 'long_input'),
			);	
		}
		$options['enable_ip'] = array(
			'view' => 'textarea',
			'title' => __('Enabled ip (in new line)', 'pn'),
			'default' => is_isset($db_data, 'enable_ip'),
			'name' => 'enable_ip',
			'rows' => '10',
		);
		$options['list_method'] = array(
			'view' => 'user_func',
			'name' => 'list_method',
			'func_data' => $db_data,
			'func' => '_api_add_list_method',
		);		
		
		$params_form = array(
			'filter' => 'all_api_addform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
				
	}
	
	function _api_add_list_method($db_data) { 
	
		$form = new PremiumForm();
		$lists = apply_filters('api_all_methods', array());
		$en = pn_json_decode(is_isset($db_data, 'api_actions'));
		if (!is_array($en)) { $en = array(); }
	?>
		<div class="premium_standart_line"> 
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Methods available', 'pn'); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
					if (is_array($lists)) {
						foreach ($lists as $k => $title) {
							$checked = 0;
							if (isset($en[$k])) {
								$checked = 1;
							}
							$scroll_lists[] = array(
								'title' => $title,
								'checked' => $checked,
								'value' => $k,
							);
						}	
					}	
					echo get_check_list($scroll_lists, 'api_actions[]', '', '', 1);
					?>			
				<div class="premium_clear"></div>
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>				
	<?php
	}	
	
}

if (!function_exists('def_premium_action_all_add_api')) {
	add_action('premium_action_all_add_api', 'def_premium_action_all_add_api');
	function def_premium_action_all_add_api() {
		global $wpdb;

		_method('post');
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator', 'pn_api'));
			
		$data_id = intval(is_param_post('data_id'));
		
		$last_data = '';
		if ($data_id > 0) {
			$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "api WHERE id = '$data_id'");
			if (!isset($last_data->id)) {
				$data_id = 0;
			}
		}		
		
		$array = array();
		$user_id = intval(is_param_post('user_id'));
		$ui = get_userdata($user_id);
		if (isset($ui->ID)) {
			$array['user_id'] = $user_id;
			$array['user_login'] = is_isset($ui, 'user_login');
		} else {
			$array['user_id'] = 0;
			$array['user_login'] = '';
		}

		$api_actions = is_param_post('api_actions');
		$lists = apply_filters('api_all_methods', array());
		$en = array(); 
		if (is_array($lists) and is_array($api_actions)) {
			foreach ($lists as $k => $title) {
				if (in_array($k, $api_actions)) {
					$en[$k] = 1;
				}
			}
		}		
		$array['api_actions'] = pn_json_encode($en);
		$array['enable_ip'] = pn_strip_input(is_param_post('enable_ip'));		
		
		$array = apply_filters('all_api_addform_post',$array, $last_data);
				
		if ($data_id) {
			$res = apply_filters('item_api_edit_before', pn_ind(), $data_id, $array, $last_data);
			if ($res['ind']) {
				$result = $wpdb->update($wpdb->prefix . 'api', $array, array('id' => $data_id));
				do_action('item_api_edit', $data_id, $array, $last_data, $result);
				$res_errors = _debug_table_from_db($result, 'api', $array);
				_display_db_table_error($form, $res_errors);
			} else { $form->error_form(is_isset($res, 'error')); }
		} else {
			$res = apply_filters('item_api_add_before', pn_ind(), $array);
			if ($res['ind']) {
				$array['create_date'] = current_time('mysql');
				$array['api_login'] = get_random_password(32, true, true);
				$array['api_key'] = unique_api_key();
				$result = $wpdb->insert($wpdb->prefix . 'api', $array);
				$data_id = $wpdb->insert_id;
				if ($result) {
					do_action('item_api_add', $data_id, $array);
				} else {
					$res_errors = _debug_table_from_db($result, 'api', $array);
					_display_db_table_error($form, $res_errors);					
				}
			} else { $form->error_form(is_isset($res, 'error')); }		
		}

		$url = admin_url('admin.php?page=all_add_api&item_id=' . $data_id . '&reply=true');
		$form->answer_form($url);
	}
}	