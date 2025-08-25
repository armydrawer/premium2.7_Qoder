<?php
if (!defined('ABSPATH')) { exit(); }

function WMXI_X19() {
	global $premiumbox;
		
	$object = array();

	$type = intval($premiumbox->get_option('x19', 'type'));
	if (0 == $type) {
		if (defined('WMX19_KEEPER_TYPE')) {
			$object = new WMXI($premiumbox->plugin_dir . '/moduls/x19/classed/wmxi.crt', get_charset());
			if ('CLASSIC' == WMX19_KEEPER_TYPE) {
				if (defined('WMX19_ID') and defined('WMX19_CLASSIC_KEYPASS') and defined('WMX19_CLASSIC_KEYPATH')) {
					$object->Classic(WMX19_ID, array('pass' => WMX19_CLASSIC_KEYPASS, 'file' => str_replace('{DIR_PATH}', $premiumbox->plugin_dir . '/', WMX19_CLASSIC_KEYPATH)));
				}
			} else {
				if (defined('WMX19_LIGHT_KEYPATH') and defined('WMX19_LIGHT_CERTPATH') and defined('WMX19_LIGHT_KEYPASS')) {
					$object->Light(array('key' => str_replace('{DIR_PATH}', $premiumbox->plugin_dir . '/', WMX19_LIGHT_KEYPATH), 'cer' => str_replace('{DIR_PATH}', $premiumbox->plugin_dir . '/', WMX19_LIGHT_CERTPATH), 'pass' => WMX19_LIGHT_KEYPASS ));
				}
			}
		}
	} elseif (1 == $type) {
		$object = new WMXI($premiumbox->plugin_dir . '/moduls/x19/classed/wmxi.crt', get_charset());
		$wmid = pn_strip_input($premiumbox->get_option('x19', 'wmid'));
		$clkey = pn_strip_input($premiumbox->get_option('x19', 'clkey'));
		$clpass = pn_strip_input(premium_decrypt($premiumbox->get_option('x19', 'clpass'), EXT_SALT));
		if ($wmid and $clkey and $clpass) {
			$object->Classic($wmid, array('pass' => $clpass, 'file' => str_replace('{DIR_PATH}', $premiumbox->plugin_dir . '/', $clkey)));
		}
	} elseif (2 == $type) {
		$object = new WMXI($premiumbox->plugin_dir . '/moduls/x19/classed/wmxi.crt', get_charset());
		$ltcert = pn_strip_input($premiumbox->get_option('x19', 'ltcert'));
		$ltkey = pn_strip_input($premiumbox->get_option('x19', 'ltkey'));
		$ltpass = pn_strip_input(premium_decrypt($premiumbox->get_option('x19', 'ltpass'), EXT_SALT));
		if ($ltcert and $ltkey and $ltpass) {
			$object->Light(array('key' => str_replace('{DIR_PATH}', $premiumbox->plugin_dir . '/', $ltkey), 'cer' => str_replace('{DIR_PATH}', $premiumbox->plugin_dir . '/', $ltcert), 'pass' => $ltpass));
		}
	}

	return $object;
}

function x19_info_for_wm($wm) {
	
	$arr = array();
	$arr['err'] = 0;
	$arr['wmid'] = '';
	$curl_options = array(
		CURLOPT_TIMEOUT => 20,
		CURLOPT_CONNECTTIMEOUT => 20,
	);	
	$result = get_curl_parser('https://passport.webmoney.ru/asp/CertView.asp?purse=' . $wm, $curl_options, 'moduls', 'x19');
	if (!$result['err']) {
		$out = $result['output'];
		if (strstr($out, 'Object moved')) {
			$arr['err'] = 1;
		} else {
			$urlwmid = '';
			if (preg_match('/WebMoney.Events" href="(.*?)">/s',$out, $item)) {
				$urlwmid = trim($item[1]);
			}
			$wmid = explode('?', $urlwmid);
			$wmid = trim(is_isset($wmid, 1));
			if ($wmid) {
				$arr['wmid'] = $wmid;
			} else {
				$arr['err'] = 1;	
			}
		}
	} else {
		$arr['err'] = 1;
	}		
	
	return $arr;
}

function wmid_with_purse($object, $purse) {
	
	$res = $object->X8('', $purse)->toArray();
	$retval = intval(is_isset($res, 'retval'));
	$darr = array('wmid' => '', 'result' => print_r($res, true));
	if (1 == $retval and isset($res['testwmpurse']['wmid'])) {
		if (isset($res['testwmpurse']['wmid']['@attributes'])) {
			$darr['wmid'] = pn_maxf_mb(pn_strip_input($res['testwmpurse']['wmid'][0]), 250);
		} else {
			$darr['wmid'] = pn_maxf_mb(pn_strip_input($res['testwmpurse']['wmid']), 250);
		}
	}
	
	return $darr;
}

