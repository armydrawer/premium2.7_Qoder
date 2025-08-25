<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Recalculation of exchange amount[:en_US][ru_RU:]Пересчет суммы обмена[:ru_RU]
description: [en_US:]Recalculation of exchange amount[:en_US][ru_RU:]Пересчет суммы обмена[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('all_moduls_active_' . $name, 'bd_all_moduls_active_recalcbids');
add_action('pn_plugin_activate', 'bd_all_moduls_active_recalcbids');
function bd_all_moduls_active_recalcbids() {
	global $wpdb;

	$table_name = $wpdb->prefix . "recalculations"; 
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`direction_id` bigint(20) NOT NULL default '0',
		`change_course` int(1) NOT NULL default '0',
		`change_sum` int(1) NOT NULL default '0',
		`course_minute` varchar(20) NOT NULL default '0',
		`sum_minute` varchar(20) NOT NULL default '0',
		`course_status` longtext NOT NULL,
		`sum_status` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`direction_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);

	$table_name = $wpdb->prefix . "recalclogs"; 
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`create_date` datetime NOT NULL,
		`bid_id` bigint(20) NOT NULL default '0',
		`old_data` longtext NOT NULL,
		`new_data` longtext NOT NULL,
		PRIMARY KEY (`id`),
		INDEX (`bid_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);	
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'recalc_amount'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `recalc_amount` datetime NOT NULL");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'recalc_course'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `recalc_course` datetime NOT NULL");
    }
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "exchange_bids LIKE 'recalc_date'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "exchange_bids ADD `recalc_date` datetime NOT NULL");
    }	
}
 
add_filter('change_bids_filter_list', 'recalcbids_change_bids_filter_list'); 
function recalcbids_change_bids_filter_list($lists) {
	
	$options = array(
		'0' => '--' . __('All', 'pn') . '--',
		'1' => __('Yes', 'pn'),
		'2' => __('No', 'pn'),
	);		
	$lists['other']['recalc_date'] = array(
		'title' => __('Order recalculation', 'pn'),
		'name' => 'recalc_date',
		'options' => $options,
		'view' => 'select',
		'work' => 'options',
	);
	
	return $lists;
}
 
add_filter('where_request_sql_bids', 'recalcbids_where_request_sql_bids', 10,2); 
function recalcbids_where_request_sql_bids($where, $pars_data) {
	global $wpdb;
	
	$pr = $wpdb->prefix;
	$sql_operator = is_sql_operator($pars_data);
	$recalc_date = intval(is_isset($pars_data, 'recalc_date'));
	if (1 == $recalc_date) {
		$where .= " {$sql_operator} {$pr}exchange_bids.recalc_date != '0000-00-00 00:00:00'"; 
	} elseif (2 == $recalc_date) {	
		$where .= " {$sql_operator} {$pr}exchange_bids.recalc_date = '0000-00-00 00:00:00'";
	}
	
	return $where;
}

add_filter('list_admin_notify', 'list_admin_notify_recalcbids');
function list_admin_notify_recalcbids($places_admin) {
	
	$places_admin['admin_recalcbids'] = __('Order recalculation', 'pn');
	
	return $places_admin;
}

add_filter('list_user_notify', 'list_user_notify_recalcbids');
function list_user_notify_recalcbids($places_admin) {
	
	$places_admin['user_recalcbids'] = __('Order recalculation', 'pn');
	
	return $places_admin;
}	

add_action('init', 'recalcbids_init', 0);
function recalcbids_init() {
	
	add_filter('list_notify_tags_admin_recalcbids', 'def_mailtemp_tags_bids');
	add_filter('list_notify_tags_user_recalcbids', 'def_mailtemp_tags_bids');
	
}
 
add_action('item_direction_delete', 'item_direction_delete_recalcbids');
function item_direction_delete_recalcbids($item_id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "recalculations WHERE direction_id = '$item_id'");
}

add_filter('pn_caps', 'recalcbids_pn_caps');
function recalcbids_pn_caps($pn_caps) {
	
	$pn_caps['pn_bids_recalc'] = __('Work with recalculation of order', 'pn');
	
	return $pn_caps;
}

