<?php
if (!defined('ABSPATH')) { exit(); }

function get_expfile_url($type, $ext, $vers = 0, $lang = '') {
	global $premiumbox;

	$filehash = pn_strip_symbols(replace_cyr($premiumbox->get_option('txtxml', 'filehash')));
	$lang = trim($lang);
	$vers = intval($vers);
	$url = get_request_link('export' . $type, $ext);
	$data = array();
	if ($filehash) {
		$data['hcron'] = $filehash;
	}
	if ($vers) {
		$data['vers'] = $vers;
	}
	if ($lang) {
		$data['lang'] = $lang;
	}
	$url = add_query_args($data, $url);

	return $url;
}

function check_hash_file() {
	global $premiumbox;

	$filehash = pn_strip_symbols(replace_cyr($premiumbox->get_option('txtxml', 'filehash')));
	$now_hash = trim(is_param_get('hcron'));
	if ($filehash and $filehash != $now_hash) {
		return 0;
	}

	return 1;
}

function txtxml_create_error($text, $type) {

	echo get_txtxml_create_error($text, $type);
	exit;
}

function get_txtxml_create_error($text, $type) {

	$error = '';
	if ('xml' == $type) {
		$error .= '<?xml version="1.0" encoding="utf-8"?>' . "\n";
		$error .= '<error>'. $text .'</error>';
	} elseif ('json' == $type) {
		$error .= pn_json_encode(array('error', $text));
	} else {
		$error .= $text;
	}

	return $error;
}

function get_xml_value_vers($arr, $vers = 0) {

	$def = trim(is_isset($arr, 0));
	$now = trim(is_isset($arr, $vers));
	if (strlen($now) < 2) {
		return $def;
	}

	return $now;
}

function json_commission($com) {

	$com_arr = explode(',', $com);
	$perc = 0;
	$sum = 0;
	foreach ($com_arr as $c) {
		if (strstr($c, '%')) {
			$perc = preg_replace("/[^0-9\.]/", '', $c);
			$perc = is_sum($perc);
		} else {
			$sum = preg_replace("/[^0-9\.]/", '', $c);
			$sum = is_sum($sum);
		}
	}
	if ($perc > 0 and $sum > 0) {
		return array('+', array('%' => $perc), $sum);
	} elseif($perc > 0) {
		return array('%' => $perc);
	} elseif($sum > 0) {
		return $sum;
	}

	return 0;
}

function line_replace_word($currency, $line_value) {

	$s_line = explode(',', $line_value);
	$ns_line = array();
	foreach ($s_line as $s_lin) {
		$s_line_arr = explode(' ', $s_lin);
		$new_line = array();
		foreach ($s_line_arr as $s_l) {
			if ($s_l != $currency) {
				$new_line[] = $s_l;
			}
		}
		$ns_line[] = implode(' ', $new_line);
	}

	return trim(implode(',', $ns_line));
}

