<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_dgroups', 'def_adminpage_title_pn_dgroups');
	function def_adminpage_title_pn_dgroups() {
			
		return __('Direction groups', 'pn');
	}

	add_action('pn_adminpage_content_pn_dgroups', 'def_adminpage_content_pn_dgroups');
	function def_adminpage_content_pn_dgroups() {
		
		premium_table_list();	

	}

}

add_action('premium_action_pn_dgroups', 'def_premium_action_pn_dgroups');
function def_premium_action_pn_dgroups() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_directions'));
			
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
			
	if (isset($_POST['save'])) {	
		
		do_action('pntable_dgroups_save');
		$arrs['reply'] = 'true';
			
	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {

			if ('delete' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);		
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "dgroups WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_dgroups_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "dgroups WHERE id = '$id'");
							do_action('item_dgroups_delete', $id, $item, $result);
						}
					}
				}		
			}
					
			do_action('pntable_dgroups_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
		} 
	}

	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
}

if (!class_exists('pn_dgroups_Table_List')) {
	class pn_dgroups_Table_List extends PremiumTable {

		function __construct() {  
		
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
			
		}
	 
		function column_default($item, $column_name) {
			
			if ('title' == $column_name) {	
				return pn_strip_input($item->title);
			}
			
			return '';
		}	
		
		function column_cb($item) {
			
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
		}

		function get_row_actions($item) {
			
			$actions = array(
				'edit'      => '<a href="' . admin_url('admin.php?page=pn_add_dgroups&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
			);			
			
			return $actions;
		}	

		function get_columns() {
			
			$columns = array(
				'cb'        => '',          
				'title'     => __('Title', 'pn'),
			);
			
			return $columns;
		}			
		
		function get_bulk_actions() {
			
			$actions = array(
				'delete' => __('Delete', 'pn'),
			);		
			
			return $actions;
		}
		
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$oinfo = $this->db_order('site_order', 'ASC');
			$orderby = $oinfo['orderby'];
			$order = $oinfo['order'];
			
			$where = '';			
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if ($this->navi) {
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "dgroups WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "dgroups WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");
			
		}
		
		function extra_tablenav($which) {
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_dgroups'); ?>"><?php _e('Add new', 'pn'); ?></a>
		<?php
		}	  
	}
}