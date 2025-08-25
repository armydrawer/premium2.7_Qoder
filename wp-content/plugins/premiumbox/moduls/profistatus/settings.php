<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_action('admin_menu', 'admin_menu_profistatus', 100);
	function admin_menu_profistatus() {
		
		$plugin = get_plugin_class();
		add_submenu_page("pn_config", __('Status settings', 'pn'), __('Status settings', 'pn'), 'administrator', "pn_profistatus", array($plugin, 'admin_temp')); 
		
	}

	add_filter('pn_adminpage_title_pn_profistatus', 'def_adminpage_title_pn_profistatus');
	function def_adminpage_title_pn_profistatus($page) {
		
		return __('Status settings', 'pn');
	} 

	add_action('pn_adminpage_content_pn_profistatus', 'def_adminpage_content_pn_profistatus');
	function def_adminpage_content_pn_profistatus() {
		
		$form = new PremiumForm();

		$statused = list_bid_status();
		
		$options = array();
		$options['the_title'] = array(
			'view' => 'h3',
			'title' => __('Status settings', 'pn'),
			'submit' => __('Save', 'pn'),
		);
		
		$title = __('Change non-standard currency reserve when the status', 'pn');
		$now_statused = array();
		foreach ($statused as $st => $st_title) {
			$now_statused['give_' . $st] = $st_title . ' ('. __('give', 'pn') . ')';
			$now_statused['get_' . $st] = $st_title . ' ('. __('get', 'pn') . ')';
		}
		$options['settings1'] = array(
			'view' => 'user_func',
			'func_data' => array('list' => $now_statused, 'name' => 'reserve', 'title' => $title),
			'func' => '_profistatus_option',
		);
		
		$options['the_title1'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save', 'pn'),
		);		

		$title = __('Statuses that work with merchants', 'pn');
		$options['settings2'] = array(
			'view' => 'user_func',
			'func_data' => array('list' => $statused, 'name' => 'merch', 'title' => $title),
			'func' => '_profistatus_option',
		);
		$options['the_title2'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save', 'pn'),
		);		
		
		$title = __('Statuses that work with auto payouts', 'pn');
		$options['settings2_1'] = array(
			'view' => 'user_func',
			'func_data' => array('list' => $statused, 'name' => 'paymerch', 'title' => $title),
			'func' => '_profistatus_option',
		);
		$options['the_title2_1'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save', 'pn'),
		);		

		$title = __('Statuses with which orders can be canceled', 'pn');
		$options['settings3'] = array(
			'view' => 'user_func',
			'func_data' => array('list' => $statused, 'name' => 'cancel', 'title' => $title),
			'func' => '_profistatus_option',
		);
		$options['the_title3'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save', 'pn'),
		);		

		$title = __('Statuses under which you can make a request by manual payment', 'pn');
		$options['settings4'] = array(
			'view' => 'user_func',
			'func_data' => array('list' => $statused, 'name' => 'payed', 'title' => $title),
			'func' => '_profistatus_option',
		);
		$options['the_title4'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save', 'pn'),
		);		

		$title = __('Order statuses for which the request is considered active', 'pn');
		$options['settings5'] = array(
			'view' => 'user_func',
			'func_data' => array('list' => $statused, 'name' => 'bid_active', 'title' => $title),
			'func' => '_profistatus_option',
		);
		$options['the_title5'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save', 'pn'),
		);		

		$title = __('Order statuses included in statistics', 'pn');
		$options['settings6'] = array(
			'view' => 'user_func',
			'func_data' => array('list' => $statused, 'name' => 'bid_has', 'title' => $title),
			'func' => '_profistatus_option',
		);
		$options['the_title6'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save', 'pn'),
		);		

		$title = __('Display button "Transfer" if order status is', 'pn');
		$options['settings7'] = array(
			'view' => 'user_func',
			'func_data' => array('list' => $statused, 'name' => 'apbutton', 'title' => $title),
			'func' => '_profistatus_option',
		);
		$options['the_title7'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save', 'pn'),
		);		

		$title = __('Perform payout for frozen orders if status of the order is', 'pn');
		$options['settings8'] = array(
			'view' => 'user_func',
			'func_data' => array('list' => $statused, 'name' => 'aptimeout', 'title' => $title),
			'func' => '_profistatus_option',
		);
		$options['the_title8'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save', 'pn'),
		);		

		$title = __('Statuses under which we calculate limits for auto payouts', 'pn');
		$options['settings9'] = array(
			'view' => 'user_func',
			'func_data' => array('list' => $statused, 'name' => 'paymerchlim', 'title' => $title),
			'func' => '_profistatus_option',
		);
		$options['the_title9'] = array(
			'view' => 'h3',
			'title' => '',
			'submit' => __('Save', 'pn'),
		);		

		$title = __('Statuses under which we calculate limits for merchants', 'pn');
		$options['settings10'] = array(
			'view' => 'user_func',
			'func_data' => array('list' => $statused, 'name' => 'merchlim', 'title' => $title),
			'func' => '_profistatus_option',
		);
				
		$params_form = array(
			'filter' => 'profistatus_option',
			'button_title' => __('Save', 'pn'),
		);
		$form->init_form($params_form, $options);

	}
	
}	

function _profistatus_option($data) {
	
	$list = $data['list'];
	$name = $data['name'];
	$sett = get_status_sett($name, 1);
	?>
	<div class="premium_standart_line">
		<div class="premium_stline_left"><div class="premium_stline_left_ins"><?php echo $data['title']; ?></div></div>
		<div class="premium_stline_right"><div class="premium_stline_right_ins">
			<div class="premium_wrap_standart">
				<?php 
				$scroll_lists = array();
				if (is_array($list)) {
					foreach ($list as $key => $val) {
						$checked = 0;
						if (in_array($key, $sett)) {
							$checked = 1;
						}
						$scroll_lists[] = array(
							'title' => $val,
							'checked' => $checked,
							'value' => $key,
						);
					}	
				}	
				echo get_check_list($scroll_lists, $name . '[]', '', '300', 1);				
				?>
					<div class="premium_clear"></div>
			</div>
		</div></div>
			<div class="premium_clear"></div>
	</div>	
	<?php
}

add_action('premium_action_pn_profistatus', 'def_premium_action_pn_profistatus');
function def_premium_action_pn_profistatus() {
	global $wpdb, $premiumbox;	

	_method('post');
		
	$form = new PremiumForm();
	$form->send_header();
		
	pn_only_caps(array('administrator'));
		
	$l_arrs = array('reserve', 'payed', 'cancel', 'merch', 'paymerch', 'apbutton', 'aptimeout', 'bid_active', 'bid_has', 'paymerchlim', 'merchlim');
	foreach ($l_arrs as $l_arr) {
		$new = array();
		$d = is_param_post($l_arr);
		if (is_array($d)) {
			foreach ($d as $v) {
				$v = is_status_name($v);
				if ($v) {
					$new[] = $v;
				}
			}
		}
		$premiumbox->update_option('statussett_' . $l_arr, '', $new);
	}

	$url = admin_url('admin.php?page=pn_profistatus&reply=true');
	$form->answer_form($url);
	
}  	