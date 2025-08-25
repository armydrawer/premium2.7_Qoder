<?php
if (!defined('ABSPATH')) { exit(); }

add_filter('pn_adminpage_title_pn_psys', 'def_adminpage_title_pn_psys');
function def_adminpage_title_pn_psys() {
	
	return __('Payment systems','pn');
}

add_action('pn_adminpage_content_pn_psys', 'def_adminpage_content_pn_psys');
function def_adminpage_content_pn_psys() {
	
	premium_table_list();
	
}

add_action('premium_action_pn_psys', 'def_premium_action_pn_psys');
function def_premium_action_pn_psys() {
	global $wpdb;	

	_method('post');
	
	pn_only_caps(array('administrator', 'pn_currency'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();

	if (isset($_POST['save'])) {
		
		do_action('pntable_psys_save');
		$arrs['reply'] = 'true';
		
	} else {
		if (isset($_POST['id']) and is_array($_POST['id'])) {

			if ('basket' == $action){	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "psys WHERE id = '$id' AND auto_status != '0'");
					if (isset($item->id)) {
						$res = apply_filters('item_psys_basket_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "psys SET auto_status = '0' WHERE id = '$id'");
							do_action('item_psys_basket', $id, $item, $result);
						}
					}		
				}	
			}
					
			if ('unbasket' == $action){	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "psys WHERE id = '$id' AND auto_status != '1'");
					if (isset($item->id)) {
						$res = apply_filters('item_psys_unbasket_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "psys SET auto_status = '1' WHERE id = '$id'");
							do_action('item_psys_unbasket', $id, $item, $result);
						}
					}		
				}	
			}
				
			if ('delete' == $action) {			
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "psys WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_psys_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "psys WHERE id = '$id'");
							do_action('item_psys_delete', $id, $item, $result);
						}
					}
				}			
			}
				
			do_action('pntable_psys_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
		} 
	}
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
} 

class pn_psys_Table_List extends PremiumTable {
		
	function __construct() { 
	
		parent::__construct();
				
		$this->primary_column = 'title';
		$this->save_button = 0;
		
	}

	function get_thwidth() {
		
		$array = array();
		$array['id'] = '30px';
		
		return $array;
	}		
		
	function column_default($item, $column_name) {
		
		if ('id' == $column_name) {
			return $item->id;
		} elseif ('logo' == $column_name) {
			$logo = get_psys_logo($item, 1); 
			if ($logo) {
				return '<img src="' . $logo . '" style="max-width: 40px; max-height: 40px;" alt="" />';
			}
		} elseif ('logo2' == $column_name) {
			$logo = get_psys_logo($item, 2); 
			if ($logo) {
				return '<img src="' . $logo . '" style="max-width: 40px; max-height: 40px;" alt="" />';
			}
		} elseif ('title' == $column_name) {
			return pn_strip_input(ctv_ml($item->psys_title));
		}	
		
		return '';
	}	
		
	function column_cb($item) {
		
		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
	}

	function get_row_actions($item) {
		
		$actions = array(
			'edit'      => '<a href="' . admin_url('admin.php?page=pn_add_psys&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
		);			
		
		return $actions;
	}		
		
	function get_columns() {
		
		$columns = array(
			'cb'        => '',
			'id'     => __('ID', 'pn'),
			'title'     => __('PS title', 'pn'),
			'logo'     => __('Main logo', 'pn'),
		);
		if (get_settings_second_logo()) {
			$columns['logo2'] = __('Additional logo', 'pn');
		}	
		
		return $columns;
	}	

	function get_submenu() {
		
		$options = array();				
		$options['filter'] = array(
			'options' => array(
				'1' => __('published', 'pn'),
				'9' => __('in basket', 'pn'),
			),
		);	
		
		return $options;
	}

	function get_bulk_actions() {
		
		$actions = array(
			'basket'    => __('In basket', 'pn'),
		);
		$filter = intval(is_param_get('filter'));
		if ($filter) {
			$actions = array(
				'unbasket' => __('Restore', 'pn'),
				'delete' => __('Delete', 'pn'),
			);
		}			
		
		return $actions;
	}
		
	function get_sortable_columns() {
		
		$sortable_columns = array( 
			'id' => array('id', false),
			'title' => array('psys_title', 'desc'),
		);
		
		return $sortable_columns;
	}	
		
	function prepare_items() {
		global $wpdb; 
			
		$per_page = $this->count_items();
		$current_page = $this->get_pagenum();
		$offset = $this->get_offset();
				
		$oinfo = $this->db_order('psys_title', 'desc');
		$orderby = $oinfo['orderby'];
		$order = $oinfo['order'];	

		$where = '';
			
		$filter = intval(is_param_get('filter'));
		if (9 == $filter) {	
			$where .= " AND auto_status = '0'";
		} else {
			$where .= " AND auto_status = '1'";
		}			

		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "psys WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "psys WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page"); 
 		
	}
		
 	function extra_tablenav($which) {
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_psys'); ?>"><?php _e('Add new', 'pn'); ?></a>
		<?php
	}   
} 