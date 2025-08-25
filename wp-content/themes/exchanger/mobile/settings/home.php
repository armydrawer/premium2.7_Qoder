<?php 
if (!defined('ABSPATH')) { exit(); }

add_action('admin_menu', 'pn_adminpage_theme_mobile_home');
function pn_adminpage_theme_mobile_home() {
	
	$plugin = get_plugin_class();

	add_submenu_page("themes.php", __('Homepage (mobile version)', 'pntheme'), __('Homepage (mobile version)', 'pntheme'), 'administrator', "pn_theme_mobile_home", array($plugin, 'admin_temp'));
	
}

add_filter('pn_adminpage_title_pn_theme_mobile_home', 'pn_adminpage_title_pn_theme_mobile_home');
function pn_adminpage_title_pn_theme_mobile_home($page) {
	
	return __('Homepage (mobile version)', 'pntheme');
} 

add_filter('pn_theme_mobile_home_option', 'def_pn_theme_mobile_home_option', 1);
function def_pn_theme_mobile_home_option($options) {
	global $wpdb;

	$change = get_option('mho_change');
	
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Information', 'pntheme'),
		'submit' => __('Save', 'pntheme'),
	);
	$options['wtitle'] = array(
		'view' => 'inputbig',
		'title' => __('Title', 'pntheme'),
		'default' => is_isset($change, 'wtitle'),
		'name' => 'wtitle',
		'work' => 'input',
		'ml' => 1,
	);
	$options['wtext'] = array(
		'view' => 'editor',
		'title' => __('Text', 'pntheme'),
		'default' => is_isset($change, 'wtext'),
		'name' => 'wtext',
		'work' => 'text',
		'rows' => '20',
		'media' => 1,
		'formatting_tags' => 1,
		'ml' => 1,
	);		
	$options['center_title'] = array(
		'view' => 'h3',
		'title' => __('Welcome message', 'pntheme'),
		'submit' => __('Save', 'pntheme'),
	);	
	$options['ititle'] = array(
		'view' => 'inputbig',
		'title' => __('Title', 'pntheme'),
		'default' => is_isset($change, 'ititle'),
		'name' => 'ititle',
		'work' => 'input',
		'ml' => 1,
	);	
	$options['itext'] = array(
		'view' => 'editor',
		'title' => __('Text', 'pntheme'),
		'default' => is_isset($change, 'itext'),
		'name' => 'itext',
		'work' => 'text',
		'rows' => '20',
		'media' => 1,
		'formatting_tags' => 1,
		'ml' => 1,
	);		
	$options['line1'] = array(
		'view' => 'line',
	);
	$options['blocknews'] = array(
		'view' => 'select',
		'title' => __('News column', 'pntheme'),
		'options' => array('0' => __('hide', 'pntheme'), '1' => __('show', 'pntheme')),
		'default' => is_isset($change,'blocknews'),
		'name' => 'blocknews',
		'work' => 'int',
	);
	
	$categories = get_categories('hide_empty=0');
	$array = array();
	$array[0] = '--' . __('All', 'pntheme') . '--';
	if (is_array($categories)) {
		foreach ($categories as $cat) {
			$array[$cat->cat_ID] = ctv_ml($cat->name);
		}
	}	
	$options['catnews'] = array(
		'view' => 'select',
		'title' => __('Category', 'pntheme'),
		'options' => $array,
		'default' => is_isset($change, 'catnews'),
		'name' => 'catnews',
		'work' => 'int',
	);	
	$options['line2'] = array(
		'view' => 'line',
	);	
	$options['blocreviews'] = array(
		'view' => 'select',
		'title' => __('Reviews column', 'pntheme'),
		'options' => array('0' => __('hide', 'pntheme'), '1' => __('show', 'pntheme')),
		'default' => is_isset($change, 'blocreviews'),
		'name' => 'blocreviews',
		'work' => 'int',
	);
	$options['line3'] = array(
		'view' => 'line',
	);	
	$options['lastobmen'] = array(
		'view' => 'select',
		'title' => __('Last exchange', 'pntheme'),
		'options' => array('0' => __('hide', 'pntheme'), '1' => __('show', 'pntheme')),
		'default' => is_isset($change, 'lastobmen'),
		'name' => 'lastobmen',
		'work' => 'int',
	);
	$options['partners'] = array(
		'view' => 'select',
		'title' => __('Partners', 'pntheme'),
		'options' => array('0' => __('hide', 'pntheme'), '1' => __('show', 'pntheme')),
		'default' => is_isset($change, 'partners'),
		'name' => 'partners',
		'work' => 'int',
	);	
	$options['line4'] = array(
		'view' => 'line',
	);	
	$options['advantages'] = array(
		'view' => 'select',
		'title' => __('Advantages', 'pntheme'),
		'options' => array('0' => __('hide', 'pntheme'), '1' => __('show', 'pntheme')),
		'default' => is_isset($change, 'advantages'),
		'name' => 'advantages',
		'work' => 'int',
	);	
	$options['line5'] = array(
		'view' => 'line',
	);	
	$options['reserve'] = array(
		'view' => 'select',
		'title' => __('Reserve', 'pntheme'),
		'options' => array('0' => __('hide', 'pntheme'), '1' => __('show', 'pntheme')),
		'default' => is_isset($change,'reserve'),
		'name' => 'reserve',
		'work' => 'int',
	);	
	$options['hidecurr'] = array(
		'view' => 'user_func',
		'name' => 'hidecurr',
		'func_data' => $change,
		'func' => 'pn_theme_home_hidecurr',
		'work' => 'input_array',
	);
	
	return $options;
}

add_action('pn_adminpage_content_pn_theme_mobile_home', 'def_pn_adminpage_content_pn_theme_mobile_home');
function def_pn_adminpage_content_pn_theme_mobile_home() {
	
	$form = new PremiumForm();
	$params_form = array(
		'filter' => 'pn_theme_mobile_home_option',
	);
	$form->init_form($params_form);		
		
} 

add_action('premium_action_pn_theme_mobile_home', 'def_premium_action_pn_theme_mobile_home');
function def_premium_action_pn_theme_mobile_home() {
	global $wpdb;

	_method('post');
	pn_only_caps(array('administrator'));

	$form = new PremiumForm();
	$form->send_header();
	
	$data = $form->strip_options('pn_theme_mobile_home_option', 'post');

	$change = get_option('mho_change');
	if (!is_array($change)) { $change = array(); } 
				
	$change['blocknews'] = $data['blocknews'];
	$change['catnews'] = $data['catnews'];	
			
	$change['lastobmen'] = $data['lastobmen'];
	
	$change['blocreviews'] = $data['blocreviews'];
	$change['partners'] = $data['partners'];
	$change['advantages'] = $data['advantages'];
	
	$change['wtitle'] = $data['wtitle'];
	$change['ititle'] = $data['ititle'];
			
	$change['wtext'] = $data['wtext'];
	$change['itext'] = $data['itext'];
	
	$change['reserve'] = $data['reserve'];
	$change['hidecurr'] = implode(',', $data['hidecurr']);
					
	update_option('mho_change', $change);	
	
	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
	
	$form->answer_form($back_url);
	
}