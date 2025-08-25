<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_all_usfield', 'def_adminpage_title_all_usfield');
	function def_adminpage_title_all_usfield() {
		
		return __('Verification fields', 'pn');
	}

	add_action('pn_adminpage_content_all_usfield', 'def_pn_adminpage_content_all_usfield');
	function def_pn_adminpage_content_all_usfield() {
		
		premium_table_list();

	}

}

add_action('premium_action_all_usfield', 'def_premium_action_all_usfield');
function def_premium_action_all_usfield() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_userverify'));

	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
					
	if (isset($_POST['save'])) {
							
		do_action('pntable_usfield_save');
		$arrs['reply'] = 'true';

	} else {			
		if (isset($_POST['id']) and is_array($_POST['id'])) {				
							
			if ('active' == $action) {
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_field WHERE id = '$id' AND status != '1'");
					if (isset($item->id)) {
						$res = apply_filters('item_usfield_active_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->update($wpdb->prefix . 'uv_field', array('status' => '1'), array('id' => $id));
							do_action('item_usfield_active', $id, $item, $result);
						}
					}
				}
			}	

			if ('deactive' == $action) {
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_field WHERE id = '$id' AND status != '0'");
					if (isset($item->id)) {		
						$res = apply_filters('item_usfield_deactive_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->update($wpdb->prefix . 'uv_field', array('status' => '0'), array('id' => $id));
							do_action('item_usfield_deactive', $id, $item, $result);
						}
					}
				}
			}	

			if ('delete' == $action) {
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_field WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_usfield_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "uv_field WHERE id = '$id'");
							do_action('item_usfield_delete', $id, $item, $result);
						}
					}
				}
			}
					
			do_action('pntable_usfield_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';		
		}
	}	
					
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
} 

if (!class_exists('all_usfield_Table_List')) {
	class all_usfield_Table_List extends PremiumTable {

		function __construct() { 
		
			parent::__construct();
					
			$this->primary_column = 'title';
			$this->save_button = 0;
			
		}
			
		function column_default($item, $column_name) {
				
			if ('type' == $column_name) {
				$types = array('0' => __('Text input field', 'pn'), '1' => __('File', 'pn'), '2' => __('Select', 'pn'));
				return is_isset($types, $item->fieldvid);
			} elseif ('lang' == $column_name) {
				if (strlen($item->locale) < 2) {
					return __('All', 'pn');
				} elseif (function_exists('get_title_forkey')) {
					return get_title_forkey($item->locale);
				}			
			} elseif ('required' == $column_name) {	
				if (0 == $item->uv_req) { 
					return '<span class="bred">'. __('No', 'pn') .'</span>'; 
				} else { 
					return '<span class="bgreen">'. __('Yes', 'pn') .'</span>'; 
				}			
			} elseif ('title' == $column_name) {		
				return pn_strip_input(ctv_ml($item->title));
			} elseif ('status' == $column_name) {	
				if (0 == $item->status) { 
					return '<span class="bred">'. __('inactive field', 'pn') .'</span>'; 
				} else { 
					return '<span class="bgreen">'. __('active field', 'pn') .'</span>'; 
				}			
			} 
				
			return '';
		}	
			
		function column_cb($item) {
			
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
		}

		function get_row_actions($item) {
			
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=all_add_usfield&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
			);			
			
			return $actions;
		}		

		function get_columns() {
			
			$columns = array(
				'cb'        => '',
				'title'     => __('Custom field name', 'pn'),
				'type'    => __('Verification field type', 'pn'),
				'required' => __('Required field', 'pn'),
				'lang' => __('Language', 'pn'),
				'status'    => __('Status', 'pn'),
			);
			
			return $columns;
		}	
			
		function tr_class($tr_class, $item) {
			
			if (0 == $item->status) {
				$tr_class[] = 'tr_red';
			}
			
			return $tr_class;
		}	
			
		function get_bulk_actions() {
			
			$actions = array(
				'active'    => __('Activate', 'pn'),
				'deactive'    => __('Deactivate', 'pn'),
				'delete'    => __('Delete', 'pn'),
			);
			
			return $actions;
		}
					
		function get_submenu() {
			
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('active fields', 'pn'),
					'2' => __('inactive fields', 'pn'),
				),
				'title' => '',
			);	
			
			return $options;
		}		
			
		function prepare_items() {
			global $wpdb; 
				
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
				
			$oinfo = $this->db_order('uv_order', 'ASC');
			$orderby = $oinfo['orderby'];
			$order = $oinfo['order'];
				
			$where = '';
				
			$filter = intval(is_param_get('filter'));
			if (1 == $filter) { 
				$where .= " AND status='1'"; 
			} elseif (2 == $filter) {
				$where .= " AND status='0'";
			}
				
			$where = $this->search_where($where);
			if ($this->navi) {
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "uv_field WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_field WHERE id > 0 $where ORDER BY fieldvid ASC, $orderby $order LIMIT $offset , $per_page"); 
			
		}
			
 		function extra_tablenav($which) {		  			  	
		?>
			<a href="<?php echo admin_url('admin.php?page=all_add_usfield'); ?>"><?php _e('Add new', 'pn'); ?></a>		
		<?php 
		} 	  
	}
}	