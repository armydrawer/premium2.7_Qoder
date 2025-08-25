<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_caccounts', 'def_adminpage_title_pn_caccounts');
	function def_adminpage_title_pn_caccounts() {
		
		return __('Currency accounts', 'pn');
	}

	add_action('pn_adminpage_content_pn_caccounts', 'def_adminpage_content_pn_caccounts');
	function def_adminpage_content_pn_caccounts() {
		
		premium_table_list();
		
	}
	
}	

add_filter('csl_get_curr_acc', 'def_csl_get_curr_acc', 10, 2);
function def_csl_get_curr_acc($log, $id) {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_caccounts')) {
		$comment = '';
		$last = '';
		$id = intval($id);
			
		$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "curr_accounts WHERE id = '$id'");
		$count = 0;
		$comment = pn_strip_input(is_isset($item, 'text_comment'));
		if (strlen($comment) > 0) {
			$count = 1;
		}
			
		$log['status'] = 'success';
		$log['comment'] = $comment;
		$log['count'] = $count;
		$log['last'] = '';
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Authorisation Error', 'pn');
	}
		
	return $log;
}
	
add_filter('csl_add_curr_acc', 'def_csl_add_curr_acc', 10, 2);
function def_csl_add_curr_acc($log, $id) {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_caccounts')) {
		$text = pn_strip_input(is_param_post('comment'));
		$id = intval($id);
			
		$log['status'] = 'success';
			
		$arr = array();
		$arr['text_comment'] = $text;
		$wpdb->update($wpdb->prefix . 'curr_accounts', $arr, array('id' => $id));
				
	} else {
		
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Authorisation Error', 'pn');
		
	}		
		
	return $log;
}	

add_action('premium_action_pn_caccounts', 'def_premium_action_pn_caccounts');
function def_premium_action_pn_caccounts() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_caccounts'));

	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
		
	if (isset($_POST['save'])) {
							
		do_action('pntable_caccounts_save');
		$arrs['reply'] = 'true';

	} else {
		if (isset($_POST['id']) and is_array($_POST['id'])) {				
						
			if ('active' == $action) {
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "curr_accounts WHERE id = '$id' AND status != '1'");
					if (isset($item->id)) {
						$res = apply_filters('item_caccounts_active_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->update($wpdb->prefix . 'curr_accounts', array('status' => '1'), array('id' => $id));
							do_action('item_caccounts_active', $id, $item, $result);
						}
					}
				}
			}	

			if ('notactive' == $action) {
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "curr_accounts WHERE id = '$id' AND status != '0'");
					if (isset($item->id)) {
						$res = apply_filters('item_caccounts_notactive_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->update($wpdb->prefix . 'curr_accounts', array('status' => '0'), array('id' => $id));
							do_action('item_caccounts_notactive', $id, $item, $result);
						}
					}	
				}
			}	

			if ('delete' == $action) {
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "curr_accounts WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_caccounts_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "curr_accounts WHERE id = '$id'");
							do_action('item_caccounts_delete', $id, $item, $result);
						}
					}
				}
			}
				
			do_action('pntable_caccounts_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';		
		} 
	}
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
}  

class pn_caccounts_Table_List extends PremiumTable {

	function __construct() {  
	
		parent::__construct();
				
		$this->primary_column = 'title';
		$this->save_button = 0;
		
	}
		
	function column_default($item, $column_name) {
		
		if ('id' == $column_name) {
			return $item->id;
		} elseif ('title' == $column_name) {
			return pn_strip_input($item->title);
		} elseif ('tech_title' == $column_name) {
			return pn_strip_input($item->tech_title);			
		} elseif ('account' == $column_name) {
			return pn_strip_input($item->accountnum);				
		} elseif ('inday' == $column_name){
			return is_sum($item->inday);
		} elseif ('inmonth' == $column_name) {
			return is_sum($item->inmonth);
		} elseif ('sinday' == $column_name) {
			$date = current_time('Y-m-d 00:00:00');
			return get_vaccount_sum($item->accountnum, 'in', $date);
		} elseif ('sinmonth' == $column_name) {
			$date = current_time('Y-m-01 00:00:00');			
			return get_vaccount_sum($item->accountnum, 'in', $date);	
		} elseif ('comment' == $column_name) {
			$comment = trim(is_isset($item, 'text_comment'));
			return _comment_label('curr_acc', $item->id, $comment);
		} elseif ('status' == $column_name) {
			$st = $item->status;
			if (0 == $st) {
				return '<span class="bred">' . __('inactive account', 'pn') . '</span>';
			} else { 
				return '<span class="bgreen">' . __('active account', 'pn') . '</span>';
			}
		} elseif ('unique' == $column_name) {
			$st = intval($item->accunique);
			if (0 == $st) {
				return '<span class="bred">' . __('no', 'pn') . '</span>';
			} else { 
				return '<span class="bgreen">' . __('yes', 'pn') . '</span>';
			}			
		} 	
		
		return '';
	}

