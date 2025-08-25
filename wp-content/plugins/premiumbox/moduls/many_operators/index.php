<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Live operator[:en_US][ru_RU:]Оператор live[:ru_RU]
description: [en_US:]Highlighting the request if operator processes it[:en_US][ru_RU:]Выделение заявки цветом, если с ней работает оператор[:ru_RU]
version: 2.7.0
category: [en_US:]Orders[:en_US][ru_RU:]Заявки[:ru_RU]
cat: req
*/

$path = get_extension_file(__FILE__);
$name = get_extension_name($path);

add_action('pn_plugin_activate', 'all_moduls_active_operworks');
add_action('all_moduls_active_' . $name, 'all_moduls_active_operworks');
function all_moduls_active_operworks() {
	global $wpdb;	
	
	$table_name = $wpdb->prefix . "bids_operators"; 
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`createdate` datetime NOT NULL,
		`user_id` bigint(20) NOT NULL default '0',
		`user_login` varchar(250) NOT NULL,
		`bid_id` bigint(20) NOT NULL default '0',
		PRIMARY KEY (`id`),
		INDEX (`createdate`),
		INDEX (`user_id`),
		INDEX (`bid_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET={$wpdb->charset} AUTO_INCREMENT=1;";
	$wpdb->query($sql);
	
	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'only_directions'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `only_directions` longtext NOT NULL");
    }

	$query = $wpdb->query("SHOW COLUMNS FROM " . $wpdb->prefix . "users LIKE 'only_merchants'");
    if (0 == $query) {
		$wpdb->query("ALTER TABLE " . $wpdb->prefix . "users ADD `only_merchants` longtext NOT NULL");
    }	
	
}

add_action('all_user_editform_post', 'operworks_all_user_editform_post');
function operworks_all_user_editform_post($new_user_data) {
	
	if (current_user_can('administrator')) { 
	
		$new_user_data['only_merchants'] = pn_strip_input(is_param_post('only_merchants'));
		$new_user_data['only_directions'] = pn_strip_input(is_param_post('only_directions'));
		
	}
	
	return $new_user_data;
}

add_filter('all_user_editform', 'operworks_all_user_editform', 300, 2);
function operworks_all_user_editform($options, $db_data) {
	global $premiumbox, $wpdb;
	
	$user_id = $db_data->ID;
	
	if (current_user_can('administrator')) { 
	
		$options['only_directions'] = array(
			'view' => 'textarea',
			'title' => __('Only directions (ID, separated by commas)', 'pn'),
			'default' => pn_strip_input($db_data->only_directions),
			'name' => 'only_directions',
			'rows' => '5',
			'work' => 'text',
		);
		$options['only_merchants'] = array(
			'view' => 'textarea',
			'title' => __('Only merchants (Key, separated by commas)', 'pn'),
			'default' => pn_strip_input($db_data->only_merchants),
			'name' => 'only_merchants',
			'rows' => '5',
			'work' => 'text',
		);		
		
	}
	
	return $options;
}

function operworks_create_data_for_db($value, $p) {
	
	$arr1 = $arr2 = array();
	
	$array = explode(',', pn_strip_input($value));
	foreach ($array as $arr) {
		$arr = trim($arr);
		if (strlen($arr) > 0) {
			$f = mb_substr($arr, 0, 1);
			if ('-' == $f) {
				$arr2[] = mb_substr($arr, 1, mb_strlen($arr));
			} else {
				$arr1[] = $arr;
			}
		}
	}
	
	$arr1 = array_unique($arr1);
	$arr2 = array_unique($arr2);

	$arr = array(
		'arr1' => create_data_for_db($arr1, $p),
		'arr2' => create_data_for_db($arr2, $p)
	);	
	
	return $arr;
}

add_filter('module_live_where', 'operworks_module_live_where', 100);
function operworks_module_live_where($where) {
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	
	if ($user_id) {
		
		$db1_arr = operworks_create_data_for_db(is_isset($ui, 'only_directions'), 'int');
		$db1 = $db1_arr['arr1'];
		$db1n = $db1_arr['arr2'];
		
		$db2_arr = operworks_create_data_for_db(is_isset($ui, 'only_merchants'), 'var');
		$db2 = $db2_arr['arr1'];
		$db2n = $db2_arr['arr2'];

		$where_arr = explode('OR', $where);
		$new_where_arr = array();
		$sql_where = '';

		$sql = array();

		if ($db1 and $db1n) {
			$sql[] = "direction_id IN ($db1) AND direction_id NOT IN ($db1n)";
		} elseif ($db1) {
			$sql[] = "direction_id IN ($db1)";
		} elseif ($db1n) {
			$sql[] = "direction_id NOT IN ($db1n)";
		}
		
		if ($db2 and $db2n) {
			$sql[] = "m_in IN ($db2) AND m_in NOT IN ($db2n)";
		} elseif ($db2) {
			$sql[] = "m_in IN ($db2)";
		} elseif ($db2n) {
			$sql[] = "m_in NOT IN ($db2n)";
		}			

		if (1 == count($sql)) {
			$sql_where = " AND " . implode('', $sql);
		} elseif (2 == count($sql)) {
			$sql_where = " AND (" . implode(' OR ', $sql) . ")";
		}
		
		foreach ($where_arr as $where_ar) {
			$new_where_arr[] = $where_ar . $sql_where;
		}
	
		return implode(' OR ', $new_where_arr);
	}
	
	return $where;
}

add_filter('where_request_sql_bids', 'operworks_where_request_sql_bids', 100);
function operworks_where_request_sql_bids($where) {
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	
	if ($user_id) {
		
		$db1_arr = operworks_create_data_for_db(is_isset($ui, 'only_directions'), 'int');
		$db1 = $db1_arr['arr1'];
		$db1n = $db1_arr['arr2'];
		
		$db2_arr = operworks_create_data_for_db(is_isset($ui, 'only_merchants'), 'var');
		$db2 = $db2_arr['arr1'];
		$db2n = $db2_arr['arr2'];
		
		$where_arr = explode('OR', $where);
		$new_where_arr = array();
		$sql_where = '';

		$sql = array();

		if ($db1 and $db1n) {
			$sql[] = "direction_id IN ($db1) AND direction_id NOT IN ($db1n)";
		} elseif ($db1) {
			$sql[] = "direction_id IN ($db1)";
		} elseif ($db1n) {
			$sql[] = "direction_id NOT IN ($db1n)";
		}
		
		if ($db2 and $db2n) {
			$sql[] = "m_in IN ($db2) AND m_in NOT IN ($db2n)";
		} elseif ($db2) {
			$sql[] = "m_in IN ($db2)";
		} elseif ($db2n) {
			$sql[] = "m_in NOT IN ($db2n)";
		}	

		if (1 == count($sql)) {
			$sql_where = " AND " . implode('', $sql);
		} elseif (2 == count($sql)) {
			$sql_where = " AND (" . implode(' OR ', $sql) . ")";
		}
		
		foreach ($where_arr as $where_ar) {
			$new_where_arr[] = $where_ar . $sql_where;
		}
	
		return implode(' OR ', $new_where_arr);
	}
	
	return $where;
}

add_filter('change_bids_filter_list', 'operworks_change_bids_filter_list', 100);
function operworks_change_bids_filter_list($lists) {
	
	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	
	if ($user_id) {
		
		$db1 = operworks_create_data_for_db(is_isset($ui, 'only_directions'), 'int');
	
		if ($db1 and isset($lists['currency']['direction_id'])) {
			unset($lists['currency']['direction_id']);
		}		
	
	}
	
	return $lists;
}
 
add_filter('change_bid_status', 'operworks_change_bidstatus', 200);    
function operworks_change_bidstatus($data) { 
	global $wpdb;

	$stop_action = intval(is_isset($data, 'stop'));
	$set_status = $data['set_status'];
	if (!$stop_action) {
		if ('realdelete' == $set_status or 'archived' == $set_status) {
			
			$id = $data['bid']->id;
			$wpdb->query("DELETE FROM " . $wpdb->prefix . "bids_operators WHERE bid_id = '$id'");
			
		}
	}

	return $data;
}
 
add_action('pn_adminpage_content_pn_bids', 'operworks_pn_admin_content_pn_bids');
function operworks_pn_admin_content_pn_bids() {
?>
<script type="text/javascript">
jQuery(function($) {
	
 	$(document).on('change', '.wmo_input', function() {
		var id = $(this).parents('.one_bids').attr('id').replace('bidid_', '');
		var thet = $(this);
		var par = thet.parents('.wmo_wrap');
		var check = 0;
		if ($(this).prop('checked')) {
			check = 1;
		}
		thet.prop('disabled', true);
		var param = 'id=' + id + '&check='+check;
		$('.filter_change').addClass('active');
		$.ajax({
			type: "POST",
			url: "<?php the_pn_link('operworks_change', 'post');?>",
			dataType: 'json',
			data: param,
			error: function(res, res2, res3) {
				<?php do_action('pn_js_error_response', 'ajax'); ?>
			},			
			success: function(res)
			{		
				$('.filter_change').removeClass('active');
				thet.prop('disabled', false);
				
				if (res['status'] == 'success') {
					par.html(res['html']);
				}
				if (res['status'] == 'error') {
					<?php do_action('pn_js_alert_response'); ?>
				} 		
			}
		});	
		
		return false;
	});
	
});
</script>
<?php	
}

add_action('premium_action_operworks_change', 'pn_premium_action_operworks_change');
function pn_premium_action_operworks_change() {
	global $wpdb;	

	_method('post');
	_json_head();

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);
	
	$log = array();
	$log['status'] = '';
	$log['status_code'] = 0; 
	$log['status_text'] = __('Error', 'pn');
	
	if (current_user_can('administrator') or current_user_can('pn_bids')) {
	
		$bid_id = intval(is_param_post('id'));
		$check = intval(is_param_post('check'));
		
		if (1 == $check) {
			$work = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bids_operators WHERE bid_id = '$bid_id' AND user_id = '$user_id'");
			$arr = array();
			$arr['createdate'] = current_time('mysql');
			$arr['user_id'] = $user_id;
			$arr['user_login'] = is_user($ui->user_login);
			$arr['bid_id'] = $bid_id;
			if (isset($work->id)) {
				$wpdb->update($wpdb->prefix . "bids_operators", $arr, array('id' => $work->id));
			} else {
				$wpdb->insert($wpdb->prefix . "bids_operators", $arr);
			}
		} else {
			$wpdb->query("DELETE FROM " . $wpdb->prefix . "bids_operators WHERE bid_id = '$bid_id' AND user_id = '$user_id'");
		}
		$log['html'] = get_bid_operworks($bid_id);
		$log['status'] = 'success';
	
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1;
		$log['status_text'] = __('Authorisation Error', 'pn');
	}
	
	echo pn_json_encode($log);	
	exit;
}

function get_bid_operworks($item_id) {
	global $wpdb, $premiumbox;

	$ui = wp_get_current_user();
	$user_id = intval($ui->ID);	

	$minuts = intval($premiumbox->get_option('operworks', 'minuts'));
	if ($minuts < 1) { $minuts = 1; }

	$second = $minuts * 60;
	$time = current_time('timestamp') + $second;
	$ldate = date('Y-m-d H:i:s', $time);

	$works = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "bids_operators WHERE bid_id = '$item_id' AND createdate < '$ldate'");
	$iam = 0;
	$yourself = intval($premiumbox->get_option('operworks', 'yourself'));
	$users = array();
	foreach ($works as $work) {
		if ($work->user_id == $user_id) {
			$iam = 1;
			if ($yourself) {
				$users[$work->user_id] = is_user($work->user_login);
			}
		} else {
			$users[$work->user_id] = is_user($work->user_login);
		}
	}

	$ch = '';
	$class = '';
	if (1 == $iam) { $ch = 'checked="checked"'; }
	if (count($users) > 0 or 1 == $iam) {
		$class='btbg_redded';
	}	
	
	$html = '<div class="bids_text ' . $class . '"><label><strong>' . __('Processing order', 'pn') . '</strong> <input type="checkbox" ' . $ch . ' class="wmo_input" name="" autocomplete="off" value="1" /></label></div>';
	
	if (count($users) > 0) {
		$html .= '
		<div class="bids_text ' . $class . '">
			<div><strong>' . __('Operators processing order are', 'pn') . ':</strong></div>
			';
			foreach ($users as $user_id => $user_login) {
				$html .= '<div class="wmo_line"><a href="' . pn_edit_user_link($user_id) . '">' . $user_login . '</a></div>';
			}
			$html .= '
		</div>';
	}	

	return $html;
}

add_filter('onebid_col1', 'onebid_col1_operworks', 99, 2);
function onebid_col1_operworks($actions, $item) {	
	
	$item_id = $item->id;
	$html = get_bid_operworks($item_id);
	
	$n_actions = array();
	$n_actions['operworks'] = array(
		'type' => 'html',
		'html' => '<div class="wmo_wrap">' . $html . '</div>',
	);
	$actions = pn_array_insert($actions, 'status', $n_actions);
	
	return $actions;
}

if (!function_exists('bids_globalajax_admin_request')) {
	add_filter('globalajax_admin_request', 'bids_globalajax_admin_request');
	function bids_globalajax_admin_request($params) {
		
		$page = trim(is_param_get('page'));
		if ('pn_bids' == $page) {
			$params .= "+ '&bids_ids=' + $('#visible_ids').val()";
		}
		
		return $params;
	}
}

add_filter('globalajax_admin_data', 'operworks_globalajax_admin_data'); 
function operworks_globalajax_admin_data($log) {
	global $wpdb;

	if (isset($_POST['bids_ids'])) {
		if (current_user_can('administrator') or current_user_can('pn_bids')) {
			$bids_ids = is_param_post('bids_ids');
			$bids_ids_parts = explode(',', $bids_ids);
			$ins = create_data_for_db($bids_ids_parts, 'int');
			if ($ins) {
				$bids = array();
				$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id IN($ins)");
				foreach ($items as $item) {
					$bids[$item->id] = get_bid_operworks($item->id);
				}
				$log['wmo_bids'] = $bids;
			}
		}
	}
	
	return $log;
}

add_action('globalajax_admin_result', 'operworks_globalajax_admin_result');
function operworks_globalajax_admin_result() {
?>
if (res['wmo_bids']) {
	for (key in res['wmo_bids']) {
		$('#bidid_'+ key).find('.wmo_wrap').html(res['wmo_bids'][key]);
	}	
}
<?php
}

add_action('admin_menu', 'pn_adminpage_operworks');
function pn_adminpage_operworks() {
	global $premiumbox;	
	
	add_submenu_page("pn_moduls", __('Live operator', 'pn'), __('Live operator', 'pn'), 'administrator', "pn_operworks", array($premiumbox, 'admin_temp'));
}

add_filter('pn_adminpage_title_pn_operworks', 'def_adminpage_title_pn_operworks');
function def_adminpage_title_pn_operworks($page) {
	
	return __('Live operator', 'pn');
} 

add_action('pn_adminpage_content_pn_operworks', 'def_adminpage_content_pn_operworks');
function def_adminpage_content_pn_operworks() {
	global $premiumbox;

	$form = new PremiumForm();

	$options = array();
	$options['top_title'] = array(
		'view' => 'h3',
		'title' => __('Operator settings', 'pn'),
		'submit' => __('Save', 'pn'),
	);	
	$options['minuts'] = array(
		'view' => 'inputbig',
		'title' => __('Time needed to process order (min.)', 'pn'),
		'default' => $premiumbox->get_option('operworks', 'minuts'),
		'name' => 'minuts',
	);
	$options['yourself'] = array(
		'view' => 'select',
		'title' => __('Display your login in list of operators', 'pn'),
		'default' => $premiumbox->get_option('operworks', 'yourself'),
		'options' => array('0' => __('No', 'pn'), '1' => __('Yes', 'pn')),
		'name' => 'yourself',
	);	
	
	$params_form = array(
		'filter' => 'pn_operworks_options',
		'button_title' => __('Save', 'pn'),
	);
	$form->init_form($params_form, $options);
	
}  

add_action('premium_action_pn_operworks', 'def_premium_action_pn_operworks');
function def_premium_action_pn_operworks() {
	global $premiumbox;	

	_method('post');
	
	$form = new PremiumForm();
	$form->send_header();
	
	pn_only_caps(array('administrator'));

	$options = array('minuts', 'yourself');	
	foreach ($options as $key) {
		$premiumbox->update_option('operworks', $key, intval(is_param_post($key)));
	}				

	$url = admin_url('admin.php?page=pn_operworks&reply=true');
	$form->answer_form($url);
	
}  