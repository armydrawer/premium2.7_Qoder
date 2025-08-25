<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_bc_add_corrs', 'def_adminpage_title_pn_bc_add_corrs');
	function def_adminpage_title_pn_bc_add_corrs() {
		
		$id = intval(is_param_get('item_id'));
		if ($id) {
			return __('Edit adjustment', 'pn');
		} else {
			return __('Add adjustment', 'pn');
		}
		
	}

	add_action('pn_adminpage_content_pn_bc_add_corrs', 'def_adminpage_content_pn_bc_add_corrs');
	function def_adminpage_content_pn_bc_add_corrs() {
		global $wpdb;

		$form = new PremiumForm();

		$id = intval(is_param_get('item_id'));
		$data_id = 0;
		$data = '';
		
		if ($id) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchange_directions WHERE id = '$id'");
			if (isset($data->id)) {
				$data_id = $data->id;
			}	
		}

		if ($data_id) {
			$title = __('Edit adjustment', 'pn');
		} else {
			$title = __('Add adjustment', 'pn');
		}
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_bc_corrs'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_bc_add_corrs'),
				'title' => __('Add new', 'pn')
			);	
		}
		$form->back_menu($back_menu, $data);

		$options = array();
		$options['hidden_block'] = array(
			'view' => 'hidden_input',
			'name' => 'data_id',
			'default' => $data_id,
		);	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => $title,
			'submit' => __('Save','pn'),
		);
		
		$direction_id = intval(is_isset($data, 'direction_id'));

		if ($direction_id) {
			$dir_row = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$direction_id'");
			if (isset($dir_row->id)) {
				$dir_c = is_course_direction($dir_row, '', '', 'admin');
				$options['course'] = array(
					'view' => 'textfield',
					'title' => __('Exchange rate', 'pn'), 
					'default' => is_isset($dir_c, 'give') . '&rarr;' . is_isset($dir_c, 'get'),
				);	
			}
		}
		
		$opts = array();
		$opts[0] = '--' . __('No item', 'pn') . '--';
		$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions ORDER BY site_order1 ASC");
		foreach ($directions as $direction) { 
			$opts[$direction->id] = pn_strip_input($direction->tech_name) . pn_item_status($direction, 'direction_status', array('0' => __('inactive direction', 'pn'), '2' => __('hold direction', 'pn'))) . pn_item_basket($direction);
		}
		$options['direction_id'] = array(
			'view' => 'select_search',
			'title' => __('Exchange direction', 'pn'),
			'options' => $opts,
			'default' => $direction_id,
			'name' => 'direction_id',
		);	
		$options['line0'] = array(
			'view' => 'line',
		);	
		$alls = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bestchange_currency_codes ORDER BY currency_code_title ASC");
		$vs[0] = '--' . __('No item', 'pn') . '--';
		foreach ($alls as $all) {
			$vs[$all->currency_code_id] = pn_strip_input($all->currency_code_title);
		}
		$alls = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bestchange_cities ORDER BY city_title ASC");
		$cities[0] = '--' . __('No item', 'pn') . '--';
		foreach ($alls as $all) {
			$cities[$all->city_id] = pn_strip_input($all->city_title);
		}	
		$options['city_id'] = array(
			'view' => 'select_search',
			'title' => __('City', 'pn'),
			'options' => $cities,
			'default' => is_isset($data, 'city_id'),
			'name' => 'city_id',
		);		
		$options['v1'] = array(
			'view' => 'select_search',
			'title' => __('Send', 'pn'),
			'options' => $vs,
			'default' => is_isset($data, 'v1'),
			'name' => 'v1',
		);	
		$options['v2'] = array(
			'view' => 'select_search',
			'title' => __('Receive', 'pn'),
			'options' => $vs,
			'default' => is_isset($data, 'v2'),
			'name' => 'v2',
		);
		$options['show_positions'] = array(
			'view' => 'textfield',
			'default' => '<a href="#" class="button js_displayrating_bestchange" target="_blank">' . __('Show rating', 'pn') . '</a>',
		);		
		$options['line1'] = array(
			'view' => 'line',
		);		
		$options['pars_position'] = array(
			'view' => 'inputbig',
			'title' => __('Position', 'pn'),
			'default' => is_isset($data, 'pars_position'),
			'name' => 'pars_position',
		);
		$options['min_res'] = array(
			'view' => 'inputbig',
			'title' => __('Min reserve for position','pn'),
			'default' => is_isset($data, 'min_res'),
			'name' => 'min_res',
			'atts' => array('id' => 'pn_min_res'),
		);
		$options['step'] = array(
			'view' => 'inputbig',
			'title' => __('Step', 'pn'),
			'default' => is_isset($data, 'step'),
			'name' => 'step',
		);	
		$options['min_sum'] = array(
			'view' => 'inputbig',
			'title' => __('Min rate', 'pn'),
			'default' => is_isset($data, 'min_sum'),
			'name' => 'min_sum',
		);
		$options['max_sum'] = array(
			'view' => 'inputbig',
			'title' => __('Max rate', 'pn'),
			'default' => is_isset($data, 'max_sum'),
			'name' => 'max_sum',
		);
		$options['line3'] = array(
			'view' => 'line',
		);	
		$options['reset_course'] = array(
			'view' => 'select',
			'title' => __('Reset to standard rate', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => is_isset($data, 'reset_course'),
			'name' => 'reset_course',
		);	
		$options['standart_course_give'] = array(
			'view' => 'inputbig',
			'title' => __('Standart rate Send', 'pn'),
			'default' => is_isset($data, 'standart_course_give'),
			'name' => 'standart_course_give',
		);
		$options['standart_course_get'] = array(
			'view' => 'inputbig',
			'title' => __('Standart rate Receive', 'pn'),
			'default' => is_isset($data, 'standart_course_get'),
			'name' => 'standart_course_get',
		);
		$options['line6'] = array(
			'view' => 'line',
		);
		$options['float_course'] = array(
			'view' => 'select',
			'title' => __('Float course', 'pn'),
			'options' => array('0' => __('auto', 'pn'), '1' => __('1 = XXX', 'pn'), '2' => __('XXX = 1', 'pn')),
			'default' => is_isset($data, 'float_course'),
			'name' => 'float_course',
		);		
		$options['convert_course'] = array(
			'view' => 'select',
			'title' => __('Convert course to', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('1 = XXX', 'pn'), '2' => __('XXX = 1', 'pn')),
			'default' => is_isset($data, 'convert_course'),
			'name' => 'convert_course',
		);		
		$options['line4'] = array(
			'view' => 'line',
		);
		$options['black_ids'] = array(
			'view' => 'textarea',
			'title' => __('Black list of exchangers ID (separate coma)', 'pn'),
			'default' => is_isset($data, 'black_ids'),
			'name' => 'black_ids',
			'rows' => 5,
			'atts' => array('autocomplete' => 'off'),
		);
		$options['white_ids'] = array(
			'view' => 'textarea',
			'title' => __('White list of exchangers ID (separate coma)', 'pn'),
			'default' => is_isset($data, 'white_ids'),
			'name' => 'white_ids',
			'rows' => 5,
			'atts' => array('autocomplete' => 'off'),
		);
		$options['line5'] = array(
			'view' => 'line',
		);		
		$options['status'] = array(
			'view' => 'select',
			'title' => __('Enable parser', 'pn'),
			'options' => array('1' => __('Yes', 'pn'), '0' => __('No', 'pn')),
			'default' => is_isset($data, 'status'),
			'name' => 'status',
		);	
		$params_form = array(
			'filter' => 'pn_bccorrs_addform',
			'data' => $data,
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
 		?>
<script type="text/javascript">
jQuery(function($) {
	
	function set_show_link() {
		var url = '<?php echo get_request_link('displayrating_bestchange', 'html'); ?>?v1=' + $('#pn_v1').val() + '&v2=' + $('#pn_v2').val() + '&city_id=' + $('#pn_city_id').val() + '&minres=' + $('#pn_min_res').val();
		$('.js_displayrating_bestchange').attr('href', url);
	}
	
	$(document).on('change', '#pn_city_id, #pn_v1, #pn_v2, #pn_min_res', function() {
		set_show_link();
	});
	
	set_show_link();

});
</script>	
		<?php 
	} 

}

