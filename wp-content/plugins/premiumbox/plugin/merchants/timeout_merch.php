<?php
if (!defined('ABSPATH')) { exit(); }

add_action('pn_adminpage_content_pn_merchants', 'set_merchwait_substrate', 9);
add_action('pn_adminpage_content_all_cron', 'set_merchwait_substrate', 9);
add_action('pn_adminpage_content_pn_paymerchants', 'set_merchwait_substrate', 9);
function set_merchwait_substrate() {
?>
	<div class="premium_substrate">
		<?php _e('Cron URL for bids with status merchant wait', 'pn'); ?><br /> 
		<a href="<?php echo get_cron_link('set_merchwait'); ?>" target="_blank"><?php echo get_cron_link('set_merchwait'); ?></a>
	</div>	
<?php
} 

function set_merchwait() {
	global $wpdb, $premiumbox;

	if (!$premiumbox->is_up_mode()) {

		$touapcount = intval($premiumbox->get_option('exchange', 'touapcount'));
		if ($touapcount < 1 or $touapcount > 50) {
			$touapcount = 5;
		}
		
		$bids = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'merchwait' ORDER BY edit_date ASC LIMIT $touapcount");
		foreach ($bids as $item) {
			
			$direction_id = intval(is_isset($item, 'direction_id'));
			$direction = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' AND id = '$direction_id'");
			
			$bid = set_merchant($item, $direction);

			$stop = 0;
			if (isset($bid->stop)) {
				$stop = $bid->stop;
				unset($bid->stop);
			}
			
			if (!$stop) {
				
				$array = array();
				$array['edit_date'] = current_time('mysql');
				$array['status'] = 'new';
				$wpdb->update($wpdb->prefix . 'exchange_bids', $array, array('id' => $bid->id));
				
				$item = pn_object_replace($bid, $array);
								
				$ch_data = array(
					'bid' => $item,
					'bid_status' => array('merchwait'),
					'set_status' => 'new',
					'place' => 'cron',
					'who' => 'system',
					'old_status' => 'merchwait',
					'direction' => $direction
				);
				_change_bid_status($ch_data);	 		
				
			}
			
		}
	}
} 

add_filter('list_cron_func', 'merchwait_list_cron_func');
function merchwait_list_cron_func($filters) {
	
	$filters['set_merchwait'] = array(
		'title' => __('Cron URL for bids with status merchant wait', 'pn'),
		'file' => '2min',
	);
	
	return $filters;
} 