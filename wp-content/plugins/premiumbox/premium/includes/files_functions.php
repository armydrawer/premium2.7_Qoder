<?php
if (!defined('ABSPATH')) { exit(); }

function get_hf_ext($file_name) {
	
	return strtolower(strrchr($file_name, "."));
}

function set_hf_js() {
	global $pn_hidefiles_js;
	
	$pn_hidefiles_js = 1;
}

function get_hf_filesize_info() {
	
	$max_mb = pn_max_upload();
	$max_upload_size = $max_mb * 1024 * 1024;
	$fileupform = pn_enable_filetype();
	$allowed = array();
	foreach ($fileupform as $file_k => $file_v) {
		$allowed[] = $file_k;
	}
	
	return strtoupper(implode(', ', $allowed)) . ', ' . __('max.', 'premium') . ' ' . $max_mb . '' . __('MB', 'premium');
}

add_action('admin_footer', 'hf_admin_js', 1000);
add_action('wp_footer', 'hf_admin_js', 1000);
function hf_admin_js() {
	global $pn_hidefiles_js;
		
	$pn_hidefiles_js = intval($pn_hidefiles_js);
	if ($pn_hidefiles_js) {	
		$max_mb = pn_max_upload();
		$max_upload_size = $max_mb * 1024 * 1024;
	?>
<div style="display: none;">
	<form method="post" class="upload_hf_form" enctype="multipart/form-data" action="<?php echo get_pn_action('hf_upload'); ?>">
		<input type="hidden" name="formid" id="upload_hf_form_id" value="" />
		<input type="hidden" name="type" id="upload_hf_form_type" value="" />
		<input type="hidden" name="id" id="upload_hf_form_valid" value="" />
		<input type="hidden" name="redir" id="upload_hf_form_redir" value="" />
		<div id="upload_hf_form_file"></div>
	</form>
</div>
<script type="text/javascript">
jQuery(function($) {
	
	$(document).on('change', '.js_hf_input', function() {
		
		var thet = $(this);
		var type = thet.parents('.form_hf').attr('data-type');
		var redir = thet.parents('.form_hf').attr('data-redir');
		var id = thet.parents('.form_hf').attr('data-id');
		var form_id = thet.parents('.form_hf').attr('id');
		var input_now = thet.parents('.js_hf_input_linewrap').html();
		var ccn = thet[0].files.length;
		if (ccn > 0) {
            var fileInput = thet[0];
			var bitec = fileInput.files[0].size;		
			if (bitec > <?php echo $max_upload_size; ?>) {
				alert('<?php _e('Max.', 'premium'); ?> <?php echo $max_mb; ?> <?php _e('MB', 'premium'); ?> !');
				thet.val('');
			} else {
				$('#upload_hf_form_type').val(type);
				$('#upload_hf_form_valid').val(id);
				$('#upload_hf_form_redir').val(redir);
				$('#upload_hf_form_id').val(form_id);
				
				$("#upload_hf_form_file").html('');
				
				let dt = new DataTransfer();
				dt.items.add(this.files[0]);

				let n_input = $('<input>', {
					type: 'file',
					name: thet.attr('name'),
					class: thet.attr('class'),
					id: thet.attr('id'),
					accept: thet.attr('accept'),
					multiple: thet.prop('multiple'),
					required: thet.prop('required'),
				})[0];
				n_input.files = dt.files;

				$("#upload_hf_form_file").append(n_input);
				
				/* thet.clone().appendTo("#upload_hf_form_file"); */
				
				$('.upload_hf_form').submit();
			}
		}	
		
	});

    $('.upload_hf_form').ajaxForm({
	    dataType:  'json',
        beforeSubmit: function(a, f, o) {
			
			var form_id = $('#upload_hf_form_id').val();
			$('#' + form_id).addClass('uploading');
			
			$('.form_hf input').prop('disabled', true);
			$('.form_hf').find('.js_hf_bar').show();
			$('.form_hf').find('.js_hf_bar_abs').width('0px');
			$('.js_hf_response').html('');
			
        },
		error: function(res, res2, res3) {
			<?php do_action('pn_js_error_response', 'ajax'); ?>
		},
		uploadProgress: function(event, position, total, percentComplete) {
			
			var form_id = $('#upload_hf_form_id').val();
			var percentVal = percentComplete + '%';
            $('#' + form_id).find('.js_hf_bar_abs').width(percentVal);
			
		},		
        success: function(res) {
			
			var form_id = $('#upload_hf_form_id').val();
			
            if (res['status'] == 'success') {
				$('#' + form_id).find('.js_hf_files').html(res['response']);
		    } 
			
			if (res['status'] == 'error') {
				$('#' + form_id + '_hf_response').html('<div class="resultfalse">' + res['status_text'] + '</div>');
		    } 
			
			if (res['url']) {
				window.location.href = res['url']; 
			}
            
            $('.js_hf_input').val('');
			$('.form_hf').find('.ustbl_bar').hide();
			$('.form_hf input').prop('disabled', false);
			$('#' + form_id).removeClass('uploading');
			
        }
    });		

    $(document).on('click', '.js_hf_del', function() {
		
		var id = $(this).attr('data-id');
		var insert_html = $(this).parents('.js_hf_files');
		var thet = $(this);
		$('.js_hf_response').html('');
		if (!thet.hasClass('act')) {
			if (confirm("<?php _e('Are you sure you want to delete the file?', 'premium'); ?>")) {
				thet.addClass('act');
				var param = 'id=' + id;
				$.ajax({
					type: "POST",
					url: "<?php echo get_pn_action('hf_delete'); ?>",
					dataType: 'json',
					data: param,
					error: function(res, res2, res3) {
						<?php do_action('pn_js_error_response', 'ajax'); ?>
					},			
					success: function(res)
					{
						if (res['status'] == 'success') {
							insert_html.html(res['response']);
						} 
						
						if (res['status'] == 'error') {
							<?php do_action('pn_js_error_response', 'ajax'); ?>
						}
						
						if (res['url']) {
							window.location.href = res['url']; 
						}

						thet.removeClass('act');
					}
				});
			}
		}
		
        return false;
    });
	
});
</script>
	<?php
	}
} 

