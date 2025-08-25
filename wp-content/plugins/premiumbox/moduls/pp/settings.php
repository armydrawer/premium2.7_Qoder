<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_psettings', 'def_adminpage_title_pn_psettings');
	function def_adminpage_title_pn_psettings() {
		
		return __('Settings', 'pn');
	}

	add_action('pn_adminpage_content_pn_psettings', 'def_adminpage_content_pn_psettings');
	function def_adminpage_content_pn_psettings() {
		global $premiumbox;
		
		$form = new PremiumForm();
		
		$options = array();
		$options['top_title'] = array(
			'view' => 'h3',
			'title' => __('Settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);	
		$options['wref'] = array(
			'view' => 'select',
			'title' => __('Referral lifetime', 'pn'),
			'options' => array('0' => __('Users + by cookies', 'pn'), '1' => __('By cookies', 'pn')),
			'default' => $premiumbox->get_option('partners', 'wref'),
			'name' => 'wref',
		);	
		$options['clife'] = array(
			'view' => 'input',
			'title' => __('Cookies lifetime (days)', 'pn'),
			'default' => $premiumbox->get_option('partners', 'clife'),
			'name' => 'clife',
		);	
		$options['line1'] = array(
			'view' => 'line',
		);		
		$options['minpay'] = array(
			'view' => 'input',
			'title' => __('Minimum payout', 'pn').' ('. cur_type() .')',
			'default' => $premiumbox->get_option('partners', 'minpay'),
			'name' => 'minpay',
		);
		$options['scalc'] = array(
			'view' => 'select',
			'title' => __('Accrue partner reward from', 'pn'),
			'options' => array('0' => __('Profits in direction settings', 'pn'), '1' => __('Exchange amounts', 'pn')),
			'default' => $premiumbox->get_option('partners', 'scalc'),
			'name' => 'scalc',
		);		
		$options['calc'] = array(
			'view' => 'select',
			'title' => __('Charge affiliate reward from', 'pn'),
			'options' => array('0' => __('All users', 'pn'), '1' => __('Registered users only', 'pn')),
			'default' => $premiumbox->get_option('partners', 'calc'),
			'name' => 'calc',
		);				
		$options['line2'] = array(
			'view' => 'line',
		);
		$options['uskidka'] = array(
			'view' => 'select',
			'title' => __('Take users discount into account when calculating partner reward', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes, discount percentage', 'pn'), '2' => __('Yes, discount amount', 'pn')),
			'default' => $premiumbox->get_option('partners', 'uskidka'),
			'name' => 'uskidka',
		);				
		$options['line3'] = array(
			'view' => 'line',
		);	
		$options['disable_pages'] = array(
			'view' => 'user_func',
			'name' => 'pages',
			'func_data' => array(),
			'func' => 'pn_pp_disable_pages_option',
		);		
		$options['line4'] = array(
			'view' => 'line',
		);
		$options['reserv'] = array(
			'view' => 'user_func',
			'name' => 'reserv',
			'func_data' => array(),
			'func' => 'pn_pp_reserv_option',
		);
		$options['line5'] = array(
			'view' => 'line',
		);
		$options['text_banners'] = array(
			'view' => 'select',
			'title' => __('Show promo text materials', 'pn'),
			'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
			'default' => $premiumbox->get_option('partners', 'text_banners'),
			'name' => 'text_banners',
		);
		$options['line6'] = array(
			'view' => 'line',
		);	 
		$tags = array(
			'minpay' => array(
				'title' => __('Minimum payout', 'pn'),
				'start' => '[minpay]',
			),
			'currency' => array(
				'title' => __('Currency type', 'pn'),
				'start' => '[currency]',
			),
			'dbalans' => array(
				'title' => __('Available for payout', 'pn'),
				'start' => '[dbalans]',
			),			
		);
		$options['payouttext'] = array(
			'view' => 'editor',
			'title' => __('Text above form of withdrawal of partner funds', 'pn'),
			'default' => $premiumbox->get_option('partners', 'payouttext'),
			'tags' => $tags,
			'formatting_tags' => 1,
			'rows' => '13',
			'name' => 'payouttext',
			'work' => 'text',
			'ml' => 1,
		);	
		
		$params_form = array(
			'filter' => 'pn_pp_adminform',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);		
	}

	function pn_pp_disable_pages_option() {	
		global $premiumbox;

		$pages = $premiumbox->get_option('partners', 'pages');
		if (!is_array($pages)) { $pages = array(); }				
		$list = array(
			'paccount' => __('Affiliate account', 'pn'),
			'promotional' => __('Promotional materials', 'pn'),
			'plinks' => __('Affiliate transitions', 'pn'),
			'pexch' => __('Affiliate exchanges', 'pn'),
			'preferals' => __('Affiliate referrals', 'pn'),
			'payouts' => __('Affiliate payouts', 'pn'),
			'partnersfaq' => __('Affiliate FAQ', 'pn'),
			'terms' => __('Affiliate terms and conditions', 'pn'),
		);
		?>
		<div class="premium_standart_line"> 
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Show sections', 'pn'); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
									
					foreach ($list as $key => $title) {
						$checked = 0;
						if (in_array($key, $pages)) {
							$checked = 1;
						}	
						$scroll_lists[] = array(
							'title' => $title,
							'checked' => $checked,
							'value' => $key,
						);
					}
					echo get_check_list($scroll_lists, 'pages[]');
					?>
					<div class="premium_clear"></div>
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>								
		<?php				
	}

	function pn_pp_reserv_option() {
		global $premiumbox;

		$reserv = $premiumbox->get_option('partners', 'reserv');
		if (!is_array($reserv)) { $reserv = array(); }

		$list = array(
			'0' => __('pending order', 'pn'),
			'1' => __('paid order', 'pn'),
		);
		?>	
		<div class="premium_standart_line"> 
			<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php _e('Consider affiliate payments included in reserve', 'pn'); ?></div></div>
			<div class="premium_stline_right"><div class="premium_stline_right_ins">
				<div class="premium_wrap_standart">
					<?php
					$scroll_lists = array();
									
					foreach ($list as $key => $title) {
						$checked = 0;
						if (in_array($key, $reserv)) {
							$checked = 1;
						}	
						$scroll_lists[] = array(
							'title' => $title,
							'checked' => $checked,
							'value' => $key,
						);
					}
					echo get_check_list($scroll_lists, 'reserv[]');
					?>
					<div class="premium_clear"></div>
				</div>
			</div></div>
				<div class="premium_clear"></div>
		</div>	
		<?php 	
	} 

}

add_action('premium_action_pn_psettings', 'def_premium_action_pn_psettings');
function def_premium_action_pn_psettings() {
	global $premiumbox;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator', 'pn_pp'));
		
	$premiumbox->update_option('partners', 'wref', intval(is_param_post('wref')));
	$premiumbox->update_option('partners', 'payouttext', pn_strip_text(is_param_post_ml('payouttext')));
	$premiumbox->update_option('partners', 'text_banners', intval(is_param_post('text_banners')));
	$premiumbox->update_option('partners', 'scalc', intval(is_param_post('scalc')));
	$premiumbox->update_option('partners', 'calc', intval(is_param_post('calc')));
	$premiumbox->update_option('partners', 'uskidka', intval(is_param_post('uskidka')));
	$premiumbox->update_option('partners', 'reserv', is_param_post('reserv'));
	$premiumbox->update_option('partners', 'minpay',is_sum(is_param_post('minpay'), 2));		
	$premiumbox->update_option('partners', 'clife', intval(is_param_post('clife')));
	$premiumbox->update_option('partners', 'pages', is_param_post('pages'));
				
	do_action('pn_pp_adminform_post');
				
	$url = admin_url('admin.php?page=pn_psettings&reply=true');
	$form->answer_form($url);
	
}		