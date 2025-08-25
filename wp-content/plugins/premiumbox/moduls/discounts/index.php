<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Cumulative user discount[:en_US][ru_RU:]Накопительная скидка пользователей[:ru_RU]
description: [en_US:]Cumulative user discount[:en_US][ru_RU:]Накопительная скидка пользователей[:ru_RU]
version: 2.7.0
category: [en_US:]Users[:en_US][ru_RU:]Пользователи[:ru_RU]
cat: user
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_discounts');
function bd_all_moduls_active_discounts() {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "user_discounts";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
        `sumec` varchar(50) NOT NULL default '0',
		`discount` varchar(50) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`sumec`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);
	
}

/*
add_action('pn_adminpage_quicktags_page', 'pn_adminpage_quicktags_page_discount');
function pn_adminpage_quicktags_page_discount() {
?>
edButtons[edButtons.length] = 
new edButton('premium_table_discount', '<?php _e('Discount table', 'pn'); ?>', '[table_discount]');
<?php	
}
*/

add_action('admin_menu', 'admin_menu_discount');
function admin_menu_discount() {
	global $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_discount')) {
		add_menu_page(__('User discounts', 'pn'), __('User discounts', 'pn'), 'read', "pn_discount", array($premiumbox, 'admin_temp'), $premiumbox->get_icon_link('discount'));	
		add_submenu_page("pn_discount", __('Add discount', 'pn'), __('Add discount', 'pn'), 'read', "pn_add_discount", array($premiumbox, 'admin_temp'));
	}
	
}

add_filter('pn_caps','discount_pn_caps');
function discount_pn_caps($pn_caps) {
	
	$pn_caps['pn_discount'] = __('User discounts settings', 'pn');
	
	return $pn_caps;
}
 
function shortcode_table_discount($atts,$content = "") { 
	global $wpdb;

    $datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "user_discounts ORDER BY (sumec -0.0) ASC");
	$temp = '
	<div class="discont_div">
		<div class="discont_div_ins">
			<table>
				<tr>
					<th>' . __('Total amount of exchanges', 'pn') . ' (' . cur_type() . ')</th>
					<th>' . __('Discount (%)', 'pn') . '</th>
				</tr>';
				
				foreach ($datas as $item) {
					$temp .= '
					<tr>
						<td> > ' . is_out_sum(is_sum($item->sumec), 2, 'reserv') . '</td>
						<td>' . is_sum($item->discount) . '%</td>
					</tr>
					';
				}
				
				$temp .= '
			</table>
		</div>	
	</div>
	';
	
	return $temp;
}
add_shortcode('table_discount', 'shortcode_table_discount');

add_filter('user_discount', 'user_discount_discounts', 9, 2);
function user_discount_discounts($discount, $user_id) {
	global $wpdb;
	
	$discount = is_sum($discount);
	if ($discount < '0.0001') {
		$sm = 0;
		if (function_exists('get_user_sum_exchanges')) {
			$sm = is_sum(get_user_sum_exchanges($user_id));
		}
		$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_discounts WHERE ('$sm' -0.0) >= sumec ORDER BY (sumec -0.0) DESC");
		if (isset($data->discount)) {
			$discount = is_sum($data->discount);
		}	
	}
	
	return $discount;
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'add');
$premiumbox->include_path(__FILE__, 'list'); 