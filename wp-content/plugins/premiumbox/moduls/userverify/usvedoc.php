<?php
if (!defined('ABSPATH')) { exit(); }

function usve_js() {
	
	$max_mb = pn_max_upload();
	$max_upload_size = $max_mb * 1024 * 1024;
?>
	$(document).on('change', '.usveupfilesome', function() {
		
		var thet = $(this);
		var text = thet.val();
		var par = thet.parents('form');
		var ccn = thet[0].files.length;
		if (ccn > 0) {
            var fileInput = thet[0];
			var bitec = fileInput.files[0].size;		
			if (bitec > <?php echo $max_upload_size; ?>) {
				alert('<?php _e('Max.','pn'); ?> <?php echo $max_mb; ?> <?php _e('MB', 'pn'); ?> !');
				thet.val('');
			} else {
				par.submit();
			}
		}	
		
	});
	
    $('.usveajaxform').ajaxForm({
	    dataType:  'json',
        beforeSubmit: function(a, f, o) {
			
			$('.usveajaxform.upload_form').removeClass('upload_form');
		    f.addClass('upload_form');
			$('.upload_form').find('.ustbl_res').html(' ');
			$('input.usveupfilesome').prop('disabled', true);
			$('.upload_form').find('.ustbl_bar').show();
			$('.upload_form').find('.ustbl_bar_abs').width('0px');
			
        },
		error: function(res, res2, res3) {
			<?php do_action('pn_js_error_response', 'form'); ?>
		},		
		uploadProgress: function(event, position, total, percentComplete) {
			
			var percentVal = percentComplete + '%';
            $('.upload_form').find('.ustbl_bar_abs').width(percentVal);
			
		},	
        success: function(res, res2, res3) { 
		
			if (res['status'] == 'error') {
				$('.upload_form').find('.ustbl_res').html('<div class="ustbl_res_error">' + res['status_text'] + '</div>');
		    }
			
			if (res['response']) {
				$('.upload_form').find('.ustbl_res').html(res['response']); 
			}		
			
			if (res['url']) {
				window.location.href = res['url']; 
			}
			
			$('input.usveupfilesome').prop('disabled', false);
			$('.upload_form').find('.ustbl_bar').hide();
			
        }
    });

    $(document).on('click', '.usvefilelock_delete', function() {
		var id = $(this).attr('data-id');
		var thet = $(this);
		if (!thet.hasClass('active')) {
			if (confirm("<?php _e('Are you sure you want to delete the file?', 'pn'); ?>")) {
				thet.addClass('active');
				var param = 'id=' + id;
				$.ajax({
					type: "POST",
					url: "<?php echo get_pn_action('delete_userverify_file'); ?>",
					dataType: 'json',
					data: param,
					error: function(res, res2, res3) {
						<?php do_action('pn_js_error_response', 'ajax'); ?>
					},			
					success: function(res)
					{
						if (res['status'] == 'success') {
							thet.parents('.ustbl_res').html(' ');
						} 
						if (res['status'] == 'error') {
							<?php do_action('pn_js_alert_response'); ?>
						}
							
						thet.removeClass('active');
					}
				});
			}
		}
		
        return false;
    });			
<?php
}

