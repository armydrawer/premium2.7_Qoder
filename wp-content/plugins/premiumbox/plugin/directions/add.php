<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_add_directions', 'pn_admin_title_pn_add_directions');
	function pn_admin_title_pn_add_directions() {
	global $db_data, $wpdb;	
		
		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$db_data = '';
		
		if ($item_id) {
			$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$item_id'");
			if (isset($db_data->id)) {
				$data_id = $db_data->id;
			}	
		}		
		
		if ($data_id) {
			return __('Edit exchange direction', 'pn');
		} else {
			return __('Add exchange direction', 'pn');
		}	
		
	}

	add_action('pn_adminpage_content_pn_add_directions', 'def_pn_admin_content_pn_add_directions');
	function def_pn_admin_content_pn_add_directions() {
		global $db_data, $wpdb;

		$form = new PremiumForm();

		$data_id = intval(is_isset($db_data, 'id'));
		if ($data_id) {
			$title = __('Edit exchange direction', 'pn');
		} else {
			$title = __('Add exchange direction', 'pn');
		}
		
		$title .= ' "<span id="title1"></span>-<span id="title2"></span>"';
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_directions'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);	
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_directions'),
				'title' => __('Add new','pn')
			);			
			if (0 != is_isset($db_data,'direction_status') and 1 == is_isset($db_data,'auto_status')) {
				$back_menu['direction_link'] = array(
					'link' => get_exchange_link($db_data->direction_name),
					'title' => __('View', 'pn'),
					'atts' => array('target' => "blank"),
				);			
			}
		}
		$form->back_menu($back_menu, $db_data);
		
		$dir_c = is_course_direction($db_data, '', '', 'admin');
		
		$list_tabs = array(
			'tab1' => __('General settings', 'pn'), 
			'tab2' => __('Rate', 'pn') . ' <span class="one_tabs_submenu">[<span id="rate1">' . is_isset($dir_c, 'give') . '</span> => <span id="rate2">' . is_isset($dir_c, 'get') . '</span>]</span>',
			'tab3' => __('Payment systems fees', 'pn'),
			'tab4' => __('Exchange office fees', 'pn'),
			'tab5' => __('Exchange amount', 'pn'),
			'tab6' => __('Customer information', 'pn'),
			'tab7' => __('Limitations and checking', 'pn'),
			'dirtemp' => __('Notification settings', 'pn'),
			'tab8' => __('Custom fields', 'pn'),
		);	
		
		$params_form = array(
			'key' => 'tab_direction',
			'hidden_data' => array('data_id' => $data_id),
			'page_title' => $title,
			'tabs' => apply_filters('list_tabs_direction', $list_tabs, $db_data),
			'button_title' => __('Save', 'pn'),
			'data' => $db_data,
			'data_id' => $data_id,
		);
		$form->init_tab_form($params_form);	
	?>
		
	<script type="text/javascript">
	jQuery(function($) {
		
		function set_visible_title() {
			
			var direction_status = parseInt($('#direction_status').val());
			if (direction_status == 1) {
				$('.add_tabs_pagetitle').removeClass('notactive');
			} else {
				$('.add_tabs_pagetitle').addClass('notactive');
			}
			
			var title1 = $('#currency_id_give option:selected').html().replace(new RegExp("-", 'g'), '');
			var title2 = $('#currency_id_get option:selected').html().replace(new RegExp("-", 'g'), '');
			$('#title1').html(title1);
			$('#title2').html(title2);
			
		}
		
		$('#direction_status, #currency_id_give, #currency_id_get').change(function() {
			
			set_visible_title();
			
		});
		
		set_visible_title();	
		
		function set_tech_title() {
			
			var title = $.trim($('.tech_name').val());
			if (title.length > 0) {
				$('title').html(title);
			}
			
		}
		
		$('.tech_name').change(function() {
			
			set_tech_title();
			
		});
		
		set_tech_title();	
		
		function set_now_decimal(obj, dec) {
			
			if (obj.length > 0) {
				var sum = obj.val().replace(new RegExp(",", 'g'), '.');
				var len_arr = sum.split('.');
				var len_data = len_arr[1];
				if (typeof len_data !== typeof undefined) {
					var len = len_data.length;
					if (len > dec) {
						var new_data = len_arr[0] + '.' + len_data.substr(0, dec);
						obj.val(new_data);
					}
				} else {
					var new_data = sum;
				}
			}
			
		}
		
		function set_valut_decimal() {
			
			var decimal1 = $('#currency_id_give option:selected').attr('data-decimal');
			var decimal2 = $('#currency_id_get option:selected').attr('data-decimal');
			set_now_decimal($('#course_give'), decimal1);
			set_now_decimal($('#course_get'), decimal2);
			
		}
		
		$('#direction_status, #currency_id_give, #currency_id_get').change(function() {
			
			set_valut_decimal();
			
		});
		
		$('#course_give, #course_get').change(function() {
			
			set_valut_decimal();
			
		});
		
		$('#course_give, #course_get').keyup(function() {
			
			set_valut_decimal();
			
		});
		
		set_valut_decimal();		

	});
	</script>	
	<?php
	}

}	

