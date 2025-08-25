<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_all_add_roles', 'pn_admin_title_all_add_roles');
	function pn_admin_title_all_add_roles($pages) {
		
		$id = is_user_role_name(is_param_get('item_key'));
		if ($id) {
			return __('Edit user role', 'pn');
		} else {
			return __('Add user role', 'pn');
		}
		
	}

	add_action('pn_adminpage_content_all_add_roles', 'def_pn_admin_content_all_add_roles');
	function def_pn_admin_content_all_add_roles() {
		global $wpdb;

		$id = is_user_role_name(is_param_get('item_key'));
		$data_id = '';
		
		$prefix = $wpdb->prefix;
		
		global $wp_roles;
		if (!isset($wp_roles)) {
			$wp_roles = new WP_Roles();
		}	
		
		$data = array();
		
		if (is_array($wp_roles->role_names)) {
			foreach ($wp_roles->role_names as $role_key => $role_title) {
				if ($id == $role_key) {
					$data_id = $role_key;
					$data = array(
						'title' => $role_title,
						'key' => $role_key,
					);
				}
			}
		}	
		
		if ($data_id) {
			$title = __('Edit user role', 'pn') . ' "' . is_isset($data, 'key') . '"';
		} else {
			$title = __('Add user role', 'pn');
		}
		
		$form = new PremiumForm();
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=all_roles'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=all_add_roles'),
				'title' => __('Add new', 'pn')
			);	
		}
		
		$form->back_menu($back_menu, $data); 

		$options = array();
		$options['hidden_block'] = array(
			'view' => 'hidden_input',
			'name' => 'item_key',
			'default' => $data_id,
		);	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => $title,
			'submit' => __('Save', 'pn'),
		);	
		$options['title'] = array(
			'view' => 'inputbig',
			'title' => __('Role name', 'pn'),
			'default' => is_isset($data, 'title'),
			'name' => 'title',
		);	
		
		if (!$data_id) {
			$options['key'] = array(
				'view' => 'inputbig',
				'title' => __('System role name', 'pn'),
				'default' => is_isset($data, 'key'),
				'name' => 'key',
			);			
		}		
		
		if ('administrator' != $data_id and 'users' != $data_id and 'admin' != $data_id) {
	
			$options['cap_title'] = array(
				'view' => 'h3',
				'title' => __('Capabilities', 'pn'),
				'submit' => __('Save', 'pn'),
			);			

			$pn_caps = get_pn_capabilities();	
			$capabilities = array();
			if ($data_id) {
				$capabilities = $wp_roles->roles[$data_id]['capabilities'];
			}
			
			if (is_array($pn_caps)) {
				foreach ($pn_caps as $key => $val) {			
					$default = 0;
					if (isset($capabilities[$key])) {
						$default = 1;	
					}	
					if ('list_users' == $key) {
						$options[] = array(
							'view' => 'line',
						);							
					}				
					$options[$key] = array(
						'view' => 'checkbox',
						'label' => $val,
						'value' => '1',
						'default' => $default,
						'name' => 'cap_' . $key,
					);												
				}
			}	
			
		}
		
		$params_form = array();
		$form->init_form($params_form, $options);
	} 

}

add_action('premium_action_all_add_roles', 'def_premium_action_all_add_roles');
function def_premium_action_all_add_roles() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator'));	

	$prefix = $wpdb->prefix;
		
	$data_key = is_user_role_name(is_param_post('item_key'));
		
	$role_key = is_user_role_name(is_param_post('key'));
	$role_title = pn_strip_input(is_param_post('title'));
	if (strlen($role_title) < 1) { $role_title = $role_key; } 
				
	$list_roles = array();
		
	global $wp_roles;
	if (!isset($wp_roles)){
		$wp_roles = new WP_Roles();
	}	
		
	if (is_array($wp_roles->role_names)) {
		foreach ($wp_roles->role_names as $key => $title) {
			$list_roles[$key] = $key;
		}
	}
		
	if ($data_key and isset($list_roles[$data_key])) {
			
		$wp_user_roles = get_option($prefix . 'user_roles');
		if (isset($wp_user_roles[$data_key])) {
			$wp_user_roles[$data_key]['name'] = $role_title;
		}
		update_option($prefix . 'user_roles', $wp_user_roles);							

	} else {
		if (!$role_key) { $form->error_form(__('You did not enter a system role name', 'pn')); }
		if (isset($list_roles[$role_key]) or 'admin' == $role_key) { $form->error_form(__('Role with this name exists', 'pn')); }
						
		$result = add_role($role_key, $role_title, array());
		$data_key = $role_key;
	}	
	
	if ($data_key) {
		if ('administrator' != $data_key and 'users' != $data_key and 'admin' != $data_key) {

			$pn_caps = get_pn_capabilities();
			$capabilities = array('level_0' => '1');

			foreach ($pn_caps as $key => $val) {
				$value = intval(is_param_post('cap_' . $key));
				if ($value) {	
					$capabilities[$key] = 1;
				}
			} 
						
			$roles = get_option($prefix . 'user_roles');
			$roles[$data_key]['capabilities'] = $capabilities;
			$roles = serialize($roles);
			$wpdb->update($prefix . 'options' , array('option_value' => $roles), array('option_name' => $prefix . 'user_roles'));
		
		}		
	}

	$url = admin_url('admin.php?page=all_add_roles&item_key=' . $data_key . '&reply=true');
	$form->answer_form($url);
}	