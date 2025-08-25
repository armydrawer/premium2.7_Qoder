<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Information about bank card[:en_US][ru_RU:]Информация о банковской карте[:ru_RU]
description: [en_US:]Information about bank card[:en_US][ru_RU:]Информация о банковской карте[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_cardinfo');
function bd_all_moduls_active_cardinfo() {
	global $wpdb;	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'card_scheme_give'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `card_scheme_give` varchar(500) NOT NULL");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'card_issuer_give'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `card_issuer_give` varchar(500) NOT NULL");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'card_country_give'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `card_country_give` varchar(250) NOT NULL");
    }	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'card_scheme_get'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `card_scheme_get` varchar(500) NOT NULL");
    } 
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'card_issuer_get'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `card_issuer_get` varchar(500) NOT NULL");
    } 
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'card_country_get'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `card_country_get` varchar(250) NOT NULL");
    } 		
	
	$table_name = $wpdb->prefix . "card_detected_memory";
	$sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`card` varchar(250) NOT NULL,
		`card_info` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`card`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;"; 
	$wpdb->query($sql);		
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'cen_give'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `cen_give` longtext NOT NULL");
    }

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE 'cen_get'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "currency ADD `cen_get` longtext NOT NULL");
    }	
	
}

add_filter('onebid_col2', 'onebid_col2_cardinfo', 10, 3);
function onebid_col2_cardinfo($nactions, $item, $v) {
	
	$actions = array();
	if ($item->card_scheme_give) {
		$actions['card_scheme_give'] = array(
			'type' => 'text',
			'title' => __('Card type', 'pn'),
			'label' => pn_strip_input($item->card_scheme_give),
		);
	}
	if ($item->card_issuer_give) {
		$actions['card_issuer_give'] = array(
			'type' => 'text',
			'title' => __('Issuer', 'pn'),
			'label' => pn_strip_input($item->card_issuer_give),
		);
	}
	if ($item->card_country_give) {
		$actions['card_country_give'] = array(
			'type' => 'text',
			'title' => __('Country', 'pn'),
			'label' => pn_strip_input($item->card_country_give),
		);	
	}
	
	return pn_array_insert($nactions, 'account_give', $actions);
}

add_filter('onebid_col3', 'onebid_col3_cardinfo', 10, 3);
function onebid_col3_cardinfo($nactions, $item, $v) {
	
	$actions = array();
	if ($item->card_scheme_get) {
		$actions['card_scheme_get'] = array(
			'type' => 'text',
			'title' => __('Card type', 'pn'),
			'label' => pn_strip_input($item->card_scheme_get),
		);
	}
	if ($item->card_issuer_get) {
		$actions['card_issuer_get'] = array(
			'type' => 'text',
			'title' => __('Issuer', 'pn'),
			'label' => pn_strip_input($item->card_issuer_get),
		);
	}
	if ($item->card_country_get) {
		$actions['card_country_get'] = array(
			'type' => 'text',
			'title' => __('Country', 'pn'),
			'label' => pn_strip_input($item->card_country_get),
		);
	}
	
	return pn_array_insert($nactions, 'account_get', $actions);
}

add_action('tab_currency_tab3', 'cardinfo_tab_currency_tab3', 60, 2);
function cardinfo_tab_currency_tab3($data, $data_id) {
	
	$form = new PremiumForm();
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Allowed names of banks (in new line) for currency I give', 'pn'); ?></span></div>
			<?php 
			$form->editor('cen_give', pn_strip_input(is_isset($data, 'cen_give')), 10, array(), 0, 0); 
			?>	
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Allowed names of banks (in new line) for currency I get', 'pn'); ?></span></div>
			<?php 
			$form->editor('cen_get', pn_strip_input(is_isset($data, 'cen_get')), 10, array(), 0, 0); 
			?>	
		</div>		
	</div>		
<?php		
}

add_filter('pn_currency_addform_post', 'cardinfo_currency_addform_post');
function cardinfo_currency_addform_post($array) {
			
	$array['cen_give'] = pn_strip_input(is_param_post('cen_give'));
	$array['cen_get'] = pn_strip_input(is_param_post('cen_get'));	
		
	return $array;
}