add_action('premium_action_pn_add_directions', 'def_premium_action_pn_add_directions');
function def_premium_action_pn_add_directions() {
	global $wpdb;

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_directions'));
		
	$data_id = intval(is_param_post('data_id'));
		
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}	
			
	$array = array();
		
	$array['currency_id_give'] = $currency_id_give = intval(is_param_post('currency_id_give'));
	$array['currency_id_get'] = $currency_id_get = intval(is_param_post('currency_id_get'));
				
	$xml_value1 = $xml_value2 = '';
	$title_value1 = $title_value2 = '';
	$status_currency1 = $status_currency2 = 0;
				
	$currency_data1 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_give'");
	if (isset($currency_data1->id)) {
		$array['psys_id_give'] = $currency_data1->psys_id;
		$xml_value1 = is_xml_value($currency_data1->xml_value);
		$title_value1 = get_currency_title($currency_data1);
		$status_currency1 = intval($currency_data1->currency_status);
	} else {
		$form->error_form(__('Error! Send currency does not exist', 'pn'));
	}
				
	$currency_data2 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_get'");
	if (isset($currency_data2->id)) {
		$array['psys_id_get'] = $currency_data2->psys_id;
		$xml_value2 = is_xml_value($currency_data2->xml_value);
		$title_value2 = get_currency_title($currency_data2);
		$status_currency2 = intval($currency_data2->currency_status);
	} else {
		$form->error_form(__('Error! Receive currency does not exist', 'pn'));
	}
		
	if (1 != $status_currency1) {
		$form->error_form(__('Error! Send currency deactivated', 'pn'));
	}
		
	if (1 != $status_currency2) {
		$form->error_form(__('Error! Receive currency deactivated', 'pn'));
	}	

	$array['direction_status'] = intval(is_param_post('direction_status'));
				
	$tech_name = pn_strip_input(is_param_post('tech_name'));
	if (!$tech_name) {
		$tech_name = $title_value1 . ' &rarr; ' . $title_value2;
	}
	$array['tech_name'] = $tech_name;
				
	$direction_name = trim(is_param_post('direction_name'));
	if ($direction_name) {
		$direction_name = is_direction_name($direction_name);
	} 
	if (!$direction_name) {
		$direction_permalink_temp = apply_filters('direction_permalink_temp', '[xmlv1]_to_[xmlv2]');
		$direction_permalink_temp = str_replace('[xmlv1]', $xml_value1, $direction_permalink_temp);
		$direction_permalink_temp = str_replace('[xmlv2]', $xml_value2, $direction_permalink_temp);
		$direction_name = is_direction_name($direction_permalink_temp);
		$direction_name = strtolower($direction_name);
	}		
				
	$array['direction_name'] = unique_direction_name($direction_name, $data_id);		
					
	$course_give = is_sum(is_param_post('course_give'), intval($currency_data1->currency_decimal));
	$course_get = is_sum(is_param_post('course_get'), intval($currency_data2->currency_decimal));
				
	if ($course_give <= 0) {
		$course_give = 1;
	}
	
	if ($course_get <= 0) {
		$course_get = 1;
	}	
	
	$array['course_give'] = $course_give;
	$array['course_get'] = $course_get;
	
	$array['profit_sum1'] = is_sum(is_param_post('profit_sum1'));	
	$array['profit_pers1'] = is_sum(is_param_post('profit_pers1'));
	$array['profit_sum2'] = is_sum(is_param_post('profit_sum2'));	
	$array['profit_pers2'] = is_sum(is_param_post('profit_pers2'));					
		
	$array['pay_com1'] = intval(is_param_post('pay_com1'));
	$array['pay_com2'] = intval(is_param_post('pay_com2'));
	$array['nscom1'] = intval(is_param_post('nscom1'));
	$array['nscom2'] = intval(is_param_post('nscom2'));
	$array['dcom1'] = intval(is_param_post('dcom1'));
	$array['dcom2'] = intval(is_param_post('dcom2'));
	$array['com_det1'] = intval(is_param_post('com_det1'));
	$array['com_det2'] = intval(is_param_post('com_det2'));	
	$array['com_sum1'] = is_sum(is_param_post('com_sum1'));	
	$array['com_pers1'] = is_sum(is_param_post('com_pers1'));
	$array['com_sum2'] = is_sum(is_param_post('com_sum2'));	
	$array['com_pers2'] = is_sum(is_param_post('com_pers2'));	
	$array['minsum1com'] = is_sum(is_param_post('minsum1com'));
	$array['minsum2com'] = is_sum(is_param_post('minsum2com'));			
		
	$array['com_box_sum1'] = is_sum(is_param_post('com_box_sum1'));	
	$array['com_box_pers1'] = is_sum(is_param_post('com_box_pers1'));
	$array['com_box_min1'] = is_sum(is_param_post('com_box_min1'));	
	$array['com_box_ns1'] = intval(is_param_post('com_box_ns1'));	
	$array['com_box_det1'] = intval(is_param_post('com_box_det1'));				
	$array['com_box_sum2'] = is_sum(is_param_post('com_box_sum2'));	
	$array['com_box_pers2'] = is_sum(is_param_post('com_box_pers2'));
	$array['com_box_min2'] = is_sum(is_param_post('com_box_min2'));
	$array['com_box_ns2'] = intval(is_param_post('com_box_ns2'));	
	$array['com_box_det2'] = intval(is_param_post('com_box_det2'));					
	
	$array['min_sum1'] = is_sum(is_param_post('min_sum1'));
	$array['max_sum1'] = is_sum(is_param_post('max_sum1'));
	$array['min_sum2'] = is_sum(is_param_post('min_sum2'));
	$array['max_sum2'] = is_sum(is_param_post('max_sum2'));

	$array['mailtemp'] = pn_strip_text(is_param_post_ml('dirtemp'));
	
	$maildata = array(
		'email' => pn_strip_input(is_param_post('maildata_email')),
		'phone' => pn_strip_input(is_param_post('maildata_phone')),
		'telegram' => pn_strip_input(is_param_post('maildata_telegram')),
	);
	$array['maildata'] = pn_json_encode($maildata);
		
	$ui = wp_get_current_user();
	$user_id = intval(is_isset($ui, 'ID'));

	$array['edit_date'] = current_time('mysql');
	$array['edit_user_id'] = $user_id;
	$array['auto_status'] = 1;	
	$array = apply_filters('pn_direction_addform_post', $array, $last_data);

	if ($data_id) {
		$res = apply_filters('item_direction_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {
			$result = $wpdb->update($wpdb->prefix . 'directions', $array, array('id' => $data_id));
			do_action('item_direction_edit', $data_id, $array, $last_data, $result);
			$res_errors = _debug_table_from_db($result, 'directions', $array);
			_display_db_table_error($form, $res_errors);			
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}
	} else {
		$res = apply_filters('item_direction_add_before', pn_ind(), $array);
		if ($res['ind']) {
			$array['create_date'] = current_time('mysql');
			$result = $wpdb->insert($wpdb->prefix . 'directions', $array);
			$data_id = $wpdb->insert_id;
			if ($result) {
				do_action('item_direction_add', $data_id, $array);
			} else {
				$res_errors = _debug_table_from_db($result, 'directions', $array);
				_display_db_table_error($form, $res_errors);				
			}
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}
	}	
				
	if ($data_id) {					
			
		$list_directions_temp = apply_filters('list_directions_temp', array());
		if (is_array($list_directions_temp)) {
			foreach ($list_directions_temp as $key => $title) {						
				$value = pn_strip_text(is_param_post_ml($key));
				update_direction_meta($data_id, $key, $value);
				delete_direction_txtmeta($data_id, $key);
			}
		}
		
		/* custom fields */
		$cfs_del = array();
		$cf_directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "cf_directions WHERE direction_id = '$data_id'");
		foreach ($cf_directions as $cf_item) {
			$cfs_del[$cf_item->cf_id] = $cf_item->cf_id;
		}	
		if (isset($_POST['cf'])) {
			$cf = explode(',', $_POST['cf']);	
			foreach ($cf as $cfid) {
				$cfid = intval($cfid);
				if (!isset($cfs_del[$cfid])) {		
					$arr = array();
					$arr['direction_id'] = $data_id;
					$arr['cf_id'] = $cfid;
					$wpdb->insert($wpdb->prefix . 'cf_directions', $arr);	
				} else {
					unset($cfs_del[$cfid]);
				}
			}
		}		
		foreach ($cfs_del as $tod) {
			$wpdb->query("DELETE FROM " . $wpdb->prefix . "cf_directions WHERE cf_id = '$tod' AND direction_id = '$data_id'");			
		}					
		/* end custom fields */
				
	}

	$url = admin_url('admin.php?page=pn_add_directions&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
	
}	
 
