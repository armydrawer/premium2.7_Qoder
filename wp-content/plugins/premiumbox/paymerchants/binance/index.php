<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Binance[:en_US][ru_RU:]Binance[:ru_RU]
description: [en_US:]Binance automatic payouts[:en_US][ru_RU:]авто выплаты Binance[:ru_RU]
version: 2.7.0
*/

if (!class_exists('Ext_AutoPayut_Premiumbox')) { return; }

if (!class_exists('paymerchant_binance')) {
	class paymerchant_binance extends Ext_AutoPayut_Premiumbox {
		
		private $currency_lists = array('USDT','BTC','ETH','LTC','DASH','ETC','XMR','XRP','ZEC','DOGE','BCHSV','BCHABC','BTG','XLM','EOS','NEO','TRX','CAS','WAVES','STEEM','XEM','PAX','USDC','TUSD','REP','IOTA','LSK','ADA','OMG','XVG','ZRX','BNB','ICX','KMD','BTT','SOL','DOT','SHIB','RVN');
		
		function __construct($file, $title)
		{
			parent::__construct($file, $title, 1);
		}

		function get_map() {
			
			$map = array(
				'API_KEY'  => array(
					'title' => '[en_US:]API key[:en_US][ru_RU:]Ключ API[:ru_RU]',
					'view' => 'input',	
					'hidden' => 1,
				),
				'API_SECRET'  => array(
					'title' => '[en_US:]API secret[:en_US][ru_RU:]Секретный ключ[:ru_RU]',
					'view' => 'input',
					'hidden' => 1,
				),				
			);
			
			return $map;
		}
		
		function settings_list() {
			
			$arrs = array();
			$arrs[] = array('API_KEY', 'API_SECRET');
			
			return $arrs;
		}
		
		function options($options, $data, $m_id, $place) {
			
			$options = pn_array_unset($options, array('checkpay', 'note', 'enableip', 'resulturl'));		
			
			$options['line_error_status'] = array(
				'view' => 'line',
			);
			
			$options['network'] = array(
				'view' => 'input',
				'title' => __('Network', 'pn'),
				'default' => is_isset($data, 'network'),
				'name' => 'network',
				'work' => 'input',
			);			
			
			$text = __('
			• For trading transaction, amounts are rounded to 6 decimal points according to mathematical rules.<br>
			• Order type BUY.<br>
			• Stock exchange charges fee per trading transaction, depending on amount traded.<br>
			• Stock exchange charges fee for funds withdrawal. The user will receive amount minus fee.', 'pn');
			
			$options[] = array(
				'view' => 'warning',
				'title' => '',
				'default' => $text,
			);			
			
			$options['buy'] = array(
				'view' => 'select',
				'title' => __('Buy additional amount of crypto missing on balance', 'pn'),
				'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn'), '2' => __('Buy and do not withdrawal', 'pn'), '3' => __('Buy entire amount', 'pn')),
				'default' => intval(is_isset($data, 'buy')),
				'name' => 'buy',
				'work' => 'int',
			);						
			
			$options['birg_pers'] = array(
				'view' => 'input',
				'title' => __('Trading fee of stock exchange (%)', 'pn'),
				'default' => is_isset($data, 'birg_pers'),
				'name' => 'birg_pers',
				'work' => 'sum',
			);			
			
			$options['buytype'] = array(
				'view' => 'select',
				'title' => __('Order type', 'pn'),
				'options' => array('0' => __('Market', 'pn'), '1' => __('Limit', 'pn')),
				'default' => intval(is_isset($data, 'buytype')),
				'name' => 'buytype',
				'work' => 'int',
			);
			
			$options['buytype_help'] = array(
				'view' => 'help',
				'title' => __('More info', 'pn'),
				'default' => __('
				• Market - order will be executed instantly according to the market value at the exchange.<br>
				• Limit - order will be placed at the exchange at your price, and executed according to the value of parameter "Order execution time"', 'pn'),
			);
			
			$options['timeinforce'] = array(
				'view' => 'select',
				'title' => __('Order execution time (if Limit)', 'pn'),
				'options' => array('GTC' => 'GTC', 'IOC' => 'IOC', 'FOK' => 'FOK'),
				'default' => is_isset($data, 'timeinforce'),
				'name' => 'timeinforce',
				'work' => 'input',
			);
			
			$options['timeinforce_help'] = array(
				'view' => 'help',
				'title' => __('More info', 'pn'),
				'default' => __('
				• GTC (Good-Til-Canceled) - orders are effective until they are executed or canceled.<br>
				• IOC (Immediate or Cancel) - orders fills all or part of an order immediately and cancels the remaining part of the order.<br>
				• FOK (Fill or Kill) - orders fills all in its entirety, otherwise, the entire order will be cancelled.','pn'),
				);			
			
			$options['buysymbols'] = array(
				'view' => 'select',
				'title' => __('Determine trading code', 'pn'),
				'options' => array('0' => __('Automatically', 'pn'), '1' => __('Manually', 'pn')),
				'default' => intval(is_isset($data, 'buysymbols')),
				'name' => 'buysymbols',
				'work' => 'int',
			);			
			
			$buycurr = array();
			
			if (is_array($this->currency_lists)) {
				foreach ($this->currency_lists as $curr) {
					$buycurr[$curr] = $curr;
				}
			}
			
			$options['buycurr'] = array(
				'view' => 'select',
				'title' => __('Trading operation code (if Manually)', 'pn'),
				'options' => $buycurr,
				'default' => is_isset($data, 'buycurr'),
				'name' => 'buycurr',
				'work' => 'input',
			);

			$text = '
			<div><strong>CRON:</strong> <a href="' . get_mlink('ap_' . $m_id . '_cron' . chash_url($m_id, 'ap')) . '" target="_blank">' . get_mlink('ap_' . $m_id . '_cron' . chash_url($m_id, 'ap')) . '</a></div>
			';
			$options[] = array(
				'view' => 'textfield',
				'title' => '',
				'default' => $text,
			);			
			
			return $options;
		}			

		function get_reserve_lists($m_id, $m_defin) {
			
			$currencies = $this->currency_lists;
			
			$purses = array();
			foreach ($currencies as $currency) {
				$purses[$m_id . '_' . strtolower($currency)] = strtoupper($currency);
			} 
			
			return $purses;
		}		

		function update_reserve($code, $m_id, $m_defin) { 
		
			$sum = 0;
			$purse = strtoupper(trim(str_replace($m_id . '_', '', $code))); 
			if ($purse) {
				try {
					$class = new AP_Binance($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'API_SECRET'));
					$res = $class->get_balance();
					if (is_array($res)) {
						$rezerv = '-1';
						foreach ($res as $pursename => $sum) {
							$pursename = strtoupper($pursename);
							if ($pursename == $purse) {
								$rezerv = trim((string)$sum);
								break;
							}
						}
						if ('-1' != $rezerv) {
							$sum = $rezerv;
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

		function buy_bnb($coin_info, $buybnb_enable, $class) {
			
			$buybnb_enable = intval($buybnb_enable);
			if ($buybnb_enable) {
				$bnb_balance = 0;
				foreach ($coin_info as $d) {
					$coin = trim(is_isset($d, 'coin'));
					$free = is_sum(is_isset($d, 'free'));
					if ($coin and 'BNB' == $coin) {
						$bnb_balance = $free;
					}
				}
				if ($bnb_balance < 1) {
					$buy_bnb = 1; 
					$res = $class->buy(0, 'BNBUSDT', 0, $buy_bnb);
					if (!isset($res['executedQty'])) {
						return 0;
					} else {
						sleep(5);
					}						
				}
			}	
			
			return 1;
		}

		function do_auto_payouts($error, $pay_error, $m_id, $item, $place, $direction_data, $paymerch_data, $unmetas, $modul_place, $direction, $test, $m_defin) {
			
			$item_id = $item->id;			
			$trans_id = 0;				
			
			$currency_code_give = strtoupper($item->currency_code_give);
			$currency_code_get = strtoupper($item->currency_code_get);
			
			$currency_id_give = intval($item->currency_id_give);
			$currency_id_get = intval($item->currency_id_get);			
							
			$account = $item->account_get;
					
			if (!$account) {
				$error[] = __('Wrong client wallet', 'pn');
			}			
					
			$out_sum = $sum = is_sum(is_paymerch_sum($item, $paymerch_data));			
			
			$buy = intval(is_isset($paymerch_data, 'buy')); /* settings */
			$add_comission = 1; /* settings */
			
			$class = new AP_Binance($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'API_SECRET'));
			
			$coin_info = $class->coins_info();
			
			$res_buy = $this->buy_bnb($coin_info, 0, $class);
			if (1 != $res_buy) {
				$error[] = 'error buyed bnb';
			}
			
			$currency_send = $currency_code_get;
			$network_send = pn_strip_input(is_isset($paymerch_data, 'network'));
			$sum_send = $sum;
			
			if ('USDT' == $currency_code_get and !$network_send) {
				global $wpdb;
				
				$currency_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$currency_id_get'");
				if (isset($currency_data->id)) {
					$xml_value = mb_strtoupper(is_xml_value($currency_data->xml_value));
					if ('USDT' == $xml_value) {
						$network_send = 'OMNI';
					} elseif ('USDTERC' == $xml_value or 'USDTERC20' == $xml_value) { 
						$network_send = 'ETH';
					} elseif ('USDTTRC' == $xml_value or 'USDTTRC20' == $xml_value) {	
						$network_send = 'TRX';
					} elseif ('USDTBEP' == $xml_value or 'USDTBEP20' == $xml_value) {	
						$network_send = 'BSC';
					}
				}
			}
			
			$balance = 0;
			$cd = array();
			$nts = array();

			foreach ($coin_info as $d) {		
				$coin = trim(is_isset($d, 'coin'));
				$free = is_sum(is_isset($d, 'free'));
				if ($coin and $coin == $currency_send) {
					$balance = $free;
					$networkList = $d['networkList'];
					foreach ($networkList as $net) {
						$network = trim(is_isset($net, 'network'));
						$nts[] = $network;
						$isDefault = intval(is_isset($net, 'isDefault'));
						if (!$network_send and 1 == $isDefault or $network_send and $network_send == $network) {
							$cd = $net;
						}
					}
				}	
			}
			
			if (count($cd) < 1) {
				$error[] = 'not currency in binance ' . $currency_send . ' and network ' . $network_send . ', has networks: ' . implode(',', $nts);
			}			
			
			$withdrawEnable = intval(is_isset($cd, 'withdrawEnable'));
			$withdrawFee = is_sum(is_isset($cd, 'withdrawFee'));
			$withdrawIntegerMultiple = is_sum(is_isset($cd, 'withdrawIntegerMultiple'));
			$withdrawMax = is_sum(is_isset($cd, 'withdrawMax'));
			$withdrawMin = is_sum(is_isset($cd, 'withdrawMin'));
			
			if ($add_comission) {
				$sum_send = $sum_send + $withdrawFee;
			}
			
			if ($withdrawIntegerMultiple > 0) {
				$sum_send1 = $sum_send / $withdrawIntegerMultiple;
				$sum_send2 = floor($sum_send1) * $withdrawIntegerMultiple;
				$sum_send = is_sum($sum_send2);
			}
			
			if (1 != $withdrawEnable) {
				$error[] = 'withdraw disabled';
			}			
			if ($sum_send < $withdrawMin) {
				$error[] = 'min amount: ' . $withdrawMin . ' , now amount: ' . $sum_send . '';
			}
			if ($sum_send > $withdrawMax and $withdrawMax > 0) {
				$error[] = 'max amount: ' . $withdrawMax . ' , now amount: ' . $sum_send . '';
			}			
			
			if (0 == count($error)) { 
				$buy_sum = 0;
				if (1 == $buy or 2 == $buy) {
					if ($sum_send > $balance) {
						$buy_sum = $sum_send - $balance;
					}
				}
				if (3 == $buy) {
					$buy_sum = $sum_send;
				}
				$buy_sum = is_sum($buy_sum, 12);
				if ($buy_sum > 0) {
					$execution_type = intval(is_isset($paymerch_data, 'buytype'));
					$buysymbols = intval(is_isset($paymerch_data, 'buysymbols'));
					$buycurr = is_xml_value(is_isset($paymerch_data, 'buycurr'));
					if (!$buycurr) { $buycurr = 'USDT'; }
					$birg_pers = is_sum(is_isset($paymerch_data, 'birg_pers'));
					
					$timeinforce = pn_strip_input(is_isset($paymerch_data, 'timeinforce'));
					$tinf = array('GTC' => 'GTC', 'IOC' => 'IOC', 'FOK' => 'FOK');
					if (!in_array($timeinforce, $tinf)) { $timeinforce = 'GTC'; }
					
					$symbol = '';
					if (1 == $buysymbols) {
						$symbol = strtoupper($currency_code_get . $buycurr);
					} else {	
						$symbol = strtoupper($currency_code_get . $currency_code_give);
					}	

					if ($birg_pers <= 0) {
						$tradefee = $class->tradeFee();
						if (isset($tradefee[$symbol])) {
							if (0 == $execution_type) { //market
								$birg_pers = $tradefee[$symbol]['takerCommission'];
							} else { //limit
								$birg_pers = $tradefee[$symbol]['makerCommission'];
							}
						}
					}
					
					$buy_sum = pers_alter_sum($buy_sum, $birg_pers);
					
					$exchangeInfo = $class->exchangeInfo();
					if (isset($exchangeInfo[$symbol])) {
						//if($execution_type == 0){
							//$filter_name = 'MARKET_LOT_SIZE';
						//} else {
							$filter_name = 'LOT_SIZE';
						//}							
						$m_info = is_isset($exchangeInfo[$symbol]['filters'], $filter_name);
						
						$m_min = is_sum(is_isset($m_info, 'minQty'), 12);
						$m_max = is_sum(is_isset($m_info, 'maxQty'), 12);
						$m_size = is_sum(is_isset($m_info, 'stepSize'), 12);
						$m_symb = $exchangeInfo[$symbol]['symb'];
						
						if ($m_size > 0) {
							$quantity = $buy_sum / $m_size;
							$quantity = floor($quantity) * $m_size;
							if ($quantity < $buy_sum) {
								$quantity = $quantity + $m_size;
							}
							$quantity = is_sum($quantity);
						} else {
							$quantity = is_sum($buy_sum, $m_symb, 'up');
						}
						
						if ($quantity >= $m_min and $quantity <= $m_max) {
							$course = is_sum($item->course_get);
							$res = $class->buy($execution_type, $symbol, $course, $quantity, $timeinforce);
							if (isset($res['executedQty'])) {
								
								$nb = is_sum($res['executedQty']);
								$nb2 = $balance + $nb;
								$balance = is_sum($nb2);
								
								sleep(5);
								
							} else {
								$error[] = __('Failed to buy cryptocurrency', 'pn');
							}							
						} else {
							$error[] = 'trading amount error. min: ' . $m_min . ' max: ' . $m_max . ' now: ' . $quantity;
						}
					} else {
						$error[] = 'pair not trading';
					}
				}
			
				if (2 == $buy) {
					$error[] = __('Cryptocurrency only', 'pn');
				} 				
			}

			if ($sum_send > $balance) {
				$error[] = 'balance: ' . $balance . ' , now amount: ' . $sum_send . '';
			}			
					
			$dest_tag = trim(is_isset($unmetas, 'dest_tag'));		
			
			if (0 == count($error)) {
				$result = $this->set_ap_status($item, $test);
				if ($result) {
					try {
						$res = $class->send_money($currency_send, $sum_send, $account, $network_send, $dest_tag);
						if (isset($res['id'])) {
							$trans_id = pn_strip_input($res['id']);
						} else {
							$error[] = __('Payout error', 'pn');
							$pay_error = 1;
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
					'trans_out' => $trans_id,
					'system' => 'user',
					'out_sum' => $out_sum,
					'm_place' => $modul_place. ' ' .$m_id,
					'm_id' => $m_id,
					'm_defin' => $m_defin,
					'm_data' => $paymerch_data,
				);
				set_bid_status('coldsuccess', $item_id, $params, $direction); 	
						
				if ('admin' == $place) {
					pn_display_mess(__('Automatic payout is done', 'pn'), __('Automatic payout is done', 'pn'), 'true');
				} 	
				
			}
		}	

		function cron($m_id, $m_defin, $m_data) {
			global $wpdb;
			
			$error_status = is_status_name(is_isset($m_data, 'error_status'));
			
			$class = new AP_Binance($this->name, $m_id, is_isset($m_defin, 'API_KEY'), is_isset($m_defin, 'API_SECRET'));
			$endTime = current_time('timestamp');
			$startTime = $endTime - (3 * DAY_IN_SECONDS);
			$transactions = $class->get_payout_transactions($startTime, $endTime);
			$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldsuccess' AND m_out = '$m_id'");
			
			foreach ($items as $item) {
				$currency = mb_strtoupper($item->currency_code_get);
				$trans_id = trim($item->trans_out);
				if ($trans_id) {
					if (isset($transactions[$trans_id])) {
						$check_status = intval($transactions[$trans_id]['status']); //0:Email Sent, 1:Cancelled, 2:Awaiting Approval, 3:Rejected, 4:Processing, 5:Failure, 6:Completed
						$txt_id = pn_strip_input(is_isset($transactions[$trans_id], 'txId'));
						if (6 == $check_status or 4 == $check_status and $txt_id) {
							
							$params = array(
								'txid_out' => $txt_id,
								'system' => 'system',
								'bid_status' => array('coldsuccess'),
								'm_place' => 'cron ' . $m_id,
								'm_id' => $m_id,
								'm_defin' => $m_defin,
								'm_data' => $m_data,
							);
							set_bid_status('success', $item->id, $params);
							
						} elseif (in_array($check_status, array('1', '3', '5'))) {
							
							$this->reset_cron_status($item, $error_status, $m_id);
							
						}
					}	
				}
			}
		}		
	}
}

new paymerchant_binance(__FILE__, 'Binance');