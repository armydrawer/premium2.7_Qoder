<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_currency_codes', 'def_adminpage_title_pn_currency_codes');
	function def_adminpage_title_pn_currency_codes() {
		
		return __('Currency codes', 'pn');
	}

	add_action('pn_adminpage_content_pn_currency_codes', 'def_adminpage_content_pn_currency_codes');
	function def_adminpage_content_pn_currency_codes() {
		
		premium_table_list();
		
	}
	
}	

add_action('premium_action_pn_currency_codes', 'def_premium_action_pn_currency_codes');
function def_premium_action_pn_currency_codes() {
	global $wpdb;
		
	_method('post');
		
	pn_only_caps(array('administrator', 'pn_currency'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();		
		
	$ui = wp_get_current_user();
	$user_id = intval(is_isset($ui, 'ID'));
		
	if (isset($_POST['save'])) {
			
		if (current_user_can('administrator') or current_user_can('pn_change_ir')) {
			if (isset($_POST['internal_rate']) and is_array($_POST['internal_rate'])) {
				foreach ($_POST['internal_rate'] as $id => $internal_rate) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_codes WHERE id = '$id'");
					if (isset($item->id)) {
						
						$internal_rate = is_sum($internal_rate);
						if ($internal_rate <= 0) { $internal_rate = 1; }
									
						$arr = array();				
						$arr['internal_rate'] = $internal_rate;
						$arr['edit_date'] = current_time('mysql');
						$arr['edit_user_id'] = $user_id;
							
						$result = $wpdb->update($wpdb->prefix . 'currency_codes', $arr, array('id' => $id));
						
						do_action('item_currency_code_save', $item->id, $item, $result, $arr);
						
					}
				}
			}
		}
					
		do_action('pntable_currency_codes_save');
		$arrs['reply'] = 'true';
			
	} else {
		if (isset($_POST['id']) and is_array($_POST['id'])) {	
			
			if ('basket' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_codes WHERE id = '$id' AND auto_status != '0'");
					if (isset($item->id)) {
						$res = apply_filters('item_currency_code_basket_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "currency_codes SET auto_status = '0' WHERE id = '$id'");
							do_action('item_currency_code_basket', $id, $item, $result);
						}
					}		
				}	
			}
					
			if ('unbasket' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_codes WHERE id = '$id' AND auto_status != '1'");
					if (isset($item->id)) {
						$res = apply_filters('item_currency_code_unbasket_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "currency_codes SET auto_status = '1' WHERE id = '$id'");
							do_action('item_currency_code_unbasket', $id, $item, $result);
						}
					}		
				}	
			}			
			
			if ('delete' == $action) {
				foreach ($_POST['id'] as $id) {
					$id = intval($id);		
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_codes WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_currency_code_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "currency_codes WHERE id = '$id'");
							do_action('item_currency_code_delete', $id, $item, $result);
						}   
					}
				}		
			}
				
			do_action('pntable_currency_codes_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
			
		} 
	}
						
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
} 

class pn_currency_codes_Table_List extends PremiumTable {
		
	function __construct() { 
	
		parent::__construct();
				
		$this->primary_column = 'title';
		$save_button = 0;
		if (current_user_can('administrator') or current_user_can('pn_change_ir')) {
			$save_button = 1;
		}
		$this->save_button = $save_button;
		
	}
		
	function get_thwidth() {
		
		$arr = array();
		$arr['id'] = '50px';
		
		return $arr;
	}
		
	function column_default($item, $column_name) {
		
		if ('id' == $column_name) {
			return $item->id;
		} elseif ('rate' == $column_name) {
			return '1 ' . cur_type() . ' = ' . is_cc_rate($item->id, $item) . ' ' . is_site_value($item->currency_code_title);			
		} elseif ('od' == $column_name) {	
			if (current_user_can('administrator') or current_user_can('pn_change_ir')) {
				$standart_course_cc = is_cc_check_rate($item);
				if (!$standart_course_cc) {
					return '<input type="text" style="width: 100%;" name="internal_rate[' . $item->id . ']" value="' . is_sum($item->internal_rate) . '" />';
				}	
			} 
			return is_cc_rate($item->id, $item);
		} elseif ('title' == $column_name) {
			return is_site_value($item->currency_code_title);
		} 
		
		return '';
	}	
		
	function column_cb($item) {
		
		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
	}

	function get_row_actions($item) {
		
		$actions = array(
			'edit'      => '<a href="' . admin_url('admin.php?page=pn_add_currency_codes&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
		);			
		
		return $actions;
	}		
		
	function get_columns() {
		
		$columns = array(
			'cb'        => '',
			'id' => __('ID', 'pn'),
			'title'     => __('Currency code', 'pn'),
			'rate'     => __('Internal rate', 'pn'),
			'od'    => __('Internal rate per', 'pn') . ' 1 ' . cur_type() . '',
		);
		
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

	function get_search() {
		
		$search = array();
			
		$search['code'] = array(
			'view' => 'input',
			'title' => __('Currency code', 'pn'),
			'default' => is_site_value(is_param_get('code')),
			'name' => 'code',
		);			
			
		return $search;
	}	
		
	function get_bulk_actions() {
		
		$actions = array(
			'basket'    => __('In basket', 'pn'),
		);
		
		$filter = intval(is_param_get('filter'));
		if (9 == $filter) {
			$actions = array(
				'unbasket' => __('Restore', 'pn'),
				'delete' => __('Delete', 'pn'),
			);
		}
		
		return $actions;
	}
		
	function get_sortable_columns() {
		
		$sortable_columns = array( 
			'id'     => array('id', false),
			'title'     => array('currency_code_title', 'asc'),
		);
		
		return $sortable_columns;
	}	

	function prepare_items() {
		global $wpdb; 
			
		$per_page = $this->count_items();
		$current_page = $this->get_pagenum();
		$offset = $this->get_offset();
				
		$oinfo = $this->db_order('currency_code_title', 'asc');
		$orderby = $oinfo['orderby'];
		$order = $oinfo['order'];	
			
		$where = '';
			
		$filter = intval(is_param_get('filter'));
		if (9 == $filter) {	
			$where .= " AND auto_status = '0'";
		} else {
			$where .= " AND auto_status = '1'";
		}

		$code = is_site_value(is_param_get('code'));
		if ($code) {
			$where .= " AND currency_code_title LIKE '%$code%'";
		}		
			
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "currency_codes WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "currency_codes WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");
  		
	}
		
 	function extra_tablenav($which) {
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_currency_codes'); ?>"><?php _e('Add new', 'pn'); ?></a>
		<?php
	} 	 
}	