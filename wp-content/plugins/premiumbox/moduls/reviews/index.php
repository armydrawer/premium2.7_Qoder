<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Reviews[:en_US][ru_RU:]Отзывы[:ru_RU]
description: [en_US:]Reviews[:en_US][ru_RU:]Отзывы[:ru_RU]
version: 2.7.0
category: [en_US:]Settings[:en_US][ru_RU:]Настройки[:ru_RU]
cat: sett
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if (!function_exists('all_moduls_active_reviews')) {
	add_action('all_moduls_active_' . $name, 'all_moduls_active_reviews');
	add_action('pn_plugin_activate', 'all_moduls_active_reviews');
	function all_moduls_active_reviews() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . "reviews";
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
			`create_date` datetime NOT NULL,
			`edit_date` datetime NOT NULL,
			`auto_status` int(1) NOT NULL default '1',
			`edit_user_id` bigint(20) NOT NULL default '0',
			`user_id` bigint(20) NOT NULL default '0',
			`user_name` tinytext NOT NULL,
			`user_email` tinytext NOT NULL,
			`user_site` tinytext NOT NULL,
			`user_ip` varchar(50) NOT NULL,
			`user_browser` varchar(250) NOT NULL,
			`review_date` datetime NOT NULL,
			`review_hash` tinytext NOT NULL,
			`review_text` longtext NOT NULL,
			`review_answer` longtext NOT NULL,
			`review_status` varchar(150) NOT NULL default 'moderation',
			`review_locale` varchar(10) NOT NULL,
			PRIMARY KEY (`id`),
			INDEX (`create_date`),
			INDEX (`edit_date`),
			INDEX (`edit_user_id`),
			INDEX (`auto_status`),
			INDEX (`user_id`),
			INDEX (`review_date`),
			INDEX (`review_status`),
			INDEX (`review_locale`)
		) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
		$wpdb->query($sql);

		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "reviews LIKE 'user_ip'"); /* 2.2 */
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "reviews ADD `user_ip` varchar(50) NOT NULL");
		}
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "reviews LIKE 'user_browser'"); /* 2.2 */
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "reviews ADD `user_browser` varchar(250) NOT NULL");
		}
		
	}
}

if (!function_exists('list_tech_pages_reviews')) {
	add_filter('pn_tech_pages', 'list_tech_pages_reviews');
	function list_tech_pages_reviews($pages) {
		
		$pages[] = array(
			'post_name'      => 'reviews',
			'post_title'     => '[en_US:]Reviews[:en_US][ru_RU:]Отзывы[:ru_RU]',
			'post_content'   => '[reviews_page]',
			'post_template'   => 'pn-pluginpage.php',
		);			
		
		return $pages;
	}
}

if (!function_exists('placed_form_reviews')) {
	add_filter('placed_form', 'placed_form_reviews');
	function placed_form_reviews($placed) {
		
		$placed['reviewsform'] = __('Add reviews form', 'pn');
		
		return $placed;
	}
}

if (!function_exists('reviews_pn_caps')) {
	add_filter('pn_caps', 'reviews_pn_caps');
	function reviews_pn_caps($pn_caps) {
		
		$pn_caps['pn_reviews'] = __('Work with reviews', 'pn');
		
		return $pn_caps;
	} 
}

if (!function_exists('admin_menu_reviews')) {
	add_action('admin_menu', 'admin_menu_reviews');
	function admin_menu_reviews() {
		
		$plugin = get_plugin_class();
		if (current_user_can('administrator') or current_user_can('pn_reviews')) {
			add_menu_page(__('Reviews', 'pn'), __('Reviews', 'pn'), 'read', 'all_reviews', array($plugin, 'admin_temp'), $plugin->get_icon_link('reviews'));  
			add_submenu_page("all_reviews", __('Add', 'pn'), __('Add', 'pn'), 'read', "all_add_reviews", array($plugin, 'admin_temp'));	
			add_submenu_page("all_reviews", __('Settings', 'pn'), __('Settings', 'pn'), 'read', "all_settings_reviews", array($plugin, 'admin_temp'));
		}
		
	}
}

if (!function_exists('list_admin_notify_reviews')) {
	add_filter('list_admin_notify', 'list_admin_notify_reviews', 10, 2);
	function list_admin_notify_reviews($places_admin, $pl) {
		
		if ('email' == $pl) {
			$places_admin['newreview'] = __('New review', 'pn');
		}
		
		return $places_admin;
	}
}