function x19_create_log($dir_id, $log_text) {
	global $premiumbox, $wpdb;
	
	$log = intval($premiumbox->get_option('x19', 'logs'));
	if ($log) {

		$arr = array();
		$arr['create_date'] = current_time('mysql');
		$arr['dir_id'] = $dir_id;
		$arr['error_text'] = pn_strip_input($log_text);
		$wpdb->insert($wpdb->prefix . "x19_logs", $arr);
	
	}
}

function ind_x19() {
	
	$arr = array(6, 7, 8, 9, 15, 16, 17, 18, 19, 23, 25, 20, 21, 26, 27);
	
	return $arr;
}

function info_x19($x19mod, $bank_name1, $bank_name2, $account1, $account2) {
	
	$arr = array(
		'type' => '',
		'dir' => '',
		'bank_name' => '',
		'bank_account' => '',
		'card_number' => '',
		'emoney_name' => '',
		'emoney_id' => '',
		'phone' => '',
		'crypto_name' => '',
		'crypto_address' => '',	
	);	
	
	if (1 == $x19mod) { /* Наличные в офисе -> WM */
		$arr['type'] = 1;
		$arr['dir'] = 2;
	} elseif (2 == $x19mod) { /* Банковский счет -> WM */ 
		$arr['type'] = 3;
		$arr['dir'] = 2;						
		$arr['bank_name'] = $bank_name1;
		$arr['bank_account'] = $account1;					
	} elseif (3 == $x19mod) { /* Банковская карта -> WM */ 
		$arr['type'] = 4;
		$arr['dir'] = 2;						
		$arr['bank_name'] = $bank_name1;
		$arr['card_number'] = $account1;					
	} elseif (4 == $x19mod) { /* Системы денежных переводов -> WM */
		$arr['type'] = 2;
		$arr['dir'] = 2;					
	} elseif (5 == $x19mod) { /* SMS -> WM */
		$arr['type'] = 6;
		$arr['dir'] = 2;
		$arr['phone'] = $account1;	
	} elseif (6 == $x19mod) { /* WM -> Наличные в офисе */
		$arr['type'] = 1;
		$arr['dir'] = 1;
	} elseif (7 == $x19mod) { /* WM -> Банковский счет */
		$arr['type'] = 3;
		$arr['dir'] = 1;
		$arr['bank_name'] = $bank_name2;
		$arr['bank_account'] = $account2;					
	} elseif (8 == $x19mod) { /* WM -> Банковская карта */
		$arr['type'] = 4;
		$arr['dir'] = 1;
		$arr['bank_name'] = $bank_name2;
		$arr['card_number'] = $account2;					
	} elseif (9 == $x19mod) { /* WM -> Системы денежных переводов */
		$arr['type'] = 2;
		$arr['dir'] = 1;					
	} elseif (10 == $x19mod) { /* PayPal -> WM */
		$arr['type'] = 5;
		$arr['dir'] = 2; 
		$arr['emoney_name'] = 'paypal';
		$arr['emoney_id'] = $account1;					
	} elseif (11 == $x19mod) { /* Skrill (Moneybookers) -> WM */
		$arr['type'] = 5;
		$arr['dir'] = 2; 
		$arr['emoney_name'] = 'skrill';
		$arr['emoney_id'] = $account1;					
	} elseif (12 == $x19mod) { /* QIWI Кошелёк -> WM */
		$arr['type'] = 5;
		$arr['dir'] = 2; 
		$arr['emoney_name'] = 'qiwi';
		$arr['emoney_id'] = $account1;					
	} elseif (13 == $x19mod) { /* Яндекс.Деньги -> WM */
		$arr['type'] = 5;
		$arr['dir'] = 2; 
		$arr['emoney_name'] = 'yoomoney';
		$arr['emoney_id'] = $account1;					
	} elseif (15 == $x19mod) { /* WM -> PayPal */
		$arr['type'] = 5;
		$arr['dir'] = 1; 
		$arr['emoney_name'] = 'paypal';
		$arr['emoney_id'] = $account2;					
	} elseif (16 == $x19mod) { /* WM -> Skrill (Moneybookers) */
		$arr['type'] = 5;
		$arr['dir'] = 1; 
		$arr['emoney_name'] = 'skrill';
		$arr['emoney_id'] = $account2;					
	} elseif (17 == $x19mod) { /* WM -> QIWI Кошелёк */
		$arr['type'] = 5;
		$arr['dir'] = 1; 
		$arr['emoney_name'] = 'qiwi';
		$arr['emoney_id'] = $account2;					
	} elseif (18 == $x19mod) { /* WM -> Яндекс.Деньги */
		$arr['type'] = 5;
		$arr['dir'] = 1; 
		$arr['emoney_name'] = 'yoomoney';
		$arr['emoney_id'] = $account2;	
	} elseif (19 == $x19mod) { /* WM -> advcash */
		$arr['type'] = 5;
		$arr['dir'] = 1; 
		$arr['emoney_name'] = 'advcash';
		$arr['emoney_id'] = $account2;
	} elseif (14 == $x19mod) { /* advcash -> WM */
		$arr['type'] = 5;
		$arr['dir'] = 2; 
		$arr['emoney_name'] = 'advcash';
		$arr['emoney_id'] = $account1;						
	} elseif (23 == $x19mod) { /* WM -> perfectmoney */
		$arr['type'] = 5;
		$arr['dir'] = 1; 
		$arr['emoney_name'] = 'perfectmoney';
		$arr['emoney_id'] = $account2;
	} elseif (22 == $x19mod) { /* perfectmoney -> WM */
		$arr['type'] = 5;
		$arr['dir'] = 2; 
		$arr['emoney_name'] = 'perfectmoney';
		$arr['emoney_id'] = $account1;
	} elseif (25 == $x19mod) { /* WM -> payeer */
		$arr['type'] = 5;
		$arr['dir'] = 1; 
		$arr['emoney_name'] = 'payeer';
		$arr['emoney_id'] = $account2;
	} elseif (24 == $x19mod) { /* payeer -> WM */
		$arr['type'] = 5;
		$arr['dir'] = 2; 
		$arr['emoney_name'] = 'payeer';
		$arr['emoney_id'] = $account1;
	} elseif (21 == $x19mod) { /* WM -> Bitcoin */
		$arr['type'] = 8;
		$arr['dir'] = 1; 
		$arr['crypto_name'] = 'BTC';
		$arr['crypto_address'] = $account2;
	} elseif (26 == $x19mod) { /* WM -> USDT */
		$arr['type'] = 8;
		$arr['dir'] = 1; 
		$arr['crypto_name'] = 'USDT';
		$arr['crypto_address'] = $account2;
	} elseif (27 == $x19mod) { /* WM -> ETH */
		$arr['type'] = 8;
		$arr['dir'] = 1; 
		$arr['crypto_name'] = 'ETH';
		$arr['crypto_address'] = $account2;
	}	
	
	return $arr;
}

