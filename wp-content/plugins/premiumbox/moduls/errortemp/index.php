<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Stylized error output[:en_US][ru_RU:]Стилизованный вывод ошибки[:ru_RU]
description: [en_US:]Stylized error output[:en_US][ru_RU:]Стилизованный вывод ошибки[:ru_RU]
version: 2.7.0
category: [en_US:]Other[:en_US][ru_RU:]Остальное[:ru_RU]
cat: other
*/

add_filter('premium_display_mess', 'theme_premium_display_mess', 10, 4);
function theme_premium_display_mess($text_html, $title, $text, $species) {
	
	$textlogo = get_textlogo();
	if (!$textlogo) { $textlogo = get_caps_name(str_replace(array('http://', 'https://', 'www.'), '', PN_SITEURL)); }
	
	$html = '
	<style type="text/css">
	body {
		color: #2e3033;
		background: #f3f7fc;
		padding: 0; 
		margin: 0;
	}
	</style>
	<div style="min-width: 320px; width: 100%; padding: 0 0 20px 0px; margin: 0;">
		<div style="width: 100%; background: #fff; box-shadow: 0 0 5px #e1e9f2; padding: 40px 10px; text-align: center; margin: 0 0 30px 0;">
			<a href="' . get_site_url_ml() . '" style="font: bold 26px Arial; text-decoration: none; color: #000;">' . $textlogo . '</a>
		</div>
		<div style="border: 1px solid #e6ecf4; font: 16px Arial; text-align: center; border-radius: 10px; background: #fff; box-shadow: 0 0 5px #e1e9f2; max-width: 500px; padding: 40px 10px; margin: 0 auto;">
			<div>
		';
			
			if ('error' == $species) {
				$html .= '<div style="color: #ed1c24; margin: 0 0 20px; font: 600 16px Arial;">' . __('Error!', 'pn') . '</div>';
			} 
			
			$html .= $text;	
		
		$html .= '
			</div>
		</div>
	</div>
	';
	
	return $html;
}