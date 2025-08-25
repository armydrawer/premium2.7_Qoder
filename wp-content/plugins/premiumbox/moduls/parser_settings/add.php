<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_add_parser_pairs', 'def_adminpage_title_pn_add_parser_pairs');
	function def_adminpage_title_pn_add_parser_pairs($title) {
		
		$id = intval(is_param_get('item_id'));
		if ($id) {
			return __('Edit rate', 'pn');
		} else {
			return __('Add rate', 'pn');
		}
		
	} 

	add_action('pn_adminpage_content_pn_add_parser_pairs', 'def_adminpage_content_pn_add_parser_pairs');
	function def_adminpage_content_pn_add_parser_pairs() {
		global $wpdb;

		$form = new PremiumForm();

		$id = intval(is_param_get('item_id'));
		$data_id = 0;
		$data = '';
		
		if ($id) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "parser_pairs WHERE id = '$id'");
			if (isset($data->id)) {
				$data_id = $data->id;
			}	
		}

		if ($data_id) {
			$title = __('Edit rate', 'pn');
		} else {
			$title = __('Add rate', 'pn');
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
			'link' => admin_url('admin.php?page=pn_parser_pairs'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=pn_add_parser_pairs'),
				'title' => __('Add new', 'pn')
			);
			$back_menu['copy'] = array(
				'link' => pn_link('copy_parser_pairs') . '&item_id='.$data_id,
				'title' => __('Copy', 'pn')
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
		$options['title_birg'] = array(
			'view' => 'inputbig',
			'title' => __('Source name', 'pn'),
			'default' => is_isset($data, 'title_birg'),
			'name' => 'title_birg',
		);
		$options['title_pair_give'] = array(
			'view' => 'inputbig',
			'title' => __('Currency name Send', 'pn'),
			'default' => is_isset($data, 'title_pair_give'),
			'name' => 'title_pair_give',
		);
		$options['title_pair_get'] = array(
			'view' => 'inputbig',
			'title' => __('Currency name Receive', 'pn'),
			'default' => is_isset($data, 'title_pair_get'),
			'name' => 'title_pair_get',
		);		
		$options['pair_give'] = array(
			'view' => 'textarea',
			'title' => __('Rate formula for Send', 'pn'),
			'default' => is_isset($data, 'pair_give'),
			'name' => 'pair_give',
			'rows' => 10,
			'atts' => array('style' => 'width:100%'),
		);		
		$options['pair_get'] = array(
			'view' => 'textarea',
			'title' => __('Rate formula for Receive', 'pn'),
			'default' => is_isset($data, 'pair_get'),
			'name' => 'pair_get',
			'rows' => 10,
			'atts' => array('style' => 'width:100%'),
		);
		$options['pair_result'] = array(
			'view' => 'textfield',
			'title' => __('Exchange rate', 'pn') . ' (' . __('result', 'pn') . ')',
			'default' => get_parser_course(is_isset($data, 'pair_give')) . ' => '. get_parser_course(is_isset($data, 'pair_get')),
		);		
		
		$params_form = array(
			'filter' => 'pn_parser_pairs_addform',
			'data' => $data,
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
			
	} 

}

add_action('premium_action_pn_add_parser_pairs', 'def_premium_action_pn_add_parser_pairs');
function def_premium_action_pn_add_parser_pairs() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_directions', 'pn_parser'));
		
	$data_id = intval(is_param_post('data_id'));
	$last_data = '';
	if ($data_id > 0) {
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "parser_pairs WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}	
		
	$array = array();
	$array['title_birg'] = pn_strip_input(is_param_post('title_birg'));
	$array['title_pair_give'] = pn_strip_input(is_param_post('title_pair_give'));
	$array['title_pair_get'] = pn_strip_input(is_param_post('title_pair_get'));
	$array['pair_give'] = pn_strip_input(is_param_post('pair_give'));
	$array['pair_get'] = pn_strip_input(is_param_post('pair_get'));
				
	$array = apply_filters('pn_parser_pairs_addform_post',$array, $last_data);
	if ($data_id) {	
		$res = apply_filters('item_parser_pairs_edit_before', pn_ind(), $data_id, $array, $last_data);
		if ($res['ind']) {
			$result = $wpdb->update($wpdb->prefix . 'parser_pairs', $array, array('id' => $data_id));
			do_action('item_parser_pairs_edit', $data_id, $array, $last_data, $result);	
			$res_errors = _debug_table_from_db($result, 'parser_pairs', $array);
			_display_db_table_error($form, $res_errors);
		} else { $form->error_form(is_isset($res, 'error')); }
	} else {	
		$res = apply_filters('item_parser_pairs_add_before', pn_ind(), $array);
		if ($res['ind']) {
			$result = $wpdb->insert($wpdb->prefix . 'parser_pairs', $array);
			$data_id = $wpdb->insert_id;
			if ($result) {
				do_action('item_parser_pairs_add', $data_id, $array);
			} else {
				$res_errors = _debug_table_from_db($result, 'parser_pairs', $array);
				_display_db_table_error($form, $res_errors);
			}				
		} else { $form->error_form(is_isset($res, 'error')); }
	}

	$url = admin_url('admin.php?page=pn_add_parser_pairs&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
}	

add_action('premium_action_copy_parser_pairs', 'def_premium_action_copy_parser_pairs');
function def_premium_action_copy_parser_pairs() {
	global $wpdb;	

	$form = new PremiumForm();
	$form->send_header();

	pn_only_caps(array('administrator', 'pn_directions', 'pn_parser'));	
			
	$item_id = intval(is_param_get('item_id'));
	if ($item_id) {
		$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "parser_pairs WHERE id = '$item_id'");
		if (isset($data->id)) {	
			$array = array();
			foreach ($data as $key => $item) {
				if ('id' != $key) {
					if ('title_pair_get' == $key) {
						$item .= '[copy]';
					}
					$array[$key] = $item;
				}
			}
			$wpdb->insert($wpdb->prefix . 'parser_pairs', $array);
		}
	}
				
	$url = admin_url('admin.php?page=pn_parser_pairs') . '&reply=true';
	$form->answer_form($url);			
}