<?php
if (!defined('ABSPATH')) { exit(); }

add_action('widgets_init', 'checkstatus_register_widgets');
function checkstatus_register_widgets() {
	
	class pn_checkstatus_Widget extends WP_Widget { 
		
		public function __construct($id_base = false, $widget_options = array(), $control_options = array()) {
			parent::__construct('get_pn_checkstatus', __('Check order status', 'pn'), $widget_options = array(), $control_options = array());
		}
		
		public function widget($args, $instance) {
			extract($args);

			global $wpdb;	
			
			if (is_ml()) {
				$lang = get_locale();
				$title = pn_strip_input(is_isset($instance, 'title' . $lang));
			} else {
				$title = pn_strip_input(is_isset($instance, 'title'));
			}
			if (!$title) { $title = __('Check order status', 'pn'); }
			
			$array = get_form_replace_array('checkstatusform', 'checkstatus', 'widget');
			$array['[title]'] = $title;
	
			$temp_form = '
			<div class="checkstatus_widget">
				<div class="checkstatus_widget_ins">
				[form]
				
					<div class="checkstatus_widget_title">
						<div class="checkstatus_widget_title_ins">
							[title]
						</div>
					</div>
					
					[result]
			
					<div class="checkstatus_widget_body">
						<div class="checkstatus_widget_body_ins">
						
							[html]
							
							<div class="widget_checkstatus_line_subm">
								[submit]
							</div>							
	 
						</div>
					</div>			

				[/form]
				</div>
			</div>
			';
	
			$temp_form = apply_filters('widget_checkstatus_form_temp', $temp_form);
			echo replace_tags($array, $temp_form);
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
		<?php
		} 
	}

	register_widget('pn_checkstatus_Widget');
}