<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_directions', 'pn_adminpage_title_pn_directions');
	function pn_adminpage_title_pn_directions() {
		
		return __('Exchange directions', 'pn');
	}

	add_action('pn_adminpage_content_pn_directions', 'def_pn_adminpage_content_pn_directions');
	function def_pn_adminpage_content_pn_directions() {
		
		premium_table_list();
		
	}
	
}	

add_action('premium_action_pn_directions', 'def_premium_action_pn_directions');
function def_premium_action_pn_directions() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_directions'));

	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
				
	if (isset($_POST['save'])) {
					
		$v = get_currency_data();

		$ui = wp_get_current_user();
		$user_id = intval(is_isset($ui, 'ID'));
							
		if (isset($_POST['course_give'], $_POST['course_get']) and is_array($_POST['course_give']) and is_array($_POST['course_get'])) {	
			$now_date = current_time('mysql');	
			foreach ($_POST['course_give'] as $id => $course_give) {
				$id = intval($id);
				$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$id'");
				if (isset($item->id)) {
					if (isset($v[$item->currency_id_give], $v[$item->currency_id_get])) {
						$vd1 = $v[$item->currency_id_give];
						$vd2 = $v[$item->currency_id_get];
							
						$course_give = is_sum($course_give, $vd1->currency_decimal);
						$course_get = is_sum($_POST['course_get'][$id], $vd2->currency_decimal);
									
						$arr = array();				
						if ($course_give != $item->course_give or $course_get != $item->course_get) {
							$arr['course_give'] = $course_give;
							$arr['course_get'] = $course_get;
						}
						if (count($arr) > 0) {
							
							$arr['edit_date'] = $now_date;
							$arr['edit_user_id'] = $user_id;
							$result = $wpdb->update($wpdb->prefix . 'directions', $arr, array('id' => $id));
							
							do_action('item_direction_save', $item->id, $item, $result, $arr);
							
						}
					}
				}	
			}
		}

		if (isset($_POST['com_box_sum1'], $_POST['com_box_sum2'], $_POST['com_box_pers1'], $_POST['com_box_pers2']) and is_array($_POST['com_box_sum1'])) {
			foreach ($_POST['com_box_sum1'] as $id => $com_box_sum1) {
				$id = intval($id);
				$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$id'");
				if (isset($item->id)) {
					
					$com_box_sum1 = is_sum($com_box_sum1);
					$com_box_sum2 = is_sum($_POST['com_box_sum2'][$id]);			
					$com_box_pers1 = is_sum($_POST['com_box_pers1'][$id]);	
					$com_box_pers2 = is_sum($_POST['com_box_pers2'][$id]);				
									
					$array = array();
					$array['com_box_sum1'] = $com_box_sum1;
					$array['com_box_sum2'] = $com_box_sum2;
					$array['com_box_pers1'] = $com_box_pers1;
					$array['com_box_pers2'] = $com_box_pers2;					
					$result = $wpdb->update($wpdb->prefix . 'directions', $array, array('id' => $id));
					
					do_action('item_direction_save', $item->id, $item, $result, $array);
					
				}
			}
		}		 		
					
		do_action('pntable_directions_save', $v);
		$arrs['reply'] = 'true';

	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {				
					
			if ('basket' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$id' AND auto_status != '0'");
					if (isset($item->id)) {
						$res = apply_filters('item_direction_basket_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "directions SET auto_status = '0' WHERE id = '$id'");
							do_action('item_direction_basket', $id, $item, $result);
						}
					}		
				}	
			}
					
			if ('unbasket' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$id' AND auto_status != '1'");
					if (isset($item->id)) {
						$res = apply_filters('item_direction_unbasket_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "directions SET auto_status = '1' WHERE id = '$id'");
							do_action('item_direction_unbasket', $id, $item, $result);
						}
					}		
				}	
			}					
					
			if ('active' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$id' AND direction_status != '1'");
					if (isset($item->id)) {
						$res = apply_filters('item_direction_active_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "directions SET direction_status = '1' WHERE id = '$id'");
							do_action('item_direction_active', $id, $item, $result);
						}
					}
				}			
			}

			if ('hold' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$id' AND direction_status != '2'");
					if (isset($item->id)) {
						$res = apply_filters('item_direction_hold_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "directions SET direction_status = '2' WHERE id = '$id'");
							do_action('item_direction_hold', $id, $item, $result);
						}
					}
				}		
			}

			if ('deactive' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$id' AND direction_status != '0'");
					if (isset($item->id)) {
						$res = apply_filters('item_direction_deactive_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "directions SET direction_status = '0' WHERE id = '$id'");
							do_action('item_direction_deactive', $id, $item, $result);
						}
					}
				}		
			}					
					
			if ('delete' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_direction_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "directions WHERE id = '$id'");
							do_action('item_direction_delete', $id, $item, $result);
						}
					}
				}			
			}
				
			do_action('pntable_directions_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';		
		} 	
	}
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
} 

