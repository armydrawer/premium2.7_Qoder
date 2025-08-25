<?php 
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('get_list_user_menu')) {
	function get_list_user_menu() {
		
		$plugin = get_plugin_class();
		
		$account_list_pages = array(
			'account' => array(
				'title' => '',
				'url' => '',
				'type' => 'page',
				'class' => '',
				'id' => '',
			),
			'security' => array(
				'title' => '',
				'url' => '',
				'type' => 'page',
				'class' => '',
				'id' => '',			
			),										
		);
		$account_list_pages = apply_filters('account_list_pages', $account_list_pages);
		$pages = get_option($plugin->page_name);
		
		$list = array();
		if (is_array($account_list_pages)) {
			foreach ($account_list_pages as $key => $data) {
				$type = trim(is_isset($data, 'type'));
				$url = trim(is_isset($data, 'url'));
				$title = trim(is_isset($data, 'title'));
				$target = intval(is_isset($data, 'target'));
				$class = trim(is_isset($data, 'class'));
				$id = trim(is_isset($data, 'id'));
				
				if ('page' == $type) {
					if (isset($pages[$key])) {
						$page_url = get_permalink($pages[$key]);
						$current = '';
						if (is_page($pages[$key])) {
							$current = 'current';
						}
						$list[] = array(
							'url' => $page_url,
							'title' => get_the_title($pages[$key]),
							'target' => '',
							'class' => is_isset($data, 'class'),
							'id' => is_isset($data, 'id'),
							'current' => $current,
						);
					}
				} elseif ('target_link' == $type) {
					$list[] = array(
						'url' => $url,
						'title' => $title,
						'target' => 1,
						'class' => $class,
						'id' => $id,	
						'current' => is_place_url($url),
					);				
				} else {
					$list[] = array(
						'url' => $url,
						'title' => $title,
						'target' => $target,
						'class' => $class,
						'id' => $id,	
						'current' => is_place_url($url),
					);
				}
			}
		}
		
		return $list;
	}
}

add_filter('premium_js_login', 'def_premium_js_login');
function def_premium_js_login($ind) {
	
	if (1 == $ind) {
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);
		if ($user_id > 0) {
			return 0;
		}
	}
	
	return $ind;
}

function get_currency_categories() {
	
	$list = array(
		'1' => array(
			'title' => __('Cryptocurrencies', 'pn'),
			'decimal' => 8,
		),
		'2' => array(
			'title' => __('Electronic currencies', 'pn'),
			'decimal' => 2,
		),
		'3' => array(
			'title' => __('Cash', 'pn'),
			'decimal' => 2,
		),
		'4' => array(
			'title' => __('Bank card', 'pn'),
			'decimal' => 2,
		),		
	);
	
	return $list;
}

function get_currency_category_title($cat_id) {
	
	$cat_id = intval($cat_id);
	$cat_list = get_currency_categories();
	$cats = array();
	foreach ($cat_list as $cat_key => $cat_data) {
		$cats[$cat_key] = is_isset($cat_data, 'title');
	}		
	if (isset($cats[$cat_id])) {
		return $cats[$cat_id];
	} else {
		return __('no category', 'pn');
	}
	
}

add_filter('placed_form', 'def_placed_form', 0);
function def_placed_form() {
	
	$placed = array(
		'exchangeform' => __('Exchange type', 'pn'),
	);
	
	return $placed;
}

add_filter('set_exchange_cat_filters', 'def_set_exchange_cat_filters', 0);
function def_set_exchange_cat_filters() {
	
	$cats = array(
		'home' => __('Homepage exchange table', 'pn'),
		'exchange' => __('Exchange type', 'pn'),
	);
	
	return $cats;
}

add_action('set_exchange_filters', 'dirstatus_set_exchange_filters', 0);
function dirstatus_set_exchange_filters($lists) {
	
	$lists[] = array(
		'title' => __('Frozen exchange direction', 'pn'),
		'name' => 'holdstatus',
	);
	
	return $lists;
}

