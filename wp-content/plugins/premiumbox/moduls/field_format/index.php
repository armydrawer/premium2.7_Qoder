<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Allowed symbols[:en_US][ru_RU:]Разрешенные символы[:ru_RU]
description: [en_US:]Allowed symbols[:en_US][ru_RU:]Разрешенные символы[:ru_RU]
version: 2.7.0
category: [en_US:]Currency[:en_US][ru_RU:]Валюты[:ru_RU]
cat: currency
dependent: -
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_fieldformat');
add_action('pn_plugin_activate', 'bd_all_moduls_active_fieldformat');
function bd_all_moduls_active_fieldformat() {
	global $wpdb;
	
	//cifrzn - что используется (0-буквы и цифры, 1-только цифры, 2-только буквы, 3-email, 4-все символы, 5-телефон
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'firstzn'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE ".$wpdb->prefix ."currency ADD `firstzn` varchar(150) NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'cifrzn'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `cifrzn` int(2) NOT NULL default '4'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'backspace'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `backspace` int(1) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'minzn'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `minzn` int(5) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'maxzn'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `maxzn` int(5) NOT NULL default '100'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency_custom_fields LIKE 'firstzn'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency_custom_fields ADD `firstzn` varchar(150) NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency_custom_fields LIKE 'cifrzn'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency_custom_fields ADD `cifrzn` int(2) NOT NULL default '4'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency_custom_fields LIKE 'backspace'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency_custom_fields ADD `backspace` int(1) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency_custom_fields LIKE 'minzn'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency_custom_fields ADD `minzn` int(5) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency_custom_fields LIKE 'maxzn'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency_custom_fields ADD `maxzn` int(5) NOT NULL default '100'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "direction_custom_fields LIKE 'firstzn'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "direction_custom_fields ADD `firstzn` varchar(150) NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "direction_custom_fields LIKE 'cifrzn'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "direction_custom_fields ADD `cifrzn` int(2) NOT NULL default '4'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "direction_custom_fields LIKE 'backspace'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "direction_custom_fields ADD `backspace` int(1) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "direction_custom_fields LIKE 'minzn'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "direction_custom_fields ADD `minzn` int(5) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "direction_custom_fields LIKE 'maxzn'");
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "direction_custom_fields ADD `maxzn` int(5) NOT NULL default '100'");
	}		
	
}

add_filter('pntable_columns_pn_cfc', 'fieldformat_pntable_columns_pn_cf');
add_filter('pntable_columns_pn_cf', 'fieldformat_pntable_columns_pn_cf');
function fieldformat_pntable_columns_pn_cf($columns) {
	
	$n_columns = array();
	$n_columns['minzn'] = __('Min. number of symbols', 'pn');
	$n_columns['maxzn'] = __('Max. number of symbols', 'pn');
	$columns = pn_array_insert($columns, 'site_title', $n_columns);
	
	return $columns;
}

add_filter('pntable_column_pn_cfc', 'fieldformat_pntable_column_pn_cf', 10, 3);
add_filter('pntable_column_pn_cf', 'fieldformat_pntable_column_pn_cf', 10, 3);
function fieldformat_pntable_column_pn_cf($html, $column_name, $item) {
	
	if ('minzn' == $column_name) {
		return intval($item->minzn);
	} elseif ('maxzn' == $column_name) {
		return intval($item->maxzn);	 
	}
	
	return $html;
}

