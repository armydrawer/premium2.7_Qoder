<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_archive_bids', 'def_adminpage_title_pn_archive_bids');
	function def_adminpage_title_pn_archive_bids() {
		
		return __('Archived orders', 'pn');
	}

	add_action('pn_adminpage_content_pn_archive_bids', 'def_adminpage_content_pn_archive_bids');
	function def_adminpage_content_pn_archive_bids() {
		
		$form = new PremiumForm();
		$text = '<a href="' . get_request_link('archivebids', 'html', get_locale(), array('all' => '1')) . '" target="_blank">' . __('Download operations archive', 'pn') . '</a>';
		$form->substrate($text);
	
		premium_table_list();
		
	}

}

add_action('premium_action_pn_archive_bids', 'def_premium_action_pn_archive_bids');
function def_premium_action_pn_archive_bids() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_archive'));
		
	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
		
	if (isset($_POST['save'])) {	
	
		do_action('pntable_archive_save');	
		$arrs['reply'] = 'true';
		
	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {
			
			do_action('pntable_archive_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';
			
		} 
	}

	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
}
 
class pn_archive_bids_Table_List extends PremiumTable {

	function __construct() { 
	
		parent::__construct();
				
		$this->primary_column = 'title';
		$this->save_button = 0;
		
	}
		
	function column_default($item, $column_name) {
		
		$arch = get_archive_info($item);
		
		if ('status' == $column_name) {
			return get_bid_status(is_isset($arch, 'status'));
		} elseif ('valut1' == $column_name) {	
			return pn_strip_input(ctv_ml(is_isset($arch, 'psys_give')) . ' ' . ctv_ml(is_isset($arch, 'currency_code_give')));
		} elseif ('valut2' == $column_name) {	
			return pn_strip_input(ctv_ml(is_isset($arch, 'psys_get')) . ' ' . ctv_ml(is_isset($arch, 'currency_code_get')));
		} elseif ('title' == $column_name) {
			return __('Order', 'pn') . ' ' . $item->bid_id;
		} else {
			return pn_strip_input(ctv_ml(is_isset($arch, $column_name)));
		} 
		
		return '';
	}	
		
	function column_cb($item) {
		
		return '<input type="checkbox" name="id[]" class="pntable-checkbox-single" autocomplete="off" value="' . $item->id . '" />';              
	}	
		
	function get_row_actions($item) {
		
		$actions = array(
			'edit'      => '<a href="' . admin_url('admin.php?page=pn_archive_bid&item_id=' . $item->id . '&paged=' . is_param_get('paged')) . '">' . __('View','pn') . '</a>',
		);			
		
		return $actions;
	}		
		
	function get_columns() {
		
		$columns = array(       
			'title'     => __('ID', 'pn'),
			'archive_date'     => __('Date of archiving', 'pn'),
			'create_date'     => __('Date of creation', 'pn'),
			'valut1' => __('Currency Send', 'pn'),
			'valut2' => __('Currency Receive', 'pn'),
			'user_id' => __('User ID', 'pn'),
			'ref_id' => __('Referral ID', 'pn'),
			'account_give' => __('Account To send', 'pn'),
			'account_get' => __('Account To receive', 'pn'),
			'to_account' => __('Merchant account', 'pn'),
			'from_account' => __('Account used for automatic payout', 'pn'),
			'trans_in' => __('Merchant transaction ID', 'pn'),
			'trans_out' => __('Auto payout transaction ID', 'pn'),
			'txid_in' => __('Merchant txID', 'pn'),
			'txid_out' => __('Auto payout txID', 'pn'),
			'user_phone' => __('Mobile phone number', 'pn'),
			'user_skype' => __('User skype', 'pn'),
			'user_email' => __('User e-mail', 'pn'),
			'user_passport' => __('User passport number', 'pn'),
			'status'  => __('Status', 'pn'),			
		);
		
		return $columns;
	}	
		
