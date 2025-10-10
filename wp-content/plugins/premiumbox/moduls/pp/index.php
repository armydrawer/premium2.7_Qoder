<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Affiliate program[:en_US][ru_RU:]Партнерская программа[:ru_RU]
description: [en_US:]Affiliate program[:en_US][ru_RU:]Партнерская программа[:ru_RU]
version: 2.7.3
category: [en_US:]Users[:en_US][ru_RU:]Пользователи[:ru_RU]
cat: user 
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_pp');
add_action('pn_plugin_activate', 'bd_all_moduls_active_pp');
function bd_all_moduls_active_pp() {
	global $wpdb;	
	
    $query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'ref_id'");
    if (0 == $query) {
        $wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `ref_id` bigint(20) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'partner_pers'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `partner_pers` varchar(50) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'max_partner_pers'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `max_partner_pers` varchar(50) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'partner_type'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `partner_type` varchar(50) NOT NULL default '0'");
    }	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'p_payout'"); 
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `p_payout` int(2) NOT NULL default '1'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'payout_com'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `payout_com` varchar(50) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'ref_id'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `ref_id` bigint(20) NOT NULL default '0'");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'partner_sum'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `partner_sum` varchar(50) NOT NULL default '0'");
    }	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'partner_pers'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `partner_pers` varchar(50) NOT NULL default '0'");
    }	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'pcalc'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `pcalc` int(1) NOT NULL default '0'");
    }	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'pcalc_txt'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `pcalc_txt` longtext NOT NULL");
    }	
	
	$table_name = $wpdb->prefix . "partner_pers";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
        `sumec` varchar(50) NOT NULL default '0',
		`pers` varchar(50) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`sumec`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);		

	$table_name = $wpdb->prefix . "plinks";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`user_id` bigint(20) NOT NULL default '0',
		`user_login` varchar(250) NOT NULL,
		`pdate` datetime NOT NULL,
		`pbrowser` longtext NOT NULL,
		`pip` longtext NOT NULL,
		`prefer` longtext NOT NULL,
		`user_hash` varchar(150) NOT NULL,
		`query_string` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`user_id`),
		INDEX (`pdate`),
		INDEX (`user_hash`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "plinks LIKE 'user_hash'"); /* 2.1 */
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "plinks ADD `user_hash` varchar(150) NOT NULL");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "plinks LIKE 'query_string'"); /* 2.1 */
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "plinks ADD `query_string` longtext NOT NULL");
    }	

/*
0 - в ожидании
1 - выполнена
2 - отказано
3 - отменена пользователем
*/
	$table_name = $wpdb->prefix . "user_payouts";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`create_date` datetime NOT NULL,
		`edit_date` datetime NOT NULL,
		`auto_status` int(1) NOT NULL default '1',
		`edit_user_id` bigint(20) NOT NULL default '0',	
		`pay_date` datetime NOT NULL,
		`user_id` bigint(20) NOT NULL default '0',
		`user_login` varchar(250) NOT NULL,
		`pay_sum` varchar(250) NOT NULL default '0',
		`pay_sum_or` varchar(250) NOT NULL default '0',
		`currency_id` bigint(20) NOT NULL default '0',
		`psys_title` longtext NOT NULL,
		`currency_code_id` bigint(20) NOT NULL default '0',
		`currency_code_title` varchar(250) NOT NULL,
		`pay_account` varchar(250) NOT NULL,
		`status` int(1) NOT NULL default '0',
		`comment` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`create_date`),
		INDEX (`edit_date`),
		INDEX (`auto_status`),
		INDEX (`edit_user_id`),
		INDEX (`pay_date`),
		INDEX (`user_id`),
		INDEX (`currency_id`),
		INDEX (`currency_code_id`),
		INDEX (`status`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "user_payouts LIKE 'create_date'"); /* 2.0 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "user_payouts ADD `create_date` datetime NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "user_payouts LIKE 'edit_date'"); /* 2.0  */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "user_payouts ADD `edit_date` datetime NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "user_payouts LIKE 'auto_status'"); /* 2.0  */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "user_payouts ADD `auto_status` int(1) NOT NULL default '1'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "user_payouts LIKE 'edit_user_id'"); /* 2.0  */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "user_payouts ADD `edit_user_id` bigint(20) NOT NULL default '0'");
	}	
	
}

