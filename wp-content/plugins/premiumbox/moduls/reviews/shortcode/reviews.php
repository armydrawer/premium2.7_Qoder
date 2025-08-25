<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('pn_adminpage_quicktags_page_reviews')) {
	add_action('pn_adminpage_quicktags_page', 'pn_adminpage_quicktags_page_reviews');
	function pn_adminpage_quicktags_page_reviews() {
	?>
	edButtons[edButtons.length] = 
	new edButton('premium_reviews', '<?php _e('Reviews', 'pn'); ?>', '[reviews count=5]');

	edButtons[edButtons.length] = 
	new edButton('premium_reviews_form', '<?php _e('Reviews form', 'pn'); ?>', '[reviews_form]');
	<?php	
	}
}

if (!function_exists('def_replace_array_reviewsform')) {
	add_filter('replace_array_reviewsform', 'def_replace_array_reviewsform', 10, 3);
	function def_replace_array_reviewsform($array, $prefix, $place = '') {

		$fields = get_form_fields('reviewsform', $place);
		
		$filter_name = '';
		if ('widget' == $place) {
			$prefix = 'widget_' . $prefix;
			$filter_name = 'widget_';
		}
		$html = prepare_form_fileds($fields, $filter_name . 'reviews_form_line', $prefix);
		
		$array = array(
			'[form]' => '<form method="post" class="ajax_post_form" action="' . get_pn_action('reviewsform') . '">',
			'[/form]' => '</form>',
			'[result]' => '<div class="resultgo"></div>',
			'[html]' => $html,
			'[submit]' => '<input type="submit" formtarget="_top" name="submit" class="' . $prefix . '_submit" value="' . __('Leave a review', 'pn') . '" />',
		);	
		
		return $array;
	}
}

if (!function_exists('get_reviews_form')) {
	function get_reviews_form() {

		$plugin = get_plugin_class();

		$reviews_form = '';
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);		
		
		$by = intval($plugin->get_option('reviews', 'by'));
		
		if (0 == $by or 1 == $by and $user_id > 0 or 2 == $by and 0 == $user_id) {
			if ('not' != $plugin->get_option('reviews', 'method')) {	
			
				$array = get_form_replace_array('reviewsform', 'rf');	
			
				$temp_form = '
				<div class="rf_div_wrap">
				[form]

					<div class="rf_div_title">
						<div class="rf_div_title_ins">
							'. __('Post review', 'pn') .'
						</div>
					</div>
				
					<div class="rf_div">
						<div class="rf_div_ins">
							
							[html]
							
							<div class="rf_line has_submit">
								[submit]
							</div>					
							
							[result]
							
						</div>
					</div>
				
				[/form]
				</div>
				';
			
				$temp_form = apply_filters('reviews_form_temp', $temp_form);
				$reviews_form = replace_tags($array, $temp_form);
			}
		}
		
		return $reviews_form;
	}
	add_shortcode('reviews_form', 'get_reviews_form');
}

