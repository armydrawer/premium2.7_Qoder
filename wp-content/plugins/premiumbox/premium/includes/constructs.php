<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('item_direction_copy_constructs')) {
	add_action('item_direction_copy', 'item_direction_copy_constructs', 1, 2);
	function item_direction_copy_constructs($last_id, $new_id) {
		global $wpdb;
		
		$naps_meta = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "constructs WHERE item_id = '$last_id'"); 
		foreach ($naps_meta as $nap) {
			$arr = array();
			if ('id' != $k) {
				foreach($nap as $k => $v) {
					if ('item_id' == $k) {
						$arr[$k] = $new_id;
					} else {
						$arr[$k] = is_sum($v);
					}
				}
			}
			$wpdb->insert($wpdb->prefix . 'constructs', $arr);
		}
	}
}
 
if (!function_exists('the_constructs_html')) {
	function the_constructs_html($data_id, $ind) {
		global $constucts_js;
		
		if ($data_id) {
			$constucts_js = 1;
	?>
		<div class="construct_html <?php echo $ind; ?>_html" data-name="<?php echo $ind; ?>" data-id="<?php echo $data_id; ?>">
			<?php echo get_constructs_html($data_id, $ind); ?>
		</div>
	<?php
		}
	}
}

if (!function_exists('get_constructs_html')) {
	function get_constructs_html($data_id, $ind) {
		global $wpdb;	
		
		$db_constructs_table = apply_filters('db_constructs_itemtype', '', $ind);
		$temp = '';
		$scheme = apply_filters('db_constructs_scheme', array(), $ind);
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "constructs WHERE item_id = '$data_id' AND itemtype = '$db_constructs_table' ORDER BY (amount -0.0) ASC"); 
		foreach ($items as $item) {
			$options = pn_json_decode($item->itemsettings);
			$temp .= get_constructs_line($item, $data_id, $ind, $scheme, $options);
		}
		$temp .= get_constructs_line('', $data_id, $ind, $scheme, array());
		
		return $temp;
	}
}

if (!function_exists('get_constructs_line')) {
	function get_constructs_line($item, $data_id, $ind, $scheme, $options) {
		
		$temp = '<div class="construct_line js_construct_line" data-id="' . intval(is_isset($item, 'id')) . '">';
		if (is_array($scheme)) {
			foreach ($scheme as $sch_name => $data) {
				$type = trim(is_isset($data, 'type'));
				$name = trim(is_isset($data, 'name'));
				$title = trim(is_isset($data, 'title'));
				$after = trim(is_isset($data, 'after'));
				
				if ('input' == $type) {
					
					$temp .= '
					<div class="construct_item">';
						if (strlen($title) > 0) {
							$temp .= '
							<div class="construct_title">
								'. $title .'
							</div>
							';
						}
						
						$value = '';
						if ('amount' == $name) {
							$value = is_isset($item, $name);
						} else {
							$value = is_isset($options, $name);
						}
						
						$temp .= '
						<div class="construct_input">
							<input type="text" name="" style="width: 100px;" class="js_construct_elem" data-name="' . $name . '" value="' . is_sum($value) . '" /> ' . $after . '
						</div>
					</div>';	
					
				} elseif ('clear' == $type) {
					
					$temp .= '<div class="premium_clear"></div>';
				
				} elseif ('title' == $type) {			
					
					$temp .= '
					<div class="construct_line_title">
						'. $title .'
					</div>';
					
				} elseif ('actions' == $type) {	
					
					if (isset($item->id)) {
						$temp .= '<div class="construct_add js_construct_add">' . __('Save', 'premium') . '</div>';
						$temp .= '<div class="construct_del js_construct_del">' . __('Delete', 'premium') . '</div>';
					} else {
						$temp .= '<div class="construct_add js_construct_add">' . __('Add new', 'premium') . '</div>';
					}
			
					$temp .= '<div class="premium_clear"></div>';			
					
				}
				
			}
		}
		$temp .= '<div class="premium_clear"></div></div>';
		
		return $temp;
	}
}

