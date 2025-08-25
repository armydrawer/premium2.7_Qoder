<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('pn_removeusers_admin_bar_links')) {
	
	add_action('init', 'hide_standart_adminbar', 10);
	function hide_standart_adminbar() {
		if (!current_user_can('read')) {
			add_filter('show_admin_bar', '__return_false');
		}
	}	
	
	add_action('wp_before_admin_bar_render', 'pn_removeusers_admin_bar_links');
	function pn_removeusers_admin_bar_links() {
		global $wp_admin_bar;

		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);
		if ($user_id) {
			
			if (current_user_can('administrator') or current_user_can('add_users')) {
				$wp_admin_bar->remove_menu('new-user');
				$wp_admin_bar->add_menu(array(
					'id'     => 'new-user',
					'href' => admin_url('admin.php?page=all_add_user'),
					'parent' => 'new-content',
					'title'  => __('User', 'pn'),
					'meta' => array()		
				));		
			}

			$edit_user_link = admin_url('admin.php?page=all_edit_user&item_id=' . $user_id);
			
			$wp_admin_bar->remove_menu('my-account');
			$wp_admin_bar->add_menu(array(
				'id'     => 'my-account',
				'href' => $edit_user_link,
				'parent' => 'top-secondary',
				'title'  => is_user($ui->user_login),
				'meta' => array()		
			));	
			
			$wp_admin_bar->remove_menu('user-info');
			$wp_admin_bar->add_menu(array(
				'id'     => 'user-info',
				'href' => $edit_user_link,
				'parent' => 'user-actions',
				'title'  => is_user($ui->user_login),
				'meta' => array('tabindex' => '-1')		
			));		
			
			$wp_admin_bar->remove_menu('edit-profile');
			$wp_admin_bar->add_menu(array(
				'id'     => 'edit-profile',
				'href' => $edit_user_link,
				'parent' => 'user-actions',
				'title'  => __('Edit profile', 'pn'),
				'meta' => array()		
			));

			$wp_admin_bar->remove_menu('logout');
			$wp_admin_bar->add_menu(array(
				'id'     => 'logout',
				'href' => get_pn_action('logout', 'get') . '&return_url=' . urlencode(get_admin_panel_url()),
				'parent' => 'user-actions',
				'title'  => __('Exit', 'pn'),
				'meta' => array()		
			));	
			
		}
	}

}

if (!function_exists('pn_close_admin_init_user')) {
	add_action('admin_init', 'pn_close_admin_init_user');
	function pn_close_admin_init_user() {
		
		$request = ltrim(get_request_query(), '/');
		$delete_pages = array('wp-admin/user-edit.php', 'wp-admin/users.php', 'wp-admin/user-new.php', 'wp-admin/profile.php');
		if (in_array($request, $delete_pages)) {
			pn_display_mess(__('Page does not exist', 'pn'));
		}	
		
	}
}

if (!function_exists('pn_get_edit_user_link')) {
	add_filter('get_edit_user_link', 'pn_get_edit_user_link', 10, 2);
	function pn_get_edit_user_link($link, $user_id) {
		
		return admin_url('admin.php?page=all_edit_user&item_id=' . $user_id);
	}
}

if (!function_exists('pn_close_admin_menu_user')) {
	add_action('admin_menu', 'pn_close_admin_menu_user', 1000);
	function pn_close_admin_menu_user() {
		global $menu;
		
		$restricted = array();
		$restricted[] = 'users.php';
		$restricted[] = 'profile.php';
		
		if (is_array($menu)) {
			foreach ($menu as $menu_key => $menu_data) {
				$menu_data_key = is_isset($menu_data, 2);
				if (in_array($menu_data_key,$restricted)) {
					unset($menu[$menu_key]);
				}			
			}
		}	
	}
}