<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('def_accountform_filelds')) {
	add_filter('accountform_filelds', 'def_accountform_filelds');
	function def_accountform_filelds($items) {
		
		$ui = wp_get_current_user();
		
		$items['login'] = array(
			'name' => 'login',
			'title' => __('Login', 'pn'),
			'req' => 0,
			'value' => is_user(is_isset($ui, 'user_login')),
			'type' => 'input',
			'atts' => array('disabled' => 'disabled'),
		);
		
		$user_fields = get_user_fields();
		foreach ($user_fields  as $field_key => $field_data) {
			
			if (pn_allow_uv($field_key)) {
				$items[$field_key] = array(
					'name' => $field_key,
					'title' => is_isset($field_data, 'title'),
					'req' => 0,
					'value' => strip_uf(is_isset($ui,$field_key), $field_key),
					'type' => 'input',
				);
				$dis = apply_filters('disabled_account_form_line', 0, $field_key, $ui);
				if (1 == $dis) {
					$items[$field_key]['atts']['disabled'] = 'disabled';
				}
			}
		
		}	
		
		return $items;
	}
}	

if (!function_exists('def_replace_array_accountform')) {
	add_filter('replace_array_accountform', 'def_replace_array_accountform', 10, 3);
	function def_replace_array_accountform($array, $prefix, $place = '') {

		$fields = get_form_fields('accountform', $place);
		
		$filter_name = '';
		if ('widget' == $place) {
			$prefix = 'widget_'. $prefix;
			$filter_name = 'widget_';
		}
		$html = prepare_form_fileds($fields, $filter_name . 'account_form_line', $prefix);	
		
		$array = array(
			'[form]' => '<form method="post" class="ajax_post_form" action="' . get_pn_action('accountform') . '">',
			'[/form]' => '</form>',
			'[result]' => '<div class="resultgo"></div>',
			'[html]' => $html,
			'[submit]' => '<input type="submit" formtarget="_top" name="submit" class="' . $prefix . '_submit" value="' . __('Save', 'pn') . '" />',
		);	
		
		return $array;
	}
}

if (!function_exists('account_page_shortcode')) {
	function account_page_shortcode($atts, $content = "") {
		
		$temp = '';
		
		$temp .= apply_filters('before_account_page', '');
				
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);		
				
		if ($user_id) {
				
			$array = get_form_replace_array('accountform', 'acf');	
				
			$temp_form = '
			<div class="acf_div_wrap">
			[form]
				
				<div class="acf_div_title">
					<div class="acf_div_title_ins">
						'. __('Personal data', 'pn') .'
					</div>
				</div>
			
				<div class="acf_div">
					<div class="acf_div_ins">
						
						[html]
						
						<div class="acf_line has_submit">
							[submit]
						</div>
						
						[result]
					</div>
				</div>

			[/form]
			</div>
			';
		
			$temp_form = apply_filters('account_form_temp', $temp_form);
			$temp .= replace_tags($array, $temp_form);		

		} else {
			$temp .= '<div class="resultfalse">' . __('Error! You must authorize', 'pn') . '</div>';
		}
		
		$temp .= apply_filters('after_account_page', '');	
		
		return $temp;
	}
	add_shortcode('account_page', 'account_page_shortcode');
}

if (!function_exists('def_premium_siteaction_accountform')) {
	add_action('premium_siteaction_accountform', 'def_premium_siteaction_accountform');
	function def_premium_siteaction_accountform() {
		global $wpdb;	
		
		_method('post');
		_json_head();
		
		$log = array();
		$log['status'] = '';
		$log['status_code'] = 0;
		$log['status_text'] = '';
		
		$plugin = get_plugin_class();
		
		$plugin->up_mode('post');
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		if (!$user_id) {
			$log['status'] = 'error'; 
			$log['status_code'] = 1;
			$log['status_text']= __('Error! You must authorize', 'pn');
			echo pn_json_encode($log);
			exit;		
		}
		
		$log = _log_filter($log, 'accountform');
		
		if (!$log['status_code']) {
		
			$new_user_data = array();
		
			$user_fields = get_user_fields();
			foreach ($user_fields  as $field_key => $field_data) {
				if (pn_allow_uv($field_key)) {
					$disabled = apply_filters('disabled_account_form_line', 0, $field_key, $ui);
					if (1 != $disabled) {	
						$in = intval(is_isset($field_data, 'in'));
						$unique = intval(is_isset($field_data, 'unique'));
						$new_value = strip_uf(is_param_post($field_key), $field_key);
						$old_value = strip_uf(is_isset($ui, $field_key), $field_key);
						$set_value = 1;					
					
						if ($unique) {
							if ($new_value and $new_value != $old_value) {
								if ($in) {
									$count = $wpdb->get_var("SELECT COUNT(ID) FROM " . $wpdb->prefix . "users WHERE ID != '$user_id' AND `$field_key` = '$new_value'");
								} else {
									$count = $wpdb->get_var("SELECT COUNT(umeta_id) FROM " . $wpdb->prefix . "usermeta WHERE user_id != '$user_id' AND meta_key = '$field_key' AND meta_value = '$new_value'");
								}						
								if ($count > 0) {	

									$log['status'] = 'error'; 
									$log['status_code'] = 1;
									$log['status_text'] = sprintf(__('%s is already in use','pn'), is_isset($field_data, 'title'));
									
									$set_value = 0;
								}
							}			
						}					
					
						if ($set_value) {
							if ($in) {
								$new_user_data[$field_key] = $new_value;
							} else {
								update_user_meta($user_id, $field_key, $new_value) or add_user_meta($user_id, $field_key, $new_value, true);
							}	
						}					
					}
				}
			}
			
			if (count($new_user_data) > 0) {
				$wpdb->update($wpdb->prefix . 'users', $new_user_data, array('ID' => $user_id));
			}			

			do_action('user_account_post', $user_id, $ui);
		
		}
		
		if (!$log['status_code']) {
			
			$log['status'] = 'success';
			$log['status_code'] = 0;
			$log['status_text'] = apply_filters('account_success_message', __('Data successfully saved', 'pn'));	
			
		}		
		
		echo pn_json_encode($log);
		exit;
	}
}

if (!function_exists('disabled_account_form_line_standart')) {
	add_filter('disabled_account_form_line', 'disabled_account_form_line_standart', 9, 3);
	function disabled_account_form_line_standart($ind, $name, $ui) {
		
		if (1 != $ind) {
			$value = strip_uf(is_isset($ui, $name), $name);
			if (pn_change_uv($name) == 0 and strlen($value) > 0) {
				return 1;
			}
		}
		
		return $ind;
	}
} 