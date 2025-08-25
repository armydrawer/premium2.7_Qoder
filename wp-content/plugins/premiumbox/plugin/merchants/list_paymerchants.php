<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_paymerchants', 'def_adminpage_title_pn_paymerchants');
	function def_adminpage_title_pn_paymerchants($page) {
		
		return __('Automatic payouts', 'pn');
	} 

	add_action('pn_adminpage_content_pn_paymerchants', 'def_adminpage_content_pn_paymerchants');
	function def_adminpage_content_pn_paymerchants() {
		
		premium_table_list();
		
	}

}

add_action('premium_action_pn_paymerchants', 'def_premium_action_pn_paymerchants');
function def_premium_action_pn_paymerchants() {
	global $wpdb, $premiumbox;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_merchants'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
		
	if (isset($_POST['save'])) {
		
		do_action('pntable_paymerchants_save');	
		$arrs['reply'] = 'true';
			
	} else {		
		
		if (isset($_POST['id']) and is_array($_POST['id'])) {

			if ('active' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exts WHERE id = '$id' AND ext_type = 'paymerchants' AND ext_status != '1'");
					if (isset($item->id)) {
						$wpdb->query("UPDATE " . $wpdb->prefix . "exts SET ext_status = '1' WHERE id = '$id'");
						include_extanded($premiumbox, 'paymerchants', $item->ext_plugin);
						do_action('ext_paymerchants_active_' . $item->ext_plugin, $item->ext_key);
						do_action('ext_paymerchants_active', $item->ext_plugin, $item->ext_key);
					}
				}		
			}

			if ('deactive' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exts WHERE id = '$id' AND ext_type = 'paymerchants' AND ext_status != '0'");
					if (isset($item->id)) {
						$wpdb->query("UPDATE " . $wpdb->prefix . "exts SET ext_status = '0' WHERE id = '$id'");
						include_extanded($premiumbox, 'paymerchants', $item->ext_plugin);
						do_action('ext_paymerchants_deactive_' . $item->ext_plugin, $item->ext_key);
						do_action('ext_paymerchants_deactive', $item->ext_plugin, $item->ext_key);
					}
				}		
			}

			if ('delete' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exts WHERE id = '$id' AND ext_type = 'paymerchants'");
					if (isset($item->id)) {
						$wpdb->query("DELETE FROM " . $wpdb->prefix . "exts WHERE id = '$id'");
						include_extanded($premiumbox, 'paymerchants', $item->ext_plugin);
						do_action('ext_paymerchants_delete_' . $item->ext_plugin, $item->ext_key);
						do_action('ext_paymerchants_delete', $item->ext_plugin, $item->ext_key);
					}
				}		
			}			

			$arrs['reply'] = 'true';
		} 
		
	}	
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
} 
	
add_action('premium_action_pn_paymerchants_activate', 'def_premium_action_pn_paymerchants_activate');
function def_premium_action_pn_paymerchants_activate() {
	global $wpdb, $premiumbox;	

	pn_only_caps(array('administrator', 'pn_merchants'));
		
	$id = intval(is_param_get('id'));	
	if ($id) {
			
		$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exts WHERE id = '$id' AND ext_type = 'paymerchants' AND ext_status != '1'");
		if (isset($item->id)) {
			$wpdb->query("UPDATE " . $wpdb->prefix . "exts SET ext_status = '1' WHERE id = '$id'");
			include_extanded($premiumbox, 'paymerchants', $item->ext_plugin);
			do_action('ext_paymerchants_active_' . $item->ext_plugin, $item->ext_key);
			do_action('ext_paymerchants_active', $item->ext_plugin, $item->ext_key);
		}

	}
			
	$url = pn_admin_filter_data(urldecode(is_param_get('_wp_http_referer')), 'reply') . '&reply=true';
	wp_redirect($url);
	exit;		
}

add_action('premium_action_pn_paymerchants_deactivate', 'def_premium_action_pn_paymerchants_deactivate');
function def_premium_action_pn_paymerchants_deactivate() {
	global $wpdb, $premiumbox;	

	pn_only_caps(array('administrator', 'pn_merchants'));
				
	$id = intval(is_param_get('id'));
	if ($id) {
			
		$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exts WHERE id = '$id' AND ext_type = 'paymerchants' AND ext_status != '0'");
		if (isset($item->id)) {
			$wpdb->query("UPDATE " . $wpdb->prefix . "exts SET ext_status = '0' WHERE id = '$id'");
			include_extanded($premiumbox, 'paymerchants', $item->ext_plugin);
			do_action('ext_paymerchants_deactive_' . $item->ext_plugin, $item->ext_key);
			do_action('ext_paymerchants_deactive', $item->ext_plugin, $item->ext_key);
		}
			
	}

	$url = pn_admin_filter_data(urldecode(is_param_get('_wp_http_referer')), 'reply') . '&reply=true';
	wp_redirect($url);
	exit;		
}	
	
