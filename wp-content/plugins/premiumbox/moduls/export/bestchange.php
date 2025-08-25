<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_export_bestchange', 'pn_admin_title_pn_export_bestchange');
	function pn_admin_title_pn_export_bestchange($page) {
		
		return __('Bestchange Export/Import', 'pn');
	} 

 	add_action('pn_adminpage_content_pn_export_bestchange', 'def_pn_admin_content_pn_export_bestchange');
	function def_pn_admin_content_pn_export_bestchange() {
		global $wpdb;
		
		if (current_user_can('administrator') or current_user_can('pn_impexp_bestchange')) {
	?>
	<div class="premium_body">	
		<form method="post" target="_blank" action="<?php the_pn_link('export_bestchange', 'post'); ?>">
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
	<?php }  ?>

	<?php if (current_user_can('administrator') or current_user_can('pn_impexp_bestchange')) { ?>
	<div class="premium_body">	
		<form method="post" target="_blank" action="<?php the_pn_link('import_bestchange', 'post'); ?>" enctype="multipart/form-data">
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
	<?php } ?>	
	<?php
	}

}

	add_action('premium_action_export_bestchange', 'def_premium_action_export_bestchange');
	function def_premium_action_export_bestchange() {
		global $wpdb, $premiumbox;	

		header('Content-Type: text/html; charset=' . get_charset());

		pn_only_caps(array('administrator', 'pn_impexp_bestchange'));

		$path = $premiumbox->upload_dir . '/';		
			
		$file = $path . 'bestchange_export-' . date('Y-m-d-H-i') . '.csv';           
		$fs = @fopen($file, 'w');
		
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' ORDER BY id DESC");
		
		$content = '';
			
		$array = array(
			'direction_id' => __('Direction id', 'pn'), 
			'bestchange' => __('Bestchange', 'pn'),
			'v1' => __('Currency id give', 'pn'),
			'v2' => __('Currency id get', 'pn'),
			'city_id' => __('City id', 'pn'),
			'pars_position' => __('Position', 'pn'),
			'min_res' => __('Min reserve for position', 'pn'),
			'step' => __('Step', 'pn'),
			'min_sum' => __('Min rate', 'pn'),
			'max_sum' => __('Max rate', 'pn'),
			'reset_course' => __('Max rate', 'pn'),
			'standart_course_give' => __('Standart rate Send', 'pn'),
			'standart_course_get' => __('Standart rate Receive', 'pn'),
		);
				
		$csv_title = '';
		$csv_key = '';
		foreach ($array as $k => $v) {
			$csv_title .= '"' . get_cptgn($v) . '";';
			$csv_key .= '"' . get_cptgn($k) . '";'; 
		}	
				
		$content .= $csv_title . "\n";
		$content .= $csv_key . "\n";
	
		foreach ($items as $item) {
			$b_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bestchange_directions WHERE direction_id = '{$item->id}'");
			
			$line = '';
						
			foreach ($array as $k => $v) {
				$line .= '"';
				
				if ('direction_id' == $k) {
					$line .= $item->id;
				} elseif ('bestchange' == $k) {
					$line .= $item->bestchange_id;
				} else {
					$line .= rez_exp(is_isset($b_data, $k));
				}
				
				$line .= '";';
			}
						
			$line .= "\n";
			$content .= $line;
		}
			
		@fwrite($fs, $content);
		@fclose($fs);	
		
		pn_download_file($file, basename($file), 1);
		
		pn_display_mess(__('Error! Unable to create file!', 'pn'));	
		
	}

	add_action('premium_action_import_bestchange', 'def_premium_action_import_bestchange');
	function def_premium_action_import_bestchange() {
		global $wpdb, $premiumbox;	

		pn_only_caps(array('administrator', 'pn_impexp_bestchange'));
		
		$premit_ext = array(".csv");
		if (isset($_FILES['importfile'], $_FILES['importfile']['name'])) {
			$ext = strtolower(strrchr($_FILES['importfile']['name'], "."));
			if (in_array($ext, $premit_ext)) {
					
				$max_mb = pn_max_upload();
				$max_upload_size = $max_mb * 1024 * 1024;
					
				if ($_FILES["importfile"]["size"] > 0 and $_FILES["importfile"]["size"] < $max_upload_size) {
					$tempFile = $_FILES['importfile']['tmp_name'];
					$filename = pn_strip_symbols(time() . $_FILES['importfile']['name'], '.');

					$path = $premiumbox->upload_dir . '/';
						
					$targetFile =  str_replace('//', '/', $path) . $filename;
						
					if (move_uploaded_file($tempFile, $targetFile)) {
						
						$error = 0;
							
						$result = file_get_contents($targetFile);
						$lines = explode("\n", $result);
						if (count($lines) > 1) {
								
							$file_map = array();
							$csv_keys = explode(';', is_isset($lines, 1));
							foreach ($csv_keys as $csv_k => $csv_v) {
								$file_map[$csv_k] = rez_exp($csv_v);
							}
							
							$r = -1;
							
							foreach ($lines as $line) { $r++;
								if ($r > 1) {	
									$line = get_tgncp(trim($line));
									if ($line) {
										
										$db_array = array();
											
										$items = explode(';', $line);
										foreach ($items as $item_key => $item) {
											if (isset($file_map[$item_key])) {
												$item = rez_exp($item);	
												$db_key = $file_map[$item_key];
												$db_array[$db_key] = pn_maxf_mb(pn_strip_input($item), 500);
											}
										}	
												
										if (count($db_array) > 0) {
											
											$direction_id = intval(is_isset($db_array, 'direction_id'));
											$bestchange = intval(is_isset($db_array, 'bestchange'));
											$id_v1 = intval(is_isset($db_array, 'v1'));
											$id_v2 = intval(is_isset($db_array, 'v2'));
											$city_id = intval(is_isset($db_array, 'city_id'));
											$pars_position = pn_strip_input(is_isset($db_array, 'pars_position'));											
											$min_res = is_sum(is_isset($db_array, 'min_res'));
											$step = pn_parser_num(is_isset($db_array, 'step'));
											$min_sum = is_sum(is_isset($db_array, 'min_sum'));
											$max_sum = is_sum(is_isset($db_array, 'max_sum'));
											$reset_course = intval(is_isset($db_array, 'reset_course'));
											$standart_course_give = is_sum(is_isset($db_array, 'standart_course_give'));
											$standart_course_get = is_sum(is_isset($db_array, 'standart_course_get'));
											
											$wpdb->query("DELETE FROM " . $wpdb->prefix . "bestchange_directions WHERE direction_id = '$direction_id'");
												
											if ($bestchange) {
												$array = array();
												$array['status'] = 1;
												$array['direction_id'] = 0;
												$array['currency_id_give'] = 0;
												$array['currency_id_get'] = 0;
												$direction = '';
												if ($direction_id) {
													$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$direction_id'");
													if (isset($direction->id)) {
														$array['direction_id'] = $direction->id;
														$array['currency_id_give'] = $direction->currency_id_give;
														$array['currency_id_get'] = $direction->currency_id_get;			
													} else {
														$direction_id = 0;
													}
												}

												$array['v1'] = $id_v1;
												$array['v2'] = $id_v2;
												$array['city_id'] = $city_id;
		
												$array['pars_position'] = $pars_position;
												$array['min_res'] = $min_res;
												$array['step'] = $step;
												$array['reset_course'] = $reset_course;
												$array['min_sum'] = $min_sum;
												$array['max_sum'] = $max_sum;
												$array['standart_course_give'] = $standart_course_give;
												$array['standart_course_get'] = $standart_course_get;
												if ($array['direction_id'] > 0) {
													$wpdb->insert($wpdb->prefix . 'bestchange_directions', $array);
													$wpdb->update($wpdb->prefix . "directions", array('bestchange_id' => 1), array('id' => $array['direction_id']));
												}
											}
										}
									}	
								}
							}								
						} 
							
						if (0 == $error) {
							
							if (is_file($targetFile)) {
								@unlink($targetFile);
							}
								
							$url = admin_url('admin.php?page=pn_export_bestchange&reply=true');
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