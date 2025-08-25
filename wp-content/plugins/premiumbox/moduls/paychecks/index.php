<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Payment checks[:en_US][ru_RU:]Платежные чеки[:ru_RU]
description: [en_US:]Payment checks[:en_US][ru_RU:]Платежные чеки[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'all_moduls_active_paycheks');
add_action('pn_plugin_activate', 'all_moduls_active_paycheks');
function all_moduls_active_paycheks() {
	global $wpdb;			

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'paycheks'");
    if (0 == $query) { 
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `paycheks` longtext NOT NULL");
    }

}

add_action('admin_menu', 'admin_menu_paycheks');
function admin_menu_paycheks() {
	global $premiumbox;	
	
	add_submenu_page("pn_moduls", __('Payment checks', 'pn'), __('Payment checks', 'pn'), 'administrator', "pn_paycheks", array($premiumbox, 'admin_temp'));
	
} 

add_filter('pn_adminpage_title_pn_paycheks', 'def_adminpage_title_pn_paycheks');
function def_adminpage_title_pn_paycheks($page) {
	
	return __('Payment checks', 'pn');
}

add_action('pn_adminpage_content_pn_paycheks', 'def_adminpage_content_pn_paycheks');
function def_adminpage_content_pn_paycheks() {
	global $wpdb, $premiumbox;

	$form = new PremiumForm();

	$options = array();
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => '',
		'submit' => __('Save', 'pn'),
	);
	$options['title'] = array(
		'view' => 'inputbig',
		'title' => __('Title', 'pn'),
		'default' => $premiumbox->get_option('paycheks', 'title'),
		'name' => 'title',
		'work' => 'input',
		'ml' => 1,
	);
	$options['extext'] = array(
		'view' => 'inputbig',
		'title' => __('Example text', 'pn'),
		'default' => $premiumbox->get_option('paycheks', 'extext'),
		'name' => 'extext',
		'work' => 'input',
		'ml' => 1,
	);
	$options['eximg'] = array(
		'view' => 'uploader',
		'title' => __('Example image', 'pn'),
		'default' => $premiumbox->get_option('paycheks', 'eximg'),
		'name' => 'eximg',
		'work' => 'input',
		'ml' => 1,
	);
	$options['hastext'] = array(
		'view' => 'inputbig',
		'title' => __('Text when image is loaded', 'pn'),
		'default' => $premiumbox->get_option('paycheks', 'hastext'),
		'name' => 'hastext',
		'work' => 'input',
		'ml' => 1,
	);	
	$params_form = array(
		'filter' => 'pn_paycheks_options',
		'button_title' => __('Save', 'pn'),
	);
	$form->init_form($params_form, $options); 
	
}  

add_action('premium_action_pn_paycheks', 'def_premium_action_pn_paycheks');
function def_premium_action_pn_paycheks() {
	global $wpdb, $premiumbox;	

	_method('post');
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));
	
	$title = pn_strip_input(is_param_post_ml('title'));
	$premiumbox->update_option('paycheks', 'title', $title);

	$extext = pn_strip_input(is_param_post_ml('extext'));
	$premiumbox->update_option('paycheks', 'extext', $extext);

	$eximg = pn_strip_input(is_param_post_ml('eximg'));
	$premiumbox->update_option('paycheks', 'eximg', $eximg);

	$hastext = pn_strip_input(is_param_post_ml('hastext'));
	$premiumbox->update_option('paycheks', 'hastext', $hastext);	

	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
			
	$form->answer_form($back_url);
	
}

add_filter('change_bid_status', 'paycheks_change_bidstatus', 500);    
function paycheks_change_bidstatus($data) { 
	global $premiumbox;	

	$set_status = $data['set_status'];
	$stop_action = intval(is_isset($data, 'stop')); 
	if (!$stop_action) {
		if ('realdelete' == $set_status) {
			
			$id = $data['bid']->id;
			hf_delete_item_files('paychecks', $id);
			
		}
	}

	return $data;
}

