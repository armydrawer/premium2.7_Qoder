<?php
if (!defined('ABSPATH')) { exit(); }

/*
title: [en_US:]Bulk information editor for users[:en_US][ru_RU:]Массовый редактор информации для пользователей[:ru_RU]
description: [en_US:]Bulk information editor for users[:en_US][ru_RU:]Массовый редактор информации для пользователей[:ru_RU]
version: 2.7.0
category: [en_US:]Exchange directions[:en_US][ru_RU:]Направления обменов[:ru_RU]
cat: directions
new: 1
*/

add_action('admin_menu', 'admin_menu_masseditor', 1000);
function admin_menu_masseditor(){
	global $premiumbox;	
	
	if (current_user_can('administrator') or current_user_can('pn_directions')) {
		add_submenu_page("pn_directions", __('Bulk information editor for users', 'pn'), __('Bulk information editor for users', 'pn'), 'read', "pn_nasp_masseditor", array($premiumbox, 'admin_temp'));
	}
	
} 

add_filter('pn_adminpage_title_pn_nasp_masseditor', 'pn_adminpage_title_pn_nasp_masseditor');
function pn_adminpage_title_pn_nasp_masseditor() {
	
	return __('Exchange directions', 'pn');
}

add_action('pn_adminpage_content_pn_nasp_masseditor', 'def_pn_adminpage_content_pn_nasp_masseditor');
function def_pn_adminpage_content_pn_nasp_masseditor() {
	
	premium_table_list();
	
}	

add_action('premium_action_pn_nasp_masseditor', 'def_premium_action_pn_nasp_masseditor');
function def_premium_action_pn_nasp_masseditor() {
	global $wpdb;	

	_method('post');
	pn_only_caps(array('administrator', 'pn_directions'));

	$arrs = array(
		'paged' => intval(is_param_post('paged')),
	);
	$action = get_request_action();
				
	if (isset($_POST['save'])) {
					
		$list_directions_temp = apply_filters('list_directions_temp', array());
		
		if (isset($_POST['ids']) and is_array($_POST['ids'])) {	
			foreach ($_POST['ids'] as $id => $template_id) {
				$id = intval($id);
				$item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "directions WHERE id = '$id'");
				if (isset($item->id)) {
					
					$arr = array();
					
					if ('minmax' == $template_id) {
						
						$arr['min_sum1'] = is_sum(is_param_post('min_sum1_' . $id));
						$arr['max_sum1'] = is_sum(is_param_post('max_sum1_' . $id));
						$arr['min_sum2'] = is_sum(is_param_post('min_sum2_' . $id));
						$arr['max_sum2'] = is_sum(is_param_post('max_sum2_' . $id));

						$result = $wpdb->update($wpdb->prefix . 'directions', $arr, array('id' => $id));
						do_action('item_direction_save', $item->id, $item, $result, $arr);
						
					} elseif ('com' == $template_id) {	
					
						$arr['com_sum1'] = is_sum(is_param_post('com_sum1_' . $id));
						$arr['com_pers1'] = is_sum(is_param_post('com_pers1_' . $id));
						$arr['com_sum2'] = is_sum(is_param_post('com_sum2_' . $id));
						$arr['com_pers2'] = is_sum(is_param_post('com_pers2_' . $id));				
					
						$result = $wpdb->update($wpdb->prefix . 'directions', $arr, array('id' => $id));
						do_action('item_direction_save', $item->id, $item, $result, $arr);
						
					} elseif ('dopcom' == $template_id) {
						
						$arr['com_box_sum1'] = is_sum(is_param_post('com_box_sum1_' . $id));
						$arr['com_box_pers1'] = is_sum(is_param_post('com_box_pers1_' . $id));
						$arr['com_box_sum2'] = is_sum(is_param_post('com_box_sum2_' . $id));
						$arr['com_box_pers2'] = is_sum(is_param_post('com_box_pers2_' . $id));						
						
						$result = $wpdb->update($wpdb->prefix . 'directions', $arr, array('id' => $id));
						do_action('item_direction_save', $item->id, $item, $result, $arr);
						
					} elseif ($template_id) {
						
						if (isset($list_directions_temp[$template_id])) {
							$value = pn_strip_text(is_param_post_ml('textfield_' . $id));
							update_direction_meta($id, $template_id, $value);
							delete_direction_txtmeta($id, $template_id);
						}
					
					}
					
				}	
			}
		}			
					
		do_action('pntable_nasp_masseditor_save');
		$arrs['reply'] = 'true';

	} 
				
	$url = pn_admin_filter_data('', 'reply, paged');
	$url = add_query_args($arrs, $url);
	wp_redirect($url);
	exit;			
} 

