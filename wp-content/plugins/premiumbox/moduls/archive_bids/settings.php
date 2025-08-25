<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_settings_archive_bids', 'def_adminpage_title_pn_settings_archive_bids');
	function def_adminpage_title_pn_settings_archive_bids($page) {
		
		return __('Archiving settings', 'pn');
	}

	add_action('pn_adminpage_content_pn_settings_archive_bids', 'def_adminpage_content_pn_settings_archive_bids');
	function def_adminpage_content_pn_settings_archive_bids() {
		global $premiumbox;

		$form = new PremiumForm();

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Archiving settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);
		$options['txt'] = array(
			'view' => 'select',
			'title' => __('Delete files of orders during archiving', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('archivebids', 'txt'),
			'name' => 'txt',
			'work' => 'int',
		);
		$options['loadhistory'] = array(
			'view' => 'select',
			'title' => __('Allow users to download their exchange history', 'pn'),
			'options' => array('0' => __('No', 'pn'),'1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('archivebids', 'loadhistory'),
			'name' => 'loadhistory',
			'work' => 'int',
		);
		$options['limit_archive'] = array(
			'view' => 'input',
			'title' => __('Number of orders for archiving', 'pn'),
			'default' => $premiumbox->get_option('archivebids', 'limit_archive'),
			'name' => 'limit_archive',
			'work' => 'int',
		);	
		
		$params_form = array(
			'filter' => 'pn_settings_archive_bids_options',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
	}  

}

add_action('premium_action_pn_settings_archive_bids', 'def_premium_action_pn_settings_archive_bids');
function def_premium_action_pn_settings_archive_bids() {
	global $premiumbox;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_archive'));
		
	$txt = intval(is_param_post('txt'));
	$premiumbox->update_option('archivebids', 'txt', $txt);	

	$loadhistory = intval(is_param_post('loadhistory'));
	$premiumbox->update_option('archivebids', 'loadhistory', $loadhistory);

	$limit_archive = intval(is_param_post('limit_archive'));
	$premiumbox->update_option('archivebids', 'limit_archive', $limit_archive);	

	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
				
	$form->answer_form($back_url);
	
} 	