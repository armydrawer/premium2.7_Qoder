<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('def_adminpage_title_all_sort_advantages') and is_admin()) {
	add_filter('pn_adminpage_title_all_sort_advantages', 'def_adminpage_title_all_sort_advantages');
	function def_adminpage_title_all_sort_advantages() {
		
		return __('Sort advantages', 'pn');
	}
}

if (!function_exists('def_adminpage_content_all_sort_advantages') and is_admin()) {
	add_action('pn_adminpage_content_all_sort_advantages', 'def_adminpage_content_all_sort_advantages');
	function def_adminpage_content_all_sort_advantages() {
		global $wpdb;

		$form = new PremiumForm();

		$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "advantages WHERE auto_status = '1' ORDER BY site_order ASC");
		$sort_list = array();
		foreach ($datas as $item) {
			$sort_list[0][] = array(
				'title' => pn_strip_input(ctv_ml($item->title)),
				'id' => $item->id,
				'number' => $item->id,
			);		
		}
		
		$form->sort_one_screen($sort_list, pn_link('', 'post'));
	}
}

if (!function_exists('def_premium_action_all_sort_advantages')) {
	add_action('premium_action_all_sort_advantages', 'def_premium_action_all_sort_advantages');
	function def_premium_action_all_sort_advantages() {
		global $wpdb;	
		
		if (current_user_can('read')) {
			$number = is_param_post('number');
			$y = 0;
			if (is_array($number)) {	
				foreach ($number as $id) { $y++;
					$id = intval($id);
					$wpdb->query("UPDATE " . $wpdb->prefix . "advantages SET site_order = '$y' WHERE id = '$id'");	
				}	
			}
		}
		
	}
}