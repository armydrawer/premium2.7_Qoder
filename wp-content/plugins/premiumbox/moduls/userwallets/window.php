<?php
if (!defined('ABSPATH')) { exit(); }
 
add_filter('placed_form', 'placed_form_userwallets');
function placed_form_userwallets($placed) {
	
	$placed['userwalletsform'] = __('Add userwallet form', 'pn');
	
	return $placed;
}
 
add_filter('userwalletsform_filelds', 'def_userwalletsform_filelds');
function def_userwalletsform_filelds($items) {
	global $wpdb;

	$currencies = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' AND currency_status = '1' AND user_wallets = '1'");
	$n_options = array('0' => '--' . __('select currency', 'pn') . '--');
	$options = array();
	foreach ($currencies as $currency) {
		$options[$currency->id] = get_currency_title($currency);
	}	
	asort($options);
	foreach ($options as $option_id => $option_data) {
		$n_options[$option_id] = $option_data;
	}
	$items['currency_id'] = array(
		'name' => 'currency_id',
		'title' => __('Payment system', 'pn'),
		'req' => 1,
		'value' => '',
		'type' => 'select',
		'options' => $n_options,
		'atts' => array('class' => 'userwalletsform_currency_id'),
	);
	$items['account'] = array(
		'name' => 'account',
		'title' => __('Account number', 'pn'),
		'req' => 1,
		'value' => '',
		'type' => 'input',
		'atts' => array('class' => 'userwalletsform_account'),
	);				
	
	return $items;
}

add_filter('replace_array_userwalletsform', 'def_replace_array_userwalletsform', 10, 3);
function def_replace_array_userwalletsform($array, $prefix, $place = '') {
	global $wpdb, $premiumbox;
	
	$fields = get_form_fields('userwalletsform', $place);
	
	$filter_name = '';
	if ('widget' == $place) {
		$prefix = 'widget_' . $prefix;
		$filter_name = 'widget_';
	}
	$html = prepare_form_fileds($fields, $filter_name . 'userwallets_form_line', $prefix);	
	
	$array = array(
		'[form]' => '<form method="post" class="ajax_post_form" action="' . get_pn_action('userwalletsform') . '">',
		'[/form]' => '</form>',
		'[result]' => '<div class="resultgo"></div>',
		'[html]' => $html,
		'[submit]' => '<input type="submit" formtarget="_top" name="submit" class="' . $prefix . '_submit" value="' . __('Add acount', 'pn') . '" />',
	);	
	
	return $array;
}

add_action('premium_js', 'premium_js_userwalletsform');
function premium_js_userwalletsform() {	
?>	
jQuery(function($) { 

	$(document).on('click', '.js_add_userwallet', function() {
		
		$(document).JsWindow('show', {
			window_class: 'update_window',
			title: '<?php _e('Add account', 'pn'); ?>',
			content: $('.userwalletsform_box_html').html(),
			insert_div: '.userwalletsform_box',
			shadow: 1
		});		
		
        var id = $(this).attr('data-currid');		
		$('.userwalletsform_currency_id').val(id);
		
        var account = $(this).attr('data-account');		
		$('.userwalletsform_account').val(account);				
		
        var redir = $(this).attr('data-redir');		
		$('#userwalletsform_redir').val(redir);		
		
	    return false;
	});	
	
});	
<?php	
}

add_action('wp_footer', 'wp_footer_userwalletsform');
function wp_footer_userwalletsform() {
		
	$array = get_form_replace_array('userwalletsform', 'rb');
		
	$temp = '
	<div class="userwalletsform_box_html" style="display: none;">		
		[html]	
		<div class="rb_line">[submit]</div>
		[result]
	</div>';	
		
	$temp .= '
	[form]
		<input type="hidden" name="redir" id="userwalletsform_redir" value="" />
			
		<div class="userwalletsform_box"></div>
	[/form]
	';
		
	$temp = apply_filters('userwallets_form_temp', $temp);
	echo replace_tags($array, $temp);	
}

