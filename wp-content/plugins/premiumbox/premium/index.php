<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('premium_plugins_loaded')) {
	add_action('plugins_loaded', 'premium_plugins_loaded');
	function premium_plugins_loaded() {
		load_plugin_textdomain('premium', false, dirname(plugin_basename(__FILE__)) . '/languages'); 
	}			
}

require_once(__DIR__ . "/includes/functions.php");
require_once(__DIR__ . "/includes/files_functions.php");
require_once(__DIR__ . "/includes/default-constants.php");
require_once(__DIR__ . "/includes/db.php"); 
require_once(__DIR__ . "/includes/lang_functions.php");
require_once(__DIR__ . "/includes/lang_filters.php");
require_once(__DIR__ . "/includes/rtl_functions.php");
require_once(__DIR__ . "/includes/init_page.php");
require_once(__DIR__ . "/includes/init_cron.php");
require_once(__DIR__ . "/includes/security.php");
require_once(__DIR__ . "/includes/menu_filters.php");
require_once(__DIR__ . "/includes/class-form.php");
require_once(__DIR__ . "/includes/class-list-table.php");
require_once(__DIR__ . "/includes/cookie.php");
require_once(__DIR__ . "/includes/mail_filters.php"); 
require_once(__DIR__ . "/includes/comment_system.php");
require_once(__DIR__ . "/includes/constructs.php");
require_once(__DIR__ . "/includes/wp_comments.php");
require_once(__DIR__ . "/includes/pagenavi.php");
require_once(__DIR__ . "/includes/class-premium.php");
require_once(__DIR__ . "/includes/class-extension.php");