function status_for_recalculate_admin() {
	
	$st = array('coldnew', 'new', 'cancel', 'techpay', 'payed', 'amlwait', 'coldpay', 'partpay', 'realpay', 'verify', 'error', 'payouterror', 'scrpayerror', 'partpayout');
	$st = apply_filters('status_for_recalculate_admin', $st);
	$st = (array)$st;
	
	return $st;
}
 
add_filter('onebid_actions', 'onebid_actions_dop_recalcbids', 1000, 2);
function onebid_actions_dop_recalcbids($onebid_actions, $item) {
	
	if (current_user_can('administrator') or current_user_can('pn_bids_recalc')) {
		$status = $item->status;
		$st = status_for_recalculate_admin();
		if (in_array($status, $st)) {
			$onebid_actions['recalculate_amount'] = array(
				'type' => 'link',
				'title' => __('Recalculate amount', 'pn'),
				'label' => __('Recalculate amount', 'pn'),
				'link' => get_request_link('recalculate_bid', 'html', get_locale(), array('item_id' => $item->id, 'recalc' => 0)),
				'link_target' => '_blank',
				'link_class' => 'editting',
			);
			$onebid_actions['recalculate_course'] = array(
				'type' => 'link',
				'title' => __('Recalculate rate', 'pn'),
				'label' => __('Recalculate rate', 'pn'),
				'link' => get_request_link('recalculate_bid', 'html', get_locale(), array('item_id' => $item->id, 'recalc' => 1)),
				'link_target' => '_blank',
				'link_class' => 'editting',
			);			
		}
	}
	
	return $onebid_actions;
}

add_action('premium_request_recalculate_bid', 'def_recalculate_one_bid');
function def_recalculate_one_bid() {
	global $wpdb, $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_bids_recalc')) {
		
		admin_pass_protected(__('Enter security code', 'pn'), __('Enter', 'pn'), 'edit'); 	
		
		$bid_id = intval(is_param_get('item_id'));
		$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$bid_id'");
		if (isset($item->id)) {
			$status = $item->status;
			$st = status_for_recalculate_admin();
			if (in_array($status, $st)) {
				$recalc = intval(is_param_get('recalc'));
				$ch_s = 4;
				$ch_c = 0;
				if (1 == $recalc) {
					$ch_s = 0;
					$ch_c = 4;
				}
				$item = recalculation_bid($bid_id, $item, $ch_s, $ch_c, '');
				$hashdata = bid_hashdata($bid_id, $item);
			}
		}	

		_e('Done', 'pn');
	}	
}
			
