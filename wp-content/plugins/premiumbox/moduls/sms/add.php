<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_all_sms_add', 'def_adminpage_title_all_sms_add');
	function def_adminpage_title_all_sms_add() {
		global $db_data, $wpdb;

		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$db_data = '';
		
		if ($item_id) {
			$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exts WHERE id = '$item_id' AND ext_type = 'sms'");
			if (isset($db_data->id)) {
				$data_id = $db_data->id;
			}	
		}		
		
		if ($data_id) {
			return __('Edit SMS gate', 'pn');
		} else {
			return __('Add SMS gate', 'pn');
		}
	}

	add_action('pn_adminpage_content_all_sms_add', 'def_adminpage_content_all_sms_add');
	function def_adminpage_content_all_sms_add() {
	global $db_data, $wpdb, $premiumbox;

		$form = new PremiumForm();

		$data_id = intval(is_isset($db_data, 'id'));
		if ($data_id) {
			$title = __('Edit SMS gate', 'pn') . ' "' . is_isset($db_data, 'ext_title') . '"';
		} else {
			$title = __('Add SMS gate', 'pn');
		}

		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=all_sms_list'),
			'title' => __('Back to list', 'pn')
		);
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=all_sms_add'),
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
		
		$options['ext_title'] = array(
			'view' => 'inputbig',
			'title' => __('Title', 'pn'),
			'default' => is_isset($db_data, 'ext_title'),
			'name' => 'ext_title',
			'atts' => array('autocomplete' => 'off'),
		);
		
		$scripts = array();
		$scripts[0] = '--' . __('Select', 'pn') . '--';
		$scripts_list = list_extended($premiumbox, 'sms');
		foreach ($scripts_list as $sc_key => $sc_val) {
			$place = is_isset($sc_val, 'place');
			$vers = is_isset($sc_val, 'vers');
			$theme = '';
			if ('theme' == $place) {
				$theme = ' (' . __('Theme', 'pn') . ')';
			}
			$scripts[$sc_key] = ctv_ml(is_isset($sc_val, 'title')) . ' (' . $sc_key . ' v.' . $vers . ')' . $theme;
		}
		asort($scripts);
		
		$now_script = trim(is_isset($db_data, 'ext_plugin'));
		
		$options['script'] = array(
			'view' => 'select_search',
			'title' => __('Module', 'pn'),
			'options' => $scripts,
			'default' => $now_script,
			'name' => 'script',
		);		
		$options['ext_status'] = array(
			'view' => 'select',
			'title' => __('Status', 'pn'),
			'options' => array('1' => __('active SMS gate', 'pn'), '0' => __('inactive SMS gate', 'pn')),
			'default' => is_isset($db_data, 'ext_status'),
			'name' => 'ext_status',
		);		
		$params_form = array(
			'data' => $db_data,
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
		
		if ($now_script) {
			
			$options = array();
			$options['top_title'] = array(
				'view' => 'h3',
				'title' => __('Module settings', 'pn'),
				'submit' => __('Save', 'pn'),
			);
			$options['hidden_block'] = array(
				'view' => 'hidden_input',
				'name' => 'item_key',
				'default' => $db_data->ext_key,
			);
			$options['hidden_block_script'] = array(
				'view' => 'hidden_input',
				'name' => 'script',
				'default' => $now_script,
			);			
			$options = apply_filters('ext_sms_data', $options, $now_script, $db_data->ext_key);
			
			if (count($options) > 3) {
				
				if (is_has_admin_password()) {
					
					$placeholder = '';
					if (is_pass_protected()) {
						$placeholder = __('Enter security password', 'pn');
					}
					$options['pass_line'] = array(
						'view' => 'line',
					);				
					$options['pass'] = array(
						'view' => 'inputbig',
						'title' => '<span class="bred">' . __('Security password', 'pn') . '</span>',
						'default' => '',
						'name' => 'pass',
						'atts' => array('autocomplete' => 'off', 'placeholder' => $placeholder),
						'work' => 'none',
					);
					$options['warning_pass'] = array(
						'view' => 'warning',
						'title' => __('More info', 'pn'),
						'default' => sprintf(__('Enter your security password to save the settings. Instructions for setting the security password are available in the <a href="%s">link</a>.', 'pn'), 'https://premiumexchanger.com/' . get_lang_key(get_admin_lang()) . '/wiki/kod-bezopasnosti-dlya-podtverzhdeniya-platezhey/'),
					);
					
				}
				
				$params_form = array(
					'form_link' => pn_link('all_sms_data', 'post'),
					'button_title' => __('Save', 'pn'),
				);
				$form->init_form($params_form, $options);
				
			}
			
			$data = pn_json_decode($db_data->ext_options);
			if (!is_array($data)) { $data = array(); }	
			
			$options = sms_setting_list($data, $db_data, 1);

			if (count($options) > 2) {
				do_action('before_sms_admin', $now_script, $data, $db_data->ext_key);

				$params_form = array(
					'form_link' => pn_link('pn_sms_settings', 'post'),
					'button_title' => __('Save', 'pn'),
				);
				$form->init_form($params_form, $options);
			}
	
		}
	} 
	
}			

