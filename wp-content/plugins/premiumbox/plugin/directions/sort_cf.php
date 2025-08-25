<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_sort_cf', 'def_adminpage_title_pn_sort_cf');
	function def_adminpage_title_pn_sort_cf() {
		
		return __('Sort custom fields', 'pn');
	}

	add_action('pn_adminpage_content_pn_sort_cf', 'def_pn_admin_content_pn_sort_cf');
	function def_pn_admin_content_pn_sort_cf() {
		global $wpdb;

		$form = new PremiumForm();

		$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "direction_custom_fields WHERE auto_status = '1' ORDER BY cf_order ASC");
		
		$sort_list = array();
		foreach ($datas as $item) {
			$uniqueid = pn_strip_input($item->uniqueid);
			if ($uniqueid) { $uniqueid = ' (' . $uniqueid . ')'; }
			
			$sort_list[0][] = array(
				'title' => pn_strip_input(ctv_ml($item->tech_name)) . $uniqueid . pn_item_status($item),
				'id' => $item->id,
				'number' => $item->id,
			);		
		}
		
		$form->sort_one_screen($sort_list, pn_link('', 'post'));	

	}

}

add_action('premium_action_pn_sort_cf', 'def_premium_action_pn_sort_cf');
function def_premium_action_pn_sort_cf() {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_directions')) {
		$number = is_param_post('number');
		$y = 0;
		if (is_array($number)) {	
			foreach ($number as $id) { $y++;
				$id = intval($id);
				$wpdb->query("UPDATE " . $wpdb->prefix . "direction_custom_fields SET cf_order = '$y' WHERE id = '$id'");
			}
		}
	}
}	