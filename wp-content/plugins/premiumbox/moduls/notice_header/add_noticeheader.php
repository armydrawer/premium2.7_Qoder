<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('def_adminpage_title_all_add_noticeheader')) {
	add_filter('pn_adminpage_title_all_add_noticeheader', 'def_adminpage_title_all_add_noticeheader');
	function def_adminpage_title_all_add_noticeheader() {
		global $db_data, $wpdb;
			
		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$db_data = '';
			
		if ($item_id) {
			$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "notice_head WHERE id = '$item_id'");
			if (isset($db_data->id)) {
				$data_id = $db_data->id;
			}	
		}	
			
		if ($data_id) {
			return __('Edit warning message', 'pn');
		} else {
			return __('Add warning message', 'pn');
		}
	}
}

if (!function_exists('def_pn_adminpage_content_all_add_noticeheader')) {
	add_action('pn_adminpage_content_all_add_noticeheader', 'def_pn_adminpage_content_all_add_noticeheader');
	function def_pn_adminpage_content_all_add_noticeheader() {
		global $db_data, $wpdb;

		$form = new PremiumForm();

		$data_id = intval(is_isset($db_data, 'id'));
		if ($data_id) {
			$title = __('Edit warning message', 'pn');
		} else {
			$title = __('Add warning message', 'pn');
		}	
			
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=all_noticeheader'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);			
		if($data_id){
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=all_add_noticeheader'),
				'title' => __('Add new', 'pn')
			);	
		}
		$form->back_menu($back_menu, $db_data);	
			
		$options = array();
		$options['hidden_block'] = array(
			'view' => 'hidden_input',
			'name' => 'data_id',
			'default' => $data_id,
		);	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => $title,
			'submit' => __('Save', 'pn'),
		);
		$options['notice_display'] = array(
			'view' => 'select',
			'title' => __('Location', 'pn'),
			'options' => array('0' => __('header', 'pn'), '1' => __('pop-up window', 'pn'), '2' => __('notification window', 'pn')),
			'default' => is_isset($db_data, 'notice_display'),
			'name' => 'notice_display',
			'atts' => array(
				'class' => 'js_hide_input',
				'to_class' => 'bnvib',
			),
		);	
		$notice_display = intval(is_isset($db_data, 'notice_display'));
		if (0 == $notice_display) {
			$class_a1 = 'pn_hide';
			$class_a2 = '';
		} else {
			$class_a1 = '';	
			$class_a2 = 'pn_hide';
		}			
		$options['notice_type'] = array(
			'view' => 'select',
			'title' => __('Notification type', 'pn'),
			'options' => array('0' => __('on period of time', 'pn'), '1' => __('on schedule', 'pn')),
			'default' => is_isset($db_data, 'notice_type'),
			'name' => 'notice_type',
			'atts' => array(
				'class' => 'js_hide_input',
				'to_class' => 'thevib',
			),
		);
		$notice_type = intval(is_isset($db_data, 'notice_type'));
		if (0 == $notice_type) {
			$class_1 = '';
		} else {
			$class_1 = 'pn_hide';		
		}	
		$options['datestart'] = array(
			'view' => 'datetime',
			'title' => __('Start date', 'pn'),
			'default' => is_isset($db_data, 'datestart'),
			'name' => 'datestart',
			'class' => 'thevib thevib0 ' . $class_1,
		);
		$options['dateend'] = array(
			'view' => 'datetime',
			'title' => __('End date', 'pn'),
			'default' => is_isset($db_data, 'dateend'),
			'name' => 'dateend',
			'class' => 'thevib thevib0 ' . $class_1,
		);	
		$options['datetime'] = array(
			'view' => 'user_func',
			'func_data' => $db_data,
			'func' => 'all_noticehead_datetime',
		);	
		$options['line1'] = array(
			'view' => 'line',
		);	
		$options['theclass'] = array(
			'view' => 'inputbig',
			'title' => __('CSS class', 'pn'),
			'default' => is_isset($db_data, 'theclass'),
			'name' => 'theclass',
			'class' => 'bnvib bnvib0 ' . $class_a2,
		);	
		$options['url'] = array(
			'view' => 'inputbig',
			'title' => __('Link', 'pn'),
			'default' => is_isset($db_data, 'url'),
			'name' => 'url',
			'ml' => 1,
		);
		$options['button_text'] = array(
			'view' => 'inputbig',
			'title' => __('Button text', 'pn'),
			'default' => is_isset($db_data, 'button_text'),
			'name' => 'button_text',
			'class' => 'bnvib bnvib1 bnvib2 ' . $class_a1,
			'ml' => 1,
		);
		$options['save_days'] = array(
			'view' => 'input',
			'title' => __('Expires days', 'pn'),
			'default' => is_isset($db_data, 'save_days'),
			'name' => 'save_days',
		);			
		$options['text'] = array(
			'view' => 'textarea',
			'title' => __('Text', 'pn'),
			'default' => is_isset($db_data, 'text'),
			'name' => 'text',
			'rows' => '10',
			'word_count' => 1,
			'ml' => 1,
		);		
		$options['status'] = array(
			'view' => 'select',
			'title' => __('Status', 'pn'),
			'options' => array('1' => __('published', 'pn'), '0' => __('moderating', 'pn')),
			'default' => is_isset($db_data, 'status'),
			'name' => 'status',
		);

		$params_form = array(
			'filter' => 'all_noticehead_addform',
			'data' => $db_data,
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);

	}
} 

