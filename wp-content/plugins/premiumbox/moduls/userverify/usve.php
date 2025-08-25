<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_all_usve', 'def_adminpage_title_all_usve');
	function def_adminpage_title_all_usve() {
		
		return __('Identity verification', 'pn');
	}

	add_action('pn_adminpage_content_all_usve', 'def_pn_adminpage_content_all_usve');
	function def_pn_adminpage_content_all_usve() {
		
		premium_table_list();
		
	}
	
}	

add_action('premium_action_enable_userverify', 'def_premium_action_enable_userverify');
function def_premium_action_enable_userverify() {	
	global $wpdb;
		
	pn_only_caps(array('administrator', 'pn_userverify'));
					
	$id = intval(is_param_get('id'));
	$place = trim(is_param_get('place'));
			
	$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "verify_bids WHERE id = '$id' AND auto_status = '1' AND status != '2'");	
	if (isset($data->id)) {
		$data_id = $data->id;
					
		$array = array();
		$array['status'] = 2;
		$array['comment'] = '';
		$wpdb->update($wpdb->prefix . 'verify_bids', $array, array('id' => $id));
				
		$user_id = $data->user_id;
		$user_data = get_userdata($user_id);
		if (1 != $user_data->user_verify) {
				
			$arr = array();
			$arr['user_verify'] = 1;
			$wpdb->update($wpdb->prefix . 'users', $arr, array('ID' => $user_id));
			do_action('item_users_verify', $user_id, $user_data);
				
		}
					
		$uv_auto = array();	
		$user_fields = get_user_fields();

		$fields = $wpdb->get_results("
		SELECT * FROM " . $wpdb->prefix . "uv_field 
		LEFT OUTER JOIN " . $wpdb->prefix . "uv_field_user 
		ON(" . $wpdb->prefix . "uv_field.id = " . $wpdb->prefix . "uv_field_user.uv_field) 
		WHERE " . $wpdb->prefix . "uv_field.fieldvid = '0' AND uv_id = '$data_id' 
		");
				
		foreach ($fields as $field) {
			if ($field->uv_auto and pn_verify_uv($field->uv_auto)) {
				$uv_auto[$field->uv_auto] = strip_uf($field->uv_data, $field->uv_auto);
			}
		}
					
		foreach ($uv_auto as $uv_k => $uv_v) {
			$in = intval(is_isset($user_fields[$uv_k], 'in'));
			$unique = intval(is_isset($user_fields[$uv_k], 'unique'));
			$count = 0;
			if ($unique) {
				if ($in) {
					$count = $wpdb->get_var("SELECT COUNT(ID) FROM " . $wpdb->prefix . "users WHERE ID != '$user_id' AND `$uv_k` = '$uv_v'");
				} else {
					$count = $wpdb->get_var("SELECT COUNT(umeta_id) FROM " . $wpdb->prefix . "usermeta WHERE user_id != '$user_id' AND meta_key = '$uv_k' AND meta_value = '$uv_v'");
				}
			}
			if ($count < 1) {
				if ($in) {
					$wpdb->update($wpdb->prefix . 'users', array($uv_k => $uv_v), array('ID' => $user_id));
				} else {
					update_user_meta($user_id, $uv_k, $uv_v) or add_user_meta($user_id, $uv_k, $uv_v, true);
				}
			} 				
		}

		$now_locale = get_locale();
		$user_locale = pn_strip_input($data->locale);
		$user_email = is_email($data->user_email);
			
		set_locale($user_locale);
				
		$notify_tags = array();
		$notify_tags = apply_filters('notify_tags_userverify1_u', $notify_tags);		

		$user_send_data = array(
			'user_email' => $user_email,
		);	
		$user_send_data = apply_filters('user_send_data', $user_send_data, 'userverify1_u', $user_data);
		$result_mail = apply_filters('premium_send_message', 0, 'userverify1_u', $notify_tags, $user_send_data);

		set_locale($now_locale);
							
	}
			
	if ('all' == $place) {
		$url = admin_url('admin.php?page=all_usve&reply=true');
		$paged = intval(is_param_post('paged'));
		if ($paged > 1) { $url .= '&paged=' . $paged; }			
	} else {	
		$url = admin_url('admin.php?page=all_add_usve&item_id=' . $id . '&reply=true');
	}
		
	wp_redirect($url);
	exit;
}	

