<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_x19_logs', 'def_adminpage_title_pn_x19_logs');
	function def_adminpage_title_pn_x19_logs() {
		
		return __('x19 logs', 'pn');
	} 

	add_action('pn_adminpage_content_pn_x19_logs', 'def_adminpage_content_pn_x19_logs');
	function def_adminpage_content_pn_x19_logs() {
		
		premium_table_list();
		
	}

}

add_action('premium_action_pn_x19_logs', 'def_premium_action_pn_x19_logs');
function def_premium_action_pn_x19_logs() {
	global $wpdb;
	
	_method('post');
	pn_only_caps(array('administrator'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
		
	if (isset($_POST['save'])) {
				
		do_action('pntable_x19logs_save');	
		$arrs['reply'] = 'true';

	} elseif (isset($_POST['delete_all'])) {

		$wpdb->query("DELETE FROM " . $wpdb->prefix . "x19_logs");
		do_action('pntable_x19logs_deleteall');
		$arrs['reply'] = 'true';

	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {				
				
			do_action('pntable_x19logs_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
	
		} 
	}

	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;					
} 

class pn_x19_logs_Table_List extends PremiumTable {

	function __construct() {  
	
		parent::__construct();
				
		$this->primary_column = 'title';
		$this->save_button = 0;
		
	}
		
	function column_default($item, $column_name) {
		
		if ('title' == $column_name) {
			return get_pn_time($item->create_date, 'd.m.Y H:i:s');
		} elseif ('data' == $column_name) {
			return pn_strip_input($item->error_text);
		} elseif ('dir_id' == $column_name) {
			return pn_strip_input($item->dir_id);
		} 
		
		return '';
	}	
		
	function get_columns() {
		
		$columns = array(        
			'title'     => __('Date', 'pn'),
			'data'    => __('Data', 'pn'),
			'dir_id'    => __('Direction id', 'pn'),
		);
		
		return $columns;
	}
		
	function get_search() {
		
		$search = array();
		$search['dir_id'] = array(
			'view' => 'input',
			'title' => __('Direction id', 'pn'),
			'default' => pn_strip_input(is_param_get('dir_id')),
			'name' => 'dir_id',
		);		
		
		return $search;			
	}	

	function prepare_items() {
		global $wpdb; 
			
		$per_page = $this->count_items();
		$current_page = $this->get_pagenum();
		$offset = $this->get_offset();
			
		$oinfo = $this->db_order('id', 'DESC');
		$orderby = $oinfo['orderby'];
		$order = $oinfo['order'];
			
		$where = '';

		$dir_id = intval(is_param_get('dir_id'));
		if ($dir_id > 0) { 
			$where .= " AND dir_id = '$dir_id'";
		} 		
			
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "x19_logs WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "x19_logs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");
  		
	}

	function extra_tablenav($which) {		  	
	?>
		<input type="submit" name="delete_all" style="background: #f4eaee;" value="<?php _e('Delete logs', 'pn'); ?>">	
	<?php 
	}   
}