add_filter('pn_config_option', 'recalculate_pn_config_option');
function recalculate_pn_config_option($options) {
	global $premiumbox;
	
	$options['recalc'] = array(
		'view' => 'select',
		'title' => __('Order recalculating method (only course)', 'pn'),
		'options' => array('0' => __('Upon order status change', 'pn'), '1' => __('By cron', 'pn'), '2' => __('Allways', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'recalc'),
		'name' => 'recalc',
	);
	$options['recalcnull'] = array(
		'view' => 'select',
		'title' => __('If rate is not available (recalculation of order by rate)', 'pn'),
		'options' => array('0' => __('Recalculate order by zero rate', 'pn'), '1' => __('Recalculate order by old rate', 'pn')),
		'default' => $premiumbox->get_option('exchange', 'recalcnull'),
		'name' => 'recalcnull',
	);	
	
	return $options;	
}

add_action('pn_config_option_post', 'recalculate_pn_config_option_post');
function recalculate_pn_config_option_post() {
	global $premiumbox;
	
	$recalc = intval(is_param_post('recalc'));
	$premiumbox->update_option('exchange', 'recalc', $recalc);
	
	$recalcnull = intval(is_param_post('recalcnull'));
	$premiumbox->update_option('exchange', 'recalcnull', $recalcnull);
	
}

add_filter('list_tabs_direction', 'list_tabs_direction_recalcbids');
function list_tabs_direction_recalcbids($list_tabs) {
	
	$list_tabs['recalcbids'] = __('Order recalculating', 'pn');
	
	return $list_tabs;
}
		
add_action('tab_direction_recalcbids', 'direction_tab_recalcbids',10,2);
function direction_tab_recalcbids($data, $data_id) {	
	global $wpdb, $premiumbox;

 	$data_id = intval(is_isset($data, 'id'));
	$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "recalculations WHERE direction_id = '$data_id'");
	
	$change_course = intval(is_isset($item, 'change_course'));
	$change_sum = intval(is_isset($item, 'change_sum'));
	$course_minute = intval(is_isset($item, 'course_minute'));
	$sum_minute = intval(is_isset($item, 'sum_minute'));
	
	$lists = list_bid_status();
	?>
		<div class="add_tabs_line">
			<div class="add_tabs_title"><?php _e('Recalculation according to amount of payment', 'pn'); ?></div>
			<div class="add_tabs_submit">
				<input type="submit" name="" class="button" value="<?php _e('Save', 'pn'); ?>" />
			</div>
		</div>
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Order recalculation upon changing payment amount', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<select name="recalc_change_sum" autocomplete="off"> 
						<option value="0" <?php selected($change_sum, 0); ?>><?php _e('No', 'pn'); ?></option>
						<option value="4" <?php selected($change_sum, 4); ?>><?php _e('Yes, always', 'pn'); ?></option>
						<option value="1" <?php selected($change_sum, 1); ?>><?php _e('Yes, if payment amount changed', 'pn'); ?></option>					
						<option value="2" <?php selected($change_sum, 2); ?>><?php _e('Yes, if payment amount increased', 'pn'); ?></option>	
						<option value="3" <?php selected($change_sum, 3); ?>><?php _e('Yes, if payment amount decreased', 'pn'); ?></option>
					</select>
				</div>			
			</div>
			<div class="add_tabs_single">			
			</div>
		</div>	
		<div class="add_tabs_line">
			<div class="add_tabs_single long">
				<div class="add_tabs_sublabel"><span><?php _e('Order status for recalculation', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
			
					$def = @unserialize(is_isset($item, 'sum_status'));
					if (!is_array($def)) { $def = array(); }
											
					foreach ($lists as $key => $title) {
						$checked = 0;
						if (in_array($key, $def)) {
							$checked = 1;
						}	
						$scroll_lists[] = array(
							'title' => $title,
							'checked' => $checked,
							'value' => $key,
						);
					}
					echo get_check_list($scroll_lists, 'recalc_sum_status[]', '', '300');
					?>				
						<div class="premium_clear"></div>
				</div>
			</div>
		</div>			
		
		<div class="add_tabs_line">
			<div class="add_tabs_title"><?php _e('Recalculation according to exchange rate', 'pn'); ?></div>
			<div class="add_tabs_submit">
				<input type="submit" name="" class="button" value="<?php _e('Save', 'pn'); ?>" />
			</div>
		</div>	
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Order recalculation upon changing exchange rate', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<select name="recalc_change_course" autocomplete="off"> 
						<option value="0" <?php selected($change_course, 0); ?>><?php _e('No', 'pn'); ?></option>
						<option value="4" <?php selected($change_course, 4); ?>><?php _e('Yes, always', 'pn'); ?></option>
						<option value="1" <?php selected($change_course, 1); ?>><?php _e('Yes, if rate changed', 'pn'); ?></option>					
						<option value="2" <?php selected($change_course, 2); ?>><?php _e('Yes, if rate increased', 'pn'); ?></option>	
						<option value="3" <?php selected($change_course, 3); ?>><?php _e('Yes, if rate decreased', 'pn'); ?></option>
					</select>
				</div>			
			</div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Perform recalculation through', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<input type="text" name="recalc_course_minute" value="<?php echo $course_minute; ?>" /> <?php _e('minuts', 'pn'); ?>
				</div>			
			</div>			
		</div>
		<div class="add_tabs_line">
			<div class="add_tabs_single long">
				<div class="add_tabs_sublabel"><span><?php _e('Order status for recalculation', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
			
					$def = @unserialize(is_isset($item, 'course_status'));
					if (!is_array($def)) { $def = array(); }
											
					foreach ($lists as $key => $title) {
						$checked = 0;
						if (in_array($key, $def)) {
							$checked = 1;
						}	
						$scroll_lists[] = array(
							'title' => $title,
							'checked' => $checked,
							'value' => $key,
						);
					}
					echo get_check_list($scroll_lists, 'recalc_course_status[]', '', '300');
					?>				
						<div class="premium_clear"></div>
				</div>
			</div>
		</div>			
	<?php 
} 

add_action('item_direction_edit', 'item_direction_edit_recalcbids', 10, 2);
add_action('item_direction_add', 'item_direction_edit_recalcbids', 10, 2);
function item_direction_edit_recalcbids($data_id, $array) {
	global $wpdb;
	
	if ($data_id) {
		$change_course = intval(is_param_post('recalc_change_course'));	
		$change_sum = intval(is_param_post('recalc_change_sum'));
		
		if ($change_course > 0 or $change_sum > 0) {
			
			$arr = array();
			$arr['change_course'] = $change_course;
			$arr['change_sum'] = $change_sum;
			$arr['direction_id'] = $data_id;
			$arr['sum_minute'] = intval(is_param_post('recalc_sum_minute'));
			$arr['course_minute'] = intval(is_param_post('recalc_course_minute'));
			
			$p_statused = is_param_post('recalc_course_status');
			$statused = array();
			if (is_array($p_statused)) {
				foreach ($p_statused as $st) { 
					$st = is_status_name($st);
					if ($st) {
						$statused[] = $st;
					}
				}
			}
			$arr['course_status'] = @serialize($statused);
			
			$p_statused = is_param_post('recalc_sum_status');
			$statused = array();
			if (is_array($p_statused)) {
				foreach ($p_statused as $st) {
					$st = is_status_name($st);
					if ($st) {
						$statused[] = $st;
					}
				}
			}
			$arr['sum_status'] = @serialize($statused);			
			
			$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "recalculations WHERE direction_id = '$data_id'"); 
			if (isset($item->id)) {
				$wpdb->update($wpdb->prefix . "recalculations", $arr, array('id' => $item->id));
			} else {
				$wpdb->insert($wpdb->prefix . "recalculations", $arr);
			}
			
			do_action('item_direction_save', $data_id, $item, '', $arr);
			
		} else {
			
			$wpdb->query("DELETE FROM " . $wpdb->prefix . "recalculations WHERE direction_id = '$data_id'");
			
		}
	}
}

add_action('item_direction_copy', 'item_direction_copy_recalcbids', 10, 2);
function item_direction_copy_recalcbids($last_id, $data_id) {
	global $wpdb;	
	
	$data = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "recalculations WHERE direction_id = '$last_id'");
	foreach ($data as $item) {
		$arr = array();
		foreach ($item as $k => $v) {
			$arr[$k] = $v;
		}
		if (isset($arr['id'])) {
			unset($arr['id']);
		}
		$arr['direction_id'] = $data_id;
		$wpdb->insert($wpdb->prefix . 'recalculations', $arr);
	}	
}

