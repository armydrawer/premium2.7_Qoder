<?php 
if (!defined('ABSPATH')) { exit(); }

function tapibot_command($apibot_id, $command, $api_server, $api_version, $api_lang, $api_login, $api_key, $api_partner_id, $data = array()) {
	
	$res = array();
	$api_server = trim($api_server);
	$api_version = trim($api_version);
	$api_lang = trim($api_lang);
	$api_login = trim($api_login);
	$api_key = trim($api_key);
	$api_partner_id = trim($api_partner_id);
	if (!is_array($data)) { $data = array(); }
	$apibot_id = intval($apibot_id);
	
	if (!$api_server) {
		return array('error' => 'Empty API Server');
	}
	
	if ('v1' == $api_version) {
		
		$post = array();
		
		$api_partner_id = intval($api_partner_id);
		if ($api_partner_id) {
			
			$post['partner_id'] = $api_partner_id;
			
		}

		$headers = array(
			'api-login: ' . $api_login,
			'api-key: ' . $api_key,
			'api-lang: ' . $api_lang,
		);		
		
		if ('test' == $command) {
		
			$get = array();
			$url = 'https://' . $api_server . '/api/userapi/v1/test/?' . http_build_query($get);
			$res = tapibot_curl($apibot_id, $url, $headers, $post);
			
		} elseif ('list_currency' == $command) {
			
			$get = array();
			if (isset($data['id'])) {
				$post['currency_id_give'] = $data['id'];
			}
			
			$url = 'https://' . $api_server . '/api/userapi/v1/get_direction_currencies/?' . http_build_query($get);
			$res = tapibot_curl($apibot_id, $url, $headers, $post);
			
		} elseif ('get_direction' == $command) {
			
			$get = array();
			if (isset($data['currency_id_give'])) {
				$post['currency_id_give'] = $data['currency_id_give'];
			}
			if (isset($data['currency_id_get'])) {
				$post['currency_id_get'] = $data['currency_id_get'];
			}			
			
			$url = 'https://' . $api_server . '/api/userapi/v1/get_direction/?' . http_build_query($get);
			$res = tapibot_curl($apibot_id, $url, $headers, $post);			
			
		} elseif ('get_calc' == $command) {
			
			$get = array();
			foreach ($data as $data_k => $data_v) {
				$post[$data_k] = $data_v;
			}				
			
			$url = 'https://' . $api_server . '/api/userapi/v1/get_calc/?' . http_build_query($get);
			$res = tapibot_curl($apibot_id, $url, $headers, $post);			
		
		} elseif ('create_bid' == $command) {
			
			$get = array();
			foreach ($data as $data_k => $data_v) {
				$post[$data_k] = $data_v;
			}				
			
			$url = 'https://' . $api_server . '/api/userapi/v1/create_bid/?' . http_build_query($get);
			$res = tapibot_curl($apibot_id, $url, $headers, $post);
		
		} elseif ('bid_info' == $command) {
			
			$get = array();
			foreach ($data as $data_k => $data_v) {
				$post[$data_k] = $data_v;
			}				
			
			$url = 'https://' . $api_server . '/api/userapi/v1/bid_info/?' . http_build_query($get);
			$res = tapibot_curl($apibot_id, $url, $headers, $post);		
		
		} elseif ('cancel_bid' == $command) {
			
			$get = array();
			foreach ($data as $data_k => $data_v) {
				$post[$data_k] = $data_v;
			}				
			
			$url = 'https://' . $api_server . '/api/userapi/v1/cancel_bid/?' . http_build_query($get);
			$res = tapibot_curl($apibot_id, $url, $headers, $post);

		} elseif ('pay_bid' == $command) {
			
			$get = array();
			foreach ($data as $data_k => $data_v) {
				$post[$data_k] = $data_v;
			}				
			
			$url = 'https://' . $api_server . '/api/userapi/v1/pay_bid/?' . http_build_query($get);
			$res = tapibot_curl($apibot_id, $url, $headers, $post);			
		
		}
		
	}
	
	return $res;
}