if (!function_exists('reviews_page_shortcode')) {
	function reviews_page_shortcode($atts, $content = "") {
		global $wpdb;
	
		$plugin = get_plugin_class();

		$temp = '';
		$temp .= apply_filters('before_reviews_page', '');
				
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);
						
		$limit = intval($plugin->get_option('reviews', 'count')); if ($limit < 1) { $limit=10; }
		
		$where = get_reviews_where();
		$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "reviews WHERE auto_status = '1' AND review_status = 'publish' $where"); 
		$pagenavi = get_pagenavi_calc($limit, get_query_var('paged'), $count);
		$reviews = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "reviews WHERE auto_status = '1' AND review_status = 'publish' $where ORDER BY review_date DESC LIMIT ". $pagenavi['offset'] .",".$pagenavi['limit']);					

		$reviews_list = apply_filters('before_reviews_page_content', '<div class="many_reviews"><div class="many_reviews_ins">');
		
		if (count($reviews) > 0) {
			$r = 0;	
			$reviews_date_format = apply_filters('reviews_date_format', get_option('date_format') . ', ' . get_option('time_format'));
			
			foreach ($reviews as $item) { $r++;
				$site = esc_url($item->user_site);
				$site1 = $site2 = '';
				if ($site) {
					$site1 = '<a href="' . $site . '" target="_blank" rel="nofollow noreferrer noopener">';
					$site2 = '</a>';
				}
				
				$reviews_list .= '
				<div class="one_reviews" id="review-' . $item->id . '" itemprop="review" itemscope itemtype="https://schema.org/Review">
				';
				
				$review_html ='
				<meta itemprop="name" content="' . pn_strip_input(wp_trim_words($item->review_text, 2)) . '">
				<meta itemprop="datePublished" content="' . get_pn_time($item->review_date, 'Y-m-d') . '">
				<div itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
					<meta itemprop="worstRating" content="1"> 
					<meta itemprop="ratingValue" content="5"> 
					<meta itemprop="bestRating" content="5">
				</div>				
				
				<div class="one_reviews_ins">
					<div class="one_reviews_abs"></div>
					
					<div class="one_reviews_name">
						'. $site1 .'
						<span itemprop="author">' . pn_strip_input($item->user_name) . '</span>
						'. $site2 .'							
					</div>
					<div class="one_reviews_date">' . get_pn_time($item->review_date, $reviews_date_format) . '</div>
						<div class="clear"></div>
							
					<div class="one_reviews_text" itemprop="description">
						'.  apply_filters('comment_text', _span_break_words($item->review_text)) .'
						<div class="clear"></div>
					</div>';
					
					$answer = trim($item->review_answer);
					if ($answer) {
							
						$review_html .='
						<div class="one_reviews_answer">
							<div class="one_reviews_answer_title">' . __('Administration comment', 'pn') . ':</div>
							'.  apply_filters('comment_text', $item->review_answer) .'
							<div class="clear"></div>
						</div>
						';
							
					}
						
					$review_html .= '	
				</div>
				';
				
				$reviews_list .= apply_filters('reviews_one', $review_html, $item, $r, $reviews_date_format);
				$reviews_list .= '</div>';
			}
		} else {
			$reviews_list .='<div class="no_reviews"><div class="no_reviews_ins">' . __('No reviews', 'pn') . '</div></div>';
		}
		
		$reviews_list .= apply_filters('after_reviews_page_content', '</div></div>');
			
		$reviews_navi = get_pagenavi($pagenavi);
		
		$reviews_form = get_reviews_form();
		
		$array = array(
			'[form]' => $reviews_form,
			'[list]' => $reviews_list,
			'[navi]' => $reviews_navi,
		);
		
		$page_map = '
			[list]
			[navi]
			[form]
		';

		$page_map = apply_filters('reviews_page_map', $page_map);
		$temp .= replace_tags($array, $page_map);	
		
		$temp .= apply_filters('after_reviews_page', '');
		
		return $temp;
	}
	add_shortcode('reviews_page', 'reviews_page_shortcode');
}

if (!function_exists('reviews_shortcode')) {
	function reviews_shortcode($atts, $content = "") {
		global $wpdb;

		$temp = '';				
		$temp .= apply_filters('before_reviews_page_content', '<div class="many_reviews"><div class="many_reviews_ins">');

		$where = get_reviews_where();
		$limit = intval(is_isset($atts, 'count')); if ($limit < 1) { $limit=10; }
		$reviews = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "reviews WHERE auto_status = '1' AND review_status = 'publish' $where ORDER BY review_date DESC LIMIT ". $limit);					
		
		if (count($reviews) > 0) {
			$r = 0;	
			$reviews_date_format = apply_filters('reviews_date_format', get_option('date_format') . ', ' . get_option('time_format'));
			foreach ($reviews as $item) { $r++;
			
				$site = esc_url($item->user_site);
				$site1 = $site2 = '';
				if ($site) {
					$site1 = '<a href="' . $site . '" target="_blank" rel="nofollow noreferrer noopener">';
					$site2 = '</a>';
				}
				
				$temp .= '<div class="one_reviews" id="review-' . $item->id . '" itemprop="review" itemscope itemtype="https://schema.org/Review">';
				
				$review_html ='
				<meta itemprop="name" content="' . pn_strip_input(wp_trim_words($item->review_text, 2)) . '">
				<meta itemprop="datePublished" content="' . get_pn_time($item->review_date, 'Y-m-d') . '">
				<div itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
					<meta itemprop="worstRating" content="1"> 
					<meta itemprop="ratingValue" content="5"> 
					<meta itemprop="bestRating" content="5">
				</div>				
				
				<div class="one_reviews_ins">
					<div class="one_reviews_abs"></div>
					
					<div class="one_reviews_name">
						'. $site1 .'
						<span itemprop="author">'. pn_strip_input($item->user_name) .'</span>
						'. $site2 .'							
					</div>
					<div class="one_reviews_date">'. get_pn_time($item->review_date, $reviews_date_format) .'</div>
						<div class="clear"></div>
							
					<div class="one_reviews_text" itemprop="description">
						'.  apply_filters('comment_text', _span_break_words($item->review_text)) .'
						<div class="clear"></div>
					</div>';
					
					$answer = $item->review_answer;
					if ($answer) {
							
						$review_html .='
						<div class="one_reviews_answer">
							<div class="one_reviews_answer_title">' . __('Administration comment', 'pn') . ':</div>
							'.  apply_filters('comment_text', $item->review_answer) .'
							<div class="clear"></div>
						</div>
						';
							
					}
						
					$review_html .= '	
				</div>
				';			
				
				$temp .= apply_filters('reviews_one', $review_html, $item, $r, $reviews_date_format);
				$temp .= '</div>';
				
			}
		} else {
			$temp .='<div class="no_reviews"><div class="no_reviews_ins">' . __('No reviews', 'pn') . '</div></div>';
		}
		
		$temp .= apply_filters('after_reviews_page_content', '</div></div>');

		return $temp;
	}
	add_shortcode('reviews', 'reviews_shortcode');
}

