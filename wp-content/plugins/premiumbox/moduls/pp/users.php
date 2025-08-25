<?php
if (!defined('ABSPATH')) { exit(); }

add_action('delete_user', 'delete_user_pp');
function delete_user_pp($user_id) {
	global $wpdb;

	$user_id = intval($user_id);
    $wpdb->update($wpdb->prefix . "users" , array('ref_id' => '0'), array('ref_id' => $user_id));
	
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "plinks WHERE user_id = '$user_id'");
	foreach ($items as $item) {
		$item_id = $item->id;
		$res = apply_filters('item_plinks_delete_before', pn_ind(), $item_id, $item);
		if ($res['ind']) {
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "plinks WHERE id = '$item_id'");
			do_action('item_plinks_delete', $item_id, $item, $result);
		}
	}	
	
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "user_payouts WHERE user_id = '$user_id'");
	foreach ($items as $item) {
		$item_id = $item->id;
		$res = apply_filters('item_user_payouts_delete_before', pn_ind(), $item_id, $item);
		if ($res['ind']) {
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "user_payouts WHERE id = '$item_id'");
			do_action('item_user_payouts_delete', $item_id, $item, $result);
		}
	}
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "archive_data WHERE item_id = '$user_id' AND meta_key IN('plinks','pbids','pbids_sum','pbids_exsum')");
}

add_filter('pn_user_register_data', 'pn_user_register_data_pp', 10, 2);
function pn_user_register_data_pp($array, $user_id) {
	
	$user_id = intval($user_id);
	$ref_id = intval(get_time_cookie('ref_id')); 
	if ($ref_id < 0) { $ref_id = 0; }
	
	if ($ref_id) {
		$array['ref_id'] = $ref_id;
	}	
	
	return $array;
}

add_action('template_redirect', 'init_pp', 8);
function init_pp() {
	global $wpdb, $premiumbox;
	
	$ref_id = intval(is_param_get(stand_refid()));
	if ($ref_id > 0) {
		$referer = pn_maxf(pn_strip_input(is_isset($_SERVER, 'HTTP_REFERER')), 500);
		$user = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "users WHERE ID = '$ref_id'");
		if (isset($user->ID)) {
			$time = current_time('timestamp');
			$date = current_time('mysql');	
					
			$clife = intval($premiumbox->get_option('partners', 'clife'));
			if ($clife < 1) { $clife = 365; }
			$cookie_time = $time + ($clife * 24 * 60 * 60);	
					
			add_time_cookie('ref_id', $ref_id, $cookie_time); 
				
			$query_string = pn_maxf(pn_strip_input(is_isset($_SERVER, 'QUERY_STRING')), 500);
				
			$ip = pn_strip_input(pn_real_ip()); 
			$browser = pn_maxf(pn_strip_input(is_isset($_SERVER, 'HTTP_USER_AGENT')), 250);
			$wpdb->insert($wpdb->prefix . 'plinks', array('query_string' => $query_string, 'user_hash' => get_session_id(), 'user_id' => $ref_id, 'user_login'=> is_user($user->user_login),'pbrowser' => $browser, 'pdate' => $date, 'pip' => $ip, 'prefer' => $referer));
		}
	}
}

add_action('all_user_editform_post', 'pp_all_user_editform_post');
function pp_all_user_editform_post($new_user_data) {
	
	if (current_user_can('administrator') or current_user_can('pn_pp')) { 
		$new_user_data['ref_id'] = intval(is_param_post('ref_id'));
		$new_user_data['partner_type'] = intval(is_param_post('partner_type'));
		$new_user_data['partner_pers'] = is_sum(is_param_post('partner_pers'));
		$new_user_data['max_partner_pers'] = is_sum(is_param_post('max_partner_pers'));
	}
	
	return $new_user_data;
}

