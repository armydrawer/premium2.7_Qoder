<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) { 

	add_action('admin_menu', 'admin_menu_admin');
	function admin_menu_admin() {
		
		$plugin = get_plugin_class();
		add_submenu_page("options-general.php", __('Admin Panel', 'pn'), __('Admin Panel', 'pn'), 'administrator', "all_admin", array($plugin, 'admin_temp'));
		
	}
	
	add_filter('pn_adminpage_title_all_admin', 'def_adminpage_title_all_admin');
	function def_adminpage_title_all_admin() {
		
		return __('Admin Panel', 'pn');
	}
 
	add_action('pn_adminpage_content_all_admin', 'def_pn_adminpage_content_all_admin');
	function def_pn_adminpage_content_all_admin() {	
		
		$form = new PremiumForm();
		$params_form = array(
			'filter' => 'all_adminpanel_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form);	
					  
	}  
	
}

add_filter('all_adminpanel_option', 'def_all_adminpanel_option', 1);
function def_all_adminpanel_option($options) {
	
	$plugin = get_plugin_class();
			
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Widgets on the main page', 'pn'),
		'submit' => __('Save', 'pn'),
	);
		$options['w0'] = array(
			'view' => 'select',
			'title' => __('Hide Welcome Panel', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin', 'w0'),
			'name' => 'w0',
			'work' => 'int',
		);		
		$options['w1'] = array(
			'view' => 'select',
			'title' => __('Hide At a Glance', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin', 'w1'),
			'name' => 'w1',
			'work' => 'int',
		);
		$options['w2'] = array(
			'view' => 'select',
			'title' => __('Hide Activity', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin', 'w2'),
			'name' => 'w2',
			'work' => 'int',
		);
		$options['w3'] = array(
			'view' => 'select',
			'title' => __('Hide Quick Drafts', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin', 'w3'),
			'name' => 'w3',
			'work' => 'int',
		);
		$options['w4'] = array(
			'view' => 'select',
			'title' => __('Hide WordPress News', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin', 'w4'),
			'name' => 'w4',
			'work' => 'int',
		);
		$options['w6'] = array(
			'view' => 'select',
			'title' => __('Hide Incoming Refs', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin', 'w6'),
			'name' => 'w6',
			'work' => 'int',
		);
		$options['w7'] = array(
			'view' => 'select',
			'title' => __('Hide Plugins', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin', 'w7'),
			'name' => 'w7',
			'work' => 'int',
		);
		$options['w8'] = array(
			'view' => 'select',
			'title' => __('Hide Recent Drafts', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin', 'w8'),
			'name' => 'w8',
			'work' => 'int',
		);
	$options['center_title'] = array(
		'view' => 'h3',
		'title' => __('Menu Sections', 'pn'),
		'submit' => __('Save', 'pn'),
	);
		$options['ws0'] = array(
			'view' => 'select',
			'title' => sprintf(__('Hide section "%s"', 'pn'), __('Posts', 'pn')),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin', 'ws0'),
			'name' => 'ws0',
			'work' => 'int',
		);
		$options['ws1'] = array(
			'view' => 'select',
			'title' => sprintf(__('Hide section "%s"', 'pn'), __('Comments', 'pn')),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin','ws1'),
			'name' => 'ws1',
			'work' => 'int',
		);			
		$options['ws2'] = array(
			'view' => 'select',
			'title' => sprintf(__('Hide section "%s"', 'pn'), __('Media', 'pn')),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin', 'ws2'),
			'name' => 'ws2',
			'work' => 'int',
		);
		$options['ws3'] = array(
			'view' => 'select',
			'title' => sprintf(__('Hide section "%s"', 'pn'), __('Tools', 'pn')),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin', 'ws3'),
			'name' => 'ws3',
			'work' => 'int',
		);
		$options['ws4'] = array(
			'view' => 'select',
			'title' => sprintf(__('Hide section "%s"', 'pn'), __('Settings (media)', 'pn')),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin', 'ws4'),
			'name' => 'ws4',
			'work' => 'int',
		);
		$options['ws5'] = array(
			'view' => 'select',
			'title' => sprintf(__('Hide section "%s"', 'pn'), __('Settings (privacy)', 'pn')),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin', 'ws5'),
			'name' => 'ws5',
			'work' => 'int',
		);
		$options['ws6'] = array(
			'view' => 'select',
			'title' => sprintf(__('Hide section "%s"', 'pn'), __('Settings (writing)', 'pn')),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin', 'ws6'),
			'name' => 'ws6',
			'work' => 'int',
		);			
	$options['other_title'] = array(
		'view' => 'h3',
		'title' => __('Other', 'pn'),
		'submit' => __('Save', 'pn'),
	);	
		$options['wm0'] = array(
			'view' => 'select',
			'title' => __('Disable RSS feed', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('admin', 'wm0'),
			'name' => 'wm0',
			'work' => 'int',
		);		
			
	return $options;
}
	
add_action('premium_action_all_admin', 'def_premium_action_all_admin');
function def_premium_action_all_admin() {
	
	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();		
		
	pn_only_caps(array('administrator'));
			
	$plugin = get_plugin_class();
			
	$data = $form->strip_options('all_adminpanel_option', 'post');	
			
	foreach ($data as $key => $val) {
		$plugin->update_option('admin', $key, $val);
	}		
					
	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
				
	$form->answer_form($back_url);
}	