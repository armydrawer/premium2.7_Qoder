<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_export_currency', 'pn_admin_title_pn_export_currency');
	function pn_admin_title_pn_export_currency($page) {
		
		return __('Currency Export/Import', 'pn');
	} 	
	
	add_action('pn_adminpage_content_pn_export_currency', 'def_pn_admin_content_pn_export_currency');
	function def_pn_admin_content_pn_export_currency() {
		global $wpdb;
		
		if (current_user_can('administrator') or current_user_can('pn_export_currency')) {
			?>
			<div class="premium_body">	
				<form method="post" target="_blank" action="<?php the_pn_link('export_currency', 'post'); ?>">
					<div class="premium_standart_line"> 
						<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Select data', 'pn'); ?></div></div>
						<div class="premium_stline_right"><div class="premium_stline_right_ins">
							<div class="premium_wrap_standart">
							
								<?php
								$scroll_lists = array();
								
								$array = array(
									'psys_title' => __('PS title', 'pn'),
									'currency_code_title' => __('Currency code', 'pn'),
									'xml_value' => __('XML name', 'pn'),
									'currency_decimal' => __('Amount of Decimal places', 'pn'),					
									'show_give' => __('Show field "From Account"', 'pn'),
									'show_get' => __('Show filed "Onto Account"', 'pn'),
									'currency_reserv' => __('Reserve', 'pn'),
									'currency_status' => __('Status', 'pn'),
									'currency_logo1' => __('Main logo', 'pn'),
								);
								if (get_settings_second_logo()) {
									$array['currency_logo2'] = __('Additional logo', 'pn');
								}
								$array = apply_filters('list_export_currency', $array);
								foreach ($array as $key => $val) {
									$scroll_lists[] = array(
										'title' => $val,
										'checked' => 0,
										'value' => $key,
									);
								}
								echo get_check_list($scroll_lists, 'data[]', '', '500', 1);
								?>

								<div class="premium_clear"></div>
							</div>							
						</div></div>
							<div class="premium_clear"></div>
					</div>
					<div class="premium_standart_line"> 
						<div class="premium_stline_left"></div>
						<div class="premium_stline_right"><div class="premium_stline_right_ins">
							<div class="premium_wrap_standart">
								<input type="submit" name="" class="button" value="<?php _e('Download', 'pn'); ?>" />
								<div class="premium_clear"></div>
							</div>							
						</div></div>
							<div class="premium_clear"></div>
					</div>	
				</form>	
			</div>
			<?php
		}

		if (current_user_can('administrator') or current_user_can('pn_import_currency')) {
			?>
			<div class="premium_body">	
				<form method="post" target="_blank" action="<?php the_pn_link('import_currency', 'post'); ?>" enctype="multipart/form-data">
					<div class="premium_standart_line"> 
						<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Import', 'pn'); ?></div></div>
						<div class="premium_stline_right"><div class="premium_stline_right_ins">
							<div class="premium_wrap_standart">
								<input type="file" name="importfile" value="" />
								<div class="premium_clear"></div>
							</div>
						</div></div>
							<div class="premium_clear"></div>
					</div>	
					<div class="premium_standart_line"> 
						<div class="premium_stline_left"></div>
						<div class="premium_stline_right"><div class="premium_stline_right_ins">
							<div class="premium_wrap_standart">
								<input type="submit" name="" class="button" value="<?php _e('Upload', 'pn'); ?>" />
								<div class="premium_clear"></div>
							</div>
						</div></div>
							<div class="premium_clear"></div>
					</div>	
				</form>
			</div>			
			<?php				
		}	
	
	} 	
	
}