if (!function_exists('def_premium_siteaction_reviewsform')) {
	add_action('premium_siteaction_reviewsform', 'def_premium_siteaction_reviewsform');
	function def_premium_siteaction_reviewsform() {
		global $wpdb;	
		
		_method('post');
		_json_head();
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);
		
		$log = array();
		$log['status_text'] = '';
		$log['status'] = 'error';
		$log['status_code'] = 0; 	
		
		$plugin = get_plugin_class();
		
		$plugin->up_mode('post');
		
		$log = _log_filter($log, 'reviewsform');
		
		if (!$log['status_code']) {
			$start = 0;	
			$by = intval($plugin->get_option('reviews', 'by'));
			$method = $plugin->get_option('reviews', 'method');
			if (0 == $by or 1 == $by and $user_id > 0 or 2 == $by and $user_id < 1) {
				if ('not' != $method) {
					$start = 1;
				}	
			}
			if (!$start) {
				$log['status'] = 'error';
				$log['status_code'] = 2;
				$log['status_text'] = __('Error! Reviews are disabled', 'pn');
				$log = pn_array_unset($log, 'url');				
			}				
		} 		
		
		$array = array();
		$array['user_id'] = $user_id;
		$array['create_date'] = current_time('mysql');
		$array['review_date'] = current_time('mysql');
		$review_hash = wp_generate_password(25, false, false);
		$array['review_hash'] = md5($review_hash);
		$array['review_locale'] = get_locale();
		
		$array['user_name'] = $name = pn_maxf_mb(pn_strip_input(is_param_post('name')), 150);
		$array['user_email'] = $email = is_email(is_param_post('email'));
		$array['user_ip'] = pn_real_ip(); 
		$array['user_browser'] = pn_maxf(pn_strip_input(is_isset($_SERVER,'HTTP_USER_AGENT')), 250);
		if (1 == $plugin->get_option('reviews', 'website')) {
			$array['user_site'] = $website = pn_maxf_mb(pn_strip_input(is_param_post('user_website')), 500);
		}	
		$array['review_text'] = $text = pn_maxf_mb(pn_strip_input(is_param_post('text')), 1000);
		
		$return_url = pn_strip_input(is_param_post('return_url'));	 
					
		if (!$log['status_code']) {
			if (mb_strlen($name) < 2) {		
				$log['status'] = 'error';
				$log['status_code'] = 3;
				$log['status_text'] = __('Error! You must enter your name', 'pn');
				$log = pn_array_unset($log, 'url');		
			}
		}

		if (!$log['status_code']) {
			if (!$email) {		
				$log['status'] = 'error';
				$log['status_code'] = 4;
				$log['status_text'] = __('Error! You must enter your e-mail', 'pn');
				$log = pn_array_unset($log, 'url');		
			}
		}		
				
		if (!$log['status_code']) {
			if (mb_strlen($text) < 3) {	
				$log['status'] = 'error';
				$log['status_code'] = 5;
				$log['status_text'] = __('Error! You must enter a message', 'pn');
				$log = pn_array_unset($log, 'url');		
			}
		}

		if (!$log['status_code']) {		
			$maxlinks = intval($plugin->get_option('reviews', 'maxlinks'));
			$links = count_urls($text);
			if ($links > $maxlinks) {
				$log['status'] = 'error';
				$log['status_code'] = 6;
				$log['status_text'] = sprintf(__('Error! Maximum number of links - %s', 'pn'), $maxlinks);
				$log = pn_array_unset($log, 'url');	
			}	
		}

		if (!$log['status_code']) {		
			$disabledip = pn_strip_input($plugin->get_option('reviews', 'disabledip'));
			if ($disabledip and pn_has_ip($disabledip)) {
				$log['status'] = 'error';
				$log['status_code'] = 7;
				$log['status_text'] = __('Error! Your IP banned', 'pn');
				$log = pn_array_unset($log, 'url');	
			}	
		}

		if (!$log['status_code']) {		
			$stopwords = pn_strip_input($plugin->get_option('reviews','stopwords'));
			$stopword_arr = explode(',', $stopwords);
			$stopword_arr = array_map('trim', $stopword_arr);			
			if (strlen($text) > 0 and strstr_array($text, $stopword_arr)) {
				$log['status'] = 'error';
				$log['status_code'] = 8;
				$log['status_text'] = __('Error! You message is spam', 'pn');
				$log = pn_array_unset($log, 'url');	
			}	
		}

		if (!$log['status_code']) {		
			$disdomains = pn_strip_input($plugin->get_option('reviews', 'disdomains'));
			$disdomains_arr = explode("\n", $disdomains);
			$disdomains_arr = array_map('trim', $disdomains_arr);
			$email_arr = explode('@', $array['user_email']);
			$email_domain = trim(is_isset($email_arr, 1));
			if ($email_domain and in_array($email_domain, $disdomains_arr)) {
				$log['status'] = 'error';
				$log['status_code'] = 8;
				$log['status_text'] = __('Error!  You message is spam', 'pn');
				$log = pn_array_unset($log, 'url');	
			}	
		}

		if (!$log['status_code']) {
			$blacklist = pn_strip_input($plugin->get_option('reviews', 'blacklist'));
			$blacklist_arr = explode("\n", $blacklist);
			$blacklist_arr = array_map('trim', $blacklist_arr);
			if ($array['user_email'] and in_array($array['user_email'], $blacklist_arr)) {
				$log['status'] = 'error';
				$log['status_code'] = 8;
				$log['status_text'] = __('Error! You e-mail is blocked', 'pn');
				$log = pn_array_unset($log, 'url');			
			}
		}

		if (!$log['status_code']) {
			$array = apply_filters('all_reviews_addform_post', $array);
			$res = apply_filters('item_reviews_add_before', pn_ind(), $array);
			if (!$res['ind']) {
				$log['status'] = 'error';
				$log['status_code'] = 9;
				$log['status_text'] = is_isset($res, 'error');
				$log = pn_array_unset($log, 'url');		
			}
		}		
					
		if (!$log['status_code']) {			
					
			if ('moderation' == $method) { /* if moderation */
							
				$review_status = 'moderation';
				$success_message = __('Your review has been successfully added and is waiting for moderation', 'pn');					
								
			} elseif ('verify' == $method) { /* if verification */
							
				$review_status = 'moderation';
				$success_message = __('Your review has been successfully added. We sent you an email for confirmation', 'pn');					
								
				$notify_tags = array();
				$notify_tags['[link]'] = get_request_link('confirmreview', '', get_locale(), array('review_hash' => $review_hash));
				$notify_tags = apply_filters('notify_tags_confirmreview', $notify_tags, $ui);	
								
				$user_send_data = array(
					'user_email' => $array['user_email'],
				);	
				$user_send_data = apply_filters('user_send_data', $user_send_data, 'confirmreview', $ui);
				$result_mail = apply_filters('premium_send_message', 0, 'confirmreview', $notify_tags, $user_send_data); 										
									
			} else { /* if add */
							
				$review_status = 'publish';
				$success_message = __('Your review has been successfully added', 'pn');

			}	
									
			$array['auto_status'] = 1;
			$array['review_status'] = $review_status;				
			$wpdb->insert($wpdb->prefix . 'reviews', $array);
			$review_id = $wpdb->insert_id;
			$array['id'] = $review_id;
			$review_object = (object)$array;
						
			if ('notmoderation' == $method) {
				$return_url = get_review_link($review_id, $review_object);						
			}
						
			$log['status'] = 'success';
			$log['clear'] = 1;
			$log['status_text'] = apply_filters('reviews_form_success_message', $success_message, $method);
						
			$log['url'] = get_safe_url(apply_filters('reviews_form_redirect', $return_url)); 
						
			if ('moderation' == $method) {
				mailto_add_reviews($review_object, 'moderation', $ui);
			} elseif ('notmoderation' == $method) {
				mailto_add_reviews($review_object, 'publish', $ui);
			}

		}				
		
		echo pn_json_encode($log);
		exit;
	}
}

