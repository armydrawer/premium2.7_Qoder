<?php
if (!defined('ABSPATH')) { exit(); }
 
add_action('premium_js','premium_js_walletsverify');
function premium_js_walletsverify() {
?>	
jQuery(function($) { 

    $(document).on('click', '.js_userwallet_verify', function() {

		var id = $(this).attr('data-id');
		var redir = $(this).attr('data-redir');
		var thet = $(this);
		if (!thet.hasClass('act')) {
			thet.addClass('act');
			var param = 'id=' + id + '&redir=' + redir;
			$.ajax({
				type: "POST",
				url: "<?php echo get_pn_action('info_userwallets'); ?>",
				dataType: 'json',
				data: param,
				error: function(res, res2, res3) {
					<?php do_action('pn_js_error_response', 'ajax'); ?>
				},			
				success: function(res)
				{
					if (res['status'] == 'success') {
						$(document).JsWindow('show', {
							window_class: 'walletverify_window',
							title: '<?php _e('Verify wallet', 'pn'); ?>',
							content: res['html'],
							insert_div: '.walletsverify_box',
							shadow: 1
						});						
					} 
					if (res['status'] == 'error') {
						<?php do_action('pn_js_alert_response'); ?>
					}
					thet.removeClass('act');
				}
			});			
		}

        return false;
    });
	
    $(document).on('click','.verify_action_button', function() {
		
		var thet = $(this);
		var id = thet.attr('data-id');
		var wait_title = thet.attr('data-title');
		var redir = thet.attr('data-redir');
		$('.resultgo_walletverify').html('<div class="resulttrue">' + wait_title + '</div>');
		
		if (!thet.prop('disabled')) {
			thet.prop('disabled', true);
		
			var param ='id=' + id + '&redir=' + redir;
			$.ajax({
				type: "POST",
				url: "<?php echo get_pn_action('accountverify'); ?>",
				dataType: 'json',
				data: param,
				error: function(res, res2, res3) {
					<?php do_action('pn_js_error_response', 'ajax'); ?>
				},			
				success: function(res)
				{
					
					if (res['status'] == 'error') {
						$('.resultgo_walletverify').html('<div class="resultfalse">' + res['status_text'] + '</div>');
					}
					
					if (res['status'] == 'success') {
						$(document).JsWindow('hide');
					}
					
					if (res['url']) {
						window.location.href = res['url']; 
					}
					
					thet.prop('disabled', false);
				}
			});
		}
	
        return false;
    });	

});	
<?php	
}

add_action('wp_footer', 'wp_footer_walletsverify');
function wp_footer_walletsverify() {
	
	set_hf_js();
	
	$temp = '
	<div class="walletsverify_box"></div>
	';
		
	echo $temp;	
} 	

function set_walletsverify($user_wallet_id, $ui) {
	global $premiumbox, $wpdb; 	
	
	$user_id = intval($ui->ID);
	
	$arr = array(
		'user_id' => $user_id,
		'error' => '',
		'item' => '',
		'curr' => '',
	);
	
	$status = intval($premiumbox->get_option('usve', 'acc_status'));
	if ($status) {
		if ($user_id) {
			if ($user_wallet_id > 0) {
				$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE id = '$user_wallet_id' AND user_id = '$user_id'");
				if (isset($item->id)) {
					$arr['item'] = $item;
					$currency_id = $item->currency_id;
					$has_verify = intval(get_currency_meta($currency_id, 'has_verify'));
					if (1 == $has_verify) {
						$curr = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' AND currency_status = '1' AND user_wallets = '1' AND id = '$currency_id'"); 
						if (isset($curr->id)) {
							$arr['curr'] = $curr;
						} else {
							$arr['error'] = __('Error! Currency does not exist or disabled', 'pn');
						}
					} else {
						$arr['error'] = __('Error! Currency does not exist or disabled', 'pn');
					}					
				} else {
					$arr['error'] = __('Error! Currency does not exist or disabled', 'pn');
				}
			} else {
				$arr['error'] = __('Error! Account nubmer does not exist', 'pn');
			}			
		} else {
			$arr['error'] = __('Error! Page is available for authorized users only', 'pn');
		}	
	} else {
		$arr['error'] = __('Error! Verification disabled', 'pn');
	}
	
	return $arr;
}

