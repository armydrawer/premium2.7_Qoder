<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('account_list_pages', 'account_list_pages_pp', 99);
function account_list_pages_pp($account_list_pages) {
	global $premiumbox;
	
	$pages = $premiumbox->get_option('partners', 'pages');
	if (!is_array($pages)) { $pages = array(); }
	foreach ($pages as $page) {
		$account_list_pages[$page] = array('type' => 'page');
	}
	
	return $account_list_pages;
}

add_filter('pp_banners', 'def_pp_banners', 0);
function def_pp_banners($banners) {
	
	$banners = array(
		'text' => __('Text materials', 'pn'),
		'banner1' => sprintf(__('Banners %s', 'pn'), '(468x60)'),
		'banner2' => sprintf(__('Banners %s', 'pn'), '(200x200)'),
		'banner3' => sprintf(__('Banners %s', 'pn'), '(120x600)'),
		'banner4' => sprintf(__('Banners %s', 'pn'), '(100x100)'),
		'banner5' => sprintf(__('Banners %s', 'pn'), '(88x31)'),
		'banner6' => sprintf(__('Banners %s', 'pn'), '(336x280)'),
		'banner7' => sprintf(__('Banners %s', 'pn'), '(250x250)'),
		'banner8' => sprintf(__('Banners %s', 'pn'), '(240x400)'),
		'banner9' => sprintf(__('Banners %s', 'pn'), '(234x60)'),
		'banner10' => sprintf(__('Banners %s', 'pn'), '(120x90)'),
		'banner11' => sprintf(__('Banners %s', 'pn'), '(120x60)'),
		'banner12' => sprintf(__('Banners %s', 'pn'), '(120x240)'),
		'banner13' => sprintf(__('Banners %s', 'pn'), '(125x125)'),
		'banner14' => sprintf(__('Banners %s', 'pn'), '(300x600)'),
		'banner15' => sprintf(__('Banners %s', 'pn'), '(300x250)'),
		'banner16' => sprintf(__('Banners %s', 'pn'), '(80x150)'),
		'banner17' => sprintf(__('Banners %s', 'pn'), '(728x90)'),
		'banner18' => sprintf(__('Banners %s', 'pn'), '(160x600)'),
		'banner19' => sprintf(__('Banners %s', 'pn'), '(80x15)'),
	);	
	
	return $banners;
}

add_filter('banner_pages', 'def_banner_pages');
function def_banner_pages($banner_pages) {
	global $premiumbox;
	
	$text_banners = intval($premiumbox->get_option('partners', 'text_banners'));
	if (!$text_banners) {
		if (isset($banner_pages['text'])) {
			unset($banner_pages['text']);
		}
	}
	
	return $banner_pages;
}

add_filter('list_tabs_currency', 'list_tabs_currency_pp', 200);
function list_tabs_currency_pp($list_tabs) {
	
	$list_tabs['partners'] = __('Affiliate program', 'pn');
	
	return $list_tabs;
}

add_action('tab_currency_partners', 'tab_currency_tab_pp', 10, 2);
function tab_currency_tab_pp($data, $data_id) {
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Allow affiliate money withdrawal', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$p_payout = intval(is_isset($data, 'p_payout')); 
				?>									
				<select name="p_payout" autocomplete="off"> 
					<option value="1" <?php selected($p_payout, 1); ?>><?php _e('Yes', 'pn'); ?></option>
					<option value="0" <?php selected($p_payout, 0); ?>><?php _e('No', 'pn'); ?></option>
				</select>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Fee of payment system for payout of funds to partner', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="payout_com" style="width: 100px;" value="<?php echo is_sum(is_isset($data, 'payout_com')); ?>" />%
			</div>
		</div>		
	</div>		
<?php
}

add_filter('pn_currency_addform_post', 'pn_currency_addform_post_pp');
function pn_currency_addform_post_pp($array) {
	
	$array['p_payout'] = intval(is_param_post('p_payout'));
	$array['payout_com'] = is_sum(is_param_post('payout_com'));
	
	return $array;
}