class pn_directions_Table_List extends PremiumTable {

	function __construct() { 
	
		parent::__construct();
					
		$this->primary_column = 'title';
		$this->save_button = 1;
		
	}
		
	function get_thwidth() {
		
		$array = array();
		$array['id'] = '30px';
		$array['course_give'] = '120px';
		$array['course_get'] = '120px';
		$array['comboxlist_give'] = '80px';
		$array['comboxlist_get'] = '80px';
		
		return $array;
	}	
		
	function column_default($item, $column_name) {
		
		$standart = is_direction_check_rate($item);	
		if ('course_give' == $column_name) {
			$dir_c = is_course_direction($item, '', '', 'admin');
			if (0 == $standart) {	
				return '<input type="text" style="width: 100%;" name="course_give[' . $item->id . ']" value="' . is_isset($dir_c, 'give') . '" />'; 
			} else {
				return '<strong>' . is_isset($dir_c, 'give') . '</strong>';
			}
		} elseif ('course_get' == $column_name) {	
			$dir_c = is_course_direction($item, '', '', 'admin');
			if (0 == $standart) {	
				return '<input type="text" style="width: 100%;" name="course_get[' . $item->id . ']" value="' . is_isset($dir_c, 'get') . '" />';
			} else {
				return '<strong>' . is_isset($dir_c, 'get') . '</strong>';
			}
		} elseif ('comboxlist_give' == $column_name) {	
			$show = '
			<div><input type="text" style="width: 100%; max-width: 80px;" name="com_box_sum1[' . $item->id . ']" value="' . is_sum($item->com_box_sum1) . '" /> S</div>
			<div><input type="text" style="width: 100%; max-width: 80px;" name="com_box_pers1[' . $item->id . ']" value="' . is_sum($item->com_box_pers1) . '" /> %</div>
			';
			return $show;
		} elseif ('comboxlist_get' == $column_name) {	
			$show = '
			<div><input type="text" style="width: 100%; max-width: 80px;" name="com_box_sum2[' . $item->id . ']" value="' . is_sum($item->com_box_sum2) . '" /> S</div>
			<div><input type="text" style="width: 100%; max-width: 80px;" name="com_box_pers2[' . $item->id . ']" value="' . is_sum($item->com_box_pers2) . '" /> %</div>
			';
			return $show;							
		} elseif ('status' == $column_name) {
			if (0 == $item->direction_status) { 
				return '<span class="bred">' . __('inactive direction', 'pn') . '</span>'; 
			} elseif (1 == $item->direction_status) { 
				return '<span class="bgreen">' . __('active direction', 'pn') . '</span>'; 
			} elseif (2 == $item->direction_status) { 
				return '<strong>' . __('hold direction', 'pn') . '</strong>'; 	
			}	
		} elseif ('title' == $column_name) {
			return pn_strip_input($item->tech_name);
		} elseif ('id' == $column_name) {
			return '<strong>' . $item->id . '</strong>';	
		} 
		
		return '';
	}	
		
	function column_cb($item) {
		
		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
	}

