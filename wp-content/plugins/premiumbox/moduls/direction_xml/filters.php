<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('set_exchange_cat_filters', 'set_exchange_cat_filters_txtxml');
function set_exchange_cat_filters_txtxml($cats) {
	
	$cats['files'] = __('Files containing rates needed for monitoring', 'pn');
	
	return $cats;
}

add_action('tab_currency_tab1', 'txtxml_tab_currency_tab1', 41, 2);
function txtxml_tab_currency_tab1($data, $data_id) {
	global $premiumbox;

	$count = intval($premiumbox->get_option('txtxml', 'alias'));
	$xml_value_alias = pn_json_decode(is_isset($data, 'xml_value_alias'));
	if (!is_array($xml_value_alias)) {
		$xml_value_alias = array();
	}

	$r = 0;
	while ($r++ < $count) {
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('XML name', 'pn'); ?> (vers: <?php echo $r; ?>)</span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="xml_value_alias[<?php echo $r; ?>]" value="<?php echo is_xml_value(is_isset($xml_value_alias, $r)); ?>" />
			</div>
		</div>
	</div>
<?php		
	}
}

add_filter('pn_currency_addform_post', 'txtxml_pn_currency_addform_post');
function txtxml_pn_currency_addform_post($array) {
	global $premiumbox;
	
	$xml_value_alias = array();
	$post = is_param_post('xml_value_alias');
	$count = intval($premiumbox->get_option('txtxml', 'alias'));
	$r = 0;
	while ($r++ < $count) {
		$xml_value_alias[$r] = is_xml_value(is_isset($post, $r));
	}
	$array['xml_value_alias'] = pn_json_encode($xml_value_alias);
	
	return $array;
}
 
add_filter('vd1_filter_dirredirect', 'txtxml_vd1_filter_dirredirect', 10, 2);
function txtxml_vd1_filter_dirredirect($filter, $cur) {
	
	$filter .= " OR auto_status='1' AND currency_status = '1' AND xml_value_alias LIKE '%\"{$cur}\"%'";
	
	return $filter;
} 

add_filter('vd2_filter_dirredirect', 'txtxml_vd2_filter_dirredirect', 10, 2);
function txtxml_vd2_filter_dirredirect($filter, $cur) {
	
	$filter .= " OR auto_status='1' AND currency_status = '1' AND xml_value_alias LIKE '%\"{$cur}\"%'";
	
	return $filter;
}

add_action('list_tabs_direction', 'txtxml_list_tabs_direction'); 
function txtxml_list_tabs_direction($list_tabs) {
	
	$list_tabs['tab12'] = __('TXT and XML export settings', 'pn');
	
	return $list_tabs;
}

add_action('tab_direction_tab12', 'txtxml_tab_direction_tab12', 10, 2);
function txtxml_tab_direction_tab12($data, $data_id) {
?>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Show in file', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="show_file" autocomplete="off">
					<?php 
					$show_file = is_isset($data, 'show_file'); 
					if (!is_numeric($show_file)) { $show_file = 1; }
					?>						
					<option value="1" <?php selected($show_file, 1); ?>><?php _e('Yes', 'pn'); ?></option>
					<option value="0" <?php selected($show_file, 0); ?>><?php _e('No', 'pn'); ?></option>
					<option value="2" <?php selected($show_file, 2); ?>><?php _e('According to shedule', 'pn'); ?></option>						
				</select>
			</div>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Show exchange direction on shedule', 'pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<?php
					$xml_show = explode(':', is_isset($data, 'xml_show1'));
					$h1 = is_isset($xml_show, 0);
					$m1 = is_isset($xml_show, 1);
				?>
				<select name="xml_show_h1" style="width: 50px;" autocomplete="off">	
					<?php
					$r = -1;
					while ($r++ < 23) {
					?>
						<option value="<?php echo $r; ?>" <?php selected($h1, $r);?>><?php echo zeroise($r, 2); ?></option>
					<?php } ?>
				</select>
					:
				<select name="xml_show_m1" style="width: 50px;" autocomplete="off">	
					<?php
					$r = -1;
					while ($r++ < 59) {
					?>
						<option value="<?php echo $r; ?>" <?php selected($m1, $r);?>><?php echo zeroise($r, 2); ?></option>
					<?php } ?>
				</select>				
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<?php
					$xml_show = explode(':', is_isset($data, 'xml_show2'));
					$h2 = is_isset($xml_show, 0);
					$m2 = is_isset($xml_show, 1);
				?>			
				<select name="xml_show_h2" style="width: 50px;" autocomplete="off">	
					<?php
					$r = -1;
					while ($r++ < 23) {
					?>
						<option value="<?php echo $r; ?>" <?php selected($h2, $r); ?>><?php echo zeroise($r, 2); ?></option>
					<?php } ?>
				</select>	
					:
				<select name="xml_show_m2" style="width: 50px;" autocomplete="off">	
					<?php
					$r = -1;
					while ($r++ < 59) {
					?>
						<option value="<?php echo $r; ?>" <?php selected($m2, $r); ?>><?php echo zeroise($r, 2); ?></option>
					<?php } ?>
				</select>								
			</div>
		</div>		
	</div>	
<?php
}	

