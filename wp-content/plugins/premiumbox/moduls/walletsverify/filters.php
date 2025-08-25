<?php
if (!defined('ABSPATH')) { exit(); }

add_filter("pntable_trclass_pn_userwallets", 'walletsverify_pntable_trclass_pn_userwallets', 10, 2);
function walletsverify_pntable_trclass_pn_userwallets($tr_class, $item) {

	if (1 == is_isset($item, 'verify')) {
		$tr_class[] = 'tr_green';
	}

	return $tr_class;
}

add_filter("pntable_bulkactions_pn_userwallets", 'walletsverify_pntable_bulkactions_pn_userwallets');
function walletsverify_pntable_bulkactions_pn_userwallets($actions) {

	$new_actions = array(
		'verify'    => __('Verified', 'pn'),
		'unverify'    => __('Unverified', 'pn'),
	);
	$actions = pn_array_insert($actions, 'basket', $new_actions, 'before');

	return $actions;
}

add_filter("pntable_columns_pn_userwallets", 'walletsverify_pntable_columns_pn_userwallets', 100);
function walletsverify_pntable_columns_pn_userwallets($columns) {

	$columns['verifystatus'] = __('Status', 'pn');

	return $columns;
}

add_filter("pntable_column_pn_userwallets", 'walletsverify_pntable_column_pn_userwallets', 10, 3);
function walletsverify_pntable_column_pn_userwallets($return, $column_name, $item) {

	if ('verifystatus' == $column_name) {
		if (1 == $item->verify) {
			$status ='<span class="bgreen">' . __('Verified account nubmer', 'pn') . '</span>';
		} else {
			$status ='<span class="bred">' . __('Unverified account nubmer', 'pn') . '</span>';
		}

		return $status;
	}

	return $return;
}

add_filter("pntable_submenu_pn_userwallets", 'pntable_submenu_pn_userwallets_walletsverify', 10, 3);
function pntable_submenu_pn_userwallets_walletsverify($options) {

	$options['filter2'] = array(
		'options' => array(
			'1' => __('verified account number', 'pn'),
			'2' => __('unverified account number', 'pn'),
		),
		'title' => '',
	);

	return $options;
}

add_filter("pntable_searchwhere_pn_userwallets", 'pntable_searchwhere_pn_userwallets_walletsverify');
function pntable_searchwhere_pn_userwallets_walletsverify($where) {

	$filter2 = intval(is_param_get('filter2'));
	if (1 == $filter2) {
		$where .= " AND verify = '1'";
	} elseif (2 == $filter2) {
		$where .= " AND verify = '0'";
	}

	return $where;
}

add_action('pntable_userwallets_action', 'pntable_userwallets_action_walletsverify', 10, 2);
function pntable_userwallets_action_walletsverify($action, $post_ids) {
	global $wpdb;

	if ('verify' == $action) {
		foreach ($post_ids as $id) {
			$id = intval($id);
			$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE id = '$id' AND verify != '1'");
			if (isset($item->id)) {
				$result = $wpdb->query("UPDATE " . $wpdb->prefix . "user_wallets SET verify = '1' WHERE id = '$id'");
				do_action('item_userwallets_verify', $id, $item, $result);
			}
		}
	}
	if ('unverify' == $action) {
		foreach ($post_ids as $id) {
			$id = intval($id);
			$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE id = '$id' AND verify != '0'");
			if (isset($item->id)) {
				$result = $wpdb->query("UPDATE " . $wpdb->prefix . "user_wallets SET verify = '0' WHERE id = '$id'");
				do_action('item_userwallets_unverify', $id, $item, $result);
			}
		}
	}
}

add_action('item_userwallets_edit', 'walletsverify_item_userwallets_edit', 10, 3);
function walletsverify_item_userwallets_edit($id, $array, $last_data) {

	$array['id'] = $id;
	$item = (object)$array;
	if (0 == is_isset($last_data,'verify') and 1 == $item->verify) {
		do_action('item_userwallets_verify', $id, $item);
	}

	if (1 == is_isset($last_data,'verify') and 0 == $item->verify) {
		do_action('item_userwallets_unverify', $id, $item);
	}

}

