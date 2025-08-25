<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_pn_vaccounts', 'def_adminpage_title_pn_vaccounts');
	function def_adminpage_title_pn_vaccounts() {
		
		return __('Currency accounts', 'pn');
	}

	add_action('pn_adminpage_content_pn_vaccounts', 'def_adminpage_content_pn_vaccounts');
	function def_adminpage_content_pn_vaccounts() {
		
		$form = new PremiumForm();
		?>
		<div style="margin: 0 0 20px 0;">
		<?php
		$form->help(__('On shortcodes', 'pn'), 
			//__('display = "0" - show once randomly', 'pn') . '<br />' .
			__('display = "1" - show always randomly', 'pn') . '<br />' .
			__('display = "2" - show consistently within each order', 'pn') . '<br />' .
			__('hide = "0" - visible account number', 'pn') . '<br />' .
			__('hide = "1" - invisible (hide) account number', 'pn') . '<br />' .
			__('copy = "0" - remove copy account function', 'pn') . '<br />' .
			__('copy = "1" - copy account entirely', 'pn') . '<br />' .
			__('copy = "2"  - copy each space-separated account', 'pn') . '<br />'		
		);
		?>
		</div>
		<?php
		premium_table_list();
		
	}
	
}	

add_filter('csl_get_curracc', 'def_csl_get_curracc', 10, 2);
function def_csl_get_curracc($log, $id){
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_vaccounts')) {
		$comment = '';
		$last = '';
		$id = intval($id);
			
		$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_accounts WHERE id = '$id'");
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
	
add_filter('csl_add_curracc', 'def_csl_add_curracc', 10, 2);
function def_csl_add_curracc($log, $id) {
	global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_vaccounts')) {
		$text = pn_strip_input(is_param_post('comment'));
		$id = intval($id);
			
		$log['status'] = 'success';
			
		$arr = array();
		$arr['text_comment'] = $text;
		$wpdb->update($wpdb->prefix . 'currency_accounts', $arr, array('id' => $id));
				
	} else {
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Authorisation Error', 'pn');
	}		
		
	return $log;
}

add_action('premium_action_pn_vaccounts', 'def_premium_action_pn_vaccounts');
function def_premium_action_pn_vaccounts() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_vaccounts'));

	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
		
	if (isset($_POST['save'])) {
							
		do_action('pntable_vaccounts_save');
		$arrs['reply'] = 'true';

	} else {
		if (isset($_POST['id']) and is_array($_POST['id'])) {				
						
			if ('active' == $action) {
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_accounts WHERE id = '$id' AND status != '1'");
					if (isset($item->id)) {
						$res = apply_filters('item_vaccounts_active_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->update($wpdb->prefix . 'currency_accounts', array('status' => '1'), array('id' => $id));
							do_action('item_vaccounts_active', $id, $item, $result);
						}
					}
				}
			}	

			if ('notactive' == $action) {
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_accounts WHERE id = '$id' AND status != '0'");
					if (isset($item->id)) {
						$res = apply_filters('item_vaccounts_notactive_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->update($wpdb->prefix . 'currency_accounts', array('status' => '0'), array('id' => $id));
							do_action('item_vaccounts_notactive', $id, $item, $result);
						}
					}	
				}
			}		

			if ('delete' == $action) {
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "currency_accounts WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_vaccounts_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "currency_accounts WHERE id = '$id'");
							do_action('item_vaccounts_delete', $id, $item, $result);
						}
					}
				}
			}
				
			do_action('pntable_vaccounts_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';		
		} 
	}
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
}  

class pn_vaccounts_Table_List extends PremiumTable {

	function __construct() {   
	
		parent::__construct();
				
		$this->primary_column = 'cid';
		$this->save_button = 0;
		
	}
		
	function column_default($item, $column_name) {
		
		if ('idsnew' == $column_name) {
			$code = "[num_schet currency_id='" . $item->currency_id . "' display='2' hide='0' copy='1']";
			return '<input type="text" style="width: 100%;" class="clpb_item" name="" data-clipboard-text="' . $code . '" value="' . $code . '" />';
		} elseif ('currency' == $column_name) {
			return get_currency_title_by_id($item->currency_id);
		} elseif ('title' == $column_name) {
			$accountnum = pn_strip_input($item->accountnum);
			return $accountnum;			
		} elseif ('inday' == $column_name) {
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
			$text_comment = trim(is_isset($item, 'text_comment'));
			return _comment_label('curracc', $item->id, $text_comment);
		} elseif ('cid' == $column_name) {
			return $item->currency_id;
		} elseif ('unique' == $column_name) {
			$st = intval($item->accunique);
			if (0 == $st) {
				return '<span class="bred">' . __('no', 'pn') . '</span>';
			} else { 
				return '<span class="bgreen">' . __('yes', 'pn') . '</span>';
			}			
		} elseif ('status' == $column_name) {
			$st = $item->status;
			if (0 == $st) {
				return '<span class="bred">' . __('inactive account', 'pn') . '</span>';
			} else { 
				return '<span class="bgreen">' . __('active account', 'pn') . '</span>';
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
			'edit'      => '<a href="' . admin_url('admin.php?page=pn_add_vaccounts&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
		);			
		
		return $actions;
	}		
		
	function get_columns() {
		
		$columns = array(
			'cb'        => '',
			'cid'    => __('Shortcode ID', 'pn'),
			'idsnew'    => __('Shortcode', 'pn'),
			'currency'    => __('Currency name', 'pn'),
			'title'    => __('Account', 'pn'),
			'status'    => __('Status', 'pn'),
			'inday' => __('Daily limit', 'pn'),
			'inmonth' => __('Monthly limit', 'pn'),
			'sinday' => __('Amount of exchanges (today)', 'pn'),
			'sinmonth' => __('Amount of exchanges (month)', 'pn'),
			'unique' => __('Uniqueness', 'pn'),
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
		$currencies = list_currency(__('All currency', 'pn'));
		$search['currency_id'] = array(
			'view' => 'select',
			'title' => __('Currency', 'pn'),
			'default' => pn_strip_input(is_param_get('currency_id')),
			'name' => 'currency_id',
			'options' => $currencies,
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
			'cid'     => array('currency_id', 'DESC'),
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
			
		$currency_id = intval(is_param_get('currency_id'));
		if ($currency_id > 0) { 
			$where .= " AND currency_id='$currency_id'"; 
		}

		$accountnum = pn_sfilter(pn_strip_input(is_param_get('item')));
		if ($accountnum) { 
			$where .= " AND accountnum LIKE '%$accountnum%'"; 
		}		
			
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "currency_accounts WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "currency_accounts WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page"); 
 		
	}
		
	function extra_tablenav($which) {
	?>
		<a href="<?php echo admin_url('admin.php?page=pn_add_vaccounts'); ?>"><?php _e('Add new', 'pn'); ?></a>
		<a href="<?php echo admin_url('admin.php?page=pn_add_vaccounts_many'); ?>"><?php _e('Add list', 'pn'); ?></a>		
	<?php 
	}   
}