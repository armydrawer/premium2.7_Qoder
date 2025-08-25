<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]User accounts[:en_US][ru_RU:]Счета пользователей[:ru_RU]
description: [en_US:]User accounts[:en_US][ru_RU:]Счета пользователей[:ru_RU]
version: 2.7.0
category: [en_US:]Users[:en_US][ru_RU:]Пользователи[:ru_RU]
cat: user
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_filter('pn_caps', 'userwallets_pn_caps');
function userwallets_pn_caps($caps) {
	
	$caps['pn_userwallets'] = __('Work with user accounts', 'pn');
	
	return $caps;
}

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_userwallets');
add_action('pn_plugin_activate', 'bd_all_moduls_active_userwallets');
function bd_all_moduls_active_userwallets() {
	global $wpdb;

	$table_name = $wpdb->prefix . "user_wallets";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`edit_date` datetime NOT NULL,
		`auto_status` int(1) NOT NULL default '1',
		`edit_user_id` bigint(20) NOT NULL default '0',	
		`user_id` bigint(20) NOT NULL default '0',	
		`user_login` varchar(250) NOT NULL,
		`currency_id` bigint(20) NOT NULL default '0',
		`accountnum` longtext NOT NULL,
		`verify` int(1) NOT NULL default '0',
		`vidzn` int(5) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`create_date`),
		INDEX (`edit_date`),
		INDEX (`auto_status`),
		INDEX (`edit_user_id`),
		INDEX (`user_id`),
		INDEX (`currency_id`),
		INDEX (`verify`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "user_wallets LIKE 'create_date'"); /* 2.0 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "user_wallets ADD `create_date` datetime NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "user_wallets LIKE 'edit_date'"); /* 2.0  */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "user_wallets ADD `edit_date` datetime NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "user_wallets LIKE 'auto_status'"); /* 2.0  */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "user_wallets ADD `auto_status` int(1) NOT NULL default '1'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "user_wallets LIKE 'edit_user_id'"); /* 2.0  */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "user_wallets ADD `edit_user_id` bigint(20) NOT NULL default '0'");
	}	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'user_wallets'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `user_wallets` int(2) NOT NULL default '1'");
    }		
	
}

add_filter('pn_tech_pages', 'list_tech_pages_userwallets');
function list_tech_pages_userwallets($pages) {
	
	$pages[] = array(
		'post_name'      => 'userwallets',
		'post_title'     => '[en_US:]Your accounts[:en_US][ru_RU:]Ваши счета[:ru_RU]',
		'post_content'   => '[userwallets]',
		'post_template'   => 'pn-pluginpage.php',
	);		
	
	return $pages;
}

add_action('admin_menu', 'admin_menu_userwallets');
function admin_menu_userwallets() {
	global $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_userwallets')) {
		add_menu_page(__('User accounts', 'pn'), __('User accounts', 'pn'), 'read', "pn_userwallets", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('currency_codes'));	
		add_submenu_page("pn_userwallets", __('Add user account', 'pn'), __('Add user account', 'pn'), 'read', "pn_add_userwallets", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_userwallets", __('Settings', 'pn'), __('Settings', 'pn'), 'read', "pn_settings_userwallets", array($premiumbox, 'admin_temp'));
	}
	
}

add_action('item_currency_delete', 'item_currency_delete_userwallets');
function item_currency_delete_userwallets($id) {
	global $wpdb;
	
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE currency_id = '$id'");
	foreach ($items as $item) {
		$item_id = $item->id;
		$res = apply_filters('item_userwallets_delete_before', pn_ind(), $item_id, $item);
		if ($res['ind']) {
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "user_wallets WHERE id = '$item_id'");
			do_action('item_userwallets_delete', $item_id, $item, $result);
		}
	}
	
}

add_action('delete_user', 'delete_user_userwallets');
function delete_user_userwallets($user_id) {
	global $wpdb;
	
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE user_id = '$user_id'");
	foreach ($items as $item) {
		$item_id = $item->id;
		$res = apply_filters('item_userwallets_delete_before', pn_ind(), $item_id, $item);
		if ($res['ind']) {
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "user_wallets WHERE id = '$item_id'");
			do_action('item_userwallets_delete', $item_id, $item, $result);
		}
	}	
	
}

add_action('tab_currency_tab3', 'userwallets_tab_currency_tab3', 9, 2);
function userwallets_tab_currency_tab3($data, $data_id) {
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Allow users to add new wallet in Account section', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="user_wallets" autocomplete="off">
					<?php 
					$user_wallets = intval(is_isset($data, 'user_wallets')); 
					?>	
					<option value="0" <?php selected($user_wallets, 0); ?>><?php _e('No', 'pn'); ?></option>
					<option value="1" <?php selected($user_wallets, 1); ?>><?php _e('Yes', 'pn'); ?></option>
				</select>
			</div>			
		</div>
		<div class="add_tabs_single">
			
		</div>
	</div>
<?php		
} 

add_filter('pn_currency_addform_post', 'pn_currency_addform_post_userwallets');
function pn_currency_addform_post_userwallets($array) {
	
	$array['user_wallets'] = intval(is_param_post('user_wallets'));
	
	return $array;
}

add_filter('error_bids', 'userwallets_error_bids', 950, 4); 
function userwallets_error_bids($error_bids, $direction, $vd1, $vd2) {
	global $wpdb, $premiumbox;	
	
	$created = intval($premiumbox->get_option('usve', 'acc_created'));
	if ($created) {
		$user_id = intval(is_isset($error_bids['bid'], 'user_id'));
		$user_login = is_email(is_isset($error_bids['bid'], 'user_login'));
		if ($user_id) {
		
			$account = pn_strip_input(is_isset($error_bids['bid'], 'account_give'));
			if ($account and function_exists('create_userwallets')) {
				create_userwallets($user_id, $user_login, $vd1->id, $account);
			}
			$account = pn_strip_input(is_isset($error_bids['bid'], 'account_get'));	
			if ($account and function_exists('create_userwallets')) {
				create_userwallets($user_id, $user_login, $vd2->id, $account);
			}

		}
	}	
	
	return $error_bids;
}

add_filter('atts_field_account', 'userwallets_atts_field_account', 10, 3);
function userwallets_atts_field_account($account_field, $vd, $direction) {
	global $wpdb;
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	if ($user_id) {
			
		$currency_id = $vd->id;
		$arr = array(
			'0' => array(
				'title' => __('No wallet', 'pn'),
				'value' => '',
			),
		);
		$purses = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE auto_status = '1' AND user_id = '$user_id' AND currency_id = '$currency_id'");
		foreach ($purses as $ps) {
			$arr[] = array(
				'title' => pn_strip_input($ps->accountnum),
				'value' => pn_strip_input($ps->accountnum),
			);
		}	
	
		if (count($arr) > 1) {
			$account_field['choice'] = $arr;
		}
		
	}
	
	return $account_field;
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'settings');
$premiumbox->include_path(__FILE__, 'add');
$premiumbox->include_path(__FILE__, 'list');
$premiumbox->include_path(__FILE__, 'window');

$premiumbox->auto_include($path . '/shortcode'); 