add_filter('pn_userwallets_addform', 'walletsverify_pn_userwallets_addform', 10, 2);
function walletsverify_pn_userwallets_addform($options, $data) {

	$options['verify_line'] = array(
		'view' => 'line',
	);
	$options['verify'] = array(
		'view' => 'select',
		'title' => __('Status', 'pn'),
		'options' => array('0' => __('Unverified account nubmer', 'pn'), '1' => __('Verified account nubmer', 'pn')),
		'default' => is_isset($data, 'verify'),
		'name' => 'verify',
	);

	return $options;
}

add_filter('pn_userwallets_addform_post', 'walletsverify_pn_userwallets_addform_post');
function walletsverify_pn_userwallets_addform_post($array) {

	$array['verify'] = intval(is_param_post('verify'));

	return $array;
}

add_filter('_icon_indicators', 'uv_wallets_icon_indicators');
function uv_wallets_icon_indicators($lists) {

	$plugin = get_plugin_class();
	$lists['uv_wallets'] = array(
		'title' => __('Account verification requests', 'pn'),
		'img' => $plugin->plugin_url . 'images/verify.png',
		'url' => admin_url('admin.php?page=pn_userwallets_verify&filter=1')
	);

	return $lists;
}

add_filter('_icon_indicator_uv_wallets', 'def_icon_indicator_uv_wallets');
function def_icon_indicator_uv_wallets($count) {
	global $wpdb;

	if (current_user_can('administrator') or current_user_can('pn_userwallets')) {
		$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "uv_wallets WHERE status = '0'");
	}

	return $count;
}

if (!function_exists('list_tabs_currency_verify')) {
	add_filter('list_tabs_currency', 'list_tabs_currency_verify');
	function list_tabs_currency_verify($list_tabs) {

		$list_tabs['verify'] = __('Verification', 'pn');

		return $list_tabs;
	}
}

add_action('tab_currency_verify', 'walletsverify_tab_currency_verify', 10, 2);
function walletsverify_tab_currency_verify($data, $data_id) {

	$form = new PremiumForm();

	$has_verify = intval(get_currency_meta(is_isset($data, 'id'), 'has_verify'));
	$verify_files = intval(get_currency_meta(is_isset($data, 'id'), 'verify_files'));
	$help_verify = pn_strip_text(get_currency_meta(is_isset($data, 'id'), 'help_verify'));
?>

	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Ability for account verification', 'pn'); ?></span></div>
			<?php $form->select('has_verify', array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')), $has_verify);  ?>
		</div>
		<div class="add_tabs_single">
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Nubmer of images for upload', 'pn'); ?></span></div>
			<?php
			$form->input('verify_files', $verify_files, array());
			?>
		</div>
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Instruction for account verification', 'pn'); ?></span></div>
			<?php
			$form->editor('help_verify', $help_verify, 8, array(), 1, 0, '', 1, 1, 1);
			?>
		</div>
	</div>
<?php
}

add_action('item_currency_edit', 'item_currency_edit_walletsverify');
add_action('item_currency_add', 'item_currency_edit_walletsverify');
function item_currency_edit_walletsverify($data_id) {

	if ($data_id) {
		$has_verify = intval(is_param_post('has_verify'));
		update_currency_meta($data_id, 'has_verify', $has_verify);

		$verify_files = intval(is_param_post('verify_files'));
		update_currency_meta($data_id, 'verify_files', $verify_files);

		$help_verify = pn_strip_text(is_param_post_ml('help_verify'));
		update_currency_meta($data_id, 'help_verify', $help_verify);
	}

}

add_filter('item_userwallets_delete_before', 'uv_wallets_item_userwallets_delete_before', 10, 3);
function uv_wallets_item_userwallets_delete_before($arr, $id, $item) {
	global $premiumbox;

	if ($arr['ind']) {
		if (1 == $premiumbox->get_option('usve', 'disabledelete') and 1 == $item->verify and _is('is_action')) {
			$arr['ind'] = 0;
			$arr['error'] = __('forbidden to delete', 'pn');
		}
	}

	return $arr;
}

add_filter('body_list_userwallets', 'uv_wallets_body_list_userwallets', 10, 4);
function uv_wallets_body_list_userwallets($one_line, $item, $key, $title) {
	global $premiumbox, $wpdb;

	if ('action' == $key) {
		if (1 == $premiumbox->get_option('usve', 'disabledelete') and 1 == $item->verify) {
			$one_line = '--';
		}
	} elseif ('acc' == $key) {
		$verify = intval($item->verify);
		if (1 == $verify) {
			$one_line .= '<div class="verify_status success">' . __('Verified', 'pn') . '</div>';
		} else {
			$status = intval($premiumbox->get_option('usve', 'acc_status'));
			if ($status) {
				$currency_id = $item->currency_id;
				$has_verify = intval(get_currency_meta($currency_id, 'has_verify'));
				if (1 == $has_verify) {
					$user_wallet_id = $item->user_wallet_id;
					$verify_request = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "uv_wallets WHERE user_wallet_id = '$user_wallet_id' AND status = '0'");
					if ($verify_request > 0) {
						$one_line .= '<div class="verify_status wait">' . __('Verification request is in process', 'pn') . '</div>';
					} else {
						$one_line .= '<div class="verify_status not">'. userwallet_verify_link($user_wallet_id, $premiumbox->get_page('userwallets'), __('Pass verification', 'pn')) .'</div>';
					}
				}
			}
		}
	}

	return $one_line;
}