add_action('premium_action_export_currency', 'def_premium_action_export_currency');
function def_premium_action_export_currency() {
	global $wpdb, $premiumbox;	

	pn_only_caps(array('administrator', 'pn_export_currency'));			
		
	$path = $premiumbox->upload_dir . '/';		
			
	$file = $path.'currencyexport-' . date('Y-m-d-H-i') . '.csv';           
	$fs = @fopen($file, 'w');
		
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' ORDER BY id DESC");
		
	$data = is_param_post('data');
			
	$content = '';
			
	$array = array(
		'id' => __('Identifier', 'pn'), 
		'psys_title' => __('PS title', 'pn'),
		'currency_code_title' => __('Currency code', 'pn'),
		'xml_value' => __('XML name', 'pn'),
		'currency_decimal' => __('Amount of Decimal places', 'pn'),					
		'show_give' => __('Show field "From Account"', 'pn'),
		'show_get' => __('Show filed "Onto Account"', 'pn'),
		'currency_reserv' => __('Reserve', 'pn'),
		'currency_status' => __('Status', 'pn'),
		'currency_logo1' => __('Main logo', 'pn'),
	);
	if (get_settings_second_logo()) {
		$array['currency_logo2'] = __('Additional logo', 'pn');
	}	
	$array = apply_filters('list_export_currency', $array);
				
	if (is_array($data)) {
				
		$en = array();
		$csv_title = '';
		$csv_key = '';
		foreach ($array as $k => $v) {
			if (in_array($k, $data) or 'id' == $k) {
				$en[] = $k;
				$csv_title .= '"' . get_cptgn(rez_exp($v)) . '";';
				$csv_key .= '"' . get_cptgn(rez_exp($k)) . '";';
			} 
		}	
				
		$content .= $csv_title . "\n";
		$content .= $csv_key . "\n";

		$export_filter = array(
			'int_arr' => array('id', 'currency_decimal'),
			'qw_arr' => array('show_give','show_get','currency_status'),
			'sum_arr' => array('currency_reserv'),
		);
		$export_filter = apply_filters('export_currency_filter', $export_filter);
			
		$qw_arr = (array)is_isset($export_filter, 'qw_arr');
		$sum_arr = (array)is_isset($export_filter, 'sum_arr');
		$int_arr = (array)is_isset($export_filter, 'int_arr');
				
		if (count($en) > 0) {
			foreach ($items as $item) {
				$line = '';
						
				foreach ($en as $key) {
					$line .= '"';
					
					if (in_array($key, $qw_arr)) {
						$line .= rez_exp(get_cptgn(get_exvar(is_isset($item, $key), array(__('no', 'pn'), __('yes', 'pn')))));
					} elseif (in_array($key, $sum_arr)) {
						$line .= rez_exp(rep_dot(is_isset($item, $key)));
					} elseif (in_array($key, $int_arr)) {
						$line .= intval(is_isset($item, $key));	
					} elseif ($key == 'currency_logo1') {
						$arr_logo = @unserialize(is_isset($item, 'currency_logo'));
						$logo = rez_exp(is_ssl_url(ctv_ml(is_isset($arr_logo, 'logo1'))));
						$line .= $logo;
					} elseif ($key == 'currency_logo2') {
						$arr_logo = @unserialize(is_isset($item, 'currency_logo'));
						$logo = rez_exp(is_ssl_url(ctv_ml(is_isset($arr_logo, 'logo2'))));
						$line .= $logo;
					} else {
						$line .= rez_exp(get_cptgn(ctv_ml(is_isset($item, $key))));
					}
						
					$line .= '";';
				}
						
				$line .= "\n";
				$content .= $line;
			}
		}
	}
			
	@fwrite($fs, $content);
	@fclose($fs);	
		
	pn_download_file($file, basename($file), 1);
		
	pn_display_mess(__('Error! Unable to create file!', 'pn'));	
	
}

