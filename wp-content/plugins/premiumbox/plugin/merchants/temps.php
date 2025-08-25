<?php
if (!defined('ABSPATH')) { exit(); }

function merchant_temps_body_class() {
	global $premiumbox;
	
	$body_class = "";
	if (1 == $premiumbox->get_option('exchange', 'mhead_style')) {
		$body_class = "body_black";
	}
	return implode(' ', get_body_class($body_class));
}

function merchant_temps_script() {
	global $premiumbox;

	$time = current_time('timestamp');

	$temp = '';
	
	$array = array(
		'jquery' => '<script type="text/javascript" src="' . get_premium_url() . 'js/jquery/script.min.js?ver=' . $time . '"></script>',
		'jquery-ui' => '<script type="text/javascript" src="' . get_premium_url() . 'js/jquery-ui/script.min.js"></script>',
		'clipboard' => '<script type="text/javascript" src="' . get_premium_url() . 'js/jquery-clipboard/script.min.js"></script>',
		'form' => '<script type="text/javascript" src="' . get_premium_url() . 'js/jquery-forms/script.min.js"></script>',
		'style' => '<link rel="stylesheet" href="' . $premiumbox->plugin_url . 'merchant_style.css?ver=' . $time . '" type="text/css" media="all" />',
	);
	
	$array = apply_filters('merchant_temps_script', $array);
	foreach ($array as $name => $link) {
		$temp .= $link . "\n";
	}
	
	return $temp;
}

add_filter('merchant_header', 'def_merchant_header', 0);
function def_merchant_header($html) {
	global $premiumbox, $bids_data;
	
	$item_title1 = pn_strip_input(ctv_ml($bids_data->psys_give)) . ' ' . is_site_value($bids_data->currency_code_give);
	$item_title2 = pn_strip_input(ctv_ml($bids_data->psys_get)) . ' ' . is_site_value($bids_data->currency_code_get);
	$title = sprintf(__('Exchange %1$s to %2$s','pn'), $item_title1, $item_title2);
	
	$html .= '
	<!DOCTYPE html>
	<html ' . get_language_attributes('html') . '>
	<head>

		'. apply_filters('merchant_header_head', '', $title) .'

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		<meta name="HandheldFriendly" content="True" />
		<meta name="MobileOptimized" content="320" />
		<meta name="format-detection" content="telephone=no" />
		<meta name="PalmComputingPlatform" content="true" />
		<meta name="apple-touch-fullscreen" content="yes"/>
		<meta charset="'. get_charset() .'">
		<title>'. $title .'</title>
			
		'. merchant_temps_script() .'
		
	</head>
	<body class="' . merchant_temps_body_class() . '">';
		
	return $html;
}

add_filter('merchant_footer', 'def_merchant_footer', 1000);
function def_merchant_footer($html) {
	
	$html .= "	
	</body>
	</html>
	";
	
	return $html;
} 

add_action('merchant_init_form', 'def_merchant_init_form', 10, 3);
function def_merchant_init_form($m_in, $sum_to_pay, $direction) {
	global $bids_data;	
	
	echo apply_filters('merchant_header', '', $direction, 'form'); 
							
	$form = apply_filters('merchant_bidform', '', $m_in, $sum_to_pay, $direction);
	if (strlen($form) > 0) {
		echo '<div id="goedform" style="display: none;">';
		echo $form;
		echo '</div>';
		echo '<div id="redirect_text" class="success_div" style="display: none;">' . __('Redirecting. Please wait', 'pn') . '</div>';
		?>
		<script type="text/javascript">
		jQuery(function($) {
			
			document.oncontextmenu=function(e) {return false};
			window.history.replaceState(null, null, '<?php echo get_bids_url($bids_data->hashed); ?>');
			$('#redirect_text').show();
			$('#goedform form').attr('target','_self').submit();
			
		});
		</script>							
		<?php
	}							
								
	echo apply_filters('merchant_footer', '', $direction, 'form');	
}

