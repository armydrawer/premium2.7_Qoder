<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_sort_cfc', 'def_adminpage_title_pn_sort_cfc');
	function def_adminpage_title_pn_sort_cfc() {
		
		return __('Sort custom fields', 'pn');
	}

	add_action('pn_adminpage_content_pn_sort_cfc', 'def_pn_admin_content_pn_sort_cfc');
	function def_pn_admin_content_pn_sort_cfc() {
		global $wpdb;

		$selects = array();

		$form = new PremiumForm();
		
		$places_t = array('give', 'get');
		$place = is_param_get('place');
		
		$selects[] = array(
			'link' => admin_url("admin.php?page=pn_sort_cfc"),
			'title' => '--' . __('Make a choice', 'pn') . '--',
			'default' => '',
		);	
		$selects[] = array(
			'link' => admin_url("admin.php?page=pn_sort_cfc&place=give"),
			'title' => __('Currency Send', 'pn'),
			'default' => 'give',
		);
		$selects[] = array(
			'link' => admin_url("admin.php?page=pn_sort_cfc&place=get"),
			'title' => __('Currency Receive', 'pn'),
			'default' => 'get',
		);	
		
		$form->select_box($place, $selects, __('Make a choice', 'pn'));

		if (in_array($place, $places_t)) {
			
			$orderby = '';
			if ('give' == $place) {
				$orderby = 'cf_order_give';
			} else {
				$orderby = 'cf_order_get';
			}
			$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency_custom_fields WHERE auto_status = '1' ORDER BY $orderby ASC");	
			
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
			
			$form->sort_one_screen($sort_list, pn_link('pn_sort_cfc') . '&place=' . $place);
			
		}	
	}

}

add_action('premium_action_pn_sort_cfc', 'def_premium_action_pn_sort_cfc');
function def_premium_action_pn_sort_cfc() {
	global $wpdb;
	
	_method('post');
	
	if (current_user_can('administrator') or current_user_can('pn_currency')) {
			
		$place = is_param_get('place');
		$orderby = '';
		if($place == 'give'){
			$orderby = 'cf_order_give';
		} else {
			$orderby = 'cf_order_get';
		}
			
		$number = is_param_post('number');
		$y = 0;
		if (is_array($number)) {
			foreach ($number as $id) { $y++;
				$id = intval($id);
				$wpdb->query("UPDATE " . $wpdb->prefix . "currency_custom_fields SET {$orderby} ='{$y}' WHERE id = '{$id}'");	
			}	
		}
	}
	
}