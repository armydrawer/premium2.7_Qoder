<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Exchange rate dependent on amount of exchange[:en_US][ru_RU:]Курс зависящий от суммы обмена[:ru_RU]
description: [en_US:]Exchange rate dependent on amount of exchange[:en_US][ru_RU:]Курс зависящий от суммы обмена[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('item_direction_delete', 'item_direction_delete_sumcurs');
function item_direction_delete_sumcurs($item_id) {
	global $wpdb;
	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "constructs WHERE itemtype = 'coursebysum' AND item_id = '$item_id'");
	
}
 
add_action('item_direction_edit', 'item_direction_sumcurs');
add_action('item_direction_add', 'item_direction_sumcurs');
function item_direction_sumcurs($item_id) { 
	global $wpdb;
	
	$wpdb->query("UPDATE " . $wpdb->prefix . "constructs SET item_id = '$item_id' WHERE itemtype = 'coursebysum' AND item_id = '0'");
	
}

add_filter('db_constructs_itemtype', 'db_constructs_itemtype_coursebysum', 10, 2);
function db_constructs_itemtype_coursebysum($name, $ind) {
	
	if ('coursebysum' == $ind) {
		$name = 'coursebysum';
	}
	
	return $name;
}

add_filter('db_constructs_access', 'db_constructs_access_coursebysum', 10, 2);
function db_constructs_access_coursebysum($name, $ind) {
	
	if ('coursebysum' == $ind) {
		$name = 'pn_directions';
	}
	
	return $name;
}
 
add_filter('db_constructs_scheme', 'coursebysum_constructs_scheme', 10, 2);
function coursebysum_constructs_scheme($array, $ind) {
	global $premiumbox;	
	
	if ('coursebysum' == $ind) {
		
		$pers = '';
		$what = intval($premiumbox->get_option('sumcurs', 'what'));
		if (1 == $what) {
			$pers = ' (%)';
		}		
		
		$array = array(
			'rate' => array(
				'name' => 'amount',
				'title' => __('Amount', 'pn'),
				'type' => 'input',
			),
			'course1' => array(
				'name' => 'course1',
				'title' => __('Send', 'pn') . $pers,
				'type' => 'input',
			),
			'course2' => array(
				'name' => 'course2',
				'title' => __('Receive', 'pn') . $pers,
				'type' => 'input',
			),			
			'actions' => array(
				'type' => 'actions',
			),			
		);
		
	}
	
	return $array;
}

add_action('tab_direction_tab2', 'tab_direction_tab2_sumcurs', 11); 
function tab_direction_tab2_sumcurs($data) {
	
	$data_id = intval(is_isset($data, 'id'));
	$form = new PremiumForm();
	if ($data_id > 0) {
	?>
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<div class="add_tabs_sublabel"><span><?php _e('Rate is depends on exchange amount', 'pn'); ?></span></div>
			<?php echo the_constructs_html($data_id, 'coursebysum'); ?>
		</div>
	</div>	
	<div class="add_tabs_line">
		<div class="add_tabs_single long">
			<?php $form->help(__('More info', 'pn'), __('Set a the lower amount of exchange in field "Amount". Then set a currency rate for Giving and Receiving. If the user wants to send you the specified amount then the rate will be the same you previously set.', 'pn')); ?>
		</div>
	</div>			
	<?php 
	}
}

add_filter('get_calc_data', 'get_calc_data_sumcurs', 80, 2);
function get_calc_data_sumcurs($cdata, $calc_data) {
	global $wpdb, $premiumbox;
	
	$direction = $calc_data['direction'];
	$direction_id = $direction->id;
	$post_sum = is_sum(is_isset($calc_data, 'post_sum'), 20);
	
	$set_course = intval(is_isset($calc_data, 'set_course'));
	if (1 != $set_course) {
		$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "constructs WHERE itemtype = 'coursebysum' AND item_id = '$direction_id'");
		if ($cc > 0) {	
			$cdata['dis1c'] = 1;
			$cdata['dis2'] = 1;	
			$cdata['dis2c'] = 1;
			
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "constructs WHERE itemtype = 'coursebysum' AND item_id = '$direction_id' AND ('$post_sum' -0.0) >= amount ORDER BY (amount -0.0) DESC");
			if (isset($data->id)) {
				$course_give = $cdata['course_give'];
				$course_get = $cdata['course_get'];
					
				$decimal_give = $cdata['decimal_give'];
				$decimal_get = $cdata['decimal_get'];
				
				$options = pn_json_decode($data->itemsettings);
					
				$what = intval($premiumbox->get_option('sumcurs', 'what'));
				if (0 == $what) {
					$cdata['course_give'] = is_sum(is_isset($options, 'course1'), $decimal_give);
					$cdata['course_get'] = is_sum(is_isset($options, 'course2'), $decimal_get);
				} elseif (0 != $course_give and 0 != $course_get) {
					$c1 = is_sum(is_isset($options, 'course1'), 20);
					$c2 = is_sum(is_isset($options, 'course2'), 20);
					$one_pers1 = $course_give / 100;
					$cdata['course_give'] = is_sum($course_give + ($one_pers1 * $c1), $decimal_give);
					$one_pers2 = $course_get / 100;
					$cdata['course_get'] = is_sum($course_get + ($one_pers2 * $c2), $decimal_get);
				}
			}
		}
	}
	
	return $cdata;
}	

