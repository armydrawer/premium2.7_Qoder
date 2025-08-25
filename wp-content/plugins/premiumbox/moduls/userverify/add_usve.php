<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_all_add_usve', 'def_adminpage_title_all_add_usve');
	function def_adminpage_title_all_add_usve() {
		global $db_data, $wpdb;	
			
		$data_id = 0;
		$item_id = intval(is_param_get('item_id'));
		$db_data = '';
			
		if ($item_id) {
			$db_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "verify_bids WHERE id = '$item_id'");
			if (isset($db_data->id)) {
				$data_id = $db_data->id;
			}	
		}		
			
		if ($data_id) {
			return __('Edit verification', 'pn');
		} else {
			return __('Add verification', 'pn');
		}	
	}

	add_action('pn_adminpage_content_all_add_usve', 'def_adminpage_content_all_add_usve');
	function def_adminpage_content_all_add_usve() {
		global $db_data, $wpdb;

		$form = new PremiumForm();

		$data_id = intval(is_isset($db_data, 'id'));
		if ($data_id) {
			$title = __('Edit verification', 'pn');
		} else {
			$title = __('Add verification', 'pn');
		}

		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=all_usve'),
			'title' => __('Back to list', 'pn')
		);
		$back_menu['save'] = array(
			'link' => '#',
			'title' => __('Save', 'pn'),
			'atts' => array('class' => "savelink save_admin_ajax_form"),
		);		
		if ($data_id) {
			$back_menu['add'] = array(
				'link' => admin_url('admin.php?page=all_add_usve'),
				'title' => __('Add new', 'pn')
			);	
			if (1 == is_isset($db_data, 'auto_status')) {
				$back_menu['approve'] = array(
					'link' => pn_link('enable_userverify') . '&id=' . $data_id,
					'title' => __('Approve', 'pn')
				);
			}
		}
		$form->back_menu($back_menu, $db_data);

		$options = array();
		$options['hidden_block'] = array(
			'view' => 'hidden_input',
			'name' => 'data_id',
			'default' => $data_id,
		);	
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => $title,
			'submit' => __('Save', 'pn'),
		);	
		
		$options['user_id'] = array(
			'view' => 'input',
			'title' => __('User ID', 'pn'),
			'default' => is_isset($db_data, 'user_id'),
			'name' => 'user_id',
		);		
		if (isset($db_data->id)) {
			$options['user_id']['atts'] = array('disabled' => 'disabled');
		} 	

		$txtfields = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_field WHERE fieldvid IN('0','2') AND status = '1' ORDER BY uv_order ASC");
		foreach ($txtfields as $txtfield) {
			$id = $txtfield->id;
			$field = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_field_user WHERE uv_field = '$id' AND uv_id = '$data_id'");

			$options['uv' . $id] = array(
				'view' => 'inputbig',
				'title' => pn_strip_input(ctv_ml($txtfield->title)),
				'default' => is_isset($field, 'uv_data'),
				'name' => 'uv' . $id,
			);	
		}	
		
		$status = array('1' => __('Pending request', 'pn'), '2' => __('Confirmed request', 'pn'), '3' => __('Request is declined', 'pn'));
		if (isset($db_data->id)) {
			$options['status'] = array(
				'view' => 'textfield',
				'title' => __('Status', 'pn'),
				'default' => is_isset($status, is_isset($db_data, 'status')),
			);	
		}

		$params_form = array(
			'filter' => 'all_userverify_addform',
			'data' => $db_data,
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	

		$max_mb = pn_max_upload();
		$max_upload_size = $max_mb * 1024 * 1024;
		$fileupform = pn_enable_filetype();
		$allow = array();
		foreach ($fileupform as $f_k => $f_v) {
			$allow[] = $f_k;
		}
				
		echo '<div class="premium_single">';
			
			$fields = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_field WHERE fieldvid = '1' AND status = '1' ORDER BY uv_order ASC");
			foreach ($fields as $field) {	

				$temp = '<form action="' . pn_link('userverify_upload', 'post') . '" class="usveajaxform" enctype="multipart/form-data" method="post">
					<input type="hidden" name="theid" value="' . $field->id . '" />
					<input type="hidden" name="id" value="' . $data_id . '" />
				';
											
				$thetitle = pn_strip_input(ctv_ml($field->title));
											
				$req_txt = '';
				if (1 == $field->uv_req) {
					$req_txt = '<span class="bred">*</span>';
				}								
											
				$temp .= '
				<div class="premium_standart_div">
					<div class="premium_standart_line ustbl_line">
						<div class="usvelabeldown">' . $thetitle . ' ' . $req_txt . '</div>
						<div class="usvelabeldownsyst">(' . strtoupper(implode(', ', $allow)) . ', ' . __('max.', 'pn') . ' ' . $max_mb . '' . __('MB', 'pn') . ')</div>
															
						<div class="usveupfile">
							<input type="file" class="usveupfilesome" name="file" value="" />
						</div>
															
						<div class="ustbl_res">' . get_usvedoc_temp($data_id, $field->id) . '</div>
					</div>
				</div>
				';
								
				$temp .= '</form>';
				echo $temp;	
				
			}	

		echo '
			<div id="usveformedres"></div>
		</div>';
			
		if (isset($db_data->id)) {
			$temp = '	
			<div class="premium_body">
				<h3 style="padding: 0; margin: 0;">' . __('Failure reason', 'pn') . '</h3>
				<form method="post" action="' . pn_link('disable_userverify') . '&id=' . $data_id . '">
					<p><textarea name="textstatus" style="width: 100%; height: 100px;">' . pn_strip_input(is_isset($db_data, 'comment')) . '</textarea></p>
					<p><label><input type="checkbox" name="delete_files" autocomplete="off" value="1" /> ' . __('Delete verification files', 'pn') . '</label></p>
					<input type="submit" name="submit" class="button" value="' . __('Decline verification', 'pn') . '" />
				</form>	
			</div>
			';
			echo $temp;
		} 
		?>
<script type="text/javascript">
jQuery(function($) { 
	<?php usve_js(); ?>
});	
</script>
		<?php  
	}	

}	