if (!function_exists('all_noticehead_datetime')) {
	function all_noticehead_datetime($data) {
			
		$notice_type = intval(is_isset($data, 'notice_type'));
		if (0 == $notice_type) {
			$class_2 = 'pn_hide';
		} else {
			$class_2 = '';			
		}	
			
		$days = array(
			'd1' => __('monday', 'pn'),
			'd2' => __('tuesday', 'pn'),
			'd3' => __('wednesday', 'pn'),
			'd4' => __('thursday', 'pn'),
			'd5' => __('friday', 'pn'),
			'd6' => '<span class="bred">' . __('saturday', 'pn') . '</span>',
			'd7' => '<span class="bred">' . __('sunday', 'pn') . '</span>',
		);	
	?>
	<div class="premium_standart_line thevib thevib1 <?php echo $class_2; ?>">
		<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Period for display (hours)', 'pn'); ?></div></div>
		<div class="premium_stline_right"><div class="premium_stline_right_ins">
			<div class="premium_wrap_standart">
			
				<input type="text" name="h1m1" class="input js_timepicker" value="<?php echo zeroise(is_isset($data, 'h1'), 2); ?>:<?php echo zeroise(is_isset($data, 'm1'), 2); ?>" /> - 
				<input type="text" name="h2m2" class="input js_timepicker" value="<?php echo zeroise(is_isset($data, 'h2'), 2); ?>:<?php echo zeroise(is_isset($data, 'm2'), 2); ?>" />			
										
				<div class="premium_clear"></div>
			</div>
			</div></div>
			<div class="premium_clear"></div>
		</div>				
		<div class="premium_standart_line thevib thevib1 <?php echo $class_2; ?>">
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Period for display (days)', 'pn'); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">
					<?php
						$scroll_lists = array();
						foreach ($days as $key => $val) {
							$checked = 0;
							if (1 == is_isset($data,$key)) {
								$checked = 1;
							}
							$scroll_lists[] = array(
								'title' => $val,
								'checked' => $checked,
								'value' => 1,
								'name' => $key,
							);	
						}	
						echo get_check_list($scroll_lists, 'hidecurr[]');
					?>			
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>				
	<?php
	}
} 

