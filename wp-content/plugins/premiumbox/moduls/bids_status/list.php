<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_bidstatus', 'pn_admin_title_pn_bidstatus');
	function pn_admin_title_pn_bidstatus() {
		
		return __('Orders status', 'pn');
	}

	add_action('pn_adminpage_content_pn_bidstatus', 'def_adminpage_content_pn_bidstatus');
	function def_adminpage_content_pn_bidstatus() {
		
		premium_table_list();
		
	}

}

add_action('premium_action_pn_bidstatus', 'def_premium_action_pn_bidstatus');
function def_premium_action_pn_bidstatus() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_bidstatus'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
			
	if (isset($_POST['save'])) {		
	
		do_action('pntable_bidstatus_save');
		$arrs['reply'] = 'true';
		
	} else {		
		if (isset($_POST['id']) and is_array($_POST['id'])) {											
			if ('delete' == $action) {			
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'my{$id}'");
					if (0 == $cc) {
						$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "bidstatus WHERE id = '$id'");
						if (isset($item->id)) {
							$res = apply_filters('item_bidstatus_delete_before', pn_ind(), $id, $item);
							if ($res['ind']) {
								$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "bidstatus WHERE id = '$id'");
								do_action('item_bidstatus_delete', $id, $item, $result);
							}
						}					
					}
				}					
			}
				
			do_action('pntable_bidstatus_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
		} 
	}
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
} 

class pn_bidstatus_Table_List extends PremiumTable {

	function __construct() { 
	
		parent::__construct();
				
		$this->primary_column = 'title';
		$this->save_button = 0;
		
	}
		
	function column_default($item, $column_name) {
		global $wpdb;
			
		if ('cap' == $column_name) {
			$status_id = $item->id;
			$cc = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE status = 'my{$status_id}'");
			return $cc;
		} elseif ('title' == $column_name) {
			return pn_strip_input(ctv_ml($item->title));
		} 
		
		return '';
	}	
		
	function column_cb($item) {
		
		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
	}

	function get_row_actions($item) {
		
		$actions = array(
			'edit'      => '<a href="' . admin_url('admin.php?page=pn_add_bidstatus&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
		);			
		
		return $actions;
	}		
		
	function get_columns() {
		
		$columns = array(
			'cb'        => '',
			'title'     => __('Displayed name', 'pn'),
			'cap'     => __('Amount of orders', 'pn'),
		);
		
		return $columns;
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
			
		$oinfo = $this->db_order('id', 'DESC');
		$orderby = $oinfo['orderby'];
		$order = $oinfo['order'];

		$where = $this->search_where('');
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "bidstatus WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "bidstatus WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page"); 
 		
	}
		
	function extra_tablenav($which) {		  	
	?>
		<a href="<?php echo admin_url('admin.php?page=pn_add_bidstatus'); ?>"><?php _e('Add new', 'pn'); ?></a>		
	<?php 
	} 	  
}