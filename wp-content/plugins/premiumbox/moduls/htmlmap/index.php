<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]HTML sitemap[:en_US][ru_RU:]HTML карта сайта[:ru_RU]
description: [en_US:]HTML sitemap[:en_US][ru_RU:]HTML карта сайта[:ru_RU]
version: 2.7.0
category: [en_US:]Settings[:en_US][ru_RU:]Настройки[:ru_RU]
cat: sett
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

if (!function_exists('list_tech_pages_htmlmap')) {
	add_filter('pn_tech_pages', 'list_tech_pages_htmlmap');
	function list_tech_pages_htmlmap($pages) {
		
		$pages[] = array(
			'post_name'      => 'sitemap',
			'post_title'     => '[en_US:]Sitemap[:en_US][ru_RU:]Карта сайта[:ru_RU]',
			'post_content'   => '[sitemap]',
			'post_template'   => 'pn-pluginpage.php',
		);		
		
		return $pages;
	}
}

if (!function_exists('def_adminpage_htmlmap')) {
	add_action('admin_menu', 'def_adminpage_htmlmap', 500);
	function def_adminpage_htmlmap() {
		
		$plugin = get_plugin_class();
		add_submenu_page("options-general.php", __('HTML sitemap settings', 'pn'), __('HTML sitemap settings', 'pn'), 'administrator', "all_htmlmap", array($plugin, 'admin_temp'));
		
	}
}

if (!function_exists('def_adminpage_title_all_htmlmap')) {
	add_filter('pn_adminpage_title_all_htmlmap', 'def_adminpage_title_all_htmlmap');
	function def_adminpage_title_all_htmlmap($page) {
		
		return __('HTML sitemap settings', 'pn');
	} 
}
 
if (!function_exists('def_pn_adminpage_content_all_htmlmap')) {
	add_action('pn_adminpage_content_all_htmlmap', 'def_pn_adminpage_content_all_htmlmap');
	function def_pn_adminpage_content_all_htmlmap() {
		
		$plugin = get_plugin_class();

		$form = new PremiumForm();

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('HTML sitemap settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);
		
		$args = array('public' => 1);
		$post_types = get_post_types($args, 'objects');

		foreach ($post_types as $data) {
			$post_type = is_isset($data, 'name');
			if ('attachment' != $post_type) {
				$post_label = is_isset($data, 'label');
				$hierarchical = intval(is_isset($data, 'hierarchical'));
		
				$options[$post_type . '_title'] = array(
					'view' => 'inputbig',
					'title' => sprintf(__('Section title for "%s"', 'pn'), $post_label),
					'default' => $plugin->get_option('htmlmap', $post_type . '_title'),
					'name' => $post_type . '_title',
					'work' => 'input',
					'ml' => 1,
				);		
				$options[$post_type . '_show'] = array(
					'view' => 'select',
					'title' => sprintf(__('Show "%s" in sitemap', 'pn'), $post_label),
					'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
					'default' => $plugin->get_option('htmlmap', $post_type . '_show'),
					'name' => $post_type . '_show',
				);
				$options[] = array(
					'view' => 'user_func',
					'name' => 'exclude_' . $post_type,
					'func_data' => array('type' => $post_type, 'label' => $post_label),
					'func' => 'all_htmlmap_option1',
				);				
				$options[] = array(
					'view' => 'line',
				);				
			}
		}		
		
		$params_form = array(
			'filter' => 'all_htmlmap_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);	
	} 
}

if (!function_exists('all_htmlmap_option1')) {
	function all_htmlmap_option1($data) {
		
		$type = is_isset($data, 'type');
		$label = is_isset($data, 'label');
		$args = array(
			'post_type' => $type,
			'posts_per_page' => '-1'
		);
		$pages = get_posts($args);
		$plugin = get_plugin_class();
		
		$exclude_pages = $plugin->get_option('htmlmap', 'exclude_' . $type);
		if (!is_array($exclude_pages)) { $exclude_pages = array(); }
		?>
		<div class="premium_standart_line">
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php printf(__('Exclude "%s" from sitemap', 'pn'), $label); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">
				
					<?php
					$scroll_lists = array();
					if (is_array($pages)) {
						foreach ($pages as $item) {
							$checked = 0;
							if (in_array($item->ID, $exclude_pages)) {
								$checked = 1;
							}
							$scroll_lists[] = array(
								'title' => '<a href="' . get_permalink($item->ID) . '" target="_blank">' . pn_strip_input(ctv_ml($item->post_title)) . '</a>',
								'checked' => $checked,
								'value' => $item->ID,
							);
						}	
					}	
					echo get_check_list($scroll_lists, 'exclude_' . $type . '[]', '', '300', 1);
					?>
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>					
		<?php 	
	} 
} 

if (!function_exists('def_premium_action_all_htmlmap')) {
	add_action('premium_action_all_htmlmap', 'def_premium_action_all_htmlmap');
	function def_premium_action_all_htmlmap() {	

		_method('post');
		
		$form = new PremiumForm();
		$form->send_header();
		
		pn_only_caps(array('administrator'));
		
		$plugin = get_plugin_class();
		
		$args = array('public' => 1);
		$post_types = get_post_types($args, 'objects');

		foreach ($post_types as $data) {
			$post_type = is_isset($data, 'name');
			if ('attachment' != $post_type) {
				
				$new_exclude_page = array();
				$exclude_page = is_param_post('exclude_' . $post_type);
				if (is_array($exclude_page)) {
					foreach ($exclude_page as $val) {
						$new_exclude_page[] = intval($val);
					}
				}
				$plugin->update_option('htmlmap','exclude_' . $post_type, $new_exclude_page);
				
				$plugin->update_option('htmlmap', $post_type . '_title', pn_strip_input(is_param_post_ml($post_type . '_title')));
				$plugin->update_option('htmlmap', $post_type . '_show', intval(is_param_post($post_type . '_show')));
			}
		}
		
		do_action('all_htmlmap_option_post');
		
		$url = admin_url('options-general.php?page=all_htmlmap&reply=true');
		$form->answer_form($url);
		
	} 
}

$plugin = get_plugin_class();
$plugin->include_path(__FILE__, 'display');