<?php
if (!defined('ABSPATH')) { exit(); }
 
add_filter('list_tabs_direction', 'merch_list_tabs_direction', 100, 2);
function merch_list_tabs_direction($list_tabs, $db_data) {
	
	if (current_user_can('administrator') or current_user_can('pn_directions_merchant')) {
		$list_tabs['merch'] = __('Merchants and payouts', 'pn');
	}	
	
	return $list_tabs;
}

add_action('tab_direction_merch', 'directions_cf_tab_direction_tab11', 10, 2);
function directions_cf_tab_direction_tab11($data, $data_id) {
	
	if (current_user_can('administrator') or current_user_can('pn_directions_merchant')) {
			
		$paymerch_data = get_direction_meta($data_id, 'paymerch_data');

		$lists = list_extandeds_data('merchants');
		$m_arr = @unserialize(is_isset($data, 'm_in'));
		$m_arr = (array)$m_arr;
		
		$lists = list_checks_top($lists, $m_arr);
		?>	
		
		<div class="add_tabs_line">
			<div class="add_tabs_label"><span><?php _e('Merchant', 'pn'); ?></span></div>
			<div class="add_tabs_single long">
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();				
					foreach ($lists as $m_key => $m_data) {
						$checked = 0;
						if (in_array($m_key, $m_arr)) {
							$checked = 1;
						}	
						$link_title = $m_data['title'];
						if (current_user_can('administrator') or current_user_can('pn_merchants')) {
							$link_title = '<a href="' . admin_url('admin.php?page=pn_merchants_add&item_id=' . $m_data['id']) . '" target="_blank">' . $m_data['title'] . '</a>';
						}
						$scroll_lists[] = array(
							'title' => $link_title,
							'search' => $m_data['title'],
							'checked' => $checked,
							'value' => $m_key,
						);
					}
					echo get_check_list($scroll_lists, 'm_in[]', '', '', 1);
					?>			
				</div>			
			</div>
		</div>
		
		<div class="add_tabs_line">
			<div class="add_tabs_label"></div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Daily limit for merchant', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php 
					$m_in_max = is_sum(is_isset($paymerch_data, 'm_in_max')); 
					?>			
					<input type="text" name="m_in_max" style="width: 100%;" value="<?php echo $m_in_max; ?>" />
				</div>			
			</div>	
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Monthly limit for merchant', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php 
					$m_in_max_month = is_sum(is_isset($paymerch_data, 'm_in_max_month'));  
					?>			
					<input type="text" name="m_in_max_month" style="width: 100%;" value="<?php echo $m_in_max_month; ?>" />
				</div>			
			</div>		
		</div>
		
		<div class="add_tabs_line">
			<div class="add_tabs_label"></div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Min. payment amount for single order', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php 
					$m_in_min_sum = is_sum(is_isset($paymerch_data, 'm_in_min_sum'));  
					?>			
					<input type="text" name="m_in_min_sum" style="width: 100%;" value="<?php echo $m_in_min_sum; ?>" />
				</div>			
			</div>			
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Max. payment amount for single order', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php 
					$m_in_max_sum = is_sum(is_isset($paymerch_data, 'm_in_max_sum'));  
					?>			
					<input type="text" name="m_in_max_sum" style="width: 100%;" value="<?php echo $m_in_max_sum; ?>" />
				</div>			
			</div>		
		</div>

		<div class="add_tabs_line">
			<div class="add_tabs_label"></div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Daily limit of orders (quantities) for merchant', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php 
					$m_in_maxc_day = intval(is_isset($paymerch_data, 'm_in_maxc_day')); 
					?>			
					<input type="text" name="m_in_maxc_day" style="width: 100%;" value="<?php echo $m_in_maxc_day; ?>" />
				</div>			
			</div>	
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Monthly limit of orders (quantities) for merchant', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php 
					$m_in_maxc_month = intval(is_isset($paymerch_data, 'm_in_maxc_month'));  
					?>			
					<input type="text" name="m_in_maxc_month" style="width: 100%;" value="<?php echo $m_in_maxc_month; ?>" />
				</div>			
			</div>		
		</div>	

		<?php
			$lists = list_extandeds_data('paymerchants');
			$m_arr = @unserialize(is_isset($data, 'm_out'));
			$m_arr = (array)$m_arr;	
		
			$lists = list_checks_top($lists, $m_arr);
		?>
		
		<div class="add_tabs_line">
			<div class="add_tabs_label"><span><?php _e('Automatic payout', 'pn'); ?></span></div>
			<div class="add_tabs_single long">
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
									
					foreach ($lists as $m_key => $m_data) {
						$checked = 0;
						if (in_array($m_key, $m_arr)) {
							$checked = 1;
						}
						$link_title = $m_data['title'];
						if (current_user_can('administrator') or current_user_can('pn_merchants')) {
							$link_title = '<a href="' . admin_url('admin.php?page=pn_paymerchants_add&item_id=' . $m_data['id']) . '" target="_blank">' . $m_data['title'] . '</a>';
						}					
						$scroll_lists[] = array(
							'title' => $link_title,
							'search' => $m_data['title'],
							'checked' => $checked,
							'value' => $m_key,
						);
					}
					echo get_check_list($scroll_lists, 'm_out[]', '', '', 1);
					?>
				</div>			
			</div>	
		</div>
		<div class="add_tabs_line">
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Automatic payout when order has status "Paid order"', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php 
					$m_out_realpay = intval(is_isset($paymerch_data, 'm_out_realpay')); 
					?>									
					<select name="m_out_realpay" autocomplete="off"> 
						<option value="0" <?php selected($m_out_realpay, 0); ?>>--<?php _e('Default', 'pn'); ?>--</option>
						<option value="1" <?php selected($m_out_realpay, 1); ?>><?php _e('No', 'pn'); ?></option>
						<option value="2" <?php selected($m_out_realpay, 2); ?>><?php _e('Yes', 'pn'); ?></option>
					</select>
				</div>			
			</div>	
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Automatic payout when order has status "Order is on checking"', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php 
					$m_out_verify = intval(is_isset($paymerch_data, 'm_out_verify')); 
					?>									
					<select name="m_out_verify" autocomplete="off"> 
						<option value="0" <?php selected($m_out_verify, 0); ?>>--<?php _e('Default', 'pn'); ?>--</option>
						<option value="1" <?php selected($m_out_verify, 1); ?>><?php _e('No', 'pn'); ?></option>
						<option value="2" <?php selected($m_out_verify, 2); ?>><?php _e('Yes', 'pn'); ?></option>
					</select>
				</div>			
			</div>		
		</div>	
		<div class="add_tabs_line">
			<div class="add_tabs_label"></div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Daily automatic payout limit', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php 
					$m_out_max = is_sum(is_isset($paymerch_data, 'm_out_max')); 
					?>			
					<input type="text" name="m_out_max" style="width: 100%;" value="<?php echo $m_out_max; ?>" />
				</div>			
			</div>	
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Monthly automatic payout limit', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php 
					$m_out_max_month = is_sum(is_isset($paymerch_data, 'm_out_max_month'));  
					?>			
					<input type="text" name="m_out_max_month" style="width: 100%;" value="<?php echo $m_out_max_month; ?>" />
				</div>			
			</div>		
		</div>
		<div class="add_tabs_line">
			<div class="add_tabs_label"></div>	
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Min. amount of automatic payouts due to order', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php 
					$m_out_min_sum = is_sum(is_isset($paymerch_data, 'm_out_min_sum'));  
					?>			
					<input type="text" name="m_out_min_sum" style="width: 100%;" value="<?php echo $m_out_min_sum; ?>" />
				</div>			
			</div>	
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Max. amount of automatic payouts due to order', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php 
					$m_out_max_sum = is_sum(is_isset($paymerch_data, 'm_out_max_sum'));  
					?>			
					<input type="text" name="m_out_max_sum" style="width: 100%;" value="<?php echo $m_out_max_sum; ?>" />
				</div>			
			</div>		
		</div>		
		<div class="add_tabs_line">
			<div class="add_tabs_label"></div>
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Automatic payout delay (in hours)', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php 
					$m_out_timeout = is_sum(is_isset($paymerch_data, 'm_out_timeout')); 
					?>			
					<input type="text" name="m_out_timeout" style="width: 100%;" value="<?php echo $m_out_timeout; ?>" />
				</div>			
			</div>	
			<div class="add_tabs_single">
				<div class="add_tabs_sublabel"><span><?php _e('Whom the delay is for', 'pn'); ?></span></div>
				<div class="premium_wrap_standart">
					<?php 
					$m_out_timeout_user = intval(is_isset($paymerch_data, 'm_out_timeout_user')); 
					?>
					<select name="m_out_timeout_user" autocomplete="off"> 
						<option value="0" <?php selected($m_out_timeout_user, 0); ?>><?php _e('everyone', 'pn'); ?></option>
						<option value="1" <?php selected($m_out_timeout_user, 1); ?>><?php _e('newcomers', 'pn'); ?></option>
						<option value="2" <?php selected($m_out_timeout_user, 2); ?>><?php _e('not registered users', 'pn'); ?></option>
						<option value="3" <?php selected($m_out_timeout_user, 3); ?>><?php _e('not verified users', 'pn'); ?></option>
					</select>
				</div>			
			</div>		
		</div>
		<?php
	} 
	
}

