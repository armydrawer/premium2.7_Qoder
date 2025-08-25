<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('premium_wp_mail_content_type')) {
	add_filter('wp_mail_content_type', 'premium_wp_mail_content_type');
	function premium_wp_mail_content_type() {
		
		return "text/html";
	}
}

if (!function_exists('premium_html_wp_mail')) {
	add_filter('wp_mail', 'premium_html_wp_mail');
	function premium_html_wp_mail($data) {
		
		$data['message'] = ' 
		<html> 
			<head> 
				<title>' . $data['subject'] . '</title> 
			</head> 
			<body>
				'. $data['message'] .'
			</body> 
		</html>';
		
		return $data;
	}	
}

if (!function_exists('premium_default_wp_mail')) {
	add_filter('wp_mail', 'premium_default_wp_mail', 100);
	function premium_default_wp_mail($data) {
		
		$headers = trim(is_isset($data, 'headers'));
		if (!$headers) {
			$data['headers'] = "From: " . get_bloginfo('sitename') . " <support@" . str_replace(array('http://', 'https://', 'www.'), '', PN_SITEURL) . ">\r\n";
		}
		
		return $data;
	}
}

if (!function_exists('standart_pn_email_send')) {
	add_filter('pn_email_send', 'standart_pn_email_send', 10, 6);
	function standart_pn_email_send($result, $recipient_mail = '', $subject = '', $html = '', $sender_name = '', $sender_mail = '') {
		
		$headers = '';
		$sender_name = trim($sender_name);
		$sender_mail = trim($sender_mail);
		if ($sender_name and $sender_mail) {
			$headers = "From: $sender_name <". $sender_mail .">\r\n";
		}		
		
		$recipient_mails = explode(',', $recipient_mail);
		foreach ($recipient_mails as $mail) {
			$mail = trim($mail);
			if (is_email($mail)) {
				$result = wp_mail($mail, $subject, $html, $headers);
			}
		}		
		
		return $result;
	}
}

if (!function_exists('premium_recovery_mode_email')) {
	/* wp-includes/class-wp-recovery-mode-email-service.php */
	add_filter('recovery_mode_email', 'premium_recovery_mode_email', 10, 2);	
	function premium_recovery_mode_email($email, $url) {
		
		if (isset($email['to'])) {
			unset($email['to']);
		}
		
		return $email;
	}
}

if (!function_exists('_load_notify')) {
	function _load_notify() {
		global $wpdb;

		$notify_list = array();
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "notify");
		foreach ($items as $item) {
			$notify_list[$item->notify_type][$item->notify_place] = pn_json_decode($item->notify_options);
		}	
		
		return $notify_list;
	}
}

if (!function_exists('get_notify_data')) {
	function get_notify_data($type, $place) {
		global $wpdb, $pn_notify_list;

		$type = trim($type);
		$place = trim($place);

		if (!is_array($pn_notify_list)) {
			$pn_notify_list = _load_notify();
		}
		
		if (isset($pn_notify_list[$type][$place])) {
			return $pn_notify_list[$type][$place];
		}
		
		return array();
	}
}

if (!function_exists('update_notify_data')) {
	function update_notify_data($type, $place, $options) {
		global $wpdb, $pn_notify_list;

		$type = pn_maxf(trim($type), 180);
		$place = pn_maxf(trim($place), 180);
		if (!is_array($options)) { $options = array(); }

		if (!is_array($pn_notify_list)) {
			$pn_notify_list = _load_notify();
		}
		
		if ($type and $place) {
			
			$notify_data = $wpdb->get_row("SELECT id FROM " . $wpdb->prefix . "notify WHERE notify_type = '$type' AND notify_place = '$place'");
			$arr = array();
			$arr['notify_type'] = $type;
			$arr['notify_place'] = $place;
			$arr['notify_options'] = pn_json_encode($options);
			
			if (isset($notify_data->id)) {
				$notify_id = $notify_data->id;
				$result = $wpdb->update($wpdb->prefix . "notify", $arr, array("id" => $notify_id));
			} else {
				$result = $wpdb->insert($wpdb->prefix . "notify", $arr);
			}
			if ($result) {
				$pn_notify_list[$type][$place] = $options;
				return $result;
			}	
			
		}
		
		return 0;
	}
}

if (!function_exists('delete_notify_data')) {
	function delete_notify_data($type, $place) {
		global $wpdb, $pn_notify_list;

		$type = pn_maxf(trim($type), 180);
		$place = pn_maxf(trim($place), 180);

		if (!is_array($pn_notify_list)) {
			$pn_notify_list = _load_notify();
		}
		
		if ($type and $place) {
			
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "notify WHERE notify_type = '$type' AND notify_place = '$place'");
			
			if (isset($pn_notify_list[$type][$place])) {
				unset($pn_notify_list[$type][$place]);
			}
			
			return $result;	
			
		}
		
		return 0;
	}
}