add_action('premium_action_userverify_upload', 'def_premium_action_userverify_upload');
function def_premium_action_userverify_upload() {
	global $wpdb;	
			
	_method('post');
	_json_head();		

	$plugin = get_plugin_class();
			
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0;
	$log['status_text'] = '';	
			
	if (!current_user_can('administrator') and !current_user_can('pn_userverify')) {
		$log['status'] = 'error'; 
		$log['status_code'] = 1;
		$log['status_text']= __('Error! Insufficient privileges', 'pn');
		echo pn_json_encode($log);
		exit;		
	}
					
	$id = intval(is_param_post('id'));
	if ($id < 1) { $id = 0; } /* id заявки */
			
	$theid = intval(is_param_post('theid'));
	if ($theid < 1) { $theid = 0; }	/* id поля */
			
	$locale = get_locale();
				
	$field_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_field WHERE fieldvid = '1' AND status = '1' AND id = '$theid'");
	if (!isset($field_data->id)) {
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Error! Error loading file', 'pn');			
		echo pn_json_encode($log);
		exit;	
	}		
				
	$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "verify_bids WHERE id = '$id'");
	$user_id = intval(is_isset($data, 'user_id'));
	$data_id = intval(is_isset($data, 'id'));
	
	$log = download_usve_file($log, $data_id, $theid, $user_id);				
			
	echo pn_json_encode($log);
	exit;	
}
			