add_filter('list_tabs_direction', 'list_tabs_direction_paycheks');
function list_tabs_direction_paycheks($list_tabs) {
	
	$list_tabs['paycheks'] = __('Payment checks', 'pn');
	
	return $list_tabs;
}

add_action('tab_direction_paycheks', 'def_tab_direction_paycheks', 20, 2);
function def_tab_direction_paycheks($data, $data_id) {
	
	$options = pn_json_decode(is_isset($data, 'paycheks'));
	if (!is_array($options)) { $options = array(); }
	
	$paybutton = intval(is_isset($options, 'paybutton'));
	
	$lists = list_bid_status();
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Order status for enable download', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php
				$scroll_lists = array();
			
				$statused = is_isset($options, 'statused');
				if (!is_array($statused)) { $statused = array(); }
											
				foreach ($lists as $key => $title) {
					$checked = 0;
					if (in_array($key, $statused)) {
						$checked = 1;
					}	
					$scroll_lists[] = array(
						'title' => $title,
						'checked' => $checked,
						'value' => $key,
					);
				}
				echo get_check_list($scroll_lists, 'paychek_status[]', '', '300');
				?>				
				<div class="premium_clear"></div>
			</div>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Disable "I paid" button before loading receipt', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">									
				<select name="paychek_paybutton" autocomplete="off"> 
					<option value="0" <?php selected($paybutton, 0); ?>><?php _e('No', 'pn'); ?></option>
					<option value="1" <?php selected($paybutton, 1); ?>><?php _e('Yes', 'pn'); ?></option>
				</select>
			</div>			
		</div>		
	</div>		
<?php
}  

add_filter('pn_direction_addform_post', 'paycheks_direction_addform_post');
function paycheks_direction_addform_post($array) {
		
	$options = array();	
	$options['paybutton'] = intval(is_param_post('paychek_paybutton')); 	
	
	$paychek_status = is_param_post('paychek_status');
	$statused = array();
	if (is_array($paychek_status)) {
		foreach ($paychek_status as $st) { 
			$st = is_status_name($st);
			if ($st) {
				$statused[] = $st;
			}
		}
	}
	$options['statused'] = $statused;
	
	$array['paycheks'] = pn_json_encode($options);
	
	return $array;
}

add_filter('merchant_payed_button', 'paychecks_merchant_payed_button', 10000, 3);
function paychecks_merchant_payed_button ($pay_button, $sum_to_pay, $direction) {
	global $bids_data, $wpdb;

	$options = pn_json_decode(is_isset($direction, 'paycheks'));
	if (!is_array($options)) { $options = array(); }
	
	$paybutton = intval(is_isset($options, 'paybutton'));
	if ($paybutton) {
		$status = is_status_name($bids_data->status);
		$statused = is_isset($options, 'statused');
		if (!is_array($statused)) { $statused = array(); }
		if (in_array($status, $statused)) {
			$bid_id = $bids_data->id;
			$count_files = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "hidefiles WHERE itemtype = 'paychecks' AND item_id = '$bid_id'");
			if ($count_files < 1) {
				return '';
			}
		}
	}	
	
	return $pay_button;
}

add_filter('allow_payedbids', 'paychecks_allow_payedbids', 10000, 3);
function paychecks_allow_payedbids ($ind, $bids_data, $direction) {
	global $wpdb;
	
	if (1 == $ind and !_is('is_api')) {
		$options = pn_json_decode(is_isset($direction, 'paycheks'));
		if (!is_array($options)) { $options = array(); }
		
		$paybutton = intval(is_isset($options, 'paybutton'));
		if ($paybutton) {
			$status = is_status_name($bids_data->status);
			$statused = is_isset($options, 'statused');
			if (!is_array($statused)) { $statused = array(); }
			if (in_array($status, $statused)) {
				$bid_id = $bids_data->id;
				$count_files = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "hidefiles WHERE itemtype = 'paychecks' AND item_id = '$bid_id'");
				if ($count_files < 1) {
					return 0;
				}
			}
		}			
	}
	
	return $ind;
}

