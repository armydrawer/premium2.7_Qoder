<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_pexch', 'def_adminpage_title_pn_pexch');
	function def_adminpage_title_pn_pexch() {
		
		return __('Partnership exchanges', 'pn');
	}

	add_action('pn_adminpage_content_pn_pexch', 'def_adminpage_content_pn_pexch');
	function def_adminpage_content_pn_pexch() {
		
		premium_table_list();
		
	}

}

add_action('premium_action_pn_pexch', 'def_premium_action_pn_pexch');
function def_premium_action_pn_pexch() {
	global $wpdb;
		
	_method('post');
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
		
	if (isset($_POST['save'])) {
			
		if (current_user_can('administrator') or current_user_can('pn_pp_bids')) {		
			if (isset($_POST['partner_sum']) and is_array($_POST['partner_sum'])) {
				foreach ($_POST['partner_sum'] as $id => $partner_sum) {
					$id = intval($id);
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$id'");
					if (isset($item->id)) {
						
						$partner_sum = is_sum($partner_sum);
						$arr = array(
							'partner_sum' => $partner_sum,
						);
						
						$result = $wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET partner_sum = '$partner_sum' WHERE id = '$id'");
						
						do_action('item_pexch_save', $item->id, $item, $result, $arr);
						
					}
				}
			}									
		}
			
		do_action('pntable_pexch_save');
		$arrs['reply'] = 'true';

	} else {		
		if (isset($_POST['id']) and is_array($_POST['id'])) {
			
			if ('approve' == $action) {	
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$id' AND pcalc != '1'");
					if (isset($item->id)) {
						$res = apply_filters('item_pexch_approve_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET pcalc = '1' WHERE id = '$id'");
							do_action('item_pexch_approve', $id, $item, $result);
						}
					}		
				}		
			}

			if ('unapprove' == $action) {	 
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "exchange_bids WHERE id = '$id' AND pcalc != '0'");
					if (isset($item->id)) {	
						$res = apply_filters('item_pexch_unapprove_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("UPDATE " . $wpdb->prefix . "exchange_bids SET pcalc = '0' WHERE id = '$id'");
							do_action('item_pexch_unapprove', $id, $item, $result);	
						}
					}
				}		
			}
			
			do_action('pntable_pexch_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
		} 		
	}	
		
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
}  
	 
class pn_pexch_Table_List extends PremiumTable {

	function __construct() {  
	
		parent::__construct();
				
		$this->primary_column = 'date';
		$this->save_button = 1;
		
	}
		
	function column_default($item, $column_name) {
			
		if ('user' == $column_name) {
				
			$user_id = $item->user_id;
			$us = '';
			if ($user_id > 0) {
				$us .= '<a href="' . pn_edit_user_link($user_id) . '">';
				if (isset($item->user_login)) {
					$us .= is_user($item->user_login); 
				}
				$us .='</a>';
			} else {
				$us = __('Guest', 'pn');
			}
				
			return $us;
				
		} elseif ('bid' == $column_name) {
			return '<a href="' . admin_url('admin.php?page=pn_bids&bidid=' . $item->id) . '" target="_blank">' . $item->id . '</a>';			
		} elseif ('date' == $column_name) {
			return get_pn_time($item->create_date, 'd.m.Y, H:i');
		} elseif ('data' == $column_name) {
			return is_sum($item->sum1dc) . '</span> ' . pn_strip_input(ctv_ml($item->psys_give)) . ' ' . is_site_value($item->currency_code_give) . '<br />' . is_sum($item->sum2c) . '</span> ' . pn_strip_input(ctv_ml($item->psys_get)) . ' ' . is_site_value($item->currency_code_get); 
		} elseif ('refsum' == $column_name) {
			$refsum = '';
			$sum = is_sum($item->partner_sum);
			if (current_user_can('administrator') or current_user_can('pn_pp_bids')) {
				$refsum .= '<div><input type="text" style="width: 90px;" name="partner_sum[' . $item->id . ']" value="' . $sum . '" /> ' . cur_type() . '</div>';
			} else {	
				$refsum .= '<div>' . $sum . ' ' . cur_type() . '</div>';
			}
			$txt = pn_strip_input($item->pcalc_txt);
			if (strlen($txt) > 0 and 0 == $sum) {
				$refsum .= '<div class="bred">' . $txt . '</div>';	
			}
			return $refsum;
		} elseif ('pers' == $column_name) {
			return is_sum($item->partner_pers) . '%';		
		} elseif ('profit' == $column_name) {
			return is_sum($item->profit) . ' ' . cur_type();
		} elseif ('exsum' == $column_name) {
			return is_sum($item->exsum) . ' ' . cur_type();				
		} elseif ('ref' == $column_name) {
			$user_id = $item->ref_id;
			$us = '';
			if ($user_id > 0) {
				$ui = get_userdata($user_id);
				$us .='<a href="' . pn_edit_user_link($user_id) . '">';
				if (isset($ui->user_login)) {
					$us .= is_user($ui->user_login); 
				}
				$us .='</a>';
			}	
				
			return $us;	
		} elseif ('status' == $column_name) {	
			if (0 == $item->pcalc) { 
				return '<span class="bred">' . __('Not accrued reward', 'pn') . '</span>'; 
			} else { 
				return '<span class="bgreen">' . __('Accrued reward', 'pn') . '</span>'; 
			}	
		}
		
		return '';
	}

	function column_cb($item) {
		
		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
	}		
		
	function get_columns() {
		
		$columns = array(      
			'cb'        => '',	
			'date'    => __('Date', 'pn'),
			'user'    => __('User', 'pn'),
			'bid' => __('ID Request', 'pn'),
			'data' => __('Exchange amounts', 'pn'),
			'exsum' => __('Exchange amount', 'pn'),
			'profit' => __('Profit', 'pn'),
			'refsum' => __('Partner earned', 'pn'),
			'pers' => __('Partner percent', 'pn'),
			'ref'    => __('Referral', 'pn'),
			'status'  => __('Status', 'pn'),
		);
		
		return $columns;
	}	
		
	function get_submenu() {
		
		$options = array();
		$options['filter'] = array(
			'options' => array(
				'1' => __('Accrued reward', 'pn'),
				'2' => __('Not accrued reward', 'pn'),
			),
			'title' => '',
		);		
		
		return $options;
	}		
		
	function tr_class($tr_class, $item) {
		
		if (1 != $item->pcalc) {
			$tr_class[] = 'tr_red';
		}
		
		return $tr_class;
	}

	function get_bulk_actions() {
		
		$actions = array(
			'approve'    => __('Accrued reward', 'pn'),
			'unapprove'    => __('Not accrued reward', 'pn'),
		);
		
		return $actions;
	}		
		
	function get_search() {
		
		$search = array();
		$search['suser'] = array(
			'view' => 'input',
			'title' => __('Referral', 'pn'),
			'default' => pn_strip_input(is_param_get('suser')),
			'name' => 'suser',
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
		$suser = pn_strip_input(is_param_get('suser')); 
		if ($suser) {
			$suser_id = username_exists($suser);
			$where .= " AND ref_id='$suser_id'";
		}
		
		$filter = intval(is_param_get('filter'));
		if (1 == $filter) { 
			$where .= " AND pcalc='1'"; 
		} elseif (2 == $filter) {
			$where .= " AND pcalc='0'";
		}			
			
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "exchange_bids WHERE ref_id > 0 AND status = 'success' $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "exchange_bids WHERE ref_id > 0 AND status = 'success' $where ORDER BY $orderby $order LIMIT $offset , $per_page");
  		
	}	  
} 