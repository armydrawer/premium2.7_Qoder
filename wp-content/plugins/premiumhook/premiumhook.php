<?php 
/*
Plugin Name: Premium Exchanger hooks
Plugin URI: http://premiumexchanger.com
Description: Actions and filters
Version: 0.1
Author: Premium
Author URI: http://premiumexchanger.com
*/

if( !defined( 'ABSPATH')){ exit(); }

add_action('wp_footer','my_wp_footer'); 
function my_wp_footer(){
?>

<!-- Put online chat code or another code here / Razmestite kod onlajn chata ili drugoi kod vmesto jetogo teksta !-->

<?php
}

add_filter('pn_site_langs','myhook_pn_site_langs');
function myhook_pn_site_langs($langs){
 
$langs['ua_UA'] = 'Український'; 
$langs['kz_KZ'] = 'Казақ';
$langs['es_ES'] = 'Español';

return $langs;
}