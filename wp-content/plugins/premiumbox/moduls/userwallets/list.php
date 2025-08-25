<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_userwallets', 'def_adminpage_title_pn_userwallets');
	function def_adminpage_title_pn_userwallets() {
		
		return __('User accounts', 'pn');
	}

	add_action('pn_adminpage_content_pn_userwallets', 'def_adminpage_content_pn_userwallets');
	function def_adminpage_content_pn_userwallets() {
		
		premium_table_list();
		
	}

}

add_action('premium_action_pn_userwallets', 'def_premium_action_pn_userwallets');
function def_premium_action_pn_userwallets() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_userwallets'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();

	if (isset($_POST['save'])) {
		
		do_action('pntable_userwallets_save');
		$arrs['reply'] = 'true';
		
	} else {
		if (isset($_POST['id']) and is_array($_POST['id'])) {
			
			if ('basket' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE id = '$id' AND auto_status != '0'");
					if (isset($item->id)) {
						$res = apply_filters('item_userwallets_basket_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "user_wallets SET auto_status = '0' WHERE id = '$id'");
							do_action('item_userwallets_basket', $id, $item, $result);
						}
					}		
				}	
			}
					
			if ('unbasket' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE id = '$id' AND auto_status != '1'");
					if (isset($item->id)) {
						$res = apply_filters('item_userwallets_unbasket_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "user_wallets SET auto_status = '1' WHERE id = '$id'");
							do_action('item_userwallets_unbasket', $id, $item, $result);
						}
					}		
				}	
			}			
			
			if ('delete' == $action) {
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "user_wallets WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_userwallets_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "user_wallets WHERE id = '$id'");
							do_action('item_userwallets_delete', $id, $item, $result);
						}
					}
				}
			}
				
			do_action('pntable_userwallets_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
		}
	}
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;		
} 

class pn_userwallets_Table_List extends PremiumTable {

	function __construct() { 
	
		parent::__construct();
				
		$this->primary_column = 'title';
		$this->save_button = 0;
		
	}
		
	function column_default($item, $column_name) {
			
		if ('user' == $column_name) {
			return '<a href="' . pn_edit_user_link($item->user_id) . '">' . is_user($item->user_login) . '</a>'; 
		} elseif ('ps' == $column_name) { 	
			return get_currency_title_by_id($item->currency_id);		
		} elseif ('title' == $column_name) {
			return pn_strip_input($item->accountnum);
		} 
		
		return '';
	}	
		
	function column_cb($item) {
		
		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
	}

	function get_row_actions($item) {
		
		$actions = array(
			'edit'      => '<a href="' . admin_url('admin.php?page=pn_add_userwallets&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
		);			
		
		return $actions;
	}	

	function get_columns() {
		
		$columns = array(
			'cb'        => '',          
			'title'     => __('Account number', 'pn'),
			'user'    => __('User', 'pn'),
			'ps' => __('PS', 'pn'),
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
		
	function get_search() {
		
		$search = array();
		$search['user'] = array(
			'view' => 'input',
			'title' => __('User', 'pn'),
			'default' => pn_strip_input(is_param_get('user')),
			'name' => 'user',
		);
		$search['accountnum'] = array(
			'view' => 'input',
			'title' => __('Account number', 'pn'),
			'default' => pn_strip_input(is_param_get('accountnum')),
			'name' => 'accountnum',
		);		
		$currency = list_currency(__('All currency', 'pn'));
		$search['currency_id'] = array(
			'view' => 'select',
			'options' => $currency,
			'title' => __('Currency', 'pn'),
			'default' => pn_strip_input(is_param_get('currency_id')),
			'name' => 'currency_id',
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

		$filter = intval(is_param_get('filter'));
		if (9 == $filter) {	
			$where .= " AND auto_status = '0'";
		} else {
			$where .= " AND auto_status = '1'";
		}	

		$user = pn_sfilter(pn_strip_input(is_param_get('user')));
		if ($user) { 
			$where .= " AND user_login LIKE '%$user%'";
		}

		$accountnum = pn_sfilter(pn_strip_input(is_param_get('accountnum')));
		if ($accountnum) { 
			$where .= " AND accountnum LIKE '%$accountnum%'";
		}

		$currency_id = intval(is_param_get('currency_id'));
		if ($currency_id) { 
			$where .= " AND currency_id = '$currency_id'";
		}		
					
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "user_wallets WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "user_wallets WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  	
		
	}
		
	function extra_tablenav($which) {	 
	?>		
		<a href="<?php echo admin_url('admin.php?page=pn_add_userwallets'); ?>"><?php _e('Add new', 'pn'); ?></a>
	<?php
	} 	  
}