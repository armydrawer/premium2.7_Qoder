<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Bookmarks for admin bar[:en_US][ru_RU:]Закладки для админ бара[:ru_RU]
description: [en_US:]Bookmarks for admin bar[:en_US][ru_RU:]Закладки для админ бара[:ru_RU]
version: 2.7.0
category: [en_US:]Navigation[:en_US][ru_RU:]Навигация[:ru_RU]
cat: nav
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if (!function_exists('bd_all_moduls_active_fav')) {
	add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_fav');
	add_action('pn_plugin_activate', 'bd_all_moduls_active_fav');
	function bd_all_moduls_active_fav() {
		global $wpdb;	
		
		$table_name = $wpdb->prefix . "user_fav"; 
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
			`user_id` bigint(20) NOT NULL default '0',
			`link` varchar(250) NOT NULL default '0',
			`title` varchar(250) NOT NULL default '0',
			`menu_order` bigint(20) NOT NULL default '0',
			PRIMARY KEY (`id`),
			INDEX (`user_id`),
			INDEX (`menu_order`)
		) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
		$wpdb->query($sql);
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "user_fav LIKE 'menu_order'"); /* 1.6 */
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "user_fav ADD `menu_order` bigint(20) NOT NULL default '0'");
		}
	}
}

if (!function_exists('wp_before_admin_bar_render_fav')) {
	add_action('wp_before_admin_bar_render', 'wp_before_admin_bar_render_fav', 0);
	function wp_before_admin_bar_render_fav() {
		global $wp_admin_bar, $wpdb;
		
		if (current_user_can('read')) {
			
			$plugin = get_plugin_class();
			
			$ui = wp_get_current_user();
			$user_id = intval($ui->ID);	
		
			$wp_admin_bar->add_menu(array(
				'id'     => 'new_fav',
				'href' => '#',
				'title'  => '<div id="add_userfav" style="height: 32px; padding: 0 0px 0 25px; background: url(' . $plugin->plugin_url . 'images/fav.png) no-repeat 0 center">' . __('Add to bookmarks', 'pn') . '</div>',
				'meta' => array( 
					'title' => __('Add to bookmarks', 'pn')	
				)		
			));	
			
			$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "user_fav WHERE user_id = '$user_id' ORDER BY menu_order ASC");
			foreach ($items as $item) {
				
				$wp_admin_bar->add_menu(array(
					'id'     => 'new_fav' . $item->id,
					'href' => esc_url($item->link),
					'parent' => 'new_fav',
					'title'  => '<div style="height: 32px; padding: 0 40px 0 0px; position: relative;">' . pn_strip_input($item->title) . '<div class="remove_userfav" data-id="' . $item->id . '" style="height: 32px; width: 30px; position: absolute; top: 0; right: 0px; background: url(' . $plugin->plugin_url . 'images/removefav.png) no-repeat center center" title="' . __('Delete', 'pn') . '"></div></div>',
					'meta' => array( 
						'title' => pn_strip_input($item->title) 
					),		
				));
				
			}
			
			$wp_admin_bar->add_menu( array(
				'id'     => 'fav_sort',
				'href' => admin_url('admin.php?page=all_sort_fav'),
				'parent' => 'new_fav',
				'title'  => __('Sort bookmarks', 'pn'),
				'meta' => array( 
					'title' => __('Sort bookmarks', 'pn')
				)		
			));			
		}
	}
}

if (!function_exists('delete_user_fav')) {
	add_action('delete_user', 'delete_user_fav');
	function delete_user_fav($user_id) {
		global $wpdb;
		
		$wpdb->query("DELETE FROM " . $wpdb->prefix . "user_fav WHERE user_id = '$user_id'");
	}
}

if (!function_exists('fav_admin_footer')) {
	add_action('admin_footer', 'fav_admin_footer');
	add_action('wp_footer', 'fav_admin_footer');
	function fav_admin_footer() { 
		if (current_user_can('read')) { 
	?>	
	<script type="text/javascript">
	jQuery(function($) {
		
		$('#add_userfav:not(.active)').on('click', function() {
			$(this).addClass('active');
			var pageTitle = $('title').html();
			var new_url = encodeURIComponent(window.location.href);
			var param = 'link=' + new_url + '&title=' + encodeURIComponent(pageTitle);	
			$.ajax({
				type: "POST",
				url: "<?php echo get_pn_action('fav_add_link'); ?>", 
				dataType: 'json',
				data: param,
				error: function(res, res2, res3) {
					<?php do_action('pn_js_error_response', 'ajax'); ?>
				},			
				success: function(res)
				{		
					window.location.href = '';
				}
			});	

			return false;
		});		

		$('.remove_userfav:not(.active)').on('click', function() {
			$(this).addClass('active');
			var param = 'id=' + $(this).attr('data-id');	
			$.ajax({
				type: "POST",
				url: "<?php echo get_pn_action('fav_remove_link'); ?>",
				dataType: 'json',
				data: param,
				error: function(res, res2, res3) {
					<?php do_action('pn_js_error_response', 'ajax'); ?>
				},			
				success: function(res)
				{		
					window.location.href = '';
				}
			});
			
			return false;
		});	
		
	});	
	</script>
	<?php	
		}
	} 
}