add_action('premium_action_import_currency', 'def_premium_action_import_currency');
function def_premium_action_import_currency() {
	global $wpdb, $premiumbox;	

	pn_only_caps(array('administrator', 'pn_import_currency'));
		
	$premit_ext = array(".csv");
	if (isset($_FILES['importfile'], $_FILES['importfile']['name'])) {
		$ext = strtolower(strrchr($_FILES['importfile']['name'],"."));
		if (in_array($ext,$premit_ext)) {
					
			$max_mb = pn_max_upload();
			$max_upload_size = $max_mb * 1024 * 1024;
					
			if ($_FILES["importfile"]["size"] > 0 and $_FILES["importfile"]["size"] < $max_upload_size) {
				$tempFile = $_FILES['importfile']['tmp_name'];
				$filename = pn_strip_symbols(time() . $_FILES['importfile']['name'], '.');

				$path = $premiumbox->upload_dir . '/';
						
				$targetFile =  str_replace('//', '/', $path) . $filename;
						
				if (move_uploaded_file($tempFile, $targetFile)) {
						
					$error = 0;
							
					$array = array(
						'id' => __('Identifier', 'pn'),
						'psys_title' => __('PS title', 'pn'),
						'currency_code_title' => __('Currency code', 'pn'),
						'xml_value' => __('XML name', 'pn'),
						'currency_decimal' => __('Amount of Decimal places', 'pn'),							
						'show_give' => __('Show field "From Account"', 'pn'),
						'show_get' => __('Show filed "Onto Account"', 'pn'),
						'currency_reserv' => __('Reserve', 'pn'),
						'currency_status' => __('Status', 'pn'),
						'currency_logo1' => __('Main logo', 'pn'),
						'currency_logo2' => __('Additional logo', 'pn'),							
					);
					$array = apply_filters('list_export_currency', $array);					
						
					$allow_key = array();
					$nochecked_key = apply_filters('nochecked_export_currency', array('currency_logo1', 'currency_logo2'));
					foreach ($array as $k => $v) {
						if (in_array($k, $nochecked_key)) {
							$allow_key[] = $k;
						} else {
							$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "currency LIKE '{$k}'");
							if (1 == $query) {
								$allow_key[] = $k;
							}
						}
					}					
							
					$result = file_get_contents($targetFile);
					$lines = explode("\n", $result);
					if (count($lines) > 2) {
								
						$file_map = array();
						$csv_keys = explode(';', is_isset($lines, 1));
						foreach ($csv_keys as $csv_k => $csv_v) {
							$file_map[$csv_k] = rez_exp($csv_v);
						}
								
						$r = -1;
							
						$export_filter = array(
							'int_arr' => array('id', 'currency_decimal'),
							'qw_arr' => array('show_give','show_get','currency_status'),
							'sum_arr' => array('currency_reserv'),
						);
						$export_filter = apply_filters('export_currency_filter', $export_filter);						
							
						$int_arr = (array)$export_filter['int_arr'];
						$sum_arr = (array)$export_filter['sum_arr'];
						$qw_arr = (array)$export_filter['qw_arr'];						
							
						foreach ($lines as $line) { $r++;
							if ($r > 1) {	
								$line = get_tgncp(trim($line));
								if ($line) {
									$db_array = array();
											
									$items = explode(';',$line);
									foreach ($items as $item_key => $item) {
										$item = rez_exp($item);	
										$db_key = $file_map[$item_key];
										if (in_array($db_key, $allow_key)) {	
											if (in_array($db_key, $int_arr)) {
												$db_array[$db_key] = intval($item);
											} elseif (in_array($db_key, $sum_arr)) {	
												$db_array[$db_key] = is_sum($item);
											} elseif (in_array($db_key, $qw_arr)) {
												$db_array[$db_key] = intval(get_exvar(mb_strtolower($item), array(__('no', 'pn') => '0', __('yes', 'pn') => '1')));
											} else {
												$db_array[$db_key] = pn_maxf_mb(pn_strip_input($item), 500);
											}
										}
									}	
												
									if (count($db_array) > 0) {
										
										$currency_logo = '';
											
										if (isset($db_array['currency_logo1'])) {
											
											$currency_logo = array(
												'logo1' => $db_array['currency_logo1'],
												'logo2' => is_isset($db_array, 'currency_logo2'),
											);
											$db_array['currency_logo'] = @serialize($currency_logo);
												
											unset($db_array['currency_logo1']);
											if (isset($db_array['currency_logo2'])) {
												unset($db_array['currency_logo2']);
											}
										}
											
										$data_id = intval(is_isset($db_array, 'id'));
										if (isset($db_array['id'])) {
											unset($db_array['id']);
										}											
												
										$locale = get_locale();
												
										if (isset($db_array['psys_title']) and $db_array['psys_title']) {	
											$now = $db_array['psys_title'];
											if (is_ml()) {
												$now_ml = '[' . $locale . ':]' . $now . '[:' . $locale . ']';
											} else {
												$now_ml = $now;
											}	
											$psys_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "psys WHERE psys_title LIKE '%" . $now_ml . "%' OR psys_title = '$now'");
											if (isset($psys_data->id)) {
												$db_array['psys_id'] = $psys_data->id;
											} else {	
												$up_arr = array(
													'psys_title' => $db_array['psys_title'],
													'psys_logo' => $currency_logo,
												);
												$wpdb->insert($wpdb->prefix . 'psys', $up_arr);
												$db_array['psys_id'] = $wpdb->insert_id;
											}
										}
											
										if (isset($db_array['currency_code_title']) and $db_array['currency_code_title']) {
											$now = $db_array['currency_code_title'];
											$currency_code_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_codes WHERE currency_code_title = '$now'");
											if (isset($currency_code_data->id)) {
												$db_array['currency_code_id'] = $currency_code_data->id;
											} else {	
												$up_arr = array(
													'currency_code_title' => $db_array['currency_code_title'],
													'internal_rate' => '1',
												);
												$wpdb->insert($wpdb->prefix . 'currency_codes', $up_arr);
												$db_array['currency_code_id'] = $wpdb->insert_id;
											}
										}

										$install = 1;
										if ($data_id) {
											$vd = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE id = '$data_id'");
											if (isset($vd->id) and count($db_array) > 0) {
												$install = 0;
														
												if (isset($db_array['psys_title'])) {
													$db_array['psys_title'] = replace_value_ml($vd->psys_title, $db_array['psys_title'], $locale);
												}
												if (isset($db_array['currency_code_title'])) {
													$db_array['currency_code_title'] = replace_value_ml($vd->currency_code_title, $db_array['currency_code_title'], $locale);
												}
													
												if (count($db_array) > 0) {	
													$wpdb->update($wpdb->prefix.'currency', $db_array, array('id' => $data_id));
												}
											}
										}																							
												
										if (1 == $install) {
											if (isset($db_array['psys_id']) and isset($db_array['currency_code_id'])) {
												$wpdb->insert($wpdb->prefix . 'currency', $db_array);
											}
										}
											
										do_action('export_currency_end');
									}
								}	
							}
						}								
					} 
							
					if (0 == $error) {
						if (is_file($targetFile)) {
							@unlink($targetFile);
						}
								
						$url = admin_url('admin.php?page=pn_export_currency&reply=true');
						wp_redirect($url);
						exit;	
					}	
				} else {
					pn_display_mess(__('Error! Error loading file', 'pn'));
				}
			} else {
				pn_display_mess(__('Error! Incorrect file size!', 'pn'));
			}	
		} else {
			pn_display_mess(__('Error! Incorrect file format!', 'pn'));
		}
	} else {
		pn_display_mess(__('Error! File is not selected!', 'pn'));
	}		
}	 