add_action('tab_currency_tab3', 'fieldformat_tab_currency_tab3', 20, 2);
function fieldformat_tab_currency_tab3($data, $data_id) {
	
	$form = new PremiumForm();
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Min. number of symbols', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="minzn" style="width: 100px;" value="<?php echo is_sum(is_isset($data, 'minzn')); ?>" />
			</div>	
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. number of symbols', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="maxzn" style="width: 100px;" value="<?php echo is_sum(is_isset($data, 'maxzn')); ?>" />
			</div>			
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('First symbols', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="firstzn" style="width: 100px;" value="<?php echo pn_strip_input(is_isset($data, 'firstzn')); ?>" />
			</div>
			<?php $form->help(__('More info', 'pn'), __('Checking the first symbols when client enters own account. For example, the first symbol of WebMoney Z wallet is set as Z.', 'pn')); ?>	
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Allowed symbols', 'pn'); ?></span></div>
			<?php $form->select('cifrzn', array('0' => __('Numbers and latin letters', 'pn'), '1' => __('Numbers', 'pn'), '2' => __('Latin letters', 'pn'), '3' => __('E-mail', 'pn'), '5' => __('Phone number', 'pn'), '4' => __('Any symbols', 'pn')), is_isset($data, 'cifrzn'));  ?>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Remove spaces in details', 'pn'); ?></span></div>
			<?php $form->select('backspace', array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')), is_isset($data, 'backspace'));  ?>
		</div>
	</div>	
<?php		
}

add_filter('pn_currency_addform_post', 'fieldformat_currency_addform_post');
function fieldformat_currency_addform_post($array) {
		
	$array['minzn'] = intval(is_param_post('minzn'));
	$array['maxzn'] = intval(is_param_post('maxzn'));
	$array['firstzn'] = pn_strip_input(is_param_post('firstzn'));
	$array['cifrzn'] = intval(is_param_post('cifrzn'));
	$array['backspace'] = intval(is_param_post('backspace'));	
		
	return $array;
}

add_filter('pn_cfc_addform', 'fieldformat_pn_cfc_addform', 10, 2);
add_filter('pn_cf_addform', 'fieldformat_pn_cfc_addform', 10, 2);
function fieldformat_pn_cfc_addform($options, $db_data){
		
	$vid = intval(is_isset($db_data, 'vid'));
	if (0 == $vid) {
		$cl1 = '';
	} else {
		$cl1 = 'pn_hide';			
	}	
		
	$n_options = array();

	$n_options['minzn'] = array(
		'view' => 'input',
		'title' => __('Min. number of symbols', 'pn'),
		'default' => is_isset($db_data, 'minzn'),
		'name' => 'minzn',
		'class' => 'thevib thevib0 ' . $cl1,
	);	
	$n_options['maxzn'] = array(
		'view' => 'input',
		'title' => __('Max. number of symbols', 'pn'),
		'default' => is_isset($db_data, 'maxzn'),
		'name' => 'maxzn',
		'class' => 'thevib thevib0 ' . $cl1,
	);				
	$n_options['firstzn'] = array(
		'view' => 'inputbig',
		'title' => __('First symbols', 'pn'),
		'default' => is_isset($db_data, 'firstzn'),
		'name' => 'firstzn',
		'class' => 'thevib thevib0 ' . $cl1,
	);
	$n_options['firstzn_help'] = array(
		'view' => 'help',
		'title' => __('More info', 'pn'),
		'default' => __('Checking the first symbols while a customer fills out a field.', 'pn'),
		'class' => 'thevib thevib0 ' . $cl1,
	);
	$n_options['cifrzn'] = array(
		'view' => 'select',
		'title' => __('Allowed symbols', 'pn'),
		'options' => array('0' => __('Numbers and latin letters', 'pn'), '1' => __('Numbers', 'pn'), '2' => __('Latin letters', 'pn'), '3' => __('E-mail', 'pn'), '5' => __('Phone number', 'pn'), '4' => __('Any symbols', 'pn')),
		'default' => is_isset($db_data, 'cifrzn'),
		'name' => 'cifrzn',
		'class' => 'thevib thevib0 ' . $cl1,
	);
	$n_options['backspace'] = array(
		'view' => 'select',
		'title' => __('Remove spaces in details', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => is_isset($db_data, 'backspace'),
		'name' => 'backspace',
		'class' => 'thevib thevib0 ' . $cl1,
	);			

	$options = pn_array_insert($options, 'vid', $n_options);
	
	return $options;
}

add_filter('pn_cfc_addform_post', 'fieldformat_cfc_addform_post');
add_filter('pn_cf_addform_post', 'fieldformat_cfc_addform_post');
function fieldformat_cfc_addform_post($array) {
		
	$array['minzn'] = intval(is_param_post('minzn'));
	$array['maxzn'] = intval(is_param_post('maxzn'));
	$array['firstzn'] = pn_strip_input(is_param_post('firstzn'));
	$array['cifrzn'] = intval(is_param_post('cifrzn'));
	$array['backspace'] = intval(is_param_post('backspace'));	
		
	return $array;
}

add_filter('list_export_currency', 'fieldformat_list_export_currency');
function fieldformat_list_export_currency($array) {
	
	$array['minzn'] = __('Min. number of symbols', 'pn');
	$array['maxzn'] = __('Max. number of symbols', 'pn');
	$array['firstzn'] = __('First symbols', 'pn');
	$array['backspace'] = __('Remove spaces in details', 'pn');
	
	return $array;
}

add_filter('export_currency_filter', 'fieldformat_export_currency_filter');
function fieldformat_export_currency_filter($export_currency_filter) {
	
	$export_currency_filter['int_arr'][] = 'minzn';
	$export_currency_filter['int_arr'][] = 'maxzn';
	$export_currency_filter['qw_arr'][] = 'backspace';
	
	return $export_currency_filter;
} 

add_filter('atts_field_cf', 'fieldformat_placeholder_create', 10, 2);
add_filter('atts_field_cfc', 'fieldformat_placeholder_create', 10, 2); 
add_filter('atts_field_userwallet', 'fieldformat_placeholder_create', 10, 2); 
add_filter('atts_field_account', 'fieldformat_placeholder_create', 10, 2);
function fieldformat_placeholder_create($atts, $item) {
	
	$placeholder = create_placeholder($item, __('or', 'pn'));
	if ($placeholder) {
		$atts['placeholder'] = $placeholder;
	}
	
	return $atts;
}

add_filter('get_purse', 'fieldformat_get_purse', 10, 3);