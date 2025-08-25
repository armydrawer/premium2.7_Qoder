<?php
if (!defined('ABSPATH')) { exit(); }
 
add_action('wp_dashboard_setup', 'premiumbox_license_wp_dashboard_setup');
function premiumbox_license_wp_dashboard_setup() {
	
	wp_add_dashboard_widget('premiumbox_license_pn_dashboard_widget', __('License Info', 'pn'), 'premiumbox_dashboard_license_pn_in_admin_panel');
	
}

function premiumbox_license_data($end_time, $show_ok = 0, $class = 0) {
	
	$show_ok = intval($show_ok);
	$class = intval($class);
	$text = '';
	$time = current_time('timestamp');
	$cou_days = ceil(($end_time - $time) / DAY_IN_SECONDS);
	$cou_days = intval($cou_days);
	
	$link = 'https://premium.gitbook.io/main/pered-nachalom-raboty/licenziya-na-skript/prodlenie-licenzii';
	
	if (0 == $cou_days) {
		if ($class) {
			$text .= '<span class="bred_dash">';
		}
			$text .= sprintf(__('License validity period expires today. License renewal <a href="%s" target="_blank">instructions</a>', 'pn'), $link);
		if ($class) {
			$text .= '</span>';
		}
	} elseif ($cou_days <= 7) {
		if ($class) {
			$text .= '<span class="bred">';
		}
			$text .= sprintf(__('Days till license expiration date: %1s days. License renewal <a href="%2s" target="_blank">instructions</a>', 'pn'), $cou_days, $link);
		if ($class) {
			$text .= '</span>';
		}		
	} else {
		if ($show_ok) {
			$text .= sprintf(__('Days till license expiration date: %1s days. License renewal <a href="%2s" target="_blank">instructions</a>', 'pn'), $cou_days, $link);
		}
	}	
	
	return $text;
}

function premiumbox_dashboard_license_pn_in_admin_panel() {
	
	$text = __('No data available', 'pn');
	$end_time = get_pn_license_time();
	if ($end_time) {
		$text = premiumbox_license_data($end_time, 1, 1);
	}
	
	echo $text;
}

add_action('after_pn_adminpage_title', 'after_pn_adminpage_title_premiumbox_license', 20, 2);
function after_pn_adminpage_title_premiumbox_license() { 
	
	$end_time = get_pn_license_time();
	if ($end_time and class_exists('PremiumForm')) { 
		$text = premiumbox_license_data($end_time, 0, 0);
		$form = new PremiumForm();
		if ($text) {
			echo '<div style="padding: 0 0 20px 0;">';
			$form->warning($text);
			echo '</div>';
		}
	}
	
}

add_filter('admin_footer_text', 'premiumbox_admin_footer_text', 1);
function premiumbox_admin_footer_text($text) {
	
	$text .= '<div>&copy; ' . get_copy_date('2015') . ' <strong>Premium Exchanger</strong>.';
	$end_time = get_pn_license_time();
	if ($end_time) {
		$text .= ' ' . premiumbox_license_data($end_time, 1, 1);
	}
	$text .= '</div>';
	
	return $text;
}

add_filter('login_headerurl', 'premiumbox_login_headerurl');
function premiumbox_login_headerurl($login_header_url) {
	
	$login_header_url = 'https://premiumexchanger.com/';
	
	return $login_header_url;
}

add_filter('login_headertext', 'premiumbox_login_headertext');
function premiumbox_login_headertext($login_header_title) {
	
	$login_header_title = 'PremiumExchanger';
	
	return $login_header_title;
}

add_action('login_head', 'premiumbox_login_head');
function premiumbox_login_head() {
	global $premiumbox;
	
?>
<style>
.login h1 a {
	height: 108px;
	width: 108px;
	background: url(<?php echo $premiumbox->plugin_url; ?>images/admin-logo.png) no-repeat center center;	
}
</style>
<?php
}