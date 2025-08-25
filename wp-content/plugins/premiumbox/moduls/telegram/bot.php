<?php
if (!defined('ABSPATH')) { exit(); }

add_action('pn_api_page', 'telegram_api_page', 20, 3); 
function telegram_api_page($module, $version, $endpoint) {
	global $wpdb, $premiumbox;	

	if ('telegram' == $module and 'v1' == $version) {

		status_header(200);

		$json = array(
			'error' => 1,
			'error_text' => 'Api disabled',
		);
		
		if (!$premiumbox->is_up_mode()) {
			
			$json = array(
				'error' => 3,
				'error_text' => 'Method not supported',
			);			
			
			if ('webhook' == $endpoint) {
				
				$json = array(
					'error' => 0,
					'error_text' => '',
				);				

				$tdata = get_option('telegram_settings');
				if (!is_array($tdata)) { $tdata = array(); }
				$token = pn_strip_input(is_isset($tdata, 'token'));
				
				$bots = intval(is_isset($tdata, 'bots'));
				$nologin_work = intval(is_isset($tdata, 'nologin'));
				
				$class = new TelegramBot($token, is_isset($tdata, 'bot_logs'), is_isset($tdata, 'answer_logs'));
				
				if (!$token) {
					
					$class->create_log('no token', 1);
					$json = array(
						'error' => 1,
						'error_text' => 'no token',
					);
					echo pn_json_encode($json);
					exit;						
				}				
				
				$telegram_token = pn_strip_input(is_param_get('telegram_token'));
				if ($telegram_token != $token) {
					
					$class->create_log('invalid token', 1);
					$json = array(
						'error' => 1,
						'error_text' => 'no token',
					);
					echo pn_json_encode($json);
					exit;					
				}				
				
				$request = @file_get_contents('php://input');
				$res = @json_decode($request, true);
				
				$class->create_log($res, 1);
				
				$now = array();
				if (isset($res['callback_query'], $res['callback_query']['from'])) {
					
					if (isset($res['callback_query']['from']['id'])) {
						$now['chat_id'] = $res['callback_query']['from']['id'];
					}
					
					if (isset($res['callback_query']['from']['is_bot'])) {
						$now['is_bot'] = $res['callback_query']['from']['is_bot'];
					}
					
					if (isset($res['callback_query']['from']['first_name'])) {
						$now['first_name'] = $res['callback_query']['from']['first_name'];
					}
					
					if (isset($res['callback_query']['from']['language_code'])) {
						$now['language_code'] = $res['callback_query']['from']['language_code'];
					}
					
					if (isset($res['callback_query']['from']['username'])) {
						$now['username'] = $res['callback_query']['from']['username'];
					}
					
					if (isset($res['callback_query']['data'])) {
						$now['text'] = $res['callback_query']['data'];
					}
					
					$now['callback'] = 1;
					
				} elseif (isset($res['message'], $res['message']['from'])) {
					
					if (isset($res['message']['from']['id'])) {
						$now['chat_id'] = $res['message']['from']['id'];
					}
					
					if (isset($res['message']['from']['is_bot'])) {	
						$now['is_bot'] = $res['message']['from']['is_bot'];
					}
					
					if (isset($res['message']['from']['first_name'])) {	
						$now['first_name'] = $res['message']['from']['first_name'];
					}
					
					if (isset($res['message']['from']['language_code'])) {	
						$now['language_code'] = $res['message']['from']['language_code'];
					}
					
					if (isset($res['message']['from']['username'])) {	
						$now['username'] = $res['message']['from']['username'];
					}
					
					if (isset($res['message']['text'])) {	
						$now['text'] = $res['message']['text'];
					}
					
					$now['callback'] = 0;
					
				}
			
				$callback = intval(is_isset($now, 'callback'));
				
				$is_bot = intval(is_isset($now, 'is_bot'));
				if (1 == $is_bot and 0 == $bots) {
					
					$json = array(
						'error' => 1,
						'error_text' => 'your bot',
					);					
					echo pn_json_encode($json);
					exit;
				}
				
				$chat_id = pn_strip_input(is_isset($now, 'chat_id'));
				if (strlen($chat_id) < 1) {
					
					$json = array(
						'error' => 1,
						'error_text' => 'no chat id',
					);					
					echo pn_json_encode($json);
					exit;					
				}				
				
				$lang = mb_strtolower(pn_strip_input(is_isset($now, 'language_code')));
				if ($lang) {
					$lang = $lang . '_' . mb_strtoupper($lang);
				} 
				
				$first_name = pn_strip_input(is_isset($now, 'first_name'));
				
				$login = mb_strtolower(pn_strip_input(is_isset($now, 'username')));
				
				$command = trim(is_isset($now, 'text'));
				
				$chat = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "telegram WHERE telegram_chat_id = '$chat_id'");
				if (!isset($chat->id)) {
					
					$arr = array();
					$arr['telegram_chat_id'] = $chat_id;
					$arr['create_date'] = current_time('mysql');
					$wpdb->insert($wpdb->prefix . "telegram", $arr);
					$arr['id'] = $wpdb->insert_id;
					$chat = (object)$arr;
					
				}
				if (isset($chat->id)) {
				
					$data = @unserialize(is_isset($chat,'data'));
					
					$arr = array();
					$up = 0;
					
					$telegram_login = pn_strip_input(is_isset($chat, 'telegram_login'));
					
					if ('/start' == $command) { 
						$start = intval(is_isset($data, 'command_start'));
						if (1 != $start) {
							$up = 1;
							
							$welcome_text = pn_strip_text(ctv_ml(is_isset($tdata, 'welocome_text'), $lang));
							if (strlen($welcome_text) < 1) { $welcome_text = 'Hi, chat ID: [chat_id]'; }
							$welcome_text = str_replace('[first_name]', $first_name, $welcome_text);
							$welcome_text = str_replace('[chat_id]', $chat_id, $welcome_text);
							
							$class->send('text', $chat_id, $welcome_text);
							$data['command_start'] = 1;
						}
					}
					
					if (0 == $nologin_work) {
						
						if (!$login) {
							$no_login = intval(is_isset($data, 'no_login'));
							if (1 != $no_login) {
								$up = 1;
								
								$nologin_text = pn_strip_text(ctv_ml(is_isset($tdata, 'nologin_text'), $lang));
								if (strlen($nologin_text) < 1) { $nologin_text = 'please, add your login in telegram'; }
								$nologin_text = str_replace('[first_name]', $first_name, $nologin_text);
					
								$class->send('text', $chat_id, $nologin_text);
								$data['no_login'] = 1;
							}
						} 
						
					} else {
						
						if (!$login) {
							$login = $chat_id;
						}
		
					}
					
					if ($login) {
						if ($login != $telegram_login) {
							$up = 1;
							
							$chat = pn_object_replace($chat, array('telegram_login' => $login));
							$arr['telegram_login'] = $login;
						
							$yeslogin_text = pn_strip_text(ctv_ml(is_isset($tdata, 'yeslogin_text'), $lang));
							if (strlen($yeslogin_text) > 0) { 
								$yeslogin_text = str_replace('[first_name]', $first_name, $yeslogin_text);
								$yeslogin_text = str_replace('[login]', $login, $yeslogin_text);
								$class->send('text', $chat_id, $yeslogin_text);	
							}					
						}
					}				
					
					$arr['data'] = @serialize($data);
					
					if (1 == $up) {
						$wpdb->update($wpdb->prefix . "telegram", $arr, array('id' => $chat->id));	
					}
				}
			}
		}
		
		echo pn_json_encode($json);
		exit;		
	}
}