add_action('premium_request_exportjson', 'def_premium_request_exportjson');
function def_premium_request_exportjson() {
	global $premiumbox;

	header('Content-Type: application/json; charset=' . get_charset());

	if (1 == $premiumbox->get_option('up_mode')) {
		txtxml_create_error(__('Maintenance','pn'), 'json');
	}

	if (!check_hash_file()) {
		txtxml_create_error(__('Maintenance', 'pn'), 'json');
	}

	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml', 'json'), 'json');
	if (1 != $show_files) {
		txtxml_create_error(__('Maintenance', 'pn'), 'json');
	}

	$show_data = pn_exchanges_output('files');
	if (1 != $show_data['show']) {
		txtxml_create_error(__('Maintenance', 'pn'), 'json');
	}

	if (1 != $premiumbox->get_option('txtxml', 'create')) {
		txtxml_create_bd(1);
	}

	$create_time = get_option('txtxml_create_time');
	pn_header_lastmodifier($create_time);

	$directions = get_array_option($premiumbox, 'pn_directions_filedata');
	if (!is_array($directions)) { $directions = array(); }

	$vers = intval(is_param_get('vers'));

	$json = array(
		'version' => '1.3'
	);
	$currencies = array();
	$exchange = array();
	$amounts = array();
	foreach ($directions as $direction) {
		$cid1 = is_isset($direction, 'cid1');
		$cid2 = is_isset($direction, 'cid2');
		$currencies[$cid1] = get_xml_value_vers(is_isset($direction, 'from'), $vers);
		$currencies[$cid2] = get_xml_value_vers(is_isset($direction, 'to'), $vers);
		$c1 = $direction['in'];
		$c2 = $direction['out'];
		$course = 'no';
		if ($c1 > $c2 and $c2 > 0) {
			$course = $c1 / $c2;
			$course = is_sum($course, $direction['d1']);
		} elseif ($c1 > 0) {
			$course = $c2 / $c1;
			if ($course != 1) { $course = -1 * $course; }
			$course = is_sum($course, $direction['d2']);
		}
		if ('no' != $course) {
			$data = array(
				'xr' => $course,
				'amount' => $direction['amount'],
			);
			$amounts[$cid2] = $direction['amount'];
			$min = preg_replace("/[^0-9\.]/", '', is_isset($direction, 'minamount'));
			$min = is_sum($min);
			if ($min) {
				$data['min'] = $min;
			}
			$max = preg_replace("/[^0-9\.]/", '', is_isset($direction, 'maxamount'));
			$max = is_sum($max);
			if ($max) {
				$data['max'] = $max;
			}
			$inFee = json_commission(is_isset($direction, 'fromfee'));
			if ($inFee) {
				$data['inFee'] = $inFee;
			}
			$outFee = json_commission(is_isset($direction, 'tofee'));
			if ($outFee) {
				$data['outFee'] = $outFee;
			}

			$options = array();
			$param = trim(is_isset($direction, 'param'));
			if (strstr($param, 'manual')) {
				$options['manual'] = 1;
			}
			if (strstr($param, 'auth')) {
				$options['auth'] = 1;
			}
			if (strstr($param, 'verifying')) {
				$options['ident'] = 1;
			}
			if (count($options) > 0) {
				$data['options'] = $options;
			}

			$city = trim(is_isset($direction, 'city'));
			if (isset($exchange[$cid1]['to'][$cid2])) {
				if ($city) {
					$exchange[$cid1]['to'][$cid2]['cities'][] = $city;
				}
			} else {
				if ($city) {
					$data['cities'][] = $city;
				}
				$exchange[$cid1]['to'][$cid2] = $data;
			}
		}
	}
	ksort($currencies);
	$json['currencies']['list'] = $currencies;
	ksort($amounts);
	$json['currencies']['amounts'] = $amounts;
	$json['exchange'] = $exchange;

	echo pn_json_encode($json);
	exit;
}

add_action('premium_request_exporttxt','def_premium_request_exporttxt');
function def_premium_request_exporttxt() {
	global $premiumbox;

	header("Content-type: text/txt; charset=" . get_charset());

	if (1 == $premiumbox->get_option('up_mode')) {
		txtxml_create_error(__('Maintenance', 'pn'), 'txt');
	}

	if (!check_hash_file()) {
		txtxml_create_error(__('Maintenance', 'pn'), 'txt');
	}

	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml', 'txt'), 'txt');
	if (1 != $show_files) {
		txtxml_create_error(__('Maintenance', 'pn'), 'txt');
	}

	$show_data = pn_exchanges_output('files');
	if (1 != $show_data['show']) {
		txtxml_create_error(__('Maintenance', 'pn'), 'txt');
	}

	if (1 != $premiumbox->get_option('txtxml', 'create')) {
		txtxml_create_bd(1);
	}

	$create_time = get_option('txtxml_create_time');
	pn_header_lastmodifier($create_time);

	$directions = get_array_option($premiumbox, 'pn_directions_filedata');
	if (!is_array($directions)) { $directions = array(); }

	$vers = intval(is_param_get('vers'));

	foreach ($directions as $direction) {

		echo get_xml_value_vers(is_isset($direction, 'from'), $vers) . ';' . get_xml_value_vers(is_isset($direction, 'to'), $vers) . ';' . is_isset($direction, 'in') . ';' . is_isset($direction, 'out') . ';' . is_isset($direction, 'amount') . ';' . is_isset($direction, 'city') . ";\n";
	}

	exit;
}

