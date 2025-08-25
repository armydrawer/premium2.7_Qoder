<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Proxy for merchants[:en_US][ru_RU:]Прокси для мерчантов[:ru_RU]
description: [en_US:]Proxy for merchants[:en_US][ru_RU:]Прокси для мерчантов[:ru_RU]
version: 2.7.0
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
dependent: -
*/

add_action('_paymerchants_options', 'proxy_get_merchants_options', 100, 5);
add_action('_merchants_options', 'proxy_get_merchants_options', 100, 5);
add_action('_tradeapi_options', 'proxy_get_merchants_options', 100, 5);
function proxy_get_merchants_options($options, $name, $data, $id, $place) {
	
	$options['proxy_title'] = array(
		'view' => 'h3',
		'title' => __('Proxy settings', 'pn'),
		'submit' => __('Save', 'pn'),
	);		
	$options['proxy_ip'] = array(
		'view' => 'inputbig',
		'title' => __('IP address', 'pn'),
		'default' => is_isset($data, 'proxy_ip'),
		'name' => 'proxy_ip',
		'work' => 'input',
	);	
	$options['proxy_port'] = array(
		'view' => 'inputbig',
		'title' => __('Port', 'pn'),
		'default' => is_isset($data, 'proxy_port'),
		'name' => 'proxy_port',
		'work' => 'input',
	);
	$options['proxy_login'] = array(
		'view' => 'inputbig',
		'title' => __('Login', 'pn'),
		'default' => is_isset($data, 'proxy_login'),
		'name' => 'proxy_login',
		'work' => 'input',
	);
	$options['proxy_password'] = array(
		'view' => 'inputbig',
		'title' => __('Password', 'pn'),
		'default' => is_isset($data, 'proxy_password'),
		'name' => 'proxy_password',
		'work' => 'input',
	);
	$options['proxy_tunnel'] = array(
		'view' => 'select',
		'title' => __('Disable proxy tunnel', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => is_isset($data, 'proxy_tunnel'),
		'name' => 'proxy_tunnel',
		'work' => 'int',
	);		
		
	return $options;
}

add_filter('curl_merch', 'proxy_merch_curl_parser', 10, 3);
function proxy_merch_curl_parser($ch, $m_name, $m_id) {
	
	$m_data = get_merch_data($m_id);
	
	return m_curl_proxy($ch, $m_data);
}

add_filter('curl_ap', 'proxy_ap_curl_parser', 10, 3);
function proxy_ap_curl_parser($ch, $m_name, $m_id) {
	
	$m_data = get_paymerch_data($m_id);
	
	return m_curl_proxy($ch, $m_data);
}

add_filter('curl_trade', 'proxy_trade_curl_parser', 10, 3);
function proxy_trade_curl_parser($ch, $m_name, $m_id) {
	
	$m_data = get_tradeapi_data($m_id);
	
	return m_curl_proxy($ch, $m_data);
}

function m_curl_proxy($ch, $m_data) {
	
	$ip = trim(is_isset($m_data, 'proxy_ip'));
	$port = trim(is_isset($m_data, 'proxy_port'));
	$login = trim(is_isset($m_data, 'proxy_login'));
	$password = trim(is_isset($m_data, 'proxy_password'));	
	$tunnel = intval(is_isset($m_data, 'proxy_tunnel'));
		
	if ($ip and $port) {
		
		if ($tunnel) {
			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
		}
		
		curl_setopt($ch, CURLOPT_PROXY, $ip);
		curl_setopt($ch, CURLOPT_PROXYPORT, $port);		
			
		if ($password and $login) {
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, $login . ':' . $password);
		} elseif ($password) {
			curl_setopt($ch, CURLOPT_PROXYAUTH, $password);
		}
	}	
	
	return $ch;
}			