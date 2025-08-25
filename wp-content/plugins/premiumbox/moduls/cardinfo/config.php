<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_action('admin_menu', 'admin_menu_cardinfo');
	function admin_menu_cardinfo() {
		global $premiumbox;
	
		if (current_user_can('administrator') or current_user_can('pn_directions')) {
			add_submenu_page('pn_moduls', __('Bank card type determination', 'pn'), __('Bank card type determination', 'pn'), 'read', 'pn_cardinfo', array($premiumbox, 'admin_temp'));  
		}
		
	}	
	
	add_filter('pn_adminpage_title_pn_cardinfo', 'def_adminpage_title_pn_cardinfo');
	function def_adminpage_title_pn_cardinfo() {
		
		return __('Bank card type determination', 'pn');
	}	
	 
	add_action('pn_adminpage_content_pn_cardinfo', 'def_adminpage_content_pn_cardinfo');
	function def_adminpage_content_pn_cardinfo() {
		global $premiumbox;

		$form = new PremiumForm();

		$options = array();	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);
		$options['memory'] = array(
			'view' => 'select',
			'title' => __('Remember already checked cards', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('cardinfo', 'memory'),
			'name' => 'memory',
		);
		$options['notapi'] = array(
			'view' => 'select',
			'title' => __('If the API did not produce a result', 'pn'),
			'options' => array('0' => __('Nothing', 'pn'), '1' => __('Error', 'pn')),
			'default' => $premiumbox->get_option('cardinfo', 'notapi'),
			'name' => 'notapi',
		);		
		$options['server'] = array(
			'view' => 'select',
			'title' => __('Card type determination source', 'pn'),
			'default' => $premiumbox->get_option('cardinfo', 'server'),
			'options' => apply_filters('cardinfo_servers', array('0' => __('by first digits of card number', 'pn'), '1' => 'bincodes.com', '2' => 'binlist.net', '3' => 'rapidapi.com')),
			'name' => 'server',
		);
		$options['key'] = array(
			'view' => 'inputbig',
			'title' => __('API key of services', 'pn'),
			'default' => $premiumbox->get_option('cardinfo', 'key'),
			'name' => 'key',
		);
		$options['timeout'] = array(
			'view' => 'input',
			'title' => __('Timeout (sec.)', 'pn'),
			'default' => $premiumbox->get_option('cardinfo', 'timeout'),
			'name' => 'timeout',
		);
		$options['timeout_help'] = array(
			'view' => 'help',
			'title' => __('More info', 'pn'),
			'default' => __('Timeout is the period when the website awaits a response from a third-party service. If no response is received in the preset period, the website will continue running without response. If the duration is not specified or is 0, the standard 20-second timeout is applied. There is no universal value for the timeout, since it depends on the operation speed of a specific service.', 'pn'),
		);		
		$options['check'] = array(
			'view' => 'user_func',
			'name' => 'check',
			'func_data' => array(),
			'func' => 'pn_cardinfo_option',	
		);	
		
		$params_form = array(
			'filter' => 'pn_cardinfo_configform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
		
	}
	
 	function pn_cardinfo_option(){
		global $wpdb, $premiumbox;

		$currencies = list_currency('');
	
		$cardinfo = $premiumbox->get_option('cardinfo', 'currency');
		if (!is_array($cardinfo)) { $cardinfo = array(); }

		?>
			<div class="premium_standart_line"> 
				<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Always check currencies', 'pn'); ?></div></div>
				<div class="premium_stline_right"><div class="premium_stline_right_ins">
					<div class="premium_wrap_standart">
					
						<?php
						$scroll_lists = array();
									
						foreach ($currencies as $currency_key => $currency_title) {
							if ($currency_key) {
								$checked = 0;
								if (in_array($currency_key, $cardinfo)) {
									$checked = 1;
								}	
								$scroll_lists[] = array(
									'title' => $currency_title,
									'checked' => $checked,
									'value' => $currency_key,
								);
							}
						}
						echo get_check_list($scroll_lists, 'currency[]', '', '', 1);
						?>			
					
						<div class="premium_clear"></div>
					</div>
				</div></div>
					<div class="premium_clear"></div>
			</div>						
		<?php
		
	}
	
}	
	
add_action('premium_action_pn_cardinfo', 'def_premium_action_pn_cardinfo');
function def_premium_action_pn_cardinfo() {
	global $wpdb, $premiumbox;

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_directions'));	
		
	$options = array('id', 'timeout', 'server', 'memory', 'notapi');		
	foreach ($options as $key) {
		$premiumbox->update_option('cardinfo', $key, intval(is_param_post($key)));
	}
		
	$options = array('key');		
	foreach ($options as $key) {
		$premiumbox->update_option('cardinfo', $key, pn_strip_input(is_param_post($key)));
	}	
		
	$db_currency = array();
	$currency = is_param_post('currency');
	if (is_array($currency)) {
		foreach ($currency as $v) {
			$db_currency[] = intval($v);
		}
	}
	$premiumbox->update_option('cardinfo', 'currency', $db_currency);	

	do_action('pn_cardinfo_configform_post');
				
	$url = admin_url('admin.php?page=pn_cardinfo&reply=true');
	$form->answer_form($url);
	
}		