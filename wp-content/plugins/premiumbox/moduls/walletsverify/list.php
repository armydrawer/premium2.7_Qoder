<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_userwallets_verify', 'pn_admin_title_pn_userwallets_verify');
	function pn_admin_title_pn_userwallets_verify() {
		
		return __('Account verification', 'pn');
	}

	add_action('pn_adminpage_content_pn_userwallets_verify', 'def_pn_adminpage_content_pn_userwallets_verify');
	function def_pn_adminpage_content_pn_userwallets_verify() {
		
		premium_table_list();
		
	}

}

add_action('premium_action_pn_userwallets_verify', 'def_premium_action_pn_userwallets_verify');
function def_premium_action_pn_userwallets_verify() {
	global $wpdb;

	_method('post');
	pn_only_caps(array('administrator', 'pn_userwallets'));	

	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
			
	if (isset($_POST['save'])) {
							
		do_action('pntable_walletsverify_save');
		$arrs['reply'] = 'true';

	} else {
		if (isset($_POST['id']) and is_array($_POST['id'])) {
			
			if ('true' == $action) {		
				foreach ($_POST['id'] as $id) {
					$id = intval($id);		
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_wallets WHERE id = '$id' AND status != '1'");
					if (isset($item->id)) {
						
						$user_wallet_id = $item->user_wallet_id;
							
						$res = apply_filters('item_walletsverify_true_before', pn_ind(), $id, $item);
						if ($res['ind']) {
								
							do_action('item_walletsverify_true', $id, $item);
							$arr = array();
							$arr['status'] = 1;
							$result = $wpdb->update($wpdb->prefix . 'uv_wallets', $arr, array('id' => $item->id));
							if ($result) {
								do_action('item_walletsverify_true_after', $id, $item);
							}

							$arr = array();
							$arr['verify'] = 1;
							$wpdb->update($wpdb->prefix . 'user_wallets', $arr, array('id' => $user_wallet_id));
							do_action('item_userwallets_verify', $user_wallet_id);
				
							$now_locale = get_locale();
							$user_locale = pn_strip_input($item->locale);

							$purse = pn_strip_input($item->wallet_num);
							$ui = get_userdata($item->user_id);
								
							set_locale($user_locale);

							$notify_tags = array();
							$notify_tags['[user_login]'] = $item->user_login;
							$notify_tags['[purse]'] = $purse;
							$notify_tags['[comment]'] = $item->comment;
							$notify_tags = apply_filters('notify_tags_userverify3_u', $notify_tags, $item);					
							
							$user_send_data = array(
								'user_email' => is_email($item->user_email),
							);	
							$user_send_data = apply_filters('user_send_data', $user_send_data, 'userverify3_u', $ui);
							$result_mail = apply_filters('premium_send_message', 0, 'userverify3_u', $notify_tags, $user_send_data);

							set_locale($now_locale);
 							
						}	
					}		
				}
			}

			if ('false' == $action) {			
				foreach ($_POST['id'] as $id) {
					$id = intval($id);		
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_wallets WHERE id = '$id' AND status != '2'");
					if (isset($item->id)) {
						
						$user_wallet_id = $item->user_wallet_id;
									
						$res = apply_filters('item_walletsverify_false_before', pn_ind(), $id, $item);
						if ($res['ind']) {	
									
							do_action('item_walletsverify_false', $id, $item);
							$arr = array();
							$arr['status'] = 2;
							$result = $wpdb->update($wpdb->prefix . 'uv_wallets', $arr, array('id' => $item->id));
							if ($result) {
								do_action('item_walletsverify_false_after', $id, $item);
							}

							$now_locale = get_locale();
							$user_locale = pn_strip_input($item->locale);

							$purse = pn_strip_input($item->wallet_num);
							$ui = get_userdata($item->user_id);

							set_locale($user_locale);
										
							$notify_tags = array();
							$notify_tags['[user_login]'] = $item->user_login;
							$notify_tags['[purse]'] = $purse;
							$notify_tags['[comment]'] = $item->comment;
							$notify_tags = apply_filters('notify_tags_userverify4_u', $notify_tags, $item);					
								
							$user_send_data = array(
								'user_email' => is_email($item->user_email),
							);	
							$user_send_data = apply_filters('user_send_data', $user_send_data, 'userverify4_u', $ui);
							$result_mail = apply_filters('premium_send_message', 0, 'userverify4_u', $notify_tags, $user_send_data);

							set_locale($now_locale);

							$verify_request = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "uv_wallets WHERE user_wallet_id = '$user_wallet_id' AND status = '1'");
							if (0 == $verify_request) {			
								$arr = array();
								$arr['verify'] = 0;
								$wpdb->update($wpdb->prefix.'user_wallets', $arr, array('id' => $user_wallet_id));	
								do_action('item_userwallets_unverify', $user_wallet_id);
							}
									
						}
					}
				}			
			}

			if ('delete' == $action) {			
				foreach ($_POST['id'] as $id) {
					$id = intval($id);			
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "uv_wallets WHERE id = '$id'");
					if (isset($item->id)) {
						
						$user_wallet_id = $item->user_wallet_id;
								
						$res = apply_filters('item_walletsverify_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {						
								
							do_action('item_walletsverify_delete', $id, $item);
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "uv_wallets WHERE id = '$id'");
							if ($result) {
								do_action('item_walletsverify_delete_after', $id, $item);
							}
								
							$now_locale = get_locale();
							$user_locale = pn_strip_input($item->locale);

							$purse = pn_strip_input($item->wallet_num);
							$ui = get_userdata($item->user_id);
										
							set_locale($user_locale);

							$notify_tags = array();
							$notify_tags['[user_login]'] = $item->user_login;
							$notify_tags['[purse]'] = $purse;
							$notify_tags['[comment]'] = $item->comment;
							$notify_tags = apply_filters('notify_tags_userverify5_u', $notify_tags, $item);					
								
							$user_send_data = array(
								'user_email' => is_email($item->user_email),
							);	
							$user_send_data = apply_filters('user_send_data', $user_send_data, 'userverify5_u', $ui);
							$result_mail = apply_filters('premium_send_message', 0, 'userverify5_u', $notify_tags, $user_send_data);

							set_locale($now_locale);
								
							$verify_request = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "uv_wallets WHERE user_wallet_id = '$user_wallet_id' AND status = '1'");
							if (0 == $verify_request) {		
								$arr = array();
								$arr['verify'] = 0;
								$wpdb->update($wpdb->prefix . 'user_wallets', $arr, array('id' => $user_wallet_id));								
								do_action('item_userwallets_unverify', $user_wallet_id);
							}
									
						}
					}
				}			
			}
			
			do_action('pntable_walletsverify_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
			
		}				
	} 
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
} 

