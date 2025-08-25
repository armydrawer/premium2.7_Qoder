<?php
if (!defined('ABSPATH')) { exit(); }
 
if (is_admin()) {

	add_action('admin_menu', 'admin_menu_exchange_filters', 100);
	function admin_menu_exchange_filters() {
		global $premiumbox;
		
		add_submenu_page("pn_config", __('Exchange filters', 'pn'), __('Exchange filters', 'pn'), 'administrator', "pn_exchange_filters", array($premiumbox, 'admin_temp'));
		
	}

	add_filter('pn_adminpage_title_pn_exchange_filters', 'def_adminpage_title_pn_exchange_filters');
	function def_adminpage_title_pn_exchange_filters($page) {
		
		return __('Exchange filters', 'pn');
	} 

	add_action('pn_adminpage_content_pn_exchange_filters', 'def_adminpage_content_pn_exchange_filters');
	function def_adminpage_content_pn_exchange_filters() {
		global $wpdb, $premiumbox;

		$form = new PremiumForm();

		$options = array();
		$options['warning'] = array(
			'view' => 'warning',
			'title' => __('More info', 'pn'),
			'default' => sprintf(__('You can set the filter properties for the following objects. For example, if the user will see the exchange direction on the home page if his country is forbidden for this exchange direction. Will the user see the exchange direction on the home page if he is not registered user and etc.', 'pn'), ''),
		);
		$changes = array(
			'0' => __('Show but disabled', 'pn'),
			'1' => __('Hide', 'pn'),
		);		
		$lists = apply_filters('set_exchange_filters', array());
		$lists = (array)$lists;	
		
		$get_lists = array();
		foreach ($lists as $list) {
			$title = trim(is_isset($list, 'title'));
			$name = trim(is_isset($list, 'name'));
			$none = trim(is_isset($list, 'none'));
			if ($name) {
				$get_lists[] = array(
					'title' => $title,
					'name' => $name,
					'none' => $none,
				);
			}
		}	

		$cats = apply_filters('set_exchange_cat_filters', array());
		
		foreach ($cats as $k => $v) {
			$options[] = array(
				'view' => 'h3',
				'title' => $v,
				'submit' => __('Save', 'pn'),
			);	
			if (isset($get_lists) and is_array($get_lists)) {
				foreach ($get_lists as $vn) {
					$none_arr = explode(',', $vn['none']);
					$none_arr = array_map('trim', $none_arr);
					if (!in_array($k, $none_arr)) {
						$options[] = array(
							'view' => 'select',
							'title' => $vn['title'],
							'options' => $changes,
							'default' => $premiumbox->get_option('exf_' . $k . '_' . $vn['name']),
							'name' => 'exf_' . $k . '_' . $vn['name'],
						);	
					}
				}
			}	
		}	
		
		$params_form = array(
			'filter' => 'pn_exchange_filters_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);					
	} 
	
}

add_action('premium_action_pn_exchange_filters', 'def_premium_action_pn_exchange_filters');
function def_premium_action_pn_exchange_filters() {
	global $wpdb, $premiumbox;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator'));

	$lists = apply_filters('set_exchange_filters', array());
	$lists = (array)$lists;
						
	$cats = apply_filters('set_exchange_cat_filters', array());

	foreach ($cats as $k => $v) {
		foreach ($lists as $list) {
			$title = trim(is_isset($list, 'title'));
			$name = trim(is_isset($list, 'name'));
			$none = trim(is_isset($list, 'none'));
			if ($name) {
				$none_arr = explode(',', $none);
				$none_arr = array_map('trim', $none_arr);
				if (!in_array($k, $none_arr)) {
					
					$val = intval(is_param_post('exf_' . $k . '_' . $name));
					$premiumbox->update_option('exf_' . $k . '_' . $name, '', $val);
				
				}
			}
		}	
	}
		
	do_action('pn_exchange_filters_option_post');

	$url = admin_url('admin.php?page=pn_exchange_filters&reply=true');
	$form->answer_form($url);	
	
}	