add_action('tab_direction_tab1', 'direction_tab_direction_tab1', 10, 2);
function direction_tab_direction_tab1($data, $data_id) {
	
	$currencies = list_currency(__('No item', 'pn'), 1);
	$form = new PremiumForm();
?>							
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Send', 'pn'); ?></span></div>
				
			<?php 
			$atts = array();
			$atts['id'] = 'currency_id_give';
			$option_data = array();
			$opts = array();
			foreach ($currencies as $key => $val) {
				$option_data[$key] = 'data-decimal="' . $val['decimal'] . '"';
				$opts[$key] = $val['title'];
			}	
			$form->select_search('currency_id_give', $opts, is_isset($data, 'currency_id_give'), $atts, $option_data); 
			?>
			
			<?php do_action('tab_dir_direction', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Receive', 'pn'); ?></span></div>
				
			<?php 
			$atts = array();
			$atts['id'] = 'currency_id_get';
			$option_data = array();
			$opts = array();
			foreach ($currencies as $key => $val) {
				$option_data[$key] = 'data-decimal="' . $val['decimal'] . '"';
				$opts[$key] = $val['title'];
			}	
			$form->select_search('currency_id_get', $opts, is_isset($data, 'currency_id_get'), $atts, $option_data); 
			?>			
					
			<?php do_action('tab_dir_direction', 2, $data, $data_id); ?>
		</div>
	</div>
<?php	
}

add_action('tab_direction_tab1', 'techname_tab_direction_tab1', 20, 2);
function techname_tab_direction_tab1($data, $data_id) {
?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Technical name', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="tech_name" class="tech_name" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($data, 'tech_name')); ?>" />
			</div>
			<?php do_action('tab_dir_techname', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Status', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="direction_status" id="direction_status" autocomplete="off">
					<?php 
						$direction_status = is_isset($data, 'direction_status'); 
						if (!is_numeric($direction_status)) { $direction_status = 1; }
					?>						
					<option value="1" <?php selected($direction_status, 1); ?>><?php _e('active direction', 'pn');?></option>
					<option value="0" <?php selected($direction_status, 0); ?>><?php _e('inactive direction', 'pn');?></option>
					<option value="2" <?php selected($direction_status, 2); ?>><?php _e('hold direction', 'pn');?></option>
				</select>
			</div>		
			<?php do_action('tab_dir_techname', 2, $data, $data_id); ?>
		</div>
	</div>
<?php	
}

add_action('tab_direction_tab1', 'permalink_tab_direction_tab1', 30, 2);
function permalink_tab_direction_tab1($data, $data_id) {
	global $premiumbox;	

	$form = new PremiumForm();
	$gp = $premiumbox->general_tech_pages();
	$permalink = rtrim(get_site_url_ml(), '/') . '/' . is_isset($gp, 'exchange');
?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Permalink', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php echo $permalink; ?><br />
				<input type="text" name="direction_name" style="width: 100%;" value="<?php echo is_direction_name(is_isset($data, 'direction_name')); ?>" />
			</div>
			<?php $form->help(__('More info', 'pn'), sprintf(__('Permanent link for exchange direction: %sPERMANENTLINK', 'pn'), $permalink)); ?>
			<?php do_action('tab_dir_permalink', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<?php do_action('tab_dir_permalink', 2, $data, $data_id); ?>
		</div>
	</div>
<?php	
}

add_action('tab_direction_tab2', 'rate_tab_direction_tab2', 10, 2);
function rate_tab_direction_tab2($data, $data_id) {	

	$dir_c = is_course_direction($data, '', '', 'admin');
?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Exchange rate', 'pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<input type="text" name="course_give" id="course_give" style="width: 100%;" value="<?php echo is_isset($dir_c, 'give'); ?>" />
			</div>			
			<?php do_action('tab_dir_rate', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<input type="text" name="course_get" id="course_get" style="width: 100%;" value="<?php echo is_isset($dir_c, 'get'); ?>" />	
			</div>		
			<?php do_action('tab_dir_rate', 2, $data, $data_id); ?>
		</div>
	</div>
<?php
}

add_action('tab_direction_tab2', 'profit_tab_direction_tab2', 20, 2);
function profit_tab_direction_tab2($data, $data_id) {
	
	$form = new PremiumForm();
?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Profit', 'pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('With give amount', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<div><input type="text" name="profit_sum1" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'profit_sum1')); ?>" /> S</div>
				<div><input type="text" name="profit_pers1" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'profit_pers1')); ?>" /> %</div>
			</div>						
			<?php do_action('tab_dir_profit', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('With receive amount', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<div><input type="text" name="profit_sum2" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'profit_sum2')); ?>" /> S</div>
				<div><input type="text" name="profit_pers2" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'profit_pers2')); ?>" /> %</div>	
			</div>		
			<?php do_action('tab_dir_profit', 2, $data, $data_id); ?>
		</div>
	</div>
	<div class="add_tabs_line">
		<?php $form->help(__('More info', 'pn'), __('Enter profit amount for this direction. Profit may be set in numbers (S) or in percent (%). This value is used for the affiliate program.', 'pn')); ?>
	</div>