add_filter('merchant_formstep_after', 'merchant_formstep_after_paychecks', 10, 5); 
function merchant_formstep_after_paychecks($html, $m_in, $direction, $vd1, $vd2) {
	global $bids_data, $wpdb, $premiumbox;
	
	$options = pn_json_decode(is_isset($direction, 'paycheks'));
	if (!is_array($options)) { $options = array(); }
		
	$status = is_status_name($bids_data->status);
	$statused = is_isset($options, 'statused');
	if (!is_array($statused)) { $statused = array(); }
	if (in_array($status, $statused)) {
		$bid_id = intval($bids_data->id);
		$count_files = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "hidefiles WHERE itemtype = 'paychecks' AND item_id = '$bid_id'");
	
		set_hf_js();
				
		$title = pn_strip_input(ctv_ml($premiumbox->get_option('paycheks', 'title')));
		if (strlen($title) < 1) {
			$title = __('Upload your payment receipt', 'pn');
		}
			
		$eximg = pn_strip_input(ctv_ml($premiumbox->get_option('paycheks', 'eximg')));	
		$tooltip = pn_strip_input(ctv_ml($premiumbox->get_option('paycheks', 'extext')));
		
		$hastext = pn_strip_input(ctv_ml($premiumbox->get_option('paycheks', 'hastext')));
		if (strlen($hastext) < 1) {
			$hastext = __('The check has been successfully uploaded.', 'pn');
		}		
			
		if ($count_files < 1) {	
			
			$hf_info = get_hf_filesize_info();	
				
			$fileform = '
			<div class="ustbl_warn">(' . $hf_info . ')</div>
			';	
										
			$fileform .= '
			<div class="ustbl_file">
				<input type="file" class="js_hf_input" name="file" value="" />				
			</div>
			
			<div id="paychecks_hf_response"></div>
			';
													
			$fileform .= '
			<div class="ustbl_bar js_hf_bar"><div class="ustbl_bar_abs js_hf_bar_abs"></div></div>
			';
			
			/*
				$fileform .= '
				<div class="ustbl_res js_hf_files">
					'. get_hf_files('paychecks', $bid_id, 'administrator, pn_bids', 0) .'
				</div>					
				';
			*/
								
			$fileform = apply_filters('paychecks_fileform', $fileform, $hf_info, $bid_id);

		} else {
			
			$fileform = '<div class="resulttrue">' . $hastext . '</div>';
			
		}			
				
		$html .= '
		<div class="ustbl_line">
			<div class="ustbl_line_ins">
				<div class="ustbl_line_left">
					<div class="ustbl_title">' . $title . '</div>
						
						<div class="form_hf" id="paychecks" data-type="paychecks" data-redir="' . get_bids_url($bids_data->hashed) . '" data-id="' . $bid_id . '">
						
						'. $fileform .'
						
						</div>
						
				</div>';
															
				if ($tooltip or $eximg) {
						
					$html .= '
					<div class="ustbl_line_right">
						<div class="ustbl_line_right_abs"></div>';
																	
							if ($eximg) {
								$html .= '<div class="ustbl_eximg"><img src="' . $eximg . '" alt="" /></div>';
							}
																	
							if (strlen($tooltip) > 0) {
								$html .= '<div class="ustbl_descr">' . $tooltip . '</div>';
							}
																	
					$html .= '	
					</div>';
						
				}
															
				$html .= '
					<div class="clear"></div>
			</div>	
		</div>';			
			
	}								
	
	return $html;
}