function list_bid_status() {
	
	$status = array(
		'coldnew' => __('application pending verification', 'pn'),
		'new' => __('new order', 'pn'),
		'cancel' => __('cancelled order by user', 'pn'),
		'delete' => __('deleted order', 'pn'),
		'techpay' => __('when user entered payment section', 'pn'),
		'payed' => __('user marked order as paid', 'pn'),
		'coldpay' => __('waiting for merchant confirmation', 'pn'),
		'partpay' => __('partial paid', 'pn'),
		'realpay' => __('paid order', 'pn'),
		'verify' => __('order is on checking', 'pn'),
		'amlwait' => __('waiting for aml check', 'pn'),
		'amlerror' => __('aml check failed', 'pn'),
		'merchwait' => __('waiting for details from the merchant', 'pn'),
		'mercherror' => __('merchant error', 'pn'), 
		'error' => __('error order', 'pn'),
		'payouterror' => __('automatic payout error', 'pn'), 
		'scrpayerror' => __('automatic payout error (payment system API)', 'pn'),
		'coldsuccess' => __('waiting for automatic payment module confirmation', 'pn'),
		'partpayout' => __('partial payout', 'pn'),
		'success' => __('successful order', 'pn'),
	);	
	
	return apply_filters('bid_status_list', $status);
}	

function get_status_sett($place, $in_array = 0) {
	global $premiumbox;	
	
	$case = array(
		'reserve' => array(
			'give_coldpay', 'give_realpay', 'give_verify', 'give_coldsuccess', 'give_success', 'give_delete', 'give_amlwait', 'give_amlerror', 'give_merchwait',
			'get_new', 'get_coldnew', 'get_techpay', 'get_coldpay', 'get_realpay', 'get_verify', 'get_coldsuccess', 'get_success', 'get_delete', 'get_amlwait', 'get_amlerror', 'get_merchwait',
		),
		'merch' => array('new', 'techpay'),
		'paymerch' => array('payed', 'coldpay', 'partpay', 'realpay', 'verify'),
		'cancel' => array('new', 'techpay', 'coldnew', 'amlwait', 'merchwait'),
		'payed' => array('new', 'techpay'),
		'bid_active' => array('coldnew', 'new', 'techpay', 'payed', 'coldpay', 'partpay', 'amlwait', 'amlerror', 'merchwait', 'mercherror'),
		'bid_has' => array('coldnew', 'new', 'techpay', 'payed', 'coldpay', 'partpay', 'realpay', 'verify', 'amlwait', 'amlerror', 'merchwait', 'mercherror', 'payouterror', 'scrpayerror', 'coldsuccess', 'partpayout', 'success'),
		'apbutton' => array('realpay', 'verify', 'payed', 'partpay'),
		'aptimeout' => array('realpay', 'verify', 'payed', 'partpay'),		
		'paymerchlim' => array('realpay', 'verify', 'success', 'coldsuccess'),
		'merchlim' => array('coldnew', 'new', 'techpay', 'payed', 'coldpay', 'partpay', 'realpay', 'verify', 'amlwait', 'amlerror', 'merchwait', 'mercherror', 'payouterror', 'scrpayerror', 'coldsuccess', 'partpayout', 'success'),
	);
	
	$status = is_isset($case, $place);
	if (!is_array($status)) {
		$status = array();
	}
	
	$sett = $premiumbox->get_option('statussett_' . $place);
	if (is_array($sett) and count($sett) > 0) { 
		$status = $sett;
	}
	
	if ('reserve' == $place) {
		$status[] = 'get_auto';
	}
	
	if ('bid_active' == $place or 'bid_has' == $place) {
		$status[] = 'auto';
	}	

	$in_array = intval($in_array);
	if ($in_array) {
		return $status;
	}
	
	return create_data_for_db($status, 'status');
}

