<?php
if (!defined('ABSPATH')) { exit(); }

if (!function_exists('def_adminpage_title_all_advantages') and is_admin()) {
	add_filter('pn_adminpage_title_all_advantages', 'def_adminpage_title_all_advantages');
	function def_adminpage_title_all_advantages() {
		
		return __('Advantages', 'pn');
	}
}

if (!function_exists('def_adminpage_content_all_advantages') and is_admin()) {
	add_action('pn_adminpage_content_all_advantages', 'def_adminpage_content_all_advantages');
	function def_adminpage_content_all_advantages() {
		premium_table_list();	
	}
}

if (!function_exists('def_premium_action_all_advantages')) {
	add_action('premium_action_all_advantages', 'def_premium_action_all_advantages');
	function def_premium_action_all_advantages() {
		global $wpdb;	

		_method('post');
		
		pn_only_caps(array('administrator', 'pn_advantages'));
		
		$arrs = array(
			'paged' => intval(is_param_post('paged')),
		);
		$action = get_request_action();
		
		if (isset($_POST['save'])) {
			
			do_action('pntable_advantages_save');	
			$arrs['reply'] = 'true';
			
		} else {	
			if (isset($_POST['id']) and is_array($_POST['id'])) {

				if ('basket' == $action) {	
					foreach ($_POST['id'] as $id) {
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "advantages WHERE id = '$id' AND auto_status != '0'");
						if (isset($item->id)) {
							$res = apply_filters('item_advantages_basket_before', pn_ind(), $id, $item);
							if ($res['ind']) {
								$result = $wpdb->query("UPDATE " . $wpdb->prefix . "advantages SET auto_status = '0' WHERE id = '$id'");
								do_action('item_advantages_basket', $id, $item, $result);
							}
						}		
					}	
				}
					
				if ('unbasket' == $action) {	
					foreach ($_POST['id'] as $id) {
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "advantages WHERE id = '$id' AND auto_status != '1'");
						if (isset($item->id)) {
							$res = apply_filters('item_advantages_unbasket_before', pn_ind(), $id, $item);
							if ($res['ind']) {
								$result = $wpdb->query("UPDATE " . $wpdb->prefix . "advantages SET auto_status = '1' WHERE id = '$id'");
								do_action('item_advantages_unbasket', $id, $item, $result);
							}
						}		
					}	
				}

				if ('approve' == $action) {	
					foreach ($_POST['id'] as $id) {
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "advantages WHERE id = '$id' AND status != '1'");
						if (isset($item->id)) {
							$res = apply_filters('item_advantages_approve_before', pn_ind(), $id, $item);
							if ($res['ind']) {
								$result = $wpdb->query("UPDATE " . $wpdb->prefix . "advantages SET status = '1' WHERE id = '$id'");
								do_action('item_advantages_approve', $id, $item, $result);
							}
						}		
					}		
				}

				if ('unapprove' == $action) {	
					foreach ($_POST['id'] as $id) {
						$id = intval($id);	
						$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "advantages WHERE id = '$id' AND status != '0'");
						if (isset($item->id)) {
							$res = apply_filters('item_advantages_unapprove_before', pn_ind(), $id, $item);
							if ($res['ind']){
								$result = $wpdb->query("UPDATE " . $wpdb->prefix . "advantages SET status = '0' WHERE id = '$id'");
								do_action('item_advantages_unapprove', $id, $item, $result);
							}
						}
					}		
				}	
			
				if ('delete' == $action) {		
					foreach ($_POST['id'] as $id) {
						$id = intval($id);
						$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "advantages WHERE id = '$id'");
						if (isset($item->id)) {
							$res = apply_filters('item_advantages_delete_before', pn_ind(), $id, $item);
							if ($res['ind']) {
								$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "advantages WHERE id = '$id'");
								do_action('item_advantages_delete', $id, $item, $result);
							}
						}
					}		
				}
				
				do_action('pntable_advantages_action', $action, $_POST['id']);
				$arrs['reply'] = 'true';	
			} 
		}

		$url = pn_admin_filter_data('', 'reply, paged');
		$url = add_query_args($arrs, $url);
		wp_redirect($url);
		exit;			
	}
}

if (!class_exists('all_advantages_Table_List')) {
	class all_advantages_Table_List extends PremiumTable {

		function __construct() {  
		
			parent::__construct();
				
			$this->primary_column = 'title';
			$this->save_button = 0;
		}

		function column_default($item, $column_name) {
			
			if ('cimage' == $column_name) {
				$img = pn_strip_input($item->img);
				if ($img) {
					return '<img src="' . $img . '" style="width: 50px;" alt="" />';	
				}	
			} elseif ('status' == $column_name) {
				if ('0' == $item->status) { 
					return '<span class="bred">' . __('moderating', 'pn') . '</span>'; 
				} else { 
					return '<span class="bgreen">' . __('published', 'pn') . '</span>'; 
				}	
			} elseif ('title' == $column_name) {	
				return pn_strip_input(ctv_ml($item->title));
			}
			
			return '';
		}	
		
		function column_cb($item) {
			
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
		}

		function get_row_actions($item) {
			
			$actions = array(
				'edit'      => '<a href="' . admin_url('admin.php?page=all_add_advantages&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
			);			
			
			return $actions;
		}	

		function get_columns() {
			
			$columns = array(
				'cb'        => '',          
				'title'     => __('Title', 'pn'),
				'cimage'    => __('Image', 'pn'),
				'status'  => __('Status', 'pn'),
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
					'9' => __('in basket', 'pn'),
				),
			);	
			
			return $options;
		}	

		function get_bulk_actions() {
			
			$actions = array(
				'approve'    => __('Approve', 'pn'),
				'unapprove'    => __('Decline', 'pn'),
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
		
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$oinfo = $this->db_order('site_order', 'ASC');
			$orderby = $oinfo['orderby'];
			$order = $oinfo['order'];
			
			$where = '';
			
			$filter = intval(is_param_get('filter'));
			if (1 == $filter) {
				$where = " AND status = '1'";
			} elseif (2 == $filter) {
				$where = " AND status = '0'";
			}

			if (9 == $filter) {	
				$where .= " AND auto_status = '0'";
			} else {
				$where .= " AND auto_status = '1'";
			}			
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if ($this->navi) {
				$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "advantages WHERE id > 0 $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "advantages WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		}
		
		function extra_tablenav($which) {
			?>
			<a href="<?php echo admin_url('admin.php?page=all_add_advantages'); ?>"><?php _e('Add new', 'pn'); ?></a>
			<?php
		}	  
	}
}