<?php
}

add_action('tab_direction_tab3', 'fees_tab_direction_tab3', 10, 2);
function fees_tab_direction_tab3($data, $data_id) {
?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Payment systems fees', 'pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('User &rarr; Exchange', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<div><input type="text" name="com_sum1" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_sum1')); ?>" /> S</div>
				<div><input type="text" name="com_pers1" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_pers1')); ?>" /> %</div>
			</div>							
			<?php do_action('tab_dir_fees', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange &rarr; User', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<div><input type="text" name="com_sum2" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_sum2')); ?>" /> S</div>
				<div><input type="text" name="com_pers2" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_pers2')); ?>" /> %</div>	
			</div>		
			<?php do_action('tab_dir_fees', 2, $data, $data_id); ?>
		</div>
	</div>
<?php
}

add_action('tab_direction_tab3', 'payfees_tab_direction_tab3', 20, 2);
function payfees_tab_direction_tab3($data, $data_id) {
	
	$form = new PremiumForm();
?>		
	<div class="add_tabs_line">
		<div class="add_tabs_label"></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('User &rarr; Exchange', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<label><input type="checkbox" name="pay_com1" <?php checked(is_isset($data, 'pay_com1'), 1); ?> autocomplete="off" value="1" /> <?php _e('exchange pays fee', 'pn'); ?></label>
			</div>							
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange &rarr; User', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<label><input type="checkbox" name="pay_com2" <?php checked(is_isset($data, 'pay_com2'), 1); ?> autocomplete="off" value="1" /> <?php _e('exchange pays fee', 'pn'); ?></label>
			</div>		
		</div>
	</div>
	<div class="add_tabs_line">
		<?php $form->help(__('More info', 'pn'), __('Check this box if you are to pay a payment system fee instead of client', 'pn')); ?>
	</div>
						
	<div class="add_tabs_line">
		<div class="add_tabs_label"></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('User &rarr; Exchange', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<label><input type="checkbox" name="nscom1" <?php checked(is_isset($data, 'nscom1'), 1); ?> autocomplete="off" value="1" /> <?php _e('non standard fees', 'pn'); ?></label>
			</div>
			<?php $form->help(__('More info','pn'), __('Check this box if a payment system takes a fee for incoming payment.', 'pn')); ?>	
			<div class="premium_wrap_standart">	
				<?php
				$com_det1 = intval(is_isset($data, 'com_det1'));
				?>
				<select name="com_det1" autocomplete="off">
					<option value="0" <?php selected($com_det1,0); ?>><?php _e('percentage + amount', 'pn'); ?></option>
					<option value="1" <?php selected($com_det1,1); ?>><?php _e('amount + percentage', 'pn'); ?></option>
				</select>
			</div>							
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange &rarr; User', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<label><input type="checkbox" name="nscom2" <?php checked(is_isset($data, 'nscom2'), 1); ?> autocomplete="off" value="1" /> <?php _e('non standard fees', 'pn'); ?></label>
			</div>	
			<?php $form->help(__('More info', 'pn'), __('Check this box if a payment system takes a fee for incoming payment.', 'pn')); ?>
			<div class="premium_wrap_standart">	
				<?php
				$com_det2 = intval(is_isset($data, 'com_det2'));
				?>
				<select name="com_det2" autocomplete="off">
					<option value="0" <?php selected($com_det2, 0); ?>><?php _e('percentage + amount', 'pn'); ?></option>
					<option value="1" <?php selected($com_det2, 1); ?>><?php _e('amount + percentage', 'pn'); ?></option>
				</select>
			</div>	
		</div>
	</div>								
<?php
} 

add_action('tab_direction_tab3', 'maxfees_tab_direction_tab3', 30, 2);
function maxfees_tab_direction_tab3($data, $data_id) {
?>		
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Min. amount of fees', 'pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('User &rarr; Exchange', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="minsum1com" value="<?php echo is_sum(is_isset($data, 'minsum1com')); ?>" />		
			</div>							
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange &rarr; User', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="minsum2com" value="<?php echo is_sum(is_isset($data, 'minsum2com')); ?>" />				
			</div>		
		</div>
	</div>
<?php
}  
	 
add_action('tab_direction_tab4', 'combox_tab_direction_tab4', 10, 2);
function combox_tab_direction_tab4($data, $data_id) {
	
	$com_box_sum1 = is_sum(is_isset($data, 'com_box_sum1'));
	$com_box_pers1 = is_sum(is_isset($data, 'com_box_pers1'));
	$com_box_sum2 = is_sum(is_isset($data, 'com_box_sum2'));
	$com_box_pers2 = is_sum(is_isset($data, 'com_box_pers2'));
	?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Additional sender fee', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="com_box_sum1" style="width: 80%;" id="com_box_sum1" value="<?php echo $com_box_sum1; ?>" /> S			
			</div>
			<div class="premium_wrap_standart">
				<input type="text" name="com_box_pers1" style="width: 80%;" id="com_box_pers1" value="<?php echo $com_box_pers1; ?>" /> %		
			</div>	
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Additional recipient fee', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="com_box_sum2" style="width: 80%;" id="com_box_sum2" value="<?php echo $com_box_sum2; ?>" /> S		
			</div>
			<div class="premium_wrap_standart">
				<input type="text" name="com_box_pers2" style="width: 80%;" id="com_box_pers2" value="<?php echo $com_box_pers2; ?>" /> %		
			</div>
		</div>
	</div>	
<?php
}    

add_action('tab_direction_tab4', 'comboxmin_tab_direction_tab4', 20, 2);
function comboxmin_tab_direction_tab4($data, $data_id) {		
	?>	
	<div class="add_tabs_line">
		<div class="add_tabs_label"></div>
		<div class="add_tabs_single">	
			<div class="add_tabs_sublabel"><span><?php _e('Minimum sender fee', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="com_box_min1" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_box_min1')); ?>" /> S				
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Minimum recipient fee', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="com_box_min2" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_box_min2')); ?>" /> S				
			</div>			
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_label"></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Additional sender fee', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<label><input type="checkbox" name="dcom1" <?php checked(is_isset($data, 'dcom1'), 1); ?> autocomplete="off" value="1" /> <?php _e('subtract fee from payment amount', 'pn'); ?></label>
			</div>
			<div class="premium_wrap_standart">
				<label><input type="checkbox" name="com_box_ns1" <?php checked(is_isset($data, 'com_box_ns1'), 1); ?> autocomplete="off" value="1" /> <?php _e('non standard fees', 'pn'); ?></label>
			</div>	
			<div class="premium_wrap_standart">	
				<?php
				$com_box_det1 = intval(is_isset($data, 'com_box_det1'));
				?>
				<select name="com_box_det1" autocomplete="off">
					<option value="0" <?php selected($com_box_det1, 0); ?>><?php _e('percentage + amount', 'pn'); ?></option>
					<option value="1" <?php selected($com_box_det1, 1); ?>><?php _e('amount + percentage', 'pn'); ?></option>
				</select>
			</div>						
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Additional recipient fee', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<label><input type="checkbox" name="dcom2" <?php checked(is_isset($data, 'dcom2'), 1); ?> autocomplete="off" value="1" /> <?php _e('add fee to payout amount', 'pn'); ?></label>
			</div>
			<div class="premium_wrap_standart">
				<label><input type="checkbox" name="com_box_ns2" <?php checked(is_isset($data, 'com_box_ns2'), 1); ?> autocomplete="off" value="1" /> <?php _e('non standard fees', 'pn'); ?></label>
			</div>
			<div class="premium_wrap_standart">	
				<?php
				$com_box_det2 = intval(is_isset($data, 'com_box_det2'));
				?>
				<select name="com_box_det2" autocomplete="off">
					<option value="0" <?php selected($com_box_det2, 0); ?>><?php _e('percentage + amount', 'pn'); ?></option>
					<option value="1" <?php selected($com_box_det2, 1); ?>><?php _e('amount + percentage', 'pn'); ?></option>
				</select>
			</div>		
		</div>
	</div>	
<?php
}

add_action('tab_direction_tab5', 'minamount_tab_direction_tab5', 10, 2);
function minamount_tab_direction_tab5($data, $data_id) {		
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Minimum amount', 'pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<input type="text" name="min_sum1" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'min_sum1')); ?>" />
			</div>
			<?php do_action('tab_dir_minamount', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<input type="text" name="min_sum2" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'min_sum2')); ?>" />
			</div>		
			<?php do_action('tab_dir_minamount', 2, $data, $data_id); ?>
		</div>
	</div>												
