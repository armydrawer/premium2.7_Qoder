<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Show Exchange direction settings in XML/TXT file[:en_US][ru_RU:]Настройка вывода направлений обмена в XML/TXT файле[:ru_RU]
description: [en_US:]Show Exchange direction settings in XML/TXT file[:en_US][ru_RU:]Настройка вывода направлений обмена в XML/TXT файле[:ru_RU]
version: 2.7.1
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_directionxml');
add_action('pn_plugin_activate', 'bd_all_moduls_active_directionxml');
function bd_all_moduls_active_directionxml() {
	global $wpdb;	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'show_file'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `show_file` int(1) NOT NULL default '1'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'xml_city'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `xml_city` longtext NOT NULL");
    } else {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions CHANGE `xml_city` `xml_city` longtext NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'xml_manual'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `xml_manual` int(1) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'xml_juridical'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " .$wpdb->prefix . "directions ADD `xml_juridical` int(1) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'xml_floating'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `xml_floating` varchar(50) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'xml_delay'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `xml_delay` varchar(50) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'xml_show1'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `xml_show1` varchar(50) NOT NULL");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'xml_show2'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `xml_show2` varchar(50) NOT NULL");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'xml_param'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `xml_param` longtext NOT NULL");
    }

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'xml_value_alias'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `xml_value_alias` longtext NOT NULL");
    }
	
}	

add_action('pn_config_option_post', 'txtxml_create_bd', 1000);
add_action('pn_exchange_filters_option_post', 'txtxml_create_bd', 1000);
add_action('pntable_psys_save', 'txtxml_create_bd', 1000);
add_action('pntable_psys_action', 'txtxml_create_bd', 1000);
add_action('item_psys_add', 'txtxml_create_bd', 1000);
add_action('item_psys_edit', 'txtxml_create_bd', 1000);
add_action('pntable_currency_save', 'txtxml_create_bd', 1000);
add_action('pntable_currency_action', 'txtxml_create_bd', 1000);
add_action('item_currency_add', 'txtxml_create_bd', 1000);
add_action('item_currency_edit', 'txtxml_create_bd', 1000);
add_action('pntable_currency_codes_save', 'txtxml_create_bd', 1000);
add_action('pntable_currency_codes_action', 'txtxml_create_bd', 1000);
add_action('item_currency_code_edit', 'txtxml_create_bd', 1000);
add_action('item_currency_code_add', 'txtxml_create_bd', 1000);
add_action('pntable_directions_save', 'txtxml_create_bd', 1000);
add_action('pntable_directions_action', 'txtxml_create_bd', 1000);
add_action('item_direction_edit', 'txtxml_create_bd', 1000);
add_action('item_direction_add', 'txtxml_create_bd', 1000);
add_action('after_update_currency_reserve', 'txtxml_create_bd', 1000);
add_action('after_update_direction_reserve', 'txtxml_create_bd', 1000);
add_action('export_direction_end', 'txtxml_create_bd', 1000);
add_action('export_currency_end', 'txtxml_create_bd', 1000);
add_action('pntable_indxs_save', 'txtxml_create_bd', 1000);
add_action('pntable_indxs_action', 'txtxml_create_bd', 1000);
add_action('item_indxs_edit', 'txtxml_create_bd', 1000);
add_action('item_indxs_add', 'txtxml_create_bd', 1000);
add_action('item_parser_pairs_edit', 'txtxml_create_bd', 1000);
add_action('item_parser_pairs_add', 'txtxml_create_bd', 1000);
add_action('pntable_parser_pairs_save', 'txtxml_create_bd', 1000);
add_action('pntable_parser_pairs_action', 'txtxml_create_bd', 1000);
add_action('pntable_parsercourses_deleteall', 'txtxml_create_bd', 1000);
add_action('item_bccorrs_edit', 'txtxml_create_bd', 1000);
add_action('pntable_bccorrs_save', 'txtxml_create_bd', 1000);
add_action('pntable_bccorrs_action', 'txtxml_create_bd', 1000);
add_action('request_bestchange_end', 'txtxml_create_bd', 20);
add_action('item_bcorrs_edit', 'txtxml_create_bd', 1000);
add_action('pntable_bcorrs_save', 'txtxml_create_bd', 1000);
add_action('pntable_bcorrs_action', 'txtxml_create_bd', 1000);
add_action('request_bestchangeapi_end', 'txtxml_create_bd', 20);
add_action('load_new_parser_courses', 'txtxml_create_bd', 20);
add_action('request_fcourse', 'txtxml_create_bd', 20);
add_action('fres_change_reserve', 'txtxml_create_bd', 20);
add_action('pn_txtxml_option_post', 'txtxml_create_bd', 20);
add_action('reservcurs_end', 'txtxml_create_bd', 1000);
add_action('item_cities_edit', 'txtxml_create_bd', 20);
add_action('item_cities_add', 'txtxml_create_bd', 20);
add_action('pntable_cities_save', 'txtxml_create_bd', 20);
add_action('pntable_cities_action', 'txtxml_create_bd', 20);