add_action('merchant_init_info', 'def_merchant_init_info', 10, 3);
function def_merchant_init_info($m_in, $sum_to_pay, $direction) {
	global $bids_data;	
	
	echo apply_filters('merchant_header', '', $direction, 'info'); 
							
	$action = apply_filters('merchant_bidaction', '', $m_in, $sum_to_pay, $direction);				
	if ($action) {
		echo $action;
		?>
		<script type="text/javascript">
		jQuery(function($) {
			
			var clipboard = new ClipboardJS('.js_copy');
			var clipboard2 = new ClipboardJS('.zone_copy');
			var clipboard3 = new ClipboardJS('.zone_text');
					
			$('.js_copy').on('click', function() {
				
				$(this).addClass('copied');
				
			});	
								
			$('.zone_copy').on('click', function() {
				
				$(this).addClass('copied');
				$('.zone_div').removeClass('active');
				$(this).parents('.zone_div').addClass('active');
				
			});
			
			$('.zone_text').on('click', function() {
				
				$('.zone_div').removeClass('active');
				$(this).parents('.zone_div').addClass('active');
				
			});			
								
			$(document).click(function(event) {
				
				if ($(event.target).closest(".js_copy").length) { return; }
				
				$('.js_copy').removeClass('copied');
				event.stopPropagation();
				
			});								
								
			$(document).click(function(event) {
				
				if ($(event.target).closest(".zone_div").length) { return; }
				
				$('.zone_copy').removeClass('copied').parents('.zone_div').removeClass('active');
				event.stopPropagation();
				
			});
			
		});
		</script>
		<?php
	}							
								
	echo apply_filters('merchant_footer', '', $direction, 'info');	
}

add_filter('merchant_header', 'merchdesign_merchant_header', 5);
function merchdesign_merchant_header($html) {
	global $premiumbox, $bids_data;

	$logo = get_logotype();
	$textlogo = get_textlogo();
	
	$item_title1 = pn_strip_input(ctv_ml($bids_data->psys_give)) . ' ' . is_site_value($bids_data->currency_code_give);
	$item_title2 = pn_strip_input(ctv_ml($bids_data->psys_get)) . ' ' . is_site_value($bids_data->currency_code_get);
	$title = sprintf(__('Exchange %1$s to %2$s', 'pn'), $item_title1, $item_title2);
	$title_order = apply_filters('merchant_order_title', __('Order ID', 'pn') . ' <strong>' . $bids_data->id . '</strong>');
	
	$html .= '
	<div class="header">
		<div class="logo">
			<div class="logo_ins">
				<a href="' . get_site_url(1) . '">';
								
					if ($logo) {
						$html .= '<img src="' . $logo . '" alt="" />';
					} elseif ($textlogo) {
						$html .= $textlogo; 
					} else { 
						$textlogo = str_replace(array('http://', 'https://', 'www.'), '', PN_SITEURL);
						$html .= get_caps_name($textlogo);	
					} 
									
				$html .= '				
				</a>	
			</div>
		</div>
			<div class="clear"></div>
	</div>
	<div class="exchange_title">
		<div class="exchange_title_ins">
			'. $title .'
		</div>
	</div>
	<div class="order_title">
		<div class="order_title_ins">
			'. $title_order .'
		</div>
	</div>	
	<div class="back_div"><a href="' . get_bids_url($bids_data->hashed) . '" id="back_link">' . __('Back to order page', 'pn') . '</a></div>
	<div class="content">
	';

	return $html;
}

add_filter('merchant_footer', 'merchdesign_merchant_footer', 990);
function merchdesign_merchant_footer($html) {
	global $premiumbox, $bids_data;

	$html .= '
	</div>';
	
	$html .= "
	<script type='text/javascript'>
		jQuery(function($){			
			$(document).on('keyup', function( e ){
				if(e.which == 27){
					var nurl = $('a#back_link').attr('href');
					window.location.href = nurl;
				}
			});								
		});
	</script>	
	";

	return $html;
} 