add_filter('all_user_editform', 'pp_all_user_editform', 100, 2);
function pp_all_user_editform($options, $db_data) {
	global $premiumbox, $wpdb;
	
	$user_id = $db_data->ID;
	
	if (current_user_can('administrator') or current_user_can('pn_pp')) { 
	
		$options[] = array(
			'view' => 'h3',
			'title' => __('Affiliate program', 'pn'),
			'submit' => __('Save', 'pn'),
		);	
		$options['ref_id'] = array(
			'view' => 'input',
			'title' => __('Referral', 'pn'),
			'default' => intval($db_data->ref_id),
			'name' => 'ref_id',
		);		
		$options['partner_pers'] = array(
			'view' => 'input',
			'title' => __('Individual affiliate reward (%)', 'pn'),
			'default' => is_sum($db_data->partner_pers),
			'name' => 'partner_pers',
		);
		$options['max_partner_pers'] = array(
			'view' => 'input',
			'title' => __('Max. individual affiliate reward (%)', 'pn'),
			'default' => is_sum($db_data->max_partner_pers),
			'name' => 'max_partner_pers',
		);
		$options['partner_type'] = array(
			'view' => 'select',
			'title' => __('Accrue partner reward from', 'pn'),
			'options' => array('0' => '--' . __('Default', 'pn') . '--', '1' => __('Profits in direction settings', 'pn'), '2' => __('Exchange amounts', 'pn')),
			'default' => intval($db_data->partner_type),
			'name' => 'partner_type',
		);		
		$options['partner_plinks'] = array(
			'view' => 'textfield',
			'title' => __('Transitions', 'pn'),
			'default' => get_partner_plinks($user_id),
		);	
		$options['affiliate_exchange'] = array(
			'view' => 'textfield',
			'title' => __('Affiliate exchange', 'pn'),
			'default' => get_user_count_refobmen($user_id) . ' (' . get_user_sum_refobmen($user_id) . ' ' . cur_type() . ')',
		);
		$options['affiliate_interest'] = array(
			'view' => 'textfield',
			'title' => __('Affiliate interest', 'pn'),
			'default' => get_user_pers_refobmen($user_id, $db_data) . '%',
		);	
		$options['balance'] = array(
			'view' => 'textfield',
			'title' => __('Amount on your balance', 'pn'),
			'default' => '<a href="' . get_request_link('ppbalans', 'html', get_locale(), array('user_id' => $user_id)) . '" target="_blank">' . get_partner_money($user_id, array('0', '1')) . '</a> ' . cur_type(),
		);
		$options['earn'] = array(
			'view' => 'textfield',
			'title' => __('All time earned', 'pn'),
			'default' => get_partner_earn_all($user_id) . ' ' . cur_type(),
		);
		$options['partner_payouts'] = array(
			'view' => 'textfield',
			'title' => __('Paid in total', 'pn'),
			'default' => get_partner_payout($user_id, array('1')) . ' ' . cur_type(),
		);	
		
	}
	
	return $options;
}

add_filter('pntable_columns_all_users', 'pp_pntable_columns_all_users');
function pp_pntable_columns_all_users($columns) {
	
	if (current_user_can('administrator') or current_user_can('pn_pp')) { 
		$columns['partnermoney'] = __('Amount on your balance', 'pn');
	}
	
	return $columns;
}

add_filter('pntable_column_all_users', 'pp_pntable_column_all_users', 10, 3); 
function pp_pntable_column_all_users($empty, $column_name, $item) {
	global $premiumbox;	
	
	if ('partnermoney' == $column_name) {
		$minpay = is_sum($premiumbox->get_option('partners', 'minpay'), 2);
	    $balans = get_partner_money($item->ID, array('0', '1'));
	    $dbalans = 0;
	    if ($balans >= $minpay) {
            $dbalans = $balans;
        } 
		
	    return '<a href="' . get_request_link('ppbalans', 'html', get_locale(), array('user_id' => $item->ID)) . '" target="_blank">' . $balans . '</a> <span class="bgreen" title="' . __('Available for payment', 'pn') . '">(' . $dbalans . ')</span> ' . cur_type();
	}
			
	return $empty;	
}