function download_usve_file($log, $data_id, $theid, $user_id) {
	global $wpdb;	
		
	$plugin = get_plugin_class();
		
	if (is_array($_FILES) and isset($_FILES['file'], $_FILES['file']['name'])) {
		
		$ext = pn_mime_filetype($_FILES['file']);
		$tempFile = $_FILES['file']['tmp_name'];
							
		$max_mb = pn_max_upload();
		$max_upload_size = $max_mb * 1024 * 1024;
		$fileupform = pn_enable_filetype();	
						
		$ext_old = strtolower(strrchr($_FILES['file']['name'], "."));
		if (isset($fileupform[$ext_old])) {
			$fi = @getimagesize($_FILES['file']['tmp_name']);
			$mtype = is_isset($fi, 'mime');
			if (in_array($mtype, $fileupform)) {
				if (isset($fileupform[$ext])) {
					if ($_FILES["file"]["size"] > 0 and $_FILES["file"]["size"] < $max_upload_size) {
											
						$filename = time() . '_' . pn_strip_symbols(replace_cyr($_FILES['file']['name']), '.');				
										
						$path = $plugin->upload_dir . '/';
						$path2 = $path . 'userverify/';
						$path3 = $path . 'userverify/' . $data_id . '/';
						if (!is_dir($path)) { 
							@mkdir($path , 0777);
						}
						if (!is_dir($path2)) { 
							@mkdir($path2 , 0777);
						}	
						if (!is_dir($path3)) { 
							@mkdir($path3 , 0777);
						}

						$htacces = $path2 . '.htaccess';
						if (!is_file($htacces)) {
							$nhtaccess = "Order allow,deny \n Deny from all";
							$file_open = @fopen($htacces, 'w');
							@fwrite($file_open, $nhtaccess);
							@fclose($file_open);		
						}								

						$targetFile =  str_replace('//', '/', $path3) . $filename;
						if (is_debug_mode()) {
							$result = move_uploaded_file($tempFile, $targetFile);
						} else {
							$result = @move_uploaded_file($tempFile, $targetFile);
						}
							
						if ($result) {
									
							if (is_debug_mode()) {
								$fdata = file_get_contents($targetFile);
							} else {
								$fdata = @file_get_contents($targetFile);
							}									
							$fdata = str_replace('*', '%star%', $fdata);
								
							if (is_file($targetFile)) {
								@unlink($targetFile);
							}									
												
							$olddata = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_field_user WHERE uv_id = '$data_id' AND uv_field = '$theid'");
													
							$arr = array();
							$arr['user_id'] = $user_id;
							$arr['uv_data'] = $filename;
							$arr['uv_id'] = $data_id;
							$arr['uv_field'] = $theid;
							$arr['fieldvid'] = 1;
													
							if (isset($olddata->id)) {										
								$wpdb->update($wpdb->prefix . 'uv_field_user', $arr, array('id' => $olddata->id));
								$uv_field_user_id = $olddata->id;
							} else {									
								$wpdb->insert($wpdb->prefix . 'uv_field_user', $arr);
								$uv_field_user_id = $wpdb->insert_id;
							}
												
							if ($uv_field_user_id) {
									
								$file = $plugin->upload_dir . '/userverify/' . $data_id . '/' . $uv_field_user_id . '.php';
								$file_text = add_phpf_data($fdata);
										
								$file_open = @fopen($file, 'w');
								@fwrite($file_open, $file_text);
								@fclose($file_open);
													
								if (!is_file($file)) {
									$wpdb->query("DELETE FROM " . $wpdb->prefix . "uv_field_user WHERE id = '$uv_field_user_id'");
									$log['status'] = 'error';
									$log['status_code'] = 1;
									$log['status_text'] = __('Error! Error loading file', 'pn');	
									echo pn_json_encode($log);
									exit;
								}												
							}			
						
							if (_is('is_adminaction')) {				
								$wpdb->query("UPDATE " . $wpdb->prefix . "uv_field_user SET user_id = '$user_id' WHERE uv_id = '$data_id'");
							}					
							$log['response'] = get_usvedoc_temp($data_id, $theid);
												
						} else {
							$log['status'] = 'error';
							$log['status_code'] = 1;
							$log['status_text'] = __('Error! Error loading file', 'pn');
						}
					} else {
						$log['status'] = 'error';
						$log['status_code'] = 1;
						$log['status_text'] = __('Max.', 'pn') . ' ' . $max_mb . ' ' . __('MB', 'pn') . '!';			
					}
				} else {
					$log['status'] = 'error';
					$log['status_code'] = 1;
					$log['status_text'] = __('Error! Incorrect file format', 'pn');					
				}
			} else {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error! Incorrect file format', 'pn');					
			}
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! Incorrect file format', 'pn');
		}
	}	
	return $log;
}

