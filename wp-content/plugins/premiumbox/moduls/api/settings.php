<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_all_settings_api', 'def_adminpage_title_all_settings_api');
	function def_adminpage_title_all_settings_api() {
		return __('Settings', 'pn');
	}

	add_action('pn_adminpage_content_all_settings_api', 'def_adminpage_content_all_settings_api');
	function def_adminpage_content_all_settings_api() {
		
		$plugin = get_plugin_class();
			
		$form = new PremiumForm();
			
		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);
			
		$options['method'] = array(
			'view' => 'select',
			'title' => __('API', 'pn'),
			'options' => array('0' => __('Disable', 'pn'), '1' => __('All users', 'pn'), '2' => __('Featured users', 'pn')),
			'default' => $plugin->get_option('api', 'method'),
			'name' => 'method',
		);
		$options['logs'] = array(
			'view' => 'select',
			'title' => __('Logs', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('api', 'logs'),
			'name' => 'logs',
		);
		$options['callbacks'] = array(
			'view' => 'select',
			'title' => __('Callbacks log', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $plugin->get_option('api', 'callbacks'),
			'name' => 'callbacks',
		);		

		$options['list_method'] = array(
			'view' => 'user_func',
			'name' => 'list_method',
			'func_data' => array(),
			'func' => '_api_settings_list_method',
		);			
			
		$form = new PremiumForm();
		$params_form = array(
			'filter' => 'all_api_settingsform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
	}
	
 	function _api_settings_list_method($bd_data) { 
		global $wpdb;

		$plugin = get_plugin_class();
		$form = new PremiumForm();
		$lists = apply_filters('api_all_methods', array());
		$en = $plugin->get_option('api', 'enabled_method');
		if (!is_array($en)) { $en = array(); }
	?>
		<div class="premium_standart_line"> 
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Methods available to users', 'pn'); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
					if (is_array($lists)) {
						foreach ($lists as $k => $title) {
							$checked = 0;
							if (isset($en[$k])) {
								$checked = 1;
							}
							$scroll_lists[] = array(
								'title' => $title,
								'checked' => $checked,
								'value' => $k,
							);
						}	
					}	
					echo get_check_list($scroll_lists, 'api_m[]', '', '', 1);
					?>			
				<div class="premium_clear"></div>
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>				
	<?php
	}	
}

add_action('premium_action_all_settings_api', 'def_premium_action_all_settings_api');
function def_premium_action_all_settings_api() {
	global $wpdb;	

	$plugin = get_plugin_class();
			
	_method('post');
			
	$form = new PremiumForm();
	$form->send_header();
			
	pn_only_caps(array('administrator', 'pn_api'));
				
	$options = array('method', 'logs', 'callbacks');	
	foreach ($options as $key) {
		$val = intval(is_param_post($key));
		$plugin->update_option('api', $key, $val);
	}
		
	$api_m = is_param_post('api_m');
	$lists = apply_filters('api_all_methods', array());
	$en = array(); 
		
	if (is_array($lists) and is_array($api_m)) {
		foreach ($lists as $k => $title) {
			if (in_array($k, $api_m)) {
				$en[$k] = 1;
			}
		}
	}

	$plugin->update_option('api', 'enabled_method', $en);		
						
	do_action('all_api_settingsform_post');
					
	$url = admin_url('admin.php?page=all_settings_api&reply=true');
	$form->answer_form($url);
}	