<?php
if (!defined('ABSPATH')) { exit(); }

add_action('premium_request_displayrating_bestchange', 'displayrating_bestchange');
function displayrating_bestchange() {	

	if (current_user_can('administrator') or current_user_can('pn_bestchange')) {	
		global $wpdb, $premiumbox;
		
		header('Content-Type: text/html; charset=' . get_charset());
		status_header(200);
		
		if (function_exists('download_data_bestchange')) {
			download_data_bestchange($premiumbox->get_option('bcbroker', 'server'), $premiumbox->get_option('bcbroker', 'timeout'), $premiumbox->get_option('bcbroker', 'type'));
		}	
		 
		$min_res = is_sum(is_param_get('minres'));
		
		$alls = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bestchange_currency_codes ORDER BY currency_code_title ASC");
		$vs[0] = '--' . __('No', 'pn') . '--';
		foreach ($alls as $all) {
			$vs[$all->currency_code_id] = pn_strip_input($all->currency_code_title);
		}
		$alls = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bestchange_cities ORDER BY city_title ASC");
		$cities[0] = '';
		foreach ($alls as $all) {
			$cities[$all->city_id] = '(' . pn_strip_input($all->city_title) . ')';
		}		
		
		$v1 = intval(is_param_get('v1'));
		$v2 = intval(is_param_get('v2'));
		$city_id = intval(is_param_get('city_id'));	
		
		$items = get_bestchange_rates($v1, $v2, $city_id, $min_res);

		$float = 'l';
		$adv_step = 0;
		
		$float_course = intval(is_param_get('float_course'));
		if (1 == $float_course) {
			$float = 'l';
		} elseif (2 == $float_course) {
			$float = 'r';
		} elseif (0 == $float_course) {
			$float = float_bc_rates($items);	
		}		
		
		$items = prepare_bc_rates($items, $float, '');
		
		if (3 != $float_course) {
			$adv_step = get_adv_step($items, $float);
			$security_step = '';
			
			$options = get_option('bestchange');
			if (!is_array($options)) { $options = array(); }
			$disable_security = intval(is_isset($options, 'secury'));
			if (1 != $disable_security) {
				
				$indicator = apply_filters('indicator_position_from_adv_step', 2);
				if ('l' == $float) {
					$max_rate = 0;
					if (isset($items[0]['ns2'])) {
						$max_rate = $items[0]['ns2'] + is_sum($adv_step * $indicator);
					}
					$security_step = ', ' . __('security max rate', 'pn') . ': <span>' . $max_rate . '</span>';			
				} else {
					$max_rate = 0;
					if (isset($items[0]['ns1'])) {
						$max_rate = $items[0]['ns1'] + is_sum($adv_step * $indicator);
					}
					$security_step = ', ' . __('security min rate','pn') . ': <span>' . $max_rate . '</span>';		
				}
				$max_rate = is_sum($max_rate);
				
			}
			
			if ($adv_step < 0) { $adv_step = -1 * $adv_step; }
		}
		
		$title = is_isset($vs, $v1) . ' - ' . is_isset($vs, $v2) . ' '. is_isset($cities, $city_id);
		
		$plugin = get_plugin_class();
		$plugin_url = $plugin->plugin_url;
		?>
<!DOCTYPE html>
<html <?php echo get_language_attributes(); ?>>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<meta name="HandheldFriendly" content="True" />
	<meta name="MobileOptimized" content="320" />
	<meta name="format-detection" content="telephone=no" />
	<meta name="PalmComputingPlatform" content="true" />
	<meta name="apple-touch-fullscreen" content="yes"/>
	<meta charset="<?php bloginfo('charset'); ?>">

	<title><?php echo $title; ?></title>
	
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
	<link rel='stylesheet' href='<?php echo $plugin_url; ?>moduls/bestchange/style.css?ver=<?php echo time(); ?>' type='text/css' media='all' />	
</head>
<body <?php body_class(); ?>>
<div class="container">
	<div class="table_wrap">
		<div class="table_inner">
			<div class="table_title"><?php echo $title; ?></div>
			<?php if ($adv_step > 0) { ?>
				<div class="table_recommend"><?php _e('Recommended step', 'pn'); ?>: <span><?php echo $adv_step; ?></span><?php echo $security_step; ?></div>
			<?php } ?>
			<div class="table_select">
				<?php
				$url = get_request_link('displayrating_bestchange', 'html') . '?v1=' . $v1 . '&v2=' . $v2 . '&city_id=' . $city_id . '&minres=' . $min_res . '&float_course=';
				?>
				<select name="" onchange="location = this.options[this.selectedIndex].value;" autocomplete="off">
					<option value="<?php echo $url; ?>0" <?php selected(0, $float_course); ?>>auto</option>
					<option value="<?php echo $url; ?>1" <?php selected(1, $float_course); ?>>1 = XXX</option>
					<option value="<?php echo $url; ?>2" <?php selected(2, $float_course); ?>>XXX = 1</option>
					<option value="<?php echo $url; ?>3" <?php selected(3, $float_course); ?>>bestchange</option>
				</select>
			</div>
			
			<div class="table">
				<table>
					<thead>
						<tr>
							<th style="width: 5px;"></th>
							<th><?php _e('Exchanger', 'pn'); ?></th>
							<th><?php _e('Give', 'pn'); ?> <span><?php echo is_isset($vs, $v1); ?></span></th>
							<th><?php _e('Get', 'pn'); ?> <span><?php echo is_isset($vs, $v2); ?></span></th>
							<th><?php _e('Step', 'pn'); ?></th>
							<th><?php _e('Reserve', 'pn'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$count = apply_filters('count_position_from_adv_step', 10);
						
						$last = '';
						$step = '-';
						$r = 0;
						foreach ($items as $item) { $r++;
							$class = array();
							if (0 == $r%2) { $class[] = 'odd'; }
							if ($r == $count + 1) { $class[] = 'adv'; }
						
							$course1 = $course2 = 0;
						
							if (3 == $float_course) {
								$course1 = $item['s1'];
								$course2 = $item['s2'];								
							} else {
								if ('l' == $float) {
									$course1 = $item['ns1'];
									$course2 = $item['ns2'];
									if (is_numeric($last)) {
										$step = is_sum($last - $course2, 12);
										$step = is_sum(-1 * $step, 12);
									}
									$last = $course2;
								} elseif ('r' == $float) {
									$course1 = $item['ns1'];
									$course2 = $item['ns2'];
									if (is_numeric($last)) {
										$step = is_sum($last - $course1, 12);
										$step = is_sum(-1 * $step, 12);
									}
									$last = $course1;
								}
							}								
						?>
							<tr class="<?php echo implode(' ', $class); ?>">
								<td><?php echo $r; ?>.</td>
								<td><?php echo is_isset($item, 'ex'); ?> (<?php echo is_isset($item, 'idex'); ?>)</td>
								<td><?php echo $course1; ?></td>
								<td><?php echo $course2; ?></td>
								<td><?php echo get_sum_color($step); ?></td>
								<td><?php echo is_isset($item, 'res'); ?></td>
							</tr>
						<?php 
						} 
						?>
					</tbody>
				</table>
			</div>

		</div>
	</div>
</div>
</body>
</html>		
		<?php
		exit;
	}	 
} 