add_action('premium_siteaction_hf_delete', 'def_premium_siteaction_hf_delete');
function def_premium_siteaction_hf_delete() {
	global $wpdb, $premiumbox;	
	
	_method('post');
	_json_head();

	$log = array();
	$log['status'] = 'error';
	$log['status_code'] = 1;
	$log['status_text'] = __('Error! File does not exist', 'premium');
	$log['response'] = '';
	
	$premiumbox->up_mode('post');
	
	$id = intval(is_param_post('id'));
	if ($id > 0) {
		$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "hidefiles WHERE id = '$id'");
		if (isset($data->id)) {
			$log = apply_filters('hf_delete_action', $log, $data);
		}
	}
	
	echo pn_json_encode($log);
	exit;
}

function hf_delete_item_files($type, $item_id) {
	global $wpdb;
	
	$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "hidefiles WHERE itemtype = '$type' AND item_id = '$item_id'");
	foreach ($items as $item) {
		hf_delete_file($type, $item->id);
	}
	
}

function hf_delete_file($type, $id) {
	global $wpdb, $premiumbox;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "hidefiles WHERE id = '$id'");
	$path = $premiumbox->upload_dir . '/' . $type . '/' . $id . '.php';
	if (is_file($path)) {
		@unlink($path);
	}
	
}

