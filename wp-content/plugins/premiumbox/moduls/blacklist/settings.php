<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_all_settings_blacklist', 'def_adminpage_title_all_settings_blacklist');
	function def_adminpage_title_all_settings_blacklist() {
		
		return __('Settings', 'pn');
	}

	add_action('pn_adminpage_content_all_settings_blacklist', 'def_adminpage_content_all_settings_blacklist');
	function def_adminpage_content_all_settings_blacklist() {

		$form = new PremiumForm();
		$plugin = get_plugin_class();
		
		$options = array();	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);

		$options['method'] = array(
			'view' => 'select',
			'title' => __('Method', 'pn'),
			'options' => array('0' => __('throw an error', 'pn'), '1' => __('stop auto payments', 'pn')),
			'default' => $plugin->get_option('blacklist', 'method'),
			'name' => 'method',
		);
		
		$options['check'] = array(
			'view' => 'user_func',
			'name' => 'check',
			'func_data' => array(),
			'func' => 'pn_checkblacklist_option',	
		);	
		
		$params_form = array(
			'filter' => 'all_blacklist_configform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
		
	}

	function pn_checkblacklist_option() {
		
		$plugin = get_plugin_class();
		
		$checks = $plugin->get_option('blacklist', 'check');
		if (!is_array($checks)) { $checks = array(); }
		
		$fields = array(
			'0'=> __('Invoice Send', 'pn'),
			'1'=> __('Invoice Receive', 'pn'),
			'2'=> __('Mobile phone number', 'pn'),
			'3'=> __('Skype', 'pn'),
			'4'=> __('E-mail', 'pn'),
			'5'=> __('IP', 'pn'),
			'6' => __('Real account', 'pn'),
		);
		?>
		<div class="premium_standart_line"> 
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Check selected fields', 'pn'); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">
				
					<?php
					$scroll_lists = array();
							
					foreach ($fields as $key => $val) {
						$checked = 0;
						if (in_array($key,$checks)) {
							$checked = 1;
						}	
						$scroll_lists[] = array(
							'title' => $val,
							'checked' => $checked,
							'value' => $key,
						);
					}
					echo get_check_list($scroll_lists, 'check[]', '');
					?>			
				
					<div class="premium_clear"></div>
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>						
		<?php	
	}

}

add_action('premium_action_all_settings_blacklist', 'def_premium_action_all_settings_blacklist');
function def_premium_action_all_settings_blacklist() {
	global $wpdb;

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_blacklist'));

	$plugin = get_plugin_class();
		
	$check = pn_strip_input_array(is_param_post('check'));
	$plugin->update_option('blacklist', 'check', $check);
	
	$method = intval(is_param_post('method'));
	$plugin->update_option('blacklist', 'method', $method);	

	do_action('all_blacklist_configform_post');
				
	$url = admin_url('admin.php?page=all_settings_blacklist&reply=true');
	$form->answer_form($url);
	
}