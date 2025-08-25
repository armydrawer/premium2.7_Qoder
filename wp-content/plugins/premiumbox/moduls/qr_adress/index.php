<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]QR code generator[:en_US][ru_RU:]QR код генератор[:ru_RU]
description: [en_US:]QR code generator[:en_US][ru_RU:]QR код генератор[:ru_RU]
version: 2.7.0
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

add_action('pn_adminpage_quicktags', 'pn_adminpage_quicktags_qr_adress', 0); 
function pn_adminpage_quicktags_qr_adress() {
?>
edButtons[edButtons.length] = 
new edButton('premium_qr_code', '<?php _e('QR code', 'pn'); ?>', '[qr_code size="200"]', '[/qr_code]');
<?php	
}

add_filter('pn_other_tags','qr_adress_other_tags', 0);
function qr_adress_other_tags($tags) {

	$tags['qr_code'] = array(
		'title' => __('QR code', 'pn'),
		'start' => '[qr_code size="200"]',
		'end' => '[/qr_code]',
	);

	return $tags;
}

function shortcode_qr_code($atts, $content = "") { 

	$size = intval(is_isset($atts, 'size')); if ($size < 1) { $size = 200; }
	
    return '<div class="js_qr_code_wrap">' . get_qr(strip_tags(do_shortcode($content)), $size, $size) . '</div>';
}
add_shortcode('qr_code', 'shortcode_qr_code'); 

function get_qr($text, $width, $height) {
	
	return '<img src="https://api.qrserver.com/v1/create-qr-code/?size=' . $width . 'x' . $height . '&data=' . urlencode($text) . '" title="' . $text . '" alt="' . $text . '" />';
}

add_action('_merchants_options', 'qr_code_get_merchants_options', 10, 5);
function qr_code_get_merchants_options($options, $name, $data, $id, $place) {
	
	$in_array = apply_filters('qr_keys', array());
	if (in_array($name, $in_array)) {
		$options['qrcode'] = array(
			'view' => 'select',
			'title' => __('Show QR code on payment page', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => is_isset($data, 'qrcode'),
			'name' => 'qrcode',
			'work' => 'int',
		);	
	}
	
	return $options;
}	
	
add_filter('zone_table_line', 'qr_code_zone_table_line', 10, 4);
function qr_code_zone_table_line($now_zone, $m_id, $key, $item) {
	global $bids_data;
	
	$new_html = '';
	if (isset($bids_data->id)) {
		$item_id = $bids_data->id;
		$m_data = get_merch_data($m_id);
		$qrcode = intval(is_isset($m_data, 'qrcode'));
		if (1 == $qrcode) {
			$copy_text = trim(is_isset($item, 'copy'));
			if (in_array($key, array('account', 'dest_tag')) and $copy_text) {
				$new_html = '
				<div style="padding: 0px 0 0px;">
					' . get_qr($copy_text, 260, 260) . '
				</div>
				';
			}
		}
	}
	
	return $now_zone . $new_html;
}		