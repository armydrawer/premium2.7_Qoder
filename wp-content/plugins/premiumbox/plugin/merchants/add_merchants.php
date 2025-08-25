<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_merchants_add', 'def_adminpage_title_pn_merchants_add');
	function def_adminpage_title_pn_merchants_add() {
		global $db_data, $wpdb;

		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$db_data = '';
		
		if ($item_id) {
			$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exts WHERE id = '$item_id' AND ext_type = 'merchants'");
			if (isset($db_data->id)) {
				$data_id = $db_data->id;
			}	
		}		
		
		if ($data_id) {
			return __('Edit merchant', 'pn');
		} else {
			return __('Add merchant', 'pn');
		}
	}

	add_action('pn_adminpage_content_pn_merchants_add', 'def_adminpage_content_pn_merchants_add');
	function def_adminpage_content_pn_merchants_add() {
		global $db_data, $wpdb, $premiumbox;

		$form = new PremiumForm();

		$data_id = intval(is_isset($db_data, 'id'));
		if ($data_id) {
			$title = __('Edit merchant', 'pn') . ' "' . is_isset($db_data, 'ext_title') . '"';
		} else {
			$title = __('Add merchant', 'pn');
		}

		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_merchants'),
			'title' => __('Back to list', 'pn')
		);
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_merchants_add'),
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
		$scripts_list = list_extended($premiumbox, 'merchants');
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
			'options' => array('1' => __('active merchant', 'pn'), '0' => __('inactive merchant', 'pn')),
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
			$options = apply_filters('ext_merchants_data', $options, $now_script, $db_data->ext_key);
			
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
						'default' => sprintf(__('Enter your security password to save the settings. Instructions for setting the security password are available in the <a href="%s">link</a>.', 'pn'), 'https://premium.gitbook.io/main/kod-bezopasnosti-dlya-podtverzhdeniya-platezhey'),
					);
				}
				
				$params_form = array(
					'form_link' => pn_link('pn_merchants_data', 'post'),
					'button_title' => __('Save', 'pn'),
				);
				$form->init_form($params_form, $options);			
			}
			
			$data = pn_json_decode($db_data->ext_options);
			if (!is_array($data)) { $data = array(); }	
			
			$options = merchants_setting_list($data, $db_data, 1);

			if (count($options) > 2) {
				do_action('before_merchants_admin', $now_script, $data, $db_data->ext_key);

				$params_form = array(
					'form_link' => pn_link('pn_merchants_settings', 'post'),
					'button_title' => __('Save', 'pn'),
				);
				$form->init_form($params_form, $options);
			}
	
		}
	} 
	
}	

add_action('premium_action_pn_merchants_settings', 'def_premium_action_pn_merchants_settings');
function def_premium_action_pn_merchants_settings() {	
	global $wpdb;

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_merchants'));
		
	$data_id = intval(is_param_post('item_id'));
	if ($data_id > 0) {
		$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exts WHERE id = '$data_id' AND ext_type = 'merchants'");
		if (isset($db_data->id)) {
			$options = merchants_setting_list('', $db_data, 0);
			$options_data = $form->strip_options('', 'post', $options);
			$merch_data = array();	
			$auto_create_hash = apply_filters('auto_create_hash', 0);
			$in = array('resulturl', 'cronhash');
			foreach ($options_data as $key => $val) {
				if (in_array($key,$in) and strlen($val) < 1 and 1 == $auto_create_hash) {	
					$val = mb_strtolower(get_random_password(16, true, true));
				}
				$merch_data[$key] = $val;
			}
			$merch_data = apply_filters('_merchants_ext_options_array', $merch_data, $db_data);
			$array = array();
			$array['ext_options'] = pn_json_encode($merch_data);
			$wpdb->update($wpdb->prefix . 'exts', $array, array('id' => $data_id));
		}
	}

	do_action('_merchants_options_post');		

	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply'=>'true'), $back_url);
	$form->answer_form($back_url);
}

add_action('premium_action_pn_merchants_data', 'def_premium_action_pn_merchants_data');
function def_premium_action_pn_merchants_data() {	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_merchants'));
		
	$error = save_pass_protected(stripslashes(is_param_post('pass')));
	if ($error) {
		$form->error_form(__('Error! You have entered an incorrect security password', 'pn'));
	}	
		
	$item_key = is_extension_name(is_param_post('item_key'));
	$script = is_extension_name(is_param_post('script'));
			
	$up = apply_filters('ext_merchants_data_post', 0, $script, $item_key);
	if (1 != $up) {
		$form->error_form(__('Settings cannot be written', 'pn'));
	}

	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
	$form->answer_form($back_url);
	
}

add_action('premium_action_pn_merchants_add', 'def_premium_action_pn_merchants_add');
function def_premium_action_pn_merchants_add() {
	global $wpdb, $premiumbox;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_merchants'));	

	$data_id = intval(is_param_post('data_id'));

	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exts WHERE id = '$data_id' AND ext_type = 'merchants'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}
		
	$script = is_extension_name(is_param_post('script'));
	/* if (!$script) { $form->error_form(__('Module not chosen','pn')); } */
		
	$status = intval(is_param_post('ext_status'));
		
	if (isset($last_data->id)) {
		$ext_key = $last_data->ext_key;
	} else {
		$ext_key = _ext_set_key($script, 'merchants', $data_id);
	}

	$title = pn_strip_input(is_param_post('ext_title'));
	if (!$title) { 
		$scripts = list_extended($premiumbox, 'merchants');
		$scr_data = is_isset($scripts, $script);
		$title = ctv_ml(is_isset($scr_data, 'title')) . ' (' . $ext_key . ')'; 
	}

	$array = array();
	$array['ext_type'] = 'merchants';
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
		include_extanded($premiumbox, 'merchants', $script);
		if (1 == $status) {
			do_action('ext_merchants_active_' . $script, $array['ext_key']);
			do_action('ext_merchants_active', $script, $array['ext_key']);	
		} else {
			do_action('ext_merchants_deactive_' . $script, $array['ext_key']);
			do_action('ext_merchants_deactive', $script, $array['ext_key']);	
		}	
	}

	$url = admin_url('admin.php?page=pn_merchants_add&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
	
}	 