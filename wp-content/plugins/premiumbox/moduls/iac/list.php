<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_iac', 'def_adminpage_title_pn_iac');
	function def_adminpage_title_pn_iac() {
		
		return __('Adjustment', 'pn');
	}

	add_action('pn_adminpage_content_pn_iac', 'def_pn_adminpage_content_pn_iac');
	function def_pn_adminpage_content_pn_iac() {
		
		premium_table_list();
		
	}
	
}

add_action('premium_action_pn_iac', 'def_premium_action_pn_iac');
function def_premium_action_pn_iac() {
	global $wpdb;

	_method('post');
	pn_only_caps(array('administrator'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
			
	if (isset($_POST['save'])) {
							
		do_action('pntable_iac_save');
		$arrs['reply'] = 'true';

	} else {
		if (isset($_POST['id']) and is_array($_POST['id'])) {				
				
			if ('approve' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "iac WHERE id = '$id' AND status != '1'");
					if (isset($item->id)) {
						$res = apply_filters('item_iac_approve_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "iac SET status = '1' WHERE id = '$id'");
							do_action('item_iac_approve', $id, $item, $result);
						}
					}		
				}		
			}

			if ('unapprove' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "iac WHERE id = '$id' AND status != '0'");
					if (isset($item->id)) {	
						$res = apply_filters('item_iac_unapprove_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "iac SET status = '0' WHERE id = '$id'");
							do_action('item_iac_unapprove', $id, $item, $result);	
						}
					}
				}		
			}
				
			if ('delete' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "iac WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_iac_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "iac WHERE id = '$id'");
							do_action('item_iac_delete', $id, $item, $result);
						}
					}					
				}		
			}
				
			do_action('pntable_iac_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';				
		} 
	}	
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
} 

class pn_iac_Table_List extends PremiumTable {

	function __construct() {  
	
		parent::__construct();
				
		$this->primary_column = 'create';
		$this->save_button = 0;
		
	}
	
	function column_default($item, $column_name) {
		
		if ('sum' == $column_name) {
			return get_sum_color($item->amount);
		} elseif ('create' == $column_name) {
			return get_pn_time($item->create_date, 'd.m.Y H:i');			
		} elseif ('user' == $column_name) {
			$user_id = $item->user_id;
			$us = '';
			if ($user_id > 0) {
				$us .='<a href="' . pn_edit_user_link($user_id) . '">';
				$us .= $item->user_id; 
				$us .='</a>';
			}
			return $us;
		} elseif ('title' == $column_name) {
			return pn_strip_input($item->title);
		} elseif ('bid_id' == $column_name) {
			return pn_strip_input($item->bid_id);			
		} elseif ('code' == $column_name) {
			$currency_codes = list_currency_codes('--' . __('Undefined', 'pn') . '--');
			return is_isset($currency_codes, $item->currency_code_id);
		} elseif ('status' == $column_name) {
			if ('0' == $item->status) { 
				return '<span class="bred">' . __('moderating', 'pn') . '</span>'; 
			} else { 
				return '<span class="bgreen">' . __('ok', 'pn') . '</span>'; 
			}		
		} 
			
		return '';
	}	
	
	function column_cb($item) { 
	
		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
	}

	function get_row_actions($item) {
		
		$actions = array(
			'edit'      => '<a href="' . admin_url('admin.php?page=pn_iac_add&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
		);			
		
		return $actions;
	}		
	
	function get_columns() {
		
		$columns = array(
			'cb'        => '',
			'create' => __('Creation date', 'pn'),
			'user' => __('User ID', 'pn'),
			'sum' => __('Amount', 'pn'),
			'code' => __('Currency code', 'pn'),
			'bid_id' => __('Bid id', 'pn'),
			'title'     => __('Comment', 'pn'),
			'status'  => __('Status', 'pn'),
		);
		
		return $columns;
	}	
	
	function tr_class($tr_class, $item) {
		
		if (0 == $item->status) {
			$tr_class[] = 'tr_red';
		}
		
		return $tr_class;
	}	
	
	function get_search() {
		
		$search = array();
		$currency_codes = list_currency_codes(__('All codes', 'pn'));
		$search['currency_code_id'] = array(
			'view' => 'select',
			'title' => __('Code', 'pn'),
			'default' => pn_strip_input(is_param_get('currency_code_id')),
			'options' => $currency_codes,
			'name' => 'currency_code_id',
		);
		$search['user_id'] = array(
			'view' => 'input',
			'title' => __('User ID', 'pn'),
			'default' => pn_strip_input(is_param_get('user_id')),
			'name' => 'user_id',
		);		
		$search['bid_id'] = array(
			'view' => 'input',
			'title' => __('Bid id', 'pn'),
			'default' => pn_strip_input(is_param_get('bid_id')),
			'name' => 'bid_id',
		);	
		
		return $search;
	}		
			
	function get_submenu() {
		
		$options = array();
		$options['filter'] = array(
			'options' => array(
				'1' => __('published', 'pn'),
				'2' => __('moderating', 'pn'),
			),
		);		
		$options['filter2'] = array(
			'options' => array(
				'1' => __('expenditure', 'pn'),
				'2' => __('income', 'pn'),
			),
			'title' => '',
		);	
		
		return $options;
	}	

	function get_bulk_actions() {
		
		$actions = array(
			'approve'    => __('Approve', 'pn'),
			'unapprove'    => __('Decline', 'pn'),
			'delete'    => __('Delete', 'pn'),
		);
		
		return $actions;
	}
    
	function get_sortable_columns() {
		
		$sortable_columns = array( 
			'create'     => array('create_date', 'desc'),
			'sum'     => array('(amount -0.0)', false),
		);
		
		return $sortable_columns;
	}	
		
	function prepare_items() {
		global $wpdb; 
			
		$per_page = $this->count_items();
		$current_page = $this->get_pagenum();
		$offset = $this->get_offset();
			
		$oinfo = $this->db_order('create_date', 'DESC');
		$orderby = $oinfo['orderby'];
		$order = $oinfo['order'];				
			
		$where = '';
		
		$filter = intval(is_param_get('filter'));
		if (1 == $filter) {
			$where = " AND status = '1'";
		} elseif (2 == $filter) {
			$where = " AND status = '0'";
		}
		
		$filter2 = intval(is_param_get('filter2'));
		if (1 == $filter2) { 
			$where .= " AND amount > 0"; 
		} elseif (2 == $filter2) {
			$where .= " AND amount <= 0";
		}		

		$currency_code_id = intval(is_param_get('currency_code_id'));
		if ($currency_code_id > 0) { 
			$where .= " AND currency_code_id='$currency_code_id'"; 
		}

		$user_id = intval(is_param_get('user_id'));
		if ($user_id > 0) { 
			$where .= " AND user_id='$user_id'"; 
		}	

		$bid_id = intval(is_param_get('bid_id'));
		if ($bid_id > 0) { 
			$where .= " AND bid_id='$bid_id'"; 
		}			
			
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "iac WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "iac WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page"); 
 		
	}	

	function extra_tablenav($which) {
	?>
		<a href="<?php echo admin_url('admin.php?page=pn_iac_add'); ?>"><?php _e('Add new', 'pn'); ?></a>		
	<?php 
	} 
}