<?php
if (!defined('ABSPATH')) { exit(); }

if (is_admin()) {

	add_filter('pn_adminpage_title_pn_parser_pairs', 'def_adminpage_title_pn_parser_pairs');
	function def_adminpage_title_pn_parser_pairs($title) {
		
		return __('Rates', 'pn');
	} 

	add_action('pn_adminpage_content_pn_parser_pairs', 'def_adminpage_content_pn_parser_pairs');
	function def_adminpage_content_pn_parser_pairs() {
		
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

add_action('premium_action_pn_parser_pairs', 'def_premium_action_pn_parser_pairs');
function def_premium_action_pn_parser_pairs() {
	global $wpdb;	

	_method('post');
	
	pn_only_caps(array('administrator', 'pn_directions', 'pn_parser'));

	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
				
	if (isset($_POST['save'])) {
					
		if (isset($_POST['pair_give']) and is_array($_POST['pair_give']) and isset($_POST['pair_get']) and is_array($_POST['pair_get'])) {
			foreach ($_POST['pair_give'] as $id => $pair_give) {
				$id = intval($id);
				$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "parser_pairs WHERE id = '$id'");
				if (isset($item->id)) {
					
					$pair_give = pn_strip_input($pair_give);
					$pair_get = pn_strip_input($_POST['pair_get'][$id]);	
								
					$array = array();	
					$array['pair_give'] = $pair_give;
					$array['pair_get'] = $pair_get;
					$result = $wpdb->update($wpdb->prefix . "parser_pairs", $array, array('id' => $id));
					
					do_action('item_parser_pairs_save', $item->id, $item, $result, $array);
				
				}
			}					
		}
			
		do_action('pntable_parser_pairs_save');
		$arrs['reply'] = 'true';

	} else {	
		if (isset($_POST['id']) and is_array($_POST['id'])) {
			
			if ('delete' == $action) {				
				foreach ($_POST['id'] as $id) {
					$id = intval($id);	
					$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "parser_pairs WHERE id = '$id'");
					if (isset($item->id)) {
						$res = apply_filters('item_parser_pairs_delete_before', pn_ind(), $id, $item);
						if ($res['ind']) {
							$result = $wpdb->query("DELETE FROM " . $wpdb->prefix . "parser_pairs WHERE id = '$id'");
							do_action('item_parser_pairs_delete', $id, $item, $result);
							if ($result) {
								$wpdb->update($wpdb->prefix . "directions", array('new_parser' => '0'), array('new_parser' => $id));
								$wpdb->update($wpdb->prefix . "currency_codes", array('new_parser' => '0'), array('new_parser' => $id));
							}
						}	
					}		
				}
			}
				
			do_action('pntable_parser_pairs_action', $action, $_POST['id']);
			$arrs['reply'] = 'true';					
		} 		
	}
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
}  

class pn_parser_pairs_Table_List extends PremiumTable {

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
			'edit'      => '<a href="' . admin_url('admin.php?page=pn_add_parser_pairs&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
		);			
		return $actions;
	}		
		
	function column_default($item, $column_name) {
			
		if ('source' == $column_name) {
			return pn_strip_input($item->title_birg); 
		} elseif ('calc1' == $column_name) {		
			return '<textarea style="width: 100%; height: 100px;" name="pair_give[' . $item->id . ']">' . pn_strip_input($item->pair_give) . '</textarea>';	
		} elseif ('calc2' == $column_name) {	
			return '<textarea style="width: 100%; height: 100px;" name="pair_get[' . $item->id . ']">' . pn_strip_input($item->pair_get) . '</textarea>';
		} elseif ('rate1' == $column_name) {
			return get_parser_course($item->pair_give);
		} elseif ('rate2' == $column_name) {
			return get_parser_course($item->pair_get);
		} elseif ('title' == $column_name) {	
			return pn_strip_input($item->title_pair_give) . '-' . pn_strip_input($item->title_pair_get);
		} elseif ('copy' == $column_name) {	
			return '<a href="' . pn_link('copy_parser_pairs', 'post') . '&item_id=' . $item->id . '" class="button">' . __('Copy', 'pn') . '</a>';			
		} 
			
		return '';
	}		

	function get_columns() {
		
		$columns = array(
			'cb'        => '',
			'title'     => __('Rate name', 'pn'),
			'source'     => __('Source name', 'pn'),
			'calc1' => __('Rate formula for Send', 'pn'),
			'calc2' => __('Rate formula for Receive', 'pn'),
			'rate1' => __('Rate for Send', 'pn'),
			'rate2' => __('Rate for Receive', 'pn'),	
			'copy' => __('Copy', 'pn'),
		);
		
		return $columns;
	}	
		
	function get_bulk_actions() {
		
		$actions = array(
			'delete'    => __('Delete', 'pn'),
		);
		
		return $actions;
	}
		
	function get_search() {
		
		$options = array();
		$options[0] = '--' . __('All','pn') . '--';
			
		$items = get_parser_list();  		
		foreach ($items as $item) {
			$options[$item->title_birg] = pn_strip_input($item->title_birg);
		}
		$search = array();
		$search['title_birg'] = array(
			'view' => 'select',
			'title' => __('Source name', 'pn'),
			'default' => is_param_get('title_birg'),
			'options' => $options,
			'name' => 'title_birg',
		);		
		
		return $search;
	}	
		
	function prepare_items() {
		global $wpdb; 
			
		$per_page = $this->count_items();
		$current_page = $this->get_pagenum();
		$offset = $this->get_offset();
			
		$oinfo = $this->db_order('menu_order', 'ASC');
		$orderby = $oinfo['orderby'];
		$order = $oinfo['order'];			
			
		$where = '';
			
		$title_birg = pn_sfilter(pn_strip_input(is_param_get('title_birg')));
		if ($title_birg) {
			$where .= " AND title_birg = '$title_birg'";
		}
			
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "parser_pairs WHERE id > 0 $where");
		}
		$order_by = _order_by_parser();
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "parser_pairs WHERE id > 0 $where ORDER BY $order_by LIMIT $offset , $per_page");  		
	}
		
 	function extra_tablenav($which) {		  	
	?>
		<a href="<?php echo admin_url('admin.php?page=pn_add_parser_pairs'); ?>"><?php _e('Add new', 'pn'); ?></a>		
	<?php 
	}   
}