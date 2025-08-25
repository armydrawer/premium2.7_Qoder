<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_coupons', 'pn_adminpage_title_pn_coupons');
	function pn_adminpage_title_pn_coupons() {
		
		return __('Discount coupons', 'pn');
	}

	add_action('pn_adminpage_content_pn_coupons', 'def_pn_adminpage_content_pn_coupons');
	function def_pn_adminpage_content_pn_coupons() {
		
		premium_table_list();
		
	}

}

add_action('premium_action_pn_coupons', 'def_premium_action_pn_coupons');
function def_premium_action_pn_coupons() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_coupons'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
		
	if (isset($_POST['save'])) {	
	
		do_action('pntable_coupons_save');
		$arrs['reply'] = 'true';
		
	} else {
		if (isset($_POST['id']) and is_array($_POST['id'])) {				
			
			if ('approve' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "coupons WHERE id = '$id' AND status != '1'");
					if (isset($item->id)) {
						$res = apply_filters('item_coupons_approve_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "coupons SET status = '1' WHERE id = '$id'");
							do_action('item_coupons_approve', $id, $item, $result);
						}
					}		
				}	
			}

			if ('unapprove' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "coupons WHERE id = '$id' AND status != '0'");
					if (isset($item->id)) {	
						$res = apply_filters('item_coupons_unapprove_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "coupons SET status = '0' WHERE id = '$id'");
							do_action('item_coupons_unapprove', $id, $item, $result);	
						}
					}
				}		
			}
				
			if ('delete' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);		
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "coupons WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_coupons_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "coupons WHERE id = '$id'");
							do_action('item_coupons_delete', $id, $item, $result);
						}
					}	
				}			
			}
			
			do_action('pntable_coupons_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
		}
	}	
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
} 

if (!class_exists('pn_coupons_Table_List')) {
	class pn_coupons_Table_List extends PremiumTable {

		function __construct() {   
		
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
			
		}
		
		function column_default($item, $column_name) {
			
			if ('discount' == $column_name) {
				return is_sum($item->discount) . '%';	
			} elseif ('title' == $column_name) {
				return is_coupon($item->coupon_code);
			} elseif ('ctype' == $column_name) {
				if ('0' == $item->coupon_type) { 
					return '<strong>'. __('disposable', 'pn') .'</strong>'; 
				} elseif ('1' == $item->coupon_type) {
					return '<strong>'. __('reusable', 'pn') .'</strong>';
				}
			} elseif ('used' == $column_name) {
				if ('0' == $item->coupon_used) { 
					return '<span class="bred">'. __('no', 'pn') .'</span>'; 
				} else { 
					return '<span class="bgreen">'. __('yes', 'pn') .'</span>'; 
				}				
			} elseif ('status' == $column_name) {
				if ('0' == $item->status) { 
					return '<span class="bred">'. __('moderating', 'pn') .'</span>'; 
				} else { 
					return '<span class="bgreen">'. __('published', 'pn') .'</span>'; 
				}				
			} 
			
			return '';
		}	
		
		function column_cb($item) {
			
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
		}

		function get_row_actions($item) {
			
			$actions = array(
				'edit'      => '<a href="' . admin_url('admin.php?page=pn_add_coupons&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
			);			
			
			return $actions;
		}	

		function get_columns() {
			
			$columns = array(
				'cb'        => '',          
				'title'     => __('Coupon code', 'pn'),
				'discount'    => __('Discount (%)', 'pn'),
				'ctype'    => __('Coupon type', 'pn'),
				'used'    => __('Used', 'pn'),
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
					'1' => __('disposable', 'pn'),
					'2' => __('reusable', 'pn'),
				),
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

		function get_search() {
			
			$search = array();
			$search['coupon_code'] = array(
				'view' => 'input',
				'title' => __('Coupon code', 'pn'),
				'default' => is_coupon(is_param_get('coupon_code')), 
				'name' => 'coupon_code',
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

			$filter = intval(is_param_get('filter'));
			if (1 == $filter) {
				$where .= " AND status = '1'";
			} elseif (2 == $filter) {
				$where .= " AND status = '0'";
			} 
					
			$filter2 = intval(is_param_get('filter2'));
			if (1 == $filter2) {
				$where .= " AND coupon_type = '0'";
			} elseif (2 == $filter2) {
				$where .= " AND coupon_type = '1'";
			}

			$coupon_code = is_coupon(is_param_get('coupon_code'));
			if ($coupon_code) {
				$where .= " AND coupon_code = '$coupon_code'";
			}			
				
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if ($this->navi) {
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "coupons WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "coupons WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page"); 
			
		}
		
		function extra_tablenav($which) {
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_coupons'); ?>"><?php _e('Add new', 'pn'); ?></a>
		<?php
		}	  
	}
}