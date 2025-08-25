<?php
if (!defined('ABSPATH')) { exit(); }
 
add_filter('list_currency_fields', 'wchecks_list_currency_fields', 10, 4);
function wchecks_list_currency_fields($fields, $vd, $direction, $side_id) {
	
	if ($direction->check_purse == $side_id or 3 == $direction->check_purse) {
		
		$field = array();
		$field_name = 'check_purse' . $side_id;
		$field_text = pn_strip_input(ctv_ml($vd->check_text));
		if (strlen($field_text) < 1) { 
			$field_text = __('e-wallet has valid status', 'pn');
		}
		$field[$field_name] = array(
			'type' => 'checkbox',
			'name' => $field_name,
			'autocomplete' => 'off',
			'value' => 1,
			'text' => $field_text,
			'class' => 'js_changecalc',
			'cd' => '1',
		);
		$fields = pn_array_insert($fields, 'account' . $side_id, $field, 'after');
		
	}
	
	return $fields;
}

add_filter('get_calc_data_params', 'wchecks_get_calc_data_params', 0, 3);
function wchecks_get_calc_data_params($calc_data, $place = '', $bid = '') { 

	if ('calculator' == $place) {
		if (isset($calc_data['cd'])) {
			$calc_data['check1'] = intval(is_isset($calc_data['cd'], 'check_purse1'));
			$calc_data['check2'] = intval(is_isset($calc_data['cd'], 'check_purse2'));			
		}
	}
	if ('recalc' == $place) {
		$calc_data['check1'] = intval(is_isset($bid, 'check_purse1'));
		$calc_data['check2'] = intval(is_isset($bid, 'check_purse2'));
	}	
	if ('action' == $place) {
		$check_purse1 = 0;
		$check_purse2 = 0;
		
		$direction = $calc_data['direction'];
		$vd1 = $calc_data['vd1'];
		$vd2 = $calc_data['vd2'];
		$check_enable = intval($direction->check_purse);
		
		$account1 = trim(is_isset($calc_data, 'account1'));
		$account2 = trim(is_isset($calc_data, 'account2'));
		
		if ($account1) {
			if (1 == $check_enable or 3 == $check_enable) {
				$check_purse1 = apply_filters('set_check_account_give', 0, $account1, $vd1->check_purse);
			}
		}
		if ($account2) {
			if (2 == $check_enable or 3 == $check_enable) {
				$check_purse2 = apply_filters('set_check_account_get', 0, $account2, $vd2->check_purse);
			}
		}		
			
		$calc_data['check1'] = $check_purse1;
		$calc_data['check2'] = $check_purse2;
	}
	
	return $calc_data;
}

add_filter('get_calc_data', 'wchecks_get_calc_data', 0, 2); 
function wchecks_get_calc_data($cdata, $calc_data) {
	
	$direction = $calc_data['direction'];

	$cdata['check1'] = $check1 = intval(is_isset($calc_data, 'check1'));
	$cdata['check2'] = $check2 = intval(is_isset($calc_data, 'check2'));
	
	if (1 == $check1) {
		$cdata['com_sum1'] = is_sum($direction->com_sum1_check);
		$cdata['com_pers1'] = is_sum($direction->com_pers1_check);							
	}
	if (1 == $check2) {
		$cdata['com_sum2'] = is_sum($direction->com_sum2_check);
		$cdata['com_pers2'] = is_sum($direction->com_pers2_check);							
	}		
	
	return $cdata;
}
 
add_filter('error_bids', 'wchecks_error_bids', 400, 5);  
function wchecks_error_bids($error_bids, $direction, $vd1, $vd2, $cdata) {
	
	$check_purse1 = $cdata['check1'];
	$check_purse2 = $cdata['check2'];

	$req_check_purse = intval($direction->req_check_purse);
	if (1 == $req_check_purse or 3 == $req_check_purse) {
		if (1 != $check_purse1 and !isset($error_bids['error_fields']['account1'])) {
			$error_bids['error_fields']['account1'] = apply_filters('check_purse_text_give', __('Your account is not verified', 'pn'), $vd1->check_purse);		
		}
	}
	if (2 == $req_check_purse or 3 == $req_check_purse) {
		if (1 != $check_purse2 and !isset($error_bids['error_fields']['account2'])) {
			$error_bids['error_fields']['account2'] = apply_filters('check_purse_text_get', __('Your account is not verified', 'pn'), $vd2->check_purse);			
		}
	}		

	$error_bids['bid']['check_purse1'] = $check_purse1;
	$error_bids['bid']['check_purse2'] = $check_purse2;

	return $error_bids;
}

if (!function_exists('list_tabs_currency_verify')) {
	add_filter('list_tabs_currency', 'list_tabs_currency_verify');
	function list_tabs_currency_verify($list_tabs) {
		
		$list_tabs['verify'] = __('Verification', 'pn');
		
		return $list_tabs;
	}
}