add_filter('error_bids', 'cardinfo_error_bids', 900, 4);  
function cardinfo_error_bids($error_bids, $direction, $vd1, $vd2) {
	global $premiumbox;
	
	$cardinfo = $premiumbox->get_option('cardinfo', 'currency');
	if (!is_array($cardinfo)) { $cardinfo = array(); }
	
	$notapi = intval($premiumbox->get_option('cardinfo', 'notapi'));
	
	$bid = is_isset($error_bids, 'bid');
	
	if (!isset($error_bids['error_fields']['account1'])) {
		$account = trim(is_isset($bid, 'account_give'));
		$currency_id = intval(is_isset($bid, 'currency_id_give'));
		if ($account) {
			$y = array();
			$n = array();
			$cen_arr = explode("\n", pn_strip_input(is_isset($vd1, 'cen_give')));
			if (is_array($cen_arr)) {
				foreach ($cen_arr as $value) {
					$value = mb_strtolower(trim($value));
					if (strlen($value) > 0) {
						$f_symbol = mb_substr($value, 0, 1);
						if ('-' == $f_symbol) {
							$n[] = trim(mb_substr($value, 1, mb_strlen($value)));
						} else {
							$y[] = $value;
						}
					}
				}
			}
			if (in_array($currency_id, $cardinfo) or count($y) > 0 or count($n) > 0) {
				$info = check_data_for_card($account);
				if ($info['scheme']) {
					$array['card_scheme_give'] = pn_strip_input($info['scheme']);
				}
				if ($info['issuer']) {
					$array['card_issuer_give'] = pn_strip_input($info['issuer']);
				}
				if ($info['country']) {
					$array['card_country_give'] = pn_strip_input($info['country']);
				}		

				$error = 0;
				if ($notapi and !$info['scheme']) {
					$error = 1;
				} 
				
				$issuer = mb_strtolower(trim($info['issuer']));
				
				if (!$error and count($n) > 0) {
					foreach ($n as $no_value) {
						if ($issuer == $no_value) {
							$error = 1;
							break;
						}
					}
				}

				if (!$error and count($y) > 0) {
					$error = 1;
					foreach ($y as $yes_value) {
						if ($issuer == $yes_value) {
							$error = 0;
							break;
						}
					}
				}				
				
				if ($error) {
					$error_text = __('the account number was not validated', 'pn');
					if (current_user_can('administrator')) {
						$error_text .= ' (' . $issuer . ')';
					}
					$error_bids['error_fields']['account1'] = $error_text;
				}
			}
		}
	}

	if (!isset($error_bids['error_fields']['account2'])) {
		$account = trim(is_isset($bid, 'account_get'));
		$currency_id = intval(is_isset($bid, 'currency_id_get'));
		if ($account) {
			$y = array();
			$n = array();
			$cen_arr = explode("\n", pn_strip_input(is_isset($vd2, 'cen_get')));
			if (is_array($cen_arr)) {
				foreach ($cen_arr as $value) {
					$value = mb_strtolower(trim($value));
					if (strlen($value) > 0) {
						$f_symbol = mb_substr($value, 0, 1);
						if ('-' == $f_symbol) {
							$n[] = trim(mb_substr($value, 1, mb_strlen($value)));
						} else {
							$y[] = $value;
						}
					}
				}
			}			
			if (in_array($currency_id, $cardinfo) or count($y) > 0 or count($n) > 0) {
				$info = check_data_for_card($account);
				if ($info['scheme']) {
					$array['card_scheme_get'] = pn_strip_input($info['scheme']);
				}
				if ($info['issuer']) {
					$array['card_issuer_get'] = pn_strip_input($info['issuer']);
				}
				if ($info['country']) {
					$array['card_country_get'] = pn_strip_input($info['country']);
				}
				
				$error = 0;
				if ($notapi and !$info['scheme']) {
					$error = 1;
				}

				$issuer = mb_strtolower(trim($info['issuer']));
				
				if (!$error and count($n) > 0) {
					foreach ($n as $no_value) {
						if ($issuer == $no_value) {
							$error = 1;
							break;
						}
					}
				}
				
				if (!$error and count($y) > 0) {
					$error = 1;
					foreach ($y as $yes_value) {
						if ($issuer == $yes_value) {
							$error = 0;
							break;
						}
					}
				}				
			
				if ($error) {
					$error_text = __('the account number was not validated', 'pn');
					if (current_user_can('administrator')) {
						$error_text .= ' (' . $issuer . ')';
					}					
					$error_bids['error_fields']['account2'] = $error_text;
				}				
			}			
		}	
	}			
	
	return $error_bids;
}