add_action('premium_siteaction_hf_upload', 'def_premium_siteaction_hf_upload');
function def_premium_siteaction_hf_upload() {
	global $wpdb, $premiumbox;	
	
	_method('post');
	_json_head();

	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0;
	$log['status_text'] = '';
	$log['response'] = '';
	
	$premiumbox->up_mode('post');
	
	$log = apply_filters('hf_upload_action', $log, pn_strip_input(is_param_post('type')), pn_strip_input(is_param_post('id')), pn_strip_input(is_param_post('redir')));		
	
	echo pn_json_encode($log);
	exit;
}

function hf_upload_file($type, $id) {
	global $wpdb, $premiumbox;
	
	$res = array(
		'err' => 1,
		'err_text' => __('No file', 'premium'),
	);
	
	if (isset($_FILES['file'], $_FILES['file']['name'])) {
		$ext = pn_mime_filetype($_FILES['file']);
		$tempFile = $_FILES['file']['tmp_name'];
								
		$max_mb = pn_max_upload();
		$max_upload_size = $max_mb * 1024 * 1024;
		$fileupform = pn_enable_filetype();
		
		$ext_old = get_hf_ext($_FILES['file']['name']);
		if (isset($fileupform[$ext_old])) {
			$fi = @getimagesize($_FILES['file']['tmp_name']);
			$mtype = is_isset($fi, 'mime');
			
			$res['err_text'] = __('Error! Incorrect file format', 'premium');
			
			if (in_array($mtype, $fileupform)) {
				if (isset($fileupform[$ext])) {
					if ($_FILES["file"]["size"] > 0 and $_FILES["file"]["size"] < $max_upload_size) {

						$filename_old = pn_strip_symbols(replace_cyr($_FILES['file']['name']), '.');
						$filename = mt_rand(1000, 5000) . time();
														
						$path = $premiumbox->upload_dir . '/';
						$path2 = $path . $type . '/';	
						if (!is_dir($path)) { 
							@mkdir($path , 0777);
						}
						if (!is_dir($path2)) { 
							@mkdir($path2 , 0777);
						}	

						$htacces = $path2 . '.htaccess';
						if (!is_file($htacces)) {
							$nhtaccess = "Order allow,deny \n Deny from all";
							$file_open = @fopen($htacces, 'w');
							@fwrite($file_open, $nhtaccess);
							@fclose($file_open);		
						}													

						$targetFile =  str_replace('//', '/', $path2) . $filename;
						if (is_debug_mode()) {
							$result = @move_uploaded_file($tempFile, $targetFile);
						} else {
							$result = move_uploaded_file($tempFile, $targetFile);
						}										
						if ($result) {
												
							if (is_debug_mode()) {
								$fdata = @file_get_contents($targetFile);
							} else {
								$fdata = file_get_contents($targetFile);
							}
												
							$fdata = str_replace('*', '%star%', $fdata);
							if (is_file($targetFile)) {
								@unlink($targetFile);
							}
															
							$arr = array();
							$ui = wp_get_current_user();
							$arr['user_id'] = intval($ui->ID);
							$arr['itemtype'] = $type;
							$arr['file_name'] = $filename_old;
							$arr['file_ext'] = $ext_old;
							$arr['item_id'] = $id;							
							$wpdb->insert($wpdb->prefix . 'hidefiles', $arr);
							$arr['id'] = $files_id = $wpdb->insert_id;
															
							if ($files_id) {

								$file = $path2 . $files_id . '.php';
																
								$file_text = add_phpf_data($fdata);
																
								$file_open = @fopen($file, 'w');
								@fwrite($file_open, $file_text);
								@fclose($file_open);
																
								if (!is_file($file)) {
									
									$wpdb->query("DELETE FROM " . $wpdb->prefix . "hidefiles WHERE id = '$files_id'");
									
									$res['err_text'] = __('Error! Error loading file', 'premium');
									
									return $res;
								}

							}														
													
							$res['err'] = 0;
							$res['data'] = $arr;
												
						} else {
							$res['err_text'] = __('Error! Error loading file', 'premium');
						}
					} else {
						$res['err_text'] = __('Max.', 'premium') . ' ' . $max_mb . ' ' . __('MB', 'premium') . '!';			
					}
				} 
			} 
		} 		
	} 
	
	return $res;
}

