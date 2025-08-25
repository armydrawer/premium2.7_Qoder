<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_add_currency', 'def_adminpage_title_pn_add_currency');
	function def_adminpage_title_pn_add_currency() {
		global $db_data, $wpdb;	
		
		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$db_data = '';
		
		if ($item_id) {
			$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$item_id'");
			if (isset($db_data->id)) {
				$data_id = $db_data->id;
			}	
		}		
		
		if ($data_id) {
			return __('Edit currency', 'pn');
		} else {
			return __('Add currency', 'pn');
		}	
		
	}

 	add_action('pn_adminpage_content_pn_add_currency', 'def_adminpage_content_pn_add_currency');
	function def_adminpage_content_pn_add_currency() {
		global $db_data, $wpdb;

		$form = new PremiumForm();

		$data_id = intval(is_isset($db_data, 'id'));
		if ($data_id) {
			$title = __('Edit currency', 'pn') . ' - "' . get_currency_title($db_data) . '"';
		} else {
			$title = __('Add currency', 'pn');
		}	

		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_currency'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);				
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_currency'),
				'title' => __('Add new', 'pn')
			);	
		}
		$form->back_menu($back_menu, $db_data);

		$list_tabs = array(
			'tab1' => __('General settings', 'pn'), 
			'tab2' => __('Reserve and limits', 'pn') . ' <span class="one_tabs_submenu">[' . get_sum_color(get_currency_reserve($db_data)) . ']</span>',
			'tab3' => __('Field settings', 'pn'),
			'tab4' => __('Custom fields', 'pn'),
		);
		
		$params_form = array(
			'key' => 'tab_currency',
			'hidden_data' => array('data_id' => $data_id),
			'page_title' => $title,
			'tabs' => apply_filters('list_tabs_currency', $list_tabs, $db_data),
			'button_title' => __('Save', 'pn'),
			'data' => $db_data,
			'data_id' => $data_id,
		);
		$form->init_tab_form($params_form);								
	
	}  

}

