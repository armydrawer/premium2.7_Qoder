<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_all_api', 'def_adminpage_title_all_api');
	function def_adminpage_title_all_api() {
			
			return __('API keys', 'pn');
	}

	add_action('pn_adminpage_content_all_api', 'def_adminpage_content_all_api');
	function def_adminpage_content_all_api() {
			
		premium_table_list();
			
	}

}

add_action('premium_action_all_api', 'def_premium_action_all_api');
function def_premium_action_all_api() {
	global $wpdb;	

	_method('post');
		
	pn_only_caps(array('administrator', 'pn_api'));
			
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();			
			
	if (isset($_POST['save'])) {	
		
		do_action('pntable_api_save');
		$arrs['reply'] = 'true';
				
	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {				
						
			if ('delete' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);		
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "api WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_api_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "api WHERE id = '$id'");
							do_action('item_api_delete', $id, $item, $result);
						}
					}
				}	
			}
					
			do_action('pntable_api_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
		} 
	}
					
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			 
}

if (!class_exists('all_api_Table_List')) {
	class all_api_Table_List extends PremiumTable {

		function __construct() {
			
			parent::__construct();
				
			$this->primary_column = 'api_login';
			$this->save_button = 0;
			
		}
		
		function get_thwidth() {
			
			$array = array();
			$array['api_key'] = '160px';
			
			return $array;
		}	

		function column_default($item, $column_name) {
			
			if ('user' == $column_name) {
				$user_id = $item->user_id;
				if ($user_id > 0) {
					$us ='<a href="'. pn_edit_user_link($user_id) .'">';
					$us .= is_user($item->user_login) . ' (' . $user_id . ')'; 
					$us .='</a>';
				} else {
					$us = __('System', 'pn');
				}
				
				return $us;	
			} elseif ('api_key' == $column_name) {
				return is_api_key($item->api_key);
			} elseif ('api_login' == $column_name) {
				return is_api_key($item->api_login);				
			} elseif ('methods' == $column_name) {
				$methods = array();
				$en = pn_json_decode(is_isset($item, 'api_actions'));
				if (!is_array($en)) { $en = array(); }
				foreach ($en as $en_k => $en_v) {
					$methods[] = $en_k;
				}
				return implode('<br />', $methods);
			} elseif ('date' == $column_name) {
				return get_pn_time($item->create_date, 'd.m.Y, H:i');	
			}
			
			return '';
		}		
		
		function column_cb($item) {
			
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
		}

		function get_row_actions($item) {
			
			$actions = array(
				'edit'      => '<a href="'. admin_url('admin.php?page=all_add_api&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
			);			
			
			return $actions;
		}	
		
		function get_columns() {
			
			$columns = array(
				'cb'        => '',          
				'api_login'    => __('API login', 'pn'),
				'api_key'    => __('API key', 'pn'),
				'user'    => __('User', 'pn'),
				'methods'  => __('Methods available', 'pn'),
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
		
			$search['api_login'] = array(
				'view' => 'input',
				'title' => __('API login', 'pn'),
				'default' => pn_strip_input(is_param_get('api_login')),
				'name' => 'api_login',
			);
			$search['api_key'] = array(
				'view' => 'input',
				'title' => __('API key', 'pn'),
				'default' => pn_strip_input(is_param_get('api_key')),
				'name' => 'api_key',
			);			
			$search['user_id'] = array(
				'view' => 'input',
				'title' => __('User id', 'pn'),
				'default' => pn_strip_input(is_param_get('user_id')),
				'name' => 'user_id',
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

			$user_id = intval(is_param_get('user_id'));
			if ($user_id) {
				$where .= " AND user_id = '$user_id'";
			} 
			
			$api_login = pn_strip_input(is_param_get('api_login'));
			if ($api_login) {
				$where .= " AND api_login = '$api_login'";
			}
			
			$api_key = pn_strip_input(is_param_get('api_key'));
			if ($api_key) {
				$where .= " AND api_key = '$api_key'";
			}			

			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if ($this->navi) {
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "api WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "api WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
 		function extra_tablenav($which) {
		?>
			<a href="<?php echo admin_url('admin.php?page=all_add_api'); ?>"><?php _e('Add new', 'pn'); ?></a>
		<?php
		} 	  
	}
}