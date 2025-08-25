<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {
	
	add_filter('pn_adminpage_title_all_roles', 'pn_admin_title_all_roles');
	function pn_admin_title_all_roles() {
		
		return __('User roles', 'pn');
	}

	add_action('pn_adminpage_content_all_roles', 'def_pn_admin_content_all_roles');
	function def_pn_admin_content_all_roles() {
		premium_table_list();		
	} 

}

add_action('premium_action_all_roles', 'def_premium_action_all_roles');
function def_premium_action_all_roles() {
	global $wpdb;	

	_method('post');
	
	pn_only_caps(array('administrator'));	

	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
			
	if (isset($_POST['save'])) {
			
		do_action('pntable_all_roles_save');
		$arrs['reply'] = 'true';
			
	} else {		
		if (isset($_POST['id']) and is_array($_POST['id'])) {	
		
			if ('delete' == $action) {
				foreach ($_POST['id'] as $id) {
					$name = is_user_role_name($id);
					if ($name) {
						if ('administrator' != $name and 'users' != $name) {
							remove_role($name);
						} 
					}					
				}
			}	
				
			do_action('pntable_all_roles_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';		
		} 
	}
		
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
}

if (!class_exists('all_roles_Table_List')) {
	class all_roles_Table_List extends PremiumTable {

		function __construct() {
			
			parent::__construct();
			
			$this->primary_column = 'role_name';
			
		}
		
		function column_default($item, $column_name) {
			
			if ('system_role_name' == $column_name) {
				return is_isset($item, 'name');		
			} elseif ('role_name' == $column_name) {
				return pn_strip_input(is_isset($item, 'title'));
			}	
			
			return '';
		}	
		
		function column_cb($item) {
			
			$name = is_isset($item, 'name');
			if ('administrator' != $name and 'users' != $name) {
				return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . is_isset($item, 'name') . '" />';              
			}
			
			return '';
		}	

		function get_row_actions($item) {
			
			$actions = array(
				'edit' => '<a href="' . admin_url('admin.php?page=all_add_roles&item_key=' . is_isset($item, 'name')) . '">' . __('Edit','pn') . '</a>',
			);
			
			return $actions;
		}		
		
		function get_columns() {
			
			$columns = array(
				'cb'        => '',
				'role_name'     => __('Role name', 'pn'),
				'system_role_name' => __('System role name', 'pn'),
			);
			
			return $columns;
		}	

		function get_bulk_actions() {
			
			$actions = array(
				'delete'    => __('Delete', 'pn'),
			);
			
			return $actions;
		}

		function prepare_items() {
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$start_items = array();
			
			global $wp_roles;
			if (!isset($wp_roles)) {
				$wp_roles = new WP_Roles();
			}		
			if (isset($wp_roles)) {
				foreach ($wp_roles->role_names as $role => $name) {
					$start_items[] = array(
						'title' => $name,
						'name' => is_user_role_name($role),
					);
				}
			}
			
			$this->items = array_slice($start_items, $offset, $per_page);
			if ($this->navi) {
				$this->total_items = count($start_items);
			}	
		}		
		
  		function extra_tablenav($which) {		  	
		?>
			<a href="<?php echo admin_url('admin.php?page=all_add_roles'); ?>"><?php _e('Add new', 'pn'); ?></a>
		<?php 
		}
	}	
} 