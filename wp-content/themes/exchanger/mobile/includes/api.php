<?php 
if (!defined('ABSPATH')) { exit(); }

if (function_exists('is_mobile') and is_mobile()) {
	
	remove_action('wp_footer', 'statuswork_wp_footer');
	remove_action('wp_footer', 'wp_footer_topbutton');
	remove_action('live_change_html', 'js_select_live');
	remove_action('premium_js', 'premium_js_exchange_widget');
	
	add_filter('before_reviews_page', 'mobile_page_title');
	add_filter('before_plinks_page', 'mobile_page_title'); 
	add_filter('before_preferals_page', 'mobile_page_title');
	add_filter('before_payouts_page', 'mobile_page_title');
	add_filter('before_sitemap_page', 'mobile_page_title');
	add_filter('before_tarifs_page', 'mobile_page_title');
	add_filter('before_pexch_page', 'mobile_page_title');
	add_filter('before_userxch_page', 'mobile_page_title');
	add_filter('before_userverify_page', 'mobile_page_title');
	add_filter('before_promotional_page', 'mobile_page_title');
	add_filter('before_exchange_page', 'mobile_page_title_exchange');
	add_filter('before_exchangestep_page', 'mobile_page_title_exchange');
	
}

function mobile_page_title() {
	
	$html = '<h1 class="page_wrap_title">' . get_the_title() . '</h1>';
	
	return $html;
}

function mobile_page_title_exchange() {
	
	if (function_exists('is_pn_page') and is_pn_page('exchange')) {
		$html = '<h1 class="page_wrap_title" id="the_title_page">' . get_exchange_title() . '</h1>';
		return $html;		
	} elseif (function_exists('is_pn_page') and is_pn_page('hst')) {
		$html = '<h1 class="page_wrap_title" id="the_title_page">' . get_exchangestep_title() . '</h1>';
		return $html;
	}	
	
}