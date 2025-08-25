<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_merchantlogs', 'def_adminpage_title_pn_merchantlogs');
	function def_adminpage_title_pn_merchantlogs() {
		
		return __('Merchants log', 'pn');
	} 

	add_action('pn_adminpage_content_pn_merchantlogs', 'def_adminpage_content_pn_merchantlogs');
	function def_adminpage_content_pn_merchantlogs() {
		
		premium_table_list();
		
	}

}

add_action('premium_action_pn_merchantlogs', 'def_premium_action_pn_merchantlogs');
function def_premium_action_pn_merchantlogs() {
	global $wpdb;
	
	_method('post');
	pn_only_caps(array('administrator'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
		
	if (isset($_POST['save'])) {
				
		do_action('pntable_merchantlogs_save');	
		$arrs['reply'] = 'true';

	} elseif (isset($_POST['delete_all'])) {

		$wpdb->query("DELETE FROM " . $wpdb->prefix . "merch_logs");
		do_action('pntable_merchantlogs_deleteall');
		$arrs['reply'] = 'true';

	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {
				
			do_action('pntable_merchantlogs_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
	
		} 
	}

	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;					
} 

class pn_merchantlogs_Table_List extends PremiumTable {

	function __construct() { 
	
		parent::__construct();
				
		$this->primary_column = 'title';
		$this->save_button = 0;
		
	}
		
	function column_default($item, $column_name) {
		
		if ('title' == $column_name) {
			return get_pn_time($item->create_date, 'd.m.Y H:i:s');
		} elseif ('data' == $column_name) {
			$data = '';
			if ($item->url) {
				$data .= '<div><strong>url:</strong>' . $item->url . '</div>';
			}
			if ($item->headers) {
				$data .= '<div><strong>headers:</strong>' . $item->headers . '</div>';
			}
			if ($item->json_data) {
				$data .= '<div><strong>post_data:</strong>' . $item->json_data . '</div>';
			}		
			if ($item->result) {
				$data .= '<div><strong>result:</strong>' . $item->result . '</div>';
			}
			if ($item->error) {
				$data .= '<div><strong>post_data:</strong>' . $item->error . '</div>';
			}				
			return $data;
		} elseif ('merchant' == $column_name) {
			return is_extension_name($item->m_name) . '(' . is_extension_name($item->m_id) . ')';
		} elseif ('ip' == $column_name) {
			return pn_strip_input($item->ip);		
		}
		
		return '';
	}	
		
	function get_columns() {
		
		$columns = array(        
			'title'     => __('Date', 'pn'),
			'data'    => __('Data', 'pn'),
			'merchant'    => __('Merchant', 'pn'),
			'ip'     => __('IP', 'pn'),
		);
		
		return $columns;
	}
		
	function get_search() {
		
		global $wpdb;
		$options = array();
		$options[0] = '--' . __('All', 'pn') . '--';
		$items = $wpdb->get_results("SELECT DISTINCT(m_id) FROM " . $wpdb->prefix . "merch_logs ORDER BY m_id ASC");  		
		foreach ($items as $item) {
			$options[$item->m_id] = is_extension_name($item->m_id);
		}
			
		$search = array();
		$search['merchant'] = array(
			'view' => 'select',
			'title' => __('Merchant', 'pn'),
			'default' => is_extension_name(is_param_get('merchant')),
			'options' => $options,
			'name' => 'merchant',
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

		$merchant = is_extension_name(is_param_get('merchant'));
		if ($merchant) { 
			$where .= " AND m_id = '$merchant'";
		}  	
			
		$ip = pn_strip_input(is_param_get('ip'));
		if ($ip) { 
			$where .= " AND ip LIKE '%$ip%'";
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
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "merch_logs WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "merch_logs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  	
		
	}

	function extra_tablenav($which) {		  	
	?>
		<input type="submit" name="delete_all" style="background: #f4eaee;" value="<?php _e('Delete logs', 'pn'); ?>">	
	<?php 
	}		  
}