add_filter('_icon_indicators', 'pp_icon_indicators');
function pp_icon_indicators($lists) {
	
	$plugin = get_plugin_class();
	$lists['pp'] = array(
		'title' => __('Requests for payouts', 'pn'),
		'img' => $plugin->plugin_url . 'images/newpayout.png',
		'url' => admin_url('admin.php?page=pn_payouts&filter=1')
	);
	
	return $lists;
}

add_filter('_icon_indicator_pp', 'def_icon_indicator_pp');
function def_icon_indicator_pp($count) {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_pp')) {
		$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "user_payouts WHERE auto_status = '1' AND status = '0'");
	}	
	
	return $count;
}

add_filter('list_tabs_direction', 'list_tabs_direction_pp', 200);
function list_tabs_direction_pp($list_tabs) {
	
	$list_tabs['partners'] = __('Affiliate program', 'pn');
	
	return $list_tabs;
}

add_action('tab_direction_partners', 'tab_direction_tab_pp', 10, 2);
function tab_direction_tab_pp($data, $data_id) {
	
	$pp_data = get_direction_meta($data_id, 'pp_data');
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Affiliate payments', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<?php 
				$p_disable = intval(is_isset($pp_data, 'disable')); 
				?>									
				<select name="p_disable" autocomplete="off"> 
					<option value="0" <?php selected($p_disable, 0); ?>><?php _e('pay', 'pn'); ?></option>
					<option value="1" <?php selected($p_disable, 1); ?>><?php _e('not to pay', 'pn'); ?></option>
				</select>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Fixed amount of payment for benefit of partner', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="p_ind_sum" style="width: 100px;" value="<?php echo is_sum(is_isset($pp_data, 'ind_sum')); ?>" /><?php echo cur_type(); ?>
			</div>
		</div>		
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Min. amount of payment for benefit of partner', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="p_min_sum" style="width: 100px;" value="<?php echo is_sum(is_isset($pp_data, 'min_sum')); ?>" /><?php echo cur_type(); ?>
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Max. amount of payment for benefit of partner', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="p_max_sum" style="width: 100px;" value="<?php echo is_sum(is_isset($pp_data, 'max_sum')); ?>" /><?php echo cur_type(); ?>
			</div>
		</div>		
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Individual percent given by an affiliate program', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="p_pers" style="width: 100px;" value="<?php echo is_sum(is_isset($pp_data, 'pers')); ?>" />%
			</div>
		</div>		
	</div>
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Maximum percent given by an affiliate program', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="p_max" style="width: 100px;" value="<?php echo is_sum(is_isset($pp_data, 'max')); ?>" />%
			</div>
		</div>
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('for users (separated by commas)', 'pn'); ?></span></div>
			<div class="premium_wrap_standart">
				<input type="text" name="p_max_user" style="width: 100%;" value="<?php echo pn_strip_input(is_isset($pp_data, 'max_user')); ?>" />
			</div>
		</div>		
	</div>	
<?php
}
 
add_action('item_direction_edit', 'item_direction_edit_pp');
add_action('item_direction_add', 'item_direction_edit_pp');
function item_direction_edit_pp($data_id) {
	
	$pp_data = array();
	$pp_data['disable'] = intval(is_param_post('p_disable'));
	$pp_data['pers'] = is_sum(is_param_post('p_pers'));
	$pp_data['max'] = is_sum(is_param_post('p_max'));
	$pp_data['max_user'] = pn_strip_input(is_param_post('p_max_user'));
	$pp_data['ind_sum'] = is_sum(is_param_post('p_ind_sum'));
	$pp_data['min_sum'] = is_sum(is_param_post('p_min_sum'));
	$pp_data['max_sum'] = is_sum(is_param_post('p_max_sum'));	
	update_direction_meta($data_id, 'pp_data', $pp_data);
	
} 

