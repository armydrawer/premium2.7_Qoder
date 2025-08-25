<?php
if (!defined('ABSPATH')) { exit(); }
 
add_filter('placed_form', 'placed_form_zreserv');
function placed_form_zreserv($placed) {
	
	$placed['reservform'] = __('Reserve request', 'pn');
	
	return $placed;
}

add_filter('reservform_filelds', 'def_reservform_filelds');
function def_reservform_filelds($items) {
	
	$ui = wp_get_current_user();

	$items['sum'] = array(
		'name' => 'sum',
		'title' => __('Required amount', 'pn'),
		'req' => 1,
		'value' => '',
		'type' => 'input',
	);
	$items['email'] = array(
		'name' => 'email',
		'title' => __('E-mail', 'pn'),
		'req' => 1,
		'value' => is_email(is_isset($ui, 'user_email')),
		'type' => 'input',
	);		
	$items['comment'] = array(
		'name' => 'comment',
		'title' => __('Comment', 'pn'),
		'req' => 0,
		'value' => '', 
		'type' => 'text',
	);		
	
	return $items;
}

add_filter('replace_array_reservform', 'def_replace_array_reservform', 10, 3);
function def_replace_array_reservform($array, $prefix, $place = '') {
	global $wpdb, $premiumbox;
	
	$fields = get_form_fields('reservform', $place);
	
	$filter_name = '';
	if ('widget' == $place) {
		$prefix = 'widget_' . $prefix;
		$filter_name = 'widget_';
	}
	$html = prepare_form_fileds($fields, $filter_name . 'reserv_form_line', $prefix);	
	
	$array = array(
		'[form]' => '<form method="post" class="ajax_post_form" action="' . get_pn_action('reservform') . '">',
		'[/form]' => '</form>',
		'[result]' => '<div class="resultgo"></div>',
		'[html]' => $html,
		'[submit]' => '<input type="submit" formtarget="_top" name="submit" class="' . $prefix . '_submit" value="' . __('Send a request', 'pn') . '" />',
	);	
	
	return $array;
}
 
add_action('premium_js', 'premium_js_zreserve');
function premium_js_zreserve() {	
?>	
jQuery(function($) { 

	$(document).on('click', '.js_reserve', function() {
		
		$(document).JsWindow('show', {
			window_class: 'update_window',
			title: '<?php _e('Request to reserve', 'pn'); ?> "<span id="reserve_box_title"></span>"',
			content: $('.reserve_box_html').html(),
			insert_div: '.reserve_box',
			shadow: 1
		});		
		
		var title = $(this).attr('data-title');
        var id = $(this).attr('data-id');		
		$('#reserve_box_title').html(title);	
		$('#reserve_box_id').attr('value',id);				
		
	    return false;
	});	
	
});	
<?php	
}

add_action('wp_footer', 'wp_footer_zreserve');
function wp_footer_zreserve() {
		
	$array = get_form_replace_array('reservform', 'rb');
		
	$temp = '
	<div class="reserve_box_html" style="display: none;">		
		[html]	
		<div class="rb_line">[submit]</div>
		[result]
	</div>';	
		
	$temp .= '
	[form]
		<input type="hidden" name="id" id="reserve_box_id" value="0" />
			
		<div class="reserve_box"></div>
	[/form]
	';
		
	$temp = apply_filters('zreserve_form_temp', $temp);
	echo replace_tags($array, $temp);	
}

add_action('premium_siteaction_reservform', 'def_premium_siteaction_reservform');
function def_premium_siteaction_reservform() {
	global $wpdb, $premiumbox;	
	
	_method('post');
	_json_head();

	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0;
	$log['status_text'] = '';
	$log['errors'] = array();
	
	$premiumbox->up_mode('post');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	$log = _log_filter($log, 'reservform');
		
	$id = intval(is_param_post('id'));
	$sum = is_sum(is_param_post('sum'), 8);
	$email = is_email(is_param_post('email'));
	$comment = pn_maxf_mb(pn_strip_input(is_param_post('comment')), 500);
		
	if (!$log['status_code']) {
		if ($sum <= 0) {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! Requested amount is lesser than zero', 'pn');
			$log = pn_array_unset($log, 'url');	
		}		
	}		
		
	if (!$log['status_code']) {
		if (!$email) {
			$log['status'] = 'error';
			$log['status_code'] = 2;
			$log['status_text'] = __('Error! You have not entered e-mail', 'pn');
			$log = pn_array_unset($log, 'url');	
		}		
	}
	
	if (!$log['status_code']) {
		$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$id' AND direction_status IN('1','2') AND auto_status = '1'");
		if (!isset($direction->id)) {
			$log['status'] = 'error';
			$log['status_code'] = 3;
			$log['status_text'] = __('Error! Direction does not exist', 'pn');
			$log = pn_array_unset($log, 'url');	
		}		
	}	
				
	$reserve = 0;
	if (!$log['status_code']) {
		$v = get_currency_data();
		$curr_id_get = $direction->currency_id_get;
		$cur = is_isset($v, $curr_id_get);
		$reserve = get_direction_reserve('', $cur, $direction);
		if ($sum <= $reserve) {
			$log['status'] = 'error';
			$log['status_code'] = 4;
			$log['status_text'] = __('Error! The necessary reserve is available', 'pn');
			$log = pn_array_unset($log, 'url');			
		}
	}
	
	if (!$log['status_code']) {

		$last = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "direction_reserve_requests WHERE user_email = '$email' AND direction_id = '$id'");
					
		$array = array();
		$array['request_date'] = current_time('mysql');
		$array['direction_id'] = $id;
		$array['direction_title'] = pn_strip_input($direction->tech_name);
		$array['user_email'] = $email;
		$array['request_comment'] = $comment;
		$array['request_amount'] = $sum;
		$array['request_locale'] = get_locale();
					
		if (isset($last->id)) {
			$wpdb->update($wpdb->prefix . "direction_reserve_requests", $array, array('id' => $last->id));
		} else {
			$wpdb->insert($wpdb->prefix . "direction_reserve_requests", $array);
		}
		
		$now_locale = get_locale();
		$set_locale = get_admin_lang();
		
		set_locale($set_locale);

		$notify_tags = array();
		$notify_tags['[sum]'] = $array['request_amount'];
		$notify_tags['[sumres]'] = $reserve;
		$notify_tags['[direction]'] = $array['direction_title'];
		$notify_tags['[direction_url]'] = get_exchange_link($direction->direction_name);
		$notify_tags['[email]'] = $array['user_email'];
		$notify_tags['[comment]'] = $comment;
		$notify_tags['[ip]'] = pn_real_ip();
		$notify_tags = apply_filters('notify_tags_zreserv_admin', $notify_tags, $ui);
			
		$user_send_data = array(
			'admin_email' => 1,
		);	
		$result_mail = apply_filters('premium_send_message', 0, 'zreserv_admin', $notify_tags, $user_send_data); 

		set_locale($now_locale);								
					
		$log['status'] = 'success';
		$log['clear'] = 1;
		$log['status_text'] = __('Request has been successfully created', 'pn');
			
	}
	
	echo pn_json_encode($log);
	exit;
}