add_action('tab_direction_tab12', 'txtxmlcity_tab_direction_tab12', 15, 2);
function txtxmlcity_tab_direction_tab12($data, $data_id) {
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('City where exchanges with cash is available', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="xml_city" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($data, 'xml_city')); ?>" />
			</div>
		</div>
	</div>
<?php
}

add_action('tab_direction_tab12', 'txtxml_tab_direction_tab12p', 20, 2);
function txtxml_tab_direction_tab12p($data, $data_id) {
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span>floating</span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="xml_floating" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($data, 'xml_floating')); ?>" />
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span>delay</span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="xml_delay" style="width: 100%;" value="<?php echo intval(is_isset($data, 'xml_delay')); ?>" />
			</div>
		</div>		
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<select name="xml_manual" autocomplete="off">
					<?php 
						$xml_manual = is_isset($data, 'xml_manual'); 
					?>						
					<option value="0" <?php selected($xml_manual, 0); ?>><?php _e('Default exchange mode', 'pn'); ?></option>
					<option value="1" <?php selected($xml_manual, 1); ?>><?php _e('Auto exchange mode (forced)', 'pn'); ?></option>
					<option value="2" <?php selected($xml_manual, 2); ?>><?php _e('Manual exchange mode (forced)', 'pn'); ?></option>
				</select>
			</div>			
		</div>
		<div class="add_tabs_single">
			<div class="premium_wrap_standart">
				<select name="xml_juridical" autocomplete="off">
					<?php 
						$xml_juridical = is_isset($data, 'xml_juridical'); 
					?>						
					<option value="0" <?php selected($xml_juridical, 0); ?>><?php _e('Individual transfer', 'pn');?></option>
					<option value="1" <?php selected($xml_juridical, 1); ?>><?php _e('Legal entity transfer', 'pn');?></option>
				</select>
			</div>		
		</div>		
	</div>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Tags for parameter param', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="xml_param" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($data, 'xml_param')); ?>" />
			</div>
		</div>
	</div>			
<?php	
}

add_filter('pn_direction_addform_post', 'txtxml_pn_direction_addform_post');
function txtxml_pn_direction_addform_post($array) {
	
	$array['show_file'] = intval(is_param_post('show_file'));
	$array['xml_show1'] = intval(is_param_post('xml_show_h1')) . ':' . intval(is_param_post('xml_show_m1'));
	$array['xml_show2'] = intval(is_param_post('xml_show_h2')) . ':' . intval(is_param_post('xml_show_m2'));
	$xml_floating = pn_strip_input(is_param_post('xml_floating'));
	if (strlen($xml_floating) < 1) { $xml_floating = '0'; }
	$array['xml_floating'] = $xml_floating;
	$array['xml_city'] = pn_strip_input(is_param_post('xml_city'));
	$array['xml_param'] = pn_strip_input(is_param_post('xml_param'));
	$array['xml_manual'] = intval(is_param_post('xml_manual'));
	$array['xml_juridical'] = intval(is_param_post('xml_juridical'));
	$array['xml_delay'] = intval(is_param_post('xml_delay'));
	
	return $array;
}

add_filter('get_directions_where', 'txtxml_get_directions_where', 10, 2);
function txtxml_get_directions_where($where, $place) {
	
	if ('files' == $place) {
		$where .= "AND show_file IN('1','2') ";	
	}
	
	return $where;
} 

function get_dirxml_show($ind, $item) {
	
	if (2 == $item->show_file) {
		$ind = 0;
		$now_time = current_time('timestamp');
		$today = date('d.m.Y', $now_time);
		$yestarday = date('d.m.Y', ($now_time - (24 * 60 * 60)));
		$tomorrow = date('d.m.Y', ($now_time + (24 * 60 * 60)));
		$xml_show = explode(':', is_isset($item, 'xml_show1'));
		$h1 = zeroise(intval(is_isset($xml_show, 0)), 2);
		$m1 = zeroise(intval(is_isset($xml_show, 1)), 2);
		$xml_show = explode(':', is_isset($item, 'xml_show2'));
		$h2 = zeroise(intval(is_isset($xml_show, 0)), 2);
		$m2 = zeroise(intval(is_isset($xml_show, 1)), 2);	
		if ($h1 > $h2 or $h1 == $h2 and $m1 > $m2) { 
			$time1 = strtotime($yestarday . ' ' . $h1 . ':' . $m1);
			$time2 = strtotime($today . ' ' . $h2 . ':' . $m2);
			$time3 = strtotime($today . ' ' . $h1 . ':' . $m1);
			$time4 = strtotime($tomorrow . ' ' . $h2 . ':' . $m2);
			if ($now_time >= $time1 and $now_time < $time2 or $now_time >= $time3 and $now_time < $time4) {
				$ind = 1;
			}
		} elseif ($h1 == $h2 and $m1 == $m2) {
			$ind = 1;
		}  else { 
			$time1 =  strtotime($today . ' ' . $h1 . ':' . $m1);
			$time2 =  strtotime($today . ' ' . $h2 . ':' . $m2);
			if ($now_time >= $time1 and $now_time < $time2) {
				$ind = 1;
			}	
		}	
	}
	
	return $ind;
}

add_filter('get_direction_output', 'txtxml_get_direction_output', 10, 3);
function txtxml_get_direction_output($ind, $item, $place) {
	
	if (1 == $ind and 'files' == $place) {
		return get_dirxml_show($ind, $item);
	}
	
	return $ind;
} 