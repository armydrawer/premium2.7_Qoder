<?php
if (!class_exists('TelegramBot')) {
	class TelegramBot
	{
		private $api_key = "";
		private $bot_logs = 0;
		private $answer_logs = 0;
		public $timeout = 20;

		function __construct($api_key, $bot_logs = '', $answer_logs = '')
		{
			$this->api_key = trim($api_key);
			$this->bot_logs = intval($bot_logs);
			$this->answer_logs = intval($answer_logs);
		}	
		
		function set_webhook($url) {
			
			return $this->bot_command('setWebhook', array('url' => $url));	
		}
		
		function unset_webhook() {
			
			return $this->bot_command('deleteWebhook');	
		}		
		 
		function prepare_emoji($text) {
			
			$text = preg_replace_callback('@\\\x([0-9a-fA-F]{2})@x', function($captures) { return chr(hexdec($captures[1])); }, $text);
			
			return $text;
		}			
		 
		function send($type, $chat_id, $item, $reply_message_id = '', $inline_keyboard = '', $keyboard = '') {
			
			$type = trim($type);
			$reply_message_id = intval($reply_message_id);
			
			$item = strip_tags($item, '<b>,<strong>,<i>,<em>,<a>,<code>,<pre>');
			$item = $this->prepare_emoji($item);
			
			$params = array(
				'chat_id' => $chat_id,
			);
			
			if ('text' == $type){
				$params['text'] = $item;
				$params['parse_mode'] = 'HTML'; //'Markdown';//разметка(включена), https://core.telegram.org/bots/api#markdown-style
			} else {
				$params['photo'] = $item;
			}
			
			if ($reply_message_id > 0) {
				$params['reply_to_message_id'] = $reply_message_id;
			}
			
			$reply_markup = array();
			
			$reply_markup["remove_keyboard"] = true;
			
			if (is_array($inline_keyboard)) {
				$reply_markup["inline_keyboard"] = $inline_keyboard;
			} elseif (is_array($keyboard)) {
				$reply_markup["keyboard"] = $keyboard;
				$reply_markup["one_time_keyboard"] = true;
				$reply_markup["resize_keyboard"] = true;
				if (isset($reply_markup["remove_keyboard"])) {
					unset($reply_markup["remove_keyboard"]);
				}
			}
			
			if (count($reply_markup) > 0) {
				$params['reply_markup'] = json_encode($reply_markup);
			}
			
			$command = 'sendMessage';
			if ('photo' == $type) {
				$command = 'sendPhoto';
			}
			$result = $this->bot_command($command, $params);
			if (isset($result['message_id']) and $result['message_id'] > 0) {
				return $result['message_id'];
			} else {
				return 0;
			}				
		}	
		
		function bot_command($command, $post = array()) {
			
			$url = 'https://api.telegram.org/bot'. $this->api_key .'/'. $command;
	
			$ch = curl_init();
	
			curl_setopt_array($ch, array(
				CURLOPT_HEADER => false,
				CURLOPT_POST => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CONNECTTIMEOUT => $this->timeout,
				CURLOPT_TIMEOUT => $this->timeout,
				CURLOPT_POSTFIELDS => $post,
				CURLOPT_URL => $url
			));
	
			$res = curl_exec($ch);
			$errno = curl_errno($ch);
			$result = @json_decode($res, true);
			
			if ($errno > 0) {
				$this->create_log('Curl error:' . $errno);
			}
			
			$this->create_log($result);			
			
			if (isset($result['ok'], $result['result']) and $result['ok']) {
				return $result;
			} 
			
			return '';
		}
		
		function create_log($result = '', $place = 0) {
			global $wpdb;
			
			$place = intval($place);
			$update = 0;
			
			if (0 == $place and 1 == $this->bot_logs) {
				$update = 1;
			}
			
			if (1 == $place and 1 == $this->answer_logs) {
				$update = 1;
			}
			
			if ($update) {
				$arr = array();
				$arr['create_date'] = current_time('mysql');
				$arr['log_ip'] = pn_real_ip();
				$arr['place'] = $place;
				$arr['error_text'] = pn_strip_input(print_r($result, true));
				$wpdb->insert($wpdb->prefix . "telegram_logs", $arr);
			}
			
		}
	}
}