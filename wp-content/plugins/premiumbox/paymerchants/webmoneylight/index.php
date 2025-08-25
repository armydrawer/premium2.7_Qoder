<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Webmoney Light[:en_US][ru_RU:]Webmoney Light[:ru_RU]
description: [en_US:]Webmoney Light automatic payouts[:en_US][ru_RU:]авто выплаты Webmoney Light[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_webmoney_light')) {
	class paymerchant_webmoney_light extends Ext_AutoPayut_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title);	
		}
		
		function get_map() {
			
			$map = array(
				'WMID'  => array(
					'title' => '[en_US:]WMID[:en_US][ru_RU:]WMID[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'KEYPATH'  => array(
					'title' => '[en_US:]Certificate name .cer[:en_US][ru_RU:]Имя сертификата .cer[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),				
				'KEYPASS'  => array(
					'title' => '[en_US:]Private key name .key[:en_US][ru_RU:]Имя приватного ключа .key[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),				
				'WMZ_PURSE'  => array(
					'title' => '[en_US:]WMZ wallet number for payments[:en_US][ru_RU:]WMZ кошелек для авто выплат[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WMF_PURSE'  => array(
					'title' => '[en_US:]WMF wallet number for payments[:en_US][ru_RU:]WMF кошелек для авто выплат[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WMT_PURSE'  => array(
					'title' => '[en_US:]WMT wallet number for payments[:en_US][ru_RU:]WMT кошелек для авто выплат[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),				
				'WME_PURSE'  => array(
					'title' => '[en_US:]WME wallet number for payments[:en_US][ru_RU:]WME кошелек для авто выплат[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WMB_PURSE'  => array(
					'title' => '[en_US:]WMB wallet number for payments[:en_US][ru_RU:]WMB кошелек для авто выплат[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'WMG_PURSE'  => array(
					'title' => '[en_US:]WMG wallet number for payments[:en_US][ru_RU:]WMG кошелек для авто выплат[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WMX_PURSE'  => array(
					'title' => '[en_US:]WMX wallet number for payments[:en_US][ru_RU:]WMX кошелек для авто выплат[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WMK_PURSE'  => array(
					'title' => '[en_US:]WMK wallet number for payments[:en_US][ru_RU:]WMK кошелек для авто выплат[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WML_PURSE'  => array(
					'title' => '[en_US:]WML wallet number for payments[:en_US][ru_RU:]WML кошелек для авто выплат[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'WMH_PURSE'  => array(
					'title' => '[en_US:]WMH wallet number for payments[:en_US][ru_RU:]WMH кошелек для авто выплат[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),				
			);
			
			return $map;
		}
		
		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('WMID', 'KEYPASS');
			
			return $arrs;
		}				

		function options($options, $data, $id, $place) {
			
			$options = pn_array_unset($options, array('resulturl', 'checkpay', 'error_status', 'enableip', 'cronhash'));
			
			$html_request = '';
			$num_request = intval(get_option('old_webmoney_id'));
			$new_request = intval(is_isset($data, 'num_request'));
			if ($num_request > 0 and $new_request < 1) {
				$html_request = ' (' . $num_request . ')';
			}				
			
			$options['num_request'] = array(
				'view' => 'input',
				'title' => __('Current payment ID', 'pn') . $html_request,
				'default' => is_isset($data, 'num_request'),
				'name' => 'num_request',
				'work' => 'int',
			);								
			
			return $options;
		}			

		function get_reserve_lists($m_id, $m_defin) {
			
			$purses = array(
				$m_id . '_1' => is_isset($m_defin, 'WMZ_PURSE'),
				$m_id . '_2' => is_isset($m_defin, 'WMF_PURSE'),
				$m_id . '_3' => is_isset($m_defin, 'WME_PURSE'),
				$m_id . '_4' => is_isset($m_defin, 'WMT_PURSE'),
				$m_id . '_5' => is_isset($m_defin, 'WMB_PURSE'),
				$m_id . '_7' => is_isset($m_defin, 'WMG_PURSE'),
				$m_id . '_8' => is_isset($m_defin, 'WMX_PURSE'),
				$m_id . '_9' => is_isset($m_defin, 'WMK_PURSE'),
				$m_id . '_10' => is_isset($m_defin, 'WML_PURSE'),
				$m_id . '_11' => is_isset($m_defin, 'WMH_PURSE'),
			);
			
			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin) { 
			global $premiumbox;	
			
			$sum = 0;	
			$purses = $this->get_reserve_lists($m_id, $m_defin);	
			$purse = trim(is_isset($purses, $code));
			if ($purse) {		
				try {
					$oWMXI = new WMXI( $premiumbox->plugin_dir . '/paymerchants/' . $this->name . '/classed/wmxi.crt', 'UTF-8' );
					$oWMXI->Light(array('key' => $this->replace_constant($m_defin, 'KEYPATH'), 'cer' => $premiumbox->plugin_dir . '/paymerchants/' . $this->name . '/classed/wmxi.crt', 'pass' => is_isset($m_defin,'KEYPASS')));
					$aResponse = $oWMXI->X9(is_isset($m_defin, 'WMID'))->toObject();
					$server_reply = is_isset($aResponse, 'retval');
					if ('0' == $server_reply) {
								
						if (isset($aResponse->purses->purse)) {
							$wmid_purses = $aResponse->purses->purse;
								
							$rezerv = '-1';
								
							foreach ($wmid_purses as $wp) {
								if ($wp->pursename == $purse) {
									$rezerv = (string)$wp->amount;
									break;
								}
							}						
								
							if ('-1' != $rezerv) {
								$sum = $rezerv;
							}
						}

					} 
				}
				catch (Exception $e)
				{
					$this->logs($e->getMessage(), $m_id);		
				} 				
			}
			
			return $sum;						
		}			

		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {
			
			$item_id = $item->id;
			$trans_id = 0;			
			
			$currency = mb_strtoupper($item->currency_code_get);
			$currency = str_replace(array('WMZ', 'USD'), 'Z', $currency);
			$currency = str_replace(array('WMF', 'ETH'), 'F', $currency);
			$currency = str_replace(array('WMT', 'USDT'), 'T', $currency);
			$currency = str_replace(array('WME', 'EUR'), 'E', $currency);
			$currency = str_replace(array('WMB', 'BYN'), 'B', $currency);
			$currency = str_replace(array('WMG', 'GLD'), 'G', $currency);
			$currency = str_replace(array('WMX', 'BTC'), 'X', $currency);
			$currency = str_replace(array('WMK', 'KZT'), 'K', $currency);
			$currency = str_replace(array('WML', 'LTC'), 'L', $currency);
			$currency = str_replace(array('WMH', 'BCH'), 'H', $currency);					

			$enable = array('Z', 'F', 'T', 'E', 'B', 'G', 'X', 'K', 'L', 'H');
			if (!in_array($currency, $enable)) {
				$error[] = __('Wrong currency code', 'pn'); 
			}						
						
			$account = $item->account_get;
			$account = mb_strtoupper($account);
			if (!is_wm_purse($account, $currency)) {
				$error[] = __('Wrong client wallet', 'pn');
			}		
					
			$site_purse = is_isset($m_defin, 'WM' . $currency . '_PURSE');
			$site_purse = mb_strtoupper($site_purse);
			if (!is_wm_purse($site_purse, $currency)) {
				$error[] = __('Your account set on website does not match with currency code', 'pn');
			}	

			$out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);
		
			if (0 == count($error)) {
				global $premiumbox;
				
				$result = $this->set_ap_status($item, $test);					
				if ($result) {					
					
					$notice = get_text_paymerch($m_id, $item, $sum);
					if (!$notice) { $notice = sprintf(__('ID order %s', 'pn'), $item->id); }
					$notice = trim(pn_maxf($notice, 245));
							
					if (is_file($premiumbox->plugin_dir . '/paymerchants/' . $this->name . '/classed/wmxi.crt') and is_isset($m_defin, 'KEYPASS') and $this->replace_constant($m_defin, 'KEYPATH')) {
							
						$num_request = intval(is_isset($paymerch_data, 'num_request'));
						$num_request = $num_request + 1;
						
						$paymerch_data = pn_array_unset($paymerch_data, array('ext_status'));
						$paymerch_data['num_request'] = $num_request;
						
						$db_data = pn_json_encode($paymerch_data);
						global $wpdb;
						
						$wpdb->query("UPDATE " . $wpdb->prefix . "exts SET ext_options = '$db_data' WHERE ext_key = '$m_id' AND ext_type = 'paymerchants'");	 				
							
						try {
							
							$oWMXI = new WMXI($premiumbox->plugin_dir . '/paymerchants/' . $this->name . '/classed/wmxi.crt', 'UTF-8');
							$oWMXI->Light(array('key' => $this->replace_constant($m_defin, 'KEYPATH'), 'cer' => $premiumbox->plugin_dir . '/paymerchants/' . $this->name . '/classed/wmxi.crt', 'pass' => is_isset($m_defin,'KEYPASS')));
								
							$aResponse = $oWMXI->X2($num_request, $site_purse, $account, $sum , 0, '', $notice, 0, 0)->toObject();
							$server_reply = is_isset($aResponse, 'retval');
								
							if ('0' != $server_reply) {
								$error[] = is_isset($aResponse, 'retdesc') . ' Code:' . $server_reply;
								$pay_error = 1;
							} 
								
						}
						catch (Exception $e)
						{
							$error[] = $e->getMessage();
							$pay_error = 1;
						} 
							
					} else {
						$error[] = 'Error interfaice';
						$pay_error = 1;
					}
						
				} else {
					$error[] = 'Database error';
				}										
			}
					
			if (count($error) > 0) {
				
				$this->reset_ap_status($error, $pay_error, $item, $place, $m_id, $test);
				
			} else {
	
				$params = array(
					'from_account' => $site_purse,
					'trans_out' => $trans_id,
					'out_sum' => $out_sum,
					'system' => 'user',
					'm_place' => $modul_place . ' ' . $m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);
				set_bid_status('success', $item_id, $params, $direction); 						
						
				if ('admin' == $place) {
					pn_display_mess(__('Automatic payout is done', 'pn'), __('Automatic payout is done', 'pn'), 'true');
				}  		
			}
		}				
		
	}
}

global $premiumbox;
$path = get_extension_file(__FILE__);
$premiumbox->file_include($path . '/classed/wmxicore.class');	
$premiumbox->file_include($path . '/classed/wmxi.class');
$premiumbox->file_include($path . '/classed/wmxiresult.class');
$premiumbox->file_include($path . '/classed/wmsigner.class');

new paymerchant_webmoney_light(__FILE__, 'Webmoney Light');