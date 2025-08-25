<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('pn_adminpage_title_pn_config', 'def_adminpage_title_pn_config');
function def_adminpage_title_pn_config($page) {
	
	return __('General settings', 'pn');
} 
	
add_action('pn_adminpage_content_pn_config', 'def_adminpage_content_pn_config');
function def_adminpage_content_pn_config() {
	global $wpdb, $premiumbox;

	$form = new PremiumForm();

	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('General settings', 'pn'),
		'submit' => __('Save','pn'),
	);
	$options['up_mode'] = array(
		'view' => 'select',
		'title' => __('Updating mode', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('up_mode'),
		'name' => 'up_mode',
		'work' => 'int',
	);
	$options[] = array(
		'view' => 'line',
	);
	$options['adminpass'] = array(
		'view' => 'select',
		'title' => __('Remember successful entry of the security code', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('adminpass'),
		'name' => 'adminpass',
		'work' => 'int',
	);
	$options['nocopydata'] = array( 
		'view' => 'select',
		'title' => __('Remove the copy button in requests', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('nocopydata'),
		'name' => 'nocopydata',
		'work' => 'int',
	);	
	$options['bidsfile'] = array(
		'view' => 'select',
		'title' => __('Store ticket data in a file?', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('bidsfile'),
		'name' => 'bidsfile',
		'work' => 'int',
	);	
	$options['exchange_title'] = array(
		'view' => 'h3',
		'title' => __('Exchange settings', 'pn'),
		'submit' => __('Save', 'pn'),
	);
	$tablevids = array(
		//'0' => sprintf(__('Table %1s', 'pn'), '1'),
		'1' => sprintf(__('Table %1s', 'pn'), '2'),
		'2' => sprintf(__('Table %1s', 'pn'), '3'),
		'3' => sprintf(__('Table %1s', 'pn'), '4'),
		'4' => sprintf(__('Table %1s', 'pn'), '5'),
		'99' => __('Exchange form', 'pn'),
	);
	$tablevids = apply_filters('exchange_tablevids_list', $tablevids);
	$options['tablevid'] = array(
		'view' => 'select',
		'title' => __('Exchange pairs table type', 'pn'),
		'options' => $tablevids,
		'default' => $premiumbox->get_option('exchange', 'tablevid'),
		'name' => 'tablevid',
		'work' => 'int',
	);
	if (1 == get_settings_second_logo()) {
		$options['tableicon'] = array(
			'view' => 'select',
			'title' => __('Show PS logo in exchange table', 'pn'),
			'options' => array('0' => __('Main logo', 'pn'), '1' => __('Additional logo', 'pn')),
			'default' => $premiumbox->get_option('exchange', 'tableicon'),
			'name' => 'tableicon',
			'work' => 'int',
		);
	}	
	$options[] = array(
		'view' => 'line',
	);		
	$options['tablenothome'] = array(
		'view' => 'select',
		'title' => __('If non-existent direction is selected', 'pn'),
		'options' => array('0' => __('Show error', 'pn'), '1' => __('Show nearest', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'tablenothome'),
		'name' => 'tablenothome',
		'work' => 'int',
	);
	$options['tableselecthome'] = array(
		'view' => 'select',
		'title' => __('Display in home exchange form', 'pn'),
		'options' => array('0' => __('All currencies', 'pn'), '1' => __('Only available currencies for exchange', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'tableselecthome'),
		'name' => 'tableselecthome',
		'work' => 'int',
	);
	$options['hidecurrtype'] = array(
		'view' => 'select',
		'title' => __('Hide currency codes above table for selecting exchange direction', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'hidecurrtype'),
		'name' => 'hidecurrtype',
		'work' => 'int',
	);
	$options['exch_method'] = array(
		'view' => 'select',
		'title' => __('Exchange type', 'pn'),
		'options' => array('0' => __('On a new page', 'pn')/*, '1' => __('On a main page', 'pn')*/),
		'default' => $premiumbox->get_option('exchange', 'exch_method'),
		'name' => 'exch_method',
		'work' => 'int',
	);
	$options['tablehideerror'] = array(
		'view' => 'select',
		'title' => __('Hide errors of min and max amounts in the selection table', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'tablehideerror'),
		'name' => 'tablehideerror',
		'work' => 'int',
	);	
	$options['exchangeform_title'] = array(
		'view' => 'h3',
		'title' => __('Exchange form settings', 'pn'),
		'submit' => __('Save', 'pn'),
	);
	$options['tablenot'] = array(
		'view' => 'select',
		'title' => __('If non-existent direction is selected from exchange form', 'pn'),
		'options' => array('0' => __('Show error', 'pn'), '1' => __('Show nearest', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'tablenot'),
		'name' => 'tablenot',
		'work' => 'int',
	);
	$options['tableselect'] = array(
		'view' => 'select',
		'title' => __('Display in exchange form', 'pn'),
		'options' => array('0' => __('All currencies', 'pn'), '1' => __('Only available currencies for exchange', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'tableselect'),
		'name' => 'tableselect',
		'work' => 'int',
	);	
	$options['formhideerror'] = array(
		'view' => 'select',
		'title' => __('Hide errors of min and max amounts in start exchange form', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'formhideerror'),
		'name' => 'formhideerror',
		'work' => 'int',
	);	
	$options['hidesavedata'] = array(
		'view' => 'select',
		'title' => __('Disable remembering application data', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'hidesavedata'),
		'name' => 'hidesavedata',
		'work' => 'int',
	);
	$options['hidecheckrule'] = array(
		'view' => 'select',
		'title' => __('Hide acceptance of the terms of exchange', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'hidecheckrule'),
		'name' => 'hidecheckrule',
		'work' => 'int',
	);	
	$options['exchangeother_title'] = array(
		'view' => 'h3',
		'title' => __('Other settings', 'pn'),
		'submit' => __('Save', 'pn'),
	);
	$options['enable_step2'] = array(
		'view' => 'select',
		'title' => __('Use exchange step №2, where user confirms his details', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'enable_step2'),
		'name' => 'enable_step2',
		'work' => 'int',
	);
	$options['tostext'] = array(
		'view' => 'editor',
		'title' => __('Check mark text with rules', 'pn'),
		'default' => $premiumbox->get_option('exchange', 'tostext'),
		'name' => 'tostext',
		'rows' => '10',
		'formatting_tags' => 1,
		'other_tags' => 1,
		'ml' => 1,
	);		
	$exsum = array(
		'0' => __('Amount To send', 'pn'),
		'1' => __('Amount To send with add. fees', 'pn'),
		'2' => __('Amount To send with add. fees and PS fees', 'pn'),
		'6' => __('Amount To send for reserve', 'pn'),
		'3' => __('Amount Receive', 'pn'),
		'4' => __('Amount To receive with add. fees', 'pn'),
		'5' => __('Amount To receive with add. fees and PS fees', 'pn'),
		'7' => __('Amount To receive for reserve', 'pn'),
	);	
	$options['exch_exsum'] = array(
		'view' => 'select',
		'title' => __('Amount needed to be exchanged is', 'pn'),
		'options' => $exsum,
		'default' => $premiumbox->get_option('exchange', 'exch_exsum'),
		'name' => 'exch_exsum',
		'work' => 'int',		
	);
	$options['otherdir'] = array(
		'view' => 'select',
		'title' => __('Show block other directions', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('By currency give', 'pn'), '2' => __('By currency get', 'pn')),
		'default' => $premiumbox->get_option('exchange','otherdir'),
		'name' => 'otherdir',
		'work' => 'int',		
	);	
	$options['reservdopcom'] = array(
		'view' => 'select',
		'title' => __('Disable adding additional commission to reserve', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'reservdopcom'),
		'name' => 'reservdopcom',
		'work' => 'int',
	);
	$options['dependenceminmax'] = array(
		'view' => 'select',
		'title' => __('Dependence of the minimum and maximum amount', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes, all site', 'pn'), '2' => __('Yes, in export file', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'dependenceminmax'),
		'name' => 'dependenceminmax',
		'work' => 'int',
	);	
	$options['admin_mail'] = array(
		'view' => 'select',
		'title' => __('Send e-mail notifications to admin if admin changes status of order on his own', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'admin_mail'),
		'name' => 'admin_mail',
		'work' => 'int',
	);
	$options[] = array(
		'view' => 'h3',
		'title' => '',
		'submit' => __('Save', 'pn'),
	);
	$options['allow_dev'] = array(
		'view' => 'select',
		'title' => __('Allow to manage order using another browser', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'allow_dev'),
		'name' => 'allow_dev',
		'work' => 'int',
	);										
	$options['an_hidden'] = array(
		'view' => 'select',
		'title' => __('Data visibility in order for personal information', 'pn'),
		'options' => array('0' => __('do not show data', 'pn'), '1' => __('hide data', 'pn'), '2' => __('do not hide first 4 symbols', 'pn'), '3' => __('do not hide last 4 symbols', 'pn'), '4' => __('do not hide first 4 symbols and the last 4 symbols', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'an_hidden'),
		'name' => 'an_hidden',
		'work' => 'int',
	);	
	$options[] = array(
		'view' => 'h3',
		'title' => '',
		'submit' => __('Save', 'pn'),
	);
	$options['mercherroraction'] = array(  
		'view' => 'select',
		'title' => __('Action if the merchant does not work', 'pn'),
		'options' => array('0' => __('Connect a merchant', 'pn'), '1' => __('Convert the request to a merchant error', 'pn'), '2' =>__('Try connecting another merchant', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'mercherroraction'),
		'name' => 'mercherroraction',
		'work' => 'int',
	);
	$options['erroraaccount'] = array(
		'view' => 'editor',
		'title' => __('Error text if account is not specified', 'pn'),
		'default' => $premiumbox->get_option('exchange', 'erroraaccount'),
		'tags' => '',
		'rows' => '8',
		'name' => 'erroraaccount',
		'work' => 'text',
		'formatting_tags' => 0,
		'other_tags' => 0,
		'ml' => 1,
	);		
	$options['mhead_style'] = array(
		'view' => 'select',
		'title' => __('Style of page header used for redirecting', 'pn'),
		'options' => array('0' => __('White style', 'pn'), '1' => __('Black style', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'mhead_style'),
		'name' => 'mhead_style',
		'work' => 'int',
	);		
	$options['m_ins'] = array(
		'view' => 'select',
		'title' => __('If there are no payment instructions given to merchant then', 'pn'),
		'options' => array('0' => __('Nothing to be shown', 'pn'), '1' => __('Show relevant payment instructions of exchange direction', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'm_ins'),
		'name' => 'm_ins',
		'work' => 'int',
	);
	$options['mp_ins'] = array(
		'view' => 'select',
		'title' => __('If there are no instructions for automatic payments mode then', 'pn'),
		'options' => array('0' => __('Nothing to be shown', 'pn'), '1' => __('Show relevant payment instructions of exchange direction', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'mp_ins'),
		'name' => 'mp_ins',
		'work' => 'int',
	);
	$options['avsumbig'] = array(
		'view' => 'select',
		'title' => __('Make payout if received amount is more than required', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'avsumbig'),
		'name' => 'avsumbig',
		'work' => 'int',
	);
	$options['touapcount'] = array(
		'view' => 'input',
		'title' => __('Number of applications in cron requests', 'pn'),
		'default' => $premiumbox->get_option('exchange', 'touapcount'),
		'name' => 'touapcount',
		'work' => 'int',
	);		
	$options['bidsind'] = array(
		'view' => 'user_func',
		'func_data' => array(),
		'func' => '_bidsind_option',
	);	

	$params_form = array(
		'filter' => 'pn_config_option',
		'button_title' => __('Save', 'pn'),
	);
	$form->init_form($params_form, $options);
		
} 

function _bidsind_option() {
	
	$plugin = get_plugin_class();
	$bid_status_list = list_bid_status();
		
	$bidsind = $plugin->get_option('bidsind');
	if (!is_array($bidsind)) { $bidsind = array(); }
	?>
	<div class="premium_standart_line">
		<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Select order statuses for notification in topar', 'pn'); ?></div></div>
		<div class="premium_stline_right"><div class="premium_stline_right_ins">
			<div class="premium_wrap_standart">
				<?php 
				$scroll_lists = array();
				if (is_array($bid_status_list)) {
					foreach ($bid_status_list as $key => $val) {
						$checked = 0;
						if (in_array($key, $bidsind)) {
							$checked = 1;
						}
						$scroll_lists[] = array(
							'title' => $val,
							'checked' => $checked,
							'value' => $key,
						);
					}	
				}	
				echo get_check_list($scroll_lists, 'bidsind[]');				
				?>
				<div class="premium_clear"></div>
			</div>
		</div></div>
			<div class="premium_clear"></div>
	</div>		
	<?php				
} 

add_action('premium_action_pn_config', 'def_premium_action_pn_config');
function def_premium_action_pn_config() {
	global $wpdb, $premiumbox;	

	_method('post');
	
	$form = new PremiumForm();
	$form->send_header();	
	
	pn_only_caps(array('administrator'));
			
	$opts =  array('up_mode'); 
	foreach ($opts as $opt) {
		$premiumbox->update_option('up_mode', '', intval(is_param_post($opt)));
	}
	
	$button = array();
	$array = is_param_post('bidsind');
	if (is_array($array)) {
		foreach ($array as $v) {
			$v = is_status_name($v);
			if ($v) {
				$button[] = $v;
			}
		}
	}
	$premiumbox->update_option('bidsind', '', $button);	
	
	$opts =  array('adminpass', 'bidsfile', 'nocopydata'); 
	foreach ($opts as $opt) {
		$premiumbox->update_option($opt, '', intval(is_param_post($opt)));
	}
	
	update_option('asalt', get_random_password(12));

	$options = array(
		'tablevid', 'tableicon', 'dependenceminmax', 'reservdopcom', 'hidecurrtype', 'tablehideerror', 'formhideerror',
		'tablenothome', 'tableselecthome', 'tablenot', 'tableselect',
		'allow_dev', 'exch_exsum', 'exch_method', 'otherdir', 'hidesavedata', 'hidecheckrule',
		'enable_step2', 'an_hidden', 'admin_mail', 'mercherroraction',
		'avsumbig', 'touapcount', 'm_ins', 'mp_ins', 'mhead_style'
	);
	foreach ($options as $key) {
		
		$val = pn_strip_input(is_param_post($key));
		$premiumbox->update_option('exchange', $key, $val);
		
	}

	$options = array(
		'tostext', 'erroraaccount',
	);
	foreach ($options as $key) {
		$val = pn_strip_text(is_param_post_ml($key));
		$premiumbox->update_option('exchange', $key, $val);
	}	
		
	do_action('pn_config_option_post');			
		
	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
				
	$form->answer_form($back_url);	
	
}