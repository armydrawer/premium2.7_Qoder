<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_add_indxs', 'def_adminpage_title_pn_add_indxs');
	function def_adminpage_title_pn_add_indxs($title) {
		
		$id = intval(is_param_get('item_id'));
		if ($id) {
			return __('Edit coefficient', 'pn');
		} else {
			return __('Add coefficient', 'pn');
		}
		
	} 

	add_action('pn_adminpage_content_pn_add_indxs', 'def_adminpage_content_pn_add_indxs');
	function def_adminpage_content_pn_add_indxs() {
		global $wpdb;

		$form = new PremiumForm();

		$id = intval(is_param_get('item_id'));
		$data_id = 0;
		$data = '';
		
		if ($id) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "indxs WHERE id = '$id'");
			if (isset($data->id)) {
				$data_id = $data->id;
			}	
		}

		if ($data_id) {
			$title = __('Edit coefficient', 'pn');
		} else {
			$title = __('Add coefficient', 'pn');
		}	
		?>
		<div style="margin: 0 0 10px 0;">
			<?php 
			$text = sprintf(__('For creating an exchange rate you can use the following mathematical operations:<br><br> 
			* multiplication<br> 
			/ division<br> 
			- subtraction<br> 
			+ addition<br><br> 
			An example of a formula where two exchange rates are multiplied: [bitfinex_btcusd_last_price] * [cbr_usdrub]<br> 
			For more detailed instructions, follow the <a href="%s" target="_blank" rel="noreferrer noopener">link</a>.', 'pn'), 'https://premium.gitbook.io/main/parseryi-kursov-valyut/');
			
			$form->help(__('Example of formulas for parser', 'pn'), $text);
			?>
		</div>	
		<?php
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_indxs'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_indxs'),
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
			'submit' => __('Save', 'pn'),
		);	
		
		$options['indx_name'] = array(
			'view' => 'inputbig',
			'title' => __('Index name', 'pn'),
			'default' => is_isset($data, 'indx_name'),
			'name' => 'indx_name',
		);
		
		$options['indx_value'] = array(
			'view' => 'textarea',
			'title' => __('Index value formula', 'pn'),
			'default' => is_isset($data, 'indx_value'),
			'name' => 'indx_value',
			'rows' => 10,
			'atts' => array('style' => 'width:100%'),
		);	
		
		$options['indx_value_show'] = array(
			'view' => 'textfield',
			'title' => __('Index value', 'pn'),
			'default' => get_formula(is_isset($data, 'indx_value'), 0), 
		);	
		
		$options['indx_type'] = array(
			'view' => 'select',
			'title' => __('Index type', 'pn'),
			'options' => array('0' => __('index value', 'pn'), '1' => __('adding formula to rate', 'pn')),
			'default' => is_isset($data, 'indx_type'),
			'name' => 'indx_type',
		);		
		
		$options['indx_comment'] = array(
			'view' => 'textarea',
			'title' => __('Comment', 'pn'),
			'default' => is_isset($data, 'indx_comment'),
			'name' => 'indx_comment',
			'rows' => '10',
		);		
		
		$cats = get_inxs_cats();
		if (count($cats) > 1) {
			$options['cat_id'] = array(
				'view' => 'select',
				'title' => __('Index category', 'pn'),
				'options' => get_inxs_cats(),
				'default' => is_isset($data, 'cat_id'),
				'name' => 'cat_id',
			);
		}
		
		$params_form = array(
			'filter' => 'pn_indxs_addform',
			'data' => $data,
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
			
	} 

}

add_action('premium_action_pn_add_indxs', 'def_premium_action_pn_add_indxs');
function def_premium_action_pn_add_indxs() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_indxs'));
		
	$data_id = intval(is_param_post('data_id'));
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "indxs WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
    }

    $indx_value = pn_strip_input(is_param_post('indx_value'));
    $indx_value = str_replace(',', '.', $indx_value);

    $array = array();
	$array['indx_comment'] = pn_strip_input(is_param_post('indx_comment'));
	$array['cat_id'] = intval(is_param_post('cat_id'));
	$array['indx_name'] = unique_indx(is_param_post('indx_name'), $data_id);
	$array['indx_value'] = $indx_value;
	$array['indx_type'] = intval(is_param_post('indx_type'));
				
	$array = apply_filters('pn_indxs_addform_post',$array, $last_data);
	if ($data_id) {	
		$res = apply_filters('item_indxs_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {
			$result = $wpdb->update($wpdb->prefix . 'indxs', $array, array('id' => $data_id));
			do_action('item_indxs_edit', $data_id, $array, $last_data, $result);	
			$res_errors = _debug_table_from_db($result, 'indxs', $array);
			_display_db_table_error($form, $res_errors);
		} else { $form->error_form(is_isset($res, 'error')); }
	} else {	
		$res = apply_filters('item_indxs_add_before', pn_ind(), $array);
		if ($res['ind']) {
			$result = $wpdb->insert($wpdb->prefix . 'indxs', $array);
			$data_id = $wpdb->insert_id;
			if ($result) {
				do_action('item_indxs_add', $data_id, $array);
			} else {
				$res_errors = _debug_table_from_db($result, 'indxs', $array);
				_display_db_table_error($form, $res_errors);
			}				
		} else { $form->error_form(is_isset($res, 'error')); }
	}

	$url = admin_url('admin.php?page=pn_add_indxs&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
	
}	