add_action('premium_request_exportxml', 'def_premium_request_exportxml');
function def_premium_request_exportxml() {
	global $premiumbox;

	header("Content-Type: text/xml; charset=" . get_charset());

	if (1 == $premiumbox->get_option('up_mode')) {
		txtxml_create_error(__('Maintenance', 'pn'), 'xml');
	}

	if (!check_hash_file()) {
		txtxml_create_error(__('Maintenance', 'pn'), 'xml');
	}

	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml', 'xml'), 'xml');
	if (1 != $show_files) {
		txtxml_create_error(__('Maintenance', 'pn'), 'xml');
	}

	$show_data = pn_exchanges_output('files');
	if (1 != $show_data['show']) {
		txtxml_create_error(__('Maintenance', 'pn'), 'xml');
	}

	if (1 != $premiumbox->get_option('txtxml', 'create')) {
		txtxml_create_bd(1);
	}

	$create_time = get_option('txtxml_create_time');
	pn_header_lastmodifier($create_time);

	$vers = intval(is_param_get('vers'));

	$directions = get_array_option($premiumbox, 'pn_directions_filedata');
	if (!is_array($directions)) { $directions = array(); }

 	echo '<?xml version="1.0" encoding="' . get_charset() . '"?>' . "\n";
	?>
	<rates xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://docs.bestchange.biz/schema/1.0.xsd">
	<?php
 	$exclude_currency = intval($premiumbox->get_option('txtxml', 'exclude_currency_give'));
	foreach ($directions as $direction) {
		$currency_give = is_isset($direction, 'c1');
		$currency_get = is_isset($direction, 'c2');
		$min = 'no';
		if (isset($direction['minamount'])) {
			$min = $direction['minamount'];
			if ($exclude_currency) {
				$min = line_replace_word($currency_give, $min);
			}
		}
		$max = 'no';
		if (isset($direction['maxamount'])) {
			$max = $direction['maxamount'];
			if ($exclude_currency) {
				$max = line_replace_word($currency_give, $max);
			}
		}
		$minfee = 'no';
		if (isset($direction['minfee'])) {
			$minfee = $direction['minfee'];
			if ($exclude_currency) {
				$minfee = line_replace_word($currency_give, $minfee);
			}
		}
		$fromfee = 'no';
		if (isset($direction['fromfee'])) {
			$fromfee = $direction['fromfee'];
			if ($exclude_currency) {
				$fromfee = line_replace_word($currency_give, $fromfee);
			}
		}
		$tofee = 'no';
		if (isset($direction['tofee'])) {
			$tofee = $direction['tofee'];
			if ($exclude_currency) {
				$tofee = line_replace_word($currency_get, $tofee);
			}
		}
		$floating = 'no';
		if (isset($direction['floating'])) {
			$floating = $direction['floating'];
		}
		$delay = 'no';
		if (isset($direction['delay'])) {
			$delay = $direction['delay'];
		}
		$param = 'no';
		if (isset($direction['param'])) {
			$param = $direction['param'];
		}
		$city = 'no';
		if (isset($direction['city'])) {
			$city = $direction['city'];
		}
		?>
		<item>
			<from><?php echo get_xml_value_vers(is_isset($direction, 'from'), $vers); ?></from>
			<to><?php echo get_xml_value_vers(is_isset($direction, 'to'), $vers); ?></to>
			<in><?php echo is_isset($direction, 'in'); ?></in>
			<out><?php echo is_isset($direction, 'out'); ?></out>
			<amount><?php echo is_isset($direction, 'amount'); ?></amount>
			<?php if ('no' != $min) { ?><minamount><?php echo $min; ?></minamount><?php } ?>
			<?php if ('no' != $max) { ?><maxamount><?php echo $max; ?></maxamount><?php } ?>
			<?php if ('no' != $minfee) { ?><minfee><?php echo $minfee; ?></minfee><?php } ?>
			<?php if ('no' != $fromfee) { ?><fromfee><?php echo $fromfee; ?></fromfee><?php } ?>
			<?php if ('no' != $tofee) { ?><tofee><?php echo $tofee; ?></tofee><?php } ?>
			<?php if ('no' != $floating) { ?><floating><?php echo $floating; ?></floating><?php } ?>
			<?php if ('no' != $delay) { ?><delay><?php echo $delay; ?></delay><?php } ?>
			<?php if ('no' != $param) { ?><param><?php echo $param; ?></param><?php } ?>
			<?php if ('no' != $city) { ?><city><?php echo $city; ?></city><?php } ?>
			<?php do_action('item_xml_line', $direction, 'old'); ?>
		</item>
		<?php
	}
	?>
	</rates>
	<?php
	exit;
}

