<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_bcc', 'def_adminpage_title_pn_bcc');
	function def_adminpage_title_pn_bcc() {
		
		return __('Confirmation log', 'pn');
	}

	add_action('pn_adminpage_content_pn_bcc', 'def_adminpage_content_pn_bcc');
	function def_adminpage_content_pn_bcc() {
		
		premium_table_list();	
		
	}
	
}	

add_action('premium_action_pn_bcc', 'def_premium_action_pn_bcc');
function def_premium_action_pn_bcc() {
	global $wpdb;
	
	_method('post');
	pn_only_caps(array('administrator', 'pn_bids'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
		
	if (isset($_POST['save'])) {
				
		do_action('pntable_bcc_save');	
		$arrs['reply'] = 'true';

	} elseif (isset($_POST['delete_all'])) {

		$wpdb->query("DELETE FROM " . $wpdb->prefix . "bcc_logs");
		do_action('pntable_bcc_deleteall');
		$arrs['reply'] = 'true';

	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {
				
			if ('delete' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
								
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bcc_logs WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_bcc_delete_before', pn_ind(), $id, $item);
						if (1 == $res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "bcc_logs WHERE id = '$id'");
							do_action('item_bcc_delete', $id, $item, $result);
						}
					}
				}		
			}				
				
			do_action('pntable_bcc_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';	
		} 
	}

	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;					
}

class pn_bcc_Table_List extends PremiumTable {

	function __construct() {  
	
		parent::__construct();
				
		$this->primary_column = 'title';
		$this->save_button = 0;
		
	}
		
	function column_default($item, $column_name) {
		
		if ('bid' == $column_name) {
			return '<a href="' . admin_url('admin.php?page=pn_bids&bidid=' . $item->bid_id) . '">' . $item->bid_id . '</a>';
		} elseif ('cc' == $column_name) {	
			return intval($item->counter);
		} elseif ('title' == $column_name) {	
			return get_pn_time($item->createdate, 'd.m.Y H:i:s');
		}
		
		return '';
	}	
		
	function column_cb($item) {
		
		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
	}		
		
	function get_columns() {
		
		$columns = array(       
			'cb'        => '', 
			'title'     => __('Date', 'pn'),
			'bid'    => __('Order ID', 'pn'),
			'cc'    => __('Confirmation order number', 'pn'),
		);
		
		return $columns;
	}

	function get_search() {
		
		$search = array();	
		$search[] = array(
			'view' => 'input',
			'title' => __('Order ID', 'pn'),
			'default' => pn_strip_input(is_param_get('bid_id')),
			'name' => 'bid_id',
		);		
		
		return $search;
	}
		
	function get_bulk_actions() {
		
		$actions = array(		
			'delete'    => __('Delete', 'pn')
		);
		
		return $actions;
	}		

	function prepare_items() {
		global $wpdb; 
			
		$per_page = $this->count_items();
		$current_page = $this->get_pagenum();
		$offset = $this->get_offset();
			
		$oinfo = $this->db_order('createdate', 'DESC');
		$orderby = $oinfo['orderby'];
		$order = $oinfo['order'];
			
		$where = '';

		$bid_id = intval(is_param_get('bid_id'));	
		if ($bid_id) { 
			$where .= " AND bid_id='$bid_id'";
		}		 		
			
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if (1 == $this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "bcc_logs WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "bcc_logs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
	}

	function extra_tablenav($which) {		  	
	?>
		<input type="submit" name="delete_all" style="background: #f4eaee;" value="<?php _e('Delete logs', 'pn'); ?>">	
	<?php 
	} 	  
}