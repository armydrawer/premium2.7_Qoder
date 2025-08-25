<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_all_geoip', 'def_adminpage_title_all_geoip');
	function def_adminpage_title_all_geoip() {
		
		return __('Settings', 'pn');
	}

	add_action('pn_adminpage_content_all_geoip', 'def_adminpage_content_all_geoip');
	function def_adminpage_content_all_geoip() {
		
		$plugin = get_plugin_class();			
			
		$form = new PremiumForm();
			
		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);
		$options['enabled'] = array(
			'view' => 'user_func',
			'name' => 'enabled',
			'func_data' => array(),
			'func' => 'all_geoip_enabled_country',
			'work' => 'input_array',
		);
		$options['line1'] = array(
			'view' => 'line',
		);			
		$options['blocked'] = array(
			'view' => 'user_func',
			'name' => 'blocked',
			'func_data' => array(),
			'func' => 'all_geoip_country',
			'work' => 'input_array',
		);	
		$options['block_title'] = array(
			'view' => 'h3',
			'title' => __('Blocked page data', 'pn'),
			'submit' => __('Save', 'pn'),
		);					
		$options['title'] = array(
			'view' => 'inputbig',
			'title' => __('Title', 'pn'),
			'default' => $plugin->get_option('geoip', 'title'),
			'name' => 'title',
			'work' => 'input',
			'ml' => 1,
		);
		$options['text'] = array(
			'view' => 'editor',
			'title' => __('Text', 'pn'),
			'default' => $plugin->get_option('geoip', 'text'),
			'name' => 'text',
			'work' => 'text',
			'rows' => '20',
			'ml' => 1,
		);	
			
		$form = new PremiumForm();
		$params_form = array(
			'filter' => 'all_geoip_form',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
			
	}

	function all_geoip_enabled_country() {
		
		$countries = get_countries();
		$en_country = get_option('geoip_country');
		if (!is_array($en_country)) { $en_country = array(); }
		?>
		<div class="premium_standart_line"> 
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Active countries', 'pn'); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
							
					if (is_array($countries)) {
						foreach ($countries as $attr => $title) {
							$checked = 0;
							if (in_array($attr, $en_country)) {
								$checked = 1;
							}	
							$scroll_lists[] = array(
								'title' => ctv_ml($title) . '(' . $attr . ')',
								'checked' => $checked,
								'value' => $attr,
							);
						}
					}
					$scroll_lists = pn_array_sort($scroll_lists, 'title', 'asc');
					$scroll_lists = pn_array_sort($scroll_lists, 'checked', 'desc', 'num');
					echo get_check_list($scroll_lists, 'enabled[]', '', '300', 1);
					?>
		
					<div class="premium_clear"></div>
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>	
		<?php			
	}

	function all_geoip_country($change) {
		global $wpdb;

		$plugin = get_plugin_class();	
			
		$blocked = $plugin->get_option('geoip', 'blocked');
		if (!is_array($blocked)) { $blocked = array(); }
			
		$countries = get_option('geoip_country');
		if (!is_array($countries)) { $countries = array(); }
		?>
		<div class="premium_standart_line"> 
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Blocked countries', 'pn'); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
						
					$checked = 0;
					if (in_array('NaN', $blocked)) {
						$checked = 1;
					}
					$scroll_lists[] = array(
						'title' => __('is not determined', 'pn') . ' (NaN)',
						'checked' => $checked,
						'value' => 'NaN',
					);	
						
					$i_scroll_lists = array();
					if (is_array($countries)) {
						foreach ($countries as $attr) {
							$checked = 0;
							if (in_array($attr, $blocked)) {
								$checked = 1;
							}	
							$i_scroll_lists[] = array(
								'title' => get_country_title($attr) . ' (' . $attr . ')',
								'checked' => $checked,
								'value' => $attr,
							);
						}
					}
					$i_scroll_lists = pn_array_sort($i_scroll_lists, 'title', 'asc');
					$i_scroll_lists = pn_array_sort($i_scroll_lists, 'checked', 'desc', 'num');
					$scroll_lists = array_merge($scroll_lists, $i_scroll_lists);
					echo get_check_list($scroll_lists, 'blocked[]', array('NaN' => 'bred'), '300', 1);
					?>
		
					<div class="premium_clear"></div>
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>	
		<?php
	}

}

add_action('premium_action_all_geoip', 'def_premium_action_all_geoip');
function def_premium_action_all_geoip() {
	
	$plugin = get_plugin_class();	

	_method('post');
			
	$form = new PremiumForm();
	$form->send_header();
			
	pn_only_caps(array('administrator', 'pn_geoip'));
				
	$options = array('title', 'text');	
	foreach ($options as $key) {
		$val = pn_strip_text(is_param_post_ml($key));
		$plugin->update_option('geoip', $key, $val);
	}
			
	$enabled = is_param_post('enabled');
	$geoip = array();
	
	if (is_array($enabled)) {
		foreach ($enabled as $cou) {
			$cou = is_country_attr($cou);
			if ($cou) {
				$geoip[$cou] = $cou;
			}
		}
	}
				
	update_option('geoip_country', $geoip);			
			
			
	$blocked = is_param_post('blocked');
	$now = array();
	
	if (is_array($blocked)) {
		foreach ($blocked as $cou) {
			$cou = is_country_attr($cou);
			if ($cou) {
				$now[$cou] = $cou;
			}
		}
	}
			
	$plugin->update_option('geoip', 'blocked', $now);
						
	do_action('all_geoip_form_post');
					
	$url = admin_url('admin.php?page=all_geoip&reply=true');
	$form->answer_form($url);
		
}	