if (!function_exists('list_tabs_direction_verify')) {
	add_filter('list_tabs_direction', 'list_tabs_direction_verify');
	function list_tabs_direction_verify($list_tabs) {

		$list_tabs['verify'] = __('Verification', 'pn');

		return $list_tabs;
	}
}

add_action('tab_direction_verify', 'tab_direction_verify_uv_wallets',20,2);
function tab_direction_verify_uv_wallets($data, $data_id) {
?>
	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Account verification Send', 'pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Verified accounts only', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php
				$verify = get_direction_meta($data_id, 'verify_acc1');
				?>
				<select name="verify_acc1" autocomplete="off">
					<option value="0" <?php selected($verify, 0); ?>><?php _e('No', 'pn'); ?></option>
					<option value="1" <?php selected($verify, 1); ?>><?php _e('Yes', 'pn'); ?></option>
					<option value="2" <?php selected($verify, 2); ?>><?php _e('If exchange amount is more than', 'pn'); ?></option>
				</select>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange amount for Send', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php
				$verify_sum = get_direction_meta($data_id, 'verify_sum_acc1');
				?>
				<input type="text" name="verify_sum_acc1" style="width: 100%;" value="<?php echo is_sum($verify_sum); ?>" />
			</div>
		</div>
	</div>

	<div class="add_tabs_line">
		<div class="add_tabs_label"><span><?php _e('Account verification Receive', 'pn'); ?></span></div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Verified accounts only', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php
				$verify = get_direction_meta($data_id, 'verify_acc2');
				?>
				<select name="verify_acc2" autocomplete="off">
					<option value="0" <?php selected($verify, 0); ?>><?php _e('No', 'pn'); ?></option>
					<option value="1" <?php selected($verify, 1); ?>><?php _e('Yes', 'pn'); ?></option>
					<option value="2" <?php selected($verify, 2); ?>><?php _e('If exchange amount is more than', 'pn'); ?></option>
				</select>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Exchange amount for Receive', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php
				$verify_sum = get_direction_meta($data_id, 'verify_sum_acc2');
				?>
				<input type="text" name="verify_sum_acc2" style="width: 100%;" value="<?php echo is_sum($verify_sum); ?>" />
			</div>
		</div>
	</div>
<?php
}

add_action('item_direction_edit', 'item_direction_edit_uv_wallets');
add_action('item_direction_add', 'item_direction_edit_uv_wallets');
function item_direction_edit_uv_wallets($data_id) {

	update_direction_meta($data_id, 'verify_acc1', intval(is_param_post('verify_acc1')));
	update_direction_meta($data_id, 'verify_acc2', intval(is_param_post('verify_acc2')));
	update_direction_meta($data_id, 'verify_sum_acc1', is_sum(is_param_post('verify_sum_acc1')));
	update_direction_meta($data_id, 'verify_sum_acc2', is_sum(is_param_post('verify_sum_acc2')));

}

