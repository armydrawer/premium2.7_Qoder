<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('def_adminpage_title_all_xmlmap')) {
	add_filter('pn_adminpage_title_all_xmlmap', 'def_adminpage_title_all_xmlmap');
	function def_adminpage_title_all_xmlmap($page) {
		
		return __('XML sitemap settings', 'pn');
	} 
}

if (!function_exists('def_adminpage_content_all_xmlmap')) {
	add_action('pn_adminpage_content_all_xmlmap', 'def_adminpage_content_all_xmlmap');
	function def_adminpage_content_all_xmlmap() {
		
		$plugin = get_plugin_class();
	?>
		<div class="premium_substrate">
			<?php _e('XML sitemap', 'pn'); ?>:<br /> 
			<a href="<?php echo get_request_link('sitemap', 'xml'); ?>" target="_blank"><?php echo get_request_link('sitemap', 'xml'); ?></a>
		</div>	
	<?php
		$form = new PremiumForm();

		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('XML sitemap settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);
			
		$args = array('public' => 1);
		$post_types = get_post_types($args, 'objects');
		foreach ($post_types as $data) {
			$post_type = is_isset($data, 'name');
			if ('attachment' != $post_type) {
				$post_label = is_isset($data, 'label');
				$hierarchical = intval(is_isset($data, 'hierarchical'));
				$options[$post_type . '_show'] = array(
					'view' => 'select',
					'title' => sprintf(__('Show "%s" in sitemap', 'pn'), $post_label),
					'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
					'default' => $plugin->get_option('xmlmap', $post_type . '_show'),
					'name' => $post_type . '_show',
				);
				$options[] = array(
					'view' => 'user_func',
					'name' => 'exclude_' . $post_type,
					'func_data' => array('type' => $post_type, 'label' => $post_label),
					'func' => 'all_xmlmap_option1',
				);				
				$options[] = array(
					'view' => 'line',
				);				
			}
		}					
			
		$params_form = array(
			'filter' => 'all_xmlmap_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);
	}
}

if (!function_exists('all_xmlmap_option1')) {
	function all_xmlmap_option1($data) {
		
		$type = is_isset($data, 'type');
		$label = is_isset($data, 'label');
		$plugin = get_plugin_class();
			
		$args = array(
			'post_type' => $type,
			'posts_per_page' => '-1'
		);
		$pages = get_posts($args);

		$exclude_pages = $plugin->get_option('xmlmap','exclude_' . $type);
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
								'title' => '<a href="' . get_permalink($item->ID) . '" target="_blank" rel="noreferrer noopener">' . pn_strip_input(ctv_ml($item->post_title)) . '</a>',
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

if (!function_exists('def_premium_action_all_xmlmap')) {
	add_action('premium_action_all_xmlmap', 'def_premium_action_all_xmlmap');
	function def_premium_action_all_xmlmap() {
		
		_method('post');
			
		$form = new PremiumForm();
		$form->send_header();
			
		pn_only_caps(array('administrator', 'pn_seo'));
			
		$plugin = get_plugin_class();
			
		$args = array('public' => 1);
		$post_types = get_post_types($args, 'objects');

		foreach ($post_types as $data) {
			$post_type = is_isset($data, 'name');
			if ('attachment' != $post_type) {
				$new_exclude_page = array();
				$exclude_page = is_param_post('exclude_'.$post_type);
				if (is_array($exclude_page)) {
					foreach ($exclude_page as $val) {
						$new_exclude_page[] = intval($val);
					}
				}
				$plugin->update_option('xmlmap','exclude_' . $post_type, $new_exclude_page);

				$plugin->update_option('xmlmap', $post_type . '_show', intval(is_param_post($post_type . '_show')));
			}
		}				

		do_action('all_xmlmap_option_post');
			
		$url = admin_url('admin.php?page=all_xmlmap&reply=true');
		$form->answer_form($url);
		
	} 
}

if (!function_exists('def_premium_request_sitemap')) {
add_action('premium_request_sitemap', 'def_premium_request_sitemap');
function def_premium_request_sitemap() {
	
$plugin = get_plugin_class();

header("Content-Type: text/xml");
$site_url = get_site_url_ml();
$now_time = current_time('timestamp');
?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="' . $plugin->plugin_url . 'moduls/seo/sm_style/sitemap.xsl"?>'; ?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">	
    <url>
		<loc><?php echo $site_url; ?></loc>
		<changefreq>daily</changefreq>
		<priority>1.0</priority>
		<lastmod><?php echo date('Y-m-d', $now_time); ?></lastmod>
	</url>	
<?php
if (!$plugin->is_up_mode()) {
	
do_action('insert_xmlmap_page');	
	
$args = array('public' => 1);
$post_types = get_post_types($args, 'objects');
$ex = get_option('the_pages');
foreach ($post_types as $data) {
	$post_type = is_isset($data, 'name');
	if ('attachment' != $post_type) { 
		$post_label = is_isset($data, 'label');	
		if (1 == $plugin->get_option('xmlmap', $post_type . '_show')) {
			$exclude_pages = $plugin->get_option('xmlmap', 'exclude_' . $post_type);
			if (!is_array($exclude_pages)) { $exclude_pages = array(); }
			if (isset($ex['home'])) {
				$exclude_pages[] = $ex['home'];
			}
			$exclude = join(',', $exclude_pages);
			$args = array(
				'post_type' => $post_type,
				'posts_per_page' => '-1',
				'exclude' => $exclude
			);			
			$items = get_posts($args);				
			foreach ($items as $item) {
?>
	<url>
		<loc><?php echo get_permalink($item->ID); ?></loc>
		<changefreq>daily</changefreq>
		<priority>0.6</priority>
		<lastmod><?php echo get_pn_date($item->post_modified, 'Y-m-d'); ?></lastmod>
	</url>
<?php		
			}			
		}
	}
}
}
?>
</urlset>
<?php
}
}