add_action('premium_request_exportnewxml', 'def_premium_request_exportnewxml');
function def_premium_request_exportnewxml() {
	global $premiumbox;

	header("Content-Type: text/xml; charset=" . get_charset());

	if (1 == $premiumbox->get_option('up_mode')) {
		txtxml_create_error(__('Maintenance', 'pn'), 'xml');
	}

	if (!check_hash_file()) {
		txtxml_create_error(__('Maintenance', 'pn'), 'xml');
	}

	$show_files = apply_filters('show_txtxml_files', $premiumbox->get_option('txtxml', 'xml'), 'newxml');
	if (1 != $show_files) {
		txtxml_create_error(__('Maintenance', 'pn'), 'xml');
	}

	$show_data = pn_exchanges_output('files');
	if (1 != $show_data['show']) {
		txtxml_create_error(__('Maintenance', 'pn'), 'xml');
	}

	if (1 != $premiumbox->get_option('txtxml', 'create')) {
		txtxml_create_bd(1);
	}

	$create_time = get_option('txtxml_create_time');
	pn_header_lastmodifier($create_time);

	$vers = intval(is_param_get('vers'));

	$directions = get_array_option($premiumbox, 'pn_directions_filedata');
	if (!is_array($directions)) { $directions = array(); }

 	echo '<?xml version="1.0" encoding="' . get_charset() . '"?>' . "\n";
	?>
	<rates xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://docs.bestchange.biz/schema/1.1.xsd">
	<?php
 	$exclude_currency = intval($premiumbox->get_option('txtxml','exclude_currency_give'));
	foreach ($directions as $direction) {
		$currency_give = is_isset($direction, 'c1');
		$currency_get = is_isset($direction, 'c2');
		$min = 'no';
		if (isset($direction['minamount'])) {
			$min = $direction['minamount'];
			if ($exclude_currency) {
				$min = line_replace_word($currency_give, $min);
			}
		}
		$max = 'no';
		if (isset($direction['maxamount'])) {
			$max = $direction['maxamount'];
			if ($exclude_currency) {
				$max = line_replace_word($currency_give, $max);
			}
		}
		$fromfee = '';
		if (isset($direction['fromfee'])) {
			$fromfee = $direction['fromfee'];
			$fromfee = line_replace_word($currency_give, $fromfee);
		}
		$fromfee_arr = explode(',', $fromfee);

		$tofee = '';
		if (isset($direction['tofee'])) {
			$tofee = $direction['tofee'];
			$tofee = line_replace_word($currency_get, $tofee);
		}
		$tofee_arr = explode(',', $tofee);

		$floating = array();
		$floating_arr = array();
		if (isset($direction['floating'])) {
			$floating_arr = explode(',', $direction['floating']);
		}
		foreach ($floating_arr as $fl) {
			if (strstr($fl, '%')) {
				$floating[] = 'percent="' . trim(str_replace('%', '', $fl)) . '"';
			} else {
				$fl = intval($fl);
				if ($fl) {
					$floating[] = 'minutes="' . $fl . '"';
				}
			}
		}

		$delay = 'no';
		if (isset($direction['delay'])) {
			$delay = $direction['delay'];
		}

		$param = array();
		if (isset($direction['param'])) {
			$param = explode(',', $direction['param']);
		}
		$city = 'no';
		if (isset($direction['city'])) {
			$city = $direction['city'];
		}
		?>
		<item>
			<from><?php echo get_xml_value_vers(is_isset($direction, 'from'), $vers); ?></from>
			<to><?php echo get_xml_value_vers(is_isset($direction, 'to'), $vers); ?></to>
			<in><?php echo is_isset($direction, 'in'); ?></in>
			<out><?php echo is_isset($direction, 'out'); ?></out>
			<amount><?php echo is_isset($direction, 'amount'); ?></amount>
			<?php if ('no' != $min) { ?><frommin><?php echo $min; ?></frommin><?php } ?>
			<?php if ('no' != $max) { ?><frommax><?php echo $max; ?></frommax><?php } ?>
			<?php foreach ($fromfee_arr as $d) { $d = trim($d); if ($d) { $type = 'abs'; if (strstr($d, '%')) { $type = '%'; } ?><fromfee type="<?php echo $type; ?>"><?php echo trim(str_replace('%', '', $d)); ?></fromfee><?php }} ?>
			<?php foreach ($tofee_arr as $d) { $d = trim($d); if ($d) { $type = 'abs'; if (strstr($d, '%')) { $type = '%'; } ?><tofee type="<?php echo $type; ?>"><?php echo trim(str_replace('%', '', $d)); ?></tofee><?php }} ?>
			<?php if (is_array($floating) and count($floating) > 0) { ?><floating <?php echo implode(' ', $floating); ?> /><?php } ?>
			<?php if ('no' != $delay) { ?><delay><?php echo $delay; ?></delay><?php } ?>
			<?php foreach ($param as $d) { $d = trim($d); if ($d) { ?><<?php echo $d; ?>>true</<?php echo $d; ?>><?php }} ?>
			<?php if ('no' != $city) { ?><city><?php echo $city; ?></city><?php } ?>
			<?php do_action('item_xml_line', $direction, 'new'); ?>
		</item>
		<?php
	}
	?>
	</rates>
	<?php
	exit;
}