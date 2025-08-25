<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Exchange filter for guests[:en_US][ru_RU:]Фильтр обмена для гостей[:ru_RU]
description: [en_US:]Exchange filter for users who make the exchange without registering on the website[:en_US][ru_RU:]Фильтр обмена для пользователей которые совершают обмен без регистрации на сайте[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_napsguest');
add_action('pn_plugin_activate', 'bd_all_moduls_active_napsguest');
function bd_all_moduls_active_napsguest() {
	global $wpdb;	
	
	/* hidegost - статус гостей (0 - не скрывать, 1 - запретить) */
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'hidegost'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `hidegost` int(1) NOT NULL default '0'");
    } else {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions CHANGE `hidegost` `hidegost` int(1) NOT NULL default '0'");
	}
	
}

add_action('tab_direction_tab7', 'napsguest_tab_direction_tab', 1, 2);
function napsguest_tab_direction_tab($data, $data_id) {
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange directions availability for guests', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$hidegost = intval(is_isset($data, 'hidegost')); 
				?>														
				<select name="hidegost" autocomplete="off"> 
					<option value="0" <?php selected($hidegost, 0); ?>><?php _e('not to hide', 'pn'); ?></option>
					<option value="1" <?php selected($hidegost, 1); ?>><?php _e('apply exchange filters', 'pn'); ?></option>
				</select>
			</div>
		</div>
	</div>		
	<?php 		
}

add_filter('pn_direction_addform_post', 'napsguest_pn_direction_addform_post');
function napsguest_pn_direction_addform_post($array) {
	
	$array['hidegost'] = intval(is_param_post('hidegost'));
	
	return $array;
}

add_action('pn_config_option', 'napsguest_pn_config_option');
function napsguest_pn_config_option($options){
	global $premiumbox;	

	$options[] = array(
		'view' => 'select',
		'title' => __('Hide exchange directions from guests', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'gostnaphide'),
		'name' => 'gostnaphide',
	);		
	
	return $options;
}

add_action('pn_config_option_post', 'napsguest_pn_config_option_post');
function napsguest_pn_config_option_post() {
	global $premiumbox;	

	$val = intval(is_param_post('gostnaphide'));
	$premiumbox->update_option('exchange', 'gostnaphide', $val);
	
}

add_action('set_exchange_filters', 'naps_guest_set_exchange_filters');
function naps_guest_set_exchange_filters($lists) {
	
	$lists[] = array(
		'title' => __('Filtering guest users', 'pn'),
		'name' => 'napsguest',
		'none' => 'api',
	);
	
	return $lists;
}

add_filter('get_directions_where', 'napsguest_get_directions_where', 10, 2);
function napsguest_get_directions_where($where, $place) {
	global $premiumbox;

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	if ($user_id < 1) {
		$ind = $premiumbox->get_option('exf_' . $place . '_napsguest');
		if (1 == $ind) {
			$where .= "AND hidegost = '0' ";
		}
	}

	return $where;
}

add_filter('pn_exchanges_output', 'napsguest_exchanges_output', 10, 2);
function napsguest_exchanges_output($show_data, $place) {
	global $premiumbox;

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	if ($user_id < 1 and isset($show_data['work']) and 1 == $show_data['work']) {
		$ind = $premiumbox->get_option('exchange', 'gostnaphide');
		if (1 == $ind) {
			$show_data['work'] = 0;
			$show_data['show'] = 0;
			$show_data['text'] = sprintf(__('Exchange directions are available for authorized users only, <a href="%1s" class="js_window_login">sign in</a> or <a href="%2s" class="js_window_join">sign up</a>', 'pn'), $premiumbox->get_page('login'), $premiumbox->get_page('register'));
		}	
	}

	return $show_data;
}

add_filter('error_bids', 'error_bids_napsguest', 300, 2);  
function error_bids_napsguest($error_bids, $direction) { 
	global $premiumbox;	
	
	$user_id = intval(is_isset($error_bids['bid'],'user_id'));
	if (!_is('is_api')) {	
		if (1 == $direction->hidegost and !$user_id) {
			$error_bids['error_text'][] = sprintf(__('Error! Direction is available to authorized users only, <a href="%1s" class="js_window_login">sign in</a> or <a href="%2s" class="js_window_join">sign up</a>', 'pn'), $premiumbox->get_page('login'), $premiumbox->get_page('register'));		
		}
	}
	
	return $error_bids;
}