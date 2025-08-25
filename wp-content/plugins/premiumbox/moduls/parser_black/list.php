<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_blackparser', 'def_adminpage_title_pn_blackparser');
	function def_adminpage_title_pn_blackparser($title) {
		
		return __('Auto broker', 'pn');
	} 

	add_action('pn_adminpage_content_pn_blackparser', 'def_pn_adminpage_content_pn_blackparser');
	function def_pn_adminpage_content_pn_blackparser() {
		
		premium_table_list();
		
	}
	
}	

add_action('premium_action_pn_blackparser', 'def_premium_action_pn_blackparser');
function def_premium_action_pn_blackparser() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_directions', 'pn_parser'));

	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
				
	if (isset($_POST['save'])) {
					
		if (isset($_POST['url']) and is_array($_POST['url'])) {
			foreach ($_POST['url'] as $id => $url) {
				$id = intval($id);
				$url = pn_strip_input($url);
							
				$array = array();	
				$array['url'] = $url;
				$wpdb->update($wpdb->prefix . "blackparsers", $array, array('id' => $id));	
			}						
		}
					
		do_action('pntable_blackparsers_save');
		$arrs['reply'] = 'true';	

	} else {		
		if (isset($_POST['id']) and is_array($_POST['id'])) {
			
			if ('delete' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);		
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blackparsers WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_blackparsers_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "blackparsers WHERE id = '$id'");
							do_action('item_blackparsers_delete', $id, $item, $result);
						}	
					}		
				}
			}
			
			do_action('pntable_blackparsers_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';			
		} 		
	}
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
}  

class pn_blackparser_Table_List extends PremiumTable {

	function __construct() {  
	
		parent::__construct();
				
		$this->primary_column = 'title';
		$this->save_button = 1;
		
	}
		
	function get_thwidth() {
		
		$arr = array();
		$arr['title'] = '140px';
		
		return $arr;
	}
		
	function column_default($item, $column_name) {
		
		if ('url' == $column_name) {		
			return '<input type="text" style="width: 100%;" name="url[' . $item->id . ']" value="' . pn_strip_input($item->url) . '" />';				
		} elseif ('title' == $column_name) {
			return pn_strip_input($item->title);
		} 
		
		return '';
	}	
		
	function column_cb($item) {
		
		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
	}

	function get_row_actions($item) {
		
		$actions = array(
			'edit'      => '<a href="' . admin_url('admin.php?page=pn_add_blackparser&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
		);			
		
		return $actions;
	}		

	function get_columns() {
		
		$columns = array(
			'cb'        => '',
			'title'     => __('Website name', 'pn'),
			'url' => __('XML file URL', 'pn'),
		);
		
		return $columns;
	}	
		
	function get_bulk_actions() {
		
		$actions = array(
			'delete'    => __('Delete', 'pn'),
		);
		
		return $actions;
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
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "blackparsers WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "blackparsers WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  
		
	}
		
	function extra_tablenav($which) {		  	
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_blackparser'); ?>"><?php _e('Add new', 'pn'); ?></a>		
		<?php 
	} 	  
}