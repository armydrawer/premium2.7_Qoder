<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('all_user_editform', 'api_all_user_editform', 101, 2);
function api_all_user_editform($options, $data) {
	
	$user_id = $data->ID;
	if (current_user_can('administrator') or current_user_can('pn_api')) { 	
		$options['work_api'] = array(
			'view' => 'select',
			'title' => __('Work with REST API', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => intval(is_isset($data, 'work_api')),
			'name' => 'work_api',
		);			
	}
	
	return $options;
}

add_action('all_user_editform_post', 'api_all_user_editform_post');
function api_all_user_editform_post($new_user_data) {
	
	if (current_user_can('administrator') or current_user_can('pn_api')) { 
		$new_user_data['work_api'] = intval(is_param_post('work_api'));
	}
	
	return $new_user_data;
}