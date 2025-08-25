<?php
if (!defined('ABSPATH')) { exit(); }
 
add_action('premium_js','premium_js_userverify');
function premium_js_userverify() {
	
	$plugin = get_plugin_class();
	$ui = wp_get_current_user();
	$user_id = intval(is_isset($ui,'ID'));
	if ($user_id > 0 and 1 == $plugin->get_option('usve', 'status')) {
?>	
jQuery(function($) { 

	$(document).on('click', '#go_usve', function() {
		$('#usveformed').submit();
	});

	$('#usveformed').ajaxForm({
		dataType:  'json',
		beforeSubmit: function(a, f, o) {
			$('#go_usve').prop('disabled', true);			
		},
		error: function(res, res2, res3) {
			<?php do_action('pn_js_error_response', 'ajax'); ?>
		},		
		success: function(res) {
			if (res['status'] == 'success') {
				$('#usveformedres').html('<div class="resulttrue"><div class="resultclose"></div>' + res['status_text'] + '</div>');
			} 
			if (res['status'] == 'error') {
				$('#usveformedres').html('<div class="resultfalse"><div class="resultclose"></div>' + res['status_text'] + '</div>');
			} 	
			if (res['url']) {
				window.location.href = res['url']; 
			}			
			$('#go_usve').prop('disabled', false);
				
			<?php do_action('ajax_post_form_result', 'site'); ?>
		}
	});	
		
	<?php usve_js(); ?>
});		
<?php	
	} 
}

add_filter('placed_form', 'placed_form_userverifyform');
function placed_form_userverifyform($placed) {
	
	$placed['userverifyform'] = __('User verification form', 'pn');
	
	return $placed;
}

