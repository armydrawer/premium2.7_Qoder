<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_parsconv', 'def_adminpage_title_pn_parsconv');
	function def_adminpage_title_pn_parsconv() {
		
		return __('Converter', 'pn');
	}

 	add_action('pn_adminpage_content_pn_parsconv', 'def_adminpage_content_pn_parsconv');
	function def_adminpage_content_pn_parsconv() {
		global $wpdb;
	
		$lists = array();
		$lists[''] = '--' . __('All', 'pn') . '--';
		$birgs = apply_filters('new_parser_links', array());
		foreach ($birgs as $birg) {
			$lists[is_isset($birg, 'birg_key')] = is_isset($birg, 'title');
		}
	?>
	<form action="<?php the_pn_link('parsconv_form', 'post'); ?>" class="finstats_form" method="post">
		<div class="finfiletrs">
					
			<div class="fin_list">
				<div class="fin_label"><?php _e('Source', 'pn'); ?></div>
				<select name="source" autocomplete="off">
					<?php
					foreach ($lists as $b_key => $b_title) { 
					?>
					<option value="<?php echo $b_key; ?>"><?php echo $b_title; ?></option>
					<?php
					}
					?>
				</select>
			</div>
			
			<div class="fin_list">
				<div class="fin_label"><?php _e('Currency', 'pn'); ?></div>
				<input type="text" name="currency" value="" />
			</div>			

				<div class="premium_clear"></div>

			<div class="fin_line"><label><input type="checkbox" name="reverse_course" autocomplete="off" value="1" /> <?php _e('reverse course', 'pn'); ?></label></div>		
			
				<div class="premium_clear"></div>		
				
			<input type="submit" name="submit" class="finstat_link" value="<?php _e('Apply', 'pn'); ?>" />
			<div class="finstat_ajax"></div>
				
				<div class="premium_clear"></div>
		</div>
	</form>

	<div id="finres"></div>

	<script type="text/javascript">
	jQuery(function($) {
		
		$('.finstats_form').ajaxForm({
			dataType:  'json',
			beforeSubmit: function(a, f, o) {
				$('.finstat_link').prop('disabled', true);
				$('.finstat_ajax').show();
			},
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response'); ?>
			},		
			success: function(res) {
				
				$('.finstat_link').prop('disabled', false);
				$('.finstat_ajax').hide();
				
				if (res['status'] == 'error') {
					<?php do_action('pn_js_alert_response'); ?>
				} else if (res['status'] == 'success') {
					$('#finres').html(res['table']);
				}
			}
		});
		
	});
	</script>	
		
	<?php
	} 

}

add_action('premium_action_parsconv_form', 'pn_premium_action_parsconv_form');
function pn_premium_action_parsconv_form() {
	global $wpdb;

	_method('post');
	_json_head();	

	$log = array();
	$log['status'] = 'success';
	$log['status_code'] = 0; 
	$log['status_text'] = '';	
		
	if (current_user_can('administrator') or current_user_can('pn_directions') or current_user_can('pn_parser')) {
			
		$s_birg = pn_strip_input(is_param_post('source'));
		$reverse_course = intval(is_param_post('reverse_course'));
		$currency = trim(is_param_post('currency'));
		$currency = strtoupper($currency);
		$curr_arr = explode(',', $currency);
		$curr = array_map('trim', $curr_arr);
			
		$birgs_list = array();
		$birgs = apply_filters('new_parser_links', array());
		foreach ($birgs as $birg) {
			$birgs_list[is_isset($birg, 'birg_key')] = is_isset($birg, 'title');
		}			
			
		$parser_pairs = get_parser_pairs();
		$items = array();
		foreach ($parser_pairs as $pi_key => $pi_value) {
			$birg = trim(is_isset($pi_value, 'birg'));

			$return = 1;
				
			if ($s_birg and $s_birg != $birg) {
				$return = 0;
			}	
				
			if (1 == $return) {
				$items[$pi_key] = $pi_value;
				$items[$pi_key]['code'] = $pi_key;
			}
		}		

		$courses = array();

		foreach ($items as $item) {
				
			$title = trim(is_isset($item, 'title'));
			$t = '';
			if ($title) {
				$t = ' ('. $title .')';
			}
				
			$give = strtoupper(pn_strip_input(is_isset($item, 'give')));
			$get = strtoupper(pn_strip_input(is_isset($item, 'get')));
			if (1 == count($curr) or in_array($give, $curr) and in_array($get, $curr)) {
				
				$courses[$give . '_' . $get . '_' . pn_strip_input(is_isset($birgs_list, is_isset($item, 'birg')))] = array(
					'g1' => $give,
					'g2' => $get,
					'b' => pn_strip_input(is_isset($birgs_list, is_isset($item, 'birg'))),
					'c1' => 1,
					'c2' => '[' . is_isset($item, 'code') . ']',
				);
					
				if ($reverse_course) {
						
					$courses[$get . '_' . $give . '_' . pn_strip_input(is_isset($birgs_list, is_isset($item, 'birg')))] = array(
						'g1' => $get,
						'g2' => $give,
						'b' => pn_strip_input(is_isset($birgs_list, is_isset($item, 'birg'))),
						'c1' => '[' . is_isset($item, 'code') . ']',
						'c2' => 1,
					);					
										
				}
				
			}
		}
			
		asort($courses);
			
		foreach ($courses as $c) {
				
			$array = array();
			$array['title_birg'] = is_isset($c, 'b');
			$array['title_pair_give'] = is_isset($c, 'g1');
			$array['title_pair_get'] = is_isset($c, 'g2');
			$array['pair_give'] = is_isset($c, 'c1');
			$array['pair_get'] = is_isset($c, 'c2');
			$wpdb->insert($wpdb->prefix . 'parser_pairs', $array);	
					
		}
			
		$log['table'] = '<div class="finresults"><strong>' . __('Ok', 'pn') . '</strong></div>';
			
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('You do not have permission', 'pn');
	}	
		
	echo pn_json_encode($log);	
	exit;
} 	