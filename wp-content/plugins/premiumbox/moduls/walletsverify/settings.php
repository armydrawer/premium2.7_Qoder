<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('pn_userwallets_settings_options', 'def_userwallets_settings_options');
function def_userwallets_settings_options($options) {
	global $premiumbox;	
		
	$options['verify_title'] = array(
		'view' => 'h3',
		'title' => __('Verification settings', 'pn'),
		'submit' => __('Save', 'pn'),
	);		
	$options['acc_status'] = array(
		'view' => 'select',
		'title' => __('Allow send request', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('usve', 'acc_status'),
		'name' => 'acc_status',
	);
	$options['disabledelete'] = array(
		'view' => 'select',
		'title' => __('Prevent user from deleting verified accounts', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('usve', 'disabledelete'),
		'name' => 'disabledelete',
	);
	$options['create_notacc'] = array(
		'view' => 'select',
		'title' => __('Allow creating orders if account not verified', 'pn'),
		'default' => $premiumbox->get_option('usve', 'create_notacc'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'name' => 'create_notacc',
	);
	$options['checkallcurr'] = array(
		'view' => 'select',
		'title' => __('Reconcile account across all currencies', 'pn'),
		'default' => $premiumbox->get_option('usve', 'checkallcurr'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'name' => 'checkallcurr',
	);
	$options['notusercheck'] = array(
		'view' => 'select',
		'title' => __('Allow the use of verified accounts without authorization', 'pn'),
		'default' => $premiumbox->get_option('usve', 'notusercheck'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'name' => 'notusercheck',
	);	
		
	return $options;
} 

add_action('pn_userwallets_settings_options_post','def_userwallets_settings_options_post');
function def_userwallets_settings_options_post() {
	global $premiumbox;	

	$options = array('acc_status', 'disabledelete', 'create_notacc', 'checkallcurr', 'notusercheck');
	foreach ($options as $key) {
		$val = intval(is_param_post($key));
		$premiumbox->update_option('usve', $key, $val);
	}
				
}	