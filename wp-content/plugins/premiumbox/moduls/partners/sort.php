<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	if (!function_exists('def_adminpage_title_all_sort_partners')) {
		add_filter('pn_adminpage_title_all_sort_partners', 'def_adminpage_title_all_sort_partners');
		function def_adminpage_title_all_sort_partners() {
			
			return __('Sort partners', 'pn');
		}
	}

	if (!function_exists('def_pn_adminpage_content_all_sort_partners')) {
		add_action('pn_adminpage_content_all_sort_partners', 'def_pn_adminpage_content_all_sort_partners');
		function def_pn_adminpage_content_all_sort_partners() {
			global $wpdb;

			$form = new PremiumForm();

			$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "partners WHERE auto_status = '1' ORDER BY site_order ASC");
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

}

if (!function_exists('def_premium_action_all_sort_partners')) {
	add_action('premium_action_all_sort_partners', 'def_premium_action_all_sort_partners');
	function def_premium_action_all_sort_partners() {
		global $wpdb;	
		
		if (current_user_can('read')) {
			$number = is_param_post('number');
			$y = 0;
			if (is_array($number)) {	
				foreach ($number as $id) { $y++;
					$id = intval($id);
					$wpdb->query("UPDATE " . $wpdb->prefix . "partners SET site_order = '$y' WHERE id = '$id'");	
				}	
			}
		}
	}
}