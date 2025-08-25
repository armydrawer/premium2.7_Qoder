<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_all_logs_api', 'def_adminpage_title_all_logs_api');
	function def_adminpage_title_all_logs_api() {
		
		return __('Logs', 'pn');
	} 

	add_action('pn_adminpage_content_all_logs_api', 'def_adminpage_content_all_logs_api');
	function def_adminpage_content_all_logs_api() {
		
		premium_table_list();
		
	}
	
}	

add_action('premium_action_all_logs_api', 'def_premium_action_all_logs_api');
function def_premium_action_all_logs_api() {
	global $wpdb;
	
	_method('post');
	pn_only_caps(array('administrator', 'pn_api'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
		
	if (isset($_POST['save'])) {
				
		do_action('pntable_apilogs_save');	
		$arrs['reply'] = 'true';

	} elseif (isset($_POST['delete_all'])) {

		$wpdb->query("DELETE FROM " . $wpdb->prefix . "api_logs");
		do_action('pntable_apilogs_deleteall');
		$arrs['reply'] = 'true';

	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {
				
			do_action('pntable_apilogs_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';	
	
		} 
	}

	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;					
} 

class all_logs_api_Table_List extends PremiumTable {

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
			if ($item->headers_data) {
				$data .= '<div><strong>headers:</strong>'. $item->headers_data .'</div>';
			}
			if ($item->post_data) {
				$data .= '<div><strong>post_data:</strong>'. $item->post_data .'</div>';
			}						
			return $data;
		} elseif ('api_login' == $column_name) {
			return pn_strip_input($item->api_login);			
		} elseif ('api_key' == $column_name) {
			return pn_strip_input($item->api_key);
		} elseif ('api_action' == $column_name) {
			return pn_strip_input($item->api_action);				
		} elseif ('ip' == $column_name) {
			return pn_strip_input($item->ip);		
		}
		
		return '';
	}	
		
	function get_columns() {
		
		$columns = array(       
			'date'     => __('Date', 'pn'),
			'data'    => __('Data', 'pn'),
			'api_login'    => __('API login', 'pn'),
			'api_key'    => __('API key', 'pn'),
			'api_action'    => __('API action', 'pn'),
			'ip'     => __('IP', 'pn'),
		);
		
		return $columns;
	}		
		
	function get_search() {
		
		$search = array();
		$search['api_login'] = array(
			'view' => 'input',
			'title' => __('API login', 'pn'),
			'default' => pn_strip_input(is_param_get('api_login')),
			'name' => 'api_login',
		);		
		$search['api_key'] = array(
			'view' => 'input',
			'title' => __('API key', 'pn'),
			'default' => pn_strip_input(is_param_get('api_key')),
			'name' => 'api_key',
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

		$api_login = pn_strip_input(is_param_get('api_login'));
		if ($api_login) {
			$where .= " AND api_login = '$api_login'";
		}

		$api_key = pn_strip_input(is_param_get('api_key'));
		if ($api_key) { 
			$where .= " AND api_key = '$api_key'";
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
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "api_logs WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "api_logs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
	}

	function extra_tablenav($which) {		  	
		?>
			<input type="submit" name="delete_all" style="background: #f4eaee;" value="<?php _e('Delete logs', 'pn'); ?>">	
		<?php 
	} 		  
}