function get_hf_files($type, $id, $caps, $show_file_names = 1) {
	global $wpdb;
	
	$show_file_names = intval($show_file_names);
	
	$html = '<div class="hf_files_wrap">';
	
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "hidefiles WHERE itemtype = '$type' AND item_id = '$id'");
		foreach ($items as $item) {
			
			$html .='
			<div class="hf_files_line">';
			
				$file_name = pn_maxf_mb(pn_strip_input($item->file_name), 10) . '...';
				if (!$show_file_names) {
					$file_name = __('Download', 'premium');
				}
			
				$html .= '<a href="' . get_hf_file_download($item->id) . '" target="_blank" rel="noreferrer noopener">' . $file_name . '</a>';
				
				$html .= ' | <a href="#" data-id="' . $item->id . '" class="bred js_hf_del">' . __('Delete', 'premium') . '</a>';

				if (current_user_cans($caps)) {
					$html .= ' | <a href="' . get_hf_file_view($item->id) . '" target="_blank">' . __('View', 'premium') . '</a>';
				}
			
			$html .='
			</div>';
			
		}	
	$html .= '</div>';
	
	return $html;
}

function get_hf_file_download($item_id) {
	
	return get_pn_action('hf_file_single', 'get') . '&id=' . $item_id;
}

function get_hf_file_view($item_id) {
	
	return get_pn_action('hf_file_single', 'get') . '&stype=view&id=' . $item_id;
}

add_action('premium_siteaction_hf_file_single', 'def_premium_siteaction_hf_file_single');
function def_premium_siteaction_hf_file_single() {
	global $wpdb, $premiumbox; 

	$premiumbox->up_mode();

	$id = intval(is_param_get('id'));
	if (!$id) {
		pn_display_mess(__('Error!', 'premium'));
	}
	
	$stype = is_param_get('stype');
	if ('view' != $stype) { $stype = 'download'; }
	
	if ($id > 0) {
		$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "hidefiles WHERE id = '$id'");
		if (isset($data->id)) {
			
			do_action('hf_file_single_action', $data, $stype);
			
		}
	}

	pn_display_mess(__('Error! File does not exist', 'premium'));
}

function hf_download_file($id, $data = '') {
	global $wpdb, $premiumbox;
	
	if (!isset($data->id)) {
		$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "hidefiles WHERE id = '$id'");
	}
	if (isset($data->id)) {
		$file = $premiumbox->upload_dir . '/'. $data->itemtype .'/' . $data->id . '.php';
		if (is_file($file)) {
			
			$fileupform = pn_enable_filetype();
			$mtype = is_isset($fileupform, $data->file_ext);

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
			header('Content-Disposition: attachment; filename=' . $data->file_name);
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . strlen($fdata));

			echo $fdata;
			exit;			
		}
	}
	
	pn_display_mess(__('Error! File does not exist', 'premium'));
}

function hf_view_file($id, $data = '') {
	global $wpdb, $premiumbox;
	
	if (!isset($data->id)) {
		$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "hidefiles WHERE id = '$id'");
	}
	if (isset($data->id)) {
		$file = $premiumbox->upload_dir . '/'. $data->itemtype .'/' . $data->id . '.php';
		if (is_file($file)) {
			
			$fdata = @file_get_contents($file);
			$fdata = str_replace('%star%', '*', $fdata);
			$fdata = get_phpf_data($fdata);
			$fdata = pn_string($fdata);

			$fileupform = pn_enable_filetype();
			$mtype = is_isset($fileupform, $data->file_ext);

			header('Content-Type: ' . $mtype . '; charset=' . get_charset());

			echo $fdata;
			exit;			
			
		}
	}
	
	pn_display_mess(__('Error! File does not exist', 'premium'));
}