	function get_search() {
		
		$currencies = list_currency(__('All currency', 'pn'));
		
		$search = array();			
		$status = list_bid_status();
		$a_status = array('' => '--' . __('All status', 'pn') . '--');
		$a_status = array_merge($a_status, $status);
		$search['status'] = array(
			'view' => 'select',
			'options' => $a_status,
			'title' => __('Status', 'pn'),
			'default' => is_status_name(is_param_get('status')),
			'name' => 'status',
		);		
		$search[] = array(
			'view' => 'line',
		);		
		$search['user_id'] = array(
			'view' => 'input',
			'title' => __('User ID', 'pn'),
			'default' => pn_strip_input(is_param_get('user_id')),
			'name' => 'user_id',
		);
		$search['ref_id'] = array(
			'view' => 'input',
			'title' => __('Referral ID', 'pn'),
			'default' => pn_strip_input(is_param_get('ref_id')),
			'name' => 'ref_id',
		);
		$search['bid_id'] = array(
			'view' => 'input',
			'title' => __('Order ID', 'pn'),
			'default' => pn_strip_input(is_param_get('bid_id')),
			'name' => 'bid_id',
		);
		$search['account_give'] = array(
			'view' => 'input',
			'title' => __('Account Send', 'pn'),
			'default' => pn_strip_input(is_param_get('account_give')),
			'name' => 'account_give',
		);
		$search['account_get'] = array(
			'view' => 'input',
			'title' => __('Account Receive', 'pn'),
			'default' => pn_strip_input(is_param_get('account_get')),
			'name' => 'account_get',
		);
		$search[] = array(
			'view' => 'line',
		);
		$search['to_account'] = array(
			'view' => 'input',
			'title' => __('Merchant account', 'pn'),
			'default' => pn_strip_input(is_param_get('to_account')),
			'name' => 'to_account',
		);
		$search['from_account'] = array(
			'view' => 'input',
			'title' => __('Account used for automatic payout', 'pn'),
			'default' => pn_strip_input(is_param_get('from_account')),
			'name' => 'from_account',
		);
		$search['trans_in'] = array(
			'view' => 'input',
			'title' => __('Merchant transaction ID', 'pn'),
			'default' => pn_strip_input(is_param_get('trans_in')),
			'name' => 'trans_in',
		);
		$search['trans_out'] = array(
			'view' => 'input',
			'title' => __('Auto payout transaction ID', 'pn'),
			'default' => pn_strip_input(is_param_get('trans_out')),
			'name' => 'trans_out',
		);
		$search['txid_in'] = array(
			'view' => 'input',
			'title' => __('Merchant txID', 'pn'),
			'default' => pn_strip_input(is_param_get('txid_in')),
			'name' => 'txid_in',
		);
		$search['txid_out'] = array(
			'view' => 'input',
			'title' => __('Auto payout txID', 'pn'),
			'default' => pn_strip_input(is_param_get('txid_out')),
			'name' => 'txid_out',
		);		
		$search[] = array(
			'view' => 'line',
		);
		$search['first_name'] = array(
			'view' => 'input',
			'title' => __('First name', 'pn'),
			'default' => pn_strip_input(is_param_get('first_name')),
			'name' => 'first_name',
		);
		$search['last_name'] = array(
			'view' => 'input',
			'title' => __('Last name', 'pn'),
			'default' => pn_strip_input(is_param_get('last_name')),
			'name' => 'last_name',
		);
		$search['second_name'] = array(
			'view' => 'input',
			'title' => __('Second name', 'pn'),
			'default' => pn_strip_input(is_param_get('second_name')),
			'name' => 'second_name',
		);	
		$search[] = array(
			'view' => 'line',
		);			
		$search['user_phone'] = array(
			'view' => 'input',
			'title' => __('Mobile phone number', 'pn'),
			'default' => pn_strip_input(is_param_get('user_phone')),
			'name' => 'user_phone',
		);
		$search['user_skype'] = array(
			'view' => 'input',
			'title' => __('Skype', 'pn'),
			'default' => pn_strip_input(is_param_get('user_skype')),
			'name' => 'user_skype',
		);
		$search['user_telegram'] = array(
			'view' => 'input',
			'title' => __('Telegram', 'pn'),
			'default' => pn_strip_input(is_param_get('user_telegram')),
			'name' => 'user_telegram',
		);			
		$search['user_email'] = array(
			'view' => 'input',
			'title' => __('E-mail', 'pn'),
			'default' => pn_strip_input(is_param_get('user_email')),
			'name' => 'user_email',
		);
		$search['user_passport'] = array(
			'view' => 'input',
			'title' => __('Passport number', 'pn'),
			'default' => pn_strip_input(is_param_get('user_passport')),
			'name' => 'user_passport',
		);
		$search[] = array(
			'view' => 'line',
		);
		$search['date1'] = array(
			'view' => 'date',
			'title' => __('Start date', 'pn'),
			'default' => is_pn_date(is_param_get('date1')),
			'name' => 'date1',
		);
		$search['date2'] = array(
			'view' => 'date',
			'title' => __('End date', 'pn'),
			'default' => is_pn_date(is_param_get('date2')),
			'name' => 'date2',
		);
		$search['curr1'] = array(
			'view' => 'select',
			'title' => __('Currency Send', 'pn'),
			'default' => pn_strip_input(is_param_get('curr1')),
			'options' => $currencies,
			'name' => 'curr1',
		);	
		$search['curr2'] = array(
			'view' => 'select',
			'title' => __('Currency Receive', 'pn'),
			'default' => pn_strip_input(is_param_get('curr2')),
			'options' => $currencies,
			'name' => 'curr2',
		);			
		$search[] = array(
			'view' => 'line',
		);
		$search['adate1'] = array(
			'view' => 'date',
			'title' => __('Start date (archiving)', 'pn'),
			'default' => is_pn_date(is_param_get('adate1')),
			'name' => 'adate1',
		);
		$search['adate2'] = array(
			'view' => 'date',
			'title' => __('End date (archiving)', 'pn'),
			'default' => is_pn_date(is_param_get('adate2')),
			'name' => 'adate2',
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

		$user_id = intval(is_param_get('user_id'));
		if ($user_id) {
			$where .= " AND user_id = '$user_id'";
		}
		
		$ref_id = intval(is_param_get('ref_id'));
		if ($ref_id) {
			$where .= " AND ref_id = '$ref_id'";
		}
		
		$bid_id = intval(is_param_get('bid_id'));
		if ($bid_id) {
			$where .= " AND bid_id = '$bid_id'";
		}
		
		$account1 = pn_sfilter(pn_strip_input(is_param_get('account_give')));
		if ($account1) {
			$where .= " AND account_give LIKE '%$account1%'";
		}
		
		$account2 = pn_sfilter(pn_strip_input(is_param_get('account_get')));
		if ($account2) {
			$where .= " AND account_get LIKE '%$account2%'";
		}
		
		$to_account = pn_sfilter(pn_strip_input(is_param_get('to_account')));
		if ($to_account) {
			$where .= " AND to_account LIKE '%$to_account%'";
		}
		
		$from_account = pn_sfilter(pn_strip_input(is_param_get('from_account')));
		if ($from_account) {
			$where .= " AND from_account LIKE '%$from_account%'";
		}
		
		$trans_in = pn_sfilter(pn_strip_input(is_param_get('trans_in')));
		if ($trans_in) {
			$where .= " AND trans_in LIKE '%$trans_in%'";
		}
		
		$trans_out = pn_sfilter(pn_strip_input(is_param_get('trans_out')));
		if ($trans_out) {
			$where .= " AND trans_out LIKE '%$trans_out%'";
		}
		
		$txid_in = pn_sfilter(pn_strip_input(is_param_get('txid_in')));
		if ($txid_in) {
			$where .= " AND txid_in LIKE '%$txid_in%'";
		}
		
		$txid_out = pn_sfilter(pn_strip_input(is_param_get('txid_out')));
		if ($txid_out) {
			$where .= " AND txid_out LIKE '%$txid_out%'";
		}
		
		$first_name = pn_sfilter(pn_strip_input(is_param_get('first_name')));
		if ($first_name) {
			$where .= " AND first_name LIKE '%$first_name%'";
		}
		
		$last_name = pn_sfilter(pn_strip_input(is_param_get('last_name')));
		if ($last_name) {
			$where .= " AND last_name LIKE '%$last_name%'";
		}
		
		$second_name = pn_sfilter(pn_strip_input(is_param_get('second_name')));
		if ($second_name) {
			$where .= " AND second_name LIKE '%$second_name%'";
		}
		
		$user_phone = pn_sfilter(pn_strip_input(is_param_get('user_phone')));
		if ($user_phone) {
			$where .= " AND user_phone LIKE '%$user_phone%'";
		}
		
		$user_skype = pn_sfilter(pn_strip_input(is_param_get('user_skype')));
		if ($user_skype) {
			$where .= " AND user_skype LIKE '%$user_skype%'";
		}
		
		$user_telegram = pn_sfilter(pn_strip_input(is_param_get('user_telegram')));
		if ($user_telegram) {
			$where .= " AND user_telegram LIKE '%$user_telegram%'";
		}
		
		$user_email = pn_sfilter(pn_strip_input(is_param_get('user_email')));
		if ($user_email) {
			$where .= " AND user_email LIKE '%$user_email%'";
		}
		
		$user_passport = pn_sfilter(pn_strip_input(is_param_get('user_passport')));
		if ($user_passport) {
			$where .= " AND user_passport LIKE '%$user_passport%'";
		}
		
		$curr1 = pn_sfilter(intval(is_param_get('curr1')));
		if ($curr1) {
			$where .= " AND currency_id_give = '$curr1'";
		}
		
		$curr2 = pn_sfilter(intval(is_param_get('curr2')));
		if ($curr2) {
			$where .= " AND currency_id_get = '$curr2'";
		}
		
		$date1 = is_pn_date(is_param_get('date1'));
		if ($date1) {
			$date = get_pn_date($date1, 'Y-m-d');
			$where .= " AND create_date >= '$date'";
		}
		
		$date2 = is_pn_date(is_param_get('date2'));
		if ($date2) {
			$date = get_pn_date($date2, 'Y-m-d');
			$where .= " AND create_date < '$date'";
		}
		
		$adate1 = is_pn_date(is_param_get('adate1'));
		if ($adate1) {
			$date = get_pn_date($adate1, 'Y-m-d');
			$where .= " AND archive_date >= '$date'";
		}	
		
		$adate2 = is_pn_date(is_param_get('adate2'));
		if ($adate2) {
			$date = get_pn_date($adate2, 'Y-m-d');
			$where .= " AND archive_date < '$date'";
		}
		
		$status = is_status_name(is_param_get('status'));
		if ($status) {
			$where .= " AND status = '$status'";
		}
			
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "archive_exchange_bids WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "archive_exchange_bids WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page");
  		
	}	  
} 	