class pn_nasp_masseditor_Table_List extends PremiumTable {

	function __construct() { 
	
		parent::__construct();
					
		$this->primary_column = 'title';
		$this->save_button = 1;
		
	}
		
	function get_thwidth() {
		
		$array = array();
		$array['id'] = '30px';
		
		return $array;
	}	
		
	function column_default($item, $column_name) {
		
		if ('field' == $column_name) {
			global $premiumbox;	
			
			$template_id = pn_strip_input(is_param_get('template_id'));
			$html = '<input type="hidden" name="ids[' . $item->id . ']" value="' . $template_id . '" />';
			
			if ('minmax' == $template_id) {
				
				$html .= '
					<div>' . __('Minimum amount', 'pn') . ' (' . __('give', 'pn') . ')</div>
					<div style="padding: 0 0 5px 0;"><input type="text" style="width: 100%; max-width: 80px;" name="min_sum1_' . $item->id . '" value="' . is_sum($item->min_sum1) . '" /></div>
					<div>' . __('Maximum amount', 'pn') . ' (' . __('give', 'pn') . ')</div>
					<div style="padding: 0 0 5px 0;"><input type="text" style="width: 100%; max-width: 80px;" name="max_sum1_' . $item->id . '" value="' . is_sum($item->max_sum1) . '" /></div>
					<div>' . __('Minimum amount', 'pn') . ' (' . __('get', 'pn') . ')</div>
					<div style="padding: 0 0 5px 0;"><input type="text" style="width: 100%; max-width: 80px;" name="min_sum2_' . $item->id . '" value="' . is_sum($item->min_sum2) . '" /></div>
					<div>' . __('Maximum amount', 'pn') . ' (' . __('get', 'pn') . ')</div>
					<div style="padding: 0 0 5px 0;"><input type="text" style="width: 100%; max-width: 80px;" name="max_sum2_' . $item->id . '" value="' . is_sum($item->max_sum2) . '" /></div>					
				';
				
			} elseif ('com' == $template_id) {	
			
				$html .= '
					<div>' . __('User &rarr; Exchange', 'pn') . '</div>
					<div style="padding: 0 0 5px 0;"><input type="text" style="width: 100%; max-width: 80px;" name="com_sum1_' . $item->id . '" value="' . is_sum($item->com_sum1) . '" />S</div>
					<div style="padding: 0 0 5px 0;"><input type="text" style="width: 100%; max-width: 80px;" name="com_pers1_' . $item->id . '" value="' . is_sum($item->com_pers1) . '" />%</div>
					<div>' . __('Exchange &rarr; User', 'pn') . '</div>
					<div style="padding: 0 0 5px 0;"><input type="text" style="width: 100%; max-width: 80px;" name="com_sum2_' . $item->id . '" value="' . is_sum($item->com_sum2) . '" />S</div>
					<div style="padding: 0 0 5px 0;"><input type="text" style="width: 100%; max-width: 80px;" name="com_pers2_' . $item->id . '" value="' . is_sum($item->com_pers2) . '" />%</div>					
				';			
			
			} elseif ('dopcom' == $template_id) {
				
				$html .= '
					<div>' . __('Additional sender fee', 'pn') . '</div>
					<div style="padding: 0 0 5px 0;"><input type="text" style="width: 100%; max-width: 80px;" name="com_box_sum1_' . $item->id . '" value="' . is_sum($item->com_box_sum1) . '" />S</div>
					<div style="padding: 0 0 5px 0;"><input type="text" style="width: 100%; max-width: 80px;" name="com_box_pers1_' . $item->id . '" value="' . is_sum($item->com_box_pers1) . '" />%</div>
					<div>' . __('Additional recipient fee', 'pn') . '</div>
					<div style="padding: 0 0 5px 0;"><input type="text" style="width: 100%; max-width: 80px;" name="com_box_sum2_' . $item->id . '" value="' . is_sum($item->com_box_sum2) . '" />S</div>
					<div style="padding: 0 0 5px 0;"><input type="text" style="width: 100%; max-width: 80px;" name="com_box_pers2_' . $item->id . '" value="' . is_sum($item->com_box_pers2) . '" />%</div>					
				';				
			
			} elseif ($template_id) {	
				
				$form = new PremiumForm();
				$text = '';
				$text = pn_strip_text(get_direction_meta($item->id, $template_id));
				if (strlen($text) < 1) {
					$text = pn_strip_text(get_direction_txtmeta($item->id, $template_id));
				}									
				if (strlen($text) < 1) { 
					$text = $premiumbox->get_option('naps_temp', $template_id); 
				} 
			
				$html .= $form->get_editor('textfield_' . $item->id, $text, '12', '', 1, 0, apply_filters('direction_instruction_tags', array(), $template_id), 1, 1, 1);
				
			}
			
			return $html;
		} elseif ('status' == $column_name) {
			if (0 == $item->direction_status) { 
				return '<span class="bred">' . __('inactive direction', 'pn') . '</span>'; 
			} elseif (1 == $item->direction_status) { 
				return '<span class="bgreen">' . __('active direction', 'pn') . '</span>'; 
			} elseif (2 == $item->direction_status) { 
				return '<strong>' . __('hold direction', 'pn') . '</strong>'; 	
			}	
		} elseif ('title' == $column_name) {
			return pn_strip_input($item->tech_name);
		} elseif ('id' == $column_name) {
			return '<strong>' . $item->id . '</strong>';	
		} 
		
		return '';
	}	
		