add_filter('pn_direction_addform_post', 'merchant_direction_addform_post');
function merchant_direction_addform_post($array) {
		
	if (current_user_can('administrator') or current_user_can('pn_directions_merchant')) {
		$m_arrs = is_param_post('m_in');
		if (!is_array($m_arrs)) { $m_arrs = array(); }
		$m_in = array();
			
		foreach ($m_arrs as $m_arr) {
			$m_arr = is_extension_name($m_arr);
			if ($m_arr) {
				$m_in[] = $m_arr;
			}
		}
			
		$array['m_in'] = @serialize($m_in);
			
		$m_arrs = is_param_post('m_out');
		if (!is_array($m_arrs)) { $m_arrs = array(); }
		$m_out = array();
			
		foreach($m_arrs as $m_arr){
			$m_arr = is_extension_name($m_arr);
			if ($m_arr) {
				$m_out[] = $m_arr;
			}
		}
				
		$array['m_out'] = @serialize($m_out);
	}		
		
	return $array;
}

add_action('item_direction_edit', 'merchant_item_direction', 10, 2);
add_action('item_direction_add', 'merchant_item_direction', 10, 2);
function merchant_item_direction($data_id, $array) {
	
	if ($data_id) {
		if (current_user_can('administrator') or current_user_can('pn_directions_merchant')) {
			
			$old_paymerch_data = get_direction_meta($data_id, 'paymerch_data');
			
			$paymerch_data = array();
			$paymerch_data['m_in_max'] = is_sum(is_param_post('m_in_max'));
			$paymerch_data['m_in_max_month'] = is_sum(is_param_post('m_in_max_month'));
			$paymerch_data['m_in_min_sum'] = is_sum(is_param_post('m_in_min_sum'));
			$paymerch_data['m_in_max_sum'] = is_sum(is_param_post('m_in_max_sum'));
			$paymerch_data['m_in_maxc_day'] = intval(is_param_post('m_in_maxc_day'));
			$paymerch_data['m_in_maxc_month'] = intval(is_param_post('m_in_maxc_month'));
			$paymerch_data['m_out_realpay'] = intval(is_param_post('m_out_realpay'));
			$paymerch_data['m_out_verify'] = intval(is_param_post('m_out_verify')); 
			$paymerch_data['m_out_max'] = is_sum(is_param_post('m_out_max'));
			$paymerch_data['m_out_max_month'] = is_sum(is_param_post('m_out_max_month'));
			$paymerch_data['m_out_min_sum'] = is_sum(is_param_post('m_out_min_sum'));
			$paymerch_data['m_out_max_sum'] = is_sum(is_param_post('m_out_max_sum'));
			$paymerch_data['m_out_timeout'] = is_sum(is_param_post('m_out_timeout'));
			$paymerch_data['m_out_timeout_user'] = intval(is_param_post('m_out_timeout_user'));
			update_direction_meta($data_id, 'paymerch_data', $paymerch_data);
			
			do_action('item_direction_save', $data_id, $old_paymerch_data, 1, $paymerch_data);
			
		}		
	}
	
}

