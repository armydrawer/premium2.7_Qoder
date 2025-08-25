<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_all_tapibot', 'def_adminpage_title_all_tapibot');
	function def_adminpage_title_all_tapibot() {
		
		return __('T-API bots', 'pn');
	}

	add_action('pn_adminpage_content_all_tapibot', 'def_adminpage_content_all_tapibot');
	function def_adminpage_content_all_tapibot() {
		
		premium_table_list();
			
	}

}

add_action('premium_action_all_tapibot', 'def_premium_action_all_tapibot');
function def_premium_action_all_tapibot() {
	global $wpdb;	

	_method('post');
		
	pn_only_caps(array('administrator', 'pn_tapibot'));
			
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();			
			
	if (isset($_POST['save'])) {	
		
		do_action('pntable_tapibot_save');
		$arrs['reply'] = 'true';
				
	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {				
				
			if ('approve' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibots WHERE id = '$id' AND bot_status != '1'");
					if (isset($item->id)) {
						$res = apply_filters('item_tapibot_approve_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "tapibots SET bot_status = '1' WHERE id = '$id'");
							do_action('item_tapibot_approve', $id, $item, $result);
						}
					}		
				}		
			}

			if ('unapprove' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibots WHERE id = '$id' AND bot_status != '0'");
					if (isset($item->id)) {
						$res = apply_filters('item_tapibot_unapprove_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "tapibots SET bot_status = '0' WHERE id = '$id'");
							do_action('item_tapibot_unapprove', $id, $item, $result);
						}
					}
				}		
			}
				
			if ('delete' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);		
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibots WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_tapibot_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "tapibots WHERE id = '$id'");
							do_action('item_tapibot_delete', $id, $item, $result);
							$wpdb->query("DELETE FROM " . $wpdb->prefix . "tapibot_logs WHERE tapibot_id = '$id'");
							$wpdb->query("DELETE FROM " . $wpdb->prefix . "tapibot_chats WHERE tapibot_id = '$id'");
							$wpdb->query("DELETE FROM " . $wpdb->prefix . "tapibot_bids WHERE tapibot_id = '$id'");
						}
					}
				}	
			}
					
			do_action('pntable_tapibot_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
		} 
	}
					
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			 
}

if (!class_exists('all_tapibot_Table_List')) {
	class all_tapibot_Table_List extends PremiumTable {

		function __construct() { 
		
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
			
		}	

		function column_default($item, $column_name) {
			
			if ('id' == $column_name) {
				return pn_strip_input($item->id);	
			} elseif ('status' == $column_name) {
				if ('0' == $item->bot_status) { 
					return '<span class="bred">' . __('moderating', 'pn') . '</span>'; 
				} else { 
					return '<span class="bgreen">' . __('published', 'pn') . '</span>'; 
				}
			} elseif ('logs' == $column_name) {
				if ('0' == $item->bot_logs) { 
					return '<span class="bred">' . __('no', 'pn') . '</span>'; 
				} else { 
					return '<span class="bgreen">' . __('yes', 'pn') . '</span>'; 
				}				
			} elseif ('test' == $column_name) {	
				return '<a href="' . pn_link('tapibot_testserver') . '&id=' . $item->id . '" class="button" target="_blank">' . __('Test', 'pn') . '</a>';
			} elseif ('title' == $column_name) {	
				return pn_strip_input($item->bot_title);
			}
			
			return '';
		}		
		
		function column_cb($item) {
			
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
		}

		function get_row_actions($item) {
			
			$actions = array(
				'edit'      => '<a href="' . admin_url('admin.php?page=all_add_tapibot&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
			);			
			
			return $actions;
		}	
		
		function get_columns() {
			
			$columns = array(
				'cb'        => '',  
				'id'    => __('ID', 'pn'),
				'title'     => __('Title', 'pn'),
				'test'     => __('Test', 'pn'),
				'logs'  => __('Logs', 'pn'),
				'status'  => __('Status', 'pn'),
			);
			
			return $columns;
		}				
		
		function tr_class($tr_class, $item) {
			
			if (0 == $item->bot_status) {
				$tr_class[] = 'tr_red';
			}
			
			return $tr_class;
		}		
		
		function get_submenu() {
			
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('published', 'pn'),
					'2' => __('moderating', 'pn'),
				),
			);	
			
			return $options;
		}		
		
		function get_bulk_actions() {
			
			$actions = array(
				'approve'    => __('Approve', 'pn'),
				'unapprove'    => __('Decline', 'pn'),
				'delete' => __('Delete', 'pn'),
			);		
			
			return $actions;
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

			$filter = intval(is_param_get('filter'));
			if (1 == $filter) {
				$where = " AND bot_status = '1'";
			} elseif (2 == $filter) {
				$where = " AND bot_status = '0'";
			}			

			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if ($this->navi) {
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "tapibots WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "tapibots WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");
			
		}
		
 		function extra_tablenav($which) {
		?>
			<a href="<?php echo admin_url('admin.php?page=all_add_tapibot'); ?>"><?php _e('Add new', 'pn'); ?></a>
		<?php
		} 	  
	}
}