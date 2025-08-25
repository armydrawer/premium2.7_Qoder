<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_recalclogs', 'def_adminpage_title_pn_recalclogs');
	function def_adminpage_title_pn_recalclogs() {
		
		return __('Recalculations log', 'pn');
	} 

	add_action('pn_adminpage_content_pn_recalclogs', 'def_adminpage_content_pn_recalclogs');
	function def_adminpage_content_pn_recalclogs() {
		
		premium_table_list();
		
	}

}

add_action('premium_action_pn_recalclogs', 'def_premium_action_pn_recalclogs');
function def_premium_action_pn_recalclogs() {
	global $wpdb;
	
	_method('post');
	pn_only_caps(array('administrator'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
		
	if (isset($_POST['save'])) {
				
		do_action('pntable_recalclogs_save');	
		$arrs['reply'] = 'true';

	} elseif (isset($_POST['delete_all'])) {

		$wpdb->query("DELETE FROM " . $wpdb->prefix . "recalclogs");
		do_action('pntable_recalclogs_deleteall');
		$arrs['reply'] = 'true';

	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {
				
			do_action('pntable_recalclogs_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
	
		} 
	}

	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;					
} 

class pn_recalclogs_Table_List extends PremiumTable {

	function __construct() { 
	
		parent::__construct();
				
		$this->primary_column = 'title';
		$this->save_button = 0;
		
	}
		
	function column_default($item, $column_name) {
		
		if ('title' == $column_name) {
			return get_pn_time($item->create_date, 'd.m.Y H:i:s');
		} elseif ('old_data' == $column_name) {
			
			$arr = pn_json_decode($item->old_data);
			if (!is_array($arr)) { $arr = array(); }
			
			$data = '<div><strong>' . __('Rate', 'pn') . ':</strong>' . is_sum(is_isset($arr, 'course_give')) . '=>' . is_sum(is_isset($arr, 'course_get')) . '</div>';
			
			$lists = array(
				'sum1dc' => __('Amount (with add. fees)', 'pn'),
				'sum1c' => __('Amount (with add. fees and PS fees)', 'pn'),
				'sum2dc' => __('Amount (with add. fees)', 'pn'),
				'sum2c' => __('Amount (with add. fees and PS fees)', 'pn'),			
			);
			
			foreach ($lists as $list_k => $list_v) {
				$data .= '<div><strong>'. $list_v .': </strong>' . is_sum(is_isset($arr, $list_k)) . '</div>';
			}
							
			return $data;
		} elseif ('new_data' == $column_name) {
			
			$arr = pn_json_decode($item->new_data);
			if (!is_array($arr)) { $arr = array(); }
			
			$data = '<div><strong>' . __('Rate', 'pn') . ':</strong>' . is_sum(is_isset($arr, 'course_give')) . '=>' . is_sum(is_isset($arr, 'course_get')) . '</div>';
			
			$lists = array(
				'sum1dc' => __('Amount (with add. fees)', 'pn'),
				'sum1c' => __('Amount (with add. fees and PS fees)', 'pn'),
				'sum2dc' => __('Amount (with add. fees)', 'pn'),
				'sum2c' => __('Amount (with add. fees and PS fees)', 'pn'),			
			);
			
			foreach ($lists as $list_k => $list_v) {
				$data .= '<div><strong>'. $list_v .': </strong>' . is_sum(is_isset($arr, $list_k)) . '</div>';
			}
							
			return $data;			
		} elseif ('bid_id' == $column_name) {
			return pn_strip_input($item->bid_id);		
		}
		
		return '';
	}	
		
	function get_columns() {
		
		$columns = array(        
			'title'     => __('Date', 'pn'),
			'old_data'    => __('Old data', 'pn'),
			'new_data'    => __('New data', 'pn'),
			'bid_id'     => __('Bid id', 'pn'),
		);
		
		return $columns;
	}
		
	function get_search() {
		
		$search = array();
		$search['bid_id'] = array(
			'view' => 'input',
			'title' => __('Bid id', 'pn'),
			'default' => pn_strip_input(is_param_get('bid_id')),
			'name' => 'bid_id',
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

		$bid_id = is_extension_name(is_param_get('bid_id'));
		if ($bid_id) { 
			$where .= " AND bid_id = '$bid_id'";
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
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "recalclogs WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "recalclogs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  	
		
	}

	function extra_tablenav($which) {		  	
	?>
		<input type="submit" name="delete_all" style="background: #f4eaee;" value="<?php _e('Delete logs', 'pn'); ?>">	
	<?php 
	}		  
}