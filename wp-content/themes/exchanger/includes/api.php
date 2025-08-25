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