add_action('tab_currency_verify', 'wchecks_tab_currency_verify', 30, 2);
function wchecks_tab_currency_verify($data, $data_id) {

	$form = new PremiumForm();
	
	$wchecks = array();
	$wchecks[0] = '--' . __('No item', 'pn') . '--';
	$list_wchecks = list_extandeds('wchecks');
	foreach ($list_wchecks as $key => $title) {
		$wchecks[$key] = $title;
	}
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Checking account for verification in PS', 'pn'); ?></span></div>
			<?php	
			$form->select_search('check_purse', $wchecks, is_isset($data, 'check_purse')); 
			?>	
		</div>
		<div class="add_tabs_single">
		</div>
	</div>
	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Text indicating the verified wallet', 'pn'); ?></span></div>
			<?php 
			$atts = array('class' => 'big_input');
			$form->input('check_text', is_isset($data, 'check_text'), $atts); 
			?>			
		</div>
	</div>
<?php		
}

add_filter('pn_currency_addform_post', 'wchecks_currency_addform_post');
function wchecks_currency_addform_post($array) {
	
	$array['check_text'] = pn_strip_input(is_param_post_ml('check_text'));
	$array['check_purse'] = is_extension_name(is_param_post('check_purse'));
	
	return $array;
}

if (!function_exists('list_tabs_direction_verify')) {
	add_filter('list_tabs_direction', 'list_tabs_direction_verify');
	function list_tabs_direction_verify($list_tabs) {
		
		$list_tabs['verify'] = __('Verification', 'pn');
		
		return $list_tabs;
	}
}

add_action('tab_direction_tab3', 'wchecks_tab_direction_tab', 11, 2);
function wchecks_tab_direction_tab($data, $data_id) {
?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Payment systems fees (for verified accounts)', 'pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('User &rarr; Exchange', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<div><input type="text" name="com_sum1_check" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_sum1_check')); ?>" /> S</div>
				<div><input type="text" name="com_pers1_check" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_pers1_check')); ?>" /> %</div>
			</div>							
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange &rarr; User', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<div><input type="text" name="com_sum2_check" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_sum2_check')); ?>" /> S</div>
				<div><input type="text" name="com_pers2_check" style="width: 80%;" value="<?php echo is_sum(is_isset($data, 'com_pers2_check')); ?>" /> %</div>	
			</div>		
		</div>
	</div>
<?php
} 

add_action('tab_direction_verify', 'wchecks_tab_direction_verify', 100, 2);
function wchecks_tab_direction_verify($data, $data_id) {		
?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Checking account for verification in PS', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="check_purse" id="check_purse" autocomplete="off">
					<option value="0" <?php selected(is_isset($data, 'check_purse'), 0); ?>><?php _e('No', 'pn'); ?></option>
					<option value="1" <?php selected(is_isset($data, 'check_purse'), 1); ?>><?php _e('Account Send', 'pn'); ?></option>
					<option value="2" <?php selected(is_isset($data, 'check_purse'), 2); ?>><?php _e('Account Receive', 'pn'); ?></option>
					<option value="3" <?php selected(is_isset($data, 'check_purse'), 3); ?>><?php _e('Account Send and Receive', 'pn'); ?></option>
				</select>
			</div>							
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Require account verification in PS', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="req_check_purse" id="req_check_purse" autocomplete="off">
					<option value="0" <?php selected(is_isset($data, 'req_check_purse'), 0); ?>><?php _e('No', 'pn'); ?></option>
					<option value="1" <?php selected(is_isset($data, 'req_check_purse'), 1); ?>><?php _e('Account Send', 'pn'); ?></option>
					<option value="2" <?php selected(is_isset($data, 'req_check_purse'), 2); ?>><?php _e('Account Receive', 'pn'); ?></option>
					<option value="3" <?php selected(is_isset($data, 'req_check_purse'), 3); ?>><?php _e('Account Send and Receive', 'pn'); ?></option>
				</select>
			</div>		
		</div>
	</div>
<?php 
} 

add_filter('pn_direction_addform_post', 'wchecks_direction_addform_post');
function wchecks_direction_addform_post($array) {
	
	$array['com_sum1_check'] = is_sum(is_param_post('com_sum1_check'));	
	$array['com_pers1_check'] = is_sum(is_param_post('com_pers1_check'));
	$array['com_sum2_check'] = is_sum(is_param_post('com_sum2_check'));	
	$array['com_pers2_check'] = is_sum(is_param_post('com_pers2_check'));			
	$array['check_purse'] = intval(is_param_post('check_purse'));
	$array['req_check_purse'] = intval(is_param_post('req_check_purse'));
	
	return $array;
}

add_filter('list_export_directions', 'wchecks_list_export_directions');
function wchecks_list_export_directions($array) {
	
	$array['com_sum1_check'] = __('Fee Send for verfified account','pn');
	$array['com_pers1_check'] = __('Fee (%) Send for verfified account','pn');
	$array['com_sum2_check'] = __('Fee Receive for verfified account','pn');
	$array['com_pers2_check'] = __('Fee (%) Receive for verfified account','pn');
	
	return $array;
}

add_filter('export_directions_filter', 'wchecks_export_directions_filter');
function wchecks_export_directions_filter($export_currency_filter) {
	
	$export_currency_filter['sum_arr'][] = 'com_sum1_check';
	$export_currency_filter['sum_arr'][] = 'com_pers1_check';
	$export_currency_filter['sum_arr'][] = 'com_sum2_check';
	$export_currency_filter['sum_arr'][] = 'com_pers2_check';
	
	return $export_currency_filter;
}