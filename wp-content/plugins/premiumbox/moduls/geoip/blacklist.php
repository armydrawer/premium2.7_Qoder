<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_all_geoip_blacklist', 'def_adminpage_title_all_geoip_blacklist');
	function def_adminpage_title_all_geoip_blacklist() {
		
		return __('Blacklist', 'pn');
	}

	add_action('pn_adminpage_content_all_geoip_blacklist', 'def_pn_adminpage_content_all_geoip_blacklist');
	function def_pn_adminpage_content_all_geoip_blacklist() {
		
		premium_table_list();	
			
	}
	
}	

add_action('premium_action_all_geoip_blacklist', 'def_premium_action_all_geoip_blacklist');
function def_premium_action_all_geoip_blacklist() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_geoip'));

	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
			
	if (isset($_POST['save'])) {
			
		do_action('pntable_geoip_blackip_save');
		$arrs['reply'] = 'true';
			
	} else {
		if (isset($_POST['id']) and is_array($_POST['id'])) {			
						
			if ('delete' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "geoip_ips WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_geoip_blackip_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "geoip_ips WHERE id = '$id'");
							do_action('item_geoip_blackip_delete', $id, $item, $result);
						}
					}				
				}		
			}

			do_action('pntable_geoip_blackip_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
		} 
	}
					
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
}

if (!class_exists('all_geoip_blacklist_Table_List')) {
	class all_geoip_blacklist_Table_List extends PremiumTable {

		function __construct() { 
		
			parent::__construct();
					
			$this->primary_column = 'theip';
			$this->save_button = 0;
			
		}
		
		function column_default($item, $column_name) {
			
			if ('theip' == $column_name) {
				return pn_strip_input($item->theip);			
			}  
			
			return '';
		}	
		
		function column_cb($item) {
			
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
		}		
		
		function get_columns() {
			
			$columns = array(
				'cb'        => '',          
				'theip'     => __('IP', 'pn'),
			);
			
			return $columns;
		}	
		
		function get_bulk_actions() {
			
			$actions = array(
				'delete'    => __('Delete', 'pn'),
			);
			
			return $actions;
		}
		
		function get_search() {
			
			$search = array();
			$search['item'] = array(
				'view' => 'input',
				'title' => __('IP', 'pn'),
				'default' => pn_strip_input(is_param_get('item')),
				'name' => 'item',
			);	
			
			return $search;
		}		
		
		function prepare_items() {
			global $wpdb; 
				
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
				
			$oinfo = $this->db_order('id', 'desc');
			$orderby = $oinfo['orderby'];
			$order = $oinfo['order'];
			
			$where = '';

			$item = pn_sfilter(pn_strip_input(is_param_get('item')));
			if ($item) { 
				$where .= " AND theip LIKE '%$item%'";
			}	
				
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if ($this->navi) {
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "geoip_ips WHERE thetype = '0' $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "geoip_ips WHERE thetype = '0' $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav($which) {
		?>
			<a href="<?php echo admin_url('admin.php?page=all_geoip_addblacklist'); ?>"><?php _e('Add new', 'pn'); ?></a>
		<?php
		} 	  
	}
} 