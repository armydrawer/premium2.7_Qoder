<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Money transfer number[:en_US][ru_RU:]Номер денежного перевода[:ru_RU]
description: [en_US:]Form used for entering money transfer number after creating a request[:en_US][ru_RU:]Форма для ввода номера денежного перевода после создания заявки[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_napsidenty');
function bd_all_moduls_active_napsidenty() {
	global $wpdb;
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'identy'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `identy` varchar(250) NOT NULL");
    }
}

if (!function_exists('list_tabs_direction_verify')) {
	add_filter('list_tabs_direction', 'list_tabs_direction_verify');
	function list_tabs_direction_verify($list_tabs) {
		
		$list_tabs['verify'] = __('Verification', 'pn');
		
		return $list_tabs;
	}
}

add_action('tab_direction_verify', 'napsidenty_tab_direction_verify', 52, 2);
function napsidenty_tab_direction_verify($data, $data_id) {
	global $premiumbox;

	$form = new PremiumForm();
	$atts_input = array();
	$atts_input['class'] = 'big_input';
?>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Show field for entering number of money transfer', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<select name="enable_naps_identy" autocomplete="off">
					<?php 
					$enable_naps_identy = intval(get_direction_meta($data_id, 'enable_naps_identy')); 
					?>						
					<option value="0" <?php selected($enable_naps_identy, 0); ?>><?php _e('No', 'pn'); ?></option>
					<option value="1" <?php selected($enable_naps_identy, 1); ?>><?php _e('Yes', 'pn'); ?></option>						
				</select>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Field name for entering number of money transfer', 'pn'); ?></span></div>
			<?php
			$naps_identy_text = pn_strip_input(get_direction_meta($data_id, 'naps_identy_text'));
			if (strlen($naps_identy_text) < 1) { $naps_identy_text = $premiumbox->get_option('napsidenty', 'text'); }
			$form->input('naps_identy_text' , $naps_identy_text, $atts_input, 1);
			?>
		</div>		
	</div>
<?php	
}

add_action('item_direction_edit', 'item_direction_edit_napsidenty'); 
add_action('item_direction_add', 'item_direction_edit_napsidenty');
function item_direction_edit_napsidenty($data_id) {
	
	$enable_naps_identy = intval(is_param_post('enable_naps_identy'));
	update_direction_meta($data_id, 'enable_naps_identy', $enable_naps_identy);
	
	$naps_identy_text = pn_strip_input(is_param_post_ml('naps_identy_text'));
	update_direction_meta($data_id, 'naps_identy_text', $naps_identy_text);
	
} 

add_action('admin_menu', 'admin_menu_napsidenty');
function admin_menu_napsidenty() {
	global $premiumbox;	
	
	add_submenu_page("pn_moduls", __('Number of money transfer', 'pn'), __('Number of money transfer', 'pn'), 'administrator', "pn_napsidenty", array($premiumbox, 'admin_temp'));
	
} 

add_filter('pn_adminpage_title_pn_napsidenty', 'def_adminpage_title_pn_napsidenty');
function def_adminpage_title_pn_napsidenty($page) {
	
	return __('Number of money transfer', 'pn');
}

add_action('pn_adminpage_content_pn_napsidenty', 'def_adminpage_content_pn_napsidenty');
function def_adminpage_content_pn_napsidenty() {
	global $wpdb, $premiumbox;

	$form = new PremiumForm();

	$options = array();
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => '',
		'submit' => __('Save', 'pn'),
	);
	$options['text'] = array(
		'view' => 'inputbig',
		'title' => __('Field name for entering number of money transfer', 'pn'),
		'default' => $premiumbox->get_option('napsidenty', 'text'),
		'name' => 'text',
		'work' => 'input',
		'ml' => 1,
	);	
	$params_form = array(
		'filter' => 'pn_napsidenty_options',
		'button_title' => __('Save', 'pn'),
	);
	$form->init_form($params_form, $options); 
	
}  

add_action('premium_action_pn_napsidenty', 'def_premium_action_pn_napsidenty');
function def_premium_action_pn_napsidenty() {
	global $wpdb, $premiumbox;	

	_method('post');
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));
	
	$text = pn_strip_input(is_param_post_ml('text'));
	$premiumbox->update_option('napsidenty', 'text', $text);				

	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
			
	$form->answer_form($back_url);
	
}

add_filter('merchant_payed_button', 'napsidenty_merchant_payed_button', 10000);
function napsidenty_merchant_payed_button ($content) {
	global $bids_data;

	$enable_identy = intval(get_direction_meta($bids_data->direction_id, 'enable_naps_identy'));
	if (1 == $enable_identy) {
		$napsidenty = pn_strip_input(is_isset($bids_data, 'identy'));	
		if (!$napsidenty) {
			return '';
		}
	}	
	
	return $content;
}

add_filter('allow_payedbids', 'napsidenty_allow_payedbids', 10000, 3);
function napsidenty_allow_payedbids ($ind, $bids_data, $direction) {
	
	if (1 == $ind and !_is('is_api')) {
		$direction_id = $direction->id;
		$bid_id = $bids_data->id;
		$enable_identy = intval(get_direction_meta($direction_id, 'enable_naps_identy'));
		if (1 == $enable_identy) {	
			$napsidenty = pn_strip_input(is_isset($bids_data, 'identy'));
			if (!$napsidenty) { 
				return 0;
			}
		}	
	}
	
	return $ind;
}
	