add_action('premium_action_pn_sms_settings', 'def_premium_action_pn_sms_settings');
function def_premium_action_pn_sms_settings() {	
	global $wpdb;

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_change_notify'));
		
	$data_id = intval(is_param_post('item_id'));
	if ($data_id > 0) {
		$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exts WHERE id = '$data_id' AND ext_type = 'sms'");
		if (isset($db_data->id)) {
			$options = sms_setting_list('', $db_data, 0);
			$options_data = $form->strip_options('', 'post', $options);
			$merch_data = array();	
			foreach ($options_data as $key => $val) {
				$merch_data[$key] = $val;
			}
			$merch_data = apply_filters('_sms_ext_options_array', $merch_data, $db_data);
			$array = array();
			$array['ext_options'] = pn_json_encode($merch_data);
			$wpdb->update($wpdb->prefix . 'exts', $array, array('id' => $data_id));
		}
	}

	do_action('_sms_options_post');		

	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
	$form->answer_form($back_url);
}

add_action('premium_action_all_sms_data', 'def_premium_action_all_sms_data');
function def_premium_action_all_sms_data() {	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_change_notify'));
		
	$error = save_pass_protected(stripslashes(is_param_post('pass')));
	if ($error) {
		$form->error_form(__('Error! You have entered an incorrect security password', 'pn'));
	}	
		
	$item_key = is_extension_name(is_param_post('item_key'));
	$script = is_extension_name(is_param_post('script'));
			
	$up = apply_filters('ext_sms_data_post', 0, $script, $item_key);
	if (1 != $up) {
		$form->error_form(__('Settings cannot be written', 'pn'));
	}

	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
	$form->answer_form($back_url);
	
}

add_action('premium_action_all_sms_add', 'def_premium_action_all_sms_add');
function def_premium_action_all_sms_add() {
	global $wpdb, $premiumbox;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_change_notify'));	

	$data_id = intval(is_param_post('data_id'));

	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exts WHERE id = '$data_id' AND ext_type = 'sms'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}
		
	$script = is_extension_name(is_param_post('script'));
	/* if (!$script) { $form->error_form(__('Module not chosen', 'pn')); } */
		
	$status = intval(is_param_post('ext_status'));
		
	if (isset($last_data->id)) {
		$ext_key = $last_data->ext_key;
	} else {
		$ext_key = _ext_set_key($script, 'sms', $data_id);
	}		

	$title = pn_strip_input(is_param_post('ext_title'));
	if (strlen($title) < 1) { 
		$scripts = list_extended($premiumbox, 'sms');
		$scr_data = is_isset($scripts, $script);
		$title = ctv_ml(is_isset($scr_data, 'title')) . ' (' . $ext_key . ')'; 
	}

	$array = array();
	$array['ext_type'] = 'sms';
	$array['ext_title'] = $title;
	$array['ext_plugin'] = $script;
	$array['ext_key'] = $ext_key;
	$array['ext_status'] = $status;

	if ($data_id) {
		$wpdb->update($wpdb->prefix . 'exts', $array, array('id' => $data_id));
	} else {
		$wpdb->insert($wpdb->prefix . 'exts', $array);
		$data_id = $wpdb->insert_id;		
	}
		
	if ($data_id) {
		include_extanded($premiumbox, 'sms', $script);
		if (1 == $status) {
			do_action('ext_sms_active_' . $script, $array['ext_key']);
			do_action('ext_sms_active', $script, $array['ext_key']);	
		} else {
			do_action('ext_sms_deactive_' . $script, $array['ext_key']);
			do_action('ext_sms_deactive', $script, $array['ext_key']);	
		}	
	}

	$url = admin_url('admin.php?page=all_sms_add&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
	
}	 