add_action('admin_menu', 'admin_menu_sumcurs');
function admin_menu_sumcurs() {
	global $premiumbox;	
	
	if (current_user_can('administrator')) {
		add_submenu_page("pn_moduls", __('Rate is depends on exchange amount', 'pn'), __('Rate is depends on exchange amount', 'pn'), 'read', "pn_sumcurs", array($premiumbox, 'admin_temp'));
	}
	
}

add_filter('pn_adminpage_title_pn_sumcurs', 'def_adminpage_title_pn_sumcurs');
function def_adminpage_title_pn_sumcurs($page) {
	
	return __('Rate is depends on exchange amount', 'pn');
} 

add_action('pn_adminpage_content_pn_sumcurs', 'def_adminpage_content_pn_sumcurs');
function def_adminpage_content_pn_sumcurs() {
	global $premiumbox;

	$form = new PremiumForm();
	
	$options = array();
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => '',
		'submit' => __('Save', 'pn'),
	);	
	$options['what'] = array(
		'view' => 'select',
		'title' => __('Method of exchange rate formation', 'pn'),
		'options' => array('0' => __('specify the exchange rate directly', 'pn'), '1' => __('add interest to the exchange rate', 'pn')),
		'default' => $premiumbox->get_option('sumcurs', 'what'),
		'name' => 'what',
	);	
	$options['xmlshow'] = array(
		'view' => 'select',
		'title' => __('Use in XML 2.0 file', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('sumcurs', 'xmlshow'),
		'name' => 'xmlshow',
	);
	$params_form = array(
		'filter' => 'pn_sumcurs_options',
		'button_title' => __('Save', 'pn'),
	);
	$form->init_form($params_form, $options);
	
}  

add_action('premium_action_pn_sumcurs', 'def_premium_action_pn_sumcurs');
function def_premium_action_pn_sumcurs() {
	global $premiumbox;	
	
	_method('post');
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));
	
	$premiumbox->update_option('sumcurs', 'what', intval(is_param_post('what')));	
	$premiumbox->update_option('sumcurs', 'xmlshow', intval(is_param_post('xmlshow')));	

	$url = admin_url('admin.php?page=pn_sumcurs&reply=true');
	$form->answer_form($url);
	
}   

add_action('item_xml_line', 'sumcurs_item_xml_line', 10, 2);
function sumcurs_item_xml_line($data, $type) {
	global $premiumbox, $wpdb;
	
	if ('new' == $type) {
		$xmlshow = intval($premiumbox->get_option('sumcurs', 'xmlshow'));
		$what = intval($premiumbox->get_option('sumcurs', 'what'));
		if ($xmlshow) {
			
			$max_amount = is_sum($data['amount']);
			$decimal_give = intval($data['d1']);
			$decimal_get = intval($data['d2']);	
			$course_give = $def_course_give = intval($data['in']);
			$course_get = $def_course_get = intval($data['out']);				
			$direction = $data['dir'];
			
			$direction_id = $direction['id'];
			
			if ($max_amount > 0) {
				$n_rates = array();
				$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "constructs WHERE itemtype = 'coursebysum' AND item_id = '$direction_id' ORDER BY (amount -0.0) ASC");
				$count = count($items);
				$r = 0;
				foreach ($items as $item) { $r++;
					$options = pn_json_decode($item->itemsettings);
					if (0 == $what) {
						$course_give = is_sum(is_isset($options, 'course1'), $decimal_give);
						$course_get = is_sum(is_isset($options, 'course2'), $decimal_get);
					} elseif (0 != $def_course_give and 0 != $def_course_get) {
						$c1 = is_sum(is_isset($options, 'course1'), 20);
						$c2 = is_sum(is_isset($options, 'course2'), 20);
						$one_pers1 = $def_course_give / 100;
						$course_give = is_sum($def_course_give + ($one_pers1 * $c1), $decimal_give);
						$one_pers2 = $def_course_get / 100;
						$course_get = is_sum($def_course_get + ($one_pers2 * $c2), $decimal_get);
					}					
					$n_rates[$r] = array(
						'min' => is_sum($item->amount),
						'c1' => $course_give,
						'c2' => $course_get,
					);
				}
				if (count($n_rates) > 0) {
					foreach ($n_rates as $k => $n_rate) {
						$n_key = $k + 1;
						$max = $max_amount;
						if (isset($n_rates[$n_key])) {
							$max = $n_rates[$n_key]['min'];
						}
						?>
						<step frommin="<?php echo $n_rate['min']; ?>" frommax="<?php echo $max; ?>">
							<in><?php echo $n_rate['c1']; ?></in>
							<out><?php echo $n_rate['c2']; ?></out>
						</step>
						<?php
					}
				}
			}	
		}
	}
}