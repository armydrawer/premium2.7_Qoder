<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('login_placed_form')) {
	add_filter('placed_form', 'login_placed_form');
	function login_placed_form($placed) {
		
		$placed['loginform'] = __('Authourization form', 'pn');	
		
		return $placed;
	}
}

if (!function_exists('adminpage_quicktags_page_login')) {
	add_action('pn_adminpage_quicktags_page', 'adminpage_quicktags_page_login');
	function adminpage_quicktags_page_login() {
	?>
	edButtons[edButtons.length] = 
	new edButton('premium_login', '<?php _e('Authourization form', 'pn'); ?>','[login_form]');
	<?php	
	}
}

if (!function_exists('def_loginform_filelds')) {
	add_filter('loginform_filelds', 'def_loginform_filelds');
	function def_loginform_filelds($items) {

		$items['logmail'] = array(
			'name' => 'logmail',
			'title' => __('Login or email', 'pn'),
			'req' => 1,
			'value' => '',
			'type' => 'input',
		);
		$items['pass'] = array(
			'name' => 'pass',
			'title' => __('Password', 'pn'),
			'req' => 1,
			'value' => '',
			'type' => 'password',
		);		
		
		return $items;
	}
}

if (!function_exists('def_replace_array_loginform')) {
	add_filter('replace_array_loginform', 'def_replace_array_loginform', 10, 3);
	function def_replace_array_loginform($array, $prefix, $place = '') {
		
		$plugin = get_plugin_class();

		$fields = get_form_fields('loginform', $place); 
		
		$filter_name = '';
		if ('widget' == $place) {
			$prefix = 'widget_' . $prefix;
			$filter_name = 'widget_';
		}
		$html = prepare_form_fileds($fields, $filter_name . 'login_form_line', $prefix);
		
		$return_url = pn_strip_input(urldecode(is_param_get('return_url')));  
		
		$array = array(
			'[form]' => '<form method="post" class="ajax_post_form" action="' . get_pn_action('loginform') . '">',
			'[/form]' => '</form>',
			'[result]' => '<div class="resultgo"></div>',
			'[html]' => $html,
			'[submit]' => '<input type="submit" formtarget="_top" name="submit" class="' . $prefix . '_submit" value="' . __('Sign in', 'pn') . '" />',
			'[toslink]' => $plugin->get_page('tos'),
			'[registerlink]' => $plugin->get_page('register'),
			'[lostpasslink]' => $plugin->get_page('lostpass'),
			'[return_link]' => '<input type="hidden" name="return_url" value="' . $return_url . '" />',
		);	
		
		return $array;
	}
}

if (!function_exists('get_login_formed')) {
	function get_login_formed() {
		
		$temp = '';
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);
		
		if ($user_id < 1) {
		
			$array = get_form_replace_array('loginform', 'log');	
			
			$temp_form = '
			<div class="log_div_wrap">
			[form]
				[return_link]
				
				<div class="log_div_title">
					<div class="log_div_title_ins">
						'. __('Authorization', 'pn') .'
					</div>
				</div>
			
				<div class="log_div">
					<div class="log_div_ins">
						
						[html]
						
						<div class="log_line">
							<div class="log_line_subm_left">
								[submit]
							</div>
							<div class="log_line_subm_right">
								<p><a href="[registerlink]">' . __('Sign up', 'pn') . '</a></p>
								<p><a href="[lostpasslink]">' . __('Forgot password?', 'pn') . '</a></p>
							</div>
							
							<div class="clear"></div>
						</div>

						[result]
	 
					</div>
				</div>

			[/form]
			</div>
			';
		
			$temp_form = apply_filters('login_form_temp', $temp_form);
			$temp .= '<div class="not_frame">';
			$temp .= replace_tags($array, $temp_form);	
			$temp .= '</div>';

		} else {
			$temp .= '<div class="resultfalse">' . __('Error! This form is available for unauthorized users only', 'pn') . '</div>';
		}
		
		return $temp;	
	}
}

if (!function_exists('login_form_shortcode')) {
	function login_form_shortcode($atts, $content = "") {
		
		$temp = get_login_formed();
		
		return $temp;
	}
	add_shortcode('login_form', 'login_form_shortcode');
}

if (!function_exists('login_page_shortcode')) {
	function login_page_shortcode($atts, $content = "") {
		
		$temp = apply_filters('before_login_page', '');	
		$temp .= get_login_formed();
		$temp .= apply_filters('after_login_page', '');
		
		return $temp;
	}
	add_shortcode('login_page', 'login_page_shortcode');
}

