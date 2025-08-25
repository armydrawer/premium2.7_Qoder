<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_export_parsers', 'pn_admin_title_pn_export_parsers');
	function pn_admin_title_pn_export_parsers($page) {
		
		return __('Parser pairs Export/Import', 'pn');
	} 

	add_action('pn_adminpage_content_pn_export_parsers', 'def_pn_admin_content_pn_export_parsers');
	function def_pn_admin_content_pn_export_parsers() {
		global $wpdb;
	?>
	<div class="premium_body">	
		<form method="post" target="_blank" action="<?php the_pn_link('export_parsers', 'post'); ?>">
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

	<div class="premium_body">	
		<form method="post" target="_blank" action="<?php the_pn_link('import_parsers', 'post'); ?>" enctype="multipart/form-data">
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

add_action('premium_action_export_parsers', 'def_premium_action_export_parsers');
function def_premium_action_export_parsers() {
	global $wpdb, $premiumbox;	

	pn_only_caps(array('administrator', 'pn_impexp_parser'));	

	$path = $premiumbox->upload_dir . '/';		
			
	$file = $path . 'parsersexport-' . date('Y-m-d-H-i') . '.csv';           
	$fs = @fopen($file, 'w');
		
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "parser_pairs");
		
	$content = '';
			
	$array = array(
		'id' => '', 
		'title_pair_give' => '',
		'title_pair_get' => '',
		'title_birg' => '',
		'pair_give' => '',
		'pair_get' => '',
		'course1' => '',
		'course2' => '',
	);
				
	$en = array();
	$csv_title = '';
	$csv_key = '';
	foreach ($array as $k => $v) {
		$en[] = $k;
		$csv_title .= '"' . get_cptgn($v) . '";';
		$csv_key .= '"' . get_cptgn($k) . '";'; 
	}	
				
	//$content .= $csv_title."\n";
	$content .= $csv_key . "\n";
	
	if (count($en) > 0) {
		foreach ($items as $item) {
			$line = '';
						
			foreach ($en as $key) {
				$line .= '"';
				if ('course1' == $key) {
					$line .= get_parser_course($item->pair_give);
				} elseif ('course2' == $key) {
					$line .= get_parser_course($item->pair_get);
				} else {
					$line .= get_cptgn(rez_exp(ctv_ml(is_isset($item, $key))));
				}
				$line .= '";';
			}
						
			$line .= "\n";
			$content .= $line;
		}
	}
			
	@fwrite($fs, $content);
	@fclose($fs);	
		
	pn_download_file($file, basename($file), 1);
		
	pn_display_mess(__('Error! Unable to create file!', 'pn'));
	
}

add_action('premium_action_import_parsers', 'def_premium_action_import_parsers');
function def_premium_action_import_parsers() {
	global $wpdb, $premiumbox;	

	pn_only_caps(array('administrator', 'pn_impexp_parser'));
	
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
						$csv_keys = explode(';', is_isset($lines, 0));
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
											if ($db_key) {
												$db_array[$db_key] = pn_maxf_mb(pn_strip_input($item), 500);
											}
										}
									}	
												
									if (count($db_array) > 0) {
											
										$data_id = intval(is_isset($db_array, 'id'));
										if (isset($db_array['course1'])) {
											unset($db_array['course1']);
										}
										if (isset($db_array['course2'])) {
											unset($db_array['course2']);
										}											

										if (count($db_array) > 0) {
											$vd = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "parser_pairs WHERE id = '$data_id'");
											if (isset($vd->id)) {
												$wpdb->update($wpdb->prefix . 'parser_pairs', $db_array, array('id' => $data_id));
											} else {
												$wpdb->insert($wpdb->prefix . 'parser_pairs', $db_array);
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
								
						$url = admin_url('admin.php?page=pn_export_parsers&reply=true');
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