add_action('premium_action_disable_userverify', 'def_premium_action_disable_userverify');
function def_premium_action_disable_userverify() {	
	global $wpdb;
		
	pn_only_caps(array('administrator', 'pn_userverify'));
			
	$id = intval(is_param_get('id'));
	$place = trim(is_param_get('place'));
			
	$plugin = get_plugin_class();
	$delete_files = intval(is_param_post('delete_files'));
			
	$data = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "verify_bids WHERE id='$id' AND auto_status = '1' AND status != '3'");	
	if (isset($data->id)) {
		$data_id = $data->id;
					
		$array = array();
		$array['status'] = 3;
		if (isset($_POST['textstatus'])) {
			$array['comment'] = $textstatus = pn_strip_text(is_param_post('textstatus'));
		} else {
			$textstatus = pn_strip_input($data->comment);
		}
		$wpdb->update($wpdb->prefix . 'verify_bids', $array, array('id' => $id));
				
		$user_id = $data->user_id;
		$user_data = get_userdata($user_id);
		$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "verify_bids WHERE user_id = '$user_id' AND status = '2' AND id != '$data_id'");
		if (0 == $cc) {
			if (0 != $user_data->user_verify) {
				$arr = array();
				$arr['user_verify'] = 0;
				$wpdb->update($wpdb->prefix . 'users', $arr, array('ID' => $user_id));
				do_action('item_users_unverify', $user_id, $user_data);
			}
		}
					
		$now_locale = get_locale();
		$user_locale = pn_strip_input($data->locale);
		$user_email = is_email($data->user_email);
			
		set_locale($user_locale);
						
		if (strlen($textstatus) < 1) {
			$textstatus = pn_strip_text(ctv_ml($plugin->get_option('usve', 'canceltext')));
		}			
						
		$notify_tags = array();
		$notify_tags['[text]'] = $textstatus;
		$notify_tags = apply_filters('notify_tags_userverify2_u', $notify_tags);		

		$user_send_data = array(
			'user_email' => $user_email,
		);	
		$user_send_data = apply_filters('user_send_data', $user_send_data, 'userverify2_u', $user_data);
		$result_mail = apply_filters('premium_send_message', 0, 'userverify2_u', $notify_tags, $user_send_data, $user_locale);
				
		set_locale($now_locale);

		if ($delete_files) {
			$path = $plugin->upload_dir . '/userverify/' . $data_id . '/';
			full_del_dir($path);
					
			$items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_field_user WHERE uv_id = '$data_id'");
			foreach ($items as $item) {
				$item_id = $item->id;
				$res = apply_filters('item_usfielduser_delete_before', pn_ind(), $item_id, $item);
				if ($res['ind']) {
					$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "uv_field_user WHERE id = '$item_id'");
					do_action('item_usfielduser_delete', $item_id, $item, $result); 
				}
			}					
		}
			
	}
			
	if ('all' == $place) {
		$url = admin_url('admin.php?page=all_usve&reply=true');
		$paged = intval(is_param_post('paged'));
		if ($paged > 1) { $url .= '&paged=' . $paged; }			
	} else {	
		$url = admin_url('admin.php?page=all_add_usve&item_id=' . $id . '&reply=true');
	}
	wp_redirect($url);
	exit;	
}

add_action('premium_action_all_usve', 'def_premium_action_all_usve');
function def_premium_action_all_usve() { 
	global $wpdb;
			
	_method('post');
	pn_only_caps(array('administrator', 'pn_userverify'));	

	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
			
	if (isset($_POST['save'])) {
							
		do_action('pntable_usve_save');
		$arrs['reply'] = 'true';

	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {	

			if ('basket' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "verify_bids WHERE id = '$id' AND auto_status != '0'");
					if (isset($item->id)) {
						$res = apply_filters('item_usve_basket_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "verify_bids SET auto_status = '0' WHERE id = '$id'");
							do_action('item_usve_basket', $id, $item, $result);
						}
					}		
				}	
			}
						
			if ('unbasket' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "verify_bids WHERE id = '$id' AND auto_status != '1'");
					if (isset($item->id)) {
						$res = apply_filters('item_usve_unbasket_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "verify_bids SET auto_status = '1' WHERE id = '$id'");
							do_action('item_usve_unbasket', $id, $item, $result);
						}
					}		
				}	
			}
				
			if ('delete' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "verify_bids WHERE id = '$id'");
					if (isset($item->id)) {		
						$res = apply_filters('item_usve_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {			
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "verify_bids WHERE id = '$id'");
							do_action('item_usve_delete', $id, $item, $result);
						}
					}		
				}	
			}
				
			do_action('pntable_usve_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';				
		} 
	}
					
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
} 