add_filter('pn_tech_pages', 'list_tech_pages_pp');
function list_tech_pages_pp($pages) {
	 
	$pages[] = array(
		'post_name'      => 'paccount',
		'post_title'     => '[en_US:]Affiliate account[:en_US][ru_RU:]Партнёрский аккаунт[:ru_RU]',
		'post_content'   => '[paccount_page]',
		'post_template'   => 'pn-pluginpage.php',
	);	
	$pages[] = array(
		'post_name'      => 'promotional',
		'post_title'     => '[en_US:]Promotional materials[:en_US][ru_RU:]Рекламные материалы[:ru_RU]',
		'post_content'   => '[promotional_page]',
		'post_template'   => 'pn-pluginpage.php',
	);	
	$pages[] = array(
		'post_name'      => 'pexch',
		'post_title'     => '[en_US:]Affiliate exchanges[:en_US][ru_RU:]Партнёрские обмены[:ru_RU]',
		'post_content'   => '[pexch_page]',
		'post_template'   => 'pn-pluginpage.php',
	);	
	$pages[] = array(
		'post_name'      => 'plinks',
		'post_title'     => '[en_US:]Affiliate transitions[:en_US][ru_RU:]Партнёрские переходы[:ru_RU]',
		'post_content'   => '[plinks_page]',
		'post_template'   => 'pn-pluginpage.php',
	);	
	$pages[] = array(
		'post_name'      => 'preferals',
		'post_title'     => '[en_US:]Referrals[:en_US][ru_RU:]Рефералы[:ru_RU]',
		'post_content'   => '[preferals_page]',
		'post_template'   => 'pn-pluginpage.php',
	);
	$pages[] = array(
		'post_name'      => 'payouts',
		'post_title'     => '[en_US:]Affiliate payouts[:en_US][ru_RU:]Вывод партнёрских средств[:ru_RU]',
		'post_content'   => '[payouts_page]',
		'post_template'   => 'pn-pluginpage.php',
	);
	$pages[] = array(
		'post_name' => 'partnersfaq',
		'post_title' => '[en_US:]Affiliate FAQ[:en_US][ru_RU:]Партнёрский FAQ[:ru_RU]',
		'post_content' => '',
		'post_template' => '',
	);
	$pages[] = array(
		'post_name' => 'terms',
		'post_title' => '[en_US:]Affiliate terms and conditions[:en_US][ru_RU:]Условия участия в партнерской программе[:ru_RU]',
		'post_content' => '',
		'post_template' => '',
	);		
		
	return $pages;
}

add_filter('pn_caps', 'pp_caps');
function pp_caps($pn_caps) {
	
	$pn_caps['pn_pp'] = __('Use affiliate program', 'pn');
	$pn_caps['pn_pp_bids'] = __('Editing the amount of the partner reward', 'pn');
	
	return $pn_caps;
}

add_filter('list_admin_notify', 'list_admin_notify_payout');
function list_admin_notify_payout($places_admin) {
	
	$places_admin['payout'] = __('Affiliate reward payout request', 'pn');
	
	return $places_admin;
}

add_filter('list_user_notify', 'list_user_notify_partprofit');
function list_user_notify_partprofit($places_admin) {
	
	$places_admin['partprofit'] = __('Affiliate reward charging', 'pn');
	
	return $places_admin;
}

