<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_operator', 'def_adminpage_title_pn_operator');
	function def_adminpage_title_pn_operator($page) {
		
		return __('Work status', 'pn');
	} 

	add_action('pn_adminpage_content_pn_operator', 'def_adminpage_content_pn_operator');
	function def_adminpage_content_pn_operator() {
		global $wpdb, $premiumbox;

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save', 'pn'),
		);

		$options['operator_type'] = array(
			'view' => 'select',
			'title' => __('Principle of determining work status', 'pn'),
			'options' => array('0' => __('Manually', 'pn'), '1' => __('Automatically', 'pn'), '2' => __('Schedule', 'pn')),
			'default' => $premiumbox->get_option('operator_type'),
			'name' => 'operator_type',
		);	
		
		$options['line1'] = array(
			'view' => 'line',
		);	

		$options['show_button'] = array(
			'view' => 'select',
			'title' => __('Operator button', 'pn'),
			'options' => array('0' => __('Hide button', 'pn'), '1' => __('Right', 'pn'), '2' => __('Left', 'pn')),
			'default' => $premiumbox->get_option('statuswork', 'show_button'),
			'name' => 'show_button',
		);
		
		$status_operator = _operator_status_list();

		if (is_array($status_operator)) {
			foreach ($status_operator as $key => $title) {
				$options['text' . $key] = array(
					'view' => 'inputbig',
					'title' => __('Text', 'pn') . ' "' . $title . '"',
					'default' => $premiumbox->get_option('statuswork', 'text' . $key),
					'name' => 'text' . $key,
					'ml' => 1,
				);
				$options['link' . $key] = array(
					'view' => 'inputbig',
					'title' => __('Link', 'pn') . ' "' . $title . '"',
					'default' => $premiumbox->get_option('statuswork', 'link' . $key),
					'name' => 'link' . $key,
					'ml' => 1,
				);			
			}
		}	
		
		$options['line2'] = array(
			'view' => 'line',
		);		
		
		$options['center_title'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save', 'pn'),
		);	
		$options['clear1'] = array(
			'view' => 'clear_table',
		);	
		$options['second_title'] = array(
			'view' => 'h3',
			'title' => __('Manual mode settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);
		$options['operator'] = array(
			'view' => 'select',
			'title' => __('Status', 'pn'),
			'options' => $status_operator,
			'default' => $premiumbox->get_option('operator'),
			'name' => 'operator',
		);		
		
		$form = new PremiumForm();
		$params_form = array(
			'filter' => 'pn_operator_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
		
	}  

}

add_action('premium_action_pn_operator', 'def_premium_action_pn_operator');
function def_premium_action_pn_operator() {
	global $premiumbox;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator'));
		
	$status_operator = _operator_status_list();			
		
	$options = array('operator', 'operator_type');					
	foreach ($options as $key) {
		$premiumbox->update_option($key, '', intval(is_param_post($key)));
	}
			
	$options = array('show_button');					
	foreach ($options as $key) {
		$premiumbox->update_option('statuswork', $key, intval(is_param_post($key)));				
	}			
				
	if (is_array($status_operator)) {
		foreach ($status_operator as $key => $title) {
			$val = pn_strip_input(is_param_post_ml('text' . $key));
			$premiumbox->update_option('statuswork', 'text' . $key, $val);
					
			$val = pn_strip_input(is_param_post_ml('link' . $key));
			$premiumbox->update_option('statuswork', 'link' . $key, $val);				
		}
	}		
		
	$url = admin_url('admin.php?page=pn_operator&reply=true');
	$form->answer_form($url);
	
} 