add_filter('direction_instruction_tags', 'userwalletsverify_directions_tags', 10, 2);
function userwalletsverify_directions_tags($tags, $key) {

    $in_page = array('description_txt', 'timeline_txt', 'window_txt', 'frozen_txt');
    if (!in_array($key, $in_page)) {
        $tags['create_acc_give'] = array(
            'title' => __('Link to verification of Send account', 'pn'),
            'start' => '[create_acc_give]',
        );
        $tags['create_acc_get'] = array(
            'title' => __('Link to verification of Receive account', 'pn'),
            'start' => '[create_acc_get]',
        );
    }

	return $tags;
}

add_filter('change_bid_status', 'userwalletsverify_change_bidstatus', 100);
function userwalletsverify_change_bidstatus($data) {
	global $wpdb, $premiumbox;

	$place = $data['place'];
	$set_status = $data['set_status'];
	$bid = $data['bid'];
	$who = $data['who'];
	$old_status = $data['old_status'];
	$direction = $data['direction'];
	$stop_action = intval(is_isset($data, 'stop'));

	if ('new' == $set_status and !$stop_action) {
		$show_error = intval($premiumbox->get_option('usve', 'create_notacc'));
		if (1 == $show_error) {
			$direction_id = $bid->direction_id;
			$cold = 0;
			if (1 != $bid->accv_give) {
				$verify = intval(get_direction_meta($direction_id, 'verify_acc1'));
				$sum = is_sum($bid->sum1);
				$verify_sum = is_sum(get_direction_meta($direction_id, 'verify_sum_acc1'));
				if (1 == $verify or 2 == $verify and $sum >= $verify_sum) {
					$cold = 1;
				}
			}
			if (1 != $bid->accv_get) {
				$verify = intval(get_direction_meta($direction_id, 'verify_acc2'));
				$sum = is_sum($bid->sum2c);
				$verify_sum = is_sum(get_direction_meta($direction_id, 'verify_sum_acc2'));
				if (1 == $verify or 2 == $verify and $sum >= $verify_sum) {
					$cold = 1;
				}
			}
			if (1 == $cold) {

				$array = array();
				$array['edit_date'] = current_time('mysql');
				$array['status'] = 'coldnew';
				$wpdb->update($wpdb->prefix . 'exchange_bids', $array, array('id' => $bid->id));

				$old_status = $bid->status;
				$bid = pn_object_replace($bid, $array);
				$data['bid'] = $bid;
				$data['stop'] = 1;

				$ch_data = array(
					'bid' => $bid,
					'set_status' => 'coldnew',
					'place' => 'walletsverify_module',
					'who' => 'user',
					'old_status' => $old_status,
					'direction' => $direction
				);
				_change_bid_status($ch_data);

			}
		}
	}

	return $data;
}

add_filter('coldnew_to_new', 'userwalletsverify_coldnew_to_new', 10, 2);
function userwalletsverify_coldnew_to_new($ind, $item) {

	if (1 == $ind) {
		$direction_id = $item->direction_id;
		$cold = 0;
		if (2 == $item->accv_give) {
			$cold = 1;
		}
		if (2 == $item->accv_get) {
			$cold = 1;
		}
		if (1 == $cold) {
			return 0;
		}
	}

	return $ind;
}

add_action('item_userwallets_verify', 'coldnew_item_userwallets_verify', 10, 2);
function coldnew_item_userwallets_verify($uw_id, $uw = '') {
	global $premiumbox, $wpdb;

	if (!isset($uw->id)) {
		$uw = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE id = '$uw_id'");
	}

	$user_id = intval(is_isset($uw, 'user_id'));
	$currency_id = intval(is_isset($uw, 'currency_id'));
	$account = trim(is_isset($uw, 'accountnum'));
	$st = get_status_sett('cancel');
	$wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET accv_give = '1' WHERE user_id = '$user_id' AND currency_id_give = '$currency_id' AND account_give = '$account' AND status IN($st)");
	$wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET accv_get = '1' WHERE user_id = '$user_id' AND currency_id_get = '$currency_id' AND account_get = '$account' AND status IN($st)");

	$show_error = intval($premiumbox->get_option('usve', 'create_notacc'));
	if (1 == $show_error) {
		coldnew_to_new();
	}
}

