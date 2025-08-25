<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_all_geoip_addblacklist', 'def_adminpage_title_all_geoip_addblacklist');
	function def_adminpage_title_all_geoip_addblacklist() {
		
		return __('Block IP', 'pn');
	}

	add_action('pn_adminpage_content_all_geoip_addblacklist', 'def_adminpage_content_all_geoip_addblacklist');
	function def_adminpage_content_all_geoip_addblacklist() {
		global $wpdb;

		$form = new PremiumForm();

		$title = __('Block IP', 'pn');
			
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=all_geoip_blacklist'),
			'title' => __('Back to list', 'pn')
		);	
		$form->back_menu($back_menu, '');

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => $title,
			'submit' => __('Save', 'pn'),
		);	
		$options['lists'] = array(
			'view' => 'textarea',
			'title' => __('IP addresses (at the beginning of a new line)', 'pn'),
			'default' => '',
			'name' => 'lists',
			'rows' => '15',
		);
			
		$params_form = array(
			'filter' => 'all_geoip_addblacklist_addform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
			
	}

}

add_action('premium_action_all_geoip_addblacklist', 'def_action_all_geoip_addblacklist');
function def_action_all_geoip_addblacklist() {
	global $wpdb;	

	_method('post');
			
	$form = new PremiumForm();
	$form->send_header();
			
	pn_only_caps(array('administrator', 'pn_geoip'));

	$lists = explode("\n", is_param_post('lists'));
	foreach ($lists as $list) {
		$ip = pn_strip_input($list);
		if ($ip) {
			$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "geoip_ips WHERE theip = '$ip' AND thetype = '0'");
			if (0 == $cc) {
				$array = array();
				$array['theip'] = $ip;
				$array['thetype'] = 0;
				$wpdb->insert($wpdb->prefix . 'geoip_ips', $array);
			}
		}
	}	

	$url = admin_url('admin.php?page=all_geoip_blacklist&reply=true');
	$form->answer_form($url);	
	
}	