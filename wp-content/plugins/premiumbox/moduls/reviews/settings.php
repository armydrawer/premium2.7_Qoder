<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	if (!function_exists('def_adminpage_title_all_settings_reviews')) {
		add_filter('pn_adminpage_title_all_settings_reviews', 'def_adminpage_title_all_settings_reviews');
		function def_adminpage_title_all_settings_reviews() {
			
			return __('Settings', 'pn');
		}
	}

	if (!function_exists('def_adminpage_content_all_settings_reviews')) {
		add_action('pn_adminpage_content_all_settings_reviews', 'def_adminpage_content_all_settings_reviews');
		function def_adminpage_content_all_settings_reviews() {
			
			$plugin = get_plugin_class();
			
			$form = new PremiumForm();
			
			$options = array();
			$options['top_title'] = array(
				'view' => 'h3',
				'title' => __('Settings', 'pn'),
				'submit' => __('Save', 'pn'),
			);
			
			$count = intval($plugin->get_option('reviews', 'count'));
			if (!$count) { $count = 10; }
			$options['count'] = array(
				'view' => 'input',
				'title' => __('Amount of reviews on a page', 'pn'),
				'default' => $count,
				'name' => 'count',
			);				
			$options['deduce'] = array(
				'view' => 'select',
				'title' => __('Display reviews', 'pn'),
				'options' => array('0' => __('All', 'pn'), '1' =>__('by language', 'pn')),
				'default' => $plugin->get_option('reviews', 'deduce'),
				'name' => 'deduce',
			);
			$options['method'] = array(
				'view' => 'select',
				'title' => __('Method used for adding process', 'pn'),
				'options' => array('not' => __('Forbidden to add', 'pn'), 'verify' => __('E-mail confirmation', 'pn'), 'moderation' => __('Moderation by admin', 'pn'), 'notmoderation' => __('Without moderation', 'pn')),
				'default' => $plugin->get_option('reviews', 'method'),
				'name' => 'method',
			);
			$options['by'] = array(
				'view' => 'select',
				'title' => __('For whom', 'pn'),
				'options' => array('0' => __('All' ,'pn'), '1' => __('only users', 'pn'), '2' =>__('only quests', 'pn')),
				'default' => $plugin->get_option('reviews', 'by'),
				'name' => 'by',
			);			
			$options['website'] = array(
				'view' => 'select',
				'title' => __('Enable field "Website"', 'pn'),
				'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
				'default' => $plugin->get_option('reviews', 'website'),
				'name' => 'website',
			);
			$options['maxlinks'] = array(
				'view' => 'input',
				'title' => __('Maximum links in review', 'pn'),
				'default' => $plugin->get_option('reviews', 'maxlinks'),
				'name' => 'maxlinks',
			);
			$options['stopwords'] = array(
				'view' => 'editor',
				'title' => __('Stop words', 'pn'),
				'default' => $plugin->get_option('reviews', 'stopwords'),
				'name' => 'stopwords',
				'rows' => '5',
			);
			$options['disdomains'] = array(
				'view' => 'editor',
				'title' => __('Disable e-mail domains (in new line)', 'pn'),
				'default' => $plugin->get_option('reviews', 'disdomains'),
				'name' => 'disdomains',
				'rows' => '10',
			);
			$options['blacklist'] = array(
				'view' => 'textarea',
				'title' => __('Black list (in new line)', 'pn'),
				'default' => $plugin->get_option('reviews', 'blacklist'),
				'name' => 'blacklist',
				'rows' => '10',
			);			
			$options['disabledip'] = array(
				'view' => 'textarea',
				'title' => __('Disabled ip (in new line)', 'pn'),
				'default' => $plugin->get_option('reviews', 'disabledip'),
				'name' => 'disabledip',
				'rows' => '10',
			);			
			
			$form = new PremiumForm();
			$params_form = array(
				'filter' => 'all_reviews_settingsform',
				'button_title' => __('Save', 'pn'),
			);
			$form->init_form($params_form, $options);
			
		}
	}
}

if (!function_exists('def_premium_action_all_settings_reviews')) {
	add_action('premium_action_all_settings_reviews', 'def_premium_action_all_settings_reviews');
	function def_premium_action_all_settings_reviews() {
		global $wpdb;	

		$plugin = get_plugin_class();
			
		_method('post');
			
		$form = new PremiumForm();
		$form->send_header();
			
		pn_only_caps(array('administrator', 'pn_reviews'));
				
		$options = array('count', 'by', 'deduce', 'website', 'maxlinks');	
		foreach ($options as $key) {
			$val = intval(is_param_post($key));
			$plugin->update_option('reviews', $key, $val);
		}
		
		$options = array('method', 'stopwords', 'disdomains', 'blacklist', 'disabledip');	
		foreach ($options as $key) {
			$val = pn_strip_input(is_param_post($key));
			$plugin->update_option('reviews', $key, $val);
		}		
						
		do_action('all_reviews_settingsform_post');
					
		$url = admin_url('admin.php?page=all_settings_reviews&reply=true');
		$form->answer_form($url);
		
	}
}	