function list_x19() {
	
	$array = array(
		'0' => '--' . __('No', 'pn') . '--',
		'1' => __('Cash', 'pn') . ' -> ' . 'Webmoney',
		'2' => __('Bank account', 'pn') . ' -> ' . 'Webmoney',
		'3' => __('Bank card', 'pn') . ' -> ' . 'Webmoney',
		'4' => __('Money transfer system', 'pn') . ' -> ' . 'Webmoney',
		'5' => __('SMS', 'pn') . ' -> ' . 'Webmoney',
		'6' => 'Webmoney' . ' -> ' . __('Cash', 'pn'),
		'7' => 'Webmoney' . ' -> ' . __('Bank account', 'pn'),
		'8' => 'Webmoney' . ' -> ' . __('Bank card', 'pn'),
		'9' => 'Webmoney' . ' -> ' . __('Money transfer system', 'pn'),
		'10' => 'PayPal' . ' -> ' . 'Webmoney',
		'11' => 'Skrill (Moneybookers)' . ' -> ' . 'Webmoney',
		'12' => 'QIWI' . ' -> ' . 'Webmoney',
		'13' => 'YooMoney' . ' -> ' . 'Webmoney',
		'14' => 'AdvCash' . ' -> ' . 'Webmoney',
		'22' => 'PerfectMoney' . ' -> ' . 'Webmoney',
		'24' => 'Payeer' . ' -> ' . 'Webmoney',
		'15' => 'Webmoney' . ' -> ' . 'PayPal',
		'16' => 'Webmoney' . ' -> ' . 'Skrill (Moneybookers)',
		'17' => 'Webmoney' . ' -> ' . 'QIWI',
		'18' => 'Webmoney' . ' -> ' . 'YooMoney',
		'19' => 'Webmoney' . ' -> ' . 'AdvCash',
		'23' => 'Webmoney' . ' -> ' . 'PerfectMoney',
		'25' => 'Webmoney' . ' -> ' . 'Payeer',
		'20' => 'Webmoney' . ' -> ' . 'Webmoney',
		'21' => 'Webmoney' . ' -> ' . 'Bitcoin',
		'26' => 'Webmoney' . ' -> ' . 'USDT',
		'27' => 'Webmoney' . ' -> ' . 'ETH',			
	);	
	
	return $array;
}