function get_usvedoc_temp($id, $field_id) {
	global $wpdb;

	$temp = '';

	$id = intval($id);
	if ($id < 1) { $id = 0; }
	$field_id = intval($field_id);
		
	$userverify = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_field_user WHERE uv_id = '$id' AND uv_field = '$field_id' AND fieldvid = '1'");
	if (isset($userverify->id)) {
		$file = pn_strip_input($userverify->uv_data);
		if ($file) {
			$temp .= '
			<div class="usvefilelock">
				<div class="usvefilelock_delete" data-id="' . $userverify->id . '"></div>
					<a href="' . get_usve_doc($userverify->id) . '" target="_blank">' . $file . '</a>
			';
					
				if (current_user_can('administrator') or current_user_can('pn_userverify')) {
					$temp .= ' | <a href="' . get_usve_doc_view($userverify->id) . '" target="_blank">' . __('View', 'pn') . '</a>';
				}
					
			$temp .= '
				</div>	
			';
		} 
	}
		
	return $temp;
}

function get_usve_doc($uv_field_user_id) {
	
	return get_pn_action('usvedoc').'&id='. $uv_field_user_id;
}

function get_usve_doc_view($uv_field_user_id) {
	
	return pn_link('usvedoc_view').'&id='. $uv_field_user_id;
}

add_action('premium_siteaction_usvedoc', 'def_premium_siteaction_usvedoc');
function def_premium_siteaction_usvedoc() {
	global $wpdb; 

	$plugin = get_plugin_class();

	$plugin->up_mode();

	$id = intval(is_param_get('id'));
	if ($id < 1) {
		pn_display_mess(__('Error!', 'pn'));
	}
		
	$dostup = 0;
	$where = " AND fieldvid='1'";
	if (current_user_can('administrator') or current_user_can('pn_userverify')) {
		$dostup = 1;
		$where = "";
	}	

	$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_field_user WHERE id = '$id' $where");

	$file_id = intval(is_isset($data, 'id'));

	if ($file_id < 1) {
		pn_display_mess(__('Error!', 'pn'));
	}

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
		
	if ($data->user_id == $user_id and $user_id > 0) {
		$dostup = 1;
	}

	if (1 != $dostup) {
		pn_display_mess(__('Error! Access denied', 'pn'));
	}

	$file = $plugin->upload_dir . '/userverify/' . $data->uv_id . '/' . $file_id . '.php';

	if (is_file($file)) {

		$ext = strtolower(strrchr($data->uv_data, "."));
		$fileupform = pn_enable_filetype();
		$mtype = is_isset($fileupform, $ext);

		$fdata = @file_get_contents($file);
		$fdata = str_replace('%star%', '*', $fdata);
		$fdata = get_phpf_data($fdata);
		$fdata = pn_string($fdata);

		if (ob_get_level()) {
			ob_end_clean();
		}
		header('Content-Type: ' . $mtype . '; charset=' . get_charset());
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . $data->uv_data);
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . strlen($fdata));

		echo $fdata;
		exit;
	} 
		
	pn_display_mess(__('Error! File does not exist', 'pn'));
}

add_action('premium_action_usvedoc_view', 'def_premium_action_usvedoc_view');
function def_premium_action_usvedoc_view() {
	global $wpdb; 

	$plugin = get_plugin_class();

	pn_only_caps(array('administrator', 'pn_userverify'));	

	$id = intval(is_param_get('id'));
	if ($id < 1) {
		pn_display_mess(__('Error!', 'pn'));
	}

	$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_field_user WHERE id = '$id'");
	$file_id = intval(is_isset($data, 'id'));

	if ($file_id < 1) {
		pn_display_mess(__('Error!', 'pn'));
	}

	$file = $plugin->upload_dir . '/userverify/' . $data->uv_id . '/' . $file_id . '.php';
		
	if (is_file($file)) {

		$fdata = @file_get_contents($file);
		$fdata = str_replace('%star%', '*', $fdata);
		$fdata = get_phpf_data($fdata);
		$fdata = pn_string($fdata);

		$ext = strtolower(strrchr($data->uv_data, "."));
		$fileupform = pn_enable_filetype();
		$mtype = is_isset($fileupform, $ext);

		header('Content-Type: ' . $mtype . '; charset=' . get_charset());

		echo $fdata;
		exit;

	} 
	
	pn_display_mess(__('Error! File does not exist', 'pn'));
}