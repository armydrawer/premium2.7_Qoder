<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_all_telegram', 'def_adminpage_title_all_telegram');
	function def_adminpage_title_all_telegram() {
		
		return __('Telegram settings', 'pn');
	} 

 	add_action('pn_adminpage_content_all_telegram', 'def_adminpage_content_all_telegram');
	function def_adminpage_content_all_telegram() {
		global $premiumbox, $wpdb;

		$data = get_option('telegram_settings');
		if (!is_array($data)) { $data = array(); }

		$form = new PremiumForm();
		
		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Telegram settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);	
		
		$options['token'] = array(
			'view' => 'inputbig',
			'title' => __('Token', 'pn'),
			'default' => is_isset($data, 'token'),
			'name' => 'token',
			'atts' => array('autocomplete' => 'off'),
		);		
		$text = '
		<div>' . __('In Telegram, send message <b>/newbot</b> to user @BotFather and follow instructions. Specify the received token in the field above.', 'pn') . '</div>
		';
		$options['telegram_help'] = array(
			'view' => 'help',
			'title' => __('How to create a Telegram bot?', 'pn'),
			'default' => $text,
		);		
		$options['bot_logs'] = array(
			'view' => 'select',
			'title' => __('Log bot requests', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => is_isset($data, 'bot_logs'),
			'name' => 'bot_logs',
		);		
		$options['answer_logs'] = array(
			'view' => 'select',
			'title' => __('Log user requests', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => is_isset($data, 'answer_logs'),
			'name' => 'answer_logs',
		);
		$options['bots'] = array(
			'view' => 'select',
			'title' => __('Bots', 'pn'),
			'options' => array('0' => __('Disabled bots', 'pn'), '1' => __('Enabled bots', 'pn')),
			'default' => is_isset($data, 'bots'),
			'name' => 'bots',
		);		
		
		$tags = array(
			'first_name' => array(
				'title' => __('First name', 'pn'),
				'start' => '[first_name]',
			),
			'chat_id' => array(
				'title' => __('Chat ID', 'pn'),
				'start' => '[chat_id]',
			),
			'b' => array(
				'title' => 'b',
				'start' => '<b>',
				'end' => '</b>',
			),
			'strong' => array(
				'title' => 'strong',
				'start' => '<strong>',
				'end' => '</strong>',
			),
			'i' => array(
				'title' => 'i',
				'start' => '<i>',
				'end' => '</i>',
			),
			'em' => array(
				'title' => 'em',
				'start' => '<em>',
				'end' => '</em>',
			),			
		);		
		$options['welocome_text'] = array(
			'view' => 'editor',
			'title' => __('Text of first message from bot', 'pn'),
			'default' => is_isset($data, 'welocome_text'),
			'tags' => $tags,
			'rows' => '10',
			'name' => 'welocome_text',
			'work' => 'text',
			'ml' => 1,
		);	

		$options['nologin'] = array(
			'view' => 'select',
			'title' => __('No login', 'pn'),
			'options' => array('0' => __('Text about no login', 'pn'), '1' => __('Use chat id', 'pn')),
			'default' => is_isset($data, 'nologin'),
			'name' => 'nologin',
		);

		$tags = array(
			'first_name' => array(
				'title' => __('First name', 'pn'),
				'start' => '[first_name]',
			),
			'b' => array(
				'title' => 'b',
				'start' => '<b>',
				'end' => '</b>',
			),
			'strong' => array(
				'title' => 'strong',
				'start' => '<strong>',
				'end' => '</strong>',
			),
			'i' => array(
				'title' => 'i',
				'start' => '<i>',
				'end' => '</i>',
			),
			'em' => array(
				'title' => 'em',
				'start' => '<em>',
				'end' => '</em>',
			),			
		);		
		$options['nologin_text'] = array(
			'view' => 'editor',
			'title' => __('Message to user, if no login is specified in Telegram settings', 'pn'),
			'default' => is_isset($data, 'nologin_text'),
			'tags' => $tags,
			'rows' => '10',
			'name' => 'nologin_text',
			'work' => 'text',
			'ml' => 1,
		);

		$tags = array(
			'first_name' => array(
				'title' => __('First name', 'pn'),
				'start' => '[first_name]',
			),
			'login' => array(
				'title' => __('Login', 'pn'),
				'start' => '[login]',
			),
			'b' => array(
				'title' => 'b',
				'start' => '<b>',
				'end' => '</b>',
			),
			'strong' => array(
				'title' => 'strong',
				'start' => '<strong>',
				'end' => '</strong>',
			),
			'i' => array(
				'title' => 'i',
				'start' => '<i>',
				'end' => '</i>',
			),
			'em' => array(
				'title' => 'em',
				'start' => '<em>',
				'end' => '</em>',
			),			
		);		
		$options['yeslogin_text'] = array(
			'view' => 'editor',
			'title' => __('Message to user about successful bot binding', 'pn'),
			'default' => is_isset($data, 'yeslogin_text'),
			'tags' => $tags,
			'rows' => '10',
			'name' => 'yeslogin_text',
			'work' => 'text',
			'ml' => 1,
		);	

		$options['tooltip_line'] = array(
			'view' => 'line',
		);

		$options['tooltip'] = array(
			'view' => 'textarea',
			'title' => __('Tip for Telegram field in user profile', 'pn'),
			'default' => is_isset($data, 'tooltip'),
			'rows' => '10',
			'name' => 'tooltip',
			'work' => 'text',
			'ml' => 1,
		);		
		
		$text = '
		<div>' . __('To register the webhook, follow the link', 'pn') . ' <a href="' . pn_link('all_telegram_set') . '" target="_blank">' . pn_link('all_telegram_set') . '</a></div>
		<div>' . __('To remove the webhook, follow the link', 'pn') . ' <a href="' . pn_link('all_telegram_unset') . '" target="_blank">' . pn_link('all_telegram_unset') . '</a></div>
		';
		$options['telegram_textfield'] = array(
			'view' => 'textfield',
			'title' => '',
			'default' => $text,
		);
		$params_form = array(
			'filter' => 'all_telegram_options',
			'data' => $data,
			'form_link' => pn_link('', 'post'),
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
	}
	
}
	
add_action('premium_action_all_telegram_set', 'def_premium_action_all_telegram_set');
function def_premium_action_all_telegram_set() {
	global $wpdb, $premiumbox;	

	$form = new PremiumForm();
	$form->send_header();

	pn_only_caps(array('administrator'));
			
	$data = get_option('telegram_settings');
	if (!is_array($data)) { $data = array(); }
	$token = pn_strip_input(is_isset($data, 'token'));
		
	if (!$token) {
		$form->error_form(__('Error! You have not saved bot token in settings', 'pn'));
	}
		
	$api_url = get_api_link('telegram', 'v1', 'webhook') . '?telegram_token=' . $token; 
	$class = new TelegramBot($token, is_isset($data, 'bot_logs'), is_isset($data, 'answer_logs'));	
	$res = $class->set_webhook($api_url);	
			
	if (!isset($res['result'])) {
		$form->error_form(__('Error! API error', 'pn'));
	}			

	$back_url = admin_url('admin.php?page=all_telegram&reply=true');			
	$form->answer_form($back_url);
	
}

add_action('premium_action_all_telegram_unset', 'def_premium_action_all_telegram_unset');
function def_premium_action_all_telegram_unset() {
	global $wpdb, $premiumbox;	

	$form = new PremiumForm();
	$form->send_header();

	pn_only_caps(array('administrator'));
		
	$data = get_option('telegram_settings');
	if (!is_array($data)) { $data = array(); }
	$token = pn_strip_input(is_isset($data, 'token'));
		
	if (!$token) {
		$form->error_form(__('Error! You have not saved bot token in settings', 'pn'));
	}
		
	$class = new TelegramBot($token, is_isset($data, 'bot_logs'), is_isset($data, 'answer_logs'));	
	$res = $class->unset_webhook();	
			
	if (!isset($res['result'])) {
		$form->error_form(__('Error! API error', 'pn'));
	}	

	$back_url = admin_url('admin.php?page=all_telegram&reply=true');			
	$form->answer_form($back_url);
	
}	
	 
add_action('premium_action_all_telegram', 'def_premium_action_all_telegram');
function def_premium_action_all_telegram() {
	global $wpdb, $premiumbox;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator'));
		
	$data = array();
	$data['token'] = pn_strip_input(is_param_post('token'));
	$data['bot_logs'] = intval(is_param_post('bot_logs'));
	$data['answer_logs'] = intval(is_param_post('answer_logs'));
	$data['nologin'] = intval(is_param_post('nologin'));
	$data['bots'] = intval(is_param_post('bots'));
	$data['welocome_text'] = pn_strip_text(addslashes(is_param_post_ml('welocome_text')));
	$data['nologin_text'] = pn_strip_text(addslashes(is_param_post_ml('nologin_text')));
	$data['yeslogin_text'] = pn_strip_text(addslashes(is_param_post_ml('yeslogin_text')));
	$data['tooltip'] = pn_strip_input(is_param_post_ml('tooltip'));
		
	update_option('telegram_settings', $data);

	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
				
	$form->answer_form($back_url);
	
}