function txtxml_create_bd_filter($v) {
	
	txtxml_create_bd();
	
	return $v;
}

function txtxml_create_bd($ind = 0) {
	global $wpdb, $premiumbox;

	$ind = intval($ind);
	if (1 == $premiumbox->get_option('txtxml', 'create') or 1 == $ind) {

		$time = current_time('timestamp');
		update_option('txtxml_create_time', $time);
	
		$fromfee_setting = intval($premiumbox->get_option('txtxml', 'fromfee'));
		$tofee_setting = intval($premiumbox->get_option('txtxml', 'tofee'));
		$decimal_with = intval($premiumbox->get_option('txtxml', 'decimal_with'));
		$decimal = intval($premiumbox->get_option('txtxml', 'decimal'));

		$directions = array();
	
		$v = get_currency_data();

		$where = get_directions_where("files");
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where ORDER BY site_order1 ASC");
		foreach ($items as $item) { 
			$output = apply_filters('get_direction_output', 1, $item, 'files');
			if (1 == $output) {
				
				$valid1 = $item->currency_id_give;
				$valid2 = $item->currency_id_get;
						
				if (isset($v[$valid1]) and isset($v[$valid2])) {
					$vd1 = $v[$valid1];
					$vd2 = $v[$valid2];
					$decimal1 = $vd1->currency_decimal;
					$decimal2 = $vd2->currency_decimal;
					if (1 == $decimal_with) {
						$decimal1 = $decimal2 = $decimal;
					}
					$direction_id = $item->id;
				
					$lines = array();
					$lines['d1'] = $decimal1;
					$lines['d2'] = $decimal2;
					$lines['dir'] = $item;					
					$lines['cid1'] = $vd1->id;	
					$lines['cid2'] = $vd2->id;

					$from = array(
						'0' => mb_strtoupper(is_xml_value($vd1->xml_value)),
					);
					$alias = pn_json_decode($vd1->xml_value_alias);
					if (is_array($alias)) {
						foreach ($alias as $ver => $xml) {
							$from[$ver] = mb_strtoupper(is_xml_value($xml));
						}
					}
					$lines['from'] = $from;
					
					$to = array(
						'0' => mb_strtoupper(is_xml_value($vd2->xml_value)),
					);
					$alias = pn_json_decode($vd2->xml_value_alias);
					if (is_array($alias)) {
						foreach ($alias as $ver => $xml) {
							$from[$ver] = mb_strtoupper(is_xml_value($xml));
						}
					}					
					$lines['to'] = $to;
					
					$dir_c = is_course_direction($item, $vd1, $vd2, 'files');
					
					$lines['in'] = is_sum(is_isset($dir_c, 'give'), $decimal1); 
					$lines['out'] = is_sum(is_isset($dir_c, 'get'), $decimal2);
					$lines['amount'] = get_direction_reserve($vd1, $vd2, $item);
					
					$currency_code_give = is_site_value($vd1->currency_code_title);
					$currency_code_get = is_site_value($vd2->currency_code_title);
					
					$lines['c1'] = $currency_code_give;
					$lines['c2'] = $currency_code_get;
					
					$min1 = is_sum($item->com_box_min1, $decimal1);
					$min2 = is_sum($item->com_box_min2, $decimal2);
					if ($min1 > 0) { 
						$minfee = $min1;
						$vtype = $currency_code_give;
					} else {
						$minfee = $min2;
						$vtype = $currency_code_get;					
					}
					if ($minfee > 0) {
						$lines['minfee'] = $minfee . ' ' . $vtype;
					}						
							
					$fromfee = array();
					if (1 == $fromfee_setting) {
						if ($item->com_sum1) { 
							$fromfee[] = is_sum($item->com_sum1, $decimal1) . ' ' . $currency_code_give;
						}
						if ($item->com_pers1) {
							$fromfee[] = is_sum($item->com_pers1, $decimal1) . ' %';
						}							
					} elseif (2 == $fromfee_setting) {
						if (isset($item->com_sum1_check) and $item->com_sum1_check) { 
							$fromfee[] = is_sum($item->com_sum1_check, $decimal1) . ' ' . $currency_code_give;
						}
						if (isset($item->com_pers1_check) and $item->com_pers1_check) {
							$fromfee[] = is_sum($item->com_pers1_check, $decimal1). ' %';
						}							
					} else {
						if (0 == $item->dcom1) {
							if (isset($item->com_box_sum1) and $item->com_box_sum1) { 
								$fromfee[] = is_sum($item->com_box_sum1, $decimal1) . ' ' . $currency_code_give;
							}
							if (isset($item->com_box_pers1) and $item->com_box_pers1) {
								$fromfee[] = is_sum($item->com_box_pers1, $decimal1) . ' %';
							}	
						}
					}
					if (count($fromfee) > 0) {
						$lines['fromfee'] = join(', ', $fromfee);
					}	

					$tofee = array();
					if (1 == $tofee_setting) {
						if ($item->com_sum2) { 
							$tofee[] = is_sum($item->com_sum2, $decimal2) . ' ' . $currency_code_get;
						}
						if ($item->com_pers2) {
							$tofee[] = is_sum($item->com_pers2, $decimal2) . ' %';
						}							
					} elseif (2 == $tofee_setting) {
						if (isset($item->com_sum2_check) and $item->com_sum2_check) { 
							$tofee[] = is_sum($item->com_sum2_check, $decimal2) . ' ' . $currency_code_get;
						}
						if (isset($item->com_pers2_check) and $item->com_pers2_check) {
							$tofee[] = is_sum($item->com_pers2_check, $decimal2) . ' %';
						}							
					} else {
						if (0 == $item->dcom1) {
							if ($item->com_box_sum2) { 
								$tofee[] = is_sum($item->com_box_sum2, $decimal2) . ' ' . $currency_code_get;
							}
							if ($item->com_box_pers2) {
								$tofee[] = is_sum($item->com_box_pers2, $decimal2) . ' %';
							}	
						}
					}						
					if (count($tofee) > 0) {
						$lines['tofee'] = join(', ', $tofee);
					}
					
					$dir_minmax = get_direction_minmax($item, $vd1, $vd2, $lines['in'], $lines['out'], $lines['amount'], 'xml');  
					$min1 = is_isset($dir_minmax, 'min_give');
					$max1 = is_isset($dir_minmax, 'max_give');
					
					$lines['minamount'] = $min1 . ' ' . $currency_code_give;
					if (is_numeric($max1)) {
						$lines['maxamount'] = $max1 . ' ' . $currency_code_give;
					}												
					
					$xml_floating = pn_strip_input(is_isset($item, 'xml_floating'));
					if ($xml_floating) {
						$lines['floating'] = $xml_floating;
					}
					
					$xml_delay = intval(is_isset($item, 'xml_delay'));
					if ($xml_delay) {
						$lines['delay'] = $xml_delay;
					}

					$m_in = is_isset($item,'m_in');
					$m_in_arr = @unserialize($m_in);
					$has_m_in = 0;
					if (!is_array($m_in_arr) and strlen($m_in) > 0 or is_array($m_in_arr) and count($m_in_arr) > 0) {
						$has_m_in = 1;
					}	
					$m_out = is_isset($item, 'm_out');
					$m_out_arr = @unserialize($m_out);
					$has_m_out = 0;
					if (!is_array($m_out_arr) and strlen($m_out) > 0 or is_array($m_out_arr) and count($m_out_arr) > 0) {
						$has_m_out = 1;
					}					
					
					$params = array();
					$xml_param = pn_strip_input(is_isset($item, 'xml_param'));
					if ($xml_param) {
						$params[] = $xml_param;
					}
							
					$xml_manual = intval(is_isset($item, 'xml_manual'));
					if (0 == $xml_manual) {
						if (1 != $has_m_in or 1 != $has_m_out) {
							$params[] = 'manual';
						}
					} elseif (2 == $xml_manual) {
						$params[] = 'manual';
					} 
					
					$xml_juridical = intval(is_isset($item, 'xml_juridical'));
					if ($xml_juridical) {
						$params[] = 'juridical';
					}
					if (count($params) > 0) {
						$lines['param'] = join(',', $params);
					}				
					
					$lines['cities'] = pn_strip_input(is_isset($item, 'xml_city'));
					$lines = apply_filters('file_xml_lines', $lines, $item, $vd1, $vd2, $decimal1, $decimal2);
					if (count($lines) > 0) {
						$directions[$item->id] = $lines;
					}
				}
			}
		}
	
		$directions = apply_filters('file_xml_directions', $directions);
		update_array_option($premiumbox, 'pn_directions_filedata', $directions);		

	}
} 

add_filter('file_xml_directions', 'file_xml_directions_parsecities');
function file_xml_directions_parsecities($old_directions) {
	
	$directions = array();
	foreach ($old_directions as $old_direction_id => $line) {
		$cities = explode(',', is_isset($line, 'cities'));
		if (isset($line['cities'])) {
			unset($line['cities']);
		}	
		$r = 0; 
		foreach ($cities as $city) { $r++;
			$key = $old_direction_id . '_' . $r;
			$city = trim($city);
			if ($city) {
				$directions[$key] = array_merge($line, array('city' => $city));
			} else {
				$directions[$key] = $line;
			}		
		}	
	}
	
	return $directions;
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'settings');
$premiumbox->include_path(__FILE__, 'output');
$premiumbox->include_path(__FILE__, 'filters');