if (!function_exists('list_user_notify_reviews')) {
	add_filter('list_user_notify', 'list_user_notify_reviews', 10, 2);
	function list_user_notify_reviews($places_admin, $pl) {
		
		if ('email' == $pl) {
			$places_admin['newreview_auto'] = __('Autoresponder (new review)', 'pn');
			$places_admin['confirmreview'] = __('Review confirmation', 'pn');
			$places_admin['answerreview'] = __('Review answer', 'pn');
		}
		
		return $places_admin;
	}
}

if (!function_exists('def_list_notify_tags_newreview')) {
	add_filter('list_notify_tags_newreview', 'def_list_notify_tags_newreview');
	function def_list_notify_tags_newreview($tags) {
		
		$tags['user'] = array(
			'title' => __('User', 'pn'),
			'start' => '[user]',
		);
		$tags['user_ip'] = array(
			'title' => __('User ip', 'pn'),
			'start' => '[user_ip]',
		);
		$tags['user_browser'] = array(
			'title' => __('User browser', 'pn'),
			'start' => '[user_browser]',
		);
		$tags['text'] = array(
			'title' => __('Text', 'pn'),
			'start' => '[text]',
		);	
		$tags['management'] = array(
			'title' => __('Manage a review', 'pn'),
			'start' => '[management]',
		);	
		$tags['status'] = array(
			'title' => __('Review status', 'pn'),
			'start' => '[status]',
		);
		
		return $tags;
	}
}

if (!function_exists('def_list_notify_tags_newreview_auto')) {
	add_filter('list_notify_tags_newreview_auto', 'def_list_notify_tags_newreview_auto');
	add_filter('list_notify_tags_answerreview', 'def_list_notify_tags_newreview_auto');
	function def_list_notify_tags_newreview_auto($tags) {
		
		$tags['user'] = array(
			'title' => __('User', 'pn'),
			'start' => '[user]',
		);
		$tags['user_ip'] = array(
			'title' => __('User ip', 'pn'),
			'start' => '[user_ip]',
		);
		$tags['user_browser'] = array(
			'title' => __('User browser', 'pn'),
			'start' => '[user_browser]',
		);
		$tags['text'] = array(
			'title' => __('Text', 'pn'),
			'start' => '[text]',
		);
		$tags['answer'] = array(
			'title' => __('Admin comment', 'pn'),
			'start' => '[answer]',
		);	
		$tags['review_link'] = array(
			'title' => __('Review link', 'pn'),
			'start' => '[review_link]',
		);	
		$tags['status'] = array(
			'title' => __('Review status', 'pn'),
			'start' => '[status]',
		);
		
		return $tags;
	}
}

if (!function_exists('def_list_notify_tags_confirmreview')) {
	add_filter('list_notify_tags_confirmreview', 'def_list_notify_tags_confirmreview');
	function def_list_notify_tags_confirmreview($tags) {
		
		$tags['link'] = array(
			'title' => __('Confirmation Link', 'pn'),
			'start' => '[link]',
		);	
		
		return $tags;
	}
}

if (!function_exists('reviews_icon_indicators')) {
	add_filter('_icon_indicators', 'reviews_icon_indicators');
	function reviews_icon_indicators($lists) {
		
		$plugin = get_plugin_class();
		$lists['reviews'] = array(
			'title' => __('Reviews about moderation', 'pn'),
			'img' => $plugin->plugin_url . 'images/reviews.png',
			'url' => admin_url('admin.php?page=all_reviews&filter=2')
		);
		
		return $lists;
	}
}

if (!function_exists('def_icon_indicator_reviews')) {
	add_filter('_icon_indicator_reviews', 'def_icon_indicator_reviews');
	function def_icon_indicator_reviews($count) {
		global $wpdb;
		
		if (current_user_can('administrator') or current_user_can('pn_reviews')) {
			$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "reviews WHERE auto_status = '1' AND review_status = 'moderation'");
		}	
		
		return $count;
	}
}

add_action('item_reviews_edit', 'def_item_reviews_edit', 10, 4);
function def_item_reviews_edit($data_id, $array, $last_data, $result) {
	
	$now_answer = $array['review_answer'];
	$last_answer = pn_strip_input(is_isset($last_data,'review_answer'));
	if (strlen($now_answer) > 0 and strlen($last_answer) < 1 and 'publish' == $array['review_status']) { 
	
		$notify_tags = array();
		$notify_tags['[user]'] = $array['user_name'];
		$notify_tags['[user_ip]'] = $array['user_ip'];
		$notify_tags['[user_browser]'] = $array['user_browser'];
		$object = (object)$array;
		$notify_tags['[review_link]'] = get_review_link($data_id, $object);
		$notify_tags['[text]'] = $array['review_text'];
		$notify_tags['[answer]'] = $array['review_answer'];
		$st_arr = array('moderation' => __('moderating', 'pn'), 'publish' => __('published', 'pn'));
		$notify_tags['[status]'] = is_isset($st_arr, $array['review_status']);	
		$notify_tags = apply_filters('notify_tags_answerreview', $notify_tags);	
							
		$user_send_data = array(
			'user_email' => $array['user_email'],
		);	
		$user_send_data = apply_filters('user_send_data', $user_send_data, 'answerreview');
		$result_mail = apply_filters('premium_send_message', 0, 'answerreview', $notify_tags, $user_send_data);
		
	}
	
}

