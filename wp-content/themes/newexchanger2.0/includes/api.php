<?php
if( !defined( 'ABSPATH')){ exit(); }

add_action('live_change_html','js_select_live');
function js_select_live(){
	?>
	$(document).Jselect('init', {trigger: '.js_my_sel', class_ico: 'currency_logo'});
	<?php
}

add_action('live_change_html','js_checkbox_live');
function js_checkbox_live(){
	?>
	$(document).JcheckboxInit();
	<?php
}

add_filter('insert_table_col_title', 'theme_insert_table_col_title');
function theme_insert_table_col_title(){
	return 1;
}

add_filter('news_widget_one', 'theme_news_widget_one', 10, 5);
function theme_news_widget_one($html, $item, $count, $r, $date_format){
	$image_arr = wp_get_attachment_image_src(get_post_thumbnail_id($item->ID), 'full');
	$image = trim(is_isset($image_arr,0));
	$link = get_permalink($item->ID);
	$title = pn_strip_input(ctv_ml($item->post_title));

	$html = '
	<div class="widget_news_line swiper-slide news-slide">';

		if($image){
			$html .= '
			<div class="widget_news_image"><a href="'. $link .'" title="'. $title .'"><img src="'. $image .'" alt="'. $title .'" /></a></div>
			';
		}

		$html .= '
		<div class="widget_news_wrapper">
		<div class="widget_news_date">'. get_the_time('Y.m.d, H:i', $item->ID) .'</div>
		<div class="widget_news_title"><a href="'. $link .'" title="'. $title .'">'. $title .'</a></div>
		<div class="widget_news_content"><a href="'. $link .'" title="'. $title .'">'. get_pn_excerpt($item, 10) .'</a></div>
		</div>
	</div>
	';

	return $html;
}

add_filter('lchange_widget_line', 'my_lchange_widget_line', 10, 2);
function my_lchange_widget_line($widget, $bid){

	$widget_old = '
	<div class="'. $bid['place'] .'_lchange_line lchangeid_[id]">
		<div class="'. $bid['place'] .'_lchange_body">

			<div class="'. $bid['place'] .'_lchange_why">
				<div class="'. $bid['place'] .'_lchange_ico currency_logo" style="background-image: url('. $bid['logo_give'] .');"></div>
				<div class="'. $bid['place'] .'_lchange_txt">
					<div class="'. $bid['place'] .'_lchange_sum">'. $bid['sum_give'] .'</div>
					<div class="'. $bid['place'] .'_lchange_name">'. $bid['currency_code_give'] .'</div>
				</div>
					<div class="clear"></div>
			</div>

			<div class="'. $bid['place'] .'_lchange_arr"></div>

			<div class="'. $bid['place'] .'_lchange_why">
				<div class="'. $bid['place'] .'_lchange_ico currency_logo" style="background-image: url('. $bid['logo_get'] .');"></div>
				<div class="'. $bid['place'] .'_lchange_txt">
					<div class="'. $bid['place'] .'_lchange_sum">'. $bid['sum_get'] .'</div>
					<div class="'. $bid['place'] .'_lchange_name">'. $bid['currency_code_get'] .'</div>
				</div>
			</div>
				<div class="clear"></div>
		</div>
		<div class="'. $bid['place'] .'_lchange_date">'. $bid['editdate'] .'</div>
	</div>
	';

	$widget = '
	<div class="crypto">
		<div class="crypto__direction">
			<div class="direction">
				<span class="direction__title">'. $bid['currency_code_give'] .'</span>
				<span class="direction__arrow">→</span>
				<span class="direction__title">'. $bid['currency_code_get'] .'</span>
			</div>
			<span class="time">'. $bid['editdate'] .'</span>
		</div>
		<div class="crypto__history">
			<div class="coin">
				<div class="coin__logo"><img src="'. $bid['logo_give'] .'" alt=""></div>
				<div class="coin__info">
					<span>'. $bid['sum_give'] .'</span>
					<span class="coin__name">'. $bid['currency_code_give'] .'</span>
				</div>
			</div>
			<div class="coin_arrow"></div>
			<div class="coin">
				<div class="coin__logo"><img src="'. $bid['logo_get'] .'" alt=""></div>
				<div class="coin__info">
					<span>'. $bid['sum_get'] .'</span>
					<span class="coin__name">'. $bid['currency_code_get'] .'</span>
				</div>
			</div>
		</div>
	</div>';

	return $widget;
}

function theme_operator(){
	if (function_exists('get_operator_status')) {
		$operator = get_operator_status();
		if ($operator) {
			$html = '
			<div class="html_oper st_online"></div>
			';
		} else {
			$html = '
			<div class="html_oper st_offline"></div>
			';
		}
		echo $html;
	}
}

add_filter('merchant_temps_script', 'mystyle_merchant_temps_script');
function mystyle_merchant_temps_script($array) {
    $array['style'] = '<link rel="stylesheet" href="'.PN_TEMPLATEURL.'/css/merchant.min.css" type="text/css" media="all" />';
    $array['main-style'] = '<link rel="stylesheet" href="'.PN_TEMPLATEURL.'/css/main.min.css" type="text/css" media="all" />';
    return $array;
}