	function tr_class($tr_class, $item) {
		
		if (0 == $item->status) {
			$tr_class[] = 'tr_red';
		}
		
		return $tr_class;
	}		
		
	function column_cb($item) {
		
		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
	}

	function get_row_actions($item) {
		
		$actions = array(
			'edit'      => '<a href="' . admin_url('admin.php?page=pn_add_caccounts&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
		);			
		
		return $actions;
	}		
		
	function get_columns() {
		
		$columns = array(
			'cb'        => '',
			'id'    => __('ID', 'pn'),
			'title'    => __('Account title', 'pn'),
			'tech_title'    => __('Account title (technical)', 'pn'),
			'account'    => __('Account', 'pn'),	
			'inday' => __('Daily limit', 'pn'),
			'inmonth' => __('Monthly limit', 'pn'),
			'sinday' => __('Amount of exchanges (today)', 'pn'),
			'sinmonth' => __('Amount of exchanges (month)', 'pn'),
			'unique' => __('Uniqueness', 'pn'),
			'status'    => __('Status', 'pn'),
			'comment'     => __('Comment', 'pn'),
		);
		
		return $columns;
	}	
		
	function get_search() {
		
		$search = array();
		$search['item'] = array(
			'view' => 'input',
			'title' => __('Account', 'pn'),
			'default' => pn_strip_input(is_param_get('item')),
			'name' => 'item',
		);
		$search['title'] = array(
			'view' => 'input',
			'title' => __('Account title', 'pn'),
			'default' => pn_strip_input(is_param_get('title')),
			'name' => 'title',
		);
		$search['tech_title'] = array(
			'view' => 'input',
			'title' => __('Account title (technical)', 'pn'),
			'default' => pn_strip_input(is_param_get('tech_title')),
			'name' => 'tech_title',
		);		
		
		return $search;		
	}	
			
	function get_submenu() {
		
		$options = array();
		$options['filter'] = array(
			'options' => array(
				'1' => __('active accounts', 'pn'),
				'2' => __('inactive accounts', 'pn'),
			),
			'title' => '',
		);		
		
		return $options;
	}		

	function get_bulk_actions() {
		
		$actions = array(
			'active'    => __('Activated', 'pn'),
			'notactive'    => __('Deactivate', 'pn'),
			'delete'    => __('Delete', 'pn'),
		);
		
		return $actions;
	}
		 
	function get_sortable_columns() {
		
		$sortable_columns = array( 
			'id'     => array('id', 'DESC'),
			'account' => array('accountnum', false),
			'title'     => array('title', false),
			'inday'     => array('(inday -0.0)', false),
			'inmonth'     => array('(inmonth -0.0)', false),
		);
		
		return $sortable_columns;
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
			$where .= " AND status='1'"; 
		} elseif (2 == $filter) {
			$where .= " AND status='0'";
		}		

		$accountnum = pn_sfilter(pn_strip_input(is_param_get('item')));
		if (strlen($accountnum) > 0)	{ 
			$where .= " AND accountnum LIKE '%$accountnum%'"; 
		}

		$title = pn_sfilter(pn_strip_input(is_param_get('title')));
		if (strlen($title) > 0)	{ 
			$where .= " AND title LIKE '%$title%'"; 
		}

		$tech_title = pn_sfilter(pn_strip_input(is_param_get('tech_title')));
		if (strlen($tech_title) > 0)	{ 
			$where .= " AND tech_title LIKE '%$tech_title%'"; 
		}		
			
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "curr_accounts WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "curr_accounts WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page"); 
 		
	}
		
	function extra_tablenav($which) {
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_caccounts'); ?>"><?php _e('Add new', 'pn'); ?></a>
			<a href="<?php echo admin_url('admin.php?page=pn_add_caccounts_many'); ?>"><?php _e('Add list', 'pn'); ?></a>		
		<?php 
	} 	  
}