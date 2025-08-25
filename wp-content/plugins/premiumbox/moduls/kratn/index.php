<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Multiplicity of the exchange amount[:en_US][ru_RU:]Кратность суммы обмена[:ru_RU]
description: [en_US:]Multiplicity of the exchange amount[:en_US][ru_RU:]Кратность суммы обмена[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path); 

add_action('pn_plugin_activate', 'bd_all_moduls_active_kratn');
add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_kratn');
function bd_all_moduls_active_kratn() { 
	global $wpdb;

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'kratn'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `kratn` int(1) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'kratn_sum'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `kratn_sum` varchar(150) NOT NULL default '0'");
    }
	
}

add_action('tab_direction_tab2', 'kratn_tab_direction_tab2', 20, 2);
function kratn_tab_direction_tab2($data, $data_id) {
	
	$kratn = intval(is_isset($data,'kratn'));
	
	$exsum = array(
		'0' => __('No', 'pn'),
		'1' => __('Amount To send', 'pn'),
		'2' => __('Amount To send with add. fees', 'pn'),
		'3' => __('Amount To send with add. fees and PS fees', 'pn'),
		'4' => __('Amount Receive', 'pn'),
		'5' => __('Amount To receive with add. fees and PS fees', 'pn'),
	);	
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Multiplicity of the exchange amount', 'pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Amount', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="kratn">
					<?php foreach ($exsum as $ex_k => $ex_t) { ?>
						<option value="<?php echo $ex_k; ?>" <?php selected($ex_k, $kratn); ?>><?php echo $ex_t; ?></option>
					<?php } ?>
				</select>
			</div>			
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Multiplicity', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="kratn_sum" style="width: 100%;" value="<?php echo is_sum(is_isset($data, 'kratn_sum')); ?>" />	
			</div>		
		</div>
	</div>		
	<?php 		
}	

add_filter('pn_direction_addform_post', 'kratn_direction_addform_post');
function kratn_direction_addform_post($array) {
	
	$array['kratn'] = intval(is_param_post('kratn'));
	$array['kratn_sum'] = is_sum(is_param_post('kratn_sum'));
	
	return $array;
}
 
function _kratn($sum, $kratn_sum, $rec) {
	
	if (0 != $kratn_sum) {
		$zn = intval($sum / $kratn_sum);
		if (1 != $rec) {
			if ($zn < 1) { $zn = 1; }
		}
		
		return $zn * $kratn_sum;
	} 
		
	return $sum;
}

add_filter('get_calc_data', 'rkratn_get_calc_data', 10, 2);
function rkratn_get_calc_data($cdata, $calc_data) {
	
	$direction = $calc_data['direction'];
	$kratn = intval(is_isset($direction, 'kratn'));
	$kratn_sum = is_sum(is_isset($direction, 'kratn_sum'));
	if ($kratn > 0 and $kratn_sum > 0) {
		$cdata['correct_up'] = 0;
		$cdata['correct_down'] = 1;
		$cdata['have_kratn'] = intval(is_isset($calc_data, 'have_kratn'));
		$cdata['kratn_sum'] = is_isset($calc_data, 'kratn_sum');
	}
	
	return $cdata;
}
 	
add_filter('get_calc_data', 'kratn_get_calc_data', 9990, 2);
function kratn_get_calc_data($cdata, $calc_data) {
	
	$direction = $calc_data['direction'];
	$kratn = intval(is_isset($direction, 'kratn'));
	$kratn_sum = is_sum(is_isset($direction, 'kratn_sum'));
	$recalc = intval(is_isset($calc_data, 'recalc'));
	
	if ($kratn > 0 and $kratn_sum > 0) {
		
		$have_kratn = intval($cdata['have_kratn']);
		
		$sum = $cdata['sum1'];
		$name = 'sum1';
		$name2 = 'sum1dc';
		$name3 = 'sum1c';
		$dej = 1;
		if (2 == $kratn) {
			$sum = $cdata['sum1dc'];
			$name = 'sum1dc';
			$name2 = 'sum1';
			$name3 = 'sum1c';
			$dej = 5;
			$decimal = $cdata['decimal_give'];	
		} elseif (3 == $kratn) {
			$sum = $cdata['sum1c'];
			$name = 'sum1c';
			$name2 = 'sum1';
			$name3 = 'sum1dc';
			$dej = 3;
			$decimal = $cdata['decimal_give'];	
		} elseif (4 == $kratn) {
			$sum = $cdata['sum2'];
			$name = 'sum2';
			$name2 = 'sum2dc';
			$name3 = 'sum2c';
			$dej = 2;
			$decimal = $cdata['decimal_get'];	
		} elseif (5 == $kratn) {	
			$sum = $cdata['sum2c'];
			$name = 'sum2c';
			$name2 = 'sum2c';
			$name3 = 'sum2dc';
			$dej = 4;	
			$decimal = $cdata['decimal_get'];
		}
		if ($sum > 0) {
			$calc_data['have_kratn'] = 1;
			if ($have_kratn) {
				if ($cdata[$name] == $cdata[$name2] and $cdata[$name] == $cdata[$name3]) {
					$cdata[$name] = $cdata[$name2] = $cdata[$name3] = $cdata['kratn_sum'];
				} else {
					$cdata[$name] = $cdata['kratn_sum'];
				}
			} else {
				$new = _kratn($sum, $kratn_sum, $recalc);
				$calc_data['post_sum'] = $new;
				$calc_data['dej'] = $dej; 
				$calc_data['kratn_sum'] = is_sum($new);
				$c = get_calc_data($calc_data);
				
				return $c;
			}
		}
	}
	
	return $cdata;
}