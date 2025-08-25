<?php
if (!defined('ABSPATH')) { exit(); }

if (!class_exists('all_users_Table_List')) {

	add_filter('pn_adminpage_title_all_users', 'def_adminpage_title_all_users');
	function def_adminpage_title_all_users() {
		
		return __('Users', 'pn');
	}

	add_action('pn_adminpage_content_all_users', 'def_pn_admin_content_all_users');
	function def_pn_admin_content_all_users() {
		premium_table_list();
	}		
	
	add_action('premium_action_all_users', 'def_premium_action_all_users');
	function def_premium_action_all_users() {
		global $wpdb;	

		_method('post');
		
		$arrs = array(
			'paged' => intval(is_param_post('paged')),
		);
		$action = get_request_action();
				
		$ui = wp_get_current_user();
		
		$search_role = '"administrator"';
				
		if (isset($_POST['changerole'])) {
			
			if (current_user_can('administrator') or current_user_can('promote_users')) {
				
				$roles = array();
				global $wp_roles;
				if (!isset($wp_roles)) {
					$wp_roles = new WP_Roles();
				}
				if (isset($wp_roles)) { 
					foreach ($wp_roles->role_names as $role => $name) {
						$roles[$role] = $name;	
					}
				}
				
				$new_role = is_user_role_name(is_param_post('new_role'));
				
				if (isset($roles[$new_role])) {
					if (isset($_POST['id']) and is_array($_POST['id'])) {
						foreach ($_POST['id'] as $id) {
							$id = intval($id);
							if ($id != $ui->ID) {
								$user_data = get_userdata($id);
								if (isset($user_data->ID)) {
									
									$u_roles = $user_data->roles; if (!is_array($u_roles)) { $u_roles = array(); }

									$enable = 1;
									
									if (in_array('administrator', $u_roles) and 'administrator' != $new_role) {
										$enable = 0;
										$count_admin = $wpdb->get_var("SELECT COUNT(ID) FROM " . $wpdb->prefix . "users tbl_users LEFT OUTER JOIN " . $wpdb->prefix . "usermeta tbl_usermeta ON(tbl_users.ID = tbl_usermeta.user_id) WHERE tbl_users.ID != '$id' AND tbl_usermeta.meta_key = '" . $wpdb->prefix . "capabilities' AND tbl_usermeta.meta_value LIKE '%{$search_role}%'");
										if ($count_admin > 0) {
											$enable = 1;
										}	
									}
									
									if (1 == $enable) {
										
										$u = new WP_User($id);
										$u->add_role($new_role);
										foreach ($u_roles as $u_role) {
											if ($u_role != $new_role) {
												$u->remove_role($u_role);
											}
										}
										
										if ('administrator' == $new_role) {
											$created_data = pn_json_decode($user_data->created_data);
											if (!is_array($created_data)) { $created_data = array(); }
											$created_data['admin_id'] = $ui->ID;
											$created_data['admin_date'] = current_time('mysql');
											$created_data['admin_place'] = 'list';
											$new_user_data = array();
											$new_user_data['created_data'] = pn_json_encode($created_data);
											$wpdb->update($wpdb->prefix . 'users', $new_user_data, array('ID' => $id));
										}
										
									}
								}	
							}
						}
					}				
					
					$arrs['reply'] = 'true';
				}
			}
					
		} elseif (isset($_POST['save'])) {
			
			if (current_user_can('administrator') or current_user_can('edit_users')) {	
				do_action('pntable_users_save');
				$arrs['reply'] = 'true';
			}
			
		} else {	
			if (current_user_can('administrator') or current_user_can('delete_users')) {
				require_once(ABSPATH . 'wp-admin/includes/user.php');
		
				if (isset($_POST['id']) and is_array($_POST['id'])) {	
				
					if ('delete' == $action) {		
						foreach ($_POST['id'] as $id) {
							$id = intval($id);
							if ($id != $ui->ID) {
								$user_data = get_userdata($id);
								if (isset($user_data->ID)) {
									$roles = $user_data->roles; if (!is_array($roles)) { $roles = array(); }
									$enable = 1;
									if (in_array('administrator', $roles)) {
										$enable = 0;
										$count_admin = $wpdb->get_var("SELECT COUNT(ID) FROM " . $wpdb->prefix . "users tbl_users LEFT OUTER JOIN " . $wpdb->prefix . "usermeta tbl_usermeta ON(tbl_users.ID = tbl_usermeta.user_id) WHERE tbl_users.ID != '$id' AND tbl_usermeta.meta_key = '" . $wpdb->prefix . "capabilities' AND tbl_usermeta.meta_value LIKE '%{$search_role}%'");
										if ($count_admin > 0) {
											$enable = 1;
										}
									}
									
									if (1 == $enable) {
										wp_delete_user($id, $ui->ID);
									}
								}	
							}
						}			
					}
					
					do_action('pntable_users_action', $action, $_POST['id']);
					$arrs['reply'] = 'true';					
				}	
			} 
		}
		
		$url = pn_admin_filter_data('', 'reply, paged');
		$url = add_query_args($arrs, $url);
		wp_redirect($url);
		exit;			
	} 

 	class all_users_Table_List extends PremiumTable {

		function __construct() {  
		
			parent::__construct();
			
			$this->primary_column = 'username';
			$this->save_button = 1;
			
		}
		
		function get_thwidth() {
			
			$array = array();
			$array['user_id'] = '30px';
			
			return $array;
		}		
	
		function column_default($item, $column_name) {
			global $cd_ui, $now_roles;
			
			if (!isset($cd_ui[$item->ID])) {
				$cd_ui[$item->ID] = get_userdata($item->ID);
			}
			
			if ('user_id' == $column_name) { 
				return $item->ID;	
			} elseif ('register_date' == $column_name) {
				return get_pn_time($item->user_registered, 'd.m.Y, H:i');
			} elseif ('user_email' == $column_name) {	
				return '<a href="mailto:' . is_email($item->user_email) . '" target="_blank">' . is_email($item->user_email) . '</a>';
			} elseif ('last_adminpanel' == $column_name) {
				$admin_time_last = pn_strip_input(is_isset($item, 'last_adminpanel'));
				if ($admin_time_last) {
					return date("d.m.Y, H:i:s", $admin_time_last);
				}	
			} elseif ('user_browser' == $column_name) {
				$user_browser = get_browser_name(is_isset($item, 'user_browser'), __('Unknown', 'pn'));
				return $user_browser;
			} elseif ('user_ip' == $column_name) {
				$user_ip = pn_strip_input(is_isset($item, 'user_ip'));
				return $user_ip;		
			} elseif ('role' == $column_name) {
				if (isset($cd_ui[$item->ID]->roles)) {
					$role = get_user_role($cd_ui[$item->ID]->roles);
					return is_isset($now_roles, $role);
				}
			} elseif ('user_bann' == $column_name) {
				$user_bann = intval(is_isset($item, 'user_bann'));
				if (1 == $user_bann) {		
					return '<span class="bred">' . __('banned', 'pn') . '</span>';
				} else {
					return __('not banned', 'pn');
				}
			} elseif ('username' == $column_name) {	
				$ui = wp_get_current_user();
				$user_login = '<strong>' . is_user($item->user_login) . '</strong>';
				if ($ui->ID == $item->ID or current_user_can('administrator') or current_user_can('edit_users')) {
					$user_login = '<a href="' . pn_edit_user_link($item->ID) . '" target="_blank"><strong>' . is_user($item->user_login) . '</strong></a>';
				}	
				return $user_login;
			} elseif ('user_phone' == $column_name) {	
				return pn_strip_input($cd_ui[$item->ID]->user_phone);
			} elseif ('user_skype' == $column_name) {	
				return pn_strip_input($cd_ui[$item->ID]->user_skype);				
			} elseif ('user_telegram' == $column_name) {	
				return pn_strip_input($cd_ui[$item->ID]->user_telegram);			
			}
				
			return '';
		}		
	
		function column_cb($item) {
			
			return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->ID . '" />';              
		}		
	
		function get_row_actions($item) {
			
			$actions = array();
			$ui = wp_get_current_user();
			if ($ui->ID == $item->ID or current_user_can('administrator') or current_user_can('edit_users')) {
				$actions['edit'] = '<a href="' . pn_edit_user_link($item->ID) . '">' . __('Edit', 'pn') . '</a>';
			}			
			
			return $actions;
		}	

		function get_columns() {
			
			$columns = array(         
				'cb'        => '',
				'user_id' => 'ID',
				'username' => __('User login', 'pn'),
				'register_date' => __('Registration date', 'pn'),
				'user_email' => __('E-mail', 'pn'),
				'role' => __('Role', 'pn'),
				'last_adminpanel' => __( 'Admin Panel', 'pn' ),
				'user_phone' => __('Mobile phone number', 'pn'),
				'user_skype' => __('Skype', 'pn'),
				'user_telegram' => __('Telegram', 'pn'),
				'user_browser' => __('Browser', 'pn'),
				'user_ip' => __('IP', 'pn'),
				'user_bann' => __('Block', 'pn'),			
			);	
			
			return $columns;
		}
		
		function get_search() {
			
			$search = array();
			$search['user_id'] = array(
				'view' => 'input',
				'title' => __('User ID', 'pn'),
				'default' => pn_strip_input(is_param_get('user_id')),
				'name' => 'user_id',
			);			
			$search['user_login'] = array(
				'view' => 'input',
				'title' => __('User login', 'pn'),
				'default' => is_user(is_param_get('user_login')),
				'name' => 'user_login',
			);		
			$search['user_email'] = array(
				'view' => 'input',
				'title' => __('User email', 'pn'),
				'default' => pn_strip_input(is_param_get('user_email')),
				'name' => 'user_email',
			);	
			
			return $search;
		}
		
		function get_submenu() {
			global $now_roles;
			
			$options = array();
			$options['role'] = array(
				'options' => $now_roles, 
				'title' => '',
			);	
			
			return $options;
		}

		function tr_class($tr_class, $item) {
			
			if (1 == $item->user_bann) {
				$tr_class[] = 'tr_red';
			}
			
			return $tr_class;
		}

		function get_bulk_actions() {
			
			$actions = array();
			
			if (current_user_can('administrator') or current_user_can('delete_users')) {
				$actions = array(
					'delete'    => __('Delete', 'pn'),
				);
			}
			
			return $actions;
		}		

		function get_sortable_columns() {
			
			$sortable_columns = array( 
				'user_id'     => array('tbl_users.ID', 'desc'),
				'username'     => array('tbl_users.user_login', false),
				'register_date' => array('tbl_users.user_registered', false),
				'last_adminpanel' => array('(tbl_users.last_adminpanel -0.0)', false),
			);
			
			return $sortable_columns;
		}	
	
		function prepare_items() {
			global $wpdb; 
			
			$per_page = $this->count_items();
			$current_page = $this->get_pagenum();
			$offset = $this->get_offset();
			
			$oinfo = $this->db_order('tbl_users.ID', 'DESC');
			$orderby = $oinfo['orderby'];
			$order = $oinfo['order'];
			
			$where = '';
			
			$user_id = intval(is_param_get('user_id'));
			if ($user_id > 0) {
				$where .= " AND tbl_users.ID = '$user_id'";
			}			
			
			$user_login = pn_sfilter(pn_strip_input(is_param_get('user_login')));
			if ($user_login) {
				$where .= " AND tbl_users.user_login LIKE '%$user_login%'";
			}
			
			$user_email = pn_sfilter(pn_strip_input(is_param_get('user_email')));
			if ($user_email) {
				$where .= " AND tbl_users.user_email LIKE '%$user_email%'";
			}

			$role = is_user_role_name(is_param_get('role'));
			if ($role) {
				$search_role = '"' . $role . '"';
				$where .= " AND tbl_usermeta.meta_value LIKE '%{$search_role}%'";
			}		
			
			$where = $this->search_where($where);
			$select_sql = $this->select_sql('');
			if ($this->navi) {
				$this->total_items = $wpdb->get_var("SELECT COUNT(ID) FROM " . $wpdb->prefix . "users tbl_users LEFT OUTER JOIN " . $wpdb->prefix . "usermeta tbl_usermeta ON(tbl_users.ID = tbl_usermeta.user_id) WHERE tbl_usermeta.meta_key = '" . $wpdb->prefix . "capabilities' $where");
			}
			$this->items = $wpdb->get_results("SELECT * $select_sql FROM ". $wpdb->prefix ."users tbl_users LEFT OUTER JOIN " . $wpdb->prefix . "usermeta tbl_usermeta ON(tbl_users.ID = tbl_usermeta.user_id)  WHERE tbl_usermeta.meta_key = '" . $wpdb->prefix . "capabilities' $where ORDER BY $orderby $order LIMIT $offset , $per_page");  		
		
			global $now_roles;
			$now_roles = array();
			
			global $wp_roles;
			if (!isset($wp_roles)) {
				$wp_roles = new WP_Roles();
			}
			if (isset($wp_roles)) { 
				foreach ($wp_roles->role_names as $role => $name) {
					$now_roles[$role] = $name;	
				}
			}			
		}	

 		function extra_tablenav($which) {
			global $wpdb, $now_roles;	
		
			if (current_user_can('administrator') or current_user_can('promote_users')) {
			?>
				<?php   
				if ('top' == $which) {
				?>
				<select name="new_role" autocomplete="off">
					<option value="0"><?php _e('Change role to...', 'pn'); ?></option>
					<?php
					if (is_array($now_roles)) {
						foreach ($now_roles as $role_name => $role_title) {
							?>
							<option value='<?php echo $role_name; ?>'><?php echo $role_title; ?></option>
							<?php
						}
					}
					?>
				</select>			
				<input type="submit" name="changerole" value="<?php _e('Change role for users', 'pn'); ?>">
				<?php
				}
				?>
			<?php
			}
			?>
			<?php if (current_user_can('administrator') or current_user_can('add_users')) { ?>
				<a href="<?php echo admin_url('admin.php?page=all_add_user'); ?>"><?php _e('Add new', 'pn'); ?></a>
			<?php } ?>
			<?php  
		}	
	} 
}