add_filter('hf_upload_action', 'paychecks_hf_upload_action', 10, 4);
function paychecks_hf_upload_action($log, $type, $id, $redir) {
	global $wpdb, $premiumbox;	
	
	if ('paychecks' == $type) {
	
		$bid = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$id'");
		if (isset($bid->id)) {
	
			$is_true = is_true_userhash($bid);
			$status = is_status_name($bid->status);
	
			if ($is_true) {
				$direction_id = intval($bid->direction_id);
				$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$direction_id'");
				
				$options = pn_json_decode(is_isset($direction, 'paycheks'));
				if (!is_array($options)) { $options = array(); }
		
				$status = is_status_name($bid->status);
				$statused = is_isset($options, 'statused');
				if (!is_array($statused)) { $statused = array(); }
				
				if (in_array($status, $statused)) {

					$need_files = 1;

					$count_files = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "hidefiles WHERE itemtype = 'paychecks' AND item_id = '$id'");
					if ($count_files < $need_files) {
							
						$f_data = hf_upload_file($type, $id);
						if (!$f_data['err']) {
								
							$log['status'] = 'success';
							$log['status_code'] = 0;
							if ($redir) {
								$log['url'] = $redir;
							}							
							
							$log['response'] = get_hf_files('paychecks', $id, 'administrator, pn_bids', 0);

						} else {
							$log['status'] = 'error';
							$log['status_code'] = 1;
							$log['status_text'] = $f_data['err_text'];											
						}	
						
					} else {
						$log['status'] = 'error';
						$log['status_code'] = 1;
						$log['status_text'] = sprintf(__('Error! Maximum number of files for upload: %s', 'pn'), $need_files);						
					}							
	
				} else {
					$log['status'] = 'error';
					$log['status_code'] = 1;
					$log['status_text'] = __('Error! Order does not exist', 'pn');					
				}
			} else {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error! You cannot control this order in another browser', 'pn');			
			}
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! Order does not exist', 'pn');			
		}
		
	}
	
	return $log;
}

add_filter('hf_delete_action', 'paychecks_hf_delete_action', 10, 2);
function paychecks_hf_delete_action($log, $data) {
	global $wpdb, $premiumbox;	
	
	if ('paychecks' == $data->itemtype) {
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		$access = 0;
		if (current_user_cans('administrator, pn_bids')) {
			$access = 1;
		}		
		
		if (1 == $access) {
				
			hf_delete_file('paychecks', $data->id);
					
			$log['status'] = 'success';
			$log['response'] = get_hf_files('paychecks', $data->item_id, 'administrator, pn_bids', 0);	
			
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! File does not exist', 'pn');			
		} 		
		
	}
	
	return $log;
}

add_action('hf_file_single_action', 'paychecks_hf_file_single_action', 10, 2);
function paychecks_hf_file_single_action($data, $stype) {
	global $wpdb, $premiumbox;
	
	if ('paychecks' == $data->itemtype) {
		if ('download' == $stype) {
			
			$access = 0;

			$ui = wp_get_current_user();
			$user_id = intval($ui->ID);
			
			if (current_user_cans('administrator, pn_bids')) {
				$access = 1;
			}

			if ($access) {
				hf_download_file($data->id, $data);
			}			
			
		} else {
			
			pn_only_caps(array('administrator', 'pn_bids'));

			hf_view_file($data->id, $data); 		
			
		}
		
		pn_display_mess(__('Error! Access denied', 'pn'));
	}
}

add_filter('onebid_col4', 'onebid_col_paychecks', 95, 4);
function onebid_col_paychecks($actions, $item, $v, $direction) {	 
	
	$options = pn_json_decode(is_isset($direction, 'paycheks'));
	if (!is_array($options)) { $options = array(); }
		
	$statused = is_isset($options, 'statused');
	if (!is_array($statused)) { $statused = array(); }
	
	if (count($statused) > 0) {
	
		set_hf_js();
		
		$item_id = $item->id;
		$html = '<div class="bids_text"><span class="bt">' . __('Payment check', 'pn') . ':</span><div class="js_hf_files">' . get_hf_files('paychecks', $item_id, 'administrator, pn_bids', 0) . '</div></div>';
		
		$actions['paychecks'] = array(
			'type' => 'html',
			'html' => $html,
		);
	
	}
	
	return $actions;
}