add_action('premium_action_pn_add_currency', 'def_premium_action_pn_add_currency');
function def_premium_action_pn_add_currency() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_currency'));
		
	$data_id = intval(is_param_post('data_id'));
		
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}	
		
	$array = array();	
	$array['cat_id'] = $cat_id = intval(is_param_post('cat_id'));
	
	$array['currency_decimal'] = intval(is_param_post('currency_decimal'));
	if ($array['currency_decimal'] < 0) { $array['currency_decimal'] = apply_filters('default_currency_decimal', 4); }						

	$array['currency_status'] = intval(is_param_post('currency_status'));
				
	$array['currency_code_id'] = 0;
	$array['currency_code_title'] = '';
				
	$currency_code_id = intval(is_param_post('currency_code_id'));
	if ($currency_code_id) {
		$currency_code_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_codes WHERE id = '$currency_code_id'");
		if (isset($currency_code_data->id)) {
			$array['currency_code_id'] = $currency_code_data->id;
			$array['currency_code_title'] = is_site_value($currency_code_data->currency_code_title);
		}
	} 
	
	$currency_code_id = intval($array['currency_code_id']);
	if (!$currency_code_id) {
		$currency_code_title = is_site_value(is_param_post('currency_code_title'));
		if (strlen($currency_code_title) < 2) {
			$form->error_form(__('Error! Currency code not entered', 'pn'));
		} else {
			$currency_code_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_codes WHERE currency_code_title = '$currency_code_title'");
			if (isset($currency_code_data->id)) {
				$array['currency_code_id'] = $currency_code_data->id;
				$array['currency_code_title'] = is_site_value($currency_code_data->currency_code_title);
			} else {	
				$arr = array();
				$array['currency_code_title'] = $arr['currency_code_title'] = $currency_code_title;
				$arr['auto_status'] = 1;
				$arr['edit_date'] = current_time('mysql');
				$arr['create_date'] = current_time('mysql');
				if (current_user_can('administrator') or current_user_can('pn_change_ir')) {
					$currency_code_internal_rate = is_sum(is_param_post('currency_code_internal_rate'));
					if ($currency_code_internal_rate <= 0) { $currency_code_internal_rate = 1; }
					$arr['internal_rate'] = $currency_code_internal_rate;
				}
				$result = $wpdb->insert($wpdb->prefix . 'currency_codes', $arr);
				$array['currency_code_id'] = $wpdb->insert_id;
				if (!$array['currency_code_id']) {
					$res_errors = _debug_table_from_db($result, 'currency_codes', $arr);
					_display_db_table_error($form, $res_errors);
				}				
			}
		}		
	}
			
	$logo1 = pn_strip_input(is_param_post_ml('currency_logo'));
	$logo2 = pn_strip_input(is_param_post_ml('currency_logo_second'));
	if (!$logo2) {
		$logo2 = $logo1;
	}
	$currency_logo = array(
		'logo1' => $logo1,
		'logo2' => $logo2,
	);
	$array['currency_logo'] = @serialize($currency_logo);
		
	$array['psys_id'] = 0;
	$array['psys_title'] = '';
	$array['psys_logo'] = '';
				
	$psys_id = intval(is_param_post('psys_id'));
	if ($psys_id) {
		$psys_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "psys WHERE id = '$psys_id'");
		if (isset($psys_data->id)) {
			$array['psys_id'] = $psys_data->id;
			$array['psys_title'] = pn_strip_input($psys_data->psys_title);
			$array['psys_logo'] = $psys_data->psys_logo; 
		}
	} 
	
	$psys_id = intval($array['psys_id']);
	if (!$psys_id) {
		$psys_title = pn_strip_input(is_param_post_ml('psys_title'));
		if (!$psys_title) { 
			$form->error_form(__('Error! Payment system name not entered', 'pn'));
		} else {
			$psys_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "psys WHERE psys_title = '$psys_title'");
			if (isset($psys_data->id)) {
				$array['psys_id'] = $psys_data->id;
				$array['psys_title'] = pn_strip_input($psys_data->psys_title);
				$array['psys_logo'] = $psys_data->psys_logo;
			} else {	
				$arr = array();
				$array['psys_title'] = $arr['psys_title'] = $psys_title;
				$arr['psys_logo'] = @serialize($currency_logo);
				$arr['auto_status'] = 1;
				$arr['edit_date'] = current_time('mysql');
				$arr['create_date'] = current_time('mysql');
				$result = $wpdb->insert($wpdb->prefix . 'psys', $arr);
				$array['psys_id'] = $wpdb->insert_id;
				if (!$array['psys_id']) {
					$res_errors = _debug_table_from_db($result, 'psys', $arr);
					_display_db_table_error($form, $res_errors);
				}
			}
		}
	} 

	$xml_value = is_xml_value(is_param_post('xml_value'));
	if (!$xml_value) {
		$xml_value = pn_strip_symbols(replace_cyr(ctv_ml($array['psys_title'])));
		$xml_value = unique_xml_value($xml_value, $data_id);
	}		
	$array['xml_value'] = strtoupper($xml_value);
		
	$array['reserv_place'] = is_extension_name(is_param_post('reserv_place'));
	$array['currency_reserv'] = is_sum(is_param_post('currency_reserv'));
		
	$array['show_give'] = intval(is_param_post('show_give'));
	$array['show_get'] = intval(is_param_post('show_get'));	
		
	$ui = wp_get_current_user();
	$user_id = intval(is_isset($ui, 'ID'));

	$array['edit_date'] = current_time('mysql');
	$array['edit_user_id'] = $user_id;
	$array['auto_status'] = 1;
	$array = apply_filters('pn_currency_addform_post', $array, $last_data);		
			
	if ($data_id) {
		$res = apply_filters('item_currency_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {
			$result = $wpdb->update($wpdb->prefix . 'currency', $array, array('id' => $data_id));
			do_action('item_currency_edit', $data_id, $array, $last_data, $result);
			$res_errors = _debug_table_from_db($result, 'currency', $array);
			_display_db_table_error($form, $res_errors);
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}
	} else {
		$res = apply_filters('item_currency_add_before', pn_ind(), $array);
		if ($res['ind']) {
			$array['create_date'] = current_time('mysql');
			$result = $wpdb->insert($wpdb->prefix . 'currency', $array);
			$data_id = $wpdb->insert_id;
			if ($result) {
				do_action('item_currency_add', $data_id, $array);
			} else {
				$res_errors = _debug_table_from_db($result, 'currency', $array);
				_display_db_table_error($form, $res_errors);				
			}
		} else { 
			$form->error_form(is_isset($res, 'error')); 
		}
	}

	if ($data_id) {
			
		$cfs_del = array();
		$cf_currency = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "cf_currency WHERE currency_id = '$data_id' AND place_id = '1'");
		foreach ($cf_currency as $cf_item) {
			$cfs_del[$cf_item->cf_id] = $cf_item->cf_id;
		}	
		if (isset($_POST['cfgive'])) {
			$cf = explode(',', $_POST['cfgive']);	
			foreach ($cf as $index => $cf_id) {
				$cf_id = intval($cf_id);
				if (!isset($cfs_del[$cf_id])) {		
					$arr = array();
					$arr['currency_id'] = $data_id;
					$arr['cf_id'] = $cf_id;
					$arr['place_id'] = 1;
					$wpdb->insert($wpdb->prefix . 'cf_currency', $arr);	
				} else {
						unset($cfs_del[$cf_id]);
				}
			}
		}	
		foreach ($cfs_del as $cf_id) {
			$wpdb->query("DELETE FROM " . $wpdb->prefix . "cf_currency WHERE cf_id = '$cf_id' AND currency_id = '$data_id' AND place_id = '1'");			
		}

		$cfs_del = array();
		$cf_currency = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "cf_currency WHERE currency_id = '$data_id' AND place_id = '2'");
		foreach ($cf_currency as $cf_item) {
			$cfs_del[$cf_item->cf_id] = $cf_item->cf_id;
		}	
		if (isset($_POST['cfget'])) {
			$cf = explode(',', $_POST['cfget']);	
			foreach ($cf as $index => $cf_id) {
				$cf_id = intval($cf_id);
				if (!isset($cfs_del[$cf_id])) {		
					$arr = array();
					$arr['currency_id'] = $data_id;
					$arr['cf_id'] = $cf_id;
					$arr['place_id'] = 2;
					$wpdb->insert($wpdb->prefix . 'cf_currency', $arr);	
				} else {
					unset($cfs_del[$cf_id]);
				}
			}
		}	
		foreach ($cfs_del as $cf_id) {
			$wpdb->query("DELETE FROM " . $wpdb->prefix . "cf_currency WHERE cf_id = '$cf_id' AND currency_id = '$data_id' AND place_id = '2'");			
		}		
			
	}	

	$url = admin_url('admin.php?page=pn_add_currency&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
	
}
	