add_action('premium_siteaction_userwalletsform', 'def_premium_siteaction_userwalletsform');
function def_premium_siteaction_userwalletsform() {
	global $wpdb, $premiumbox;	
	
	_method('post');
	_json_head();

	$log = array();
	$log['status'] = '';
	$log['status_text'] = '';
	$log['status_code'] = 0;
	
	$premiumbox->up_mode('post');
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	if (!$user_id) {
		$log['status'] = 'error'; 
		$log['status_code'] = 1;
		$log['status_text'] = __('Error! You must authorize', 'pn');
		echo pn_json_encode($log);
		exit;		
	}

	$log = _log_filter($log, 'userwalletsform');
	
	if (!$log['status_code']) {
	
		$arr = create_userwallets($user_id, $ui->user_login, is_param_post('currency_id'), is_param_post('account'));
		if ($arr['error'] > 0) {
			$log['status'] = 'error'; 
			$log['status_code'] = 2;
			$log['status_text'] = $arr['error_text'];
			echo pn_json_encode($log);
			exit;		
		}
		
		$log['status'] = 'success';
		$log['status_text'] = __('Account is successfully added', 'pn');
		$log['status_code'] = 0;	
		
		$redirect_url = trim(is_param_post('redir'));
		if ($redirect_url) {
			$log['url'] = get_safe_url(apply_filters('userwallets_redirect', $redirect_url));
		} 
	
	}
	
	echo pn_json_encode($log);
	exit;
}

function add_createwallet_link($currency_id, $account, $title, $redirect_url = '') {
	
	return '<a href="#" class="js_add_userwallet" data-currid="' . $currency_id . '" data-redir="' . $redirect_url . '" data-account="' . $account . '">' . $title . '</a>';
}

function create_userwallets($user_id, $user_login, $currency_id, $account) {
	global $wpdb, $premiumbox;	

	$arr = array(
		'error' => 1,
		'error_text' => 'default',
		'data_id' => 0,
	);

	$user_id = intval($user_id);
	if ($user_id) {
		$currency_id = intval($currency_id);
		$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency WHERE auto_status = '1' AND currency_status = '1' AND user_wallets = '1' AND id = '$currency_id'");
		if (isset($item->id)) {
			$account = get_purse($account, $item);
			if ($account) { 		
				$where = " AND user_id='$user_id'";
				$uniq = intval($premiumbox->get_option('usve', 'uniq'));
				if ($uniq) {
					$where = '';
				}
				$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "user_wallets WHERE currency_id = '$currency_id' AND accountnum = '$account' AND auto_status = '1' $where");
				if (0 == $cc) {
					
					$array = array();
					$array['user_id'] = $user_id;
					$array['user_login'] = is_user($user_login);
					$array['currency_id'] = $currency_id;
					$array['accountnum'] = $account;
					$res = apply_filters('item_userwallets_add_before', pn_ind(), $array); 
					if ($res['ind']) {
						$array['edit_date'] = current_time('mysql');
						$array['auto_status'] = 1;
						$array['create_date'] = current_time('mysql');
						$result = $wpdb->insert($wpdb->prefix . 'user_wallets', $array);
						$data_id = $wpdb->insert_id;	
						if ($result) {
							$arr['error'] = 0;
							$arr['error_text'] = '';							
							do_action('item_userwallets_add', $data_id, $array, $result);
						} else {
							$res_errors = _debug_table_from_db($result, 'user_wallets', $array);
							$arr['error_text'] = implode(',', $res_errors);	
						}
					} else {
						$arr['error_text'] = is_isset($res, 'error');
					}
					
				} else {
					$arr['error_text'] = __('Error! This account already exists', 'pn');
				}
			} else {
				$arr['error_text'] = __('Error! Invalid wallet account', 'pn');	
			}
		} else {
			$arr['error_text'] = __('Error! Currency does not exist or disabled', 'pn');	
		}
	} else {
		$arr['error_text'] = __('Error! You must authorize', 'pn');
	}

	return $arr;
}