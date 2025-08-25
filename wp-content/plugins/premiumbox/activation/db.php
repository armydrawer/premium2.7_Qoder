<?php
if (!defined('ABSPATH')) { exit(); }		
	
global $wpdb;
$prefix = $wpdb->prefix;
$charset = $wpdb->charset;

	/*
	payment systems

	psys_title - значение
	psys_logo - логотип
	*/
	$table_name = $wpdb->prefix . "psys";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`edit_date` datetime NOT NULL,
		`auto_status` int(1) NOT NULL default '1',
		`edit_user_id` bigint(20) NOT NULL default '0',		
		`psys_title` longtext NOT NULL,
		`psys_logo` longtext NOT NULL,
		`t2_1` bigint(20) NOT NULL default '0',
		`t2_2` bigint(20) NOT NULL default '0',		
		PRIMARY KEY (`id`),
		INDEX (`auto_status`),
		INDEX (`create_date`),
		INDEX (`edit_date`),
		INDEX (`edit_user_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;";
	$wpdb->query($sql);
	/* end payment systems */	 
	
	/*
	currency_codes

	currency_code_title - значение
	internal_rate - внутренний курс за 1 доллар
	*/
	$table_name = $wpdb->prefix ."currency_codes";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`edit_date` datetime NOT NULL,
		`auto_status` int(1) NOT NULL default '1',
		`edit_user_id` bigint(20) NOT NULL default '0',	
		`currency_code_title` longtext NOT NULL,
		`internal_rate` longtext NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`auto_status`),
		INDEX (`create_date`),
		INDEX (`edit_date`),
		INDEX (`edit_user_id`)		
	) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;";
	$wpdb->query($sql);
	/* end currency_codes */

/*
currency

currency_logo - логотип валюты 
psys_logo - лого платежки 
psys_id - id ПС 
psys_title - название ПС 
currency_code_id - id кода валюты 
currency_code_title - название кода валюты 
currency_decimal - знаков после запятой
currency_status - активность валюты (1 - активна, 0 - не активна)
currency_reserv - резерв (автосумма)
reserv_place - откуда брать резерв (0-считать)
xml_value - значение для XML
show_give - выводить при отдаете 
show_get - выводить при получаете 
*/
	$table_name = $wpdb->prefix . "currency";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`edit_date` datetime NOT NULL,
		`auto_status` int(1) NOT NULL default '1',
		`edit_user_id` bigint(20) NOT NULL default '0',
		`currency_logo` longtext NOT NULL,
		`psys_logo` longtext NOT NULL,
		`psys_id` bigint(20) NOT NULL default '0',
		`psys_title` longtext NOT NULL,		
		`currency_code_id` bigint(20) NOT NULL default '0',
		`currency_code_title` longtext NOT NULL,		
		`cat_id` bigint(20) NOT NULL default '0',
		`currency_decimal` int(2) NOT NULL default '8',
		`show_give` int(2) NOT NULL default '1',
		`show_get` int(2) NOT NULL default '1',		
		`reserv_order` bigint(20) NOT NULL default '0',
		`t1_1` bigint(20) NOT NULL default '0',
		`t1_2` bigint(20) NOT NULL default '0',
		`currency_reserv` varchar(50) NOT NULL default '0',
		`currency_status` int(1) NOT NULL default '1',
		`reserv_place` varchar(150) NOT NULL default '0',
		`xml_value` varchar(250) NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`auto_status`),
		INDEX (`xml_value`),
		INDEX (`currency_status`),
		INDEX (`psys_id`),
		INDEX (`currency_code_id`),
		INDEX (`cat_id`),
		INDEX (`reserv_order`),
		INDEX (`t1_1`),
		INDEX (`t1_2`),
		INDEX (`create_date`),
		INDEX (`edit_date`),
		INDEX (`edit_user_id`)		
	) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;";
	$wpdb->query($sql);		
	
	$table_name = $wpdb->prefix . "currency_meta";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`item_id` bigint(20) NOT NULL default '0',
		`meta_key` longtext NOT NULL,
		`meta_value` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`item_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;";
	$wpdb->query($sql);
/* end currency */	
	
/*
Дополнительные поля валют

tech_name - техническое название
cf_name - название
vid - 0 текст, 1- select, 2 - textarea
cf_req - 0-не обязательно, 1-обязательно
datas - если селект, то массив выборки
uniqueid - идентификатор для автовыплат и прочего
*/	
	$table_name = $wpdb->prefix . "currency_custom_fields";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`edit_date` datetime NOT NULL,
		`auto_status` int(1) NOT NULL default '1',
		`edit_user_id` bigint(20) NOT NULL default '0',		
		`tech_name` longtext NOT NULL,
		`cf_name` longtext NOT NULL,
		`uniqueid` varchar(250) NOT NULL,
		`vid` int(1) NOT NULL default '0',
		`cf_req` int(1) NOT NULL default '0',
		`datas` longtext NOT NULL,
		`cf_order_give` bigint(20) NOT NULL default '0',
		`cf_order_get` bigint(20) NOT NULL default '0',		
		`status` int(2) NOT NULL default '1',
		PRIMARY KEY (`id`),
		INDEX (`auto_status`),
		INDEX (`status`),
		INDEX (`vid`),
		INDEX (`cf_order_give`),
		INDEX (`cf_order_get`),
		INDEX (`create_date`),
		INDEX (`edit_date`),
		INDEX (`edit_user_id`)		
	) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;";
	$wpdb->query($sql);
	
	$table_name = $wpdb->prefix . "cf_currency";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`currency_id` bigint(20) NOT NULL default '0',
		`cf_id` bigint(20) NOT NULL default '0',
		`place_id` bigint(20) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`currency_id`),
		INDEX (`cf_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;";
	$wpdb->query($sql);			
	
/*
Дополнительные поля направлений

tech_name - техническое название
cf_name - название
vid - 0 текст, 1- select
cf_req - 0-не обязательно, 1-обязательно
datas - если селект, то массив выборки
*/	
	$table_name = $wpdb->prefix . "direction_custom_fields";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`edit_date` datetime NOT NULL,
		`auto_status` int(1) NOT NULL default '1',
		`edit_user_id` bigint(20) NOT NULL default '0',
		`tech_name` longtext NOT NULL,
		`cf_name` longtext NOT NULL,
		`vid` int(1) NOT NULL default '0',
		`cf_req` int(1) NOT NULL default '0',
		`uniqueid` varchar(250) NOT NULL,
		`cf_auto` varchar(250) NOT NULL,
		`datas` longtext NOT NULL,
		`status` int(2) NOT NULL default '1',
		`cf_order` bigint(20) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`auto_status`),
		INDEX (`status`),
		INDEX (`cf_order`),
		INDEX (`create_date`),
		INDEX (`edit_date`),
		INDEX (`edit_user_id`)		
	) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;";
	$wpdb->query($sql);		
	
	$table_name = $wpdb->prefix . "cf_directions";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`direction_id` bigint(20) NOT NULL default '0',
		`cf_id` bigint(20) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`direction_id`),
		INDEX (`cf_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;";
	$wpdb->query($sql);

/*
directions
*/
	$table_name = $wpdb->prefix . "directions";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`edit_date` datetime NOT NULL,
		`auto_status` int(1) NOT NULL default '1',
		`edit_user_id` bigint(20) NOT NULL default '0',
		`direction_status` int(2) NOT NULL default '1',
		`direction_name` varchar(350) NOT NULL,
		`tech_name` longtext NOT NULL,
		`currency_id_give` bigint(20) NOT NULL default '0',
		`currency_id_get` bigint(20) NOT NULL default '0',
		`psys_id_give` bigint(20) NOT NULL default '0',
		`psys_id_get` bigint(20) NOT NULL default '0',
		`course_give` varchar(50) NOT NULL default '0',
		`course_get` varchar(50) NOT NULL default '0',		
		`enable_user_discount` int(1) NOT NULL default '1',
		`max_user_discount` varchar(5) NOT NULL default '50',
		`min_sum1` varchar(250) NOT NULL default '0',
		`min_sum2` varchar(250) NOT NULL default '0',
		`max_sum1` varchar(250) NOT NULL default '0',
		`max_sum2` varchar(250) NOT NULL default '0',
		`com_sum1` varchar(50) NOT NULL default '0',
		`com_sum2` varchar(50) NOT NULL default '0',		
		`com_pers1` varchar(20) NOT NULL default '0',
		`com_pers2` varchar(20) NOT NULL default '0',
		`pay_com1` int(1) NOT NULL default '0',
		`pay_com2` int(1) NOT NULL default '0',
		`dcom1` int(1) NOT NULL default '0',
		`dcom2` int(1) NOT NULL default '0',
		`nscom1` int(1) NOT NULL default '0',
		`nscom2` int(1) NOT NULL default '0',
		`com_det1` int(1) NOT NULL default '0',
		`com_det2` int(1) NOT NULL default '0', 
		`minsum1com` varchar(50) NOT NULL default '0',  
		`minsum2com` varchar(50) NOT NULL default '0',
		`com_box_sum1` varchar(250) NOT NULL default '0',
		`com_box_pers1` varchar(250) NOT NULL default '0',
		`com_box_min1` varchar(250) NOT NULL default '0',
		`com_box_ns1` int(1) NOT NULL default '0',
		`com_box_det1` int(1) NOT NULL default '0',		
		`com_box_sum2` varchar(250) NOT NULL default '0',
		`com_box_pers2` varchar(250) NOT NULL default '0',
		`com_box_min2` varchar(250) NOT NULL default '0',
		`com_box_ns2` int(1) NOT NULL default '0',
		`com_box_det2` int(1) NOT NULL default '0',
		`profit_sum1` varchar(50) NOT NULL default '0',
		`profit_sum2` varchar(50) NOT NULL default '0',
		`profit_pers1` varchar(20) NOT NULL default '0',
		`profit_pers2` varchar(20) NOT NULL default '0',
		`m_in` longtext NOT NULL,
		`m_out` longtext NOT NULL,		
		`to3_1` bigint(20) NOT NULL default '0',
		`site_order1` bigint(20) NOT NULL default '0',
		`mailtemp` longtext NOT NULL,
		`maildata` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`auto_status`),
		INDEX (`currency_id_give`),
		INDEX (`currency_id_get`),
		INDEX (`psys_id_give`),
		INDEX (`psys_id_get`),
		INDEX (`site_order1`),
		INDEX (`to3_1`),
		INDEX (`direction_status`),
		INDEX (`direction_name`),
		INDEX (`create_date`),
		INDEX (`edit_date`),
		INDEX (`edit_user_id`)		
	) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;";
	$wpdb->query($sql);																				
	
	$table_name = $wpdb->prefix . "directions_meta";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`item_id` bigint(20) NOT NULL default '0',
		`meta_key` longtext NOT NULL,
		`meta_value` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`item_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;";
	$wpdb->query($sql);

/*
транзакции резерва

trans_title - название транзакции
trans_sum - сумма
currency_id - id валюты
currency_code_id - id типа валюты
currency_code_title - название типа валюты
*/
	$table_name = $wpdb->prefix . "currency_reserv";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`edit_date` datetime NOT NULL,
		`auto_status` int(1) NOT NULL default '1',
		`edit_user_id` bigint(20) NOT NULL default '0',		
		`trans_title` longtext NOT NULL,
		`trans_sum` varchar(50) NOT NULL default '0',
		`currency_id` bigint(20) NOT NULL default '0',
		`currency_code_id` bigint(20) NOT NULL default '0',
		`currency_code_title` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`auto_status`),
		INDEX (`currency_id`),
		INDEX (`currency_code_id`),
		INDEX (`create_date`),
		INDEX (`edit_date`),
		INDEX (`edit_user_id`)		
	) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;";
	$wpdb->query($sql);

	$table_name = $wpdb->prefix . "exchange_bids";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`create_date` datetime NOT NULL,
		`edit_date` datetime NOT NULL,
		`status` varchar(35) NOT NULL,
		`bid_locale` varchar(10) NOT NULL,
		`currency_id_give` bigint(20) NOT NULL default '0', 
		`currency_id_get` bigint(20) NOT NULL default '0',	
		`currency_code_give` varchar(35) NOT NULL, 
		`currency_code_get` varchar(35) NOT NULL, 
		`currency_code_id_give` bigint(20) NOT NULL default '0', 
		`currency_code_id_get` bigint(20) NOT NULL default '0',
		`psys_give` longtext NOT NULL, 
		`psys_get` longtext NOT NULL,
		`psys_id_give` bigint(20) NOT NULL default '0', 
		`psys_id_get` bigint(20) NOT NULL default '0',		
		`hashed` varchar(35) NOT NULL,
		`direction_id` bigint(20) NOT NULL default '0',
		`user_id` bigint(20) NOT NULL default '0',
		`user_login` varchar(150) NOT NULL,
		`user_ip` varchar(150) NOT NULL,
		`user_agent` varchar(250) NOT NULL,
		`metas` longtext NOT NULL,
		`dmetas` longtext NOT NULL,
		`unmetas` longtext NOT NULL,
		`account_give` varchar(250) NOT NULL,
		`account_get` varchar(250) NOT NULL,		
		`pay_ac` varchar(250) NOT NULL,
		`pay_sum` varchar(50) NOT NULL default '0',	
		`out_sum` varchar(50) NOT NULL default '0',	
		`user_discount` varchar(10) NOT NULL default '0',
		`user_discount_sum` varchar(50) NOT NULL default '0',		
		`first_name` varchar(150) NOT NULL,
		`last_name` varchar(150) NOT NULL,
		`second_name` varchar(150) NOT NULL,
		`user_phone` varchar(150) NOT NULL,
		`user_skype` varchar(150) NOT NULL,
		`user_telegram` varchar(150) NOT NULL,
		`user_email` varchar(150) NOT NULL,
		`user_passport` varchar(250) NOT NULL,
		`to_account` varchar(250) NOT NULL, 
		`from_account` varchar(250) NOT NULL,
		`dest_tag` varchar(250) NOT NULL,	
		`trans_in` varchar(250) NOT NULL,
		`trans_out` varchar(250) NOT NULL,
		`txid_in` varchar(250) NOT NULL,
		`txid_out` varchar(250) NOT NULL,		
		`user_hash` varchar(150) NOT NULL,	
		`hashdata` longtext NOT NULL,
		`exceed_pay` int(1) NOT NULL default '0',
		`touap_date` datetime NOT NULL,		
		`course_give` varchar(50) NOT NULL default '0', 
		`course_get` varchar(50) NOT NULL default '0',		
		`exsum` varchar(50) NOT NULL default '0',
		`sum1dc` varchar(50) NOT NULL default '0',
		`sum2c` varchar(50) NOT NULL default '0',
		`profit` varchar(50) NOT NULL default '0',		
		`sum1` varchar(50) NOT NULL default '0', 
		`dop_com1` varchar(50) NOT NULL default '0',
		`com_ps1` varchar(50) NOT NULL default '0',
		`com_ps2` varchar(50) NOT NULL default '0',
		`sum1c` varchar(50) NOT NULL default '0', 
		`sum1r` varchar(50) NOT NULL default '0',
		`sum2t` varchar(50) NOT NULL default '0',
		`sum2` varchar(50) NOT NULL default '0', 
		`dop_com2` varchar(50) NOT NULL default '0',
		`sum2dc` varchar(50) NOT NULL default '0',
		`sum2r` varchar(50) NOT NULL default '0',
		`m_in` varchar(150) NOT NULL default '0',
		`m_out` varchar(150) NOT NULL default '0',		
		PRIMARY KEY (`id`),
		INDEX (`status`),
		INDEX (`direction_id`),
		INDEX (`currency_id_give`),
		INDEX (`currency_id_get`),
		INDEX (`currency_code_id_give`),
		INDEX (`currency_code_id_get`),
		INDEX (`psys_id_give`),
		INDEX (`psys_id_get`),
		INDEX (`m_in`),
		INDEX (`m_out`),
		INDEX (`user_id`),
		INDEX (`hashed`),
		INDEX (`create_date`),
		INDEX (`edit_date`)		
	) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;";
	$wpdb->query($sql); 			
	
	$table_name = $wpdb->prefix . "bids_meta";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`item_id` bigint(20) NOT NULL default '0',
		`meta_key` longtext NOT NULL,
		`meta_value` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`item_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;";
	$wpdb->query($sql);					 