add_filter('merchant_formstep_after', 'napsidenty_merchant_formstep_after', 10, 5);
function napsidenty_merchant_formstep_after($html, $m_in, $direction, $vd1, $vd2) { 
	global $bids_data, $premiumbox;	
	
	$status = is_status_name($bids_data->status);
	$m_st = get_status_sett('merch', 1);
	if (in_array($status, $m_st)) {
	
		$enable_identy = intval(get_direction_meta($bids_data->direction_id, 'enable_naps_identy'));
		if (1 == $enable_identy) {
			$napsidenty = pn_strip_input(is_isset($bids_data, 'identy'));	
			if (!$napsidenty) {
				
				$naps_identy_text = pn_strip_input(ctv_ml(get_direction_meta($direction->id, 'naps_identy_text')));
				if (!$naps_identy_text) { $naps_identy_text = pn_strip_input(ctv_ml($premiumbox->get_option('napsidenty', 'text'))); }
				
				$html = '
				<div class="block_smsbutton">
					<div class="block_smsbutton_ins">
						<div class="block_smsbutton_label">
							<div class="block_smsbutton_label_ins">
								'. $naps_identy_text .'
							</div>
						</div>
						<div class="block_smsbutton_action">
							<input type="text" name="" id="napsidenty_text" value="" />
							<input type="submit" name="" data-id="' . $bids_data->id . '" id="napsidenty_send" value="' . __('Confirm', 'pn') . '" />
								<div class="clear"></div>
						</div>
					</div>
				</div>
				';
				
			}
		}
	
	}
	
	return $html;
} 

add_action('premium_js', 'premium_js_napsidenty');
function premium_js_napsidenty() {
?>	
jQuery(function($) { 

	$(document).on('click', '#napsidenty_send', function() {
		if (!$(this).prop('disabled')) {
				
			var id = $(this).attr('data-id');
			var txt = $('#napsidenty_text').val();
			var thet = $(this);
			thet.prop('disabled', true);

			var param ='id=' + id + '&txt=' + txt;
			$.ajax({
				type: "POST",
				url: "<?php echo get_pn_action('save_napsidenty_bids'); ?>",
				dataType: 'json',
				data: param,
				error: function(res, res2, res3) {
					<?php do_action('pn_js_error_response', 'ajax'); ?>
				},			
				success: function(res)
				{
					if (res['status'] == 'success') {
						window.location.href = '';
					} 
					if (res['status'] == 'error') {
						thet.prop('disabled', false);
						<?php do_action('pn_js_alert_response'); ?>
					}
				}
			});
		}
		
		return false;
	});		

});		
<?php	
} 

add_action('premium_siteaction_save_napsidenty_bids', 'def_premium_siteaction_save_napsidenty_bids');
function def_premium_siteaction_save_napsidenty_bids() {
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
	$bid_id = intval(is_param_post('id'));
	$txt = pn_maxf_mb(pn_strip_input(is_param_post('txt')), 250);
	if ($bid_id > 0 and $txt) {
		$m_st = get_status_sett('merch');
		$bid_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$bid_id' AND status IN($m_st)");
		if (isset($bid_data->id)) {
			$is_true = is_true_userhash($bid_data);
			if ($is_true) {
				$direction_id = $bid_data->direction_id;
				$enable_identy = intval(get_direction_meta($direction_id, 'enable_naps_identy'));
				if (1 == $enable_identy) { 
					$napsidenty = pn_strip_input(is_isset($bid_data, 'identy'));
					if (!$napsidenty) { 
						$arr = array();
						$arr['identy'] = $txt;
						$wpdb->update($wpdb->prefix . "exchange_bids", $arr, array('id' => $bid_id));
						$log['status'] = 'success';
					} else {
						$log['status'] = 'success';
					}			
				} else {
					$log['status'] = 'error';
					$log['status_code'] = 1;
					$log['status_text'] = __('Error bid', 'pn');
				}
			} else {
				$log['status'] = 'error';
				$log['status_code'] = 1;
				$log['status_text'] = __('Error bid', 'pn');			
			}
		} else {
			$log['status'] = 'error';
			$log['status_code'] = 1;
			$log['status_text'] = __('Error bid', 'pn');
		}
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('You have not entered number', 'pn');		
	}
	
	echo pn_json_encode($log);
	exit;
}

add_filter('change_bids_filter_list', 'napsidenty_change_bids_filter_list'); 
function napsidenty_change_bids_filter_list($lists) {
	
	$lists['other']['identy'] = array(
		'title' => __('Number of money transfer', 'pn'),
		'name' => 'identy',
		'view' => 'input',
		'work' => 'input',
	);	
	
	return $lists;
}

add_filter('where_request_sql_bids', 'where_request_sql_bids_napsidenty', 0, 2); 
function where_request_sql_bids_napsidenty($where, $pars_data) {
	global $wpdb;
	
	$sql_operator = is_sql_operator($pars_data);
	$identy = pn_strip_input(pn_sfilter(is_isset($pars_data, 'identy')));
	if ($identy) {
		$where .= " {$sql_operator} {$wpdb->prefix}exchange_bids.identy LIKE '%$identy%'";
	} 
	
	return $where;
}

add_filter('onebid_col1','onebid_col_napsidenty', 99, 2);
function onebid_col_napsidenty($cols, $item, $v = '') {
	global $wpdb;	
	
	$identy = pn_strip_input(is_isset($item, 'identy'));
	if ($identy) {
		$cl = '';
		$bid_id = $item->id;
		$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE status != 'auto' AND identy = '$identy' AND id != '$bid_id'");
		if ($cc > 0) {
			$cl = 'bred_dash';
		}
		$cols['identy'] = array(
			'type' => 'text',
			'title' => __('Number of money transfer', 'pn'),
			'label' => $identy,
			'copy' => $identy,
			'class' => $cl,
		);				
	}
	
	return $cols; 
}