add_action('item_userwallets_unverify', 'coldnew_item_userwallets_unverify', 10, 2);
function coldnew_item_userwallets_unverify($uw_id, $uw = '') {
	global $premiumbox, $wpdb;

	if (!isset($uw->id)) {
		$uw = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE id = '$uw_id'");
	}

	$user_id = intval(is_isset($uw, 'user_id'));
	$currency_id = intval(is_isset($uw, 'currency_id'));
	$account = trim(is_isset($uw, 'accountnum'));
	$st = get_status_sett('cancel');
	$wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET accv_give = '2' WHERE user_id = '$user_id' AND currency_id_give = '$currency_id' AND account_give = '$account' AND status IN($st)");
	$wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET accv_get = '2' WHERE user_id = '$user_id' AND currency_id_get = '$currency_id' AND account_get = '$account' AND status IN($st)");

}

if (!function_exists('coldnew_to_new')) {
	function coldnew_to_new() {
		global $wpdb;

		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'coldnew'");
		foreach ($items as $item) {
			$ind = apply_filters('coldnew_to_new', 1, $item);
			if (1 == $ind) {

				$array = array();
				$array['edit_date'] = current_time('mysql');
				$array['status'] = 'new';
				$array = apply_filters('array_data_bids_new', $array, $item);
				$wpdb->update($wpdb->prefix . 'exchange_bids', $array, array('id' => $item->id));

				$bid = pn_object_replace($item, $array);

				$ch_data = array(
					'bid' => $bid,
					'set_status' => 'new',
					'place' => 'walletsverify_module',
					'who' => 'system',
					'old_status' => 'coldnew',
					'direction' => ''
				);
				_change_bid_status($ch_data);

			}
		}
	}
}

add_filter('direction_instruction', 'userwalletsverify_direction_instruction', 10, 5);
function userwalletsverify_direction_instruction($instruction, $txt_name, $direction, $vd1, $vd2) {
	global $bids_data, $direction_data, $wpdb, $premiumbox;

	$create_acc_give = '***no create bid***';
	$create_acc_get = '***no create bid***';

	if (isset($bids_data->id)) {
		$create_acc_give = '';
		$create_acc_get = '';

		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);

		$checkallcurr = intval($premiumbox->get_option('usve', 'checkallcurr'));
		$notusercheck = intval($premiumbox->get_option('usve', 'notusercheck'));

		$currency_id_give = $vd1->id;
		$currency_id_get = $vd2->id;
		$account_give = pn_strip_input($bids_data->account_give);
		$account_get = pn_strip_input($bids_data->account_get);

		if ($user_id < 0) {
			$create_acc_give = '<a href="' . $premiumbox->get_page('login') . '" class="js_window_login">' . __('Account verification link', 'pn') . '</a>';
			$create_acc_get = '<a href="' . $premiumbox->get_page('login') . '" class="js_window_login">' . __('Account verification link', 'pn') . '</a>';
		} else {
			if (strlen($account_give) > 1) {
				$where = " AND currency_id='$currency_id_give'";
				if ($checkallcurr) {
					$where = "";
				}
				$user_account = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE user_id = '$user_id' AND accountnum = '$account_give' $where");
				if (isset($user_account->id)) {
					if (1 == $user_account->verify) {
						$create_acc_give = __('Verified account nubmer', 'pn');
					} else {
						$create_acc_give = userwallet_verify_link($user_account->id, get_bids_url($bids_data->hashed), __('Account verification link', 'pn'));
					}
				} else {
					$create_acc_give = add_createwallet_link($currency_id_give, $account_give, __('Account verification link', 'pn'), get_bids_url($bids_data->hashed));
				}
			}
			if (strlen($account_get) > 1) {
				$where = " AND currency_id='$currency_id_get'";
				if ($checkallcurr) {
					$where = "";
				}
				$user_account = $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."user_wallets WHERE user_id = '$user_id' AND accountnum = '$account_get' $where");
				if (isset($user_account->id)) {
					if (1 == $user_account->verify) {
						$create_acc_get = __('Verified account nubmer', 'pn');
					} else {
						$create_acc_get = userwallet_verify_link($user_account->id, get_bids_url($bids_data->hashed), __('Account verification link', 'pn'));
					}
				} else {
					$create_acc_get = add_createwallet_link($currency_id_get, $account_get, __('Account verification link', 'pn'), get_bids_url($bids_data->hashed));
				}
			}
		}
	}

	$instruction = str_replace('[create_acc_give]', $create_acc_give, $instruction);
	$instruction = str_replace('[create_acc_get]', $create_acc_get, $instruction);

	return $instruction;
}