if (!function_exists('def_siteaction_fav_add_link')) {
	add_action('premium_siteaction_fav_add_link', 'def_siteaction_fav_add_link');
	function def_siteaction_fav_add_link() {
		global $wpdb;	
		
		_method('post');
		_json_head();
		
		$log = array();
		$log['status'] = '';
		$log['status_code'] = 0;
		$log['status_text'] = '';
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		if (current_user_can('read'))  {
			$link = pn_strip_input(urldecode(is_param_post('link')));
			$title = pn_strip_input(urldecode(is_param_post('title')));
			$title = explode('‹', $title);
			$title = pn_maxf_mb($title[0], 240);
			
			$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "user_fav WHERE user_id = '$user_id' AND link = '$link'");
			if (0 == $cc) {
				$array = array();
				$array['user_id'] = $user_id;
				$array['link'] = $link;
				$array['title'] = $title;
				$wpdb->insert($wpdb->prefix . "user_fav", $array);		
			}
		}
		
		echo pn_json_encode($log);
		exit;
	}
}

if (!function_exists('def_premium_siteaction_fav_remove_link')) {
	add_action('premium_siteaction_fav_remove_link', 'def_premium_siteaction_fav_remove_link');
	function def_premium_siteaction_fav_remove_link() {
		global $wpdb;	
		
		_method('post');
		_json_head();
		
		$log = array();
		$log['status'] = '';
		$log['status_code'] = 0;
		$log['status_text'] = '';
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		if (current_user_can('read')) {
			$id = intval(is_param_post('id'));
			if ($id > 0) {
				$wpdb->query("DELETE FROM " . $wpdb->prefix . "user_fav WHERE user_id = '$user_id' AND id = '$id'");
			}
		}
		
		echo pn_json_encode($log);	
		exit;
	}
}

if (!function_exists('admin_menu_fav')) {
	add_action('admin_menu', 'admin_menu_fav');
	function admin_menu_fav() {
		
		$plugin = get_plugin_class();	
		add_submenu_page("all_none_menu", __('Sort bookmarks', 'pn'), __('Sort bookmarks', 'pn'), 'read', "all_sort_fav", array($plugin, 'admin_temp'));
		
	}
}

if (!function_exists('def_adminpage_title_all_sort_fav')) {
	add_filter('pn_adminpage_title_all_sort_fav', 'def_adminpage_title_all_sort_fav');
	function def_adminpage_title_all_sort_fav($page) {
		
		return __('Sort bookmarks', 'pn');
	}
}

if (!function_exists('def_adminpage_content_all_sort_fav')) {
	add_action('pn_adminpage_content_all_sort_fav', 'def_adminpage_content_all_sort_fav');
	function def_adminpage_content_all_sort_fav() {
		global $wpdb;

		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);

		$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "user_fav WHERE user_id = '$user_id' ORDER BY menu_order ASC");	
		$sort_list = array();
		foreach ($datas as $item) {
			$sort_list[0][] = array(
				'title' => pn_strip_input($item->title),
				'id' => $item->id,
				'number' => $item->id,
			);			
		}
		
		$form = new PremiumForm();
		$form->sort_one_screen($sort_list, pn_link('', 'post'));

	}
}

if (!function_exists('def_premium_action_all_sort_fav')) {
	add_action('premium_action_all_sort_fav', 'def_premium_action_all_sort_fav');
	function def_premium_action_all_sort_fav() {
		global $wpdb;	
	
		if (current_user_can('read')) {
			$number = is_param_post('number');
			$y = 0;
			if (is_array($number)) {	
				foreach ($number as $id) { $y++;
					$id = intval($id);
					$wpdb->query("UPDATE " . $wpdb->prefix . "user_fav SET menu_order = '$y' WHERE id = '$id'");	
				}	
			}
		}
	} 
}