if (!class_exists('pn_paymerchants_Table_List')) {	
	class pn_paymerchants_Table_List extends PremiumTable { 

		function __construct() { 
		
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
			$this->count_items = 50;
			
		}
		
		function get_thwidth() {
			
			$array = array();
			$array['title'] = '200px';
			
			return $array;
		}		
		
		function column_default($item, $column_name) {
			
			if ('title' == $column_name) {	
				return '<strong>' . pn_strip_input($item->ext_title) . '</strong>';	
			} elseif ('script' == $column_name) {	
				$script = is_isset($item, 'ext_plugin');
				$theme = '';
				if (strstr($script, '_theme')) {
					$theme = ' (' . __('Theme', 'pn') . ')';
				}
				$script = str_replace('_theme', '', $script);
				return $script . $theme;
			} elseif ('settings' == $column_name) {	
				return apply_filters('paymerchants_settingtext_' . is_isset($item, 'ext_plugin'), '<span class="bgreen">' . __('ok', 'pn') . '</span>', $item->ext_key);
			} elseif ('security' == $column_name) {	
				return apply_filters('paymerchants_security_' . is_isset($item, 'ext_plugin'), '<span class="bgreen">' . __('ok', 'pn') . '</span>', $item->ext_key, $item);
			} elseif ('key' == $column_name) {
				return $item->ext_key;
			} elseif ('status' == $column_name) {
				$status = intval(is_isset($item, 'ext_status'));
				if (1 != $status) { 
					return '<span class="bred">' . __('inactive auto payout', 'pn') . '</span>'; 
				} else { 
					return '<span class="bgreen">' . __('active auto payout', 'pn') . '</span>'; 
				}
			} elseif ('has' == $column_name) {
				global $premiumbox;
				
				$has = has_extanded_script($premiumbox, 'paymerchants', is_isset($item, 'ext_plugin'));
				if (1 != $has) { 
					return '<span class="bred">' . __('no', 'pn') . '</span>'; 
				} else { 
					return '<span class="bgreen">' . __('yes', 'pn') . '</span>'; 
				}				
			}
			
			return '';
		}	
		
		function get_search() {
			
			$search = array();
			$search['title'] = array(
				'view' => 'input',
				'title' => __('Title', 'pn'),
				'default' => pn_strip_input(is_param_get('title')),
				'name' => 'title',
			);			
			
			return $search;
		}	
		
		function get_submenu() {
			
			$options = array();
			$options['filter'] = array(
				'options' => array(
					'1' => __('active auto payouts', 'pn'),
					'2' => __('inactive auto payouts', 'pn'),
				),
				'title' => '',
			);		
			
			return $options;		
		}
		
		function column_cb($item) {
			
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
		}

		function get_row_actions($item) {
			
			$actions = array();
			$status = intval(is_isset($item, 'ext_status'));
			if (1 == $status) {
				$actions['deactive']  = '<a href="' . pn_link('pn_paymerchants_deactivate', 'post') . '&id=' . is_isset($item, 'id') . '&_wp_http_referer=' . urlencode($_SERVER['REQUEST_URI']) . '">' . __('Deactivate', 'pn') . '</a>';
			} else {
				$actions['active']  = '<a href="' . pn_link('pn_paymerchants_activate', 'post') . '&id=' . is_isset($item, 'id') . '&_wp_http_referer=' . urlencode($_SERVER['REQUEST_URI']) . '">' . __('Activate', 'pn') . '</a>';
			}
			$actions['edit'] = '<a href="' . admin_url('admin.php?page=pn_paymerchants_add&item_id=' . is_isset($item, 'id')) . '">'. __('Settings','pn') . '</a>';
			
			return $actions;
		}			
		
		function get_columns() {
			
			$columns = array(
				'cb'        => '',
				'title'     => __('Title', 'pn'),
				'settings'     => __('Settings', 'pn'),
				'script'     => __('Folder name', 'pn'),
				'status'     => __('Status', 'pn'),
				'security'     => __('Security', 'pn'),
				'key'     => __('Key', 'pn'),
				'has'     => __('Files', 'pn'),
			);
			
			return $columns;
		}	
		
		function tr_class($tr_class, $item) {
			
			$status = intval(is_isset($item, 'ext_status'));
			if (1 != $status) {
				$tr_class[] = 'tr_red';
			}
			
			return $tr_class;
		}		

		function get_bulk_actions() {
			
			$actions = array(
				'active'    => __('Activated', 'pn'),
				'deactive'    => __('Deactivated', 'pn'),
				'delete'    => __('Delete', 'pn'),
			);
			
			return $actions;
		}
		
		function prepare_items() {
			global $wpdb;

			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();

			$oinfo = $this->db_order('ext_title', 'ASC');
			$orderby = $oinfo['orderby'];
			$order = $oinfo['order'];
			
			$where = '';
			$filter = intval(is_param_get('filter'));
			if (1 == $filter) {
				$where = " AND ext_status = '1'";
			} elseif (2 == $filter) {
				$where = " AND ext_status = '0'";
			}

			$title = pn_sfilter(pn_strip_input(is_param_get('title')));
			if ($title) { 
				$where .= " AND ext_title LIKE '%$title%'"; 
			}

			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if ($this->navi) {
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exts WHERE ext_type = 'paymerchants' $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "exts WHERE ext_type = 'paymerchants' $where ORDER BY $orderby $order LIMIT $offset , $per_page");
			
		}
		
 		function extra_tablenav($which) {
			?>
			<a href="<?php echo admin_url('admin.php?page=pn_paymerchants_add'); ?>"><?php _e('Add new', 'pn'); ?></a>
			<?php
		}   		
	}
}