if (!class_exists('all_usve_Table_List')) {
	class all_usve_Table_List extends PremiumTable {

		function __construct() { 
		
			parent::__construct();
					
			$this->primary_column = 'create_date';
			$this->save_button = 0;
			
		}
			
		function get_thwidth() {
			
			$array = array();
			$array['create_date'] = '140px';
			
			return $array;
		}		
		
		function column_default($item, $column_name) {
				
			if ('id' == $column_name) {
				return $item->id;
			} elseif ('create_date' == $column_name) {	
				return get_pn_time($item->create_date, 'd.m.Y, H:i');			
			} elseif ('ip' == $column_name) {
				return pn_strip_input($item->user_ip);
			} elseif ('user' == $column_name) {
				return '<a href="' . pn_edit_user_link($item->user_id) . '">' . is_user($item->user_login) . '</a>';
			} elseif ('status' == $column_name) {
				if (1 == $item->status) {
					$status = '<strong>'. __('Pending request', 'pn') .'</strong>';
				} elseif (2 == $item->status) {
					$status = '<span class="bgreen">'. __('Confirmed request', 'pn') .'</span>';
				} elseif (3 == $item->status) {
					$status = '<span class="bred">'. __('Request is declined', 'pn') .'</span>';
				} else {
					$status = '<strong>'. __('automatic', 'pn') .'</strong>';
				}
				return $status;
			} elseif ('reason' == $column_name) {
				$comment_text = trim($item->comment);
				return _comment_label('verify', $item->id, $comment_text);	
			} 	
			
			return '';
		}	
		
		function column_cb($item) {
			
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
		}

		function get_row_actions($item) {
			
			$paged = intval(is_param_get('paged'));
			$actions = array(
				'edit'      => '<a href="' . admin_url('admin.php?page=all_add_usve&item_id=' . $item->id) . '">' . __('Edit data', 'pn') . '</a>',
				'disable'      => '<a href="' . pn_link('disable_userverify') . '&id=' . $item->id . '&paged=' . $paged . '&place=all" class="bred">' . __('Decline verification', 'pn') . '</a>',
			);			
			
			return $actions;
		}			
			
		function get_columns() {
			
			$columns = array(
				'cb'        => '', 
				'create_date'     => __('Creation date', 'pn'),	
				'user'     => __('User', 'pn'),
				'ip' => __('IP', 'pn'),
				'status'  => __('Status', 'pn'),
				'reason' => __('Failure reason', 'pn'),
			);
			
			return $columns;
		}

		function tr_class($tr_class, $item) {
			
			if (2 == $item->status) {
				$tr_class[] = 'tr_green';
			}
			
			if (3 == $item->status) {
				$tr_class[] = 'tr_red';
			}			
			
			return $tr_class;
		}		
		
		function get_bulk_actions() {
			
			$actions = array(
				'basket'    => __('In basket', 'pn'),
			);
			$filter = intval(is_param_get('filter'));
			if (9 == $filter) {
				$actions = array(
					'unbasket' => __('Restore', 'pn'),
					'delete' => __('Delete', 'pn'),
				);
			}	
			
			return $actions;
		}
		
		function get_search() {
			
			$search = array();
			$search['user_login'] = array(
				'view' => 'input',
				'title' => __('User login', 'pn'),
				'default' => pn_strip_input(is_param_get('user_login')),
				'name' => 'user_login',
			);
			$search['user_ip'] = array(
				'view' => 'input',
				'title' => __('IP', 'pn'),
				'default' => pn_strip_input(is_param_get('user_ip')),
				'name' => 'user_ip',
			);	
			
			return $search;
		}	
				
		function get_submenu() {
			
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('pending request', 'pn'),
					'2' => __('confirmed request', 'pn'),
					'3' => __('cancelled request', 'pn'),
					'9' => __('in basket', 'pn'),
				),
			);
			
			return $options;
		}	
		
		function prepare_items() {
			global $wpdb; 
				
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
				
			$oinfo = $this->db_order('create_date', 'DESC');
			$orderby = $oinfo['orderby'];
			$order = $oinfo['order'];
			
			$where = '';

			$filter = intval(is_param_get('filter'));
			if (1 == $filter) { //на модерации
				$where .= " AND status = '1'";
			} elseif (2 == $filter) { //активен
				$where .= " AND status = '2'";
			} elseif (3 == $filter) { //завершен
				$where .= " AND status = '3'";
			} else { //все, кроме автозаявок
				$where .= " AND status != '0'";
			}

			if (9 == $filter) {	
				$where .= " AND auto_status = '0'";
			} else {
				$where .= " AND auto_status = '1'";
			}				

			$user_login = pn_sfilter(pn_strip_input(is_param_get('user_login')));
			if ($user_login) {
				$where .= " AND user_login LIKE '%$user_login%'";
			}

			$user_ip = pn_sfilter(pn_strip_input(is_param_get('user_ip')));
			if ($user_ip) {
				$where .= " AND user_ip LIKE '%$user_ip%'";
			}		
				
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if ($this->navi) {
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "verify_bids WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "verify_bids WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");
			
		}	

		function extra_tablenav($which) {
		?>
			<a href="<?php echo admin_url('admin.php?page=all_add_usve'); ?>"><?php _e('Add new', 'pn'); ?></a>
		<?php
		} 	
	}
} 