function check_data_for_card($card) {
	global $wpdb, $premiumbox;
	
	$info = array(
		'scheme' => '',
		'issuer' => '',
		'country' => '',
	);
	
	$server = intval($premiumbox->get_option('cardinfo', 'server'));
	$memory = intval($premiumbox->get_option('cardinfo', 'memory'));
	
	$key = pn_strip_input($premiumbox->get_option('cardinfo', 'key'));
	$timeout = intval($premiumbox->get_option('cardinfo', 'timeout'));
	if ($timeout < 1) { $timeout = 10; }
		
	$curl_options = array(
		CURLOPT_TIMEOUT => $timeout,
		CURLOPT_CONNECTTIMEOUT => $timeout,
	);	
	
	$card = preg_replace("/\s/", '', $card);
	$bin = mb_substr($card, 0, 6);
	
	$save_memory = 0;
	if ($memory) {
		$card_memory = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "card_detected_memory WHERE card = '$card'");
		if (isset($card_memory->id)) {
			$card_info = @unserialize($card_memory->card_info);
			$info = array(
				'scheme' => mb_strtolower(trim(is_isset($card_info, 'scheme'))),
				'issuer' => trim(is_isset($card_info, 'issuer')),
				'country' => trim(is_isset($card_info, 'country')),
			);
		} else {
			$save_memory = 1;
		}	
	}
	
	if (strlen($info['scheme']) < 1) {
		$info = apply_filters('check_data_for_card', $info, $server);
	}	
		
	if (strlen($info['scheme']) < 1) {	
		if (1 == $server) {
			$curl = get_curl_parser('https://api.bincodes.com/bin/?format=json&api_key=' . $key . '&bin=' . $bin, $curl_options, 'moduls', 'cardinfo'); 
			$string = $curl['output'];
			if (!$curl['err']) {
				$res = @json_decode($string, true);
				if (is_array($res)) {
					$info['scheme'] = mb_strtolower(is_isset($res, 'card'));
					$info['issuer'] = is_isset($res, 'bank');
					$info['country'] = is_isset($res, 'country');			
				}
			}		
		} elseif (2 == $server) {
			$curl = get_curl_parser('https://lookup.binlist.net/' . $bin, $curl_options, 'moduls', 'cardinfo');
			$string = $curl['output'];
			if (!$curl['err']) {
				$res = @json_decode($string, true);
				if (is_array($res)) {
					$info['scheme'] = mb_strtolower(is_isset($res, 'scheme'));
					if (isset($res['bank']['name'])) {
						$info['issuer'] = $res['bank']['name'];
					}
					if (isset($res['country']['name'])) {
						$info['country'] = $res['country']['name'];
					}	
				}
			}
		} elseif (3 == $server) {
			$curl_options[CURLOPT_CUSTOMREQUEST] = 'POST';
			$curl_options[CURLOPT_POSTFIELDS] = json_encode(array(
				'bin' => $bin
			));
			$curl_options[CURLOPT_HTTPHEADER] = array(
				"Content-Type: application/json",
				"x-rapidapi-host: bin-ip-checker.p.rapidapi.com",
				"x-rapidapi-key: " . $key
			);
			$curl = get_curl_parser('https://bin-ip-checker.p.rapidapi.com/?bin=' . $bin, $curl_options, 'moduls', 'cardinfo');
			$string = $curl['output'];
			if (!$curl['err']) {
				$res = @json_decode($string, true);
				if (isset($res['BIN'])) {
					if (isset($res['BIN']['scheme'])) {
						$info['scheme'] = mb_strtolower($res['BIN']['scheme']);
					}
					if (isset($res['BIN']['issuer'], $res['BIN']['issuer']['name'])) {
						$info['issuer'] = $res['BIN']['issuer']['name'];
					}
					if (isset($res['BIN']['country'], $res['BIN']['country']['name'])) {
						$info['country'] = $res['BIN']['country']['name'];
					}	
				}
			}				
		} elseif (0 == $server) {
			$info['scheme'] = mb_strtolower(card_scheme_detected($card));				
		}
	}

	$info = pn_strip_input_array($info);
	
	if (strlen($info['scheme']) > 0 and strlen($info['country']) > 0 and $save_memory) {
		$arr = array();
		$arr['card'] = $card;
		$arr['card_info'] = @serialize($info);
		$wpdb->insert($wpdb->prefix . "card_detected_memory", $arr);
	}
	
	return $info;
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'config');