<?php
}  

add_action('tab_direction_tab5', 'maxamount_tab_direction_tab5', 20, 2);
function maxamount_tab_direction_tab5($data, $data_id) {		
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Maximum amount', 'pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<input type="text" name="max_sum1" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'max_sum1')); ?>" />
			</div>
			<?php do_action('tab_dir_maxamount', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<input type="text" name="max_sum2" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'max_sum2')); ?>" />
			</div>		
			<?php do_action('tab_dir_maxamount', 2, $data, $data_id); ?>
		</div>
	</div>
<?php
} 

add_action('tab_direction_tab6', 'directions_temp_tab_direction_tab6', 10, 2);
function directions_temp_tab_direction_tab6($data, $data_id) {
	global $premiumbox;	

	$form = new PremiumForm();
	$list_directions_temp = apply_filters('list_directions_temp', array());
	if (is_array($list_directions_temp)) {
		foreach ($list_directions_temp as $key => $title) { 
			$text = '';
			$text = pn_strip_text(get_direction_meta($data_id, $key));
			if (strlen($text) < 1) {
				$text = pn_strip_text(get_direction_txtmeta($data_id, $key));
			}									
			if (strlen($text) < 1) { 
				$text = $premiumbox->get_option('naps_temp', $key); 
			} 
			?>
			<div class="add_tabs_line">
				<div class="add_tabs_submit">
					<input type="submit" name="" class="button" value="<?php _e('Save', 'pn'); ?>" />
				</div>
			</div>
			<div class="add_tabs_line">
				<div class="add_tabs_label"><span><?php echo $title; ?></span></div>
				<div class="add_tabs_single long">
					<?php $form->editor($key, $text, '12', '', 1, 0, apply_filters('direction_instruction_tags', array(), $key), 1, 1, 1); ?>
				</div>
			</div>			
			<?php  
		}
	}													
}  
 
