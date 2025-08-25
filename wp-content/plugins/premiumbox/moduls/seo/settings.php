<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('def_adminpage_title_all_seo')) {
	add_filter('pn_adminpage_title_all_seo', 'def_adminpage_title_all_seo');
	function def_adminpage_title_all_seo($page) {
		
		return __('SEO', 'pn');
	} 
}

if (!function_exists('def_all_seo_option')) {
	add_filter('all_seo_option', 'def_all_seo_option', 9, 3);
	function def_all_seo_option($options, $place, $hierarchical) {
		
		$plugin = get_plugin_class();
			
		if ($place) {
			
			$options['all_title'] = array(
				'view' => 'h3',
				'title' => __('Settings', 'pn'),
				'submit' => __('Save', 'pn'),
			);		
			$options['hidden_block'] = array(
				'view' => 'hidden_input',
				'name' => 'place',
				'default' => $place,
			);
			$options['hidden_block2'] = array(
				'view' => 'hidden_input',
				'name' => 'hierarchical',
				'default' => $hierarchical,
			);			
				
			if ('default' == $place) {				
				$options['ogp_def_img'] = array(
					'view' => 'uploader',
					'title' => __('OGP image', 'pn') . ' (' . __('default','pn') . ')',
					'default' => $plugin->get_option('seo', 'ogp_def_img'),
					'name' => 'ogp_def_img',
					'work' => 'input',
				);
				$options['all_post_title'] = array(
					'view' => 'h3',
					'title' => __('Single microdata settings', 'pn'),
					'submit' => __('Save', 'pn'),
				);					
				$options['post_img'] = array(
					'view' => 'uploader',
					'title' => __('Image for news (default)', 'pn'),
					'default' => $plugin->get_option('seo', 'post_img'),
					'name' => 'post_img',
					'work' => 'input',
				);				
				$options['post_author'] = array(
					'view' => 'inputbig',
					'title' => __('Author', 'pn'),
					'default' => $plugin->get_option('seo', 'post_author'),
					'name' => 'post_author',
					'work' => 'input',
					'ml' => 1,
				);
				$options['post_name'] = array(
					'view' => 'inputbig',
					'title' => __('Organization name', 'pn'),
					'default' => $plugin->get_option('seo', 'post_name'),
					'name' => 'post_name',
					'work' => 'input',
					'ml' => 1,
				);
				$options['post_address'] = array(
					'view' => 'inputbig',
					'title' => __('Organization address', 'pn'),
					'default' => $plugin->get_option('seo', 'post_address'),
					'name' => 'post_address',
					'work' => 'input',
					'ml' => 1,
				);
				$options['post_telephone'] = array(
					'view' => 'inputbig',
					'title' => __('Organization telephone', 'pn'),
					'default' => $plugin->get_option('seo', 'post_telephone'),
					'name' => 'post_telephone',
					'work' => 'input',
					'ml' => 1,
				);				
			} else {
				if (1 != $hierarchical) {
					$options[$place . '_title'] = array(
						'view' => 'input',
						'title' => __('Page title', 'pn'),
						'default' => $plugin->get_option('seo', $place . '_title'),
						'name' => $place . '_title',
						'work' => 'input',
						'ml' => 1,
						'atts' => array('class' => 'long_input'),
					);	
					$options[$place . '_key'] = array( 
						'view' => 'textarea',
						'title' => __('Page keywords', 'pn'),
						'default' => $plugin->get_option('seo', $place .'_key'),
						'name' => $place . '_key',
						'work' => 'input',
						'rows' => '4',
						'word_count' => 1,
						'ml' => 1,
					);
					$options[$place . '_descr'] = array( 
						'view' => 'textarea',
						'title' => __('Page description', 'pn'),
						'default' => $plugin->get_option('seo', $place . '_descr'),
						'name' => $place . '_descr',
						'work' => 'input',
						'rows' => '6',
						'word_count' => 1,
						'ml' => 1,
					);					
					$options['title_line'] = array(
						'view' => 'line',
					);
					$options['ogp_' . $place . '_title'] = array(
						'view' => 'input',
						'title' => __('OGP title', 'pn'),
						'default' => $plugin->get_option('seo', 'ogp_' . $place . '_title'),
						'name' => 'ogp_' . $place . '_title',
						'work' => 'input',
						'ml' => 1,
						'atts' => array('class' => 'long_input'),
					);	
					$options['ogp_' . $place . '_descr'] = array( 
						'view' => 'textarea',
						'title' => __('OGP description', 'pn'),
						'default' => $plugin->get_option('seo', 'ogp_' . $place . '_descr'),
						'name' => 'ogp_' . $place . '_descr',
						'work' => 'input',
						'rows' => '6',
						'word_count' => 1,
						'ml' => 1,
					);	
					$options['ogp_' . $place . '_img'] = array(
						'view' => 'uploader',
						'title' => __('OGP image', 'pn'),
						'default' => $plugin->get_option('seo', 'ogp_' . $place . '_img'),
						'name' => 'ogp_' . $place . '_img',
						'work' => 'input',
					);
					$options['line2'] = array(
						'view' => 'line',
					);						
				}
				if ('home' != $place) {
					$tags = array();
					$tags['sitename'] = array(
						'title' => __('Site name', 'pn'),
						'start' => '[sitename]',
					);				
					$tags['title'] = array(
						'title' => __('Title', 'pn'),
						'start' => '[title]',
					);	
					$options[$place . '_temp'] = array(
						'view' => 'editor',
						'title' => __('Title template', 'pn'),
						'default' => $plugin->get_option('seo', $place . '_temp'),
						'tags' => $tags,
						'rows' => '3',
						'name' => $place . '_temp',
						'work' => 'input',
						'ml' => 1,
					);
				}
			}
		}
			
		return $options;
	}
}