if (!function_exists('get_review_link')) {
	function get_review_link($review_id, $data = '') {
		global $wpdb;

		$review_id = intval($review_id);

		if (!is_object($data)) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "reviews WHERE auto_status = '1' AND id = '$review_id'");
		}
		if (!isset($data->review_date)) {
			return '#';
		}

		$review_date = pn_strip_input($data->review_date);
		
		$plugin = get_plugin_class();
		
		$count = intval($plugin->get_option('reviews', 'count')); if ($count < 1) { $count=10; }
		
		$reviews_temp = rtrim($plugin->get_page('reviews'), '/');
		$reviews_arr = explode('/', $reviews_temp);
		$reviews_ind = end($reviews_arr);
		
		$deduce = intval($plugin->get_option('reviews', 'deduce'));
		$where = '';
		$reviews_page = get_site_url_or();
		if (1 == $deduce and is_ml()) {	
			$locale = pn_strip_input($data->review_locale);
			$where = " AND review_locale='$locale'";
			$site_lang = get_lang_key(get_site_lang());
			$now_lang = get_lang_key($locale);
			if ($site_lang != $now_lang) {
				$reviews_page .= $now_lang . '/';
			}			
		} 
		if ($reviews_ind) {
			$reviews_page .= $reviews_ind . '/';
		}		
		$args = array();
		$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "reviews WHERE auto_status = '1' AND review_status = 'publish' $where AND id != '$review_id' AND review_date >= '$review_date'");
		if ($cc >= $count) { 
			$pp = floor($cc / $count) + 1;
			if ($pp > 1) {
				$reviews_page .= 'page/' . $pp . '/';
			} 
		} 
		$args['review_id'] = $review_id;
		$reviews_page = add_query_args($args, $reviews_page) . '#review-' . $review_id;
		
		return $reviews_page;
	}
}

if (!function_exists('list_reviews')) {
	function list_reviews($count = 5) {
		global $wpdb;
		
		$plugin = get_plugin_class();
		$count = intval($count); if ($count < 1) { $count = 5; }
		$where = get_reviews_where();
		
		return $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "reviews WHERE auto_status = '1' AND review_status = 'publish' $where ORDER BY review_date DESC limit $count");	
	}
}

if (!function_exists('get_reviews_where')) {
	function get_reviews_where() {
		
		$plugin = get_plugin_class();
		$where = '';
		if (is_ml()) {
			$deduce = intval($plugin->get_option('reviews', 'deduce'));
			if (1 == $deduce) {	
				$locale = get_locale();
				$where = " AND review_locale='$locale'";	
			}
		}
		
		return $where;
	}
}

if (!function_exists('def_reviewsform_filelds')) {
	add_filter('reviewsform_filelds', 'def_reviewsform_filelds');
	function def_reviewsform_filelds($items) {
		
		$plugin = get_plugin_class();
		
		$ui = wp_get_current_user();

		$items['name'] = array(
			'name' => 'name',
			'title' => __('Your name', 'pn'),
			'req' => 1,
			'value' => strip_uf(is_isset($ui, 'first_name'), 'first_name'),
			'type' => 'input',
			'atts' => array('class' => 'notclear'),
		);
		$items['email'] = array(
			'name' => 'email',
			'title' => __('Your e-mail', 'pn'),
			'req' => 1,
			'value' => strip_uf(is_isset($ui, 'user_email'), 'user_email'),
			'type' => 'input',
			'atts' => array('class' => 'notclear'),
		);
		$website = intval($plugin->get_option('reviews', 'website'));
		if (1 == $website) {
			$items['user_website'] = array(
				'name' => 'user_website',
				'title' => __('Website', 'pn'),
				'req' => 0,
				'value' => strip_uf(is_isset($ui, 'user_website'), 'user_website'),
				'type' => 'input',
				'atts' => array('class' => 'notclear'),
			);	
		}
		$items['text'] = array(
			'name' => 'text',
			'title' => __('Review', 'pn'),
			'req' => 1,
			'value' => '', 
			'type' => 'text',
		);
		
		return $items;
	}
}

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'add');
$plugin->include_path(__FILE__, 'list');
$plugin->include_path(__FILE__, 'settings');
$plugin->auto_include($path . '/widget');
$plugin->auto_include($path . '/shortcode');