function tapibot_curl($apibot_id, $url, $headers, $post) {
	
	$res = array('error' => 'CURL init error');	
	if ($ch = curl_init()) {
							
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'PremiumExchanger/2.7.0');
		curl_setopt($ch, CURLOPT_URL, $url);				
					
		if (count($post) > 0) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		}
					
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 240);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 240);
		curl_setopt($ch, CURLOPT_ENCODING, '');
							
		$err  = curl_errno($ch);
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);			
							
		curl_close($ch);
					
		$res = @json_decode($result, true);
		
		if ($apibot_id > 0) {
			tapibot_create_log($apibot_id, $url, http_build_query($post), '', $headers, $result);
		}
		
		if (!is_array($res)) { $res = array('error' => 'the server is not responding'); }
							
	}
	
	return $res;
}

function tapibot_create_log($apibot_id, $log_url, $log_post, $log_json, $log_headers, $log_answer) {
	global $wpdb;
	
	$arr = array();
	$arr['tapibot_id'] = pn_strip_input($apibot_id);
	$arr['create_date'] = current_time('mysql');
	$arr['log_ip'] = pn_real_ip();
	$arr['log_url'] = pn_strip_input($log_url);
	$arr['log_post'] = pn_strip_input(print_r($log_post, true));			
	$arr['log_json'] = pn_strip_input(print_r($log_json, true));
	$arr['log_headers'] = pn_strip_input(print_r($log_headers, true));
	$arr['log_answer'] = pn_strip_input(print_r($log_answer, true));
	$wpdb->insert($wpdb->prefix . "tapibot_logs", $arr);
	
}

function tapibot_send($bot, $chat_data, $uniq_id, $settings, $text, $in = '', $image = '') {

	$exch_button_text = pn_strip_input(ctv_ml(is_isset($settings,'exch_button_text')));
	if (strlen($exch_button_text) < 1) { $exch_button_text = __('Start a new exchange', 'pn'); }

	$out = array ();

	$r = 0;
	$s = 0;
	$line = 0;
	while ($r++ < 6) {
		$mbut_title = pn_strip_input(ctv_ml(is_isset($settings, 'mbut_title' . $r)));
		if (strlen($mbut_title) > 0) { $s++;
			$out[$line][]['text'] = $mbut_title;
			if (3 == $s) {
				$s = 0;
				$line++;
			}
		}
	}
	
	if ($s > 0) {
		$line++;
	}
	$out[$line][]['text'] = $exch_button_text;
	
	$memory_data = intval(is_isset($settings, 'memory_data'));
	if ($memory_data) {
		$memory_button_text = pn_strip_input(ctv_ml(is_isset($settings, 'memory_button_text')));
		if (strlen($memory_button_text) < 1) {
			$memory_button_text = __('Delete saved data', 'pn');
		}
		$out[$line][]['text'] = $memory_button_text;
	}

	$image = trim($image);

	$bot_logs = intval(is_isset($bot, 'bot_logs'));
	$bot_parsmode = intval(is_isset($bot, 'bot_parsmode'));
	$token = pn_strip_input(is_isset($bot, 'bot_token'));
	
	$first_name = trim(is_isset($chat_data,'first_name'));
	if (strlen($first_name) < 1) { $first_name = 'unknown'; }
	
	$text = str_replace('[first_name]', $first_name, $text);
	$text = str_replace('[chat_id]', $uniq_id, $text);
	
	$class = new TAPIBOT_CLASS($token, $bot->id, $bot_logs, $bot_parsmode);
	$class->send($uniq_id, $text, 0, $in, $out, $image);
	
}