add_filter('pntable_columns_pn_directions', 'merchant_pntable_columns_pn_directions');
function merchant_pntable_columns_pn_directions($columns) {
	
	if (current_user_can('administrator') or current_user_can('pn_directions_merchant')) {
		$n_columns = array();
		$n_columns['merchant'] = __('Merchant', 'pn');
		$n_columns['paymerchant'] = __('Automatic payouts', 'pn');
		$columns = pn_array_insert($columns, 'status', $n_columns, 'before');
	}
	
	return $columns;
}

add_filter('pntable_column_pn_directions', 'merchant_pntable_column_pn_directions', 10, 3); 
function merchant_pntable_column_pn_directions($empty, $column_name, $item) {
				
	if ('merchant' == $column_name) {	

		$lists = list_extandeds_data('merchants');
		$m_arr = @unserialize(is_isset($item, 'm_in'));
		$m_arr = (array)$m_arr;
				
		$lists = list_checks_top($lists, $m_arr);
				
		if (count($lists) > 0) {
			$html = '<div style="width: 100%; background: #fff; padding: 5px; max-height: 120px; overflow-y: scroll;" class="merch_div" data-m="merchants" data-id="' . $item->id . '">';
			foreach ($lists as $m_key => $m_data) {
				$checked = '';
				if (in_array($m_key, $m_arr)) { $checked = 'checked="checked"'; }
							
				$link_title = $m_data['title'];
				if (current_user_can('administrator') or current_user_can('pn_merchants')) {
					$link_title = '<a href="' . admin_url('admin.php?page=pn_merchants_add&item_id=' . $m_data['id']) . '" target="_blank">' . $m_data['title'] . '</a>';
				}
				$html .='<div><label><input type="checkbox" class="merch_once" name="" ' . $checked . ' autocomplete="off" value="' . $m_key . '" /> ' . $link_title . '</label></div>';
			}			
			$html .='</div>';
		} else {
			$html = __('No merchants available', 'pn');
		}
				
		return $html;

	} elseif ('paymerchant' == $column_name) {	

		$lists = list_extandeds_data('paymerchants');
		$m_arr = @unserialize(is_isset($item, 'm_out')); 
		$m_arr = (array)$m_arr;
				
		$lists = list_checks_top($lists, $m_arr);
				
		if (count($lists) > 0) {
			$html = '<div style="width: 100%; background: #fff; padding: 5px; max-height: 120px; overflow-y: scroll;" class="merch_div" data-m="paymerchants" data-id="' . $item->id . '">';
			foreach ($lists as $m_key => $m_data) {
				$checked = '';
				if (in_array($m_key, $m_arr)) { $checked = 'checked="checked"'; }
							
				$link_title = $m_data['title'];
				if (current_user_can('administrator') or current_user_can('pn_merchants')) {
					$link_title = '<a href="' . admin_url('admin.php?page=pn_paymerchants_add&item_id=' . $m_data['id']) . '" target="_blank">' . $m_data['title'] . '</a>';
				}							
				$html .='<div><label><input type="checkbox" class="merch_once" name="" ' . $checked . ' autocomplete="off" value="' . $m_key . '" /> ' . $link_title . '</label></div>';
			}			
			$html .='</div>';
		} else {
			$html = __('No payouts available', 'pn');
		}	
				
		return $html;
	
	}
				
	return $empty;	
}

