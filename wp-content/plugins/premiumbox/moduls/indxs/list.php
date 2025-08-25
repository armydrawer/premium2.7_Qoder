<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_indxs', 'def_adminpage_title_pn_indxs');
	function def_adminpage_title_pn_indxs($title) {
		
		return __('Custom coefficients', 'pn');
	} 

	add_action('pn_adminpage_content_pn_indxs', 'def_adminpage_content_pn_indxs');
	function def_adminpage_content_pn_indxs() {
		
		$form = new PremiumForm();
 		?>
		<div style="margin: 0 0 10px 0;">
			<?php 
			$text = sprintf(__('For creating an exchange rate you can use the following mathematical operations:<br><br> 
			* multiplication<br> 
			/ division<br> 
			- subtraction<br> 
			+ addition<br><br> 
			An example of a formula where two exchange rates are multiplied: [bitfinex_btcusd_last_price] * [cbr_usdrub]<br> 
			For more detailed instructions, follow the <a href="%s" target="_blank" rel="noreferrer noopener">link</a>.', 'pn'), 'https://premium.gitbook.io/main/parseryi-kursov-valyut');
			$form->help(__('Example of formulas for parser', 'pn'), $text);
			?>
		</div>
		<?php 
		premium_table_list();
	}
	
}	

add_filter('csl_get_indxs', 'def_csl_get_indxs', 10, 2);
function def_csl_get_indxs($log, $id){
global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_indxs')) {
		$comment = '';
		$last = '';
		$id = intval($id);
			
		$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "indxs WHERE id = '$id'");
		$count = 0;
		$comment = pn_strip_input(is_isset($item, 'indx_comment'));
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
	
add_filter('csl_add_indxs', 'def_csl_add_indxs', 10, 2);
function def_csl_add_indxs($log, $id){
global $wpdb;
	
	if (current_user_can('administrator') or current_user_can('pn_indxs')) {
		
		$text = pn_strip_input(is_param_post('comment'));
		$id = intval($id);
			
		$log['status'] = 'success';
			
		$arr = array();
		$arr['indx_comment'] = $text;
		$wpdb->update($wpdb->prefix . 'indxs', $arr, array('id' => $id));
				
	} else {
		
		$log['status'] = 'error';
		$log['status_code'] = 1; 
		$log['status_text'] = __('Authorisation Error', 'pn');
		
	}		
		
	return $log;
}	

add_action('premium_action_pn_indxs', 'def_premium_action_pn_indxs');
function def_premium_action_pn_indxs() {
	global $wpdb;	

	_method('post');
	
	pn_only_caps(array('administrator', 'pn_indxs'));

	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
				
	if (isset($_POST['save'])) {
					
		if (isset($_POST['indx_value']) and is_array($_POST['indx_value'])) {
			foreach ($_POST['indx_value'] as $id => $indx_value) {
				$id = intval($id);
				$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "indxs WHERE id = '$id'");
				if (isset($item->id)) {
					
					$indx_value = pn_strip_input($indx_value);
                    $indx_value = str_replace(',', '.', $indx_value);
								
					$array = array();	
					$array['indx_value'] = $indx_value;
					$result = $wpdb->update($wpdb->prefix . "indxs", $array, array('id' => $id));
					
					do_action('item_indxs_save', $item->id, $item, $result, $array);
				
				}
			}					
		}
			
		do_action('pntable_indxs_save');
		$arrs['reply'] = 'true';

	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {
			
			if ('delete' == $action) {				
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "indxs WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_indxs_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "indxs WHERE id = '$id'");
							do_action('item_indxs_delete', $id, $item, $result);
						}	
					}		
				}
			}
				
			do_action('pntable_indxs_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';					
		} 		
	}
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
}  

class pn_indxs_Table_List extends PremiumTable {

	function __construct() { 
	
		parent::__construct();
				
		$this->primary_column = 'title';
		$this->save_button = 1;
	}

	function column_cb($item) {
		
		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
	}
		
	function get_row_actions($item) {
		
		$actions = array(
			'edit'      => '<a href="' . admin_url('admin.php?page=pn_add_indxs&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
		);			
		return $actions;
	}		
		
	function column_default($item, $column_name) {
			
		if ('title' == $column_name) {
			return '<input type="text" class="clpb_item" style="width: 100%;" name="" data-clipboard-text="[' . is_inxs(is_isset($item, 'indx_name')) . ']" value="[' . is_inxs(is_isset($item, 'indx_name')) . ']" />'; 
		} elseif ('val1' == $column_name) {		
			return '<textarea style="width: 100%; height: 100px;" name="indx_value[' . $item->id . ']">' . pn_strip_input($item->indx_value) . '</textarea>';	
		} elseif ('calc1' == $column_name) {
			return get_formula($item->indx_value, 0, 0);  
		} elseif ('cat' == $column_name) {	
			$cats = get_inxs_cats(); 
			return is_isset($cats, $item->cat_id);
		} elseif ('type' == $column_name) {	
			if (0 == $item->indx_type) {
				return __('index value', 'pn');
			} else {
				return __('adding formula to rate', 'pn');
			}				
		} elseif ('comment' == $column_name) {
			$comment = trim(is_isset($item, 'indx_comment'));
			return _comment_label('indxs', $item->id, $comment);			
		} 
			
		return '';
	}		

	function get_columns() {
		
		$columns = array(
			'cb' => '',
			'title'     => __('Index name', 'pn'),
			'val1'     => __('Index value formula', 'pn'),
			'calc1' => __('Index value', 'pn'),	
			'type' => __('Index type', 'pn'),
			'comment' => __('Comment', 'pn'),
		);
		
		$cats = get_inxs_cats();
		if (count($cats) > 1) {
			$columns['cat'] = __('Index category', 'pn');
		}
		
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
		
		$options = array();
		$options[0] = '--' . __('All','pn') . '--';
			
		$cats = get_inxs_cats();  		
		foreach ($cats as $cat_id => $cat_name) {
			$cat_id = $cat_id + 1;
			$options[$cat_id] = $cat_name;
		}
		
		if (count($cats) > 1) {
			$search['cat_id'] = array(
				'view' => 'select',
				'title' => __('Index category', 'pn'),
				'default' => is_param_get('cat_id'),
				'options' => $options,
				'name' => 'cat_id',
			);
		}

		$search['item'] = array(
			'view' => 'input',
			'title' => __('Index name', 'pn'),
			'default' => pn_strip_input(is_param_get('item')),
			'name' => 'item',
		);		
		
		return $search;
	}	
		
	function prepare_items() {
		global $wpdb; 
			
		$per_page = $this->count_items();
		$current_page = $this->get_pagenum();
		$offset = $this->get_offset();
			
		$oinfo = $this->db_order('site_order', 'ASC');
		$orderby = $oinfo['orderby'];
		$order = $oinfo['order'];			
			
		$where = '';
		
		$cat_id = intval(is_param_get('cat_id'));
		if ($cat_id) {
			$cat_id = $cat_id - 1;
			$where .= " AND cat_id = '$cat_id'";
		}
		
		$item = pn_sfilter(pn_strip_input(is_param_get('item')));
		if ($item) {
			$where .= " AND indx_name LIKE '%$item%'";
		}
			
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "indxs WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "indxs WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page"); 
 		
	}
		
 	function extra_tablenav($which) {		  	
	?>
		<a href="<?php echo admin_url('admin.php?page=pn_add_indxs'); ?>"><?php _e('Add new', 'pn'); ?></a>		
	<?php 
	}   
}