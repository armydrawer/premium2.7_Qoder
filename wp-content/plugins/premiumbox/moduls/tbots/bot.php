<?php 
if (!defined('ABSPATH')) { exit(); }

add_action('pn_api_page', 'tapibot_api_page', 20, 3); 
function tapibot_api_page($module, $version, $endpoint) {
	global $wpdb, $premiumbox;	

	$endpoint = pn_strip_input($endpoint);

	if (!$premiumbox->is_up_mode()) {

		if ('tapibot' == $module and 'v1' == $version and 'callback' == $endpoint) {
		
			status_header(200);
		
			$json = array(
				'error' => 2,
				'error_text' => 'Bot not supported',
			);
			
			$bot_id = intval(is_param_get('bot_id'));
			if ($bot_id > 0) {
				$bot = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibots WHERE id = '$bot_id'");
				if (isset($bot->id)) {
					
					$settings = pn_json_decode($bot->bot_settings);
					$bot_logs = intval(is_isset($bot, 'bot_logs'));
					$bot_parsmode = intval(is_isset($bot, 'bot_parsmode'));
					$api_lang = trim(is_isset($bot, 'api_lang'));
					$log_bot_id = 0;
					if ($bot_logs) {
						$log_bot_id = $bot_id;
					}
					
					set_locale($api_lang);
					
					if (1 == $bot->bot_status) {
						
						if ($bot_logs) {
							tapibot_create_log($bot->id, $_SERVER['REQUEST_URI'], $_POST, '', '', '');
						}
						
						$secret = trim(get_option('tapibots_callback_secret'));
						$md5_sercet = trim(is_param_get('sk'));
						if (!$md5_sercet or $md5_sercet != md5($secret)) {
							
							$json = array(
								'error' => 5,
								'error_text' => 'Wrong secret',
							);
							echo pn_json_encode($json);
							exit;
						}

						$u_id = intval(is_param_get('u_id'));
						$check_bid_id = intval(is_param_post('bid_id'));
						
						$json = array(
							'error' => 6,
							'error_text' => 'Bid not exists',
						);	
						
						if ($check_bid_id > 0) {
							$has = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "tapibot_bids WHERE tapibot_id = '$bot_id' AND uniq_id = '$u_id' AND bid_id = '$check_bid_id'");
							if ($has) {
								
								tapibot_check_bidstatus('', $check_bid_id, $bot, $log_bot_id, $settings, $u_id);
								
								$json = array(
									'error' => 0,
									'error_text' => 'OK',
								);								
							}
						} 
						
					}
				}
			}
			
			echo pn_json_encode($json);
			exit;
			
		}
		
		if ('tapibot' == $module and 'v1' == $version and 'webhook' == $endpoint) {

			status_header(200);

			$request = @file_get_contents('php://input');
			
			$data = @json_decode($request, true);
				
			$json = array(
				'error' => 2,
				'error_text' => 'Bot not supported',
			);			
				
			$bot_id = intval(is_param_get('id'));
			if ($bot_id > 0) {
				$bot = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibots WHERE id = '$bot_id'");
				if (isset($bot->id)) {
					
					$settings = pn_json_decode($bot->bot_settings);
					$bot_logs = intval(is_isset($bot, 'bot_logs'));
					$bot_parsmode = intval(is_isset($bot, 'bot_parsmode'));
					$api_lang = trim(is_isset($bot, 'api_lang'));
					$log_bot_id = 0;
					if ($bot_logs) {
						$log_bot_id = $bot_id;
					}
						
					set_locale($api_lang);
				
					$json = array(
						'error' => 3,
						'error_text' => 'Bot disabled',
					);			
				
					if (1 == $bot->bot_status) {
							
						if ($bot_logs) {
							tapibot_create_log($bot->id, $_SERVER['REQUEST_URI'], $_POST, $data, '', '');
						}
								
						$secret = trim(get_option('tapibots_secret'));
						$md5_sercet = trim(is_param_get('sk'));

						if (!$md5_sercet or $md5_sercet != md5($secret)) {

							$json = array(
								'error' => 5,
								'error_text' => 'Wrong secret',
							);
							echo pn_json_encode($json);
							exit;
						}							
								
						$chat_data = tapibot_chat_data($data);
							
						$is_bot = intval(is_isset($chat_data, 'is_bot'));	
						if ($is_bot) {

							$json = array(
								'error' => 5,
								'error_text' => 'No bots, please',
							);
							echo pn_json_encode($json);
							exit;	
						}						
								
						$command = pn_strip_input(is_isset($chat_data, 'command'));
						$uniq_id = pn_strip_input(is_isset($chat_data, 'uniq_id'));
							
						if ($uniq_id and $command) {
							
							$replace_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibot_words WHERE enter_word = '$command'");
							if (isset($replace_data->id)) {
								$command = $replace_data->get_word;
							}
							
							$first = 0;
							
							$chat = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibot_chats WHERE tapibot_id = '$bot_id' AND uniq_id = '$uniq_id'");
							if (!isset($chat->id)) {
								$first = 1;
									
								$array = array();
								$array['tapibot_id'] = $bot_id;
								$array['uniq_id'] = $uniq_id;
								$wpdb->insert($wpdb->prefix . "tapibot_chats", $array);
								$array['id'] = $wpdb->insert_id;
								$chat = $array;
								
							}							
							
							$info = pn_json_decode(is_isset($chat, 'now_info'));
							if (!is_array($info)) { $info = array(); }
							
							$save_info = pn_json_decode(is_isset($chat, 'save_info'));
							if (!is_array($save_info)) { $save_info = array(); }							
							
							if (1 == $first) {
									
								$welocome_text = pn_strip_text(ctv_ml(is_isset($settings, 'welocome_text')));
								if (strlen($welocome_text) > 1) {
									tapibot_send($bot, $chat_data, $uniq_id, $settings, $welocome_text, '');
								}
									
							}	

							$exch_button_text = pn_strip_input(ctv_ml(is_isset($settings,'exch_button_text')));
							if (strlen($exch_button_text) < 1) { $exch_button_text = __('Start a new exchange', 'pn'); }
							
							$error_api_text = pn_strip_text(ctv_ml(is_isset($settings,'error_api_text')));
							if (strlen($error_api_text) < 1) {
								$error_api_text = __('An unexpected error has occurred. Please try again later.', 'pn');
							}	

							$m_commands = array();
							$r = 0;
							while ($r++ < 6) {
								$mbut_title = pn_strip_input(ctv_ml(is_isset($settings, 'mbut_title' . $r)));
								$mbut_text = pn_strip_text(ctv_ml(is_isset($settings, 'mbut_text' . $r)));
								if (strlen($mbut_text) < 1) {
									$mbut_text = __('Description not found', 'pn');
								}								
								if (strlen($mbut_title) > 0) {
									$m_commands[$mbut_title] = $mbut_text;
								}
							}
							
							if (isset($m_commands[$command])) {
								
								tapibot_send($bot, $chat_data, $uniq_id, $settings, $m_commands[$command], '');
								$command = '/empty';
								
							}
							
							$memory_data = intval(is_isset($settings, 'memory_data'));
							if (!$memory_data) {
								
								$save_info = array();
								
							} else {
								$memory_button_text = pn_strip_input(ctv_ml(is_isset($settings, 'memory_button_text')));
								if (strlen($memory_button_text) < 1) {
									$memory_button_text = __('Delete saved data', 'pn');
								}
								if ($command == $memory_button_text) {
									
									$save_info = array();
									$memory_ok_text = pn_strip_text(ctv_ml(is_isset($settings, 'memory_ok_text')));
									if (strlen($memory_ok_text) < 1) {
										$memory_ok_text = __('Data deleted successfully', 'pn');
									}
									tapibot_send($bot, $chat_data, $uniq_id, $settings, $memory_ok_text);
									$command = '/empty';
									
								}
							}							
							
							if ($command == $exch_button_text or '/start' == $command) {	
								$command = '';
								$info = array();
							}							
											
							$setcurr_list = intval(is_isset($settings,'setcurr_list'));
									
							$give_currency_id = intval(is_isset($info, 'give_currency_id'));
							$give_currency_title = pn_strip_input(is_isset($info, 'give_currency_title'));
							$get_currency_id = intval(is_isset($info, 'get_currency_id'));
							$get_currency_title = pn_strip_input(is_isset($info, 'get_currency_title'));
								
							$stop = 0;
								
							$bid_data = '';	
									
							if (!strstr($command, '/checkbid_') and !strstr($command, '/paidbid_') and !strstr($command, '/cancelbid_') and '/empty' != $command) {

								if (!$give_currency_id and !$stop) {
									$curr = tapibot_command($log_bot_id, 'list_currency', $bot->api_server, $bot->api_version, $bot->api_lang, $bot->api_login, $bot->api_key, $bot->api_partner_id, array());
									if (isset($curr['error'], $curr['data'], $curr['data']['give']) and is_array($curr['data']['give'])) {
										if (0 == $curr['error']) {
											$lists = $curr['data']['give'];
											if (count($lists) > 0) {
												$go = 1;
												$setcurr_give_text = '';
												if (strlen($command) > 0) {
													$word = strtolower($command);
													$word_error1 = tapibot_return_word1($command);
													$word_error2 = tapibot_return_word2($command);
													foreach ($lists as $list) {
														$api_title = pn_strip_input(is_isset($list, 'title'));
														$api_lower_title = strtolower($api_title);
														$api_currency_id = intval(is_isset($list, 'id'));
														if ($word == $api_lower_title or $word_error1 == $api_lower_title or $word_error2 == $api_lower_title) {
															$give_currency_id = $api_currency_id;
															$give_currency_title = $api_title;
														}
													}	
													if ($give_currency_id > 0) {
														$info['give_currency_id'] = $give_currency_id;
														$info['give_currency_title'] = $give_currency_title;
														$go = 0;
														$command = '';
													} else {
														$setcurr_give_text = sprintf(__('We are very sorry, we could not find such a currency - "%s".', 'pn'), $command) . "\n";
													}
												}
												if ($go) {
													$text = pn_strip_text(ctv_ml(is_isset($settings, 'setcurr_give_text')));
													$in = array();
													if ($setcurr_list) {
														if (strlen($text) < 1) {
															$text = __('Select the currency you want to donate from the list.', 'pn');
														}
														foreach ($lists as $list) {
															$in[] = array(
																array(
																	'text' => pn_strip_input(is_isset($list, 'title')),
																	'callback_data' => pn_strip_input(is_isset($list, 'title')),
																),
															);
														}
													} else {
														if (strlen($text) < 1) {
															$text = __('Enter the name of the currency you want to give.', 'pn');
														}
													}
													$setcurr_give_text .= $text;
													$stop = 1;
													tapibot_send($bot, $chat_data, $uniq_id, $settings, $setcurr_give_text, $in);												
												}	
											} else {
												$stop = 1;
												tapibot_send($bot, $chat_data, $uniq_id, $settings, __('The list of currencies is empty. Try later.', 'pn'), '');
											}											
										} else {
											$stop = 1;
											tapibot_send($bot, $chat_data, $uniq_id, $settings, $curr['error_text'], '');	
										}
									} else {
										$stop = 1;
										tapibot_send($bot, $chat_data, $uniq_id, $settings, $error_api_text, '');
									}
								}

								if (!$get_currency_id and !$stop) {
									$curr = tapibot_command($log_bot_id, 'list_currency', $bot->api_server, $bot->api_version, $bot->api_lang, $bot->api_login, $bot->api_key, $bot->api_partner_id, array('id' => $give_currency_id));
									if (isset($curr['error'], $curr['data'], $curr['data']['get']) and is_array($curr['data']['get'])) {
										if (0 == $curr['error']) {
											$lists = $curr['data']['get'];
											if (count($lists) > 0) {
												$go = 1;
												$setcurr_get_text = '';
												if (strlen($command) > 0) {
													$word = strtolower($command);
													$word_error1 = tapibot_return_word1($command);
													$word_error2 = tapibot_return_word2($command);
													foreach ($lists as $list) {
														$api_title = pn_strip_input(is_isset($list, 'title'));
														$api_lower_title = strtolower($api_title);
														$api_currency_id = intval(is_isset($list, 'id'));
														if ($word == $api_lower_title or $word_error1 == $api_lower_title or $word_error2 == $api_lower_title) {
															$get_currency_id = $api_currency_id;
															$get_currency_title = $api_title;
														}
													}	
													if ($get_currency_id > 0) {
														$info['get_currency_id'] = $get_currency_id;
														$info['get_currency_title'] = $get_currency_title;
														$go = 0;
														$command = '';
													} else {
														$setcurr_get_text = sprintf(__('We are very sorry, we could not find such a currency - "%s".', 'pn'), $command) . "\n";
													}
												}
												if ($go) {
													$text = pn_strip_text(ctv_ml(is_isset($settings, 'setcurr_get_text')));
													$in = array();
													if ($setcurr_list) {
														if (strlen($text) < 1) {
															$text = __('Select the currency you want to get from the list.', 'pn');
														}
														foreach ($lists as $list) {
															$in[] = array(
																array(
																	'text' => pn_strip_input(is_isset($list, 'title')),
																	'callback_data' => pn_strip_input(is_isset($list, 'title')),
																),
															);
														}
													} else {
														if (strlen($text) < 1) {
															$text = __('Enter the name of the currency you want to get.', 'pn');
														}
													}	
													$setcurr_get_text .= $text;
													$stop = 1;
													tapibot_send($bot, $chat_data, $uniq_id, $settings, $setcurr_get_text, $in);												
												}	
											} else {
												$stop = 1;
												tapibot_send($bot, $chat_data, $uniq_id, $settings, __('The list of currencies is empty. Try later.', 'pn'), '');
											}											
										} else {
											$stop = 1;
											tapibot_send($bot, $chat_data, $uniq_id, $settings, $curr['error_text'], '');	
										}
									} else {
										$stop = 1;
										tapibot_send($bot, $chat_data, $uniq_id, $settings, $error_api_text, '');
									}
								}	

								$dir_data = '';
								if (!$stop) {
									$apidata = tapibot_command($log_bot_id, 'get_direction', $bot->api_server, $bot->api_version, $bot->api_lang, $bot->api_login, $bot->api_key, $bot->api_partner_id, array('currency_id_give' => $give_currency_id, 'currency_id_get' => $get_currency_id));
									if (isset($apidata['error'], $apidata['data']) and is_array($apidata['data'])) {
										if (0 == $apidata['error']) {
											$dir_data = $apidata['data'];
										} else {
											$stop = 1;
											tapibot_send($bot, $chat_data, $uniq_id, $settings, $apidata['error_text']);	
										}
									} else {
										$stop = 1;
										tapibot_send($bot, $chat_data, $uniq_id, $settings, $error_api_text);
									}								
								}
								
								if (isset($dir_data['id']) and !$stop) {
									
									$currency_code_give = pn_strip_input(is_isset($dir_data, 'currency_code_give'));
									$currency_code_get = pn_strip_input(is_isset($dir_data, 'currency_code_get'));
											
									$give_minmax_arr = $get_minmax_arr = array();
									
									$min_give = pn_strip_input(is_isset($dir_data, 'min_give'));
									$max_give = pn_strip_input(is_isset($dir_data, 'max_give'));
									if ($min_give > 0 and 'no' != $min_give) {
										$give_minmax_arr[] = __('min.', 'pn') . ': ' . $min_give . ' ' . $currency_code_give;
									}
									if ('no' != $max_give) {
										$give_minmax_arr[] = __('max.', 'pn') . ': ' . $max_give . ' ' . $currency_code_give;
									}	
									
									$min_get = pn_strip_input(is_isset($dir_data, 'min_get'));
									$max_get = pn_strip_input(is_isset($dir_data, 'max_get'));										
									if ($min_get > 0 and 'no' != $min_get) {
										$get_minmax_arr[] = __('min.', 'pn') . ': ' . $min_get . ' ' . $currency_code_get;
									}
									if ('no' != $max_get) {
										$get_minmax_arr[] = __('max.', 'pn') . ': ' . $max_get . ' ' . $currency_code_get;
									}										
											
									$give_minmax = '';
									if (count($give_minmax_arr) > 0) {
										$give_minmax = '(' . implode(',', $give_minmax_arr) . ')';
									}									
											
									$get_minmax = '';
									if (count($get_minmax_arr) > 0) {
										$get_minmax = '(' . implode(',', $get_minmax_arr) . ')';
									}								
									
									$set_currency = intval(is_isset($info, 'set_currency'));
									if (0 == $set_currency) {
										$go = 1;
										if (strlen($command) > 0) {
											
											if ('choice_1' == $command) {
												$go = 0;
												$set_currency = 1;
												$info['set_currency'] = $set_currency;
											} elseif ('choice_2' == $command) {
												$go = 0;
												$set_currency = 2;
												$info['set_currency'] = $set_currency;											
											} 
											
											$command = '';
											
										} 
										if ($go) {
											
											$dir_info = is_isset($dir_data, 'info');
											
											$text = pn_strip_text(ctv_ml(is_isset($settings,'selectplace_text')));
											if (strlen($text) < 1) {
												$text = sprintf(__('Exchange rate %1s %2s = %3s %4s.','pn'), pn_strip_input(is_isset($dir_data, 'course_give')), $give_currency_title, pn_strip_input(is_isset($dir_data, 'course_get')), $get_currency_title);
											}
											$text = str_replace('[course_give]', pn_strip_input(is_isset($dir_data, 'course_give')), $text);
											$text = str_replace('[course_get]', pn_strip_input(is_isset($dir_data, 'course_get')), $text);
											$text = str_replace('[currency_give]', $give_currency_title, $text);
											$text = str_replace('[currency_get]', $get_currency_title, $text);
											
											$timeline_text = pn_strip_text(is_isset($dir_info, 'timeline_text'));
											if (strlen($timeline_text) > 0) {
												$text .= "\n" . $timeline_text;
											}
											
											$before_button_text = pn_strip_text(is_isset($dir_info, 'before_button_text'));
											if (strlen($before_button_text) > 0) {
												$text .= "\n" . $before_button_text;
											}
											
											$in = array();	
											$in[] = array(
												array (
													'text' =>  sprintf(__('Give %1s %2s', 'pn'), $give_currency_title, $give_minmax),
													'callback_data' => 'choice_1',
												),
											);										
											$in[] = array(
												array (
													'text' =>  sprintf(__('Get %1s %2s', 'pn'), $get_currency_title, $get_minmax),
													'callback_data' => 'choice_2',
												),
											);										
											
											$stop = 1;
											tapibot_send($bot, $chat_data, $uniq_id, $settings, $text, $in);										
											
										}
									} 
									
									$amount = is_sum(is_isset($info, 'amount'));
									if (!$stop) {
										if ($amount > 0) {
											if (1 == $set_currency) {
												if ($min_give > 0 and 'no' != $min_give) {
													if ($amount < $min_give) {
														$amount = '0';
													}
												}
												if ('no' != $max_give) {
													if ($amount > $max_give) {
														$amount = '0';
													}
												}												
											} else {
												if ($min_get > 0 and 'no' != $min_get) {
													if ($amount < $min_get) {
														$amount = '0';
													}
												}
												if ('no' != $max_get) {
													if ($amount > $max_get) {
														$amount = '0';
													}
												}											
											}																					
										}
										if ('/clear_amount' == $command) {
											$amount = '0';
											$info['amount'] = '0';
										}
										if (0 == $amount) {
											$go = 1;
											if (strlen($command) > 0) {
												$amount = is_sum($command);
												if ($set_currency == 1) {
													if ($min_give > 0 and 'no' != $min_give) {
														if ($amount < $min_give) {
															$amount = '0';
														}
													}
													if ('no' != $max_give) {
														if ($amount > $max_give) {
															$amount = '0';
														}
													}												
												} else {
													if ($min_get > 0 and 'no' != $min_get) {
														if ($amount < $min_get) {
															$amount = '0';
														}
													}
													if ('no' != $max_get) {
														if ($amount > $max_get) {
															$amount = '0';
														}
													}											
												}		
												if ($amount > 0) {
													$go = 0;
													$command = '';
													$info['amount'] = $amount;
												}
											}
											if ($go) {
												if (1 == $set_currency) {
													$text = pn_strip_text(ctv_ml(is_isset($settings,'selplace1_text')));
													if (strlen($text) < 1) {
														$text = sprintf(__('Exchange rate %1s %2s = %3s %4s.','pn'), pn_strip_input(is_isset($dir_data, 'course_give')), $give_currency_title, pn_strip_input(is_isset($dir_data, 'course_get')), $get_currency_title);
														$text .= "\n" . sprintf(__('Enter the amount in the %1s %2s you want to give', 'pn'), $give_currency_title, $give_minmax);
														$text .= "\n<i>" . __('The amount is entered without taking into account the commission of the payment system', 'pn') . '</i>';
													}
												} else {
													$text = pn_strip_text(ctv_ml(is_isset($settings,'selplace2_text')));
													if (strlen($text) < 1) {
														$text = sprintf(__('Exchange rate %1s %2s = %3s %4s.','pn'), pn_strip_input(is_isset($dir_data, 'course_give')), $give_currency_title, pn_strip_input(is_isset($dir_data, 'course_get')), $get_currency_title);
														$text .= "\n" . sprintf(__('Enter the amount in %1s %2s you want to receive', 'pn'), $get_currency_title, $get_minmax);
														$text .= "\n<i>" . __('The amount is entered without taking into account the commission of the payment system', 'pn') . '</i>';
													}
												}
												$text = str_replace('[course_give]', pn_strip_input(is_isset($dir_data, 'course_give')), $text);
												$text = str_replace('[course_get]', pn_strip_input(is_isset($dir_data, 'course_get')), $text);
												$text = str_replace('[currency_give]', $give_currency_title, $text);
												$text = str_replace('[currency_get]', $get_currency_title, $text);
												$text = str_replace('[minmax_give]', $give_minmax, $text);
												$text = str_replace('[minmax_get]', $get_minmax, $text);
												
												$stop = 1;
												tapibot_send($bot, $chat_data, $uniq_id, $settings, $text);											
											}
										}
									}
									
									$fields = array();
									$dir_fields = is_isset($dir_data, 'dir_fields');
									if (!is_array($dir_fields)) { $dir_fields = array(); }
									$fields = array_merge($fields, $dir_fields);
									$give_fields = is_isset($dir_data, 'give_fields');
									if (!is_array($give_fields)) { $give_fields = array(); }
									$fields = array_merge($fields, $give_fields);
									$get_fields = is_isset($dir_data, 'get_fields');
									if (!is_array($get_fields)) { $get_fields = array(); }							
									$fields = array_merge($fields, $get_fields);								
									
									$save_fields = is_isset($info, 'save_fields');
									if (!is_array($save_fields)) { $save_fields = array(); }
									
									if (!$stop) {
										
										if (strstr($command, '/clear_field_')) {
											$field_name = str_replace('/clear_field_', '', $command);
											$command = '';
											if (isset($save_fields[$field_name])) { 
												unset($save_fields[$field_name]);
											}											
										}
										
										if (strstr($command, '/dontchange')) {
											$info['go_to_exchange'] = 1;
										}
										
										if (strstr($command, '/wantchange')) {
											$info['go_to_exchange'] = 0;
											$ldn = array();
											if (count($fields) > 0) {
												foreach ($fields as $field) {
													$field_name = is_isset($field, 'name');
													$field_type = is_isset($field, 'type');
													if ('select' == $field_type) {
														$text = pn_strip_input(is_isset($field, 'label'));
														$ldn[$field_name] = $text;
													} elseif ('text' == $field_type or 'textarea' == $field_type) {
														$text = pn_strip_input(is_isset($field, 'label'));
														$ldn[$field_name] = $text;												
													} elseif ('checkbox' == $field_type) {
														$text = pn_strip_input(is_isset($field, 'text'));
														$ldn[$field_name] = $text;												
													}
												}
											}
											$text = __('What would you like to change?', 'pn');
											$in = array();	
											$in[] = array(
												array (
													'text' => __('Exchange amount', 'pn'),
													'callback_data' => '/clear_amount',
												),
											);	
											foreach ($ldn as $ldn_k => $ldn_v) {
												$in[] = array(
													array (
														'text' => $ldn_v,
														'callback_data' => '/clear_field_' . $ldn_k,
													),
												);												
											}
											$in[] = array(
												array (
													'text' => __("Don't change anything", 'pn'),
													'callback_data' => '/dontchange',
												),
											);
											$stop = 1;
											tapibot_send($bot, $chat_data, $uniq_id, $settings, $text, $in);										
										}
										
										$info['save_fields'] = $save_fields;
									}
									
									$save_fields = is_isset($info, 'save_fields');
									if (!is_array($save_fields)) { $save_fields = array(); }
									
									if (!$stop) {
										if (count($fields) > 0) {
											foreach ($fields as $field) {
												$field_name = is_isset($field, 'name');
												$field_type = is_isset($field, 'type');
												$memory_field = trim(is_isset($save_info, $field_name));
												if (!isset($save_fields[$field_name])) {
													$go = 1;												
													if (strlen($command) > 0) {
														if ('select' == $field_type) {
															$options = is_isset($field, 'options');
															if (isset($options[$command])) {
																$go = 0;
																$save_fields[$field_name] = pn_strip_input($command);
															}
														} elseif ('text' == $field_type or 'textarea' == $field_type) {
															$go = 0;
															$req = intval(is_isset($field, 'req'));
															if (!$req) {
																$command = str_replace('/do_not_fill_out', '', $command);
															}
															$format = trim(is_isset($field, 'format'));
															if ($format) {
																$f = get_user_fields();
																if (isset($f[$format])) {
																	$command = strip_uf($command, $format);
																}	
															} 
															if ($req) {
																$command = pn_strip_input($command);
																if (strlen($command) > 0) {
																	$save_fields[$field_name] = $save_info[$field_name] = $command;
																}
															} else {
																$save_fields[$field_name] = $save_info[$field_name] = pn_strip_input($command);
															}
														} elseif ('checkbox' == $field_type) {	
															if ('answer_1' == $command) {
																$go = 0;
																$save_fields[$field_name] = 1;
															} elseif ('answer_2' == $command) {
																$go = 0;
																$save_fields[$field_name] = 0;
															} 													
														}
														$command = '';
													}
													if ($go) {
														
														$stop = 1;

														if ('select' == $field_type) {
															
															$text = pn_strip_input(is_isset($field, 'label'));
															$req = intval(is_isset($field, 'req'));

															$options = is_isset($field, 'options');
															$in = array();																
															if (is_array($options)) {
																foreach ($options as $o_k => $o_v) {
																	$o_k = pn_strip_input($o_k);
																	$o_v = pn_strip_input($o_v);
																	if ('0' != $o_k and $req or !$req) {
																		$in[] = array(
																			array (
																				'text' => $o_v,
																				'callback_data' => $o_k,
																			),
																		);		
																	}
																}										
															}
															tapibot_send($bot, $chat_data, $uniq_id, $settings, $text, $in);
															
														} elseif ('text' == $field_type or 'textarea' == $field_type) {
															
															$req = intval(is_isset($field, 'req'));
															
															$text = __('Enter value', 'pn');
															$text .= "\n" . pn_strip_input(is_isset($field, 'label'));
															if ($req) {
																$text .= " (" . __('Required to fill out', 'pn') . ')';
															}
															$tooltip = 	pn_strip_input(is_isset($field, 'tooltip'));
															if (strlen($tooltip) > 0) {
																$text .= "\n" . $tooltip;
															}
															$in = array();
															if ($memory_data and strlen($memory_field) > 0) {
																$in[] = array(
																	array (
																		'text' => $memory_field,
																		'callback_data' => $memory_field,
																	),
																);
															}
															if (!$req) {
																$in[] = array(
																	array (
																		'text' => __('do not fill out', 'pn'),
																		'callback_data' => '/do_not_fill_out',
																	),
																);																																			
															}
															tapibot_send($bot, $chat_data, $uniq_id, $settings, $text, $in);
															
														} elseif ('checkbox' == $field_type) {
															
															$text = pn_strip_input(is_isset($field, 'text'));
															$in = array();	
															$in[] = array(
																array (
																	'text' => __('Yes', 'pn'),
																	'callback_data' => 'answer_1',
																),
															);										
															$in[] = array(
																array (
																	'text' => __('No', 'pn'),
																	'callback_data' => 'answer_2',
																),
															);																								
															tapibot_send($bot, $chat_data, $uniq_id, $settings, $text, $in);
															
														}													
														
														break;
													}
												} 
											}
										} 
										$info['save_fields'] = $save_fields;
									}	

									$save_fields = is_isset($info, 'save_fields');
									if (!is_array($save_fields)) { $save_fields = array(); }	

									$go_to_exchange = intval(is_isset($info, 'go_to_exchange'));
									if (!$stop and !$go_to_exchange) {
										$cd = array();
										$ld = array();
										$ldn = array();
										if (count($fields) > 0) {
											foreach ($fields as $field) {
												$field_name = is_isset($field, 'name');
												$field_type = is_isset($field, 'type');
												$field_cd = intval(is_isset($field, 'cd'));
												$save_field = is_isset($save_fields, $field_name);
												if ('select' == $field_type) {
													$options = is_isset($field, 'options');
													$text = pn_strip_input(is_isset($field, 'label'));
													$ld[] = $text . ': ' . is_isset($options, $save_field);
													$ldn[$field_name] = $text;
													if ($field_cd) {
														$cd[$field_name] = urlencode($save_field);
													}
												} elseif ('text' == $field_type or 'textarea' == $field_type) {
													$text = pn_strip_input(is_isset($field, 'label'));
													$ld[] = $text . ': ' . $save_field;
													$ldn[$field_name] = $text;
													if ($field_cd) {
														$cd[$field_name] = urlencode($save_field);
													}												
												} elseif ('checkbox' == $field_type) {
													$text = pn_strip_input(is_isset($field, 'text'));
													$ldn[$field_name] = $text;
													$nfg = intval($save_field);
													if ($nfg) {
														$ld[] = $text;
													}
													if ($field_cd and $nfg) {
														$cd[$field_name] = urlencode(pn_strip_input(is_isset($field, 'value')));
													}												
												}
											}
										}
										$go = 1;									
										if (strlen($command) > 0) {
											if ('/make_exchange' == $command) {
												$go = 0;
												$info['go_to_exchange'] = 1;
											}
											$command = '';
										}
										if ($go) {
											$direction_id = $dir_data['id'];
											$calc_amount = is_sum(is_isset($info, 'amount'));
											$set_currency = intval(is_isset($info, 'set_currency'));
											if (1 == $set_currency) {
												$calc_action = 1;
											} else {
												$calc_action = 2;
											}
											$cd_string = urlencode(http_build_query($cd));
											$apidata = tapibot_command($log_bot_id, 'get_calc', $bot->api_server, $bot->api_version, $bot->api_lang, $bot->api_login, $bot->api_key, $bot->api_partner_id, array('direction_id' => $direction_id, 'calc_amount' => $calc_amount, 'calc_action' => $calc_action, 'cd' => $cd_string));
											if (isset($apidata['error'], $apidata['data']) and is_array($apidata['data'])) {
												if (0 == $apidata['error']) {
													$now_data = $apidata['data'];
													$text = sprintf(__('Exchange rate %1s %2s = %3s %4s.', 'pn'), pn_strip_input(is_isset($now_data, 'course_give')), $give_currency_title, pn_strip_input(is_isset($now_data, 'course_get')), $get_currency_title);
													$text .= "\n" . sprintf(__('Your give %1s %2s','pn'), pn_strip_input(is_isset($now_data, 'sum_give')), $give_currency_title);
													$com = pn_strip_input(is_isset($now_data, 'com_give'));
													if (strlen($com) > 0) {
														$text .= "\n" . sprintf(__('Your give %1s %2s %3s', 'pn'), pn_strip_input(is_isset($now_data, 'sum_give_com')), $give_currency_title, $com);
													}
													$text .= "\n" . sprintf(__('Your get %1s %2s','pn'), pn_strip_input(is_isset($now_data, 'sum_get')), $get_currency_title);
													$com = pn_strip_input(is_isset($now_data, 'com_get'));
													if (strlen($com) > 0) {
														$text .= "\n" . sprintf(__('Your get %1s %2s %3s','pn'), pn_strip_input(is_isset($now_data, 'sum_get_com')), $get_currency_title, $com);
													}												
													if (count($ld) > 0) {
														$text .= "\n" . __('Your data', 'pn');
														$text .= "\n" . implode("\n", $ld);
													}
													$in = array();	
													$in[] = array(
														array (
															'text' => __('Make an exchange with this data', 'pn'),
															'callback_data' => '/make_exchange',
														),
													);										
													$in[] = array(
														array (
															'text' => __('To change the data', 'pn'),
															'callback_data' => '/wantchange',
														),
													);	
													$stop = 1;
													tapibot_send($bot, $chat_data, $uniq_id, $settings, $text, $in);	
												} else {
													$stop = 1;
													tapibot_send($bot, $chat_data, $uniq_id, $settings, $apidata['error_text'], '');	
												}
											} else {
												$stop = 1;
												tapibot_send($bot, $chat_data, $uniq_id, $settings, $error_api_text, '');
											}										
										}									
									}
									
									$bid_id = intval(is_isset($info, 'bid_id'));
									if ($bid_id < 1 and !$stop) {
										
										$save_fields = is_isset($info, 'save_fields');
										if (!is_array($save_fields)) { $save_fields = array(); }
										
										$post_data = array();
										
										$fd = array();
										if (count($fields) > 0) {
											foreach ($fields as $field) {
												$field_name = is_isset($field, 'name');
												$field_type = is_isset($field, 'type');
												$save_field = is_isset($save_fields, $field_name);
												if ('select' == $field_type) {
													$text = pn_strip_input(is_isset($field, 'label'));
													$fd[$field_name] = $text;
													$post_data[$field_name] = $save_field;
												} elseif ('text' == $field_type or 'textarea' == $field_type) {
													$text = pn_strip_input(is_isset($field, 'label'));
													$fd[$field_name] = $text;
													$post_data[$field_name] = $save_field;												
												} elseif ('checkbox' == $field_type) {
													$nfg = intval($save_field);
													if ($nfg) {
														$post_data[$field_name] = is_isset($field, 'value');	
													}												
												}
											}
										}	
										$post_data['direction_id'] = $dir_data['id'];
										$post_data['calc_amount'] = is_sum(is_isset($info, 'amount'));
										
										$callback_secret = trim(get_option('tapibots_callback_secret'));
										if (!$callback_secret) {
											$callback_secret = get_random_password(16);
											update_option('tapibots_callback_secret', $callback_secret);
										}	
										
										$post_data['callback_url'] = get_api_link('tapibot', 'v1', 'callback') . '?bot_id=' . $bot_id . '&u_id=' . $uniq_id . '&sk=' . md5($callback_secret);
										$set_currency = intval(is_isset($info, 'set_currency'));
										if (1 == $set_currency) {
											$calc_action = 1;
										} else {
											$calc_action = 2;
										}									
										$post_data['calc_action'] = $calc_action;
										$apidata = tapibot_command($log_bot_id, 'create_bid', $bot->api_server, $bot->api_version, $bot->api_lang, $bot->api_login, $bot->api_key, $bot->api_partner_id, $post_data);
										if (isset($apidata['error'], $apidata['data']) and is_array($apidata['data'])) {
											if (0 == $apidata['error']) {
												$now_data = $apidata['data'];
												if (isset($now_data['id'])) {
														
													$info['bid_id'] = intval($now_data['id']);
													$bid_data = $now_data;
														
													$barr = array();
													$arr['tapibot_id'] = $bot_id;
													$arr['uniq_id'] = $uniq_id;
													$arr['bid_id'] = $info['bid_id'];
													$wpdb->insert($wpdb->prefix . "tapibot_bids", $arr);
														
												} else {
													$stop = 1;
													tapibot_send($bot, $chat_data, $uniq_id, $settings, __('Bid not created', 'pn'), '');
												}
											} else {
												$stop = 1;
												if (isset($apidata['error_fields']) and is_array($apidata['error_fields']) and count($apidata['error_fields']) > 0) {
													$not_correct = array();
													foreach ($apidata['error_fields'] as $ef => $ef_title) {
														if ('sum1' == $ef or 'sum2' == $ef) {
															$not_correct['amount_error'] = __('Amount error', 'pn') . ': ' . $ef_title;
														} elseif ('sum1c' != $ef or 'sum2c' != $ef) {	
															$not_correct[] = is_isset($fd, $ef) . ': ' . $ef_title;
														}
													}
													$text = implode("\n", $not_correct); 
													$in = array();										
													$in[] = array(
														array (
															'text' => __('To change the data', 'pn'),
															'callback_data' => '/wantchange',
														),
													);											
													tapibot_send($bot, $chat_data, $uniq_id, $settings, $text, $in);												
												} else {
													tapibot_send($bot, $chat_data, $uniq_id, $settings, $apidata['error_text']);	
												}
											}
										} else {
											$stop = 1;
											tapibot_send($bot, $chat_data, $uniq_id, $settings, $error_api_text, '');
										}																				
									}
									
									$bid_id = intval(is_isset($info, 'bid_id'));
									if (!$stop and $bid_id > 0) {
										$command = '/checkbid_' . $bid_id;
										$info = array();
									}	
								}
							} 
							
							if (strstr($command, '/cancelbid_')) {
								$check_bid_id = intval(str_replace('/cancelbid_', '', $command));
								$command = '';
								$b_error = 1;
								if ($check_bid_id > 0) {
									$has = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "tapibot_bids WHERE tapibot_id='$bot_id' AND uniq_id='$uniq_id' AND bid_id='$check_bid_id'");
									if ($has) {
										$b_error = 0;

										$apidata = tapibot_command($log_bot_id, 'cancel_bid', $bot->api_server, $bot->api_version, $bot->api_lang, $bot->api_login, $bot->api_key, $bot->api_partner_id, array('id' => $check_bid_id));
										if (isset($apidata['error'], $apidata['data'])) {
											if (0 == $apidata['error']) {														
												tapibot_send($bot, $chat_data, $uniq_id, $settings, __('You canceled the application', 'pn'));
												/* $command = '/checkbid_' . $check_bid_id; */
											} else {
												tapibot_send($bot, $chat_data, $uniq_id, $settings, $apidata['error_text']);	
											}
										} else {
											tapibot_send($bot, $chat_data, $uniq_id, $settings, $error_api_text);
										}

									}
								} 
								if ($b_error) {
									tapibot_send($bot, $chat_data, $uniq_id, $settings, __('Bid not exists', 'pn'));
								}
							}
							
							if (strstr($command, '/paidbid_')) {
								$check_bid_id = intval(str_replace('/paidbid_', '', $command));
								$command = '';
								$b_error = 1;
								if ($check_bid_id > 0) {
									$has = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "tapibot_bids WHERE tapibot_id='$bot_id' AND uniq_id='$uniq_id' AND bid_id='$check_bid_id'");
									if ($has) {
										$b_error = 0;

										$apidata = tapibot_command($log_bot_id, 'pay_bid', $bot->api_server, $bot->api_version, $bot->api_lang, $bot->api_login, $bot->api_key, $bot->api_partner_id, array('id' => $check_bid_id));
										if (isset($apidata['error'], $apidata['data'])) {
											if (0 == $apidata['error']) {														
												tapibot_send($bot, $chat_data, $uniq_id, $settings, __('You have confirmed the payment on the application', 'pn'));
												/* $command = '/checkbid_' . $check_bid_id; */
											} else {
												tapibot_send($bot, $chat_data, $uniq_id, $settings, $apidata['error_text']);	
											}
										} else {
											tapibot_send($bot, $chat_data, $uniq_id, $settings, $error_api_text);
										}	

									}
								} 
								if ($b_error) {
									tapibot_send($bot, $chat_data, $uniq_id, $settings, __('Bid not exists', 'pn'));
								}								
							}	
							
							if (strstr($command, '/checkbid_')) {
								$check_bid_id = intval(str_replace('/checkbid_', '', $command));
								$command = '';
								$b_error = 1;
								if ($check_bid_id > 0) {
									$has = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "tapibot_bids WHERE tapibot_id='$bot_id' AND uniq_id='$uniq_id' AND bid_id='$check_bid_id'");
									if ($has) {
										$b_error = 0;
										tapibot_check_bidstatus($bid_data, $check_bid_id, $bot, $log_bot_id, $settings, $uniq_id);
									}
								} 
								if ($b_error) {
									tapibot_send($bot, $chat_data, $uniq_id, $settings, __('Bid not exists', 'pn'));
								}								
							}
								
							$chat_id = intval(is_isset($chat, 'id'));
							$arr = array();
							$arr['now_info'] = pn_json_encode(pn_strip_input_array($info));
							$arr['save_info'] = pn_json_encode(pn_strip_input_array($save_info));
							$wpdb->update($wpdb->prefix . "tapibot_chats", $arr, array('id' => $chat_id));
							
							$json = array(
								'error' => 0,
								'error_text' => 'OK',
							);
							echo pn_json_encode($json);
							exit;
						}
					}
				}	
			}
			
			echo pn_json_encode($json);
			exit;
		}
		
	}
}