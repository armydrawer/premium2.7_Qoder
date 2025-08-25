<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_settings_new_parser', 'def_adminpage_title_pn_settings_new_parser');
	function def_adminpage_title_pn_settings_new_parser($page) {
		return __('Parser settings', 'pn');
	} 

	add_action('pn_adminpage_content_pn_settings_new_parser', 'def_adminpage_content_pn_settings_new_parser');
	function def_adminpage_content_pn_settings_new_parser() {
		global $wpdb, $premiumbox;
		
		$form = new PremiumForm();
		
		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Parser settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);
		$options['parser_sort'] = array(
			'view' => 'select',
			'title' => __('Sort', 'pn'),
			'options' => array('0' => __('Manual', 'pn'), '1' => __('By name', 'pn')),
			'default' => $premiumbox->get_option('newparser', 'parser_sort'),
			'name' => 'parser_sort',
		);		
		$options['parser'] = array(
			'view' => 'select',
			'title' => __('Parser type', 'pn'),
			'options' => array('0' => __('CURL', 'pn'), '1' => __('Multithreaded', 'pn')),
			'default' => $premiumbox->get_option('newparser', 'parser'),
			'name' => 'parser',
		);		
		$options['parser_log'] = array(
			'view' => 'select',
			'title' => __('Logging parsing', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn'), '2' => __('Only errors', 'pn')),
			'default' => $premiumbox->get_option('newparser', 'parser_log'),
			'name' => 'parser_log',
		);
		$options['timeout'] = array(
			'view' => 'inputbig',
			'title' => __('Timeout (sec.)', 'pn'),
			'default' => $premiumbox->get_option('newparser', 'timeout'),
			'name' => 'timeout',
		);
		$options['timeout_help'] = array(
			'view' => 'help',
			'title' => __('More info', 'pn'),
			'default' => __('Timeout is the period when the website awaits a response from a third-party service. If no response is received in the preset period, the website will continue running without response. If the duration is not specified or is 0, the standard 20-second timeout is applied. There is no universal value for the timeout, since it depends on the operation speed of a specific service.', 'pn'),
		);		
		$date = __('No', 'pn');
		$time_parser = get_option('time_new_parser');
		if ($time_parser) {
			$date = date('d.m.Y H:i', $time_parser);
		}
		$options['time_last'] = array(
			'view' => 'textfield',
			'title' => __('Last update time', 'pn'),
			'default' => $date,
		);
		$options['delold'] = array(
			'view' => 'select',
			'title' => __('Delete old data', 'pn'),
			'default' => $premiumbox->get_option('newparser', 'delold'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'name' => 'delold',
		);
		$options['hidecours'] = array(
			'view' => 'select',
			'title' => __('Disable parser courses in directions?', 'pn'),
			'default' => $premiumbox->get_option('newparser', 'hidecours'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'name' => 'hidecours',
		);
		$options['sources'] = array(
			'view' => 'user_func',
			'name' => 'sources',
			'func_data' => '',
			'func' => 'pn_settings_new_parser_sources_options',
		);		
		$params_form = array(
			'filter' => 'pn_settings_new_parser_options',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
		
	}  
	
 	function pn_settings_new_parser_sources_options($db_data) { 
		global $wpdb;

		$birgs = apply_filters('new_parser_links', array());
		
		$work_birgs = get_option('work_birgs');
		if (!is_array($work_birgs)) { $work_birgs = array(); }
	?>
		<div class="premium_standart_line"> 
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Rates sources', 'pn'); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
                    $birgs = list_checks_top($birgs, $work_birgs);
					if (is_array($birgs)) {
						foreach ($birgs as $birg_key => $birg_data) {
							$checked = 0;
							if (in_array($birg_key, $work_birgs)) {
								$checked = 1;
							}
							$scroll_lists[] = array(
								'title' => is_isset($birg_data, 'title'),
								'checked' => $checked,
								'value' => $birg_key,
							);
						}	
					}	
					echo get_check_list($scroll_lists, 'sources[]', '', '300', 1);
					?>			
				<div class="premium_clear"></div>
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>				
	<?php
	} 	

}

add_action('premium_action_pn_settings_new_parser', 'def_premium_action_pn_settings_new_parser');
function def_premium_action_pn_settings_new_parser() {
	global $wpdb, $premiumbox;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_directions', 'pn_parser'));
		
	$sources = is_param_post('sources');
	$work_birgs = array();
	if (is_array($sources)) {
		foreach ($sources as $id) {
			$id = pn_string($id);
			if (strlen($id) > 0) {
				$work_birgs[] = $id;
			}
		}	
	}
	$work_birgs = pn_strip_input_array($work_birgs);
	update_option('work_birgs', $work_birgs);
		
	$premiumbox->update_option('newparser', 'parser_sort', intval(is_param_post('parser_sort')));
	$premiumbox->update_option('newparser', 'parser', intval(is_param_post('parser')));
	$premiumbox->update_option('newparser', 'parser_log', intval(is_param_post('parser_log')));
	$premiumbox->update_option('newparser', 'timeout', intval(is_param_post('timeout')));
	$premiumbox->update_option('newparser', 'delold', intval(is_param_post('delold')));
	$premiumbox->update_option('newparser', 'hidecours', intval(is_param_post('hidecours')));

	$url = admin_url('admin.php?page=pn_settings_new_parser&reply=true');
	$form->answer_form($url);
}
