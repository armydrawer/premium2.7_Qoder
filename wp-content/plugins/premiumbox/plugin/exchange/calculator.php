<?php
if (!defined('ABSPATH')) { exit(); }
	
add_action('premium_siteaction_exchange_calculator', 'def_premium_siteaction_exchange_calculator');
function def_premium_siteaction_exchange_calculator() { 
	global $wpdb, $premiumbox;	
	
	_method('post');
	_json_head(); 
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error', 'pn');		

	$cdata = '';
	$calc_data = '';	
	$comis_text1 = ' ';
	$comis_text2 = ' ';
	$error_fields = array();
	$sum1 = 0;
	$sum1c = 0;
	$sum2 = 0;
	$sum2c = 0;
	$viv_com1 = $viv_com2 = 0;
	$user_discount = ' ';
	$course_html = ' ';
	$reserve_html = ' ';
	$changed = 0;
	
	$direction_id = intval(is_param_post('id'));
	$sum = is_sum(is_param_post('sum'));
	$dej = intval(is_param_post('dej'));
	$cd = urldecode(trim(is_param_post('cd')));
	parse_str($cd, $cd_arr);
	
	$show_data = pn_exchanges_output('exchange');
	if (1 == $show_data['show']) {
		if ($dej > 0 or $dej < 5) { 
			$where = get_directions_where('exchange');
			$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where AND id = '$direction_id'");
			if (isset($direction->id)) {
				$output = apply_filters('get_direction_output', 1, $direction, 'exchange');
				if ($output) {
					$currency_id_give = $direction->currency_id_give;
					$currency_id_get = $direction->currency_id_get;
					$vd1 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_give' AND auto_status = '1' AND currency_status = '1'");
					$vd2 = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_get' AND auto_status = '1' AND currency_status = '1'");
					if (isset($vd1->id) and isset($vd2->id)) {
						
						$calc_data = array( 
							'vd1' => $vd1,
							'vd2' => $vd2,
							'direction' => $direction,
							'user_id' => $user_id,
							'ui' => $ui,
							'post_sum' => $sum,
							'dej' => $dej,
							'cd' => $cd_arr,
						);
						$calc_data = apply_filters('get_calc_data_params', $calc_data, 'calculator');							
						$cdata = get_calc_data($calc_data, 1);
							
						$course_give = $cdata['course_give'];
						$course_get = $cdata['course_get'];
						$decimal_give = $cdata['decimal_give'];
						$decimal_get = $cdata['decimal_get'];
						$currency_code_give = $cdata['currency_code_give'];
						$currency_code_get = $cdata['currency_code_get'];
						$sum1 = $cdata['sum1'];
						$sum1c = $cdata['sum1c'];
						$sum2 = $cdata['sum2'];
						$sum2c = $cdata['sum2c'];
						$comis_text1 = $cdata['comis_text1'];
						$comis_text2 = $cdata['comis_text2'];
						$user_discount = $cdata['user_discount'];
						$viv_com1 = $cdata['viv_com1'];
						$viv_com2 = $cdata['viv_com2'];
						$changed = $cdata['changed'];
						
						add_pn_cookie('cache_sum', $sum1);
								
						$minmax_data = array(
							'min_give' => $cdata['min_give'],
							'max_give' => $cdata['max_give'],
							'min_get' => $cdata['min_get'],
							'max_get' => $cdata['max_get'],				
						);
						
						$dir_minmax = get_direction_minmax($direction, $vd1, $vd2, $course_give, $course_get, $cdata['reserve'], 'calculator', $minmax_data); 
						$min1 = is_isset($dir_minmax, 'min_give');
						$max1 = is_isset($dir_minmax, 'max_give');
						$min2 = is_isset($dir_minmax, 'min_get');
						$max2 = is_isset($dir_minmax, 'max_get');							 	
									
						if ($sum1 < $min1) {
							$error_fields['sum1'] = '<span class="js_amount" data-id="sum1" data-val="' . $min1 . '">' . __('min', 'pn') . '.: ' . is_out_sum($min1, $decimal_give, 'tbl') . ' ' . $currency_code_give . '</span>';														
						}
									
						if ($sum1 > $max1 and is_numeric($max1)) {
							$error_fields['sum1'] = '<span class="js_amount" data-id="sum1" data-val="' . $max1 . '">' . __('max', 'pn') . '.: ' . is_out_sum($max1, $decimal_give, 'tbl') . ' ' . $currency_code_give . '</span>';													
						}
									
						if ($sum2 < $min2) {
							$error_fields['sum2'] = '<span class="js_amount" data-id="sum2" data-val="' . $min2 . '">' . __('min', 'pn') . '.: ' . is_out_sum($min2, $decimal_get, 'tbl') . ' ' . $currency_code_get . '</span>';														
						}
									
						if ($sum2 > $max2 and is_numeric($max2)) {
							$error_fields['sum2'] = '<span class="js_amount" data-id="sum2" data-val="' . $max2 . '">' . __('max', 'pn') . '.: ' . is_out_sum($max2, $decimal_get, 'tbl') . ' ' . $currency_code_get . '</span>';													
						}	

						$reserve = is_out_sum($cdata['reserve'], $vd2->currency_decimal, 'reserv');
						$reserve_html = $reserve . ' ' . $currency_code_get;
						
						$course_html = is_out_sum($course_give, $decimal_give, 'course') . ' ' . $currency_code_give . ' = ' . is_out_sum($course_get, $decimal_get, 'course') . ' ' . $currency_code_get;									

						if ($sum1 <= 0) {
							$error_fields['sum1'] = __('amount must be greater than 0', 'pn');
						}	
						
						if ($sum2 <= 0) {
							$error_fields['sum2'] = __('amount must be greater than 0', 'pn');
						}
						
						if ($sum1c <= 0) {
							$error_fields['sum1c'] = __('amount must be greater than 0', 'pn');
						}
						
						if ($sum2c <= 0) {
							$error_fields['sum2c'] = __('amount must be greater than 0', 'pn');
						}	
						
					}
				}
			}
		}
	}
	
	$log['sum1'] = $sum1;
	$log['sum2'] = $sum2;
	$log['sum1c'] = $sum1c;
	$log['sum2c'] = $sum2c;
	$log['changed'] = $changed;
	$log['viv_com1'] = $viv_com1;
	$log['viv_com2'] = $viv_com2;	
	$log['user_discount'] = $user_discount;
	$log['course_html'] = $course_html;
	$log['reserve_html'] = $reserve_html;
	$log['comis_text1'] = $comis_text1;
	$log['comis_text2'] = $comis_text2;
	$log['error_fields'] = $error_fields;
	$log = apply_filters('log_exchange_changes', $log, $cdata, $calc_data);
	
	echo pn_json_encode($log);
	exit;
}