if (!function_exists('def_adminpage_content_all_seo')) {
	add_action('pn_adminpage_content_all_seo', 'def_adminpage_content_all_seo');
	function def_adminpage_content_all_seo() {
		
		$form = new PremiumForm();
		$plugin = get_plugin_class();
			
		$place = trim(is_param_get('place'));
			
		$selects = array();
		$selects[] = array(
			'link' => admin_url("admin.php?page=all_seo"),
			'title' => '--' . __('Make a choice', 'pn') . '--',
			'default' => '',
		);
		$selects[] = array(
			'link' => admin_url("admin.php?page=all_seo&place=default"),
			'title' => __('General settings', 'pn'),
			'default' => 'default',
		);			
		$selects[] = array(
			'link' => admin_url("admin.php?page=all_seo&place=home"),
			'title' => __('Home page', 'pn'),
			'default' => 'home',
		);
				
		$hiers = array();
				
		$args = array('public' => 1);
		$post_types = get_post_types($args, 'objects');
		foreach ($post_types as $data) {
			$post_type = is_isset($data, 'name');
			if ('attachment' != $post_type) {
				$post_label = is_isset($data, 'label');
				$hierarchical = intval(is_isset($data, 'hierarchical'));
				if (1 == $hierarchical) {
					$hiers[$post_type] = $post_type;
				}	
					
				$selects[] = array(
					'link' => admin_url("admin.php?page=all_seo&place=" . $post_type),
					'title' => $post_label,
					'default' => $post_type,
				);					
			}
		}
					
		$selects = apply_filters('selects_all_seo', $selects);		
		
		$form->select_box($place, $selects, __('Setting up', 'pn'));
			
		$options = array();
			
		$hierarchical = 0;	
		if (isset($hiers[$place])) {
			$hierarchical = 1;
		}
			
		if ($place) {
			$options = apply_filters('all_seo_option', $options, $place, $hierarchical);
			$params_form = array(
				'filter' => 'all_seo_option_' . $place,
				'button_title' => __('Save', 'pn'),
			);
			$form->init_form($params_form, $options);
		}	
	} 
}

if (!function_exists('def_premium_action_all_seo')) {
	add_action('premium_action_all_seo', 'def_premium_action_all_seo');
	function def_premium_action_all_seo() {
		
		$plugin = get_plugin_class();

		_method('post');
			
		$form = new PremiumForm();
		$form->send_header();
			
		pn_only_caps(array('administrator', 'pn_seo'));
			
		$place = trim(is_param_post('place')); 
		$hierarchical = intval(is_param_post('hierarchical'));
		if ($place) {
			
			$options = apply_filters('all_seo_option', array(), $place, $hierarchical);
			$data = $form->strip_options('all_seo_option_' . $place, 'post', $options);
						
			foreach ($data as $data_key => $data_value) {
				$plugin->update_option('seo', $data_key, $data_value);
			}				
			
		}
			
		$url = admin_url('admin.php?page=all_seo&place=' . $place . '&reply=true');
		$form->answer_form($url);
		
	} 
}	