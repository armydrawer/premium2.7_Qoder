<?php
if (!defined('ABSPATH')) { exit(); }

function get_lchange_line($bid, $place) {
	
	$line = '';

	$array = array('place' => $place);
	$bid = array_merge($bid, $array);

	$line = '
	<div class="'. $place .'_lchange_line lchangeid_'. $bid['id'] .'">
		<div class="'. $place .'_lchange_date">'. $bid['editdate'] .'</div>
			<div class="clear"></div>
								
		<div class="'. $place .'_lchange_body">
							
			<div class="'. $place .'_lchange_why"> 
				<div class="'. $place .'_lchange_ico currency_logo" style="background-image: url('. $bid['logo_give'] .');"></div>
				<div class="'. $place .'_lchange_txt">
					<div class="'. $place .'_lchange_sum">'. is_out_sum($bid['sum_give'], $bid['decimal_give'], 'all') .'</div>
					<div class="'. $place .'_lchange_name">'. $bid['currency_code_give'] .'</div>
				</div>
					<div class="clear"></div>
			</div>
							
			<div class="'. $place .'_lchange_arr"></div>
							
			<div class="'. $place .'_lchange_why">
				<div class="'. $place .'_lchange_ico currency_logo" style="background-image: url('. $bid['logo_get'] .');"></div>
				<div class="'. $place .'_lchange_txt">
					<div class="'. $place .'_lchange_sum">'. is_out_sum($bid['sum_get'], $bid['decimal_get'], 'all') .'</div>
					<div class="'. $place .'_lchange_name">'. $bid['currency_code_get'] .'</div>
				</div>
			</div>
			
				<div class="clear"></div>
		</div>
	</div>	
	';					
						
	$line = apply_filters('lchange_'. $place .'_line', $line, $bid);						
						
	return $line;					
}

/*
add_action('pn_adminpage_quicktags_page', 'pn_adminpage_quicktags_lchanges');
function pn_adminpage_quicktags_lchanges() {
?>
edButtons[edButtons.length] = 
new edButton('premium_lchanges_form', '<?php _e('Last exchanges', 'pn'); ?>','[last_exchanges count=""]');
<?php	
}
*/

function lchanges_shortcode($atts, $content = "") {
	
	$html = '';				

	$count = intval(is_isset($atts, 'count'));
	if ($count < 1) { $count = 1; }
		
	$html = '
	<div class="shortcode_lchanges">';
		
		$bids = get_last_bids('success', $count);
		if (count($bids) > 0) { 
			foreach ($bids as $bid) {
				$html .= get_lchange_line($bid, 'shortcode');
			}
		} else {
			$html .= '<div class="resultfalse">'. __('No orders', 'pn') .'</div>';
		}
						
	$html .= '				
	</div>		
	';	

	$html = apply_filters('lchange_shortcode_block', $html);	
	
	return $html;
}
add_shortcode('last_exchanges', 'lchanges_shortcode');  