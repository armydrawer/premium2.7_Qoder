<?php
if (!defined('ABSPATH')) { exit(); }

class pn_lastobmens_Widget extends WP_Widget { 
	
 	public function __construct($id_base = false, $widget_options = array(), $control_options = array()) {
		parent::__construct('get_pn_lastobmens', __('Recent exchanges', 'pn'), $widget_options = array(), $control_options = array());
	}
	
	public function widget($args, $instance) {
		extract($args);

		global $wpdb, $live_lchange, $count_pn_lastobmens;
		
		$live_lchange = intval($live_lchange);
		$count_pn_lastobmens++;
		
		if (1 == $count_pn_lastobmens) {
		
			if (is_ml()) {
				$lang = get_locale();
				$title = pn_strip_input(is_isset($instance, 'title' . $lang));
			} else {
				$title = pn_strip_input(is_isset($instance, 'title'));
			}
			if (!$title) { $title = __('Recent exchange', 'pn'); }
			
			$count = intval(is_isset($instance, 'count')); if ($count < 1) { $count = 1; }
			$live = intval(is_isset($instance, 'live'));
			
			if (1 == get_settings_second_logo()) {
				$logo_num = intval(is_isset($instance, 'logo_num'));
			} else {
				$logo_num = 0;
			}
			$logo_num = $logo_num + 1;
			
			$widget = '
			<input type="hidden" name="lc_count" class="lc_count" value="' . $count . '" />
			<input type="hidden" name="lc_logo" class="lc_logo" value="' . $logo_num . '" />
			<div class="widget widget_lchange_div">
				<div class="widget_ins">
					<div class="widget_title">
						<div class="widget_titlevn">
							'. $title .'
						</div>
					</div>';
					
					$cl = '';
					if ($live) { 
						$live_lchange++;
						$cl = 'globalajax_ind';
					}
					
					$widget .= '
					<div class="widget_lchange_html">
						<div class="widget_lchange_abs ' . $cl . '"></div>
						<div class="widget_lchange_ajax">
					';
					
					$bids = get_last_bids('success', $count);
					if (count($bids) > 0) {
						foreach ($bids as $bid) {
							$widget .= get_lchange_line($bid, 'widget');
						}
					} else {
						$widget .= '<div class="widget_lchange_noitem">' . __('No orders','pn') . '</div>';
					}
					
					$widget .= '
						</div>
					</div>';
							
			$widget .= '				
				</div>
			</div>		
			';					
				
			echo $widget;
		}
	}

	public function form($instance) { 
	?>
		<?php if (is_ml()) { 
			$langs = get_langs_ml();
			foreach ($langs as $key) {
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title_' . $key); ?>"><strong><?php _e('Title'); ?> (<?php echo get_title_forkey($key); ?>): </strong></label><br />
			<input type="text" name="<?php echo $this->get_field_name('title' . $key); ?>" id="<?php $this->get_field_id('title' . $key); ?>" class="widefat" value="<?php echo is_isset($instance, 'title' . $key); ?>">
		</p>		
			<?php } ?>
		
		<?php } else { ?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><strong><?php _e('Title'); ?>: </strong></label><br />
			<input type="text" name="<?php echo $this->get_field_name('title'); ?>" id="<?php $this->get_field_id('title'); ?>" class="widefat" value="<?php echo is_isset($instance, 'title'); ?>">
		</p>
		<?php } ?>
		<p>
			<label for="<?php echo $this->get_field_id('count'); ?>"><strong><?php _e('Count of exchanges', 'pn'); ?>: </strong></label><br />
			<input type="text" name="<?php echo $this->get_field_name('count'); ?>" id="<?php $this->get_field_id('count'); ?>" class="widefat" value="<?php echo is_isset($instance, 'count'); ?>">
		</p>
		<?php if (1 == get_settings_second_logo()) { ?>
		<p>
			<label for="<?php echo $this->get_field_id('logo_num'); ?>"><strong><?php _e('Logo version', 'pn'); ?>: </strong></label><br />
			<select name="<?php echo $this->get_field_name('logo_num'); ?>" style="width: 100%" autocomplete="off" id="<?php $this->get_field_id('logo_num'); ?>">
				<option value="0" <?php selected(is_isset($instance, 'logo_num'), 0); ?>><?php _e('Main logo', 'pn'); ?></option>
				<option value="1" <?php selected(is_isset($instance, 'logo_num'), 1); ?>><?php _e('Additional logo', 'pn'); ?></option>
			</select>
		</p>
		<?php } ?>
		<p>
			<label for="<?php echo $this->get_field_id('live'); ?>"><strong><?php _e('Online update', 'pn'); ?>: </strong></label><br />
			<select name="<?php echo $this->get_field_name('live'); ?>" style="width: 100%" autocomplete="off" id="<?php $this->get_field_id('live'); ?>">
				<option value="0"><?php _e('No', 'pn'); ?></option>
				<option value="1" <?php selected(is_isset($instance, 'live'), 1); ?> ><?php _e('Yes', 'pn'); ?></option>
			</select>
		</p>		
	<?php
	} 
}
register_widget('pn_lastobmens_Widget');

add_filter('globalajax_site_request', 'globalajax_site_request_lastbids');
function globalajax_site_request_lastbids($params) {
	global $live_lchange;
	
	if ($live_lchange > 0) {
		$params .= "+ '&lchange=1' + '&lc_count=' + $('.lc_count').val() + '&lc_logo=' + $('.lc_logo').val()";
	}
	
	return $params;
}

add_filter('globalajax_site_data', 'globalajax_site_data_lastbids');
function globalajax_site_data_lastbids($logs) {
	global $wpdb;

	$lchange = is_param_post('lchange');
	$lastbids = '';
	
	if (1 == $lchange) {
		$count = intval(is_param_post('lc_count')); if ($count < 1 or $count > 10) { $count = 1; }
		$bids = get_last_bids('success', $count);
		foreach ($bids as $bid) {
			$lastbids .= get_lchange_line($bid, 'widget');	
		}					
	}
	
	$logs['lb_res'] = 1;
	$logs['lb_html'] = $lastbids;
	
	return $logs;	
}	

add_action('globalajax_site_result', 'globalajax_site_result_lastbids');
function globalajax_site_result_lastbids() {
	global $live_lchange;
	
	if ($live_lchange > 0) {	
?>
	if (res['lb_res']) {
		$('.widget_lchange_ajax').html(res['lb_html']);
	}
<?php	
	}
}