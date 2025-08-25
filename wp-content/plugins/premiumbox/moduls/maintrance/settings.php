<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_maintrance', 'pn_adminpage_title_pn_maintrance');
	function pn_adminpage_title_pn_maintrance($page) {
		
		return __('Maintenance mode', 'pn');
	} 

	add_action('pn_adminpage_content_pn_maintrance', 'def_adminpage_content_pn_maintrance');
	function def_adminpage_content_pn_maintrance() {
		global $wpdb, $premiumbox;

		$form = new PremiumForm();

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save', 'pn'),
		);	
		$options['maintrance'] = array(
			'view' => 'select',
			'title' => __('How to switch maintenance mode', 'pn'),
			'options' => array('0' => __('Manually', 'pn'), '1' => __('Depends on operator status', 'pn')),
			'default' => $premiumbox->get_option('tech', 'maintrance'),
			'name' => 'maintrance',
		);	
		$status = intval($premiumbox->get_option('tech', 'manualy'));
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "maintrance ORDER BY id DESC");
		$selects = array();
		$selects[0] = '--' . __('No', 'pn') . '--';
		foreach ($items as $item) {
			$selects[$item->id] = pn_strip_input(ctv_ml($item->the_title));
		}	
		$options['manualy'] = array(
			'view' => 'select',
			'title' => __('Maintenance mode', 'pn'),
			'options' => $selects,
			'default' => $premiumbox->get_option('tech', 'manualy'),
			'name' => 'manualy',
		);		
		
		$params_form = array(
			'filter' => 'pn_maintrance_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
	}

}	

add_action('premium_action_pn_maintrance', 'def_premium_action_pn_maintrance');
function def_premium_action_pn_maintrance() {
	global $wpdb, $premiumbox;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_maintrance'));
		
	$options = array('maintrance', 'manualy');	
	foreach ($options as $key) {
		$val = intval(is_param_post($key));
		$premiumbox->update_option('tech', $key, $val);
	}		
				
	do_action('pn_maintrance_option_post');
		
	$url = admin_url('admin.php?page=pn_maintrance&reply=true');
	$form->answer_form($url);
	
}	