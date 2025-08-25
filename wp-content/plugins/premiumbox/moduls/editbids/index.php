<?php
if( !defined( 'ABSPATH')){ exit(); }

/*
title: [en_US:]Order editor[:en_US][ru_RU:]Редактор заявки на обмен[:ru_RU]
description: [en_US:]Order editor[:en_US][ru_RU:]Редактор заявки на обмен[:ru_RU]
version: 2.7.0
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path); 

add_filter('pn_caps', 'editbids_pn_caps');
function editbids_pn_caps($caps) {
	
	$caps['pn_bids_edit'] = __('Edit order', 'pn');
	
	return $caps;
}

add_filter('onebid_actions', 'onebid_actions_dop_editbids', 1000, 3);
function onebid_actions_dop_editbids($onebid_actions, $item, $v) {
	
	if (current_user_can('administrator') or current_user_can('pn_bids_edit')) {
		$onebid_actions['edit_bids'] = array(
			'type' => 'link',
			'title' => __('Edit', 'pn'),
			'label' => __('Edit', 'pn'),
			'link' => get_request_link('edit_one_bid', 'html', get_locale(), array('item_id' => $item->id)),
			'link_target' => '_blank',
			'link_class' => 'editting',
		);	
	}
	
	return $onebid_actions;
}

add_filter('list_param_edit_bids', 'def_list_param_edit_bids', 10, 2);
function def_list_param_edit_bids($lists, $item) {
	
	$lists['course_give'] = array(
		'title' => __('Rate Send', 'pn'),
		'name' => 'course_give',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'course_give')),
		'work' => 'sum',
	);
	$lists['course_get'] = array(
		'title' => __('Rate Receive', 'pn'),
		'name' => 'course_get',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'course_get')),
		'work' => 'sum',
	);	
	$lists['exsum'] = array(
		'title' => __('Amount in internal currency', 'pn'),
		'name' => 'exsum',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'exsum')),
		'work' => 'sum',
	);	
	$lists['profit'] = array(
		'title' => __('Profit', 'pn'),
		'name' => 'profit',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'profit')),
		'work' => 'sum',
	);	
	$lists['user_discount'] = array(
		'title' => __('User discount (%)', 'pn'),
		'name' => 'user_discount',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'user_discount')),
		'work' => 'sum',
	);
	$lists['user_discount_sum'] = array(
		'title' => __('User discount (amount)', 'pn'),
		'name' => 'user_discount_sum',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'user_discount_sum')),
		'work' => 'sum',
	);	
	$lists['to_account'] = array(
		'title' => __('Merchant account', 'pn'),
		'name' => 'to_account',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'to_account')),
		'work' => 'input',
	);
	$lists['dest_tag'] = array(
		'title' => __('Destination tag', 'pn'),
		'name' => 'dest_tag',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'dest_tag')),
		'work' => 'input',
	);	
	$lists['from_account'] = array(
		'title' => __('Automatic payout account', 'pn'),
		'name' => 'from_account',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'from_account')),
		'work' => 'input',
	);
	$lists['trans_in'] = array(
		'title' => __('Merchant transaction ID', 'pn'),
		'name' => 'trans_in',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'trans_in')),
		'work' => 'input',
	);
	$lists['trans_out'] = array(
		'title' => __('Auto payout transaction ID', 'pn'),
		'name' => 'trans_out',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'trans_out')),
		'work' => 'input',
	);
	$lists['txid_in'] = array(
		'title' => __('Merchant TxID', 'pn'),
		'name' => 'txid_in',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'txid_in')),
		'work' => 'input',
	);
	$lists['txid_out'] = array(
		'title' => __('Auto payout TxID', 'pn'),
		'name' => 'txid_out',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'txid_out')),
		'work' => 'input',
	);	
	$lists['pay_sum'] = array(
		'title' => __('Real amount to pay', 'pn'),
		'name' => 'pay_sum',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'pay_sum')),
		'work' => 'input',
	);
	$lists['pay_ac'] = array(
		'title' => __('Real account', 'pn'),
		'name' => 'pay_ac',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'pay_ac')),
		'work' => 'input',
	);	
	$lists['out_sum'] = array(
		'title' => __('Real amount to auto payout', 'pn'),
		'name' => 'out_sum',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'out_sum')),
		'work' => 'input',
	);
	$lists['account_give'] = array(
		'title' => __('From account', 'pn'),
		'name' => 'account_give',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'account_give')),
		'work' => 'input',
	);
	$lists['account_get'] = array(
		'title' => __('Into account', 'pn'),
		'name' => 'account_get',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'account_get')),
		'work' => 'input',
	);
	$lists['sum1'] = array(
		'title' => __('Amount To send', 'pn'),
		'name' => 'sum1',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'sum1')),
		'work' => 'sum',
	);
	$lists['dop_com1'] = array(
		'title' => __('Add. fees amount', 'pn'),
		'name' => 'dop_com1',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'dop_com1')),
		'work' => 'sum',
	);
	$lists['sum1dc'] = array(
		'title' => __('Amount (with add. fees)', 'pn'),
		'name' => 'sum1dc',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'sum1dc')),
		'work' => 'sum',
	);	
	$lists['com_ps1'] = array(
		'title' => __('PS fees amount', 'pn'),
		'name' => 'com_ps1',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'com_ps1')),
		'work' => 'sum',
	);	
	$lists['sum1c'] = array(
		'title' => __('Amount (with add. fees and PS fees)', 'pn'),
		'name' => 'sum1c',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'sum1c')),
		'work' => 'sum',
	);
	$lists['sum1r'] = array(
		'title' => __('Amount for reserve', 'pn'),
		'name' => 'sum1r',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'sum1r')),
		'work' => 'sum',
	);	
	$lists['sum2t'] = array(
		'title' => __('Amount at the Exchange Rate', 'pn'),
		'name' => 'sum2t',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'sum2t')),
		'work' => 'sum',
	);	
	$lists['sum2'] = array(
		'title' => __('Amount (discount included)', 'pn'),
		'name' => 'sum2',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'sum2')),
		'work' => 'sum',
	);
	$lists['dop_com2'] = array(
		'title' => __('Add. fees amount', 'pn'),
		'name' => 'dop_com2',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'dop_com2')),
		'work' => 'sum',
	);	
	$lists['sum2dc'] = array(
		'title' => __('Amount To receive (add. fees)', 'pn'),
		'name' => 'sum2dc',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'sum2dc')),
		'work' => 'sum',
	);
	$lists['com_ps2'] = array(
		'title' => __('PS fees amount', 'pn'),
		'name' => 'com_ps2',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'com_ps2')),
		'work' => 'sum',
	);	
	$lists['sum2c'] = array(
		'title' => __('Amount To receive (add.fees and PS fees)', 'pn'),
		'name' => 'sum2c',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'sum2c')),
		'work' => 'sum',
	);
	$lists['sum2r'] = array(
		'title' => __('Amount for reserve', 'pn'),
		'name' => 'sum2r',
		'view' => 'input',
		'default' => is_sum(is_isset($item, 'sum2r')),
		'work' => 'sum',
	);
	$lists['last_name'] = array(
		'title' => __('Last name', 'pn'),
		'name' => 'last_name',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'last_name')),
		'work' => 'input',
	);	
	$lists['first_name'] = array(
		'title' => __('First name', 'pn'),
		'name' => 'first_name',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'first_name')),
		'work' => 'input',
	);
	$lists['second_name'] = array(
		'title' => __('Second name', 'pn'),
		'name' => 'second_name',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'second_name')),
		'work' => 'input',
	);	
	$lists['user_phone'] = array(
		'title' => __('Mobile phone number', 'pn'),
		'name' => 'user_phone',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'user_phone')),
		'work' => 'input',
	);
	$lists['user_skype'] = array(
		'title' => __('Skype', 'pn'),
		'name' => 'user_skype',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'user_skype')),
		'work' => 'input',
	);
	$lists['user_telegram'] = array(
		'title' => __('Telegram', 'pn'),
		'name' => 'user_telegram',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'user_telegram')),
		'work' => 'input',
	);	
	$lists['user_email'] = array(
		'title' => __('E-mail', 'pn'),
		'name' => 'user_email',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'user_email')),
		'work' => 'input',
	);		
	$lists['user_passport'] = array(
		'title' => __('Passport number', 'pn'),
		'name' => 'user_passport',
		'view' => 'input',
		'default' => pn_strip_input(is_isset($item, 'user_passport')),
		'work' => 'input',
	);						
	
	return $lists;
}

add_action('premium_request_edit_one_bid', 'def_edit_one_bid');
function def_edit_one_bid() {
	global $wpdb, $premiumbox;
	
	if (current_user_can('administrator') or current_user_can('pn_bids_edit')) {
		$bid_id = intval(is_param_get('item_id'));
		if ($bid_id > 0) {
			$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$bid_id'");
			if (isset($item->id)) {
				$title = sprintf(__('Edit order ID %s', 'pn'), $bid_id);		
?>
<!DOCTYPE html>
<html <?php echo get_language_attributes('html'); ?>>
<head>
	<meta charset="<?php echo get_bloginfo('charset'); ?>">
	<title><?php echo $title; ?></title>
			
	<script type="text/javascript" src="<?php echo get_premium_url(); ?>js/jquery/script.min.js"></script>
	<link rel="stylesheet" href="<?php echo $premiumbox->plugin_url; ?>moduls/editbids/bid_style.css?vers=<?php echo current_time('U'); ?>" type="text/css" media="all" />
	<?php do_action('edit_bid_head', $item); ?>

</head>
<body>
<div id="container">	
	<div class="header">
		<div class="header_ins">
			<?php echo $title; ?>
		</div>
	</div>
<?php 
	$reply = is_param_get('reply');
	if ('true' == $reply) {
?>
	<div class="resulttrue"><?php _e('Action completed successfully','pn'); ?></div>
<?php
	} elseif ('false' == $reply) {
?>
	<div class="resultfalse"><?php _e('Error! You have entered an incorrect security password', 'pn'); ?></div>
<?php		
	}
	$lists = apply_filters('list_param_edit_bids', array(), $item); 
?>
	<div class="content">
		<div class="content_ins">
			<form method="post" action="<?php the_pn_link('edit_one_bid_post', 'post'); ?>">
				<input type="hidden" name="item_id" value="<?php echo $bid_id; ?>" />
				<table>
					<?php
					foreach ($lists as $list) {
					?>
					<tr>
						<th><?php echo is_isset($list, 'title'); ?>:</th>
						<td><input type="text" name="<?php echo is_isset($list, 'name'); ?>" value="<?php echo is_isset($list, 'default'); ?>" autocomplete="off" /></td>
					</tr>				
					<?php } ?>
					
					<?php
					if (is_has_admin_password('edit')) {
						$placeholder = '';
						if (is_pass_protected('edit')) {
							$placeholder = __('Enter security password', 'pn');
						}
						?>
						<tr class="password_tr">
							<th><?php _e('Security password', 'pn'); ?>:</th>
							<td><input type="password" name="pass" placeholder="<?php echo $placeholder; ?>" autocomplete="off" value="" /></td>
						</tr>
						<?php
					}
					?>
					<tr>
						<th></th>
						<td><input type="submit" name="" value="<?php _e('Edit', 'pn'); ?>" /></td>
					</tr>
				</table>
			</form>
		</div>
	</div>
	<?php do_action('onebid_edit_view', $bid_id, $item, $lists); ?>
</div>	
</body>
</html>	
<?php
			} else {
				pn_display_mess(__('Error! Order does not exist','pn'));
			}
		} else {
			pn_display_mess(__('Error! Order does not exist','pn'));
		}
	} else {
		pn_display_mess(__('Error! Insufficient privileges','pn'));
	}
}  

add_action('premium_action_edit_one_bid_post', 'def_edit_one_bid_post');
function def_edit_one_bid_post() {
	global $wpdb, $premiumbox;

	_method('post');
	
	if (current_user_can('administrator') or current_user_can('pn_bids_edit')) {
		
		$bid_id = intval(is_param_post('item_id'));
		
		$error = save_pass_protected(is_param_post('pass'), 'edit'); 
		if ($error) {
			$back_url = get_request_link('edit_one_bid', 'html', get_locale(), array('item_id' => $bid_id, 'reply' => 'false'));
			wp_redirect($back_url);
			exit;			
		}

		if ($bid_id > 0) {
			$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$bid_id'");
			if (isset($item->id)) {
				
				$arr = array();
				$lists = apply_filters('list_param_edit_bids', array(), $item); 
				foreach ($lists as $list) {
					$name = trim(is_isset($list, 'name'));
					if ($name) {
						$work = trim(is_isset($list, 'work'));
						$value = is_param_post($name);
						if ('input' == $work) {
							$value = pn_strip_input($value);
						} elseif ('sum' == $work) {
							$value = is_sum($value);
						}
						$arr[$name] = $value;
					}
				}	
				
				if (count($arr) > 0) {
					
					$wpdb->update($wpdb->prefix . "exchange_bids", $arr, array('id' => $item->id));
					
					do_action('onebid_edit', $bid_id, $arr, $item, $lists);

					$item = pn_object_replace($item, $arr);
					bid_hashdata($item->id, $item);
					
				}
				
				$back_url = get_request_link('edit_one_bid', 'html', get_locale(), array('item_id' => $bid_id, 'reply' => 'true')); 
				wp_redirect($back_url);
				exit;
				
			} 
		} 		
	} 

	pn_display_mess(__('Error! Insufficient privileges', 'pn'));
}