if (!function_exists('def_premium_action_all_add_noticeheader')) {
	add_action('premium_action_all_add_noticeheader', 'def_premium_action_all_add_noticeheader');
	function def_premium_action_all_add_noticeheader() {
		global $wpdb;

		_method('post');
			
		$form = new PremiumForm();
		$form->send_header();
			
		pn_only_caps(array('administrator', 'pn_noticeheader'));
			
		$data_id = intval(is_param_post('data_id'));

		$ui = wp_get_current_user();
		$user_id = intval(is_isset($ui, 'ID'));

		$last_data = '';
		if ($data_id > 0) {
			$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "notice_head WHERE id = '$data_id'");
			if (!isset($last_data->id)) {
				$data_id = 0;
			}
		}	
			
		$array = array();
		$array['notice_type'] = intval(is_param_post('notice_type'));
		$array['notice_display'] = intval(is_param_post('notice_display'));
		$array['url'] = pn_strip_input(is_param_post_ml('url'));
		$array['button_text'] = pn_strip_input(is_param_post_ml('button_text'));
		$array['text'] = pn_strip_text(is_param_post_ml('text'));
		$array['theclass'] = pn_strip_input(is_param_post('theclass'));
		$array['status'] = intval(is_param_post('status'));
		$array['save_days'] = intval(is_param_post('save_days'));
		$array['datestart'] = get_pn_time(is_param_post('datestart'), 'Y-m-d H:i:s');
		$array['dateend'] = get_pn_time(is_param_post('dateend'), 'Y-m-d H:i:s');
		$h1m1 = explode(':', is_param_post('h1m1'));
		$h2m2 = explode(':', is_param_post('h2m2'));
		$array['h1'] = zeroise(intval(is_isset($h1m1, 0)), 2);
		$array['h2'] = zeroise(intval(is_isset($h2m2, 0)), 2);
		$array['m1'] = zeroise(intval(is_isset($h1m1, 1)), 2);
		$array['m2'] = zeroise(intval(is_isset($h2m2, 1)), 2);	
		$array['d1'] = intval(is_param_post('d1'));
		$array['d2'] = intval(is_param_post('d2'));
		$array['d3'] = intval(is_param_post('d3'));
		$array['d4'] = intval(is_param_post('d4'));
		$array['d5'] = intval(is_param_post('d5'));
		$array['d6'] = intval(is_param_post('d6'));
		$array['d7'] = intval(is_param_post('d7'));	
			
		$array['edit_date'] = current_time('mysql');
		$array['edit_user_id'] = $user_id;
		$array['auto_status'] = 1;
		$array = apply_filters('all_noticeheader_addform_post', $array, $last_data);	
			
		if ($data_id) {
			$res = apply_filters('item_noticeheader_edit_before', pn_ind(), $data_id, $array, $last_data);
			if ($res['ind']) {
				$result = $wpdb->update($wpdb->prefix . 'notice_head', $array, array('id' => $data_id));
				do_action('item_noticeheader_edit', $data_id, $array, $last_data, $result);
				$res_errors = _debug_table_from_db($result, 'notice_head', $array);
				_display_db_table_error($form, $res_errors);				
			} else { 
				$form->error_form(is_isset($res, 'error')); 
			}
		} else {
			$res = apply_filters('item_schedule_operators_add_before', pn_ind(), $array);
			if ($res['ind']) {
				$array['create_date'] = current_time('mysql');
				$result = $wpdb->insert($wpdb->prefix . 'notice_head', $array);
				$data_id = $wpdb->insert_id;
				if ($result) {
					do_action('item_noticeheader_add', $data_id, $array);
				} else {
					$res_errors = _debug_table_from_db($result, 'notice_head', $array);
					_display_db_table_error($form, $res_errors);					
				}
			} else { 
				$form->error_form(is_isset($res, 'error')); 
			}
		}	
			
		$url = admin_url('admin.php?page=all_add_noticeheader&item_id=' . $data_id . '&reply=true');
		$form->answer_form($url);	
		
	}
}	