add_action('pn_adminpage_content_pn_directions', 'merchant_adminpage_content_pn_directions');
function merchant_adminpage_content_pn_directions() {
?>
<script type="text/javascript">
jQuery(function($) {
	
	$(document).on('change', '.merch_once', function() {
		
		var parent_div = $(this).parents('.merch_div');	
		parent_div.find('input, select').prop('disabled', true);
		var m = parent_div.attr('data-m');
		var id = parent_div.attr('data-id');
		
		var arrs = [];
		var k = -1;
		parent_div.find('input:checked, select').each(function() { k++;
			arrs[k] = $(this).val();
		});
		
		$('#premium_ajax').show();
		var param = 'id=' + id + '&m=' + m + '&arrs=' + arrs;
		
		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('merchant_direction_save', 'post'); ?>",
			data: param,
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{
				$('#premium_ajax').hide();	
				parent_div.find('input, select').prop('disabled', false);
			}
		});
		
		return false;
	});
	
});
</script>		
<?php		
}		

add_action('premium_action_merchant_direction_save', 'pn_premium_action_merchant_direction_save');
function pn_premium_action_merchant_direction_save() {
	global $wpdb;
	
	_method('post');
	
	if (current_user_can('administrator') or current_user_can('pn_directions_merchant')) {
		$type = trim(is_param_post('m'));
		if ('paymerchants' != $type) { $type = 'merchants'; }
		$data_id = intval(is_param_post('id'));
		if ($data_id > 0) {
			$arrs = explode(',', is_param_post('arrs'));
			$n_arrs = array();
			foreach ($arrs as $arr) {
				$arr = is_extension_name($arr);
				if ($arr) {
					$n_arrs[] = $arr;
				}
			}
			$array = array();
			if ('merchants' == $type) {
				$array['m_in'] = @serialize($n_arrs);
			} else {
				$array['m_out'] = @serialize($n_arrs);
			}
			$wpdb->update($wpdb->prefix . 'directions', $array, array('id' => $data_id));
		}	
	} 
	
}