<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('new_parser_links', 'blackparser_new_parser_links', 0);
function blackparser_new_parser_links($links) {
	global $wpdb; 
	
	$lists = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "blackparsers ORDER BY id DESC");
	foreach ($lists as $list) {
		
		$links['xmlc_' . $list->id] = array(
			'title' => pn_strip_input($list->title),
			'url' => pn_strip_input($list->url),
			'birg_key' => 'xmlc_' . $list->id,
		);	
		
	}
	
	return $links;
}	