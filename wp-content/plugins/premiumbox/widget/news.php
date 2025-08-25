<?php
if (!defined('ABSPATH')) { exit(); }

if (!class_exists('pn_news_Widget')) { 
	class pn_news_Widget extends WP_Widget {
		
		public function __construct($id_base = false, $widget_options = array(), $control_options = array()) {
			parent::__construct('get_pn_news', __('News', 'pn'), $widget_options = array(), $control_options = array());
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
			
			if (!$title) { $title = __('News', 'pn'); }
			
			$count = intval(is_isset($instance, 'count'));
			if ($count < 1) { $count = 5; }
			
			$sorter = pn_strip_input(is_isset($instance, 'sorter'));
			$desc = pn_strip_input(is_isset($instance, 'desc'));
			$cat = intval(is_isset($instance, 'tcat'));
			$args = array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'orderby' => $sorter,
				'order' => $desc,
			);	
			if ($cat) {
				$args['cat'] = $cat;
			}
			$args['posts_per_page'] = '-1';
			$data_posts = get_posts($args);
			$countpost = count($data_posts);
			$data_posts = array_slice($data_posts, 0, $count);
			
			$hidepost = $countpost - $count; if ($hidepost < 1) { $hidepost = 0; }
			
			$news_url = get_blog_url();	
			
			$news = '';
			$r = 0;
			
			$date_format = get_option('date_format');
			
			foreach ($data_posts as $item) { $r++;
			
				$fclass = '';
				if (1 == $r) { $fclass = 'first'; }
						
				$lclass = '';
				if ($r == $count) { $lclass = 'last'; }
						
				if (0 == $r%2) {
					$oddeven = 'even';
				} else {
					$oddeven = 'odd';
				}	
				
				$temps = apply_filters('news_widget_one', '', $item, $count, $r, $date_format, $lclass, $oddeven);
				if (!trim($temps)) {
					$temps = '
						<div class="widget_news_line ' . $fclass . ' ' . $lclass . ' ' . $oddeven . '">
							<div class="widget_news_date">' . get_the_time($date_format, $item->ID) . '</div>
								<div class="clear"></div>
							<div class="widget_news_content"><a href="' . get_permalink($item->ID) . '">' . pn_strip_input(ctv_ml($item->post_title)) . '</a></div>
						</div>		
					';
				}
				
				$news .= $temps;
			} 		
			
			$array = array(
				'[countpost]' => $countpost,
				'[hidepost]' => $hidepost,
				'[count]' => $count,
				'[title]' => $title,
				'[url]' => $news_url,
				'[news]' => $news,
			);		
			
			$widget = '
			<div class="widget widget_news_div">
				<div class="widget_ins">
					<div class="widget_title">
						<div class="widget_titlevn">
							[title]
						</div>
					</div>
						
					<div class="widget_items">	
						[news]
					</div>
						
					<div class="widget_news_more_wrap">
						<a href="[url]" class="widget_news_more"><span>' . __('All news', 'pn') . '</span></a>
							<div class="clear"></div>
					</div>
				</div>
			</div>
			';		
			
			$widget = apply_filters('news_widget_block', $widget);
			$widget = replace_tags($array, $widget);
			echo $widget;
		}

		public function form($instance) { 
		
			$categories = get_categories('hide_empty=0');
		?>
			<?php if (is_ml()) { 
				$langs = get_langs_ml();
				foreach ($langs as $key) {
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title_' . $key); ?>"><strong><?php _e('Widget title', 'pn'); ?> (<?php echo get_title_forkey($key); ?>): </strong></label><br />
				<input type="text" name="<?php echo $this->get_field_name('title' . $key); ?>" id="<?php $this->get_field_id('title' . $key); ?>" class="widefat" value="<?php echo is_isset($instance, 'title' . $key); ?>">
			</p>		
				<?php } ?>
			
			<?php } else { ?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><strong><?php _e('Widget title', 'pn'); ?>: </strong></label><br />
				<input type="text" name="<?php echo $this->get_field_name('title'); ?>" id="<?php $this->get_field_id('title'); ?>" class="widefat" value="<?php echo is_isset($instance, 'title'); ?>">
			</p>
			<?php } ?>
			
			<p>
				<label for="<?php echo $this->get_field_id('count'); ?>"><strong><?php _e('Count news', 'pn'); ?>: </strong></label><br />
				<input type="text" name="<?php echo $this->get_field_name('count'); ?>" id="<?php $this->get_field_id('count'); ?>" class="widefat" value="<?php echo is_isset($instance, 'count'); ?>">
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('tcat'); ?>"><strong><?php _e('Category news', 'pn'); ?>: </strong></label><br />
				<select name="<?php echo $this->get_field_name('tcat'); ?>" style="width: 100%" autocomplete="off" id="<?php $this->get_field_id('tcat'); ?>">
					<option value="0">--<?php _e('All', 'pn'); ?>--</option>
					<?php if (is_array($categories)) { 
						foreach ($categories as $cats) {
					?>
						<option value="<?php echo $cats->cat_ID;?>" <?php selected(is_isset($instance, 'tcat'), $cats->cat_ID); ?>><?php echo ctv_ml($cats->name);?></option>
					<?php }} ?>
				</select>
			</p>		
			
			<p>
				<label for="<?php echo $this->get_field_id('sorter'); ?>"><strong><?php _e('Sort by', 'pn'); ?>: </strong></label><br />
				<select name="<?php echo $this->get_field_name('sorter'); ?>" style="width: 100%" autocomplete="off" id="<?php $this->get_field_id('sorter'); ?>">
					<option value="date"><?php _e('Date', 'pn'); ?></option>
					<option value="title" <?php selected(is_isset($instance, 'title'), 'title'); ?> ><?php _e('Title', 'pn'); ?></option>
					<option value="rand" <?php selected(is_isset($instance, 'rand'), 'rand'); ?> ><?php _e('Random', 'pn'); ?></option>
				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('desc'); ?>"><strong><?php _e('Show by', 'pn'); ?>: </strong></label><br />
				<select name="<?php echo $this->get_field_name('desc'); ?>" style="width: 100%" autocomplete="off" id="<?php $this->get_field_id('desc'); ?>">
					<option value="desc"><?php _e('Decrease', 'pn'); ?></option>
					<option value="asc" <?php selected(is_isset($instance, 'desc'), 'asc'); ?>><?php _e('Increase', 'pn'); ?></option>
				</select>
			</p>		
		<?php
			do_action('news_widget_options', $instance, $this);
		
		} 
	}

	register_widget('pn_news_Widget');
}