class pn_userwallets_verify_Table_List extends PremiumTable {

	function __construct() {
		
		parent::__construct();
				
		$this->primary_column = 'create_date';
		$this->save_button = 0;
		
	}
		
	function column_default($item, $column_name) {
			
		if ('accnum' == $column_name) {
			return pn_strip_input($item->wallet_num);
		} elseif ('create_date' == $column_name) {	
			return get_pn_time($item->create_date, 'd.m.Y, H:i');
		} elseif ('ip' == $column_name) {
			return pn_strip_input($item->user_ip);
		} elseif ('user' == $column_name) {
			$user_id = $item->user_id;
			$us = '<a href="' . pn_edit_user_link($user_id) . '">' . is_user($item->user_login) . '</a>';
			return $us;
		} elseif ('ps' == $column_name) { 	
			return get_currency_title_by_id($item->currency_id);
		} elseif ('files' == $column_name) {
			$html = '<div class="js_hf_files">';
			if (function_exists('get_usac_files')) {
				set_hf_js();
				$html .= get_usac_files($item->user_wallet_id);
			}
			$html .= '</div>';
			return $html;
		} elseif ('status' == $column_name) {
			if (1 == $item->status) {
				$status ='<span class="bgreen">'. __('Verified', 'pn') .'</span>';
			} elseif (2 == $item->status) {
				$status ='<span class="bred">'. __('Unverified', 'pn') .'</span>';
			} else {
				$status = '<b>'.  __('Pending verification', 'pn')  .'</b>';
			}
			return $status;
		} elseif ('comment' == $column_name) {
			$comment_text = trim($item->comment);		
			return _comment_label('walletsverify', $item->id, $comment_text);				
		} 
			
		return '';
	}	
		
