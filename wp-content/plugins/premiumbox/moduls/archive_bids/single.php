<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_archive_bid', 'pn_adminpage_title_pn_archive_bid');
	function pn_adminpage_title_pn_archive_bid() {
		
		return __('Archived order', 'pn');
	}

	add_action('pn_adminpage_content_pn_archive_bid', 'def_adminpage_content_pn_archive_bid');
	function def_adminpage_content_pn_archive_bid() {
		global $wpdb;

		$form = new PremiumForm();

		$data_id = 0;
		$data = '';
		$id = intval(is_param_get('item_id'));
		
		if ($id) {
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "archive_exchange_bids WHERE id = '$id'");
			if (isset($data->id)) {
				$data_id = $data->id;
			}	
		}	
		
		$paged = intval(is_param_get('paged')); if ($paged < 1) { $paged = 1; }
		
		$back_menu = array();
		$back_menu['back'] = array(
			'link' => admin_url('admin.php?page=pn_archive_bids&paged=' . $paged),
			'title' => __('Back to list', 'pn')
		);
		$form->back_menu($back_menu, $data);		
		
		if ($data_id) {
			
			$archive_content = get_archive_info($data);
			?>
			<div class="premium_single">
				
				<div class="premium_single_line">
					<strong><?php _e('Archivation date', 'pn'); ?>:</strong> <?php echo get_pn_time($data->archive_date, 'd.m.Y H:i'); ?>
				</div>	
				
				<div class="premium_single_line">
					<strong><?php _e('Status', 'pn'); ?>:</strong> <?php echo get_bid_status($data->status); ?>
				</div>
				
				<?php 
				$title_arr = array(
					'bid_id' => __('Bid id', 'pn'),
					'create_date' => __('Creation date', 'pn'),
					'edit_date' => __('Modification date', 'pn'),
					'to_account' => __('Merchant account', 'pn'),
					'from_account' => __('Account used for automatic payout', 'pn'),
					'trans_in' => __('Merchant transaction ID', 'pn'),
					'trans_out' => __('Auto payout transaction ID', 'pn'),
					'txid_in' => __('Merchant txID', 'pn'),
					'txid_out' => __('Auto payout txID', 'pn'),
					'account_give' => __('From account', 'pn'),
					'account_get' => __('Into account', 'pn'),
					'last_name' => __('Last name', 'pn'),
					'first_name' => __('First name', 'pn'),
					'second_name' => __('Second name', 'pn'),
					'user_phone' => __('Mobile phone number', 'pn'),
					'user_skype' => __('Skype', 'pn'),
					'user_telegram' => __('Telegram', 'pn'),
					'user_email' => __('E-mail', 'pn'),
					'user_passport' => __('Passport number', 'pn'),
					'user_id' => __('User ID', 'pn'),
					'user_ip' => __('User IP', 'pn'),
					'profit' => __('Profit', 'pn'),
					'exsum' => __('Amount in internal currency', 'pn'),
					'summ1c' => __('Amount To send (add.fee and PS fee)', 'pn'),
					'summ1cr' => __('Amount Send for reserve', 'pn'),					
					'sum1c' => __('Amount To send (add.fee and PS fee)', 'pn'),
					'sum1r' => __('Amount Send for reserve', 'pn'),					
					'summ2c' => __('Amount To receive (add.fees and PS fees)', 'pn'),
					'summ2cr' => __('Amount Receive for reserve', 'pn'),					
					'sum2c' => __('Amount To receive (add.fees and PS fees)', 'pn'),
					'sum2r' => __('Amount Receive for reserve', 'pn'),					
					'vtype1' => __('Currency code for Send', 'pn'),
					'vtype2' => __('Currency code for Receive', 'pn'),
					'currency_code_give' => __('Currency code for Send', 'pn'),
					'currency_code_get' => __('Currency code for Receive', 'pn'),
					'pcalc' => __('Partner status', 'pn'),
					'ref_id' => __('Referral ID', 'pn'),
					'summp' => __('Partner earned', 'pn'),
					'partner_sum' => __('Partner earned', 'pn'),
					'pay_sum' => __('Real amount to pay', 'pn'),
					'pay_ac' => __('Real account', 'pn'),					
				);
				$en_key = array();
				foreach ($title_arr as $k => $v) {
					$en_key[] = $k;
				}
				if (is_array($archive_content)) {
					
					foreach ($archive_content as $key => $val) {
						if (in_array($key, $en_key)) {
							$title = is_isset($title_arr, $key);
						?>
						<div class="premium_single_line">
							<strong><?php echo $title; ?>:</strong> <?php echo $val; ?>
						</div>						
						<?php
						}
					}
					
					if (isset($archive_content['admin_comment'])) {
						?>
						<div class="premium_single_line">
							<strong><?php _e('admin comm.', 'pn'); ?>:</strong><br />
							<?php echo $archive_content['admin_comment']; ?>
						</div>						
						<?php						
					}
					
					if (isset($archive_content['user_comment'])) {
						?>
						<div class="premium_single_line">
							<strong><?php _e('user comm.', 'pn'); ?>:</strong><br />
							<?php echo $archive_content['user_comment']; ?>
						</div>						
						<?php						
					}					
				}
				?>
				
			</div> 
			<?php 	
		} else {
			_e('Error! Not found', 'pn');
		}		
	}
}