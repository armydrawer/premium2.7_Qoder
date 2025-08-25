<?php
if (!defined('ABSPATH')) { exit(); }

add_action('tab_direction_tab1', 'dgroups_tab_direction_tab1', 50, 2);
function dgroups_tab_direction_tab1($data, $data_id) {
	global $wpdb;
	
	$form = new PremiumForm();
?>	
	<div class="add_tabs_line">
		<div class="add_tabs_single">
			<div class="add_tabs_sublabel"><span><?php _e('Direction group', 'pn'); ?></span></div>
			
			<?php
			$group_id = intval(is_isset($data, 'group_id'));
			if (0 == $group_id) {
				$cl1 = '';
			} else {
				$cl1 = 'pn_hide';		
			}	

			$groups = get_dir_groups('--' . __('Add new', 'pn') . '--');
			
			$form->select_search('group_id', $groups, $group_id, array('class'=>'js_adhide_input', 'to_class' => 'thevib_group')); 
			?>	
			
			<div class="premium_wrap_standart thevib_group <?php echo $cl1; ?>">
				<input type="text" name="group_title" style="width: 100%;" value="" />
			</div>	
		</div>
	</div>
<?php		
}

add_filter('pn_direction_addform_post', 'dgroups_pn_direction_addform_post', 10);
function dgroups_pn_direction_addform_post($array) {
	global $wpdb;
	
	$form = new PremiumForm();
	
	$array['group_id'] = 0;
				
	$group_id = intval(is_param_post('group_id'));
	if ($group_id) {
		$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "dgroups WHERE id = '$group_id'");
		if (isset($data->id)) {
			$array['group_id'] = $data->id; 
		}
	} 
	
	$group_id = intval($array['group_id']);
	if (!$group_id) {
		$group_title = pn_strip_input(is_param_post('group_title'));
		if ($group_title) { 
			$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "dgroups WHERE title = '$group_title'");
			if (isset($data->id)) {
				$array['group_id'] = $data->id;
			} else {	
				$arr = array();
				$arr['title'] = $group_title;
				$result = $wpdb->insert($wpdb->prefix . 'dgroups', $arr);
				$array['group_id'] = $wpdb->insert_id;
				if (!$array['group_id']) {
					$res_errors = _debug_table_from_db($result, 'dgroups', $arr);
					_display_db_table_error($form, $res_errors);
				}
			}
		}
	}	
	
	return $array;
}

add_filter('pntable_columns_pn_directions', 'dgroups_pntable_columns_pn_directions', 1000);
function dgroups_pntable_columns_pn_directions($columns) {
	
	$columns['dgroups'] = __('Direction group', 'pn');
	
	return $columns;
}

add_filter('pntable_column_pn_directions', 'dgroups_pntable_column_pn_directions', 10, 3);
function dgroups_pntable_column_pn_directions($column, $column_name, $item) {
	global $wpdb, $pn_dgroups;
	
	if ('dgroups' == $column_name) {	
	
		$list = get_dir_groups(__('No group', 'pn'));
		
		return is_isset($list, $item->group_id);
	} 
	
	return $column;
}

add_filter('pntable_searchbox_pn_directions', 'dgroups_pntable_searchbox_pn_directions', 100);
add_filter('pntable_searchbox_pn_nasp_masseditor', 'dgroups_pntable_searchbox_pn_directions', 100);
function dgroups_pntable_searchbox_pn_directions($search) {

	$search['dgroups_line'] = array(
		'view' => 'line',
	);
	
	$lists = get_dir_groups(__('No group', 'pn'));
	$s = array();
	$s['0'] = '--' . __('All groups', 'pn') . '--';
	foreach ($lists as $list_k => $list_v) {
		$s[($list_k + 1)] = $list_v;
	}

	$search['group_id'] = array(
		'view' => 'select',
		'title' => __('Direction group', 'pn'),
		'default' => pn_strip_input(is_param_get('group_id')),
		'options' => $s,
		'name' => 'group_id',
	);	
	
	return $search;
}