add_action('tab_direction_tab8', 'directions_cf_tab_direction_tab8', 10, 2);
function directions_cf_tab_direction_tab8($data, $data_id) {
	global $wpdb;	

	$form = new PremiumForm();
			
	$cfs_in = array();
	$cf_directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "cf_directions WHERE direction_id > 0 AND direction_id = '$data_id'");
	foreach ($cf_directions as $cf) {
		$cfs_in[$cf->cf_id] = $cf->cf_id;
	}						
	$custom_fields = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "direction_custom_fields ORDER BY cf_order ASC");
		
	$lists = array();
	if (is_array($custom_fields)) {
		foreach ($custom_fields as $custom_field) {
			$lists[$custom_field->id] = $custom_field;
		}
	}		
	$lists = list_checks_top($lists, $cfs_in);	
	?>	
	<div class="add_tabs_line">
		<div class="premium_wrap_standart ajax_checkbox">
			<?php
			$scroll_lists = array();
			$class = array();
			foreach ($lists as $cf_data) {
				
				$checked = 0;
				if (isset($cfs_in[$cf_data->id]) or 0 == count($cfs_in) and 'user_email' == $cf_data->cf_auto) {
					$checked = 1;
				}

				if ('user_email' == $cf_data->cf_auto) {
					$class[$cf_data->id] = 'bred';
				}
							
				$uniqueid = '';
				if ($cf_data->uniqueid) {
					$uniqueid = ' (' . $cf_data->uniqueid . ')';
				}
				
				$tech_title = pn_strip_input(ctv_ml($cf_data->tech_name));
				if (!$tech_title) { $tech_title = pn_strip_input(ctv_ml($cf_data->cf_name)); }
							
				$scroll_lists[] = array(
					'title' => $tech_title . pn_item_status($cf_data) . pn_item_basket($cf_data),
					'checked' => $checked,
					'value' => $cf_data->id,
					'atts' => 'data-id="' . $cf_data->id . '"',
				);	
			}	
			echo get_check_list($scroll_lists, '', $class, '500', 1);
			?>	
			<input type="hidden" name="cf" class="ajax_checkbox_input" value="" />
			<div class="premium_clear"></div>
		</div>
		<?php $form->warning(__('Check E-mail field. It is necessary in order to notify users via e-mail', 'pn')); ?>
	</div>			
	<?php	
}

