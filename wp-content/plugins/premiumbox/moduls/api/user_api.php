<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('pn_tech_pages', 'list_tech_pages_user_api');
function list_tech_pages_user_api($pages) {
	
	$pages[] = array(
		'post_name'      => 'user_api',
		'post_title'     => '[en_US:]API[:en_US][ru_RU:]API[:ru_RU]',
		'post_content'   => '[user_api]',
		'post_template'   => 'pn-pluginpage.php',
	);		
	
	return $pages;
}

function access_user_api() {
	
	$plugin = get_plugin_class();
	$method = intval($plugin->get_option('api', 'method'));
	$ui = wp_get_current_user();
	$work_api = intval(is_isset($ui, 'work_api'));
	$user_id = intval($ui->ID);
	if (1 == $method or 2 == $method and 1 == $work_api) {
		if ($user_id) {
			return 1;
		}
	}
	
	return 0;
}

function get_user_enable_methods() {
	
	$api_lists = array();
	$api_all_lists = apply_filters('api_all_methods', array());
	$plugin = get_plugin_class();
	$enable = $plugin->get_option('api', 'enabled_method');
	if (!is_array($enable)) { $enable = array(); }
	
	foreach ($api_all_lists as $api_all_list_k => $api_all_list_v) {
		if (isset($enable[$api_all_list_k])) {
			$api_lists[$api_all_list_k] = $api_all_list_v;
		}
	}	
	
	return $api_lists;
}

add_filter('account_list_pages', 'user_api_list_pages_userwallets', 2000);
function user_api_list_pages_userwallets($account_list_pages) {
	
	if (access_user_api()) {
		$account_list_pages['user_api'] = array(
			'type' => 'page',			
		);
	}
	
	return $account_list_pages;
}
 
