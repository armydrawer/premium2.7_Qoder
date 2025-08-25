<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Currency tags[:en_US][ru_RU:]Тег для валют[:ru_RU]
description: [en_US:]Currency tags[:en_US][ru_RU:]Тег для валют[:ru_RU]
version: 2.7.0
category: [en_US:]Currency[:en_US][ru_RU:]Валюты[:ru_RU]
cat: currency
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('pn_plugin_activate', 'bd_all_moduls_active_currtags');
add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_currtags');
function bd_all_moduls_active_currtags() {
	global $wpdb;
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'tags'");
    if (0 == $query) { 
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `tags` longtext NOT NULL");
    }	
	
}

add_action('tab_currency_tab1', 'currtags_tab_currency_tab1', 60, 2);
function currtags_tab_currency_tab1($data, $data_id) {
	
	$form = new PremiumForm();
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Tag', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="tags" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($data, 'tags')); ?>" />
			</div>
		</div>
	</div>
<?php		
}

add_filter('pn_currency_addform_post', 'currtags_currency_addform_post');
function currtags_currency_addform_post($array) {
	
	$array['tags'] = mb_strtolower(pn_strip_input(is_param_post('tags')));
	
	return $array;
}

add_filter('all_vtype_line', 'all_vtype_line_currtags', 10, 2);
function all_vtype_line_currtags($arr, $vd) {
	
	$tagline = pn_strip_input($vd->tags);
	if ($tagline) {
		$tags = explode(',', $tagline);
		$n_ctypes = array();
		foreach ($tags as $tag) {
			$tag_key = mb_strtolower(pn_strip_symbols(replace_cyr($tag)));
			$tag_title = pn_strip_input($tag);
			if ($tag) {
				$n_ctypes[$tag_key] = $tag_title;
			}
		}
		return $n_ctypes;		
	}
	
	return $arr;
}	

add_filter('exchange_table_ct', 'currtags_exchange_table_ct', 10, 2);
function currtags_exchange_table_ct($ctypes, $vd) {
	
	$tagline = pn_strip_input($vd->tags);
	if ($tagline) {
		$tags = explode(',', $tagline);
		$n_ctypes = array();
		foreach ($tags as $tag) {
			$tag = mb_strtolower(pn_strip_symbols(replace_cyr($tag)));
			if ($tag) {
				$n_ctypes[] = $tag;
			}
		}
		
		return $n_ctypes;
	}
	
	return $ctypes;
}