	function get_row_actions($item) {
		
		$actions = array(
			'edit'      => '<a href="' . admin_url('admin.php?page=pn_add_directions&item_id=' . $item->id) . '">' . __('Edit', 'pn') . '</a>',
		);
		if (0 != $item->direction_status and 1 == $item->auto_status) {
			$actions['view'] = '<a href="' . get_exchange_link($item->direction_name) . '" target="_blank">' . __('View', 'pn') . '</a>';
		}	
		
		return $actions;
	}		
		
	function tr_class($tr_class, $item) {
		
		if (0 == $item->direction_status) {
			$tr_class[] = 'tr_red';
		} elseif (2 == $item->direction_status) {
			$tr_class[] = 'tr_blue';
		}			
		
		return $tr_class;
	}		
		
	function get_columns() {
		
		$columns = array(
			'id'     => __('ID', 'pn'),
			'title'     => __('Direction', 'pn'),
			'field' => __('Field', 'pn'),
			'status' => __('Status', 'pn'),
		);	
		
		return $columns;
	}	
			
	function get_submenu() {
		
		$options = array();
		$options['filter'] = array(
			'options' => array(
				'2' => __('active direction', 'pn'),
				'1' => __('inactive direction', 'pn'),
				'3' => __('frozen direction', 'pn'),
				'9' => __('in basket', 'pn'),
			),
		);	
		
		return $options;
	}
		
