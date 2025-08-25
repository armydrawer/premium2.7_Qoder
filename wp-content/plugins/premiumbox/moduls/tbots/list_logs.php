<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_all_tapibotlogs', 'def_adminpage_title_all_tapibotlogs');
	function def_adminpage_title_all_tapibotlogs() {
		
		return __('T-Api-bots log', 'pn');
	} 

	add_action('pn_adminpage_content_all_tapibotlogs', 'def_adminpage_content_all_tapibotlogs');
	function def_adminpage_content_all_tapibotlogs() {
		
		premium_table_list();
		
	}

}

add_action('premium_action_all_tapibotlogs', 'def_premium_action_all_tapibotlogs');
function def_premium_action_all_tapibotlogs() {
	global $wpdb;
	
	_method('post');
	
	pn_only_caps(array('administrator', 'pn_tapibot'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
		
	if (isset($_POST['save'])) {
				
		do_action('pntable_tapibotlogs_save');	
		$arrs['reply'] = 'true';

	} elseif (isset($_POST['delete_all'])) {

		$wpdb->query("DELETE FROM " . $wpdb->prefix . "tapibot_logs");
		do_action('pntable_tapibotlogs_deleteall');
		$arrs['reply'] = 'true';

	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {
				
			do_action('pntable_tapibotlogs_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
	
		} 
	}

	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;					
} 

class all_tapibotlogs_Table_List extends PremiumTable {

	function __construct() {  
	
		parent::__construct();
				
		$this->primary_column = 'date';
		$this->save_button = 0;
		
	}
		
	function column_default($item, $column_name) {
		
		if ('date' == $column_name) {
			return get_pn_time($item->create_date, 'd.m.Y H:i:s');
		} elseif ('data' == $column_name) {
			$data = '';
			if ($item->log_url) {
				$data .= '<div><strong>url:</strong>'. pn_strip_input($item->log_url) .'</div>';
			}
			if ($item->log_post) {
				$data .= '<div><strong>post:</strong>'. pn_strip_input($item->log_post) .'</div>';
			}
			if ($item->log_json) {
				$data .= '<div><strong>json:</strong>'. pn_strip_input($item->log_json) .'</div>';
			}		
			if ($item->log_headers) {
				$data .= '<div><strong>headers:</strong>'. pn_strip_input($item->log_headers) .'</div>';
			}
			if ($item->log_answer) {
				$data .= '<div><strong>answer:</strong>'. pn_strip_input($item->log_answer) .'</div>';
			}				
			return $data;
		} elseif ('ip' == $column_name) {
			return pn_strip_input($item->log_ip);
		} elseif ('id' == $column_name) {
			return pn_strip_input($item->tapibot_id);			
		}
		
		return '';
	}	
		
	function get_columns() {
		
		$columns = array(        
			'id'    => __('Bot ID', 'pn'),
			'date'     => __('Date', 'pn'),
			'data'    => __('Data', 'pn'),
			'ip'     => __('IP', 'pn'),
		);
		
		return $columns;
	}
		
	function get_search() {	
	
		$search = array();
		$search['id'] = array(
			'view' => 'input',
			'title' => __('Bot ID', 'pn'),
			'default' => pn_strip_input(is_param_get('id')),
			'name' => 'id',
		);	
		$search['ip'] = array(
			'view' => 'input',
			'title' => __('IP', 'pn'),
			'default' => pn_strip_input(is_param_get('ip')),
			'name' => 'ip',
		);
		$search['date1'] = array(
			'view' => 'datetime',
			'title' => __('Start date', 'pn'),
			'default' => pn_strip_input(is_param_get('date1')),
			'name' => 'date1',
		);
		$search['date2'] = array(
			'view' => 'datetime',
			'title' => __('End date', 'pn'),
			'default' => pn_strip_input(is_param_get('date2')),
			'name' => 'date2',
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

		$id = pn_strip_input(is_param_get('id'));
		if ($id) { 
			$where .= " AND tapibot_id = '$id'";
		}
		
		$ip = pn_strip_input(is_param_get('ip'));
		if ($ip) { 
			$where .= " AND ip = '$ip'";
		}

		$date1 = pn_strip_input(is_param_get('date1'));
		if ($date1) {
			$date = get_pn_date($date1, 'Y-m-d H:i:s');
			$where .= " AND create_date >= '$date'";
		}
			
		$date2 = pn_strip_input(is_param_get('date2'));
		if ($date2) {
			$date = get_pn_date($date2, 'Y-m-d H:i:s');
			$where .= " AND create_date <= '$date'";
		}			
			
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "tapibot_logs WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "tapibot_logs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");
  		
	}

	function extra_tablenav($which) {		  	
	?>
		<input type="submit" name="delete_all" style="background: #f4eaee;" value="<?php _e('Delete logs', 'pn'); ?>">	
	<?php 
	}		 
	
}