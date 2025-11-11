<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('widgets_init', 'themethqu_register_widgets');
function themethqu_register_widgets() {

	class pn_thqu_Widget extends WP_Widget {

		public function __construct($id_base = false, $widget_options = array(), $control_options = array()){
			parent::__construct('get_pn_thqu', __('General issues','pntheme'), $widget_options = array(), $control_options = array());
		}

		public function widget($args, $instance){
			extract($args);


			$lang = get_locale();
			$title_widget = pn_strip_input(is_isset($instance,'title'.$lang));
			if (!$title_widget) { $title_widget = __('General issues','pntheme'); }
			$link_widget = pn_strip_input(is_isset($instance,'url'.$lang));

			$widget = '
			<div class="widget">
				<div class="widget_ins">
					<div class="widget_title">
						<div class="widget_titlevn widget_align_title">'. $title_widget .'</div>
					</div>

					<a href="'. $link_widget .'" class="widget__btn">'. __('Answers','pntheme') .'</a>

					<div class="clear"></div>
				</div>
			</div>';

			echo $widget;

		}

		public function form($instance){

		?>
			<?php if(is_ml()){
				$langs = get_langs_ml();
				foreach($langs as $key){
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title_'.$key); ?>"><strong><?php _e('Title','pntheme'); ?> (<?php echo get_title_forkey($key); ?>): </strong></label><br />
				<input type="text" name="<?php echo $this->get_field_name('title'.$key); ?>" id="<?php $this->get_field_id('title'.$key); ?>" class="widefat" value="<?php echo is_isset($instance,'title'.$key); ?>">
			</p>
				<?php } ?>
			<?php } ?>

			<?php if(is_ml()){
				$langs = get_langs_ml();
				foreach($langs as $key){
			?>
			<p>
				<label for="<?php echo $this->get_field_id('url_'.$key); ?>"><strong><?php _e('Link','pntheme'); ?> (<?php echo get_title_forkey($key); ?>): </strong></label><br />
				<input type="text" name="<?php echo $this->get_field_name('url'.$key); ?>" id="<?php $this->get_field_id('url'.$key); ?>" class="widefat" value="<?php echo is_isset($instance,'url'.$key); ?>">
			</p>
				<?php } ?>
			<?php } ?>

		<?php
		}
	}

	register_widget('pn_thqu_Widget');
}
