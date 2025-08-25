<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Blacklist Bestchange[:en_US][ru_RU:]Черный список Bestchange[:ru_RU]
description: [en_US:]Blacklist Bestchange[:en_US][ru_RU:]Черный список Bestchange[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('pn_plugin_activate', 'all_pn_moduls_active_blacklistbest');
add_action('all_moduls_active_' . $name, 'all_pn_moduls_active_blacklistbest');
function all_pn_moduls_active_blacklistbest() {
	global $wpdb;	
	
	$query = $wpdb->query("SHOW COLUMNS FROM ". $wpdb->prefix ."exchange_bids LIKE 'pbb'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE ". $wpdb->prefix ."exchange_bids ADD `pbb` int(5) NOT NULL default '0'");
    }
	
}

add_action('admin_menu', 'admin_menu_blacklistbest');
function admin_menu_blacklistbest() {
	global $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_blacklistbest')) {
		add_submenu_page('pn_moduls', __('Blacklist Bestchange', 'pn'), __('Blacklist Bestchange', 'pn'), 'read', 'pn_blacklistbest', array($premiumbox, 'admin_temp'));  
	}
	
}

add_filter('pn_caps', 'blacklistbest_pn_caps');
function blacklistbest_pn_caps($pn_caps) {
	
	$pn_caps['pn_blacklistbest'] = __('Work with a blacklist Bestchange', 'pn');
	
	return $pn_caps;
}

add_filter('error_bids', 'blacklistbest_error_bids', 800, 2);  
function blacklistbest_error_bids($error_bids, $direction) {
	global $wpdb, $premiumbox, $pbb_error_text;

	$bb_error = 0;
	
	$comment = '';

	$checks = $premiumbox->get_option('blacklistbest', 'check');
	if (!is_array($checks)) { $checks = array(); }
	
	$method = intval($premiumbox->get_option('blacklistbest', 'method'));
	
	if (in_array(0, $checks) and !isset($error_bids['error_fields']['account1']) and 0 == $bb_error) {
		$item = is_isset($error_bids['bid'], 'account_give');
		$info = check_data_for_bestchange($item); 
		if ($info['error'] > 0) {
			if (1 != $method) {
				$error_bids['error_fields']['account1'] = __('In blacklist', 'pn');
			}
			$bb_error = 1;
			$comment .= $info['info'] . "\n";
		}	
	}		
		
	if (in_array(1, $checks) and !isset($error_bids['error_fields']['account2']) and 0 == $bb_error) {
		$item = is_isset($error_bids['bid'], 'account_get');
		$info = check_data_for_bestchange($item); 
		if ($info['error'] > 0) {
			if (1 != $method) {
				$error_bids['error_fields']['account2'] = __('In blacklist', 'pn');
			}
			$bb_error = 1;
			$comment .= $info['info'] . "\n";
		}	
	}	
	
	if (in_array(2, $checks) and 0 == $bb_error) {
		$item = str_replace('+', '', is_isset($error_bids['bid'], 'user_phone'));
		$info = check_data_for_bestchange($item);
		if ($info['error'] > 0) {
			if (1 != $method) {
				$error_bids['error_text'][] = __('Error! Your phone in blacklist', 'pn');
			}
			$bb_error = 1;
			$comment .= $info['info'] . "\n";
		}
	}

	if (in_array(3, $checks) and 0 == $bb_error) {
		$item = is_isset($error_bids['bid'], 'user_skype');
		$info = check_data_for_bestchange($item);
		if ($info['error'] > 0) {
			if (1 != $method) {
				$error_bids['error_text'][] = __('Error! Your skype in blacklist', 'pn');
			}
			$bb_error = 1;
			$comment .= $info['info'] . "\n";
		}
	}
 
	if (in_array(4, $checks) and 0 == $bb_error) {
		$item = is_isset($error_bids['bid'], 'user_email');
		$info = check_data_for_bestchange($item);
		if ($info['error'] > 0) {
			if (1 != $method) {
				$error_bids['error_text'][] = __('Error! Your e-mail in blacklist', 'pn');
			}
			$bb_error = 1;
			$comment .= $info['info'] . "\n";
		}
	}

	if (in_array(5, $checks) and 0 == $bb_error) {
		$item = is_isset($error_bids['bid'], 'user_ip');
		$info = check_data_for_bestchange($item);
		if ($info['error'] > 0) {
			if (1 != $method) {
				$error_bids['error_text'][] = __('Error! For your exchange denied', 'pn');
			}
			$bb_error = 1;
			$comment .= $info['info'] . "\n";
		}
	}
	
	if (1 == $bb_error) {
		$comment = pn_strip_text($comment);
		if ($comment) {
			$pbb_error_text = $comment;
		}
		$error_bids['bid']['pbb'] = 1;
	}	
	
	return $error_bids;
}