function user_api_page_shortcode($atts, $content) {
	global $wpdb;
	
	$temp = apply_filters('before_userapi_page', '');
	
	$plugin = get_plugin_class();
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	if (access_user_api()) {

		$data_id = 0;
		$data = '';
		$item_id = intval(is_param_get('item_id'));
		if ($item_id > 0) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "api WHERE id = '$item_id' AND user_id = '$user_id'");
			if (isset($data->id)) {
				$data_id = $data->id;
			}
		}
		
		$api_lists = get_user_enable_methods();
		
		$title = __('Add API key', 'pn');
		if ($data_id > 0) {
			$title = __('Edit API key', 'pn');
		}
	
		$temp .= '
		<form method="post" class="ajax_post_form" action="' . get_pn_action('userapiform') . '">
			<input type="hidden" name="item_id" value="' . $data_id . '" />
			<div class="userapi_form">
				<div class="userapi_form_ins">
					<div class="userapi_form_title">
						<div class="userapi_form_title_ins">
							'. $title .'
						</div>
					</div>';
					
					if ($data_id > 0) {
						$temp .='
						<div class="userapi_form_label">
							'. __('API login', 'pn') .'
						</div>
						<div class="userapi_form_login">
							<span class="js_copy pn_copy" data-clipboard-text="'. is_api_key($data->api_login) .'">'. is_api_key($data->api_login) .'</span>
						</div>
						<div class="userapi_form_label">
							'. __('API key', 'pn') .'
						</div>						
						<div class="userapi_form_token">
							<span class="js_copy pn_copy" data-clipboard-text="'. is_api_key($data->api_key) .'">'. is_api_key($data->api_key) .'</span>
						</div>
						';
					}	
					
					$temp .='
					<div class="userapi_options">
						<div class="userapi_options_ins">';
						
						foreach ($api_lists as $api_list_key => $api_list_title) {
							$checked = '';
							$en = pn_json_decode(is_isset($data, 'api_actions'));
							if (!is_array($en)) { $en = array(); }
							if (isset($en[$api_list_key])) {
								$checked = 'checked="checked"';
							}	
							
							$temp .= '<div class="userapi_option_one"><div class="userapi_option_ins"><label><input type="checkbox" name="api_actions[]" '. $checked .' value="'. $api_list_key .'" /> '. $api_list_title .'</label></div></div>';
						}						
						
						$temp .= '	
						</div>
					</div>
					<div class="userapi_textarea_label">'. __('Enabled ip (in new line)', 'pn') .'</div>
					<div class="userapi_textarea">
						<textarea name="enable_ip">'. is_isset($data, 'enable_ip') .'</textarea>
					</div>';
					
					$temp .= '
					<div class="userapi_submit">
						<input type="submit" formtarget="_top" name="" value="'. $title .'" />
					</div>
						<div class="clear"></div>
				</div>	
			</div>		
				<div class="resultgo"></div>
		</form>';
		
		if ($data_id < 1) {
		
			$page_userapi_url = $plugin->get_page('user_api');
		
			$list = '';			
			$limit = apply_filters('limit_list_userapi', 15);
			$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "api WHERE user_id = '$user_id'");
			$pagenavi = get_pagenavi_calc($limit, get_query_var('paged'), $count);
			$datas = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "api WHERE user_id = '$user_id' ORDER BY id DESC LIMIT ". $pagenavi['offset'] .",".$pagenavi['limit']);	
			$pagenavi_html = get_pagenavi($pagenavi);
		
			$s = 0;
			foreach ($datas as $data) { $s++;
				$line_one = '
				<div class="userapi_table_one" id="userapiid_'. $data->id .'">
					<div class="userapi_table_one_ins">
						<a href="#" class="userapi_table_one_delete"></a>
						<div class="userapi_table_one_title">'. __('API login','pn') .'</div>
						<div class="userapi_table_one_login"><span class="js_copy pn_copy" data-clipboard-text="'. is_api_key($data->api_login) .'">'. is_api_key($data->api_login) .'</span></div>
						<div class="userapi_table_one_title">'. __('API key','pn') .'</div>
						<div class="userapi_table_one_token"><span class="js_copy pn_copy" data-clipboard-text="'. is_api_key($data->api_key) .'">'. is_api_key($data->api_key) .'</span></div>
						<div class="userapi_table_one_edit"><a href="'. $page_userapi_url .'?item_id='. $data->id .'">'. __('edit api key','pn') .'</a></div>
					</div>
				</div>
				';
				$list .= $line_one;
			}	
			if (0 == $s) {
				$list .= apply_filters('userapi_noitem', '<div class="userapi_table_one"><div class="no_items"><div class="no_items_ins">' . __('No data', 'pn') . '</div></div></div>');
			}	
		
			$temp .= '
			<div class="userapi_table">
				<div class="userapi_table_ins">
					<div class="userapi_table_title">
						<div class="userapi_table_title_ins">
							'. __('Your API keys', 'pn') .'
						</div>
					</div>

					'. $list .'
				</div>
			</div>';
			
			$temp .= $pagenavi_html;
		
		}

	} else {
		$temp .= '<div class="resultfalse">'. __('Error! Page access denied', 'pn') .'</div>';
	}
	
	$temp .= apply_filters('after_userapi_page', '');
	
	return $temp;
}
add_shortcode('user_api', 'user_api_page_shortcode');