add_action('premium_siteaction_info_userwallets', 'def_premium_siteaction_info_userwallets');
function def_premium_siteaction_info_userwallets() {
	global $wpdb, $premiumbox;	
	
	_method('post');
	_json_head();

	$log = array();
	$log['html'] = '';
	$log['status'] = '';
	$log['status_code'] = 0;
	$log['status_text'] = '';

	$premiumbox->up_mode('post');
	
	$user_wallet_id = intval(is_param_post('id'));
	$redirect_url = pn_strip_input(is_param_post('redir'));
	$ui = wp_get_current_user();
	$data = set_walletsverify($user_wallet_id, $ui);	
	$error = $data['error'];
	$item = $data['item'];
	$curr = $data['curr'];
	$user_id = $data['user_id'];	

	if (strlen($error) < 1) {	
		if (0 == $item->verify) {
			$verify_request = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "uv_wallets WHERE user_wallet_id = '$user_wallet_id' AND status = '0'");
			if ($verify_request < 1) {
				
				$help = pn_strip_text(ctv_ml(get_currency_meta($curr->id, 'help_verify')));
				
				$html = '';
				
				if (strlen($help) > 0) {
					$html .= '<div class="rb_line"><div class="verify_tab_descr">'. apply_filters('comment_text', $help) .'</div></div>';	
				}

				$verify_files = intval(get_currency_meta($curr->id, 'verify_files'));
				if ($verify_files > 0) {
						
					$hf_info = get_hf_filesize_info();	
						
					$html .= '<div class="form_hf" id="accountverify" data-type="accountverify" data-redir="" data-id="' . $user_wallet_id . '">';	
						
						$fileform = '
						<div class="rb_line">
							<div class="verify_acc_syst">(' . $hf_info . ')</div>
						</div>';	
								
						$fileform .= '
						<div class="rb_line">		
							<div class="verify_acc_file js_hf_input_linewrap">
								<input type="file" class="verify_acc_filesome js_hf_input" name="file" value="" />
							</div>				
						</div>
						';
											
						$fileform .= '
						<div class="rb_line">
							<div class="ustbl_bar js_hf_bar"><div class="ustbl_bar_abs js_hf_bar_abs"></div></div>
							<div class="verify_acc_html js_hf_files">
								'. get_usac_files($user_wallet_id) .'
							</div>					
						</div>
						';
						
						$html .= apply_filters('accountverify_fileform', $fileform, $hf_info, $user_wallet_id);
						
					$html .= '</div>';
						
				}

				$html .= '<div class="standart_window_submit"><input type="submit" formtarget="_top" class="verify_action_button" data-redir="' . $redirect_url . '" data-id="' . $user_wallet_id . '" data-title="' . __('Verification request is in process', 'pn') . '" name="" value="' . __('Send a request', 'pn') . '" /></div><div class="resultgo_walletverify js_hf_response" id="accountverify_hf_response"></div>';
	
				$log['status'] = 'success';
				$log['html'] = $html;
		
			} else {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error! Verification request already sent', 'pn');					
			}		
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! Account verified', 'pn');			
		}
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = $error;			
	}	
	
	echo pn_json_encode($log);
	exit;	
}

add_action('premium_siteaction_accountverify', 'def_premium_siteaction_accountverify');
function def_premium_siteaction_accountverify() {
	global $wpdb, $premiumbox;	
	
	_method('post');
	_json_head();

	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0;
	$log['status_text'] = '';

	$premiumbox->up_mode('post');
	
	$redirect_url = trim(is_param_post('redir'));
	
	$user_wallet_id = intval(is_param_post('id'));
	$ui = wp_get_current_user();
	$data = set_walletsverify($user_wallet_id, $ui);	
	$error = $data['error'];
	$item = $data['item'];
	$curr = $data['curr'];
	$user_id = $data['user_id'];	

	if (strlen($error) < 1) {
		if (0 == $item->verify) {
			$verify_request = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "uv_wallets WHERE user_wallet_id = '$user_wallet_id' AND status = '0'");
			if ($verify_request < 1) {					
				$verify_files = intval(get_currency_meta($curr->id, 'verify_files'));
				$count_files = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "hidefiles WHERE itemtype = 'accountverify' AND item_id = '$user_wallet_id'");
				if ($verify_files > 0 and $count_files < 1) {
					$log['status'] = 'error'; 
					$log['status_code'] = 1;
					$log['status_text']= __('Error! You have to upload an image', 'pn');
					echo pn_json_encode($log);
					exit;						
				}	

				$array = array();
				$array['create_date'] = current_time('mysql');
				$array['currency_id'] = $item->currency_id;
				$array['user_id'] = $user_id;
				$array['user_login'] = is_user($ui->user_login);
				$array['user_email'] = is_email($ui->user_email);
				$array['user_wallet_id'] = $user_wallet_id;
				$array['wallet_num'] = pn_strip_input($item->accountnum);
				$array['user_ip'] = pn_real_ip();
				$array['locale'] = pn_strip_input(get_locale());
				$array['status'] = 0;
				$wpdb->insert($wpdb->prefix . 'uv_wallets', $array);	

				$now_locale = get_locale();
				$admin_lang = get_admin_lang();
				set_locale($admin_lang);

				$notify_tags = array();
				$notify_tags['[user_login]'] = $array['user_login'];
				$notify_tags['[purse]'] = $array['wallet_num'];
				$notify_tags['[comment]'] = '';
				$notify_tags = apply_filters('notify_tags_userverify2', $notify_tags, $ui, $item, $array);					
						
				$user_send_data = array(
					'admin_email' => 1,
				);	
				$result_mail = apply_filters('premium_send_message', 0, 'userverify2', $notify_tags, $user_send_data);	 								
				
				set_locale($now_locale);

				$log['status'] = 'success';
				if ($redirect_url) {
					$log['url'] = get_safe_url($redirect_url);
				}				
						
			} else {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error! Verification request already sent', 'pn');					
			}
		} else {
			$log['status'] = 'success';	
			if ($redirect_url) {
				$log['url'] = get_safe_url($redirect_url);
			}			
		}
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = $error;			
	}		
	
	echo pn_json_encode($log);
	exit;
} 