add_filter('userverifyform_filelds', 'def_userverifyform_filelds');
function def_userverifyform_filelds($items) {
	global $wpdb;	
		
	$locale = get_locale();
	$country = get_user_country();
	$ui = wp_get_current_user();

	$txtfields = $wpdb->get_results("
	SELECT * FROM " . $wpdb->prefix . "uv_field 
	WHERE fieldvid IN('0','2') AND status = '1' AND locale IN('0','$locale') AND country = ''
	OR fieldvid IN('0','2') AND status = '1' AND locale IN('0','$locale') AND country LIKE '%\"{$country}\"%'
	ORDER BY uv_order ASC");
	foreach ($txtfields as $txtfield) {
		
		$thetitle = pn_strip_input(ctv_ml($txtfield->title));
		$fieldvid = intval($txtfield->fieldvid);
		$uv_auto = $txtfield->uv_auto;
		$txtvalue = '';
		if ($uv_auto) {
			$txtvalue = strip_uf(is_isset($ui, $uv_auto), $uv_auto);
		}
		$datas = explode("\n", ctv_ml($txtfield->datas));
		$options = array();
		$options[0] = __('No selected', 'pn');
		foreach ($datas as $key => $da) {
			$key = $key + 1;
			$da = pn_strip_input($da);
			if (strlen($da) > 0) {
				$options[$key] = $da;
			}	
		}	
			
		$items['uv' . $txtfield->id] = array(
			'name' => 'uv' . $txtfield->id,
			'title' => $thetitle,
			'placeholder' => '',
			'req' => $txtfield->uv_req,
			'value' => $txtvalue,
		);
		
		if (0 == $fieldvid) {
			$items['uv' . $txtfield->id]['type'] = 'input';
			$items['uv' . $txtfield->id]['tooltip'] = pn_strip_input(ctv_ml($txtfield->helps));
		} else {
			$items['uv' . $txtfield->id]['type'] = 'select';
			$items['uv' . $txtfield->id]['options'] = $options;
		}
		
	}		
		
	return $items;
}

function usve_userverify_shortcode($atts, $content) { 
	global $wpdb;

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);

	$plugin = get_plugin_class();

	$temp = apply_filters('before_userverify_page', '');
	
	if (1 == $plugin->get_option('usve','status')) { 
		if ($user_id) {
			if (isset($ui->user_verify) and 0 == $ui->user_verify) { 
				$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "verify_bids WHERE auto_status = '1' AND user_id = '$user_id' AND status IN('1','2')");
				if (0 == $cc) {
					
					$verify_text = trim(ctv_ml($plugin->get_option('usve', 'text')));
					if (strlen($verify_text) > 0) {
						$temp .= ' 
						<div class="userverify_text">
							<div class="userverify_text_ins">
								<div class="userverify_text_abs"></div>
								<div class="text">
									' . apply_filters('the_content', $verify_text) . '
										<div class="clear"></div>
								</div>
							</div>
						</div>
						';
					}
					
					$locale = get_locale();

					$temp .= '
					<div class="userverify_div_wrap">
						<div class="userverify_div_wrap_ins">';
					
							if (is_older_browser()) {

								$temp .= '<div class="resultfalse">' . __('Error! You are using an old version of your browser!', 'pn') . '</div>';

							} else {

								$temp .= '
								<div class="userverify_div_title">
									<div class="userverify_div_title_ins">
										'. __('Personal information', 'pn') .'
									</div>
								</div>
								<div class="userverify_div">
									<div class="userverify_div_ins">
								';

									$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "verify_bids WHERE auto_status = '1' AND user_id = '$user_id' AND status = '0'");
										
									$array = array();
									$array['create_date'] = current_time('mysql');
									$array['status'] = 0;
									$array['auto_status'] = 1;
									$array['user_id'] = $user_id;
									$array['user_login'] = is_user($ui->user_login);
									$array['user_email'] = is_email($ui->user_email);
									$array['locale'] = $locale;						
									
									if (isset($data->id)) {
										$id = $data->id;
										$wpdb->update($wpdb->prefix . 'verify_bids', $array, array('id' => $id));					
									} else {
										$wpdb->insert($wpdb->prefix . 'verify_bids', $array);
										$id = $wpdb->insert_id;
									}
							
									$temp .= '
									<form action="' . get_pn_action('userverify_created') . '" id="usveformed" method="post">
										<input type="hidden" name="id" value="' . $id . '" />
									';
									
										$fields = get_form_fields('userverifyform');
										$temp .= prepare_form_fileds($fields, 'userverifyform', 'uv');			
									
									$temp .= '
									</form>';
							
								$temp .= '
									</div>
								</div>';
		
								$max_mb = pn_max_upload();
								$fileupform = pn_enable_filetype();
								$allowed = array();
								foreach ($fileupform as $file_k => $file_v) {
									$allowed[] = $file_k;
								}
								
								$country = get_user_country();
						
								$fields = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_field WHERE 
								fieldvid = '1' AND status = '1' AND locale IN('0','$locale') AND country = ''
								OR fieldvid = '1' AND status = '1' AND locale IN('0','$locale') AND country LIKE '%\"{$country}\"%'
								ORDER BY uv_order ASC");
								if (count($fields) > 0) {
								
									$temp .= '
									<div class="userverify_div_title">
										<div class="userverify_div_title_ins">
											'. __('Scans or photos of documents', 'pn') .'
										</div>
									</div>							
									<div class="userverify_div">
										<div class="userverify_div_ins">';
						
										foreach ($fields as $field) {
										
											$temp .= '
											<form action="' . get_pn_action('userverify_upload') . '" class="usveajaxform" enctype="multipart/form-data" method="post">
												<input type="hidden" name="theid" value="' . $field->id . '" />
												<input type="hidden" name="id" value="' . $id . '" />
											';
											
											$thetitle = pn_strip_input(ctv_ml($field->title));
											
											$req_txt = '';
											if (1 == $field->uv_req) {
												$req_txt = '<span class="req">*</span>';
											}				

											$tooltip = pn_strip_input(ctv_ml($field->helps));
											$eximg = pn_strip_input(ctv_ml($field->eximg));
											
											$file_line = '
											<div class="ustbl_line">
												<div class="ustbl_line_ins">
													<div class="ustbl_line_left">
														<div class="ustbl_title">' . $thetitle . ' ' . $req_txt . '</div>
														<div class="ustbl_warn">(' . strtoupper(implode(', ', $allowed)) . ', ' . __('max.', 'pn') . ' ' . $max_mb . '' . __('MB', 'pn') . ')</div>
														<div class="ustbl_file"><input type="file" class="usveupfilesome" name="file" value="" /></div>
														<div class="ustbl_res">';

															if (function_exists('get_usvedoc_temp')) {
																$file_line .= get_usvedoc_temp($id, $field->id);
															}

														$file_line .= '
														</div>
														<div class="ustbl_bar"><div class="ustbl_bar_abs"></div></div>
													</div>';
													
													if ($tooltip or $eximg) {
														$file_line .= '
														<div class="ustbl_line_right">
															<div class="ustbl_line_right_abs"></div>';
															
															if ($eximg) {
																$file_line .= '<div class="ustbl_eximg"><img src="' . $eximg . '" alt="" /></div>';
															}
															
															if (strlen($tooltip) > 0) {
																$file_line .= '<div class="ustbl_descr">' . $tooltip . '</div>';
															}
															
														$file_line .= '	
														</div>';
													}
													
													$file_line .= '
														<div class="clear"></div>
												</div>	
											</div>
											';
											
											$temp .= apply_filters('userverify_fileform_line', $file_line, $field, $fileupform, $max_mb);
										
											$temp .= '</form>';
											
										}		
							
									$temp .= '
										</div>
									</div>	
									';		
						
								}
							
								$temp .= '
								<div class="userverify_div">
									<div class="userverify_div_ins">
										<div id="usveformedres"></div>
										<div class="uv_line has_submit">
											<input type="submit" name="submit" formtarget="_top" id="go_usve" value="' . __('Send a request', 'pn') . '" />
										</div>								
									</div>
								</div>	
								';

							}

					$temp .= '
						</div>
					</div>';
				}
			}
		
			$lists = array(
				'date' => __('Date', 'pn'),
				'status' => __('Status', 'pn'),
			);
			$lists = apply_filters('lists_table_userverify', $lists);
			$lists = (array)$lists;		
			
			$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "verify_bids WHERE user_id = '$user_id' AND auto_status = '1' AND status != '0' ORDER BY create_date DESC");			
			$count = count($datas);
				
			$date_format = get_option('date_format');
			$time_format = get_option('time_format');
					
			$table_list = '<table>';
			$table_list .= '<thead><tr>';
			foreach ($lists as $list_key => $list_val) {
				$table_list .= '<th class="th_' . $list_key . '">' . $list_val . '</th>';
			}
			$table_list .= '</tr></thead><tbody>';				
					
			$s = 0;
			foreach ($datas as $item) {  $s++;
				if ($s%2 == 0) { $odd_even = 'even'; } else { $odd_even = 'odd'; }
						
				$table_list .= '<tr>';
				foreach ($lists as $key => $title) {
					$table_list .= '<td>';
								
					$one_line = '';							
					if ('date' == $key) {
						$one_line = get_pn_time($item->create_date, "{$date_format}, {$time_format}");
					}
					if ('status' == $key) {
						if (1 == $item->status) {
							$status = '<strong>' . __('Pending request', 'pn') . '</strong>';
						} elseif (2 == $item->status) {
							$status = '<span class="bgreen">' . __('Confirmed request', 'pn') . '</span>';
						} elseif (3 == $item->status) {
							$status = '<span class="bred">' . __('Request is declined', 'pn') . '</span>';
							$comment = pn_strip_text(is_isset($item, 'comment'));
							if (strlen($comment) > 0) {
								$status .= '<div class="userverify_comment">' . $comment . '</div>';
							}
						} else {
							$status = '<strong>' . __('automatic', 'pn') . '</strong>';
						}
						$one_line = $status;
					}				
					
					$table_list .= apply_filters('body_list_userverify', $one_line, $item, $key, $title, $date_format, $time_format);
					$table_list .= '</td>';	
				}
				$table_list .= '</tr>';
				
			}	

			if (0 == $s) {
				$table_list .= '<tr><td colspan="' . count($lists) . '"><div class="no_items"><div class="no_items_ins">' . __('No item', 'pn') . '</div></div></td></tr>';
			}	

			$table_list .= '</tbody></table>';	
					
			$array = array(
				'[table_list]' => $table_list,
			);					
					
			$temp_form = '
			<div class="userverify_table pntable_wrap">	
				<div class="pntable_wrap_ins">
						
					<div class="pntable_wrap_title">
						<div class="pntable_wrap_title_ins">
							'. __('Requests for verification', 'pn') .':
						</div>
					</div>
						<div class="clear"></div>
							
					<div class="pntable">	 
						<div class="pntable_ins">
						
						[table_list]
								
						</div>
					</div>
					
				</div>
			</div>			
			';
			
			$temp_form = apply_filters('userverify_form_temp', $temp_form);
			$temp .= replace_tags($array, $temp_form);				
				
		} else {
			$temp .= '<div class="resultfalse">'. __('Error! You must be logged in', 'pn') .'</div>';
		}
	} else {
		$temp .= '<div class="resultfalse">'. __('Error! Orders for person verification are not accepted', 'pn') .'</div>';
	}

	$temp .= apply_filters('after_userverify_page', '');
	
	return $temp;
}
add_shortcode('userverify', 'usve_userverify_shortcode');
 