add_action('tab_direction_dirtemp', 'def_tab_direction_dirtemp', 10, 2);
function def_tab_direction_dirtemp($data, $data_id) {
	
	$form = new PremiumForm();
	$text = pn_strip_text(is_isset($data, 'mailtemp'));	
	$tags = array();
	if (function_exists('def_mailtemp_tags_bids')) {
		$tags = def_mailtemp_tags_bids(array());
	}
	$tags = pn_array_unset($tags, 'dirtemp');
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_submit">
			<input type="submit" name="" class="button" value="<?php _e('Save', 'pn'); ?>" />
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Template', 'pn'); ?></span></div>
		<div class="add_tabs_single long">
			<?php $form->editor('dirtemp', $text, '12', '', 1, 0, $tags, 1, 1, 0); ?>
		</div>
	</div>	
	<?php
	$maildata = is_isset($data, 'maildata');
	$maildata = pn_json_decode($maildata);
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Admin email', 'pn'); ?></span></div>
		<div class="add_tabs_single long">
			<div class="premium_wrap_standart">
				<input type="text" name="maildata_email" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($maildata, 'email')); ?>" />
			</div>
		</div>
	</div>		
	<?php 
	if (is_extension_active('pn_extended', 'moduls', 'sms')) {
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Admin phone', 'pn'); ?></span></div>
		<div class="add_tabs_single long">
			<div class="premium_wrap_standart">
				<input type="text" name="maildata_phone" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($maildata, 'phone')); ?>" />
			</div>
		</div>
	</div>		
	<?php 
	}
	if (is_extension_active('pn_extended', 'moduls', 'telegram')) {
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Admin telegram', 'pn'); ?></span></div>
		<div class="add_tabs_single long">
			<div class="premium_wrap_standart">
				<input type="text" name="maildata_telegram" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($maildata, 'telegram')); ?>" />
			</div>
		</div>
	</div>		
	<?php 
	}
} 