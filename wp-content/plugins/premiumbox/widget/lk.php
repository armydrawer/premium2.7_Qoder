<?php
if (!defined('ABSPATH')) { exit(); }

if (!class_exists('pn_lk_Widget')) { 
	class pn_lk_Widget extends WP_Widget { 
		
		public function __construct($id_base = false, $widget_options = array(), $control_options = array()) {
			parent::__construct('get_pn_lk', __('Personal account', 'pn'), $widget_options = array(), $control_options = array());
		}
		
		public function widget($args, $instance) {
			extract($args);

			global $wpdb;	
			
			$ui = wp_get_current_user();
			$user_id = intval($ui->ID);
			
			if ($user_id) {
			
				if (is_ml()) {
					$lang = get_locale();
					$title_widget = pn_strip_input(is_isset($instance, 'title' . $lang));
				} else {
					$title_widget = pn_strip_input(is_isset($instance, 'title'));
				}
				
				if (strlen($title_widget) < 1) { $title_widget = __('Personal account', 'pn'); }
			
				$list_page = pn_list_user_menu();
		
				$array = array(
					'[list_page]' => $list_page,
					'[exit]' => get_pn_action('logout', 'get'),
					'[title]' => $title_widget,
				);
				$array = apply_filters('widget_user_form_array', $array, $instance);
		
				$temp_form = '
				<div class="user_widget">
					<div class="user_widget_ins">
					
						<!-- before title -->
						<div class="user_widget_title">
							<div class="user_widget_title_ins">
								[title]
							</div>
						</div>
						<!-- after title -->
	
						<div class="user_widget_body">
							<div class="user_widget_body_ins">

								<!-- before list -->
								
								[list_page]
								
								<!-- after list -->
		
								<div class="user_widget_exit">
									<a href="[exit]" class="exit_link">' . __('Exit', 'pn') . '</a>
								</div>							
		 
							</div>
						</div>			
					</div>
				</div>
				';
		
				$temp_form = apply_filters('widget_user_form_temp', $temp_form, $instance);
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
			do_action('lk_widget_options', $instance, $this);

		}  
	}
	register_widget('pn_lk_Widget');
}