add_action('premium_siteaction_userverify_created', 'def_premium_siteaction_userverify_created');
function def_premium_siteaction_userverify_created() {
	global $wpdb;	
		
	_method('post');
	_json_head();
		
	$log = array();	
	$log['status'] = '';
	$log['status_code'] = 0;
	$log['status_text'] = '';
	$log['errors'] = array();
		
	$plugin = get_plugin_class();
		
	$plugin->up_mode('post');
	
	$log = _log_filter($log, 'userverifyform');	 
		
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
		
	if (!$log['status_code']) {	
		if (!$user_id) {
			$log['status'] = 'error'; 
			$log['status_code'] = 1;
			$log['status_text']= __('Error! You must be logged in', 'pn');
			echo pn_json_encode($log);
			exit;		
		}
	}
		
	if (!$log['status_code']) {	
		if (1 != $plugin->get_option('usve', 'status')) {
			$log['status'] = 'error'; 
			$log['status_code'] = 1;
			$log['status_text']= __('Error! Verification form is disabled', 'pn');
			echo pn_json_encode($log);
			exit;		
		}
	}
			
	$userverify_url = apply_filters('userverify_redirect', $plugin->get_page('userverify'));	
		
	if (!$log['status_code']) {	
		$id = intval(is_param_post('id'));
		if ($id < 1) { $id = 0; }
		if ($id and $user_id) { 
			if (isset($ui->user_verify) and 0 == $ui->user_verify) {
				$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "verify_bids WHERE user_id = '$user_id' AND auto_status = '1' AND status IN('1','2')");
				if (0 == $cc) {
					$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "verify_bids WHERE user_id = '$user_id' AND status = '0' AND auto_status = '1' AND id = '$id'");
					if (isset($data->id)) {

						$locale = get_locale();
						$country = get_user_country();
			
						$fields = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_field WHERE 
						fieldvid IN('0','2') AND status = '1' AND locale IN('0','$locale') AND country = ''
						OR fieldvid IN('0','2') AND status = '1' AND locale IN('0','$locale') AND country LIKE '%\"{$country}\"%'
						ORDER BY uv_order ASC");
						foreach ($fields as $field) {
							$field_id = $field->id;
							$title_field = pn_strip_input(ctv_ml($field->title));
							$uv_req = intval($field->uv_req);

							$fieldvid = intval($field->fieldvid);
							if (2 == $fieldvid) {
								$datas = explode("\n", ctv_ml($field->datas));
								$options = array();
								foreach ($datas as $key => $da) {
									$key = $key + 1;
									$da = pn_strip_input($da);
									if (strlen($da) > 0) {
										$options[$key] = $da;
									}	
								}	
								$post_value = intval(is_param_post('uv' . $field->id));
								$value = pn_maxf_mb(pn_strip_input(is_isset($options, $post_value)), 500);
							} else {
								$value = strip_uf(is_param_post('uv' . $field->id), $field->uv_auto);
							}
								
							$us_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_field_user WHERE uv_id = '$id' AND uv_field = '$field_id'");
								
							$arr = array();
							$arr['user_id'] = $user_id;
							$arr['uv_data'] = $value;
							$arr['uv_id'] = $id;
							$arr['uv_field'] = $field_id;
							$arr['fieldvid'] = $fieldvid;
								
							if (isset($us_data->id)) {
								$wpdb->update($wpdb->prefix . 'uv_field_user', $arr, array('id' => $us_data->id)); 
							} else {
								$wpdb->insert($wpdb->prefix . 'uv_field_user', $arr);
							}
								
							if (1 == $uv_req and !$value) {	
								$log['status'] = 'error';
								$log['status_code'] = 1;
								$log['status_text'] = sprintf(__('Error! You have not entered "%s"', 'pn'), $title_field);		
								echo pn_json_encode($log);
								exit;
							}
						}				
						
						$fields = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_field WHERE 
						fieldvid = '1' AND status = '1' AND locale IN('0','$locale') AND country = ''
						OR fieldvid = '1' AND status = '1' AND locale IN('0','$locale') AND country LIKE '%\"{$country}\"%'
						ORDER BY uv_order ASC");
						foreach ($fields as $field) {
							$field_id = $field->id;
							$title_field = pn_strip_input(ctv_ml($field->title));
							$uv_req = intval($field->uv_req);
							if (1 == $uv_req) {
								$us_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_field_user WHERE uv_id = '$id' AND uv_field = '$field_id'");
								if (!isset($us_data->uv_data) or !$us_data->uv_data) {
									
									$log['status'] = 'error';
									$log['status_code'] = 1;
									$log['status_text'] = sprintf(__('Error! You have not uploaded %s', 'pn'), $title_field);
									echo pn_json_encode($log);
									exit;
								}
							}
						}
						
						$array = array();
						$array['create_date'] = current_time('mysql');
						$array['user_id'] = $user_id;
						$array['user_login'] = is_user($ui->user_login);
						$array['user_email'] = is_email($ui->user_email);
						$array['user_ip'] = pn_real_ip();
						$array['status'] = 1;
						$array['auto_status'] = 1;
						$wpdb->update($wpdb->prefix . 'verify_bids', $array, array('id' => $id));
							
						$now_lang = get_locale();
						$admin_lang = get_admin_lang();
						set_locale($admin_lang);
			
						$notify_tags = array();
						$notify_tags = apply_filters('notify_tags_userverify1', $notify_tags, $ui, $array);		

						$user_send_data = array(
							'admin_email' => 1,
						);
						$result_mail = apply_filters('premium_send_message', 0, 'userverify1', $notify_tags, $user_send_data); 
															
						set_locale($now_lang);

						$log['url'] = $userverify_url; 							
							
					} else {
						$log['status_code'] = 1;
						$log['url'] = $userverify_url;
					}
				} else {
					$log['status_code'] = 1;
					$log['url'] = $userverify_url;
				}	
			} else {
				$log['status_code'] = 1;
				$log['url'] = $userverify_url;
			}	
		} else {
			$log['status_code'] = 1;		
			$log['url'] = $userverify_url;
		}
	}
					
	echo pn_json_encode($log);
	exit;
}

