<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_discount', 'pn_adminpage_title_pn_discount');
	function pn_adminpage_title_pn_discount() {
		
		return __('User discounts', 'pn');
	}

	add_action('pn_adminpage_content_pn_discount', 'def_pn_adminpage_content_pn_discount');
	function def_pn_adminpage_content_pn_discount() {
		
		premium_table_list();
		
	}

}

add_action('premium_action_pn_discount', 'def_premium_action_pn_discount');
function def_premium_action_pn_discount() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_discount'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
		
	if (isset($_POST['save'])) {	
	
		do_action('pntable_discount_save');
		$arrs['reply'] = 'true';
		
	} else {
		if (isset($_POST['id']) and is_array($_POST['id'])) {	
		
			if ('delete' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);		
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_discounts WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_discount_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "user_discounts WHERE id = '$id'");
							do_action('item_discount_delete', $id, $item, $result);
						}
					}	
				}			
			}	
			
			do_action('pntable_discount_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
		}
	}	
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
} 

if (!class_exists('pn_discount_Table_List')) {
	class pn_discount_Table_List extends PremiumTable {

		function __construct() { 
		
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
			
		}
		
		function column_default($item, $column_name) {
			
			if ('discount' == $column_name) {
				return is_sum($item->discount) . '%';	
			} elseif ('title' == $column_name) {
				return is_sum($item->sumec);
			} 
			
			return '';
		}	
		
		function column_cb($item) {
			
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
		}

		function get_row_actions($item) {
			
			$actions = array(
				'edit'      => '<a href="' . admin_url('admin.php?page=pn_add_discount&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
			);			
			
			return $actions;
		}	

		function get_columns() {
			
			$columns = array(
				'cb'        => '',          
				'title'     => __('Total amount of exchanges', 'pn') . ' (' . cur_type() . ')',
				'discount'    => __('Discount (%)', 'pn'),
			);
			
			return $columns;
		}	
		
		function get_bulk_actions() {
			
			$actions = array(
				'delete'    => __('Delete', 'pn'),
			);
			
			return $actions;
		}
		
		function get_sortable_columns() {
			
			$sortable_columns = array( 
				'discount'     => array('(discount -0.0)', 'desc'),
				'title'     => array('(sumec -0.0)', false),
			);
			
			return $sortable_columns;
		}	
		
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$oinfo = $this->db_order('(discount -0.0)', 'DESC');
			$orderby = $oinfo['orderby'];
			$order = $oinfo['order'];		

			$where = $this->search_where('');
			$select_sql = $this->select_sql('');
			if ($this->navi) {
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "user_discounts WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "user_discounts WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");
			
		}
		
		function extra_tablenav($which) {
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_discount'); ?>"><?php _e('Add new', 'pn'); ?></a>
		<?php
		}	  
	}
}