add_action('item_user_payouts_wait', 'reserv_item_user_payouts_wait', 1, 3);
add_action('item_user_payouts_success', 'reserv_item_user_payouts_wait', 1, 3);
add_action('item_user_payouts_not', 'reserv_item_user_payouts_wait', 1, 3);
add_action('item_user_payouts_delete', 'reserv_item_user_payouts_wait', 1, 3);
add_action('item_user_payouts_basket', 'reserv_item_user_payouts_wait', 1, 3);
add_action('item_user_payouts_unbasket', 'reserv_item_user_payouts_wait', 1, 3);
function reserv_item_user_payouts_wait($id, $item, $result) {
	
	$result = intval($result);
	if (function_exists('update_currency_reserve') and $result) {
		update_currency_reserve($item->currency_id);
	}
	
}
 
add_filter('default_update_currency_reserve', 'default_update_currency_reserve_pp', 10);
function default_update_currency_reserve_pp($reserve_calc) {	

	if (!strstr($reserve_calc, '[payouts')) {
		$reserve_calc .= '- [payouts]';
	}
	
	return $reserve_calc;
}

add_filter('get_formula_code', 'pp_get_formula_code', 15, 4);   
function pp_get_formula_code($n, $code, $id, $update) { 
	global $wpdb, $premiumbox;
	
	if (strstr($code, '[payouts')) {
		$now_ids = str_replace(array('[payouts', ']'), '', $code);
		$bd_ids = formula_array_of_string($now_ids, $id);
		if (count($bd_ids) > 0) {
			$n = 0;
			$reserv = $premiumbox->get_option('partners', 'reserv');
			if (!is_array($reserv)) { $reserv = array(); }
			$status = create_data_for_db($reserv, 'int');
			$bd_id = create_data_for_db($bd_ids, 'int');
			if ($status) {
				$sum = $wpdb->get_var("SELECT SUM(pay_sum) FROM " . $wpdb->prefix . "user_payouts WHERE auto_status = '1' AND currency_id IN($bd_id) AND status IN($status)");
				return is_sum($sum);
			} 	
		}
	}
	
	return $n;
}	
 
add_filter('change_bid_status', 'pp_change_bidstatus', 90);    
function pp_change_bidstatus($data) { 
	global $wpdb, $premiumbox;

	$place = $data['place'];
	$set_status = $data['set_status'];
	$bid = $data['bid'];
	$who = $data['who'];
	$old_status = $data['old_status'];
	$direction = $data['direction'];

	$stop_action = intval(is_isset($data, 'stop'));

	$item_id = $bid->id;
	$not = array('realdelete', 'auto', 'archived');
	if (!in_array($set_status, $not) and !$stop_action) {
		if ('success' == $set_status) {
			$calc = intval($premiumbox->get_option('partners', 'calc'));
			if (0 == $calc or 1 == $calc and $bid->user_id > 0) {
				$ref_id = $bid->ref_id;
				$partner_sum = is_sum($bid->partner_sum);
				if ($ref_id and $partner_sum > 0) {
					$rd = get_userdata($ref_id);
					$ctype = cur_type();
					if (isset($rd->user_email)) {
						$ref_email = is_email($rd->user_email);
						$arr = array('pcalc' => 1);
						$data['bid'] = pn_object_replace($bid, $arr);
						$wpdb->update($wpdb->prefix . 'exchange_bids', $arr, array('id' => $item_id));
					
						$notify_tags = array();
						$notify_tags['[sum]'] = $partner_sum;
						$notify_tags['[ctype]'] = $ctype;
						$notify_tags = apply_filters('notify_tags_partprofit', $notify_tags);		

						$user_send_data = array(
							'user_email' => $ref_email,
						);	
						$user_send_data = apply_filters('user_send_data', $user_send_data, 'partprofit', $rd);
						$result_mail = apply_filters('premium_send_message', 0, 'partprofit', $notify_tags, $user_send_data);						
					}
				}
			}
		} elseif ($bid->pcalc > 0) {
			$arr = array('pcalc' => 0);
			$data['bid'] = pn_object_replace($bid, $arr);
			$wpdb->update($wpdb->prefix . 'exchange_bids', $arr, array('id' => $item_id));
		}		
	}
	
	return $data;
}