add_filter('hf_upload_action', 'accountverify_hf_upload_action', 10, 4);
function accountverify_hf_upload_action($log, $type, $id, $redir) {
	global $wpdb, $premiumbox;	
	
	if ('accountverify' == $type) {
	
		$user_wallet_id = $id;
		if ($user_wallet_id < 1) { $user_wallet_id = 0; }	
		$ui = wp_get_current_user();
		$data = set_walletsverify($user_wallet_id, $ui);	 
		$error = $data['error'];
		$item = $data['item'];
		$curr = $data['curr'];
		$user_id = $data['user_id'];
		
		if (strlen($error) < 1) {
			if (0 == $item->verify) {
				$verify_request = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "uv_wallets WHERE user_wallet_id = '$user_wallet_id' AND status = '0'");
				if ($verify_request < 1) {					
					$verify_files = intval(get_currency_meta($curr->id, 'verify_files'));
					if ($verify_files > 0) {
						$count_files = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "hidefiles WHERE itemtype = 'accountverify' AND item_id = '$user_wallet_id'");
						if ($count_files < $verify_files) {
							
							$f_data = hf_upload_file($type, $user_wallet_id);
							if (!$f_data['err']) {
								
								$log['status'] = 'success';
								$log['status_code'] = 0;
								$log['response'] = get_usac_files($user_wallet_id);
								if ($redir) {
									$log['url'] = $redir;
								}	
		
							} else {
								$log['status'] = 'error';
								$log['status_code'] = 1;
								$log['status_text'] = $f_data['err_text'];											
							}	
						} else {
							$log['status'] = 'error';
							$log['status_code'] = 1;
							$log['status_text'] = sprintf(__('Error! Maximum number of files for upload: %s', 'pn'), $verify_files);						
						}							
					} else {
						$log['status'] = 'error';
						$log['status_code'] = 1;
						$log['status_text'] = sprintf(__('Error! Maximum number of files for upload: %s', 'pn'), $verify_files);							
					}
				} else {
					$log['status'] = 'success';
					$log['response'] = get_usac_files($user_wallet_id);
				}												
			} else {
				$log['status'] = 'success';
				$log['response'] = get_usac_files($user_wallet_id);
			}
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = $error;			
		}		
	}
	
	return $log;
}

add_filter('hf_delete_action', 'accountverify_hf_delete_action', 10, 2);
function accountverify_hf_delete_action($log, $data) {
	global $wpdb, $premiumbox;	
	
	if ('accountverify' == $data->itemtype) {
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		if (!$user_id) {
			
			$log['status'] = 'error'; 
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! You must authorize', 'pn');
			
			return $log;		
		}	
		
		$user_wallet_id = $data->item_id;
		$wallet = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE id = '$user_wallet_id'");
			
		$access = 0;
		if ($wallet->user_id == $user_id and 0 == $wallet->verify or current_user_can('administrator') or current_user_can('pn_userwallets')) {
			$access = 1;
		}		
		
		if (1 == $access) {
				
			hf_delete_file('accountverify', $data->id);
					
			$log['status'] = 'success';
			$log['response'] = get_usac_files($user_wallet_id);
				
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! File does not exist', 'pn');			
		} 		
		
	}
	
	return $log;
}

function get_usac_files($id) { 

	$temp = get_hf_files('accountverify', $id, 'administrator, pn_userwallets', 1);
	/* from old style */
	$temp = str_replace('hf_files_wrap', 'hf_files_wrap verify_accline_wrap', $temp);
	$temp = str_replace('hf_files_line', 'hf_files_line verify_accline', $temp);
	$temp = str_replace('js_hf_del', 'js_hf_del js_usac_del', $temp);
	/* end from old style */
	
	return $temp;
}

add_action('hf_file_single_action', 'accountverify_hf_file_single_action', 10, 2);
function accountverify_hf_file_single_action($data, $stype) {
	global $wpdb, $premiumbox;
	
	if ('accountverify' == $data->itemtype) {
		if ('download' == $stype) {
			
			$access = 0;

			$ui = wp_get_current_user();
			$user_id = intval($ui->ID);
			
			if ($data->user_id == $user_id or current_user_cans('administrator, pn_userwallets')) {
				$access = 1;
			}

			if ($access) {
				hf_download_file($data->id, $data);
			}			
			
		} else {
			
			pn_only_caps(array('administrator', 'pn_userwallets'));

			hf_view_file($data->id, $data); 		
			
		}
		
		pn_display_mess(__('Error! Access denied', 'pn'));
	}
}