add_action('premium_siteaction_delete_userverify_file', 'def_premium_siteaction_delete_userverify_file');
function def_premium_siteaction_delete_userverify_file() {
	global $wpdb;

	_method('post');	
	_json_head();
		
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0;
	$log['status_text'] = '';

	$plugin = get_plugin_class();
		
	$plugin->up_mode('post');

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
		
	if (!$user_id) {
		$log['status'] = 'error'; 
		$log['status_code'] = 1;
		$log['status_text']= __('Error! You must authorize', 'pn');
		echo pn_json_encode($log);
		exit;		
	}
		
	$id = intval(is_param_post('id'));
	if ($id < 1) { $id = 0; } /* id юзер поля */

	if ($id > 0) {
		$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_field_user WHERE fieldvid = '1' AND id = '$id'");
		if (isset($item->id)) {
			$uv_id = $item->uv_id;
			$item_id = $item->id;
				
			$access = 0;
				
			if (current_user_can('administrator') or current_user_can('pn_userverify')) {
				$access = 1;
			}
				
			if (1 != $access) {
				$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "verify_bids WHERE user_id = '$user_id' AND auto_status = '1' AND status = '0' AND id = '$uv_id'");
				if (isset($data->id)) {
					$access = 1;
				}	
			}
				
			if ($access) {
				$res = apply_filters('item_usfielduser_delete_before', pn_ind(), $item_id, $item);
				if ($res['ind']) {
					$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "uv_field_user WHERE id = '$item_id'");
					do_action('item_usfielduser_delete', $item_id, $item, $result);
					$log['status'] = 'success';
				}
			}		
		}
	}

	echo pn_json_encode($log);
	exit;
}

