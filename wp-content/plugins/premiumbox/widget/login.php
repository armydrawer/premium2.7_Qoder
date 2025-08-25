<?php
if (!defined('ABSPATH')) { exit(); }

if (!class_exists('pn_login_Widget')) { 
	class pn_login_Widget extends WP_Widget {
		
		public function __construct($id_base = false, $widget_options = array(), $control_options = array()) {
			parent::__construct('get_pn_login', __('Authorization', 'pn'), $widget_options = array(), $control_options = array());
		}
		
		public function widget($args, $instance) {
			extract($args);

			$ui = wp_get_current_user();
			$user_id = intval($ui->ID);
			
			if (!$user_id) {
			
				if (is_ml()) {
					$lang = get_locale();
					$title_widget = pn_strip_input(is_isset($instance, 'title' . $lang));
				} else {
					$title_widget = pn_strip_input(is_isset($instance, 'title'));
				}
				if (strlen($title_widget) < 1) { $title_widget = __('Authorization', 'pn'); }
			
				$array = get_form_replace_array('loginform', 'log', 'widget');
				$array['[title]'] = $title_widget;	
		
				$temp_form = '
				<div class="login_widget">
					<div class="login_widget_ins">
					[form]
					
						<div class="login_widget_title">
							<div class="login_widget_title_ins">
								[title]
							</div>
						</div>

						[result]

						<div class="login_widget_body">
							<div class="login_widget_body_ins">
							
								[html]
								
								<div class="widget_log_line_text">
									<div class="login_widget_subm_left">
										<a href="[registerlink]">'. __('Sign up', 'pn') .'</a>
									</div>
									<div class="login_widget_subm_right">
										<a href="[lostpasslink]">'. __('Forgot password?', 'pn') .'</a>
									</div>
									
									<div class="clear"></div>
								</div>	

								<div class="widget_log_line_subm">
									[submit]
								</div>							
		 
							</div>
						</div>			

					[/form]
					</div>
				</div>
				';
		
				$temp_form = apply_filters('widget_login_form_temp', $temp_form);
				echo '<div class="not_frame">';
				echo replace_tags($array, $temp_form);	
				echo '</div>';
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
		<?php
			do_action('login_widget_options', $instance, $this);
		
		}  
	}
	register_widget('pn_login_Widget');
}