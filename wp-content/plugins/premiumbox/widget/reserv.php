<?php
if (!defined('ABSPATH')) { exit(); }

class pn_reserv_Widget extends WP_Widget { 
	
 	public function __construct($id_base = false, $widget_options = array(), $control_options = array()) {
		parent::__construct('get_pn_reserv', __('Currency reserve', 'pn'), $widget_options = array(), $control_options = array());
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
		
		if (!$title) { $title = __('Currency reserve', 'pn'); }
		
		$currency_codes_show = intval(is_isset($instance, 'currency_codes_show'));
		
		if (1 == get_settings_second_logo()) {
			$logo_num = intval(is_isset($instance, 'logo_num'));
		} else {
			$logo_num = 0;
		}
		$logo_num = $logo_num + 1;
		
		$output = is_isset($instance,'output');
		if (!is_array($output)) { $output = array(); }
		
		$datas = list_view_currencies($output);
		
		$reserv = '';
		$r = 0;
		
		$currency_codes = array();
		foreach ($datas as $item) { $r++;	
			
			$vt = str_replace('.', '_', $item['currency_code']);
			$cl = array('widget_reserv_vt', 'widget_reserv_vt_0', 'widget_reserv_vt_' . $vt);
			$currency_codes[$vt] = $vt;		
					
			if (0 == $r%2) {
				$oddeven = 'even';
			} else {
				$oddeven = 'odd';
			}			
			
			$logo = $item['logo'];
			if (2 == $logo_num) {
				$logo = $item['logo2'];
			}
			
			$temp = apply_filters('reserv_widget_one', '', $item, $r);
			if (!trim($temp)) {
				$temp = '
				<div class="widget_reserv_line ' . $oddeven . '">  
					<div class="widget_reserv_ico currency_logo" style="background-image: url(' . $logo . ');" data-logo="' . $item['logo'] . '" data-logo-next="' . $item['logo2'] . '"></div>
					<div class="widget_reserv_block">
						<div class="widget_reserv_title">
							'. $item['title'] .'
						</div>
						<div class="widget_reserv_sum">
							'. is_out_sum($item['reserv'], $item['decimal'], 'reserv') .'
						</div>
					</div>
						<div class="clear"></div>
				</div>		
				';
			}
			
			$reserv .= '<div class="' . implode(' ', $cl) . '">';
			$reserv .= $temp;
			$reserv .= '</div>';
		} 		
		
		$currency_code_filter = '';
		if ($currency_codes_show) {
			$currency_code_filter .= '<div class="widget_reserv_filters">';
			$currency_code_filter .= '<div class="widget_reserv_filter current" data-id="0"><span>' . __('All', 'pn') . '</span></div>';
			foreach ($currency_codes as $vt) {
				$currency_code_filter .= '<div class="widget_reserv_filter" data-id="' . $vt . '"><span>' . $vt . '</span></div>';
			}
			$currency_code_filter .= '
				<div class="clear"></div>
			</div>';
		}
		
		$array = array(
			'[table]' => $reserv,
			'[title]' => $title,
			'[filter]' => $currency_code_filter,
		);		
		$array = apply_filters('replace_array_reserv', $array);
		
		$widget = '
			<div class="widget_reserv_div">
				<div class="widget_reserv_div_ins">
					<div class="widget_reserv_div_title">
						<div class="widget_reserv_div_title_ins">
							[title]
						</div>
					</div>
					
					[filter]
					
					[table]
				</div>
			</div>
		';		
		$widget = apply_filters('reserv_widget_block', $widget);
		$widget = replace_tags($array, $widget);
		echo $widget;
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
			<label for="<?php echo $this->get_field_id('currency_codes_show'); ?>"><strong><?php _e('Show currency code filter', 'pn'); ?>: </strong></label><br />
			<select name="<?php echo $this->get_field_name('currency_codes_show'); ?>" style="width: 100%" autocomplete="off" id="<?php $this->get_field_id('currency_codes_show'); ?>">
				<option value="0" <?php selected(is_isset($instance,'currency_codes_show'), 0); ?>><?php _e('No', 'pn'); ?></option>
				<option value="1" <?php selected(is_isset($instance,'currency_codes_show'), 1); ?>><?php _e('Yes', 'pn'); ?></option>
			</select>
		</p>
		
		<?php if (1 == get_settings_second_logo()) { ?>
		<p>
			<label for="<?php echo $this->get_field_id('logo_num'); ?>"><strong><?php _e('Logo version','pn'); ?>: </strong></label><br />
			<select name="<?php echo $this->get_field_name('logo_num'); ?>" style="width: 100%" autocomplete="off" id="<?php $this->get_field_id('logo_num'); ?>">
				<option value="0" <?php selected(is_isset($instance,'logo_num'), 0); ?>><?php _e('Main logo','pn'); ?></option>
				<option value="1" <?php selected(is_isset($instance,'logo_num'), 1); ?>><?php _e('Additional logo','pn'); ?></option>
			</select>
		</p>		
		<?php } ?>
		
		<div style="padding: 0 0 2px 0;"><label><strong><?php _e('Show currency reserve','pn'); ?>:</strong></label></div>
		<div style="border: 1px solid #ddd; border-radius: 3px; padding: 10px; margin: 0 0 10px 0;">
			<?php 
			$output = is_isset($instance,'output');
			if(!is_array($output)){ $output = array(); }
			
			$currencies = list_view_currencies();

			$scroll_lists = array();
			if (is_array($currencies)) {
				foreach ($currencies as $item) {
					$checked = 0;
					if (in_array($item['id'], $output)) {
						$checked = 1;
					}	
					$scroll_lists[] = array(
						'title' => $item['title'],
						'checked' => $checked,
						'value' => $item['id'],
					);
				}	
			}	
			echo get_check_list($scroll_lists, $this->get_field_name('output') . '[]', '', '200', 1);				
			?>
		</div>
	<?php
		do_action('reserv_widget_options', $instance, $this);
	} 
}

register_widget('pn_reserv_Widget');

if (!function_exists('premium_js_reservwidget')) {
	add_action('premium_js', 'premium_js_reservwidget');
	function premium_js_reservwidget() {
	?>
	jQuery(function($) {
		
		$('.widget_reserv_filter').on('click', function() {
			$('.widget_reserv_filter').removeClass('current');
			$(this).addClass('current');
			var id = $(this).attr('data-id');
			$('.widget_reserv_vt').hide();
			$('.widget_reserv_vt_' + id).show();
		
			return false;
		});	
		
	});
	<?php	
	}
} 