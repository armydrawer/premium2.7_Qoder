<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('def_adminpage_title_all_robotstxt')) {
	add_filter('pn_adminpage_title_all_robotstxt', 'def_adminpage_title_all_robotstxt');
	function def_adminpage_title_all_robotstxt($page) {
		
		return __('Robots.txt settings', 'pn');
	}
}

if (!function_exists('def_adminpage_content_all_robotstxt')) {
	add_action('pn_adminpage_content_all_robotstxt', 'def_adminpage_content_all_robotstxt');
	function def_adminpage_content_all_robotstxt() {
		
		$plugin = get_plugin_class();
	?>
		<div class="premium_substrate">
			<?php _e('Robots.txt URL', 'pn'); ?>:<br /> 
			<a href="<?php echo get_site_url_or(); ?>robots.txt" target="_blank"><?php echo get_site_url_or(); ?>robots.txt</a>
		</div>	
	<?php	
		$form = new PremiumForm();
		
		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Robots.txt settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);		
		$options['txt'] = array( 
			'view' => 'textarea',
			'title' => __('Text', 'pn'),
			'default' => $plugin->get_option('robotstxt', 'txt'),
			'name' => 'txt',
			'rows' => '20',
		);

		$params_form = array(
			'filter' => 'robotstxt_changeform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
	}
}

if (!function_exists('def_premium_action_all_robotstxt')) {
	add_action('premium_action_all_robotstxt', 'def_premium_action_all_robotstxt');
	function def_premium_action_all_robotstxt() {
		
		$plugin = get_plugin_class();
		
		_method('post');
		pn_only_caps(array('administrator', 'pn_seo'));
		
		$form = new PremiumForm();
		
		$options = array('txt');	
						
		foreach ($options as $key) {
			$val = pn_strip_input(is_param_post($key));
			$plugin->update_option('robotstxt', $key, $val);
		}				

		do_action('robotstxt_changeform_post');
		
		$url = admin_url('admin.php?page=all_robotstxt&reply=true');
		$form->answer_form($url);
		
	} 
}

if (!function_exists('set_robotstxt')) {
	add_filter('robots_txt', 'set_robotstxt', 99, 2);
	function set_robotstxt($output, $public) {
		if (1 == $public) {
			$plugin = get_plugin_class();
			
			$txt = pn_strip_text($plugin->get_option('robotstxt', 'txt'));
			if (strlen($txt) > 0) {
				$txt = $txt . "\n\r";
			} else {
				$txt = "User-agent: *\nDisallow: /wp-admin/\nDisallow: /wp-includes/\n\r";
			}
	 
			$txt.= 'Sitemap: ' . get_request_link('sitemap', 'xml') . "\n";
			
			return $txt;
		}
		
		return $output;
	}
}