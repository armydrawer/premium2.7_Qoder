<?php 
if (!defined('ABSPATH')) { exit(); }

function paccount_page_shortcode($atts, $content) {
	global $wpdb, $premiumbox;
	
	$temp = '';
	
	$url = rtrim(get_site_url_ml(), '/') . '/';
	$temp .= apply_filters('before_paccount_page', '');
	
	$pages = $premiumbox->get_option('partners', 'pages');
	if (!is_array($pages)) { $pages = array(); }	
	if (in_array('paccount', $pages)) {	
	
		$ui = wp_get_current_user();
		$user_id = intval($ui->ID);	
		
		if ($user_id) {

			$date_reg = get_pn_time($ui->user_registered, get_option('date_format'));
			$plinks = get_partner_plinks($user_id);
			$referals = $wpdb->get_var("SELECT COUNT(ID) FROM " . $wpdb->prefix . "users WHERE ref_id = '$user_id'");
			$count_exchanges = get_user_count_refobmen($user_id);
			$exchanges_sum = get_user_sum_refobmen($user_id);
			if ($plinks > 0 and $count_exchanges > 0) {
				$cti = is_sum(($count_exchanges/$plinks) * 100, 2);
			} elseif ($count_exchanges > 0) {
				$cti = $count_exchanges * 100;
			} else {
				$cti = 0;
			}	
			
			$balans = get_partner_money($user_id, array('1'));
			$minpay = is_sum($premiumbox->get_option('partners', 'minpay'));
			$balans2 = get_partner_money($user_id, array('0', '1'));
			$dbalans = 0;
			if ($balans2 >= $minpay) {
				$dbalans = $balans2;
			} 		 
			$z_all = get_partner_earn_all($user_id); 
			$pay1 = get_partner_payout($user_id, array('1'));
			$pay2 = get_partner_payout($user_id, array('0'));

			$stand_refid = stand_refid();
			$cur_type = cur_type();
		
			$list_stat_paccount = array(
				'id' => array(
					'title' => __('Identification number', 'pn'),
					'content' => $user_id,
				),
				'registration_date' => array(
					'title' => __('Registration date', 'pn'),
					'content' => $date_reg,
				),	
				'user_email' => array(
					'title' => __('E-mail', 'pn'),
					'content' => is_email($ui->user_email),				
				),
				'percent' => array(
					'title' => __('Your aff. percentage', 'pn'),
					'content' => is_out_sum(get_user_pers_refobmen($user_id, $ui), 2, 'all') . '%',				
				),				
			);
			$list_stat_paccount = apply_filters('list_stat_paccount', $list_stat_paccount);		
		
			$topstat = '
			<table>';	
				foreach ($list_stat_paccount as $list_key => $list_value) {
					$topstat .= '
					<tr>
						<th>' . is_isset($list_value, 'title') . '</th>
						<td>' . is_isset($list_value, 'content') . '</td>
					</tr>					
					';
				}
			$topstat .= '	
			</table>
			';		
		
			$list_stat_pp = array(
				'visitors' => array(
					'title' => __('Visitors', 'pn'),
					'content' => $plinks,
				),
				'referals' => array(
					'title' => __('Count amount of referrals', 'pn'),
					'content' => $referals,
				),	
				'count_exchanges' => array(
					'title' => __('Exchanges', 'pn'),
					'content' => $count_exchanges,				
				),
				'exchanges_sum' => array(
					'title' => __('Amount of exchanges', 'pn'),
					'content' => is_out_sum($exchanges_sum, 2, 'all') .' '. $cur_type,				
				),	
				'ctr' => array(
					'title' => __('CTR', 'pn'),
					'content' => $cti .' %',				
				),
				'earned' => array(
					'title' => __('All time earned', 'pn'),
					'content' => is_out_sum($z_all, 12, 'all') .' '. $cur_type,				
				),
				'wait_payouts' => array(
					'title' => __('Waiting payments', 'pn'),
					'content' => is_out_sum($pay2, 12, 'all') .' '. $cur_type,				
				),
				'total_payouts' => array(
					'title' => __('Paid in total', 'pn'),
					'content' => is_out_sum($pay1, 12, 'all') .' '. $cur_type,				
				),
				'balance' => array(
					'title' => __('Current balance', 'pn'),
					'content' => is_out_sum($balans2, 12, 'all') .' '. $cur_type,				
				),
				'available_payouts' => array(
					'title' => __('Available for payout', 'pn'),
					'content' => is_out_sum($dbalans, 12, 'all') .' '. $cur_type,				
				),					
			);
			$list_stat_pp = apply_filters('list_stat_pp', $list_stat_pp);		
		
			$stat = '
			<table>';	
				foreach ($list_stat_pp as $list_key => $list_value) {
					$stat .= '
					<tr>
						<th>'. is_isset($list_value, 'title') .'</th>
						<td>'. is_isset($list_value, 'content') .'</td>
					</tr>					
					';
				}
			$stat .= '	
			</table>
			';				
			
			$promo = '
				<h3>'. __('Promotional materials', 'pn') .'</h3>
				<p>'. __('Ad text with a link that you place anywhere (on your website, in blogs, forums, FAQs, social networks, bookmarking services) will transit users to this website, and you will receive a guaranteed rewards for your referrals.', 'pn') .'</p>
				<p>'. __('Below are the basic options of promotional materials with your affiliate link included. You can use any text links or use ours. All you need is to copy the selected code, place it on your website and start making profit.', 'pn') .'</p>				
				<h4>'. __('Affiliate link', 'pn') .':</h4>			
				<p><textarea class="ptextareaus" onclick="this.select()">'. $url.'?'. $stand_refid .'='. $user_id .'</textarea></p>			
				<h4>'. __('Affiliate link in the HTML-code (for posting on websites and blogs)', 'pn') .':</h4>
				<p><textarea class="ptextareaus" onclick="this.select()"><a target="_blank" href="'.$url.'?'. $stand_refid .'='. $user_id .'">'. __('Currency exchange', 'pn') .'</a></textarea></p>			
				<h4>'. __('Hidden affiliate link in the HTML-code (for posting on websites and blogs)', 'pn') .':</h4>
				<p><textarea class="ptextareaus" onclick="this.select()"><a target="_blank" href="'.$url.'" onclick="this.href='.$url.'?'. $stand_refid .'='. $user_id .'">'. __('Currency exchange', 'pn') .'</a></textarea></p>				
				<h4>'. __('BBCode affiliate link (for posting on forums)','pn') .':</h4>	
				<p><textarea class="ptextareaus" onclick="this.select()">[url="'.$url.'?'. $stand_refid .'='. $user_id .'"]'. __('Currency exchange', 'pn') .'[/url]</textarea></p>   
			';	

			$array = array(
				'[topstat]' => $topstat,
				'[stat]' => $stat,
				'[promo]' => $promo,
			);	
		
			$temp_form = '
				<div class="stattablediv statstablediv">
					<div class="stattablediv_ins statstablediv_ins">
						[topstat]
					</div>
				</div>
				
				<div class="statuserdiv">
					<div class="statuserdiv_ins">
						<div class="statuserdiv_title">
							<div class="statuserdiv_title_ins">
								'. __('Statistics', 'pn') .'
							</div>
						</div>	
					
						[stat]
					</div>
				</div>
				
				<div class="promouserdiv">
					<div class="promouserdiv_ins">
					[promo]
					</div>
				</div>
			';
		
			$temp_form = apply_filters('paccount_form_temp',$temp_form);
			$temp .= replace_tags($array, $temp_form);
		
		} else {
			$temp .= '<div class="resultfalse">'. __('Error! Page is available for authorized users only', 'pn') .'</div>';
		}
	} else {
		$temp .= '<div class="resultfalse">'. __('Error! Page is unavailable', 'pn') .'</div>';
	}	
	
	$after = apply_filters('after_paccount_page', '');
	$temp .= $after;

	return $temp;
}
add_shortcode('paccount_page', 'paccount_page_shortcode');