add_filter('onebid_col1', 'onebid_col1_recalcbids', 10, 3);
function onebid_col1_recalcbids($actions, $item, $v) {
	
	$new_actions = array();
	if ('0000-00-00 00:00:00' != $item->recalc_amount) {
		$new_actions['recalc_amount'] = array(
			'type' => 'text',
			'title' => __('Recalculating amount date', 'pn'),
			'label' => get_pn_time($item->recalc_amount, 'd.m.Y H:i:s'),
			'link' => admin_url('admin.php?page=pn_recalclogs&bid_id=' . $item->id),
			'link_target' => '_blank',
		);
	}
	if ('0000-00-00 00:00:00' != $item->recalc_course) {
		$new_actions['recalc_course'] = array(
			'type' => 'text',
			'title' => __('Recalculating rate date', 'pn'),
			'label' => get_pn_time($item->recalc_course, 'd.m.Y H:i:s'),
			'link' => admin_url('admin.php?page=pn_recalclogs&bid_id=' . $item->id),
			'link_target' => '_blank',
		);	
	}		
	
	$actions = pn_array_insert($actions, 'editdate', $new_actions, 'after');
	
	return $actions;
}

add_filter('direction_instruction_tags', 'recalcbids_directions_tags', 20, 2); 
function recalcbids_directions_tags($tags, $key) {
	
	$in_page = array('description_txt', 'timeline_txt', 'window_txt', 'frozen_txt');
	if (!in_array($key, $in_page)) {
		
		$tags['recalc_course'] = array(
			'title' => __('Recalculation rate time', 'pn'),
			'start' => '[recalc_course]',
		);
		
		$tags['recalc_amount'] = array(
			'title' => __('Recalculation amount time', 'pn'),
			'start' => '[recalc_amount]',
		);		
		
	}
	
	return $tags;
}			

