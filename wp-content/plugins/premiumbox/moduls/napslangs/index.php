<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Exchange directions Language settings[:en_US][ru_RU:]Настройка языков для направлений обмена[:ru_RU]
description: [en_US:]Exchange directions Language settings[:en_US][ru_RU:]Настройка языков для направлений обмена[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_napslangs');
add_action('pn_plugin_activate', 'bd_all_moduls_active_napslangs');
function bd_all_moduls_active_napslangs() {
	global $wpdb;	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'naps_lang'"); /* 1.6 */
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `naps_lang` longtext NOT NULL");
    } else {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions CHANGE `naps_lang` `naps_lang` longtext NOT NULL");
	}
	
}

add_action('tab_direction_tab7', 'napslangs_tab_direction_tab', 1, 2);
function napslangs_tab_direction_tab($data, $data_id) {
	
	if (is_ml()) { 
		$langs = get_langs_ml();
		?>
		<div class="add_tabs_line">
			<div class="add_tabs_single long">
				<div class="add_tabs_sublabel"><span><?php _e('Language', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					
					<?php
					$scroll_lists = array();
						
					$def = pn_strip_input_array(pn_json_decode(is_isset($data, 'naps_lang')));						
					foreach ($langs as $lang) {
						$checked = 0;
						if (in_array($lang, $def) or 0 == count($def)) {
							$checked = 1;
						}	
						$scroll_lists[] = array(
							'title' => get_title_forkey($lang),
							'checked' => $checked,
							'value' => $lang,
						);
					}
					echo get_check_list($scroll_lists, 'naps_lang[]');
					?>				
					
						<div class="premium_clear"></div>
				</div>
			</div>
		</div>		
	<?php }	
	
}

add_filter('pn_direction_addform_post', 'napslangs_pn_direction_addform_post');
function napslangs_pn_direction_addform_post($array) {
	
	$array['naps_lang'] = pn_json_encode(pn_strip_input_array(is_param_post('naps_lang')));
	
	return $array;
}

add_action('set_exchange_filters', 'napslangs_set_exchange_filters');
function napslangs_set_exchange_filters($lists) {
	
	$lists[] = array(
		'title' => __('Filter by user language', 'pn'),
		'name' => 'napslangs',
	);
	
	return $lists;
}

add_filter('get_direction_output', 'napslangs_get_direction_output', 10, 3);
function napslangs_get_direction_output($show, $dir, $place) {
	global $premiumbox;	
	
	if (1 == $show) {
		$lang = get_locale();
		$ind = $premiumbox->get_option('exf_' . $place . '_napslangs');
		if (1 == $ind) {
			$def = pn_strip_input_array(pn_json_decode(is_isset($dir, 'naps_lang')));
			if (count($def) > 0 and !in_array($lang, $def)) {
				$show = 0;
			}
		}				
	}
	
	return $show;
}

add_filter('error_bids', 'error_bids_napslangs', 60, 2);   
function error_bids_napslangs($error_bids, $direction) {

	$user_locale = is_isset($error_bids['bid'], 'bid_locale');
	$naps_lang = pn_strip_input_array(pn_json_decode(is_isset($direction, 'naps_lang')));
	if (!in_array($user_locale, $naps_lang) and count($naps_lang) > 0) {
		$error_bids['error_text'][] = __('Error! Exchange direction is prohibited for your language', 'pn');			
	}	
	
	return $error_bids;
}