add_action('premium_siteaction_userapiform', 'def_premium_siteaction_userapiform');
function def_premium_siteaction_userapiform() {
	global $wpdb, $premiumbox;	
	
	_method('post');
	_json_head();
	
	$log = array();
	$log['status'] = '';
	$log['status_text'] = '';
	$log['status_code'] = 0;
	
	$premiumbox->up_mode('post');
	
	if (access_user_api()) {
		
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);
		
		$data = '';
		$data_id = intval(is_param_post('item_id'));
		if ($data_id > 0) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "api WHERE id = '$data_id' AND user_id = '$user_id'");
			if (!isset($data->id)) {
				
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error! Does not exist API key', 'pn');				
				echo pn_json_encode($log);
				exit;				
				
			}
		}
	
		$api_lists = get_user_enable_methods();
		$api_actions = is_param_post('api_actions');
		$en = array(); 
		if (is_array($api_lists) and is_array($api_actions)) {
			foreach ($api_lists as $k => $title) {
				if (in_array($k, $api_actions)) {
					$en[$k] = 1;
				}
			}
		}	
		
		if (count($en) > 0) {
			
			$array = array();
			$array['user_id'] = $user_id;
			$array['user_login'] = is_isset($ui, 'user_login');
			$array['api_actions'] = pn_json_encode($en);
			$array['enable_ip'] = pn_strip_input(is_param_post('enable_ip'));
			
			$res_errors = array();
			
			if ($data_id > 0) {
				
				$result = $wpdb->update($wpdb->prefix . 'api', $array, array('id' => $data_id));
				$res_errors = _debug_table_from_db($result, 'api', $array);
				
			} else {
				
				$array['create_date'] = current_time('mysql');
				$array['api_login'] = get_random_password(32, true, true);
				$array['api_key'] = unique_api_key();
				$result = $wpdb->insert($wpdb->prefix . 'api', $array);
				$data_id = $wpdb->insert_id;	
				if ($data_id < 1) {
					$res_errors = _debug_table_from_db($result, 'api', $array);						
				}

			}
			
			if (count($res_errors) > 0) {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = implode(',<br />', $res_errors);				
				echo pn_json_encode($log);
				exit;
			}		
			
			$log['status'] = 'success';
			$log['status_text'] = __('Successfully completed', 'pn');
			$log['url'] = apply_filters('userapi_redirect', $premiumbox->get_page('user_api') . '?item_id=' . $data_id, $data_id);				
			
		} else {
			
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error! You have not selected allowed actions', 'pn');	
			
		}			
	} else {
		
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! Page access denied', 'pn');
		
	}
	
	echo pn_json_encode($log);
	exit;
}

add_action('premium_js','premium_js_userapi');
function premium_js_userapi() {	
	if (access_user_api()) {
?>	
jQuery(function($) {
	
    $(document).on('click', '.userapi_table_one_delete', function() {
		var id = $(this).parents('.userapi_table_one').attr('id').replace('userapiid_', '');
		var thet = $(this);
		if (!thet.hasClass('act')) {
			thet.addClass('act');
			var param = 'id=' + id;
			
			$.ajax({
				type: "POST",
				url: "<?php echo get_pn_action('delete_userapi'); ?>",
				dataType: 'json',
				data: param,
				error: function(res, res2, res3) {
					<?php do_action('pn_js_error_response', 'ajax'); ?>
				},			
				success: function(res)
				{
					if (res['status'] == 'success') {
						$('#userapiid_' + id).remove();
					} 
					if (res['status'] == 'error') {
						<?php do_action('pn_js_alert_response'); ?>
					}
					thet.removeClass('act');
				}
			});	
		}
		
        return false;
    });	
});		
<?php	
	}
}  

add_action('premium_siteaction_delete_userapi', 'def_premium_siteaction_delete_userapi');
function def_premium_siteaction_delete_userapi() {
	global $wpdb, $premiumbox;	
	
	_method('post');
	_json_head();
	
	$log = array();
	$log['status'] = '';
	$log['status_text'] = '';
	$log['status_code'] = 0;
	
	$premiumbox->up_mode('post');
	
	if (!access_user_api()) {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! Page access denied', 'pn');
		echo _json_encode($log);
		exit;		
	}	
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	$id = intval(is_param_post('id'));
	$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "api WHERE user_id = '$user_id' AND id = '$id'");
	if (isset($item->id)) {
		$res = apply_filters('item_api_delete_before', pn_ind(), $item->id, $item);
		if ($res['ind']) {	
			$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "api WHERE id = '$id'");
			do_action('item_api_delete', $item->id, $item, $result);
			$log['status'] = 'success';
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = is_isset($res, 'error');			
		}
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! Page access denied', 'pn');		
	}
	
	echo pn_json_encode($log);
	exit;
}