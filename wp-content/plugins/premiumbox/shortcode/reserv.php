<?php
if (!defined('ABSPATH')) { exit(); }

/*
add_action('pn_adminpage_quicktags_page', 'pn_adminpage_quicktags_reserve');
function pn_adminpage_quicktags_reserve() {
?>
edButtons[edButtons.length] = 
new edButton('premium_reserv_form', '<?php _e('Reserve', 'pn'); ?>','[reserve ids="" notids="" line="2" logo_num="1"]');
<?php	
} 
*/

function reserve_shortcode($atts, $content = "") {
	global $wpdb;

	$temp = '';				

	$ids = trim(is_isset($atts,'ids'));
	if ($ids) {
		$ids = explode(',', $ids);
	}
	
	$notids = trim(is_isset($atts, 'notids'));
	if ($notids) {
		$notids = explode(',', $notids);
	}
	
	$line = intval(is_isset($atts, 'line'));
	$logo_num = intval(is_isset($atts, 'logo_num'));
	
	$currencies = list_view_currencies($ids, $notids);
	if (count($currencies) > 0) {
		$temp .= '
		<div class="reserv_wrap">
			<div class="reserv_block">
				<div class="reserv_many">
					<div class="reserv_many_ins">';
					
						$r = 0; 
						foreach ($currencies as $currency) { $r++; 
						
							$logo = $currency['logo'];
							if (2 == $logo_num) { 
								$logo = $currency['logo2']; 
							}					
						
							$temp .= '
							<div class="one_reserv"> 
								<div class="one_reserv_ico currency_logo" style="background-image: url('. $logo .');" data-logo="'. $currency['logo'] .'" data-logo-next="'. $currency['logo2'] .'"></div>
								<div class="one_reserv_block">
									<div class="one_reserv_title">
										'. $currency['title'] .'
									</div>
									<div class="one_reserv_sum">
										'. is_out_sum($currency['reserv'], $currency['decimal'], 'reserv') .'
									</div>
								</div>
									<div class="clear"></div>
							</div>';
							
							if ($line > 0 and 0 == $r%$line) { $temp .= '<div class="clear"></div>'; }
							
						} 
						
		$temp .= '			
							<div class="clear"></div>
					</div>	
				</div>	
			</div>
		</div>'; 
	} 	

	return $temp;
}
add_shortcode('reserve', 'reserve_shortcode');