<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	if (!function_exists('def_adminpage_title_pn_sort_indxs')) {
		add_filter('pn_adminpage_title_pn_sort_indxs', 'def_adminpage_title_pn_sort_indxs');
		function def_adminpage_title_pn_sort_indxs() {
			
			return __('Sort coefficient', 'pn');
		}
	}

	if (!function_exists('def_pn_adminpage_content_pn_sort_indxs')) {
		add_action('pn_adminpage_content_pn_sort_indxs', 'def_pn_adminpage_content_pn_sort_indxs');
		function def_pn_adminpage_content_pn_sort_indxs() {
			global $wpdb;

			$form = new PremiumForm();

			$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "indxs ORDER BY site_order ASC");
			$sort_list = array();
			foreach ($datas as $item) {
				$sort_list[0][] = array(
					'title' => pn_strip_input($item->indx_name),
					'id' => $item->id,
					'number' => $item->id,
				);		
			}
			
			$form->sort_one_screen($sort_list, pn_link('', 'post'));
		}
	}

}

if (!function_exists('def_premium_action_pn_sort_indxs')) {
	add_action('premium_action_pn_sort_indxs', 'def_premium_action_pn_sort_indxs');
	function def_premium_action_pn_sort_indxs() {
		global $wpdb;	
		
		if (current_user_can('read')) {
			$number = is_param_post('number');
			$y = 0;
			if (is_array($number)) {	
				foreach ($number as $id) { $y++;
					$id = intval($id);
					$wpdb->query("UPDATE " . $wpdb->prefix . "indxs SET site_order = '$y' WHERE id = '$id'");	
				}	
			}
		}
	}
}