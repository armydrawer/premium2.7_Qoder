<?php 
/*
https://core.telegram.org/bots/api
*/
if (!class_exists('TAPIBOT_CLASS')) {
	class TAPIBOT_CLASS
	{
		private $token = "";
		public $timeout = 20;
		private $create_log = 0;
		private $bot_id = 0;
		private $html_parse_mode = '';

		function __construct($token, $bot_id, $create_log, $parse_mode = 0)
		{
			$this->token = trim($token);
			$this->create_log = intval($create_log);
			$this->bot_id = intval($bot_id);
			$this->html_parse_mode = intval(apply_filters('telegram_api_bot_html_parse_mode', $parse_mode));
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
		 
		function send($chat_id, $item, $reply_message_id = '', $inline_keyboard = '', $keyboard = '', $image = '') {
			
			$image = trim($image);
			$reply_message_id = intval($reply_message_id);
			
			$item = strip_tags($item, '<b>,<strong>,<i>,<em>,<a>,<code>,<pre>');
			$item = $this->prepare_emoji($item);
			
			$params = array(
				'chat_id' => $chat_id,
			);
			if (strlen($image) > 0) {
				$params['photo'] = $image;
				$params['caption'] = $item;
			} else {
				$html_parse_mode = $this->html_parse_mode;
				if ($html_parse_mode) {
					$params['text'] = $item;
					$params['parse_mode'] = 'HTML';					
				} else {
					$params['text'] = $this->html_to_markdown($item);
					$params['parse_mode'] = 'Markdown';					
				}
			}
			if ($reply_message_id > 0) {
				$params['reply_to_message_id'] = $reply_message_id;
			}
			
			$reply_markup = array();
			//$reply_markup["remove_keyboard"] = true;
			
			if (is_array($inline_keyboard) and count($inline_keyboard) > 0) {
				$reply_markup["inline_keyboard"] = $inline_keyboard;
			} elseif (is_array($keyboard) and count($keyboard) > 0) {
				$reply_markup["keyboard"] = $keyboard;
				$reply_markup["one_time_keyboard"] = false;
				$reply_markup["resize_keyboard"] = true;
				if (isset($reply_markup["remove_keyboard"])) {
					unset($reply_markup["remove_keyboard"]);
				}
			}
			
			if (count($reply_markup) > 0) {
				$params['reply_markup'] = json_encode($reply_markup);
			}
			
			if (strlen($image) > 0) {
				$command = 'sendPhoto';
			} else {
				$command = 'sendMessage';
			}
			$result = $this->bot_command($command, $params);
			if (isset($result['message_id']) and $result['message_id'] > 0) {
				return $result['message_id'];
			} else {
				return 0;
			}				
		}	
		
		function bot_command($command, $post = array()) {
			
			$url = 'https://api.telegram.org/bot' . $this->token . '/' . $command;
	
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
			
			if ($this->create_log) {
				if ($errno > 0) {
					tapibot_create_log($this->bot_id, $url, $post, '', '', 'Curl error:' . $errno);
				} else {
					tapibot_create_log($this->bot_id, $url, $post, '', '', $result);
				}
			}				
			
			if (isset($result['ok'], $result['result']) and $result['ok']) {
				return $result;
			} 
			
			return '';
		}
		
		function html_to_markdown($html) {
			
			$data = strip_tags($html, '<a><b><strong><em><i><code><s><strike><del><u>');
			$data = preg_replace("~\*~i", "@STAR@", $data);
			$data = preg_replace("~\[copytext\]([^\[]+)\[/copytext\]~i", "`$1`", $data);
			$data = preg_replace("~<(/)?b[^>]*>~i", "**", $data);
			$data = preg_replace("~<(/)?strong[^>]*>~i", "**", $data);
			$data = preg_replace("~<(/)?em[^>]*>~i", "*", $data);
			$data = preg_replace("~<(/)?i[^>]*>~i", "*", $data);
			$data = preg_replace("~<(/)?code[^>]*>~i", "`", $data);
			$data = preg_replace("~<(/)?s[^>]*>~i", "~~", $data);
			$data = preg_replace("~<(/)?strike[^>]*>~i", "~~", $data);
			$data = preg_replace("~<(/)?del[^>]*>~i", "~~", $data);
			$data = preg_replace("~<(/)?u[^>]*>~i", "__", $data);
			$data = preg_replace("~@STAR@~i", "\\*", $data);
			$data = preg_replace("~(?:\r?\n)+~i", "\n", $data);
			$data = preg_replace_callback("~<a href=['\"]([^'\"]+)['\"].*>(.+)</a>~i", function ($m){
				$link = $m[1];
				$title = $m[2];
				return '['.strtr($title, ['['=>'', ']'=>'']).']('.strtr($link, ['('=>'', ')'=>'']).')';
			}, $data);
			
			return $data;
		}
	}
}