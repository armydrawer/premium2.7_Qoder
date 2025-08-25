<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_all_tapibot_words', 'def_adminpage_title_all_tapibot_words');
	function def_adminpage_title_all_tapibot_words() {
		
		return __('Words', 'pn');
	}

	add_action('pn_adminpage_content_all_tapibot_words', 'def_adminpage_content_all_tapibot_words');
	function def_adminpage_content_all_tapibot_words() {
		
		premium_table_list();
		
	}

}

add_action('premium_action_all_tapibot_words', 'def_premium_action_all_tapibot_words');
function def_premium_action_all_tapibot_words() {
	global $wpdb;	

	_method('post');
		
	pn_only_caps(array('administrator', 'pn_tapibot'));
			
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();			
			
	if (isset($_POST['save'])) {	
		
		do_action('pntable_tapibot_word_save');
		$arrs['reply'] = 'true';
				
	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {				
				
			if ('delete' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);		
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "tapibot_words WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_tapibot_word_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "tapibot_words WHERE id = '$id'");
							do_action('item_tapibot_word_delete', $id, $item, $result);
						}
					}
				}	
			}
					
			do_action('pntable_tapibot_word_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
		} 
	}
					
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
}

if (!class_exists('all_tapibot_words_Table_List')) {
	class all_tapibot_words_Table_List extends PremiumTable {

		function __construct() { 
		
			parent::__construct();
				
			$this->primary_column = 'enter_word';
			$this->save_button = 0;
			
		}	

		function column_default($item, $column_name) {
			
			if ('enter_word' == $column_name) {
				return pn_strip_input($item->enter_word);
			} elseif ('get_word' == $column_name) {	
				return pn_strip_input($item->get_word);
			}
			
			return '';
		}		
		
		function column_cb($item) {
			
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
		}

		function get_row_actions($item) {
			
			$actions = array(
				'edit'      => '<a href="' . admin_url('admin.php?page=all_add_tapibot_words&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
			);			
			
			return $actions;
		}	
		
		function get_columns() {
			
			$columns = array(
				'cb'        => '',  
				'enter_word'     => __('Enter word', 'pn'),
				'get_word'     => __('Replace word', 'pn'),
			);
			
			return $columns;
		}				
		
		function get_bulk_actions() {
			
			$actions = array(
				'delete' => __('Delete', 'pn'),
			);		
			
			return $actions;
		}	
		
		function get_search() {
		
			$search['enter_word'] = array(
				'view' => 'input',
				'title' => __('Enter word', 'pn'),
				'default' => pn_strip_input(is_param_get('enter_word')),
				'name' => 'enter_word',
			);
			$search['get_word'] = array(
				'view' => 'input',
				'title' => __('Replace word', 'pn'),
				'default' => pn_strip_input(is_param_get('get_word')),
				'name' => 'get_word',
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

			$enter_word = pn_strip_input(is_param_get('enter_word'));
			if ($enter_word) {
				$where .= " AND enter_word = '$enter_word'";
			}

			$get_word = pn_strip_input(is_param_get('get_word'));
			if ($get_word) {
				$where .= " AND get_word = '$get_word'";
			}			

			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if ($this->navi) {
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "tapibot_words WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "tapibot_words WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  
			
		}
		
 		function extra_tablenav($which) {
		?>
			<a href="<?php echo admin_url('admin.php?page=all_add_tapibot_words'); ?>"><?php _e('Add new', 'pn'); ?></a>
		<?php
		} 	  
	}
}