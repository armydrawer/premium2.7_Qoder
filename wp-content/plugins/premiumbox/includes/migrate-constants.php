<?php 
if (!defined('ABSPATH')) { exit(); }

if (!defined('MERCH_ACTION_PASSWORD') and defined('PN_SECRET_KEY')) {
	define('MERCH_ACTION_PASSWORD', PN_SECRET_KEY); 
}

if (!defined('PAY_ACTION_PASSWORD') and defined('PN_SECRET_KEY')) {
	define('PAY_ACTION_PASSWORD', PN_SECRET_KEY);	
}

if (!defined('EDIT_ACTION_PASSWORD') and defined('PN_SECRET_KEY')) {
	define('EDIT_ACTION_PASSWORD', PN_SECRET_KEY); 	
}	
	
if (!defined('EXT_SALT') and defined('PN_HASH_KEY')) {
	define('EXT_SALT', PN_HASH_KEY);
}