add_filter("pntable_searchwhere_pn_directions", 'dgroups_pntable_searchwhere_pn_directions');
add_filter("pntable_searchwhere_pn_nasp_masseditor", 'dgroups_pntable_searchwhere_pn_directions');
function dgroups_pntable_searchwhere_pn_directions($where) {
	
	$group_id = intval(is_param_get('group_id'));
	if ($group_id > 0) {
		$group_id = $group_id - 1;
		$where .= " AND group_id = '$group_id'";
	}
	
	return $where;
}

add_action('wp_dashboard_setup', 'dgroups_wp_dashboard_setup');
function dgroups_wp_dashboard_setup() {
	global $premiumbox;	
	
	if (current_user_can('administrator') or current_user_can('pn_directions')) {
		wp_add_dashboard_widget('dgroups_dashboard_widget', __('Direction groups', 'pn'), 'dgroups_dashboard_widget_function');
	}
	
}

function dgroups_dashboard_widget_function() {
	global $wpdb, $premiumbox, $pn_dgroups_widget;
	
	$pn_dgroups_widget = 1;
 	$items = get_dir_groups('--' . __('Choice group', 'pn') . '--');
?>
<p>
<select id="dgroups_id" name="dgroups_id" autocomplete="off">
	<?php foreach ($items as $item_id => $item_title) { ?>
		<option value="<?php echo $item_id; ?>"><?php echo pn_strip_input($item_title); ?></option>
	<?php } ?>
</select>
</p>
<p>
<select id="dgroups_work" name="dgroups_work" style="display: none;" autocomplete="off">
	<option value="0">--<?php _e('Choice status', 'pn'); ?>--</option>
	<option value="1"><?php _e('inactive direction', 'pn'); ?></option>
	<option value="2"><?php _e('active direction', 'pn'); ?></option>
	<option value="3"><?php _e('frozen direction', 'pn'); ?></option>
</select>
</p>
<div id="dgroups_res"></div>
<?php
} 

add_action('admin_footer', 'dgroups_admin_footer');
function dgroups_admin_footer() {
	global $premiumbox, $pn_dgroups_widget;	
	
	$pn_dgroups_widget = intval($pn_dgroups_widget);
	if ($pn_dgroups_widget) {
?>
	<script type="text/javascript">
	jQuery(function($) {
		
		$(document).on('change', '#dgroups_id', function() { 
			var id = $(this).val();
			
			$('#dgroups_res').html('');
			
			if (id == 0) {
				$('#dgroups_work').hide();
			} else {
				$('#dgroups_work').show();
			}
			
			return false;
		});		
		
		$(document).on('change', '#dgroups_work', function() { 
		
			var id = $('#dgroups_id').val();
			var st = $(this).val();
			var param = 'id=' + id + '&st=' + st;
			$('#dgroups_work').prop('disabled', true);
			$('#dgroups_res').html('');
			
			$.ajax({
				type: "POST",
				url: "<?php the_pn_link('dgroups_workaction', 'post'); ?>",
				data: param,
				error: function(res, res2, res3) {
					<?php do_action('pn_js_error_response', 'ajax'); ?>
				},			
				success: function(res)
				{
					$('#dgroups_work').prop('disabled', false);	
					$('#dgroups_res').html('<div class="premium_reply pn_success"><?php _e('Ok!', 'pn'); ?></div>');
				}
			});
			
			return false;
		});
		
	});	
	</script>	
<?php	
	}
}

add_action('premium_action_dgroups_workaction', 'def_premium_action_dgroups_workaction');
function def_premium_action_dgroups_workaction() {
	global $premiumbox, $wpdb;	
	
	_method('post');
	
	if (current_user_can('administrator') or current_user_can('pn_directions')) {
		
		$id = intval(is_param_post('id'));
		$st = intval(is_param_post('st'));
		$st = $st - 1;
		if ($st < 0 or $st > 2) { $st = 0; }
		
		$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE group_id = '$id' AND auto_status = '1'");
		foreach ($items as $item) {
			
			$array = array();
			$array['direction_status'] = $st;				
			$result = $wpdb->update($wpdb->prefix . 'directions', $array, array('id' => $item->id));
					
			do_action('item_direction_save', $item->id, $item, $result, $array);

		}		
		
	}	
	
} 