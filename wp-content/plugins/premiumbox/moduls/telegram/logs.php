<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_all_telegram_logs', 'def_adminpage_title_all_telegram_logs');
	function def_adminpage_title_all_telegram_logs() {
		
		return __('Telegram logs', 'pn');
	} 

	add_action('pn_adminpage_content_all_telegram_logs', 'def_adminpage_content_all_telegram_logs');
	function def_adminpage_content_all_telegram_logs() {
		
		premium_table_list();
		
	}
	
}	

add_action('premium_action_all_telegram_logs', 'def_premium_action_all_telegram_logs');
function def_premium_action_all_telegram_logs() {
	global $wpdb;
	
	_method('post');
	pn_only_caps(array('administrator'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();		
		
	if (isset($_POST['save'])) {
				
		do_action('pntable_telegramlogs_save');	
		$arrs['reply'] = 'true';

	} elseif (isset($_POST['delete_all'])) {

		$wpdb->query("DELETE FROM " . $wpdb->prefix . "telegram_logs");
		do_action('pntable_telegramlogs_deleteall');
		$arrs['reply'] = 'true';

	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {				
				
			do_action('pntable_telegramlogs_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
	
		} 
	}

	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;					
} 

class all_telegram_logs_Table_List extends PremiumTable {

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
		} elseif ('ip' == $column_name) {
			return pn_strip_input($item->log_ip);			
		} elseif ('place' == $column_name) {
			$place = intval($item->place);
			if (0 == $place) {
				return __('Bot log', 'pn');
			} else {
				return __('User log', 'pn');
			}
		} 
		
		return '';
	}	
		
	function get_columns() {
		
		$columns = array(        
			'title'     => __('Date', 'pn'),
			'data'    => __('Data', 'pn'),
			'place'    => __('Type', 'pn'),
			'ip'  => __('IP', 'pn'),
		);
		
		return $columns;
	}
		
	function get_search() {
		
		$search = array();
		$search['place'] = array(
			'view' => 'select',
			'title' => __('Type', 'pn'),
			'default' => pn_strip_input(is_param_get('place')),
			'options' => array('0' => __('All', 'pn'), '1' => __('Bot log', 'pn'), '2' => __('User log', 'pn')),
			'name' => 'place',
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

		$place = intval(is_param_get('place'));
		if ($place > 0) { 
			$place = $place - 1;
			$where .= " AND place = '$place'";
		} 		
			
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) { 
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "telegram_logs WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "telegram_logs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page"); 
 		
	}

	function extra_tablenav($which) {		  	
	?>
		<input type="submit" name="delete_all" style="background: #f4eaee;" value="<?php _e('Delete logs', 'pn'); ?>">	
	<?php 
	} 	  
}