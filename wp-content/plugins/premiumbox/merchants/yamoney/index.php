<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Yandex money[:en_US][ru_RU:]Yandex money[:ru_RU]
description: [en_US:]Yandex money merchant[:en_US][ru_RU:]мерчант Yandex money[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_Merchant_Premiumbox')) { return; }

if (!class_exists('merchant_yamoney')) {
	class merchant_yamoney extends Ext_Merchant_Premiumbox {

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);

			add_action('before_merchants_admin', array($this, 'before_merchants_admin'), 10, 3);
			
			$ids = $this->get_ids('merchants', $this->name);
			foreach ($ids as $id) {
				add_action('premium_merchant_' . $id . '_verify', array($this, 'merchant_verify'));
				add_action('premium_merchant_' . $id . '_status' . hash_url($id), array($this, 'merchant_status'));
			}
			
			add_action('ext_merchants_delete', array($this, 'del_dostup_files'), 10, 2);
			
		}

		function get_map() {
			
			$map = array(
				'YANDEX_MONEY_ACCOUNT'  => array(
					'title' => '[en_US:]Account wallet number[:en_US][ru_RU:]Номер кошелька[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'YANDEX_MONEY_APP_ID'  => array(
					'title' => '[en_US:]Application ID[:en_US][ru_RU:]Идентификатор приложения[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),	
				'YANDEX_MONEY_APP_KEY'  => array(
					'title' => '[en_US:]OAuth2[:en_US][ru_RU:]OAuth2[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'YANDEX_MONEY_SECRET_KEY'  => array(
					'title' => '[en_US:]Secret for HTTP-notification[:en_US][ru_RU:]Секрет для HTTP-уведомлений[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),				
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('YANDEX_MONEY_ACCOUNT');
			
			return $arrs;
		}

		function before_merchants_admin($now_script, $data, $data_id) {
			
			if ($now_script and $now_script == $this->name) { 
				$m_defin = $this->get_file_data($data_id);
				$class = new YaMoney($this->name, $data_id, is_isset($m_defin, 'YANDEX_MONEY_APP_ID'), is_isset($m_defin, 'YANDEX_MONEY_APP_KEY'));
				$token = $class->token;
				if ($token) {
					echo '<div class="premium_reply pn_success">' . sprintf(__('The application has been authenticated. If necessary, click on the link to <a href="%s" target="_blank">re-authenticate the application</a>.', 'pn'), get_mlink($data_id . '_verify') . '?get_restart=1') . '</div>';
				} else {
					echo '<div class="premium_reply pn_error">' . sprintf(__('For correct operation, <a href="%s" target="_blank">authenticate the application</a>.', 'pn'), get_mlink($data_id . '_verify')) . '</div>';
				}		
			}
			
		}

		function merchant_verify() {
			
			$m_id = key_for_url('_verify'); 
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			if (current_user_can('administrator') or current_user_can('pn_merchants')) {
				if (isset($_GET['code'])) {
					$class = new YaMoney($this->name, $m_id, is_isset($m_defin, 'YANDEX_MONEY_APP_ID'), is_isset($m_defin, 'YANDEX_MONEY_APP_KEY'));
					$token = $class->auth();
					if ($token) {
						$res = $class->info($token);
						if (!isset($res['account'])) {
							pn_display_mess(__('No data received from the payment system', 'pn'));
						} elseif ($res['account'] != is_isset($m_defin, 'YANDEX_MONEY_ACCOUNT')) {	
							pn_display_mess(sprintf(__('Authorization can me made from account %s', 'pn'), is_isset($m_defin, 'YANDEX_MONEY_ACCOUNT')));	
						} else {	
							$class->update_token($token);
							wp_redirect(admin_url('admin.php?page=pn_add_merchants&item_key=' . $m_id . '&reply=true'));
							exit;	
						}
					} else {	
						pn_display_mess(__('Retry', 'pn'));	
					}
				} else {
					$class = new YaMoney($this->name, $m_id, is_isset($m_defin, 'YANDEX_MONEY_APP_ID'), is_isset($m_defin, 'YANDEX_MONEY_APP_KEY'));
					$res = $class->info();
					if (!isset($res['account']) or $res['account'] != is_isset($m_defin, 'YANDEX_MONEY_ACCOUNT') or isset($_GET['get_restart']) and 1 == $_GET['get_restart']) {	
						header( 'Location: https://yoomoney.ru/oauth/authorize?client_id=' . is_isset($m_defin, 'YANDEX_MONEY_APP_ID') . '&response_type=code&redirect_uri=' . urlencode(get_mlink($m_id . '_verify')) . '&scope=account-info operation-history operation-details payment-p2p');
						exit();	
					} else {
						pn_display_mess(__('Payment system is configured', 'pn'), __('Payment system is configured', 'pn'), 'true');
					}
				}
			} else {
				pn_display_mess(__('Error! Insufficient privileges', 'pn'));	
			}
			
		}
		
		function options($options, $data, $m_id, $place) { 
			
			$options = pn_array_unset($options, array('pagenote', 'check_api'));
	
			$options['private_line'] = array(
				'view' => 'line',
			);			
				
			$options['paymethod'] = array(
				'view' => 'select',
				'title' => __('Payment method', 'pn'),
				'options' => array('0' => 'YooMoney', '1' => 'YooMoney card'),
				'default' => is_isset($data, 'paymethod'),
				'name' => 'paymethod',
				'work' => 'int',
			);				
				
			$text = '
			<div><strong>' . __('Enter address to create new application', 'pn') . ':</strong> <a href="https://yoomoney.ru/myservices/new" target="_blank">https://yoomoney.ru/myservices/new</a>.</div>
			<div><strong>Redirect URI:</strong> <a href="' . get_mlink($m_id . '_verify') . '" target="_blank">' . get_mlink($m_id . '_verify') . '</a></div>
			<div><strong>Cron:</strong> <a href="' . get_mlink($m_id . '_cron' . chash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_cron' . chash_url($m_id)) . '</a></div>					
			<div><strong>HTTP-notification URL:</strong> <a href="' . get_mlink($m_id . '_status' . hash_url($m_id)) . '" target="_blank">' . get_mlink($m_id . '_status' . hash_url($m_id)) . '</a></div>
			';

			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);									
			
			return $options;	
		}					
		
		function bidform($temp, $m_id, $pay_sum, $direction) {
			global $bids_data;
			
			$script = get_mscript($m_id);
			if ($script and $script == $this->name) {
				$m_defin = $this->get_file_data($m_id);

				$currency = pn_strip_input($bids_data->currency_code_give);	
				$currency = str_replace('RUR', 'RUB', $currency);
								
				$pay_sum = is_sum($pay_sum, 2); 							
							
				$text_pay = get_text_pay($m_id, $bids_data, $pay_sum);
						
				$m_data = get_merch_data($m_id);
				$paymethod = intval(is_isset($m_data, 'paymethod'));
						
				$temp = '
				<form name="pay" action="https://yoomoney.ru/quickpay/confirm.xml" method="post">
					<input name="receiver" type="hidden" value="' . is_isset($m_defin, 'YANDEX_MONEY_ACCOUNT') . '">
					';
								
					if (1 == $paymethod) {
						$temp .= '<input name="paymentType" type="hidden" value="AC" />';
					} else {
						$temp .= '<input name="paymentType" type="hidden" value="PC" />';
					}
							
					//<input name="short-dest" type="hidden" value="'. $text_pay .'" />
					//<input name="formcomment" type="hidden" value="'. $text_pay .'" />
					
					$temp .= '
					<input name="targets" type="hidden" value="' . $text_pay . '" />					
					<input name="writable-targets" type="hidden" value="false" />
					<input name="quickpay-form" type="hidden" value="shop" />               
					<input name="sum" type="hidden" value="' . $pay_sum . '" />					
					<input name="comment" type="hidden" value="" />
					<input name="label" type="hidden" value="' . $bids_data->id . '" />
								
					<input type="submit" value="' . __('Make a payment', 'pn') . '" />
				</form>';									
			
			}
			
			return $temp;
		}

		function merchant_status() {
		
			$m_id = key_for_url('_status');
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_merch_data($m_id);
			
			do_action('merchant_secure', $this->name, '', $m_id, $m_defin, $m_data);
		
			$paymethod = intval(is_isset($m_data, 'paymethod'));
			$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
			$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
			$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
			$invalid_check = intval(is_isset($m_data, 'check'));		
		
			$workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay')); 
		
			if (isset($_POST['notification_type'], $_POST['operation_id'], $_POST['amount'], $_POST['currency'], $_POST['datetime'], $_POST['sender'], $_POST['codepro'], $_POST['label'])) {
				$secret = is_isset($m_defin, 'YANDEX_MONEY_SECRET_KEY');
				$s = $_POST['notification_type'] . '&' . $_POST['operation_id'] . '&' . $_POST['amount'] . '&' . $_POST['currency'] . '&' . $_POST['datetime'] . '&' . $_POST['sender'] . '&' . $_POST['codepro'] . '&' . $secret . '&' . $_POST['label'];
				if (hash('sha1', $s) == $_POST['sha1_hash']) {
					
					$currency = 'RUB';
					$trans_id = is_param_post('operation_id');
					
					$id = intval($_POST['label']);
					$data = get_data_merchant_for_id($id);
					
					$in_sum = $_POST['amount'];
					
					$err = $data['err'];
					$status = $data['status'];
					$bid_m_script = $data['m_script'];
					$bid_err = $data['err'];
					$bid_m_id = $data['m_id'];
					
					if ($bid_err > 0) {
						$this->logs($id . ' The application does not exist or the wrong ID', $m_id);
						die('The application does not exist or the wrong ID');
					}
					
					if ($bid_m_script and $bid_m_script != $this->name or !$bid_m_script) {	
						$this->logs($id . ' wrong script', $m_id);
						die('wrong script');
					}			
					
					if ($bid_m_id and $m_id != $bid_m_id or !$bid_m_id) {
						$this->logs($id . ' not a faithful merchant', $m_id);
						die('not a faithful merchant');				
					}
					
					if (check_trans_in($m_id, $trans_id, $id)) {
						$this->logs($id . ' Error check trans in!', $m_id);
						die('Error check trans in!');
					}					
					
					$bid_currency = $data['currency'];
					$bid_currency = str_replace('RUR', 'RUB', $bid_currency);
						
					$sender = $_POST['sender'];
					if (0 == $paymethod) {
						if ('p2p-incoming' != $_POST['notification_type']) {
							$sender .= ' card';
						}	
					} else {
						if ('p2p-incoming' == $_POST['notification_type']) {
							$sender .= ' purse';
						}						
					}
					$pay_purse = is_pay_purse($sender, $m_data, $m_id);
						
					$bid_sum = is_sum($data['pay_sum'], 2);	
					$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $m_id);
					
					$codepro = $_POST['codepro'];
					$codepro_int = intval($codepro);
					
					$unaccepted = is_param_post('unaccepted');
					$unaccepted_int = intval($unaccepted);
					
					$set_status = 'coldpay';
					if (1 != $codepro_int and 1 != $unaccepted_int and 'true' != $codepro and 'true' != $unaccepted) {
						$set_status = 'realpay';
					}
					
					if (in_array($status, $workstatus)) { 
						if ($bid_currency == $currency or $invalid_ctype > 0) {
							if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {
								
								$params = array(
									'pay_purse' => $pay_purse,
									'sum' => $in_sum,
									'bid_sum' => $bid_sum,
									'bid_corr_sum' => $bid_corr_sum,
									'bid_status' => $workstatus,
									'to_account' => is_isset($m_defin, 'YANDEX_MONEY_ACCOUNT'),
									'trans_in' => $trans_id,
									'currency' => $currency,
									'bid_currency' => $bid_currency,
									'invalid_ctype' => $invalid_ctype,
									'invalid_minsum' => $invalid_minsum,
									'invalid_maxsum' => $invalid_maxsum,
									'invalid_check' => $invalid_check,	
									'm_place' => $m_id,
									'm_id' => $m_id,
									'm_data' => $m_data,
									'm_defin' => $m_defin,
								);
								set_bid_status($set_status, $id, $params, $data['direction_data']);
								
							} else {
								$this->logs($id . ' The payment amount is less than the provisions', $m_id);
								die('The payment amount is less than the provisions');
							}
						} else {
							$this->logs($id . ' Wrong type of currency', $m_id);
							die('Wrong type of currency');
						}
					} else {
						$this->logs($id . ' bad status, codepro or unaccepted', $m_id);
					}						
				} else {
					$this->logs('bad hash', $m_id);
				}
			} else {
				$this->logs('no data', $m_id);
			}
		}
		
		function cron($m_id, $m_defin, $m_data) {
			
			$paymethod = intval(is_isset($m_data, 'paymethod'));
			$show_error = intval(is_isset($m_data, 'show_error'));
			$invalid_ctype = intval(is_isset($m_data, 'invalid_ctype'));
			$invalid_minsum = intval(is_isset($m_data, 'invalid_minsum'));
			$invalid_maxsum = intval(is_isset($m_data, 'invalid_maxsum'));
			$invalid_check = intval(is_isset($m_data, 'check'));			
			
			try {	
				$class = new YaMoney($this->name, $m_id, is_isset($m_defin, 'YANDEX_MONEY_APP_ID'), is_isset($m_defin, 'YANDEX_MONEY_APP_KEY'));
				$res = $class->operationHistory('deposition', null, null, null, null, 30, true);
				foreach (isset( $res['operations'] ) ? $res['operations'] : array() as $aOperation) {
					$codepro = is_isset($aOperation, 'codepro');
					$codepro_int = intval($codepro);
					
					if ('success' == $aOperation['status'] and 'in' == $aOperation['direction'] and isset($aOperation['label'])) {
						
						$sender = is_isset($aOperation, 'sender'); 
						
						$currency = 'RUB';
						
						$trans_id = is_isset($aOperation, 'operation_id'); 
						
						$pattern_id = '';
						if (isset($aOperation['pattern_id'])) {
							$pattern_id = $aOperation['pattern_id']; //p2p
						}
						$sOrder = $aOperation['label']; //id заявки
						$dAmount = $aOperation['amount'] - 0;	//сумма
					
						if (0 == $paymethod) {
							if ('p2p' != $pattern_id) {
								$sender .= ' card';
							}	
						} else {
							if ('p2p' == $pattern_id) {
								$sender .= ' purse';
							}						
						}
						$pay_purse = is_pay_purse($sender, $m_data, $this->name);					
					
						$id = intval($sOrder);
						$data = get_data_merchant_for_id($id);
						
						$set_status = 'coldpay';
						if (1 != $codepro_int and 'true' != $codepro) {
							$set_status = 'realpay';
						}						
						
						$in_sum = $dAmount;
					
						$err = $data['err'];
						$status = $data['status'];
						$bid_m_id = $data['m_id'];
						$bid_m_script = $data['m_script'];
						
						$bid_currency = $data['currency'];
						$bid_currency = str_replace('RUR', 'RUB', $bid_currency);
						
						$bid_sum = is_sum($data['pay_sum'], 2);	
						$bid_corr_sum = apply_filters('merchant_bid_sum', $bid_sum, $m_id);
						
						$workstatus = _merch_workstatus($m_id, array('new', 'techpay', 'coldpay'));
						if (in_array($status, $workstatus)) {
							if (!check_trans_in($bid_m_id, $trans_id, $id)) {
								if (0 == $err and $bid_m_id and $bid_m_id == $m_id and $bid_m_script and $bid_m_script == $this->name) {
									if ($bid_currency == $currency or $invalid_ctype > 0) {
										if ($in_sum >= $bid_corr_sum or $invalid_minsum > 0) {	
										
											$params = array(
												'pay_purse' => $pay_purse,
												'sum' => $in_sum,
												'bid_sum' => $bid_sum,
												'bid_corr_sum' => $bid_corr_sum,
												'bid_status' => $workstatus,
												'to_account' => is_isset($m_defin, 'YANDEX_MONEY_ACCOUNT'),
												'trans_in' => $trans_id,
												'currency' => $currency,
												'bid_currency' => $bid_currency,
												'invalid_ctype' => $invalid_ctype,
												'invalid_minsum' => $invalid_minsum,
												'invalid_maxsum' => $invalid_maxsum,
												'invalid_check' => $invalid_check,
												'm_place' => $m_id . '_cron',
												'm_id' => $m_id,
												'm_data' => $m_data,
												'm_defin' => $m_defin,
											);
											set_bid_status($set_status, $id, $params, $data['direction_data']);	
											
										} else {
											$this->logs($id . ' The payment amount is less than the provisions', $m_id);
										}
									} else {
										$this->logs($id . ' Wrong type of currency', $m_id);
									}		 		 
								} else {
									$this->logs($id . ' bid error', $m_id);
								}
							} else {
								$this->logs($id . ' Error check trans in!', $m_id);
							}
						}	
					}
				}	
			}
			catch (Exception $e)
			{
				$this->logs($e->getMessage(), $m_id);
				if ($show_error and current_user_can('administrator')) {
					die($e->getMessage());
				}			
			}			
		}
		
		function del_dostup_files($script, $id) {
			if ($script == $this->name) {
				global $premiumbox;
				
				$file = $premiumbox->plugin_dir . '/merchants/' . $script . '/dostup/access_token_' . $id . '.php';
				if (is_file($file)) {
					@unlink($file);
				}
				delete_option('token_' . $id);
				
			}
		}
	}
}

new merchant_yamoney(__FILE__, 'Yandex money');