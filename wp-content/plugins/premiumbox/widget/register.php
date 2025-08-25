<?php
if (!defined('ABSPATH')) { exit(); }

if (!class_exists('pn_register_Widget')) { 
	class pn_register_Widget extends WP_Widget {
		
		public function __construct($id_base = false, $widget_options = array(), $control_options = array()) {
			parent::__construct('get_pn_register', __('Sign up', 'pn'), $widget_options = array(), $control_options = array());
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
				if (strlen($title_widget) < 1) { $title_widget = __('Sign up', 'pn'); }		
		
				$array = get_form_replace_array('registerform', 'reg', 'widget');
				$array['[title]'] = $title_widget;	
		
				$temp_form = '
				<div class="register_widget">
					<div class="register_widget_ins">
						[form]
							[return_link]
							
							<div class="register_widget_title">
								<div class="register_widget_title_ins">
									[title]
								</div>
							</div>
							
							[result]
							
							<div class="register_widget_body">
								<div class="register_widget_body_ins">
								
									[html]
									
									<div class="widget_reg_line">
										[agreement]
									</div>
									
									<div class="widget_reg_line_subm">
										[submit]
									</div>

								</div>
							</div>
						[/form]
					</div>	
				</div>
				';
		
				$temp_form = apply_filters('widget_register_form_temp', $temp_form);
				echo replace_tags($array, $temp_form);	
				
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
			do_action('register_widget_options', $instance, $this);

		}  
	}
	register_widget('pn_register_Widget');
}