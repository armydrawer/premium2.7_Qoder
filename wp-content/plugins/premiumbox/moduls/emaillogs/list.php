<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('pn_adminpage_title_all_emlogs', 'def_adminpage_title_all_emlogs');
function def_adminpage_title_all_emlogs($page) {
	
	return __('E-mail logs', 'pn');
} 

add_action('pn_adminpage_content_all_emlogs', 'def_pn_adminpage_content_all_emlogs');
function def_pn_adminpage_content_all_emlogs() {
	premium_table_list();
}

add_action('premium_action_all_emlogs', 'def_premium_action_all_emlogs');
function def_premium_action_all_emlogs() {
	global $wpdb;
		
	_method('post');
		
	pn_only_caps(array('administrator'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
		
	if (isset($_POST['save'])) {	

		do_action('pntable_emlogs_save');	
		$arrs['reply'] = 'true';

	} elseif (isset($_POST['delete_all'])) {

		$wpdb->query("DELETE FROM " . $wpdb->prefix . "email_logs");
		do_action('pntable_emlogs_deleteall');
		$arrs['reply'] = 'true';

	} else {	

		if (isset($_POST['id']) and is_array($_POST['id'])) {
			do_action('pntable_emlogs_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';	
		} 

	}

	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
} 

class all_emlogs_Table_List extends PremiumTable {

	function __construct() { 
	
		parent::__construct();
				
		$this->primary_column = 'date';
		
	}
		
	function column_default($item, $column_name) {
		
		if ('date' == $column_name) {
			return get_pn_time($item->create_date, 'd.m.Y H:i:s');
		} else {
			return pn_strip_input(is_isset($item, $column_name));					
		}
		
		return '';
	}	
		
	function get_columns() {
		
		$columns = array(         
			'date'     => __('Date', 'pn'),
			'subject'     => __('Subject of e-mail', 'pn'),
			'ot_name'     => __('Header e-mail', 'pn'),
			'to_mail'     => __('Recipient e-mail', 'pn'),
			'html'     => __('Text', 'pn'),
		);
		
		return $columns;
	}	

	function get_sortable_columns() {
		
		$sortable_columns = array( 
			'date'     => array('create_date', 'desc'),
		);
		
		return $sortable_columns;
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
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "email_logs WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "email_logs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
	}

	function extra_tablenav($which) {		  	
	?>
		<input type="submit" name="delete_all" style="background: #f4eaee;" value="<?php _e('Delete logs', 'pn'); ?>">	
	<?php 
	}		  
}