add_filter('change_bid_status', 'blacklist_change_bidstatus', 0);
function blacklist_change_bidstatus($data) {
	global $pbb_error_text, $wpdb;

	$place = $data['place'];
	$set_status = $data['set_status'];
	$bid = $data['bid'];
	$who = $data['who'];
	$old_status = $data['old_status'];
	$direction = $data['direction'];

	$comment = pn_strip_text($pbb_error_text);

	if (isset($bid->id) and strlen($comment) > 0 and 'auto' == $set_status) {
		
		$arr = array();
		$arr['comment_date'] = current_time('mysql');
		$arr['text_comment'] = $comment;
		$arr['itemtype'] = 'admin_bid';
		$arr['item_id'] = $bid->id;
		$wpdb->insert($wpdb->prefix . 'comment_system', $arr);
		
	}
	
	return $data;
}

add_filter('onebid_icons', 'onebid_icons_blacklist', 99, 2);
function onebid_icons_blacklist($onebid_icon, $item) {
	 
	$pbb = intval(is_isset($item,'pbb'));
	if (1 == $pbb) {
		
		$onebid_icon['pbb'] = array(
			'type' => 'text',
			'title' => __('In blacklist', 'pn'),
			'label' => '<span class="bred">'. __('In blacklist', 'pn') . '</span>',
		);		
		
	}
	
	return $onebid_icon; 
}

add_filter('autopayment_filter', 'blacklist_autopayment_filter', 10, 4); 
function blacklist_autopayment_filter($au_filter, $m_id, $item, $place) {
	global $premiumbox;

	if (0 == count($au_filter['error'])) {

		$method = intval($premiumbox->get_option('blacklistbest', 'method'));
		if (1 == $method) {
			$pbb = intval(is_isset($item, 'pbb'));
			if (1 == $pbb) {
				$au_filter['error'][] = __('In blacklist', 'pn');
			}
		}	
	
	}
	
	return $au_filter;
}

function check_data_for_bestchange($item) {
	global $wpdb, $premiumbox, $error_bccurl;
	
	if (1 != $error_bccurl) { $error_bccurl = 0; }
	
	$curlerror = intval($premiumbox->get_option('blacklistbest', 'curlerror'));	
	$info = array(
		'error' => 0,
		'info' => '',
	);
	
	if (1 != $error_bccurl) {
		
		$id = trim($premiumbox->get_option('blacklistbest', 'id'));
		$key = trim($premiumbox->get_option('blacklistbest', 'key'));
		$ctype = intval($premiumbox->get_option('blacklistbest', 'type'));
		
		$timeout = intval($premiumbox->get_option('blacklistbest', 'timeout'));
		if ($timeout < 1) { $timeout = 20; }
		
		$curl_options = array(
			CURLOPT_TIMEOUT => $timeout,
			CURLOPT_CONNECTTIMEOUT => $timeout,
		);	
		
		$type = 'sc';
		if (1 == $ctype) {
			$type = 's';	
		} elseif (2 == $ctype) {
			$type = 'c';
		}
		
		if ($id and $key and strlen($item) > 0) {
			$curl = get_curl_parser('https://www.bestchange.org/member/scamapi.php?id=' . $id . '&key=' . $key . '&where=c&type=' . $type . '&query=' . $item, $curl_options, 'moduls', 'blacklistbest');
			$string = $curl['output'];
			if (!$curl['err']) {
				$res = @simplexml_load_string($string);
				if (is_object($res)) {
					
					$info = array(
						'error' => (string)$res->request->results,
						'info' => (string)$res->response->result->desc,
					);
					
				}
			} else {
				$error_bccurl = 1;
				if ($curlerror) {
					$info = array(
						'error' => 1,
						'info' => 'Curl error',
					);		
				}
			}		
		}
		
	}
	
	return $info;
}

add_filter('pn_pp_adminform', 'bbblacklist_pn_pp_options');
function bbblacklist_pn_pp_options($options) {
	global $premiumbox;	
	
	$options['payoutblcheckbb'] = array(
		'view' => 'select',
		'title' => __('Check user details in blacklists when requesting affiliate rewards', 'pn') . ' (bestchange)',
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('partners', 'payoutblcheckbb'),
		'name' => 'payoutblcheckbb',
	);	
	
	return $options;
}

add_action('pn_pp_adminform_post', 'bbblacklist_pn_pp_options_post');
function bbblacklist_pn_pp_options_post() {
	global $premiumbox;
	
	$premiumbox->update_option('partners', 'payoutblcheckbb', intval(is_param_post('payoutblcheckbb')));
}

add_filter('item_user_payouts_add_before', 'bbblacklist_item_user_payouts_add_before', 10, 2);
function bbblacklist_item_user_payouts_add_before($res, $arr) {
	global $wpdb, $premiumbox;	
	 
	if (1 == $res['ind'] and !_is('is_adminaction')) {
		$check = intval($premiumbox->get_option('partners', 'payoutblcheckbb'));
		if ($check) {		
			$account = pn_strip_input($arr['pay_account']);
			$info = check_data_for_bestchange($account);
			if ($info['error'] > 0) {
				$res['ind'] = 0;
				$res['error'] = __('Error! Your account in blacklist. Contact us', 'pn');
			}
		}	
	}
	
	return $res;
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'config');