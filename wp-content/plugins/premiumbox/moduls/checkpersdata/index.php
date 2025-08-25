<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Personal data processing[:en_US][ru_RU:]Обработка персональных данных[:ru_RU]
description: [en_US:]Checkbox "Consent to processing of personal data" in forms[:en_US][ru_RU:]Галочка "Согласие на обработку персональных данных" в формах[:ru_RU]
version: 2.7.0
category: [en_US:]Users[:en_US][ru_RU:]Пользователи[:ru_RU]
cat: user
*/

if (!function_exists('list_tech_pages_checkpersdata')) {
	add_filter('pn_tech_pages', 'list_tech_pages_checkpersdata');
	function list_tech_pages_checkpersdata($pages) {
		
		$pages[] = array(
			'post_name'      => 'terms_personal_data',
			'post_title'     => '[en_US:]User agreement for personal data processing[:en_US][ru_RU:]Пользовательское соглашение по обработке персональных данных[:ru_RU]',
			'post_content'   => '',
			'post_template'   => '',
		);		
		
		return $pages;
	}
}

if (!function_exists('get_form_filelds_checkpersdata')) {
	add_filter('get_form_filelds', 'get_form_filelds_checkpersdata', 1010, 2);
	function get_form_filelds_checkpersdata($items, $name) {
		
		$plugin = get_plugin_class();

		if (1 == $plugin->get_option('checkpersdata', $name)) {
			$items['terms_personal_data'] = array(
				'type' => 'terms_personal_data',
			);
		}
		
		return $items;
	}
}

if (!function_exists('form_field_line_checkpersdata')) {
	add_filter('form_field_line', 'form_field_line_checkpersdata', 10, 4);
	function form_field_line_checkpersdata($line, $filter, $data, $prefix) {
		
		$plugin = get_plugin_class();
		$checkpersdata = intval($plugin->get_option('checkpersdata'));
		
		$type = trim(is_isset($data, 'type'));
		if ('terms_personal_data' == $type) {
			$line = '
			<div class="form_field_line ' . $prefix . '_line checkpersdata_line">
				<label><input type="checkbox" ' . checked($checkpersdata, 1, false) . ' name="tpd" autocomplete="off" value="1" /> ' . sprintf(__('I consent to processing of my personal data and accept the terms and conditions of the <a href="%s" target="_blank" rel="noreferrer noopener">User Agreement</a>.', 'pn'), $plugin->get_page('terms_personal_data')) . '</label>
			</div>
			';	
		}
		
		return $line;
	}
}

if (!function_exists('comment_form_checkpersdata')) {
	add_action('comment_form', 'comment_form_checkpersdata', 1010);
	function comment_form_checkpersdata() {
		
		$plugin = get_plugin_class();
		$checkpersdata = intval($plugin->get_option('checkpersdata'));
		
		if (1 == $plugin->get_option('checkpersdata', 'commentform')) {
			
			$line = '
			<div class="comment_form_line checkpersdata_line">
				<label><input type="checkbox" ' . checked($checkpersdata, 1, false) . ' name="tpd" autocomplete="off" value="1" /> ' . sprintf(__('I consent to processing of my personal data and accept the terms and conditions of the <a href="%s" target="_blank" rel="noreferrer noopener">User Agreement</a>.', 'pn'), $plugin->get_page('terms_personal_data')) . '</label>
			</div>
			';	
			
			echo $line;
		}	
		
	}
}

if (!function_exists('before_ajax_form_checkpersdata')) {
	add_filter('before_ajax_form', 'before_ajax_form_checkpersdata', 990, 2);
	function before_ajax_form_checkpersdata($logs, $name) {
		
		$plugin = get_plugin_class();	

		if (1 == $plugin->get_option('checkpersdata', $name)) {
			$tpd = intval(is_param_post('tpd'));
			if (!$tpd) { 
				$logs['status']	= 'error';
				$logs['status_code'] = '1'; 
				$logs['status_text'] = __('Error! You have not accepted the terms and conditions of the User Agreement', 'pn');
			}		
		}
		
		return $logs;
	}
}

if (!function_exists('checkpersdata_settings_option')) {
	add_filter('all_settings_option', 'checkpersdata_settings_option');
	function checkpersdata_settings_option($options) {
		
		$plugin = get_plugin_class();
			
		$options[] = array(
			'view' => 'line',
		);	
		$options['checkpersdata'] = array(
			'view' => 'select',
			'title' => __('Checkboxes in forms (personal data processing)', 'pn'),
			'options' => array('0' => __('Do not check box by default', 'pn'), '1' => __('Check box by default', 'pn')),
			'default' => $plugin->get_option('checkpersdata'),
			'name' => 'checkpersdata',
			'work' => 'int',
		);
			
		return $options;
	}
}

if (!function_exists('checkpersdata_all_settings_option_post')) {
	add_action('all_settings_option_post', 'checkpersdata_all_settings_option_post');
	function checkpersdata_all_settings_option_post($data) {
		
		$plugin = get_plugin_class();
		$checkpersdata = intval($data['checkpersdata']);
		$plugin->update_option('checkpersdata', '', $checkpersdata);
		
	}
}

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'settings');
$plugin->include_path(__FILE__, 'premiumbox');