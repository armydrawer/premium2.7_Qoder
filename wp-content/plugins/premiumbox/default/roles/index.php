<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('admin_menu_roles')) {
	add_action('admin_menu', 'admin_menu_roles');
	function admin_menu_roles() {
		
		$plugin = get_plugin_class();
		add_menu_page(__('User roles', 'pn'), __('User roles', 'pn'), 'administrator', "all_roles", array($plugin, 'admin_temp'), $plugin->get_icon_link('roles'), 70);
		add_submenu_page("all_roles", __('Add user role', 'pn'), __('Add user role', 'pn'), 'administrator', "all_add_roles", array($plugin, 'admin_temp'));
		
	}
	
	function is_user_role_name($name) {
		
		if (preg_match("/^[a-zA-z0-9]{3,30}$/", $name)) {
			$name = strtolower($name);
		} else {
			$name = '';
		}
		
		return $name;		
	}
	
	function get_pn_capabilities() {
		
		$wp_caps = array(
			'list_users' => __('User list', 'pn'), 
			'add_users' => __('Add users', 'pn'),
			//'create_users' => __('Add users', 'pn'),
			'edit_users' => __('Edit users', 'pn'),
			//'remove_users' => __('Remove users', 'pn'),
			'delete_users' => __('Delete users', 'pn'),
			'promote_users' => __('Change role of users', 'pn'),
			'disableip_users' => __('Permit changing allowed IP addresses in user profile', 'pn'),
			'edit_dashboard' => __('Edit dashboard', 'pn'),
			'switch_themes' => __('Switch themes', 'pn'),
			'edit_theme_options'=> __('Edit theme options', 'pn'),
			'delete_themes' => __('Delete themes', 'pn'),
			'edit_themes' => __('Edit themes', 'pn'),
			'install_themes' => __('Install themes', 'pn'),
			'update_themes' => __('Update themes', 'pn'),
			'activate_plugins'=> __('Activate plugins', 'pn'),
			'edit_plugins' => __('Edit plugins', 'pn'),
			'install_plugins' => __('Install plugins', 'pn'),
			'update_plugins' => __('Update plugins', 'pn'),
			'delete_plugins' => __('Delete plugins', 'pn'),
			'update_core' => __('Update core', 'pn'),
			'export' => __('Export Wordpress', 'pn'),
			'import' => __('Import Wordpress', 'pn'),
			'upload_files' => __('Upload files', 'pn'),
			'edit_files' => __('Edit files', 'pn'),
			'unfiltered_upload' => __('Unfiltered upload', 'pn'),
			'unfiltered_html' => __('Unfiltered HTML', 'pn'),
			'manage_options' => __('Change general settings', 'pn'),
			'edit_posts' => __('Edit posts and images', 'pn'),
			'edit_others_posts' => __('Edit others posts and images', 'pn'),		
			'edit_published_posts' => __('Edit published posts', 'pn'),
			'publish_posts' => __('Publish posts', 'pn'),
			'delete_posts' => __('Delete posts', 'pn'),
			'delete_others_posts' => __('Delete other posts', 'pn'),
			'delete_published_posts' => __('Delete published posts', 'pn'),
			'delete_private_posts' => __('Delete private posts', 'pn'),
			'edit_private_posts' => __('Edit private posts', 'pn'),
			'read_private_posts' => __('Read private posts', 'pn'),
			'manage_categories' => __('Manage categories', 'pn'),
			'moderate_comments' => __('Moderate comments', 'pn'),
			'edit_pages' => __('Edit pages', 'pn'),
			'edit_others_pages' => __('Edit other pages', 'pn'),
			'edit_published_pages' => __('Edit published pages', 'pn'),
			'edit_private_pages' => __('Edit private pages', 'pn'),
			'read_private_pages' => __('Read private pages', 'pn'),
			'publish_pages' => __('Publish pages', 'pn'),
			'delete_pages' => __('Delete pages', 'pn'),
			'delete_others_pages' => __('Delete other pages', 'pn'),
			'delete_published_pages' => __('Delete published pages', 'pn'),
			'delete_private_pages' => __('Delete private pages', 'pn'),
		);	
		$pn_caps = array(	
			'read' => __('Access to admin panel', 'pn'),
			'pn_change_notify' => __('Work with notify templates', 'pn'),
			'pn_test_cron' => __('Test cron tasks', 'pn'),
		);
		$pn_caps = apply_filters('pn_caps', $pn_caps);
		$pn_caps = (array)$pn_caps;
		$now_caps = array_merge($pn_caps, $wp_caps);
		
		return $now_caps;
	}	
	
	$plugin = get_plugin_class();
	$plugin->include_path(__FILE__, 'list_roles');
	$plugin->include_path(__FILE__, 'add_roles');
} 