if (!function_exists('constructs_admin_footer')) {
	add_action('admin_footer', 'constructs_admin_footer');
	function constructs_admin_footer() {
		global $constucts_js;
		
		$constucts_js = intval($constucts_js);
		if ($constucts_js) {		
		?>
<script type="text/javascript">
jQuery(function($) {
	
	$(document).on('click', '.js_construct_add', function() { 
		var par_div = $(this).parents('.construct_html');
		var data_id = par_div.attr('data-id');
		var data_name = par_div.attr('data-name');
		var par = $(this).parents('.js_construct_line');
		var item_id = parseInt(par.attr('data-id'));
		var param_other = '';
		
		par_div.find('input').attr('disabled',true);
		par_div.find('.js_construct_add, .js_construct_del').addClass('active');
		
		par.find('.js_construct_elem').each(function() {
			param_other += '&' + $(this).attr('data-name') + '=' + $(this).val();
		});
		
		var param = 'data_id=' + data_id + '&data_name=' + data_name + '&item_id=' + item_id + param_other;

		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('constructs_ajax_add', 'post');?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{		
				if (res['html']) {
					par_div.html(res['html']);
				} 
			}
		});		
		
		return false;
	});	
	
	$(document).on('click', '.js_construct_del', function() { 
		var par_div = $(this).parents('.construct_html');
		var data_id = par_div.attr('data-id');
		var data_name = par_div.attr('data-name');
		var par = $(this).parents('.js_construct_line');
		var item_id = parseInt(par.attr('data-id'));
		
		par_div.find('input').attr('disabled',true);
		par_div.find('.js_construct_add, .js_construct_del').addClass('active');
		
		var param = 'data_id=' + data_id + '&data_name=' + data_name + '&item_id=' + item_id;

		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('constructs_ajax_del', 'post');?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{		
				if (res['html']) {
					par_div.html(res['html']);
				} 
			}
		});		
		
		return false;
	});	
	
});
</script>
		<?php
		}
	}
}

if (!function_exists('def_premium_action_constructs_ajax_add')) {
	add_action('premium_action_constructs_ajax_add', 'def_premium_action_constructs_ajax_add');
	function def_premium_action_constructs_ajax_add() {
		global $wpdb;

		_method('post');
		_json_head();
		
		$log = array();
		$log['status'] = 'success';	
		
		$data_id = trim(is_param_post('data_id'));
		$data_name = pn_strip_symbols(is_param_post('data_name'));
		
		$db_constructs_access = apply_filters('db_constructs_access', '', $data_name);
		$db_constructs_table = apply_filters('db_constructs_itemtype', '', $data_name);
		$scheme = apply_filters('db_constructs_scheme', array(), $data_name);
		if ($db_constructs_access) {
			if (current_user_can('administrator') or current_user_can($db_constructs_access)) {
				$item_id = intval(is_param_post('item_id'));
				
				$options = array();
				if (is_array($scheme)) {
					foreach ($scheme as $sch_name => $data) {
						$type = trim(is_isset($data, 'type'));
						$name = trim(is_isset($data, 'name'));
						if ('input' == $type and 'amount' != $name) {
							$options[$name] = is_sum(is_param_post($name));
						}
					}
				}
				$options = pn_json_encode($options);
				
				$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "constructs WHERE id = '$item_id' AND item_id = '$data_id' AND itemtype = '$db_constructs_table'");
						
				$array = array();
				$array['item_id'] = $data_id;
				$array['amount'] = is_sum(is_param_post('amount'));
				$array['itemsettings'] = $options;
				$array['itemtype'] = $db_constructs_table;
						
				if (isset($item->id)) {
					$wpdb->update($wpdb->prefix . 'constructs', $array, array('id' => $item->id));
				} else {
					$wpdb->insert($wpdb->prefix . 'constructs', $array);
				}
						
				$log['html'] = get_constructs_html($data_id, $data_name);
			} 
		}
		
		echo pn_json_encode($log);
		exit;
	}
}

if (!function_exists('def_premium_action_constructs_ajax_del')) {
	add_action('premium_action_constructs_ajax_del', 'def_premium_action_constructs_ajax_del');
	function def_premium_action_constructs_ajax_del() {
		global $wpdb;

		_method('post');
		_json_head();
		
		$log = array();
		$log['status'] = 'success';	
		
		$data_id = trim(is_param_post('data_id'));
		$data_name = pn_strip_symbols(is_param_post('data_name'));	
		
		$db_constructs_access = apply_filters('db_constructs_access', '', $data_name);
		$db_constructs_table = apply_filters('db_constructs_itemtype', '', $data_name);
		if ($db_constructs_access) {	
			if (current_user_can('administrator') or current_user_can($db_constructs_access)) {
				$item_id = intval(is_param_post('item_id'));	
				
				$wpdb->query("DELETE FROM " . $wpdb->prefix . "constructs WHERE id = '$item_id' AND itemtype = '$db_constructs_table'");

				$log['html'] = get_constructs_html($data_id, $data_name);
			} 
		}

		echo pn_json_encode($log);	
		exit;
	}
}