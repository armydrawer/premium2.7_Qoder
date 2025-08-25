<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('pn_pp_adminform', 'ppapikey_pn_pp_adminform');
function ppapikey_pn_pp_adminform($options) {
	global $premiumbox;	
	
	$options['workppapikey'] = array(
		'view' => 'select',
		'title' => __('Access to REST API', 'pn') . ' (ppapi)',
		'options' => array('0' => __('Yes', 'pn'), '1' => __('No', 'pn'), '2' =>__('Only selected users', 'pn')),
		'default' => $premiumbox->get_option('partners', 'workppapikey'),
		'name' => 'workppapikey',
	);	
	
	return $options;
}

add_action('pn_pp_adminform_post', 'ppapikey_pn_pp_adminform_post');
function ppapikey_pn_pp_adminform_post() {
	global $premiumbox;
	
	$premiumbox->update_option('partners', 'workppapikey', intval(is_param_post('workppapikey')));
	
}

add_filter('all_user_editform', 'ppapikey_all_user_editform', 101, 2);
function ppapikey_all_user_editform($options, $data) {
	
	$user_id = $data->ID;
	if (current_user_can('administrator') or current_user_can('pn_pp')) { 	
		$options['workppapikey'] = array(
			'view' => 'select',
			'title' => __('Work with REST API', 'pn') . ' (ppapi)',
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => intval(is_isset($data, 'workppapikey')),
			'name' => 'workppapikey',
		);			
	}
	
	return $options;
}

add_action('all_user_editform_post', 'ppapikey_all_user_editform_post');
function ppapikey_all_user_editform_post($new_user_data) {
	
	if (current_user_can('administrator') or current_user_can('pn_pp')) { 
		$new_user_data['workppapikey'] = intval(is_param_post('workppapikey'));
	}
	
	return $new_user_data;
}

function unique_ppapikey() {
	global $wpdb;
	
	$value = wp_generate_password(32, false, false);
	if ($value) {
		$cc = $wpdb->get_var("SELECT COUNT(ID) FROM " . $wpdb->prefix . "users WHERE ppapikey = '$value'");
		if ($cc > 0) {
			return unique_ppapikey();
		} else {
			return $value;
		}
	} 
	
	return '';	
}

add_filter('list_stat_paccount', 'ppapi_list_stat_paccount');
function ppapi_list_stat_paccount($list_stat_paccount) {
	global $wpdb, $premiumbox;
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	if (function_exists('db_all_moduls_active_api')) {
	
		$workapikey = intval($premiumbox->get_option('partners', 'workppapikey'));
		$user_workapikey = intval(is_isset($ui,'workppapikey'));
		
		if (0 == $workapikey or 2 == $workapikey and 1 == $user_workapikey) {
			
			$api_key = pn_strip_input(is_isset($ui, 'ppapikey'));
			if (!$api_key) {
				$api_key = unique_ppapikey();
				$arr = array();
				$arr['ppapikey'] = $api_key;
				$wpdb->update($wpdb->prefix . "users", $arr, array('ID' => $user_id));
			}
			
			$list_stat_paccount['ppapikey'] = array(
				'title' => __('REST API key', 'pn'),
				'content' => '<div class="ppapitext"><span class="js_copy pn_copy" data-clipboard-text="' . $api_key . '">' . $api_key . '</span></div><div><a href="' . get_pn_action('changeppapi', 'get') . '" class="changeppapi">' . __('Change REST API key', 'pn') . '</a></div>',
			);
		}
	
	}

	return $list_stat_paccount;
}	

add_action('premium_siteaction_changeppapi', 'def_premium_siteaction_changeppapi');
function def_premium_siteaction_changeppapi() {
	global $wpdb, $premiumbox;	
	
	$premiumbox->up_mode();
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	if (!$user_id) {
		pn_display_mess(__('Error! You must authorize', 'pn'));		
	}
		
	$workapikey = intval($premiumbox->get_option('partners', 'workppapikey'));
	$user_workapikey = intval(is_isset($ui, 'workppapikey'));
	
	if (0 == $workapikey or 2 == $workapikey and 1 == $user_workapikey) {
		$api_key = unique_ppapikey();
		$arr = array();
		$arr['ppapikey'] = $api_key;
		$wpdb->update($wpdb->prefix . "users", $arr, array('ID' => $user_id));
	}	
	
	$url = apply_filters('changeapi_redirect', $premiumbox->get_page('paccount')); 
	wp_redirect($url);
	exit;
}