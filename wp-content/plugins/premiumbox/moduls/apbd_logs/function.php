<?php
if (!defined('ABSPATH')) { exit(); }

function insert_apbd($tbl_name, $id, $new_data, $last_data) {
	global $wpdb;

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	
	
	
	$n_data = array();
	$l_data = array();
	
	foreach ($new_data as $new_data_k => $new_data_v) {
		if ('edit_date' != $new_data_k and 'edit_user_id' != $new_data_k) {
			$d_old = pn_strip_input(is_isset($last_data, $new_data_k));
			$d_new = pn_strip_input($new_data_v);
			if ($d_old != $d_new) {
				$n_data[$new_data_k] = $d_new;
				$l_data[$new_data_k] = $d_old;
			}
		}
	}
	
	ksort($n_data);
	ksort($l_data);
	
	$sr_data = print_r($n_data, true);
	$sr_last_data = print_r($l_data, true);
	
	$data = pn_json_encode($n_data);
	$last_data = pn_json_encode($l_data);
	
	if ($sr_data != $sr_last_data) {
		
		$arr = array();
		$arr['tbl_name'] = $tbl_name;
		$arr['item_id'] = $id;
		$arr['trans_date'] = current_time('mysql');
		$arr['old_data'] = $last_data;
		$arr['new_data'] = $data;
		$arr['user_id'] = $user_id;
		$arr['user_login'] = is_isset($ui, 'user_login');
		$wpdb->insert($wpdb->prefix . 'db_admin_logs', $arr);
		
	}
	
}
 
function view_apbd($tbl_name) {
	global $wpdb;

 	$data_id = intval(is_param_get('item_id'));
	if ($data_id > 0 and current_user_can('administrator')) {
		$trans = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "db_admin_logs WHERE item_id = '$data_id' AND tbl_name = '$tbl_name' ORDER BY trans_date DESC");	
		if (count($trans) > 0) {
			?>
			<div class="apdb_wrap">
				<div style="overflow-y: auto; max-height: 300px;">
					<?php 
					foreach ($trans as $item) { 
						$old_data = pn_json_decode($item->old_data);
						$new_data = pn_json_decode($item->new_data);
					?>
						<div class="apdb_line">
							<strong><?php echo get_pn_time($item->trans_date, 'd.m.Y H:i'); ?></strong> | <a href="<?php echo pn_edit_user_link($item->user_id); ?>"><?php echo is_user($item->user_login); ?></a> |
							<?php
							$items = array();
							if (is_array($new_data)) {
								foreach ($new_data as $k => $v) {
									$items[] = '<strong>' . $k . ':</strong> ' . $v . ' <span class="bred">(' . is_isset($old_data, $k) . ')</span>';
								} 
							}
							echo implode(' | ', $items);
							?>
						</div>		
					<?php } ?>
				</div>
			</div>
			<?php
		}
	}	
}  