if (!function_exists('def_premium_siteaction_loginform')) {
	add_action('premium_siteaction_loginform', 'def_premium_siteaction_loginform');
	function def_premium_siteaction_loginform() {
		global $pn_log_in_site;	
		
		_method('post');
		_json_head(); 
		
		$plugin = get_plugin_class();
		
		$secure_cookie = is_ssl();
		
		$pn_log_in_site = 1;
		
		$log = array();	
		$log['status'] = '';
		$log['status_code'] = 0;
		$log['status_text'] = '';
		$log['errors'] = array();
		
		$plugin->up_mode('post');
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	

		if ($user_id > 0) {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! This form is available for unauthorized users only', 'pn');
			$log['url'] = get_safe_url(apply_filters('login_auth_redirect', $plugin->get_page('account'))); 
			echo pn_json_encode($log);
			exit;		
		}	

		$log = _log_filter($log, 'loginform');	
			
		$logmail = is_param_post('logmail');
		if (strstr($logmail, '@')) {
			$logmail = is_email($logmail);
		} else {
			$logmail = is_user($logmail);
		}

		$pass = is_password(is_param_post('pass'));
		
		if (!$log['status_code']) {
			if (!$logmail) {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error! Incorrect login or e-mail', 'pn');
				$log = pn_array_unset($log, 'url');
			}	
		}

		if (!$log['status_code']) {
			if (!$pass) {
				$log['status'] = 'error';
				$log['status_code'] = 2;
				$log['status_text'] = __('Error! Incorrect password', 'pn');
				$log = pn_array_unset($log, 'url');
			}	
		}	

		$user = '';	
		if (!$log['status_code']) {
			if (strstr($logmail, '@')) {
				$user = get_user_by('email', $logmail);
			} else {
				$user = get_user_by('login', $logmail);
			}
			if (!isset($user->ID)) {
				$log['status'] = 'error';
				$log['status_code'] = 3;
				$log['status_text'] = __('Error! Wrong pair of username/password entered', 'pn');	
				$log = pn_array_unset($log, 'url');			
			}	
		}		
		
		if (!$log['status_code']) {
			$user_id = intval($user->ID);
			$allowed = apply_filters('allowed_enter_admins_from_site', 0);
			$allowed = intval($allowed);
			if (!$allowed) {
				if (user_can($user_id, 'read')) {
					$log['status'] = 'error';
					$log['status_code'] = 4;
					$log['status_text'] = __('Error! Wrong pair of username/password entered', 'pn');	
					$log = pn_array_unset($log, 'url');			
				}
			}
		}

		if (!$log['status_code']) {

			$return_url = pn_strip_input(is_param_post('return_url'));
			if (!$return_url) {
				$return_url = apply_filters('login_auth_redirect', $plugin->get_page('account'));
			}					
					
			$creds = array();
			$creds['user_login'] = is_user($user->user_login);
			$creds['user_password'] = $pass;
			$creds['remember'] = true;
			$sign_user = wp_signon($creds, $secure_cookie);	
				
			$log = apply_filters('premium_auth', $log, $sign_user, 'site');
				
			if ($sign_user and !is_wp_error($sign_user)) {
				$log['status'] = 'success';
				$log['url'] = get_safe_url($return_url); 
			} elseif ($sign_user and isset($sign_user->errors['pn_error'])) {
				$log['status'] = 'error';	
				$log['status_code'] = 1;
				$log['status_text'] = $sign_user->errors['pn_error'][0];						
			} elseif ($sign_user and isset($sign_user->errors['pn_success'])) {
				$log['status'] = 'success';	
				$log['clear'] = 1;	
				$log['status_text'] = $sign_user->errors['pn_success'][0];
			} elseif ($sign_user and isset($sign_user->errors['pn_pin'])) {	
				$log['status'] = 'success';	
				$log['show_hidden'] = 1;
				$log['status_text'] = $sign_user->errors['pn_pin'][0];							
			} else {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error! Wrong pair of username/password entered', 'pn');		
			}				
				
		}					
		
		echo pn_json_encode($log);	
		exit;
	}
}

if (!function_exists('premium_js_loginform')) {
	add_action('premium_js', 'premium_js_loginform');
	function premium_js_loginform() {	
	
		$js_login = apply_filters('premium_js_login', 1);
		if (1 == $js_login) {
	?>	
	jQuery(function($) { 
	
		$(document).on('click', '.js_window_login', function() {
			$(document).JsWindow('show', {
				window_class: 'update_window',
				title: '<?php _e('Authorization', 'pn'); ?>',
				content: $('.loginform_box_html').html(),
				insert_div: '.loginform_box',
				shadow: 1
			});		
			
			var new_url = window.location.href;
			$('input[name=return_url]').val(new_url);
			
			return false;
		});	
		
	});	
	<?php	
		}
	} 
}

if (!function_exists('wp_footer_loginform')) {
	add_action('wp_footer', 'wp_footer_loginform');
	function wp_footer_loginform() {
		
		$js_login = apply_filters('premium_js_login', 1);
		if (1 == $js_login) {
			
			$array = get_form_replace_array('loginform', 'rb');
			
			$temp = '
			<div class="loginform_box_html" style="display: none;">		
				[html]	
				<div class="rb_line">[submit]</div>
				<div class="rb_line"><a href="[registerlink]" class="js_window_join">' . __('Sign up', 'pn') . '</a> | <a href="[lostpasslink]">' . __('Forgot password?', 'pn') . '</a></div>
				[result]
			</div>';	
			
			$temp .= '
			[form]
				[return_link]
				<div class="loginform_box not_frame"></div>
			[/form]
			';
			
			echo replace_tags($array, $temp);	
		}
		
	}
}