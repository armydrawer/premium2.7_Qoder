<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('admin_menu_themesettings')) {
	
	add_action('admin_menu', 'admin_menu_themesettings');
	function admin_menu_themesettings() {
		
		$plugin = get_plugin_class();
		add_submenu_page("themes.php", __('Theme logo', 'pn'), __('Theme logo', 'pn'), 'administrator', "all_themelogo", array($plugin, 'admin_temp'));
		
	}

	add_filter('pn_adminpage_title_all_themelogo', 'def_pn_adminpage_title_all_themelogo');
	function def_pn_adminpage_title_all_themelogo($page) {
		
		return __('Theme logo', 'pn');
	} 

	add_filter('all_themelogo_option', 'def_all_themelogo_option', 1);
	function def_all_themelogo_option($options) {	
	
		$plugin = get_plugin_class();
		
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Theme logo', 'pn'),
			'submit' => __('Save', 'pn'),
		);
				
		$options['favicon'] = array(
			'view' => 'uploader',
			'title' => __('Favicon', 'pn'),
			'default' => $plugin->get_option('favicon'),
			'name' => 'favicon',
			'work' => 'input',
			'ml' => 1,
		);			
				
		$options['logo'] = array(
			'view' => 'uploader',
			'title' => __('Logo', 'pn'),
			'default' => $plugin->get_option('logo'),
			'name' => 'logo',
			'work' => 'input',
			'ml' => 1,
		);					

		$options['textlogo'] = array(
			'view' => 'inputbig',
			'title' => __('Text logo', 'pn'),
			'default' => $plugin->get_option('textlogo'),
			'name' => 'textlogo',
			'work' => 'input',
			'ml' => 1,
		);		
		
		return $options;
	}	
	
	add_action('pn_adminpage_content_all_themelogo', 'def_pn_adminpage_content_all_themelogo');
	function def_pn_adminpage_content_all_themelogo() {

		$form = new PremiumForm();
		$params_form = array(
			'filter' => 'all_themelogo_option',
		);
		$form->init_form($params_form);	
		
	} 

	add_action('premium_action_all_themelogo', 'def_premium_action_all_themelogo');
	function def_premium_action_all_themelogo() {
		
		$plugin = get_plugin_class();
	
		_method('post');
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));

		$data = $form->strip_options('all_themelogo_option', 'post');
		
		$opts =  array('favicon', 'logo', 'textlogo');
		foreach ($opts as $opt) {
			$plugin->update_option($opt, '', is_isset($data, $opt));
		}	
		
		do_action('all_themelogo_option_post', $data);
		
		$back_url = is_param_post('_wp_http_referer');
		$back_url = add_query_args(array('reply' => 'true'), $back_url);
				
		$form->answer_form($back_url);	
		
	}
}

if (!function_exists('favicon_theme_wp_head')) {
	add_action('wp_head', 'favicon_theme_wp_head');
	add_action('admin_head', 'favicon_theme_wp_head');
	add_filter('premium_other_head', 'get_favicon_theme_wp_head'); 
	add_action('edit_bid_head', 'favicon_theme_wp_head');
	add_filter('merchant_header_head', 'get_favicon_theme_wp_head');
	
	function favicon_theme_wp_head() {
		
		echo get_favicon_theme_wp_head();
	}	
	
	function get_favicon_theme_wp_head($html = '') {
		
		$plugin = get_plugin_class();
		$favicon = pn_strip_input(ctv_ml($plugin->get_option('favicon')));
		if ($favicon) { 
			$wp_filetype = wp_check_filetype(basename($favicon), null);
			$favicon = is_ssl_url($favicon);
			$html .= '<link rel="shortcut icon" href="' . $favicon . '" type="' . is_isset($wp_filetype, 'type') . '" />';
			$html .= "\n";
			$html .= '<link rel="icon" href="' . $favicon . '" type="' . is_isset($wp_filetype,'type') . '" />
			';
		}
		
		return $html;
	} 
	
	function get_logotype() {
		
		$plugin = get_plugin_class();
		
		return is_ssl_url(pn_strip_input(ctv_ml($plugin->get_option('logo'))));
	}
	
	function get_textlogo() {
		
		$plugin = get_plugin_class();
		return pn_strip_input(ctv_ml($plugin->get_option('textlogo')));
	} 
}