	function get_row_actions($item) {
		
		$actions = array(
			'edit'      => '<a href="'. admin_url('admin.php?page=pn_add_directions&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
		);
		if (0 != $item->direction_status and 1 == $item->auto_status) {
			$actions['view'] = '<a href="' . get_exchange_link($item->direction_name) . '" target="_blank">' . __('View', 'pn') . '</a>';
		}	
		
		return $actions;
	}		
		
	function tr_class($tr_class, $item) {
		
		if (0 == $item->direction_status) {
			$tr_class[] = 'tr_red';
		} elseif (2 == $item->direction_status) {
			$tr_class[] = 'tr_blue';
		}			
		
		return $tr_class;
	}		
		
	function get_columns() {
		
		$columns = array(
			'cb'        => '',
			'id'     => __('ID', 'pn'),
			'title'     => __('Direction', 'pn'),
			'course_give' => __('Exchange rate 1', 'pn'),
			'course_get' => __('Exchange rate 2', 'pn'),
			'comboxlist_give' => __('Additional sender fee', 'pn'),
			'comboxlist_get' => __('Additional recipient fee', 'pn'),
			'status' => __('Status', 'pn'),
		);	
		
		return $columns;
	}	
		
	function get_bulk_actions() {
		
		$actions = array(
			'active'    => __('Activate', 'pn'),
			'hold'    => __('Freeze', 'pn'),
			'deactive'    => __('Deactivate', 'pn'),
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
			
	function get_submenu() {
	
		$options = array();
		$options['filter'] = array(
			'options' => array(
				'2' => __('active direction', 'pn'),
				'1' => __('inactive direction', 'pn'),
				'3' => __('frozen direction', 'pn'),
				'9' => __('in basket', 'pn'),
			),
		);	
		
		return $options;
	}
		
	function get_sortable_columns() {
		
		$sortable_columns = array( 
			'id' => array('id', 'desc'),
			'title' => array('site_order1', 'ASC'),
		);
		
		return $sortable_columns;
	}

	function get_search() {
		
		$search = array();
			
		$currencies = list_currency(__('All currency', 'pn'));
		$search['curr_give'] = array(
			'view' => 'select',
			'title' => __('Currency send', 'pn'),
			'default' => pn_strip_input(is_param_get('curr_give')),
			'options' => $currencies,
			'name' => 'curr_give',
		);
		$search['curr_get'] = array(
			'view' => 'select',
			'title' => __('Currency receive', 'pn'),
			'default' => pn_strip_input(is_param_get('curr_get')),
			'options' => $currencies,
			'name' => 'curr_get',
		);
		$search['line1'] = array(
			'view' => 'line',
		);				
		$psys = list_psys(__('All payment systems', 'pn'));	
		$search['psys_id_give'] = array(
			'view' => 'select',
			'title' => __('Payment system send', 'pn'),
			'default' => pn_strip_input(is_param_get('psys_id_give')),
			'options' => $psys,
			'name' => 'psys_id_give',
		);
		$search['psys_id_get'] = array(
			'view' => 'select',
			'title' => __('Payment system receive', 'pn'),
			'default' => pn_strip_input(is_param_get('psys_id_get')),
			'options' => $psys,
			'name' => 'psys_id_get',
		);
		
		return $search;
	}	
		
	function prepare_items() {
		global $wpdb; 
			
		$per_page = $this->count_items();
		$current_page = $this->get_pagenum();
		$offset = $this->get_offset();
				
		$oinfo = $this->db_order('id', 'desc');
		$orderby = $oinfo['orderby'];
		$order = $oinfo['order'];			
			
		$where = '';
			
		$filter = intval(is_param_get('filter'));
		$in_filter = array('1', '2', '3');
		if (in_array($filter, $in_filter)) {
			$filter = $filter - 1;
			$where .= " AND direction_status='$filter'"; 	
		}
			
		if (9 == $filter) {	
			$where .= " AND auto_status = '0'";
		} else {
			$where .= " AND auto_status = '1'";
		}			
			
		$curr_give = intval(is_param_get('curr_give'));
		if ($curr_give > 0) { 
			$where .= " AND currency_id_give = '$curr_give'"; 
		}
		
		$curr_get = intval(is_param_get('curr_get'));
		if ($curr_get > 0) { 
			$where .= " AND currency_id_get = '$curr_get'"; 
		}

		$psys_id_give = intval(is_param_get('psys_id_give'));
		if ($psys_id_give > 0) { 
			$where .= " AND psys_id_give = '$psys_id_give'"; 
		}
		
		$psys_id_get = intval(is_param_get('psys_id_get'));
		if ($psys_id_get > 0) { 
			$where .= " AND psys_id_get = '$psys_id_get'"; 
		}			
			
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "directions WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "directions WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page"); 
 		
	}
		
	function extra_tablenav($which) {
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_directions'); ?>"><?php _e('Add new', 'pn'); ?></a>		
		<?php  
	} 
}