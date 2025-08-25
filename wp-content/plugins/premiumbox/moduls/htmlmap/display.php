<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('sitemap_shortcode') and !is_admin()) { 
	function sitemap_shortcode($atts, $content = "") {
		global $wpdb;
		
		$plugin = get_plugin_class();
		
		$temp = apply_filters('before_sitemap_page', '<div class="sitemap_div"><div class="sitemap_div_ins">');
		
		$temp .= apply_filters('insert_sitemap_page', '');
		
		$args = array('public' => 1);
		$post_types = get_post_types($args, 'objects');
		$r = 0;
		foreach ($post_types as $data) {
			$post_type = is_isset($data, 'name');
			if ('attachment' != $post_type) { $r++;
				$post_label = is_isset($data, 'label');
				$new_title = pn_strip_input(ctv_ml($plugin->get_option('htmlmap', $post_type . '_title')));
				if (strlen($new_title) > 0) {
					$post_label = $new_title;
				}
				
				if (1 == $plugin->get_option('htmlmap', $post_type . '_show')) {
			
					$temp .= '
					<div class="sitemap_block">
						<div class="sitemap_block_ins">';	
			
							$sitemap_block_title = '
							<div class="sitemap_title">
								<div class="sitemap_title_ins">
									<div class="sitemap_title_abs"></div>
									'. $post_label .'
								</div>
							</div>
								<div class="clear"></div>
							';
							$temp .= apply_filters('sitemap_block_title', $sitemap_block_title, $post_type);
							
							$temp .= '
							<div class="sitemap_once">
								<div class="sitemap_once_ins">
								<ul class="sitemap_ul">
							';
							
								$exclude_pages = $plugin->get_option('htmlmap', 'exclude_' . $post_type);
								if (!is_array($exclude_pages)) { $exclude_pages = array(); }
								$exclude = implode(',', $exclude_pages);
								$args = array(
									'post_type' => $post_type,
									'posts_per_page' => '-1',
									'exclude' => $exclude
								);			
								$mposts = get_posts($args);
						
								foreach ($mposts as $mpos) { 
									$temp .= '<li><a href="' . get_permalink($mpos->ID) . '">' . pn_strip_input(ctv_ml($mpos->post_title)) . '</a></li>';
								}			
						
							$temp .= '
								</ul>
									<div class="clear"></div>
								</div>
							</div>';
							
						$temp .= '
						</div>
					</div>	
					';	
				
				}				
			}
		}	

		$temp .= apply_filters('after_sitemap_page', '</div></div>');			
		
		return $temp;
	}
	add_shortcode('sitemap', 'sitemap_shortcode');
}