<?php
if (!defined('ABSPATH')) { exit(); }

add_action('pn_adminpage_content_pn_amlcheck', 'amlcheck_substrate', 9);
add_action('pn_adminpage_content_all_cron', 'amlcheck_substrate', 9);
function amlcheck_substrate() {
?>
	<div class="premium_substrate">
		<?php _e('Cron URL for aml wait bids', 'pn'); ?><br /> 
		<a href="<?php echo get_cron_link('amlcheck_cron'); ?>" target="_blank"><?php echo get_cron_link('amlcheck_cron'); ?></a>
	</div>	
<?php
} 
 
function amlcheck_cron() {
	global $wpdb, $premiumbox;

	if (!$premiumbox->is_up_mode()) {

		$touapcount = intval($premiumbox->get_option('exchange', 'touapcount'));
		if ($touapcount < 1 or $touapcount > 50) {
			$touapcount = 5;
		}
		
		$bids = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'amlwait' ORDER BY edit_date ASC LIMIT $touapcount"); 
		foreach ($bids as $item) {
			
			$direction_id = intval(is_isset($item, 'direction_id'));
			$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' AND id = '$direction_id'");
			
			$checkbid = amlcheck_data($item, $direction, 'wait', 0, '');
			
			$aml_give = pn_json_decode(is_isset($checkbid, 'aml_give'));
			$aml_get = pn_json_decode(is_isset($checkbid, 'aml_get'));
			$aml_merch = pn_json_decode(is_isset($checkbid, 'aml_merch'));
			
			$goed = 1;
			$next_action = '';
			
			if (isset($aml_give['status'])) {
				if (2 == $aml_give['status']) {
					$goed = 0;
				}
			}
			if (isset($aml_get['status'])) {
				if (2 == $aml_get['status']) {
					$goed = 0;
				}
			}		
			if (isset($aml_merch['status'])) {
				if (2 == $aml_merch['status']) {
					$goed = 0;
				}
			}
			
			if (isset($aml_give['next_action']) and $aml_give['next_action']) {
				$next_action = trim($aml_give['next_action']);
			}
			if (isset($aml_get['next_action']) and $aml_get['next_action']) {
				$next_action = trim($aml_get['next_action']);
			}
			if (isset($aml_merch['next_action']) and $aml_merch['next_action']) {
				$next_action = trim($aml_merch['next_action']);
			}	

			if ($goed and $next_action) {
				
				$first_next_action = mb_substr($next_action, 0, 4);
				if ('set_' == $first_next_action) {
					
					$new_status = str_replace('set_', '', $next_action);
					$array = array();
					$array['edit_date'] = current_time('mysql');
					$array['status'] = $new_status;
					$wpdb->update($wpdb->prefix . 'exchange_bids', $array, array('id' => $item->id));
					
				} elseif (in_array($next_action, array('realpay', 'verify'))) {
					
					$array = array();
					$array['edit_date'] = $checkbid['edit_date'] = current_time('mysql');
					$array['status'] = $checkbid['status'] = $next_action;
					$wpdb->update($wpdb->prefix . 'exchange_bids', $array, array('id' => $item->id));
									
					$ch_data = array(
						'bid' => (object)$checkbid,
						'set_status' => $next_action,
						'place' => 'amlcheck_cron',
						'who' => 'system',
						'old_status' => 'amlwait',
						'direction' => $direction
					);
					_change_bid_status($ch_data);					
					
				} elseif ('payout' == $next_action) {	
					
					$m_id = trim($item->m_out);
					if ($m_id) {
						$direction_data = get_direction_meta($direction_id, 'paymerch_data');
						$paymerch_data = get_paymerch_data($m_id);
						do_action('paymerchant_action_bid', $m_id, $item, 'site', $direction_data, 'amlcheck_cron', $direction, $paymerch_data);				
					}					
					
				}
				
			}	
		}
	}
} 

add_filter('list_cron_func', 'amlcheck_list_cron_func');
function amlcheck_list_cron_func($filters) {
	
	$filters['amlcheck_cron'] = array(
		'title' => __('Cron URL for aml wait bids', 'pn'),
		'file' => '10min',
	);
	
	return $filters;
}  