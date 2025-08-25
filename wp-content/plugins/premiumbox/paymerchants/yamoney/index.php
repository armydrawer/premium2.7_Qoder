<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Yandex money[:en_US][ru_RU:]Yandex money[:ru_RU]
description: [en_US:]Yandex money automatic payouts[:en_US][ru_RU:]авто выплаты Yandex money[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_yamoney')) {
	class paymerchant_yamoney extends Ext_AutoPayut_Premiumbox{

		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);	
			
			add_action('before_paymerchants_admin',array($this, 'before_paymerchants_admin'), 10, 3); 
			
			$ids = $this->get_ids('paymerchants', $this->name);
			foreach ($ids as $id) {
				add_action('premium_merchant_ap_' . $id . '_verify', array($this, 'merchant_verify'));
			}			
			
			add_action('ext_paymerchants_delete', array($this, 'del_dostup_files'), 10, 2);
		}
		
		function get_map() {
			
			$map = array(
				'AP_YANDEX_MONEY_ACCOUNT'  => array(
					'title' => '[en_US:]Account wallet number[:en_US][ru_RU:]Номер кошелька[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),
				'AP_YANDEX_MONEY_APP_ID'  => array(
					'title' => '[en_US:]Application ID[:en_US][ru_RU:]Идентификатор приложения[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'AP_YANDEX_MONEY_APP_KEY'  => array(
					'title' => '[en_US:]OAuth2[:en_US][ru_RU:]OAuth2[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),				
			);
			
			return $map;
		}

		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('AP_YANDEX_MONEY_ACCOUNT');
			
			return $arrs;
		}	

		function before_paymerchants_admin($now_script, $data, $data_id) {
			
			if ($now_script and $now_script == $this->name) { 
				$m_defin = $this->get_file_data($data_id);
				$class = new AP_YaMoney($this->name, $data_id, is_isset($m_defin, 'YANDEX_MONEY_APP_ID'), is_isset($m_defin, 'YANDEX_MONEY_APP_KEY'));
				$token = $class->token;
				if ($token) {
					echo '<div class="premium_reply pn_success">' . sprintf(__('The application has been authenticated. If necessary, click on the link to <a href="%s" target="_blank">re-authenticate the application</a>.', 'pn'), get_mlink('ap_' . $data_id . '_verify').'?get_restart=1') . '</div>';
				} else {
					echo '<div class="premium_reply pn_error">' . sprintf(__('For correct operation, <a href="%s" target="_blank">authenticate the application</a>.', 'pn'), get_mlink('ap_' . $data_id . '_verify')) . '</div>';
				}		
			}
			
		}
		
		function merchant_verify() {
			
			$m_id = key_for_url('_verify', 'ap_'); 
			$m_defin = $this->get_file_data($m_id);
			$m_data = get_paymerch_data($m_id);
			
			if (current_user_can('administrator') or current_user_can('pn_merchants')) {
				if (isset($_GET['code'])) {
					$class = new AP_YaMoney($this->name, $m_id, is_isset($m_defin, 'AP_YANDEX_MONEY_APP_ID'), is_isset($m_defin, 'AP_YANDEX_MONEY_APP_KEY'));
					$token = $class->auth();
					if ($token) {
						$res = $class->info($token);
						if (!isset($res['account'])) {
							pn_display_mess(__('No data received from the payment system', 'pn'));
						} elseif ($res['account'] != is_isset($m_defin,'AP_YANDEX_MONEY_ACCOUNT')) {
							pn_display_mess(sprintf(__('Authorization can me made from account %s', 'pn'), is_isset($m_defin, 'AP_YANDEX_MONEY_ACCOUNT')));
						} else {
							$class->update_token($token);
							wp_redirect(admin_url('admin.php?page=pn_add_paymerchants&item_key=' . $m_id . '&reply=true'));
							exit;
						}
					} else {
						pn_display_mess(__('Retry', 'pn'));
					}
				} else {
					$class = new AP_YaMoney($this->name, $m_id, is_isset($m_defin, 'AP_YANDEX_MONEY_APP_ID'), is_isset($m_defin, 'AP_YANDEX_MONEY_APP_KEY'));
					$res = $class->info();
					if (!isset($res['account']) or $res['account'] != is_isset($m_defin, 'AP_YANDEX_MONEY_ACCOUNT') or isset($_GET['get_restart']) and 1 == $_GET['get_restart']) {	
						header( 'Location: https://money.yandex.ru/oauth/authorize?client_id=' . is_isset($m_defin, 'AP_YANDEX_MONEY_APP_ID') . '&response_type=code&redirect_uri=' . urlencode(get_mlink('ap_' . $m_id . '_verify')) . '&scope=account-info operation-history operation-details payment-p2p payment-shop');
						exit();	
					} else {	
						pn_display_mess(__('Payment system is configured', 'pn'), __('Payment system is configured', 'pn'), 'true');	
					}
				}
			} else {
				pn_display_mess(__('Error! Insufficient privileges', 'pn'));	
			}
			
		}		

		function options($options, $data, $id, $place) {
			
			$options = pn_array_unset($options, array('checkpay', 'resulturl', 'error_status', 'enableip'));

			$opt = array(
				'0' => __('Account', 'pn'),
				'1' => __('Card', 'pn'),
			);			
			$options['variant'] = array(
				'view' => 'select',
				'title' => __('Transaction type', 'pn'),
				'options' => $opt,
				'default' => intval(is_isset($data, 'variant')),
				'name' => 'variant',
				'work' => 'int',
			);	
			
			$text = '
			<div><strong>' . __('Enter address to create new application', 'pn') . ':</strong> <a href="https://yoomoney.ru/myservices/new" target="_blank">https://yoomoney.ru/myservices/new</a>.</div>
			<div><strong>Redirect URI:</strong> <a href="' . get_mlink('ap_' . $id . '_verify') . '" target="_blank">' . get_mlink('ap_' . $id . '_verify') . '</a></div>				
			';
			$options['text'] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);		
			
			return $options;
		}					

		function get_reserve_lists($m_id, $m_defin) {
			
			$list = array();
			$list[$m_id . '_1'] = is_isset($m_defin, 'AP_YANDEX_MONEY_ACCOUNT');
			
			return $list;									
		}

		function update_reserve($code, $m_id, $m_defin) { 
		
			$sum = 0;
			if ($code == $m_id . '_1') {	
				try {
					$class = new AP_YaMoney($this->name, $m_id, is_isset($m_defin, 'AP_YANDEX_MONEY_APP_ID'), is_isset($m_defin, 'AP_YANDEX_MONEY_APP_KEY'));
					$res = $class->info();
					if (is_array($res) and isset($res['balance'])) {		
						$rezerv = trim((string)$res['balance']);
						$sum = $rezerv;								
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
			
			$variant = intval(is_isset($paymerch_data, 'variant'));
			
			$currency = mb_strtoupper($item->currency_code_get);
			$currency = str_replace('RUR', 'RUB', $currency);
					
			$enable = array('RUB');
			if (!in_array($currency, $enable)) {
				$error[] = __('Wrong currency code', 'pn'); 
			}						
						
			$account = $item->account_get;
			$account = mb_strtoupper($account);
			if (!preg_match("/^[0-9]{5,20}$/", $account, $matches)) {
				$error[] = __('Wrong client wallet', 'pn');
			}							

			$out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data), 2);
							
			if (0 == count($error)) {
				$result = $this->set_ap_status($item, $test);				
				if ($result) {				
					
					$notice = get_text_paymerch($m_id, $item, $sum);
					if (!$notice) { $notice = sprintf(__('ID order %s', 'pn'), $item->id); }
					$notice = trim(pn_maxf($notice, 150));
						
					try {
						
						$class = new AP_YaMoney($this->name, $m_id, is_isset($m_defin, 'AP_YANDEX_MONEY_APP_ID'), is_isset($m_defin, 'AP_YANDEX_MONEY_APP_KEY'));
						
						if (0 == $variant) {
						
							$reguest_id = $class->addPay($account, $sum, 2, $notice, $item->id);
							if ($reguest_id) {
								$trans_id = $reguest_id;
								$res = $class->processPay($reguest_id);
								if (1 == $res['error']) {
									$error[] = __('Payout error', 'pn');
									$pay_error = 1;
								} else {
									$trans_id = $res['payment_id'];
								}
							} else {
								$error[] = 'Error interfaice';
								$pay_error = 1;
							} 	

						} else {
							
							$card_key = $class->get_card_key($account);
							if ($card_key) {
								$reguest_id = $class->requestPay($card_key, $sum, 2);
								if ($reguest_id) {
									$trans_id = $reguest_id;
									$res = $class->processPay($reguest_id);
									if (1 == $res['error']) {
										$error[] = __('Payout error', 'pn');
										$pay_error = 1;
									} else {
										$trans_id = $res['payment_id'];
									}
								} else {
									$error[] = 'Error interfaice (requestPay)';
									$pay_error = 1;
								} 	
							} else {
								$error[] = 'Error interfaice (get_card_key)';
								$pay_error = 1;
							}
							
						}
							
					}
					catch (Exception $e) 
					{
						$error[] = $e;
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
					'from_account' => is_isset($m_defin, 'AP_YANDEX_MONEY_ACCOUNT'),
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

		function del_dostup_files($script, $id) {
			if ($script == $this->name) {
				global $premiumbox;
				
				$file = $premiumbox->plugin_dir . '/paymerchants/' . $script . '/dostup/access_token_' . $id . '.php';
				if (file_exists($file)) {
					@unlink($file);
				}
				delete_option('token_ap_' . $id);
			}
		}		
	}
}

new paymerchant_yamoney(__FILE__, 'Yandex money');