add_filter('list_notify_tags_payout', 'def_mailtemp_tags_payout');
function def_mailtemp_tags_payout($tags) {
	
	$tags['user'] = array(
		'title' => __('User', 'pn'),
		'start' => '[user]',
	);
	$tags['sum'] = array(
		'title' => __('Amount', 'pn'),
		'start' => '[sum]',
	);
	$tags['ctype'] = array(
		'title' => __('Currency code', 'pn'),
		'start' => '[ctype]',
	);	
	
	return $tags;
}

add_filter('list_notify_tags_partprofit', 'def_mailtemp_tags_partprofit');
function def_mailtemp_tags_partprofit($tags) {
	
	$tags['sum'] = array(
		'title' => __('Amount', 'pn'),
		'start' => '[sum]',
	);
	$tags['ctype'] = array(
		'title' => __('Currency code', 'pn'),
		'start' => '[ctype]',
	);	
	
	return $tags;
}

add_action('admin_menu', 'admin_menu_pp', 2000);
function admin_menu_pp() {
	global $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_pp')) {
		add_menu_page(__('Affiliate program', 'pn'), __('Affiliate program', 'pn'), 'read', 'pn_pp', array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('pp'));  
		add_submenu_page("pn_pp", __('Statistics', 'pn'), __('Statistics', 'pn'), 'read', "pn_pp", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_pp", __('Partnership exchanges', 'pn'), __('Partnership exchanges', 'pn'), 'read', "pn_pexch", array($premiumbox, 'admin_temp'));		
		add_submenu_page("pn_pp", __('Transitions', 'pn'), __('Transitions', 'pn'), 'read', "pn_plinks", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_pp", __('Referrals', 'pn'), __('Referrals', 'pn'), 'read', "pn_preferals", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_pp", __('Royalties', 'pn'), __('Royalties', 'pn'), 'read', "pn_partnpers", array($premiumbox, 'admin_temp'));	
		add_submenu_page("pn_pp", __('Add reward', 'pn'), __('Add reward', 'pn'), 'read', "pn_add_partnpers", array($premiumbox, 'admin_temp'));		
		add_submenu_page("pn_pp", __('Payouts', 'pn'), __('Payouts', 'pn'), 'read', "pn_payouts", array($premiumbox, 'admin_temp'));	
		add_submenu_page("pn_pp", __('Add payout', 'pn'), __('Add payout', 'pn'), 'read', "pn_add_payouts", array($premiumbox, 'admin_temp'));		
		add_submenu_page("pn_pp", __('Banners', 'pn'), __('Banners', 'pn'), 'read', "pn_pbanners", array($premiumbox, 'admin_temp'));	
		add_submenu_page("pn_pp", __('Settings', 'pn'), __('Settings', 'pn'), 'read', "pn_psettings", array($premiumbox, 'admin_temp'));
	}
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'settings');
$premiumbox->include_path(__FILE__, 'users');
$premiumbox->include_path(__FILE__, 'stats');
$premiumbox->include_path(__FILE__, 'cron');
$premiumbox->include_path(__FILE__, 'referals');
$premiumbox->include_path(__FILE__, 'plinks');
$premiumbox->include_path(__FILE__, 'partnpers');
$premiumbox->include_path(__FILE__, 'add_partnpers');
$premiumbox->include_path(__FILE__, 'banners');
$premiumbox->include_path(__FILE__, 'payouts');
$premiumbox->include_path(__FILE__, 'add_payouts');
$premiumbox->include_path(__FILE__, 'exch');
$premiumbox->include_path(__FILE__, 'filters');
$premiumbox->include_path(__FILE__, 'files');

$premiumbox->include_path(__FILE__, 'display/referals');
$premiumbox->include_path(__FILE__, 'display/plinks');
$premiumbox->include_path(__FILE__, 'display/promotional');
$premiumbox->include_path(__FILE__, 'display/pexch');
$premiumbox->include_path(__FILE__, 'display/paccount');
$premiumbox->include_path(__FILE__, 'display/payouts');