add_filter('error_bids', 'uv_wallets_error_bids', 970, 5);
function uv_wallets_error_bids($error_bids, $direction, $vd1, $vd2, $cdata) {
	global $wpdb, $premiumbox;

	$create_notacc = intval($premiumbox->get_option('usve', 'create_notacc')); //Разрешить создавать заявки, если счет не верифицирован

	$user_id = intval(is_isset($error_bids['bid'], 'user_id'));
	if (count($error_bids['error_text']) < 1) {

		$checkallcurr = intval($premiumbox->get_option('usve', 'checkallcurr')); //Сверять счет по всем валютам
		$notusercheck = intval($premiumbox->get_option('usve', 'notusercheck')); //Разрешить использовать верифицированные счета без авторизации

		$verify_account = intval(is_isset($direction, 'verify_acc1'));
		$sum = $cdata['sum1'];
		$sum_min = is_sum(is_isset($direction, 'verify_sum_acc1'));
		$currency_id = $vd1->id;
		$account = pn_strip_input(is_isset($error_bids['bid'], 'account_give'));
		if (1 == $verify_account or 2 == $verify_account and $sum >= $sum_min) {

			$error_bids['bid']['accv_give'] = 2;

			$n_err = 0;

			if (strlen($account) < 2) {
				$n_err = 1;
				if (!$create_notacc) {
					$error_bids['error_fields']['account1'] = __('field not filled', 'pn');
				}
			}

			if (!$n_err) {
				if ($user_id < 1 and !$notusercheck) {
					$n_err = 1;
					if (!$create_notacc) {
						$error_bids['error_text'][] = sprintf(__('Error! Direction is available to authorized users only, <a href="%1s" class="js_window_login">sign in</a> or <a href="%2s" class="js_window_join">sign up</a>', 'pn'), $premiumbox->get_page('login'), $premiumbox->get_page('register'));
					}
				}
			}

			if (!$n_err) {
				$where = "";
				if (!$checkallcurr) {
					$where .= " AND currency_id = '$currency_id'";
				}
				if (!$notusercheck) {
					$where .= " AND user_id = '$user_id'";
				}
				$user_account = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE accountnum = '$account' $where");
				if (isset($user_account->id)) {
					if (1 == $user_account->verify) {
						$error_bids['bid']['accv_give'] = 1;
					} else {
						if (!$create_notacc) {
							if ($user_id > 0) {
								$v_link = userwallet_verify_link($user_account->id, '', __('pass verification', 'pn'));
								$error_bids['error_fields']['account1'] = sprintf(__('account is not verified, %s', 'pn'), $v_link);
							} else {
								$error_bids['error_text'][] = sprintf(__('Error! Direction is available to authorized users only, <a href="%1s" class="js_window_login">sign in</a> or <a href="%2s" class="js_window_join">sign up</a>', 'pn'), $premiumbox->get_page('login'), $premiumbox->get_page('register'));
							}
						}
					}
				} else {
					if (!$create_notacc) {
						if ($user_id > 0) {
							$create_link_title = sprintf(__('add an account "%s"', 'pn'), $account);
							$create_link = add_createwallet_link($currency_id, $account, $create_link_title);
							$error_bids['error_text'][] = sprintf(__('Error! You need to %s to your account', 'pn'), $create_link);
						} else {
							$error_bids['error_text'][] = sprintf(__('Error! Direction is available to authorized users only, <a href="%1s" class="js_window_login">sign in</a> or <a href="%2s" class="js_window_join">sign up</a>', 'pn'), $premiumbox->get_page('login'), $premiumbox->get_page('register'));
						}
					}
				}
			}

		} else {
			$error_bids['bid']['accv_give'] = 0;
		}

		$verify_account = intval(is_isset($direction,'verify_acc2'));
		$sum = $cdata['sum2c'];
		$sum_min = is_sum(is_isset($direction,'verify_sum_acc2'));
		$currency_id = $vd2->id;
		$account = pn_strip_input(is_isset($error_bids['bid'], 'account_get'));
		if (1 == $verify_account or 2 == $verify_account and $sum >= $sum_min) {

			$error_bids['bid']['accv_get'] = 2;

			$n_err = 0;

			if (strlen($account) < 2) {
				$n_err = 1;
				if (!$create_notacc) {
					$error_bids['error_fields']['account2'] = __('field not filled', 'pn');
				}
			}

			if (!$n_err) {
				if ($user_id < 1 and !$notusercheck) {
					$n_err = 1;
					if (!$create_notacc) {
						$error_bids['error_text'][] = sprintf(__('Error! Direction is available to authorized users only, <a href="%1s" class="js_window_login">sign in</a> or <a href="%2s" class="js_window_join">sign up</a>', 'pn'), $premiumbox->get_page('login'), $premiumbox->get_page('register'));
					}
				}
			}

			if (!$n_err) {
				$where = "";
				if (!$checkallcurr) {
					$where .= " AND currency_id = '$currency_id'";
				}
				if (!$notusercheck) {
					$where .= " AND user_id = '$user_id'";
				}
				$user_account = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE accountnum = '$account' $where");
				if (isset($user_account->id)) {
					if (1 == $user_account->verify) {
						$error_bids['bid']['accv_get'] = 1;
					} else {
						if (!$create_notacc) {
							if ($user_id > 0) {
								$v_link = userwallet_verify_link($user_account->id, '', __('pass verification', 'pn'));
								$error_bids['error_fields']['account2'] = sprintf(__('account is not verified, %s', 'pn'), $v_link);
							} else {
								$error_bids['error_text'][] = sprintf(__('Error! Direction is available to authorized users only, <a href="%1s" class="js_window_login">sign in</a> or <a href="%2s" class="js_window_join">sign up</a>', 'pn'), $premiumbox->get_page('login'), $premiumbox->get_page('register'));
							}
						}
					}
				} else {
					if (!$create_notacc) {
						if ($user_id > 0) {
							$create_link_title = sprintf(__('add an account "%s"', 'pn'), $account);
							$create_link = add_createwallet_link($currency_id, $account, $create_link_title);
							$error_bids['error_text'][] = sprintf(__('Error! You need to %s to your account', 'pn'), $create_link);
						} else {
							$error_bids['error_text'][] = sprintf(__('Error! Direction is available to authorized users only, <a href="%1s" class="js_window_login">sign in</a> or <a href="%2s" class="js_window_join">sign up</a>', 'pn'), $premiumbox->get_page('login'), $premiumbox->get_page('register'));
						}
					}
				}
			}

		} else {
			$error_bids['bid']['accv_get'] = 0;
		}

	}

	return $error_bids;
}

add_filter('onebid_col2', 'onebid_col2_uv_wallets', 10, 3);
function onebid_col2_uv_wallets($actions, $item, $v) {

	$n_actions = array();
	$accv = intval($item->accv_give);
	if (1 == $accv) {
		$n_actions['account_give_ver'] = array(
			'type' => 'text',
			'title' => '',
			'label' => '<span class="bgreen">'. __('Verified account nubmer', 'pn') .'</span>',
		);
	}

	return pn_array_insert($actions, 'account_give', $n_actions);
}

add_filter('onebid_col3', 'onebid_col3_uv_wallets', 10, 3);
function onebid_col3_uv_wallets($actions, $item, $v) {

	$n_actions = array();
	$accv = intval($item->accv_get);
	if (1 == $accv) {
		$n_actions['account_get_ver'] = array(
			'type' => 'text',
			'title' => '',
			'label' => '<span class="bgreen">'. __('Verified account nubmer', 'pn') .'</span>',
		);
	}

	return pn_array_insert($actions, 'account_get', $n_actions);
}