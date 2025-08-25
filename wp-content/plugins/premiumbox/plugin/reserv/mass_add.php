<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_mass_reserve', 'def_adminpage_title_pn_mass_reserve');
	function def_adminpage_title_pn_mass_reserve() {
		
		return __('Reserve adjustment (group)', 'pn');
	}

	add_action('pn_adminpage_content_pn_mass_reserve', 'def_adminpage_content_pn_mass_reserve');
	function def_adminpage_content_pn_mass_reserve() {
		global $wpdb;

		$form = new PremiumForm();

		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_currency_reserve'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['add'] = array(
			'link' => admin_url('admin.php?page=pn_add_currency_reserve'),
			'title' => __('Add new', 'pn')
		);	
		$form->back_menu($back_menu, '');

		$options = array();	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save', 'pn'),
		);	
		$options['trans_title'] = array(
			'view' => 'inputbig',
			'title' => __('Comment', 'pn'),
			'default' => '',
			'name' => 'trans_title',
		);
		$options['trans_sum'] = array(
			'view' => 'inputbig',
			'title' => __('Amount', 'pn'),
			'default' => '',
			'name' => 'trans_sum',
		);
		$options['currency_ids'] = array(
			'view' => 'user_func',
			'name' => 'currency_ids',
			'func_data' => '',
			'func' => 'pn_mass_reserve_option',
			'work' => 'input_array',
		);		
		$params_form = array(
			'filter' => 'pn_mass_reserve_addform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);		
	} 

	function pn_mass_reserve_option($data) {
		
		$currencies = list_currency(__('No item', 'pn'));
		if (isset($currencies[0])) {
			unset($currencies[0]);
		}
	?>
		<div class="premium_standart_line"> 
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Currency name', 'pn'); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">

					<?php
					$scroll_lists = array();						
					foreach ($currencies as $k => $v) {
						$scroll_lists[] = array(
							'title' => $v,
							'checked' => 0,
							'value' => $k,
						);
					}
					echo get_check_list($scroll_lists, 'currency_ids[]', '', '', 1);
					?>

						<div class="premium_clear"></div>
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>	
		<?php			
	}

}

add_action('premium_action_pn_mass_reserve' ,'def_premium_action_pn_mass_reserve');
function def_premium_action_pn_mass_reserve() {
	global $wpdb;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_currency_reserve'));

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
		
	$trans_title = pn_strip_input(is_param_post('trans_title'));
	$trans_sum = is_sum(is_param_post('trans_sum'));
	if ($trans_sum != 0) {
			
		$currency_ids = is_param_post('currency_ids');
		if (is_array($currency_ids) and count($currency_ids) > 0) {
			foreach ($currency_ids as $currency_id) {
				$currency_id = intval($currency_id);
				if ($currency_id) {
					$array = array();
					$array['trans_title'] = $trans_title;
					$array['trans_sum'] = $trans_sum;
					$array['currency_id'] = 0;
					$array['currency_code_id'] = 0;
					$array['currency_code_title'] = '';
					$currency_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id'");
					if (isset($currency_data->id)) {
						$array['currency_id'] = $currency_data->id;
						$array['currency_code_id'] = $currency_data->currency_code_id;
						$array['currency_code_title'] = is_site_value($currency_data->currency_code_title);

						$array['create_date'] = current_time('mysql');
						$array['edit_date'] = current_time('mysql');
						$array['edit_user_id'] = $user_id;
						$array['auto_status'] = 1;
						$wpdb->insert($wpdb->prefix . 'currency_reserv', $array);
						$data_id = $wpdb->insert_id;
						
						update_currency_reserve($array['currency_id']);
						
						do_action('item_currency_reserve_add', $data_id, $array);
					}	
				}
			}
		}
			
	}

	$url = admin_url('admin.php?page=pn_currency_reserve&reply=true');
	$form->answer_form($url);
	
}	