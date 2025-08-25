<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_directions_temp', 'pn_admin_title_pn_directions_temp');
	function pn_admin_title_pn_directions_temp($page) {
		
		return __('Exchange direction templates', 'pn');
	} 

	add_action('pn_adminpage_content_pn_directions_temp', 'def_adminpage_content_pn_directions_temp');
	function def_adminpage_content_pn_directions_temp() {
		global $premiumbox;
		
		$form = new PremiumForm();
		
		$place = is_status_name(is_param_get('place'));
		$places = apply_filters('list_directions_temp', array()); 
		$places = (array)$places;
		
		$selects = array();
		$selects[] = array(
			'link' => admin_url("admin.php?page=pn_directions_temp"),
			'title' => '--' . __('Make a choice', 'pn') . '--',
			'default' => '',
		);		
		if (is_array($places)) { 
			foreach ($places as $key => $val) { 
				$selects[] = array(
					'link' => admin_url("admin.php?page=pn_directions_temp&place=" . $key),
					'title' => $val,
					'default' => $key,
				);		
			}
		}		
		$form->select_box($place, $selects, __('Setting up', 'pn'));	

		if (isset($places[$place])) {
			
			$options = array();
			$options['hidden_block'] = array(
				'view' => 'hidden_input',
				'name' => 'place',
				'default' => $place,
			);	
			$not = apply_filters('directions_temp_notupdate', array('description_txt', 'timeline_txt', 'window_txt', 'frozen_txt', 'status_auto'));
			if (!in_array($place, $not)) {
				$options['title'] = array(
					'view' => 'inputbig',
					'title' => __('Name for website', 'pn'),
					'default' => $premiumbox->get_option('naps_title', $place),
					'name' => 'title',
					'ml' => 1,
				);
				$options['status'] = array(
					'view' => 'inputbig',
					'title' => __('Brief status description', 'pn'),
					'default' => $premiumbox->get_option('naps_status', $place),
					'name' => 'status',
					'ml' => 1,
				);			
				$options['timer'] = array(
					'view' => 'select',
					'title' => __('Page refresh', 'pn'),
					'options' => array('0' => __('No', 'pn'), '1' => __('Auto refresh', 'pn'), '2' => __('By button', 'pn')),
					'default' => $premiumbox->get_option('naps_timer', $place),
					'name' => 'timer',
				);
				$options['timer_second'] = array(
					'view' => 'input',
					'title' => __('Automatically refresh page after (sec.)', 'pn'),
					'default' => $premiumbox->get_option('naps_timer_second', $place),
					'name' => 'timer_second',
				);				
			}					
			$options['naps_nodescr'] = array(
				'view' => 'select',
				'title' => __('How to show description from form below', 'pn'),
				'options' => array('0' => __('Show relevant description of exchange direction only', 'pn'), '1' => __('If there is no description given to exchange direction then show from form below', 'pn'), '2' => __('Always show description from form below', 'pn')),
				'default' => $premiumbox->get_option('naps_nodescr', $place),
				'name' => 'naps_nodescr',
			);		
			$options['temp'] = array(
				'view' => 'editor',
				'title' => __('Text', 'pn'),
				'default' => $premiumbox->get_option('naps_temp', $place),
				'name' => 'temp',
				'tags' => apply_filters('direction_instruction_tags', array(), $place),
				'rows' => '12',
				'formatting_tags' => 1,
				'other_tags' => 1,
				'ml' => 1,
			);	
			$params_form = array(
				'filter' => 'pn_directions_temp_option',
				'button_title' => __('Save', 'pn'),
			);
			$form->init_form($params_form, $options);
			
		} 
	}  

}

add_action('premium_action_pn_directions_temp', 'def_premium_action_pn_directions_temp');
function def_premium_action_pn_directions_temp() {
	global $wpdb, $premiumbox;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_directions'));
		
	$place = is_status_name(is_param_post('place'));
	if ($place) {
		
		$premiumbox->update_option('naps_title', $place, pn_strip_input(is_param_post_ml('title')));
		$premiumbox->update_option('naps_status', $place, pn_strip_input(is_param_post_ml('status')));
		$premiumbox->update_option('naps_timer', $place, intval(is_param_post('timer')));
		$premiumbox->update_option('naps_timer_second', $place, intval(is_param_post('timer_second')));
		$premiumbox->update_option('naps_temp', $place, pn_strip_text(is_param_post_ml('temp')));
		$premiumbox->update_option('naps_nodescr', $place, intval(is_param_post('naps_nodescr')));
		
		do_action('pn_directions_temp_option_post', $place);
		
	}
		
	$url = admin_url('admin.php?page=pn_directions_temp&place=' . $place . '&reply=true');
	$form->answer_form($url);
		
}	