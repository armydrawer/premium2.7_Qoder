<?php 
if (!defined('ABSPATH')) { exit(); }

if (!defined('CURL_SSLVERSION_TLSv1_2')) {
	define('CURL_SSLVERSION_TLSv1_2', 6);
}

if (!defined('CONCATENATE_SCRIPTS')) {
	define('CONCATENATE_SCRIPTS', false);
}

if (!defined('DISALLOW_FILE_MODS')) {
	define('DISALLOW_FILE_MODS', true);
}

if (!defined('PN_SITEURL')) {
	define('PN_SITEURL', rtrim(get_option('siteurl'), '/'));
}

if (!defined('PN_SITE_URL')) {
	define('PN_SITE_URL', rtrim(PN_SITEURL, '/') . '/');
}

if (!defined('PN_TEMPLATEURL')) {
	define('PN_TEMPLATEURL', rtrim(get_template_directory_uri(), '/'));
}

if (!defined('PN_TEMPLATE_URL')) {
	define('PN_TEMPLATE_URL', rtrim(PN_TEMPLATEURL, '/') . '/');
}

if (!defined('PN_USERSESS_DAY')) {
	define('PN_USERSESS_DAY', 7);
}

if (!defined('PN_HASH_CRON')) {
	define('PN_HASH_CRON', ''); 
}

if (!defined('PN_CRON_FILE')) {
	define('PN_CRON_FILE', 0); 
}

if (!defined('PN_CRON_SLEEP')) {
	define('PN_CRON_SLEEP', 0); 
}

if (!defined('PN_ADMIN_GOWP')) {
	define('PN_ADMIN_GOWP', 'false'); 
}