function pp_calculate_bid($array, $direction, $vd1, $vd2, $cdata, $ref_id = 0) {
	global $wpdb, $premiumbox;	

	$ref_id = intval($ref_id);
	$partner_pers = 0;
	$partner_sum = 0;
	$direction_id = $direction->id;
	$user_id = $array['user_id'];
	$txt = '';
	
	if (isset($direction->pp_data)) {
		$pp_data = maybe_unserialize(is_isset($direction, 'pp_data'));
	} else {
		$pp_data = get_direction_meta($direction_id, 'pp_data');
	}	
	
	$p_disable = intval(is_isset($pp_data, 'disable'));		
	if (1 != $p_disable) {		
						
		if ($ref_id < 1) {

			if (0 == intval($premiumbox->get_option('partners', 'wref')) and $user_id) {
				$ui = get_userdata($user_id);
				$ref_id = intval(is_isset($ui, 'ref_id'));
			}	
			
			if (!$ref_id) {
				$ref_id = intval(get_time_cookie('ref_id')); 
			}

		}
				
		$profit = is_sum($array['profit']);
		$exsum = is_sum($array['exsum']);
		
		$user_discount = is_sum($array['user_discount']);
		$user_discount_sum = 0;
		if ($user_discount > 0 and $exsum > 0) {
			$user_discount_sum = is_sum($exsum / 100 * $user_discount);
		}
		
		if ($ref_id and $ref_id != $user_id) {
			$ref_data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "users WHERE ID = '$ref_id'");
			if (isset($ref_data->ID)) {
				
				$scalc = intval($premiumbox->get_option('partners', 'scalc'));
				
				$partner_type = intval(is_isset($ref_data, 'partner_type'));
				if (1 == $partner_type) {
					$scalc = 0;
				} elseif (2 == $partner_type) {
					$scalc = 1;
				}				
				
				if (1 == $scalc) {
					$r_sum = $exsum;
					$txt1 = 'exchange sum = 0,';
				} else {
					$r_sum = $profit;
					$txt1 = 'profit sum = 0,';
				}				
				
				$p_ind_sum = is_sum(is_isset($pp_data, 'ind_sum'));
				if ($p_ind_sum > 0) {
					$partner_sum = $p_ind_sum;
					$txt .= 'individual amount from direction settings,';
				} else {
					if ($r_sum > 0) {
						$p_pers = is_sum(is_isset($pp_data, 'pers'));					
						if ($p_pers > 0) {
							$partner_pers = $p_pers;
							$txt .= 'individual partner percent from direction settings';
						} else {
							$partner_pers = get_user_pers_refobmen($ref_id);
														
							$from_usr = array($ref_id);
							$p_max_user = pn_strip_input(is_isset($pp_data, 'max_user'));
							if ($p_max_user) {
								$from_usr = explode(',', $p_max_user);
								$from_usr = array_map('intval', $from_usr);
							} 
							$p_max = is_sum(is_isset($pp_data, 'max'));	
							if (in_array($ref_id, $from_usr)) {
								if ($p_max > 0 and $partner_pers > $p_max) { $partner_pers = $p_max; }
							}
							$txt .= 'partner percent = ' . $partner_pers .',';							
							
						}	
						if ($partner_pers > 0) {
							$partner_sum = $r_sum / 100 * $partner_pers;
							$partner_sum = is_sum($partner_sum);
						} else {
							$txt .= 'partner percent = 0,';
						}	
					} else {
						$txt .= $txt1;
					}
				} 
				
				if ($user_discount > 0 and $partner_sum > 0) { 
					$uskidka = intval($premiumbox->get_option('partners', 'uskidka'));
					if (1 == $uskidka) {
						$one_pers = $partner_sum / 100;
						$partner_sum = $partner_sum - ($one_pers * $user_discount);
					} elseif (2 == $uskidka) {
						$partner_sum = $partner_sum - $user_discount_sum;
					}
					if ($partner_sum <= 0) {
						$txt .= 'but minus user discount,';
					}
				}
				
				$p_min_sum = is_sum(is_isset($pp_data, 'min_sum'));
				$p_max_sum = is_sum(is_isset($pp_data, 'max_sum'));
				
				if ($partner_sum < $p_min_sum) { $partner_sum = $p_min_sum; $txt .= 'taken the minimum amount from the direction settings,'; }
				if ($p_max_sum > 0 and $partner_sum > $p_max_sum) { $partner_sum = $p_max_sum; $txt .= 'taken the maximum amount from the direction settings,'; }
				
			} else {
				$ref_id = 0;
				$txt = 'no referal in db';
			}
		} else {
			$ref_id = 0;
			$txt = 'no referal';
		}
	} else {
		$ref_id = 0;
		$txt = 'disabled in direction';
	}
	
	$array['ref_id'] = $ref_id;
	$array['partner_sum'] = $partner_sum;
	$array['partner_pers'] = $partner_pers;	
	$array['pcalc_txt'] = $txt;	

	return $array;
}