add_filter('list_directions_temp', 'def_list_directions_temp', 0);
function def_list_directions_temp($list_directions_temp) {
	
	$list_directions_temp = array(
		'description_txt' => __('Exchange description', 'pn'),
		'timeline_txt' => __('Deadline', 'pn'),
		'window_txt' => __('Popup text before order creation', 'pn'),
		'frozen_txt' => __('Frozen status text', 'pn'),
		'status_auto' => sprintf(__('Status of order is "%s"', 'pn'), __('uncreated order', 'pn')),
	);
	$bid_status_list = list_bid_status();
	foreach ($bid_status_list as $key => $title) {
		$list_directions_temp['status_' . $key] = sprintf(__('Status of order is "%s"', 'pn'), $title);
	}	
							
	return $list_directions_temp;
}

function get_bid_status($status) {
	
	$bid_status_list = list_bid_status();
	$status_title = is_isset($bid_status_list, $status);
	if (!$status_title) { $status_title = $status; }
	
	return $status_title;
}

function isset_bid_status($status) {
	
	$bid_status_list = list_bid_status();
	if (isset($bid_status_list[$status])) {
		return 1;
	}
	
	return 0;
}

function get_payuot_status($status) {
	
	$statused = array(
		'0' => __('Waiting order', 'pn'),
		'1' => __('Completed order', 'pn'),
		'2' => __('Cancelled order', 'pn'),
		'3' => __('Cancelled order by user', 'pn'),
	);	
	
	return is_isset($statused, $status);
}

function pn_exchanges_output($place = '') {
	global $premiumbox;
	
	$show_data = array(
		'show' => 1,
		'work' => 1,
		'text' => '',
	);
	if (1 == $premiumbox->get_option('up_mode')) {
		$show_data = array(
			'show' => 0,
			'work' => 0,
			'text' => __('Maintenance', 'pn'),
		);		
	}
	$show_data = apply_filters('pn_exchanges_output', $show_data, $place);
	
	return $show_data;
}

function get_comis_text($com_ps, $dop_com, $psys, $curr_code, $vid, $gt) {
	$comis_text = '';
	
	if ($com_ps > 0 or $dop_com > 0) {
		$comis_text = __('Including', 'pn') . ' ';
	}		

	if ($com_ps > 0 and $dop_com > 0) {
		$comis_text .= __('add. service fee', 'pn');
		$comis_text .= ' (<span class="dop_com">' . $dop_com . '</span> <span class="vtype curr_code">' . $curr_code . '</span>)';
		$comis_text .= __(' and', 'pn');
		$comis_text .= ' ';		
		$comis_text .= __('payment system fees', 'pn');
		$comis_text .= ' <span class="psys">' . $psys . '</span> (<span class="com_ps">' . $com_ps . '</span> <span class="vtype curr_code">' . $curr_code . '</span>) ';
	} elseif ($com_ps > 0) {
		$comis_text .= __('payment system fees', 'pn');
		$comis_text .= ' <span class="psys">' . $psys . '</span> (<span class="com_ps">' . $com_ps . '</span> <span class="vtype curr_code">' . $curr_code . '</span>) ';	
	} elseif ($dop_com > 0) {
		$comis_text .= __('add. service fee', 'pn');
		$comis_text .= ' (<span class="dop_com">' . $dop_com . '</span> <span class="vtype curr_code">' . $curr_code . '</span>)';
	}	
	
	if (1 == $gt) {
		if ($com_ps > 0 or $dop_com > 0) {
			$comis_text .= ', ';
			if (1 == $vid) {
				$comis_text .= __('you send', 'pn');
			} else {
				$comis_text .= __('you receive', 'pn');
			}
		}
	}
	
	return pn_strip_input($comis_text);
}

function get_shtd_to_account($item) {
	global $premiumbox;	
	
	$account = pn_strip_input($item->to_account);
	if (strlen($account) < 1) {
		$account = pn_strip_text(ctv_ml($premiumbox->get_option('exchange', 'erroraaccount')));
		if (strlen($account) < 1) {
			$account = '---' . __('Please contact us to provide your account number', 'pn') . '---';
		}
	}
	
	return $account;
}