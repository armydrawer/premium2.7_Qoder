<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_sort_dgroups', 'def_adminpage_title_pn_sort_dgroups');
	function def_adminpage_title_pn_sort_dgroups() {
			
		return __('Sort', 'pn');
	}

	add_action('pn_adminpage_content_pn_sort_dgroups', 'def_pn_adminpage_content_pn_sort_dgroups');
	function def_pn_adminpage_content_pn_sort_dgroups() {
		global $wpdb;

		$form = new PremiumForm();

		$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "dgroups ORDER BY site_order ASC");
		$sort_list = array();
		foreach ($datas as $item) {
			$sort_list[0][] = array(
				'title' => pn_strip_input($item->title),
				'id' => $item->id,
				'number' => $item->id,
			);		
		}
			
		$form->sort_one_screen($sort_list, pn_link('', 'post'));
	}

}

add_action('premium_action_pn_sort_dgroups', 'def_premium_action_pn_sort_dgroups');
function def_premium_action_pn_sort_dgroups() {
	global $wpdb;	
		
	if (current_user_can('read')) {
		$number = is_param_post('number');
		$y = 0;
		if (is_array($number)) {	
			foreach ($number as $id) { $y++;
				$id = intval($id);
				$wpdb->query("UPDATE " . $wpdb->prefix . "dgroups SET site_order = '$y' WHERE id = '$id'");	
			}	
		}
	}
}