	function column_cb($item) {
		
		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
	}			
		
	function tr_class($tr_class, $item) {
		
		if (1 == $item->status) {
			$tr_class[] = 'tr_green';
		}
		
		if (2 == $item->status) {
			$tr_class[] = 'tr_red';
		}		
		
		return $tr_class;
	}		
		
	function get_columns() {
		
		$columns = array(
			'cb'        => '',
			'create_date'     => __('Creation date', 'pn'),
			'user'     => __('User', 'pn'),
			'ip' => __('IP', 'pn'),
			'ps' => __('PS', 'pn'),
			'accnum' => __('Account number', 'pn'),
			'files' => __('Files', 'pn'),
			'status'  => __('Status', 'pn'),
			'comment'     => __('Failure reason', 'pn'),
		);
		
		return $columns;
	}	

	function get_bulk_actions() {
		
		$actions = array(
			'true'    => __('Verify', 'pn'),
			'false'    => __('Unverify', 'pn'),
			'delete'    => __('Delete', 'pn'),
		);
		
		return $actions;
	}
		
	function get_search() {
		
		$search = array();
		$search['user_login'] = array(
			'view' => 'input',
			'title' => __('User login', 'pn'),
			'default' => is_user(is_param_get('user_login')),
			'name' => 'user_login',
		);
		$search['wallet_num'] = array(
			'view' => 'input',
			'title' => __('Account number', 'pn'),
			'default' => pn_strip_input(is_param_get('wallet_num')),
			'name' => 'wallet_num',
		);
		$search['user_ip'] = array(
			'view' => 'input',
			'title' => __('IP', 'pn'),
			'default' => pn_strip_input(is_param_get('user_ip')),
			'name' => 'user_ip',
		);		
			
		$currency = list_currency(__('All currency', 'pn'));
		$search['currency_id'] = array(
			'view' => 'select',
			'options' => $currency,
			'title' => __('Currency', 'pn'),
			'default' => pn_strip_input(is_param_get('currency_id')),
			'name' => 'currency_id',
		);		
		
		return $search;
	}			
			
	function get_submenu() {
		
		$options = array();
		$options['filter'] = array(
			'options' => array(
				'1' => __('pending request', 'pn'),
				'2' => __('verified request', 'pn'),
				'3' => __('unverified request', 'pn'),
			),
			'title' => '',
		);
		
		return $options;
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
		if (1 == $filter) { //в ожидании
			$where .= " AND status = '0'";
		} elseif (2 == $filter) { //верифицированные
			$where .= " AND status = '1'";
		} elseif (3 == $filter) { //не верифицированные
			$where .= " AND status = '2'";
		}  		

		$user_login = is_user(is_param_get('user_login'));
		if ($user_login) {
			$where .= " AND user_login LIKE '%$user_login%'";
		}

		$user_ip = pn_sfilter(pn_strip_input(is_param_get('user_ip')));
		if ($user_ip) {
			$where .= " AND user_ip LIKE '%$user_ip%'";
		}

		$wallet_num = pn_sfilter(pn_strip_input(is_param_get('wallet_num')));
		if ($wallet_num) {
			$where .= " AND wallet_num LIKE '%$wallet_num%'";
		}

		$currency_id = intval(is_param_get('currency_id'));
		if ($currency_id) { 
			$where .= " AND currency_id = '$currency_id'";
		}		
			
		$where = $this->search_where($where);
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "uv_wallets WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "uv_wallets WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page"); 
 		
	}	
}  