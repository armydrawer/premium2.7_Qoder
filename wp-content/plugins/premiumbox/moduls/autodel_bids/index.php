<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Auto removal of unpaid requests[:en_US][ru_RU:]Автоудаление неоплаченных заявок[:ru_RU]
description: [en_US:]Auto removal of unpaid requests with the ability to set individual time of removal[:en_US][ru_RU:]Автоудаление неоплаченных заявок с возможность установить индивидуальное время удаления[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_autodelbids');
add_action('pn_plugin_activate', 'bd_all_moduls_active_autodelbids');
function bd_all_moduls_active_autodelbids() {
	global $wpdb;	
	
	$table_name = $wpdb->prefix . "auto_removal_bids";
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT ,
		`direction_id` bigint(20) NOT NULL default '0',
		`enable_autodel` int(1) NOT NULL default '0',
		`cou_hour` varchar(20) NOT NULL default '0',
		`cou_minute` varchar(20) NOT NULL default '0',
		`statused` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`direction_id`),
		INDEX (`enable_autodel`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);	 
	
}

add_action('admin_menu', 'admin_menu_autodelbids', 500);
function admin_menu_autodelbids() {
	global $premiumbox;	
	
	add_submenu_page("pn_directions", __('Automatic deletion of unpaid orders', 'pn'), __('Automatic deletion of unpaid orders', 'pn'), 'administrator', "pn_autodelbids", array($premiumbox, 'admin_temp'));
}

add_filter('pn_adminpage_title_pn_autodelbids', 'def_adminpage_title_pn_autodelbids');
function def_adminpage_title_pn_autodelbids() {
	
	return __('Automatic deletion of unpaid orders', 'pn');
}

add_action('pn_adminpage_content_pn_autodelbids', 'def_adminpage_content_pn_autodelbids');
function def_adminpage_content_pn_autodelbids() {
	global $wpdb, $premiumbox;

	$form = new PremiumForm();

	$options = array();
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Automatic deletion of unpaid orders', 'pn'),
		'submit' => __('Save','pn'),
	);
	$options['enable'] = array(
		'view' => 'select',
		'title' => __('Automatic deletion of unpaid orders', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('autodel', 'enable'),
		'name' => 'enable',
	);	
	$options['statused'] = array(
		'view' => 'user_func',
		'name' => 'statused',
		'func_data' => array(),
		'func' => '_autodelbids_statused_option',
	);	
	$options['ad_h'] = array(
		'view' => 'input',
		'title' => __('How many hours', 'pn'),
		'default' => $premiumbox->get_option('autodel', 'ad_h'),
		'name' => 'ad_h',
	);
	$options['ad_m'] = array(
		'view' => 'input',
		'title' => __('How many minutes', 'pn'),
		'default' => $premiumbox->get_option('autodel', 'ad_m'),
		'name' => 'ad_m',
	);
	$params_form = array(
		'filter' => 'pn_autodelbids_options',
		'button_title' => __('Save', 'pn'),
	);
	$form->init_form($params_form, $options);
	
}  

function _autodelbids_statused_option($data) {
	global $premiumbox;

	$status = $premiumbox->get_option('autodel', 'statused');
	if (!is_array($status)) { $status = array(); }	
	
	$lists = list_bid_status();
	?>	
	<div class="premium_standart_line"> 
		<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Delete orders with status', 'pn'); ?></div></div>
		<div class="premium_stline_right"><div class="premium_stline_right_ins">
			<div class="premium_wrap_standart">
				<?php
				$scroll_lists = array();	
				foreach ($lists as $key => $title) {
					$checked = 0;
					if (in_array($key, $status)) { 
						$checked = 1;
					}
					$scroll_lists[] = array(
						'title' => $title,
						'checked' => $checked,
						'value' => $key,
					);
				}
				echo get_check_list($scroll_lists, 'statused[]');
				?>
					<div class="premium_clear"></div>
			</div>
		</div></div>
			<div class="premium_clear"></div>
	</div>						
	<?php	
}
 
add_action('premium_action_pn_autodelbids', 'def_premium_action_pn_autodelbids');
function def_premium_action_pn_autodelbids() {
	global $wpdb, $premiumbox;	

	_method('post');
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));
	
	$enable = intval(is_param_post('enable'));
	$premiumbox->update_option('autodel', 'enable', $enable);
	
	$ad_h = intval(is_param_post('ad_h'));
	$premiumbox->update_option('autodel', 'ad_h', $ad_h);	
	
	$ad_m = intval(is_param_post('ad_m'));
	$premiumbox->update_option('autodel', 'ad_m', $ad_m);						

	$premiumbox->update_option('autodel', 'statused', is_param_post('statused'));
	
	$back_url = is_param_post('_wp_http_referer');
	$back_url = add_query_args(array('reply' => 'true'), $back_url);
			
	$form->answer_form($back_url);
	
} 