add_filter('array_data_create_bids', 'pp_array_data_create_bids', 10, 5);
function pp_array_data_create_bids($array, $direction, $vd1, $vd2, $cdata) {
	
	return pp_calculate_bid($array, $direction, $vd1, $vd2, $cdata, is_isset($array, 'ref_id'));
}

add_filter('array_data_recalculate_bids', 'pp_array_data_recalculate_bids', 10, 6);
function pp_array_data_recalculate_bids($array, $direction, $vd1, $vd2, $cdata, $item = '') {
	
	return pp_calculate_bid($array, $direction, $vd1, $vd2, $cdata, is_isset($item, 'ref_id'));
}

add_filter('change_bids_filter_list', 'pp_change_bids_filter_list'); 
function pp_change_bids_filter_list($lists) {
	
	$lists['user']['ref_id'] = array(
		'title' => __('Partner ID', 'pn'),
		'name' => 'ref_id',
		'view' => 'input',
		'work' => 'input',
	);		
	
	return $lists;
}

add_filter('where_request_sql_bids', 'pp_where_request_sql_bids', 10,2); 
function pp_where_request_sql_bids($where, $pars_data) {
	global $wpdb;
	
	$pr = $wpdb->prefix;
	$sql_operator = is_sql_operator($pars_data);
	$ref_id = intval(is_isset($pars_data, 'ref_id'));
	if ($ref_id > 0) {
		$where = " {$sql_operator} {$pr}exchange_bids.ref_id='$ref_id'"; 
	}	
	
	return $where;
}

add_filter('onebid_hidecol1', 'pp_onebid_hidecol1', 10, 3);
function pp_onebid_hidecol1($cols, $item, $v) {
	
	if (isset($item->ref_id)) {
		$rui = get_userdata($item->ref_id);
		$ref_login = is_isset($rui, 'user_login');
	} else {
		$ref_login = '---';
	}	
	
	$cols['referal'] = array(
		'type' => 'text',
		'title' => __('Referal', 'pn'),
		'label' => $ref_login,
	);
	$cols['partner_sum'] = array(
		'type' => 'text',
		'title' => __('Partner earned', 'pn'),
		'label' => is_sum($item->partner_sum) . ' '. cur_type(),
	);
	$cols['partner_pers'] = array(
		'type' => 'text',
		'title' => __('Partner percent', 'pn'),
		'label' => is_sum($item->partner_pers) . ' %',
	);	
	$pcalc_txt = pn_strip_input($item->pcalc_txt);
	if (strlen($pcalc_txt) > 0) {
		$cols['pcalc_txt'] = array(
			'type' => 'text',
			'title' => __('Partner log', 'pn'),
			'label' => $pcalc_txt,
		);
	}
	
	return $cols;
}