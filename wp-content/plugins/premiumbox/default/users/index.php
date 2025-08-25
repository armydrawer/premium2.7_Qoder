<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('admin_menu_all_users') and is_admin()) {
	add_action('admin_menu', 'admin_menu_all_users');
	function admin_menu_all_users() {
		
		$plugin = get_plugin_class();	
		
		if (current_user_cans('administrator, list_users')) {
			add_menu_page(__('Users', 'pn'), __('Users', 'pn'), 'read', "all_users", array($plugin, 'admin_temp'), $plugin->get_icon_link('users'), 69);
		}
		if (current_user_cans('administrator, add_users')) {
			add_submenu_page("all_users", __('Add user', 'pn'), __('Add user', 'pn'), 'read', "all_add_user", array($plugin, 'admin_temp'));
		}
		add_submenu_page("all_none_menu", __('Edit user', 'pn'), __('Edit user', 'pn'), 'read', "all_edit_user", array($plugin, 'admin_temp'));
		add_submenu_page("all_users", __('Authorization log', 'pn'), __('Authorization log', 'pn'), 'administrator', "all_alogs", array($plugin, 'admin_temp'));	
		add_submenu_page("all_users", __('User profile settings', 'pn'), __('User profile settings', 'pn'), 'administrator', "all_uf_settings", array($plugin, 'admin_temp'));

	}
}

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'remove_wp_users');
$plugin->include_path(__FILE__, 'filters'); 
$plugin->include_path(__FILE__, 'list_users');
$plugin->include_path(__FILE__, 'add_users');
$plugin->include_path(__FILE__, 'edit_users'); 
$plugin->include_path(__FILE__, 'enableip');
$plugin->include_path(__FILE__, 'twofactorauth');
$plugin->include_path(__FILE__, 'list_auth');
$plugin->include_path(__FILE__, 'uf_settings'); 