<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('_debug_table_from_db')) {
	function _debug_table_from_db($result, $tbl_name, $cols) {
		global $wpdb;
		
		$errors = array();
		$result = intval($result);
		if ($result < 1) {
			$query = $wpdb->query("CHECK TABLE " . $wpdb->prefix . $tbl_name);
			if (1 == $query) {	
			
				$has_primary = 0;
				$items = $wpdb->get_results("SHOW COLUMNS FROM " . $wpdb->prefix . $tbl_name);
				foreach ($items as $d) {
					$field = trim(is_isset($d, 'Field'));
					$key = trim(is_isset($d, 'Key'));
					if ('PRI' == $key) {
						$has_primary = 1;
					}
					if ($field and isset($cols[$field])) {
						unset($cols[$field]);
					}
				}

				if (!$has_primary) {
					$errors[] = sprintf(__('No primary key in DB table "%s"', 'premium'), $tbl_name);
				}
				
				if (count($cols) > 0) {
					$no_cols = array();
					foreach ($cols as $col_i => $col_d) {
						$no_cols[] = '"' . $col_i . '"';
					}
					$errors[] = sprintf(__('No columns in DB table "%1s" - %2s', 'premium'), $tbl_name, implode(', ', $no_cols));
				}
				
			} else {
				$errors[] = sprintf(__('DB table "%s" not exists'), $tbl_name);
			}
		}
		
		return $errors;
	}
}

if (!function_exists('_display_db_table_error')) {
	function _display_db_table_error($form, $res_errors) {
		if (is_array($res_errors) and count($res_errors) > 0) {
			$form->error_form(implode('<br />', $res_errors));	
		}
	}
}

if (!function_exists('pn_install_default_db')) {
	function pn_install_default_db() {
		global $wpdb;
		
		$prefix = $wpdb->prefix;
		$charset = $wpdb->charset;
			
		$table_name = $prefix . "pn_options";
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`meta_key` varchar(250) NOT NULL,
			`meta_key2` varchar(250) NOT NULL,
			`meta_value` longtext NOT NULL,
			PRIMARY KEY (`id`),
			INDEX (`meta_key`),
			INDEX (`meta_key2`)
		) ENGINE=InnoDB DEFAULT CHARSET={$charset} AUTO_INCREMENT=1;";
		$wpdb->query($sql);
		
		$table_name = $wpdb->prefix . "notify";
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`notify_type` varchar(200) NOT NULL,
			`notify_place` varchar(200) NOT NULL,
			`notify_options` longtext NOT NULL,
			PRIMARY KEY (`id`),
			INDEX (`notify_type`),
			INDEX (`notify_place`)
		) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;"; 
		$wpdb->query($sql);		

		$table_name = $wpdb->prefix . "auth_logs";
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`auth_date` datetime NOT NULL,
			`user_id` bigint(20) NOT NULL,
			`user_login` varchar(250) NOT NULL,
			`old_user_ip` varchar(250) NOT NULL,
			`old_user_browser` varchar(250) NOT NULL,
			`now_user_ip` varchar(250) NOT NULL,
			`now_user_browser` varchar(250) NOT NULL,
			`auth_status` int(1) NOT NULL default '0',
			`auth_status_text` longtext NOT NULL,
			PRIMARY KEY (`id`),
			INDEX (`user_id`),
			INDEX (`auth_date`),
			INDEX (`auth_status`)
		) ENGINE=InnoDB  DEFAULT CHARSET=$charset AUTO_INCREMENT=1;";
		$wpdb->query($sql);

		$table_name = $wpdb->prefix . "archive_data";
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`meta_key` varchar(250) NOT NULL,
			`meta_key2` varchar(250) NOT NULL,
			`meta_key3` varchar(250) NOT NULL,
			`item_id` bigint(20) NOT NULL default '0',
			`meta_value` varchar(150) NOT NULL default '0',
			PRIMARY KEY (`id`),
			INDEX (`meta_key`),
			INDEX (`meta_key2`),
			INDEX (`meta_key3`),
			INDEX (`meta_value`)
		) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
		$wpdb->query($sql);

		$table_name = $wpdb->prefix . "exts";
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`ext_type` varchar(200) NOT NULL,
			`ext_title` varchar(250) NOT NULL,
			`ext_plugin` varchar(250) NOT NULL,
			`ext_key` varchar(250) NOT NULL,
			`ext_status` int(1) NOT NULL default '0',
			`ext_options` longtext NOT NULL,
			PRIMARY KEY (`id`),
			INDEX (`ext_type`),
			INDEX (`ext_key`),
			INDEX (`ext_status`)
		) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
		$wpdb->query($sql);

		$table_name = $wpdb->prefix . "comment_system";
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`comment_date` datetime NOT NULL,
			`user_id` bigint(20) NOT NULL default '0',
			`user_login` varchar(250) NOT NULL,
			`text_comment` longtext NOT NULL,
			`itemtype` varchar(50) NOT NULL,
			`item_id` varchar(50) NOT NULL default '0',
			PRIMARY KEY (`id`),
			INDEX (`comment_date`),
			INDEX (`item_id`),
			INDEX (`itemtype`)
		) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
		$wpdb->query($sql);

		$table_name = $wpdb->prefix . "constructs";
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`itemtype` varchar(50) NOT NULL,
			`item_id` varchar(50) NOT NULL default '0',
			`amount` varchar(20) NOT NULL default '0',
			`itemsettings` longtext NOT NULL,
			PRIMARY KEY (`id`),
			INDEX (`item_id`),
			INDEX (`amount`),
			INDEX (`itemtype`)
		) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
		$wpdb->query($sql);	

		$table_name = $wpdb->prefix . "hidefiles";
		$sql = "CREATE TABLE IF NOT EXISTS $table_name(
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`itemtype` varchar(50) NOT NULL,
			`item_id` varchar(50) NOT NULL default '0',
			`user_id` bigint(20) NOT NULL default '0',
			`file_name` longtext NOT NULL,
			`file_ext` varchar(50) NOT NULL,
			PRIMARY KEY (`id`),
			INDEX (`item_id`),
			INDEX (`user_id`),
			INDEX (`itemtype`)
		) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
		$wpdb->query($sql);		
			
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'created_data'");
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `created_data` longtext NOT NULL");
		}	
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'sec_lostpass'");
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `sec_lostpass` int(1) NOT NULL default '1'");
		}
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'alogs_email'");
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `alogs_email` int(1) NOT NULL default '0'");
		}
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'alogs_sms'");
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `alogs_sms` int(1) NOT NULL default '0'");
		}
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'alogs_telegram'");
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `alogs_telegram` int(1) NOT NULL default '0'");
		}	
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'email_login'"); 
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `email_login` int(1) NOT NULL default '0'");
		}
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'sms_login'"); 
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `sms_login` int(1) NOT NULL default '0'");
		}
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'telegram_login'"); 
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `telegram_login` int(1) NOT NULL default '0'");
		}
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'enable_ips'");
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `enable_ips` longtext NOT NULL");
		}
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'user_pin'");
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `user_pin` varchar(250) NOT NULL");
		}
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'user_browser'");
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `user_browser` varchar(250) NOT NULL");
		}
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'user_ip'");
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `user_ip` varchar(250) NOT NULL");
		}
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'user_bann'");
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `user_bann` int(1) NOT NULL default '0'");
		}
		
		$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'last_adminpanel'");
		if (0 == $query) {
			$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `last_adminpanel` varchar(50) NOT NULL");
		}			

		$wpdb->update($wpdb->prefix . 'usermeta', array('meta_value' => ''), array('meta_key' => 'locale'));		
	}
}