<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Tips for fields[:en_US][ru_RU:]Подсказки для полей[:ru_RU]
description: [en_US:]Tips for fields[:en_US][ru_RU:]Подсказки для полей[:ru_RU]
version: 2.7.0
category: [en_US:]Currency[:en_US][ru_RU:]Валюты[:ru_RU]
cat: currency
dependent: -
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_fieldhelps');
add_action('pn_plugin_activate', 'bd_all_moduls_active_fieldhelps');
function bd_all_moduls_active_fieldhelps() {
	global $wpdb;
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'txt_give'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `txt_give` longtext NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'txt_get'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `txt_get` longtext NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'helps_give'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `helps_give` longtext NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'helps_get'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency ADD `helps_get` longtext NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency_custom_fields LIKE 'helps_give'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency_custom_fields ADD `helps_give` longtext NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency_custom_fields LIKE 'helps_get'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency_custom_fields ADD `helps_get` longtext NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "direction_custom_fields LIKE 'helps'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "direction_custom_fields ADD `helps` longtext NOT NULL");
	}	
	
}

add_action('tab_currency_tab3', 'fieldhelps_tab_currency_tab3', 30, 2);
function fieldhelps_tab_currency_tab3($data, $data_id) {
	
	$form = new PremiumForm();
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Field title "From Account"', 'pn'); ?></span></div>
			<?php 
			$atts = array();
			$atts['class'] = 'big_input';
			$form->input('txt_give', pn_strip_input(is_isset($data, 'txt_give')), $atts, 1); 
			?>	
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Field title "Onto Account"', 'pn'); ?></span></div>
			<?php 
			$atts = array();
			$atts['class'] = 'big_input';
			$form->input('txt_get', pn_strip_input(is_isset($data, 'txt_get')), $atts, 1); 
			?>			
		</div>		
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Tip for field "From Account"', 'pn'); ?></span></div>
			<?php 
			$form->editor('helps_give', pn_strip_input(is_isset($data, 'helps_give')), 8, array(), 1, 0); 
			?>	
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Tip for field "Onto Account"', 'pn'); ?></span></div>
			<?php 
			$form->editor('helps_get', pn_strip_input(is_isset($data, 'helps_get')), 8, array(), 1, 0); 
			?>	
		</div>		
	</div>		
<?php		
}

add_filter('pn_currency_addform_post', 'fieldhelps_currency_addform_post');
function fieldhelps_currency_addform_post($array) {
		
	$array['helps_give'] = pn_strip_input(is_param_post_ml('helps_give'));
	$array['helps_get'] = pn_strip_input(is_param_post_ml('helps_get'));	
	$array['txt_give'] = pn_strip_input(is_param_post_ml('txt_give'));
	$array['txt_get'] = pn_strip_input(is_param_post_ml('txt_get'));	
		
	return $array;
}

add_filter('atts_field_account', 'fieldhelps_atts_field_account', 10, 4);
function fieldhelps_atts_field_account($field, $vd, $direction, $side_id) {
	
	if (1 == $side_id) {
		$helps = pn_strip_text(ctv_ml(is_isset($vd, 'helps_give')));
		$label = pn_strip_text(ctv_ml(is_isset($vd, 'txt_give')));
	} else {
		$helps = pn_strip_text(ctv_ml(is_isset($vd, 'helps_get')));
		$label = pn_strip_text(ctv_ml(is_isset($vd, 'txt_get')));
	}	
	
	if (strlen($helps) > 0) {
		$field['tooltip'] = $helps;
	}
	
	if (strlen($label) > 0) {
		$field['label'] = $label;
	}			
	
	return $field;
}

add_filter('pn_cfc_addform', 'fieldhelps_pn_cfc_addform', 10, 2);
function fieldhelps_pn_cfc_addform($options, $db_data) {
		
	$vid = intval(is_isset($db_data, 'vid'));
	$cl1 = '';
	if (1 == $vid) {
		$cl1 = 'pn_hide';			
	}	
		
	$n_options = array();

	$n_options['helps_give'] = array(
		'view' => 'textarea',
		'title' => __('Tip for field "From Account"', 'pn'),
		'default' => is_isset($db_data, 'helps_give'),
		'name' => 'helps_give',
		'rows' => '8',
		'ml' => 1,
		'class' => 'thevib thevib0 thevib2 ' . $cl1
	);	
	$n_options['helps_get'] = array(
		'view' => 'textarea',
		'title' => __('Tip for field "Onto Account"', 'pn'),
		'default' => is_isset($db_data, 'helps_get'),
		'name' => 'helps_get',
		'rows' => '8',
		'ml' => 1,
		'class' => 'thevib thevib0 thevib2 ' . $cl1
	);

	$options = pn_array_insert($options, 'cf_req', $n_options);	
		
	return $options;
}

add_filter('pn_cfc_addform_post', 'fieldhelps_cfc_addform_post');
function fieldhelps_cfc_addform_post($array) {
		
	$array['helps_give'] = pn_strip_input(is_param_post_ml('helps_give'));
	$array['helps_get'] = pn_strip_input(is_param_post_ml('helps_get'));		
		
	return $array;
}

add_filter('atts_field_cfc', 'fieldhelps_atts_field_cfc', 10, 3);
function fieldhelps_atts_field_cfc($field, $data, $side_id) {
	
	if (1 == $side_id) {
		$helps = pn_strip_input(ctv_ml(is_isset($data, 'helps_give')));
	} else {
		$helps = pn_strip_input(ctv_ml(is_isset($data, 'helps_get')));
	}	
	
	if (strlen($helps) > 0) {
		$field['tooltip'] = $helps;
	}			
	
	return $field;
}

add_filter('pn_cf_addform', 'fieldhelps_pn_cf_addform', 10, 2);
function fieldhelps_pn_cf_addform($options, $db_data) {
		
	$vid = intval(is_isset($db_data, 'vid'));
	$cl1 = '';
	if (1 == $vid) {
		$cl1 = 'pn_hide';		
	}
		
	$n_options = array();

	$n_options['helps'] = array(
		'view' => 'textarea',
		'title' => __('Fill-in tips', 'pn'),
		'default' => is_isset($db_data, 'helps'),
		'name' => 'helps',
		'rows' => '8',
		'ml' => 1,
		'class' => 'thevib thevib0 thevib2 ' . $cl1
	);

	$options = pn_array_insert($options, 'cf_req', $n_options);	
		
	return $options;
}

add_filter('pn_cf_addform_post', 'fieldhelps_cf_addform_post');
function fieldhelps_cf_addform_post($array) {
	
	$array['helps'] = pn_strip_input(is_param_post_ml('helps'));
	
	return $array;
}	

add_filter('atts_field_cf', 'fieldhelps_atts_field_cf', 10, 2);
function fieldhelps_atts_field_cf($field, $data) {
	
	$helps = pn_strip_input(ctv_ml(is_isset($data, 'helps')));	
	if (strlen($helps) > 0) {
		$field['tooltip'] = $helps;
	}			
	
	return $field;
}

add_action('premium_js', 'premium_js_exchange_tooltip'); 
function premium_js_exchange_tooltip() {
?>	
jQuery(function($) {	
		
	$(document).on('focusin', '.js_window_wrap',function() {
		$(this).addClass('showed');
	});	
	
	$(document).on('focusout', '.js_window_wrap',function() {
		$(this).removeClass('showed');
	});	
		
});
<?php
}