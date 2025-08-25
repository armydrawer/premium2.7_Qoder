<?php
if (!defined('ABSPATH')) { exit(); } 

add_filter('pn_txtxml_option','statuswork_pn_txtxml_option');
function statuswork_pn_txtxml_option($options) { 
	global $premiumbox;

	$options['line_oper'] = array(
		'view' => 'line',
	);	
	$options['oper'] = array(
		'view' => 'select',
		'title' => __('Disable manual exchange directions where operator offline', 'pn'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'default' => $premiumbox->get_option('txtxml', 'oper'),
		'name' => 'oper',
	);

	return $options;
}

add_action('pn_txtxml_option_post', 'statuswork_pn_txtxml_option_post');
function statuswork_pn_txtxml_option_post($options) {
	global $premiumbox;

	$val = intval(is_param_post('oper'));
	$premiumbox->update_option('txtxml', 'oper', $val);

}

add_filter('file_xml_directions', 'statuswork_file_xml_directions', 100); 
function statuswork_file_xml_directions($directions) {
	global $premiumbox;
	
	$oper = intval($premiumbox->get_option('txtxml','oper'));
	if ($oper) {
		$operator = get_operator_status();
		foreach ($directions as $id => $line) {
			if (1 != $operator and isset($line['param']) and strstr($line['param'], 'manual')) {
				unset($directions[$id]);
			}			
		}
	}
	
	return $directions;
}

add_filter('all_noticehead_addform', 'statuswork_all_noticehead_addform', 10, 2);
function statuswork_all_noticehead_addform($options, $data) {
	
	$statused = array();
	$statused['-1'] = '--' . __('Any status','pn') . '--';
	$status_operator = _operator_status_list();
	if (is_array($status_operator)) {
		foreach ($status_operator as $key => $val) {
			$statused[$key] = $val;
		}
	}
	$array = array(
		'op_status' => array( 
			'view' => 'select',
			'title' => __('Status of operator', 'pn'),
			'options' => $statused,
			'default' => is_isset($data, 'op_status'),
			'name' => 'op_status',
		),
	);	
	
	$options = pn_array_insert($options, 'datetime', $array);
	
	return $options;
}
  
add_filter('all_noticeheader_addform_post', 'statuswork_all_noticeheader_addform_post', 10, 2);
function statuswork_all_noticeheader_addform_post($array, $last_data) {
	
	$array['op_status'] = intval(is_param_post('op_status'));
	
	return $array;
}

add_filter('get_noticehead_status', 'statuswork_get_noticehead_status', 10, 2);
function statuswork_get_noticehead_status($show, $item) {
	
	if ($show) {
		$op_status = intval($item->op_status);
		$operator = get_operator_status();
		$show = 0;
		if ($op_status == $operator or '-1' == $op_status) {
			$show = 1;
		}
	}
	
	return $show;
}

add_action('wp_dashboard_setup', 'statuswork_wp_dashboard_setup');
function statuswork_wp_dashboard_setup() {
	global $premiumbox;	
	
	if (0 == intval($premiumbox->get_option('operator_type'))) {
		wp_add_dashboard_widget('statuswork_dashboard_widget', __('Work status', 'pn'), 'statuswork_dashboard_widget_function');
	}
}

function statuswork_dashboard_widget_function() {
	global $premiumbox;	
	
	$status_operator = _operator_status_list();
	$operator = $premiumbox->get_option('operator');
?>
<select id="statuswork" name="statuswork" autocomplete="off">
	<?php 
	if (is_array($status_operator)) {
		foreach ($status_operator as $key => $title) { 
		?>
			<option value="<?php echo $key; ?>" <?php selected($operator, $key); ?>><?php echo $title; ?></option>
		<?php 
		}
	}
	?>
</select>
<?php
}

add_action('admin_footer', 'statuswork_adminpage_js_dashboard');
function statuswork_adminpage_js_dashboard() {
	global $premiumbox;	
	
	if (0 == intval($premiumbox->get_option('operator_type'))) {
		$screen = get_current_screen();
		$base = is_isset($screen, 'base');
		if ('dashboard' == $base) {
?>
<script type="text/javascript">
jQuery(function($) {
	
    $(document).on('change', '#statuswork', function() { 
		var id = $(this).val();
		var param = 'id=' + id;
		$('#statuswork').prop('disabled', true);
        $.ajax({
			type: "POST",
			url: "<?php the_pn_link('statuswork_change', 'post'); ?>",
			data: param,
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{
				$('#statuswork').prop('disabled', false);				
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

add_action('premium_action_statuswork_change', 'pn_premium_action_statuswork_change');
function pn_premium_action_statuswork_change() {
	global $premiumbox;	

	_method('post');
	
	if (current_user_can('read') and 0 == intval($premiumbox->get_option('operator_type'))) {	
		$id = intval(is_param_post('id'));
		$premiumbox->update_option('operator', '', $id);	
	}
} 

add_filter('globalajax_admin_data', 'operator_globalajax_data');
add_filter('globalajax_site_data', 'operator_globalajax_data');
function operator_globalajax_data($log) {
	global $premiumbox;		

	if (1 == intval($premiumbox->get_option('operator_type'))) {
		if (current_user_cans('administrator, pn_bids')) {
			update_option('operator_time', current_time('timestamp'));
		}
	}
	
	return $log;
}
 
add_action('wp_footer', 'statuswork_wp_footer');
function statuswork_wp_footer() {
	global $premiumbox;	
	
	$show = intval($premiumbox->get_option('statuswork', 'show_button'));
	if ($show > 0) {
		$operator = get_operator_status();
		$status = 'status_op' . $operator;
		$list = _operator_status_list();
		
		$text = pn_strip_input(ctv_ml($premiumbox->get_option('statuswork', 'text' . $operator)));
		if (strlen($text) < 1) { $text = is_isset($list, $operator); }
		$link = pn_strip_input(ctv_ml($premiumbox->get_option('statuswork','link' . $operator)));

		$style = 'toright';
		if (2 == $show) {
			$style = 'toleft';
		}
		
		$date_format = get_option('date_format');
		$time_format = get_option('time_format');
		$date = current_time("{$date_format}, {$time_format}");
		$date = apply_filters('statuswork_now_date', $date);
?>
<?php if ($link) { ?>
	<a href="<?php echo $link; ?>" class="statuswork_div <?php echo $status; ?> <?php echo $style; ?>">
<?php } else { ?>
	<div class="statuswork_div <?php echo $status; ?> <?php echo $style; ?>">
<?php } ?>
	<div class="statuswork_div_ins">
		<div class="statuswork">
			<div class="statuswork_ins">
				<div class="statuswork_title"><span><?php echo $text; ?></span></div>
				<div class="statuswork_date"><span><?php echo $date; ?></span></div>
			</div>	
		</div>
	</div>
<?php if ($link) { ?>
	</a>
<?php } else { ?>
	</div>
<?php } ?>
<?php
	}
}