add_filter('direction_instruction', 'recalcbids_direction_instruction', 20, 5);
function recalcbids_direction_instruction($instruction, $txt_name, $direction, $vd1, $vd2) {
	global $wpdb, $premiumbox, $bids_data;

	if (isset($bids_data->id)) {
		$recalc_data = '';
		if (strstr($instruction, '[bid_recalc]') or strstr($instruction, '[recalc_course]')) {
			$bid_recalc = __('undefined', 'pn');
			$direction_id = $bids_data->direction_id;
			if (!isset($recalc_data->id)) {
				$recalc_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "recalculations WHERE direction_id = '$direction_id'");
			}
			if (isset($recalc_data->id)) {
				$minute = intval(is_isset($recalc_data, 'course_minute'));
				if ($minute > 0) {
					$cou_hour = floor($minute / 60);
					$cou_minute = $minute - ($cou_hour * 60);
					if ($cou_hour > 0 and $cou_minute > 0) {
						$bid_recalc = sprintf(__('Order will be recalculated through %1s hour(s), %2s minute(s) after creating', 'pn'), $cou_hour, $cou_minute);
					} elseif ($cou_hour > 0) {
						$bid_recalc = sprintf(__('Order will be recalculate through %s hour(s) after creating', 'pn'), $cou_hour);
					} elseif ($cou_minute > 0) {
						$bid_recalc = sprintf(__('Order will be recalculate through %s minute(s) after creating', 'pn'), $cou_minute);		
					}
				} else {
					$bid_recalc = __('Exchange amount will be recalculated upon order status change', 'pn');
				}	
			}
			$instruction = str_replace(array('[bid_recalc]', '[recalc_course]'), $bid_recalc, $instruction); 
		}	
		if (strstr($instruction, '[recalc_amount]')) {
			$bid_recalc = __('undefined', 'pn');
			$direction_id = $bids_data->direction_id;
			if (!isset($recalc_data->id)) {
				$recalc_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "recalculations WHERE direction_id = '$direction_id'");
			}
			if (isset($recalc_data->id)) {
				$minute = intval(is_isset($recalc_data, 'sum_minute'));
				if ($minute > 0) {
					$cou_hour = floor($minute / 60);
					$cou_minute = $minute - ($cou_hour * 60);
					if ($cou_hour > 0 and $cou_minute > 0) {
						$bid_recalc = sprintf(__('Order will be recalculate through %1s hour(s), %2s minute(s) after creating', 'pn'), $cou_hour, $cou_minute);
					} elseif ($cou_hour > 0) {
						$bid_recalc = sprintf(__('Order will be recalculate through %s hour(s) after creating', 'pn'), $cou_hour);
					} elseif ($cou_minute > 0) {
						$bid_recalc = sprintf(__('Order will be recalculate through %s minute(s) after creating', 'pn'), $cou_minute);		
					}
				} else {
					$bid_recalc = __('Exchange amount will be recalculated upon order status change', 'pn');
				}
			}
			$instruction = str_replace('[recalc_amount]', $bid_recalc, $instruction);
		}	
	}
	
	return $instruction;
}

