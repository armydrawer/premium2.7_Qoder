<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_add_schedule_operators', 'def_adminpage_title_pn_add_schedule_operators');
	function def_adminpage_title_pn_add_schedule_operators() {
		global $db_data, $wpdb;	
		
		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$db_data = '';
		
		if ($item_id) {
			$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "schedule_operators WHERE id = '$item_id'");
			if (isset($db_data->id)) {
				$data_id = $db_data->id;
			}	
		}	
		
		if ($data_id and 1 == is_isset($db_data, 'auto_status')) {
			return __('Edit schedule', 'pn');
		} else {
			return __('Add schedule', 'pn');
		}	
	}

	add_action('pn_adminpage_content_pn_add_schedule_operators', 'def_adminpage_content_pn_add_schedule_operators');
	function def_adminpage_content_pn_add_schedule_operators() {
		global $db_data, $wpdb;	
		
		$form = new PremiumForm();
		
		$data_id = intval(is_isset($db_data, 'id'));
		if ($data_id and 1 == is_isset($db_data, 'auto_status')) {
			$title = __('Edit schedule', 'pn');
		} else {
			$title = __('Add schedule', 'pn');
		}	
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_schedule_operators'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id and 1 == is_isset($db_data, 'auto_status')) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_schedule_operators'),
				'title' => __('Add new', 'pn')
			);	
		}	
		$form->back_menu($back_menu, $db_data);	
		
		$statused = array();
		$status_operator = _operator_status_list();
		if (is_array($status_operator)) {
			foreach ($status_operator as $key => $val) {
				$statused[$key] = $val;
			}
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
	<div class="premium_body">	
		<form method="post" class="admin_ajax_form" action="<?php the_pn_link(); ?>">
			<div class="premium_ajax_loader"></div>
			<input type="hidden" name="form_method" value="post" />
			<?php wp_referer_field(); ?>
			<input type="hidden" name="data_id" value="<?php echo $data_id; ?>" />
			
			<div class="premium_standart_div">
				<?php
				$form->h3($title, __('Save', 'pn'));
				?>
					<div class="premium_standart_line">
						<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Status', 'pn'); ?></div></div>
						<div class="premium_stline_right"><div class="premium_stline_right_ins"><?php $form->select('status', $statused, is_isset($db_data, 'status')); ?></div></div>
							<div class="premium_clear"></div>
					</div>	
					<div class="premium_standart_line">
						<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Work time', 'pn'); ?></div></div>
						<div class="premium_stline_right"><div class="premium_stline_right_ins">
							<div class="premium_wrap_standart">
								<select name="h1" style="width: 50px;" autocomplete="off">	
									<?php
									$r = -1;
									while ($r++ < 23) {
									?>
									<option value="<?php echo $r; ?>" <?php selected(intval(is_isset($db_data, 'h1')), $r);?>><?php echo zeroise($r, 2); ?></option>
									<?php } ?>
								</select>
								:
								<select name="m1" style="width: 50px;" autocomplete="off">	
									<?php
									$r = -1;
									while ($r++ < 59) {
									?>
									<option value="<?php echo $r; ?>" <?php selected(intval(is_isset($db_data, 'm1')), $r);?>><?php echo zeroise($r, 2); ?></option>
									<?php } ?>
								</select>							
								-
								
								<select name="h2" style="width: 50px;" autocomplete="off">	
									<?php
									$r = -1;
									while ($r++ < 23) {
									?>
									<option value="<?php echo $r; ?>" <?php selected(intval(is_isset($db_data, 'h2')), $r);?>><?php echo zeroise($r, 2); ?></option>
									<?php } ?>
								</select>	
								:
								<select name="m2" style="width: 50px;" autocomplete="off">	
									<?php
									$r = -1;
									while ($r++ < 59) {
									?>
									<option value="<?php echo $r; ?>" <?php selected(intval(is_isset($db_data, 'm2')), $r);?>><?php echo zeroise($r, 2); ?></option>
									<?php } ?>
								</select>							
									<div class="premium_clear"></div>
							</div>
						</div></div>
							<div class="premium_clear"></div>
					</div>				
					<div class="premium_standart_line">
						<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Work days', 'pn'); ?></div></div>
						<div class="premium_stline_right"><div class="premium_stline_right_ins">
							<div class="premium_wrap_standart">
								<?php
								$scroll_lists = array();
								foreach ($days as $key => $val) {
									$checked = 0;
									if (1 == is_isset($db_data,$key)) {
										$checked = 1;
									}
									$scroll_lists[] = array(
										'title' => $val,
										'checked' => $checked,
										'value' => 1,
										'name' => $key,
									);	
								}	
								echo get_check_list($scroll_lists, 'days[]');
								?>							
							</div>
						</div></div>
							<div class="premium_clear"></div>
					</div>					
					
				<?php				
				do_action('pn_schedule_operators_addform', $db_data);
					
				$form->h3('', __('Save', 'pn'));	
				?>
			</div>
		</form>		
	</div>		
	<?php 
		$form->init_form_js();
	}

}

add_action('premium_action_pn_add_schedule_operators', 'def_premium_action_pn_add_schedule_operators');
function def_premium_action_pn_add_schedule_operators() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator'));
		
	$data_id = intval(is_param_post('data_id')); 
		
	$ui = wp_get_current_user();
	$user_id = intval(is_isset($ui, 'ID'));
		
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "schedule_operators WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}	
		
	$array = array();
	$array['status'] = intval(is_param_post('status'));
	$array['h1'] = $h1 = zeroise(intval(is_param_post('h1')), 2);
	$array['h2'] = $h2 = zeroise(intval(is_param_post('h2')), 2);
	$array['m1'] = $m1 = zeroise(intval(is_param_post('m1')), 2);
	$array['m2'] = $m2 = zeroise(intval(is_param_post('m2')), 2);
				
	$time1 = strtotime('01-01-2020 ' . $h1 . ':' . $m1 . ':00');
	$time2 = strtotime('01-01-2020 ' . $h2 . ':' . $m2 . ':00');
				
	if ($time1 > $time2) {
		$array['h1'] = $h2; 
		$array['h2'] = $h1;
		$array['m1'] = $m2;
		$array['m2'] = $m1;
	}
				
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
	$array = apply_filters('pn_schedule_operators_addform_post', $array, $last_data);
				
	if ($data_id) {	
		$res = apply_filters('item_schedule_operators_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {
			$result = $wpdb->update($wpdb->prefix . 'schedule_operators', $array, array('id' => $data_id));
			do_action('item_schedule_operators_edit', $data_id, $array, $last_data, $result);
			$res_errors = _debug_table_from_db($result, 'schedule_operators', $array);
			_display_db_table_error($form, $res_errors);
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}
	} else {
		$array['create_date'] = current_time('mysql');
		$res = apply_filters('item_schedule_operators_add_before', pn_ind(), $array);
		if ($res['ind']) {
			$result = $wpdb->insert($wpdb->prefix . 'schedule_operators', $array);
			$data_id = $wpdb->insert_id;
			if ($result) {
				do_action('item_schedule_operators_add', $data_id, $array);
			} else {
				$res_errors = _debug_table_from_db($result, 'schedule_operators', $array);
				_display_db_table_error($form, $res_errors);
			}
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}
	}

	$url = admin_url('admin.php?page=pn_add_schedule_operators&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);

}	