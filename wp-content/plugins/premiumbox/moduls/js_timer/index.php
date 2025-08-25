<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Countdown timer[:en_US][ru_RU:]Таймер обратного отсчета[:ru_RU]
description: [en_US:]Countdown timer of unpaid order deleting[:en_US][ru_RU:]Таймер обратного отсчета удаления неоплаченных заявок[:ru_RU]
version: 2.7.0
category: [en_US:]Other[:en_US][ru_RU:]Остальное[:ru_RU]
cat: other
*/

add_action('pn_adminpage_quicktags', 'pn_adminpage_quicktags_js_timer', 0); 
function pn_adminpage_quicktags_js_timer() {
?>
edButtons[edButtons.length] = 
new edButton('premium_js_timer', '<?php _e('Countdown timer', 'pn'); ?>', '[js_timer]', '[/js_timer]');
<?php	
}

add_filter('pn_other_tags', 'js_timer_pn_tags', 0);
function js_timer_pn_tags($tags) {
	
	$tags['js_timer'] = array(
		'title' => __('Countdown timer', 'pn'),
		'start' => '[js_timer]',
		'end' => '[/js_timer]',
	);
	
	return $tags;
}
 
add_filter('merchant_temps_script', 'js_timer_merchant_temps_script');
function js_timer_merchant_temps_script($lists) {
	
	$lists['jstimer'] = '<script type="text/javascript" src="' . get_premium_url() . 'js/jquery-timer/script.min.js"></script>';
	
	return $lists;
}

add_action('wp_enqueue_scripts', 'themeinit_js_timer');
function themeinit_js_timer() {
	
	wp_enqueue_script('jquery js timer', get_premium_url() . "js/jquery-timer/script.min.js", false, '0.3');
	
}

add_shortcode('js_timer', 'shortcode_js_timer');
function shortcode_js_timer($atts, $content = "") { 

	$now_time = current_time('timestamp');
	$end = trim($content);
	$end_time = strtotime($end);

	$cl = '';
	if ($end_time < $now_time) {
		$cl = 'ending';
	}
	
	$to_end = $end_time - $now_time;
	$zero = intval(is_isset($atts, 'zeroise'));
	
    return '<span class="js_timer time_span ' . $cl . '" data-zero="' . $zero . '" data-y="' . __('y.', 'pn') . '" data-m="' . __('m.', 'pn') . '" data-d="' . __('d.', 'pn') . '" data-h="' . __('h.', 'pn') . '" data-mi="' . __('min.', 'pn') . '" data-s="' . __('sec.', 'pn') . '" end-time="' . $to_end . '">' . $end . '</span>';
}

add_action('live_change_html', 'js_timer_live');
function js_timer_live() {
	?>
	$(document).JTimer();
	<?php
}