if (!function_exists('mailto_add_reviews')) {
	function mailto_add_reviews($review, $status, $ui) {
		
		$review_id = intval($review->id); 
		$user_id = intval($review->user_id);
		$user_name = pn_strip_input($review->user_name);
		$user_email = is_email($review->user_email);
		$review_link = get_review_link($review_id, $review);
		
		if ('moderation' == $status) {
			$textstatus = __('moderating', 'pn');
			$management = '( <a href="' . admin_url('admin.php?page=all_add_reviews&item_id=' . $review_id) . '">' . __('Edit', 'pn') . '</a> )';
		} else {
			$textstatus = __('published', 'pn');		
			$management = '( <a href="' . admin_url('admin.php?page=all_add_reviews&item_id=' . $review_id) .'">' . __('Edit', 'pn') . '</a> ) ( <a href="' . $review_link . '">' . __('View', 'pn') . '</a> )';
		}		
		
		if ($user_id) {
			$user = '<a href="' . pn_edit_user_link($user_id) . '">' . $user_name . '</a>';
		} else {
			$user = $user_name;
		}	
		
		$now_locale = get_locale();		
		set_locale(get_admin_lang());		
		
		$notify_tags = array();
		$notify_tags['[user]'] = $user;
		$notify_tags['[user_ip]'] = pn_strip_input($review->user_ip);
		$notify_tags['[user_browser]'] = pn_strip_input($review->user_browser);
		$notify_tags['[review_link]'] = $review_link;
		$notify_tags['[text]'] = pn_strip_input($review->review_text);
		$notify_tags['[answer]'] = '';
		$notify_tags['[status]'] = $textstatus;
		$notify_tags['[management]'] = $management;
		$notify_tags = apply_filters('notify_tags_newreview', $notify_tags);	
		
		$user_send_data = array(
			'admin_email' => 1,
		);	
		$result_mail = apply_filters('premium_send_message', 0, 'newreview', $notify_tags, $user_send_data); 	
		
		set_locale($now_locale);
		$user = $user_name;
		
		$notify_tags = array();
		$notify_tags['[user]'] = $user;
		$notify_tags['[user_ip]'] = pn_strip_input($review->user_ip);
		$notify_tags['[user_browser]'] = pn_strip_input($review->user_browser);
		$notify_tags['[review_link]'] = $review_link;
		$notify_tags['[text]'] = pn_strip_input($review->review_text);
		$notify_tags['[answer]'] = '';
		$notify_tags['[status]'] = $textstatus;
		$notify_tags = apply_filters('notify_tags_newreview_auto', $notify_tags);
		
		$user_send_data = array(
			'user_email' => $user_email,
		);
		$user_send_data = apply_filters('user_send_data', $user_send_data, 'newreview_auto', $ui);
		$result_mail = apply_filters('premium_send_message', 0, 'newreview_auto', $notify_tags, $user_send_data);		
		
	}
}

if (!function_exists('def_premium_request_confirmreview')) {
	add_action('premium_request_confirmreview', 'def_premium_request_confirmreview');
	function def_premium_request_confirmreview() {
		global $wpdb;	
		
		$plugin = get_plugin_class();
		$plugin->up_mode();
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);
		
		$method = trim($plugin->get_option('reviews', 'method'));
		$hash = is_reviews_hash(is_param_get('review_hash'));
		if ($hash and 'verify' == $method) {
			$hash_md = md5($hash);
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "reviews WHERE auto_status = '1' AND review_hash = '$hash_md' AND review_status = 'moderation'");
			if (isset($data->id)) {
				$id = $data->id;
				
				$wpdb->query("UPDATE " . $wpdb->prefix . "reviews SET review_status='publish', review_hash='' WHERE id = '$id'");
				
				$data = pn_object_replace($data, array('review_status' => 'publish', 'review_hash' => ''));
				
				$link = get_review_link($id, $data);
				mailto_add_reviews($data, 'publish', $ui);

				wp_redirect($link);
				exit;
			} 
		} 
		
		pn_display_mess(__('Error!', 'pn'), __('Error!', 'pn'), 'error');
		
	}
}