add_filter('list_tabs_direction', 'list_tabs_direction_autodelbids');
function list_tabs_direction_autodelbids($list_tabs) {
	
	$list_tabs['autodelbids'] = __('Removing unpaid orders', 'pn');
	
	return $list_tabs;
}

add_action('tab_direction_autodelbids','direction_tab_autodelbids', 10, 2);
function direction_tab_autodelbids($data, $data_id) {	
	global $wpdb, $premiumbox;
 	 
	$data_id = is_isset($data,'id');
	$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "auto_removal_bids WHERE direction_id = '$data_id'"); 
	$cou_hour = intval(is_isset($item, 'cou_hour'));
	$cou_minute = intval(is_isset($item, 'cou_minute'));
	$lists = list_bid_status();
	$statused = pn_strip_input_array(pn_json_decode(is_isset($item,'statused')));
	if (!is_array($statused)) { $statused = array(); }
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Delete orders with status', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php
				$scroll_lists = array();	
				foreach ($lists as $key => $title) {
					$checked = 0;
					if (in_array($key,$statused)) { 
						$checked = 1;
					}
					$scroll_lists[] = array(
						'title' => $title,
						'checked' => $checked,
						'value' => $key,
					);
				}
				echo get_check_list($scroll_lists, 'autodelbids_statused[]');
				?>				
					<div class="premium_clear"></div>
			</div>
		</div>
	</div>

	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Unpaid orders removal time', 'pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('How many hours', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="autodelbids_cou_hour" style="width: 50px;" value="<?php echo $cou_hour; ?>" />
					<div class="premium_clear"></div>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('How many minutes', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="autodelbids_cou_minute" style="width: 50px;" value="<?php echo $cou_minute; ?>" />
					<div class="premium_clear"></div>
			</div>	
		</div>
	</div>		
	<?php 
}

add_action('item_direction_delete', 'item_direction_delete_autodelbids');
function item_direction_delete_autodelbids($item_id) {
	global $wpdb;	

	$wpdb->query("DELETE FROM " . $wpdb->prefix . "auto_removal_bids WHERE direction_id = '$item_id'");
}

add_action('item_direction_edit', 'item_direction_edit_autodelbids', 10, 2);
add_action('item_direction_add', 'item_direction_edit_autodelbids', 10, 2);
function item_direction_edit_autodelbids($data_id, $array) {
	global $wpdb;	
	
	if ($data_id) {
		$wpdb->query("DELETE FROM " . $wpdb->prefix . "auto_removal_bids WHERE direction_id = '$data_id'");
		
		$cou_hour = intval(is_param_post('autodelbids_cou_hour'));
		$cou_minute = intval(is_param_post('autodelbids_cou_minute'));
		$statused = pn_json_encode(pn_strip_input_array(is_param_post('autodelbids_statused')));
		if ($cou_hour > 0 or $cou_minute > 0) {
			$arr = array();
			$arr['direction_id'] = $data_id;
			$arr['cou_hour'] = $cou_hour;
			$arr['cou_minute'] = $cou_minute;
			$arr['statused'] = $statused;			
			$wpdb->insert($wpdb->prefix . "auto_removal_bids", $arr);
		} 
	}
}

add_action('item_direction_copy', 'item_direction_copy_autodelbids', 10, 2);
function item_direction_copy_autodelbids($last_id, $data_id) {
	global $wpdb;	
	
	$data = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "auto_removal_bids WHERE direction_id = '$last_id'");
	foreach ($data as $item) {
		$arr = array();
		$arr['direction_id'] = $data_id;
		$arr['enable_autodel'] = $item->enable_autodel;
		$arr['cou_hour'] = $item->cou_hour;
		$arr['cou_minute'] = $item->cou_minute;
		$arr['statused'] = $item->statused;
		$wpdb->insert($wpdb->prefix . 'auto_removal_bids', $arr);
	}	
	
}

