<?php
if (!defined('ABSPATH')) { exit(); }	

global $wpdb; 
$prefix = $wpdb->prefix; 
$charset = $wpdb->charset;

	/* psys */	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "psys LIKE 't2_1'"); /* 1.6 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "psys ADD `t2_1` bigint(20) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "psys LIKE 't2_2'"); /* 1.6 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "psys ADD `t2_2` bigint(20) NOT NULL default '0'");
	}	
	/* end psys */

	/* currency */
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 't1_1'"); /* 1.6 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `t1_1` bigint(20) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 't1_2'"); /* 1.6 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `t1_2` bigint(20) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'cat_id'"); /* 2.5 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `cat_id` bigint(20) NOT NULL default '0'");
	}	
	/* end currency */
	
	/* currency_custom_fields */ 
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency_custom_fields LIKE 'cf_order_give'"); /* 1.6 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency_custom_fields ADD `cf_order_give` bigint(20) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency_custom_fields LIKE 'cf_order_get'"); /* 1.6 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency_custom_fields ADD `cf_order_get` bigint(20) NOT NULL default '0'");
	}	
	/* end currency_custom_fields */

	/* directions */
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'dcom1'"); /* 2.0 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `dcom1` int(1) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'dcom2'"); /* 2.0 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `dcom2` int(1) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'com_det1'"); /* 2.4 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `com_det1` int(1) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'com_det2'"); /* 2.4 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `com_det2` int(1) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'com_box_ns1'"); /* 2.4 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `com_box_ns1` int(1) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'com_box_ns2'"); /* 2.4 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `com_box_ns2` int(1) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'com_box_det1'"); /* 2.4 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `com_box_det1` int(1) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'com_box_det2'"); /* 2.4 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `com_box_det2` int(1) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'mailtemp'"); /* 2.6 */
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `mailtemp` longtext NOT NULL");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "directions LIKE 'maildata'"); /* 2.6 */
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "directions ADD `maildata` longtext NOT NULL");
    }	
	/* end directions */

	/* bids */
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'user_login'"); /* 1.6 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `user_login` varchar(150) NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'user_telegram'"); /* 2.0 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `user_telegram` varchar(150) NOT NULL");
	}	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'dest_tag'"); /* 2.4 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `dest_tag` varchar(150) NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'out_sum'"); /* 2.4 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `out_sum` varchar(50) NOT NULL default '0'");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'txid_in'"); /* 2.5 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `txid_in` varchar(250) NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'txid_out'"); /* 2.5 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `txid_out` varchar(250) NOT NULL");
	}
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'user_agent'"); /* 2.5 */
	if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `user_agent` varchar(250) NOT NULL");
	}	
	/* end bids */	