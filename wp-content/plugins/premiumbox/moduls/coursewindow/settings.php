<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_coursewindow', 'pn_admin_title_pn_coursewindow');
	function pn_admin_title_pn_coursewindow($page) {
		
		return __('Notification window', 'pn');
	}

	add_action('pn_adminpage_content_pn_coursewindow', 'pn_admin_content_pn_coursewindow');
	function pn_admin_content_pn_coursewindow() {
		global $wpdb, $premiumbox;

		$form = new PremiumForm();

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Notification window', 'pn'),
			'submit' => __('Save', 'pn'),
		);	
		$options['place'] = array(
			'view' => 'select',
			'title' => __('Notification window location on website', 'pn'),
			'options' => array('0' => __('Left', 'pn'), '1' => __('Right', 'pn')),
			'default' => $premiumbox->get_option('courselogs', 'place'),
			'name' => 'place',
			'work' => 'int',
		);	
		$options['out_course'] = array(
			'view' => 'select',
			'title' => __('Notify of changes in exchange rates', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('courselogs', 'out_course'),
			'name' => 'out_course',
			'work' => 'int',
		);
		$options['out_bids'] = array(
			'view' => 'select',
			'title' => __('Notify of new exchanges', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('courselogs', 'out_bids'),
			'name' => 'out_bids',
			'work' => 'int',
		);	
		$options['count'] = array(
			'view' => 'input',
			'title' => __('Max number of notifications', 'pn'),
			'default' => $premiumbox->get_option('courselogs', 'count'),
			'name' => 'count',
			'work' => 'int',
		);	
		$params_form = array(
			'filter' => 'pn_coursewindow_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	 
	}  

}

add_action('premium_action_pn_coursewindow', 'def_premium_action_pn_coursewindow');
function def_premium_action_pn_coursewindow() {
	global $wpdb, $premiumbox;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator'));
		
	$place = intval(is_param_post('place'));
	$out_course = intval(is_param_post('out_course'));
	$out_bids = intval(is_param_post('out_bids'));
	$count = intval(is_param_post('count'));

	$premiumbox->update_option('courselogs', 'place', $place);
	$premiumbox->update_option('courselogs', 'out_course', $out_course);
	$premiumbox->update_option('courselogs', 'out_bids', $out_bids);
	$premiumbox->update_option('courselogs', 'count', $count);	

	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
				
	$form->answer_form($back_url);
	
}  	