function tapibot_return_word1($word) {
	
	$replace = array(
		'q' => 'й', 
		'w' => 'ц', 
		'e' => 'у', 
		'r' => 'к', 
		't' => 'е',
		'y' => 'н', 
		'u' => 'г', 
		'i' => 'ш', 
		'o' => 'щ', 
		'p' => 'з',
		'[' => 'х', 
		']' => 'ъ', 
		'a' => 'ф', 
		's' => 'ы', 
		'd' => 'в', 
		'f' => 'а', 
		'g' => 'п', 
		'h' => 'р', 
		'j' => 'о', 
		'k' => 'л', 
		'l' => 'д', 
		';' => 'ж', 
		"'" => 'э', 
		'z' => 'я', 
		'x' => 'ч', 
		'c' => 'с', 
		'v' => 'м', 
		'b' => 'и', 
		'n' => 'т', 
		'm' => 'ь', 
		',' => 'б', 
		'.' => 'ю', 
		'/' => '.', 		
	);
	
	return strtr($word, $replace);	
}

function tapibot_return_word2($word) {
	
	$replace = array(
		'й' => 'q', 
		'ц' => 'w',  
		'у' => 'e',  
		'к' => 'r', 
		'е' => 't', 
		'н' => 'y',  
		'г' => 'u',  
		'ш' => 'i',  
		'щ' => 'o',  
		'з' => 'p', 
		'х' => '[',  
		'ъ' => ']',  
		'ф' => 'a',  
		'ы' => 's',  
		'в' => 'd',  
		'а' => 'f',  
		'п' => 'g',  
		'р' => 'h',  
		'о' => 'j',  
		'л' => 'k',  
		'д' => 'l',  
		'ж' => ';',  
		'э' => "'",  
		'я' => 'z',  
		'ч' => 'x',  
		'с' => 'c',  
		'м' => 'v',  
		'и' => 'b',  
		'т' => 'n',  
		'ь' => 'm',  
		'б' => ',',  
		'ю' => '.',  
		'.' => '/',  		
	);
	
	return strtr($word, $replace);	
}	

function tapibot_chat_data($data) {

	$chat_data = array(
		'command' => '',
		'uniq_id' => '0',
	);

	if (isset($data['callback_query'], $data['callback_query']['from'])) {
		if (isset($data['callback_query']['from']['id'])) {
			$chat_data['uniq_id'] = $data['callback_query']['from']['id'];
		}
		if (isset($data['callback_query']['from']['is_bot'])) {
			$chat_data['is_bot'] = $data['callback_query']['from']['is_bot'];
		}
		if (isset($data['callback_query']['from']['first_name'])) {
			$chat_data['first_name'] = $data['callback_query']['from']['first_name'];
		}
		if (isset($data['callback_query']['from']['language_code'])) {
			$chat_data['language_code'] = $data['callback_query']['from']['language_code'];
		}
		if (isset($data['callback_query']['from']['username'])) {
			$chat_data['username'] = $data['callback_query']['from']['username'];
		}
		if (isset($data['callback_query']['data'])) {
			$chat_data['command'] = $data['callback_query']['data'];
		}
	} elseif (isset($data['message'], $data['message']['from'])) {			
		if (isset($data['message']['from']['id'])) {
			$chat_data['uniq_id'] = $data['message']['from']['id'];
		}
		if (isset($data['message']['from']['is_bot'])) {	
			$chat_data['is_bot'] = $data['message']['from']['is_bot'];
		}
		if (isset($data['message']['from']['first_name'])) {	
			$chat_data['first_name'] = $data['message']['from']['first_name'];
		}
		if (isset($data['message']['from']['language_code'])) {	
			$chat_data['language_code'] = $data['message']['from']['language_code'];
		}
		if (isset($data['message']['from']['username'])) {	
			$chat_data['username'] = $data['message']['from']['username'];
		}
		if (isset($data['message']['text'])) {	
			$chat_data['command'] = $data['message']['text'];
		}
	}
	
	return $chat_data;
}	

function tapibot_qr($text, $width, $height) {
	
	return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $width . 'x' . $height . '&data=' . urlencode($text);
}
	
