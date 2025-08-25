<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_zreserv', 'def_adminpage_title_pn_zreserv');
	function def_adminpage_title_pn_zreserv() {
		
		return __('Reserve requests', 'pn');
	}

	add_action('pn_adminpage_content_pn_zreserv', 'def_adminpage_content_pn_zreserv');
	function def_adminpage_content_pn_zreserv() {
		
		premium_table_list();
		
	}
	
}	

add_action('premium_action_pn_zreserv', 'def_premium_action_pn_zreserv');
function def_premium_action_pn_zreserv() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_zreserv'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
				
	if (isset($_POST['save'])) {
							
		do_action('pntable_zreserv_save');
		$arrs['reply'] = 'true';

	} else {			
		if (isset($_POST['id']) and is_array($_POST['id'])) {
			
			if ('delete' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);		
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "direction_reserve_requests WHERE id = '$id'");
					if (isset($item->id)) {	
						$res = apply_filters('item_zreserv_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "direction_reserve_requests WHERE id = '$id'");
							do_action('item_zreserv_delete', $id, $item, $result);
						}
					}
				}				
			}
			
			do_action('pntable_zreserv_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';		
		} 
	}
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
} 

class pn_zreserv_Table_List extends PremiumTable {

	function __construct() {
		
		parent::__construct();
				
		$this->primary_column = 'date';
		$this->save_button = 0;
		
	}
		
	function column_default($item, $column_name) {
			
		if ('sum' == $column_name) {
			return is_sum($item->request_amount);
		} elseif ('com' == $column_name) {		
			return pn_strip_input($item->request_comment);		
		} elseif ('date' == $column_name) {		
			return get_pn_time($item->request_date, 'd.m.Y в H:i');		
		} elseif ('email' == $column_name) {
			return '<a href="mailto:' . is_email($item->user_email) . '">' . is_email($item->user_email) . '</a>';
		} elseif ('vals' == $column_name) {
			return pn_strip_input($item->direction_title);
		}
			
		return '';
	}		
		
	function column_cb($item) {
		
		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
	}		
		
	function get_columns() {
		
		$columns = array(
			'cb'        => '',
			'date'     => __('Date', 'pn'),
			'email'    => __('E-mail', 'pn'),
			'vals'  => __('Exchange direction', 'pn'),
			'sum'  => __('Amount', 'pn'),
			'com'  => __('Comment', 'pn'),			
		);
		
		return $columns;
	}	
		
	function get_sortable_columns() {
		
		$sortable_columns = array( 
			'date'     => array('request_date', 'desc'),
			'sum'     => array('(request_amount -0.0)', false),
		);
		
		return $sortable_columns;
	}	

	function get_search() {
		global $wpdb;
		
		$search = array();
			
		$directions = array();
		$directions[0] = '--' . __('All directions', 'pn') . '--';
		$directions_arr = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "directions WHERE auto_status = '1' ORDER BY site_order1 ASC");	
		foreach ($directions_arr as $direction) {
			$directions[$direction->id] = pn_strip_input($direction->tech_name) . pn_item_status($direction, 'direction_status', array('0' => __('inactive direction', 'pn'), '2' => __('hold direction', 'pn'))); 
		}
		$search['direction_id'] = array(
			'view' => 'select',
			'title' => __('Exchange direction', 'pn'),
			'default' => pn_strip_input(is_param_get('direction_id')),
			'options' => $directions,
			'name' => 'direction_id',
		);
			
		return $search;
	}	

	function get_bulk_actions() {
		
		$actions = array(
			'delete'    => __('Delete', 'pn'),
		);
		
		return $actions;
	}
		
	function prepare_items() {
		global $wpdb; 
			
		$per_page = $this->count_items();
		$current_page = $this->get_pagenum();
		$offset = $this->get_offset();
			
		$oinfo = $this->db_order('request_date', 'DESC');
		$orderby = $oinfo['orderby'];
		$order = $oinfo['order'];

		$where = '';
			
		$direction_id = intval(is_param_get('direction_id'));
		if ($direction_id > 0) { 
			$where .= " AND direction_id='$direction_id'"; 
		}		
		
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "direction_reserve_requests WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "direction_reserve_requests WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");
  		
	}	  
}	