add_filter('change_bid_status', 'recalculation_change_bidstatus', 60);     
function recalculation_change_bidstatus($data) { 
	global $wpdb, $premiumbox;
	
	$place = $data['place'];
	$set_status = $data['set_status'];
	$bid = $data['bid'];
	$who = $data['who'];
	$old_status = $data['old_status'];
	$direction = $data['direction'];	
	
	$item_id = $bid->id;
	
	$stop_action = intval(is_isset($data, 'stop'));
	
	if (isset($bid->direction_id) and !$stop_action) {
		$direction_id = $bid->direction_id;
		$recalc_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "recalculations WHERE direction_id = '$direction_id'");
		if (isset($recalc_data->id)) {
				
			$time = current_time('timestamp');

			$create_date = $bid->create_date;
			$create_time = strtotime($create_date);
			$item_status = $bid->status;
				
			$ch_s = 0;
			$minute = intval($recalc_data->sum_minute);
			$change_time = $create_time + ($minute * 60);
			$in_status = @unserialize($recalc_data->sum_status);
			if (!is_array($in_status)) { $in_status = array(); }
			if ($time >= $change_time and in_array($item_status, $in_status)) {
				$ch_s = intval($recalc_data->change_sum);
			}	
				
			$ch_c = 0;
			$recalc = intval($premiumbox->get_option('exchange','recalc'));
			if (0 == $recalc or 2 == $recalc) {
				$minute = intval($recalc_data->course_minute);
				$change_time = $create_time + ($minute * 60);
				$in_status = @unserialize($recalc_data->course_status);
				if (!is_array($in_status)) { $in_status = array(); }
				if ($time >= $change_time and in_array($item_status, $in_status)) {
					$ch_c = intval($recalc_data->change_course);
				}		
			}
			
			if ($ch_s > 0 or $ch_c > 0) {	
				$bid = recalculation_bid($bid->id, $bid, $ch_s, $ch_c, $direction);
				$hashdata = bid_hashdata($bid->id, $bid);
				$hashdata = @serialize($hashdata);
				$data['bid'] = pn_object_replace($bid, array('hashdata' => $hashdata));
			}
	
		}
	}
	
	return $data;
}	

