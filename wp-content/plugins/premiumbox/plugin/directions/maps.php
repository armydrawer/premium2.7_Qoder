<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('set_exchange_cat_filters', 'set_exchange_cat_filters_htmlmap');
function set_exchange_cat_filters_htmlmap($cats) {
	
	if (is_extension_active('pn_extended', 'moduls', 'htmlmap')) {
		$cats['sm'] = __('Sitemap HTML', 'pn');
	}
	
	return $cats;
}

add_filter('set_exchange_cat_filters', 'set_exchange_cat_filters_sitemap');
function set_exchange_cat_filters_sitemap($cats) {
	
	if (is_extension_active('pn_extended', 'moduls', 'seo')) {
		$cats['smxml'] = __('Sitemap XML', 'pn');
	}
	
	return $cats;
} 

/* html */
add_filter('all_htmlmap_option', 'pn_all_htmlmap_option', 100);
function pn_all_htmlmap_option($options) {
	
	$plugin = get_plugin_class();
	$options['exchanges'] = array(
		'view' => 'select',
		'title' => __('Show exchange directions', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $plugin->get_option('htmlmap', 'exchanges'),
		'name' => 'exchanges',
	);
	$options['line_exchanges'] = array(
		'view' => 'line',
	);	
	
	return $options;
}

add_action('all_htmlmap_option_post', 'def_all_htmlmap_option_post');
function def_all_htmlmap_option_post() {
	
	$plugin = get_plugin_class();
	$options = array('exchanges');					
	foreach ($options as $key) {
		$plugin->update_option('htmlmap', $key, intval(is_param_post($key)));
	}				
	
} 

add_filter('insert_sitemap_page', 'pn_insert_sitemap_page');
function pn_insert_sitemap_page($temp) {
	global $wpdb;
	
	$plugin = get_plugin_class();

	if (1 == $plugin->get_option('htmlmap','exchanges')) {
				
		$show_data = pn_exchanges_output('sm');		
		if ($show_data['text']) {
			$temp .= '<div class="resultfalse">' . $show_data['text'] . '</div>';
		}
				
		if (1 == $show_data['show']) {
					
			$temp .= '
			<div class="sitemap_block">
				<div class="sitemap_block_ins">';	
				
					$sitemap_block_title = '
					<div class="sitemap_title">
						<div class="sitemap_title_ins">
							<div class="sitemap_title_abs"></div>
							'. __('Exchange directions', 'pn') .'
						</div>
					</div>
						<div class="clear"></div>
					';
					$temp .= apply_filters('sitemap_block_title', $sitemap_block_title, 'exchanges');
							 
					$temp .= '
					<div class="sitemap_once">
						<div class="sitemap_once_ins">
							<ul class="sitemap_ul_exchanges">';
							
							$v = get_currency_data();				
							$where = get_directions_where('sm'); 
							$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where ORDER BY to3_1 ASC");						
							
							foreach ($directions as $direction) {
								$output = apply_filters('get_direction_output', 1, $direction, 'sm');
								if ($output) {
									$currency_id_give = $direction->currency_id_give;
									$currency_id_get = $direction->currency_id_get;
									
									if (isset($v[$currency_id_give]) and isset($v[$currency_id_get])) {
										$vd1 = $v[$currency_id_give];
										$vd2 = $v[$currency_id_get];
										
										$title1 = get_currency_title($vd1);
										$title2 = get_currency_title($vd2);
										$link = get_exchange_link($direction->direction_name);
										$line = '<li><a href="' . $link . '">' . $title1 . ' &rarr; ' . $title2 . '</a></li>';
										$temp .= apply_filters('sitemap_exchange_title', $line, $vd1, $vd2, $link, $direction);
									}
								}
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
	
	return $temp;
}

add_filter('all_xmlmap_option', 'pn_all_xmlmap_option', 100);
function pn_all_xmlmap_option($options) {
	
	$plugin = get_plugin_class();

	$options['exchanges'] = array(
		'view' => 'select',
		'title' => __('Show exchange directions', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $plugin->get_option('xmlmap', 'exchanges'),
		'name' => 'exchanges',
	);	
	
	return $options;
}

add_action('all_xmlmap_option_post', 'pn_all_xmlmap_option_post');
function pn_all_xmlmap_option_post() {
	
	$plugin = get_plugin_class();
	$options = array('exchanges');					
	foreach ($options as $key) {
		$plugin->update_option('xmlmap', $key, intval(is_param_post($key)));
	}				
	
} 

add_action('insert_xmlmap_page', 'pn_insert_xmlmap_page');
function pn_insert_xmlmap_page() {
	global $wpdb;
	
	$plugin = get_plugin_class();
	$now_time = current_time('timestamp');
	if (1 == $plugin->get_option('xmlmap', 'exchanges')) {
		$show_data = pn_exchanges_output('smxml');
		if (1 == $show_data['show']) {
			$where = get_directions_where("smxml");
			$directions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE $where ORDER BY to3_1 ASC");		
			foreach ($directions as $direction) {
				$output = apply_filters('get_direction_output', 1, $direction, 'smxml');
				if ($output) {
					$link = get_exchange_link($direction->direction_name);
?>
	<url>
		<loc><?php echo $link; ?></loc>
		<changefreq>daily</changefreq>
		<priority>0.6</priority>
		<lastmod><?php echo date('Y-m-d', $now_time); ?></lastmod>
	</url>
<?php		
				}
			}	
		}
	}
}