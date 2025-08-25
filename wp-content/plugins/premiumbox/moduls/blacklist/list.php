<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_all_blacklist', 'def_adminpage_title_all_blacklist');
	function def_adminpage_title_all_blacklist() {
		
		return __('Blacklist', 'pn');
	}

	add_action('pn_adminpage_content_all_blacklist', 'def_adminpage_content_all_blacklist');
	function def_adminpage_content_all_blacklist() {
		
		premium_table_list();
		
	}
	
}	

add_action('premium_action_all_blacklist', 'def_premium_action_all_blacklist');
function def_premium_action_all_blacklist() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_blacklist'));

	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
		
	if (isset($_POST['save'])) {
							
		do_action('pntable_blacklist_save');
		$arrs['reply'] = 'true';

	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {	
		
			if ('delete' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "blacklist WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_blacklist_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "blacklist WHERE id = '$id'");
							do_action('item_blacklist_delete', $id, $item, $result);
						}
					}
				}	
			}
			
			do_action('pntable_blacklist_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
		} 
	}
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
}

class all_blacklist_Table_List extends PremiumTable {

	function __construct() { 
	
		parent::__construct();
				
		$this->primary_column = 'cvalue';
		$this->save_button = 0;
		
	}
		
	function column_default($item, $column_name) {
			
		if ('ctype' == $column_name) {
			$arr = array('0' => __('invoice', 'pn'), '1' => __('e-mail', 'pn'), '2' => __('mobile phone number', 'pn'), '3' => __('skype', 'pn'), '4' => __('ip', 'pn'), '5' => __('real account', 'pn'));
			return is_isset($arr, $item->meta_key);
		} elseif ('type' == $column_name) {	
			$arr = array('0' => __('by settings', 'pn'), '1' => __('throw an error', 'pn'), '2' => __('stop auto payments', 'pn'));
			return is_isset($arr, $item->black_type);
		} elseif ('comment' == $column_name) {
			$comment_text = pn_strip_text($item->comment_text);	
			$has = 0;
			if ($comment_text) {
				$has = 1;
			}
			return _comment_label('blacklist', $item->id, $has);
		} elseif ('cvalue' == $column_name) {
			return pn_strip_input($item->meta_value);
		} 
			
		return '';
	}	
		
	function column_cb($item) {
		
		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
	}

	function get_row_actions($item) {
		
		$actions = array(
			'edit'      => '<a href="' . admin_url('admin.php?page=all_add_blacklist&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
		);			
		
		return $actions;
	}		
		
	function get_columns() {
		
		$columns = array(
			'cb'        => '',          
			'cvalue'     => __('Value', 'pn'),
			'ctype'    => __('Type', 'pn'),
			'type' => __('Method', 'pn'),
			'comment'     => __('Comment', 'pn'),
		);
		
		return $columns;
	}	
		
	function get_bulk_actions() {
		
		$actions = array(
			'delete'    => __('Delete', 'pn'),
		);
		
		return $actions;
	}
		
	function get_search() {
		
		$search = array();
		$search['item'] = array(
			'view' => 'input',
			'title' => '',
			'default' => pn_strip_input(is_param_get('item')),
			'name' => 'item',
		);
		$options = array(
			'0' => __('everywhere', 'pn'),
			'1' => __('account', 'pn'),
			'2' => __('e-mail', 'pn'),
			'3' => __('mobile phone number', 'pn'),
			'4' => __('skype', 'pn'),
			'5' => __('ip', 'pn'),
			'6' => __('real account', 'pn'),
		);
		$search['witem'] = array(
			'view' => 'select',
			'title' => '',
			'options' => $options,
			'default' => pn_strip_input(is_param_get('witem')),
			'name' => 'witem',
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

		$item = pn_sfilter(pn_strip_input(is_param_get('item')));
		if ($item) { 
			$where .= " AND meta_value LIKE '%$item%'";
		}		
			
		$witem = intval(is_param_get('witem'));
		if ($witem > 0) { 
			$witem = $witem - 1;
			$where .= " AND meta_key = '$witem'";
		}		
			
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "blacklist WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "blacklist WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page"); 
 		
	}
		
	function extra_tablenav($which) {
	?>
		<a href="<?php echo admin_url('admin.php?page=all_add_blacklist'); ?>"><?php _e('Add new', 'pn'); ?></a>
		<a href="<?php echo admin_url('admin.php?page=all_add_blacklist_many'); ?>"><?php _e('Add list', 'pn'); ?></a>
	<?php
	} 	  
}