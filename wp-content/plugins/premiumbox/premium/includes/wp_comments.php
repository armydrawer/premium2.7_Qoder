<?php 
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('pn_close_comment_section_all')) {
	add_action('init', 'pn_close_comment_section_all', 0);
	function pn_close_comment_section_all() {
		
		$request = ltrim(get_request_query(),'/');
		if ('wp-comments-post.php' == $request) {
			pn_display_mess('Page does not exist');
		}
		
	}
}

if (!function_exists('premium_comment_reply_link')) {
	add_filter('comment_reply_link', 'premium_comment_reply_link', 10, 2);
	function premium_comment_reply_link($link, $args) {
		
		if (strstr($link,'comment-reply-login')) {
			$link = $args['before'] . '<span class="comment-reply-login">' . strip_tags($link) . '</span>' . $args['after'];
		}
		
		return $link;
	}
}

if (!function_exists('comment_placed_form')) {
	add_filter('placed_form', 'comment_placed_form', 0);
	function comment_placed_form($placed) {
		
		$placed['commentform'] = __('Comment form', 'premium');
		
		return $placed;
	}
}

if (!function_exists('comment_all_settings_option')) {
	add_filter('all_settings_option', 'comment_all_settings_option', 100);
	function comment_all_settings_option($options) {
		
		$options['comment_line'] = array(
			'view' => 'line',
		);
			
		$plugin = get_plugin_class();
			
		$args = array('public' => 1);
		$post_types = get_post_types($args, 'objects');
		foreach ($post_types as $post_data) {
			$post_type = is_isset($post_data, 'name');
			if ('attachment' != $post_type) {
				$post_label = is_isset($post_data, 'label');
				$hierarchical = intval(is_isset($post_data, 'hierarchical'));
				if (0 == $hierarchical) {
					$options[$post_type . '_comment'] = array(
						'view' => 'select',
						'title' => sprintf(__('Comments from "%s"', 'premium'), $post_label),
						'options' => array('0' => __('No', 'premium'), '1' => __('Yes', 'premium')),
						'default' => $plugin->get_option('comment', $post_type . '_comment'),
						'name' => $post_type . '_comment',
					);
				}
			}
		}	

		return $options;
	}	
}

if (!function_exists('comment_all_settings_option_post')) {
	add_action('all_settings_option_post', 'comment_all_settings_option_post', 100);
	function comment_all_settings_option_post($data) {
		
		$plugin = get_plugin_class();
			
		$args = array('public' => 1);
		$post_types = get_post_types($args, 'objects');
		foreach ($post_types as $post_data) {
			$post_type = is_isset($post_data, 'name');
			if ('attachment' != $post_type) {
				$hierarchical = intval(is_isset($post_data, 'hierarchical'));
				if (0 == $hierarchical) {
					$plugin->update_option('comment', $post_type . '_comment', intval(is_param_post($post_type . '_comment')));
				}
			}
		}	
	}
}

if (!function_exists('def_post_type_opencomment')) {
	add_filter('post_type_opencomment', 'def_post_type_opencomment', 10, 2);
	function def_post_type_opencomment($status, $post_type) {
		
		$post_type = pn_string($post_type);
		$plugin = get_plugin_class();
		$comment = intval($plugin->get_option('comment', $post_type . '_comment'));
		if (1 != $comment) {
			return 'close';
		}
		
		return $status;
	}
}

if (!function_exists('def_hide_commentsdiv')) {
	add_action('admin_menu', 'def_hide_commentsdiv', 1000);
	function def_hide_commentsdiv() {
		
		$status = apply_filters('post_type_opencomment', 'open', 'post');
		if ('close' == $status) {
			remove_meta_box('commentsdiv', 'post', 'normal');
		}
		$status = apply_filters('post_type_opencomment', 'open', 'page');
		if ('close' == $status) {
			remove_meta_box('commentsdiv', 'page', 'normal');
		}	
		
	}
}

