<?php 
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_all_add_tapibot', 'def_adminpage_title_all_add_tapibot');
	function def_adminpage_title_all_add_tapibot() {
		global $db_data, $wpdb;
		
		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$db_data = '';
		
		if ($item_id) {
			$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibots WHERE id = '$item_id'");
			if (isset($db_data->id)) {
				$data_id = $db_data->id;
			}	
		}		
		
		if ($data_id) {
			return __('Edit', 'pn');
		} else {
			return __('Add', 'pn');
		}	
		
	}

	add_action('pn_adminpage_content_all_add_tapibot', 'def_pn_adminpage_content_all_add_tapibot');
	function def_pn_adminpage_content_all_add_tapibot() {
		global $db_data, $wpdb;

		$form = new PremiumForm();

		$data_id = intval(is_isset($db_data, 'id'));
		if ($data_id) {
			$title = __('Edit', 'pn');
		} else {
			$title = __('Add', 'pn');
		}

		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=all_tapibot'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=all_add_tapibot'),
				'title' => __('Add new', 'pn')
			);	
		}
		$form->back_menu($back_menu, $db_data);

		$options = array();
		$options['hidden_block'] = array(
			'view' => 'hidden_input',
			'name' => 'data_id',
			'default' => $data_id,
		);	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => $title,
			'submit' => __('Save','pn'),
		);
		$options['bot_title'] = array(
			'view' => 'inputbig',
			'title' => __('Title','pn'),
			'default' => is_isset($db_data, 'bot_title'),
			'name' => 'bot_title',
		);
		$options['bot_status'] = array(
			'view' => 'select',
			'title' => __('Status', 'pn'),
			'options' => array('1' => __('published', 'pn'), '0' => __('moderating', 'pn')),
			'default' => is_isset($db_data, 'bot_status'),
			'name' => 'bot_status',
		);
		$options['bot_logs'] = array(
			'view' => 'select',
			'title' => __('Logs', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => is_isset($db_data, 'bot_logs'),
			'name' => 'bot_logs',
		);
		$options['bot_parsmode'] = array(
			'view' => 'select',
			'title' => __('Parse mode', 'pn'),
			'options' => array('0' => 'Markdown', '1' => 'HTML'),
			'default' => is_isset($db_data, 'bot_parsmode'),
			'name' => 'bot_parsmode',
		);		
		$options['bot_title_line'] = array(
			'view' => 'h3',
			'title' => __('Api data', 'pn'),
			'submit' => __('Save', 'pn'),
		);
		$options['api_server'] = array(
			'view' => 'inputbig',
			'title' => __('Api server', 'pn'),
			'default' => is_isset($db_data, 'api_server'),
			'name' => 'api_server',
		);
		$options['api_login'] = array(
			'view' => 'inputbig',
			'title' => __('API login', 'pn'),
			'default' => is_isset($db_data, 'api_login'),
			'name' => 'api_login',
			'atts' => array('autocomplete' => 'off'),
		);
		$options['api_key'] = array(
			'view' => 'inputbig',
			'title' => __('API key', 'pn'),
			'default' => is_isset($db_data, 'api_key'),
			'name' => 'api_key',
			'atts' => array('autocomplete' => 'off'),
		);
		$options['api_version'] = array(
			'view' => 'select',
			'title' => __('Version', 'pn'),
			'options' => array('v1' => 'v1'),
			'default' => is_isset($db_data, 'api_version'),
			'name' => 'api_version',
		);		
		$options['api_lang'] = array(
			'view' => 'select',
			'title' => __('Type', 'pn'),
			'options' => array('ru_RU' => 'RU', 'en_US' => 'EN'),
			'default' => is_isset($db_data, 'api_lang'),
			'name' => 'api_lang',
		);
		$options['api_partner_id'] = array(
			'view' => 'inputbig',
			'title' => __('API partner id', 'pn'),
			'default' => is_isset($db_data, 'api_partner_id'),
			'name' => 'api_partner_id',
		);		
		if ($data_id) {
			$options['api_server_test'] = array(
				'view' => 'textfield',
				'title' => __('Test server', 'pn'),
				'default' => '<a href="' . pn_link('tapibot_testserver') . '&id=' . $data_id . '" class="button" target="_blank">' . __('Test', 'pn') . '</a>',
			);	
		}
		$options['tm_option_title'] = array(
			'view' => 'h3',
			'title' => __('Telegram settings', 'pn'),
			'submit' => __('Save' ,'pn'),
		);	
		
		if (!isset($db_data->id)) {
			
			$options['tm_option_warning'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => __('The settings will become available after the creation of the bot', 'pn'),
			);	
			
		} else {
			
			$options['bot_token'] = array(
				'view' => 'inputbig',
				'title' => __('Token', 'pn'),
				'default' => is_isset($db_data, 'bot_token'),
				'name' => 'bot_token',
				'atts' => array('autocomplete' => 'off'),
			);	
					
			$text = '
			<div>' . sprintf(__('<a href="%s" target="_blank">Instructions for registering bots</a>','pn'), 'https://premiumexchanger.com') . '</div>
			';
			$options['tm_help'] = array(
				'view' => 'help',
				'title' => __('How to create a Telegram bot?', 'pn'),
				'default' => $text,
			);
			$text = '
			<div><strong>'. sprintf(__('To register the webhook, follow <a href="%s" target="_blank">the link</a>', 'pn'), pn_link('tapibot_set') . '&id=' . $db_data->id) . '</strong></div>
			<div><strong>'. sprintf(__('To remove the webhook, follow <a href="%s" target="_blank">the link</a>', 'pn'), pn_link('tapibot_unset') . '&id=' . $db_data->id) . '</strong></div>
			';
			$options['tm_registerwebhook'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);
			
		}	
		
		$options['type_title_line'] = array(
			'view' => 'h3',
			'title' => __('Bot settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);
		$settings = pn_json_decode(is_isset($db_data, 'bot_settings'));
		
		$all_tags = array(
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
			'code' => array(
				'title' => 'copy',
				'start' => '<code>',
				'end' => '</code>',
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
		
		$now_tags = array();
		$tags = array_merge($all_tags, $now_tags);
		$options['welocome_text'] = array(
			'view' => 'editor',
			'title' => __('Text of first message from bot', 'pn'),
			'default' => is_isset($settings, 'welocome_text'),
			'tags' => $tags,
			'rows' => '10',
			'name' => 'welocome_text',
			'work' => 'text',
			'ml' => 1,
		);
		
		$now_tags = array();
		$tags = array_merge($all_tags, $now_tags);
		$options['error_api_text'] = array(
			'view' => 'editor',
			'title' => __('Api error text', 'pn'),
			'default' => is_isset($settings, 'error_api_text'),
			'tags' => $tags,
			'rows' => '10',
			'name' => 'error_api_text',
			'work' => 'text',
			'ml' => 1,
		);

		$r = 0;
		while ($r++ < 6) {
			$options['mbut_title' . $r] = array(
				'view' => 'inputbig',
				'title' => sprintf(__('Title menu button %s', 'pn'), $r),
				'default' => is_isset($settings, 'mbut_title' . $r),
				'name' => 'mbut_title' . $r,
				'ml' => 1,
			);		
			$now_tags = array();
			$tags = array_merge($all_tags, $now_tags);
			$options['mbut_text' . $r] = array(
				'view' => 'editor',
				'title' => sprintf(__('Text menu button %s', 'pn'), $r),
				'default' => is_isset($settings, 'mbut_text' . $r),
				'tags' => $tags,
				'rows' => '5',
				'name' => 'mbut_text' . $r,
				'work' => 'text',
				'ml' => 1,
			);		
		}
		
		$options['exch_button_text'] = array(
			'view' => 'inputbig',
			'title' => __('Exchange button text', 'pn'),
			'default' => is_isset($settings, 'exch_button_text'),
			'name' => 'exch_button_text',
			'ml' => 1,
		);		

		$options['setcurr_list'] = array(
			'view' => 'select',
			'title' => __('Display a list of currencies to select?', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => is_isset($settings, 'setcurr_list'),
			'name' => 'setcurr_list',
		);
			
		$now_tags = array();
		$tags = array_merge($all_tags, $now_tags);	
		$options['setcurr_give_text'] = array(
			'view' => 'editor',
			'title' => __('I give the text of the choice of currency', 'pn'),
			'default' => is_isset($settings, 'setcurr_give_text'),
			'tags' => $tags,
			'rows' => '10',
			'name' => 'setcurr_give_text',
			'work' => 'text',
			'ml' => 1,
		);

		$now_tags = array();
		$tags = array_merge($all_tags, $now_tags);
		$options['setcurr_get_text'] = array(
			'view' => 'editor',
			'title' => __('I get the currency selection text', 'pn'),
			'default' => is_isset($settings, 'setcurr_get_text'),
			'tags' => $tags,
			'rows' => '10',
			'name' => 'setcurr_get_text',
			'work' => 'text',
			'ml' => 1,
		);	

		$now_tags = array(
			'course_give' => array(
				'title' => __('Course give', 'pn'),
				'start' => '[course_give]',
			),
			'currency_give' => array(
				'title' => __('Currency give', 'pn'),
				'start' => '[currency_give]',
			),
			'course_get' => array(
				'title' => __('Course get', 'pn'),
				'start' => '[course_get]',
			),
			'currency_get' => array(
				'title' => __('Currency get', 'pn'),
				'start' => '[currency_get]',
			),			
		);
		$tags = array_merge($all_tags, $now_tags);
		$options['selectplace_text'] = array(
			'view' => 'editor',
			'title' => __('Exchange side selection text', 'pn'),
			'default' => is_isset($settings, 'selectplace_text'),
			'tags' => $tags,
			'rows' => '10',
			'name' => 'selectplace_text',
			'work' => 'text',
			'ml' => 1,
		);

		$now_tags = array(
			'course_give' => array(
				'title' => __('Course give', 'pn'),
				'start' => '[course_give]',
			),
			'currency_give' => array(
				'title' => __('Currency give', 'pn'),
				'start' => '[currency_give]',
			),
			'minmax_give' => array(
				'title' => __('Min. and max. give', 'pn'),
				'start' => '[minmax_give]',
			),			
			'course_get' => array(
				'title' => __('Course get', 'pn'),
				'start' => '[course_get]',
			),
			'currency_get' => array(
				'title' => __('Currency get', 'pn'),
				'start' => '[currency_get]',
			),
			'minmax_get' => array(
				'title' => __('Min. and max. get', 'pn'),
				'start' => '[minmax_get]',
			),			
		);
		$tags = array_merge($all_tags, $now_tags);
		$options['selplace1_text'] = array(
			'view' => 'editor',
			'title' => __('I give the text for entering the amount', 'pn'),
			'default' => is_isset($settings, 'selplace1_text'),
			'tags' => $tags,
			'rows' => '10',
			'name' => 'selplace1_text',
			'work' => 'text',
			'ml' => 1,
		);

		$now_tags = array(
			'course_give' => array(
				'title' => __('Course give', 'pn'),
				'start' => '[course_give]',
			),
			'currency_give' => array(
				'title' => __('Currency give', 'pn'),
				'start' => '[currency_give]',
			),
			'minmax_give' => array(
				'title' => __('Min. and max. give', 'pn'),
				'start' => '[minmax_give]',
			),			
			'course_get' => array(
				'title' => __('Course get', 'pn'),
				'start' => '[course_get]',
			),
			'currency_get' => array(
				'title' => __('Currency get', 'pn'),
				'start' => '[currency_get]',
			),
			'minmax_get' => array(
				'title' => __('Min. and max. get', 'pn'),
				'start' => '[minmax_get]',
			),			
		);
		$tags = array_merge($all_tags, $now_tags);
		$options['selplace2_text'] = array(
			'view' => 'editor',
			'title' => __('I get the text for entering the amount','pn'),
			'default' => is_isset($settings, 'selplace2_text'),
			'tags' => $tags,
			'rows' => '10',
			'name' => 'selplace2_text',
			'work' => 'text',
			'ml' => 1,
		);

		$options['memory_data'] = array(
			'view' => 'select',
			'title' => __('Remember user data?', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => is_isset($settings, 'memory_data'),
			'name' => 'memory_data',
		);
		$options['memory_button_text'] = array(
			'view' => 'inputbig',
			'title' => __('Button text for deleting user data', 'pn'),
			'default' => is_isset($settings, 'memory_button_text'),
			'name' => 'memory_button_text',
			'ml' => 1,
		);
		$now_tags = array();
		$tags = array_merge($all_tags, $now_tags);
		$options['memory_ok_text'] = array(
			'view' => 'editor',
			'title' => __('Successful deletion text', 'pn'),
			'default' => is_isset($settings, 'memory_ok_text'),
			'tags' => $tags,
			'rows' => '10',
			'name' => 'memory_ok_text',
			'work' => 'text',
			'ml' => 1,
		);

		$now_tags = array(
			'pay_text' => array(
				'title' => __('Payment text', 'pn'),
				'start' => '[pay_text]',
			),
			'instruction' => array(
				'title' => __('Instructions from the application', 'pn'),
				'start' => '[instruction]',
			),
			'bid_url' => array(
				'title' => __('Bid url', 'pn'),
				'start' => '[bid_url]',
			),
			'bid_id' => array(
				'title' => __('Bid id', 'pn'),
				'start' => '[bid_id]',
			),
			'bid_hash' => array(
				'title' => __('Bid hash', 'pn'),
				'start' => '[bid_hash]',
			),
			'bid_status' => array(
				'title' => __('Bid status', 'pn'),
				'start' => '[bid_status]',
			),
			'bid_status_title' => array(
				'title' => __('Bid status title', 'pn'),
				'start' => '[bid_status_title]',
			),
			'pay_amount' => array(
				'title' => __('Payment amount', 'pn'),
				'start' => '[pay_amount]',
			),
			'address' => array(
				'title' => __('Payment address or account', 'pn'),
				'start' => '[address]',
			),
			'dest_tag' => array(
				'title' => __('Tag', 'pn'),
				'start' => '[dest_tag]',
			),
			'psys_give' => array(
				'title' => __('Payment system give', 'pn'),
				'start' => '[psys_give]',
			),
			'psys_get' => array(
				'title' => __('Payment system get', 'pn'),
				'start' => '[psys_get]',
			),
			'currency_code_give' => array(
				'title' => __('Currency code give', 'pn'),
				'start' => '[currency_code_give]',
			),
			'currency_code_get' => array(
				'title' => __('Currency code get', 'pn'),
				'start' => '[currency_code_get]',
			),
			'amount_give' => array(
				'title' => __('Amount give', 'pn'),
				'start' => '[amount_give]',
			),
			'amount_get' => array(
				'title' => __('Amount get', 'pn'),
				'start' => '[amount_get]',
			),
			'course_give' => array(
				'title' => __('Course give', 'pn'),
				'start' => '[course_give]',
			),
			'course_get' => array(
				'title' => __('Course get', 'pn'),
				'start' => '[course_get]',
			),			
		);
		$tags = array_merge($all_tags, $now_tags);
		$options['bid_text'] = array(
			'view' => 'editor',
			'title' => __('Application information text', 'pn'),
			'default' => is_isset($settings, 'bid_text'),
			'tags' => $tags,
			'rows' => '10',
			'name' => 'bid_text',
			'work' => 'text',
			'ml' => 1,
		);
		$options['qrcode'] = array(
			'view' => 'select',
			'title' => __('QR-code', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => is_isset($settings, 'qrcode'),
			'name' => 'qrcode',
		);
		
		$params_form = array(
			'filter' => 'all_tapibot_addform',
			'data' => $db_data,
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
				
	}	
	
}

add_action('premium_action_all_add_tapibot', 'def_premium_action_all_add_tapibot');
function def_premium_action_all_add_tapibot() {
	global $wpdb;

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_tapibot'));
			
	$data_id = intval(is_param_post('data_id'));
		
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibots WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}		
		
	$array = array();
	$array['bot_title'] = pn_strip_input(is_param_post('bot_title'));
	if (strlen($array['bot_title']) < 1) {
		$form->error_form(__('Error! Title not entered', 'pn'));
	}	
	$array['bot_status'] = intval(is_param_post('bot_status'));
	$array['bot_logs'] = intval(is_param_post('bot_logs'));
	$array['bot_parsmode'] = intval(is_param_post('bot_parsmode'));
	
	$array['api_server'] = str_replace(array('https://', 'http://', '/', '\\'), '', pn_strip_input(is_param_post('api_server')));
	if (strlen($array['api_server']) < 1) {
		$form->error_form(__('Error! Api server not entered', 'pn'));
	}

	$array['api_version'] = pn_strip_input(is_param_post('api_version'));
	$array['api_lang'] = pn_strip_input(is_param_post('api_lang'));
	$array['api_partner_id'] = pn_strip_input(is_param_post('api_partner_id'));
	
	$array['api_login'] = pn_strip_input(is_param_post('api_login'));
	if (strlen($array['api_login']) < 1) {
		$form->error_form(__('Error! API login not entered', 'pn'));
	}
	
	$array['api_key'] = pn_strip_input(is_param_post('api_key'));
	if (strlen($array['api_key']) < 1) {
		$form->error_form(__('Error! API key not entered', 'pn'));
	}	
	
	$array['bot_token'] = pn_strip_input(is_param_post('bot_token'));
	
	$bs = array();
	$bs['welocome_text'] = pn_strip_text(is_param_post_ml('welocome_text'));
	$bs['exch_button_text'] = pn_strip_input(is_param_post_ml('exch_button_text'));
	$bs['error_api_text'] = pn_strip_text(is_param_post_ml('error_api_text'));
	
	$r = 0;
	while ($r++ < 6) {
		
		$bs['mbut_title' . $r] = pn_strip_input(is_param_post_ml('mbut_title' . $r));
		$bs['mbut_text' . $r] = pn_strip_text(is_param_post_ml('mbut_text' . $r));
				
	}	
	
	$bs['setcurr_list'] = intval(is_param_post('setcurr_list'));
	
	$bs['setcurr_give_text'] = pn_strip_text(is_param_post_ml('setcurr_give_text'));
	$bs['setcurr_get_text'] = pn_strip_text(is_param_post_ml('setcurr_get_text'));
	
	$bs['selectplace_text'] = pn_strip_text(is_param_post_ml('selectplace_text'));
	$bs['selplace1_text'] = pn_strip_text(is_param_post_ml('selplace1_text'));
	$bs['selplace2_text'] = pn_strip_text(is_param_post_ml('selplace2_text'));
	
	$bs['bid_text'] = pn_strip_text(is_param_post_ml('bid_text'));
	$bs['qrcode'] = intval(is_param_post('qrcode'));
	
	$bs['memory_data'] = intval(is_param_post('memory_data'));
	$bs['memory_button_text'] = pn_strip_input(is_param_post_ml('memory_button_text'));
	$bs['memory_ok_text'] = pn_strip_text(is_param_post_ml('memory_ok_text'));
	
	$array['bot_settings'] = pn_json_encode($bs);
	$array = apply_filters('all_tapibot_addform_post', $array, $last_data);
				
	if ($data_id) {
		$res = apply_filters('item_tapibot_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {
			$result = $wpdb->update($wpdb->prefix . 'tapibots', $array, array('id' => $data_id));
			do_action('item_tapibot_edit', $data_id, $array, $last_data, $result);
			$res_errors = _debug_table_from_db($result, 'tapibots', $array);
			_display_db_table_error($form, $res_errors);
		} else { $form->error_form(is_isset($res, 'error')); }
	} else {
		$res = apply_filters('item_tapibot_add_before', pn_ind(), $array);
		if ($res['ind']) {
			$result = $wpdb->insert($wpdb->prefix . 'tapibots', $array);
			$data_id = $wpdb->insert_id;
			if ($result) {
				do_action('item_tapibot_add', $data_id, $array);
			} else {
				$res_errors = _debug_table_from_db($result, 'tapibots', $array);
				_display_db_table_error($form, $res_errors);					
			}
		} else { $form->error_form(is_isset($res, 'error')); }		
	}
	
	$callback_secret = trim(get_option('tapibots_callback_secret'));
	if (!$callback_secret) {
		$callback_secret = get_random_password(16);
		update_option('tapibots_callback_secret', $callback_secret);
	}	

	$url = admin_url('admin.php?page=all_add_tapibot&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
}	

add_action('premium_action_tapibot_set','def_premium_action_tapibot_set');
function def_premium_action_tapibot_set() {
	global $wpdb, $premiumbox;	

	$form = new PremiumForm();
	$form->send_header();

	pn_only_caps(array('administrator', 'pn_tapibot'));
			
	$bot_id = intval(is_param_get('id'));
	if ($bot_id > 0) {
		$bot = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibots WHERE id = '$bot_id'");
		if (isset($bot->id)) {
			
			$bot_lang = get_lang_key($bot->api_lang);
			
			$token = pn_strip_input(is_isset($bot, 'bot_token'));
			$bot_logs = intval(is_isset($bot, 'bot_logs'));
			$bot_parsmode = intval(is_isset($bot, 'bot_parsmode'));
			
			if (!$token) {
				$form->error_form(__('Error! You have not saved bot token in settings', 'pn'));
			} 
			
			$secret = trim(get_option('tapibots_secret'));
			if (!$secret) {
				$secret = get_random_password(16);
				update_option('tapibots_secret', $secret);
			}			
			
			$webhook_url = get_api_link('tapibot', 'v1', 'webhook') . '?id=' . $bot->id . '&sk=' . md5($secret) . '&lang=' . $bot_lang;
			$class = new TAPIBOT_CLASS($token, $bot->id, $bot_logs, $bot_parsmode);	
			$res = $class->set_webhook($webhook_url);		
			if (!isset($res['result'])) {
				$form->error_form(__('Error! API error', 'pn'));
			}			
		}
	}

	$back_url = admin_url('admin.php?page=all_add_tapibot&item_id=' . $bot_id . '&reply=true');			
	$form->answer_form($back_url);
	
}

add_action('premium_action_tapibot_unset', 'def_premium_action_tapibot_unset');
function def_premium_action_tapibot_unset() {
	global $wpdb, $premiumbox;	

	$form = new PremiumForm();
	$form->send_header();

	pn_only_caps(array('administrator','pn_tapibot'));
		
	$bot_id = intval(is_param_get('id'));
	if ($bot_id > 0) {
		$bot = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibots WHERE id = '$bot_id'");
		if (isset($bot->id)) {
			
			$token = pn_strip_input(is_isset($bot, 'bot_token'));
			$bot_logs = intval(is_isset($bot, 'bot_logs'));
			$bot_parsmode = intval(is_isset($bot, 'bot_parsmode'));
			
			if (!$token) {
				$form->error_form(__('Error! You have not saved bot token in settings', 'pn'));
			}		

			$class = new TAPIBOT_CLASS($token, $bot->id, $bot_logs, $bot_parsmode);	
			$res = $class->unset_webhook();			
			if (!isset($res['result'])) {
				$form->error_form(__('Error! API error', 'pn'));
			}
			
		}
	}

	$back_url = admin_url('admin.php?page=all_add_tapibot&item_id='. $bot_id .'&reply=true');			
	$form->answer_form($back_url);		
	
}