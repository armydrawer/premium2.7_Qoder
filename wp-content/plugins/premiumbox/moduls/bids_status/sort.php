<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_sort_bidstatus', 'pn_admin_title_pn_sort_bidstatus');
	function pn_admin_title_pn_sort_bidstatus() {
		
		return __('Sort', 'pn');
	}

	add_action('pn_adminpage_content_pn_sort_bidstatus', 'def_adminpage_content_pn_sort_bidstatus');
	function def_adminpage_content_pn_sort_bidstatus() {
		global $wpdb;

		$form = new PremiumForm();

		$items = list_bid_status();
		$sort_list = array();
		foreach ($items as $item_key => $item_title) {
			$sort_list[0][] = array(
				'title' => $item_title,
				'id' => $item_key,
				'number' => $item_key,
			);		
		}
		$form->sort_one_screen($sort_list, pn_link('', 'post'));
	}
}

add_action('premium_action_pn_sort_bidstatus', 'def_premium_action_pn_sort_bidstatus');
function def_premium_action_pn_sort_bidstatus() {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_bidstatus')) {
		$number = is_param_post('number');
		$y = 0;
		if (is_array($number)) {
			$st = list_bid_status();
			$new_status = get_option('bidstatus_sortable'); 
			if (!is_array($new_status)) { $new_status = array(); }
			
			foreach($number as $id) { $y++;
				$id = trim($id);
				if (isset($st[$id])) {
					$new_status[$id] = $y;
				}
			}
			update_option('bidstatus_sortable', $new_status);
			
		}
	}
}