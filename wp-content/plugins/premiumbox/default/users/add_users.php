<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('pn_adminpage_title_pn_add_user')) {

	add_filter('pn_adminpage_title_all_add_user', 'def_adminpage_title_all_add_user');
	function def_adminpage_title_all_add_user() {
		
		return __('Add user', 'pn');
	}

	add_action('pn_adminpage_content_all_add_user', 'def_adminpage_content_all_add_user');
	function def_adminpage_content_all_add_user() {
		global $wpdb;

		if (current_user_can('administrator') or current_user_can('add_users')) {

			$form = new PremiumForm();

			$title = __('Add user', 'pn');

			$back_menu = array();
			$back_menu['back'] = array(
				'link' => admin_url('admin.php?page=all_users'),
				'title' => __('Back to list', 'pn')
			);
			$form->back_menu($back_menu, '');	
			
			$options = array();	
			$options['top_title'] = array(
				'view' => 'h3',
				'title' => $title,
				'submit' => __('Save', 'pn'),
			);	
			$options['user_login'] = array(
				'view' => 'inputbig',
				'title' => __('Login', 'pn'),
				'default' => '',
				'name' => 'user_login',
				'work' => 'input',
			);	
			$options['user_email'] = array(
				'view' => 'inputbig',
				'title' => __('E-mail', 'pn'),
				'default' => '',
				'name' => 'user_email',
				'work' => 'input',
			);
			$options['user_pass'] = array(
				'view' => 'input_password',
				'title' => __('Password', 'pn'),
				'default' => '',
				'name' => 'user_pass',
				'work' => 'input',
			);		
			
			$roles = array();
			global $wp_roles;
			if (!isset($wp_roles)) {
				$wp_roles = new WP_Roles();
			}
			if (isset($wp_roles)) { 
				foreach ($wp_roles->role_names as $role => $name) {
					$roles[$role] = $name;	
				}
			}	
			
			$options['user_role'] = array(
				'view' => 'select',
				'title' => __('Role', 'pn'),
				'options' => $roles,
				'default' => get_option('default_role'),
				'name' => 'user_role',
			);
			
			$options['mail'] = array(
				'view' => 'checkbox',
				'title' => '',
				'label' => __('Send login and password to user e-mail', 'pn'),
				'value' => '1',
				'default' => '1',
				'name' => 'mail',
			);				

			$params_form = array(
				'filter' => 'all_user_addform',
				'button_title' => __('Save', 'pn'),
			);
			$form->init_form($params_form, $options);		
		
		} else {
			echo 'Error!';
		}
	}

	add_action('premium_action_all_add_user', 'def_premium_action_all_add_user');
	function def_premium_action_all_add_user() {
		global $wpdb;	

		_method('post');
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator', 'add_users'));
		
		$user_login = is_user(is_param_post('user_login'));
		$user_email = is_email(is_param_post('user_email'));
		$user_pass = is_password(is_param_post('user_pass'));
		$user_role = is_user_role_name(is_param_post('user_role'));
		$mail = intval(is_param_post('mail'));
		
		if (!$user_login) {
			$form->error_form(__('Error! You have entered an incorrect username. The username must consist of digits or latin letters and contain from 3 up to 30 characters.', 'pn'));	
		}	
		if (!$user_email) {
			$form->error_form(__('Error! You have entered an incorrect e-mail', 'pn'));	
		}	
		if (!$user_pass) {
			$form->error_form(__('Error! Password is incorrect or does not match with the previously entered password', 'pn'));	
		}
		if ($user_login and username_exists($user_login)) {
			$form->error_form(__('Error! This login is already in use', 'pn'));	
		}
		if ($user_email and email_exists($user_email)) {
			$form->error_form(__('Error! This e-mail is already in use', 'pn'));
		}	
		
		$roles = array();
		global $wp_roles;
		if (!isset($wp_roles)) {
			$wp_roles = new WP_Roles();
		}
		if (isset($wp_roles)) { 
			foreach ($wp_roles->role_names as $role => $name) {
				$roles[$role] = $name;	
			}
		}	
		
		if (!isset($roles[$user_role])) {
			$form->error_form(__('Error! User role does not exists', 'pn'));
		}	
		
		$user_id = wp_insert_user(array('user_login' => $user_login, 'user_email' => $user_email, 'user_pass' => $user_pass, 'role' => $user_role));
		if ($user_id) {
									
			do_action('pn_user_register', $user_id);
						
			if (1 == $mail) {
				
				$notify_tags = array();
				$notify_tags['[login]'] = $user_login;
				$notify_tags['[pass]'] = $user_pass;
				$notify_tags['[email]'] = $user_email;
				$notify_tags = apply_filters('notify_tags_registerform', $notify_tags, $user_id);		

				$user_send_data = array(
					'user_email' => $user_email,
				);	
				$result_mail = apply_filters('premium_send_message', 0, 'registerform', $notify_tags, $user_send_data);	
		
			}
		}

		$url = admin_url('admin.php?page=all_users&reply=true');
		$form->answer_form($url);
		
	}	
}