function recalculation_bids() {
	global $wpdb, $premiumbox;

	if (!$premiumbox->is_up_mode()) {
		$recalc = intval($premiumbox->get_option('exchange', 'recalc'));
		if (1 == $recalc or 2 == $recalc) {
			$time = current_time('timestamp');
			$date = current_time('mysql');
			
			$v = get_currency_data();
			
			$recalcs = $wpdb->get_results("
			SELECT * FROM " . $wpdb->prefix . "recalculations 
			LEFT OUTER JOIN " . $wpdb->prefix . "directions 
			ON(" . $wpdb->prefix . "recalculations.direction_id = " . $wpdb->prefix . "directions.id) 
			WHERE " . $wpdb->prefix . "directions.auto_status = '1' AND " . $wpdb->prefix . "directions.direction_status = '1'");

			$time_left_60sec = $time - 60;
			$recalc_fool_protection = apply_filters('recalc_fool_protection', 1);
			if (1 != $recalc_fool_protection) {
				$time_left_60sec = $time;
			}
			$last_date = date('Y-m-d H:i:s', $time_left_60sec);

			foreach ($recalcs as $rec) {
				$direction_id = $rec->direction_id;
				$statused = array();
				$course_status = @unserialize($rec->course_status);
				if (!is_array($course_status)) { $course_status = array(); }
				$statused = array_merge($course_status, $statused);
				$statused = array_unique($statused);
				
				$in_status = create_data_for_db($statused, 'status');
				if ($in_status) {
					$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status IN($in_status) AND direction_id = '$direction_id' AND recalc_date < '$last_date'"); 
					foreach ($items as $item) {
						$create_date = $item->create_date;
						$create_time = strtotime($create_date);
						$item_status = $item->status;

						$course_minute = intval($rec->course_minute);
						$ch_c = 0;
						$change_time = $create_time + ($course_minute * 60);
						if ($time >= $change_time and in_array($item_status, $course_status)) {
							$ch_c = intval($rec->change_course);
						}						
						if ($ch_c > 0) {
							$item = recalculation_bid($item->id, $item, 0, $ch_c, $rec);
							$hashdata = bid_hashdata($item->id, $item, 0);
						}
					}
				}	
			}
		}
	}
}  

function recalculation_bid($bid_id, $item = '', $change_sum = '', $change_course = '', $direction = '') {
	global $wpdb, $premiumbox;

	$change_course = intval($change_course);
	$change_sum = intval($change_sum);
	$date = current_time('mysql');
	if (!isset($item->id)) {
		$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$bid_id'");
	}
	if (isset($item->id)) {
		
		$direction_id = $item->direction_id; 
		$v = get_currency_data();
		
		if (!isset($direction->id)) {
			$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' AND id = '$direction_id'");
		}		
		
		if (isset($direction->id)) {
			if (isset($v[$direction->currency_id_give]) and isset($v[$direction->currency_id_get])) {
				
				$recalcnull = intval($premiumbox->get_option('exchange', 'recalcnull'));
				
				$arr = array();
				$arr['recalc_date'] = $date;		

				$sum_from_pay = apply_filters('sum_from_pay', is_sum($item->sum1dc), $item->m_in, $direction, $item); 
				$recalc_dej = apply_filters('recalc_dej', 5, $item->m_in, $direction, $item);
				
				$sum = is_sum($sum_from_pay);
				$sum_pay = is_sum($item->pay_sum);
				$sum_pay = apply_filters('recalc_pay_sum', $sum_pay, $item->m_in, $direction, $item); 

				$calc_data = array(
					'vd1' => $v[$direction->currency_id_give],
					'vd2' => $v[$direction->currency_id_get],
					'direction' => $direction,
					'user_id' => $item->user_id, 
					'post_sum' => $item->sum1dc,
					'dej' => 5,
				);
				
				$active_s = 0;
				if ($change_sum > 0 and $sum_pay > 0) {
					$ch_sum = 0;
					if ($sum_pay > $sum) {
						$ch_sum = 1;
					} elseif ($sum > $sum_pay) {
						$ch_sum = 2;
					}					
					if (4 == $change_sum) {
						$active_s = 1;	
					}
					if (1 == $change_sum and $ch_sum > 0) {
						$active_s = 1;
					}
					if (2 == $change_sum and 1 == $ch_sum) {
						$active_s = 1;
					}
					if (3 == $change_sum and 2 == $ch_sum) {
						$active_s = 1;
					}	
					if (1 == $active_s) {
						$calc_data['post_sum'] = $sum_pay;
						$calc_data['dej'] = $recalc_dej;
					}
				}
				
				$active_c = 0;
				if ($change_course > 0 and $calc_data['post_sum'] > 0) {
				
					$dir_c = is_course_direction($direction, $v[$direction->currency_id_give], $v[$direction->currency_id_get], 'table1');
					$course_give = is_sum(is_isset($dir_c, 'give')); 
					$course_get = is_sum(is_isset($dir_c, 'get'));
						
					if (!$recalcnull or $recalcnull and $course_give > 0 and $course_get > 0) {	
						
						$ch_course = is_course_change($item->course_give, $item->course_get, $course_give, $course_get);
							
						if (4 == $change_course) {
							$active_c = 1;	
						}
						if (1 == $change_course and $ch_course > 0) {
							$active_c = 1;
						}
						if (2 == $change_course and 1 == $ch_course) {
							$active_c = 1;
						}
						if (3 == $change_course and 2 == $ch_course) {
							$active_c = 1;
						}

					}

				}
				
				if (1 != $active_c) {
					$calc_data['set_course'] = 1; 
					$calc_data['c_give'] = $item->course_give; 
					$calc_data['c_get'] = $item->course_get; 
				} else {
					if ($item->course_give != $course_give or $item->course_get != $course_get) {
	
					}
				}
					
				if (1 == $active_s or 1 == $active_c) {
					
					$calc_data['recalc'] = 1;
					$calc_data = apply_filters('get_calc_data_params', $calc_data, 'recalc', $item); 
					$cdata = get_calc_data($calc_data);					
					
					if (1 == $active_s) {
						$arr['exceed_pay'] = 0;
						$arr['recalc_amount'] = $date;
					}
					if (1 == $active_c) {
						$arr['recalc_course'] = $date;
					}					
					$arr['course_give'] = $cdata['course_give'];
					$arr['course_get'] = $cdata['course_get'];
					$arr['user_id'] = $item->user_id;
					$arr['user_discount'] = $cdata['user_discount'];
					$arr['user_discount_sum'] = $cdata['user_discount_sum'];
					$arr['exsum'] = $cdata['exsum'];
					$arr['sum1'] = $cdata['sum1'];
					$arr['dop_com1'] = $cdata['dop_com1'];
					$arr['sum1dc'] = $cdata['sum1dc'];
					$arr['com_ps1'] = $cdata['com_ps1'];
					$arr['sum1c'] = $cdata['sum1c'];
					$arr['sum1r'] = $cdata['sum1r'];
					$arr['sum2t'] = $cdata['sum2t'];
					$arr['sum2'] = $cdata['sum2'];
					$arr['dop_com2'] = $cdata['dop_com2'];
					$arr['com_ps2'] = $cdata['com_ps2'];
					$arr['sum2dc'] = $cdata['sum2dc'];
					$arr['sum2c'] = $cdata['sum2c'];
					$arr['sum2r'] = $cdata['sum2r'];	
					$arr['profit'] = $cdata['profit'];

					$arr = apply_filters('array_data_recalculate_bids', $arr, $direction, $v[$direction->currency_id_give], $v[$direction->currency_id_get], $cdata, $item);
					$wpdb->update($wpdb->prefix . "exchange_bids", $arr, array('id' => $item->id)); 

					$old_item = $item;
					$item = pn_object_replace($item, $arr);  
					
					goed_mail_to_changestatus_bids($item->id, $item, 'admin_recalcbids', 'user_recalcbids', $direction);
					
					do_action('recalculation_bid', $item->id, $item, $old_item);

				}
			}			
		}
	}
	
	return $item;
}

add_filter('list_cron_func', 'recalculation_bids_list_cron_func');
function recalculation_bids_list_cron_func($filters) {
	
	$filters['recalculation_bids'] = array(
		'title' => __('Recalculation of exchange amount', 'pn'),
		'file' => 'now',
		'site' => 'now',
	);
	
	$filters['del_recalclogs'] = array(
		'title' => __('Deleting recalculations log', 'pn'),
		'site' => '1day',
		'file' => 'none',
	);	
	
	return $filters;
}

function del_recalclogs() {
	global $wpdb, $premiumbox;
	
	if (!$premiumbox->is_up_mode()) {
		
		$count_day = intval(get_logs_sett('del_recalclogs_day'));
		if (!$count_day) { $count_day = 3; }

		if ($count_day > 0) {
			$time = current_time('timestamp') - ($count_day * DAY_IN_SECONDS); 
			$ldate = date('Y-m-d H:i:s', $time);
			$wpdb->query("DELETE FROM " . $wpdb->prefix . "recalclogs WHERE create_date < '$ldate'");
		}
	}
}

add_filter('list_logs_settings', 'recalculation_list_logs_settings');
function recalculation_list_logs_settings($filters) {	
	
	$filters['del_recalclogs_day'] = array(
		'title' => __('Deleting recalculations log', 'pn') . ' (' . __('days', 'pn') . ')',
		'count' => 3,
		'minimum' => 1,
	);
	
	return $filters;
}

add_action('admin_menu', 'pn_adminpage_recalculation', 1000);
function pn_adminpage_recalculation() {
	global $premiumbox;	
	
	if (current_user_can('administrator') or current_user_can('pn_bids')) {
		add_submenu_page("pn_bids", __('Recalculations log', 'pn'), __('Recalculations log', 'pn'), 'read', "pn_recalclogs", array($premiumbox, 'admin_temp'));
	}
	
}

add_action('recalculation_bid', 'recalclogs_recalculation_bid', 10, 3);
function recalclogs_recalculation_bid($item_id, $item, $old_item) {
	global $wpdb;	
	
	$old_item = (array)$old_item;
	$item = (array)$item;
	
	$arr1 = array();
	$arr1['course_give'] = $old_item['course_give'];
	$arr1['course_get'] = $old_item['course_get'];
	$arr1['sum1dc'] = $old_item['sum1dc'];
	$arr1['sum1c'] = $old_item['sum1c'];
	$arr1['sum2dc'] = $old_item['sum2dc'];
	$arr1['sum2c'] = $old_item['sum2c'];
	
	$arr2 = array();
	$arr2['course_give'] = $item['course_give'];
	$arr2['course_get'] = $item['course_get'];
	$arr2['sum1dc'] = $item['sum1dc'];
	$arr2['sum1c'] = $item['sum1c'];
	$arr2['sum2dc'] = $item['sum2dc'];
	$arr2['sum2c'] = $item['sum2c'];	
	
	$arr = array(
		'create_date' => current_time('mysql'),
		'bid_id' => $item_id,
		'old_data' => pn_json_encode($arr1),
		'new_data' => pn_json_encode($arr2),
	);
	$wpdb->insert($wpdb->prefix . "recalclogs", $arr); 
	
}

global $premiumbox;
$premiumbox->include_path(__FILE__, 'list');