if (!function_exists('def_premium_siteaction_commentform')) {
	add_action('premium_siteaction_commentform', 'def_premium_siteaction_commentform');
	function def_premium_siteaction_commentform() {
		global $wpdb;	
		
		_method('post');
		_json_head();
		
		$plugin = get_plugin_class();
		
		$log = array();
		$log['status'] = '';
		$log['status_code'] = 0;
		$log['status_text'] = '';
		$log['errors'] = array();
		
		$plugin->up_mode('post');
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		$log = _log_filter($log, 'commentform');
		
		$author = pn_maxf_mb(pn_strip_input(is_param_post('author')), 250);
		$email = is_email(is_param_post('email'));
		$url = pn_maxf_mb(pn_strip_input(is_param_post('url')), 250);
		$comment = pn_maxf_mb(pn_strip_input(is_param_post('comment')), 2000);
		$comment_post_ID = intval(is_param_post('comment_post_ID'));
		
		if (!$log['status_code']) {
			if ($user_id < 1 and mb_strlen($author) < 2) {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error! You must enter your name', 'premium');
				$log = pn_array_unset($log, 'url');				
			}			
		}

		if (!$log['status_code']) {
			if ($user_id < 1 and !$email) {
				$log['status'] = 'error';
				$log['status_code'] = 2;
				$log['status_text'] = __('Error! You must enter your e-mail', 'premium');
				$log = pn_array_unset($log, 'url');				
			}			
		}		
		
		if (!$log['status_code']) {
			if (mb_strlen($comment) < 3) {
				$log['status'] = 'error';
				$log['status_code'] = 3;
				$log['status_text'] = __('Error! You must enter a message', 'premium');
				$log = pn_array_unset($log, 'url');				
			}			
		}

		if (!$log['status_code']) {
			if ($comment_post_ID < 1) {
				$log['status'] = 'error';
				$log['status_code'] = 4;
				$log['status_text'] = __('Error! Post not found', 'premium');
				$log = pn_array_unset($log, 'url');				
			}			
		}

		if (!$log['status_code']) {
			if ($comment_post_ID > 0) {
				$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "posts WHERE ID = '$comment_post_ID'");
				$post_type_opencomment = apply_filters('post_type_opencomment', $item->comment_status, $item->post_type);
				if ('open' != $post_type_opencomment) {
				
					$log['status'] = 'error';
					$log['status_code'] = 5;
					$log['status_text'] = __('Error! Comments closed', 'premium');
					$log = pn_array_unset($log, 'url');	

				}	
			}			
		}		
		
		if (!$log['status_code']) {
			
			$comment_data = array();
			$comment_data['comment_post_ID'] = $comment_post_ID;
			$comment_data['comment_parent'] = intval(is_param_post('comment_parent'));
			$comment_data['author'] = $author;
			$comment_data['email'] = $email;
			$comment_data['url'] = $url;
			$comment_data['comment'] = $comment;

			$comment = wp_handle_comment_submission($comment_data);
			if (is_wp_error($comment)) {
				$data = intval($comment->get_error_data());
				if (!empty($data)) {
					$log['status'] = 'error';
					$log['status_code'] = 6;
					$log['status_text'] = $comment->get_error_message();
					echo pn_json_encode($log);
					exit;
				} else {
					$log['status'] = 'error';
					$log['status_code'] = 7;
					$log['status_text'] = __('Error! Comments bd error', 'premium');
					echo pn_json_encode($log);
					exit;
				}
			}
			
			$user = $ui;
			$cookies_consent = 1;
			
			do_action('set_comment_cookies', $comment, $user, $cookies_consent);
			
			$location = get_comment_link($comment);
			
			$location = add_query_args(
				array(
					'comment_time' => current_time('timestamp'),
				),
				$location
			);			
			
			if ('unapproved' === wp_get_comment_status($comment) and !empty($comment->comment_author_email)) {
				$location = add_query_args(
					array(
						'unapproved'      => $comment->comment_ID,
						'moderation-hash' => wp_hash($comment->comment_date_gmt),
					),
					$location
				);
			}
			
			$location = apply_filters('comment_post_redirect', $location, $comment);
			
			$log['status'] = 'success';	
			$log['url'] = get_safe_url($location);
			$log['clear'] = 1;
			$log['status_text'] = apply_filters('commentform_success_message', __('Your comment has been successfully add', 'premium'));		
			
		}
		
		echo pn_json_encode($log);
		exit;
	}
}