add_action('premium_siteaction_userverify_upload', 'def_premium_siteaction_userverify_upload');
function def_premium_siteaction_userverify_upload() {
	global $wpdb;	
	
	_method('post');	
	_json_head();
		
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0;
	$log['status_text'] = '';	
		
	$plugin = get_plugin_class();
		
	$plugin->up_mode('post');
		
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
		
	if (!$user_id) {
		$log['status'] = 'error'; 
		$log['status_code'] = 1;
		$log['status_text']= __('Error! You must authorize', 'pn');
		echo pn_json_encode($log);
		exit;		
	}
		
	if (1 != $plugin->get_option('usve','status')) {
		$log['status'] = 'error'; 
		$log['status_code'] = 1;
		$log['status_text']= __('Error! You must authorize', 'pn');
		echo pn_json_encode($log);
		exit;		
	}	
					
	$id = intval(is_param_post('id'));
	if ($id < 1) { $id = 0; } /* id заявки */
		
	$theid = intval(is_param_post('theid'));
	if ($theid < 1) { $theid = 0; }	/* id поля */
		
	$userverify_url = apply_filters('userverify_redirect', $plugin->get_page('userverify'));
		
	if ($id) {
		$locale = get_locale();
			
		$field_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_field WHERE fieldvid = '1' AND status = '1' AND id = '$theid' AND locale IN('0','$locale')");
		if (!isset($field_data->id)) {
			$log['status'] = 'error';
			$log['status_code'] = 1; 
			$log['status_text'] = __('Error! Error loading file', 'pn');			
			echo pn_json_encode($log);
			exit;	
		}		
			
		if (isset($ui->user_verify) and 0 == $ui->user_verify) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "verify_bids WHERE user_id = '$user_id' AND auto_status = '1' AND status = '0' AND id = '$id'");
			if (isset($data->id)) {
					
				$log = download_usve_file($log, $id, $theid, $user_id);												
					
			} else {

				$log['status_code'] = 1;				
				$log['url'] = $userverify_url;

			}
		} else {
			$log['status_code'] = 1;			
			$log['url'] = $userverify_url;
		}
		
	} else {
		$log['status_code'] = 1;
		$log['url'] = $userverify_url;
	}		
	
	echo pn_json_encode($log);
	exit;
}