add_action('tab_currency_tab1', 'status_tab_currency_tab1', 10, 2);
function status_tab_currency_tab1($data, $data_id) {
	$form = new PremiumForm();
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Status', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="currency_status" id="currency_status" autocomplete="off">
					<?php 
						$currency_status = is_isset($data, 'currency_status'); 
						if (!is_numeric($currency_status)) { $currency_status = 1; }
					?>						
					<option value="1" <?php selected($currency_status, 1); ?>><?php _e('Active currency', 'pn'); ?></option>
					<option value="0" <?php selected($currency_status, 0); ?>><?php _e('Inactive currency', 'pn'); ?></option>
				</select>
			</div>			
			<?php do_action('tab_curr_status', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Currency category', 'pn'); ?></span></div>
			
			<?php
			$cat_list = get_currency_categories();
			$cats = array('0' => '--' . __('No category', 'pn') . '--');
			foreach ($cat_list as $cat_key => $cat_data) {
				$cats[$cat_key] = is_isset($cat_data, 'title');
			}
			
			$cat_id = intval(is_isset($data, 'cat_id'));	
			$form->select('cat_id', $cats, $cat_id); 
			?>		
					
			<?php do_action('tab_curr_status', 2, $data, $data_id); ?>
		</div>
	</div>
<?php		
}		
 
add_action('tab_currency_tab1', 'psys_tab_currency_tab1', 20, 2);
function psys_tab_currency_tab1($data, $data_id) {
	$form = new PremiumForm();
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('PS title', 'pn'); ?></span></div>
			
			<?php
			$psys_id = intval(is_isset($data, 'psys_id'));
			if (0 == $psys_id) {
				$cl1 = '';
			} else {
				$cl1 = 'pn_hide';		
			}	

			$psys = list_psys(__('Add new', 'pn'));	
			$form->select_search('psys_id', $psys, $psys_id, array('class'=>'js_adhide_input', 'to_class' => 'thevib_psys')); 
			?>	
			
			<div class="premium_wrap_standart thevib_psys <?php echo $cl1; ?>">
				<input type="text" name="psys_title" style="width: 100%;" value="" />
			</div>	
			
			<?php do_action('tab_curr_psys', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Currency code', 'pn'); ?></span></div>
			
			<?php
			$currency_code_id = intval(is_isset($data, 'currency_code_id'));
			if (0 == $currency_code_id) {
				$cl1 = '';
			} else {
				$cl1 = 'pn_hide';		
			}	

			$currency_codes = list_currency_codes(__('Add new', 'pn'));
			$form->select_search('currency_code_id', $currency_codes, $currency_code_id, array('class' => 'js_adhide_input', 'to_class' => 'thevib_currency_code')); 
			?>	
			
			<div class="premium_wrap_standart thevib_currency_code <?php echo $cl1; ?>">
				<input type="text" name="currency_code_title" style="width: 100%;" value="" />
			</div>
			
			<?php
			if (current_user_can('administrator') or current_user_can('pn_change_ir')) {
			?>
			<div class="premium_wrap_standart thevib_currency_code <?php echo $cl1; ?>">
				<div class="add_tabs_sublabel"><span><?php _e('Internal rate per','pn'); ?> 1 <?php echo cur_type(); ?></span></div>
				<input type="text" name="currency_code_internal_rate" style="width: 100%;" value="" />
			</div>			
			<?php 
			}
			?>

			<?php do_action('tab_curr_psys', 2, $data, $data_id); ?>
		</div>
	</div>
<?php		
}

add_action('tab_currency_tab1', 'logo_tab_currency_tab1', 30, 2);
function logo_tab_currency_tab1($data, $data_id) {
	
	$form = new PremiumForm();
	$currency_logo = @unserialize(is_isset($data, 'currency_logo'));
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Main logo', 'pn'); ?></span></div>
			
			<?php	
			$form->uploader('currency_logo', is_isset($currency_logo, 'logo1'), '', 1); 
			?>		
			
			<?php do_action('tab_curr_logo', 1, $data, $data_id); ?>
		</div>
		<div class="add_tabs_single">
			<?php if (get_settings_second_logo()) { ?>
			<div class="add_tabs_sublabel"><span><?php _e('Additional logo', 'pn'); ?></span></div>
			
			<?php	
			$form->uploader('currency_logo_second', is_isset($currency_logo, 'logo2'), '', 1); 
			?>
			
			<?php } ?>
			<?php do_action('tab_curr_logo', 2, $data, $data_id); ?>
		</div>
	</div>
<?php		
}

add_action('tab_currency_tab1', 'xml_tab_currency_tab1', 40, 2);
function xml_tab_currency_tab1($data, $data_id) {
	
	$form = new PremiumForm();
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('XML name', 'pn'); ?></span></div>
			
			<div class="premium_wrap_standart">
				<input type="text" name="xml_value" value="<?php echo is_xml_value(is_isset($data, 'xml_value')); ?>" />
			</div>
			
			<?php $form->help(__('More info','pn'), sprintf(__('Allowed symbols: a-z, A-Z, 0-9, min.: %1$s , max.: %2$s symbols', 'pn'), 3, 30)); ?>	
			<?php $form->warning(sprintf(__('Enter the name (according to the standard): <a href="%s">Jsons.info</a>.','pn'), 'http://jsons.info/references/signatures/currencies')); ?>
			
			<?php do_action('tab_curr_xml', 1, $data, $data_id); ?>
			
		</div>
	</div>
<?php		
}

add_action('tab_currency_tab1', 'decimal_tab_currency_tab1', 50, 2);
function decimal_tab_currency_tab1($data, $data_id) {
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Amount of Decimal places', 'pn'); ?></span></div>
		
			<div class="premium_wrap_standart">
				<input type="text" name="currency_decimal" value="<?php echo intval(is_isset($data, 'currency_decimal')); ?>" />
			</div>
			
			<?php do_action('tab_curr_decimal', 1, $data, $data_id); ?>
			
		</div>
		<div class="add_tabs_single">
			<?php do_action('tab_curr_decimal', 2, $data, $data_id); ?>
		</div>
	</div>
<?php		
}

add_action('tab_currency_tab2', 'reserve_tab_currency_tab2', 10, 2);
function reserve_tab_currency_tab2($data, $data_id) {
	
	$form = new PremiumForm();
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Currency reserve', 'pn'); ?></span></div>
			<?php
			$rplaced = array();
			$rplaced[0] = '--' . __('calculate according to orders', 'pn') . '--';
			$rplaced[1] = '--' . __('From field for reserve', 'pn') . '--';
			$rplaced = apply_filters('reserve_place_list', $rplaced, 'currency');
			$rplaced = (array)$rplaced;
			
			$reserv_place = is_extension_name(is_isset($data, 'reserv_place'));
			$clr = ' pn_hide';
			if ('1' == $reserv_place) {
				$clr = '';
			}	
			$form->select_search('reserv_place', $rplaced, $reserv_place, array('class' => 'js_hide_input', 'to_class' => 'line_currency_reserve')); 
			?>	
		</div>
	</div>
	
	<div class="add_tabs_line line_currency_reserve	line_currency_reserve1<?php echo $clr; ?>">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Field for reserve', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="currency_reserv" style="width: 100px;" value="<?php echo is_sum(is_isset($data, 'currency_reserv')); ?>" />
			</div>
		</div>
	</div>	
<?php		
}		

add_action('tab_currency_tab3', 'show_tab_currency_tab3', 10, 2);
function show_tab_currency_tab3($data, $data_id) {
	
	$form = new PremiumForm();
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Show field "From Account"', 'pn'); ?></span></div>
			<?php $form->select('show_give', array('1' => __('Yes', 'pn'), '0' => __('No', 'pn')), is_isset($data, 'show_give'));  ?>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Show filed "Onto Account"', 'pn'); ?></span></div>
			<?php $form->select('show_get', array('1' => __('Yes', 'pn'), '0' => __('No', 'pn')), is_isset($data, 'show_get'));  ?>
		</div>
	</div>
<?php		
}	
	
add_action('tab_currency_tab4', 'cfields_tab_currency_tab4', 10, 2);
function cfields_tab_currency_tab4($data, $data_id) {
	global $wpdb;
	
	$currency_id = intval(is_isset($data, 'id'));
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Use custom fileds for Send', 'pn'); ?></span></div>
			
			<?php
			$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency_custom_fields ORDER BY cf_order_give ASC");
			$ins = array();
			$its = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "cf_currency WHERE currency_id = '$currency_id' AND place_id = '1'");
			foreach ($its as $it) {
				$ins[$it->cf_id] = $it->cf_id;
			}	
			
			$lists = array();
			if (is_array($items)) {
				foreach ($items as $item) {
					$lists[$item->id] = $item;
				}
			}		
			$lists = list_checks_top($lists, $ins);
			?>
		
			<div class="premium_wrap_standart ajax_checkbox">
				<?php
				$scroll_lists = array();
				if (is_array($lists)) {
					foreach ($lists as $item) {
						$uniqueid = pn_strip_input($item->uniqueid);
						if ($uniqueid) { $uniqueid = ' (' . $uniqueid . ')'; }
						$cf_title = pn_strip_input(ctv_ml($item->tech_name)) . $uniqueid . pn_item_status($item) . pn_item_basket($item);
						$checked = 0;
						if (isset($ins[$item->id])) {
							$checked = 1;
						}
						$scroll_lists[] = array(
							'title' => $cf_title,
							'checked' => $checked,
							'value' => $item->id,
							'atts' => 'data-id="' . $item->id . '"',
						);
					}	
				}	
				echo get_check_list($scroll_lists, '', '', '500', 1);
				?>
				<input type="hidden" name="cfgive" class="ajax_checkbox_input" value="" />
					<div class="premium_clear"></div>
			</div>				
			
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Use custom fileds for Receive', 'pn'); ?></span></div>
			
			<?php
			$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency_custom_fields ORDER BY cf_order_get ASC");	

			$ins = array();
			$its = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "cf_currency WHERE currency_id = '$currency_id' AND place_id = '2'");
			foreach ($its as $it) {
				$ins[$it->cf_id] = $it->cf_id;
			}
			
			$lists = array();
			if (is_array($items)) {
				foreach ($items as $item) {
					$lists[$item->id] = $item;
				}
			}		
			$lists = list_checks_top($lists, $ins);			
			?>
			
			<div class="premium_wrap_standart ajax_checkbox">
				<?php 
				$scroll_lists = array();
				if (is_array($lists)) {
					foreach ($lists as $item) {
						$uniqueid = pn_strip_input($item->uniqueid);
						if ($uniqueid) { $uniqueid = ' (' . $uniqueid . ')'; }
						$cf_title = pn_strip_input(ctv_ml($item->tech_name)) . $uniqueid . pn_item_status($item) . pn_item_basket($item);
						$checked = 0;
						if (isset($ins[$item->id])) {
							$checked = 1;
						}
						$scroll_lists[] = array(
							'title' => $cf_title,
							'checked' => $checked,
							'value' => $item->id,
							'atts' => 'data-id="' . $item->id . '"',
						);
					}	
				}	
				echo get_check_list($scroll_lists, '', '', '500', 1);				
				?>
				<input type="hidden" name="cfget" class="ajax_checkbox_input" value="" />
				<div class="premium_clear"></div>
			</div>			
			
		</div>
	</div>	
<?php

}