add_action('premium_action_all_add_usve', 'def_premium_action_all_add_usve');
function def_premium_action_all_add_usve() {	
	global $wpdb;	

	_method('post');
			
	$form = new PremiumForm();
	$form->send_header();
			
	pn_only_caps(array('administrator', 'pn_userverify'));
			
	$plugin = get_plugin_class();
			
	$data_id = intval(is_param_post('data_id'));
			
	$last_data = '';
	if ($data_id > 0) { 
		$last_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "verify_bids WHERE id = '$data_id'");
		if (!isset($last_data->id)) {
			$data_id = 0;
		}
	}	
			
	$user_id = intval(is_isset($last_data, 'user_id'));
			
	$array = array();				
	$array['user_ip'] = pn_real_ip();
	$array['auto_status'] = 1;
	$array['edit_date'] = current_time('mysql');
			
	if ($data_id) {
				
		$wpdb->update($wpdb->prefix . 'verify_bids', $array, array('id' => $data_id));
				
	} else {
				
		$array['create_date'] = current_time('mysql');
		$user_id = intval(is_param_post('user_id'));
		$array['user_id'] = $user_id;
		$ui = get_userdata($user_id);
		if (isset($ui->ID)) {
			$array['user_login'] = is_user($ui->user_login);
			$array['user_email'] = is_email($ui->user_email);
		} else {
			$form->error_form(__('Error! You did not choose the user', 'pn'));
		}	
		$array['status'] = 1;
				
		/* 		
		$cc = $wpdb->get_var("SELECT COUNT(id) FROM ".$wpdb->prefix."verify_bids WHERE user_id = '$user_id' AND status IN('1','2') AND id != '$data_id'");		
		if($cc > 0){
			$form->error_form(__('Error! This user already has a verification order','pn'));
		}	 
		*/			
				
		$wpdb->insert($wpdb->prefix . 'verify_bids', $array);
		$data_id = $wpdb->insert_id;

		if ($data_id) {
					
			$path = $plugin->upload_dir . '/';
			$path2 = $path . 'userverify/';
			$path3 = $path . 'userverify/' . $data_id . '/';
			$path4 = $path . 'userverify/0/';
			if (!is_dir($path)) { 
				@mkdir($path , 0777);
			}
			if (!is_dir($path2)) { 
				@mkdir($path2 , 0777);
			}	
			if (!is_dir($path3)) { 
				@mkdir($path3 , 0777);
			}
								
			$files = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_field_user WHERE uv_id = '0' AND fieldvid = '1'");
			foreach ($files as $file) {
				$or_file = $path4 . $file->id . '.php';
				$new_file = $path3 . $file->id . '.php';
				@copy($or_file, $new_file);
				@unlink($or_file);
						
				$arr = array();
				$arr['user_id'] = $user_id;
				$arr['uv_id'] = $data_id;
				$wpdb->update($wpdb->prefix . 'uv_field_user', $arr, array('id' => $file->id));
			}
				
		}
				
	}
			
	if ($data_id) {
		$fields = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_field WHERE fieldvid IN('0','2') AND status = '1' ORDER BY uv_order ASC");
		foreach ($fields as $field) {
			$field_id = $field->id;

			$value = strip_uf(is_param_post('uv' . $field->id), $field->uv_auto);
								
			$us_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_field_user WHERE uv_id = '$data_id' AND uv_field = '$field_id'");
					
			$arr = array();
			$arr['user_id'] = $user_id;
			$arr['uv_data'] = $value;
			$arr['uv_id'] = $data_id;
			$arr['uv_field'] = $field_id;
			$arr['fieldvid'] = $field->fieldvid;
								
			if (isset($us_data->id)) {
				$wpdb->update($wpdb->prefix . 'uv_field_user', $arr, array('id' => $us_data->id)); 
			} else {
				$wpdb->insert($wpdb->prefix . 'uv_field_user', $arr);
			}
		}			
	}

	$url = admin_url('admin.php?page=all_add_usve&item_id=' . $data_id . '&reply=true');
	$form->answer_form($url);
	
}	