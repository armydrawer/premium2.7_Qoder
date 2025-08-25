<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_blacklistbest', 'def_adminpage_title_pn_blacklistbest');
	function def_adminpage_title_pn_blacklistbest() {
		
		return __('Settings', 'pn');
	}

	add_action('pn_adminpage_content_pn_blacklistbest', 'def_adminpage_content_pn_blacklistbest');
	function def_adminpage_content_pn_blacklistbest() {
		global $premiumbox;

		$form = new PremiumForm();

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
			'default' => $premiumbox->get_option('blacklistbest', 'method'),
			'name' => 'method',
		);		
		$options['id'] = array(
			'view' => 'input',
			'title' => __('ID', 'pn'),
			'default' => $premiumbox->get_option('blacklistbest', 'id'),
			'name' => 'id',
		);
		$options['key'] = array(
			'view' => 'inputbig',
			'title' => __('Key', 'pn'),
			'default' => $premiumbox->get_option('blacklistbest', 'key'),
			'name' => 'key',
		);
		$options['timeout'] = array(
			'view' => 'input',
			'title' => __('Timeout (sec.)', 'pn'),
			'default' => $premiumbox->get_option('blacklistbest', 'timeout'),
			'name' => 'timeout',
		);		
		$options['timeout_help'] = array(
			'view' => 'help',
			'title' => __('More info', 'pn'),
			'default' => __('Timeout is the period when the website awaits a response from a third-party service. If no response is received in the preset period, the website will continue running without response. If the duration is not specified or is 0, the standard 20-second timeout is applied. There is no universal value for the timeout, since it depends on the operation speed of a specific service.', 'pn'),
		);
		$options['curlerror'] = array(
			'view' => 'select',
			'title' => __('If no response is received from bestchange', 'pn'),
			'options' => array('0' => __('Ok', 'pn'), '1' => __('Error', 'pn')),
			'default' => $premiumbox->get_option('blacklistbest', 'curlerror'),
			'name' => 'curlerror',
			'work' => 'int',
		);		
		$options['check'] = array(
			'view' => 'user_func',
			'name' => 'check',
			'func_data' => array(),
			'func' => 'pn_checkblacklistbest_option',	
		);
		$options['type'] = array(
			'view' => 'select',
			'title' => __('Database for verification', 'pn'),
			'options' => array('0' => __('Scammers and inadequate persons', 'pn'), '1' => __('Scammers', 'pn'), '2' => __('Inadequate persons', 'pn')),
			'default' => $premiumbox->get_option('blacklistbest', 'type'),
			'name' => 'type',
			'work' => 'int',
		);
		$options['warning_type'] = array(
			'view' => 'help',
			'title' => __('More info', 'pn'),
			'default' => __('<p>Attention! The base of fraudsters and inadequacies is collected through the efforts of exchange offices located in BestChange monitoring. BestChange does not check the information from the added records in any way. This API is not intended for automatic denial of service to customers whose details were found in the database, as there is a possibility of false positives. Using the database, you take full responsibility for the possible consequences of a denial of service to customers whose details were in this database by mistake.</p>

<p>We recommend that you use this API only as an additional check for questionable exchange transactions. The received information should be checked by the operator of the exchange office, and only after that a decision should be made on the exchange or on refusal. Please note that customers who once proved to be inadequate at any of the exchange points can conduct exchanges in the future without causing problematic situations.</p>

<p>In case of denial of service to any of the users, do not refer to entries in this database and do not motivate your refusal by having an entry in the "BestChange scam database", because your refusal to exchange is completely your decision, regardless of how your exchange uses this API - for manual verification or for automatic refusal.</p>

<p>An example response to a user whose service was refused on the basis of records from our database: According to information from other exchange points, earlier in exchanges with you there were problematic situations, so we decided to refuse to cooperate with you. Please contact another exchange office.</p>', 'pn'),
		);		
		
		$params_form = array(
			'filter' => 'pn_blacklistbest_configform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
		
	}

	function pn_checkblacklistbest_option() {
		global $premiumbox;
		
		$checks = $premiumbox->get_option('blacklistbest', 'check');
		if (!is_array($checks)) { $checks = array(); }
		
		$fields = array(
			'0'=> __('Invoice Send', 'pn'),
			'1'=> __('Invoice Receive', 'pn'),
			'2'=> __('Mobile phone number', 'pn'),
			'3'=> __('Skype', 'pn'),
			'4'=> __('E-mail', 'pn'),
			'5'=> __('IP', 'pn'),
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
							if (in_array($key, $checks)) {
								$checked = 1;
							}	
							$scroll_lists[] = array(
								'title' => $val,
								'checked' => $checked,
								'value' => $key,
							);
						}
						echo get_check_list($scroll_lists, 'check[]');
						?>			
					
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>					
		<?php	
	}

}

add_action('premium_action_pn_blacklistbest', 'def_premium_action_pn_blacklistbest');
function def_premium_action_pn_blacklistbest() {
	global $wpdb, $premiumbox;

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();		
		
	pn_only_caps(array('administrator', 'pn_blacklistbest'));	
		
	$options = array('id', 'type', 'timeout', 'curlerror', 'method');		
	foreach ($options as $key) {
		$premiumbox->update_option('blacklistbest', $key, intval(is_param_post($key)));
	}
		
	$options = array('key');		
	foreach ($options as $key) {
		$premiumbox->update_option('blacklistbest', $key, pn_strip_input(is_param_post($key)));
	}	
		
	$check = is_param_post('check');
	$premiumbox->update_option('blacklistbest', 'check', $check);

	do_action('pn_blacklistbest_configform_post');
				
	$url = admin_url('admin.php?page=pn_blacklistbest&reply=true');
	$form->answer_form($url);
	
}		