function tapibot_check_bidstatus($bid_data, $bid_id, $bot, $log_bot_id, $settings, $uniq_id) {
						
	$error_api_text = pn_strip_text(ctv_ml(is_isset($settings,'error_api_text')));
	if (strlen($error_api_text) < 1) {
		$error_api_text = __('An unexpected error has occurred. Please try again later.','pn');
	}
	
	$qrcode = intval(is_isset($settings, 'qrcode'));
		
	if (!is_array($bid_data)) {
		$apidata = tapibot_command($log_bot_id, 'bid_info', $bot->api_server, $bot->api_version, $bot->api_lang, $bot->api_login, $bot->api_key, $bot->api_partner_id, array('id' => $bid_id));
		if (isset($apidata['error'], $apidata['data']) and is_array($apidata['data'])) {
			if (0 == $apidata['error']) {
				$bid_data = $apidata['data'];
			} else {
				tapibot_send($bot, '', $uniq_id, $settings, $apidata['error_text']);	
			}
		} else {
			tapibot_send($bot, '', $uniq_id, $settings, $error_api_text);
		}											
	}
	if (is_array($bid_data)) {	

		$api_actions = is_isset($bid_data, 'api_actions');
		$type = trim(is_isset($api_actions, 'type'));
		$cancel_link = trim(is_isset($api_actions, 'cancel'));
		$pay_link = trim(is_isset($api_actions, 'pay'));
		$instruction = '';
		if (isset($api_actions['instruction'])) {
			$instruction = $bid_data['api_actions']['instruction'];
		}
		$pay_amount = is_sum(is_isset($api_actions, 'pay_amount'));
		$address = pn_strip_input(is_isset($api_actions, 'address'));	
		$dest_tag = pn_strip_input(is_isset($api_actions, 'dest_tag'));		
		
		$bid_url = pn_strip_input(is_isset($bid_data, 'url'));
		$bid_id = pn_strip_input(is_isset($bid_data, 'id'));
		$bid_hash = pn_strip_input(is_isset($bid_data, 'hash'));
		$bid_status = pn_strip_input(is_isset($bid_data, 'status'));
		$bid_status_title = pn_strip_input(is_isset($bid_data, 'status_title'));
		
		$bid_psys_give = pn_strip_input(is_isset($bid_data, 'psys_give'));
		$bid_psys_get = pn_strip_input(is_isset($bid_data, 'psys_get'));
		$bid_currency_code_give = pn_strip_input(is_isset($bid_data, 'currency_code_give'));
		$bid_currency_code_get = pn_strip_input(is_isset($bid_data, 'currency_code_get'));
		$bid_amount_give = pn_strip_input(is_isset($bid_data, 'amount_give'));
		$bid_amount_get = pn_strip_input(is_isset($bid_data, 'amount_get'));
		$bid_course_give = pn_strip_input(is_isset($bid_data, 'course_give'));
		$bid_course_get = pn_strip_input(is_isset($bid_data, 'course_get'));				
		
		$text = __('Exchange bid', 'pn') . ': ' . $bid_id;
		$text .= "\n" . __('Status', 'pn') . ': ' . $bid_status_title;
		$text .= "\n" . sprintf(__('Exchange rate %1s %2s = %3s %4s.', 'pn'), $bid_course_give, $bid_psys_give . ' ' . $bid_currency_code_give, $bid_course_get, $bid_psys_get . ' ' . $bid_currency_code_get);
		$text .= "\n" . sprintf(__('Your give %1s %2s', 'pn'), $bid_amount_give, $bid_psys_give . ' ' . $bid_currency_code_give);											
		$text .= "\n" . sprintf(__('Your get %1s %2s', 'pn'), $bid_amount_get, $bid_psys_get . ' ' . $bid_currency_code_get);																									
																						
		if (strlen($instruction) > 0) {
			$text .= "\n" . $instruction;
		}										

		$qr_arr = array();

		$pay_text = '';

		$in = array();										
		if ('finished' != $type) {
			
			$pay_text .= __('Must pay', 'pn') . ': "<code>' . $pay_amount . '</code>" ' . $bid_psys_give . ' ' . $bid_currency_code_give;
													
			$pay_link_title = __('Paid', 'pn');
			if ('form' == $type or 'link' == $type or 'coupon' == $type) {
				$pay_link_title = __('Go to the payment', 'pn');													
			} elseif ($type == 'mypaid' or $type == 'myaction') {
				if (strlen($address) > 0) {
					$pay_text .= "\n" . __('Payment account', 'pn') . ': "<code>' . $address . '</code>"';
				}
				if (strlen($dest_tag) > 0) {
					$pay_text .= ' (<code>' . $dest_tag . '</code>)';					
				}				
			} elseif ($type == 'address') {	
				$pay_link_title = __('Go to the payment', 'pn');	
				if (strlen($address) > 0) {
					$pay_text .= "\n" . __('Payment address', 'pn') . ': "<code>' . $address . '</code>"';
					$qr_arr[] = array(
						'title' => __('Payment address', 'pn'),
						'val' => $address,
					);					
				}													
				if (strlen($dest_tag) > 0) {
					$pay_text .= "\n" . __('Tag', 'pn') . ': "<code>' . $dest_tag . '</code>"';
					$qr_arr[] = array(
						'title' => __('Tag', 'pn'),
						'val' => $dest_tag,
					);					
				}
			} 
													
			if ('api' == $cancel_link) {
				$in[] = array(
					array (
						'text' => __('Cancel a order', 'pn'),
						'callback_data' => '/cancelbid_' . $bid_id,
					),
				);													
			}
			if ('disabled' != $pay_link and strlen($pay_link) > 0) {
				if ('api' == $pay_link) {
					$in[] = array(
						array (
							'text' => __('Paid', 'pn'),
							'callback_data' => '/paidbid_' . $bid_id,
						),
					);													
				} else {
					$in[] = array(
						array (
							'text' => $pay_link_title,
							'url' => $pay_link,
						),
					);														
				}
			}
			
		} 
		
		if (strlen($pay_text) > 0) {
			$text .= "\n" . $pay_text;
		}
		
		$bid_text = pn_strip_text(ctv_ml(is_isset($settings, 'bid_text')));
		if (strlen($bid_text) < 1) {
			$bid_text = $text;
		}
		$bid_text = str_replace('[pay_text]', $pay_text, $bid_text);
		$bid_text = str_replace('[instruction]', $instruction, $bid_text);
		$bid_text = str_replace('[bid_url]', $bid_url, $bid_text);
		$bid_text = str_replace('[bid_id]', $bid_id, $bid_text);
		$bid_text = str_replace('[bid_hash]', $bid_hash, $bid_text);
		$bid_text = str_replace('[bid_status]', $bid_status, $bid_text);
		$bid_text = str_replace('[bid_status_title]', $bid_status_title, $bid_text);
		$bid_text = str_replace('[pay_amount]', $pay_amount, $bid_text);
		$bid_text = str_replace('[address]', $address, $bid_text);
		$bid_text = str_replace('[dest_tag]', $dest_tag, $bid_text);
		$bid_text = str_replace('[psys_give]', $bid_psys_give, $bid_text);
		$bid_text = str_replace('[psys_get]', $bid_psys_get, $bid_text);
		$bid_text = str_replace('[currency_code_give]', $bid_currency_code_give, $bid_text);
		$bid_text = str_replace('[currency_code_get]', $bid_currency_code_get, $bid_text);
		$bid_text = str_replace('[amount_give]', $bid_amount_give, $bid_text);
		$bid_text = str_replace('[amount_get]', $bid_amount_get, $bid_text);
		$bid_text = str_replace('[course_give]', $bid_course_give, $bid_text);
		$bid_text = str_replace('[course_get]', $bid_course_get, $bid_text);		
 
		$in[] = array(
			array (
				'text' => __('Check bid status', 'pn'),
				'callback_data' => '/checkbid_' . $bid_id,
			),
		);																																
		tapibot_send($bot, '', $uniq_id, $settings, $bid_text, $in);										
			
		if ($qrcode and count($qr_arr) > 0) {
			foreach ($qr_arr as $q) {
				tapibot_send($bot, '', $uniq_id, $settings, is_isset($q, 'title'), '', tapibot_qr(is_isset($q, 'val'), 300, 300));
			}
		}
			
	}	
}