add_action('premium_action_pn_bc_add_corrs', 'def_premium_action_pn_bc_add_corrs'); 
function def_premium_action_pn_bc_add_corrs() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_bestchange'));

	$data_id = intval(is_param_post('data_id')); 
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchange_directions WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}	
		
	$array = array();
	$array['status'] = intval(is_param_post('status'));
	$direction_id = intval(is_param_post('direction_id'));
	$array['direction_id'] = 0;
	$array['currency_id_give'] = 0;
	$array['currency_id_get'] = 0;
	$direction = '';
	if ($direction_id) {
		$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$direction_id'");
		if (isset($direction->id)) {
			$array['direction_id'] = $direction->id;
			$array['currency_id_give'] = $direction->currency_id_give;
			$array['currency_id_get'] = $direction->currency_id_get;			
		} else {
			$direction_id = 0;
		}
	}
	
	if (!$direction_id) {
		$form->error_form(__('Error! You have not specified the exchange direction', 'pn'));
	}
		
	$id_v1 = intval(is_param_post('v1'));
	$id_v2 = intval(is_param_post('v2'));
	$array['v1'] = $id_v1;
	$array['v2'] = $id_v2;
		
	$v1 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchange_currency_codes WHERE currency_code_id = '$id_v1'");
	$v2 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchange_currency_codes WHERE currency_code_id = '$id_v2'");
	if (!isset($v1->id) or !isset($v2->id)) {
		$form->error_form(__('Error! You have not specified the Send or Receive value', 'pn'));
	}
		
	$city_id = intval(is_param_post('city_id'));
	$array['city_id'] = $city_id;
		
	if ($city_id > 0) {
		$city = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchange_cities WHERE city_id = '$city_id'");
		if (!isset($city->id)) {
			$form->error_form(__('Error! You have not specified the city value', 'pn'));
		}	
	}
		
	$array['pars_position'] = pn_strip_input(is_param_post('pars_position'));
	$array['min_res'] = is_sum(is_param_post('min_res'));
	$array['step'] = pn_parser_num(is_param_post('step'));
	$array['min_sum'] = is_sum(is_param_post('min_sum'));
	$array['max_sum'] = is_sum(is_param_post('max_sum'));
	$array['reset_course'] = intval(is_param_post('reset_course'));
	$array['convert_course'] = intval(is_param_post('convert_course'));
	$array['float_course'] = intval(is_param_post('float_course'));
	$array['standart_course_give'] = is_sum(is_param_post('standart_course_give'));
	$array['standart_course_get'] = is_sum(is_param_post('standart_course_get'));
	$array['black_ids'] = pn_strip_input(is_param_post('black_ids'));
	$array['white_ids'] = pn_strip_input(is_param_post('white_ids'));
		
	$array = apply_filters('pn_bccorrs_addform_post', $array, $last_data);		
				
	if ($data_id) {	
		$res = apply_filters('item_bccorrs_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {
			$result = $wpdb->update($wpdb->prefix . 'bestchange_directions', $array, array('id' => $data_id));
				
			$res_errors = _debug_table_from_db($result, 'bestchange_directions', $array);
			_display_db_table_error($form, $res_errors);	
				
			if (isset($last_data->direction_id)) {
				if ($last_data->direction_id != $direction_id) {
					$wpdb->update($wpdb->prefix . "directions", array('bestchange_id' => 0), array('id' => $last_data->direction_id));
				}
			}
			$wpdb->update($wpdb->prefix."directions", array('bestchange_id' => 1), array('id' => $direction_id));
				
			do_action('item_bccorrs_edit', $data_id, $array, $last_data, $result, $direction_id, $direction);
		} else { $form->error_form(is_isset($res, 'error')); }
	} else {
		$res = apply_filters('item_bccorrs_add_before', pn_ind(), $array);
		if ($res['ind']) {
			$result = $wpdb->insert($wpdb->prefix . 'bestchange_directions', $array);
			$data_id = $wpdb->insert_id;
			if ($result) {	
				$wpdb->update($wpdb->prefix . "directions", array('bestchange_id' => 1), array('id' => $direction_id));
				do_action('item_bccorrs_add', $data_id, $array, $direction_id, $direction);	
			} else {
				$res_errors = _debug_table_from_db($result, 'bestchange_directions', $array);
				_display_db_table_error($form, $res_errors);
			}
		} else { $form->error_form(is_isset($res, 'error')); }
	}

	$url = admin_url('admin.php?page=pn_bc_add_corrs&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
	
}	