add_filter('bid_delete_time','autodelbids_bid_delete_time', 20, 2);
function autodelbids_bid_delete_time($bid_delete_time, $bids_data) {
	global $wpdb, $premiumbox;
	
	$status = $bids_data->status;
	$direction_id = $bids_data->direction_id;
		
	$editdate = $bids_data->edit_date;
	$bid_delete_time = __('undefined', 'pn');
	$date_format = get_option('date_format');
	$time_format = get_option('time_format');
			
	if (1 == $premiumbox->get_option('autodel','enable')) {
		
		$hour = intval($premiumbox->get_option('autodel', 'ad_h'));
		$minuts = intval($premiumbox->get_option('autodel', 'ad_m'));
		$statused = $premiumbox->get_option('autodel', 'statused');
		if (!is_array($statused)) { $statused = array(); }				
		$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "auto_removal_bids WHERE direction_id = '$direction_id'");
		if (isset($data->id)) {
			$hour = intval($data->cou_hour);
			$minuts = intval($data->cou_minute);
			$statused = pn_json_decode(is_isset($data, 'statused'));
			if (!is_array($statused)) { $statused = array(); }					
		}	
		$sec = 0;
		if ($hour > 0) {
			$sec = $hour * 60 * 60;
		}
		if ($minuts > 0) {
			$sec = $sec + ($minuts * 60);
		}				
		$editdate = strtotime($editdate);
		$del_time = $editdate + $sec;
		if (in_array($bids_data->status, $statused)) {
			$bid_delete_time = date("{$date_format}, H:i:s", $del_time);
		}
		
	}	
	
	return $bid_delete_time;
} 	

function delete_notpay_bids() {
	global $wpdb, $premiumbox;
	
	if (!$premiumbox->is_up_mode()) {
		if (1 == $premiumbox->get_option('autodel', 'enable')) {
			
			$time = current_time('timestamp');
			$date = current_time('mysql');	

			$hour = intval($premiumbox->get_option('autodel', 'ad_h'));
			$minuts = intval($premiumbox->get_option('autodel', 'ad_m'));
			$statused = $premiumbox->get_option('autodel', 'statused');
			if (!is_array($statused)) { $statused = array(); }
			
			$in_status = array();
			foreach ($statused as $st) {
				$st = is_status_name($st);
				if ($st) {
					$in_status[] = "'" . $st . "'";
				}
			}
			
			$dir_autodel = array();
			$autodels = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "auto_removal_bids");
			foreach ($autodels as $au) {
				$direction_id = $au->direction_id;
				$dir_autodel[$direction_id]['hour'] = intval($au->cou_hour);
				$dir_autodel[$direction_id]['minuts'] = intval($au->cou_minute);
				$dir_statused = pn_json_decode(is_isset($au, 'statused'));
				if (!is_array($dir_statused)) { $dir_statused = array(); }
				$dir_autodel[$direction_id]['statused'] = $dir_statused;
				foreach ($dir_statused as $st) {
					$st = is_status_name($st);
					if ($st) {
						$in_status[] = "'" . $st . "'";
					}						
				}
			}
			
			$in_status = array_unique($in_status);
			if (count($in_status) > 0) {
				$in_join = join(',', $in_status);
				$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($in_join)");
				foreach ($items as $item) {
					$id = $item->id;
					$editdate = $item->edit_date;
					$create_time = strtotime($editdate);
					$direction_id = $item->direction_id;
					
					$del_hour = $hour;
					$del_minuts = $minuts;
					
					$del_statused = $statused;
					if (isset($dir_autodel[$direction_id])) {
						$dir = $dir_autodel[$direction_id];
						$del_hour = intval(is_isset($dir, 'hour'));
						$del_minuts = intval(is_isset($dir, 'minuts'));
						if (isset($dir['statused'])) {
							$del_statused = $dir['statused'];
						}
					}

					$sec = 0;
					if ($del_hour > 0) {
						$sec = $del_hour * 60 * 60;
					}
					if ($del_minuts > 0) {
						$sec = $sec + ($del_minuts * 60);
					}			
					$del_time = $time - $sec;
					if ($create_time < $del_time) {
						if (in_array($item->status, $del_statused)) {
							$arr = array('status' => 'delete', 'edit_date' => $date);
							$result = $wpdb->update($wpdb->prefix . 'exchange_bids', $arr, array('id' => $item->id));
							if (1 == $result) {	
							
								$old_status = $item->status;
								$item = pn_object_replace($item, $arr);
								
								$ch_data = array(
									'bid' => $item,
									'set_status' => 'delete',
									'place' => 'auto_removal_bids',
									'who' => 'system',
									'old_status' => $old_status,
									'direction' => ''
								);
								_change_bid_status($ch_data);
  
							}
						}
					}
				}
			}
		}
	}	
} 

add_filter('list_cron_func', 'delete_notpay_bids_list_cron_func');
function delete_notpay_bids_list_cron_func($filters) {
	
	$filters['delete_notpay_bids'] = array(
		'title' => __('Removing unpaid orders', 'pn'),
		'site' => 'now',
		'file' => 'none',
	);
	
	return $filters;
}