	function get_sortable_columns() {
		
		$sortable_columns = array( 
			'id' => array('id', 'desc'),
			'title' => array('site_order1', 'ASC'),
		);
		
		return $sortable_columns;
	}

	function get_search() {
		
		$search = array();
			
		$currencies = list_currency(__('All currency', 'pn'));
		$search['curr_give'] = array(
			'view' => 'select',
			'title' => __('Currency send', 'pn'),
			'default' => pn_strip_input(is_param_get('curr_give')),
			'options' => $currencies,
			'name' => 'curr_give',
		);
		$search['curr_get'] = array(
			'view' => 'select',
			'title' => __('Currency receive', 'pn'),
			'default' => pn_strip_input(is_param_get('curr_get')),
			'options' => $currencies,
			'name' => 'curr_get',
		);
		$search['line1'] = array(
			'view' => 'line',
		);				
		$psys = list_psys(__('All payment systems', 'pn'));	
		$search['psys_id_give'] = array(
			'view' => 'select',
			'title' => __('Payment system send', 'pn'),
			'default' => pn_strip_input(is_param_get('psys_id_give')),
			'options' => $psys,
			'name' => 'psys_id_give',
		);
		$search['psys_id_get'] = array(
			'view' => 'select',
			'title' => __('Payment system receive', 'pn'),
			'default' => pn_strip_input(is_param_get('psys_id_get')),
			'options' => $psys,
			'name' => 'psys_id_get',
		);
		$search['line2'] = array(
			'view' => 'line',
		);		
		$list_directions_temp = apply_filters('list_directions_temp', array());
		$list = array('' => '--' . __('No field selected', 'pn') . '--');
		$list['minmax'] = __('Exchange amount', 'pn');
		$list['com'] = __('Payment systems fees', 'pn');
		$list['dopcom'] = __('Exchange office fees', 'pn');
		if (is_array($list_directions_temp)) {
			foreach ($list_directions_temp as $key => $title) { 
				$list[$key] = $title;
			}
		}
		$search['template_id'] = array(
			'view' => 'select',
			'title' => __('Field', 'pn'),
			'default' => pn_strip_input(is_param_get('template_id')),
			'options' => $list,
			'name' => 'template_id',
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
			
		$filter = intval(is_param_get('filter'));
		$in_filter = array('1', '2', '3');
		if (in_array($filter, $in_filter)) {
			$filter = $filter - 1;
			$where .= " AND direction_status='$filter'"; 	
		}
			
		if (9 == $filter) {	
			$where .= " AND auto_status = '0'";
		} else {
			$where .= " AND auto_status = '1'";
		}			
			
		$curr_give = intval(is_param_get('curr_give'));
		if ($curr_give > 0) { 
			$where .= " AND currency_id_give = '$curr_give'"; 
		}
		
		$curr_get = intval(is_param_get('curr_get'));
		if ($curr_get > 0) { 
			$where .= " AND currency_id_get = '$curr_get'"; 
		}

		$psys_id_give = intval(is_param_get('psys_id_give'));
		if ($psys_id_give > 0) { 
			$where .= " AND psys_id_give = '$psys_id_give'"; 
		}
		
		$psys_id_get = intval(is_param_get('psys_id_get'));
		if ($psys_id_get > 0) { 
			$where .= " AND psys_id_get = '$psys_id_get'"; 
		}			
			
		$where = $this->search_where($where);
		$select_sql = $this->select_sql('');
		if ($this->navi) {
			$this->total_items = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "directions WHERE id > 0 $where");
		}
		$this->items = $wpdb->get_results("SELECT * $select_sql FROM " . $wpdb->prefix . "directions WHERE id > 0 $where ORDER BY $orderby $order LIMIT $offset , $per_page"); 
 		
	}
		
	function extra_tablenav($which) {
		?>
			<a href="<?php echo admin_url('admin.php?page=pn_add_directions'); ?>"><?php _e('Add new', 'pn'); ?></a>		
		<?php  
	} 
}