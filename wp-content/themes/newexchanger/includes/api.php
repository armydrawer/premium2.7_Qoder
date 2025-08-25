<?php 
if (!defined('ABSPATH')) { exit(); }

add_action('live_change_html', 'js_select_live');
function js_select_live() {
	?>
	$(document).Jselect('init', {trigger: '.js_my_sel', class_ico: 'currency_logo'});
	<?php
} 

add_action('live_change_html', 'js_checkbox_live');
function js_checkbox_live() {
	?>
	$(document).JcheckboxInit();
	<?php
} 

add_filter('insert_table_col_title', 'theme_insert_table_col_title');
function theme_insert_table_col_title() {
	return 1;
}

add_filter('news_widget_one', 'theme_news_widget_one', 10, 5);
function theme_news_widget_one($html, $item, $count, $r, $date_format) {
	
	$image_arr = wp_get_attachment_image_src(get_post_thumbnail_id($item->ID), 'site-thumbnail');
	$image = trim(is_isset($image_arr, 0));
	$link = get_permalink($item->ID);
	$title = pn_strip_input(ctv_ml($item->post_title));
	
	$html = '
	<div class="widget_news_line">';
	
		if ($image) {
			$html .= '
			<div class="widget_news_image"><a href="' . $link . '" title="' . $title . '"><img src="' . $image . '" alt="' . $title . '" /></a></div>
			';
		}
	
		$html .= '
		<div class="widget_news_date">' . get_the_time($date_format, $item->ID) . '</div>
			<div class="clear"></div>
		<div class="widget_news_title"><a href="' . $link . '" title="' . $title . '">' . $title . '</a></div>
		<div class="widget_news_content"><a href="' . $link . '" title="' . $title . '">' . get_pn_excerpt($item, 10) . '</a></div>
	</div>
	';
	
	return $html;
} 

add_filter('lchange_widget_line', 'my_lchange_widget_line', 10, 2);
function my_lchange_widget_line($widget, $bid) {
	
	$widget = '
	<div class="' . $bid['place'] . '_lchange_line lchangeid_' . $bid['id'] . '">		
		<div class="' . $bid['place'] . '_lchange_body">
							
			<div class="'. $bid['place'] .'_lchange_why"> 
				<div class="'. $bid['place'] .'_lchange_ico currency_logo" style="background-image: url('. $bid['logo_give'] .');"></div>
				<div class="'. $bid['place'] .'_lchange_txt">
					<div class="'. $bid['place'] .'_lchange_sum">'. is_out_sum($bid['sum_give'], $bid['decimal_give'], 'all') .'</div>
					<div class="'. $bid['place'] .'_lchange_name">'. $bid['currency_code_give'] .'</div>
				</div>
					<div class="clear"></div>
			</div>
							
			<div class="'. $bid['place'] .'_lchange_arr"></div>
							
			<div class="'. $bid['place'] .'_lchange_why">
				<div class="'. $bid['place'] .'_lchange_ico currency_logo" style="background-image: url('. $bid['logo_get'] .');"></div>
				<div class="'. $bid['place'] .'_lchange_txt">
					<div class="'. $bid['place'] .'_lchange_sum">'. is_out_sum($bid['sum_get'], $bid['decimal_get'], 'all') .'</div>
					<div class="'. $bid['place'] .'_lchange_name">'. $bid['currency_code_get'] .'</div>
				</div>
			</div>				
				<div class="clear"></div>
		</div>
		<div class="'. $bid['place'] .'_lchange_date">'. $bid['editdate'] .'</div>
	</div>		
	';	
	
	return $widget;
}