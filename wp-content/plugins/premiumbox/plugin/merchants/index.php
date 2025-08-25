<?php
if (!defined('ABSPATH')) { exit(); }
  
add_filter('pn_caps', 'merchants_pn_caps');
function merchants_pn_caps($pn_caps) {
	
	$pn_caps['pn_merchants'] = __('Work with merchants', 'pn');
	
	return $pn_caps;
}

add_action('admin_menu', 'admin_menu_merchants');
function admin_menu_merchants() {
	global $premiumbox;	
	
	if (current_user_can('administrator') or current_user_can('pn_merchants')) {
		add_submenu_page("pn_merchants", __('Merchants', 'pn'), __('Merchants', 'pn'), 'read', "pn_merchants", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_merchants", __('Add merchant', 'pn'), __('Add merchant', 'pn'), 'read', "pn_merchants_add", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_merchants", __('Automatic payouts', 'pn'), __('Automatic payouts', 'pn'), 'read', "pn_paymerchants", array($premiumbox, 'admin_temp'));
		add_submenu_page("pn_merchants", __('Add automatic payout', 'pn'), __('Add automatic payout', 'pn'), 'read', "pn_paymerchants_add", array($premiumbox, 'admin_temp'));		
	}
	
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'merch_func');
set_extandeds($premiumbox, 'merchants');
$premiumbox->include_path(__FILE__, 'list_merchants'); 
$premiumbox->include_path(__FILE__, 'add_merchants');  

$premiumbox->include_path(__FILE__, 'paymerch_func');
set_extandeds($premiumbox, 'paymerchants');
$premiumbox->include_path(__FILE__, 'list_paymerchants'); 
$premiumbox->include_path(__FILE__, 'add_paymerchants');  
   
$premiumbox->include_path(__FILE__, 'timeout_ap');
$premiumbox->include_path(__FILE__, 'timeout_merch');

$premiumbox->include_path(__FILE__, 'directions');
$premiumbox->include_path(__FILE__, 'temps');