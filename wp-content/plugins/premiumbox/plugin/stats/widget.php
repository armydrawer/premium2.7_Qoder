<?php
if (!defined('ABSPATH')) { exit(); }

if (!class_exists('pn_stats_Widget')) { 
	class pn_stats_Widget extends WP_Widget {
		
		public function __construct($id_base = false, $widget_options = array(), $control_options = array()) {
			parent::__construct('get_pn_stats', __('Statistics', 'pn'), $widget_options = array(), $control_options = array());
		}
		
		public function widget($args, $instance) {
			
			extract($args);

			if (is_ml()) {
				$lang = get_locale();
				$title = pn_strip_input(is_isset($instance, 'title' . $lang));
			} else {
				$title = pn_strip_input(is_isset($instance, 'title'));	
			}
			if (!$title) { $title = __('Statistics', 'pn'); }
			
			$stats = is_isset($instance, 'stats'); 
			if (!is_array($stats)) { $stats = array(); }
			
			$widget = '
			<div class="widget widget_stats_div">
				<div class="widget_ins">
					<div class="widget_title">
						<div class="widget_titlevn">
							'. $title .'
						</div>
					</div>
					';	
					
					$date = current_time('Y-m-d 00:00:00');
					
					$array = array(
						'total_users' => __('Total users', 'pn'),
						'today_users' => __('Registered users today', 'pn'),
						'count_exchanges' => __('Number of exchanges today', 'pn'),
						'amount_exchanges' => __('Amount of exchanges today', 'pn'),
						'total_reserve' => __('Total amount of reserves', 'pn'),
					);
					$lists = apply_filters('lists_pn_stats_widget', $array);				
					foreach ($lists as $list_k => $list_v) {
						if (in_array($list_k, $stats)) {
							
							if ('total_users' == $list_k) {
								$widget .= '<div class="widget_stats_line"><span>' . $list_v . ':</span> ' . is_out_sum(get_user_for_site(), 0, 'all') . '</div>';
							} elseif ('today_users' == $list_k) {
								$widget .= '<div class="widget_stats_line"><span>' . $list_v . ':</span> ' . is_out_sum(get_user_for_site($date), 0, 'all') . '</div>';
							} elseif ('count_exchanges' == $list_k) {
								$widget .= '<div class="widget_stats_line"><span>' . $list_v . ':</span> ' . is_out_sum(get_count_exchanges($date, ''), 0, 'all') . '</div>';
							} elseif ('amount_exchanges' == $list_k) {
								$widget .= '<div class="widget_stats_line"><span>' . $list_v . ':</span> ' . is_out_sum(get_sum_exchanges($date, '', cur_type()), 2, 'all') . ' ' . cur_type() . '</div>';
							} elseif ('total_reserve' == $list_k) {	
								$widget .= '<div class="widget_stats_line"><span>' . $list_v . ':</span> ' . is_out_sum(get_general_reserve(cur_type()), 2, 'reserv') . ' ' . cur_type() . '</div>';							
							} else {
								$widget .= apply_filters('show_stats_widget', '', $list_k, $list_v);
							}
							
						}
					}	
					
					$widget .= '
				</div>
			</div>
			';		
			
			echo $widget;
		}

		public function form($instance) { 
		?>
			<?php 
			if (is_ml()) { 
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
			
			<div style="border: 1px solid #ddd; border-radius: 3px; padding: 10px; margin: 0 0 10px 0;">
				<?php 
				$stats = is_isset($instance,'stats'); 
				if (!is_array($stats)) { $stats = array(); }
				
				$array = array(
					'total_users' => __('Total users', 'pn'),
					'today_users' => __('Registered users today', 'pn'),
					'count_exchanges' => __('Number of exchanges today', 'pn'),
					'amount_exchanges' => __('Amount of exchanges today', 'pn'),
					'total_reserve' => __('Total amount of reserves', 'pn'),
				);
				$lists = apply_filters('lists_stats_widget', $array);

				$scroll_lists = array();
				if (is_array($lists)) {
					foreach ($lists as $list_k => $list_v) {
						$checked = 0;
						if (in_array($list_k, $stats)) {
							$checked = 1;
						}	
						$scroll_lists[] = array(
							'title' => $list_v,
							'checked' => $checked,
							'value' => $list_k,
						);
					}	
				}	
				echo get_check_list($scroll_lists, $this->get_field_name('stats') . '[]', '', '200', 1);				
				?>
			</div>		
		<?php
			do_action('stats_widget_options', $instance, $this);
		} 
	}

	add_action('widgets_init', 'pn_stats_widget_init');
	function pn_stats_widget_init() {
		
		register_widget('pn_stats_Widget');
		
	}

}